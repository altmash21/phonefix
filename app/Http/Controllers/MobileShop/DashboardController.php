<?php

namespace App\Http\Controllers\MobileShop;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardController extends BaseMobileShopController
{
    /**
     * Dashboard — niche-scoped KPIs and chart for every role.
     * No redirects. Everyone lands here and sees their own data.
     */
    public function dashboard()
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $companyId = $this->getCompanyId();
        $niche     = $this->getUserNiche();
        $isAdmin   = $niche === 'admin';

        // ─── Niche-Scoped KPI Stats (Cached 60s for performance) ───────────────
        $kpis = Cache::remember("ms_dash_kpis_{$companyId}_{$niche}", 60, function () use ($companyId, $niche) {
            $statPurchaseInvoices = 0;
            $statBuybackReturns   = 0;
            $statSaleInvoices     = 0;
            $statPendingUdhari    = 0;
            $totalNewPhones       = 0;
            $totalSecondHand      = 0;
            $totalRepairsOpen     = 0;
            $totalUdhariDue       = 0.0;
            $totalSupplierCredit  = 0.0;
            $totalEmiBalance      = 0.0;

            switch ($niche) {
                case 'phones':
                    $statPurchaseInvoices = DB::table('ms_mobile_devices')->where('company_id', $companyId)->where('type', 'new')->count();
                    $statSaleInvoices     = DB::table('ms_mobile_sales')->where('company_id', $companyId)->where('status', '!=', 'voided')->count();
                    $statPendingUdhari    = DB::table('ms_customers')->where('company_id', $companyId)->where('udhari_balance', '>', 0)->count();
                    $totalNewPhones       = DB::table('ms_mobile_devices')->where('company_id', $companyId)->where('type', 'new')->where('status', 'in_stock')->count();
                    $totalUdhariDue       = (float) DB::table('ms_customers')->where('company_id', $companyId)->sum('udhari_balance');
                    break;

                case 'secondhand':
                    $statPurchaseInvoices = DB::table('ms_mobile_devices')->where('company_id', $companyId)->where('type', 'second_hand')->count();
                    $statBuybackReturns   = $statPurchaseInvoices;
                    $statSaleInvoices     = DB::table('ms_mobile_sales')->where('company_id', $companyId)->where('status', '!=', 'voided')
                        ->whereExists(function($q) { $q->from('ms_mobile_devices')->whereColumn('ms_mobile_devices.id','ms_mobile_sales.device_id')->where('ms_mobile_devices.type','second_hand'); })->count();
                    $totalSecondHand      = DB::table('ms_mobile_devices')->where('company_id', $companyId)->where('type', 'second_hand')->where('status', 'in_stock')->count();
                    break;

                case 'accessories':
                    $statPurchaseInvoices = DB::table('ms_parts_inventory_history')->where('type', 'addition')
                        ->whereExists(function($q) use ($companyId) { $q->from('ms_parts_inventory')->whereColumn('ms_parts_inventory.id','ms_parts_inventory_history.part_id')->where('ms_parts_inventory.company_id',$companyId); })->count();
                    $statSaleInvoices     = DB::table('ms_accessory_sales')->where('company_id', $companyId)->where('status', '!=', 'voided')->count();
                    $totalSupplierCredit  = (float) DB::table('ms_supplier_credit_wallets')->where('company_id', $companyId)->sum('credit_balance');
                    break;

                case 'covers':
                    $coverCats = $this->coverCategories;
                    $statPurchaseInvoices = DB::table('ms_parts_inventory_history')->where('type', 'addition')
                        ->whereExists(function($q) use ($companyId, $coverCats) {
                            $q->from('ms_parts_inventory')->whereColumn('ms_parts_inventory.id','ms_parts_inventory_history.part_id')
                              ->where('ms_parts_inventory.company_id',$companyId)->whereIn('ms_parts_inventory.category',$coverCats);
                        })->count();
                    $statSaleInvoices     = DB::table('ms_accessory_sales')->where('company_id', $companyId)->where('status', '!=', 'voided')
                        ->whereExists(function($q) use ($companyId, $coverCats) {
                            $q->from('ms_accessory_sale_items')->whereColumn('ms_accessory_sale_items.accessory_sale_id','ms_accessory_sales.id')
                              ->join('ms_parts_inventory','ms_accessory_sale_items.part_id','=','ms_parts_inventory.id')
                              ->whereIn('ms_parts_inventory.category',$coverCats);
                        })->count();
                    break;

                case 'repairs':
                    $statSaleInvoices     = DB::table('ms_repair_tickets')->where('company_id', $companyId)->where('status', 'delivered')->count();
                    $totalRepairsOpen     = DB::table('ms_repair_tickets')->where('company_id', $companyId)->whereNotIn('status', ['delivered', 'cancelled'])->count();
                    break;

                default: // admin — all data
                    $totalNewPhones      = DB::table('ms_mobile_devices')->where('company_id', $companyId)->where('type', 'new')->where('status', 'in_stock')->count();
                    $totalSecondHand     = DB::table('ms_mobile_devices')->where('company_id', $companyId)->where('type', 'second_hand')->where('status', 'in_stock')->count();
                    $totalRepairsOpen    = DB::table('ms_repair_tickets')->where('company_id', $companyId)->whereNotIn('status', ['delivered', 'cancelled'])->count();
                    $totalUdhariDue      = (float) DB::table('ms_customers')->where('company_id', $companyId)->sum('udhari_balance');
                    $totalSupplierCredit = (float) DB::table('ms_supplier_credit_wallets')->where('company_id', $companyId)->sum('credit_balance');
                    $totalEmiBalance     = (float) DB::table('ms_emi_providers')->where('company_id', $companyId)->sum('advance_balance');
                    $statPurchaseInvoices = DB::table('ms_purchase_orders')->where('company_id', $companyId)->count()
                        + DB::table('ms_parts_inventory_history')->where('type', 'addition')->count()
                        + max(1, $totalNewPhones);
                    $statBuybackReturns  = DB::table('ms_mobile_devices')->where('company_id', $companyId)->where('type', 'second_hand')->count();
                    $statSaleInvoices    = DB::table('ms_mobile_sales')->where('company_id', $companyId)->where('status', '!=', 'voided')->count()
                        + DB::table('ms_accessory_sales')->where('company_id', $companyId)->where('status', '!=', 'voided')->count();
                    $statPendingUdhari   = DB::table('ms_customers')->where('company_id', $companyId)->where('udhari_balance', '>', 0)->count();
                    break;
            }

            return compact(
                'statPurchaseInvoices', 'statBuybackReturns', 'statSaleInvoices', 'statPendingUdhari',
                'totalNewPhones', 'totalSecondHand', 'totalRepairsOpen', 'totalUdhariDue',
                'totalSupplierCredit', 'totalEmiBalance'
            );
        });

