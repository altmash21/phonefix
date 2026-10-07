<?php

namespace App\Http\Controllers\MobileShop;

use App\Services\MobileShop\Common\PendingJobService;
use App\Services\MobileShop\Sales\PosSaleService;
use App\Services\MobileShop\Sales\MultiSaleService;
use App\Services\MobileShop\Sales\SaleVoidService;
use App\Services\MobileShop\Sales\WhatsAppReceiptService;
use App\Services\MobileShop\Accessories\AccessorySaleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Barryvdh\DomPDF\Facade\Pdf;

class SalesController extends BaseMobileShopController
{
    protected PosSaleService $posSaleService;
    protected MultiSaleService $multiSaleService;
    protected SaleVoidService $saleVoidService;
    protected WhatsAppReceiptService $whatsAppService;

    public function __construct(
        ?PosSaleService $posSaleService = null,
        ?MultiSaleService $multiSaleService = null,
        ?SaleVoidService $saleVoidService = null,
        ?WhatsAppReceiptService $whatsAppService = null
    ) {
        $this->posSaleService = $posSaleService ?? new PosSaleService();
        $this->multiSaleService = $multiSaleService ?? new MultiSaleService();
        $this->saleVoidService = $saleVoidService ?? new SaleVoidService();
        $this->whatsAppService = $whatsAppService ?? new WhatsAppReceiptService();
    }

