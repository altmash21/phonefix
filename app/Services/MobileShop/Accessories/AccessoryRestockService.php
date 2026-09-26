<?php

namespace App\Services\MobileShop\Accessories;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccessoryRestockService
{
    /**
     * Store single part/accessory intake
     */
    public function storePart(int $companyId, Request $request): int
    {
        $request->validate([
            'name'             => 'required|string|max:150',
            'category'         => 'required|string|max:100',
            'brand'            => 'nullable|string|max:100',
            'compatible_model' => 'nullable|string|max:100',
            'display_type'     => 'nullable|string|max:50',
            'description'      => 'nullable|string|max:1000',
            'unit_cost'        => 'required|numeric|min:0',
            'selling_price'    => 'required|numeric|min:0',
            'stock_qty'        => 'required|numeric|min:0',
            'min_stock_alert'  => 'nullable|integer|min:0',
        ]);

        $categoryRow = AccessoryCategoryService::resolveCategory($companyId, $request->category, $request->name);
        $finalCategorySlug = $categoryRow ? $categoryRow->slug : AccessoryCategoryService::canonicalSlug($request->category, $request->name);
        $isGift = $request->has('is_gift_eligible') ? 1 : ($categoryRow ? ($categoryRow->is_gift_eligible ? 1 : 0) : 0);

        $partId = DB::table('ms_parts_inventory')->insertGetId([
            'company_id'       => $companyId,
            'category'         => $finalCategorySlug,
            'category_id'      => $categoryRow?->id,
            'is_gift_eligible' => $isGift,
            'brand'            => $request->brand ?: 'Universal',
            'compatible_model' => $request->compatible_model ?: 'Universal / Multi-Model',
            'display_type'     => $request->display_type ?: 'Normal',
            'description'      => $request->description,
            'hsn_code'         => $request->hsn_code ?? '85177090',
            'name'             => $request->name,
            'unit_cost'        => (float) $request->unit_cost,
            'selling_price'    => (float) $request->selling_price,
            'stock_qty'        => (int) $request->stock_qty,
            'min_stock_alert'  => $request->min_stock_alert !== null ? (int) $request->min_stock_alert : 3,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        DB::table('ms_parts_inventory_history')->insert([
            'part_id'       => $partId,
            'type'          => 'addition',
            'quantity'      => (int) $request->stock_qty,
            'balance_after' => (int) $request->stock_qty,
            'reference'     => 'Initial Stock Intake',
            'user_id'       => auth()->id(),
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return $partId;
    }

    /**
     * Bulk restock batch intake and Purchase Order creation
     */
    public function bulkRestock(int $companyId, Request $request): array
    {
        $request->validate([
            'items'                 => 'required|array|min:1',
            'items.*.name'          => 'required|string|max:150',
            'items.*.qty'           => 'required|integer|min:1',
            'items.*.unit_cost'     => 'required|numeric|min:0',
            'items.*.selling_price' => 'nullable|numeric|min:0',
            'amount_paid'           => 'nullable|numeric|min:0',
            'payment_mode'          => 'nullable|string|in:cash,bank_transfer,cheque,upi',
        ]);

        $supplierRef = trim(($request->supplier_name ? $request->supplier_name . ' ' : '') . ($request->invoice_no ? '#' . $request->invoice_no : 'Shipment Intake'));
        if (empty($supplierRef)) {
            $supplierRef = 'Batch Restock / Shipment Intake';
        }

        return DB::transaction(function () use ($request, $companyId, $supplierRef) {
            $existingCount = 0;
            $newCount = 0;
            $totalUnits = 0;
            $totalCost = 0.0;

            foreach ($request->items as $item) {
                $qty = (int) ($item['qty'] ?? 1);
                $unitCost = (float) ($item['unit_cost'] ?? 0);
                $sellingPrice = (float) ($item['selling_price'] ?? ($unitCost > 0 ? round($unitCost * 1.5, 2) : 0));
                $partName = trim($item['name']);
                $categorySlug = $item['category'] ?? 'tempered_glass';
                $brand = $item['brand'] ?? 'Universal';
                $model = $item['compatible_model'] ?? 'Universal';
                $isGift = !empty($item['is_gift_eligible']) ? 1 : 0;
                $partId = !empty($item['part_id']) ? (int) $item['part_id'] : null;

                $totalUnits += $qty;
                $totalCost += ($qty * $unitCost);

                $part = null;
                if ($partId) {
                    $part = DB::table('ms_parts_inventory')
                        ->where('company_id', $companyId)
                        ->where('id', $partId)
                        ->lockForUpdate()
                        ->first();
                } else {
                    $part = DB::table('ms_parts_inventory')
                        ->where('company_id', $companyId)
                        ->where('name', $partName)
                        ->lockForUpdate()
                        ->first();
                }

                if ($part) {
                    $newStock = (int) $part->stock_qty + $qty;
                    $updateData = [
                        'stock_qty'  => $newStock,
                        'updated_at' => now(),
                    ];
                    if ($unitCost > 0) {
                        $updateData['unit_cost'] = $unitCost;
                    }
                    if ($sellingPrice > 0) {
                        $updateData['selling_price'] = $sellingPrice;
                    }
                    if ($isGift) {
                        $updateData['is_gift_eligible'] = 1;
                    }
                    if (isset($item['min_stock_alert']) && $item['min_stock_alert'] !== '') {
                        $updateData['min_stock_alert'] = (int) $item['min_stock_alert'];
                    }
                    if (!empty($item['description'])) {
                        $updateData['description'] = $item['description'];
                    }
                    if (!empty($brand) && $brand !== 'Universal') {
                        $updateData['brand'] = $brand;
                    }
                    if (!empty($model) && $model !== 'Universal') {
                        $updateData['compatible_model'] = $model;
                    }

                    DB::table('ms_parts_inventory')->where('id', $part->id)->update($updateData);

                    DB::table('ms_parts_inventory_history')->insert([
                        'part_id'       => $part->id,
                        'type'          => 'addition',
                        'quantity'      => $qty,
                        'balance_after' => $newStock,
                        'reference'     => "Bulk Restock: {$supplierRef}",
                        'user_id'       => auth()->id(),
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);

                    $existingCount++;
                } else {
                    $categoryRow = AccessoryCategoryService::resolveCategory($companyId, $categorySlug, $partName);
                    $finalCategorySlug = $categoryRow ? $categoryRow->slug : AccessoryCategoryService::canonicalSlug($categorySlug, $partName);

                    $newPartId = DB::table('ms_parts_inventory')->insertGetId([
                        'company_id'       => $companyId,
                        'name'             => $partName,
                        'category'         => $finalCategorySlug,
                        'category_id'      => $categoryRow?->id,
                        'brand'            => $brand,
                        'compatible_model' => $model,
                        'display_type'     => $item['display_type'] ?? 'Normal',
                        'description'      => $item['description'] ?? null,
                        'hsn_code'         => $item['hsn_code'] ?? '85177090',
                        'unit_cost'        => $unitCost,
                        'selling_price'    => $sellingPrice,
                        'stock_qty'        => $qty,
                        'min_stock_alert'  => isset($item['min_stock_alert']) && $item['min_stock_alert'] !== '' ? (int) $item['min_stock_alert'] : 3,
                        'is_gift_eligible' => $isGift || ($categoryRow && $categoryRow->is_gift_eligible ? 1 : 0),
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);

                    DB::table('ms_parts_inventory_history')->insert([
                        'part_id'       => $newPartId,
                        'type'          => 'addition',
                        'quantity'      => $qty,
                        'balance_after' => $qty,
                        'reference'     => "Initial Bulk Intake: {$supplierRef}",
                        'user_id'       => auth()->id(),
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);

                    $newCount++;
                }
            }

            // Create formal Purchase Order for this batch
            $supplierName = trim($request->supplier_name ?: 'National Screen & Spare Parts Hub');
            $supplier = DB::table('ms_suppliers')->where('company_id', $companyId)->where('name', $supplierName)->first();
            if (!$supplier) {
                $supplierId = DB::table('ms_suppliers')->insertGetId([
                    'company_id' => $companyId,
                    'name'       => $supplierName,
                    'phone'      => '9820011223',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $supplierId = $supplier->id;
            }

            $invNumber = trim($request->invoice_no ?: '');
            if (empty($invNumber)) {
                $invNumber = 'INV-' . date('Ymd') . '-' . str_pad(DB::table('ms_purchase_orders')->where('company_id', $companyId)->count() + 1, 4, '0', STR_PAD_LEFT);
            }
            $basePoNum = $invNumber;
            $counter = 1;
            while (DB::table('ms_purchase_orders')->where('company_id', $companyId)->where('po_number', $invNumber)->exists()) {
                $invNumber = $basePoNum . '-' . $counter++;
            }

            $billType = $request->bill_type === 'non_gst' ? 'non_gst' : 'gst';
            $orderDate = $request->filled('order_date') ? $request->order_date : now()->toDateString();

            // Calculate payments & supplier settlement
            $rawAmountPaid = $request->has('amount_paid') && $request->amount_paid !== null && $request->amount_paid !== ''
                ? max(0.0, (float) $request->amount_paid)
                : $totalCost;

            $paymentMode = in_array($request->payment_mode, ['cash', 'bank_transfer', 'cheque', 'upi'])
                ? $request->payment_mode
                : 'cash';

            // Amount applied to THIS current purchase order (capped at totalCost)
            $poAmountPaid = min($rawAmountPaid, $totalCost);
            $poBalanceDue = max(0.0, $totalCost - $poAmountPaid);
            $poStatus = ($poBalanceDue <= 0.001) ? 'paid' : (($poAmountPaid > 0) ? 'partially_paid' : 'received');

            $poId = DB::table('ms_purchase_orders')->insertGetId([
                'company_id'   => $companyId,
                'supplier_id'  => $supplierId,
                'po_number'    => $invNumber,
                'bill_type'    => $billType,
                'order_date'   => $orderDate,
                'tax_type'     => $billType === 'gst' ? 'intra_state' : 'none',
                'subtotal'     => $totalCost,
                'cgst_amount'  => 0,
                'sgst_amount'  => 0,
                'igst_amount'  => 0,
                'total_amount' => $totalCost,
                'amount_paid'  => $poAmountPaid,
                'balance_due'  => $poBalanceDue,
                'status'       => $poStatus,
                'created_by'   => auth()->id(),
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            // 1. Record payment for this PO if paid
            if ($poAmountPaid > 0) {
                DB::table('ms_supplier_payments')->insert([
                    'company_id'        => $companyId,
                    'supplier_id'       => $supplierId,
                    'purchase_order_id' => $poId,
                    'amount'            => $poAmountPaid,
                    'payment_date'      => $orderDate,
                    'mode'              => $paymentMode,
                    'reference_no'      => $request->invoice_no ?: $invNumber,
                    'remarks'           => "Payment on bulk accessory intake {$invNumber}",
                    'recorded_by'       => auth()->id(),
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            }

            // 2. If owner submitted more than this PO cost, use excess to CLEAR OUT older supplier balance!
            $excessAmount = round($rawAmountPaid - $totalCost, 2);
            $clearedOldBalance = 0.0;

            if ($excessAmount > 0) {
                // Find older unpaid purchase orders for this supplier, oldest first
                $unpaidPOs = DB::table('ms_purchase_orders')
                    ->where('company_id', $companyId)
                    ->where('supplier_id', $supplierId)
                    ->where('id', '!=', $poId)
                    ->where('balance_due', '>', 0)
                    ->orderBy('order_date', 'asc')
                    ->orderBy('id', 'asc')
                    ->lockForUpdate()
                    ->get();

                $remainingExcess = $excessAmount;
                foreach ($unpaidPOs as $oldPo) {
                    if ($remainingExcess <= 0.001) break;

                    $applyToOld = min($remainingExcess, (float) $oldPo->balance_due);
                    $newOldPaid = (float) $oldPo->amount_paid + $applyToOld;
                    $newOldDue = max(0.0, (float) $oldPo->balance_due - $applyToOld);
                    $newOldStatus = ($newOldDue <= 0.001) ? 'paid' : 'partially_paid';

                    DB::table('ms_purchase_orders')->where('id', $oldPo->id)->update([
                        'amount_paid' => $newOldPaid,
                        'balance_due' => $newOldDue,
                        'status'      => $newOldStatus,
                        'updated_at'  => now(),
                    ]);

                    DB::table('ms_supplier_payments')->insert([
                        'company_id'        => $companyId,
                        'supplier_id'       => $supplierId,
                        'purchase_order_id' => $oldPo->id,
                        'amount'            => $applyToOld,
                        'payment_date'      => $orderDate,
                        'mode'              => $paymentMode,
                        'reference_no'      => $request->invoice_no ?: $invNumber,
                        'remarks'           => "Balance clearance from intake {$invNumber} applied to PO #{$oldPo->po_number}",
                        'recorded_by'       => auth()->id(),
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ]);

                    $remainingExcess -= $applyToOld;
                    $clearedOldBalance += $applyToOld;
                }

                // If still excess remaining after clearing ALL old POs, credit to supplier prepaid wallet
                if ($remainingExcess > 0.001) {
                    $wallet = DB::table('ms_supplier_credit_wallets')
                        ->where('company_id', $companyId)
                        ->where('supplier_id', $supplierId)
                        ->lockForUpdate()
                        ->first();

                    if (!$wallet) {
                        DB::table('ms_supplier_credit_wallets')->insert([
                            'company_id'     => $companyId,
                            'supplier_id'    => $supplierId,
                            'credit_balance' => $remainingExcess,
                            'created_at'     => now(),
                            'updated_at'     => now(),
                        ]);
                        $newBal = $remainingExcess;
                    } else {
                        $newBal = (float) $wallet->credit_balance + $remainingExcess;
                        DB::table('ms_supplier_credit_wallets')->where('id', $wallet->id)->update([
                            'credit_balance' => $newBal,
                            'updated_at'     => now(),
                        ]);
                    }

                    DB::table('ms_supplier_credit_transactions')->insert([
                        'supplier_id'   => $supplierId,
                        'txn_type'      => 'credit_added',
                        'amount'        => $remainingExcess,
                        'related_po_id' => $poId,
                        'balance_after' => $newBal,
                        'remarks'       => "Prepayment / excess clearance from intake {$invNumber}",
                        'txn_date'      => now(),
                    ]);
                }
            }

            foreach ($request->items as $item) {
                $q = (int) ($item['qty'] ?? 1);
                $c = (float) ($item['unit_cost'] ?? 0);
                DB::table('ms_purchase_order_items')->insert([
                    'purchase_order_id' => $poId,
                    'brand'             => $item['brand'] ?? 'Universal',
                    'model'             => trim($item['name']),
                    'variant'           => $item['compatible_model'] ?? ($item['category'] ?? 'Parts'),
                    'hsn_code'          => $item['hsn_code'] ?? '85177090',
                    'qty'               => $q,
                    'qty_received'      => $q,
                    'unit_cost'         => $c,
                    'tax_rate'          => 18.00,
                    'line_total'        => $q * $c,
                ]);
            }

            return [
                'existingCount'     => $existingCount,
                'newCount'          => $newCount,
                'totalUnits'        => $totalUnits,
                'totalCost'         => $totalCost,
                'amountPaid'        => $rawAmountPaid,
                'balanceDue'        => $poBalanceDue,
                'clearedOldBalance' => $clearedOldBalance,
                'invoiceNumber'     => $invNumber,
                'poId'              => $poId,
            ];
        });
    }
}
