<?php

namespace App\Services\MobileShop\Purchase;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BulkPurchaseInwardService
{
    /**
     * Store Bulk Multi-Item Purchase — creates one PO with many device lines,
     * carries unpaid balance to supplier ledger, applies advance credit wallet.
     */
    public function storeBulkPurchase(int $companyId, Request $request): array
    {
        $request->validate([
            'supplier_id'           => 'nullable|exists:ms_suppliers,id',
            'new_supplier_name'     => 'required_without:supplier_id|nullable|string|max:150',
            'new_supplier_phone'    => 'nullable|string|max:20',
            'supplier_invoice_no'   => 'nullable|string|max:100',
            'order_date'            => 'required|date',
            'bill_type'             => 'required|in:gst,non_gst',
            'bill_total'            => 'required|numeric|min:1',
            'amount_paid'           => 'required|numeric|min:0',
            'use_advance_credit'    => 'nullable|numeric|min:0',
            'payment_mode'          => 'required|in:cash,bank_transfer,cheque,upi',
            'items'                 => 'required|array|min:1',
            'items.*.brand'         => 'required|string|max:100',
            'items.*.model'         => 'required|string|max:100',
            'items.*.qty'           => 'required|integer|min:1',
            'items.*.unit_cost'     => 'required|numeric|min:0',
            'items.*.selling_price' => 'required|numeric|min:1',
            'items.*.ram'           => 'nullable|string|max:50',
            'items.*.storage'       => 'nullable|string|max:50',
            'items.*.color'         => 'nullable|string|max:50',
            'items.*.imeis'         => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request, $companyId) {
            // Resolve supplier (existing or create new)
            if ($request->filled('supplier_id')) {
                $supplierId = (int) $request->supplier_id;
            } else {
                $supplierId = DB::table('ms_suppliers')->insertGetId([
                    'company_id' => $companyId,
                    'name'       => trim($request->new_supplier_name),
                    'phone'      => $request->new_supplier_phone,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $billTotal = round((float) $request->bill_total, 2);
            $amountPaid = min(round((float) $request->amount_paid, 2), $billTotal);
            $advanceUsed = 0.00;

            // Apply advance credit wallet if requested
            $wallet = DB::table('ms_supplier_credit_wallets')
                ->where('company_id', $companyId)
                ->where('supplier_id', $supplierId)
                ->lockForUpdate()
                ->first();

            if ($request->filled('use_advance_credit') && (float) $request->use_advance_credit > 0 && $wallet) {
                $advanceUsed = min((float) $request->use_advance_credit, (float) $wallet->credit_balance, $billTotal - $amountPaid);
                if ($advanceUsed > 0) {
                    $amountPaid += $advanceUsed;
                    $newCredit = (float) $wallet->credit_balance - $advanceUsed;
                    DB::table('ms_supplier_credit_wallets')->where('id', $wallet->id)->update([
                        'credit_balance' => $newCredit,
                        'updated_at'     => now(),
                    ]);
                    DB::table('ms_supplier_credit_transactions')->insert([
                        'supplier_id'   => $supplierId,
                        'txn_type'      => 'credit_used',
                        'amount'        => $advanceUsed,
                        'related_po_id' => null,
                        'balance_after' => $newCredit,
                        'remarks'       => 'Advance credit applied on bulk purchase invoice',
                        'txn_date'      => now(),
                    ]);
                }
            }

            $balanceDue = max(0.00, $billTotal - $amountPaid);
            $newStatus = ($balanceDue <= 0) ? 'paid' : (($amountPaid > 0) ? 'partially_paid' : 'received');

            // Create PO header
            $poNum = 'PO-BULK-' . date('Ymd') . '-' . rand(100, 999);
            $poId = DB::table('ms_purchase_orders')->insertGetId([
                'company_id'   => $companyId,
                'supplier_id'  => $supplierId,
                'po_number'    => $poNum,
                'order_date'   => $request->order_date,
                'tax_type'     => $request->bill_type === 'gst' ? 'intra_state' : 'none',
                'subtotal'     => $billTotal,
                'total_amount' => $billTotal,
                'amount_paid'  => $amountPaid,
                'balance_due'  => $balanceDue,
                'status'       => $newStatus,
                'created_by'   => auth()->id(),
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            // Insert line items + bulk devices
            $deviceCount = 0;
            foreach ($request->items as $item) {
                $qty = (int) $item['qty'];
                $unitCost = (float) $item['unit_cost'];
                $lineTotal = round($unitCost * $qty, 2);
                $variantParts = [];
                if (!empty($item['ram']) && !empty($item['storage'])) {
                    $variantParts[] = $item['ram'] . '/' . $item['storage'];
                }
                if (!empty($item['color'])) {
                    $variantParts[] = $item['color'];
                }

                DB::table('ms_purchase_order_items')->insert([
                    'purchase_order_id' => $poId,
                    'brand'             => $item['brand'],
                    'model'             => $item['model'],
                    'variant'           => implode(' ', $variantParts) ?: 'Standard',
                    'hsn_code'          => '85171300',
                    'qty'               => $qty,
                    'qty_received'      => $qty,
                    'unit_cost'         => $unitCost,
                    'tax_rate'          => $request->bill_type === 'gst' ? 18.00 : 0,
                    'line_total'        => $lineTotal,
                ]);

                // Parse optional IMEI list (one per line / comma separated)
                $imeis = [];
                if (!empty($item['imeis'])) {
                    $imeis = preg_split('/[\n,]+/', trim($item['imeis']));
                    $imeis = array_values(array_filter(array_map('trim', $imeis)));
                }

                for ($i = 0; $i < $qty; $i++) {
                    $imei = !empty($imeis[$i]) ? substr($imeis[$i], 0, 20) : ('86' . str_pad($poId % 1000, 3, '0', STR_PAD_LEFT) . rand(1000000000, 9999999999));
                    DB::table('ms_mobile_devices')->insert([
                        'company_id'        => $companyId,
                        'purchase_order_id' => $poId,
                        'type'              => 'new',
                        'brand'             => $item['brand'],
                        'model'             => $item['model'],
                        'color'             => $item['color'] ?? 'Standard',
                        'ram'               => $item['ram'] ?? null,
                        'storage'           => $item['storage'] ?? null,
                        'imei_1'            => $imei,
                        'purchase_cost'     => $unitCost,
                        'selling_price'     => (float) $item['selling_price'],
                        'status'            => 'in_stock',
                        'condition_grade'   => 'brand_new',
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ]);
                    $deviceCount++;
                }
            }

            // Record cash payment row if paid now
            if ($amountPaid > 0) {
                DB::table('ms_supplier_payments')->insert([
                    'company_id'        => $companyId,
                    'supplier_id'       => $supplierId,
                    'purchase_order_id' => $poId,
                    'amount'            => $amountPaid,
                    'payment_date'      => now()->toDateString(),
                    'mode'              => $request->payment_mode,
                    'reference_no'      => $request->supplier_invoice_no,
                    'remarks'           => "Payment on bulk purchase {$poNum}" . ($advanceUsed > 0 ? " (advance credit used: ₹" . number_format($advanceUsed, 2) . ")" : ""),
                    'recorded_by'       => auth()->id(),
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            }

            return [
                'poNum'       => $poNum,
                'deviceCount' => $deviceCount,
                'balanceDue'  => $balanceDue,
                'poId'        => $poId,
            ];
        });
    }
}
