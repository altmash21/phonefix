<?php

namespace App\Http\Controllers\MobileShop;

use App\Services\MobileShop\Common\PendingJobService;
use App\Services\MobileShop\Sales\EmiBillScannerService;
use App\Services\MobileShop\Sales\PosSaleService;
use App\Services\MobileShop\Sales\MultiSaleService;
use App\Services\MobileShop\Sales\SaleVoidService;
use App\Services\MobileShop\Sales\WhatsAppReceiptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Barryvdh\DomPDF\Facade\Pdf;

class SalesController extends BaseMobileShopController
{
    protected EmiBillScannerService $emiScannerService;
    protected PosSaleService $posSaleService;
    protected MultiSaleService $multiSaleService;
    protected SaleVoidService $saleVoidService;
    protected WhatsAppReceiptService $whatsAppService;

    public function __construct(
        ?EmiBillScannerService $emiScannerService = null,
        ?PosSaleService $posSaleService = null,
        ?MultiSaleService $multiSaleService = null,
        ?SaleVoidService $saleVoidService = null,
        ?WhatsAppReceiptService $whatsAppService = null
    ) {
        $this->emiScannerService = $emiScannerService ?? new EmiBillScannerService();
        $this->posSaleService = $posSaleService ?? new PosSaleService();
        $this->multiSaleService = $multiSaleService ?? new MultiSaleService();
        $this->saleVoidService = $saleVoidService ?? new SaleVoidService();
        $this->whatsAppService = $whatsAppService ?? new WhatsAppReceiptService();
    }
    /**
     * Sales Hub — Niche-scoped sales list.
     */
    public function salesHub(Request $request)
    {
        $companyId = $this->getCompanyId();
        $niche     = $this->getUserNiche();
        $user      = auth()->user();

        // Determine what this role can create
        $canCreatePhones    = $user->can('create-sale-phones');
        $canCreateSecondhand = $user->can('create-sale-secondhand');
        $canCreateAccessories = $user->can('create-sale-accessories') || $user->hasRole('accessories-manager') || $user->hasRole('accessories-staff');
        $canCreateCovers    = $user->can('create-sale-covers') || $user->hasRole('accessories-manager') || $user->hasRole('accessories-staff');

        // Build niche-scoped sales list
        $mobileSales = collect();
        $accSales    = collect();

        switch ($niche) {
            case 'phones':
                $mobileSales = DB::table('ms_mobile_sales')
                    ->join('ms_customers', 'ms_mobile_sales.customer_id', '=', 'ms_customers.id')
                    ->join('ms_mobile_devices', 'ms_mobile_sales.device_id', '=', 'ms_mobile_devices.id')
                    ->select('ms_mobile_sales.*', 'ms_customers.name as customer_name', 'ms_customers.phone as customer_phone',
                             'ms_mobile_devices.brand', 'ms_mobile_devices.model', 'ms_mobile_devices.imei_1',
                             'ms_mobile_devices.storage', 'ms_mobile_devices.color', 'ms_mobile_devices.ram',
                             DB::raw("'new' as device_type"), DB::raw("'phone' as sale_niche"))
                    ->where('ms_mobile_sales.company_id', $companyId)
                    ->where('ms_mobile_devices.type', 'new')
                    ->where('ms_mobile_sales.status', '!=', 'voided')
                    ->orderBy('ms_mobile_sales.id', 'desc')->limit(150)->get();
                break;

            case 'secondhand':
                $mobileSales = DB::table('ms_mobile_sales')
                    ->join('ms_customers', 'ms_mobile_sales.customer_id', '=', 'ms_customers.id')
                    ->join('ms_mobile_devices', 'ms_mobile_sales.device_id', '=', 'ms_mobile_devices.id')
                    ->select('ms_mobile_sales.*', 'ms_customers.name as customer_name', 'ms_customers.phone as customer_phone',
                             'ms_mobile_devices.brand', 'ms_mobile_devices.model', 'ms_mobile_devices.imei_1',
                             'ms_mobile_devices.storage', 'ms_mobile_devices.color', 'ms_mobile_devices.ram',
                             DB::raw("'second_hand' as device_type"), DB::raw("'secondhand' as sale_niche"))
                    ->where('ms_mobile_sales.company_id', $companyId)
                    ->where('ms_mobile_devices.type', 'second_hand')
                    ->where('ms_mobile_sales.status', '!=', 'voided')
                    ->orderBy('ms_mobile_sales.id', 'desc')->limit(150)->get();
                break;

            case 'accessories':
                $accSales = DB::table('ms_accessory_sales')
                    ->leftJoin('ms_customers', 'ms_accessory_sales.customer_id', '=', 'ms_customers.id')
                    ->select('ms_accessory_sales.*', 'ms_customers.name as customer_name', 'ms_customers.phone as customer_phone',
                             DB::raw("'accessory' as sale_niche"))
                    ->where('ms_accessory_sales.company_id', $companyId)
                    ->where('ms_accessory_sales.status', '!=', 'voided')
                    ->orderBy('ms_accessory_sales.id', 'desc')->limit(150)->get();
                break;

            case 'covers':
                $coverCats = $this->coverCategories;
                $accSales = DB::table('ms_accessory_sales')
                    ->leftJoin('ms_customers', 'ms_accessory_sales.customer_id', '=', 'ms_customers.id')
                    ->select('ms_accessory_sales.*', 'ms_customers.name as customer_name', 'ms_customers.phone as customer_phone',
                             DB::raw("'cover' as sale_niche"))
                    ->where('ms_accessory_sales.company_id', $companyId)
                    ->where('ms_accessory_sales.status', '!=', 'voided')
                    ->whereExists(function($q) use ($coverCats) {
                        $q->from('ms_accessory_sale_items')
                          ->whereColumn('ms_accessory_sale_items.accessory_sale_id', 'ms_accessory_sales.id')
                          ->join('ms_parts_inventory', 'ms_accessory_sale_items.part_id', '=', 'ms_parts_inventory.id')
                          ->whereIn('ms_parts_inventory.category', $coverCats);
                    })
                    ->orderBy('ms_accessory_sales.id', 'desc')->limit(150)->get();
                break;

            default: // admin — all sales
                $mobileSales = DB::table('ms_mobile_sales')
                    ->join('ms_customers', 'ms_mobile_sales.customer_id', '=', 'ms_customers.id')
                    ->join('ms_mobile_devices', 'ms_mobile_sales.device_id', '=', 'ms_mobile_devices.id')
                    ->select('ms_mobile_sales.*', 'ms_customers.name as customer_name', 'ms_customers.phone as customer_phone',
                             'ms_mobile_devices.brand', 'ms_mobile_devices.model', 'ms_mobile_devices.imei_1', 'ms_mobile_devices.type as device_type',
                             'ms_mobile_devices.storage', 'ms_mobile_devices.color', 'ms_mobile_devices.ram')
                    ->where('ms_mobile_sales.company_id', $companyId)
                    ->where('ms_mobile_sales.status', '!=', 'voided')
                    ->orderBy('ms_mobile_sales.id', 'desc')->limit(150)->get();
                $accSales = DB::table('ms_accessory_sales')
                    ->leftJoin('ms_customers', 'ms_accessory_sales.customer_id', '=', 'ms_customers.id')
                    ->select('ms_accessory_sales.*', 'ms_customers.name as customer_name', 'ms_customers.phone as customer_phone')
                    ->where('ms_accessory_sales.company_id', $companyId)
                    ->where('ms_accessory_sales.status', '!=', 'voided')
                    ->orderBy('ms_accessory_sales.id', 'desc')->limit(150)->get();
        }

        // Attach line items to accessory sales for interactive line-item partial return modal
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

        // Aggregated KPIs for page header (Cached for 60s to avoid full-table scans on every page load)
        $kpiData = \Illuminate\Support\Facades\Cache::remember("ms_sales_kpis_{$companyId}_{$niche}", 60, function () use ($companyId, $niche, $mobileSales, $accSales) {
            $todayStart = now()->startOfDay();
            $monthStart = now()->startOfMonth();

            if ($niche === 'admin') {
                $todaySalesTotal = (float) (
                    DB::table('ms_mobile_sales')->where('company_id', $companyId)->where('created_at', '>=', $todayStart)->where('status', '!=', 'voided')->sum('total_amount')
                    + DB::table('ms_accessory_sales')->where('company_id', $companyId)->where('created_at', '>=', $todayStart)->where('status', '!=', 'voided')->sum('total_amount')
                );

                $monthSalesTotal = (float) (
                    DB::table('ms_mobile_sales')->where('company_id', $companyId)->where('created_at', '>=', $monthStart)->where('status', '!=', 'voided')->sum('total_amount')
                    + DB::table('ms_accessory_sales')->where('company_id', $companyId)->where('created_at', '>=', $monthStart)->where('status', '!=', 'voided')->sum('total_amount')
                );
            } else {
                $todaySalesTotal = (float) ($mobileSales->sum('total_amount') + $accSales->sum('total_amount'));
                $monthSalesTotal = $todaySalesTotal;
            }

            $stockDeviceCounts = DB::table('ms_mobile_devices')
                ->where('company_id', $companyId)
                ->where('status', 'in_stock')
                ->select('type', DB::raw('count(*) as c'))
                ->groupBy('type')
                ->pluck('c', 'type');

            $availableNewPhones  = in_array($niche, ['admin', 'phones']) ? ($stockDeviceCounts['new'] ?? 0) : 0;
            $availableSecondHand = in_array($niche, ['admin', 'secondhand']) ? ($stockDeviceCounts['second_hand'] ?? 0) : 0;
            $availableParts      = in_array($niche, ['admin', 'accessories', 'covers'])
                ? DB::table('ms_parts_inventory')->where('company_id', $companyId)->where('stock_qty', '>', 0)->count()
                : 0;

            return compact('todaySalesTotal', 'monthSalesTotal', 'availableNewPhones', 'availableSecondHand', 'availableParts');
        });

        $todaySalesTotal     = $kpiData['todaySalesTotal'];
        $monthSalesTotal     = $kpiData['monthSalesTotal'];
        $availableNewPhones  = $kpiData['availableNewPhones'];
        $availableSecondHand = $kpiData['availableSecondHand'];
        $availableParts      = $kpiData['availableParts'];
        $salesCount          = $mobileSales->count() + $accSales->count();

        // Optimized picker data: cached and selecting only required fields to avoid hydrating massive tables
        $customers = \Illuminate\Support\Facades\Cache::remember("ms_customers_picker_{$companyId}", 120, function () use ($companyId) {
            return DB::table('ms_customers')
                ->where('company_id', $companyId)
                ->select('id', 'name', 'phone')
                ->orderBy('name')
                ->limit(300)
                ->get();
        });

        $partsList = \Illuminate\Support\Facades\Cache::remember("ms_parts_picker_{$companyId}", 60, function () use ($companyId) {
            return DB::table('ms_parts_inventory')
                ->where('company_id', $companyId)
                ->where('stock_qty', '>', 0)
                ->select('id', 'name', 'category', 'compatible_model', 'selling_price', 'stock_qty')
                ->get();
        });

        $categories = \Illuminate\Support\Facades\Cache::remember("ms_categories_picker_{$companyId}", 300, function () use ($companyId) {
            return DB::table('ms_part_categories')
                ->where('company_id', $companyId)
                ->select('id', 'name')
                ->orderBy('name', 'asc')
                ->get();
        });

        $secondHandPhones = \Illuminate\Support\Facades\Cache::remember("ms_sh_picker_{$companyId}", 60, function () use ($companyId) {
            return DB::table('ms_mobile_devices')
                ->where('company_id', $companyId)
                ->where('type', 'second_hand')
                ->where('status', 'in_stock')
                ->select('id', 'brand', 'model', 'imei_1', 'selling_price')
                ->orderBy('brand')
                ->orderBy('model')
                ->get();
        });

        $isOwner = $this->isOwner();

        return view('mobileshop.sales', compact(
            'niche', 'mobileSales', 'accSales', 'isOwner',
            'canCreatePhones', 'canCreateSecondhand', 'canCreateAccessories', 'canCreateCovers',
            'todaySalesTotal', 'monthSalesTotal', 'salesCount',
            'availableNewPhones', 'availableSecondHand', 'availableParts', 'customers', 'partsList', 'categories', 'secondHandPhones'
        ));
    }

