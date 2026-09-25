<?php

namespace App\Http\Controllers\MobileShop;

use App\Services\MobileShop\Common\PendingJobService;
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
     * Dispatcher: Unified Sale Store
     */
    public function storeSale(Request $request)
    {
        // Support Quick Sale single-item direct submission from sales page
        if ($request->filled('part_id') && !$request->has('items')) {
            $partId = (int) $request->input('part_id');
            $qty = max(1, (int) ($request->input('quantity', 1) ?: 1));
            
            // Get item price if custom_price not provided
            $customPrice = $request->input('custom_price');
            if ($customPrice !== null && $customPrice !== '') {
                $unitPrice = (float) $customPrice;
            } else {
                $part = DB::table('ms_parts_inventory')
                    ->where('company_id', $this->getCompanyId())
                    ->where('id', $partId)
                    ->first();
                $unitPrice = (float) ($part->selling_price ?? 0);
            }

            $total = round($unitPrice * $qty, 2);
            $mode = $request->input('payment_mode', 'cash');

            // Determine amount_paid
            if ($request->filled('amount_paid')) {
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
                $phone = '0000000000';
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