        extract($kpis);

        // ─── Niche-Scoped 12-Month Spline Chart Data (Cached 5 mins & Grouped) ─
        $yr = (int) now()->year;
        $coverCategories = $this->coverCategories;

        $monthlyChartData = Cache::remember("ms_dash_chart_{$companyId}_{$niche}_{$yr}", 300, function () use ($companyId, $niche, $yr, $coverCategories) {
            $monthsLabels   = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
            $chartPurchases = array_fill(0, 12, 0);
            $chartBuybacks  = array_fill(0, 12, 0);
            $chartSales     = array_fill(0, 12, 0);
            $chartUdhari    = array_fill(0, 12, 0);

            $getMonthlyCounts = function($query) {
                return $query->selectRaw('MONTH(created_at) as m, COUNT(*) as aggregate')
                    ->groupBy(DB::raw('MONTH(created_at)'))
                    ->pluck('aggregate', 'm')
                    ->all();
            };

            switch ($niche) {
                case 'phones':
                    $salesCounts = $getMonthlyCounts(
                        DB::table('ms_mobile_sales')
                            ->where('company_id', $companyId)
                            ->whereYear('created_at', $yr)
                            ->where('status', '!=', 'voided')
                            ->whereExists(fn($q) => $q->from('ms_mobile_devices')->whereColumn('ms_mobile_devices.id', 'ms_mobile_sales.device_id')->where('type', 'new'))
                    );
                    $purchCounts = $getMonthlyCounts(
                        DB::table('ms_mobile_devices')
                            ->where('company_id', $companyId)
                            ->where('type', 'new')
                            ->whereYear('created_at', $yr)
                    );
                    for ($m = 1; $m <= 12; $m++) {
                        $chartSales[$m - 1]     = (int) ($salesCounts[$m] ?? 0);
                        $chartPurchases[$m - 1] = (int) ($purchCounts[$m] ?? 0);
                    }
                    break;

                case 'secondhand':
                    $salesCounts = $getMonthlyCounts(
                        DB::table('ms_mobile_sales')
                            ->where('company_id', $companyId)
                            ->whereYear('created_at', $yr)
                            ->where('status', '!=', 'voided')
                            ->whereExists(fn($q) => $q->from('ms_mobile_devices')->whereColumn('ms_mobile_devices.id', 'ms_mobile_sales.device_id')->where('type', 'second_hand'))
                    );
                    $buybackCounts = $getMonthlyCounts(
                        DB::table('ms_mobile_devices')
                            ->where('company_id', $companyId)
                            ->where('type', 'second_hand')
                            ->whereYear('created_at', $yr)
                    );
                    for ($m = 1; $m <= 12; $m++) {
                        $chartSales[$m - 1]    = (int) ($salesCounts[$m] ?? 0);
                        $chartBuybacks[$m - 1] = (int) ($buybackCounts[$m] ?? 0);
                    }
                    break;

                case 'accessories':
                    $salesCounts = $getMonthlyCounts(
                        DB::table('ms_accessory_sales')
                            ->where('company_id', $companyId)
                            ->whereYear('created_at', $yr)
                            ->where('status', '!=', 'voided')
                    );
                    $purchCounts = $getMonthlyCounts(
                        DB::table('ms_parts_inventory_history')
                            ->where('type', 'addition')
                            ->whereYear('created_at', $yr)
                            ->whereExists(fn($q) => $q->from('ms_parts_inventory')->whereColumn('ms_parts_inventory.id', 'ms_parts_inventory_history.part_id')->where('company_id', $companyId))
                    );
                    for ($m = 1; $m <= 12; $m++) {
                        $chartSales[$m - 1]     = (int) ($salesCounts[$m] ?? 0);
                        $chartPurchases[$m - 1] = (int) ($purchCounts[$m] ?? 0);
                    }
                    break;

                case 'covers':
                    $salesCounts = $getMonthlyCounts(
                        DB::table('ms_accessory_sales')
                            ->where('company_id', $companyId)
                            ->whereYear('created_at', $yr)
                            ->where('status', '!=', 'voided')
                            ->whereExists(fn($q) => $q->from('ms_accessory_sale_items')->whereColumn('ms_accessory_sale_items.accessory_sale_id', 'ms_accessory_sales.id')
                                ->join('ms_parts_inventory', 'ms_accessory_sale_items.part_id', '=', 'ms_parts_inventory.id')
                                ->whereIn('ms_parts_inventory.category', $coverCategories))
                    );
                    $purchCounts = $getMonthlyCounts(
                        DB::table('ms_parts_inventory_history')
                            ->where('type', 'addition')
                            ->whereYear('created_at', $yr)
                            ->whereExists(fn($q) => $q->from('ms_parts_inventory')->whereColumn('ms_parts_inventory.id', 'ms_parts_inventory_history.part_id')->where('company_id', $companyId)->whereIn('category', $coverCategories))
                    );
                    for ($m = 1; $m <= 12; $m++) {
                        $chartSales[$m - 1]     = (int) ($salesCounts[$m] ?? 0);
                        $chartPurchases[$m - 1] = (int) ($purchCounts[$m] ?? 0);
                    }
                    break;

                case 'repairs':
                    $repCounts = $getMonthlyCounts(
                        DB::table('ms_repair_tickets')
                            ->where('company_id', $companyId)
                            ->where('status', 'delivered')
                            ->whereYear('created_at', $yr)
                    );
                    for ($m = 1; $m <= 12; $m++) {
                        $chartSales[$m - 1] = (int) ($repCounts[$m] ?? 0);
                    }
                    break;

                default: // admin
                    $mobSalesCounts = $getMonthlyCounts(
                        DB::table('ms_mobile_sales')
                            ->where('company_id', $companyId)
                            ->whereYear('created_at', $yr)
                            ->where('status', '!=', 'voided')
                    );
                    $accSalesCounts = $getMonthlyCounts(
                        DB::table('ms_accessory_sales')
                            ->where('company_id', $companyId)
                            ->whereYear('created_at', $yr)
                            ->where('status', '!=', 'voided')
                    );
                    $poPurchCounts = $getMonthlyCounts(
                        DB::table('ms_purchase_orders')
                            ->where('company_id', $companyId)
                            ->whereYear('created_at', $yr)
                    );
                    $partPurchCounts = $getMonthlyCounts(
                        DB::table('ms_parts_inventory_history')
                            ->where('type', 'addition')
                            ->whereYear('created_at', $yr)
                    );
                    $bbCounts = $getMonthlyCounts(
                        DB::table('ms_mobile_devices')
                            ->where('company_id', $companyId)
                            ->where('type', 'second_hand')
                            ->whereYear('created_at', $yr)
                    );
                    $udhCounts = $getMonthlyCounts(
                        DB::table('ms_mobile_sales')
                            ->where('company_id', $companyId)
                            ->where('udhari_amount', '>', 0)
                            ->whereYear('created_at', $yr)
                    );

                    for ($m = 1; $m <= 12; $m++) {
                        $chartSales[$m - 1]     = (int) (($mobSalesCounts[$m] ?? 0) + ($accSalesCounts[$m] ?? 0));
                        $chartPurchases[$m - 1] = (int) (($poPurchCounts[$m] ?? 0) + ($partPurchCounts[$m] ?? 0));
                        $chartBuybacks[$m - 1]  = (int) ($bbCounts[$m] ?? 0);
                        $chartUdhari[$m - 1]    = (int) ($udhCounts[$m] ?? 0);
                    }
                    break;
            }

            // Demo data for new stores
            if (array_sum($chartSales) < 5) {
                $demoSales    = [244, 1507, 1804, 2400, 2240, 2607, 3521, 2607, 0, 0, 0, 0];
                $demoPurch    = [180, 189,  221,  233,  191,  284,  302,  169, 0, 0, 0, 0];
                $demoBuyback  = [ 20,  35,   42,   50,   48,   55,   60,   49, 0, 0, 0, 0];
                $demoUdhari   = [ 50,  70,   85,  110,   95,  120,  140,   90, 0, 0, 0, 0];
                for ($i = 0; $i < 12; $i++) {
                    if ($chartSales[$i]     === 0) $chartSales[$i]     = $demoSales[$i];
                    if ($chartPurchases[$i] === 0) $chartPurchases[$i] = $demoPurch[$i];
                    if ($chartBuybacks[$i]  === 0) $chartBuybacks[$i]  = $demoBuyback[$i];
                    if ($chartUdhari[$i]    === 0) $chartUdhari[$i]    = $demoUdhari[$i];
                }
            }

            return [
                'labels'    => $monthsLabels,
                'purchases' => $chartPurchases,
                'buybacks'  => $chartBuybacks,
                'sales'     => $chartSales,
                'udhari'    => $chartUdhari,
            ];
        });

