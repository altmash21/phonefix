<?php

namespace App\Http\Controllers\MobileShop;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PublicStoreController extends BaseMobileShopController
{
    /**
     * Public Website — Landing Homepage (Accessories & Express Repair Center)
     */
    public function publicLanding()
    {
        $companyId = company_id() ?? session('company_id') ?? 1;

        $p = DB::getTablePrefix();
        // Categories with part count
        $categories = DB::table('ms_part_categories')
            ->where('ms_part_categories.company_id', $companyId)
            ->leftJoin('ms_parts_inventory', function($join) use ($companyId) {
                $join->on('ms_parts_inventory.category_id', '=', 'ms_part_categories.id')
                     ->where('ms_parts_inventory.company_id', '=', $companyId);
            })
            ->select(
                'ms_part_categories.id',
                'ms_part_categories.name',
                'ms_part_categories.slug',
                DB::raw("COUNT({$p}ms_parts_inventory.id) as items_count")
            )
            ->groupBy('ms_part_categories.id', 'ms_part_categories.name', 'ms_part_categories.slug')
            ->orderBy('items_count', 'desc')
            ->get();

        // Featured accessories for homepage
        $featuredAccessories = DB::table('ms_parts_inventory')
            ->leftJoin('ms_part_categories', 'ms_parts_inventory.category_id', '=', 'ms_part_categories.id')
            ->where('ms_parts_inventory.company_id', $companyId)
            ->select(
                'ms_parts_inventory.*',
                'ms_part_categories.name as category_name',
                'ms_part_categories.slug as category_slug'
            )
            ->orderBy('ms_parts_inventory.stock_qty', 'desc')
            ->limit(8)
            ->get();

        // High-demand Fast Chargers & Cables
        $chargerEssentials = DB::table('ms_parts_inventory')
            ->where('company_id', $companyId)
            ->whereIn('category', ['charger', 'cable', 'power_bank'])
            ->orderBy('id', 'asc')
            ->limit(4)
            ->get();

        // Premium Cases & Protection
        $protectionEssentials = DB::table('ms_parts_inventory')
            ->where('company_id', $companyId)
            ->whereIn('category', ['back_cover', 'tempered_glass'])
            ->orderBy('id', 'asc')
            ->limit(4)
            ->get();

        // Live stats for social proof
        $activeRepairsCount = DB::table('ms_repair_tickets')
            ->where('company_id', $companyId)
            ->whereIn('status', ['received', 'diagnosing', 'in_progress', 'repairing', 'parts_awaited', 'testing', 'ready_for_pickup'])
            ->count();

        $completedRepairsCount = DB::table('ms_repair_tickets')
            ->where('company_id', $companyId)
            ->whereIn('status', ['completed', 'delivered'])
            ->count();

        $totalAccessoriesInStock = DB::table('ms_parts_inventory')
            ->where('company_id', $companyId)
            ->sum('stock_qty');

        return view('mobileshop.public.home', compact(
            'categories',
            'featuredAccessories',
            'chargerEssentials',
            'protectionEssentials',
            'activeRepairsCount',
            'completedRepairsCount',
            'totalAccessoriesInStock'
        ));
    }

