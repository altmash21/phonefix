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

        $categoryRow = DB::table('ms_part_categories')
            ->where('company_id', $companyId)
            ->where(function ($q) use ($request) {
                $q->where('slug', $request->category)
                  ->orWhere('name', $request->category);
            })
            ->first();
        $isGift = $request->has('is_gift_eligible') ? 1 : ($categoryRow ? ($categoryRow->is_gift_eligible ? 1 : 0) : 0);

        $partId = DB::table('ms_parts_inventory')->insertGetId([
            'company_id'       => $companyId,
            'category'         => $request->category,
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
                    $categoryRow = DB::table('ms_part_categories')
                        ->where('company_id', $companyId)
                        ->where(function ($q) use ($categorySlug) {
                            $q->where('slug', $categorySlug)->orWhere('name', $categorySlug);
                        })
                        ->first();

                    $newPartId = DB::table('ms_parts_inventory')->insertGetId([
                        'company_id'       => $companyId,
                        'name'             => $partName,
                        'category'         => $categoryRow ? $categoryRow->slug : $categorySlug,
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

            $poId = DB::table('ms_purchase_orders')->insertGetId([
                'company_id'   => $companyId,
                'supplier_id'  => $supplierId,
                'po_number'    => $invNumber,
                'bill_type'    => $billType,
                'order_date'   => now()->toDateString(),
                'tax_type'     => 'intra_state',
                'subtotal'     => $totalCost,
                'cgst_amount'  => 0,
                'sgst_amount'  => 0,
                'igst_amount'  => 0,
                'total_amount' => $totalCost,
                'amount_paid'  => $totalCost,
                'balance_due'  => 0,
                'status'       => 'received',
                'created_by'   => auth()->id(),
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

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
                'existingCount' => $existingCount,
                'newCount'      => $newCount,
                'totalUnits'    => $totalUnits,
                'totalCost'     => $totalCost,
                'invoiceNumber' => $invNumber,
                'poId'          => $poId,
            ];
        });
    }
}