        // ─── Contextual Lists for Dashboard Cards ─────────────────────────────
        $recentSales    = collect();
        $activeRepairs  = collect();
        $lowStockParts  = collect();
        $lowStockMobiles = collect();

        if (in_array($niche, ['admin', 'phones'])) {
            $recentSalesQuery = DB::table('ms_mobile_sales')
                ->join('ms_customers', 'ms_mobile_sales.customer_id', '=', 'ms_customers.id')
                ->join('ms_mobile_devices', 'ms_mobile_sales.device_id', '=', 'ms_mobile_devices.id')
                ->select('ms_mobile_sales.*', 'ms_customers.name as customer_name', 'ms_customers.phone as customer_phone',
                         'ms_mobile_devices.brand', 'ms_mobile_devices.model', 'ms_mobile_devices.imei_1')
                ->where('ms_mobile_sales.company_id', $companyId)
                ->where('ms_mobile_sales.status', '!=', 'voided')
                ->orderBy('ms_mobile_sales.id', 'desc')
                ->limit(8);
            if ($niche === 'phones') {
                $recentSalesQuery->where('ms_mobile_devices.type', 'new');
            }
            $recentSales = $recentSalesQuery->get();
        }

        if (in_array($niche, ['admin', 'repairs'])) {
            $activeRepairs = DB::table('ms_repair_tickets')
                ->join('ms_customers', 'ms_repair_tickets.customer_id', '=', 'ms_customers.id')
                ->select('ms_repair_tickets.*', 'ms_customers.name as customer_name', 'ms_customers.phone as customer_phone')
                ->where('ms_repair_tickets.company_id', $companyId)
                ->whereNotIn('ms_repair_tickets.status', ['delivered', 'cancelled'])
                ->orderBy('ms_repair_tickets.id', 'desc')
                ->limit(5)
                ->get();
        }

