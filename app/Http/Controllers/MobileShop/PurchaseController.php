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

        return view('mobileshop.purchase', compact(
            'niche', 'isAdmin', 'purchaseInvoices', 'purchaseOrders', 'newPhonePurchases', 'buybacks', 'batchRestocks',
            'canAddPhones', 'canAddSecondhand', 'canAddAccessories', 'canAddCovers',
            'totalPOValue', 'totalPODue', 'totalInvoicesCount', 'totalUnitsPurchased',
            'suppliers', 'wallets', 'categories', 'parts', 'knownBrands', 'knownModels'
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
        abort_unless(auth()->check() && (auth()->user()->can('create-purchase-phones') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin') || auth()->user()->hasRole('sales-staff')), 403, 'Unauthorized access to purchase registration.');

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
        abort_unless(auth()->check() && (auth()->user()->can('create-purchase-phones') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin') || auth()->user()->hasRole('sales-staff')), 403, 'Unauthorized action.');

        $companyId = $this->getCompanyId();
        $result = $this->bulkPurchaseService->storeBulkPurchase($companyId, $request);

        $msg = "Bulk purchase {$result['poNum']} recorded — {$result['deviceCount']} units added to stock.";
        if ($result['balanceDue'] > 0) {
            $msg .= " Balance due to supplier: ₹" . number_format($result['balanceDue'], 2) . " (carried to supplier ledger).";
        }
        return redirect()->route('mobileshop.purchase', ['company_id' => $companyId])->with('success', $msg);
    }

    /**
     * Supplier Purchase Orders Ledger & Inward Inventory
     */
    public function purchaseOrders()
    {
        return redirect()->route('mobileshop.purchase');
    }

    /**
     * Record Supplier Payment / Advance / Settlement
     */
    public function recordSupplierPayment(Request $request)
    {
        abort_unless(auth()->check() && (auth()->user()->can('create-mobileshop-procurement') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin') || auth()->user()->hasRole('sales-staff')), 403, 'Unauthorized action.');

        $result = $this->supplierPaymentService->recordSupplierPayment($this->getCompanyId(), $request);

        if (!$result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }
        return redirect()->route('mobileshop.purchase')->with('success', $result['message']);
    }

    /**
     * Update Supplier details and/or adjust prepaid wallet balance
     */
    public function updateSupplier(Request $request)
    {
        abort_unless(auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin') || auth()->user()->hasRole('sales-staff')), 403, 'Unauthorized action.');

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
            auth()->user()->can('create-purchase-phones') ||
            auth()->user()->can('create-purchase-accessories') ||
            auth()->user()->can('create-purchase-covers') ||
            auth()->user()->hasRole('admin') ||
            auth()->user()->hasRole('store-admin') ||
            auth()->user()->hasRole('sales-staff') ||
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
}