    /**
     * Public Website — Explore Accessories & Parts Catalog
     */
    public function publicStore(Request $request)
    {
        $companyId = company_id() ?? session('company_id') ?? 1;
        $categorySlug = $request->query('category', 'all');
        $brandFilter = $request->query('brand', 'all');
        $query = trim($request->query('q', ''));

        $p = DB::getTablePrefix();
        // Fetch all categories for pill tabs
        $categories = DB::table('ms_part_categories')
            ->where('ms_part_categories.company_id', $companyId)
            ->leftJoin('ms_parts_inventory', function($join) use ($companyId) {
                $join->on('ms_parts_inventory.category_id', '=', 'ms_part_categories.id')
                     ->where('ms_parts_inventory.company_id', '=', $companyId);
            })
            ->select(
                'ms_part_categories.id',
                'ms_part_categories.name',
                'ms_part_categories.slug',
                DB::raw("COUNT({$p}ms_parts_inventory.id) as items_count")
            )
            ->groupBy('ms_part_categories.id', 'ms_part_categories.name', 'ms_part_categories.slug')
            ->orderBy('ms_part_categories.name', 'asc')
            ->get();

        // Fetch distinct brands for filter
        $brands = DB::table('ms_parts_inventory')
            ->where('company_id', $companyId)
            ->whereNotNull('brand')
            ->where('brand', '!=', '')
            ->distinct()
            ->pluck('brand');

        // Query accessories/parts
        $itemsQuery = DB::table('ms_parts_inventory')
            ->leftJoin('ms_part_categories', 'ms_parts_inventory.category_id', '=', 'ms_part_categories.id')
            ->where('ms_parts_inventory.company_id', $companyId)
            ->select(
                'ms_parts_inventory.*',
                'ms_part_categories.name as category_name',
                'ms_part_categories.slug as category_slug'
            );

        if ($categorySlug !== 'all' && !empty($categorySlug)) {
            $itemsQuery->where(function($q) use ($categorySlug) {
                $q->where('ms_part_categories.slug', $categorySlug)
                  ->orWhere('ms_parts_inventory.category', $categorySlug);
            });
        }

        if ($brandFilter !== 'all' && !empty($brandFilter)) {
            $itemsQuery->where('ms_parts_inventory.brand', $brandFilter);
        }

        if (!empty($query)) {
            $itemsQuery->where(function($q) use ($query) {
                $q->where('ms_parts_inventory.name', 'like', "%{$query}%")
                  ->orWhere('ms_parts_inventory.brand', 'like', "%{$query}%")
                  ->orWhere('ms_parts_inventory.compatible_model', 'like', "%{$query}%")
                  ->orWhere('ms_parts_inventory.description', 'like', "%{$query}%");
            });
        }

        $items = $itemsQuery->orderBy('ms_parts_inventory.id', 'desc')->paginate(16)->withQueryString();

        return view('mobileshop.public.shop', compact(
            'items',
            'categories',
            'brands',
            'categorySlug',
            'brandFilter',
            'query'
        ));
    }

    /**
     * Public Website — Single Accessory / Part Detail Page
     */
    public function publicProductDetail($id)
    {
        $companyId = company_id() ?? session('company_id') ?? 1;

        $product = DB::table('ms_parts_inventory')
            ->leftJoin('ms_part_categories', 'ms_parts_inventory.category_id', '=', 'ms_part_categories.id')
            ->where('ms_parts_inventory.company_id', $companyId)
            ->where('ms_parts_inventory.id', $id)
            ->select(
                'ms_parts_inventory.*',
                'ms_part_categories.name as category_name',
                'ms_part_categories.slug as category_slug'
            )
            ->first();

        if (!$product) {
            abort(404, 'The requested mobile accessory or spare part was not found in our catalog.');
        }

        // Related items in the same category or brand
        $relatedProducts = DB::table('ms_parts_inventory')
            ->leftJoin('ms_part_categories', 'ms_parts_inventory.category_id', '=', 'ms_part_categories.id')
            ->where('ms_parts_inventory.company_id', $companyId)
            ->where('ms_parts_inventory.id', '!=', $id)
            ->where(function($q) use ($product) {
                if ($product->category_id) {
                    $q->where('ms_parts_inventory.category_id', $product->category_id);
                } else {
                    $q->where('ms_parts_inventory.category', $product->category);
                }
                if ($product->brand) {
                    $q->orWhere('ms_parts_inventory.brand', $product->brand);
                }
            })
            ->select(
                'ms_parts_inventory.*',
                'ms_part_categories.name as category_name',
                'ms_part_categories.slug as category_slug'
            )
            ->orderBy('ms_parts_inventory.id', 'desc')
            ->limit(4)
            ->get();

        return view('mobileshop.public.product', compact('product', 'relatedProducts'));
    }

    /**
     * Public Website — About Us & Lab Page
     */
    public function publicAbout()
    {
        return view('mobileshop.public.about');
    }

    /**
     * Public Website — Contact & Store Location Page
     */
    public function publicContact()
    {
        return view('mobileshop.public.contact');
    }

