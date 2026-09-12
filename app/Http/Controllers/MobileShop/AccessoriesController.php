<?php

namespace App\Http\Controllers\MobileShop;

use App\Services\MobileShop\Accessories\AccessoryCategoryService;
use App\Services\MobileShop\Accessories\AccessoryRestockService;
use App\Services\MobileShop\Accessories\AccessorySaleService;
use App\Services\MobileShop\Accessories\AccessoryVoidService;
use App\Services\MobileShop\Sales\WhatsAppReceiptService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccessoriesController extends BaseMobileShopController
{
    protected AccessoryCategoryService $categoryService;
    protected AccessoryRestockService $restockService;
    protected AccessorySaleService $saleService;
    protected AccessoryVoidService $voidService;
    protected WhatsAppReceiptService $whatsAppReceiptService;

    public function __construct(
        ?AccessoryCategoryService $categoryService = null,
        ?AccessoryRestockService $restockService = null,
        ?AccessorySaleService $saleService = null,
        ?AccessoryVoidService $voidService = null,
        ?WhatsAppReceiptService $whatsAppReceiptService = null
    ) {
        $this->categoryService        = $categoryService ?? new AccessoryCategoryService();
        $this->restockService         = $restockService ?? new AccessoryRestockService();
        $this->saleService            = $saleService ?? new AccessorySaleService();
        $this->voidService            = $voidService ?? new AccessoryVoidService();
        $this->whatsAppReceiptService = $whatsAppReceiptService ?? new WhatsAppReceiptService();
    }

    /**
     * Dedicated Full Page: Mobile-App Styled Retail Counter POS (Accessories, Glass & Covers)
     */
    public function counterPos(Request $request)
    {
        abort_unless(auth()->check() && (
            auth()->user()->can('sell-mobileshop-accessories') || 
            auth()->user()->can('create-sale-accessories') || 
            auth()->user()->can('create-mobileshop-accessories') || 
            auth()->user()->can('read-mobileshop-sales') || 
            auth()->user()->can('read-mobileshop-accessories') || 
            auth()->user()->hasRole('admin') || 
            auth()->user()->hasRole('store-admin') || 
            auth()->user()->hasRole('accessories-staff') || 
            auth()->user()->hasRole('accessories-manager') || 
            auth()->user()->hasRole('cover-staff')
        ), 403, 'Unauthorized access to Counter POS.');

        $companyId = $this->getCompanyId();
        $niche     = $this->getUserNiche();
        $presetCategory = $request->query('category') ?? $request->query('niche') ?? '';

        $customers = DB::table('ms_customers')
            ->where('company_id', $companyId)
            ->select('id', 'name', 'phone', 'gstin', 'address', 'udhari_balance')
            ->orderBy('name')
            ->limit(500)
            ->get();

        $categories = DB::table('ms_part_categories')
            ->where('company_id', $companyId)
            ->orderBy('name', 'asc')
            ->get();

        $partsQuery = DB::table('ms_parts_inventory')
            ->where('company_id', $companyId)
            ->where('stock_qty', '>', 0);

        if ($niche === 'covers' || auth()->user()->hasRole('cover-staff')) {
            $partsQuery->whereIn('category', $this->coverCategories);
        }

        $partsList = $partsQuery
            ->select('id', 'name', 'category', 'brand', 'compatible_model', 'selling_price', 'stock_qty')
            ->orderBy('name')
            ->get();

        return view('mobileshop.accessories_pos', compact(
            'customers', 'categories', 'partsList', 'niche', 'presetCategory'
        ));
    }

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
        $categories = $this->categoryService->getCategories($this->getCompanyId());
        return response()->json(['success' => true, 'categories' => $categories]);
    }

    /**
     * Store Custom Part Category
     */
    public function storeCategory(Request $request)
    {
        abort_unless(auth()->check() && (
            auth()->user()->can('create-mobileshop-accessories') || 
            auth()->user()->hasRole('admin') || 
            auth()->user()->hasRole('store-admin') || 
            auth()->user()->hasRole('accessories-staff')
        ), 403, 'Unauthorized action.');

        $result = $this->categoryService->storeCategory($this->getCompanyId(), $request);

        if (!$result['success']) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $result['message']], $result['status'] ?? 422);
            }
            return redirect()->back()->with('error', $result['message'] . '!');
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Category added!', 'id' => $result['id']]);
        }
        return redirect()->route('mobileshop.stock')->with('success', $result['message']);
    }

    /**
     * Delete Custom Part Category (Gated by Owner OTP)
     */
    public function deleteCategory(Request $request, $id)
    {
        abort_unless(auth()->check() && (
            auth()->user()->can('create-mobileshop-accessories') || 
            auth()->user()->hasRole('admin') || 
            auth()->user()->hasRole('store-admin')
        ), 403, 'Unauthorized action.');

        $result = $this->categoryService->deleteCategory(
            $this->getCompanyId(),
            (int) $id,
            $request,
            $this->isOwner(),
            fn($cId, $act, $ref, $otp) => $this->verifyOtp($cId, $act, $ref, $otp)
        );

        if (!$result['success']) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json($result, 403);
            }
            return redirect()->back()->with('error', $result['message']);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $result['message']]);
        }
        return redirect()->route('mobileshop.stock')->with('success', $result['message']);
    }

    /**
     * Store / Restock Part or Accessory
     */
    public function storePart(Request $request)
    {
        abort_unless(auth()->check() && (
            auth()->user()->can('create-mobileshop-accessories') || 
            auth()->user()->hasRole('admin') || 
            auth()->user()->hasRole('store-admin') || 
            auth()->user()->hasRole('accessories-staff')
        ), 403, 'Unauthorized action.');

        $this->restockService->storePart($this->getCompanyId(), $request);

        return $this->safeRedirect($request, 'mobileshop.purchase', [], 'success', 'Part/Accessory successfully added to inventory!');
    }

    /**
     * Bulk Restock & Batch Inflow (Manual or AI OCR Extracted)
     */
    public function bulkRestock(Request $request)
    {
        abort_unless(auth()->check() && (
            auth()->user()->can('create-mobileshop-accessories') || 
            auth()->user()->hasRole('admin') || 
            auth()->user()->hasRole('store-admin') || 
            auth()->user()->hasRole('accessories-staff')
        ), 403, 'Unauthorized action.');

        $summary = $this->restockService->bulkRestock($this->getCompanyId(), $request);

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
        abort_unless(auth()->check() && (
            auth()->user()->can('sell-mobileshop-accessories') || 
            auth()->user()->can('create-sale-accessories') || 
            auth()->user()->can('create-mobileshop-accessories') || 
            auth()->user()->hasRole('admin') || 
            auth()->user()->hasRole('store-admin') || 
            auth()->user()->hasRole('accessories-staff') || 
            auth()->user()->hasRole('accessories-manager')
        ), 403, 'Unauthorized action.');

        $result = $this->saleService->sellAccessory($this->getCompanyId(), $request, $this->getStoreStateCode());

        if (!empty($result['is_duplicate'])) {
            return redirect()->route('mobileshop.accessories.invoice', ['id' => $result['sale_id']])
                ->with('success', "Accessory sale invoice #{$result['invoice_number']} already processed.");
        }

        return redirect()->route('mobileshop.accessories.invoice', ['id' => $result['sale_id']])
            ->with('success', "Accessory sale #{$result['invoice_number']} successfully recorded!");
    }

    /**
     * Accessory Invoice & Receipt View
     */
    public function accessoryInvoice($id)
    {
        abort_unless(auth()->check() && (
            auth()->user()->can('read-mobileshop-accessories') || 
            auth()->user()->hasRole('admin') || 
            auth()->user()->hasRole('store-admin') || 
            auth()->user()->hasRole('accessories-staff')
        ), 403, 'Unauthorized access to invoice.');

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

        $result = $this->voidService->voidAccessorySale(
            $this->getCompanyId(),
            (int) $id,
            $request,
            $this->isOwner(),
            fn($cId, $act, $ref, $otp) => $this->verifyOtp($cId, $act, $ref, $otp)
        );

        if (!$result['success']) {
            if (!empty($result['otp_required'])) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json($result, 403);
                }
                return redirect()->back()->with('error', $result['message']);
            }
            return redirect()->back()->with('error', $result['message']);
        }

        return redirect()->back()->with('success', $result['message']);
    }

    /**
     * Get Part Stock Ledger History (JSON endpoint)
     */
    public function getPartHistory($id)
    {
        abort_unless(auth()->check() && (
            auth()->user()->can('read-mobileshop-accessories') || 
            auth()->user()->hasRole('admin') || 
            auth()->user()->hasRole('store-admin') || 
            auth()->user()->hasRole('accessories-staff')
        ), 403, 'Unauthorized action.');

        $companyId = $this->getCompanyId();

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
