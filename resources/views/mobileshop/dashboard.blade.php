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

@section('title', $nicheTitle . ' — MobiTrack ERP')
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
                    <i data-lucide="plus-circle" style="width:15px;height:15px;color:var(--color-primary);"></i> Register Sale (Full Page)
                </a>
                <a href="{{ route('mobileshop.pos') }}" class="dropdown-item-link">
                    <i data-lucide="smartphone" style="width:15px;height:15px;"></i> New Phone POS
                </a>
                @endif
                @if(auth()->user()->can('create-purchase-phones') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin'))
                <a href="{{ route('mobileshop.purchase.create') }}" class="dropdown-item-link" style="font-weight:600; color:#16A34A;">
                    <i data-lucide="truck" style="width:15px;height:15px;color:#16A34A;"></i> Bulk Purchase Intake
                </a>
                @endif
                @if(auth()->user()->can('create-sale-accessories') || auth()->user()->can('create-sale-covers') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin'))
                <a href="{{ route('mobileshop.sales') }}" class="dropdown-item-link">
                    <i data-lucide="zap" style="width:15px;height:15px;"></i> Accessories POS
                </a>
                @endif
                @if(auth()->user()->can('create-sale-secondhand') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin'))
                <a href="{{ route('mobileshop.second_hand') }}" class="dropdown-item-link">
                    <i data-lucide="repeat" style="width:15px;height:15px;"></i> Sell Pre-Owned
                </a>
                @endif
                @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin'))
                <a href="{{ route('mobileshop.emi.ledger') }}" class="dropdown-item-link">
                    <i data-lucide="building-2" style="width:15px;height:15px;color:#2563EB;"></i> EMI Finance Ledger
                </a>
                <a href="{{ route('mobileshop.purchase_orders') }}" class="dropdown-item-link">
                    <i data-lucide="file-text" style="width:15px;height:15px;"></i> Supplier Purchase Order
                </a>
                @endif
                @if(auth()->user()->can('create-purchase-phones') || auth()->user()->can('manage-stock-phones') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin'))
                <a href="{{ route('mobileshop.new_mobiles') }}" class="dropdown-item-link">
                    <i data-lucide="smartphone" style="width:15px;height:15px;"></i> Add Phone Stock
                </a>
                @endif
                @if(auth()->user()->can('create-purchase-secondhand') || auth()->user()->can('manage-stock-secondhand') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin'))
                <a href="{{ route('mobileshop.second_hand') }}" class="dropdown-item-link">
                    <i data-lucide="repeat" style="width:15px;height:15px;"></i> Intake Pre-Owned
                </a>
                @endif
                @if(auth()->user()->can('create-purchase-accessories') || auth()->user()->can('create-purchase-covers') || auth()->user()->can('manage-stock-accessories') || auth()->user()->can('manage-stock-covers') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin'))
                <a href="{{ route('mobileshop.purchase') }}" class="dropdown-item-link">
                    <i data-lucide="headphones" style="width:15px;height:15px;"></i> Add Part / Accessory
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

    /* ─── MAIN CHART CARD ─── */
    .chart-container-card {
        background: var(--color-surface);
        border-radius: var(--radius-card);
        border: 1px solid var(--color-border-subtle);
        padding: 12px 16px;
        box-shadow: var(--shadow-card);
        margin-bottom: 12px;
        transition: box-shadow 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .chart-container-card:hover {
        box-shadow: var(--shadow-card-hover);
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
        font-size: 14px;
        font-weight: 800;
        color: #0F172A;
        letter-spacing: -0.2px;
    }
    .chart-legend-row {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 14px;
        margin-top: 8px;
        flex-wrap: wrap;
    }
    .legend-item {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        font-weight: 600;
        color: #475569;
    }
    .legend-circle {
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }

    /* ─── DATA TABLES & SPLIT SECTION ─── */
    .dashboard-split-grid {
        display: grid;
        grid-template-columns: 3fr 2fr;
        gap: 12px;
    }
    @media (max-width: 992px) {
        .dashboard-split-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')

    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- 1. TOP 4 KPI CARDS (niche-scoped labels + counts) -->
    <!-- ══════════════════════════════════════════════════════════ -->
    @php
        $kpiPurchaseLabel = match($niche ?? 'admin') {
            'phones'      => 'Stock Additions',
            'secondhand'  => 'Buyback Intakes',
            'accessories' => 'Parts Restocks',
            'covers'      => 'Cover Restocks',
            'repairs'     => 'Jobs Received',
            default       => 'Purchase Invoice',
        };
        $kpiPurchaseSub = match($niche ?? 'admin') {
            'phones'      => 'Total New Phones Added',
            'secondhand'  => 'Pre-Owned Phones Purchased',
            'accessories' => 'Parts & Accessories Inflow',
            'covers'      => 'Covers & Tempered Added',
            'repairs'     => 'Repair Jobs Opened',
            default       => 'Supplier Orders & Stock Inflows',
        };
        $kpiReturnLabel = match($niche ?? 'admin') {
            'secondhand'  => 'Buyback Total',
            'repairs'     => 'Jobs In Progress',
            default       => 'Purchase Return',
        };
        $kpiSaleLabel = match($niche ?? 'admin') {
            'phones'      => 'Phone Sales',
            'secondhand'  => 'Pre-Owned Sales',
            'accessories' => 'Accessories Sold',
            'covers'      => 'Cover/Tempered Sold',
            'repairs'     => 'Jobs Completed',
            default       => 'Sale Invoice',
        };
        $kpiSaleSub = match($niche ?? 'admin') {
            'phones'      => 'Brand New Phones Billed',
            'secondhand'  => 'Pre-Owned Phones Sold',
            'accessories' => 'Accessories Invoiced',
            'covers'      => 'Covers & Tempered Sold',
            'repairs'     => 'Delivered to Customers',
            default       => 'Phones & Accessories Billed',
        };
    @endphp
    <div class="kpi-row">
        <!-- 1. Purchase / Intake -->
        <div class="kpi-card">
            <div class="kpi-top">
                <div class="kpi-num">{{ number_format($statPurchaseInvoices ?? 0) }}</div>
            </div>
            <div>
                <div class="kpi-label">{{ $kpiPurchaseLabel }}</div>
                <div class="kpi-sub">{{ $kpiPurchaseSub }}</div>
            </div>
        </div>

        <!-- 2. Buybacks / Returns / Open Repairs -->
        @if(in_array($niche ?? 'admin', ['admin', 'secondhand', 'repairs']))
        <div class="kpi-card">
            <div class="kpi-top">
                <div class="kpi-num">{{ number_format($statBuybackReturns ?? 0) }}</div>
            </div>
            <div>
                <div class="kpi-label">{{ $kpiReturnLabel }}</div>
                <div class="kpi-sub">
                    @if(($niche ?? '') === 'repairs') Jobs Awaiting Delivery
                    @else Devices Bought Back from Customers
                    @endif
                </div>
            </div>
        </div>
        @else
        <!-- Placeholder card for niches that don't have buybacks -->
        <div class="kpi-card">
            <div class="kpi-top">
                <div class="kpi-num">{{ number_format($totalRepairsOpen ?? 0) ?: '—' }}</div>
            </div>
            <div>
                <div class="kpi-label">{{ ($niche ?? '') === 'repairs' ? 'Open Repairs' : 'Open Repairs' }}</div>
                <div class="kpi-sub">Active Jobs in Workshop</div>
            </div>
        </div>
        @endif

        <!-- 3. Sale Invoices -->
        <div class="kpi-card">
            <div class="kpi-top">
                <div class="kpi-num">{{ number_format($statSaleInvoices ?? 0) }}</div>
            </div>
            <div>
                <div class="kpi-label">{{ $kpiSaleLabel }}</div>
                <div class="kpi-sub">{{ $kpiSaleSub }}</div>
            </div>
        </div>

        <!-- 4. Udhar / Khata (phones + admin) OR Stock Count for others -->
        <div class="kpi-card">
            <div class="kpi-top">
                @if(in_array($niche ?? 'admin', ['admin', 'phones']))
                    <div class="kpi-num">{{ number_format($statPendingUdhari ?? 0) }}</div>
                @elseif(($niche ?? '') === 'accessories')
                    <div class="kpi-num">{{ number_format($availableNewPhones ?? $statPurchaseInvoices ?? 0) }}</div>
                @elseif(($niche ?? '') === 'covers')
                    <div class="kpi-num">{{ number_format($statPurchaseInvoices ?? 0) }}</div>
                @else
                    <div class="kpi-num">{{ number_format($totalRepairsOpen ?? 0) }}</div>
                @endif
            </div>
            <div>
                @if(in_array($niche ?? 'admin', ['admin', 'phones']))
                    <div class="kpi-label">Khata / Udhari</div>
                    <div class="kpi-sub">₹{{ number_format($totalUdhariDue ?? 0, 2) }} Pending Recovery</div>
                @elseif(($niche ?? '') === 'repairs')
                    <div class="kpi-label">Active Repairs</div>
                    <div class="kpi-sub">Jobs Open in Workshop</div>
                @else
                    <div class="kpi-label">Supplier Credit</div>
                    <div class="kpi-sub">₹{{ number_format($totalSupplierCredit ?? 0, 2) }} Balance</div>
                @endif
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- 2. STORE PERFORMANCE TREND (12-Month Spline Line Graph) -->
    <!-- ══════════════════════════════════════════════════════════ -->
    <div class="chart-container-card">
        <div class="chart-header-row">
            <div>
                <div class="chart-main-title">Store Performance & Inflow Trends</div>
                <div style="font-size:12px; color:#64748B;">Annual comparison of sales, purchases, buybacks, and credit recovery across all 12 months</div>
            </div>
            <div style="font-size:11px; font-weight:600; color:#475569; background:#F1F5F9; padding:3px 8px; border-radius:5px;">
                Year: {{ date('Y') }}
            </div>
        </div>

        <!-- Chart.js Spline Canvas -->
        <div style="height: 200px; width: 100%; position: relative;">
            <canvas id="performanceSplineChart"></canvas>
        </div>

        <!-- Calm, semantic legend -->
        <div class="chart-legend-row">
            <div class="legend-item">
                <span class="legend-circle" style="background:#5E6AD2;"></span>
                <span>Sale Invoice</span>
            </div>
            <div class="legend-item">
                <span class="legend-circle" style="background:#16A34A;"></span>
                <span>Purchase Invoice</span>
            </div>
            <div class="legend-item">
                <span class="legend-circle" style="background:#D97706;"></span>
                <span>Purchase Return</span>
            </div>
            <div class="legend-item">
                <span class="legend-circle" style="background:#64748B;"></span>
                <span>Sale Return / Udhar</span>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- 4. RECENT STORE ACTIVITY & ACTIVE TICKETS -->
    <!-- ══════════════════════════════════════════════════════════ -->
    <div class="dashboard-split-grid">
        <!-- Recent Invoices -->
        <div class="card" style="margin-bottom:0;">
            <div class="card-header" style="background:#F8FAFC; border-bottom:1px solid #E2E8F0;">
                <div>
                    <div class="card-title" style="font-size:14px; font-weight:800;">Recent Sales Invoices</div>
                    <div class="card-subtitle" style="font-size:11px;">Live counter sales and customer receipts</div>
                </div>
                <a href="{{ route('mobileshop.pos') }}" class="btn btn-outline btn-sm">New POS Sale</a>
            </div>
            <div class="card-body" style="padding:0; overflow-x:auto;">
                <table class="data-table" style="margin:0; border:none;">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Customer</th>
                            <th>Device / Item</th>
                            <th style="text-align:right;">Amount (₹)</th>
                            <th style="text-align:center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentSales as $sale)
                        <tr>
                            <td>
                                <a href="{{ route('mobileshop.invoice', ['id' => $sale->id]) }}" style="font-family:monospace; font-weight:800; color:var(--brand-700); text-decoration:none;">
                                    {{ $sale->invoice_number }}
                                </a>
                            </td>
                            <td>
                                <div style="font-weight:700; color:#0F172A; font-size:13px;">{{ $sale->customer_name }}</div>
                                <div style="font-size:11px; color:#64748B;">{{ $sale->customer_phone }}</div>
                            </td>
                            <td>
                                <div style="font-weight:700; font-size:12px;">{{ $sale->brand }} {{ $sale->model }}</div>
                                <div style="font-size:10px; color:#64748B; font-family:monospace;">IMEI: {{ $sale->imei_1 }}</div>
                            </td>
                            <td style="text-align:right; font-weight:800; color:#0F172A;">
                                ₹{{ number_format($sale->total_amount, 2) }}
                            </td>
                            <td style="text-align:center;">
                                <span class="badge badge-green">Paid</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="text-align:center; padding:30px; color:#94A3B8;">No sales recorded today yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Service Desk & Low Stock Alerts -->
        <div style="display:flex; flex-direction:column; gap:20px;">
            <!-- Active Repairs -->
            <div class="card" style="margin-bottom:0;">
                <div class="card-header" style="background:#F8FAFC; border-bottom:1px solid #E2E8F0;">
                    <div>
                        <div class="card-title" style="font-size:14px; font-weight:800;">Active Repair Tickets</div>
                        <div class="card-subtitle" style="font-size:11px;">Devices currently in diagnostic / repair queue</div>
                    </div>
                    <a href="{{ route('mobileshop.repairs') }}" class="btn btn-outline btn-sm">Service Desk</a>
                </div>
                <div class="card-body" style="padding:10px 16px;">
                    <div style="display:flex; flex-direction:column; gap:8px;">
                        @forelse($activeRepairs as $repair)
                        <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 12px; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:10px;">
                            <div>
                                <div style="font-weight:800; font-size:13px; color:#0F172A;">{{ $repair->ticket_number }} — {{ $repair->brand }} {{ $repair->model }}</div>
                                <div style="font-size:11px; color:#64748B;">Client: {{ $repair->customer_name }} | Fault: {{ Str::limit($repair->reported_faults, 24) }}</div>
                            </div>
                            <span class="badge badge-blue" style="text-transform:capitalize;">{{ str_replace('_', ' ', $repair->status) }}</span>
                        </div>
                        @empty
                        <div style="text-align:center; padding:20px; color:#94A3B8; font-size:12px;">All repair tickets are up to date.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Low Stock Warnings -->
            @if(count($lowStockParts) > 0)
            <div class="card" style="margin-bottom:0; border-color:#FECDD3; background:#FFF1F2;">
                <div class="card-header" style="background:transparent; border-bottom:1px solid #FFE4E6; padding-bottom:10px;">
                    <div class="card-title" style="font-size:13px; font-weight:800; color:#BE123C; display:flex; align-items:center; gap:6px;">
                        <i data-lucide="alert-triangle" style="width:16px;height:16px;"></i> ⚠️ Low Stock Alerts ({{ count($lowStockParts) }} Parts)
                    </div>
                    <a href="{{ route('mobileshop.purchase') }}" class="btn btn-outline btn-sm" style="background:#fff; color:#BE123C; border-color:#FDA4AF;">Restock Now</a>
                </div>
                <div class="card-body" style="padding:10px 16px;">
                    <div style="display:flex; flex-wrap:wrap; gap:8px;">
                        @foreach($lowStockParts->take(5) as $lsp)
                        <span style="font-size:11px; font-weight:700; background:#fff; border:1px solid #FECDD3; padding:4px 8px; border-radius:6px; color:#9F1239;">
                            {{ $lsp->name }} (Stock: {{ $lsp->stock_qty }})
                        </span>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

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
        ctx.fillText('Chart unavailable — data shown in tables below', canvas.width / 2, canvas.height / 2);
    };
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js" onerror="window.__chartFallback();"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const chartData = @json($monthlyChartData ?? []);
        const ctx = document.getElementById('performanceSplineChart').getContext('2d');

        const labels = chartData.labels || ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        const purchaseData = chartData.purchases || [180, 189, 221, 233, 191, 284, 302, 169, 0, 0, 0, 0];
        const buybackData = chartData.buybacks || [20, 35, 42, 50, 48, 55, 60, 49, 0, 0, 0, 0];
        const saleData = chartData.sales || [244, 1507, 1804, 2400, 2240, 2607, 3521, 2607, 0, 0, 0, 0];
        const udhariData = chartData.udhari || [50, 70, 85, 110, 95, 120, 140, 90, 0, 0, 0, 0];

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Sale Invoice',
                        data: saleData,
                        borderColor: '#5E6AD2',
                        backgroundColor: 'rgba(94, 106, 210, 0.05)',
                        borderWidth: 2.5,
                        tension: 0.4,
                        pointRadius: 3.5,
                        pointBackgroundColor: '#5E6AD2',
                        pointHoverRadius: 5,
                        fill: false
                    },
                    {
                        label: 'Purchase Invoice',
                        data: purchaseData,
                        borderColor: '#16A34A',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        tension: 0.4,
                        pointRadius: 3,
                        pointBackgroundColor: '#16A34A',
                        fill: false
                    },
                    {
                        label: 'Purchase Return',
                        data: buybackData,
                        borderColor: '#D97706',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        tension: 0.4,
                        pointRadius: 3,
                        pointBackgroundColor: '#D97706',
                        fill: false
                    },
                    {
                        label: 'Sale Return / Udhar',
                        data: udhariData,
                        borderColor: '#64748B',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        tension: 0.4,
                        pointRadius: 3,
                        pointBackgroundColor: '#64748B',
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
                        bodyFont: { size: 12 },
                        padding: 10,
                        cornerRadius: 6
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
                            font: { size: 11 }
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