    /**
     * Public Website — Submit Customer Inquiry Form
     */
    public function submitContact(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:100',
            'phone'   => 'required|string|max:20',
            'email'   => 'nullable|email|max:100',
            'subject' => 'nullable|string|max:50',
            'message' => 'required|string|max:1000',
        ]);

        Log::info('Public customer contact inquiry received:', $validated);

        $sName = store_name();
        return redirect()->route('public.contact')->with('success', "Thank you, {$validated['name']}! Your inquiry has been received by our {$sName} service desk. Our technician will call you shortly on {$validated['phone']}.");
    }

    /**
     * Public Website — Real-Time Express Repair Tracker
     */
    public function publicTrackRepair(Request $request)
    {
        $ticketNo = trim($request->query('ticket_number') ?? '');
        $ticket = null;
        $activeStep = 1;
        $progressPct = 20;

        if (!empty($ticketNo)) {
            $companyId = company_id() ?? session('company_id') ?? 1;
            $ticket = DB::table('ms_repair_tickets')
                ->join('ms_customers', 'ms_repair_tickets.customer_id', '=', 'ms_customers.id')
                ->select(
                    'ms_repair_tickets.id',
                    'ms_repair_tickets.ticket_number',
                    'ms_repair_tickets.status',
                    'ms_repair_tickets.brand',
                    'ms_repair_tickets.model',
                    'ms_repair_tickets.reported_faults',
                    'ms_repair_tickets.estimated_cost',
                    'ms_repair_tickets.total_amount',
                    'ms_repair_tickets.advance_paid',
                    'ms_repair_tickets.balance_due',
                    'ms_repair_tickets.received_at',
                    'ms_repair_tickets.completed_at',
                    'ms_repair_tickets.delivered_at',
                    'ms_customers.name as customer_name'
                )
                ->where('ms_repair_tickets.company_id', $companyId)
                ->where(function($q) use ($ticketNo) {
                    $q->where('ms_repair_tickets.ticket_number', $ticketNo)
                      ->orWhere('ms_repair_tickets.ticket_number', 'like', "%{$ticketNo}%");
                })
                ->first();

            if ($ticket) {
                // Determine 5-step status
                // Steps: 1: Received, 2: Diagnosing, 3: In Repair, 4: QC Testing, 5: Ready for Pickup / Delivered
                switch ($ticket->status) {
                    case 'received':
                    case 'pending':
                        $activeStep = 1;
                        $progressPct = 20;
                        break;
                    case 'diagnosing':
                        $activeStep = 2;
                        $progressPct = 40;
                        break;
                    case 'in_progress':
                    case 'repairing':
                    case 'parts_awaited':
                        $activeStep = 3;
                        $progressPct = 65;
                        break;
                    case 'testing':
                    case 'qc':
                        $activeStep = 4;
                        $progressPct = 85;
                        break;
                    case 'ready_for_pickup':
                    case 'completed':
                    case 'delivered':
                        $activeStep = 5;
                        $progressPct = 100;
                        break;
                    default:
                        $activeStep = 2;
                        $progressPct = 35;
                }
            }
        }

        // Demo tickets available for quick click testing
        $sampleTickets = DB::table('ms_repair_tickets')
            ->select('ticket_number', 'brand', 'model', 'status')
            ->orderBy('id', 'desc')
            ->limit(3)
            ->get();

        return view('mobileshop.public.track_repair', compact('ticket', 'activeStep', 'progressPct', 'sampleTickets'));
    }

    /**
     * Public Website — Privacy & Data Zero-Wipe Policy
     */
    public function publicPrivacy()
    {
        return view('mobileshop.public.privacy');
    }

    /**
     * Public Website — Terms & Conditions of Sale
     */
    public function publicTerms()
    {
        return view('mobileshop.public.terms');
    }

    /**
     * Public Website — Refund, Return & Replacement Policy
     */
    public function publicRefunds()
    {
        return view('mobileshop.public.refunds');
    }

    /**
     * Public Website — Warranty & Guarantee Policy
     */
    public function publicWarranty()
    {
        return view('mobileshop.public.warranty');
    }

    /**
     * Public Website — Shipping & Store Pickup Policy
     */
    public function publicShipping()
    {
        return view('mobileshop.public.shipping');
    }
}
