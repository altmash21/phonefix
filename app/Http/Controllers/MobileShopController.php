<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class MobileShopController extends Controller
{
    /**
     * Resolve current company context (Fail-Closed Multi-Tenancy)
     */
    private function getCompanyId(): int
    {
        $companyId = company_id() ?? session('company_id') ?? (auth()->check() ? (auth()->user()->companies()->first()?->id ?? auth()->user()->company_id) : null);
        if (!$companyId) {
            abort(403, 'Multi-tenant context error: No active company found in session.');
        }
        if (auth()->check() && !auth()->user()->companies()->where('companies.id', $companyId)->exists()) {
            abort(403, 'Unauthorized company access attempt.');
        }
        return (int) $companyId;
    }

    /**
     * Generate Race-Free Atomic Sequential Invoice Number per Company
     */
    private function getNextInvoiceNumber(int $companyId, string $prefix): string
    {
        $exists = DB::table('ms_invoice_sequences')
            ->where('company_id', $companyId)
            ->where('prefix', $prefix)
            ->exists();

        if (!$exists) {
            try {
                DB::table('ms_invoice_sequences')->insert([
                    'company_id' => $companyId,
                    'prefix' => $prefix,
                    'current_sequence' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Throwable $e) {
                // Ignore duplicate insertion during concurrent requests
            }
        }

        $seq = DB::table('ms_invoice_sequences')
            ->where('company_id', $companyId)
            ->where('prefix', $prefix)
            ->lockForUpdate()
            ->first();

        $nextNum = ($seq ? (int) $seq->current_sequence : 0) + 1;

        DB::table('ms_invoice_sequences')
            ->where('company_id', $companyId)
            ->where('prefix', $prefix)
            ->update([
                'current_sequence' => $nextNum,
                'updated_at' => now(),
            ]);

        return sprintf('%s-%s%04d', $prefix, date('Ymd'), $nextNum);
    }

    /**
     * Resolve current store default state code (e.g., UP 09 or MH 27)
     */
    private function getStoreStateCode(): string
    {
        return (string) setting('company.state_code', '09');
    }

    /**
     * Resolve which niche a user belongs to.
     * Returns: 'admin' | 'phones' | 'secondhand' | 'accessories' | 'covers' | 'repairs' | 'none'
     */
    private function getUserNiche(): string
    {
        $user = auth()->user();
        if (!$user) return 'none';
        if ($user->hasRole('store-admin') || $user->hasRole('admin')) return 'admin';
        if ($user->hasRole('sales-staff'))       return 'phones';
        if ($user->hasRole('secondhand-staff'))  return 'secondhand';
        if ($user->hasRole('accessories-staff') || $user->hasRole('accessories-manager')) return 'accessories';
        if ($user->hasRole('cover-staff'))       return 'covers';
        if ($user->hasRole('repair-technician')) return 'repairs';
        return 'none'; // Fail-closed security
    }

    /**
     * Cover-staff category filter — back covers + tempered glass only.
     */
    private $coverCategories = ['back_cover', 'back_panel', 'tempered_glass'];

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

    /**
     * Panel 1: New Phones POS Screen
     */
    public function pos()
    {
        abort_unless(auth()->check() && (auth()->user()->can('read-mobileshop-pos') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin')), 403, 'Unauthorized access to New Phones POS counter.');

        $companyId = $this->getCompanyId();
        $newPhones = DB::table('ms_mobile_devices')->where('company_id', $companyId)->where('type', 'new')->where('status', 'in_stock')->get();
        
        // Fetch promotional gifts directly from accessories & parts catalog (back covers, tempered glass, gift-eligible parts)
        $gifts = DB::table('ms_parts_inventory')
            ->where('company_id', $companyId)
            ->where(function ($q) {
                $q->where('is_gift_eligible', 1)
                  ->orWhereIn('category', ['tempered_glass', 'back_panel', 'back_cover', 'general_accessory']);
            })
            ->where('stock_qty', '>', 0)
            ->orderBy('name', 'asc')
            ->get();

        $emiProviders = DB::table('ms_emi_providers')->where('company_id', $companyId)->where('enabled', 1)->get();
        $customers = DB::table('ms_customers')->where('company_id', $companyId)->get();

        return view('mobileshop.pos', compact(
            'newPhones',
            'gifts',
            'emiProviders',
            'customers'
        ));
    }

    /**
     * AI-Powered OCR Scan for EMI Slips, Delivery Challans & Invoices (Gemini 1.5 Flash)
     */
    public function scanEmiBill(Request $request)
    {
        abort_unless(auth()->check() && (auth()->user()->can('read-mobileshop-pos') || auth()->user()->can('create-mobileshop-pos') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin')), 403, 'Unauthorized.');

        $request->validate([
            'bill_image' => 'required|file|mimes:jpeg,png,jpg,webp,pdf,heic|max:10240',
        ]);

        $apiKey = config('services.gemini.key') ?: env('GEMINI_API_KEY');
        if (empty($apiKey)) {
            $apiKey = setting('mobileshop.gemini_api_key', '');
        }

        if (empty($apiKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Gemini API Key is not configured. Please add GEMINI_API_KEY=your_key in your .env file.',
            ], 422);
        }

        $file = $request->file('bill_image');
        $mimeType = $file->getMimeType();
        $base64Data = base64_encode(file_get_contents($file->getRealPath()));

        $model = config('services.gemini.model', 'gemini-1.5-flash');
        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . urlencode($apiKey);

        $prompt = <<<PROMPT
You are an expert OCR and retail document parser for a mobile phone store in India.
Analyze this image of an EMI Finance Slip, Delivery Challan, Retail Invoice, or Down Payment receipt.
Extract the following information accurately into a strict JSON object:

{
  "customer_name": "Full name of customer or null",
  "customer_phone": "10-digit mobile number or null",
  "customer_address": "Customer address / city if mentioned, or null",
  "customer_gstin": "GSTIN number if mentioned, or null",
  "brand": "Mobile brand (e.g. Apple, Samsung, Vivo, Oppo, Xiaomi, Realme, OnePlus) or null",
  "model": "Model name / variant (e.g. iPhone 15 128GB, Vivo V29, Galaxy S24) or null",
  "imei": "15-digit IMEI 1 number if visible, or null",
  "emi_provider": "Finance company name (e.g. Bajaj Finserv, TVS Credit, HDB Financial, Home Credit, IDFC First, DMI Finance) or null",
  "emi_loan_no": "Loan account number / agreement / reference ID or null",
  "emi_downpayment": 0.00,
  "sale_price": 0.00
}

Rules:
1. Return ONLY the JSON object. Do not include markdown code fences (```json), commentary, or extra text.
2. Clean phone numbers to standard 10-digit Indian mobile format if possible.
3. Clean IMEI numbers to 15 digits (remove spaces/slashes).
4. If a field cannot be determined, use null or 0.00 for numbers.
PROMPT;

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                        [
                            'inline_data' => [
                                'mime_type' => $mimeType,
                                'data' => $base64Data
                            ]
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.1,
                'response_mime_type' => 'application/json'
            ]
        ];

        try {
            $ch = curl_init($endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                return response()->json([
                    'success' => false,
                    'message' => 'Network error connecting to Gemini API: ' . $curlError
                ], 500);
            }

            if ($httpCode !== 200) {
                $errBody = json_decode($response, true);
                $errMsg = $errBody['error']['message'] ?? "Gemini API returned error HTTP {$httpCode}";
                return response()->json([
                    'success' => false,
                    'message' => $errMsg
                ], 500);
            }

            $resData = json_decode($response, true);
            $rawText = $resData['candidates'][0]['content']['parts'][0]['text'] ?? '{}';

            $rawText = preg_replace('/^```json\s*/i', '', trim($rawText));
            $rawText = preg_replace('/\s*```$/', '', $rawText);
            $extracted = json_decode($rawText, true);

            if (!is_array($extracted)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to parse AI output from document. Please try a clearer picture.',
                    'raw' => $rawText
                ], 422);
            }

            $companyId = $this->getCompanyId();
            $matchedDevice = null;
            if (!empty($extracted['imei'])) {
                $cleanImei = preg_replace('/[^0-9]/', '', (string)$extracted['imei']);
                $matchedDevice = DB::table('ms_mobile_devices')
                    ->where('company_id', $companyId)
                    ->where('status', 'in_stock')
                    ->where('type', 'new')
                    ->where(function($q) use ($cleanImei) {
                        $q->where('imei_1', $cleanImei)->orWhere('imei_2', $cleanImei);
                    })
                    ->first();
            }

            if (!$matchedDevice && !empty($extracted['model'])) {
                $matchedDevice = DB::table('ms_mobile_devices')
                    ->where('company_id', $companyId)
                    ->where('status', 'in_stock')
                    ->where('type', 'new')
                    ->where('model', 'LIKE', '%' . trim($extracted['model']) . '%')
                    ->first();
            }

            $matchedProviderId = null;
            if (!empty($extracted['emi_provider'])) {
                $provName = trim($extracted['emi_provider']);
                $provider = DB::table('ms_emi_providers')
                    ->where('company_id', $companyId)
                    ->where('enabled', 1)
                    ->where(function($q) use ($provName) {
                        $q->where('name', 'LIKE', "%{$provName}%")
                          ->orWhere('code', 'LIKE', "%{$provName}%");
                    })
                    ->first();
                if ($provider) {
                    $matchedProviderId = $provider->id;
                }
            }

            return response()->json([
                'success' => true,
                'data' => $extracted,
                'matched_device' => $matchedDevice,
                'matched_provider_id' => $matchedProviderId
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred during OCR: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process Brand New Mobile Sale
     */
    public function processSale(Request $request)
    {
        abort_unless(auth()->check() && (auth()->user()->can('create-mobileshop-pos') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin')), 403, 'Unauthorized action.');
        
        $request->validate([
            'customer_phone' => 'required|string|min:7|max:20',
            'customer_name' => 'required|string|min:2|max:100',
            'device_id' => 'required|exists:ms_mobile_devices,id',
            'sale_price' => 'required|numeric|min:1',
            'amount_paid' => 'required|numeric|min:0',
            'payment_mode' => 'required|in:cash,upi,card,bank_transfer,emi,credit_udhari,split',
        ]);

        $companyId = $this->getCompanyId();

        // Idempotency Check
        if ($request->filled('idempotency_key')) {
            $existing = DB::table('ms_mobile_sales')
                ->where('company_id', $companyId)
                ->where('idempotency_key', $request->idempotency_key)
                ->first();
            if ($existing) {
                return redirect()->route('mobileshop.invoice', ['id' => $existing->id])
                    ->with('success', "Sale invoice #{$existing->invoice_number} already processed (Idempotent response).");
            }
        }

        return DB::transaction(function () use ($request, $companyId) {
            $storeState = $this->getStoreStateCode();
            // Find or create customer
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

            // Lock device row
            $device = DB::table('ms_mobile_devices')
                ->where('company_id', $companyId)
                ->where('id', $request->device_id)
                ->where('status', 'in_stock')
                ->where('type', 'new')
                ->lockForUpdate()
                ->first();

            if (!$device) {
                return redirect()->back()->with('error', 'Selected new mobile device is no longer in stock!');
            }

            $salePrice = (float) $request->sale_price;
            $amountPaid = (float) $request->amount_paid;
            $taxRate = (float) ($request->tax_rate ?? 18.00);
            $isGst = $request->boolean('is_gst') || ($request->bill_type === 'gst');
            $billType = $isGst ? 'gst' : 'non_gst';
            
            // Tax Calculation
            if ($billType === 'non_gst') {
                $taxRate = 0.00;
                $cgst = 0.00;
                $sgst = 0.00;
                $igst = 0.00;
                $isStateMatch = true;
            } else {
                $taxable = round($salePrice / (1 + ($taxRate / 100)), 2);
                $totalTax = round($salePrice - $taxable, 2);
                $isStateMatch = empty($customer->state_code) || ($customer->state_code === $storeState);
                $halfTax = round($totalTax / 2, 2);

                $cgst = $isStateMatch ? $halfTax : 0.00;
                $sgst = $isStateMatch ? ($totalTax - $halfTax) : 0.00;
                $igst = !$isStateMatch ? $totalTax : 0.00;
            }

            $udhariAmount = max(0.00, $salePrice - $amountPaid);

            // EMI specifics
            $emiProviderId = $request->emi_provider_id;
            $emiFinanced = 0.00;
            if ($request->payment_mode === 'emi' && $emiProviderId) {
                $emiDownpayment = (float) ($request->emi_downpayment ?? 0.00);
                $emiFinanced = max(0.00, $salePrice - $emiDownpayment);

                $provider = DB::table('ms_emi_providers')->where('company_id', $companyId)->where('id', $emiProviderId)->lockForUpdate()->first();
                if (!$provider || $provider->advance_balance < $emiFinanced) {
                    $avail = $provider ? $provider->advance_balance : 0;
                    return redirect()->back()->with('error', "Finance pool for provider is insufficient! Available: ₹{$avail}, Required: ₹{$emiFinanced}");
                }

                DB::table('ms_emi_providers')->where('id', $emiProviderId)->decrement('advance_balance', $emiFinanced);
                DB::table('ms_emi_provider_transactions')->insert([
                    'emi_provider_id' => $emiProviderId,
                    'type' => 'sale_deduction',
                    'amount' => $emiFinanced,
                    'balance_after' => $provider->advance_balance - $emiFinanced,
                    'reference_no' => $request->emi_loan_no,
                    'notes' => "Financing for {$device->brand} {$device->model} (IMEI: {$device->imei_1})",
                    'created_at' => now(),
                ]);
            }

            // Atomic Sequential Invoice Number
            $invoiceNumber = $this->getNextInvoiceNumber($companyId, 'INV');

            // Create Sale Record
            $saleId = DB::table('ms_mobile_sales')->insertGetId([
                'company_id' => $companyId,
                'idempotency_key' => $request->idempotency_key ?? Str::uuid()->toString(),
                'customer_id' => $customer->id,
                'invoice_number' => $invoiceNumber,
                'bill_type' => $billType,
                'device_id' => $device->id,
                'sale_price' => $salePrice,
                'tax_rate' => $taxRate,
                'tax_type' => $isStateMatch ? 'intra_state' : 'inter_state',
                'cgst_amount' => $cgst,
                'sgst_amount' => $sgst,
                'igst_amount' => $igst,
                'total_amount' => $salePrice,
                'amount_paid' => $amountPaid,
                'udhari_amount' => $udhariAmount,
                'payment_mode' => $request->payment_mode,
                'emi_provider_id' => $emiProviderId,
                'emi_loan_no' => $request->emi_loan_no,
                'emi_downpayment' => $request->emi_downpayment ?? 0.00,
                'emi_financed_amount' => $emiFinanced,
                'emi_monthly_amount' => $request->emi_monthly_amount ?? 0.00,
                'emi_tenure_months' => $request->emi_tenure_months ?? 0,
                'sold_by' => auth()->id(),
                'status' => 'completed',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Update Device Status
            DB::table('ms_mobile_devices')->where('id', $device->id)->update([
                'status' => 'sold',
                'selling_price' => $salePrice,
                'updated_at' => now(),
            ]);

            // Gifts Attachment & Stock Decrement from Accessories & Parts Inventory
            if ($request->has('gift_ids') && is_array($request->gift_ids)) {
                foreach ($request->gift_ids as $giftPartId) {
                    $part = DB::table('ms_parts_inventory')
                        ->where('company_id', $companyId)
                        ->where('id', $giftPartId)
                        ->lockForUpdate()
                        ->first();

                    if ($part && $part->stock_qty >= 1) {
                        $newBalance = $part->stock_qty - 1;
                        DB::table('ms_parts_inventory')->where('id', $part->id)->update([
                            'stock_qty' => $newBalance,
                            'updated_at' => now(),
                        ]);

                        DB::table('ms_sale_gifts')->insert([
                            'sale_id' => $saleId,
                            'gift_id' => $part->id,
                            'qty' => 1,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        DB::table('ms_parts_inventory_history')->insert([
                            'part_id' => $part->id,
                            'type' => 'deduction',
                            'quantity' => 1,
                            'balance_after' => $newBalance,
                            'reference' => "Promotional Gift on Phone Sale #{$invoiceNumber}",
                            'user_id' => auth()->id(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            // Customer Khata Update if Udhari
            if ($udhariAmount > 0) {
                DB::table('ms_customers')->where('id', $customer->id)->increment('udhari_balance', $udhariAmount);
                $newBal = $customer->udhari_balance + $udhariAmount;
                DB::table('ms_customer_khata_transactions')->insert([
                    'company_id' => $companyId,
                    'customer_id' => $customer->id,
                    'type' => 'udhari_sale',
                    'sale_id' => $saleId,
                    'amount' => $udhariAmount,
                    'balance_after' => $newBal,
                    'remarks' => "Udhari on Phone Sale Invoice #{$invoiceNumber}",
                    'recorded_by' => auth()->id(),
                    'created_at' => now(),
                ]);
            }

            return redirect()->route('mobileshop.invoice', ['id' => $saleId])->with('success', "Sale #{$invoiceNumber} successfully recorded!");
        });
    }

    /**
     * Resolve Mobile Phone Sale details with all joined relations and gifts
     */
    private function resolvePhoneSaleDetails(int $companyId, int $id): array
    {
        $sale = DB::table('ms_mobile_sales')
            ->join('ms_customers', 'ms_mobile_sales.customer_id', '=', 'ms_customers.id')
            ->join('ms_mobile_devices', 'ms_mobile_sales.device_id', '=', 'ms_mobile_devices.id')
            ->select('ms_mobile_sales.*', 'ms_customers.name as customer_name', 'ms_customers.phone as customer_phone', 'ms_customers.gstin as customer_gstin', 'ms_customers.address as customer_address', 'ms_customers.udhari_balance as current_udhari_balance', 'ms_mobile_devices.brand', 'ms_mobile_devices.model', 'ms_mobile_devices.color', 'ms_mobile_devices.ram', 'ms_mobile_devices.storage', 'ms_mobile_devices.imei_1', 'ms_mobile_devices.imei_2', 'ms_mobile_devices.hsn_code', 'ms_mobile_devices.type as device_type', 'ms_mobile_devices.condition_grade')
            ->where('ms_mobile_sales.company_id', $companyId)
            ->where('ms_mobile_sales.id', $id)
            ->first();

        if (!$sale) {
            abort(404, 'Invoice not found');
        }

        $gifts = DB::table('ms_sale_gifts')
            ->leftJoin('ms_parts_inventory', 'ms_sale_gifts.gift_id', '=', 'ms_parts_inventory.id')
            ->where('ms_sale_gifts.sale_id', $id)
            ->select('ms_parts_inventory.name', 'ms_sale_gifts.qty')
            ->get();
        foreach ($gifts as $g) {
            $g->name = $g->name ?: 'Promotional Gift Item';
        }

        $emiProvider = $sale->emi_provider_id ? DB::table('ms_emi_providers')->where('company_id', $companyId)->where('id', $sale->emi_provider_id)->first() : null;
        $amountInWords = self::amountToWords($sale->total_amount);
        $customerStatement = $sale->customer_id ? $this->buildCustomerLedgerStatement($companyId, (int) $sale->customer_id) : null;

        return compact('sale', 'gifts', 'emiProvider', 'amountInWords', 'customerStatement');
    }

    /**
     * Invoice View (Dual-Format: 80mm Thermal & A4 Tax Invoice)
     */
    public function invoice($id)
    {
        abort_unless(auth()->check() && (
            auth()->user()->can('read-mobileshop-pos') || 
            auth()->user()->can('read-mobileshop-secondhand') ||
            auth()->user()->hasRole('admin') || 
            auth()->user()->hasRole('store-admin') || 
            auth()->user()->hasRole('sales-staff') ||
            auth()->user()->hasRole('secondhand-staff')
        ), 403, 'Unauthorized access to invoices.');

        $data = $this->resolvePhoneSaleDetails($this->getCompanyId(), (int) $id);
        return view('mobileshop.invoice', $data);
    }

    /**
     * Download Mobile Sales Invoice as Direct PDF
     */
    public function invoicePdf($id)
    {
        abort_unless(auth()->check() && (
            auth()->user()->can('read-mobileshop-pos') || 
            auth()->user()->can('read-mobileshop-secondhand') ||
            auth()->user()->hasRole('admin') || 
            auth()->user()->hasRole('store-admin') || 
            auth()->user()->hasRole('sales-staff') ||
            auth()->user()->hasRole('secondhand-staff')
        ), 403, 'Unauthorized access to invoice PDF.');

        $data = $this->resolvePhoneSaleDetails($this->getCompanyId(), (int) $id);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('mobileshop.pdf.phone_invoice', $data);
        $pdf->setPaper('a4', 'portrait');
        return $pdf->download("Invoice-{$data['sale']->invoice_number}.pdf");
    }

    /**
     * Brand New Mobiles Stock
     */
    public function newMobiles()
    {
        abort_unless(auth()->check() && (auth()->user()->can('read-mobileshop-new') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin') || auth()->user()->hasRole('sales-staff')), 403, 'Unauthorized access to new mobiles inventory.');

        $companyId = $this->getCompanyId();
        $mobiles = DB::table('ms_mobile_devices')
            ->where('company_id', $companyId)
            ->where('type', 'new')
            ->orderBy('id', 'desc')
            ->get();

        return view('mobileshop.new_mobiles', compact('mobiles'));
    }

    /**
     * Store Brand New Mobile into Inventory (with IMEI Uniqueness Check)
     */
    public function storeNewMobile(Request $request)
    {
        abort_unless(auth()->check() && (auth()->user()->can('create-mobileshop-pos') || auth()->user()->can('read-mobileshop-new') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin') || auth()->user()->hasRole('sales-staff')), 403, 'Unauthorized action.');

        $request->validate([
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'imei_1' => 'required|string|max:30',
            'purchase_cost' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:1',
        ]);

        $companyId = $this->getCompanyId();

        // IMEI Uniqueness check within company
        $exists = DB::table('ms_mobile_devices')
            ->where('company_id', $companyId)
            ->where('imei_1', $request->imei_1)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', "A mobile device with IMEI {$request->imei_1} is already registered in inventory!");
        }

        $supplier = DB::table('ms_suppliers')->where('company_id', $companyId)->first();
        $supplierId = $supplier ? $supplier->id : 1;
        $poNum = 'PO-PHONES-' . date('Ymd') . '-' . rand(100, 999);
        $phoneCost = (float) $request->purchase_cost;

        $photoPath = null;
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $file = $request->file('photo');
            $filename = 'new_' . time() . '_' . rand(100, 999) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/mobiles'), $filename);
            $photoPath = 'uploads/mobiles/' . $filename;
        }

        $poId = DB::table('ms_purchase_orders')->insertGetId([
            'company_id'   => $companyId,
            'supplier_id'  => $supplierId,
            'po_number'    => $poNum,
            'order_date'   => now()->toDateString(),
            'tax_type'     => 'intra_state',
            'subtotal'     => $phoneCost,
            'total_amount' => $phoneCost,
            'amount_paid'  => $phoneCost,
            'balance_due'  => 0,
            'status'       => 'received',
            'created_by'   => auth()->id(),
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        DB::table('ms_purchase_order_items')->insert([
            'purchase_order_id' => $poId,
            'brand'             => $request->brand,
            'model'             => $request->model,
            'variant'           => ($request->ram ? $request->ram . '/' . $request->storage : '') . ($request->color ? ' (' . $request->color . ')' : '') . " [IMEI: {$request->imei_1}]",
            'hsn_code'          => '85171300',
            'qty'               => 1,
            'qty_received'      => 1,
            'unit_cost'         => $phoneCost,
            'tax_rate'          => 18.00,
            'line_total'        => $phoneCost,
        ]);

        DB::table('ms_mobile_devices')->insert([
            'company_id' => $companyId,
            'purchase_order_id' => $poId,
            'type' => 'new',
            'brand' => $request->brand,
            'model' => $request->model,
            'color' => $request->color ?? 'Standard',
            'ram' => $request->ram,
            'storage' => $request->storage,
            'imei_1' => $request->imei_1,
            'imei_2' => $request->imei_2,
            'purchase_cost' => $phoneCost,
            'selling_price' => (float) $request->selling_price,
            'photo_path' => $photoPath,
            'box_photo_path' => $photoPath,
            'status' => 'in_stock',
            'condition_grade' => 'brand_new',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($request->filled('redirect_to')) {
            return redirect($request->input('redirect_to'))->with('success', "New mobile device {$request->brand} {$request->model} (IMEI: {$request->imei_1}) added to stock (PO #{$poNum})!");
        }

        return redirect()->route('mobileshop.purchase')->with('success', "New mobile device {$request->brand} {$request->model} (IMEI: {$request->imei_1}) added to stock (PO #{$poNum})!");
    }

    /**
     * Panel 2: Second Hand Hub (Pre-Owned Stock & Buyback & Sales)
     */
    public function secondHand()
    {
        abort_unless(auth()->check() && (auth()->user()->can('read-mobileshop-secondhand') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin')), 403, 'Unauthorized access to second-hand hub.');

        $companyId = $this->getCompanyId();
        $mobiles = DB::table('ms_mobile_devices')
            ->where('company_id', $companyId)
            ->where('type', 'second_hand')
            ->orderBy('id', 'desc')
            ->get();

        $availablePhones = $mobiles->where('status', 'in_stock');
        $customers = DB::table('ms_customers')->where('company_id', $companyId)->get();

        return view('mobileshop.second_hand', compact('mobiles', 'availablePhones', 'customers'));
    }

    /**
     * Store Second Hand Buyback (Intake)
     */
    public function storeSecondHand(Request $request)
    {
        abort_unless(auth()->check() && (auth()->user()->can('create-mobileshop-secondhand') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin')), 403, 'Unauthorized action.');

        $request->validate([
            'brand' => 'required',
            'model' => 'required',
            'imei_1' => 'required',
            'purchase_cost' => 'required|numeric',
            'selling_price' => 'required|numeric',
            'customer_buyback_name' => 'required',
            'customer_buyback_phone' => 'required',
        ]);

        $companyId = $this->getCompanyId();

        // Check uniqueness within company
        $exists = DB::table('ms_mobile_devices')
            ->where('company_id', $companyId)
            ->where('imei_1', $request->imei_1)
            ->exists();
        if ($exists) {
            return redirect()->back()->with('error', "A mobile device with IMEI {$request->imei_1} is already registered in inventory!");
        }

        $suppName = 'Walk-in Buyback - ' . $request->customer_buyback_name;
        $supp = DB::table('ms_suppliers')->where('company_id', $companyId)->where('name', $suppName)->first();
        if (!$supp) {
            $suppId = DB::table('ms_suppliers')->insertGetId([
                'company_id' => $companyId,
                'name'       => $suppName,
                'phone'      => $request->customer_buyback_phone,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $suppId = $supp->id;
        }

        $buybackCost = (float) $request->purchase_cost;

        $photoPath = null;
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $file = $request->file('photo');
            $filename = 'sh_' . time() . '_' . rand(100, 999) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/mobiles'), $filename);
            $photoPath = 'uploads/mobiles/' . $filename;
        }

        $bbNum = 'BUYBACK-' . date('Ymd') . '-' . rand(100, 999);
        $poId = DB::table('ms_purchase_orders')->insertGetId([
            'company_id'   => $companyId,
            'supplier_id'  => $suppId,
            'po_number'    => $bbNum,
            'order_date'   => now()->toDateString(),
            'tax_type'     => 'intra_state',
            'subtotal'     => $buybackCost,
            'total_amount' => $buybackCost,
            'amount_paid'  => $buybackCost,
            'balance_due'  => 0,
            'status'       => 'received',
            'created_by'   => auth()->id(),
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        DB::table('ms_purchase_order_items')->insert([
            'purchase_order_id' => $poId,
            'brand'             => $request->brand,
            'model'             => $request->model,
            'variant'           => "Pre-Owned [Grade: " . str_replace('_', ' ', $request->condition_grade ?? 'A') . "] IMEI: {$request->imei_1}",
            'hsn_code'          => '85171300',
            'qty'               => 1,
            'qty_received'      => 1,
            'unit_cost'         => $buybackCost,
            'tax_rate'          => 0,
            'line_total'        => $buybackCost,
        ]);

        DB::table('ms_mobile_devices')->insert([
            'company_id' => $companyId,
            'purchase_order_id' => $poId,
            'type' => 'second_hand',
            'brand' => $request->brand,
            'model' => $request->model,
            'color' => $request->color,
            'ram' => $request->ram,
            'storage' => $request->storage,
            'imei_1' => $request->imei_1,
            'imei_2' => $request->imei_2,
            'purchase_cost' => $buybackCost,
            'selling_price' => (float) $request->selling_price,
            'photo_path' => $photoPath,
            'box_photo_path' => $photoPath,
            'condition_grade' => $request->condition_grade ?? 'like_new_A_plus',
            'battery_health' => $request->battery_health,
            'customer_buyback_name' => $request->customer_buyback_name,
            'customer_buyback_phone' => $request->customer_buyback_phone,
            'customer_buyback_id_proof' => $request->customer_buyback_id_proof,
            'checklist_notes' => $request->checklist_notes,
            'status' => 'in_stock',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($request->filled('redirect_to')) {
            return redirect($request->input('redirect_to'))->with('success', "Second-hand mobile buyback registered (Invoice #{$bbNum})!");
        }

        return redirect()->route('mobileshop.purchase')->with('success', "Second-hand mobile buyback registered (Invoice #{$bbNum})!");
    }

    /**
     * Sell Second-Hand Mobile at Pre-Owned POS Counter
     */
    public function sellSecondHand(Request $request)
    {
        abort_unless(auth()->check() && (auth()->user()->can('sell-mobileshop-secondhand') || auth()->user()->can('create-mobileshop-pos') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin')), 403, 'Unauthorized action.');

        $request->validate([
            'customer_phone' => 'required',
            'customer_name' => 'required',
            'device_id' => 'required|exists:ms_mobile_devices,id',
            'sale_price' => 'required|numeric|min:1',
            'amount_paid' => 'required|numeric|min:0',
            'payment_mode' => 'required|in:cash,upi,card,credit_udhari,split',
        ]);

        $companyId = $this->getCompanyId();

        // Idempotency check
        if ($request->filled('idempotency_key')) {
            $existing = DB::table('ms_mobile_sales')
                ->where('company_id', $companyId)
                ->where('idempotency_key', $request->idempotency_key)
                ->first();
            if ($existing) {
                return redirect()->route('mobileshop.invoice', ['id' => $existing->id])
                    ->with('success', "Pre-owned sale invoice #{$existing->invoice_number} already processed.");
            }
        }

        return DB::transaction(function () use ($request, $companyId) {
            $storeState = $this->getStoreStateCode();
            // Find or create customer
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

            // Lock device row
            $device = DB::table('ms_mobile_devices')
                ->where('company_id', $companyId)
                ->where('id', $request->device_id)
                ->where('status', 'in_stock')
                ->where('type', 'second_hand')
                ->lockForUpdate()
                ->first();

            if (!$device) {
                return redirect()->back()->with('error', 'Selected second-hand device is no longer available!');
            }

            $salePrice = (float) $request->sale_price;
            $amountPaid = (float) $request->amount_paid;
            $taxRate = (float) ($request->tax_rate ?? 18.00);
            $isGst = $request->boolean('is_gst') || ($request->bill_type === 'gst');
            $billType = $isGst ? 'gst' : 'non_gst';

            if ($billType === 'non_gst') {
                $taxRate = 0.00;
                $cgst = 0.00;
                $sgst = 0.00;
                $igst = 0.00;
                $isStateMatch = true;
            } else {
                $taxable = round($salePrice / (1 + ($taxRate / 100)), 2);
                $totalTax = round($salePrice - $taxable, 2);
                $isStateMatch = empty($customer->state_code) || ($customer->state_code === $storeState);
                $halfTax = round($totalTax / 2, 2);

                $cgst = $isStateMatch ? $halfTax : 0.00;
                $sgst = $isStateMatch ? ($totalTax - $halfTax) : 0.00;
                $igst = !$isStateMatch ? $totalTax : 0.00;
            }

            $udhariAmount = max(0.00, $salePrice - $amountPaid);

            // Atomic Sequential Invoice Number for Second-Hand
            $invoiceNumber = $this->getNextInvoiceNumber($companyId, 'SH');

            $saleId = DB::table('ms_mobile_sales')->insertGetId([
                'company_id' => $companyId,
                'idempotency_key' => $request->idempotency_key ?? Str::uuid()->toString(),
                'customer_id' => $customer->id,
                'invoice_number' => $invoiceNumber,
                'bill_type' => $billType,
                'device_id' => $device->id,
                'sale_price' => $salePrice,
                'tax_rate' => $taxRate,
                'tax_type' => $isStateMatch ? 'intra_state' : 'inter_state',
                'cgst_amount' => $cgst,
                'sgst_amount' => $sgst,
                'igst_amount' => $igst,
                'total_amount' => $salePrice,
                'amount_paid' => $amountPaid,
                'udhari_amount' => $udhariAmount,
                'payment_mode' => $request->payment_mode,
                'sold_by' => auth()->id(),
                'status' => 'completed',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Update Device Status
            DB::table('ms_mobile_devices')->where('id', $device->id)->update([
                'status' => 'sold',
                'selling_price' => $salePrice,
                'updated_at' => now(),
            ]);

            // Update Customer Khata if Udhari
            if ($udhariAmount > 0) {
                DB::table('ms_customers')->where('id', $customer->id)->increment('udhari_balance', $udhariAmount);
                $newBal = $customer->udhari_balance + $udhariAmount;
                DB::table('ms_customer_khata_transactions')->insert([
                    'company_id' => $companyId,
                    'customer_id' => $customer->id,
                    'type' => 'udhari_sale',
                    'sale_id' => $saleId,
                    'amount' => $udhariAmount,
                    'balance_after' => $newBal,
                    'remarks' => "Udhari on Pre-Owned Phone Sale #{$invoiceNumber}",
                    'recorded_by' => auth()->id(),
                    'created_at' => now(),
                ]);
            }

            return redirect()->route('mobileshop.invoice', ['id' => $saleId])->with('success', "Pre-owned sale #{$invoiceNumber} successfully recorded!");
        });
    }

    /**
     * Accessories Hub — Redirects cleanly to Unified Stock Hub
     */
    public function accessories()
    {
        return redirect()->route('mobileshop.stock');
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
     * Delete Custom Part Category
     */
    public function deleteCategory($id)
    {
        abort_unless(auth()->check() && (auth()->user()->can('create-mobileshop-accessories') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin')), 403, 'Unauthorized action.');

        $companyId = $this->getCompanyId();
        DB::table('ms_part_categories')->where('company_id', $companyId)->where('id', $id)->delete();

        return redirect()->route('mobileshop.stock')->with('success', 'Category deleted successfully!');
    }

    /**
     * Store / Restock Part or Accessory
     */
    public function storePart(Request $request)
    {
        abort_unless(auth()->check() && (auth()->user()->can('create-mobileshop-accessories') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin') || auth()->user()->hasRole('accessories-staff')), 403, 'Unauthorized action.');

        $request->validate([
            'name' => 'required|string|max:150',
            'category' => 'required|string|max:100',
            'brand' => 'nullable|string|max:100',
            'compatible_model' => 'nullable|string|max:100',
            'unit_cost' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock_qty' => 'required|numeric|min:0',
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
            'company_id' => $companyId,
            'category' => $request->category,
            'category_id' => $categoryRow?->id,
            'is_gift_eligible' => $isGift,
            'brand' => $request->brand ?: 'Universal',
            'compatible_model' => $request->compatible_model ?: 'Universal / Multi-Model',
            'display_type' => $request->display_type ?? 'na',
            'hsn_code' => $request->hsn_code ?? '85177090',
            'name' => $request->name,
            'unit_cost' => (float) $request->unit_cost,
            'selling_price' => (float) $request->selling_price,
            'stock_qty' => (int) $request->stock_qty,
            'min_stock_alert' => $request->min_stock_alert ?? 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ms_parts_inventory_history')->insert([
            'part_id' => $partId,
            'type' => 'addition',
            'quantity' => (int) $request->stock_qty,
            'balance_after' => (int) $request->stock_qty,
            'reference' => 'Initial Stock Intake',
            'user_id' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($request->filled('redirect_to')) {
            return redirect($request->input('redirect_to'))->with('success', 'Part/Accessory successfully added to inventory!');
        }

        return redirect()->route('mobileshop.purchase')->with('success', 'Part/Accessory successfully added to inventory!');
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
                        'display_type' => $item['display_type'] ?? 'na',
                        'hsn_code' => $item['hsn_code'] ?? '85177090',
                        'unit_cost' => $unitCost,
                        'selling_price' => $sellingPrice,
                        'stock_qty' => $qty,
                        'min_stock_alert' => $item['min_stock_alert'] ?? 3,
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

        if ($request->filled('redirect_to')) {
            return redirect($request->input('redirect_to'))->with('success', "Bulk restock successful! Added {$summary['totalUnits']} units (Value: ₹" . number_format($summary['totalCost'], 2) . ") into inventory.");
        }

        return redirect()->route('mobileshop.purchase')->with('success', "Bulk restock successful! Added {$summary['totalUnits']} units (Value: ₹" . number_format($summary['totalCost'], 2) . ") into inventory.");
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
     * Resolve Accessory Sale details with customer and line items
     */
    private function resolveAccessorySaleDetails(int $companyId, int $id): array
    {
        $sale = DB::table('ms_accessory_sales')
            ->leftJoin('ms_customers', 'ms_accessory_sales.customer_id', '=', 'ms_customers.id')
            ->select('ms_accessory_sales.*', 'ms_customers.name as customer_name', 'ms_customers.phone as customer_phone', 'ms_customers.gstin as customer_gstin', 'ms_customers.address as customer_address', 'ms_customers.udhari_balance as current_udhari_balance')
            ->where('ms_accessory_sales.company_id', $companyId)
            ->where('ms_accessory_sales.id', $id)
            ->first();

        if (!$sale) {
            abort(404, 'Accessory invoice not found.');
        }

        $items = DB::table('ms_accessory_sale_items')
            ->where('company_id', $companyId)
            ->where('accessory_sale_id', $id)
            ->get();

        $amountInWords = self::amountToWords($sale->total_amount);
        $customerStatement = $sale->customer_id ? $this->buildCustomerLedgerStatement($companyId, (int) $sale->customer_id) : null;

        return compact('sale', 'items', 'amountInWords', 'customerStatement');
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
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('mobileshop.pdf.accessory_invoice', $data);
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
     * Void / Cancel a Mobile Device Sale (Sales Return)
     */
    public function voidMobileSale(Request $request, $id)
    {
        abort_unless(auth()->check() && (
            auth()->user()->can('void-mobileshop-sales') ||
            auth()->user()->can('read-mobileshop-sales') ||
            auth()->user()->can('create-mobileshop-pos') ||
            auth()->user()->can('create-sale-phones') ||
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

        return DB::transaction(function () use ($request, $id, $companyId, $shouldRestock, $auditReason) {
            $sale = DB::table('ms_mobile_sales')
                ->where('company_id', $companyId)
                ->where('id', $id)
                ->lockForUpdate()
                ->first();

            if (!$sale || $sale->status === 'voided') {
                return redirect()->back()->with('error', 'Sale is already voided or cannot be found.');
            }

            // Update Device status based on condition
            $newDeviceStatus = $shouldRestock ? 'in_stock' : 'returned';
            DB::table('ms_mobile_devices')->where('company_id', $companyId)->where('id', $sale->device_id)->update([
                'status' => $newDeviceStatus,
                'updated_at' => now(),
            ]);

            // Restore gifts if any to accessories & parts inventory
            $gifts = DB::table('ms_sale_gifts')->where('sale_id', $id)->get();
            foreach ($gifts as $g) {
                $part = DB::table('ms_parts_inventory')->where('id', $g->gift_id)->lockForUpdate()->first();
                if ($part) {
                    $newBalance = $part->stock_qty + ($shouldRestock ? $g->qty : 0);
                    if ($shouldRestock) {
                        DB::table('ms_parts_inventory')->where('id', $part->id)->update([
                            'stock_qty' => $newBalance,
                            'updated_at' => now(),
                        ]);
                    }
                    DB::table('ms_parts_inventory_history')->insert([
                        'part_id' => $part->id,
                        'type' => $shouldRestock ? 'addition' : 'deduction',
                        'quantity' => $g->qty,
                        'balance_after' => $shouldRestock ? $newBalance : $part->stock_qty,
                        'reference' => "Restored Gift from Returned Mobile Sale #{$sale->invoice_number}" . ($shouldRestock ? "" : " (Marked Defective)"),
                        'user_id' => auth()->id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    if ($shouldRestock) {
                        DB::table('ms_gifts')->where('id', $g->gift_id)->increment('stock_qty', $g->qty);
                    }
                }
            }

            // Reverse Customer Khata Balance if Udhari was recorded
            if ($sale->udhari_amount > 0 && $sale->customer_id) {
                $customer = DB::table('ms_customers')->where('id', $sale->customer_id)->lockForUpdate()->first();
                if ($customer) {
                    $newBal = max(0.00, $customer->udhari_balance - $sale->udhari_amount);
                    DB::table('ms_customers')->where('id', $customer->id)->update([
                        'udhari_balance' => $newBal,
                        'updated_at' => now(),
                    ]);

                    DB::table('ms_customer_khata_transactions')->insert([
                        'company_id' => $companyId,
                        'customer_id' => $customer->id,
                        'type' => 'adjustment',
                        'amount' => $sale->udhari_amount,
                        'balance_after' => $newBal,
                        'remarks' => "Reversal for Returned Mobile Sale #{$sale->invoice_number}",
                        'recorded_by' => auth()->id(),
                        'created_at' => now(),
                    ]);
                }
            }

            // Mark Sale as Voided
            DB::table('ms_mobile_sales')->where('id', $id)->update([
                'status' => 'voided',
                'voided_by' => auth()->id(),
                'voided_at' => now(),
                'void_reason' => $auditReason . ($shouldRestock ? " [Restocked to Inventory]" : " [Marked Defective]"),
                'updated_at' => now(),
            ]);

            return redirect()->back()->with('success', "Sale #{$sale->invoice_number} has been Returned and device processed.");
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
     * Customer Khata (Udhari) Screen — Scoped to Respective Logins / Roles
     */
    public function khata()
    {
        abort_unless(auth()->check() && (
            auth()->user()->can('read-mobileshop-khata') ||
            auth()->user()->can('read-reports-khata') ||
            auth()->user()->can('read-mobileshop-reports') ||
            auth()->user()->can('read-mobileshop-sales') ||
            auth()->user()->can('read-mobileshop-accessories') ||
            auth()->user()->can('read-mobileshop-repairs') ||
            auth()->user()->hasRole('admin') ||
            auth()->user()->hasRole('store-admin') ||
            auth()->user()->hasRole('sales-staff') ||
            auth()->user()->hasRole('accessories-staff') ||
            auth()->user()->hasRole('cover-staff') ||
            auth()->user()->hasRole('repair-technician')
        ), 403, 'Unauthorized access to customer khata.');

        $companyId = $this->getCompanyId();
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin') || $user->hasRole('store-admin');
        $isSalesStaff = $user->hasRole('sales-staff');
        $isAccStaff = $user->hasRole('accessories-staff') || $user->hasRole('cover-staff');
        $isTech = $user->hasRole('repair-technician');

        $txQuery = DB::table('ms_customer_khata_transactions')
            ->join('ms_customers', 'ms_customer_khata_transactions.customer_id', '=', 'ms_customers.id')
            ->select(
                'ms_customer_khata_transactions.*',
                'ms_customers.name as customer_name',
                'ms_customers.phone as customer_phone',
                'ms_customers.udhari_balance as current_customer_due'
            )
            ->where('ms_customer_khata_transactions.company_id', $companyId);

        // Scope transactions to the respective department/login
        if ($isAdmin) {
            // Admin sees all store transactions
        } elseif ($isAccStaff) {
            $txQuery->where(function ($q) use ($user) {
                $q->where('ms_customer_khata_transactions.recorded_by', $user->id)
                  ->orWhere('ms_customer_khata_transactions.remarks', 'LIKE', '%Accessory%')
                  ->orWhere('ms_customer_khata_transactions.remarks', 'LIKE', '%ACC-%')
                  ->orWhere('ms_customer_khata_transactions.remarks', 'LIKE', '%Cover%')
                  ->orWhere('ms_customer_khata_transactions.remarks', 'LIKE', '%Tempered%');
            });
        } elseif ($isSalesStaff) {
            $txQuery->where(function ($q) use ($user) {
                $q->where('ms_customer_khata_transactions.recorded_by', $user->id)
                  ->orWhere('ms_customer_khata_transactions.remarks', 'LIKE', '%Mobile%')
                  ->orWhere('ms_customer_khata_transactions.remarks', 'LIKE', '%MOB-%')
                  ->orWhere('ms_customer_khata_transactions.remarks', 'LIKE', '%Phone%');
            });
        } elseif ($isTech) {
            $txQuery->where(function ($q) use ($user) {
                $q->where('ms_customer_khata_transactions.recorded_by', $user->id)
                  ->orWhere('ms_customer_khata_transactions.remarks', 'LIKE', '%Repair%')
                  ->orWhere('ms_customer_khata_transactions.remarks', 'LIKE', '%Display%')
                  ->orWhere('ms_customer_khata_transactions.remarks', 'LIKE', '%Service%');
            });
        } else {
            $txQuery->where('ms_customer_khata_transactions.recorded_by', $user->id);
        }

        $transactions = $txQuery->orderBy('ms_customer_khata_transactions.id', 'desc')->get();

        // Scope customers list
        if ($isAdmin) {
            $customers = DB::table('ms_customers')
                ->where('company_id', $companyId)
                ->orderBy('udhari_balance', 'desc')
                ->orderBy('name', 'asc')
                ->get();
        } else {
            $relevantCustomerIds = $transactions->pluck('customer_id')->unique()->toArray();
            $customers = DB::table('ms_customers')
                ->where('company_id', $companyId)
                ->whereIn('id', $relevantCustomerIds)
                ->orderBy('udhari_balance', 'desc')
                ->orderBy('name', 'asc')
                ->get();

            // If empty, fetch all to allow selecting any customer for repayment
            if ($customers->isEmpty()) {
                $customers = DB::table('ms_customers')
                    ->where('company_id', $companyId)
                    ->orderBy('udhari_balance', 'desc')
                    ->orderBy('name', 'asc')
                    ->get();
            }
        }

        $accessoryInvoices = DB::table('ms_accessory_sales')
            ->where('company_id', $companyId)
            ->pluck('id', 'invoice_number')
            ->toArray();

        $mobileInvoices = DB::table('ms_mobile_sales')
            ->where('company_id', $companyId)
            ->pluck('id', 'invoice_number')
            ->toArray();

        return view('mobileshop.khata', compact('customers', 'transactions', 'accessoryInvoices', 'mobileInvoices', 'isAdmin'));
    }

    /**
     * Collect Khata Payment
     */
    public function collectKhata(Request $request)
    {
        abort_unless(auth()->check() && (
            auth()->user()->can('create-mobileshop-khata') ||
            auth()->user()->can('read-reports-khata') ||
            auth()->user()->can('read-mobileshop-reports') ||
            auth()->user()->can('read-mobileshop-sales') ||
            auth()->user()->can('create-sale-accessories') ||
            auth()->user()->can('create-mobileshop-pos') ||
            auth()->user()->hasRole('admin') ||
            auth()->user()->hasRole('store-admin') ||
            auth()->user()->hasRole('sales-staff') ||
            auth()->user()->hasRole('accessories-staff') ||
            auth()->user()->hasRole('cover-staff') ||
            auth()->user()->hasRole('repair-technician')
        ), 403, 'Unauthorized action.');

        $request->validate([
            'customer_id' => 'required|exists:ms_customers,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_mode' => 'required|in:cash,upi,bank_transfer,cheque',
        ]);

        $companyId = $this->getCompanyId();

        return DB::transaction(function () use ($request, $companyId) {
            $customer = DB::table('ms_customers')->where('company_id', $companyId)->where('id', $request->customer_id)->lockForUpdate()->first();
            if (!$customer) {
                return redirect()->back()->with('error', 'Customer not found.');
            }
            $amount = (float) $request->amount;
            $newBalance = max(0.00, $customer->udhari_balance - $amount);

            DB::table('ms_customers')->where('id', $customer->id)->update([
                'udhari_balance' => $newBalance,
                'updated_at' => now(),
            ]);

            DB::table('ms_customer_khata_transactions')->insert([
                'company_id' => $companyId,
                'customer_id' => $customer->id,
                'type' => 'payment_received',
                'amount' => $amount,
                'balance_after' => $newBalance,
                'payment_mode' => $request->payment_mode,
                'reference_no' => $request->reference_no,
                'remarks' => $request->remarks ?? 'Khata repayment received',
                'recorded_by' => auth()->id(),
                'created_at' => now(),
            ]);

            return redirect()->route('mobileshop.khata')->with('success', "Repayment of ₹" . number_format($amount, 2) . " received from {$customer->name}! New balance: ₹" . number_format($newBalance, 2));
        });
    }

    /**
     * Build Tally-Style Customer Ledger Statement (Chronological with running balance)
     */
    public function buildCustomerLedgerStatement(int $companyId, int $customerId): array
    {
        $customer = DB::table('ms_customers')->where('company_id', $companyId)->where('id', $customerId)->first();
        if (!$customer) {
            return [
                'customer' => null,
                'ledger' => collect([]),
                'totalBilled' => 0.00,
                'totalPaid' => 0.00,
                'closingBalance' => 0.00,
            ];
        }

        $prefix = DB::getTablePrefix();

        // 1. Mobile Sales
        $mobileSales = DB::table('ms_mobile_sales')
            ->join('ms_mobile_devices', 'ms_mobile_sales.device_id', '=', 'ms_mobile_devices.id')
            ->where('ms_mobile_sales.company_id', $companyId)
            ->where('ms_mobile_sales.customer_id', $customerId)
            ->where('ms_mobile_sales.status', '!=', 'voided')
            ->select(
                'ms_mobile_sales.id',
                'ms_mobile_sales.created_at',
                'ms_mobile_sales.invoice_number as ref_no',
                DB::raw("CONCAT({$prefix}ms_mobile_devices.brand, ' ', {$prefix}ms_mobile_devices.model, ' (IMEI: ', {$prefix}ms_mobile_devices.imei_1, ')') as particulars"),
                'ms_mobile_sales.total_amount as billed_amount',
                'ms_mobile_sales.amount_paid as paid_amount',
                'ms_mobile_sales.udhari_amount as due_amount',
                DB::raw("'mobile_sale' as entry_type")
            )
            ->get();

        // 2. Accessory Sales
        $accSales = DB::table('ms_accessory_sales')
            ->where('ms_accessory_sales.company_id', $companyId)
            ->where('ms_accessory_sales.customer_id', $customerId)
            ->where('ms_accessory_sales.status', '!=', 'voided')
            ->select(
                'ms_accessory_sales.id',
                'ms_accessory_sales.created_at',
                'ms_accessory_sales.invoice_number as ref_no',
                DB::raw("'Accessory / Parts Counter Bill' as particulars"),
                'ms_accessory_sales.total_amount as billed_amount',
                'ms_accessory_sales.amount_paid as paid_amount',
                'ms_accessory_sales.udhari_amount as due_amount',
                DB::raw("'accessory_sale' as entry_type")
            )
            ->get();

        // 3. Khata Repayments & Adjustments
        $repayments = DB::table('ms_customer_khata_transactions')
            ->where('company_id', $companyId)
            ->where('customer_id', $customerId)
            ->where('type', '!=', 'udhari_sale')
            ->select(
                'id',
                'created_at',
                DB::raw("COALESCE(reference_no, CONCAT('KHATA-', id)) as ref_no"),
                DB::raw("COALESCE(remarks, 'Payment / Settlement Received') as particulars"),
                DB::raw("0.00 as billed_amount"),
                'amount as paid_amount',
                DB::raw("0.00 as due_amount"),
                'type as entry_type'
            )
            ->get();

        // Merge and sort chronologically
        $allEntries = $mobileSales->concat($accSales)->concat($repayments)->sortBy('created_at')->values();

        $runningBalance = 0.00;
        $totalBilled = 0.00;
        $totalPaid = 0.00;

        $ledger = $allEntries->map(function ($entry) use (&$runningBalance, &$totalBilled, &$totalPaid) {
            $billed = (float) $entry->billed_amount;
            $paid = (float) $entry->paid_amount;
            
            $totalBilled += $billed;
            $totalPaid += $paid;

            if ($entry->entry_type === 'adjustment') {
                $runningBalance = max(0.00, $runningBalance - $paid);
            } else {
                $runningBalance = max(0.00, $runningBalance + ($billed - $paid));
            }

            return (object) [
                'id' => $entry->id,
                'created_at' => $entry->created_at,
                'date' => date('d/m/Y', strtotime($entry->created_at)),
                'datetime' => date('d M Y, h:i A', strtotime($entry->created_at)),
                'ref_no' => $entry->ref_no,
                'particulars' => $entry->particulars,
                'billed' => $billed,
                'paid' => $paid,
                'balance_left' => $runningBalance,
                'entry_type' => $entry->entry_type,
            ];
        });

        return [
            'customer' => $customer,
            'ledger' => $ledger,
            'totalBilled' => $totalBilled,
            'totalPaid' => $totalPaid,
            'closingBalance' => (float) $customer->udhari_balance,
        ];
    }

    /**
     * Customer Account Statement / Tally Ledger View (A4 Printable)
     */
    public function customerStatement($id)
    {
        abort_unless(auth()->check() && (
            auth()->user()->can('read-mobileshop-khata') ||
            auth()->user()->can('read-reports-khata') ||
            auth()->user()->can('read-mobileshop-sales') ||
            auth()->user()->can('read-mobileshop-accessories') ||
            auth()->user()->can('read-mobileshop-dashboard') ||
            auth()->user()->hasRole('admin') ||
            auth()->user()->hasRole('store-admin') ||
            auth()->user()->hasRole('sales-staff')
        ), 403, 'Unauthorized access to customer statement.');

        $companyId = $this->getCompanyId();
        $statement = $this->buildCustomerLedgerStatement($companyId, (int) $id);

        if (!$statement['customer']) {
            abort(404, 'Customer not found.');
        }

        return view('mobileshop.customer_statement', $statement);
    }

    /**
     * Download Customer Statement PDF
     */
    public function customerStatementPdf($id)
    {
        abort_unless(auth()->check() && (
            auth()->user()->can('read-mobileshop-khata') ||
            auth()->user()->can('read-reports-khata') ||
            auth()->user()->can('read-mobileshop-sales') ||
            auth()->user()->hasRole('admin') ||
            auth()->user()->hasRole('store-admin') ||
            auth()->user()->hasRole('sales-staff')
        ), 403, 'Unauthorized access to customer statement PDF.');

        $companyId = $this->getCompanyId();
        $statement = $this->buildCustomerLedgerStatement($companyId, (int) $id);

        if (!$statement['customer']) {
            abort(404, 'Customer not found.');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('mobileshop.pdf.customer_statement', $statement);
        $pdf->setPaper('a4', 'portrait');
        $safeName = Str::slug($statement['customer']->name, '_');
        return $pdf->download("Statement-{$safeName}.pdf");
    }

    /**
     * Purchase Orders & Supplier Ledger (Admin ONLY)
     */
    public function purchaseOrders()
    {
        abort_unless(auth()->check() && (auth()->user()->can('read-mobileshop-procurement') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin')), 403, 'Unauthorized access to procurement ledger.');

        $companyId = $this->getCompanyId();
        $purchaseOrders = DB::table('ms_purchase_orders')
            ->join('ms_suppliers', 'ms_purchase_orders.supplier_id', '=', 'ms_suppliers.id')
            ->select('ms_purchase_orders.*', 'ms_suppliers.name as supplier_name', 'ms_suppliers.phone as supplier_phone', 'ms_suppliers.gstin as supplier_gstin')
            ->where('ms_purchase_orders.company_id', $companyId)
            ->orderBy('ms_purchase_orders.id', 'desc')
            ->get();

        $suppliers = DB::table('ms_suppliers')->where('company_id', $companyId)->get();
        $wallets = DB::table('ms_supplier_credit_wallets')
            ->join('ms_suppliers', 'ms_supplier_credit_wallets.supplier_id', '=', 'ms_suppliers.id')
            ->select('ms_supplier_credit_wallets.*', 'ms_suppliers.name as supplier_name')
            ->where('ms_supplier_credit_wallets.company_id', $companyId)
            ->get();

        return view('mobileshop.purchase_orders', compact('purchaseOrders', 'suppliers', 'wallets'));
    }

    /**
     * Record Supplier Payment / Advance / Settlement
     */
    public function recordSupplierPayment(Request $request)
    {
        abort_unless(auth()->check() && (auth()->user()->can('create-mobileshop-procurement') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin')), 403, 'Unauthorized action.');

        $request->validate([
            'supplier_id' => 'required|exists:ms_suppliers,id',
            'amount' => 'required|numeric|min:1',
            'payment_mode' => 'required|in:cash,bank_transfer,cheque,upi',
        ]);

        $companyId = $this->getCompanyId();

        return DB::transaction(function () use ($request, $companyId) {
            $supplierId = $request->supplier_id;
            $paymentAmount = (float) $request->amount;
            $poId = $request->purchase_order_id;

            $wallet = DB::table('ms_supplier_credit_wallets')->where('company_id', $companyId)->where('supplier_id', $supplierId)->lockForUpdate()->first();
            if (!$wallet) {
                $walletId = DB::table('ms_supplier_credit_wallets')->insertGetId([
                    'company_id' => $companyId,
                    'supplier_id' => $supplierId,
                    'credit_balance' => 0.00,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $wallet = DB::table('ms_supplier_credit_wallets')->where('id', $walletId)->first();
            }

            if ($poId) {
                $po = DB::table('ms_purchase_orders')->where('company_id', $companyId)->where('id', $poId)->lockForUpdate()->first();
                if (!$po) {
                    return redirect()->back()->with('error', 'Purchase order not found.');
                }
                $applyToPO = min($paymentAmount, (float) $po->balance_due);
                $newPaid = (float) $po->amount_paid + $applyToPO;
                $newDue = (float) $po->balance_due - $applyToPO;
                $newStatus = ($newDue <= 0) ? 'paid' : 'partially_paid';

                DB::table('ms_purchase_orders')->where('id', $poId)->update([
                    'amount_paid' => $newPaid,
                    'balance_due' => $newDue,
                    'status' => $newStatus,
                    'updated_at' => now(),
                ]);

                DB::table('ms_supplier_payments')->insert([
                    'company_id' => $companyId,
                    'supplier_id' => $supplierId,
                    'purchase_order_id' => $poId,
                    'amount' => $applyToPO,
                    'payment_date' => now()->toDateString(),
                    'mode' => $request->payment_mode,
                    'reference_no' => $request->reference_no,
                    'remarks' => $request->remarks,
                    'recorded_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $excess = $paymentAmount - $applyToPO;
                if ($excess > 0) {
                    $newCredit = (float) $wallet->credit_balance + $excess;
                    DB::table('ms_supplier_credit_wallets')->where('supplier_id', $supplierId)->update([
                        'credit_balance' => $newCredit,
                        'updated_at' => now(),
                    ]);

                    DB::table('ms_supplier_credit_transactions')->insert([
                        'supplier_id' => $supplierId,
                        'txn_type' => 'credit_added',
                        'amount' => $excess,
                        'related_po_id' => $poId,
                        'balance_after' => $newCredit,
                        'remarks' => "Excess overpayment on PO #{$po->po_number} credited to wallet",
                        'txn_date' => now(),
                    ]);
                }
            } else {
                $newCredit = (float) $wallet->credit_balance + $paymentAmount;
                DB::table('ms_supplier_credit_wallets')->where('supplier_id', $supplierId)->update([
                    'credit_balance' => $newCredit,
                    'updated_at' => now(),
                ]);

                DB::table('ms_supplier_credit_transactions')->insert([
                    'supplier_id' => $supplierId,
                    'txn_type' => 'credit_added',
                    'amount' => $paymentAmount,
                    'related_po_id' => null,
                    'balance_after' => $newCredit,
                    'remarks' => "Direct advance payment to supplier wallet",
                    'txn_date' => now(),
                ]);
            }

            return redirect()->route('mobileshop.purchase_orders')->with('success', 'Supplier payment successfully logged and ledger updated!');
        });
    }

    /**
     * Repair Service Portal & Job Sheets
     */
    public function repairs(Request $request)
    {
        abort_unless(auth()->check() && (auth()->user()->can('read-mobileshop-repairs') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin') || auth()->user()->hasRole('repair-technician')), 403, 'Unauthorized access to repair service desk.');

        $companyId = $this->getCompanyId();
        $query = DB::table('ms_repair_tickets')
            ->join('ms_customers', 'ms_repair_tickets.customer_id', '=', 'ms_customers.id')
            ->select('ms_repair_tickets.*', 'ms_customers.name as customer_name', 'ms_customers.phone as customer_phone')
            ->where('ms_repair_tickets.company_id', $companyId);

        if ($request->filled('status')) {
            $query->where('ms_repair_tickets.status', $request->status);
        }

        $tickets = $query->orderBy('ms_repair_tickets.id', 'desc')->get();

        $ticketIds = $tickets->pluck('id')->toArray();
        $ticketParts = DB::table('ms_repair_ticket_parts')
            ->join('ms_parts_inventory', 'ms_repair_ticket_parts.part_id', '=', 'ms_parts_inventory.id')
            ->whereIn('ms_repair_ticket_parts.repair_ticket_id', $ticketIds)
            ->select('ms_repair_ticket_parts.*', 'ms_parts_inventory.name as part_name', 'ms_parts_inventory.category as part_category')
            ->get()
            ->groupBy('repair_ticket_id');

        foreach ($tickets as $t) {
            $t->decrypted_pin = $t->passcode_encrypted ? Crypt::decryptString($t->passcode_encrypted) : 'None';
            $t->decrypted_pattern = $t->pattern_code_encrypted ? Crypt::decryptString($t->pattern_code_encrypted) : 'None';
            $t->used_parts = $ticketParts->get($t->id, collect());
        }

        $parts = DB::table('ms_parts_inventory')->where('company_id', $companyId)->orderBy('name', 'asc')->get();
        $customers = DB::table('ms_customers')->where('company_id', $companyId)->orderBy('name', 'asc')->get();
        $repairs = $tickets;

        return view('mobileshop.repairs', compact('tickets', 'repairs', 'parts', 'customers'));
    }

    /**
     * Store Repair Job Sheet
     */
    public function storeRepair(Request $request)
    {
        abort_unless(auth()->check() && (auth()->user()->can('read-mobileshop-repairs') || auth()->user()->can('update-mobileshop-repairs') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin') || auth()->user()->hasRole('repair-technician')), 403, 'Unauthorized action.');

        $request->validate([
            'customer_phone' => 'required|string|min:7|max:20',
            'customer_name' => 'required|string|min:2|max:100',
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'reported_faults' => 'required|string',
        ]);

        $companyId = $this->getCompanyId();

        return DB::transaction(function () use ($request, $companyId) {
            $customer = DB::table('ms_customers')->where('company_id', $companyId)->where('phone', $request->customer_phone)->first();
            if (!$customer) {
                $customerId = DB::table('ms_customers')->insertGetId([
                    'company_id' => $companyId,
                    'name' => $request->customer_name,
                    'phone' => $request->customer_phone,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $customerId = $customer->id;
            }

            // Atomic Sequential Ticket Number (e.g. REP-202608-0001)
            $ticketNo = $this->getNextInvoiceNumber($companyId, 'REP');
            $passcodeEnc = !empty($request->passcode) ? Crypt::encryptString($request->passcode) : null;
            $patternEnc = !empty($request->pattern_code) ? Crypt::encryptString($request->pattern_code) : null;

            $estCost = (float) ($request->estimated_cost ?? 0.00);
            $advPaid = (float) ($request->advance_paid ?? 0.00);

            DB::table('ms_repair_tickets')->insert([
                'company_id' => $companyId,
                'ticket_number' => $ticketNo,
                'customer_id' => $customerId,
                'brand' => $request->brand,
                'model' => $request->model,
                'imei_serial' => $request->imei_serial,
                'passcode_encrypted' => $passcodeEnc,
                'pattern_code_encrypted' => $patternEnc,
                'reported_faults' => $request->reported_faults,
                'physical_condition' => $request->physical_condition,
                'status' => 'received',
                'estimated_cost' => $estCost,
                'total_amount' => $estCost,
                'advance_paid' => $advPaid,
                'balance_due' => max(0.00, $estCost - $advPaid),
                'received_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return redirect()->route('mobileshop.repairs')->with('success', "Repair ticket #{$ticketNo} successfully created!");
        });
    }

    /**
     * Update Repair Ticket Status & Consume Parts
     */
    public function updateRepairStatus(Request $request, $id)
    {
        abort_unless(auth()->check() && (auth()->user()->can('update-mobileshop-repairs') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin') || auth()->user()->hasRole('repair-technician')), 403, 'Unauthorized action.');

        $companyId = $this->getCompanyId();

        return DB::transaction(function () use ($request, $id, $companyId) {
            $ticket = DB::table('ms_repair_tickets')->where('company_id', $companyId)->where('id', $id)->lockForUpdate()->first();
            if (!$ticket) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Ticket not found'], 404);
                }
                return redirect()->back()->with('error', 'Ticket not found');
            }

            $status = $request->status ?? $ticket->status;
            $laborCharge = $request->has('labor_charge') && $request->labor_charge !== null ? (float) $request->labor_charge : (float) $ticket->labor_charge;
            $totalPartsCost = (float) $ticket->parts_cost;
            $partQty = max(1, (int) ($request->part_qty ?? 1));

            // Consume part from inventory if selected
            if ($request->has('consumed_part_id') && !empty($request->consumed_part_id)) {
                $part = DB::table('ms_parts_inventory')->where('company_id', $companyId)->where('id', $request->consumed_part_id)->lockForUpdate()->first();
                if ($part) {
                    if ($part->stock_qty < $partQty) {
                        if ($request->ajax() || $request->wantsJson()) {
                            return response()->json(['success' => false, 'message' => "Insufficient stock for {$part->name}! Available: {$part->stock_qty}"], 422);
                        }
                        return redirect()->back()->with('error', "Insufficient stock for {$part->name}! Available: {$part->stock_qty}");
                    }
                    
                    $newStock = $part->stock_qty - $partQty;
                    DB::table('ms_parts_inventory')->where('id', $part->id)->update([
                        'stock_qty' => $newStock,
                        'updated_at' => now(),
                    ]);
                    
                    // Log deduction in history
                    DB::table('ms_parts_inventory_history')->insert([
                        'part_id' => $part->id,
                        'type' => 'deduction',
                        'quantity' => $partQty,
                        'balance_after' => $newStock,
                        'reference' => 'Used in Repair Ticket #' . $ticket->ticket_number,
                        'user_id' => auth()->id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Record part usage against ticket
                    DB::table('ms_repair_ticket_parts')->insert([
                        'repair_ticket_id' => $ticket->id,
                        'part_id' => $part->id,
                        'quantity' => $partQty,
                        'unit_cost' => $part->unit_cost,
                        'unit_price' => $part->selling_price,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $totalPartsCost += ((float) $part->selling_price * $partQty);
                }
            }

            $additionalPayment = (float) ($request->additional_payment ?? 0.00);
            $totalAdvance = (float) $ticket->advance_paid + $additionalPayment;
            $grandTotal = $laborCharge + $totalPartsCost;
            if ($grandTotal == 0 && (float) $ticket->estimated_cost > 0) {
                $grandTotal = (float) $ticket->estimated_cost;
            }
            $balanceDue = max(0.00, $grandTotal - $totalAdvance);

            DB::table('ms_repair_tickets')->where('id', $id)->update([
                'status' => $status,
                'labor_charge' => $laborCharge,
                'parts_cost' => $totalPartsCost,
                'total_amount' => $grandTotal,
                'advance_paid' => $totalAdvance,
                'balance_due' => $balanceDue,
                'completed_at' => ($status === 'ready' || $status === 'delivered') ? ($ticket->completed_at ?? now()) : $ticket->completed_at,
                'delivered_at' => ($status === 'delivered') ? ($ticket->delivered_at ?? now()) : $ticket->delivered_at,
                'updated_at' => now(),
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Ticket #{$ticket->ticket_number} updated to status: {$status}!",
                    'ticket' => [
                        'id' => $id,
                        'status' => $status,
                        'labor_charge' => $laborCharge,
                        'parts_cost' => $totalPartsCost,
                        'total_amount' => $grandTotal,
                        'advance_paid' => $totalAdvance,
                        'balance_due' => $balanceDue,
                    ]
                ]);
            }

            return redirect()->route('mobileshop.repairs')->with('success', "Ticket #{$ticket->ticket_number} updated to status: {$status}!");
        });
    }

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

    /**
     * Reports Center (Sales, Purchases, Stock Valuation, GST & Debtor Ledgers)
     */
    public function reports(Request $request)
    {
        abort_unless(auth()->check() && (
            auth()->user()->can('read-mobileshop-reports') ||
            auth()->user()->can('read-reports-khata') ||
            auth()->user()->can('read-reports-financial') ||
            auth()->user()->hasRole('admin') ||
            auth()->user()->hasRole('store-admin')
        ), 403, 'Unauthorized access to reports.');

        $companyId = $this->getCompanyId();
        $niche     = $this->getUserNiche();
        $filter    = $request->query('period', 'all');

        $querySales = DB::table('ms_mobile_sales')
            ->join('ms_customers', 'ms_mobile_sales.customer_id', '=', 'ms_customers.id')
            ->join('ms_mobile_devices', 'ms_mobile_sales.device_id', '=', 'ms_mobile_devices.id')
            ->select('ms_mobile_sales.*', 'ms_customers.name as customer_name', 'ms_customers.phone as customer_phone', 'ms_mobile_devices.brand', 'ms_mobile_devices.model', 'ms_mobile_devices.imei_1', 'ms_mobile_devices.purchase_cost')
            ->where('ms_mobile_sales.company_id', $companyId)
            ->where('ms_mobile_sales.status', '!=', 'voided');

        $queryAccSales = DB::table('ms_accessory_sales')
            ->leftJoin('ms_customers', 'ms_accessory_sales.customer_id', '=', 'ms_customers.id')
            ->select('ms_accessory_sales.*', 'ms_customers.name as customer_name', 'ms_customers.phone as customer_phone')
            ->where('ms_accessory_sales.company_id', $companyId)
            ->where('ms_accessory_sales.status', '!=', 'voided');

        // Niche isolation for sales reports
        if ($niche === 'phones') {
            $querySales->where('ms_mobile_devices.type', 'new');
            $queryAccSales->whereRaw('1 = 0'); // No accessories for new phones niche
        } elseif ($niche === 'secondhand') {
            $querySales->where('ms_mobile_devices.type', 'second_hand');
            $queryAccSales->whereRaw('1 = 0');
        } elseif ($niche === 'accessories') {
            $querySales->whereRaw('1 = 0');
        } elseif ($niche === 'covers') {
            $querySales->whereRaw('1 = 0');
            $coverCats = $this->coverCategories;
            $queryAccSales->whereExists(function($q) use ($coverCats) {
                $q->from('ms_accessory_sale_items')
                  ->whereColumn('ms_accessory_sale_items.accessory_sale_id', 'ms_accessory_sales.id')
                  ->join('ms_parts_inventory', 'ms_accessory_sale_items.part_id', '=', 'ms_parts_inventory.id')
                  ->whereIn('ms_parts_inventory.category', $coverCats);
            });
        }

        $fromDate = $request->query('from_date');
        $toDate   = $request->query('to_date');

        if ($fromDate && $toDate) {
            $querySales->whereBetween('ms_mobile_sales.created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59']);
            $queryAccSales->whereBetween('ms_accessory_sales.created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59']);
            $filter = 'custom';
        } elseif ($filter === 'today') {
            $querySales->whereDate('ms_mobile_sales.created_at', today());
            $queryAccSales->whereDate('ms_accessory_sales.created_at', today());
        } elseif ($filter === 'yesterday') {
            $querySales->whereDate('ms_mobile_sales.created_at', today()->subDay());
            $queryAccSales->whereDate('ms_accessory_sales.created_at', today()->subDay());
        } elseif ($filter === 'week') {
            $querySales->whereBetween('ms_mobile_sales.created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()]);
            $queryAccSales->whereBetween('ms_accessory_sales.created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()]);
        } elseif ($filter === 'month') {
            $querySales->whereMonth('ms_mobile_sales.created_at', now()->month)->whereYear('ms_mobile_sales.created_at', now()->year);
            $queryAccSales->whereMonth('ms_accessory_sales.created_at', now()->month)->whereYear('ms_accessory_sales.created_at', now()->year);
        }

        $mobileSales = $querySales->orderBy('ms_mobile_sales.id', 'desc')->get();
        $accSales    = $queryAccSales->orderBy('ms_accessory_sales.id', 'desc')->get();

        $totalSalesVal = $mobileSales->sum('total_amount') + $accSales->sum('total_amount');
        $totalCogsVal  = $mobileSales->sum('purchase_cost');
        $netProfitVal  = max(0, $totalSalesVal - $totalCogsVal);
        $marginPercent = $totalSalesVal > 0 ? round(($netProfitVal / $totalSalesVal) * 100, 1) : 0;
        $totalOrdersCount = $mobileSales->count() + $accSales->count();
        $avgOrderValue = $totalOrdersCount > 0 ? round($totalSalesVal / $totalOrdersCount, 2) : 0;

        $gstTotal = $mobileSales->sum('cgst_amount') + $mobileSales->sum('sgst_amount') + $mobileSales->sum('igst_amount')
                  + $accSales->sum('cgst_amount') + $accSales->sum('sgst_amount') + $accSales->sum('igst_amount');

        $prefix = DB::getTablePrefix();

        // Top Selling Mobile Brands & Models
        $topSellingMobiles = DB::table('ms_mobile_sales')
            ->join('ms_mobile_devices', 'ms_mobile_sales.device_id', '=', 'ms_mobile_devices.id')
            ->where('ms_mobile_sales.company_id', $companyId)
            ->where('ms_mobile_sales.status', '!=', 'voided')
            ->when($fromDate && $toDate, function($q) use ($fromDate, $toDate) {
                $q->whereBetween('ms_mobile_sales.created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59']);
            })
            ->when($filter === 'today', fn($q) => $q->whereDate('ms_mobile_sales.created_at', today()))
            ->when($filter === 'yesterday', fn($q) => $q->whereDate('ms_mobile_sales.created_at', today()->subDay()))
            ->when($filter === 'week', fn($q) => $q->whereBetween('ms_mobile_sales.created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()]))
            ->when($filter === 'month', fn($q) => $q->whereMonth('ms_mobile_sales.created_at', now()->month)->whereYear('ms_mobile_sales.created_at', now()->year))
            ->select(
                'ms_mobile_devices.brand',
                'ms_mobile_devices.model',
                DB::raw('COUNT(*) as units_sold'),
                DB::raw("SUM({$prefix}ms_mobile_sales.total_amount) as total_revenue"),
                DB::raw("SUM({$prefix}ms_mobile_sales.total_amount - {$prefix}ms_mobile_devices.purchase_cost) as total_profit")
            )
            ->groupBy('ms_mobile_devices.brand', 'ms_mobile_devices.model')
            ->orderByDesc('units_sold')
            ->limit(8)
            ->get();

        // Top Selling Accessories & Parts
        $topSellingParts = DB::table('ms_accessory_sale_items')
            ->join('ms_accessory_sales', 'ms_accessory_sale_items.accessory_sale_id', '=', 'ms_accessory_sales.id')
            ->join('ms_parts_inventory', 'ms_accessory_sale_items.part_id', '=', 'ms_parts_inventory.id')
            ->where('ms_accessory_sales.company_id', $companyId)
            ->where('ms_accessory_sales.status', '!=', 'voided')
            ->when($fromDate && $toDate, function($q) use ($fromDate, $toDate) {
                $q->whereBetween('ms_accessory_sales.created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59']);
            })
            ->when($filter === 'today', fn($q) => $q->whereDate('ms_accessory_sales.created_at', today()))
            ->when($filter === 'yesterday', fn($q) => $q->whereDate('ms_accessory_sales.created_at', today()->subDay()))
            ->when($filter === 'week', fn($q) => $q->whereBetween('ms_accessory_sales.created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()]))
            ->when($filter === 'month', fn($q) => $q->whereMonth('ms_accessory_sales.created_at', now()->month)->whereYear('ms_accessory_sales.created_at', now()->year))
            ->select(
                'ms_parts_inventory.name',
                'ms_parts_inventory.category',
                'ms_parts_inventory.stock_qty as current_stock',
                DB::raw("SUM({$prefix}ms_accessory_sale_items.quantity) as total_qty_sold"),
                DB::raw("SUM({$prefix}ms_accessory_sale_items.line_total) as total_revenue")
            )
            ->groupBy('ms_parts_inventory.id', 'ms_parts_inventory.name', 'ms_parts_inventory.category', 'ms_parts_inventory.stock_qty')
            ->orderByDesc('total_qty_sold')
            ->limit(8)
            ->get();

        // Payment Mode Distribution
        $paymentModes = [
            'cash'   => (float) ($mobileSales->where('payment_mode', 'cash')->sum('total_amount') + $accSales->where('payment_mode', 'cash')->sum('total_amount')),
            'upi'    => (float) ($mobileSales->where('payment_mode', 'upi')->sum('total_amount') + $accSales->where('payment_mode', 'upi')->sum('total_amount')),
            'card'   => (float) ($mobileSales->where('payment_mode', 'card')->sum('total_amount') + $accSales->where('payment_mode', 'card')->sum('total_amount')),
            'emi'    => (float) $mobileSales->where('payment_mode', 'emi')->sum('total_amount'),
            'udhari' => (float) ($mobileSales->sum('udhari_amount') + $accSales->sum('udhari_amount')),
        ];

        // Khata & Debtors Analytics
        $totalUdhariReceivables = (float) DB::table('ms_customers')->where('company_id', $companyId)->sum('udhari_balance');
        $totalDebtorsCount = DB::table('ms_customers')->where('company_id', $companyId)->where('udhari_balance', '>', 0)->count();
        $totalSettledCount = DB::table('ms_customers')->where('company_id', $companyId)->where('udhari_balance', '<=', 0)->count();

        // Low Stock Inventory Alerts
        $lowStockAlerts = DB::table('ms_parts_inventory')
            ->where('company_id', $companyId)
            ->where('stock_qty', '<=', 5)
            ->orderBy('stock_qty', 'asc')
            ->limit(6)
            ->get();

        // Timeline Trend Data (Past 7 Days)
        $chartLabels = [];
        $chartRevenue = [];
        $chartProfit = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = now()->subDays($i)->format('Y-m-d');
            $chartLabels[] = now()->subDays($i)->format('d M');
            
            $dayMobSales = DB::table('ms_mobile_sales')
                ->join('ms_mobile_devices', 'ms_mobile_sales.device_id', '=', 'ms_mobile_devices.id')
                ->where('ms_mobile_sales.company_id', $companyId)
                ->where('ms_mobile_sales.status', '!=', 'voided')
                ->whereDate('ms_mobile_sales.created_at', $d);
            
            $dayMobRev = (float) $dayMobSales->sum('ms_mobile_sales.total_amount');
            $dayMobCost = (float) $dayMobSales->sum('ms_mobile_devices.purchase_cost');
            
            $dayAccRev = (float) DB::table('ms_accessory_sales')
                ->where('company_id', $companyId)
                ->where('status', '!=', 'voided')
                ->whereDate('created_at', $d)
                ->sum('total_amount');

            $chartRevenue[] = round($dayMobRev + $dayAccRev, 2);
            $chartProfit[] = round(max(0, ($dayMobRev - $dayMobCost) + ($dayAccRev * 0.35)), 2);
        }

        $stockPhones = DB::table('ms_mobile_devices')->where('company_id', $companyId)->where('status', 'in_stock')->get();
        if ($niche === 'phones') {
            $stockPhones = $stockPhones->where('type', 'new');
        } elseif ($niche === 'secondhand') {
            $stockPhones = $stockPhones->where('type', 'second_hand');
        }

        $partsQuery = DB::table('ms_parts_inventory')->where('company_id', $companyId);
        if ($niche === 'covers') {
            $partsQuery->whereIn('category', $this->coverCategories);
        } elseif ($niche === 'accessories') {
            $partsQuery->whereNotIn('category', $this->coverCategories);
        } elseif (in_array($niche, ['phones', 'secondhand', 'repairs'])) {
            $partsQuery->whereRaw('1 = 0');
        }

        $stockValuation = [
            'phones_cost'   => (float) $stockPhones->sum('purchase_cost'),
            'phones_retail' => (float) $stockPhones->sum('selling_price'),
            'phones_count'  => $stockPhones->count(),
            'parts_cost'    => (float) $partsQuery->sum(DB::raw('unit_cost * stock_qty')),
            'parts_retail'  => (float) $partsQuery->sum(DB::raw('selling_price * stock_qty')),
            'parts_count'   => (int) $partsQuery->sum('stock_qty'),
        ];

        $debtors = (in_array($niche, ['admin', 'phones']))
            ? DB::table('ms_customers')->where('company_id', $companyId)->where('udhari_balance', '>', 0)->orderBy('udhari_balance', 'desc')->get()
            : collect();

        return view('mobileshop.reports', compact(
            'niche', 'mobileSales', 'accSales', 'totalSalesVal', 'netProfitVal', 'marginPercent',
            'totalOrdersCount', 'avgOrderValue', 'gstTotal', 'stockValuation', 'debtors', 'filter',
            'fromDate', 'toDate', 'topSellingMobiles', 'topSellingParts', 'paymentModes',
            'totalUdhariReceivables', 'totalDebtorsCount', 'totalSettledCount', 'lowStockAlerts',
            'chartLabels', 'chartRevenue', 'chartProfit'
        ));
    }

    /**
     * Masters Hub (Categories, Financiers, Suppliers & Role Settings)
     */
    public function masters()
    {
        // Masters is Admin-only
        abort_unless(auth()->check() && (auth()->user()->hasRole('store-admin') || auth()->user()->hasRole('admin')), 403, 'Admin access required for Masters.');

        $companyId = $this->getCompanyId();

        $categories = DB::table('ms_part_categories')->where('company_id', $companyId)->orderBy('name', 'asc')->get();
        $financiers = DB::table('ms_emi_providers')->where('company_id', $companyId)->get();
        $suppliers  = DB::table('ms_supplier_credit_wallets')
            ->leftJoin('ms_suppliers', 'ms_supplier_credit_wallets.supplier_id', '=', 'ms_suppliers.id')
            ->select('ms_supplier_credit_wallets.*', 'ms_suppliers.name as supplier_name', 'ms_suppliers.phone as supplier_phone', 'ms_suppliers.gstin as supplier_gstin')
            ->where('ms_supplier_credit_wallets.company_id', $companyId)
            ->get();
        $staffUsers = \App\Models\Auth\User::whereHas('companies', function($q) use ($companyId) {
            $q->where('companies.id', $companyId);
        })->get();

        return view('mobileshop.masters', compact('categories', 'financiers', 'suppliers', 'staffUsers'));
    }

    /**
     * Sales Hub — Niche-scoped invoice list.
     * Each role sees ONLY their own niche's sales and gets only their Add button.
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
        $repairSales = collect();

        switch ($niche) {
            case 'phones':
                $mobileSales = DB::table('ms_mobile_sales')
                    ->join('ms_customers', 'ms_mobile_sales.customer_id', '=', 'ms_customers.id')
                    ->join('ms_mobile_devices', 'ms_mobile_sales.device_id', '=', 'ms_mobile_devices.id')
                    ->select('ms_mobile_sales.*', 'ms_customers.name as customer_name', 'ms_customers.phone as customer_phone',
                             'ms_mobile_devices.brand', 'ms_mobile_devices.model', 'ms_mobile_devices.imei_1',
                             DB::raw("'new' as device_type"), DB::raw("'phone' as sale_niche"))
                    ->where('ms_mobile_sales.company_id', $companyId)
                    ->where('ms_mobile_devices.type', 'new')
                    ->where('ms_mobile_sales.status', '!=', 'voided')
                    ->orderBy('ms_mobile_sales.id', 'desc')->get();
                break;

            case 'secondhand':
                $mobileSales = DB::table('ms_mobile_sales')
                    ->join('ms_customers', 'ms_mobile_sales.customer_id', '=', 'ms_customers.id')
                    ->join('ms_mobile_devices', 'ms_mobile_sales.device_id', '=', 'ms_mobile_devices.id')
                    ->select('ms_mobile_sales.*', 'ms_customers.name as customer_name', 'ms_customers.phone as customer_phone',
                             'ms_mobile_devices.brand', 'ms_mobile_devices.model', 'ms_mobile_devices.imei_1',
                             DB::raw("'second_hand' as device_type"), DB::raw("'secondhand' as sale_niche"))
                    ->where('ms_mobile_sales.company_id', $companyId)
                    ->where('ms_mobile_devices.type', 'second_hand')
                    ->where('ms_mobile_sales.status', '!=', 'voided')
                    ->orderBy('ms_mobile_sales.id', 'desc')->get();
                break;

            case 'accessories':
                $accSales = DB::table('ms_accessory_sales')
                    ->leftJoin('ms_customers', 'ms_accessory_sales.customer_id', '=', 'ms_customers.id')
                    ->select('ms_accessory_sales.*', 'ms_customers.name as customer_name', 'ms_customers.phone as customer_phone',
                             DB::raw("'accessory' as sale_niche"))
                    ->where('ms_accessory_sales.company_id', $companyId)
                    ->where('ms_accessory_sales.status', '!=', 'voided')
                    ->orderBy('ms_accessory_sales.id', 'desc')->get();
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
                    ->orderBy('ms_accessory_sales.id', 'desc')->get();
                break;

            default: // admin — all sales
                $mobileSales = DB::table('ms_mobile_sales')
                    ->join('ms_customers', 'ms_mobile_sales.customer_id', '=', 'ms_customers.id')
                    ->join('ms_mobile_devices', 'ms_mobile_sales.device_id', '=', 'ms_mobile_devices.id')
                    ->select('ms_mobile_sales.*', 'ms_customers.name as customer_name', 'ms_customers.phone as customer_phone',
                             'ms_mobile_devices.brand', 'ms_mobile_devices.model', 'ms_mobile_devices.imei_1', 'ms_mobile_devices.type as device_type')
                    ->where('ms_mobile_sales.company_id', $companyId)
                    ->where('ms_mobile_sales.status', '!=', 'voided')
                    ->orderBy('ms_mobile_sales.id', 'desc')->get();
                $accSales = DB::table('ms_accessory_sales')
                    ->leftJoin('ms_customers', 'ms_accessory_sales.customer_id', '=', 'ms_customers.id')
                    ->select('ms_accessory_sales.*', 'ms_customers.name as customer_name', 'ms_customers.phone as customer_phone')
                    ->where('ms_accessory_sales.company_id', $companyId)
                    ->where('ms_accessory_sales.status', '!=', 'voided')
                    ->orderBy('ms_accessory_sales.id', 'desc')->get();
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

        // Aggregated KPIs for page header
        $todaySalesTotal = (float) ($niche === 'admin'
            ? DB::table('ms_mobile_sales')->where('company_id',$companyId)->whereDate('created_at',today())->where('status','!=','voided')->sum('total_amount')
              + DB::table('ms_accessory_sales')->where('company_id',$companyId)->whereDate('created_at',today())->where('status','!=','voided')->sum('total_amount')
            : ($mobileSales->sum('total_amount') + $accSales->sum('total_amount')));

        $monthSalesTotal = (float) ($niche === 'admin'
            ? DB::table('ms_mobile_sales')->where('company_id',$companyId)->whereMonth('created_at',now()->month)->whereYear('created_at',now()->year)->where('status','!=','voided')->sum('total_amount')
              + DB::table('ms_accessory_sales')->where('company_id',$companyId)->whereMonth('created_at',now()->month)->whereYear('created_at',now()->year)->where('status','!=','voided')->sum('total_amount')
            : ($mobileSales->sum('total_amount') + $accSales->sum('total_amount')));

        $salesCount = $mobileSales->count() + $accSales->count();

        // Stock availability for add-sale forms
        $availableNewPhones   = in_array($niche, ['admin','phones'])    ? DB::table('ms_mobile_devices')->where('company_id',$companyId)->where('type','new')->where('status','in_stock')->count() : 0;
        $availableSecondHand  = in_array($niche, ['admin','secondhand']) ? DB::table('ms_mobile_devices')->where('company_id',$companyId)->where('type','second_hand')->where('status','in_stock')->count() : 0;
        $availableParts       = in_array($niche, ['admin','accessories','covers']) ? DB::table('ms_parts_inventory')->where('company_id',$companyId)->where('stock_qty','>',0)->count() : 0;
        $customers            = DB::table('ms_customers')->where('company_id', $companyId)->get();
        $partsList            = DB::table('ms_parts_inventory')->where('company_id', $companyId)->where('stock_qty', '>', 0)->get();
        $categories           = DB::table('ms_part_categories')->where('company_id', $companyId)->orderBy('name', 'asc')->get();

        return view('mobileshop.sales', compact(
            'niche', 'mobileSales', 'accSales',
            'canCreatePhones', 'canCreateSecondhand', 'canCreateAccessories', 'canCreateCovers',
            'todaySalesTotal', 'monthSalesTotal', 'salesCount',
            'availableNewPhones', 'availableSecondHand', 'availableParts', 'customers', 'partsList', 'categories'
        ));
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
                    ->orderBy('id', 'desc')->get();
                break;

            case 'secondhand':
                $buybacks = DB::table('ms_mobile_devices')
                    ->where('company_id', $companyId)->where('type', 'second_hand')
                    ->orderBy('id', 'desc')->get();
                break;

            case 'accessories':
                $batchRestocks = DB::table('ms_parts_inventory_history')
                    ->join('ms_parts_inventory', 'ms_parts_inventory_history.part_id', '=', 'ms_parts_inventory.id')
                    ->select('ms_parts_inventory_history.*', 'ms_parts_inventory.name as part_name', 'ms_parts_inventory.category')
                    ->where('ms_parts_inventory.company_id', $companyId)
                    ->where('ms_parts_inventory_history.type', 'addition')
                    ->orderBy('ms_parts_inventory_history.id', 'desc')->get();
                break;

            case 'covers':
                $coverCats = $this->coverCategories;
                $batchRestocks = DB::table('ms_parts_inventory_history')
                    ->join('ms_parts_inventory', 'ms_parts_inventory_history.part_id', '=', 'ms_parts_inventory.id')
                    ->select('ms_parts_inventory_history.*', 'ms_parts_inventory.name as part_name', 'ms_parts_inventory.category')
                    ->where('ms_parts_inventory.company_id', $companyId)
                    ->whereIn('ms_parts_inventory.category', $coverCats)
                    ->where('ms_parts_inventory_history.type', 'addition')
                    ->orderBy('ms_parts_inventory_history.id', 'desc')->get();
                break;

            default: // admin — all purchases
                $purchaseOrders = DB::table('ms_purchase_orders')
                    ->where('company_id', $companyId)
                    ->orderBy('id', 'desc')
                    ->get();
                $newPhonePurchases = DB::table('ms_mobile_devices')
                    ->where('company_id', $companyId)->where('type', 'new')
                    ->orderBy('id', 'desc')->get();
                $buybacks = DB::table('ms_mobile_devices')
                    ->where('company_id', $companyId)->where('type', 'second_hand')
                    ->orderBy('id', 'desc')->get();
                $batchRestocks = DB::table('ms_parts_inventory_history')
                    ->join('ms_parts_inventory', 'ms_parts_inventory_history.part_id', '=', 'ms_parts_inventory.id')
                    ->select('ms_parts_inventory_history.*', 'ms_parts_inventory.name as part_name', 'ms_parts_inventory.category')
                    ->where('ms_parts_inventory.company_id', $companyId)
                    ->where('ms_parts_inventory_history.type', 'addition')
                    ->orderBy('ms_parts_inventory_history.id', 'desc')->get();
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

        // Drop-downs for add forms
        $suppliers  = DB::table('ms_supplier_credit_wallets')
            ->leftJoin('ms_suppliers', 'ms_supplier_credit_wallets.supplier_id', '=', 'ms_suppliers.id')
            ->select('ms_supplier_credit_wallets.*', 'ms_suppliers.name as supplier_name')
            ->where('ms_supplier_credit_wallets.company_id', $companyId)
            ->get();
        $categories = DB::table('ms_part_categories')->where('company_id', $companyId)->orderBy('name', 'asc')->get();
        $parts      = DB::table('ms_parts_inventory')->where('company_id', $companyId)->orderBy('name', 'asc')->get();

        return view('mobileshop.purchase', compact(
            'niche', 'isAdmin', 'purchaseInvoices', 'purchaseOrders', 'newPhonePurchases', 'buybacks', 'batchRestocks',
            'canAddPhones', 'canAddSecondhand', 'canAddAccessories', 'canAddCovers',
            'totalPOValue', 'totalPODue', 'totalInvoicesCount', 'totalUnitsPurchased',
            'suppliers', 'categories', 'parts'
        ));
    }

    /**
     * Stock Hub — Niche-scoped inventory view.
     * Each role sees ONLY their own niche's stock.
     */
    public function stockHub(Request $request)
    {
        $companyId = $this->getCompanyId();
        $niche     = $this->getUserNiche();
        $user      = auth()->user();

        $canManagePhones      = $user->can('manage-stock-phones');
        $canManageSecondhand  = $user->can('manage-stock-secondhand');
        $canManageAccessories = $user->can('manage-stock-accessories');
        $canManageCovers      = $user->can('manage-stock-covers');
        $canManageRepairs     = $user->can('manage-stock-repairs');

        $newPhones       = collect();
        $secondHandPhones = collect();
        $parts           = collect();
        $repairTickets   = collect();
        $categories      = collect();

        switch ($niche) {
            case 'phones':
                $newPhones  = DB::table('ms_mobile_devices')->where('company_id', $companyId)->where('type', 'new')->orderBy('id', 'desc')->get();
                break;

            case 'secondhand':
                $secondHandPhones = DB::table('ms_mobile_devices')->where('company_id', $companyId)->where('type', 'second_hand')->orderBy('id', 'desc')->get();
                break;

            case 'accessories':
                $parts      = DB::table('ms_parts_inventory')->where('company_id', $companyId)->orderBy('name', 'asc')->get();
                $categories = DB::table('ms_part_categories')->where('company_id', $companyId)->orderBy('name', 'asc')->get();
                break;

            case 'covers':
                $coverCats  = $this->coverCategories;
                $parts      = DB::table('ms_parts_inventory')->where('company_id', $companyId)->whereIn('category', $coverCats)->orderBy('name', 'asc')->get();
                $categories = DB::table('ms_part_categories')->where('company_id', $companyId)->whereIn('slug', $coverCats)->orderBy('name', 'asc')->get();
                break;

            case 'repairs':
                $repairTickets = DB::table('ms_repair_tickets')
                    ->join('ms_customers', 'ms_repair_tickets.customer_id', '=', 'ms_customers.id')
                    ->select('ms_repair_tickets.*', 'ms_customers.name as customer_name', 'ms_customers.phone as customer_phone')
                    ->where('ms_repair_tickets.company_id', $companyId)
                    ->orderBy('ms_repair_tickets.id', 'desc')->get();
                break;

            default: // admin — all stock
                $newPhones        = DB::table('ms_mobile_devices')->where('company_id', $companyId)->where('type', 'new')->orderBy('id', 'desc')->get();
                $secondHandPhones = DB::table('ms_mobile_devices')->where('company_id', $companyId)->where('type', 'second_hand')->orderBy('id', 'desc')->get();
                $parts            = DB::table('ms_parts_inventory')->where('company_id', $companyId)->orderBy('name', 'asc')->get();
                $categories       = DB::table('ms_part_categories')->where('company_id', $companyId)->orderBy('name', 'asc')->get();
                $repairTickets    = DB::table('ms_repair_tickets')
                    ->join('ms_customers', 'ms_repair_tickets.customer_id', '=', 'ms_customers.id')
                    ->select('ms_repair_tickets.*', 'ms_customers.name as customer_name', 'ms_customers.phone as customer_phone')
                    ->where('ms_repair_tickets.company_id', $companyId)
                    ->orderBy('ms_repair_tickets.id', 'desc')->get();
        }

        // Summary stats for this niche
        $totalNewPhonesInStock    = $newPhones->where('status', 'in_stock')->count();
        $totalSecondHandInStock   = $secondHandPhones->where('status', 'in_stock')->count();
        $totalPartsInStock        = (int) $parts->sum('stock_qty');
        $lowStockCount            = $parts->where('stock_qty', '<=', 3)->count();
        $valuationCost = (float) $newPhones->where('status','in_stock')->sum('purchase_cost')
                       + (float) $secondHandPhones->where('status','in_stock')->sum('purchase_cost')
                       + (float) $parts->sum(fn($p) => ($p->unit_cost ?? 0) * ($p->stock_qty ?? 0));
        $valuationRetail = (float) $newPhones->where('status','in_stock')->sum('selling_price')
                         + (float) $secondHandPhones->where('status','in_stock')->sum('selling_price')
                         + (float) $parts->sum(fn($p) => ($p->selling_price ?? 0) * ($p->stock_qty ?? 0));

        return view('mobileshop.stock', compact(
            'niche',
            'newPhones', 'secondHandPhones', 'parts', 'repairTickets', 'categories',
            'canManagePhones', 'canManageSecondhand', 'canManageAccessories', 'canManageCovers', 'canManageRepairs',
            'totalNewPhonesInStock', 'totalSecondHandInStock', 'totalPartsInStock',
            'lowStockCount', 'valuationCost', 'valuationRetail'
        ));
    }

    /**
     * Dispatcher: Unified Purchase Store
     */
    public function storePurchase(Request $request)
    {
        if ($request->input('type') === 'second_hand' || $request->has('customer_buyback_name')) {
            return $this->storeSecondHand($request);
        }
        if ($request->input('type') === 'bulk_restock' || $request->has('restock_qty')) {
            return $this->bulkRestock($request);
        }
        if ($request->input('type') === 'accessory' || $request->has('compatible_model')) {
            return $this->storePart($request);
        }
        return $this->storeNewMobile($request);
    }

    /**
     * Dispatcher: Unified Sale Store
     */
    public function storeSale(Request $request)
    {
        if ($request->has('items') || $request->input('sale_type') === 'accessory') {
            return $this->sellAccessory($request);
        }
        if ($request->input('device_type') === 'second_hand' || $request->input('type') === 'second_hand') {
            return $this->sellSecondHand($request);
        }
        return $this->processSale($request);
    }

    /**
     * Dispatcher: Unified Stock Store
     */
    public function storeStock(Request $request)
    {
        if ($request->input('stock_type') === 'part' || $request->has('compatible_model') || $request->input('category')) {
            return $this->storePart($request);
        }
        if ($request->input('stock_type') === 'second_hand' || $request->input('type') === 'second_hand') {
            return $this->storeSecondHand($request);
        }
        return $this->storeNewMobile($request);
    }

    /**
     * Unified Stock Update
     */
    public function updateStock(Request $request, $id)
    {
        $companyId = $this->getCompanyId();
        if ($request->input('item_type') === 'part') {
            $part = DB::table('ms_parts_inventory')->where('company_id', $companyId)->where('id', $id)->first();
            if (!$part) {
                return redirect()->back()->with('error', 'Part not found.');
            }
            DB::table('ms_parts_inventory')->where('id', $id)->update([
                'selling_price'   => $request->filled('selling_price') ? (float) $request->selling_price : $part->selling_price,
                'min_stock_alert' => $request->filled('min_stock_alert') ? (int) $request->min_stock_alert : $part->min_stock_alert,
                'updated_at'      => now(),
            ]);
            return redirect()->back()->with('success', "Stock item '{$part->name}' updated.");
        }

        $device = DB::table('ms_mobile_devices')->where('company_id', $companyId)->where('id', $id)->first();
        if (!$device) {
            return redirect()->back()->with('error', 'Mobile device not found.');
        }
        DB::table('ms_mobile_devices')->where('id', $id)->update([
            'selling_price' => $request->filled('selling_price') ? (float) $request->selling_price : $device->selling_price,
            'status'        => $request->filled('status') ? $request->status : $device->status,
            'updated_at'    => now(),
        ]);
        return redirect()->back()->with('success', "Device {$device->brand} {$device->model} updated.");
    }

    /**
     * Resolve Purchase Order details with supplier and line items
     */
    private function resolvePurchaseOrderDetails(int $companyId, int $id): ?array
    {
        $po = DB::table('ms_purchase_orders')
            ->leftJoin('ms_suppliers', 'ms_purchase_orders.supplier_id', '=', 'ms_suppliers.id')
            ->select('ms_purchase_orders.*', 'ms_suppliers.name as supplier_name', 'ms_suppliers.phone as supplier_phone', 'ms_suppliers.gstin as supplier_gstin', 'ms_suppliers.address as supplier_address')
            ->where('ms_purchase_orders.company_id', $companyId)
            ->where('ms_purchase_orders.id', $id)
            ->first();

        if (!$po) {
            return null;
        }

        $items = DB::table('ms_purchase_order_items')->where('purchase_order_id', $po->id)->get();
        $amountInWords = self::amountToWords($po->total_amount);

        return compact('po', 'items', 'amountInWords');
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
     * Alias for purchaseInvoice
     */
    public function purchaseInvoiceView(Request $request, $id)
    {
        return $this->purchaseInvoice($request, $id);
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

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('mobileshop.pdf.purchase_invoice', $data);
        $pdf->setPaper('a4', 'portrait');
        return $pdf->download("PurchaseOrder-{$data['po']->po_number}.pdf");
    }

    /**
     * Helper to Convert Indian Rupees Amount to Words
     */
    public static function amountToWords($number)
    {
        $decimal = round($number - ($no = floor($number)), 2) * 100;
        $words = [
            0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
            6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
            11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
            16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen', 20 => 'Twenty',
            30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty', 70 => 'Seventy',
            80 => 'Eighty', 90 => 'Ninety'
        ];
        $digits = ['', 'Hundred', 'Thousand', 'Lakh', 'Crore'];
        $no = (int)$no;
        $str = [];
        $n_length = strlen((string)$no);
        $i = 0;
        while ($i < $n_length) {
            $divider = ($i == 2) ? 10 : 100;
            $number = floor($no % $divider);
            $no = floor($no / $divider);
            $i += ($divider == 10) ? 1 : 2;
            if ($number) {
                $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
                $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                $str[] = ($number < 21) ? $words[$number] . ' ' . $digits[$counter] . $plural . ' ' . $hundred
                    : $words[floor($number / 10) * 10] . ' ' . $words[$number % 10] . ' ' . $digits[$counter] . $plural . ' ' . $hundred;
            } else {
                $str[] = null;
            }
        }
        $rupees = implode('', array_reverse($str));
        $paise = ($decimal > 0) ? " and " . ($words[$decimal / 10 * 10] . " " . $words[$decimal % 10]) . ' Paise' : '';
        return 'Rupees ' . ($rupees ? trim($rupees) : 'Zero') . $paise . ' Only';
    }
}
