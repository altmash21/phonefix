<?php

namespace App\Http\Controllers\MobileShop;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class AccessoriesController extends BaseMobileShopController
{
    /**
     * Dedicated Full Page: Accessories & Spare Parts Purchase / Bulk Restock Intake
     */
    public function accessoriesPurchase(Request $request)
    {
        abort_unless(auth()->check() && (
            auth()->user()->can('create-mobileshop-accessories') || 
            auth()->user()->can('read-mobileshop-accessories') || 
            auth()->user()->can('create-purchase-accessories') || 
            auth()->user()->can('create-purchase-covers') || 
            auth()->user()->hasRole('admin') || 
            auth()->user()->hasRole('store-admin') || 
            auth()->user()->hasRole('accessories-staff')
        ), 403, 'Unauthorized access to accessories purchase.');

        $companyId = $this->getCompanyId();
        $niche     = $this->getUserNiche();

        $suppliers = DB::table('ms_suppliers')
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        $categories = DB::table('ms_part_categories')
            ->where('company_id', $companyId)
            ->orderBy('name', 'asc')
            ->get();

        $parts = DB::table('ms_parts_inventory')
            ->where('company_id', $companyId)
            ->orderBy('name', 'asc')
            ->get();

        $knownBrands = DB::table('ms_mobile_devices')
            ->where('company_id', $companyId)
            ->whereNotNull('brand')
            ->where('brand', '!=', '')
            ->distinct()
            ->pluck('brand')
            ->merge(
                DB::table('ms_parts_inventory')
                    ->where('company_id', $companyId)
                    ->whereNotNull('brand')
                    ->where('brand', '!=', '')
                    ->where('brand', '!=', 'Universal')
                    ->distinct()
                    ->pluck('brand')
            )
            ->unique()
            ->sort()
            ->values();

        $knownModels = DB::table('ms_mobile_devices')
            ->where('company_id', $companyId)
            ->whereNotNull('model')
            ->where('model', '!=', '')
            ->distinct()
            ->pluck('model')
            ->merge(
                DB::table('ms_parts_inventory')
                    ->where('company_id', $companyId)
                    ->whereNotNull('compatible_model')
                    ->where('compatible_model', '!=', '')
                    ->where('compatible_model', '!=', 'Universal')
                    ->distinct()
                    ->pluck('compatible_model')
            )
            ->unique()
            ->sort()
            ->values();

        return view('mobileshop.accessories_purchase', compact(
            'suppliers', 'categories', 'parts', 'knownBrands', 'knownModels', 'niche'
        ));
    }

    /**
     * Category List (JSON)
     */
    public function getCategories()
    {
        $companyId = $this->getCompanyId();
        $categories = DB::table('ms_part_categories')->where('company_id', $companyId)->orderBy('name', 'asc')->get();
        return response()->json(['success' => true, 'categories' => $categories]);
    }

    /**
     * Store Custom Part Category
     */
    public function storeCategory(Request $request)
    {
        abort_unless(auth()->check() && (auth()->user()->can('create-mobileshop-accessories') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin') || auth()->user()->hasRole('accessories-staff')), 403, 'Unauthorized action.');

        $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $companyId = $this->getCompanyId();
        $slug = Str::slug($request->name, '_');

        $exists = DB::table('ms_part_categories')->where('company_id', $companyId)->where('name', $request->name)->exists();
        if ($exists) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Category already exists'], 422);
            }
            return redirect()->back()->with('error', 'Category already exists!');
        }

        $catId = DB::table('ms_part_categories')->insertGetId([
            'company_id' => $companyId,
            'name' => $request->name,
            'slug' => $slug,
            'is_gift_eligible' => $request->has('is_gift_eligible') ? 1 : 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Category added!', 'id' => $catId]);
        }
        return redirect()->route('mobileshop.stock')->with('success', "Category '{$request->name}' added successfully!");
    }

    /**
     * Delete Custom Part Category (Gated by Owner OTP)
     */
    public function deleteCategory(Request $request, $id)
    {
        abort_unless(auth()->check() && (auth()->user()->can('create-mobileshop-accessories') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin')), 403, 'Unauthorized action.');

        $companyId = $this->getCompanyId();

        // OTP Security Gate: Only owner can delete category without OTP
        $itemRef = "category:{$id}";
        if (!$this->isOwner()) {
            $otpCode = $request->input('otp_code');
            if (!$this->verifyOtp($companyId, 'delete_category', $itemRef, $otpCode)) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'otp_required' => true,
                        'action' => 'delete_category',
                        'item_reference' => $itemRef,
                        'message' => 'Store Owner OTP authorization is required to delete product categories.',
                    ], 403);
                }
                return redirect()->back()->with('error', 'Store Owner OTP authorization is required to delete product categories.');
            }
        }

