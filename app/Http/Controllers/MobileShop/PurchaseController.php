<?php

namespace App\Http\Controllers\MobileShop;

use App\Services\MobileShop\Ai\GeminiDocumentScannerService;
use App\Services\MobileShop\Purchase\BulkPurchaseInwardService;
use App\Services\MobileShop\Purchase\SupplierPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class PurchaseController extends BaseMobileShopController
{
    protected BulkPurchaseInwardService $bulkPurchaseService;
    protected SupplierPaymentService $supplierPaymentService;
    protected GeminiDocumentScannerService $scannerService;

    public function __construct(
        ?BulkPurchaseInwardService $bulkPurchaseService = null,
        ?SupplierPaymentService $supplierPaymentService = null,
        ?GeminiDocumentScannerService $scannerService = null
    ) {
        $this->bulkPurchaseService = $bulkPurchaseService ?? new BulkPurchaseInwardService();
        $this->supplierPaymentService = $supplierPaymentService ?? new SupplierPaymentService();
        $this->scannerService = $scannerService ?? new GeminiDocumentScannerService();
    }
    /**
     * Purchase Hub — Niche-scoped purchase/intake list.
     * Each role sees ONLY their own niche's purchases and gets only their Add form.
     */
    public function purchaseHub(Request $request)
    {
        $companyId = $this->getCompanyId();
        $niche     = $this->getUserNiche();
        $user      = auth()->user();
        $isAdmin   = in_array($niche, ['admin']) || ($user && ($user->hasRole('admin') || user()->hasRole('store-admin')));

        $canAddPhones       = $user->can('create-purchase-phones');
        $canAddSecondhand   = $user->can('create-purchase-secondhand');
        $canAddAccessories  = $user->can('create-purchase-accessories');
        $canAddCovers       = $user->can('create-purchase-covers');

        // Niche-scoped purchase records
        $purchaseOrders    = collect();
        $newPhonePurchases = collect();
        $buybacks          = collect();
        $batchRestocks     = collect();

        switch ($niche) {
            case 'phones':
                $newPhonePurchases = DB::table('ms_mobile_devices')
                    ->where('company_id', $companyId)->where('type', 'new')
                    ->orderBy('id', 'desc')->limit(150)->get();
                break;

            case 'secondhand':
                $buybacks = DB::table('ms_mobile_devices')
                    ->where('company_id', $companyId)->where('type', 'second_hand')
                    ->orderBy('id', 'desc')->limit(150)->get();
                break;

            case 'accessories':
                $batchRestocks = DB::table('ms_parts_inventory_history')
                    ->join('ms_parts_inventory', 'ms_parts_inventory_history.part_id', '=', 'ms_parts_inventory.id')
                    ->select('ms_parts_inventory_history.*', 'ms_parts_inventory.name as part_name', 'ms_parts_inventory.category')
                    ->where('ms_parts_inventory.company_id', $companyId)
                    ->where('ms_parts_inventory_history.type', 'addition')
                    ->orderBy('ms_parts_inventory_history.id', 'desc')->limit(150)->get();
                break;

            case 'covers':
                $coverCats = $this->coverCategories;
                $batchRestocks = DB::table('ms_parts_inventory_history')
                    ->join('ms_parts_inventory', 'ms_parts_inventory_history.part_id', '=', 'ms_parts_inventory.id')
                    ->select('ms_parts_inventory_history.*', 'ms_parts_inventory.name as part_name', 'ms_parts_inventory.category')
                    ->where('ms_parts_inventory.company_id', $companyId)
                    ->whereIn('ms_parts_inventory.category', $coverCats)
                    ->where('ms_parts_inventory_history.type', 'addition')
                    ->orderBy('ms_parts_inventory_history.id', 'desc')->limit(150)->get();
                break;

            default: // admin — all purchases
                $purchaseOrders = DB::table('ms_purchase_orders')
                    ->where('company_id', $companyId)
                    ->orderBy('id', 'desc')
                    ->limit(150)
                    ->get();
                $newPhonePurchases = DB::table('ms_mobile_devices')
                    ->where('company_id', $companyId)->where('type', 'new')
                    ->orderBy('id', 'desc')->limit(150)->get();
                $buybacks = DB::table('ms_mobile_devices')
                    ->where('company_id', $companyId)->where('type', 'second_hand')
                    ->orderBy('id', 'desc')->limit(150)->get();
                $batchRestocks = DB::table('ms_parts_inventory_history')
                    ->join('ms_parts_inventory', 'ms_parts_inventory_history.part_id', '=', 'ms_parts_inventory.id')
                    ->select('ms_parts_inventory_history.*', 'ms_parts_inventory.name as part_name', 'ms_parts_inventory.category')
                    ->where('ms_parts_inventory.company_id', $companyId)
                    ->where('ms_parts_inventory_history.type', 'addition')
                    ->orderBy('ms_parts_inventory_history.id', 'desc')->limit(150)->get();
                break;
        }

        // Master Purchase Invoices Registry (Niche-scoped)
        $poQuery = DB::table('ms_purchase_orders')
            ->leftJoin('ms_suppliers', 'ms_purchase_orders.supplier_id', '=', 'ms_suppliers.id')
            ->select('ms_purchase_orders.*', 'ms_suppliers.name as supplier_name', 'ms_suppliers.phone as supplier_phone', 'ms_suppliers.gstin as supplier_gstin')
            ->where('ms_purchase_orders.company_id', $companyId);

        if ($niche === 'phones') {
            $poQuery->where(function($q) {
                $q->where('ms_purchase_orders.po_number', 'like', 'PO-PHONES%')
                  ->orWhere('ms_purchase_orders.po_number', 'like', 'PO-2026%');
            });
        } elseif ($niche === 'secondhand') {
            $poQuery->where('ms_purchase_orders.po_number', 'like', 'BUYBACK%');
        } elseif (in_array($niche, ['accessories', 'covers'])) {
            $poQuery->where(function($q) {
                $q->where('ms_purchase_orders.po_number', 'like', 'INV-%')
                  ->orWhere('ms_purchase_orders.po_number', 'like', 'RESTOCK%');
            });
        }

        $purchaseInvoices = $poQuery->orderBy('ms_purchase_orders.id', 'desc')->get();

        // Eager-load items for all purchase invoices
        $poIds = $purchaseInvoices->pluck('id')->toArray();
        $itemsByPo = DB::table('ms_purchase_order_items')
            ->whereIn('purchase_order_id', $poIds)
            ->get()
            ->groupBy('purchase_order_id');

        foreach ($purchaseInvoices as $inv) {
            $inv->items = ($itemsByPo[$inv->id] ?? collect())->values();
            $inv->item_count = $inv->items->count();
            $inv->total_units = (int) $inv->items->sum('qty');
        }

        // Summary KPIs
        $totalPOValue        = (float) $purchaseInvoices->sum('total_amount');
        $totalPODue          = (float) $purchaseInvoices->where('status', '!=', 'paid')->sum('balance_due');
        $totalInvoicesCount  = $purchaseInvoices->count();
        $totalUnitsPurchased = (int) $purchaseInvoices->sum('total_units');

        // Drop-downs for add forms and supplier payment modals
        $prefix = DB::getTablePrefix();
        $suppliers = DB::table('ms_suppliers')
            ->leftJoin('ms_supplier_credit_wallets', function ($j) use ($companyId) {
                $j->on('ms_suppliers.id', '=', 'ms_supplier_credit_wallets.supplier_id')
                  ->where('ms_supplier_credit_wallets.company_id', $companyId);
            })
            ->select('ms_suppliers.*', DB::raw("COALESCE({$prefix}ms_supplier_credit_wallets.credit_balance, 0) as credit_balance"))
            ->where('ms_suppliers.company_id', $companyId)
            ->get();

        $wallets = DB::table('ms_supplier_credit_wallets')
            ->join('ms_suppliers', 'ms_supplier_credit_wallets.supplier_id', '=', 'ms_suppliers.id')
            ->select('ms_supplier_credit_wallets.*', 'ms_suppliers.name as supplier_name')
            ->where('ms_supplier_credit_wallets.company_id', $companyId)
            ->get();
        $categories = DB::table('ms_part_categories')->where('company_id', $companyId)->orderBy('name', 'asc')->get();
        $parts      = DB::table('ms_parts_inventory')->where('company_id', $companyId)->orderBy('name', 'asc')->get();

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

        $defectiveItems = DB::table('ms_defective_items')
            ->where('company_id', $companyId)
            ->orderBy('id', 'desc')
            ->get();
        $totalDefectiveQty = (int) $defectiveItems->where('status', 'pending_supplier_return')->sum('qty');

        return view('mobileshop.purchase', compact(
            'niche', 'isAdmin', 'purchaseInvoices', 'purchaseOrders', 'newPhonePurchases', 'buybacks', 'batchRestocks',
            'canAddPhones', 'canAddSecondhand', 'canAddAccessories', 'canAddCovers',
            'totalPOValue', 'totalPODue', 'totalInvoicesCount', 'totalUnitsPurchased',
            'suppliers', 'wallets', 'categories', 'parts', 'knownBrands', 'knownModels',
            'defectiveItems', 'totalDefectiveQty'
        ));
    }

    /**
     * Dispatcher: Unified Purchase Store
     * Sniffs request payload attributes to route to the appropriate handler:
     * 1. 'second_hand' / 'customer_buyback_name' -> storeSecondHand (Customer device buyback)
     * 2. 'bulk_restock' / 'restock_qty'          -> bulkRestock (Batch inventory restock)
     * 3. 'accessory' / 'compatible_model'        -> storePart (Single accessory/part registration)
     * 4. Default                                 -> storeNewMobile (Supplier purchase order for new phone)
     */
    public function storePurchase(Request $request)
    {
        if ($request->input('type') === 'quick' || $request->has('quick_purchase')) {
            return $this->quickPurchase($request);
        }
        if ($request->input('type') === 'second_hand' || $request->has('customer_buyback_name')) {
            return app(StockController::class)->storeSecondHand($request);
        }
        if ($request->input('type') === 'bulk_restock' || $request->has('restock_qty')) {
            return app(AccessoriesController::class)->bulkRestock($request);
        }
        if ($request->input('type') === 'accessory' || $request->has('compatible_model')) {
            return app(AccessoriesController::class)->storePart($request);
        }
        return app(StockController::class)->storeNewMobile($request);
    }

    /**
     * Quick Purchase / Direct Stock Intake without requiring supplier info.
     * Takes: category, name, quantity, purchase_price, low_stock
     */
    public function quickPurchase(Request $request)
    {
        abort_unless(auth()->check() && (
            auth()->user()->can('create-purchase-accessories') ||
            auth()->user()->can('create-purchase-covers') ||
            auth()->user()->can('create-mobileshop-accessories') ||
            auth()->user()->hasRole('admin') ||
            auth()->user()->hasRole('store-admin') ||
            auth()->user()->hasRole('accessories-staff') ||
            auth()->user()->hasRole('owner') ||
            $this->isOwner()
        ), 403, 'Unauthorized action.');

        try {
            $request->validate([
                'name'           => 'required|string|max:150',
                'category'       => 'nullable|string|max:100',
                'quantity'       => 'required|integer|min:1',
                'purchase_price' => 'required|numeric|min:0',
                'low_stock'      => 'nullable|integer|min:0',
                'selling_price'  => 'nullable|numeric|min:0',
            ]);

            $companyId = $this->getCompanyId();
            $name = trim($request->input('name'));
            $qty = max(1, (int) $request->input('quantity', 1));
            $purchasePrice = (float) $request->input('purchase_price', 0);
            $lowStock = $request->filled('low_stock') ? max(0, (int) $request->input('low_stock')) : 3;
            $sellingPrice = $request->filled('selling_price') && (float) $request->input('selling_price') > 0
                ? (float) $request->input('selling_price')
                : round($purchasePrice * 1.5, 2);

            $categoryInput = trim($request->input('category', ''));
            $categoryRow = \App\Services\MobileShop\Accessories\AccessoryCategoryService::resolveCategory($companyId, $categoryInput, $name);
            $finalCategorySlug = $categoryRow ? $categoryRow->slug : \App\Services\MobileShop\Accessories\AccessoryCategoryService::canonicalSlug($categoryInput, $name);
            $isGift = ($categoryRow && $categoryRow->is_gift_eligible) ? 1 : 0;

            return DB::transaction(function () use ($companyId, $name, $qty, $purchasePrice, $sellingPrice, $lowStock, $finalCategorySlug, $categoryRow, $isGift, $request) {
                // 1. Check if part already exists in ms_parts_inventory
                $existingPart = DB::table('ms_parts_inventory')
                    ->where('company_id', $companyId)
                    ->where('name', $name)
                    ->lockForUpdate()
                    ->first();

                if ($existingPart) {
                    $newStock = (int) $existingPart->stock_qty + $qty;
                    $updateData = [
                        'stock_qty'  => $newStock,
                        'updated_at' => now(),
                    ];
                    if ($purchasePrice > 0) {
                        $updateData['unit_cost'] = $purchasePrice;
                    }
                    if ($sellingPrice > 0 && ($existingPart->selling_price <= 0 || $sellingPrice > $existingPart->selling_price)) {
                        $updateData['selling_price'] = $sellingPrice;
                    }
                    if ($lowStock !== null) {
                        $updateData['min_stock_alert'] = $lowStock;
                    }
                    if ($finalCategorySlug && ($existingPart->category === 'general_accessory' || empty($existingPart->category))) {
                        $updateData['category'] = $finalCategorySlug;
                        $updateData['category_id'] = $categoryRow?->id ?? $existingPart->category_id;
                    }

                    DB::table('ms_parts_inventory')->where('id', $existingPart->id)->update($updateData);
                    $partId = $existingPart->id;
                } else {
                    $partId = DB::table('ms_parts_inventory')->insertGetId([
                        'company_id'       => $companyId,
                        'name'             => $name,
                        'category'         => $finalCategorySlug ?: 'general_accessory',
                        'category_id'      => $categoryRow?->id,
                        'brand'            => 'Universal',
                        'compatible_model' => 'Universal',
                        'display_type'     => 'na',
                        'hsn_code'         => '85177090',
                        'unit_cost'        => $purchasePrice,
                        'selling_price'    => $sellingPrice,
                        'stock_qty'        => $qty,
                        'min_stock_alert'  => $lowStock,
                        'is_gift_eligible' => $isGift,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);
                    $newStock = $qty;
                }

                // 2. Record inventory movement history
                DB::table('ms_parts_inventory_history')->insert([
                    'part_id'       => $partId,
                    'type'          => 'addition',
                    'quantity'      => $qty,
                    'balance_after' => $newStock,
                    'reference'     => 'Quick Purchase Intake',
                    'user_id'       => auth()->id(),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);

                // 3. Resolve or create direct purchase supplier (no supplier info needed from user!)
                $supplier = DB::table('ms_suppliers')
                    ->where('company_id', $companyId)
                    ->where(function ($q) {
                        $q->where('name', 'Direct Purchase')
                          ->orWhere('name', 'Direct / Counter Purchase')
                          ->orWhere('name', 'General Supplier');
                    })
                    ->first();

                if (!$supplier) {
                    $supplierId = DB::table('ms_suppliers')->insertGetId([
                        'company_id' => $companyId,
                        'name'       => 'Direct Purchase',
                        'phone'      => '9999999999',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $supplierId = $supplier->id;
                }

                // 4. Generate PO Number matching INV-% for uniform tracking
                $totalAmount = round($purchasePrice * $qty, 2);
                $poNumber = 'INV-QP-' . date('Ymd') . '-' . str_pad(DB::table('ms_purchase_orders')->where('company_id', $companyId)->count() + 1, 4, '0', STR_PAD_LEFT);
                $counter = 1;
                $basePoNum = $poNumber;
                while (DB::table('ms_purchase_orders')->where('company_id', $companyId)->where('po_number', $poNumber)->exists()) {
                    $poNumber = $basePoNum . '-' . $counter++;
                }

                $poId = DB::table('ms_purchase_orders')->insertGetId([
                    'company_id'   => $companyId,
                    'supplier_id'  => $supplierId,
                    'po_number'    => $poNumber,
                    'order_date'   => now()->toDateString(),
                    'tax_type'     => 'intra_state',
                    'subtotal'     => $totalAmount,
                    'total_amount' => $totalAmount,
                    'amount_paid'  => $totalAmount,
                    'balance_due'  => 0.00,
                    'status'       => 'paid',
                    'created_by'   => auth()->id(),
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);

                DB::table('ms_purchase_order_items')->insert([
                    'purchase_order_id' => $poId,
                    'brand'             => 'Universal',
                    'model'             => $name,
                    'variant'           => $finalCategorySlug ?: 'General',
                    'hsn_code'          => '85177090',
                    'qty'               => $qty,
                    'qty_received'      => $qty,
                    'unit_cost'         => $purchasePrice,
                    'tax_rate'          => 0.00,
                    'line_total'        => $totalAmount,
                ]);

                $msg = "Quick Purchase recorded: {$qty}x {$name} (₹" . number_format($totalAmount, 2) . ") added to inventory!";

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success'   => true,
                        'message'   => $msg,
                        'part_id'   => $partId,
                        'po_id'     => $poId,
                        'po_number' => $poNumber,
                        'new_stock' => $newStock,
                    ]);
                }

                return redirect()->route('mobileshop.purchase')->with('success', $msg);
            });
        } catch (\Illuminate\Validation\ValidationException $ve) {
            $msg = collect($ve->errors())->flatten()->first() ?: 'Validation failed.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg, 'errors' => $ve->errors()], 422);
            }
            return redirect()->back()->withInput()->with('error', $msg);
        } catch (\Throwable $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Purchase Invoice & Inward Procurement Bill View
     */
    public function purchaseInvoice(Request $request, $id)
    {
        $companyId = $this->getCompanyId();
        $data = $this->resolvePurchaseOrderDetails($companyId, (int) $id);

        if ($data) {
            return view('mobileshop.purchase_invoice', $data);
        }

        $device = DB::table('ms_mobile_devices')->where('company_id', $companyId)->where('id', $id)->first();
        if ($device) {
            return redirect()->route('mobileshop.purchase')->with('info', "Buyback device: {$device->brand} {$device->model} (IMEI: {$device->imei_1})");
        }

        abort(404, 'Purchase record not found.');
    }

    /**
     * Download Purchase Invoice as Direct PDF
     */
    public function purchaseInvoicePdf(Request $request, $id)
    {
        $data = $this->resolvePurchaseOrderDetails($this->getCompanyId(), (int) $id);
        if (!$data) {
            abort(404, 'Purchase record not found.');
        }

        $pdf = Pdf::loadView('mobileshop.pdf.purchase_invoice', $data);
        $pdf->setPaper('a4', 'portrait');
        return $pdf->download("PurchaseOrder-{$data['po']->po_number}.pdf");
    }

    /**
     * Purchase Registration Page — full page, multi-item bulk stock entry
     * Shows supplier running balances & advance (credit wallet) balances.
     */
    public function purchaseCreate()
    {
        abort_unless(auth()->check() && (auth()->user()->can('create-purchase-accessories') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin') || auth()->user()->hasRole('accessories-staff')), 403, 'Unauthorized access to purchase registration.');

        $companyId = $this->getCompanyId();

        $prefix = DB::getTablePrefix();
        // Suppliers with running outstanding (sum of unpaid PO balances) and advance credit
        $suppliers = DB::table('ms_suppliers')
            ->leftJoin('ms_purchase_orders', function ($j) use ($companyId) {
                $j->on('ms_suppliers.id', '=', 'ms_purchase_orders.supplier_id')
                  ->where('ms_purchase_orders.company_id', $companyId)
                  ->whereNotIn('ms_purchase_orders.status', ['paid', 'cancelled']);
            })
            ->leftJoin('ms_supplier_credit_wallets', function ($j) use ($companyId) {
                $j->on('ms_suppliers.id', '=', 'ms_supplier_credit_wallets.supplier_id')
                  ->where('ms_supplier_credit_wallets.company_id', $companyId);
            })
            ->where('ms_suppliers.company_id', $companyId)
            ->groupBy('ms_suppliers.id', 'ms_suppliers.name', 'ms_suppliers.phone', 'ms_suppliers.gstin')
            ->select(
                'ms_suppliers.id', 'ms_suppliers.name', 'ms_suppliers.phone', 'ms_suppliers.gstin',
                DB::raw("COALESCE(SUM({$prefix}ms_purchase_orders.balance_due), 0) as outstanding_balance"),
                DB::raw("COALESCE(MAX({$prefix}ms_supplier_credit_wallets.credit_balance), 0) as advance_balance")
            )
            ->orderBy('ms_suppliers.name')
            ->get();

        return view('mobileshop.purchase_create', compact('suppliers'));
    }

    /**
     * Store Bulk Multi-Item Purchase — creates one PO with many device lines,
     * carries unpaid balance to supplier ledger, applies advance credit wallet.
     */
    public function storeBulkPurchase(Request $request)
    {
        abort_unless(auth()->check() && (auth()->user()->can('create-purchase-accessories') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin') || auth()->user()->hasRole('accessories-staff')), 403, 'Unauthorized action.');

        $companyId = $this->getCompanyId();
        $result = $this->bulkPurchaseService->storeBulkPurchase($companyId, $request);

        $msg = "Bulk purchase {$result['poNum']} recorded — {$result['deviceCount']} units added to stock.";
        if ($result['balanceDue'] > 0) {
            $msg .= " Balance due to supplier: ₹" . number_format($result['balanceDue'], 2) . " (carried to supplier ledger).";
        }
        return redirect()->route('mobileshop.purchase', ['company_id' => $companyId])->with('success', $msg);
    }

    /**
     * Supplier Purchase Orders Ledger & Inward Inventory (redirect to main purchase hub)
     */
    public function purchaseOrders()
    {
        return redirect()->route('mobileshop.purchase');
    }

    /**
     * Supplier Debt & Ledger Page
     * Shows all suppliers with outstanding PO balances, advance wallets, net payable, and payment history.
     */
    public function supplierDebt(Request $request)
    {
        abort_unless(auth()->check() && (
            auth()->user()->hasRole('admin') ||
            auth()->user()->hasRole('store-admin') ||
            auth()->user()->hasRole('owner') ||
            auth()->user()->hasRole('accessories-staff') ||
            auth()->user()->can('read-mobileshop-procurement') ||
            auth()->user()->can('create-mobileshop-procurement') ||
            auth()->user()->can('read-mobileshop-purchase') ||
            auth()->user()->can('read-mobileshop-dashboard') ||
            auth()->user()->can('read-admin-panel') ||
            $this->isOwner()
        ), 403, 'Unauthorized.');

        $companyId = $this->getCompanyId();

        // 1. Fetch all suppliers for this company
        $suppliers = DB::table('ms_suppliers')
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        // 2. Fetch outstanding PO balances per supplier safely without multi-table join group issues
        $poBalances = DB::table('ms_purchase_orders')
            ->where('company_id', $companyId)
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->groupBy('supplier_id')
            ->select('supplier_id', DB::raw('SUM(balance_due) as total_due'))
            ->pluck('total_due', 'supplier_id');

        // 3. Fetch credit wallets per supplier safely
        $wallets = DB::table('ms_supplier_credit_wallets')
            ->where('company_id', $companyId)
            ->pluck('credit_balance', 'supplier_id');

        // 4. Attach balances and sort cleanly in memory
        foreach ($suppliers as $s) {
            $s->outstanding_balance = (float) ($poBalances[$s->id] ?? 0);
            $s->advance_balance     = (float) ($wallets[$s->id] ?? 0);
        }

        // Sort: highest outstanding debt first, then alphabetical by name
        $suppliers = $suppliers->sortBy([
            ['outstanding_balance', 'desc'],
            ['name', 'asc'],
        ])->values();

        $totalOutstanding = (float) $suppliers->sum('outstanding_balance');
        $totalWallets     = (float) $suppliers->sum('advance_balance');

        // Full payment history (capped at 200 records for performance)
        $payments = DB::table('ms_supplier_payments')
            ->join('ms_suppliers', 'ms_supplier_payments.supplier_id', '=', 'ms_suppliers.id')
            ->leftJoin('ms_purchase_orders', 'ms_supplier_payments.purchase_order_id', '=', 'ms_purchase_orders.id')
            ->select(
                'ms_supplier_payments.*',
                'ms_suppliers.name as supplier_name',
                'ms_purchase_orders.po_number'
            )
            ->where('ms_supplier_payments.company_id', $companyId)
            ->orderBy('ms_supplier_payments.id', 'desc')
            ->limit(200)
            ->get();

        return view('mobileshop.supplier_debt', compact(
            'suppliers', 'totalOutstanding', 'totalWallets', 'payments'
        ));
    }

    /**
     * Store a new supplier manually (from Supplier Debt page)
     */
    public function storeSupplier(Request $request)
    {
        abort_unless(auth()->check() && (
            auth()->user()->hasRole('admin') ||
            auth()->user()->hasRole('store-admin') ||
            auth()->user()->hasRole('owner') ||
            auth()->user()->hasRole('accessories-staff') ||
            auth()->user()->can('create-mobileshop-procurement') ||
            auth()->user()->can('read-mobileshop-procurement') ||
            auth()->user()->can('read-mobileshop-purchase') ||
            auth()->user()->can('create-purchase-accessories') ||
            $this->isOwner()
        ), 403, 'Unauthorized.');

        $request->validate([
            'name'    => 'required|string|max:191',
            'phone'   => 'nullable|string|max:20',
            'gstin'   => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'notes'   => 'nullable|string|max:500',
        ]);

        $companyId = $this->getCompanyId();

        // Prevent duplicate supplier names within same company
        $exists = DB::table('ms_suppliers')
            ->where('company_id', $companyId)
            ->where('name', trim($request->name))
            ->exists();

        if ($exists) {
            return redirect()->back()->withInput()->with('error', "Supplier '{$request->name}' already exists.");
        }

        DB::table('ms_suppliers')->insert([
            'company_id'  => $companyId,
            'name'        => trim($request->name),
            'phone'       => $request->phone ?? '0000000000',
            'gstin'       => $request->gstin,
            'address'     => $request->address,
            'notes'       => $request->notes,
            'enabled'     => true,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return redirect()->route('mobileshop.supplier_debt', ['company_id' => $companyId])
            ->with('success', "Supplier '{$request->name}' added successfully.");
    }

    /**
     * Record Supplier Payment / Advance / Settlement
     */
    public function recordSupplierPayment(Request $request)
    {
        abort_unless(auth()->check() && (
            auth()->user()->can('create-mobileshop-procurement') ||
            auth()->user()->can('create-purchase-accessories') ||
            auth()->user()->can('read-mobileshop-purchase') ||
            auth()->user()->hasRole('admin') ||
            auth()->user()->hasRole('store-admin') ||
            auth()->user()->hasRole('owner') ||
            auth()->user()->hasRole('accessories-staff') ||
            $this->isOwner()
        ), 403, 'Unauthorized action.');

        $result = $this->supplierPaymentService->recordSupplierPayment($this->getCompanyId(), $request);

        if (!$result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }
        return redirect()->back()->with('success', $result['message']);
    }

    /**
     * Update Supplier details and/or adjust prepaid wallet balance
     */
    public function updateSupplier(Request $request)
    {
        abort_unless(auth()->check() && (
            auth()->user()->hasRole('admin') ||
            auth()->user()->hasRole('store-admin') ||
            auth()->user()->hasRole('owner') ||
            auth()->user()->hasRole('accessories-staff') ||
            auth()->user()->can('create-mobileshop-procurement') ||
            auth()->user()->can('read-mobileshop-procurement') ||
            $this->isOwner()
        ), 403, 'Unauthorized action.');

        $result = $this->supplierPaymentService->updateSupplier($this->getCompanyId(), $request);

        if (!$result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }
        return redirect()->back()->with('success', $result['message']);
    }

    /**
     * AI Scan Vendor Distributor Purchase Invoice / Stock Intake Challan (Phones & Accessories)
     */
    public function scanPurchaseInvoice(Request $request)
    {
        abort_unless(auth()->check() && (
            auth()->user()->can('create-purchase-accessories') ||
            auth()->user()->hasRole('admin') ||
            auth()->user()->hasRole('store-admin') ||
            auth()->user()->hasRole('accessories-staff')
        ), 403, 'Unauthorized action.');

        $file = $request->file('invoice_image') ?? $request->file('bill_image');
        if (!$file) {
            return response()->json([
                'success' => false,
                'message' => 'Please upload or snap a photo of the vendor purchase bill or challan.',
            ], 422);
        }

        $result = $this->scannerService->scanPurchaseInvoice($file, $this->getCompanyId());

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Record Defective Item from Purchase Order Return
     */
    public function recordPurchaseDefect(Request $request)
    {
        abort_unless(auth()->check() && (
            auth()->user()->can('create-purchase-accessories') ||
            auth()->user()->can('create-purchase-covers') ||
            auth()->user()->can('manage-stock-accessories') ||
            auth()->user()->can('manage-stock-covers') ||
            auth()->user()->hasRole('admin') ||
            auth()->user()->hasRole('store-admin') ||
            auth()->user()->hasRole('accessories-staff')
        ), 403, 'Unauthorized action.');

        $request->validate([
            'purchase_order_id' => 'required|integer',
            'po_item_id'        => 'required|integer',
            'defect_qty'        => 'required|integer|min:1',
            'defect_reason'     => 'required|string',
            'notes'             => 'nullable|string',
        ]);

        $companyId = $this->getCompanyId();
        $poId = (int) $request->input('purchase_order_id');
        $poItemId = (int) $request->input('po_item_id');
        $defectQty = (int) $request->input('defect_qty');
        $defectReason = $request->input('defect_reason');
        $notes = $request->input('notes');
        $deductStock = $request->has('deduct_stock') ? (bool) $request->input('deduct_stock') : true;

        $po = DB::table('ms_purchase_orders')
            ->where('company_id', $companyId)
            ->where('id', $poId)
            ->first();

        if (!$po) {
            return redirect()->back()->with('error', 'Purchase invoice not found.');
        }

        $poItem = DB::table('ms_purchase_order_items')
            ->where('purchase_order_id', $po->id)
            ->where('id', $poItemId)
            ->first();

        if (!$poItem) {
            return redirect()->back()->with('error', 'Item not found in this purchase invoice.');
        }

        if ($defectQty > $poItem->qty) {
            return redirect()->back()->with('error', "Defect quantity ({$defectQty}) cannot exceed purchased quantity ({$poItem->qty}).");
        }

        $supplier = DB::table('ms_suppliers')->where('id', $po->supplier_id)->first();
        $supplierName = $supplier ? $supplier->name : 'Supplier';
        $itemTitle = trim("{$poItem->brand} {$poItem->model}" . ($poItem->variant && $poItem->variant !== 'Standard' ? " {$poItem->variant}" : ''));

        // Look for matching inventory item to deduct from current sellable stock
        $part = DB::table('ms_parts_inventory')
            ->where('company_id', $companyId)
            ->where(function ($q) use ($poItem, $itemTitle) {
                $q->where('name', $itemTitle)
                  ->orWhere('name', 'like', "%{$poItem->model}%")
                  ->orWhere(function ($sub) use ($poItem) {
                      $sub->where('brand', $poItem->brand)
                          ->where('compatible_model', $poItem->model);
                  });
            })
            ->first();

        return DB::transaction(function () use ($companyId, $po, $poItem, $part, $itemTitle, $defectQty, $defectReason, $notes, $deductStock, $supplier, $supplierName, $request) {
            if ($deductStock && $part) {
                $newStock = max(0, (int) $part->stock_qty - $defectQty);
                DB::table('ms_parts_inventory')->where('id', $part->id)->update([
                    'stock_qty'  => $newStock,
                    'updated_at' => now(),
                ]);

                DB::table('ms_parts_inventory_history')->insert([
                    'part_id'       => $part->id,
                    'type'          => 'deduction',
                    'quantity'      => $defectQty,
                    'balance_after' => $newStock,
                    'reference'     => "DEFECTIVE PURCHASE RETURN: {$defectQty}x {$itemTitle} from PO #{$po->po_number} ({$defectReason}) by " . (auth()->user()?->name ?? 'Staff'),
                    'user_id'       => auth()->id(),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            $unitCost = (float) ($poItem->unit_cost ?? ($part ? $part->unit_cost : 0));
            $totalCost = round($unitCost * $defectQty, 2);

            DB::table('ms_defective_items')->insert([
                'company_id'    => $companyId,
                'part_id'       => $part ? $part->id : null,
                'item_name'     => $itemTitle,
                'source_type'   => 'purchase_return',
                'source_id'     => $po->id,
                'source_ref'    => $po->po_number,
                'supplier_id'   => $po->supplier_id,
                'supplier_name' => $supplierName,
                'customer_name' => null,
                'qty'           => $defectQty,
                'unit_cost'     => $unitCost,
                'total_cost'    => $totalCost,
                'defect_reason' => $defectReason,
                'status'        => 'pending_supplier_return',
                'notes'         => $notes,
                'created_by'    => auth()->id(),
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            $successMsg = "Successfully recorded {$defectQty}x {$itemTitle} as defective from PO #{$po->po_number}. Added to Defective Items registry.";

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMsg,
                ]);
            }

            return redirect()->back()->with('success', $successMsg);
        });
    }

    /**
     * Update Defective Item Status (e.g. Returned to Supplier, Replaced, Scrap)
     */
    public function updateDefectiveStatus(Request $request, $id)
    {
        abort_unless(auth()->check() && (
            auth()->user()->can('create-purchase-accessories') ||
            auth()->user()->can('create-purchase-covers') ||
            auth()->user()->can('manage-stock-accessories') ||
            auth()->user()->can('manage-stock-covers') ||
            auth()->user()->hasRole('admin') ||
            auth()->user()->hasRole('store-admin') ||
            auth()->user()->hasRole('accessories-staff')
        ), 403, 'Unauthorized action.');

        $request->validate([
            'status' => 'required|string|in:pending_supplier_return,returned_to_supplier,replaced_by_supplier,scrap_written_off',
            'notes'  => 'nullable|string',
        ]);

        $companyId = $this->getCompanyId();
        $defective = DB::table('ms_defective_items')
            ->where('company_id', $companyId)
            ->where('id', $id)
            ->first();

        if (!$defective) {
            return redirect()->back()->with('error', 'Defective item record not found.');
        }

        $newStatus = $request->input('status');
        $notes = $request->input('notes');
        $restockReplacement = $request->boolean('restock_replacement', false);

        DB::transaction(function () use ($defective, $newStatus, $notes, $restockReplacement, $companyId) {
            // If replacement received from supplier and restock requested
            if ($newStatus === 'replaced_by_supplier' && $restockReplacement && $defective->part_id) {
                $part = DB::table('ms_parts_inventory')
                    ->where('company_id', $companyId)
                    ->where('id', $defective->part_id)
                    ->lockForUpdate()
                    ->first();

                if ($part) {
                    $newStock = $part->stock_qty + $defective->qty;
                    DB::table('ms_parts_inventory')->where('id', $part->id)->update([
                        'stock_qty'  => $newStock,
                        'updated_at' => now(),
                    ]);

                    DB::table('ms_parts_inventory_history')->insert([
                        'part_id'       => $part->id,
                        'type'          => 'addition',
                        'quantity'      => $defective->qty,
                        'balance_after' => $newStock,
                        'reference'     => "SUPPLIER REPLACEMENT RESTOCK: {$defective->qty}x {$defective->item_name} replaced by " . ($defective->supplier_name ?: 'Supplier') . " by " . (auth()->user()?->name ?? 'Staff'),
                        'user_id'       => auth()->id(),
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                }
            }

            $updateData = [
                'status'     => $newStatus,
                'updated_at' => now(),
            ];
            if ($notes) {
                $updateData['notes'] = ($defective->notes ? $defective->notes . "\n" : '') . "[" . date('d M Y') . "] " . $notes;
            }

            DB::table('ms_defective_items')->where('id', $defective->id)->update($updateData);
        });

        $statusLabels = [
            'pending_supplier_return' => 'Pending Supplier Return',
            'returned_to_supplier'    => 'Marked Returned to Supplier',
            'replaced_by_supplier'    => 'Marked Replaced by Supplier',
            'scrap_written_off'       => 'Written off as Scrap',
        ];

        $label = $statusLabels[$newStatus] ?? $newStatus;
        return redirect()->back()->with('success', "Defective item #{$id} updated to '{$label}'.");
    }

    /**
     * Update an existing Purchase Order invoice
     */
    public function updatePurchase(Request $request)
    {
        abort_unless(auth()->check() && (
            auth()->user()->can('create-purchase-accessories') ||
            auth()->user()->can('create-purchase-covers') ||
            auth()->user()->can('create-purchase-phones') ||
            auth()->user()->can('read-mobileshop-purchase') ||
            auth()->user()->hasRole('admin') ||
            auth()->user()->hasRole('store-admin')
        ), 403, 'Unauthorized action.');

        $request->validate([
            'purchase_id'   => 'required|integer',
            'po_number'     => 'required|string|max:100',
            'order_date'    => 'required|date',
            'supplier_name' => 'nullable|string|max:150',
            'supplier_id'   => 'nullable|integer',
            'bill_type'     => 'nullable|in:gst,non_gst',
            'total_amount'  => 'required|numeric|min:0',
            'amount_paid'   => 'required|numeric|min:0',
        ]);

        $companyId = $this->getCompanyId();
        $po = DB::table('ms_purchase_orders')
            ->where('company_id', $companyId)
            ->where('id', $request->purchase_id)
            ->first();

        if (!$po) {
            return redirect()->back()->with('error', 'Purchase invoice not found.');
        }

        // Check po_number duplicate if changed
        $cleanPoNum = trim($request->po_number);
        $duplicate = DB::table('ms_purchase_orders')
            ->where('company_id', $companyId)
            ->where('po_number', $cleanPoNum)
            ->where('id', '!=', $po->id)
            ->exists();

        if ($duplicate) {
            return redirect()->back()->with('error', "Invoice/PO # '{$cleanPoNum}' is already used by another purchase record.");
        }

        return DB::transaction(function () use ($request, $companyId, $po, $cleanPoNum) {
            // Resolve supplier
            $supplierId = $po->supplier_id;
            if ($request->filled('supplier_id') && (int)$request->supplier_id > 0) {
                $supplierId = (int)$request->supplier_id;
            } elseif ($request->filled('supplier_name')) {
                $sName = trim($request->supplier_name);
                $existingSupplier = DB::table('ms_suppliers')
                    ->where('company_id', $companyId)
                    ->where('name', $sName)
                    ->first();
                if ($existingSupplier) {
                    $supplierId = $existingSupplier->id;
                } else {
                    $supplierId = DB::table('ms_suppliers')->insertGetId([
                        'company_id' => $companyId,
                        'name'       => $sName,
                        'phone'      => $request->supplier_phone ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $totalAmount = round((float) $request->total_amount, 2);
            $amountPaid = min(round((float) $request->amount_paid, 2), $totalAmount);
            $balanceDue = max(0.00, $totalAmount - $amountPaid);
            $status = ($balanceDue <= 0.001) ? 'paid' : (($amountPaid > 0) ? 'partially_paid' : 'received');
            $billType = $request->bill_type ?: ($po->bill_type ?? 'gst');

            DB::table('ms_purchase_orders')->where('id', $po->id)->update([
                'supplier_id'  => $supplierId,
                'po_number'    => $cleanPoNum,
                'order_date'   => $request->order_date,
                'bill_type'    => $billType,
                'subtotal'     => $totalAmount,
                'total_amount' => $totalAmount,
                'amount_paid'  => $amountPaid,
                'balance_due'  => $balanceDue,
                'status'       => $status,
                'updated_at'   => now(),
            ]);

            // Sync supplier payments
            $existingPayment = DB::table('ms_supplier_payments')
                ->where('company_id', $companyId)
                ->where('purchase_order_id', $po->id)
                ->first();

            if ($amountPaid > 0) {
                if ($existingPayment) {
                    DB::table('ms_supplier_payments')->where('id', $existingPayment->id)->update([
                        'supplier_id'  => $supplierId,
                        'amount'       => $amountPaid,
                        'payment_date' => $request->order_date,
                        'reference_no' => $cleanPoNum,
                        'updated_at'   => now(),
                    ]);
                } else {
                    DB::table('ms_supplier_payments')->insert([
                        'company_id'        => $companyId,
                        'supplier_id'       => $supplierId,
                        'purchase_order_id' => $po->id,
                        'amount'            => $amountPaid,
                        'payment_date'      => $request->order_date,
                        'mode'              => 'cash',
                        'reference_no'      => $cleanPoNum,
                        'remarks'           => "Payment on Purchase #{$cleanPoNum}",
                        'recorded_by'       => auth()->id(),
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ]);
                }
            } else {
                if ($existingPayment) {
                    DB::table('ms_supplier_payments')->where('id', $existingPayment->id)->delete();
                }
            }

            return redirect()->back()->with('success', "Purchase invoice #{$cleanPoNum} updated successfully.");
        });
    }

    /**
     * Delete a Purchase Order and reverse inwarded stock
     */
    public function deletePurchase(Request $request)
    {
        abort_unless(auth()->check() && (
            auth()->user()->can('create-purchase-accessories') ||
            auth()->user()->can('create-purchase-covers') ||
            auth()->user()->can('create-purchase-phones') ||
            auth()->user()->can('read-mobileshop-purchase') ||
            auth()->user()->hasRole('admin') ||
            auth()->user()->hasRole('store-admin')
        ), 403, 'Unauthorized action.');

        $request->validate([
            'purchase_id' => 'required|integer',
        ]);

        $companyId = $this->getCompanyId();
        $po = DB::table('ms_purchase_orders')
            ->where('company_id', $companyId)
            ->where('id', $request->purchase_id)
            ->first();

        if (!$po) {
            return redirect()->back()->with('error', 'Purchase invoice not found.');
        }

        // Check if any mobile devices from this PO have already been sold
        $soldDevices = DB::table('ms_mobile_devices')
            ->where('company_id', $companyId)
            ->where('purchase_order_id', $po->id)
            ->where(function($q) {
                $q->where('status', 'sold')
                  ->orWhereNotNull('sale_id');
            })
            ->count();

        if ($soldDevices > 0) {
            return redirect()->back()->with('error', "Cannot delete purchase #{$po->po_number}: {$soldDevices} device(s) from this purchase order have already been sold to customers. Please void the customer sale first.");
        }

        return DB::transaction(function () use ($companyId, $po) {
            // 1. Delete unsold mobile devices linked to this PO
            DB::table('ms_mobile_devices')
                ->where('company_id', $companyId)
                ->where('purchase_order_id', $po->id)
                ->delete();

            // 2. Reverse stock in ms_parts_inventory for line items
            $lineItems = DB::table('ms_purchase_order_items')
                ->where('purchase_order_id', $po->id)
                ->get();

            foreach ($lineItems as $item) {
                $qty = (int) ($item->qty ?? 1);
                $itemName = trim($item->model ?? '');
                $itemBrand = trim($item->brand ?? '');

                // Try to find the matching part by name
                $part = DB::table('ms_parts_inventory')
                    ->where('company_id', $companyId)
                    ->where(function($q) use ($itemName, $itemBrand) {
                        $q->where('name', $itemName)
                          ->orWhere('name', trim($itemBrand . ' ' . $itemName));
                    })
                    ->first();

                if ($part) {
                    $newStock = max(0, (int)$part->stock_qty - $qty);
                    DB::table('ms_parts_inventory')->where('id', $part->id)->update([
                        'stock_qty'  => $newStock,
                        'updated_at' => now(),
                    ]);

                    DB::table('ms_parts_inventory_history')->insert([
                        'part_id'       => $part->id,
                        'type'          => 'deduction',
                        'quantity'      => $qty,
                        'balance_after' => $newStock,
                        'reference'     => "DELETED PURCHASE #{$po->po_number}: Stock reversed by " . (auth()->user()?->name ?? 'Admin'),
                        'user_id'       => auth()->id(),
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                }
            }

            // 3. Delete defective item logs linked to this PO
            DB::table('ms_defective_items')
                ->where('company_id', $companyId)
                ->where('purchase_order_id', $po->id)
                ->delete();

            // 4. Delete goods receipts linked to this PO
            $grnIds = DB::table('ms_goods_receipts')
                ->where('company_id', $companyId)
                ->where('purchase_order_id', $po->id)
                ->pluck('id');
            if ($grnIds->isNotEmpty()) {
                DB::table('ms_goods_receipt_items')->whereIn('goods_receipt_id', $grnIds)->delete();
                DB::table('ms_goods_receipts')->whereIn('id', $grnIds)->delete();
            }

            // 5. Delete supplier payments linked to this PO
            DB::table('ms_supplier_payments')
                ->where('company_id', $companyId)
                ->where('purchase_order_id', $po->id)
                ->delete();

            // 6. Delete PO items
            DB::table('ms_purchase_order_items')->where('purchase_order_id', $po->id)->delete();

            // 7. Delete PO
            DB::table('ms_purchase_orders')->where('id', $po->id)->delete();

            return redirect()->back()->with('success', "Purchase invoice #{$po->po_number} and associated stock have been deleted successfully.");
        });
    }
}

