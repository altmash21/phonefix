@extends('mobileshop.layout')

@php
    // Niche-aware page title and action button
    $nicheTitles = [
        'admin'       => 'Store Dashboard — All Niches',
        'phones'      => 'New Phones Dashboard',
        'secondhand'  => 'Pre-Owned & Buyback Dashboard',
        'accessories' => 'Accessories & Parts Dashboard',
        'covers'      => 'Back Cover & Tempered Dashboard',
        'repairs'     => 'Service Desk Dashboard',
    ];
    $nicheTitle = $nicheTitles[$niche ?? 'admin'] ?? 'Dashboard';
@endphp

@section('title', $nicheTitle . ' — Maurya Mobile ERP')
@section('page-title', $nicheTitle)

@section('page-actions')
    <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
        <span class="badge badge-gray" style="font-size:11px;padding:4px 8px;display:inline-flex;align-items:center;gap:5px;background:#fff;border:1px solid #E2E8F0;">
            <i data-lucide="calendar" style="width:13px;height:13px;color:var(--color-primary);"></i>
            {{ date('D, d M Y') }}
        </span>
        
        <div style="position:relative;" id="new-action-wrap">
            <button type="button" class="btn btn-primary btn-sm" id="new-action-btn" style="gap:6px; font-weight:600;">
                <i data-lucide="plus" style="width:14px;height:14px;"></i>
                <span>New</span>
                <i data-lucide="chevron-down" style="width:12px;height:12px; opacity:0.8;"></i>
            </button>
            <div id="new-action-menu">
                @if(auth()->user()->can('create-sale-phones') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin'))
                <a href="{{ route('mobileshop.sales.create') }}" class="dropdown-item-link" style="font-weight:600; color:var(--color-primary);">
                    <i data-lucide="plus-circle" style="width:15px;height:15px;color:var(--color-primary);"></i> Sell New Phone
                </a>
                @endif
                @if(auth()->user()->can('create-purchase-phones') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin'))
                <a href="{{ route('mobileshop.purchase.create') }}" class="dropdown-item-link" style="font-weight:600; color:#16A34A;">
                    <i data-lucide="truck" style="width:15px;height:15px;color:#16A34A;"></i> Purchase New Phone
                </a>
                @endif
                @if(auth()->user()->can('create-sale-accessories') || auth()->user()->can('create-sale-covers') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin'))
                <a href="{{ route('mobileshop.sales') }}" class="dropdown-item-link">
                    <i data-lucide="zap" style="width:15px;height:15px;"></i> Sell Accessories
                </a>
                @endif
                @if(auth()->user()->can('create-sale-secondhand') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin'))
                <a href="{{ route('mobileshop.sales') }}" class="dropdown-item-link">
                    <i data-lucide="repeat" style="width:15px;height:15px;"></i> Sell Second Hand Phone
                </a>
                @endif
                @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin'))
                <a href="{{ route('mobileshop.emi.ledger') }}" class="dropdown-item-link">
                    <i data-lucide="building-2" style="width:15px;height:15px;color:#2563EB;"></i> EMI Finance Ledger
                </a>
                <a href="{{ route('mobileshop.purchase') }}" class="dropdown-item-link">
                    <i data-lucide="file-text" style="width:15px;height:15px;"></i> Supplier Purchase & Ledger
                </a>
                @endif
                @if(auth()->user()->can('create-purchase-phones') || auth()->user()->can('manage-stock-phones') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin'))
                <a href="{{ route('mobileshop.purchase.create') }}" class="dropdown-item-link">
                    <i data-lucide="smartphone" style="width:15px;height:15px;"></i> Purchase New Phone
                </a>
                @endif
                @if(auth()->user()->can('create-purchase-secondhand') || auth()->user()->can('manage-stock-secondhand') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin'))
                <a href="{{ route('mobileshop.stock', ['tab' => 'second_hand']) }}" class="dropdown-item-link">
                    <i data-lucide="repeat" style="width:15px;height:15px;"></i> Purchase Second Hand Phone
                </a>
                @endif
                @if(auth()->user()->can('create-purchase-accessories') || auth()->user()->can('create-purchase-covers') || auth()->user()->can('manage-stock-accessories') || auth()->user()->can('manage-stock-covers') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin'))
                <a href="{{ route('mobileshop.accessories.purchase') }}" class="dropdown-item-link">
                    <i data-lucide="headphones" style="width:15px;height:15px;"></i> Purchase Accessories & Covers
                </a>
                @endif
                @if(auth()->user()->can('manage-stock-repairs') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin'))
                <a href="{{ route('mobileshop.repairs') }}" class="dropdown-item-link" style="border-top:1px solid var(--color-card-border); margin-top:4px; padding-top:8px;">
                    <i data-lucide="wrench" style="width:15px;height:15px;"></i> New Repair Job
                </a>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    /* ─── DROPDOWN MENU ITEMS ─── */
    #new-action-menu {
        display: none;
        position: absolute;
        right: 0;
        top: calc(100% + 8px);
        width: 240px;
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 14px;
        box-shadow: var(--shadow-dropdown);
        z-index: 150;
        padding: 6px;
        overflow: hidden;
        animation: fadeIn 0.18s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .dropdown-item-link {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 12px;
        font-size: 13px;
        font-weight: 600;
        color: var(--color-text-primary);
        text-decoration: none;
        border-radius: 8px;
        transition: all 0.15s ease;
    }
    .dropdown-item-link:hover {
        background: var(--color-primary-light);
        color: var(--color-primary);
        transform: translateX(2px);
    }

    @media (max-width: 600px) {
        #new-action-menu {
            right: 0 !important;
            left: auto !important;
            max-width: calc(100vw - 32px) !important;
        }
    }

    /* ─── 3-TIER DASHBOARD ARCHITECTURE ─── */
    .pulse-kpi-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 10px;
        margin-bottom: 12px;
    }
    @media (max-width: 1300px) {
        .pulse-kpi-grid { grid-template-columns: repeat(3, 1fr); }
    }
    @media (max-width: 768px) {
        .pulse-kpi-grid { grid-template-columns: repeat(2, 1fr); gap: 6px; }
    }

    .pulse-card {
        background: var(--color-canvas);
        border: 1px solid var(--color-hairline);
        border-radius: var(--radius-md);
        padding: 9px 12px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 82px;
        transition: all 0.15s ease;
    }
    .pulse-card:hover {
        border-color: var(--color-hairline-strong);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }
    .pulse-card-label {
        font-size: 10.5px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--color-ink-muted);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .pulse-card-val {
        font-size: 17.5px;
        font-weight: 700;
        font-family: var(--font-mono);
        color: var(--color-ink);
        line-height: 1.2;
        margin: 3px 0 2px 0;
    }
    .pulse-card-sub {
        font-size: 11px;
        color: var(--color-ink-subtle);
        display: flex;
        align-items: center;
        gap: 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* ─── MIDDLE SPLIT SECTION (7-DAY CHART + PAYMENT MIX) ─── */
    .dashboard-middle-grid {
        display: grid;
        grid-template-columns: 7fr 5fr;
        gap: 12px;
        margin-bottom: 12px;
    }
    @media (max-width: 1024px) {
        .dashboard-middle-grid { grid-template-columns: 1fr; }
    }

    /* ─── CHART CARD ─── */
    .chart-container-card {
        background: var(--color-canvas);
        border-radius: var(--radius-lg);
        border: 1px solid var(--color-hairline);
        padding: 12px 14px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.02);
    }
    .chart-header-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
        flex-wrap: wrap;
        gap: 8px;
    }
    .chart-main-title {
        font-size: 13.5px;
        font-weight: 700;
        color: var(--color-ink);
        letter-spacing: -0.2px;
    }
    .legend-item {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        font-weight: 600;
        color: var(--color-ink-muted);
    }
    .legend-circle {
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }

    /* ─── LOWER DATA TABLES & ACTION CENTER ─── */
    .dashboard-split-grid {
        display: grid;
        grid-template-columns: 7fr 5fr;
        gap: 12px;
        margin-bottom: 12px;
    }
    @media (max-width: 1024px) {
        .dashboard-split-grid { grid-template-columns: 1fr; }
    }

    .action-item-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 10px;
        background: var(--color-surface-1);
        border: 1px solid var(--color-hairline);
        border-radius: var(--radius-sm);
        gap: 8px;
    }
</style>
@endpush

@section('content')

    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- 1. TIER 1: THE DAILY CASH & PULSE STRIP (6 HERO KPIS)    -->
    <!-- ══════════════════════════════════════════════════════════ -->
    @if($isAdmin)
    <div class="pulse-kpi-grid">
        <!-- 1. Today's Sales & Net Profit -->
        <div class="pulse-card">
            <div class="pulse-card-label">
                <span>Today's Sales</span>
                <i data-lucide="trending-up" style="width:13px;height:13px;color:var(--color-primary);"></i>
            </div>
            <div class="pulse-card-val">
                ₹{{ number_format($analytics['todaySales'] ?? 0, 2) }}
            </div>
            <div class="pulse-card-sub" style="color:#16a34a; font-weight:600;">
                <i data-lucide="arrow-up-right" style="width:12px;height:12px;"></i>
                <span>+₹{{ number_format($analytics['todayProfit'] ?? 0, 2) }} ({{ $analytics['todayProfitMargin'] ?? 0 }}%)</span>
            </div>
        </div>

        <!-- 2. Cash in Drawer (Physical Register Cash) -->
        <div class="pulse-card">
            <div class="pulse-card-label">
                <span>Cash in Drawer</span>
                <i data-lucide="wallet" style="width:13px;height:13px;color:#16a34a;"></i>
            </div>
            <div class="pulse-card-val">
                ₹{{ number_format($analytics['cashInDrawer'] ?? 0, 2) }}
            </div>
            <div class="pulse-card-sub">
                <span class="badge badge-green" style="font-size:9.5px;padding:1px 5px;">Physical Counter Cash</span>
            </div>
        </div>

        <!-- 3. UPI In-Flow -->
        <div class="pulse-card">
            <div class="pulse-card-label">
                <span>UPI In-Flow</span>
                <i data-lucide="qr-code" style="width:13px;height:13px;color:#2563eb;"></i>
            </div>
            <div class="pulse-card-val">
                ₹{{ number_format($analytics['todayUpi'] ?? 0, 2) }}
            </div>
            <div class="pulse-card-sub">
                <span>{{ $analytics['todayUpiCount'] ?? 0 }} digital txns today</span>
            </div>
        </div>

        <!-- 4. Month-to-Date (MTD) Sales -->
        <div class="pulse-card">
            <div class="pulse-card-label">
                <span>MTD Sales ({{ date('M') }})</span>
                <i data-lucide="calendar" style="width:13px;height:13px;color:#7c3aed;"></i>
            </div>
            <div class="pulse-card-val">
                ₹{{ number_format($analytics['monthSales'] ?? 0, 2) }}
            </div>
            <div class="pulse-card-sub">
                @if(($analytics['monthGrowthPct'] ?? 0) >= 0)
                    <span style="color:#16a34a; font-weight:600;">↑ {{ $analytics['monthGrowthPct'] ?? 0 }}% vs last mo</span>
                @else
                    <span style="color:#dc2626; font-weight:600;">↓ {{ abs($analytics['monthGrowthPct'] ?? 0) }}% vs last mo</span>
                @endif
            </div>
        </div>

        <!-- 5. Customer Khata / Udhari -->
        <div class="pulse-card">
            <div class="pulse-card-label">
                <span>Khata / Udhari</span>
                <i data-lucide="book-open" style="width:13px;height:13px;color:#ea580c;"></i>
            </div>
            <div class="pulse-card-val" style="color:#b91c1c;">
                ₹{{ number_format($analytics['totalUdhariDue'] ?? 0, 2) }}
            </div>
            <div class="pulse-card-sub">
                <a href="{{ route('mobileshop.khata') }}" style="color:var(--color-ink-muted); text-decoration:underline;">
                    {{ $analytics['totalUdhariCount'] ?? 0 }} customers owing
                </a>
            </div>
        </div>

        <!-- 6. Total Supplier Debt -->
        <div class="pulse-card">
            <div class="pulse-card-label">
                <span>Supplier Debt</span>
                <i data-lucide="truck" style="width:13px;height:13px;color:#475569;"></i>
            </div>
            <div class="pulse-card-val" style="color:#d97706;">
                ₹{{ number_format($analytics['totalSupplierDebt'] ?? 0, 2) }}
            </div>
            <div class="pulse-card-sub">
                <a href="{{ route('mobileshop.purchase') }}" style="color:var(--color-ink-muted); text-decoration:underline;">
                    {{ $analytics['unpaidPoCount'] ?? 0 }} pending invoices
                </a>
            </div>
        </div>
    </div>
    @else
    <!-- Niche-scoped 4 KPIs for Staff / Specialized Roles -->
    <div class="kpi-row">
        <div class="kpi-card">
            <div class="kpi-top">
                <div class="kpi-num">{{ number_format($statPurchaseInvoices ?? 0) }}</div>
            </div>
            <div>
                <div class="kpi-label">Stock Intakes</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-top">
                <div class="kpi-num">{{ number_format($statBuybackReturns ?? 0) }}</div>
            </div>
            <div>
                <div class="kpi-label">Returns / Buybacks</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-top">
                <div class="kpi-num">{{ number_format($statSaleInvoices ?? 0) }}</div>
            </div>
            <div>
                <div class="kpi-label">Sales Invoices</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-top">
                <div class="kpi-num">{{ number_format($statPendingUdhari ?? 0) }}</div>
            </div>
            <div>
                <div class="kpi-label">Pending Khata</div>
            </div>
        </div>
    </div>
    @endif

    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- 2. TIER 2: 7-DAY MOMENTUM & PAYMENT BREAKDOWN            -->
    <!-- ══════════════════════════════════════════════════════════ -->
    <div class="dashboard-middle-grid">
        
        <!-- Left: 7-Day Revenue & Profit Spline Chart -->
        <div class="chart-container-card">
            <div class="chart-header-row">
                <div>
                    <div class="chart-main-title">7-Day Revenue & Profit Momentum</div>
                    <div style="font-size:11px; color:var(--color-ink-subtle); margin-top:2px;">Real-time margin pulse over the last 7 trading days</div>
                </div>
                <div style="display:flex; align-items:center; gap:10px;">
                    <div class="legend-item">
                        <span class="legend-circle" style="background:#5E6AD2;"></span>
                        <span>Sales (₹)</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-circle" style="background:#16A34A;"></span>
                        <span>Profit (₹)</span>
                    </div>
                </div>
            </div>
            <div style="height: 195px; width: 100%; position: relative;">
                <canvas id="performanceSplineChart"></canvas>
            </div>
        </div>

        <!-- Right: Today's Payment Mode Mix & EOD Reconcile -->
        <div class="card" style="margin-bottom:0; display:flex; flex-direction:column; justify-content:space-between;">
            <div class="card-header" style="background:var(--color-surface-1); border-bottom:1px solid var(--color-hairline);">
                <div class="card-title" style="font-size:13px; font-weight:700;">
                    <i data-lucide="pie-chart" style="width:14px;height:14px;color:var(--color-primary);"></i>
                    <span>Payment Mode Breakdown</span>
                </div>
                <span class="badge {{ ($analytics['isTodayPaymentMode'] ?? false) ? 'badge-green' : 'badge-gray' }}">
                    {{ ($analytics['isTodayPaymentMode'] ?? false) ? "Today's Mix" : "All Time Mix" }}
                </span>
            </div>
            <div class="card-body" style="padding:10px 12px; display:flex; flex-direction:column; gap:8px;">
                @php
                    $payModes = $analytics['paymentModes'] ?? collect();
                    $payTotal = (float) $payModes->sum('total_amount');
                @endphp
                @forelse($payModes as $pm)
                    @php
                        $pct = $payTotal > 0 ? round(($pm->total_amount / $payTotal) * 100) : 0;
                        $modeColor = match(strtolower($pm->payment_mode)) {
                            'cash'    => '#16A34A',
                            'upi', 'gpay', 'phonepe', 'paytm', 'online' => '#2563EB',
                            'card'    => '#7C3AED',
                            'udhari', 'credit' => '#E11D48',
                            default   => '#4F535B'
                        };
                        $modeIcon = match(strtolower($pm->payment_mode)) {
                            'cash'    => 'banknote',
                            'upi', 'gpay', 'phonepe', 'paytm', 'online' => 'qr-code',
                            'card'    => 'credit-card',
                            'udhari', 'credit' => 'user-x',
                            default   => 'circle-dot'
                        };
                    @endphp
                    <div>
                        <div style="display:flex; justify-content:space-between; align-items:center; font-size:12px; margin-bottom:3px;">
                            <span style="font-weight:600; text-transform:capitalize; display:flex; align-items:center; gap:5px; color:var(--color-ink);">
                                <i data-lucide="{{ $modeIcon }}" style="width:13px;height:13px;color:{{ $modeColor }};"></i>
                                {{ str_replace('_', ' ', $pm->payment_mode) }}
                            </span>
                            <span style="font-family:var(--font-mono); font-weight:700; color:var(--color-ink); font-size:12px;">
                                ₹{{ number_format($pm->total_amount, 2) }}
                                <span style="font-size:10px; font-weight:500; color:var(--color-ink-subtle);">({{ $pct }}%)</span>
                            </span>
                        </div>
                        <div style="width:100%; height:5px; background:var(--color-surface-2); border-radius:3px; overflow:hidden;">
                            <div style="width:{{ $pct }}%; height:100%; background:{{ $modeColor }}; border-radius:3px;"></div>
                        </div>
                    </div>
                @empty
                    <div style="text-align:center; padding:18px; color:var(--color-ink-subtle); font-size:12px;">
                        No payment transactions recorded yet.
                    </div>
                @endforelse
            </div>
            <div class="card-footer" style="padding:6px 12px; font-size:11px; color:var(--color-ink-muted); background:var(--color-surface-1);">
                <span>Total Reconciled: <strong style="color:var(--color-ink);">₹{{ number_format($payTotal, 2) }}</strong></span>
                <a href="{{ route('mobileshop.sales') }}" style="font-size:11px; font-weight:600; color:var(--color-primary);">View Register →</a>
            </div>
        </div>

    </div>

    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- 3. TIER 3: LIVE OPERATIONS & CASH COLLECTION CENTER      -->
    <!-- ══════════════════════════════════════════════════════════ -->
    <div class="dashboard-split-grid">
        
        <!-- Left: Recent Sales Invoices -->
        <div class="card" style="margin-bottom:0;">
            <div class="card-header" style="background:var(--color-surface-1); border-bottom:1px solid var(--color-hairline);">
                <div class="card-title" style="font-size:13.5px; font-weight:700;">
                    <i data-lucide="receipt" style="width:14px;height:14px;color:var(--color-primary);"></i>
                    <span>Recent Sales Invoices</span>
                </div>
                <a href="{{ route('mobileshop.sales.create') }}" class="btn btn-primary btn-xs">+ Sell New Phone</a>
            </div>
            <div class="card-body" style="padding:0; overflow-x:auto;">
                <table class="data-table" style="margin:0; border:none;">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Customer</th>
                            <th>Item Details</th>
                            <th style="text-align:right;">Amount (₹)</th>
                            <th style="text-align:center;">Mode</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentSales as $sale)
                        <tr>
                            <td>
                                <a href="{{ route('mobileshop.invoice', ['id' => $sale->id]) }}" style="font-family:var(--font-mono); font-weight:700; color:var(--color-primary); text-decoration:none;">
                                    {{ $sale->invoice_number }}
                                </a>
                            </td>
                            <td>
                                <div style="font-weight:600; color:var(--color-ink); font-size:12.5px;">{{ $sale->customer_name }}</div>
                                <div style="font-size:10.5px; color:var(--color-ink-muted); font-family:var(--font-mono);">{{ $sale->customer_phone }}</div>
                            </td>
                            <td>
                                <div style="font-weight:600; font-size:12px;">{{ $sale->brand }} {{ $sale->model }}</div>
                                <div style="font-size:10px; color:var(--color-ink-muted); font-family:var(--font-mono);">IMEI: {{ $sale->imei_1 }}</div>
                            </td>
                            <td style="text-align:right; font-weight:700; font-family:var(--font-mono); color:var(--color-ink);">
                                ₹{{ number_format($sale->total_amount, 2) }}
                            </td>
                            <td style="text-align:center;">
                                <span class="badge badge-green" style="text-transform:capitalize;">
                                    {{ $sale->payment_mode ?? 'Paid' }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="text-align:center; padding:24px; color:var(--color-ink-muted); font-size:12px;">No sales recorded today yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer" style="padding:6px 12px; font-size:11px; background:var(--color-surface-1);">
                <a href="{{ route('mobileshop.sales') }}" style="font-weight:600; color:var(--color-primary); margin-left:auto;">View All Sales Register →</a>
            </div>
        </div>

        <!-- Right: Action Center (Ready for Pickup + Overdue Debtors) -->
        <div style="display:flex; flex-direction:column; gap:12px;">
            
            <!-- [A] Ready for Delivery (Collectable Counter Cash) -->
            <div class="card" style="margin-bottom:0;">
                <div class="card-header" style="background:var(--color-surface-1); border-bottom:1px solid var(--color-hairline);">
                    <div class="card-title" style="font-size:13px; font-weight:700; display:flex; align-items:center; gap:6px;">
                        <i data-lucide="check-circle-2" style="width:14px;height:14px;color:#16A34A;"></i>
                        <span>Ready for Pickup</span>
                    </div>
                    @if(($analytics['readyRepairsCollectable'] ?? 0) > 0)
                        <span class="badge badge-green" style="font-family:var(--font-mono);">
                            ₹{{ number_format($analytics['readyRepairsCollectable'], 2) }} Collectable
                        </span>
                    @endif
                </div>
                <div class="card-body" style="padding:10px; display:flex; flex-direction:column; gap:6px;">
                    @php $readyList = $analytics['readyRepairs'] ?? collect(); @endphp
                    @forelse($readyList as $rep)
                    <div class="action-item-row">
                        <div>
                            <div style="font-weight:700; font-size:12px; color:var(--color-ink);">
                                {{ $rep->ticket_number }} — {{ $rep->brand }} {{ $rep->model }}
                            </div>
                            <div style="font-size:10.5px; color:var(--color-ink-muted);">
                                {{ $rep->customer_name }} · <span style="font-weight:700; color:#16a34a; font-family:var(--font-mono);">₹{{ number_format($rep->total_amount, 2) }}</span>
                            </div>
                        </div>
                        @php
                            $sName = store_name();
                            $sPhone = store_phone();
                            $sAddress = store_address();
                            $rNotifyMsg = "🔧 *DEVICE READY FOR PICKUP*\n";
                            $rNotifyMsg .= "🏪 *{$sName} Service Lab*\n";
                            $rNotifyMsg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
                            $rNotifyMsg .= "Dear *{$rep->customer_name}*,\n\n";
                            $rNotifyMsg .= "Great news! Your *{$rep->brand} {$rep->model}* has been successfully serviced and passed quality inspection.\n\n";
                            $rNotifyMsg .= "📋 *Job Ticket #:* {$rep->ticket_number}\n";
                            $rNotifyMsg .= "💰 *Service Amount:* *₹" . number_format($rep->total_amount, 2) . "*\n\n";
                            $rNotifyMsg .= "📍 *Pickup Location:* {$sAddress}\n";
                            $rNotifyMsg .= "🕒 *Store Hours:* 10:00 AM – 9:30 PM (Daily)\n";
                            $rNotifyMsg .= "📞 *Helpdesk:* {$sPhone}\n";
                            $rNotifyMsg .= "_Please show this message at our counter to collect your device._";
                        @endphp
                        <a href="https://wa.me/91{{ preg_replace('/[^0-9]/', '', $rep->customer_phone) }}?text={{ rawurlencode($rNotifyMsg) }}" 
                           target="_blank"
                           class="btn btn-sm btn-outline"
                           style="color:#16a34a; border-color:#86efac; gap:4px; font-size:11px;"
                           title="Notify Customer via WhatsApp">
                            <i data-lucide="message-circle" style="width:12px;height:12px;"></i>
                            <span>Notify</span>
                        </a>
                    </div>
                    @empty
                    <div style="text-align:center; padding:12px; color:var(--color-ink-subtle); font-size:11.5px;">
                        No repaired devices currently waiting for pickup.
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- [B] High-Risk Overdue Debtors (₹5,000+) -->
            <div class="card" style="margin-bottom:0;">
                <div class="card-header" style="background:var(--color-surface-1); border-bottom:1px solid var(--color-hairline);">
                    <div class="card-title" style="font-size:13px; font-weight:700; display:flex; align-items:center; gap:6px;">
                        <i data-lucide="alert-circle" style="width:14px;height:14px;color:#E11D48;"></i>
                        <span>High-Risk Debtors (₹5,000+)</span>
                    </div>
                    <a href="{{ route('mobileshop.khata') }}" class="btn btn-outline btn-xs">Full Khata</a>
                </div>
                <div class="card-body" style="padding:10px; display:flex; flex-direction:column; gap:6px;">
                    @php $debtorList = $analytics['highRiskDebtors'] ?? collect(); @endphp
                    @forelse($debtorList as $deb)
                    <div class="action-item-row">
                        <div>
                            <div style="font-weight:700; font-size:12px; color:var(--color-ink);">
                                {{ $deb->name }}
                            </div>
                            <div style="font-size:10.5px; color:#b91c1c; font-weight:700; font-family:var(--font-mono);">
                                Due: ₹{{ number_format($deb->udhari_balance, 2) }}
                            </div>
                        </div>
                        @php
                            $sName = store_name();
                            $sPhone = store_phone();
                            $sAddress = store_address();
                            $sUpi = store_upi_id();
                            $sLandline = store_landline();
                            $debMsg = "🔔 *PAYMENT REMINDER*\n";
                            $debMsg .= "🏪 *{$sName}*\n";
                            $debMsg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
                            $debMsg .= "Dear *{$deb->name}*,\n\n";
                            $debMsg .= "Greetings from *{$sName}*!\n\n";
                            $debMsg .= "This is a polite reminder regarding your pending store credit balance:\n";
                            $debMsg .= "📌 *Outstanding Balance Due:* *₹" . number_format($deb->udhari_balance, 2) . "*\n\n";
                            if (!empty($sUpi)) {
                                $debMsg .= "Kindly arrange to clear this balance via UPI (`{$sUpi}`) or Cash at our store counter.\n";
                            } else {
                                $debMsg .= "Kindly arrange to clear this balance via UPI or Cash at our store counter.\n";
                            }
                            $debMsg .= "📞 *Accounts Desk:* {$sPhone}\n";
                            if (!empty($sLandline)) {
                                $debMsg .= "☎️ *Landline:* {$sLandline}\n";
                            }
                            $debMsg .= "🏢 *Showroom:* {$sAddress}\n";
                            $debMsg .= "_If already settled recently, please disregard this message. Thank you!_";
                        @endphp
                        <a href="https://wa.me/91{{ preg_replace('/[^0-9]/', '', $deb->phone) }}?text={{ rawurlencode($debMsg) }}" 
                           target="_blank"
                           class="btn btn-sm btn-outline"
                           style="color:#e11d48; border-color:#fca5a5; gap:4px; font-size:11px;"
                           title="Send WhatsApp Payment Reminder">
                            <i data-lucide="bell" style="width:12px;height:12px;"></i>
                            <span>Remind</span>
                        </a>
                    </div>
                    @empty
                    <div style="text-align:center; padding:12px; color:var(--color-ink-subtle); font-size:11.5px;">
                        All customer credit accounts are healthy (none above ₹5,000).
                    </div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- 4. URGENT LOW STOCK ALERT STRIP (CONDITIONAL)            -->
    <!-- ══════════════════════════════════════════════════════════ -->
    @if(count($lowStockParts) > 0)
    <div class="card" style="margin-bottom:0; border-color:#FECDD3; background:#FFF1F2;">
        <div class="card-header" style="background:transparent; border-bottom:1px solid #FFE4E6; padding:8px 12px;">
            <div class="card-title" style="font-size:12.5px; font-weight:700; color:#BE123C; display:flex; align-items:center; gap:6px;">
                <i data-lucide="alert-triangle" style="width:15px;height:15px;"></i>
                <span>Low Stock Warning ({{ count($lowStockParts) }} Parts below minimum alert level)</span>
            </div>
            <a href="{{ route('mobileshop.purchase') }}" class="btn btn-xs" style="background:#fff; color:#BE123C; border:1px solid #FDA4AF; font-weight:600;">
                + Restock Purchase Order
            </a>
        </div>
        <div class="card-body" style="padding:8px 12px;">
            <div style="display:flex; flex-wrap:wrap; gap:6px;">
                @foreach($lowStockParts->take(8) as $lsp)
                <span style="font-size:11px; font-weight:600; background:#fff; border:1px solid #FECDD3; padding:2px 8px; border-radius:4px; color:#9F1239; display:inline-flex; align-items:center; gap:4px;">
                    <span>{{ $lsp->name }}</span>
                    <strong style="color:#e11d48;">({{ $lsp->stock_qty }} left)</strong>
                </span>
                @endforeach
            </div>
        </div>
    </div>
    @endif

@endsection

@push('scripts')
<!-- Chart.js (with error boundary) -->
<script>
    window.__chartFallback = function () {
        var canvas = document.getElementById('performanceSplineChart');
        if (!canvas) return;
        var ctx = canvas.getContext('2d');
        if (!ctx) return;
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.fillStyle = '#F1F5F9';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.fillStyle = '#94A3B8';
        ctx.font = '14px Inter, sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText('Chart loading...', canvas.width / 2, canvas.height / 2);
    };
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js" onerror="window.__chartFallback();"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const trendData = @json($analytics['sevenDayTrend'] ?? []);
        const canvas = document.getElementById('performanceSplineChart');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');

        // Extract 7-day trend arrays
        let labels = [];
        let salesData = [];
        let profitData = [];

        if (trendData && trendData.length > 0) {
            labels     = trendData.map(d => d.short_label || d.label);
            salesData  = trendData.map(d => Number(d.sales || 0));
            profitData = trendData.map(d => Number(d.profit || 0));
        } else {
            // Default demo curve
            labels     = ['Day 1', 'Day 2', 'Day 3', 'Day 4', 'Day 5', 'Day 6', 'Today'];
            salesData  = [12400, 18500, 14200, 26000, 21500, 31000, 24500];
            profitData = [ 2100,  3200,  2400,  4800,  3600,  5500,  4200];
        }

        // Check if all zero to provide aesthetic demo trend
        if (salesData.reduce((a,b) => a+b, 0) === 0) {
            salesData  = [12400, 18500, 14200, 26000, 21500, 31000, 24500];
            profitData = [ 2100,  3200,  2400,  4800,  3600,  5500,  4200];
        }

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Sales (₹)',
                        data: salesData,
                        borderColor: '#5E6AD2',
                        backgroundColor: 'rgba(94, 106, 210, 0.08)',
                        borderWidth: 2.5,
                        tension: 0.35,
                        pointRadius: 3.5,
                        pointBackgroundColor: '#5E6AD2',
                        pointHoverRadius: 5,
                        fill: true
                    },
                    {
                        label: 'Profit (₹)',
                        data: profitData,
                        borderColor: '#16A34A',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        tension: 0.35,
                        pointRadius: 3,
                        pointBackgroundColor: '#16A34A',
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(15, 23, 42, 0.92)',
                        titleFont: { weight: 'bold', size: 12 },
                        bodyFont: { size: 11.5 },
                        padding: 10,
                        cornerRadius: 6,
                        callbacks: {
                            label: function (context) {
                                return context.dataset.label + ': ₹' + Number(context.raw).toLocaleString('en-IN');
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#64748B',
                            font: { weight: '500', size: 11 }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#F1F5F9'
                        },
                        ticks: {
                            color: '#64748B',
                            font: { size: 10.5 },
                            callback: function (val) {
                                return '₹' + (val >= 1000 ? (val / 1000).toFixed(0) + 'k' : val);
                            }
                        }
                    }
                }
            }
        });

        // + New action dropdown toggle
        const newBtn = document.getElementById('new-action-btn');
        const newMenu = document.getElementById('new-action-menu');
        if (newBtn && newMenu) {
            newBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                newMenu.style.display = (newMenu.style.display === 'none' || !newMenu.style.display) ? 'block' : 'none';
            });
            document.addEventListener('click', function () {
                newMenu.style.display = 'none';
            });
        }
    });
</script>
@endpush
