<?php

namespace App\Services\MobileShop\Stock;

use App\Services\MobileShop\Common\MobileShopInvoiceHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SecondHandStockService
{
    /**
     * Store Second Hand Buyback (Intake)
     */
    public function intake(Request $request, int $companyId, ?string $photoPath): array
    {
        // Check uniqueness within company
        $exists = DB::table('ms_mobile_devices')
            ->where('company_id', $companyId)
            ->where('imei_1', $request->imei_1)
            ->exists();
        if ($exists) {
            return [
                'success' => false,
                'error' => "A mobile device with IMEI {$request->imei_1} is already registered in inventory!",
            ];
        }

        $suppName = 'Walk-in Buyback - ' . $request->customer_buyback_name;
        $supp = DB::table('ms_suppliers')->where('company_id', $companyId)->where('name', $suppName)->first();
        if (!$supp) {
            $suppId = DB::table('ms_suppliers')->insertGetId([
                'company_id' => $companyId,
                'name'       => $suppName,
                'phone'      => $request->customer_buyback_phone,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $suppId = $supp->id;
        }

        $buybackCost = (float) $request->purchase_cost;
        $bbNum = 'BUYBACK-' . date('Ymd') . '-' . rand(100, 999);

        $poId = DB::table('ms_purchase_orders')->insertGetId([
            'company_id'   => $companyId,
            'supplier_id'  => $suppId,
            'po_number'    => $bbNum,
            'order_date'   => now()->toDateString(),
            'tax_type'     => 'intra_state',
            'subtotal'     => $buybackCost,
            'total_amount' => $buybackCost,
            'amount_paid'  => $buybackCost,
            'balance_due'  => 0,
            'status'       => 'received',
            'created_by'   => auth()->id(),
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        DB::table('ms_purchase_order_items')->insert([
            'purchase_order_id' => $poId,
            'brand'             => $request->brand,
            'model'             => $request->model,
            'variant'           => "Pre-Owned [Grade: " . str_replace('_', ' ', $request->condition_grade ?? 'A') . "] IMEI: {$request->imei_1}",
            'hsn_code'          => '85171300',
            'qty'               => 1,
            'qty_received'      => 1,
            'unit_cost'         => $buybackCost,
            'tax_rate'          => 0,
            'line_total'        => $buybackCost,
        ]);

        DB::table('ms_mobile_devices')->insert([
            'company_id' => $companyId,
            'purchase_order_id' => $poId,
            'type' => 'second_hand',
            'brand' => $request->brand,
            'model' => $request->model,
            'color' => $request->color,
            'ram' => $request->ram,
            'storage' => $request->storage,
            'imei_1' => $request->imei_1,
            'imei_2' => $request->imei_2,
            'purchase_cost' => $buybackCost,
            'selling_price' => (float) $request->selling_price,
            'photo_path' => $photoPath,
            'box_photo_path' => $photoPath,
            'condition_grade' => $request->condition_grade ?? 'like_new_A_plus',
            'battery_health' => $request->battery_health,
            'customer_buyback_name' => $request->customer_buyback_name,
            'customer_buyback_phone' => $request->customer_buyback_phone,
            'customer_buyback_id_proof' => $request->customer_buyback_id_proof,
            'checklist_notes' => $request->checklist_notes,
            'status' => 'in_stock',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'success' => true,
            'po_number' => $bbNum,
        ];
    }

    /**
     * Sell Second-Hand Mobile at Pre-Owned POS Counter
     */
    public function sell(Request $request, int $companyId): array
    {
        // Idempotency check
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
                ->where('type', 'second_hand')
                ->lockForUpdate()
                ->first();

            if (!$device) {
                return [
                    'success' => false,
                    'error' => 'Selected second-hand device is no longer available!',
                ];
            }

            $salePrice = (float) $request->sale_price;
            $amountPaid = (float) $request->amount_paid;
            $reqTaxRate = (float) ($request->tax_rate ?? 18.00);
            $isGst = $request->boolean('is_gst') || ($request->bill_type === 'gst');
            $billType = $isGst ? 'gst' : 'non_gst';

            $gst = MobileShopInvoiceHelper::calculateGst($salePrice, $reqTaxRate, $billType, $storeState, $customer->state_code ?? null);
            $taxRate = $gst['taxRate'];
            $cgst = $gst['cgst'];
            $sgst = $gst['sgst'];
            $igst = $gst['igst'];
            $isStateMatch = $gst['isStateMatch'];

            $udhariAmount = max(0.00, $salePrice - $amountPaid);

            // Atomic Sequential Invoice Number for Second-Hand
            $invoiceNumber = MobileShopInvoiceHelper::getNextInvoiceNumber($companyId, 'SH');

            $saleId = DB::table('ms_mobile_sales')->insertGetId([
                'company_id' => $companyId,
                'idempotency_key' => $request->idempotency_key ?? Str::uuid()->toString(),
                'customer_id' => $customer->id,
                'invoice_number' => $invoiceNumber,
                'bill_type' => $billType,
                'device_id' => $device->id,
                'sale_price' => $salePrice,
                'tax_rate' => $taxRate,
                'tax_type' => $isStateMatch ? 'intra_state' : 'inter_state',
                'cgst_amount' => $cgst,
                'sgst_amount' => $sgst,
                'igst_amount' => $igst,
                'total_amount' => $salePrice,
                'amount_paid' => $amountPaid,
                'udhari_amount' => $udhariAmount,
                'payment_mode' => $request->payment_mode,
                'sold_by' => auth()->id(),
                'status' => 'completed',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Update Device Status
            DB::table('ms_mobile_devices')->where('id', $device->id)->update([
                'status' => 'sold',
                'selling_price' => $salePrice,
                'updated_at' => now(),
            ]);

            // Update Customer Khata if Udhari
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
                    'remarks' => "Udhari on Pre-Owned Phone Sale #{$invoiceNumber}",
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
