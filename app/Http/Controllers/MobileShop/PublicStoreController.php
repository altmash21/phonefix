<?php

namespace App\Http\Controllers\MobileShop;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PublicStoreController extends BaseMobileShopController
{
    /**
     * Public Website — Landing Homepage
     */
    public function publicLanding()
    {
        $companyId = company_id() ?? session('company_id') ?? 1;

        $featuredNew = DB::table('ms_mobile_devices')
            ->where('company_id', $companyId)
            ->where('type', 'new')
            ->where('status', 'in_stock')
            ->orderBy('id', 'desc')
            ->limit(6)
            ->get();

        $featuredSecondHand = DB::table('ms_mobile_devices')
            ->where('company_id', $companyId)
            ->where('type', 'second_hand')
            ->where('status', 'in_stock')
            ->orderBy('id', 'desc')
            ->limit(6)
            ->get();

        $newCount = DB::table('ms_mobile_devices')->where('company_id', $companyId)->where('type', 'new')->where('status', 'in_stock')->count();
        $secondHandCount = DB::table('ms_mobile_devices')->where('company_id', $companyId)->where('type', 'second_hand')->where('status', 'in_stock')->count();

        return view('mobileshop.public.home', compact('featuredNew', 'featuredSecondHand', 'newCount', 'secondHandCount'));
    }

    /**
     * Public Website — Explore Shop Catalog
     */
    public function publicStore(Request $request)
    {
        $companyId = company_id() ?? session('company_id') ?? 1;
        $tab       = $request->query('tab', 'all');
        $query     = trim($request->query('q', ''));

        // Query New Phones
        $newPhonesQuery = DB::table('ms_mobile_devices')
            ->where('company_id', $companyId)
            ->where('type', 'new')
            ->where('status', 'in_stock');

        if (!empty($query)) {
            $newPhonesQuery->where(function($q) use ($query) {
                $q->where('brand', 'like', "%{$query}%")
                  ->orWhere('model', 'like', "%{$query}%");
            });
        }
        $newPhones = $newPhonesQuery->orderBy('id', 'desc')->get();

        // Query Second Hand Phones
        $secondHandQuery = DB::table('ms_mobile_devices')
            ->where('company_id', $companyId)
            ->where('type', 'second_hand')
            ->where('status', 'in_stock');

        if (!empty($query)) {
            $secondHandQuery->where(function($q) use ($query) {
                $q->where('brand', 'like', "%{$query}%")
                  ->orWhere('model', 'like', "%{$query}%");
            });
        }
        $secondHandPhones = $secondHandQuery->orderBy('id', 'desc')->get();

        // Query Accessories & Covers
        $accQuery = DB::table('ms_parts_inventory')
            ->where('company_id', $companyId)
            ->where('stock_qty', '>', 0);

        if ($tab === 'covers') {
            $accQuery->whereIn('category', $this->coverCategories);
        }

        if (!empty($query)) {
            $accQuery->where('name', 'like', "%{$query}%");
        }
        $accessories = $accQuery->orderBy('name', 'asc')->limit(24)->get();

        return view('mobileshop.public.shop', compact('newPhones', 'secondHandPhones', 'accessories', 'tab', 'query'));
    }

    /**
     * Public Website — Single Product Detail Page
     */
    public function publicProductDetail($id)
    {
        $companyId = company_id() ?? session('company_id') ?? 1;

        $device = DB::table('ms_mobile_devices')
            ->where('company_id', $companyId)
            ->where('id', $id)
            ->first();

        if (!$device) {
            abort(404, 'The requested mobile device was not found in our catalog.');
        }

        // Related in-stock devices (same brand or same type, excluding current)
        $relatedDevices = DB::table('ms_mobile_devices')
            ->where('company_id', $companyId)
            ->where('id', '!=', $id)
            ->where('status', 'in_stock')
            ->where(function($q) use ($device) {
                $q->where('brand', $device->brand)
                  ->orWhere('type', $device->type);
            })
            ->orderBy('id', 'desc')
            ->limit(4)
            ->get();

        if ($relatedDevices->count() < 4) {
            $excludeIds = $relatedDevices->pluck('id')->push($id)->toArray();
            $moreDevices = DB::table('ms_mobile_devices')
                ->where('company_id', $companyId)
                ->whereNotIn('id', $excludeIds)
                ->where('status', 'in_stock')
                ->orderBy('id', 'desc')
                ->limit(4 - $relatedDevices->count())
                ->get();
            $relatedDevices = $relatedDevices->merge($moreDevices);
        }

        // Calculate approximate EMI plans
        $price = (float) $device->selling_price;
        $emiPlans = [
            ['months' => 3, 'monthly' => round($price / 3), 'down_payment' => 0, 'bank' => 'Bajaj Finserv'],
            ['months' => 6, 'monthly' => round(($price * 1.04) / 6), 'down_payment' => 0, 'bank' => 'HDFC / ICICI'],
            ['months' => 9, 'monthly' => round(($price * 1.06) / 9), 'down_payment' => 0, 'bank' => 'IDFC First'],
            ['months' => 12, 'monthly' => round(($price * 1.08) / 12), 'down_payment' => 0, 'bank' => 'Credit Card EMI'],
        ];

        return view('mobileshop.public.product', compact('device', 'relatedDevices', 'emiPlans'));
    }

    /**
     * Public Website — About Us Page
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

        return redirect()->route('public.contact')->with('success', "Thank you, {$validated['name']}! Your message has been received by our Bandra store counter. We will call you shortly on {$validated['phone']}.");
    }

    /**
     * Public Website — Real-Time Repair Job Sheet Tracker
     */
    public function publicTrackRepair(Request $request)
    {
        $ticketNo = trim($request->query('ticket_number') ?? '');
        $ticket = null;
        if (!empty($ticketNo)) {
            $companyId = company_id() ?? session('company_id') ?? 1;
            $ticket = DB::table('ms_repair_tickets')
                ->join('ms_customers', 'ms_repair_tickets.customer_id', '=', 'ms_customers.id')
                ->select(
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
        }

        return view('mobileshop.public.track_repair', compact('ticket'));
    }
}