    /**
     * Panel 1: New Phones POS Screen — Redirects to Unified Sales Registration
     */
    public function pos()
    {
        return redirect()->route('mobileshop.sales.create');
    }

    /**
     * AI-Powered OCR Scan for EMI Slips, Delivery Challans & Invoices (Gemini 1.5 Flash)
     */
    public function scanEmiBill(Request $request)
    {
        Gate::authorize('sale.viewAny');

        $request->validate([
            'bill_image' => 'required|file|mimes:jpeg,png,jpg,webp,pdf,heic|max:10240',
        ]);

        $companyId = $this->getCompanyId();
        $result = $this->emiScannerService->scan($request, $companyId);
        $statusCode = $result['status_code'] ?? 200;
        unset($result['status_code']);

        return response()->json($result, $statusCode);
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
     * Dispatcher: Unified Sale Store
     */
    public function storeSale(Request $request)
    {
        if ($request->has('items') || $request->input('sale_type') === 'accessory') {
            return app(AccessoriesController::class)->sellAccessory($request);
        }
        if ($request->input('device_type') === 'second_hand' || $request->input('type') === 'second_hand') {
            return app(StockController::class)->sellSecondHand($request);
        }
        return $this->processSale($request);
    }

    /**
     * Sale Registration Page — full page, multi-device selection
     */
    public function saleCreate()
    {
        Gate::authorize('sale.create');

        $companyId = $this->getCompanyId();
        $inStockDevices = DB::table('ms_mobile_devices')
            ->where('company_id', $companyId)
            ->where('type', 'new')
            ->where('status', 'in_stock')
            ->orderBy('brand')->orderBy('model')
            ->get();
        $customers = DB::table('ms_customers')->where('company_id', $companyId)->orderBy('name')->get();
        $emiProviders = DB::table('ms_emi_providers')->where('company_id', $companyId)->where('enabled', 1)->get();
        $giftInventory = DB::table('ms_parts_inventory')
            ->where('company_id', $companyId)
            ->where('stock_qty', '>', 0)
            ->select('id', 'name', 'category', 'brand', 'unit_cost', 'selling_price', 'stock_qty')
            ->orderBy('name')
            ->get();

        return view('mobileshop.sales_create', compact('inStockDevices', 'customers', 'emiProviders', 'giftInventory'));
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
