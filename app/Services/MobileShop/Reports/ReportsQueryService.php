<?php

namespace App\Services\MobileShop\Reports;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsQueryService
{
    /**
     * Retrieve filtered sales and purchases for reporting and CSV exports
     */
    public function getSalesAndPurchasesForReports(int $companyId, string $niche, array $coverCategories, Request $request): array
    {
        $filter    = $request->query('period', 'all');
        $fromDate  = $request->query('from_date');
        $toDate    = $request->query('to_date');

        $querySales = DB::table('ms_mobile_sales')
            ->join('ms_customers', 'ms_mobile_sales.customer_id', '=', 'ms_customers.id')
            ->join('ms_mobile_devices', 'ms_mobile_sales.device_id', '=', 'ms_mobile_devices.id')
            ->select(
                'ms_mobile_sales.*',
                'ms_customers.name as customer_name',
                'ms_customers.phone as customer_phone',
                'ms_customers.gstin as customer_gstin',
                'ms_customers.state_code as customer_state_code',
                'ms_customers.address as customer_address',
                'ms_mobile_devices.brand',
                'ms_mobile_devices.model',
                'ms_mobile_devices.imei_1',
                'ms_mobile_devices.imei_2',
                'ms_mobile_devices.hsn_code',
                'ms_mobile_devices.purchase_cost'
            )
            ->where('ms_mobile_sales.company_id', $companyId)
            ->where('ms_mobile_sales.status', '!=', 'voided');

        $queryAccSales = DB::table('ms_accessory_sales')
            ->leftJoin('ms_customers', 'ms_accessory_sales.customer_id', '=', 'ms_customers.id')
            ->select(
                'ms_accessory_sales.*',
                'ms_customers.name as customer_name',
                'ms_customers.phone as customer_phone',
                'ms_customers.gstin as customer_gstin',
                'ms_customers.state_code as customer_state_code',
                'ms_customers.address as customer_address'
            )
            ->where('ms_accessory_sales.company_id', $companyId)
            ->where('ms_accessory_sales.status', '!=', 'voided');

        // Niche isolation for sales reports
        if ($niche === 'phones') {
            $querySales->where('ms_mobile_devices.type', 'new');
            $queryAccSales->whereRaw('1 = 0');
        } elseif ($niche === 'secondhand') {
            $querySales->where('ms_mobile_devices.type', 'second_hand');
            $queryAccSales->whereRaw('1 = 0');
        } elseif ($niche === 'accessories') {
            $querySales->whereRaw('1 = 0');
        } elseif ($niche === 'covers') {
            $querySales->whereRaw('1 = 0');
            $queryAccSales->whereExists(function($q) use ($coverCategories) {
                $q->from('ms_accessory_sale_items')
                  ->whereColumn('ms_accessory_sale_items.accessory_sale_id', 'ms_accessory_sales.id')
                  ->join('ms_parts_inventory', 'ms_accessory_sale_items.part_id', '=', 'ms_parts_inventory.id')
                  ->whereIn('ms_parts_inventory.category', $coverCategories);
            });
        }

        $queryPurchases = DB::table('ms_purchase_orders')
            ->leftJoin('ms_suppliers', 'ms_purchase_orders.supplier_id', '=', 'ms_suppliers.id')
            ->select(
                'ms_purchase_orders.*',
                'ms_suppliers.name as supplier_name',
                'ms_suppliers.phone as supplier_phone',
                'ms_suppliers.gstin as supplier_gstin',
                'ms_suppliers.state_code as supplier_state_code'
            )
            ->where('ms_purchase_orders.company_id', $companyId)
            ->whereNotIn('ms_purchase_orders.status', ['cancelled']);

        if ($fromDate || $toDate) {
            $start = $fromDate ? $fromDate . ' 00:00:00' : '1970-01-01 00:00:00';
            $end   = $toDate   ? $toDate   . ' 23:59:59' : '2099-12-31 23:59:59';
            $querySales->whereBetween('ms_mobile_sales.created_at', [$start, $end]);
            $queryAccSales->whereBetween('ms_accessory_sales.created_at', [$start, $end]);
            $queryPurchases->whereBetween('ms_purchase_orders.order_date', [$fromDate ?: '1970-01-01', $toDate ?: '2099-12-31']);
            $filter = 'custom';
        } elseif ($filter === 'today') {
            $querySales->whereDate('ms_mobile_sales.created_at', today());
            $queryAccSales->whereDate('ms_accessory_sales.created_at', today());
            $queryPurchases->whereDate('ms_purchase_orders.order_date', today());
        } elseif ($filter === 'yesterday') {
            $querySales->whereDate('ms_mobile_sales.created_at', today()->subDay());
            $queryAccSales->whereDate('ms_accessory_sales.created_at', today()->subDay());
            $queryPurchases->whereDate('ms_purchase_orders.order_date', today()->subDay());
        } elseif ($filter === 'week') {
            $querySales->whereBetween('ms_mobile_sales.created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()]);
            $queryAccSales->whereBetween('ms_accessory_sales.created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()]);
            $queryPurchases->whereBetween('ms_purchase_orders.order_date', [now()->subDays(6)->toDateString(), now()->toDateString()]);
        } elseif ($filter === 'month') {
            $querySales->whereMonth('ms_mobile_sales.created_at', now()->month)->whereYear('ms_mobile_sales.created_at', now()->year);
            $queryAccSales->whereMonth('ms_accessory_sales.created_at', now()->month)->whereYear('ms_accessory_sales.created_at', now()->year);
            $queryPurchases->whereMonth('ms_purchase_orders.order_date', now()->month)->whereYear('ms_purchase_orders.order_date', now()->year);
        }

        $mobileSales = $querySales->orderBy('ms_mobile_sales.id', 'desc')->get();
        $accSales    = $queryAccSales->orderBy('ms_accessory_sales.id', 'desc')->get();
        $purchases   = $queryPurchases->orderBy('ms_purchase_orders.order_date', 'desc')->get();

        return compact('mobileSales', 'accSales', 'purchases', 'filter', 'fromDate', 'toDate', 'companyId', 'niche');
    }

    /**
     * Compute comprehensive metrics, charts, tax summaries, stock valuation, and debtor analytics
     */
    public function calculateReportMetrics(int $companyId, string $niche, array $coverCategories, array $data): array
    {
        $mobileSales = $data['mobileSales'];
        $accSales    = $data['accSales'];
        $purchases   = $data['purchases'];
        $filter      = $data['filter'];
        $fromDate    = $data['fromDate'];
        $toDate      = $data['toDate'];

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

        // Timeline Trend Data (Past 7 Days - Aggregated in 2 fast queries)
        $sevenDaysAgo = now()->subDays(6)->startOfDay();
        $prefix = DB::getTablePrefix();
        $mobGrouped7 = DB::table('ms_mobile_sales')
            ->join('ms_mobile_devices', 'ms_mobile_sales.device_id', '=', 'ms_mobile_devices.id')
            ->where('ms_mobile_sales.company_id', $companyId)
            ->where('ms_mobile_sales.status', '!=', 'voided')
            ->where('ms_mobile_sales.created_at', '>=', $sevenDaysAgo)
            ->selectRaw("DATE({$prefix}ms_mobile_sales.created_at) as d, SUM({$prefix}ms_mobile_sales.total_amount) as total_rev, SUM({$prefix}ms_mobile_devices.purchase_cost) as total_cost")
            ->groupBy(DB::raw("DATE({$prefix}ms_mobile_sales.created_at)"))
            ->get()
            ->keyBy('d');

        $accGrouped7 = DB::table('ms_accessory_sales')
            ->where('company_id', $companyId)
            ->where('status', '!=', 'voided')
            ->where('created_at', '>=', $sevenDaysAgo)
            ->selectRaw('DATE(created_at) as d, SUM(total_amount) as total_rev')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('total_rev', 'd');

        $chartLabels = [];
        $chartRevenue = [];
        $chartProfit = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = now()->subDays($i)->format('Y-m-d');
            $chartLabels[] = now()->subDays($i)->format('d M');

            $mob = $mobGrouped7->get($d);
            $dayMobRev = (float) ($mob->total_rev ?? 0);
            $dayMobCost = (float) ($mob->total_cost ?? 0);
            $dayAccRev = (float) ($accGrouped7[$d] ?? 0);

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
            $partsQuery->whereIn('category', $coverCategories);
        } elseif ($niche === 'accessories') {
            $partsQuery->whereNotIn('category', $coverCategories);
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

        // GSTR-3B Summary (Output Tax vs Input Tax Credit - ITC)
        $outputCgst = (float) ($mobileSales->sum('cgst_amount') + $accSales->sum('cgst_amount'));
        $outputSgst = (float) ($mobileSales->sum('sgst_amount') + $accSales->sum('sgst_amount'));
        $outputIgst = (float) ($mobileSales->sum('igst_amount') + $accSales->sum('igst_amount'));
        $outputTotalTax = $outputCgst + $outputSgst + $outputIgst;

        $mobTaxable = (float) $mobileSales->sum(fn($m) => max(0, $m->total_amount - ($m->cgst_amount + $m->sgst_amount + $m->igst_amount)));
        $accTaxable = (float) $accSales->sum(fn($a) => (float) ($a->subtotal ?? ($a->total_amount - ($a->cgst_amount + $a->sgst_amount + $a->igst_amount))));
        $outputTaxableVal = $mobTaxable + $accTaxable;

        $itcCgst = (float) $purchases->sum('cgst_amount');
        $itcSgst = (float) $purchases->sum('sgst_amount');
        $itcIgst = (float) $purchases->sum('igst_amount');
        $itcTotalTax = $itcCgst + $itcSgst + $itcIgst;
        $itcTaxableVal = (float) $purchases->sum('subtotal');

        $gstr3b = [
            'output_taxable' => round($outputTaxableVal, 2),
            'output_cgst'    => round($outputCgst, 2),
            'output_sgst'    => round($outputSgst, 2),
            'output_igst'    => round($outputIgst, 2),
            'output_total'   => round($outputTotalTax, 2),
            'itc_taxable'    => round($itcTaxableVal, 2),
            'itc_cgst'       => round($itcCgst, 2),
            'itc_sgst'       => round($itcSgst, 2),
            'itc_igst'       => round($itcIgst, 2),
            'itc_total'      => round($itcTotalTax, 2),
            'net_cgst'       => round(max(0, $outputCgst - $itcCgst), 2),
            'net_sgst'       => round(max(0, $outputSgst - $itcSgst), 2),
            'net_igst'       => round(max(0, $outputIgst - $itcIgst), 2),
            'net_payable'    => round(max(0, $outputTotalTax - $itcTotalTax), 2),
        ];

        // GSTR-1: B2B Invoices vs B2C Invoices
        $b2bInvoices = collect();
        $b2cInvoices = collect();

        foreach ($mobileSales as $ms) {
            $taxable = max(0, (float) $ms->total_amount - ((float) $ms->cgst_amount + (float) $ms->sgst_amount + (float) $ms->igst_amount));
            $item = (object) [
                'type'            => 'Mobile',
                'invoice_number'  => $ms->invoice_number,
                'date'            => $ms->created_at,
                'customer_name'   => $ms->customer_name ?? 'Walk-in Customer',
                'customer_phone'  => $ms->customer_phone ?? '',
                'customer_gstin'  => trim($ms->customer_gstin ?? ''),
                'state_code'      => $ms->customer_state_code ?? '09',
                'place_of_supply' => ($ms->tax_type === 'inter_state') ? 'Other State' : '09-Uttar Pradesh',
                'taxable_value'   => $taxable,
                'tax_rate'        => (float) ($ms->tax_rate ?? 18),
                'cgst'            => (float) $ms->cgst_amount,
                'sgst'            => (float) $ms->sgst_amount,
                'igst'            => (float) $ms->igst_amount,
                'total_amount'    => (float) $ms->total_amount,
                'hsn_code'        => $ms->hsn_code ?? '8517',
                'item_desc'       => trim(($ms->brand ?? '') . ' ' . ($ms->model ?? '') . ' (' . ($ms->imei_1 ?? '') . ')'),
            ];
            if (!empty($item->customer_gstin)) {
                $b2bInvoices->push($item);
            } else {
                $b2cInvoices->push($item);
            }
        }

        foreach ($accSales as $as) {
            $taxable = (float) ($as->subtotal ?? ($as->total_amount - ((float) $as->cgst_amount + (float) $as->sgst_amount + (float) $as->igst_amount)));
            $item = (object) [
                'type'            => 'Accessory',
                'invoice_number'  => $as->invoice_number,
                'date'            => $as->created_at,
                'customer_name'   => $as->customer_name ?? 'Walk-in Customer',
                'customer_phone'  => $as->customer_phone ?? '',
                'customer_gstin'  => trim($as->customer_gstin ?? ''),
                'state_code'      => $as->customer_state_code ?? '09',
                'place_of_supply' => ((float) $as->igst_amount > 0) ? 'Other State' : '09-Uttar Pradesh',
                'taxable_value'   => $taxable,
                'tax_rate'        => (float) ($as->tax_rate ?? 18),
                'cgst'            => (float) $as->cgst_amount,
                'sgst'            => (float) $as->sgst_amount,
                'igst'            => (float) $as->igst_amount,
                'total_amount'    => (float) $as->total_amount,
                'hsn_code'        => '85177090',
                'item_desc'       => 'Mobile Accessories / Parts',
            ];
            if (!empty($item->customer_gstin)) {
                $b2bInvoices->push($item);
            } else {
                $b2cInvoices->push($item);
            }
        }

        // HSN Summary
        $hsnSummary = [
            '8517' => [
                'hsn'           => '8517',
                'desc'          => 'Smartphones & Mobile Handsets',
                'uqc'           => 'NOS',
                'qty'           => $mobileSales->count(),
                'total_value'   => (float) $mobileSales->sum('total_amount'),
                'taxable_value' => $mobTaxable,
                'cgst'          => (float) $mobileSales->sum('cgst_amount'),
                'sgst'          => (float) $mobileSales->sum('sgst_amount'),
                'igst'          => (float) $mobileSales->sum('igst_amount'),
            ],
            '85177090' => [
                'hsn'           => '85177090',
                'desc'          => 'Parts & Accessories for Cellular Phones',
                'uqc'           => 'PCS',
                'qty'           => $accSales->count(),
                'total_value'   => (float) $accSales->sum('total_amount'),
                'taxable_value' => $accTaxable,
                'cgst'          => (float) $accSales->sum('cgst_amount'),
                'sgst'          => (float) $accSales->sum('sgst_amount'),
                'igst'          => (float) $accSales->sum('igst_amount'),
            ],
        ];

        return compact(
            'niche', 'mobileSales', 'accSales', 'totalSalesVal', 'netProfitVal', 'marginPercent',
            'totalOrdersCount', 'avgOrderValue', 'gstTotal', 'stockValuation', 'debtors', 'filter',
            'fromDate', 'toDate', 'topSellingMobiles', 'topSellingParts', 'paymentModes',
            'totalUdhariReceivables', 'totalDebtorsCount', 'totalSettledCount', 'lowStockAlerts',
            'chartLabels', 'chartRevenue', 'chartProfit',
            'gstr3b', 'b2bInvoices', 'b2cInvoices', 'hsnSummary'
        );
    }
}