    /**
     * Sales Hub — Accessories & parts sales register.
     */
    public function salesHub(Request $request)
    {
        $companyId = $this->getCompanyId();
        $niche     = $this->getUserNiche();
        $user      = auth()->user();

        $canCreateAccessories = $user->can('create-sale-accessories') || $user->hasRole('accessories-manager') || $user->hasRole('accessories-staff') || $user->hasRole('admin') || $user->hasRole('store-admin');
        $canCreateCovers      = $user->can('create-sale-covers') || $canCreateAccessories;

        // Fetch itemized accessory sales
        $query = DB::table('ms_accessory_sales')
            ->leftJoin('ms_customers', 'ms_accessory_sales.customer_id', '=', 'ms_customers.id')
            ->select('ms_accessory_sales.*', 'ms_customers.name as customer_name', 'ms_customers.phone as customer_phone')
            ->where('ms_accessory_sales.company_id', $companyId)
            ->where('ms_accessory_sales.status', '!=', 'voided');

        if ($niche === 'covers') {
            $coverCats = $this->coverCategories;
            $query->whereExists(function($q) use ($coverCats) {
                $q->from('ms_accessory_sale_items')
                  ->whereColumn('ms_accessory_sale_items.accessory_sale_id', 'ms_accessory_sales.id')
                  ->join('ms_parts_inventory', 'ms_accessory_sale_items.part_id', '=', 'ms_parts_inventory.id')
                  ->whereIn('ms_parts_inventory.category', $coverCats);
            });
        }

        $accSales = $query->orderBy('ms_accessory_sales.id', 'desc')->limit(200)->get();

        // Attach line items to accessory sales for return modal & item details
        if ($accSales->isNotEmpty()) {
            $saleIds = $accSales->pluck('id')->toArray();
            $allItems = DB::table('ms_accessory_sale_items')
                ->where('company_id', $companyId)
                ->whereIn('accessory_sale_id', $saleIds)
                ->select('id', 'accessory_sale_id', 'part_id', 'part_name', 'quantity', 'unit_price', 'line_total')
                ->get()
                ->groupBy('accessory_sale_id');

            foreach ($accSales as $as) {
                $as->items = $allItems->get($as->id, collect())->values();
            }
        }

        // Aggregated KPIs (Cached for 60s)
        $kpiData = \Illuminate\Support\Facades\Cache::remember("ms_sales_kpis_{$companyId}_{$niche}", 60, function () use ($companyId) {
            $todayStart = now()->startOfDay();
            $monthStart = now()->startOfMonth();

            $todaySalesTotal = (float) DB::table('ms_accessory_sales')
                ->where('company_id', $companyId)
                ->where('created_at', '>=', $todayStart)
                ->where('status', '!=', 'voided')
                ->sum('total_amount');

            $monthSalesTotal = (float) DB::table('ms_accessory_sales')
                ->where('company_id', $companyId)
                ->where('created_at', '>=', $monthStart)
                ->where('status', '!=', 'voided')
                ->sum('total_amount');

            $availableParts = DB::table('ms_parts_inventory')
                ->where('company_id', $companyId)
                ->where('stock_qty', '>', 0)
                ->sum('stock_qty');

            return compact('todaySalesTotal', 'monthSalesTotal', 'availableParts');
        });

        $todaySalesTotal     = $kpiData['todaySalesTotal'];
        $monthSalesTotal     = $kpiData['monthSalesTotal'];
        $availableParts      = $kpiData['availableParts'];
        $salesCount          = $accSales->count();

        // Cached customer picker
        $customers = \Illuminate\Support\Facades\Cache::remember("ms_customers_picker_v2_{$companyId}", 120, function () use ($companyId) {
            return DB::table('ms_customers')
                ->where('company_id', $companyId)
                ->select('id', 'name', 'phone', 'gstin', 'address', 'udhari_balance')
                ->orderBy('name')
                ->limit(300)
                ->get();
        });

        $categories = \Illuminate\Support\Facades\Cache::remember("ms_categories_picker_v2_{$companyId}", 300, function () use ($companyId) {
            return DB::table('ms_part_categories')
                ->where('company_id', $companyId)
                ->select('id', 'name', 'slug')
                ->orderBy('name', 'asc')
                ->get();
        });

        $parts = DB::table('ms_parts_inventory')
            ->where('company_id', $companyId)
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'selling_price', 'stock_qty', 'category']);

        $isOwner = $this->isOwner();

        return view('mobileshop.sales', compact(
            'niche', 'accSales', 'isOwner',
            'canCreateAccessories', 'canCreateCovers',
            'todaySalesTotal', 'monthSalesTotal', 'salesCount',
            'availableParts', 'customers', 'categories', 'parts'
        ));
    }

    /**
     * Counter POS Screen — Redirects to Accessories POS
     */
    public function pos()
    {
        return redirect()->route('mobileshop.accessories.pos');
    }

    /**
     * Process Brand New Mobile Sale
     */
    public function processSale(Request $request)
    {
        Gate::authorize('sale.create');
        
        $request->validate([
            'customer_phone' => 'required|string|min:7|max:20',
            'customer_name'  => 'required|string|min:2|max:100',
            'device_id'      => 'required|exists:ms_mobile_devices,id',
            'sale_price'     => 'required|numeric|min:1',
            'amount_paid'    => 'required|numeric|min:0',
            'payment_mode'   => 'required|in:cash,upi,card,bank_transfer,emi,credit_udhari,split',
        ]);

        $companyId = $this->getCompanyId();
        $result = $this->posSaleService->process($request, $companyId);

        if (!$result['success']) {
            return redirect()->back()->with('error', $result['error']);
        }

        if (!empty($result['idempotent'])) {
            return redirect()->route('mobileshop.invoice', ['id' => $result['sale_id']])
                ->with('success', "Sale invoice #{$result['invoice_number']} already processed (Idempotent response).");
        }

        // Asynchronously pre-generate invoice PDF and WhatsApp receipt in background
        PendingJobService::queueInvoicePdf($companyId, (int) $result['sale_id'], (string) $result['invoice_number']);
        if (!empty($request->customer_phone)) {
            PendingJobService::queueWhatsAppReceipt(
                $companyId,
                (string) $request->customer_phone,
                (string) $request->customer_name,
                (string) $result['invoice_number'],
                (float) $request->sale_price
            );
        }

        return redirect()->route('mobileshop.invoice', ['id' => $result['sale_id']])
            ->with('success', "Sale #{$result['invoice_number']} successfully recorded!");
    }

    /**
     * Invoice View (Dual-Format: 80mm Thermal & A4 Tax Invoice)
     */
    public function invoice($id)
    {
        Gate::authorize('sale.printInvoice');

        $data = $this->resolvePhoneSaleDetails($this->getCompanyId(), (int) $id);
        return view('mobileshop.invoice', $data);
    }

    /**
     * Download Mobile Sales Invoice as Direct PDF
     */
    public function invoicePdf($id)
    {
        Gate::authorize('sale.printInvoice');

        $data = $this->resolvePhoneSaleDetails($this->getCompanyId(), (int) $id);
        $pdf = Pdf::loadView('mobileshop.pdf.phone_invoice', $data);
        $pdf->setPaper('a4', 'portrait');
        return $pdf->download("Invoice-{$data['sale']->invoice_number}.pdf");
    }

    /**
     * Void / Cancel a Mobile Device Sale (Sales Return)
     */
    public function voidMobileSale(Request $request, $id)
    {
        Gate::authorize('sale.void');

        $request->validate([
            'void_reason' => 'nullable|string',
            'return_reason_code' => 'nullable|string',
        ]);

        $companyId = $this->getCompanyId();
        $shouldRestock = $request->has('should_restock') ? (bool) $request->should_restock : true;
        $reasonLabel   = $request->input('reason_label') ?: ($request->input('return_reason_code') ?: 'Customer Return');
        $customDetails = $request->input('void_reason') ? " — {$request->input('void_reason')}" : "";
        $auditReason   = "{$reasonLabel}{$customDetails}";

        // OTP Security Gate: Only owner can void without OTP
        $itemRef = "sale:{$id}";
        if (!$this->isOwner()) {
            $otpCode = $request->input('otp_code');
            if (!$this->verifyOtp($companyId, 'void_sale', $itemRef, $otpCode)) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'otp_required' => true,
                        'action' => 'void_sale',
                        'item_reference' => $itemRef,
                        'message' => 'Store Owner OTP authorization is required to void sales invoices.',
                    ], 403);
                }
                return redirect()->back()->with('error', 'Store Owner OTP authorization is required to void sales invoices.');
            }
        }

        $result = $this->saleVoidService->void((int)$id, $companyId, $shouldRestock, $auditReason, auth()->id());

        if (!$result['success']) {
            return redirect()->back()->with('error', $result['error']);
        }

        return redirect()->back()->with('success', "Sale #{$result['invoice_number']} has been Returned and device processed.");
    }

    /**
     * Dispatcher: Unified Sale Store (Supports Single Item and Multi-Item Quick Sales)
     */
    public function storeSale(Request $request)
    {
        $companyId = $this->getCompanyId();

        // Check if multi-item submission is provided
        if ($request->has('items') && is_array($request->input('items'))) {
            $rawItems = $request->input('items');
            $cleanedItems = [];
            $computedTotal = 0.0;

            foreach ($rawItems as $idx => $item) {
                $pId = (int) ($item['part_id'] ?? 0);
                $pName = trim((string) ($item['part_name'] ?? $item['item_name'] ?? $item['name'] ?? ''));
                $qty = (int) ($item['quantity'] ?? 1);
                if ($qty < 1) {
                    return redirect()->back()->withInput()->with('error', "Quantity must be at least 1 for item row #" . ($idx + 1) . ".");
                }

                $uPrice = isset($item['unit_price']) && $item['unit_price'] !== '' ? (float) $item['unit_price'] : (isset($item['custom_price']) && $item['custom_price'] !== '' ? (float) $item['custom_price'] : null);
                if ($uPrice !== null && $uPrice < 0) {
                    return redirect()->back()->withInput()->with('error', "Price cannot be negative for item '{$pName}'.");
                }

                // Auto-resolve or register inventory item if part_id missing
                if (!$pId && !empty($pName)) {
                    $existing = DB::table('ms_parts_inventory')
                        ->where('company_id', $companyId)
                        ->where('name', $pName)
                        ->first();
                    if ($existing) {
                        $pId = (int) $existing->id;
                        if ($uPrice === null) {
                            $uPrice = (float) ($existing->selling_price ?? 0);
                        }
                    } else {
                        $pPrice = $uPrice !== null ? max(0, $uPrice) : 0.0;
                        $pId = DB::table('ms_parts_inventory')->insertGetId([
                            'company_id'       => $companyId,
                            'name'             => $pName,
                            'category'         => 'general_accessory',
                            'brand'            => 'Universal',
                            'compatible_model' => 'Universal',
                            'display_type'     => 'na',
                            'hsn_code'         => '85177090',
                            'unit_cost'        => 0.00,
                            'selling_price'    => $pPrice,
                            'stock_qty'        => max(10, $qty),
                            'min_stock_alert'  => 3,
                            'created_at'       => now(),
                            'updated_at'       => now(),
                        ]);
                    }
                }

                if ($pId > 0) {
                    if ($uPrice === null) {
                        $part = DB::table('ms_parts_inventory')
                            ->where('company_id', $companyId)
                            ->where('id', $pId)
                            ->first();
                        $uPrice = (float) ($part->selling_price ?? 0);
                    }
                    $uPrice = max(0, (float) $uPrice);
                    $lineTotal = round($uPrice * $qty, 2);
                    $computedTotal += $lineTotal;

                    $cleanedItems[] = [
                        'part_id'    => $pId,
                        'part_name'  => $pName,
                        'quantity'   => $qty,
                        'unit_price' => $uPrice,
                    ];
                }
            }

            if (empty($cleanedItems)) {
                return redirect()->back()->withInput()->with('error', 'Please enter at least one valid item to sell.');
            }
            if ($computedTotal <= 0) {
                return redirect()->back()->withInput()->with('error', 'Total sale amount must be greater than zero.');
            }

            $mode = strtolower(trim((string) $request->input('payment_mode', 'cash')));
            if ($mode === 'upi+cash') $mode = 'cash+upi';

            $phone = trim((string) $request->input('customer_phone', ''));
            if (empty($phone)) $phone = '9999999999';
            $name = trim((string) $request->input('customer_name', ''));
            if (empty($name)) $name = 'Walk-in Customer';

            if ($mode === 'udhari') {
                $amountPaid = 0.00;
            } elseif ($mode === 'cash+udhari') {
                $amountPaid = (float) ($request->input('cash_amount') ?? $request->input('amount_paid') ?? 0);
                if ($amountPaid < 0) {
                    return redirect()->back()->withInput()->with('error', 'Cash paid cannot be negative.');
                }
                if ($amountPaid > $computedTotal) {
                    return redirect()->back()->withInput()->with('error', "Cash paid (₹{$amountPaid}) cannot exceed total sale amount (₹{$computedTotal}).");
                }
            } elseif ($mode === 'upi+udhari') {
                $amountPaid = (float) ($request->input('upi_amount') ?? $request->input('amount_paid') ?? 0);
                if ($amountPaid < 0) {
                    return redirect()->back()->withInput()->with('error', 'UPI paid cannot be negative.');
                }
                if ($amountPaid > $computedTotal) {
                    return redirect()->back()->withInput()->with('error', "UPI paid (₹{$amountPaid}) cannot exceed total sale amount (₹{$computedTotal}).");
                }
            } elseif (in_array($mode, ['cash+upi', 'upi+cash'])) {
                $cashAmt = (float) ($request->input('cash_amount') ?? 0);
                $upiAmt = (float) ($request->input('upi_amount') ?? 0);
                if ($cashAmt > 0 || $upiAmt > 0) {
                    if (abs(($cashAmt + $upiAmt) - $computedTotal) > 0.05) {
                        return redirect()->back()->withInput()->with('error', "Split Cash (₹{$cashAmt}) + UPI (₹{$upiAmt}) does not match total amount (₹{$computedTotal}). Please adjust split.");
                    }
                }
                $amountPaid = $computedTotal;
            } else {
                $amountPaid = $computedTotal;
            }

            $request->merge([
                'items'          => $cleanedItems,
                'sale_type'      => 'accessory',
                'customer_phone' => $phone,
                'customer_name'  => $name,
                'amount_paid'    => $amountPaid,
                'payment_mode'   => $mode,
            ]);

            try {
                return app(AccessoriesController::class)->sellAccessory($request);
            } catch (\Throwable $e) {
                return redirect()->back()->withInput()->with('error', 'Sale failed: ' . $e->getMessage());
            }
        }

        // Support Quick Sale single-item direct submission fallback
        $partId = (int) $request->input('part_id');
        $itemName = trim((string) ($request->input('item_name') ?? $request->input('name') ?? ''));

        // If part_id is empty, resolve by item name or auto-register in inventory
        if (!$partId && !empty($itemName)) {
            $existing = DB::table('ms_parts_inventory')
                ->where('company_id', $companyId)
                ->where('name', $itemName)
                ->first();
            if ($existing) {
                $partId = (int) $existing->id;
            } else {
                $customPrice = (float) ($request->input('custom_price') ?: 0);
                $partId = DB::table('ms_parts_inventory')->insertGetId([
                    'company_id'       => $companyId,
                    'name'             => $itemName,
                    'category'         => 'general_accessory',
                    'brand'            => 'Universal',
                    'compatible_model' => 'Universal',
                    'display_type'     => 'na',
                    'hsn_code'         => '85177090',
                    'unit_cost'        => 0.00,
                    'selling_price'    => max(0, $customPrice),
                    'stock_qty'        => max(10, (int) $request->input('quantity', 1)),
                    'min_stock_alert'  => 3,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }
            $request->merge(['part_id' => $partId]);
        }

        if ($partId > 0 && !$request->has('items')) {
            $qty = max(1, (int) ($request->input('quantity', 1) ?: 1));
            
            // Get item price if custom_price not provided
            $customPrice = $request->input('custom_price');
            if ($customPrice !== null && $customPrice !== '') {
                $unitPrice = max(0, (float) $customPrice);
            } else {
                $part = DB::table('ms_parts_inventory')
                    ->where('company_id', $companyId)
                    ->where('id', $partId)
                    ->first();
                $unitPrice = max(0, (float) ($part->selling_price ?? 0));
            }

            $total = round($unitPrice * $qty, 2);
            $mode = strtolower(trim((string) $request->input('payment_mode', 'cash')));
            if ($mode === 'upi+cash') $mode = 'cash+upi';

            // Determine amount_paid
            if ($request->filled('amount_paid') && (float)$request->input('amount_paid') > 0) {
                $amountPaid = (float) $request->input('amount_paid');
            } elseif ($mode === 'udhari') {
                $amountPaid = 0.00;
            } elseif (in_array($mode, ['cash+udhari', 'upi+udhari'])) {
                $amountPaid = (float) ($request->input('split_paid_amount') ?? $request->input('cash_amount') ?? $request->input('upi_amount') ?? 0);
            } else {
                $amountPaid = $total;
            }

            $items = [
                [
                    'part_id'    => $partId,
                    'quantity'   => $qty,
                    'unit_price' => $unitPrice,
                ]
            ];

            $phone = trim((string) $request->input('customer_phone', ''));
            if (empty($phone)) {
                $phone = '9999999999';
            }
            $name = trim((string) $request->input('customer_name', ''));
            if (empty($name)) {
                $name = 'Walk-in Customer';
            }

            $request->merge([
                'items'          => $items,
                'sale_type'      => 'accessory',
                'customer_phone' => $phone,
                'customer_name'  => $name,
                'amount_paid'    => $amountPaid,
                'payment_mode'   => $mode,
            ]);
        }

        if ($request->has('items') || $request->input('sale_type') === 'accessory') {
            return app(AccessoriesController::class)->sellAccessory($request);
        }
        if ($request->input('device_type') === 'second_hand' || $request->input('type') === 'second_hand') {
            return app(StockController::class)->sellSecondHand($request);
        }
        return $this->processSale($request);
    }

    /**
     * Dedicated Bulk Multi-Customer Sale Page (Mirrors Bulk Purchase Architecture)
     */
    public function bulkSaleView(Request $request)
    {
        abort_unless(auth()->check() && (
            auth()->user()->can('create-sale-accessories') || 
            auth()->user()->can('create-sale-covers') || 
            auth()->user()->can('read-mobileshop-sales') || 
            auth()->user()->hasRole('admin') || 
            auth()->user()->hasRole('store-admin') || 
            auth()->user()->hasRole('accessories-staff') || 
            auth()->user()->hasRole('accessories-manager') ||
            auth()->user()->hasRole('owner') ||
            $this->isOwner()
        ), 403, 'Unauthorized access to bulk sales.');

        $companyId = $this->getCompanyId();
        $niche     = $this->getUserNiche();

        $customers = DB::table('ms_customers')
            ->where('company_id', $companyId)
            ->orderBy('name', 'asc')
            ->get();

        $parts = DB::table('ms_parts_inventory')
            ->where('company_id', $companyId)
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'selling_price', 'stock_qty', 'category', 'compatible_model']);

        $categories = DB::table('ms_categories')
            ->where('company_id', $companyId)
            ->orderBy('name', 'asc')
            ->get();

        return view('mobileshop.bulk_sale', compact(
            'customers', 'parts', 'categories', 'niche'
        ));
    }

    /**
     * Store Bulk Sales (Multiple Customers & Multiple Items in One Batch)
     */
    public function storeBulkSales(Request $request)
    {
        $companyId = $this->getCompanyId();
        $storeState = $this->getStoreStateCode();
        $rawSales = $request->input('sales', []);

        // Also accept JSON string if sent as payload
        if (is_string($rawSales)) {
            $rawSales = json_decode($rawSales, true) ?: [];
        }

        if (!is_array($rawSales) || count($rawSales) === 0) {
            $emptyMsg = 'No customer sales provided. Please enter at least one customer sale entry.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $emptyMsg], 422);
            }
            return redirect()->back()->with('error', $emptyMsg);
        }

        $createdSales = [];
        $totalAmountAll = 0.0;

        DB::beginTransaction();
        try {
            foreach ($rawSales as $index => $saleData) {
                $custNum = $index + 1;
                $custName = trim((string) ($saleData['customer_name'] ?? 'Walk-in Customer'));
                if (empty($custName)) $custName = 'Walk-in Customer';
                $custPhone = trim((string) ($saleData['customer_phone'] ?? '9999999999'));
                if (empty($custPhone)) $custPhone = '9999999999';

                $mode = strtolower(trim((string) ($saleData['payment_mode'] ?? 'cash')));
                if ($mode === 'upi+cash') $mode = 'cash+upi';

                $saleItems = $saleData['items'] ?? [];
                if (!is_array($saleItems) || count($saleItems) === 0) {
                    throw new \InvalidArgumentException("Customer #{$custNum} ({$custName}) has no items. Please add at least one item or remove this customer.");
                }

                $cleanedItems = [];
                $saleTotal = 0.0;

                foreach ($saleItems as $rIdx => $it) {
                    $itemNum = $rIdx + 1;
                    $pId = (int) ($it['part_id'] ?? 0);
                    $pName = trim((string) ($it['part_name'] ?? $it['item_name'] ?? $it['name'] ?? ''));
                    if (empty($pName) && !$pId) {
                        throw new \InvalidArgumentException("Customer #{$custNum} ({$custName}), Item #{$itemNum}: Item name is required.");
                    }

                    $qty = (int) ($it['quantity'] ?? 1);
                    if ($qty < 1) {
                        throw new \InvalidArgumentException("Customer #{$custNum} ({$custName}), Item #{$itemNum}: Quantity must be at least 1.");
                    }

                    $uPrice = isset($it['unit_price']) && $it['unit_price'] !== '' ? (float) $it['unit_price'] : (isset($it['custom_price']) && $it['custom_price'] !== '' ? (float) $it['custom_price'] : null);
                    if ($uPrice !== null && $uPrice < 0) {
                        throw new \InvalidArgumentException("Customer #{$custNum} ({$custName}), Item '{$pName}': Price cannot be negative.");
                    }

                    if (!$pId && !empty($pName)) {
                        $existing = DB::table('ms_parts_inventory')
                            ->where('company_id', $companyId)
                            ->where('name', $pName)
                            ->first();
                        if ($existing) {
                            $pId = (int) $existing->id;
                            if ($uPrice === null) $uPrice = (float) ($existing->selling_price ?? 0);
                        } else {
                            $pPrice = $uPrice !== null ? max(0, $uPrice) : 0.0;
                            $pId = DB::table('ms_parts_inventory')->insertGetId([
                                'company_id'       => $companyId,
                                'name'             => $pName,
                                'category'         => 'general_accessory',
                                'brand'            => 'Universal',
                                'compatible_model' => 'Universal',
                                'display_type'     => 'na',
                                'hsn_code'         => '85177090',
                                'unit_cost'        => 0.00,
                                'selling_price'    => $pPrice,
                                'stock_qty'        => max(10, $qty),
                                'min_stock_alert'  => 3,
                                'created_at'       => now(),
                                'updated_at'       => now(),
                            ]);
                        }
                    }

                    if ($pId > 0) {
                        if ($uPrice === null) {
                            $part = DB::table('ms_parts_inventory')->where('company_id', $companyId)->where('id', $pId)->first();
                            $uPrice = (float) ($part->selling_price ?? 0);
                        }
                        $uPrice = max(0, (float) $uPrice);
                        $lTot = round($uPrice * $qty, 2);
                        $saleTotal += $lTot;
                        $cleanedItems[] = [
                            'part_id'    => $pId,
                            'part_name'  => $pName,
                            'quantity'   => $qty,
                            'unit_price' => $uPrice,
                        ];
                    }
                }

                if (empty($cleanedItems)) {
                    throw new \InvalidArgumentException("Customer #{$custNum} ({$custName}): Please provide valid item details.");
                }
                if ($saleTotal <= 0) {
                    throw new \InvalidArgumentException("Customer #{$custNum} ({$custName}): Total bill must be greater than zero.");
                }

                // Compute and validate amount_paid
                if ($mode === 'udhari') {
                    $amountPaid = 0.00;
                } elseif ($mode === 'cash+udhari') {
                    $amountPaid = (float) ($saleData['cash_amount'] ?? $saleData['amount_paid'] ?? 0);
                    if ($amountPaid < 0) {
                        throw new \InvalidArgumentException("Customer #{$custNum} ({$custName}): Cash paid cannot be negative.");
                    }
                    if ($amountPaid > $saleTotal) {
                        throw new \InvalidArgumentException("Customer #{$custNum} ({$custName}): Cash paid (₹" . number_format($amountPaid, 2) . ") cannot exceed the total bill (₹" . number_format($saleTotal, 2) . ").");
                    }
                } elseif ($mode === 'upi+udhari') {
                    $amountPaid = (float) ($saleData['upi_amount'] ?? $saleData['amount_paid'] ?? 0);
                    if ($amountPaid < 0) {
                        throw new \InvalidArgumentException("Customer #{$custNum} ({$custName}): UPI paid cannot be negative.");
                    }
                    if ($amountPaid > $saleTotal) {
                        throw new \InvalidArgumentException("Customer #{$custNum} ({$custName}): UPI paid (₹" . number_format($amountPaid, 2) . ") cannot exceed the total bill (₹" . number_format($saleTotal, 2) . ").");
                    }
                } elseif (in_array($mode, ['cash+upi', 'upi+cash'])) {
                    $cPaid = (float) ($saleData['cash_amount'] ?? 0);
                    $uPaid = (float) ($saleData['upi_amount'] ?? 0);
                    if ($cPaid <= 0 && $uPaid <= 0) {
                        $cPaid = round($saleTotal / 2, 2);
                        $uPaid = round($saleTotal - $cPaid, 2);
                    }
                    if (abs(($cPaid + $uPaid) - $saleTotal) > 0.05) {
                        throw new \InvalidArgumentException("Customer #{$custNum} ({$custName}): Split Cash (₹{$cPaid}) + UPI (₹{$uPaid}) must equal total bill (₹{$saleTotal}).");
                    }
                    $amountPaid = $saleTotal;
                } else {
                    $amountPaid = $saleTotal;
                }

                $subReq = new Request([
                    'company_id'     => $companyId,
                    'customer_phone' => $custPhone,
                    'customer_name'  => $custName,
                    'items'          => $cleanedItems,
                    'amount_paid'    => $amountPaid,
                    'payment_mode'   => $mode,
                    'sale_type'      => 'accessory',
                ]);

                $res = app(AccessorySaleService::class)->sellAccessory($companyId, $subReq, $storeState);
                $createdSales[] = [
                    'sale_id'        => $res['sale_id'],
                    'invoice_number' => $res['invoice_number'],
                    'customer_name'  => $custName,
                    'amount'         => $saleTotal,
                    'mode'           => $mode,
                ];
                $totalAmountAll += $saleTotal;
            }

            if (empty($createdSales)) {
                DB::rollBack();
                $msg = 'No valid sales were processed. Please check items and quantities.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }
                return redirect()->back()->with('error', $msg);
            }

            DB::commit();

            $count = count($createdSales);
            $invoices = collect($createdSales)->pluck('invoice_number')->implode(', ');
            $successMsg = "{$count} bulk sales recorded successfully! (Invoices: {$invoices}) Total: ₹" . number_format($totalAmountAll, 2);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMsg,
                    'count'   => $count,
                    'total'   => $totalAmountAll,
                    'sales'   => $createdSales,
                ]);
            }

            return redirect()->route('mobileshop.sales')->with('success', $successMsg);
        } catch (\Throwable $e) {
            DB::rollBack();
            $errorMsg = $e->getMessage();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 422);
            }
            return redirect()->back()->with('error', $errorMsg);
        }
    }

    /**
     * Sale Registration Page — Redirects to Accessories POS
     */
    public function saleCreate()
    {
        return redirect()->route('mobileshop.accessories.pos');
    }

    /**
     * Store Multi-Device Sale — creates one sale invoice per device for the
     * same customer; unpaid total flows to customer Khata (udhari).
     */
    public function storeMultiSale(Request $request)
    {
        Gate::authorize('sale.create');

        $request->validate([
            'customer_phone'   => 'required|string|min:7|max:20',
            'customer_name'    => 'required|string|min:2|max:100',
            'device_ids'       => 'required|array|min:1',
            'device_ids.*'     => 'exists:ms_mobile_devices,id',
            'sale_prices'      => 'required|array',
            'amount_paid'      => 'required|numeric|min:0',
            'payment_mode'     => 'required|in:cash,online,upi,card,bank_transfer,emi,credit_udhari,split',
        ]);

        $companyId = $this->getCompanyId();
        $result = $this->multiSaleService->store($request, $companyId);

        if (!$result['success']) {
            return redirect()->back()->with('error', $result['error']);
        }

        return redirect()->route('mobileshop.invoice', ['id' => $result['first_invoice']])
            ->with('success', $result['count'] . " sale invoice recorded for {$result['customer_name']}. Total: ₹" . number_format($result['bill_total'], 2)
                . ($result['is_emi'] ? " (Financed: ₹" . number_format($result['emi_financed'], 2) . ", DP: ₹" . number_format($result['amount_paid'], 2) . ")" : "")
                . ($result['udhari_total'] > 0 ? ", Added to Khata: ₹" . number_format($result['udhari_total'], 2) : "")
                . ($result['gift_name'] ? ", Free Gift: {$result['gift_name']}" : "") . ".");
    }

    /**
     * Secure WhatsApp share redirect.
     * Generates wa.me redirect on-demand so customer phone numbers are not exposed in HTML DOM.
     */
    public function shareWhatsApp(int $id)
    {
        $companyId = $this->getCompanyId();
        $url = $this->whatsAppService->buildUrl($id, $companyId);

        if (!$url) {
            abort(404, 'Sale record not found.');
        }

        return redirect()->away($url);
    }
}
