<?php

namespace App\Services\MobileShop\Common;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MobileShopInvoiceHelper
{
    /**
     * Generate Race-Free Atomic Sequential Invoice Number per Company
     */
    public static function getNextInvoiceNumber(int $companyId, string $prefix): string
    {
        $exists = DB::table('ms_invoice_sequences')
            ->where('company_id', $companyId)
            ->where('prefix', $prefix)
            ->exists();

        if (!$exists) {
            try {
                DB::table('ms_invoice_sequences')->insert([
                    'company_id' => $companyId,
                    'prefix' => $prefix,
                    'current_sequence' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Throwable $e) {
                // Ignore duplicate insertion during concurrent requests
            }
        }

        $seq = DB::table('ms_invoice_sequences')
            ->where('company_id', $companyId)
            ->where('prefix', $prefix)
            ->lockForUpdate()
            ->first();

        $nextNum = ($seq ? (int) $seq->current_sequence : 0) + 1;

        DB::table('ms_invoice_sequences')
            ->where('company_id', $companyId)
            ->where('prefix', $prefix)
            ->update([
                'current_sequence' => $nextNum,
                'updated_at' => now(),
            ]);

        return sprintf('%s-%s%04d', $prefix, date('Ymd'), $nextNum);
    }

    /**
     * Resolve current store default state code (e.g., UP 09 or MH 27) with 15-minute cache
     */
    public static function getStoreStateCode(): string
    {
        return (string) Cache::remember('store:state_code', 900, function () {
            return (string) setting('company.state_code', '09');
        });
    }

    public static function clearInvoiceCache(): void
    {
        Cache::forget('store:state_code');
    }

    /**
     * Calculate GST amounts and split based on store state and customer state
     */
    public static function calculateGst(float $salePrice, float $taxRate, string $billType, string $storeState, ?string $customerStateCode): array
    {
        if ($billType === 'non_gst') {
            return [
                'taxRate'      => 0.00,
                'taxable'      => $salePrice,
                'totalTax'     => 0.00,
                'cgst'         => 0.00,
                'sgst'         => 0.00,
                'igst'         => 0.00,
                'isStateMatch' => true,
                'taxType'      => 'intra_state',
                'billType'     => 'non_gst',
            ];
        }

        $taxable = round($salePrice / (1 + ($taxRate / 100)), 2);
        $totalTax = round($salePrice - $taxable, 2);
        $isStateMatch = empty($customerStateCode) || ($customerStateCode === $storeState);
        $halfTax = round($totalTax / 2, 2);

        $cgst = $isStateMatch ? $halfTax : 0.00;
        $sgst = $isStateMatch ? ($totalTax - $halfTax) : 0.00;
        $igst = !$isStateMatch ? $totalTax : 0.00;

        return [
            'taxRate'      => $taxRate,
            'taxable'      => $taxable,
            'totalTax'     => $totalTax,
            'cgst'         => $cgst,
            'sgst'         => $sgst,
            'igst'         => $igst,
            'isStateMatch' => $isStateMatch,
            'taxType'      => $isStateMatch ? 'intra_state' : 'inter_state',
            'billType'     => 'gst',
        ];
    }

    /**
     * Find existing customer by phone (cached 5 min) or create a new customer record.
     * Supports both (companyId, Request) and (companyId, phone, name, storeState, gstin, address, stateCode)
     */
    public static function findOrCreateCustomer(
        int $companyId,
        $requestOrPhone,
        ?string $name = null,
        ?string $storeState = null,
        ?string $gstin = null,
        ?string $address = null,
        ?string $stateCode = null
    ): object {
        $defaultStoreState = self::getStoreStateCode();
        if ($requestOrPhone instanceof Request) {
            $phone = trim((string) ($requestOrPhone->customer_phone ?? ''));
            $name = trim((string) ($requestOrPhone->customer_name ?? 'Walk-in Customer'));
            $address = $requestOrPhone->customer_address ?? null;
            $gstin = $requestOrPhone->customer_gstin ?? null;
            $stateCode = $requestOrPhone->customer_state_code ?: $defaultStoreState;
        } else {
            $phone = trim((string) $requestOrPhone);
            $name = trim((string) ($name ?: 'Walk-in Customer'));
            $address = $address ?? null;
            $gstin = $gstin ?? null;
            $stateCode = $stateCode ?: ($storeState ?: $defaultStoreState);
        }

        if (empty($phone)) {
            $phone = '0000000000';
        }
        if (empty($name)) {
            $name = 'Walk-in Customer';
        }

        $customer = Cache::remember("ms_cust_{$companyId}_{$phone}", 300, function () use ($companyId, $phone) {
            return DB::table('ms_customers')
                ->where('company_id', $companyId)
                ->where('phone', $phone)
                ->first();
        });

        if (!$customer) {
            $customerId = DB::table('ms_customers')->insertGetId([
                'company_id'     => $companyId,
                'name'           => $name,
                'phone'          => $phone,
                'address'        => $address,
                'gstin'          => $gstin,
                'state_code'     => $stateCode,
                'udhari_balance' => 0.00,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
            $customer = DB::table('ms_customers')->where('id', $customerId)->first();
            Cache::put("ms_cust_{$companyId}_{$phone}", $customer, 300);
        } else if (!empty($gstin) && empty($customer->gstin)) {
            DB::table('ms_customers')->where('id', $customer->id)->update([
                'gstin'      => $gstin,
                'updated_at' => now(),
            ]);
            $customer->gstin = $gstin;
            Cache::put("ms_cust_{$companyId}_{$phone}", $customer, 300);
        }
        return $customer;
    }

    public static function clearCustomerCache(int $companyId, string $phone): void
    {
        Cache::forget("ms_cust_{$companyId}_" . trim($phone));
    }
}