        DB::table('ms_part_categories')->where('company_id', $companyId)->where('id', $id)->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Category deleted successfully!']);
        }
        return redirect()->route('mobileshop.stock')->with('success', 'Category deleted successfully!');
    }

    /**
     * Store / Restock Part or Accessory
     */
    public function storePart(Request $request)
    {
        abort_unless(auth()->check() && (auth()->user()->can('create-mobileshop-accessories') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin') || auth()->user()->hasRole('accessories-staff')), 403, 'Unauthorized action.');

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

        $companyId = $this->getCompanyId();

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

        return $this->safeRedirect($request, 'mobileshop.purchase', [], 'success', 'Part/Accessory successfully added to inventory!');
    }

    /**
     * Bulk Restock & Batch Inflow (Manual or AI OCR Extracted)
     */
    public function bulkRestock(Request $request)
    {
        abort_unless(auth()->check() && (auth()->user()->can('create-mobileshop-accessories') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin') || auth()->user()->hasRole('accessories-staff')), 403, 'Unauthorized action.');

        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:150',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.selling_price' => 'nullable|numeric|min:0',
        ]);

        $companyId = $this->getCompanyId();
        $supplierRef = trim(($request->supplier_name ? $request->supplier_name . ' ' : '') . ($request->invoice_no ? '#' . $request->invoice_no : 'Shipment Intake'));
        if (empty($supplierRef)) {
            $supplierRef = 'Batch Restock / Shipment Intake';
        }

        $summary = DB::transaction(function () use ($request, $companyId, $supplierRef) {
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

                // Check if part exists by ID or by exact Name match
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
                    // Update existing part
                    $newStock = (int) $part->stock_qty + $qty;
                    $updateData = [
                        'stock_qty' => $newStock,
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

                    // Record Ledger
                    DB::table('ms_parts_inventory_history')->insert([
                        'part_id' => $part->id,
                        'type' => 'addition',
                        'quantity' => $qty,
                        'balance_after' => $newStock,
                        'reference' => "Bulk Restock: {$supplierRef}",
                        'user_id' => auth()->id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $existingCount++;
                } else {
                    // Insert new part
                    $categoryRow = DB::table('ms_part_categories')
                        ->where('company_id', $companyId)
                        ->where(function ($q) use ($categorySlug) {
                            $q->where('slug', $categorySlug)->orWhere('name', $categorySlug);
                        })
                        ->first();

                    $newPartId = DB::table('ms_parts_inventory')->insertGetId([
                        'company_id' => $companyId,
                        'name' => $partName,
                        'category' => $categoryRow ? $categoryRow->slug : $categorySlug,
                        'category_id' => $categoryRow?->id,
                        'brand' => $brand,
                        'compatible_model' => $model,
                        'display_type' => $item['display_type'] ?? 'Normal',
                        'description' => $item['description'] ?? null,
                        'hsn_code' => $item['hsn_code'] ?? '85177090',
                        'unit_cost' => $unitCost,
                        'selling_price' => $sellingPrice,
                        'stock_qty' => $qty,
                        'min_stock_alert' => isset($item['min_stock_alert']) && $item['min_stock_alert'] !== '' ? (int) $item['min_stock_alert'] : 3,
                        'is_gift_eligible' => $isGift || ($categoryRow && $categoryRow->is_gift_eligible ? 1 : 0),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Record Ledger
                    DB::table('ms_parts_inventory_history')->insert([
                        'part_id' => $newPartId,
                        'type' => 'addition',
                        'quantity' => $qty,
                        'balance_after' => $qty,
                        'reference' => "Initial Bulk Intake: {$supplierRef}",
                        'user_id' => auth()->id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $newCount++;
                }
            }

            // Create formal Purchase Order for this batch so it appears in the Purchase Invoice list
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

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Successfully restocked {$summary['totalUnits']} units across {$summary['existingCount']} existing and {$summary['newCount']} new catalog items (Total Value: ₹" . number_format($summary['totalCost'], 2) . ")!",
                'summary' => $summary,
            ]);
        }

        return $this->safeRedirect($request, 'mobileshop.purchase', [], 'success', "Bulk restock successful! Added {$summary['totalUnits']} units (Value: ₹" . number_format($summary['totalCost'], 2) . ") into inventory.");
    }

    /**
     * Sell Accessories at Retail Counter POS (Multi-Item Cart Support & Atomic Locking)
     */
    public function sellAccessory(Request $request)
    {
        abort_unless(auth()->check() && (auth()->user()->can('sell-mobileshop-accessories') || auth()->user()->can('create-sale-accessories') || auth()->user()->can('create-mobileshop-accessories') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin') || auth()->user()->hasRole('accessories-staff') || auth()->user()->hasRole('accessories-manager')), 403, 'Unauthorized action.');

        $request->validate([
            'customer_phone' => 'required',
            'customer_name' => 'required',
            'items' => 'required|array|min:1',
            'items.*.part_id' => 'required|exists:ms_parts_inventory,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'amount_paid' => 'required|numeric|min:0',
            'payment_mode' => 'required|in:cash,upi,card,credit_udhari,split',
        ]);

        $companyId = $this->getCompanyId();

        // Idempotency check
        if ($request->filled('idempotency_key')) {
            $existing = DB::table('ms_accessory_sales')
                ->where('company_id', $companyId)
                ->where('idempotency_key', $request->idempotency_key)
                ->first();
            if ($existing) {
                return redirect()->route('mobileshop.accessories.invoice', ['id' => $existing->id])
                    ->with('success', "Accessory sale invoice #{$existing->invoice_number} already processed.");
            }
        }

        return DB::transaction(function () use ($request, $companyId) {
            $storeState = $this->getStoreStateCode();
            // Customer Find / Create
            $customer = DB::table('ms_customers')->where('company_id', $companyId)->where('phone', $request->customer_phone)->first();
            if (!$customer) {
                $customerId = DB::table('ms_customers')->insertGetId([
                    'company_id' => $companyId,
                    'name' => $request->customer_name,
                    'phone' => $request->customer_phone,
                    'gstin' => $request->customer_gstin,
                    'state_code' => $request->customer_state_code ?? $storeState,
                    'address' => $request->customer_address,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $customer = DB::table('ms_customers')->where('id', $customerId)->first();
            }

            // Atomic Sequential Invoice Number
            $invoiceNumber = $this->getNextInvoiceNumber($companyId, 'ACC');

            $subtotal = 0.00;
            $lineItemsData = [];

            // Lock and Validate Every Item in Cart
            foreach ($request->items as $cartItem) {
                $partId = $cartItem['part_id'];
                $qty = (int) $cartItem['quantity'];
                $price = (float) $cartItem['unit_price'];

                $part = DB::table('ms_parts_inventory')
                    ->where('company_id', $companyId)
                    ->where('id', $partId)
                    ->lockForUpdate()
                    ->first();

                if (!$part) {
                    abort(422, "Accessory item ID #{$partId} not found in inventory!");
                }

                if ($part->stock_qty < $qty) {
                    abort(422, "Insufficient stock for '{$part->name}'! Available: {$part->stock_qty}, Requested: {$qty}");
                }

                // Decrement stock
                $newBalance = $part->stock_qty - $qty;
                DB::table('ms_parts_inventory')
                    ->where('id', $part->id)
                    ->update([
                        'stock_qty' => $newBalance,
                        'updated_at' => now(),
                    ]);

                // Log deduction in stock history ledger
                DB::table('ms_parts_inventory_history')->insert([
                    'part_id' => $part->id,
                    'type' => 'deduction',
                    'quantity' => $qty,
                    'balance_after' => $newBalance,
                    'reference' => "Retail Counter Sale #{$invoiceNumber}",
                    'user_id' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $lineTotal = round($price * $qty, 2);
                $subtotal += $lineTotal;

                $lineItemsData[] = [
                    'company_id' => $companyId,
                    'part_id' => $part->id,
                    'part_name' => $part->name,
                    'hsn_code' => $part->hsn_code ?? '85177090',
                    'quantity' => $qty,
                    'unit_cost' => $part->unit_cost,
                    'unit_price' => $price,
                    'tax_rate' => 18.00,
                    'tax_amount' => round($lineTotal - ($lineTotal / 1.18), 2),
                    'line_total' => $lineTotal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $isGst = $request->boolean('is_gst') || ($request->bill_type === 'gst');
            $billType = $isGst ? 'gst' : 'non_gst';

            // Tax Split Calculation
            if ($billType === 'non_gst') {
                $totalTax = 0.00;
                $cgst = 0.00;
                $sgst = 0.00;
                $igst = 0.00;
            } else {
                $taxable = round($subtotal / 1.18, 2);
                $totalTax = round($subtotal - $taxable, 2);
                $isStateMatch = empty($customer->state_code) || ($customer->state_code === $storeState);
                $halfTax = round($totalTax / 2, 2);

                $cgst = $isStateMatch ? $halfTax : 0.00;
                $sgst = $isStateMatch ? ($totalTax - $halfTax) : 0.00;
                $igst = !$isStateMatch ? $totalTax : 0.00;
            }

            $amountPaid = (float) $request->amount_paid;
            $udhariAmount = max(0.00, $subtotal - $amountPaid);

            // Insert Header Record
            $saleId = DB::table('ms_accessory_sales')->insertGetId([
                'company_id' => $companyId,
                'idempotency_key' => $request->idempotency_key ?? Str::uuid()->toString(),
                'invoice_number' => $invoiceNumber,
                'bill_type' => $billType,
                'customer_id' => $customer->id,
                'subtotal' => $subtotal,
                'tax_rate' => $billType === 'non_gst' ? 0.00 : 18.00,
                'tax_amount' => $totalTax,
                'cgst_amount' => $cgst,
                'sgst_amount' => $sgst,
                'igst_amount' => $igst,
                'total_amount' => $subtotal,
                'amount_paid' => $amountPaid,
                'udhari_amount' => $udhariAmount,
                'payment_mode' => $request->payment_mode,
                'sold_by' => auth()->id(),
                'status' => 'completed',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Insert Line Items
            foreach ($lineItemsData as &$line) {
                $line['accessory_sale_id'] = $saleId;
            }
            DB::table('ms_accessory_sale_items')->insert($lineItemsData);

            // Update Customer Khata if Udhari
            if ($udhariAmount > 0) {
                DB::table('ms_customers')->where('id', $customer->id)->increment('udhari_balance', $udhariAmount);
                $newBal = $customer->udhari_balance + $udhariAmount;
                DB::table('ms_customer_khata_transactions')->insert([
                    'company_id' => $companyId,
                    'customer_id' => $customer->id,
                    'type' => 'udhari_sale',
                    'sale_id' => null,
                    'amount' => $udhariAmount,
                    'balance_after' => $newBal,
                    'remarks' => "Udhari on Accessory Sale #{$invoiceNumber}",
                    'recorded_by' => auth()->id(),
                    'created_at' => now(),
                ]);
            }

            return redirect()->route('mobileshop.accessories.invoice', ['id' => $saleId])
                ->with('success', "Accessory sale #{$invoiceNumber} successfully recorded!");
        });
    }

    /**
     * Accessory Invoice & Receipt View
     */
    public function accessoryInvoice($id)
    {
        abort_unless(auth()->check() && (auth()->user()->can('read-mobileshop-accessories') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin') || auth()->user()->hasRole('accessories-staff')), 403, 'Unauthorized access to invoice.');

        $data = $this->resolveAccessorySaleDetails($this->getCompanyId(), (int) $id);
        return view('mobileshop.accessory_invoice', $data);
    }

    /**
     * Download Accessory Sales Invoice as Direct PDF
     */
    public function accessoryInvoicePdf($id)
    {
        abort_unless(auth()->check() && (
            auth()->user()->can('read-mobileshop-accessories') || 
            auth()->user()->hasRole('admin') || 
            auth()->user()->hasRole('store-admin') || 
            auth()->user()->hasRole('accessories-staff')
        ), 403, 'Unauthorized access to invoice PDF.');

        $data = $this->resolveAccessorySaleDetails($this->getCompanyId(), (int) $id);
        $pdf = Pdf::loadView('mobileshop.pdf.accessory_invoice', $data);
        $pdf->setPaper('a4', 'portrait');
        return $pdf->download("Invoice-{$data['sale']->invoice_number}.pdf");
    }

    /**
     * Void / Cancel an Accessory Sale (Sales Return)
     */
    public function voidAccessorySale(Request $request, $id)
    {
        abort_unless(auth()->check() && (
            auth()->user()->can('void-mobileshop-sales') ||
            auth()->user()->can('read-mobileshop-sales') ||
            auth()->user()->can('sell-mobileshop-accessories') ||
            auth()->user()->can('create-sale-accessories') ||
            auth()->user()->hasRole('admin') ||
            auth()->user()->hasRole('store-admin')
        ), 403, 'Unauthorized to process sales return.');

        $request->validate([
            'void_reason' => 'nullable|string',
            'return_reason_code' => 'nullable|string',
        ]);

        $companyId = $this->getCompanyId();
        $shouldRestock = $request->has('should_restock') ? (bool) $request->should_restock : true;
        $reasonLabel   = $request->input('reason_label') ?: ($request->input('return_reason_code') ?: 'Customer Return');
        $customDetails = $request->input('void_reason') ? " — {$request->input('void_reason')}" : "";
        $auditReason   = "{$reasonLabel}{$customDetails}";
        $returnedItemsInput = $request->input('returned_items', []);

        // OTP Security Gate: Only owner can void without OTP
        $itemRef = "acc_sale:{$id}";
        if (!$this->isOwner()) {
            $otpCode = $request->input('otp_code');
            if (!$this->verifyOtp($companyId, 'void_accessory_sale', $itemRef, $otpCode)) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'otp_required' => true,
                        'action' => 'void_accessory_sale',
                        'item_reference' => $itemRef,
                        'message' => 'Store Owner OTP authorization is required to void sales invoices.',
                    ], 403);
                }
                return redirect()->back()->with('error', 'Store Owner OTP authorization is required to void sales invoices.');
            }
        }

        return DB::transaction(function () use ($request, $id, $companyId, $shouldRestock, $auditReason, $returnedItemsInput) {
            $sale = DB::table('ms_accessory_sales')
                ->where('company_id', $companyId)
                ->where('id', $id)
                ->lockForUpdate()
                ->first();

            if (!$sale || $sale->status === 'voided') {
                return redirect()->back()->with('error', 'Sale is already voided or cannot be found.');
            }

            $items = DB::table('ms_accessory_sale_items')
                ->where('company_id', $companyId)
                ->where('accessory_sale_id', $id)
                ->get();

            $totalRefundCalculated = 0.00;
            $itemsProcessedCount = 0;
            $totalOriginalItemsCount = $items->count();

            foreach ($items as $item) {
                // Determine if this item is selected for return
                $returnQty = 0;
                if (!empty($returnedItemsInput)) {
                    if (isset($returnedItemsInput[$item->id]) && (int) $returnedItemsInput[$item->id] > 0) {
                        $returnQty = min((int) $returnedItemsInput[$item->id], $item->quantity);
                    }
                } else {
                    // Full return fallback
                    $returnQty = $item->quantity;
                }

                if ($returnQty <= 0) {
                    continue;
                }

                $itemsProcessedCount++;
                $unitPrice = $item->unit_price > 0 ? (float) $item->unit_price : ((float) $item->line_total / max(1, $item->quantity));
                $itemRefund = round($unitPrice * $returnQty, 2);
                $totalRefundCalculated += $itemRefund;

                $part = DB::table('ms_parts_inventory')->where('id', $item->part_id)->lockForUpdate()->first();
                if ($part) {
                    if ($shouldRestock) {
                        $restoredStock = $part->stock_qty + $returnQty;
                        DB::table('ms_parts_inventory')->where('id', $part->id)->update([
                            'stock_qty' => $restoredStock,
                            'updated_at' => now(),
                        ]);

                        // Log stock restoration
                        DB::table('ms_parts_inventory_history')->insert([
                            'part_id' => $part->id,
                            'type' => 'addition',
                            'quantity' => $returnQty,
                            'balance_after' => $restoredStock,
                            'reference' => "RESTOCKED: {$returnQty}x {$item->part_name} from Sale #{$sale->invoice_number} ({$auditReason}) by " . auth()->user()->name,
                            'user_id' => auth()->id(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } else {
                        // Log as Defective / Quarantined without increasing sellable stock
                        DB::table('ms_parts_inventory_history')->insert([
                            'part_id' => $part->id,
                            'type' => 'deduction',
                            'quantity' => $returnQty,
                            'balance_after' => $part->stock_qty,
                            'reference' => "DEFECTIVE RETURN (Quarantined/Scrap): {$returnQty}x {$item->part_name} from Sale #{$sale->invoice_number} ({$auditReason}) by " . auth()->user()->name,
                            'user_id' => auth()->id(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            if ($itemsProcessedCount === 0) {
                return redirect()->back()->with('error', 'Please select at least one item to return.');
            }

            // Reverse Customer Khata Balance if Udhari was recorded
            if ($sale->udhari_amount > 0 && $sale->customer_id) {
                $customer = DB::table('ms_customers')->where('id', $sale->customer_id)->lockForUpdate()->first();
                if ($customer) {
                    $khataReversal = min($totalRefundCalculated, (float) $sale->udhari_amount);
                    $newBal = max(0.00, $customer->udhari_balance - $khataReversal);
                    DB::table('ms_customers')->where('id', $customer->id)->update([
                        'udhari_balance' => $newBal,
                        'updated_at' => now(),
                    ]);

                    DB::table('ms_customer_khata_transactions')->insert([
                        'company_id' => $companyId,
                        'customer_id' => $customer->id,
                        'type' => 'adjustment',
                        'amount' => $khataReversal,
                        'balance_after' => $newBal,
                        'remarks' => "Reversal for Returned Items on Sale #{$sale->invoice_number}",
                        'recorded_by' => auth()->id(),
                        'created_at' => now(),
                    ]);
                }
            }

            // Mark Header status (voided if all items returned, or partially_returned)
            $isAllReturned = ($itemsProcessedCount >= $totalOriginalItemsCount && $totalRefundCalculated >= (float) $sale->total_amount);
            $newStatus = $isAllReturned ? 'voided' : 'partially_returned';

            DB::table('ms_accessory_sales')->where('id', $id)->update([
                'status' => $newStatus,
                'voided_by' => auth()->id(),
                'voided_at' => now(),
                'void_reason' => $auditReason . ($shouldRestock ? " [Restocked]" : " [Quarantined]"),
                'updated_at' => now(),
            ]);

            $restockMsg = $shouldRestock ? 'and restocked to inventory' : 'and quarantined (defective)';
            return redirect()->back()->with('success', "Return processed successfully! Refund Amount: ₹" . number_format($totalRefundCalculated, 2) . " ({$itemsProcessedCount} item(s) returned {$restockMsg}).");
        });
    }

    /**
     * Get Part Stock Ledger History (JSON endpoint)
     */
    public function getPartHistory($id)
    {
        abort_unless(auth()->check() && (auth()->user()->can('read-mobileshop-accessories') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin') || auth()->user()->hasRole('accessories-staff')), 403, 'Unauthorized action.');

        $companyId = $this->getCompanyId();

        // Verify part belongs to company
        $part = DB::table('ms_parts_inventory')->where('company_id', $companyId)->where('id', $id)->first();
        if (!$part) {
            return response()->json(['success' => false, 'message' => 'Part not found'], 404);
        }

        $history = DB::table('ms_parts_inventory_history')
            ->leftJoin('users', 'ms_parts_inventory_history.user_id', '=', 'users.id')
            ->select('ms_parts_inventory_history.*', 'users.name as user_name')
            ->where('ms_parts_inventory_history.part_id', $id)
            ->orderBy('ms_parts_inventory_history.id', 'desc')
            ->get();

        return response()->json(['success' => true, 'history' => $history]);
    }

    /**
     * Live search parts and accessories for Repair ticket parts consumption
     */
    public function searchParts(Request $request)
    {
        abort_unless(auth()->check(), 401);

        $companyId = $this->getCompanyId();
        $q = trim($request->input('q', ''));
        $category = $request->input('category');

        $query = DB::table('ms_parts_inventory')
            ->where('company_id', $companyId);

        if (!empty($category)) {
            $query->where('category', $category);
        }

        if (!empty($q)) {
            $query->where(function ($w) use ($q) {
                $w->where('name', 'LIKE', "%{$q}%")
                  ->orWhere('brand', 'LIKE', "%{$q}%")
                  ->orWhere('compatible_model', 'LIKE', "%{$q}%")
                  ->orWhere('display_type', 'LIKE', "%{$q}%")
                  ->orWhere('category', 'LIKE', "%{$q}%");
            });
        }

        $parts = $query->orderBy('stock_qty', 'desc')
            ->orderBy('name', 'asc')
            ->limit(50)
            ->get([
                'id', 'name', 'category', 'brand', 'compatible_model',
                'display_type', 'description', 'unit_cost', 'selling_price', 'stock_qty', 'min_stock_alert'
            ]);

        return response()->json(['success' => true, 'parts' => $parts]);
    }

    /**
     * Secure WhatsApp share redirect for accessories.
     */
    public function shareWhatsApp(int $id)
    {
        $companyId = $this->getCompanyId();

        $sale = DB::table('ms_accessory_sales')
            ->leftJoin('ms_customers', 'ms_accessory_sales.customer_id', '=', 'ms_customers.id')
            ->select('ms_accessory_sales.*', 'ms_customers.name as customer_name', 'ms_customers.phone as customer_phone')
            ->where('ms_accessory_sales.id', $id)
            ->where('ms_accessory_sales.company_id', $companyId)
            ->first();

        if (!$sale) {
            abort(404, 'Accessory sale not found.');
        }

        $cPhone = preg_replace('/[^0-9]/', '', $sale->customer_phone ?? '');
        if (strlen($cPhone) === 10) {
            $cPhone = '91' . $cPhone;
        }

        $sName = setting('company.name', 'Maurya Mobile');
        $accMsg = "🧾 *PURCHASE INVOICE & RECEIPT*\n";
        $accMsg .= "🏪 *{$sName}*\n";
        $accMsg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $accMsg .= "Dear *" . ($sale->customer_name ?: 'Valued Customer') . "*,\n";
        $accMsg .= "Thank you for shopping at *{$sName}*!\n\n";
        $accMsg .= "📋 *INVOICE DETAILS*\n";
        $accMsg .= "• *Invoice #:* {$sale->invoice_number}\n";
        $accMsg .= "• *Date:* " . \Carbon\Carbon::parse($sale->created_at)->format('d M Y, h:i A') . "\n";
        $accMsg .= "• *Amount:* ₹" . number_format($sale->total_amount, 2) . " (" . strtoupper(str_replace('_', ' ', $sale->payment_mode)) . ")\n\n";
        $accMsg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $accMsg .= "🛡️ Genuine Accessories & GST Receipt\n";
        $accMsg .= "📍 Linking Road, Bandra West, Mumbai\n";
        $accMsg .= "_Thank you for choosing {$sName}!_";

        return redirect()->away('https://wa.me/' . $cPhone . '?text=' . rawurlencode($accMsg));
    }
}
