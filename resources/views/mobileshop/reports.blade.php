@extends('mobileshop.layout')

@section('title', 'Reports & Business Analytics — MobiTrack ERP')
@section('page-title', 'Financial Analytics & Store Intelligence')

@push('styles')
<style>
/* ─── Dedicated Filter & Period Toolbar Card ─── */
.reports-filter-card {
    background: #FFFFFF;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-card);
    padding: 8px 12px;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}
.reports-filter-form {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
    flex-wrap: wrap;
}
.reports-date-group {
    display: flex;
    align-items: center;
    gap: 6px;
}
.reports-kpi-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 10px;
    margin-bottom: 12px;
}
.reports-grid-charts {
    display: grid;
    grid-template-columns: 1.55fr 1fr;
    gap: 12px;
    margin-bottom: 12px;
}
.reports-grid-2col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 12px;
}
.reports-grid-sub {
    display: grid;
    grid-template-columns: 1.3fr 0.9fr;
    gap: 12px;
    margin-bottom: 12px;
}
.chart-box {
    position: relative;
    height: 200px;
    width: 100%;
}
.reports-table-scroll {
    padding: 0;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}
.reports-table-scroll table {
    min-width: 580px;
    margin: 0;
}

/* ─── Collapsible Sections Styling ─── */
.collapsible-header {
    cursor: pointer;
    user-select: none;
    transition: background 0.15s ease;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.collapsible-header:hover {
    background: #F1F5F9 !important;
}
.collapse-icon {
    width: 18px;
    height: 18px;
    transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    color: #64748B;
}
.is-collapsed .collapse-icon {
    transform: rotate(-90deg);
}
.collapsible-body {
    transition: max-height 0.3s ease, opacity 0.2s ease;
}
.collapsible-body.is-hidden {
    display: none !important;
}

/* ─── Filter Pills & Search Bars ─── */
.sub-filter-pill {
    font-size: 11px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 20px;
    border: 1px solid #CBD5E1;
    background: #FFFFFF;
    color: #475569;
    cursor: pointer;
    transition: all 0.15s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.sub-filter-pill:hover {
    background: #F8FAFC;
    color: #0F172A;
    border-color: #94A3B8;
}
.sub-filter-pill.active {
    background: #0F172A;
    color: #FFFFFF;
    border-color: #0F172A;
}

/* Large Tablets / Laptops */
@media (max-width: 1200px) {
    .reports-kpi-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

/* Standard Tablets & Small Laptops */
@media (max-width: 992px) {
    .reports-kpi-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .reports-grid-charts,
    .reports-grid-2col,
    .reports-grid-sub {
        grid-template-columns: 1fr;
        gap: 16px;
    }
}

@media (max-width: 860px) {
    .reports-filter-card {
        flex-direction: column;
        align-items: stretch;
        gap: 12px;
    }
    .reports-filter-card .filter-bar {
        width: 100%;
        overflow-x: auto;
        white-space: nowrap;
        padding-bottom: 4px;
        -webkit-overflow-scrolling: touch;
    }
}

/* Mobile Screens */
@media (max-width: 640px) {
    .reports-filter-form {
        display: flex;
        flex-wrap: wrap;
        gap: 8px !important;
        width: 100%;
    }
    .reports-date-group {
        display: flex;
        align-items: center;
        flex: 1 1 140px;
        gap: 4px;
    }
    .reports-date-group input {
        width: 100% !important;
    }
    .reports-kpi-grid {
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }
    .reports-kpi-grid > div:last-child {
        grid-column: span 2;
    }
    .chart-box {
        height: 220px;
    }
    .collapsible-header {
        flex-direction: row !important;
    }
}

@media (max-width: 420px) {
    .reports-kpi-grid {
        grid-template-columns: 1fr;
    }
    .reports-kpi-grid > div:last-child {
        grid-column: span 1;
    }
}
</style>
@endpush

@section('page-actions')
    <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
        <button type="button" id="btnToggleAllSections" onclick="toggleAllReportSections()" class="btn btn-outline btn-sm" title="Expand or Collapse All Cards" style="font-size:12px; padding:6px 12px; font-weight:600; display:inline-flex; align-items:center; gap:5px;">
            <i data-lucide="chevrons-up-down" style="width:14px;height:14px;"></i> <span class="desktop-btn-label">Toggle All</span>
        </button>
        <a href="{{ route('mobileshop.reports.export.sales', request()->all()) }}" class="btn btn-outline btn-sm" title="Download Complete Sales Register as CSV / Excel" style="font-size:12px; padding:6px 12px; font-weight:600; color:#2563EB; border-color:#93C5FD; background:#EFF6FF; text-decoration:none; display:inline-flex; align-items:center; gap:5px;">
            <i data-lucide="download" style="width:14px;height:14px;"></i> <span>Sales CSV</span>
        </a>
        <a href="{{ route('mobileshop.reports.export.gst', request()->all()) }}" class="btn btn-outline btn-sm" title="Download CA-Ready GSTR-1 & GSTR-3B Statement as CSV / Excel" style="font-size:12px; padding:6px 12px; font-weight:600; color:#7E22CE; border-color:#D8B4FE; background:#FAF5FF; text-decoration:none; display:inline-flex; align-items:center; gap:5px;">
            <i data-lucide="file-spreadsheet" style="width:14px;height:14px;"></i> <span>GST (GSTR-1) CSV</span>
        </a>
        <button type="button" onclick="window.print()" class="btn btn-outline btn-sm" title="Print this report" style="padding:6px 10px; display:inline-flex; align-items:center; justify-content:center;">
            <i data-lucide="printer" style="width:14px;height:14px;"></i>
        </button>
    </div>
@endsection

@section('content')

    <!-- 0. PERIOD & CUSTOM DATE RANGE FILTER TOOLBAR -->
    <div class="reports-filter-card">
        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
            <span style="font-size:11px; font-weight:800; text-transform:uppercase; color:#64748B; letter-spacing:0.5px; display:flex; align-items:center; gap:5px;">
                <i data-lucide="calendar" style="width:14px;height:14px; color:var(--color-primary);"></i> Filter Period:
            </span>
            <div class="filter-bar" style="margin-bottom:0; display:inline-flex; align-items:center; gap:4px; flex-wrap:wrap;">
                <a href="{{ route('mobileshop.reports', ['period' => 'all']) }}" class="filter-pill {{ $filter === 'all' ? 'active' : '' }}">All Time</a>
                <a href="{{ route('mobileshop.reports', ['period' => 'today']) }}" class="filter-pill {{ $filter === 'today' ? 'active' : '' }}">Today</a>
                <a href="{{ route('mobileshop.reports', ['period' => 'yesterday']) }}" class="filter-pill {{ $filter === 'yesterday' ? 'active' : '' }}">Yesterday</a>
                <a href="{{ route('mobileshop.reports', ['period' => 'week']) }}" class="filter-pill {{ $filter === 'week' ? 'active' : '' }}">Last 7 Days</a>
                <a href="{{ route('mobileshop.reports', ['period' => 'month']) }}" class="filter-pill {{ $filter === 'month' ? 'active' : '' }}">This Month</a>
            </div>
        </div>

        <!-- Custom Date Range Form -->
        <form method="GET" action="{{ route('mobileshop.reports') }}" class="reports-filter-form" style="display:flex; align-items:center; gap:8px; margin:0; flex-wrap:wrap;">
            <div class="reports-date-group">
                <label for="repFromDate" style="font-size:11px; font-weight:700; color:#64748B; margin:0;">From:</label>
                <input type="date" id="repFromDate" name="from_date" value="{{ $fromDate ?? '' }}" class="form-control" style="font-size:12px; padding:5px 9px; height:auto; width:135px; font-weight:500;">
            </div>
            <div class="reports-date-group">
                <label for="repToDate" style="font-size:11px; font-weight:700; color:#64748B; margin:0;">To:</label>
                <input type="date" id="repToDate" name="to_date" value="{{ $toDate ?? '' }}" class="form-control" style="font-size:12px; padding:5px 9px; height:auto; width:135px; font-weight:500;">
            </div>
            <button type="submit" class="btn btn-primary btn-sm" style="padding:6px 14px; font-size:12px; font-weight:700; display:inline-flex; align-items:center; gap:4px;">
                <i data-lucide="filter" style="width:13px;height:13px;"></i> Apply Filter
            </button>
            @if(!empty($fromDate) || !empty($toDate) || $filter !== 'all')
                <a href="{{ route('mobileshop.reports', ['period' => 'all']) }}" class="btn btn-outline btn-sm" style="padding:6px 10px; font-size:12px; color:#64748B; font-weight:600;">Reset</a>
            @endif
        </form>
    </div>

    <!-- 1. EXECUTIVE KPI METRIC CARDS -->
    <div class="reports-kpi-grid">
        
        <!-- Total Revenue -->
        <div class="card" style="padding: 16px; border-left: 4px solid var(--brand-600); background:#fff;">
            <div style="font-size:11px; font-weight:800; text-transform:uppercase; color:#64748B; display:flex; justify-content:space-between; align-items:center;">
                <span>Total Revenue</span>
                <i data-lucide="trending-up" style="width:15px;height:15px; color:var(--brand-600);"></i>
            </div>
            <div style="font-size:21px; font-weight:900; color:#0F172A; margin-top:4px;">
                ₹{{ number_format($totalSalesVal, 2) }}
            </div>
            <div style="font-size:11px; color:#64748B; margin-top:2px;">
                {{ $totalOrdersCount }} Orders • Avg ₹{{ number_format($avgOrderValue, 0) }}
            </div>
        </div>

        <!-- Gross Margin / Profit -->
        <div class="card" style="padding: 16px; border-left: 4px solid #16A34A; background:#fff;">
            <div style="font-size:11px; font-weight:800; text-transform:uppercase; color:#64748B; display:flex; justify-content:space-between; align-items:center;">
                <span>Gross Profit</span>
                <span class="badge badge-green" style="font-size:10px;">{{ $marginPercent }}%</span>
            </div>
            <div style="font-size:21px; font-weight:900; color:#16A34A; margin-top:4px;">
                ₹{{ number_format($netProfitVal, 2) }}
            </div>
            <div style="font-size:11px; color:#64748B; margin-top:2px;">
                Net Margin after COGS
            </div>
        </div>

        <!-- Khata / Udhari Due -->
        <div class="card" style="padding: 16px; border-left: 4px solid #DC2626; background:#fff;">
            <div style="font-size:11px; font-weight:800; text-transform:uppercase; color:#64748B; display:flex; justify-content:space-between; align-items:center;">
                <span>Khata Due</span>
                <i data-lucide="alert-circle" style="width:15px;height:15px; color:#DC2626;"></i>
            </div>
            <div style="font-size:21px; font-weight:900; color:#DC2626; margin-top:4px;">
                ₹{{ number_format($totalUdhariReceivables, 2) }}
            </div>
            <div style="font-size:11px; color:#64748B; margin-top:2px;">
                {{ $totalDebtorsCount }} Active ({{ $totalSettledCount }} Settled)
            </div>
        </div>

        <!-- Active Stock Valuation -->
        <div class="card" style="padding: 16px; border-left: 4px solid #2563EB; background:#fff;">
            <div style="font-size:11px; font-weight:800; text-transform:uppercase; color:#64748B; display:flex; justify-content:space-between; align-items:center;">
                <span>In-Stock Assets</span>
                <i data-lucide="package" style="width:15px;height:15px; color:#2563EB;"></i>
            </div>
            <div style="font-size:21px; font-weight:900; color:#2563EB; margin-top:4px;">
                ₹{{ number_format($stockValuation['phones_retail'] + $stockValuation['parts_retail'], 2) }}
            </div>
            <div style="font-size:11px; color:#64748B; margin-top:2px;">
                {{ $stockValuation['phones_count'] }} Phones • {{ $stockValuation['parts_count'] }} Parts
            </div>
        </div>

        <!-- GST Tax Liability & Net Payable -->
        <div class="card" style="padding: 16px; border-left: 4px solid #7E22CE; background:#fff;">
            <div style="font-size:11px; font-weight:800; text-transform:uppercase; color:#64748B; display:flex; justify-content:space-between; align-items:center;">
                <span>GST Tax Output</span>
                <i data-lucide="file-check-2" style="width:15px;height:15px; color:#7E22CE;"></i>
            </div>
            <div style="font-size:21px; font-weight:900; color:#7E22CE; margin-top:4px;">
                ₹{{ number_format($gstr3b['output_total'] ?? $gstTotal, 2) }}
            </div>
            <div style="font-size:11px; color:#64748B; margin-top:2px; display:flex; justify-content:space-between;">
                <span>ITC: ₹{{ number_format($gstr3b['itc_total'] ?? 0, 0) }}</span>
                <span style="font-weight:700; color:{{ ($gstr3b['net_payable'] ?? 0) > 0 ? '#DC2626' : '#16A34A' }};">
                    Net: ₹{{ number_format($gstr3b['net_payable'] ?? $gstTotal, 0) }}
                </span>
            </div>
        </div>

    </div>

    <!-- 2. VISUAL ANALYTICS CHARTS (COLLAPSIBLE ROW) -->
    <div class="reports-grid-charts" id="rowCharts">
        
        <!-- Revenue & Profit Trend Chart -->
        <div class="card">
            <div class="card-header collapsible-header" onclick="toggleSection('bodyChartTrend', this)" style="background:#F8FAFC; border-bottom:1px solid #E2E8F0;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div>
                        <div class="card-title">Sales Revenue & Margin Trend</div>
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <span class="badge badge-purple" style="font-size:10px;">Performance</span>
                    <i data-lucide="chevron-down" class="collapse-icon"></i>
                </div>
            </div>
            <div class="card-body collapsible-body" id="bodyChartTrend" style="padding: 16px;">
                <div class="chart-box">
                    <canvas id="revenueTrendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Payment Mode Split Donut Chart -->
        <div class="card">
            <div class="card-header collapsible-header" onclick="toggleSection('bodyChartPayment', this)" style="background:#F8FAFC; border-bottom:1px solid #E2E8F0;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div>
                        <div class="card-title">Payment Collection Split</div>
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <span class="badge badge-green" style="font-size:10px;">Collection</span>
                    <i data-lucide="chevron-down" class="collapse-icon"></i>
                </div>
            </div>
            <div class="card-body collapsible-body" id="bodyChartPayment" style="padding: 16px;">
                <div class="chart-box">
                    <canvas id="paymentModeChart"></canvas>
                </div>
            </div>
        </div>

    </div>

    <!-- 3. TOP SELLING PRODUCTS HUB (COLLAPSIBLE & FILTERABLE) -->
    <div class="reports-grid-2col" id="rowTopProducts">

        <!-- Top Selling Mobiles -->
        <div class="card">
            <div class="card-header collapsible-header" onclick="toggleSection('bodyTopMobiles', this)" style="background:#F8FAFC; border-bottom:1px solid #E2E8F0;">
                <div>
                    <div class="card-title" style="display:flex; align-items:center; gap:8px;">
                        <span>📱 Top Selling Mobile Handsets</span>
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <span class="badge badge-blue" style="font-size:10px;">Handset Ranking</span>
                    <i data-lucide="chevron-down" class="collapse-icon"></i>
                </div>
            </div>

            <div class="collapsible-body" id="bodyTopMobiles">
                <!-- Search & Filter Bar -->
                <div style="padding: 10px 16px; background:#FAFAFA; border-bottom:1px solid #E2E8F0; display:flex; gap:8px; align-items:center;">
                    <i data-lucide="search" style="width:14px;height:14px; color:#94A3B8;"></i>
                    <input type="text" id="topMobileSearchInput" oninput="filterTopMobilesList()" placeholder="Filter by brand or model (e.g. Apple, Samsung, Vivo)..." style="border:none; background:transparent; outline:none; font-size:12px; width:100%; color:#0F172A;">
                </div>

                <div class="reports-table-scroll">
                    <table class="data-table" id="tableTopMobiles">
                        <thead>
                            <tr>
                                <th style="width:8%; text-align:center;">#</th>
                                <th>Brand & Model</th>
                                <th style="text-align:center; width:18%;">Units Sold</th>
                                <th style="text-align:right;">Revenue (₹)</th>
                                <th style="text-align:right;">Gross Profit (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topSellingMobiles as $idx => $mob)
                            <tr class="top-mobile-row" data-search="{{ strtolower($mob->brand . ' ' . $mob->model) }}">
                                <td style="text-align:center;">
                                    <span class="badge {{ $idx === 0 ? 'badge-orange' : ($idx === 1 ? 'badge-blue' : 'badge-purple') }}" style="font-size:11px; font-weight:800; border-radius:50%; width:22px; height:22px; display:inline-flex; align-items:center; justify-content:center; padding:0;">
                                        {{ $idx + 1 }}
                                    </span>
                                </td>
                                <td>
                                    <div style="font-weight:800; color:#0F172A; font-size:13px;">{{ $mob->brand }} {{ $mob->model }}</div>
                                    <div style="font-size:10.5px; color:#64748B;">{{ $mob->brand }} Official</div>
                                </td>
                                <td style="text-align:center;">
                                    <span class="badge badge-blue" style="font-weight:800; font-size:12px; padding:3px 10px;">
                                        {{ $mob->units_sold }} pcs
                                    </span>
                                </td>
                                <td style="text-align:right; font-family:monospace; font-weight:700; color:#0F172A;">
                                    ₹{{ number_format($mob->total_revenue, 2) }}
                                </td>
                                <td style="text-align:right; font-family:monospace; font-weight:800; color:#16A34A;">
                                    +₹{{ number_format($mob->total_profit, 2) }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" style="text-align:center; padding:24px; color:#94A3B8;">No mobile sales recorded in this period.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Top Selling Accessories & Spare Parts -->
        <div class="card">
            <div class="card-header collapsible-header" onclick="toggleSection('bodyTopParts', this)" style="background:#F8FAFC; border-bottom:1px solid #E2E8F0;">
                <div>
                    <div class="card-title" style="display:flex; align-items:center; gap:8px;">
                        <span>📦 Top Selling Accessories & Parts</span>
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <span class="badge badge-purple" style="font-size:10px;">Fast Moving</span>
                    <i data-lucide="chevron-down" class="collapse-icon"></i>
                </div>
            </div>

            <div class="collapsible-body" id="bodyTopParts">
                <!-- Search & Filter Bar -->
                <div style="padding: 10px 16px; background:#FAFAFA; border-bottom:1px solid #E2E8F0; display:flex; gap:8px; align-items:center;">
                    <i data-lucide="search" style="width:14px;height:14px; color:#94A3B8;"></i>
                    <input type="text" id="topPartsSearchInput" oninput="filterTopPartsList()" placeholder="Filter parts (e.g. Cover, Glass, Battery, Display)..." style="border:none; background:transparent; outline:none; font-size:12px; width:100%; color:#0F172A;">
                </div>

                <div class="reports-table-scroll">
                    <table class="data-table" id="tableTopParts">
                        <thead>
                            <tr>
                                <th style="width:8%; text-align:center;">#</th>
                                <th>Part / Item Name</th>
                                <th style="text-align:center; width:18%;">Qty Sold</th>
                                <th style="text-align:right;">Revenue (₹)</th>
                                <th style="text-align:right;">In Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topSellingParts as $idx => $part)
                            <tr class="top-part-row" data-search="{{ strtolower($part->name . ' ' . $part->category) }}">
                                <td style="text-align:center;">
                                    <span class="badge {{ $idx === 0 ? 'badge-orange' : ($idx === 1 ? 'badge-blue' : 'badge-purple') }}" style="font-size:11px; font-weight:800; border-radius:50%; width:22px; height:22px; display:inline-flex; align-items:center; justify-content:center; padding:0;">
                                        {{ $idx + 1 }}
                                    </span>
                                </td>
                                <td>
                                    <div style="font-weight:800; color:#0F172A; font-size:13px;">{{ $part->name }}</div>
                                    <span class="badge badge-purple" style="font-size:9.5px; padding:1px 6px; text-transform:capitalize;">
                                        {{ str_replace('_', ' ', $part->category) }}
                                    </span>
                                </td>
                                <td style="text-align:center;">
                                    <span class="badge badge-green" style="font-weight:800; font-size:12px; padding:3px 10px;">
                                        {{ $part->total_qty_sold }} pcs
                                    </span>
                                </td>
                                <td style="text-align:right; font-family:monospace; font-weight:700; color:#0F172A;">
                                    ₹{{ number_format($part->total_revenue, 2) }}
                                </td>
                                <td style="text-align:right;">
                                    <span class="badge {{ $part->current_stock <= 5 ? 'badge-rose' : 'badge-green' }}" style="font-size:11px; font-weight:700;">
                                        {{ $part->current_stock }} left
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" style="text-align:center; padding:24px; color:#94A3B8;">No accessory sales recorded in this period.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- 4. CUSTOMER KHATA DEBTORS & INVENTORY REORDER (COLLAPSIBLE & FILTERABLE) -->
    <div class="reports-grid-sub" id="rowKhataInventory">

        <!-- Active Debtors Ranking -->
        <div class="card">
            <div class="card-header collapsible-header" onclick="toggleSection('bodyDebtors', this)" style="background:#F8FAFC; border-bottom:1px solid #E2E8F0;">
                <div>
                    <div class="card-title" style="color:#B91C1C; display:flex; align-items:center; gap:8px;">
                        <i data-lucide="alert-circle" style="width:18px;height:18px; color:#DC2626;"></i>
                        Top Pending Customer Khata Debtors (<span id="debtorCountSpan">{{ count($debtors) }}</span>)
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <a href="{{ route('mobileshop.khata') }}" onclick="event.stopPropagation();" class="btn btn-outline btn-sm" style="color:#B91C1C; border-color:#FECDD3; font-weight:700; font-size:11px;">
                        Khata Hub
                    </a>
                    <i data-lucide="chevron-down" class="collapse-icon"></i>
                </div>
            </div>

            <div class="collapsible-body" id="bodyDebtors">
                <!-- Search & Threshold Filters -->
                <div style="padding: 10px 16px; background:#FAFAFA; border-bottom:1px solid #E2E8F0; display:flex; flex-wrap:wrap; gap:8px; align-items:center; justify-content:space-between;">
                    <div style="display:flex; align-items:center; gap:6px; flex:1; min-width:180px;">
                        <i data-lucide="search" style="width:14px;height:14px; color:#94A3B8;"></i>
                        <input type="text" id="debtorSearchInput" oninput="filterDebtorsList()" placeholder="Filter debtors by name or phone..." style="border:none; background:transparent; outline:none; font-size:12px; width:100%; color:#0F172A;">
                    </div>
                    <div style="display:flex; gap:4px; align-items:center;">
                        <button type="button" class="sub-filter-pill active debtor-filter-pill" onclick="setDebtorThreshold(0, this)">All</button>
                        <button type="button" class="sub-filter-pill debtor-filter-pill" onclick="setDebtorThreshold(5000, this)">> ₹5k</button>
                        <button type="button" class="sub-filter-pill debtor-filter-pill" onclick="setDebtorThreshold(10000, this)">> ₹10k</button>
                    </div>
                </div>

                <div class="reports-table-scroll">
                    <table class="data-table" id="tableDebtors">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Phone</th>
                                <th style="text-align:right;">Outstanding Due (₹)</th>
                                <th style="text-align:center; width:30%;">Quick Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($debtors as $debtor)
                            @php
                                $dPhone = preg_replace('/[^0-9]/', '', $debtor->phone ?? '');
                                if (strlen($dPhone) === 10) $dPhone = '91' . $dPhone;
                                $stName = setting('company.name', 'MobiTrack');
                                $dMsg = "🔔 *PAYMENT REMINDER*\n🏪 *{$stName}*\nDear *{$debtor->name}*,\nPending Khata Due: *₹" . number_format($debtor->udhari_balance, 2) . "*.\nKindly clear your balance. Thank you!";
                                $dWaUrl = 'https://wa.me/' . $dPhone . '?text=' . rawurlencode($dMsg);
                            @endphp
                            <tr class="debtor-row" data-amount="{{ (float) $debtor->udhari_balance }}" data-search="{{ strtolower($debtor->name . ' ' . $debtor->phone . ' ' . $debtor->address) }}">
                                <td>
                                    <div style="font-weight:800; color:#0F172A; font-size:13px;">{{ $debtor->name }}</div>
                                    <div style="font-size:10.5px; color:#64748B;">{{ $debtor->address ?: 'Walk-in Customer' }}</div>
                                </td>
                                <td style="font-family:monospace; font-weight:700; color:#475569;">
                                    {{ $debtor->phone ?: '—' }}
                                </td>
                                <td style="text-align:right; font-family:monospace; font-weight:900; color:#DC2626; font-size:14px;">
                                    ₹{{ number_format($debtor->udhari_balance, 2) }}
                                </td>
                                <td style="text-align:center; white-space:nowrap;">
                                    <div style="display:inline-flex; gap:6px; align-items:center;">
                                        <a href="{{ route('mobileshop.khata.customer_statement', ['id' => $debtor->id]) }}" class="btn btn-outline btn-sm" style="padding:4px 8px; font-size:11px; font-weight:700; color:#0F172A; text-decoration:none;" title="View & Print Tally Statement">
                                            <i data-lucide="file-text" style="width:12px;height:12px;"></i> Statement
                                        </a>
                                        <a href="{{ $dWaUrl }}" target="_blank" class="btn btn-sm" style="background:#25D366; color:#fff; font-weight:700; border:none; padding:4px 8px; font-size:11px; text-decoration:none; display:inline-flex; align-items:center; gap:3px;">
                                            WhatsApp
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" style="text-align:center; padding:24px; color:#16A34A; font-weight:700;">
                                    🎉 All customer credit accounts are fully settled! Zero overdue balance.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Inventory Reorder & Low Stock Alerts -->
        <div class="card">
            <div class="card-header collapsible-header" onclick="toggleSection('bodyLowStock', this)" style="background:#F8FAFC; border-bottom:1px solid #E2E8F0;">
                <div>
                    <div class="card-title" style="color:#D97706; display:flex; align-items:center; gap:8px;">
                        <i data-lucide="alert-triangle" style="width:18px;height:18px; color:#D97706;"></i>
                        Low Stock & Restock Warnings
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <a href="{{ route('mobileshop.purchase') }}" onclick="event.stopPropagation();" class="btn btn-outline btn-sm" style="font-weight:700; font-size:11px;">
                        Inward PO
                    </a>
                    <i data-lucide="chevron-down" class="collapse-icon"></i>
                </div>
            </div>

            <div class="collapsible-body" id="bodyLowStock">
                <div class="reports-table-scroll">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>Category</th>
                                <th style="text-align:right;">Remaining</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lowStockAlerts as $item)
                            <tr>
                                <td>
                                    <div style="font-weight:700; color:#0F172A; font-size:12.5px;">{{ $item->name }}</div>
                                </td>
                                <td>
                                    <span class="badge badge-purple" style="font-size:9.5px; text-transform:capitalize;">
                                        {{ str_replace('_', ' ', $item->category) }}
                                    </span>
                                </td>
                                <td style="text-align:right;">
                                    <span class="badge badge-rose" style="font-weight:800; font-size:11.5px; padding:2px 8px;">
                                        {{ $item->stock_qty }} pcs left
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" style="text-align:center; padding:24px; color:#16A34A; font-weight:700;">
                                    ✓ All accessories and spare parts are adequately stocked!
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- 5. GST FILING & TAX AUDIT STATEMENT (GSTR-1 & GSTR-3B) -->
    <div class="card" id="cardGstStatement" style="margin-bottom:20px; border-top:3px solid #7E22CE;">
        <div class="card-header collapsible-header" onclick="toggleSection('bodyGstStatement', this)" style="background:#FDF4FF; border-bottom:1px solid #F3E8FF;">
            <div>
                <div class="card-title" style="color:#6B21A8; display:flex; align-items:center; gap:8px;">
                    <i data-lucide="file-check-2" style="width:18px;height:18px; color:#7E22CE;"></i>
                    GST Filing & Tax Audit Statement (GSTR-1 & GSTR-3B)
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                <span class="badge" style="background:#FAF5FF; color:#7E22CE; border:1px solid #E9D5FF; font-size:11px; font-weight:800;">
                    Net Tax: ₹{{ number_format($gstr3b['net_payable'], 2) }}
                </span>
                <span class="badge badge-purple" style="font-size:11px; font-weight:800;">
                    {{ count($b2bInvoices) }} B2B / {{ count($b2cInvoices) }} B2C
                </span>
                <i data-lucide="chevron-down" class="collapse-icon" style="color:#7E22CE;"></i>
            </div>
        </div>

        <div class="collapsible-body" id="bodyGstStatement">
            
            <!-- GSTR-3B RECONCILIATION SUMMARY BOXES -->
            <div style="padding: 18px 20px; background:#FAFAFA; border-bottom:1px solid #E2E8F0;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; flex-wrap:wrap; gap:8px;">
                    <div style="font-size:12px; font-weight:800; text-transform:uppercase; color:#64748B; letter-spacing:0.5px;">
                        GSTR-3B Tax Summary (Month / Period Reconciliation)
                    </div>
                    <div style="display:flex; gap:8px; align-items:center;">
                        <a href="{{ route('mobileshop.reports.export.gst', request()->all()) }}" class="btn btn-sm btn-primary" style="background:#7E22CE; border-color:#6B21A8; padding:5px 12px; font-size:11px; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:5px;">
                            <i data-lucide="download" style="width:13px;height:13px;"></i> Download GSTR-1 Excel/CSV for CA
                        </a>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:14px;">
                    <!-- Outward Tax (Sales) -->
                    <div style="background:#fff; border:1px solid #E2E8F0; border-radius:10px; padding:14px; border-left:4px solid #7E22CE;">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <span style="font-size:11px; font-weight:800; color:#64748B; text-transform:uppercase;">Table 3.1: Output Tax (Sales)</span>
                            <span class="badge badge-purple" style="font-size:10px;">Liability</span>
                        </div>
                        <div style="font-size:20px; font-weight:900; color:#0F172A; margin:6px 0 4px 0;">
                            ₹{{ number_format($gstr3b['output_total'], 2) }}
                        </div>
                        <div style="font-size:11px; color:#64748B; line-height:1.6;">
                            Taxable Value: <strong>₹{{ number_format($gstr3b['output_taxable'], 2) }}</strong><br>
                            CGST: ₹{{ number_format($gstr3b['output_cgst'], 2) }} &bull; SGST: ₹{{ number_format($gstr3b['output_sgst'], 2) }} &bull; IGST: ₹{{ number_format($gstr3b['output_igst'], 2) }}
                        </div>
                    </div>

                    <!-- Input Tax Credit (Purchases) -->
                    <div style="background:#fff; border:1px solid #E2E8F0; border-radius:10px; padding:14px; border-left:4px solid #0284C7;">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <span style="font-size:11px; font-weight:800; color:#64748B; text-transform:uppercase;">Table 4.0: Input Tax Credit (Purchases)</span>
                            <span class="badge badge-blue" style="font-size:10px;">ITC Credit</span>
                        </div>
                        <div style="font-size:20px; font-weight:900; color:#0284C7; margin:6px 0 4px 0;">
                            ₹{{ number_format($gstr3b['itc_total'], 2) }}
                        </div>
                        <div style="font-size:11px; color:#64748B; line-height:1.6;">
                            Taxable Purchases: <strong>₹{{ number_format($gstr3b['itc_taxable'], 2) }}</strong><br>
                            ITC CGST: ₹{{ number_format($gstr3b['itc_cgst'], 2) }} &bull; SGST: ₹{{ number_format($gstr3b['itc_sgst'], 2) }} &bull; IGST: ₹{{ number_format($gstr3b['itc_igst'], 2) }}
                        </div>
                    </div>

                    <!-- Net Tax Payable -->
                    <div style="background:{{ $gstr3b['net_payable'] > 0 ? '#FDF2F8' : '#F0FDF4' }}; border:1px solid {{ $gstr3b['net_payable'] > 0 ? '#FBCFE8' : '#BBF7D0' }}; border-radius:10px; padding:14px; border-left:4px solid {{ $gstr3b['net_payable'] > 0 ? '#DB2777' : '#16A34A' }};">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <span style="font-size:11px; font-weight:800; color:{{ $gstr3b['net_payable'] > 0 ? '#9D174D' : '#166534' }}; text-transform:uppercase;">Net GST Payable</span>
                            <span class="badge {{ $gstr3b['net_payable'] > 0 ? 'badge-pink' : 'badge-green' }}" style="font-size:10px;">
                                {{ $gstr3b['net_payable'] > 0 ? 'Cash Due' : 'ITC Surplus' }}
                            </span>
                        </div>
                        <div style="font-size:20px; font-weight:900; color:{{ $gstr3b['net_payable'] > 0 ? '#BE185D' : '#16A34A' }}; margin:6px 0 4px 0;">
                            ₹{{ number_format($gstr3b['net_payable'], 2) }}
                        </div>
                        <div style="font-size:11px; color:{{ $gstr3b['net_payable'] > 0 ? '#9D174D' : '#166534' }}; line-height:1.6;">
                            @if($gstr3b['net_payable'] > 0)
                                Net CGST: ₹{{ number_format($gstr3b['net_cgst'], 2) }} &bull; SGST: ₹{{ number_format($gstr3b['net_sgst'], 2) }} &bull; IGST: ₹{{ number_format($gstr3b['net_igst'], 2) }}
                            @else
                                Full output tax liability is offset by available Input Tax Credit!
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- GSTR-1 DETAILED BREAKDOWN (B2B, B2C, HSN) -->
            <div style="padding: 16px 20px;">
                
                <!-- SUB-SECTION 1: B2B INVOICES -->
                <div style="margin-bottom:24px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; flex-wrap:wrap; gap:6px;">
                        <div style="font-size:13px; font-weight:800; color:#0F172A; display:flex; align-items:center; gap:6px;">
                            <span class="badge badge-purple" style="font-size:10px;">Table 4</span>
                            B2B Invoices (Sales to GST Registered Customers with GSTIN)
                        </div>
                        <span style="font-size:11px; font-weight:700; color:#64748B;">{{ count($b2bInvoices) }} Registered Invoices</span>
                    </div>

                    <div class="reports-table-scroll">
                        <table class="data-table" style="min-width:700px; font-size:12px;">
                            <thead>
                                <tr>
                                    <th>Recipient GSTIN</th>
                                    <th>Customer / Firm</th>
                                    <th>Invoice #</th>
                                    <th>Date</th>
                                    <th style="text-align:right;">Taxable Value</th>
                                    <th style="text-align:right;">CGST</th>
                                    <th style="text-align:right;">SGST</th>
                                    <th style="text-align:right;">IGST</th>
                                    <th style="text-align:right;">Invoice Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($b2bInvoices as $inv)
                                    <tr>
                                        <td>
                                            <span style="font-family:monospace; font-weight:800; color:#7E22CE; background:#F5F3FF; padding:2px 6px; border-radius:4px;">
                                                {{ $inv->customer_gstin }}
                                            </span>
                                        </td>
                                        <td>
                                            <div style="font-weight:700; color:#0F172A;">{{ $inv->customer_name }}</div>
                                            <div style="font-size:10px; color:#64748B;">{{ $inv->item_desc }}</div>
                                        </td>
                                        <td style="font-weight:700;">{{ $inv->invoice_number }}</td>
                                        <td style="color:#64748B;">{{ date('d-M-Y', strtotime($inv->date)) }}</td>
                                        <td style="text-align:right; font-weight:600;">₹{{ number_format($inv->taxable_value, 2) }}</td>
                                        <td style="text-align:right; color:#7E22CE;">₹{{ number_format($inv->cgst, 2) }}</td>
                                        <td style="text-align:right; color:#7E22CE;">₹{{ number_format($inv->sgst, 2) }}</td>
                                        <td style="text-align:right; color:#7E22CE;">₹{{ number_format($inv->igst, 2) }}</td>
                                        <td style="text-align:right; font-weight:800; color:#0F172A;">₹{{ number_format($inv->total_amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" style="text-align:center; padding:18px; color:#64748B; font-style:italic;">
                                            No B2B sales with customer GSTIN recorded in this period. All transactions were consumer retail (B2C).
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- SUB-SECTION 2: HSN-WISE SUMMARY -->
                <div style="margin-bottom:12px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; flex-wrap:wrap; gap:6px;">
                        <div style="font-size:13px; font-weight:800; color:#0F172A; display:flex; align-items:center; gap:6px;">
                            <span class="badge badge-purple" style="font-size:10px;">Table 12</span>
                            HSN-wise Summary of Outward Supplies
                        </div>
                        <span style="font-size:11px; font-weight:700; color:#64748B;">Mandatory for GSTR-1 Return</span>
                    </div>

                    <div class="reports-table-scroll">
                        <table class="data-table" style="min-width:700px; font-size:12px;">
                            <thead>
                                <tr>
                                    <th>HSN Code</th>
                                    <th>Description</th>
                                    <th style="text-align:center;">UQC</th>
                                    <th style="text-align:center;">Total Qty</th>
                                    <th style="text-align:right;">Taxable Value</th>
                                    <th style="text-align:right;">Central Tax (CGST)</th>
                                    <th style="text-align:right;">State Tax (SGST)</th>
                                    <th style="text-align:right;">Integrated Tax (IGST)</th>
                                    <th style="text-align:right;">Total Invoiced Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($hsnSummary as $hsn)
                                    <tr>
                                        <td>
                                            <span style="font-family:monospace; font-weight:800; color:#0F172A; background:#F1F5F9; padding:2px 6px; border-radius:4px;">
                                                {{ $hsn['hsn'] }}
                                            </span>
                                        </td>
                                        <td style="font-weight:600; color:#334155;">{{ $hsn['desc'] }}</td>
                                        <td style="text-align:center; font-weight:700; color:#64748B;">{{ $hsn['uqc'] }}</td>
                                        <td style="text-align:center; font-weight:800; color:#0F172A;">{{ $hsn['qty'] }}</td>
                                        <td style="text-align:right; font-weight:600;">₹{{ number_format($hsn['taxable_value'], 2) }}</td>
                                        <td style="text-align:right; color:#7E22CE;">₹{{ number_format($hsn['cgst'], 2) }}</td>
                                        <td style="text-align:right; color:#7E22CE;">₹{{ number_format($hsn['sgst'], 2) }}</td>
                                        <td style="text-align:right; color:#7E22CE;">₹{{ number_format($hsn['igst'], 2) }}</td>
                                        <td style="text-align:right; font-weight:800; color:#0F172A;">₹{{ number_format($hsn['total_value'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <!-- 6. DETAILED SALES INVOICES LEDGER (COLLAPSIBLE & INTERACTIVE FILTERING) -->
    <div class="card" id="cardLedger">
        <div class="card-header collapsible-header" onclick="toggleSection('bodySalesLedger', this)" style="background:#F8FAFC; border-bottom:1px solid #E2E8F0;">
            <div>
                <div class="card-title">Detailed Sales Invoices Ledger</div>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
                <span class="badge badge-blue" id="ledgerCountBadge" style="font-size:11px; font-weight:800;">
                    {{ count($mobileSales) + count($accSales) }} Invoices
                </span>
                <i data-lucide="chevron-down" class="collapse-icon"></i>
            </div>
        </div>

        <div class="collapsible-body" id="bodySalesLedger">
            <!-- Filter Pills & Live Search Bar -->
            <div style="padding: 12px 18px; background:#FAFAFA; border-bottom:1px solid #E2E8F0; display:flex; flex-wrap:wrap; gap:12px; align-items:center; justify-content:space-between;">
                
                <!-- Category / Niche Filter Pills -->
                <div style="display:flex; flex-wrap:wrap; gap:6px; align-items:center;">
                    <span style="font-size:11px; font-weight:800; color:#64748B; text-transform:uppercase;">Category:</span>
                    <button type="button" class="sub-filter-pill active ledger-type-pill" onclick="setLedgerTypeFilter('all', this)">All</button>
                    <button type="button" class="sub-filter-pill ledger-type-pill" onclick="setLedgerTypeFilter('phone', this)">📱 Mobiles ({{ count($mobileSales) }})</button>
                    <button type="button" class="sub-filter-pill ledger-type-pill" onclick="setLedgerTypeFilter('accessory', this)">📦 Accessories ({{ count($accSales) }})</button>
                </div>

                <!-- Payment Mode Pills -->
                <div style="display:flex; flex-wrap:wrap; gap:6px; align-items:center;">
                    <span style="font-size:11px; font-weight:800; color:#64748B; text-transform:uppercase;">Payment:</span>
                    <button type="button" class="sub-filter-pill active ledger-pay-pill" onclick="setLedgerPayFilter('all', this)">All</button>
                    <button type="button" class="sub-filter-pill ledger-pay-pill" onclick="setLedgerPayFilter('cash', this)">Cash</button>
                    <button type="button" class="sub-filter-pill ledger-pay-pill" onclick="setLedgerPayFilter('upi', this)">UPI</button>
                    <button type="button" class="sub-filter-pill ledger-pay-pill" onclick="setLedgerPayFilter('emi', this)">EMI</button>
                    <button type="button" class="sub-filter-pill ledger-pay-pill" onclick="setLedgerPayFilter('card', this)">Card</button>
                </div>

                <!-- Live Search Box -->
                <div style="display:flex; align-items:center; gap:6px; background:#fff; border:1px solid #CBD5E1; border-radius:8px; padding:4px 10px; width:100%; max-width:260px;">
                    <i data-lucide="search" style="width:13px;height:13px; color:#94A3B8;"></i>
                    <input type="text" id="ledgerSearchInput" oninput="filterSalesLedgerTable()" placeholder="Search invoice, customer, IMEI..." style="border:none; outline:none; font-size:11.5px; width:100%; color:#0F172A;">
                </div>
            </div>

            <div class="reports-table-scroll">
                <table class="data-table" id="tableSalesLedger" style="min-width: 680px;">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Invoice #</th>
                            <th>Customer</th>
                            <th>Item Details</th>
                            <th>Payment</th>
                            <th style="text-align:right;">Sale Price (₹)</th>
                            <th style="text-align:right;">Profit Margin (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($mobileSales as $sale)
                        @php $margin = max(0, $sale->total_amount - ($sale->purchase_cost ?? 0)); @endphp
                        <tr class="ledger-row" data-type="phone" data-mode="{{ strtolower($sale->payment_mode) }}" data-search="{{ strtolower($sale->invoice_number . ' ' . $sale->customer_name . ' ' . $sale->customer_phone . ' ' . $sale->brand . ' ' . $sale->model . ' ' . $sale->imei_1 . ' ' . $sale->payment_mode) }}">
                            <td style="font-size:11px; color:#64748B; white-space:nowrap;">{{ \Carbon\Carbon::parse($sale->created_at)->format('d M Y, h:i A') }}</td>
                            <td>
                                <a href="{{ route('mobileshop.invoice', ['id' => $sale->id]) }}" style="font-family:monospace; font-weight:800; color:var(--brand-700); text-decoration:none;">
                                    {{ $sale->invoice_number }}
                                </a>
                            </td>
                            <td>
                                <div style="font-weight:700; font-size:13px;">{{ $sale->customer_name }}</div>
                                <div style="font-size:11px; color:#64748B;">{{ $sale->customer_phone }}</div>
                            </td>
                            <td>
                                <div style="font-weight:700;">{{ $sale->brand }} {{ $sale->model }}</div>
                                <div style="font-family:monospace; font-size:10px; color:#64748B;">IMEI: {{ $sale->imei_1 }}</div>
                            </td>
                            <td>
                                <span class="badge badge-blue" style="text-transform:uppercase; font-size:10px;">{{ $sale->payment_mode }}</span>
                            </td>
                            <td style="text-align:right; font-weight:800; color:#0F172A; white-space:nowrap;">₹{{ number_format($sale->total_amount, 2) }}</td>
                            <td style="text-align:right; font-weight:800; color:#16A34A; white-space:nowrap;">+₹{{ number_format($margin, 2) }}</td>
                        </tr>
                        @endforeach

                        @foreach($accSales as $asale)
                        <tr class="ledger-row" data-type="accessory" data-mode="{{ strtolower($asale->payment_mode) }}" data-search="{{ strtolower($asale->invoice_number . ' ' . $asale->customer_name . ' ' . $asale->customer_phone . ' ' . $asale->payment_mode . ' accessory') }}">
                            <td style="font-size:11px; color:#64748B; white-space:nowrap;">{{ \Carbon\Carbon::parse($asale->created_at)->format('d M Y, h:i A') }}</td>
                            <td>
                                <a href="{{ route('mobileshop.accessories.invoice', ['id' => $asale->id]) }}" style="font-family:monospace; font-weight:800; color:var(--brand-700); text-decoration:none;">
                                    {{ $asale->invoice_number }}
                                </a>
                            </td>
                            <td>
                                <div style="font-weight:700; font-size:13px;">{{ $asale->customer_name ?: 'Walk-in Customer' }}</div>
                                <div style="font-size:11px; color:#64748B;">{{ $asale->customer_phone ?: '—' }}</div>
                            </td>
                            <td>
                                <span class="badge badge-purple" style="font-size:10.5px;">Accessories Multi-Item Bill</span>
                            </td>
                            <td>
                                <span class="badge badge-blue" style="text-transform:uppercase; font-size:10px;">{{ $asale->payment_mode }}</span>
                            </td>
                            <td style="text-align:right; font-weight:800; color:#0F172A; white-space:nowrap;">₹{{ number_format($asale->total_amount, 2) }}</td>
                            <td style="text-align:right; font-weight:700; color:#16A34A; white-space:nowrap;">Retail Sale</td>
                        </tr>
                        @endforeach

                        <tr id="noLedgerRecordsRow" style="display: {{ (count($mobileSales) === 0 && count($accSales) === 0) ? '' : 'none' }};">
                            <td colspan="7" style="text-align:center; padding:32px; color:#94A3B8;">No transaction records matched your filter.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div id="reportsLedgerPagination"></div>
        </div>
    </div>

@endsection

@push('scripts')
<!-- Chart.js (with error boundary) -->
<script>
    window.__reportsChartFallback = function () {
        ['revenueTrendChart', 'paymentModeChart'].forEach(function (id) {
            var canvas = document.getElementById(id);
            if (!canvas) return;
            var ctx = canvas.getContext('2d');
            if (!ctx) return;
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = '#F1F5F9';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = '#94A3B8';
            ctx.font = '14px Inter, sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText('Chart unavailable — data shown in tables below', canvas.width / 2, canvas.height / 2);
        });
    };
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js" onerror="window.__reportsChartFallback();"></script>
<script>
// ─── 1. Collapsible Cards Logic ───
function toggleSection(bodyId, headerEl) {
    const bodyEl = document.getElementById(bodyId);
    if (!bodyEl) return;
    
    const isCurrentlyHidden = bodyEl.classList.contains('is-hidden');
    if (isCurrentlyHidden) {
        bodyEl.classList.remove('is-hidden');
        headerEl?.classList.remove('is-collapsed');
    } else {
        bodyEl.classList.add('is-hidden');
        headerEl?.classList.add('is-collapsed');
    }
}

let allSectionsCollapsed = false;
function toggleAllReportSections() {
    allSectionsCollapsed = !allSectionsCollapsed;
    const bodyIds = ['bodyChartTrend', 'bodyChartPayment', 'bodyTopMobiles', 'bodyTopParts', 'bodyDebtors', 'bodyLowStock', 'bodySalesLedger'];
    
    bodyIds.forEach(id => {
        const bodyEl = document.getElementById(id);
        const headerEl = bodyEl?.previousElementSibling;
        if (bodyEl) {
            if (allSectionsCollapsed) {
                bodyEl.classList.add('is-hidden');
                headerEl?.classList.add('is-collapsed');
            } else {
                bodyEl.classList.remove('is-hidden');
                headerEl?.classList.remove('is-collapsed');
            }
        }
    });
}

// ─── 2. Top Mobiles Live Search ───
function filterTopMobilesList() {
    const q = document.getElementById('topMobileSearchInput')?.value.toLowerCase().trim() || '';
    document.querySelectorAll('#tableTopMobiles tbody tr.top-mobile-row').forEach(row => {
        const text = row.dataset.search || row.textContent.toLowerCase();
        row.style.display = (!q || text.includes(q)) ? '' : 'none';
    });
}

// ─── 3. Top Parts Live Search ───
function filterTopPartsList() {
    const q = document.getElementById('topPartsSearchInput')?.value.toLowerCase().trim() || '';
    document.querySelectorAll('#tableTopParts tbody tr.top-part-row').forEach(row => {
        const text = row.dataset.search || row.textContent.toLowerCase();
        row.style.display = (!q || text.includes(q)) ? '' : 'none';
    });
}

// ─── 4. Debtors Search & Threshold Filters ───
let currentDebtorMinAmount = 0;
function setDebtorThreshold(minAmt, btnEl) {
    currentDebtorMinAmount = minAmt;
    document.querySelectorAll('.debtor-filter-pill').forEach(el => el.classList.remove('active'));
    btnEl?.classList.add('active');
    filterDebtorsList();
}

function filterDebtorsList() {
    const q = document.getElementById('debtorSearchInput')?.value.toLowerCase().trim() || '';
    let visible = 0;
    document.querySelectorAll('#tableDebtors tbody tr.debtor-row').forEach(row => {
        const amt = parseFloat(row.dataset.amount || '0');
        const text = row.dataset.search || row.textContent.toLowerCase();
        const matchesAmt = amt >= currentDebtorMinAmount;
        const matchesText = !q || text.includes(q);
        
        if (matchesAmt && matchesText) {
            row.style.display = '';
            visible++;
        } else {
            row.style.display = 'none';
        }
    });
    const countSpan = document.getElementById('debtorCountSpan');
    if (countSpan) countSpan.textContent = visible;
}

// ─── 5. Sales Ledger Multi-Filter & Search ───
let currentLedgerType = 'all';
let currentLedgerPay = 'all';

function setLedgerTypeFilter(type, btnEl) {
    currentLedgerType = type;
    document.querySelectorAll('.ledger-type-pill').forEach(el => el.classList.remove('active'));
    btnEl?.classList.add('active');
    filterSalesLedgerTable();
}

function setLedgerPayFilter(pay, btnEl) {
    currentLedgerPay = pay;
    document.querySelectorAll('.ledger-pay-pill').forEach(el => el.classList.remove('active'));
    btnEl?.classList.add('active');
    filterSalesLedgerTable();
}

function filterSalesLedgerTable() {
    const q = document.getElementById('ledgerSearchInput')?.value.toLowerCase().trim() || '';
    let visibleCount = 0;

    document.querySelectorAll('#tableSalesLedger tbody tr.ledger-row').forEach(row => {
        const rowType = row.dataset.type;
        const rowMode = row.dataset.mode;
        const rowText = row.dataset.search || row.textContent.toLowerCase();

        const matchesType = (currentLedgerType === 'all') || (rowType === currentLedgerType);
        const matchesPay  = (currentLedgerPay === 'all')  || (rowMode === currentLedgerPay);
        const matchesSearch = !q || rowText.includes(q);

        const isVisible = matchesType && matchesPay && matchesSearch;
        row.dataset.mobiHidden = isVisible ? '0' : '1';
        row.style.display = isVisible ? '' : 'none';
        if (isVisible) {
            visibleCount++;
        }
    });

    if (window.reportsLedgerPager && typeof window.reportsLedgerPager.refresh === 'function') {
        window.reportsLedgerPager.refresh();
    }

    const noRow = document.getElementById('noLedgerRecordsRow');
    if (noRow) noRow.style.display = (visibleCount === 0) ? '' : 'none';

    const countBadge = document.getElementById('ledgerCountBadge');
    if (countBadge) countBadge.textContent = `${visibleCount} Invoices`;
}

// ─── 6. Initialize Charts & Pagination ───
document.addEventListener('DOMContentLoaded', function() {
    if (window.setupMobiTablePagination && document.getElementById('tableSalesLedger')) {
        window.reportsLedgerPager = window.setupMobiTablePagination({
            tableId: 'tableSalesLedger',
            paginationContainerId: 'reportsLedgerPagination',
            rowSelector: 'tbody tr.ledger-row',
            pageSize: 25,
            itemName: 'transactions'
        });
    }

    // Revenue & Profit Trend Chart
    const trendCtx = document.getElementById('revenueTrendChart')?.getContext('2d');
    if (trendCtx) {
        new Chart(trendCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [
                    {
                        label: 'Total Revenue (₹)',
                        data: {!! json_encode($chartRevenue) !!},
                        backgroundColor: 'rgba(99, 102, 241, 0.85)',
                        borderColor: '#6366F1',
                        borderRadius: 6,
                        barPercentage: 0.6,
                    },
                    {
                        label: 'Estimated Margin (₹)',
                        data: {!! json_encode($chartProfit) !!},
                        type: 'line',
                        borderColor: '#16A34A',
                        backgroundColor: 'rgba(22, 163, 74, 0.1)',
                        borderWidth: 2.5,
                        pointBackgroundColor: '#16A34A',
                        pointRadius: 4,
                        fill: false,
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { boxWidth: 12, font: { size: 11, weight: 'bold' } }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ₹' + Number(context.raw).toLocaleString('en-IN');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#F1F5F9' },
                        ticks: {
                            callback: function(val) { return '₹' + Number(val).toLocaleString('en-IN'); },
                            font: { size: 10 }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10, weight: 'bold' } }
                    }
                }
            }
        });
    }

    // Payment Modes Donut Chart
    const payCtx = document.getElementById('paymentModeChart')?.getContext('2d');
    if (payCtx) {
        const payData = {!! json_encode($paymentModes) !!};
        new Chart(payCtx, {
            type: 'doughnut',
            data: {
                labels: ['Cash', 'UPI / QR', 'Debit/Credit Card', 'EMI / Finance', 'Khata Credit'],
                datasets: [{
                    data: [payData.cash, payData.upi, payData.card, payData.emi, payData.udhari],
                    backgroundColor: [
                        '#10B981', // Cash green
                        '#6366F1', // UPI indigo
                        '#3B82F6', // Card blue
                        '#8B5CF6', // EMI purple
                        '#EF4444'  // Khata red
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: { boxWidth: 12, font: { size: 11 } }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const val = context.raw || 0;
                                const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                                return context.label + ': ₹' + Number(val).toLocaleString('en-IN') + ' (' + pct + '%)';
                            }
                        }
                    }
                },
                cutout: '65%'
            }
        });
    }
});
</script>
@endpush

@push('styles')
<style>
    @media print {
        .reports-filter-card,
        .filter-bar,
        .filter-pill,
        .pagination-bar,
        .pagination-wrapper {
            display: none !important;
        }
        .card {
            border: 1px solid #CBD5E1 !important;
            box-shadow: none !important;
            break-inside: avoid !important;
            margin-bottom: 16px !important;
        }
    }
</style>
@endpush
