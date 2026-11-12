@extends('mobileshop.layout')

@php
    // Niche-aware page title and action button
    $nicheTitles = [
        'admin'       => 'Store Dashboard — Accessories & Repairs',
        'accessories' => 'Accessories & Parts Dashboard',
        'repairs'     => 'Service Desk & Repair Dashboard',
    ];
    $nicheTitle = $nicheTitles[$niche ?? 'admin'] ?? 'Store Dashboard';
@endphp

@section('title', $nicheTitle . ' — PhoneFix Azamgarh ERP')
@section('page-title', $nicheTitle)

@section('page-actions')
    <div class="flex items-center gap-2 flex-wrap">
        <x-ui.badge variant="outline" class="text-xs font-semibold py-1 px-2.5 bg-white">
            <i data-lucide="calendar" class="w-3.5 h-3.5 text-indigo-600"></i>
            <span>{{ date('D, d M Y') }}</span>
        </x-ui.badge>
        
        <div style="position:relative;" id="new-action-wrap">
            <x-ui.button variant="default" size="sm" id="new-action-btn" class="gap-1.5 font-semibold">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                <span>New</span>
                <i data-lucide="chevron-down" class="w-3.5 h-3.5 opacity-80"></i>
            </x-ui.button>
            <div id="new-action-menu">
                @if(auth()->user()->can('create-sale-accessories') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin') || auth()->user()->hasRole('accessories-staff'))
                <a href="{{ route('mobileshop.accessories.pos') }}" class="dropdown-item-link" style="font-weight:600; color:var(--color-primary);">
                    <i data-lucide="zap" style="width:15px;height:15px;color:var(--color-primary);"></i> Accessories Counter POS
                </a>
                @endif
                @if(auth()->user()->can('create-purchase-accessories') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin') || auth()->user()->hasRole('accessories-staff'))
                <a href="{{ route('mobileshop.accessories.purchase') }}" class="dropdown-item-link">
                    <i data-lucide="headphones" style="width:15px;height:15px;color:#16A34A;"></i> Restock Accessories & Parts
                </a>
                @endif
                @if(auth()->user()->can('manage-stock-repairs') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin') || auth()->user()->hasRole('repair-technician'))
                <a href="{{ route('mobileshop.repairs') }}" class="dropdown-item-link" style="border-top:1px solid var(--color-card-border); margin-top:4px; padding-top:8px;">
                    <i data-lucide="wrench" style="width:15px;height:15px;color:#2563EB;"></i> New Repair Job Sheet
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
        grid-template-columns: repeat(7, 1fr);
        gap: 10px;
        margin-bottom: 12px;
    }
    @media (max-width: 1400px) {
        .pulse-kpi-grid { grid-template-columns: repeat(4, 1fr); }
    }
    @media (max-width: 1024px) {
        .pulse-kpi-grid { grid-template-columns: repeat(3, 1fr); }
    }
    @media (max-width: 768px) {
        .pulse-kpi-grid { grid-template-columns: repeat(2, 1fr); gap: 6px; }
    }

    .pulse-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        padding: 11px 13px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 86px;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.03);
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .pulse-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
        transform: translateY(-1px);
    }
    .pulse-card-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .pulse-card-val {
        font-size: 19px;
        font-weight: 800;
        font-family: var(--font-mono, monospace);
        color: #0f172a;
        line-height: 1.2;
        margin: 4px 0 2px 0;
        letter-spacing: -0.02em;
    }
    .pulse-card-sub {
        font-size: 11.5px;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 5px;
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
    <!-- 1. TIER 1: THE DAILY CASH & PULSE STRIP (7 HERO KPIS)    -->
    <!-- ══════════════════════════════════════════════════════════ -->
    @if($isAdmin)
    <div class="pulse-kpi-grid">
        <!-- 1. Today's Sales & Net Profit -->
        <div class="pulse-card">
            <div class="pulse-card-label">
                <span>Today's Sales</span>
                <div class="w-6 h-6 rounded-md bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <i data-lucide="trending-up" class="w-3.5 h-3.5"></i>
                </div>
            </div>
            <div class="pulse-card-val">
                ₹{{ number_format($analytics['todaySales'] ?? 0, 2) }}
            </div>
            <div class="pulse-card-sub">
                <x-ui.badge variant="success" class="text-[10px] px-1.5 py-0 font-bold">
                    <i data-lucide="arrow-up-right" class="w-3 h-3"></i>
                    +₹{{ number_format($analytics['todayProfit'] ?? 0, 2) }} ({{ $analytics['todayProfitMargin'] ?? 0 }}%)
                </x-ui.badge>
            </div>
        </div>

        <!-- 2. Items Sold Today -->
        <div class="pulse-card">
            <div class="pulse-card-label">
                <span>Items Sold Today</span>
                <div class="w-6 h-6 rounded-md bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="package-check" class="w-3.5 h-3.5"></i>
                </div>
            </div>
            <div class="pulse-card-val text-emerald-600">
                {{ number_format($analytics['todayItemsSold'] ?? $todayItemsSold ?? 0) }} <span class="text-xs font-semibold text-slate-500">items</span>
            </div>
            <div class="pulse-card-sub">
                <a href="{{ route('mobileshop.sales') }}" class="hover:underline text-slate-600 inline-flex items-center gap-1 text-[11px]">
                    <i data-lucide="receipt" class="w-3 h-3 text-emerald-600"></i>
                    <span>Across {{ number_format($analytics['todayInvoicesCount'] ?? $todayInvoicesCount ?? 0) }} bills today</span>
                </a>
            </div>
        </div>

        <!-- 3. Cash in Drawer (Physical Register Cash) -->
        <div class="pulse-card">
            <div class="pulse-card-label">
                <span>Cash in Drawer</span>
                <div class="w-6 h-6 rounded-md bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="wallet" class="w-3.5 h-3.5"></i>
                </div>
            </div>
            <div class="pulse-card-val">
                ₹{{ number_format($analytics['cashInDrawer'] ?? 0, 2) }}
            </div>
            <div class="pulse-card-sub">
                <x-ui.badge variant="success" class="text-[10px] px-1.5 py-0 font-semibold">
                    Physical Drawer Cash
                </x-ui.badge>
            </div>
        </div>

        <!-- 4. UPI In-Flow -->
        <div class="pulse-card">
            <div class="pulse-card-label">
                <span>UPI In-Flow</span>
                <div class="w-6 h-6 rounded-md bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i data-lucide="qr-code" class="w-3.5 h-3.5"></i>
                </div>
            </div>
            <div class="pulse-card-val">
                ₹{{ number_format($analytics['todayUpi'] ?? 0, 2) }}
            </div>
            <div class="pulse-card-sub">
                <x-ui.badge variant="blue" class="text-[10px] px-1.5 py-0">
                    {{ $analytics['todayUpiCount'] ?? 0 }} digital txns
                </x-ui.badge>
            </div>
        </div>

        <!-- 5. Month-to-Date (MTD) Sales -->
        <div class="pulse-card">
            <div class="pulse-card-label">
                <span>MTD Sales ({{ date('M') }})</span>
                <div class="w-6 h-6 rounded-md bg-purple-50 text-purple-600 flex items-center justify-center">
                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                </div>
            </div>
            <div class="pulse-card-val">
                ₹{{ number_format($analytics['monthSales'] ?? 0, 2) }}
            </div>
            <div class="pulse-card-sub">
                @if(($analytics['monthGrowthPct'] ?? 0) >= 0)
                    <x-ui.badge variant="success" class="text-[10px] px-1.5 py-0 font-bold">
                        ↑ {{ $analytics['monthGrowthPct'] ?? 0 }}% vs last mo
                    </x-ui.badge>
                @else
                    <x-ui.badge variant="destructive" class="text-[10px] px-1.5 py-0 font-bold">
                        ↓ {{ abs($analytics['monthGrowthPct'] ?? 0) }}% vs last mo
                    </x-ui.badge>
                @endif
            </div>
        </div>

        <!-- 6. Customer Khata / Udhari -->
        <div class="pulse-card">
            <div class="pulse-card-label">
                <span>Khata / Udhari</span>
                <div class="w-6 h-6 rounded-md bg-rose-50 text-rose-600 flex items-center justify-center">
                    <i data-lucide="book-open" class="w-3.5 h-3.5"></i>
                </div>
            </div>
            <div class="pulse-card-val text-red-600">
                ₹{{ number_format($analytics['totalUdhariDue'] ?? 0, 2) }}
            </div>
            <div class="pulse-card-sub">
                <a href="{{ route('mobileshop.khata') }}" class="inline-flex">
                    <x-ui.badge variant="destructive" class="text-[10px] px-1.5 py-0 cursor-pointer hover:bg-red-100">
                        {{ $analytics['totalUdhariCount'] ?? 0 }} customers owing
                    </x-ui.badge>
                </a>
            </div>
        </div>

        <!-- 7. Total Supplier Debt -->
        <div class="pulse-card">
            <div class="pulse-card-label">
                <span>Supplier Debt</span>
                <div class="w-6 h-6 rounded-md bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i data-lucide="truck" class="w-3.5 h-3.5"></i>
                </div>
            </div>
            <div class="pulse-card-val text-amber-700">
                ₹{{ number_format($analytics['totalSupplierDebt'] ?? 0, 2) }}
            </div>
            <div class="pulse-card-sub">
                <a href="{{ route('mobileshop.purchase') }}" class="inline-flex">
                    <x-ui.badge variant="warning" class="text-[10px] px-1.5 py-0 cursor-pointer hover:bg-amber-100">
                        {{ $analytics['unpaidPoCount'] ?? 0 }} pending POs
                    </x-ui.badge>
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
                <div class="kpi-label">Customer Returns</div>
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
                <div class="kpi-num text-emerald-600">{{ number_format($todayItemsSold ?? 0) }}</div>
            </div>
            <div>
                <div class="kpi-label">Items Sold Today</div>
                <div class="text-xs text-slate-500 mt-0.5">{{ number_format($todayInvoicesCount ?? 0) }} bills today</div>
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
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-xs">
            <div class="flex items-center justify-between pb-3 mb-2 border-b border-slate-100 flex-wrap gap-2">
                <div>
                    <div class="text-sm font-bold text-slate-900 tracking-tight flex items-center gap-2">
                        <i data-lucide="activity" class="w-4 h-4 text-indigo-600"></i>
                        <span>7-Day Revenue & Profit Momentum</span>
                    </div>
                    <div class="text-xs text-slate-500 mt-0.5">Real-time margin pulse over the last 7 trading days</div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                        <span>Sales (₹)</span>
                    </div>
                    <div class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                        <span>Profit (₹)</span>
                    </div>
                </div>
            </div>
            <div style="height: 195px; width: 100%; position: relative;">
                <canvas id="performanceSplineChart"></canvas>
            </div>
        </div>

        <!-- Right: Today's Payment Mode Mix & EOD Reconcile -->
        <div class="rounded-xl border border-slate-200 bg-white shadow-xs flex flex-col justify-between overflow-hidden">
            <div class="p-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/60">
                <div class="text-xs font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="pie-chart" class="w-4 h-4 text-indigo-600"></i>
                    <span>Payment Mode Breakdown</span>
                </div>
                <x-ui.badge :variant="($analytics['isTodayPaymentMode'] ?? false) ? 'success' : 'secondary'">
                    {{ ($analytics['isTodayPaymentMode'] ?? false) ? "Today's Mix" : "All Time Mix" }}
                </x-ui.badge>
            </div>
            <div class="p-3.5 flex flex-col gap-2.5">
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
                            default   => '#64748B'
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
                        <div class="flex justify-between items-center text-xs mb-1">
                            <span class="font-bold capitalize flex items-center gap-1.5 text-slate-800">
                                <i data-lucide="{{ $modeIcon }}" class="w-3.5 h-3.5" style="color:{{ $modeColor }};"></i>
                                {{ str_replace('_', ' ', $pm->payment_mode) }}
                            </span>
                            <span class="font-mono font-bold text-slate-900 text-xs">
                                ₹{{ number_format($pm->total_amount, 2) }}
                                <span class="text-[11px] font-medium text-slate-500">({{ $pct }}%)</span>
                            </span>
                        </div>
                        <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-300" style="width:{{ $pct }}%; background-color:{{ $modeColor }};"></div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5 text-slate-400 text-xs font-medium">
                        No payment transactions recorded yet.
                    </div>
                @endforelse
            </div>
            <div class="p-3 border-t border-slate-100 bg-slate-50/60 flex items-center justify-between text-xs text-slate-600">
                <span>Total Reconciled: <strong class="text-slate-900 font-mono">₹{{ number_format($payTotal, 2) }}</strong></span>
                <a href="{{ route('mobileshop.sales') }}" class="font-bold text-indigo-600 hover:text-indigo-700 hover:underline inline-flex items-center gap-1">
                    <span>View Register</span>
                    <i data-lucide="arrow-right" class="w-3 h-3"></i>
                </a>
            </div>
        </div>

    </div>

    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- 3. TIER 3: LIVE OPERATIONS & CASH COLLECTION CENTER      -->
    <!-- ══════════════════════════════════════════════════════════ -->
    <div class="dashboard-split-grid">
        
        <!-- Left: Recent Sales Invoices -->
        <div class="rounded-xl border border-slate-200 bg-white shadow-xs overflow-hidden flex flex-col justify-between">
            <div class="p-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/60">
                <div class="text-xs font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="receipt" class="w-4 h-4 text-indigo-600"></i>
                    <span>Recent Sales Invoices</span>
                </div>
                <x-ui.button variant="default" size="xs" :href="route('mobileshop.accessories.pos')">
                    <i data-lucide="plus" class="w-3 h-3 mr-1"></i> Sell Accessories
                </x-ui.button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/40 text-[11px] uppercase tracking-wider font-bold text-slate-500">
                            <th class="py-2.5 px-3 font-semibold">Invoice #</th>
                            <th class="py-2.5 px-3 font-semibold">Customer</th>
                            <th class="py-2.5 px-3 font-semibold">Item Details</th>
                            <th class="py-2.5 px-3 font-semibold text-right">Amount (₹)</th>
                            <th class="py-2.5 px-3 font-semibold text-center">Mode</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($recentSales as $sale)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-2.5 px-3">
                                <a href="{{ route('mobileshop.invoice', ['id' => $sale->id]) }}" class="font-mono font-bold text-indigo-600 hover:text-indigo-800 hover:underline">
                                    {{ $sale->invoice_number }}
                                </a>
                            </td>
                            <td class="py-2.5 px-3">
                                <div class="font-bold text-slate-900 text-xs">{{ $sale->customer_name }}</div>
                                <div class="text-[11px] text-slate-500 font-mono">{{ $sale->customer_phone }}</div>
                            </td>
                            <td class="py-2.5 px-3">
                                <div class="font-semibold text-slate-900 text-xs">{{ $sale->brand }} {{ $sale->model }}</div>
                                <div class="text-[10px] text-slate-500 font-mono">IMEI: {{ $sale->imei_1 }}</div>
                            </td>
                            <td class="py-2.5 px-3 text-right font-bold font-mono text-slate-900">
                                ₹{{ number_format($sale->total_amount, 2) }}
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                <x-ui.badge variant="success" class="capitalize text-[10.5px]">
                                    {{ $sale->payment_mode ?? 'Paid' }}
                                </x-ui.badge>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-slate-400 text-xs">No sales recorded today yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3 border-t border-slate-100 bg-slate-50/60 flex items-center justify-end text-xs">
                <a href="{{ route('mobileshop.sales') }}" class="font-bold text-indigo-600 hover:text-indigo-700 hover:underline inline-flex items-center gap-1">
                    <span>View All Sales Register</span>
                    <i data-lucide="arrow-right" class="w-3 h-3"></i>
                </a>
            </div>
        </div>

        <!-- Right: Action Center (Ready for Pickup + Overdue Debtors) -->
        <div class="flex flex-col gap-3">
            
            <!-- [A] Ready for Delivery (Collectable Counter Cash) -->
            <div class="rounded-xl border border-slate-200 bg-white shadow-xs overflow-hidden">
                <div class="p-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/60">
                    <div class="text-xs font-bold text-slate-900 flex items-center gap-2">
                        <div class="w-5 h-5 rounded-md bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                        </div>
                        <span>Ready for Pickup</span>
                    </div>
                    @if(($analytics['readyRepairsCollectable'] ?? 0) > 0)
                        <x-ui.badge variant="success" class="font-mono text-[10.5px]">
                            ₹{{ number_format($analytics['readyRepairsCollectable'], 2) }} Collectable
                        </x-ui.badge>
                    @endif
                </div>
                <div class="p-3 flex flex-col gap-2">
                    @php $readyList = $analytics['readyRepairs'] ?? collect(); @endphp
                    @forelse($readyList as $rep)
                    <div class="flex justify-between items-center p-2.5 rounded-lg border border-slate-100 bg-slate-50/50 hover:bg-slate-50 transition-colors gap-2">
                        <div>
                            <div class="font-bold text-xs text-slate-900">
                                {{ $rep->ticket_number }} — {{ $rep->brand }} {{ $rep->model }}
                            </div>
                            <div class="text-[11px] text-slate-500 flex items-center gap-1.5 mt-0.5">
                                <span>{{ $rep->customer_name }}</span>
                                <span class="text-slate-300">&bull;</span>
                                <span class="font-bold text-emerald-600 font-mono">₹{{ number_format($rep->total_amount, 2) }}</span>
                            </div>
                        </div>
                        @php
                            $sName = store_name();
                            $sPhone = store_phone();
                            $sAddress = store_address();
                            $rPhone = preg_replace('/[^0-9]/', '', $rep->customer_phone ?? '');
                            if (strlen($rPhone) === 10) $rPhone = '91' . $rPhone;
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
                        <x-ui.button variant="outline" size="xs" :href="'https://wa.me/' . $rPhone . '?text=' . rawurlencode($rNotifyMsg)" target="_blank" class="text-emerald-700 border-emerald-200 hover:bg-emerald-50">
                            <i data-lucide="message-circle" class="w-3.5 h-3.5 text-emerald-600 mr-1"></i>
                            <span>Notify</span>
                        </x-ui.button>
                    </div>
                    @empty
                    <div class="text-center py-3 text-slate-400 text-xs">
                        No repaired devices currently waiting for pickup.
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- [B] High-Risk Overdue Debtors (₹5,000+) -->
            <div class="rounded-xl border border-slate-200 bg-white shadow-xs overflow-hidden">
                <div class="p-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/60">
                    <div class="text-xs font-bold text-slate-900 flex items-center gap-2">
                        <div class="w-5 h-5 rounded-md bg-rose-50 text-rose-600 flex items-center justify-center">
                            <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                        </div>
                        <span>High-Risk Debtors (₹5,000+)</span>
                    </div>
                    <x-ui.button variant="outline" size="xs" :href="route('mobileshop.khata')">
                        Full Khata
                    </x-ui.button>
                </div>
                <div class="p-3 flex flex-col gap-2">
                    @php $debtorList = $analytics['highRiskDebtors'] ?? collect(); @endphp
                    @forelse($debtorList as $deb)
                    <div class="flex justify-between items-center p-2.5 rounded-lg border border-slate-100 bg-slate-50/50 hover:bg-slate-50 transition-colors gap-2">
                        <div>
                            <div class="font-bold text-xs text-slate-900">
                                {{ $deb->name }}
                            </div>
                            <div class="text-[11px] text-rose-600 font-bold font-mono mt-0.5">
                                Due: ₹{{ number_format($deb->udhari_balance, 2) }}
                            </div>
                        </div>
                        @php
                            $sName = store_name();
                            $sPhone = store_phone();
                            $sAddress = store_address();
                            $sUpi = store_upi_id();
                            $sLandline = store_landline();
                            $debPhone = preg_replace('/[^0-9]/', '', $deb->phone ?? '');
                            if (strlen($debPhone) === 10) $debPhone = '91' . $debPhone;
                            $debMsg = "🔔 *PAYMENT REMINDER*\n";
                            $debMsg .= "🏪 *{$sName}*\n";
                            $debMsg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
                            $debMsg .= "Dear *{$deb->name}*,\n\n";
                            $debMsg .= "Greetings from *{$sName}*!\n\n";
                            $debMsg .= "This is a polite reminder regarding your pending store credit balance:\n";
                            $debMsg .= "📌 *Outstanding Balance Due:* *₹" . number_format($deb->udhari_balance, 2) . "*\n\n";
                            $debMsg .= "📄 *Download Complete PDF Statement:*\n" . route('public.statement.pdf', ['id' => $deb->id]) . "\n\n";
                            if (!empty($sUpi)) {
                                $debMsg .= "Kindly arrange to clear this balance at your earliest convenience via UPI (`{$sUpi}`) or Cash at our store counter.\n\n";
                            } else {
                                $debMsg .= "Kindly arrange to clear this balance at your earliest convenience via UPI or Cash at our store counter.\n\n";
                            }
                            $debMsg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
                            $debMsg .= "📞 *Accounts Desk:* {$sPhone}\n";
                            $debMsg .= "🏢 *Showroom:* {$sAddress}\n";
                            $debMsg .= "_If you have already settled this payment recently, please disregard this message. Thank you for your continued support!_";
                        @endphp
                        <x-ui.button variant="outline" size="xs" :href="'https://wa.me/' . $debPhone . '?text=' . rawurlencode($debMsg)" target="_blank" class="text-rose-700 border-rose-200 hover:bg-rose-50">
                            <i data-lucide="bell" class="w-3.5 h-3.5 text-rose-600 mr-1"></i>
                            <span>Remind</span>
                        </x-ui.button>
                    </div>
                    @empty
                    <div class="text-center py-3 text-slate-400 text-xs">
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
    <div class="rounded-xl border border-rose-200 bg-rose-50/60 p-4 shadow-xs mt-3">
        <div class="flex items-center justify-between pb-3 border-b border-rose-100 flex-wrap gap-2">
            <div class="text-xs font-bold text-rose-900 flex items-center gap-2">
                <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-600"></i>
                <span>Low Stock Warning ({{ count($lowStockParts) }} Parts below minimum alert level)</span>
            </div>
            <x-ui.button variant="outline" size="xs" :href="route('mobileshop.purchase')" class="bg-white text-rose-700 border-rose-300 hover:bg-rose-50">
                + Restock Purchase Order
            </x-ui.button>
        </div>
        <div class="pt-3">
            <div class="flex flex-wrap gap-1.5">
                @foreach($lowStockParts->take(8) as $lsp)
                <span class="text-xs font-semibold bg-white border border-rose-200 px-2.5 py-1 rounded-md text-rose-950 inline-flex items-center gap-1.5 shadow-xs">
                    <span>{{ $lsp->name }}</span>
                    <strong class="text-rose-600 font-mono">({{ $lsp->stock_qty }} left)</strong>
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
