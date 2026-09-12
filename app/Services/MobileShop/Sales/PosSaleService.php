<?php

namespace App\Services\MobileShop\Sales;

use App\Services\MobileShop\Common\MobileShopInvoiceHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PosSaleService
{
    /**
     * Process Brand New Mobile Sale from POS
     */
    public function process(Request $request, int $companyId): array
    {
        // Idempotency Check
        if ($request->filled('idempotency_key')) {
            $existing = DB::table('ms_mobile_sales')
                ->where('company_id', $companyId)
                ->where('idempotency_key', $request->idempotency_key)
                ->first();
            if ($existing) {
                return [
                    'success' => true,
                    'sale_id' => $existing->id,
                    'invoice_number' => $existing->invoice_number,
                    'idempotent' => true,
                ];
            }
        }

        return DB::transaction(function () use ($request, $companyId) {
            $storeState = MobileShopInvoiceHelper::getStoreStateCode();
            $customer = MobileShopInvoiceHelper::findOrCreateCustomer($companyId, $request);

            // Lock device row
            $device = DB::table('ms_mobile_devices')
                ->where('company_id', $companyId)
                ->where('id', $request->device_id)
                ->where('status', 'in_stock')
                ->where('type', 'new')
                ->lockForUpdate()
                ->first();

            if (!$device) {
                return [
                    'success' => false,
                    'error' => 'Selected new mobile device is no longer in stock!',
                ];
            }

            $salePrice = (float) $request->sale_price;
            $amountPaid = (float) $request->amount_paid;
            $reqTaxRate = (float) ($request->tax_rate ?? 18.00);
            $isGst = $request->boolean('is_gst') || ($request->bill_type === 'gst') || (!$request->has('bill_type') && !$request->has('is_gst'));
            $billType = $isGst ? 'gst' : 'non_gst';

            // Tax Calculation
            $gst = MobileShopInvoiceHelper::calculateGst($salePrice, $reqTaxRate, $billType, $storeState, $customer->state_code ?? null);
            $taxRate = $gst['taxRate'];
            $cgst = $gst['cgst'];
            $sgst = $gst['sgst'];
            $igst = $gst['igst'];
            $isStateMatch = $gst['isStateMatch'];

            $udhariAmount = max(0.00, $salePrice - $amountPaid);

            $emiProviderId = $request->payment_mode === 'emi' ? $request->emi_provider_id : null;
            $emiProcessingFee = 0.00;
            $emiFinanced = 0.00;
            if ($request->payment_mode === 'emi' && $emiProviderId) {
                $emiDownpayment = (float) ($request->emi_downpayment ?? 0.00);
                $emiFinanced = max(0.00, $salePrice - $emiDownpayment);

                $feeType = $request->input('emi_fee_type', 'flat');
                $feeVal  = (float) ($request->input('emi_fee_value', $request->input('emi_processing_fee', 0)));
                $emiProcessingFee = $feeType === 'percent' ? round(($emiFinanced * $feeVal) / 100, 2) : round($feeVal, 2);

                $provider = DB::table('ms_emi_providers')->where('company_id', $companyId)->where('id', $emiProviderId)->lockForUpdate()->first();
                if (!$provider || $provider->advance_balance < $emiFinanced) {
                    $avail = $provider ? $provider->advance_balance : 0;
                    return [
                        'success' => false,
                        'error' => "Finance pool for provider is insufficient! Available: ₹{$avail}, Required: ₹{$emiFinanced}",
                    ];
                }

                DB::table('ms_emi_providers')->where('id', $emiProviderId)->decrement('advance_balance', $emiFinanced);
                DB::table('ms_emi_provider_transactions')->insert([
                    'emi_provider_id' => $emiProviderId,
                    'type' => 'sale_deduction',
                    'amount' => $emiFinanced,
                    'balance_after' => $provider->advance_balance - $emiFinanced,
                    'reference_no' => $request->emi_loan_no,
                    'notes' => "Financing for {$device->brand} {$device->model} (IMEI: {$device->imei_1})",
                    'created_at' => now(),
                ]);
            }

            // Atomic Sequential Invoice Number
            $invoiceNumber = MobileShopInvoiceHelper::getNextInvoiceNumber($companyId, 'INV');

            // Create Sale Record
            $saleId = DB::table('ms_mobile_sales')->insertGetId([
                'company_id'          => $companyId,
                'idempotency_key'     => $request->idempotency_key ?? Str::uuid()->toString(),
                'customer_id'         => $customer->id,
                'invoice_number'      => $invoiceNumber,
                'bill_type'           => $billType,
                'device_id'           => $device->id,
                'sale_price'          => $salePrice,
                'tax_rate'            => $taxRate,
                'tax_type'            => $isStateMatch ? 'intra_state' : 'inter_state',
                'cgst_amount'         => $cgst,
                'sgst_amount'         => $sgst,
                'igst_amount'         => $igst,
                'total_amount'        => $salePrice,
                'amount_paid'         => $amountPaid,
                'udhari_amount'       => $udhariAmount,
                'payment_mode'        => $request->payment_mode,
                'emi_provider_id'     => $emiProviderId,
                'emi_loan_no'         => $request->emi_loan_no,
                'emi_downpayment'     => $request->emi_downpayment ?? 0.00,
                'emi_financed_amount' => $emiFinanced,
                'emi_processing_fee'  => $emiProcessingFee,
                'emi_monthly_amount'  => $request->emi_monthly_amount ?? 0.00,
                'emi_tenure_months'   => (int) ($request->emi_tenure_months ?: 12),
                'sold_by'             => auth()->id(),
                'status'              => 'completed',
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);

            // Update Device Status
            DB::table('ms_mobile_devices')->where('id', $device->id)->update([
                'status' => 'sold',
                'selling_price' => $salePrice,
                'updated_at' => now(),
            ]);

            // Gifts Attachment & Stock Decrement from Accessories & Parts Inventory
            if ($request->has('gift_ids') && is_array($request->gift_ids)) {
                foreach ($request->gift_ids as $giftPartId) {
                    $part = DB::table('ms_parts_inventory')
                        ->where('company_id', $companyId)
                        ->where('id', $giftPartId)
                        ->lockForUpdate()
                        ->first();

                    if ($part && $part->stock_qty >= 1) {
                        $newBalance = $part->stock_qty - 1;
                        DB::table('ms_parts_inventory')->where('id', $part->id)->update([
                            'stock_qty' => $newBalance,
                            'updated_at' => now(),
                        ]);

                        DB::table('ms_sale_gifts')->insert([
                            'sale_id' => $saleId,
                            'gift_id' => $part->id,
                            'qty' => 1,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        DB::table('ms_parts_inventory_history')->insert([
                            'part_id' => $part->id,
                            'type' => 'deduction',
                            'quantity' => 1,
                            'balance_after' => $newBalance,
                            'reference' => "Promotional Gift on Phone Sale #{$invoiceNumber}",
                            'user_id' => auth()->id(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            // Customer Khata Update if Udhari
            if ($udhariAmount > 0) {
                DB::table('ms_customers')->where('id', $customer->id)->increment('udhari_balance', $udhariAmount);
                $newBal = $customer->udhari_balance + $udhariAmount;
                DB::table('ms_customer_khata_transactions')->insert([
                    'company_id' => $companyId,
                    'customer_id' => $customer->id,
                    'type' => 'udhari_sale',
                    'sale_id' => $saleId,
                    'amount' => $udhariAmount,
                    'balance_after' => $newBal,
                    'remarks' => "Udhari on Phone Sale Invoice #{$invoiceNumber}",
                    'recorded_by' => auth()->id(),
                    'created_at' => now(),
                ]);
            }

            return [
                'success' => true,
                'sale_id' => $saleId,
                'invoice_number' => $invoiceNumber,
                'idempotent' => false,
            ];
        });
    }
}