        if (in_array($niche, ['admin', 'accessories', 'covers'])) {
            $partsQuery = DB::table('ms_parts_inventory')
                ->where('company_id', $companyId)
                ->whereRaw('stock_qty <= min_stock_alert');
            if ($niche === 'covers') {
                $partsQuery->whereIn('category', $this->coverCategories);
            }
            $lowStockParts = $partsQuery->get();
        }

        if (in_array($niche, ['admin', 'phones'])) {
            $lowStockMobiles = DB::table('ms_mobile_devices')
                ->select('brand', 'model', 'color', DB::raw('count(*) as in_stock_qty'))
                ->where('company_id', $companyId)
                ->where('status', 'in_stock')
                ->where('type', 'new')
                ->groupBy('brand', 'model', 'color')
                ->get();
        }

        // ─── Admin-Only Executive Analytics ──────────────────────────────────
        $analytics = [];
        if ($isAdmin) {
            $todaySales  = (float) DB::table('ms_mobile_sales')->where('company_id', $companyId)->whereDate('created_at', today())->where('status', '!=', 'voided')->sum('total_amount')
                         + (float) DB::table('ms_accessory_sales')->where('company_id', $companyId)->whereDate('created_at', today())->where('status', '!=', 'voided')->sum('total_amount');
            $monthSales  = (float) DB::table('ms_mobile_sales')->where('company_id', $companyId)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->where('status', '!=', 'voided')->sum('total_amount')
                         + (float) DB::table('ms_accessory_sales')->where('company_id', $companyId)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->where('status', '!=', 'voided')->sum('total_amount');
            $grossSalesTotal = (float) DB::table('ms_mobile_sales')->where('company_id', $companyId)->where('status', '!=', 'voided')->sum('total_amount')
                             + (float) DB::table('ms_accessory_sales')->where('company_id', $companyId)->where('status', '!=', 'voided')->sum('total_amount');
            $totalSalesCount = DB::table('ms_mobile_sales')->where('company_id', $companyId)->where('status', '!=', 'voided')->count()
                             + DB::table('ms_accessory_sales')->where('company_id', $companyId)->where('status', '!=', 'voided')->count();
            $cogsTotal = (float) DB::table('ms_mobile_sales')
                ->join('ms_mobile_devices', 'ms_mobile_sales.device_id', '=', 'ms_mobile_devices.id')
                ->where('ms_mobile_sales.company_id', $companyId)->where('ms_mobile_sales.status', '!=', 'voided')
                ->sum('ms_mobile_devices.purchase_cost');
            $netProfitTotal    = max(0, $grossSalesTotal - $cogsTotal);
            $profitMarginPct   = $grossSalesTotal > 0 ? round(($netProfitTotal / $grossSalesTotal) * 100, 1) : 0;
            $totalInventoryCost   = (float) DB::table('ms_mobile_devices')->where('company_id', $companyId)->where('status', 'in_stock')->sum('purchase_cost')
                                  + (float) DB::table('ms_parts_inventory')->where('company_id', $companyId)->sum(DB::raw('unit_cost * stock_qty'));
            $totalInventoryRetail = (float) DB::table('ms_mobile_devices')->where('company_id', $companyId)->where('status', 'in_stock')->sum('selling_price')
                                  + (float) DB::table('ms_parts_inventory')->where('company_id', $companyId)->sum(DB::raw('selling_price * stock_qty'));
            $expectedInventoryMargin = max(0, $totalInventoryRetail - $totalInventoryCost);
            $totalSupplierDebt    = (float) DB::table('ms_purchase_orders')->where('company_id', $companyId)->where('status', '!=', 'paid')->sum('balance_due');
            $totalRepairRevenue   = (float) DB::table('ms_repair_tickets')->where('company_id', $companyId)->where('status', 'delivered')->sum('total_amount');
            $sevenDayTrend = [];
            for ($d = 6; $d >= 0; $d--) {
                $dateObj = now()->subDays($d);
                $dateStr = $dateObj->toDateString();
                $daySales  = (float) DB::table('ms_mobile_sales')->where('company_id',$companyId)->whereDate('created_at',$dateStr)->where('status','!=','voided')->sum('total_amount')
                           + (float) DB::table('ms_accessory_sales')->where('company_id',$companyId)->whereDate('created_at',$dateStr)->where('status','!=','voided')->sum('total_amount');
                $dayCogs   = (float) DB::table('ms_mobile_sales')
                    ->join('ms_mobile_devices','ms_mobile_sales.device_id','=','ms_mobile_devices.id')
                    ->where('ms_mobile_sales.company_id',$companyId)->where('ms_mobile_sales.status','!=','voided')
                    ->whereDate('ms_mobile_sales.created_at',$dateStr)->sum('ms_mobile_devices.purchase_cost');
                $sevenDayTrend[] = ['label'=>$dateObj->format('D, d M'),'short_label'=>$dateObj->format('d M'),'sales'=>$daySales,'profit'=>max(0,$daySales-$dayCogs)];
            }
            $paymentModes = DB::table('ms_mobile_sales')->where('company_id',$companyId)->where('status','!=','voided')
                ->select('payment_mode',DB::raw('count(*) as total_txns'),DB::raw('sum(total_amount) as total_amount'))->groupBy('payment_mode')->get();
            $topBrands = DB::table('ms_mobile_sales')->join('ms_mobile_devices','ms_mobile_sales.device_id','=','ms_mobile_devices.id')
                ->where('ms_mobile_sales.company_id',$companyId)->where('ms_mobile_sales.status','!=','voided')
                ->select('ms_mobile_devices.brand',DB::raw('count(*) as units_sold'),DB::raw('sum(total_amount) as revenue'))
                ->groupBy('ms_mobile_devices.brand')->orderBy('revenue','desc')->limit(5)->get();
            $repairSla = [
                'received'    => DB::table('ms_repair_tickets')->where('company_id',$companyId)->where('status','received')->count(),
                'diagnosed'   => DB::table('ms_repair_tickets')->where('company_id',$companyId)->whereIn('status',['in_diagnosis','waiting_for_parts','waiting_approval'])->count(),
                'in_progress' => DB::table('ms_repair_tickets')->where('company_id',$companyId)->where('status','in_repair')->count(),
                'ready'       => DB::table('ms_repair_tickets')->where('company_id',$companyId)->where('status','ready')->count(),
                'delivered'   => DB::table('ms_repair_tickets')->where('company_id',$companyId)->where('status','delivered')->count(),
            ];
            $highRiskDebtors = DB::table('ms_customers')->where('company_id',$companyId)->where('udhari_balance','>=',5000)->orderBy('udhari_balance','desc')->limit(5)->get();
            $analytics = compact('todaySales','monthSales','grossSalesTotal','totalSalesCount','netProfitTotal','profitMarginPct',
                'totalInventoryCost','totalInventoryRetail','expectedInventoryMargin','totalSupplierDebt','totalRepairRevenue',
                'sevenDayTrend','paymentModes','topBrands','repairSla','highRiskDebtors');
        }

        // Role flags for view compatibility
        $isTech        = $niche === 'repairs';
        $isBuyback     = $niche === 'secondhand';
        $isSales       = $niche === 'phones';
        $isAccessories = $niche === 'accessories';
        $isCover       = $niche === 'covers';

        return view('mobileshop.dashboard', compact(
            'niche', 'isAdmin', 'isTech', 'isBuyback', 'isSales', 'isAccessories', 'isCover',
            'totalNewPhones', 'totalSecondHand', 'totalRepairsOpen',
            'totalUdhariDue', 'totalSupplierCredit', 'totalEmiBalance',
            'statPurchaseInvoices', 'statBuybackReturns', 'statSaleInvoices', 'statPendingUdhari',
            'monthlyChartData', 'lowStockParts', 'lowStockMobiles',
            'recentSales', 'activeRepairs', 'analytics'
        ));
    }
}
