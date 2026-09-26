@extends('mobileshop.layout')

@php
    $purchasePageTitle = match($niche ?? 'admin') {
        'accessories' => 'Accessories & Parts Restock',
        'repairs'     => 'Parts Intake & Consumption',
        default       => 'Purchase & Stock Inflow Hub',
    };
@endphp

@section('title', $purchasePageTitle . ' — PhoneFix Azamgarh ERP')
@section('page-title', $purchasePageTitle)

@section('page-actions')
    <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        <a href="{{ route('mobileshop.accessories.purchase') }}" class="btn btn-primary btn-sm" style="color: #fff; background: var(--color-primary); border-color: var(--color-primary); font-weight:800; display:inline-flex; align-items:center; gap:6px;">
            <i data-lucide="plus-circle" style="width:15px;height:15px;"></i> + Create Purchase Order / Restock
        </a>
        @if(($isAdmin ?? false) || auth()->user()->hasRole('store-admin') || auth()->user()->can('read-mobileshop-procurement'))
        <button type="button" onclick="openPaymentModal({{ $suppliers->first()->id ?? 0 }}, '{{ addslashes($suppliers->first()->name ?? 'Primary Supplier') }}')" class="btn btn-outline btn-sm">
            <i data-lucide="wallet" style="width:14px;height:14px;"></i> Supplier Payment / Advance
        </button>

        @endif
    </div>
@endsection

@push('styles')
<style>
    /* Safe container wrapper */
    .purchase-page-wrapper {
        position: relative;
    }

    .purchase-search-wrapper {
        width: 280px;
    }
    .purchase-search-box {
        width: 100%;
    }

    /* Responsive Modal Form Grids */
    .modal-form-grid-2 {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        margin-bottom: 12px;
    }
    .modal-form-grid-3 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
        margin-bottom: 12px;
    }
    @media (max-width: 639px) {
        .modal-form-grid-2,
        .modal-form-grid-3 {
            grid-template-columns: 1fr !important;
            gap: 10px !important;
        }
        /* Mobile Bottom-sheet styling for inline modals */
        #paymentModal,
        #editSupplierModal {
            align-items: flex-end !important;
            padding: 0 !important;
        }
        #paymentModal .card,
        #editSupplierModal .card {
            max-width: 100% !important;
            width: 100% !important;
            border-radius: 16px 16px 0 0 !important;
            max-height: 92vh !important;
            margin: 0 !important;
        }
        .modal-sticky-footer {
            position: sticky;
            bottom: 0;
            background: #FFFFFF;
            z-index: 10;
            padding: 12px 16px;
            border-top: 1px solid #E2E8F0;
        }
    }

    /* ─── RESPONSIVE BREAKPOINTS (Mobile < 768px) ─── */
    @media (max-width: 767px) {
        .purchase-page-wrapper {
            padding-bottom: 84px !important;
        }
        .hide-on-mobile {
            display: none !important;
        }

        /* Hide heavy KPI grid on mobile, show compact stat strip */
        .kpi-grid {
            display: none !important;
        }
        .mobile-stat-strip {
            display: flex !important;
            margin: 0 0 8px 0 !important;
        }

        /* Remove Box-in-Box: Strip outer card border & radius */
        .purchase-registry-card {
            border: none !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            background: transparent !important;
        }
        .purchase-header-container {
            padding: 8px 10px !important;
            background: #FFFFFF !important;
            border: 1px solid #E2E8F0 !important;
            border-radius: 10px 10px 0 0 !important;
            border-bottom: none !important;
        }
        .purchase-header-title-box {
            display: none !important;
        }
        .purchase-search-wrapper {
            width: 100% !important;
        }
        .purchase-search-input {
            min-height: 38px !important;
            font-size: 12px !important;
            border-radius: 8px !important;
            padding: 6px 68px 6px 34px !important;
        }
        .purchase-filter-toolbar {
            padding: 6px 10px !important;
            background: #F8FAFC !important;
            border-left: 1px solid #E2E8F0 !important;
            border-right: 1px solid #E2E8F0 !important;
            border-bottom: 1px solid #E2E8F0 !important;
        }
        .desktop-date-inputs {
            display: none !important;
        }
        .mobile-filter-btn {
            display: inline-flex !important;
            height: 28px !important;
            width: 30px !important;
        }

        /* Switch Table to Flat Rows (No Nested Card-in-Card) */
        #purchaseInvoicesTable {
            display: none !important;
        }
        .mobile-purchase-cards {
            display: flex !important;
            flex-direction: column !important;
            gap: 0 !important;
            padding: 0 !important;
            background: #FFFFFF !important;
            border: 1px solid #E2E8F0 !important;
            border-top: none !important;
            border-radius: 0 0 10px 10px !important;
            overflow: hidden !important;
        }
    }

    @media (min-width: 768px) {
        .mobile-filter-btn {
            display: none !important;
        }
        .desktop-date-inputs {
            display: flex !important;
        }
        #purchaseInvoicesTable {
            display: table !important;
        }
        .mobile-purchase-cards {
            display: none !important;
        }
    }
</style>
@endpush

@section('content')
<div class="purchase-page-wrapper">

    <!-- Mobile Compact Stat Strip (shown only on mobile) -->
    <div class="mobile-stat-strip">
        <div class="stat-strip-item">
            <span class="stat-label">Total</span>
            <span class="stat-val">₹{{ fmod($totalPOValue, 1) != 0 ? number_format($totalPOValue, 2) : number_format($totalPOValue, 0) }}</span>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-strip-item">
            <span class="stat-label">Due</span>
            <span class="stat-val" style="color:var(--color-danger);">₹{{ fmod($totalPODue, 1) != 0 ? number_format($totalPODue, 2) : number_format($totalPODue, 0) }}</span>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-strip-item">
            <span class="stat-label">Invoices</span>
            <span class="stat-val">{{ $totalInvoicesCount }}</span>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-strip-item" onclick="toggleDefectiveRegistry()" style="cursor:pointer;" title="Click to view Defective Items & Returns">
            <span class="stat-label">Defects</span>
            <span class="stat-val" style="color:#DC2626;">{{ $totalDefectiveQty ?? 0 }}</span>
        </div>
    </div>

    <!-- Top KPI Cards (Desktop View) -->
    <div class="kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
        <div class="card kpi-card">
            <div class="kpi-label">Total Procurement</div>
            <div class="kpi-num">₹{{ fmod($totalPOValue, 1) != 0 ? number_format($totalPOValue, 2) : number_format($totalPOValue, 0) }}</div>
        </div>

        <div class="card kpi-card">
            <div class="kpi-label">Vendor Balances Due</div>
            <div class="kpi-num" style="color:var(--color-danger);">₹{{ fmod($totalPODue, 1) != 0 ? number_format($totalPODue, 2) : number_format($totalPODue, 0) }}</div>
        </div>

        <div class="card kpi-card">
            <div class="kpi-label">Units Purchased</div>
            <div class="kpi-num" style="color:#15803D;">{{ number_format($totalUnitsPurchased) }} Units</div>
        </div>

        <div class="card kpi-card">
            <div class="kpi-label">Vendors Active</div>
            <div class="kpi-num">{{ count($suppliers) }} Vendors</div>
        </div>

        <div class="card kpi-card" onclick="toggleDefectiveRegistry()" style="cursor:pointer; border-color:#FECACA;" title="Click to view Defective Items & Returns">
            <div class="kpi-label" style="display:flex; justify-content:space-between; align-items:center;">
                <span style="color:#991B1B;">⚠️ Defective / RMA</span>
                @if(($totalDefectiveQty ?? 0) > 0)
                    <span class="badge badge-red" style="font-size:9.5px; padding:1px 6px;">{{ $totalDefectiveQty }} Pending</span>
                @endif
            </div>
            <div class="kpi-num" style="color:#DC2626;">{{ $totalDefectiveQty ?? 0 }} <span style="font-size:12px; font-weight:600; color:#64748B;">Units</span></div>
        </div>
    </div>

    <!-- Master Live Purchase Invoice Registry (Single un-nested container) -->
    <div class="card purchase-registry-card">
        <div class="purchase-header-container">
            <div class="purchase-header-title-box flex items-center gap-2" style="flex-wrap:wrap;">
                <div class="w-6 h-6 rounded-sm bg-primary-tint text-primary flex items-center justify-center flex-shrink-0">
                    <i data-lucide="file-spreadsheet" style="width:14px;height:14px;"></i>
                </div>
                <div>
                    <h2 class="text-xs font-semibold text-ink leading-tight m-0" id="purchaseRegistryHeading">Live Purchase Invoice Registry</h2>
                    <span id="purchaseVisibleCountBadge" style="display:none;"></span>
                </div>

                <!-- View Selector Tabs (Invoices vs Defective Items) -->
                <div class="purchase-view-tabs" style="display:inline-flex; align-items:center; gap:4px; margin-left:8px; background:#F1F5F9; padding:2px; border-radius:6px;">
                    <button type="button" id="tabBtnPurchases" onclick="switchPurchaseView('purchases')" style="font-size:11px; font-weight:700; height:24px; padding:0 10px; background:#5E6AD2; color:#fff; border:none; border-radius:4px; display:inline-flex; align-items:center; gap:4px; cursor:pointer;">
                        <i data-lucide="file-spreadsheet" style="width:12px;height:12px;"></i> Invoices ({{ count($purchaseInvoices) }})
                    </button>
                    <button type="button" id="tabBtnDefective" onclick="switchPurchaseView('defective')" style="font-size:11px; font-weight:700; height:24px; padding:0 10px; color:#DC2626; background:transparent; border:none; border-radius:4px; display:inline-flex; align-items:center; gap:4px; cursor:pointer;">
                        <i data-lucide="alert-triangle" style="width:12px;height:12px;"></i> Defective Items ({{ count($defectiveItems ?? []) }})
                    </button>
                </div>
            </div>

            <!-- Search Bar with Embedded Date Filter Trigger -->
            <div class="purchase-search-wrapper relative">
                <div class="search-bar purchase-search-box">
                    <i data-lucide="search"></i>
                    <input type="text" id="purchaseSearchInput" oninput="filterPurchaseTables()" placeholder="Search" class="purchase-search-input">
                    <button type="button" onclick="clearPurchaseSearch()" id="btnClearPurchaseSearch" style="display:none; background:#e2e4e8; border:none; border-radius:50%; width:16px; height:16px; color:#4f535b; cursor:pointer; font-size:10px; line-height:16px; text-align:center; padding:0;">✕</button>
                    <button type="button" onclick="openPurchaseDateFilterDrawer()" id="btnMobilePurchaseDateFilter" class="mobile-filter-btn" title="Filter by Date Range" style="height:22px; width:22px; padding:0; border-radius:4px; background:#ffffff; border:1px solid #e2e4e8; color:#5e6ad2; align-items:center; justify-content:center; cursor:pointer; position:relative;">
                        <i data-lucide="calendar" style="width:12px; height:12px;"></i>
                        <span id="purchaseDateFilterActiveIndicator" style="display:none; position:absolute; top:2px; right:2px; width:4px; height:4px; border-radius:50%; background:#5e6ad2;"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Integrated Un-Nested Toolbar (Single Flush Rail) -->
        <div class="filter-bar purchase-filter-toolbar">
            <!-- Category & Date Rail -->
            <div class="pills-scroll-rail purchase-pills-rail">
                <!-- Date Presets -->
                <button type="button" onclick="setPurchaseDatePreset('all')" id="purchaseDateBtn_all" class="filter-pill purchase-pill purchase-date-pill active">All Time</button>
                <button type="button" onclick="setPurchaseDatePreset('today')" id="purchaseDateBtn_today" class="filter-pill purchase-pill purchase-date-pill">Today</button>
                <button type="button" onclick="setPurchaseDatePreset('yesterday')" id="purchaseDateBtn_yesterday" class="filter-pill purchase-pill purchase-date-pill">Yesterday</button>
                <button type="button" onclick="setPurchaseDatePreset('week')" id="purchaseDateBtn_week" class="filter-pill purchase-pill purchase-date-pill">7 Days</button>
                <button type="button" onclick="setPurchaseDatePreset('month')" id="purchaseDateBtn_month" class="filter-pill purchase-pill purchase-date-pill">This Month</button>
            </div>

            <!-- Desktop Custom Date Pickers -->
            <div class="desktop-date-inputs flex items-center gap-1.5 flex-shrink-0">
                <label for="purchaseFromDate" class="text-[11px] font-semibold text-ink-muted m-0">From:</label>
                <input type="date" id="purchaseFromDate" onchange="onPurchaseCustomDateChange()" style="font-size:11px; padding:2px 6px; height:24px; border:1px solid var(--color-hairline); border-radius:4px; font-weight:600; color:var(--color-ink); background:var(--color-canvas);">
                <label for="purchaseToDate" class="text-[11px] font-semibold text-ink-muted m-0">To:</label>
                <input type="date" id="purchaseToDate" onchange="onPurchaseCustomDateChange()" style="font-size:11px; padding:2px 6px; height:24px; border:1px solid var(--color-hairline); border-radius:4px; font-weight:600; color:var(--color-ink); background:var(--color-canvas);">
                <button type="button" onclick="setPurchaseDatePreset('all')" title="Reset Filter" class="btn btn-outline btn-sm">Reset</button>
            </div>
        </div>

        <div class="card-body" style="padding:0; overflow-x:auto;">
            <!-- Purchase Invoices Container (Default View) -->
            <div id="purchaseInvoicesContainer">
                <table class="data-table" id="purchaseInvoicesTable" style="margin:0; width:100%;">
                    <thead style="background:#F8FAFC;">
                        <tr>
                            <th style="padding:12px 16px;">Date</th>
                            <th>Invoice / PO #</th>
                            <th>Supplier / Vendor</th>
                            <th>Purchased Items</th>
                            <th>Payment Status</th>
                            <th style="text-align:right;">Invoice Amount (₹)</th>
                            <th style="text-align:right;">Amount Paid (₹)</th>
                            <th style="text-align:right;">Supplier Due (₹)</th>
                            <th style="text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchaseInvoices as $inv)
                        @php
                            // Preview text of first 2 items
                            $itemPreviews = $inv->items->take(2)->map(function($i) {
                                return $i->brand . ' ' . $i->model . ($i->qty > 1 ? " (x{$i->qty})" : "");
                            })->implode(', ');
                            if ($inv->item_count > 2) {
                                $itemPreviews .= ' +' . ($inv->item_count - 2) . ' more';
                            }
                        @endphp
                        <tr class="purchase-invoice-row purchase-data-row" data-date="{{ \Carbon\Carbon::parse($inv->order_date)->format('Y-m-d') }}">
                            <td style="font-size:11px; color:#64748B; padding:12px 16px; white-space:nowrap;">
                                {{ \Carbon\Carbon::parse($inv->order_date)->format('d M Y') }}
                            </td>
                            <td>
                                <a href="{{ route('mobileshop.purchase.invoice', ['id' => $inv->id]) }}" style="font-family:monospace; font-weight:800; color:var(--brand-700); text-decoration:none; display:inline-flex; align-items:center; gap:4px;">
                                    <i data-lucide="file-text" style="width:13px;height:13px;"></i>
                                    #{{ $inv->po_number }}
                                </a>
                            </td>
                            <td>
                                <div style="font-weight:800; color:#0F172A; font-size:13px;">{{ $inv->supplier_name ?: 'Vendor / Distributor' }}</div>
                                <div style="font-size:11px; color:#64748B; font-family:monospace;">{{ $inv->supplier_phone ?: '—' }}</div>
                            </td>
                            <td>
                                <div style="display:inline-flex; align-items:center; gap:6px; margin-bottom:2px;">
                                    <span class="badge badge-gray" style="font-weight:800; font-size:11px;">{{ $inv->item_count }} {{ Str::plural('Item', $inv->item_count) }}</span>
                                    <span style="font-size:11px; font-weight:700; color:#15803D;">({{ $inv->total_units }} Units)</span>
                                </div>
                                <div style="font-size:11px; color:#64748B; max-width:320px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    {{ $itemPreviews ?: 'Standard stock items' }}
                                </div>
                            </td>
                            <td>
                                @if(($inv->status ?? '') === 'paid' || ($inv->status ?? '') === 'received')
                                    <span class="badge badge-green" style="font-weight:700;">Paid in Full</span>
                                @elseif(($inv->status ?? '') === 'partial' || ($inv->status ?? '') === 'partially_paid')
                                    <span class="badge badge-orange" style="font-weight:700;">Partial Due</span>
                                @else
                                    <span class="badge badge-gray" style="font-weight:700;">{{ ucfirst(str_replace('_', ' ', $inv->status ?? 'pending')) }}</span>
                                @endif
                            </td>
                            <td style="text-align:right; font-weight:900; font-size:13px; color:#0F172A;">
                                ₹{{ number_format($inv->total_amount, 2) }}
                            </td>
                            <td style="text-align:right; font-weight:700; font-size:13px; color:#15803D;">
                                ₹{{ number_format($inv->amount_paid ?? 0, 2) }}
                            </td>
                            <td style="text-align:right; font-weight:800; font-size:13px; color:{{ ($inv->balance_due ?? 0) > 0 ? '#B91C1C' : '#64748B' }};">
                                ₹{{ number_format($inv->balance_due ?? 0, 2) }}
                            </td>
                            <td style="text-align:center; white-space:nowrap;">
                                <div style="display:inline-flex; gap:6px; align-items:center;">
                                    <button type="button" class="btn btn-outline btn-sm" style="padding:4px 10px; font-weight:700; font-size:11px; display:inline-flex; align-items:center; gap:4px; color:var(--brand-700); border-color:#CBD5E1;"
                                            data-invoice='@json($inv)'
                                            data-items='@json($inv->items)'
                                            onclick="openViewPurchaseModal(this)">
                                        <i data-lucide="eye" style="width:13px;height:13px;"></i> View Items
                                    </button>
                                    <button type="button" class="btn btn-outline btn-sm" style="padding:4px 9px; font-weight:700; font-size:11px; display:inline-flex; align-items:center; gap:4px; color:#DC2626; border-color:#FECACA; background:#FEF2F2;"
                                            data-invoice='@json($inv)'
                                            data-items='@json($inv->items)'
                                            onclick="openPurchaseDefectModal(this)"
                                            title="Record Defective Item / Return to Supplier">
                                        <i data-lucide="alert-triangle" style="width:13px;height:13px;"></i> Return / Defect
                                    </button>
                                    <a href="{{ route('mobileshop.purchase.invoice', ['id' => $inv->id]) }}" class="btn btn-outline btn-sm" style="padding:4px 8px; font-size:11px; font-weight:700; color:#475569; border-color:#CBD5E1;" title="Print Full Purchase Invoice">
                                        <i data-lucide="printer" style="width:13px;height:13px;"></i>
                                    </a>
                                    <a href="{{ route('mobileshop.purchase.invoice.pdf', ['id' => $inv->id]) }}" class="btn btn-outline btn-sm" style="padding:4px 8px; font-size:11px; font-weight:700; color:#D97706; border-color:#FDE68A; background:#FFFBEB;" title="Download Purchase Order PDF">
                                        <i data-lucide="download" style="width:13px;height:13px;"></i> PDF
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" style="text-align:center; padding:32px; color:#94A3B8;">
                                <i data-lucide="inbox" style="width:36px;height:36px; stroke-width:1.5; margin-bottom:8px;"></i>
                                <div style="font-weight:700; font-size:14px; color:#475569;">No purchase invoices recorded yet</div>
                                <div style="font-size:12px;">Add phone stock or import wholesale parts bills to start recording purchase invoices.</div>
                            </td>
                        </tr>
                        @endforelse
                        <tr id="purchaseEmptyFilterRow" style="display:none;">
                            <td colspan="9" style="text-align:center; padding:36px 16px; color:#64748B;">
                                <div style="display:inline-flex; flex-direction:column; align-items:center; gap:8px;">
                                    <div style="width:38px; height:38px; border-radius:50%; background:#F1F5F9; display:flex; align-items:center; justify-content:center; color:#64748B;">
                                        <i data-lucide="calendar-x" style="width:20px; height:20px;"></i>
                                    </div>
                                    <div style="font-weight:700; color:#1E293B; font-size:13px;">No purchase invoices found for this date range</div>
                                    <div style="font-size:11.5px; color:#64748B;">Try selecting a different date preset (e.g. 7 Days, This Month) or reset filters</div>
                                    <button type="button" onclick="setPurchaseDatePreset('all')" class="filter-pill" style="margin-top:6px; cursor:pointer; background:#5E6AD2; color:#fff; border:none; padding:4px 12px; border-radius:6px; font-weight:700; font-size:11.5px;">
                                        Show All Invoices
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- MOBILE FLAT LIST VIEW -->
                <div id="purchaseMobileCards" class="mobile-purchase-cards">
                    @forelse($purchaseInvoices as $inv)
                        @php
                            $itemPreviews = $inv->items->take(2)->map(function($i) {
                                return $i->brand . ' ' . $i->model . ($i->qty > 1 ? " (x{$i->qty})" : "");
                            })->implode(', ');
                            if ($inv->item_count > 2) {
                                $itemPreviews .= ' +' . ($inv->item_count - 2) . ' more';
                            }

                            $statusLabel = 'Paid';
                            $statusBg = '#DCFCE7';
                            $statusColor = '#15803D';
                            if (($inv->status ?? '') === 'partial' || ($inv->status ?? '') === 'partially_paid') {
                                $statusLabel = 'Partial Due';
                                $statusBg = '#FEF2F2';
                                $statusColor = '#B91C1C';
                            } elseif (($inv->status ?? '') !== 'paid' && ($inv->status ?? '') !== 'received') {
                                $statusLabel = ucfirst(str_replace('_', ' ', $inv->status ?? 'pending'));
                                $statusBg = '#F1F5F9';
                                $statusColor = '#475569';
                            }
                        @endphp
                        <div class="purchase-flat-row purchase-data-row" data-date="{{ \Carbon\Carbon::parse($inv->order_date)->format('Y-m-d') }}">
                            <!-- Row 1: PO # + Payment Status + Total Amount -->
                            <div class="row-line1">
                                <a href="{{ route('mobileshop.purchase.invoice', ['id' => $inv->id]) }}" class="inv-num">
                                    #{{ $inv->po_number }}
                                </a>
                                <span class="pay-badge" style="background:{{ $statusBg }}; color:{{ $statusColor }};">
                                    {{ $statusLabel }}
                                </span>
                                <div class="row-amount">₹{{ number_format($inv->total_amount, 2) }}</div>
                            </div>

                            <!-- Row 1b: Supplier Debt & Paid Breakdown -->
                            <div style="display:flex; justify-content:space-between; align-items:center; font-size:11px; margin:3px 0 5px; padding:3px 8px; background:#F8FAFC; border-radius:6px; border:1px solid #E2E8F0;">
                                <span style="color:#15803D; font-weight:700;">Paid: ₹{{ number_format($inv->amount_paid ?? 0, 2) }}</span>
                                <span style="color:{{ ($inv->balance_due ?? 0) > 0 ? '#B91C1C' : '#64748B' }}; font-weight:800;">
                                    Due: ₹{{ number_format($inv->balance_due ?? 0, 2) }}
                                </span>
                            </div>

                            <!-- Row 2: Supplier Name + Date & Phone -->
                            <div class="row-line2">
                                <div class="cust-name">{{ $inv->supplier_name ?: 'Vendor / Distributor' }}</div>
                                <div class="cust-phone">{{ \Carbon\Carbon::parse($inv->order_date)->format('d M Y') }} • {{ $inv->supplier_phone ?: '—' }}</div>
                            </div>

                            <!-- Row 3: Items Summary + Compact Actions -->
                            <div class="row-line3">
                                <div class="items-summary">
                                    <strong>{{ $inv->item_count }} {{ Str::plural('Item', $inv->item_count) }} ({{ $inv->total_units }}u)</strong>
                                    • {{ $itemPreviews ?: 'Standard stock' }}
                                </div>
                                <div class="row-actions">
                                    <button type="button" class="compact-action-btn"
                                            data-invoice='@json($inv)'
                                            data-items='@json($inv->items)'
                                            onclick="openViewPurchaseModal(this)"
                                            title="View Line Items">
                                        <i data-lucide="eye" style="width:15px;height:15px;"></i>
                                    </button>
                                    <button type="button" class="compact-action-btn" style="color:#DC2626; border-color:#FECACA; background:#FEF2F2;"
                                            data-invoice='@json($inv)'
                                            data-items='@json($inv->items)'
                                            onclick="openPurchaseDefectModal(this)"
                                            title="Record Defective / Return Items">
                                        <i data-lucide="alert-triangle" style="width:14px;height:14px;"></i>
                                    </button>
                                    <a href="{{ route('mobileshop.purchase.invoice', ['id' => $inv->id]) }}" class="compact-action-btn" title="Print Invoice">
                                        <i data-lucide="printer" style="width:15px;height:15px;"></i>
                                    </a>
                                    <a href="{{ route('mobileshop.purchase.invoice.pdf', ['id' => $inv->id]) }}" class="compact-action-btn" title="Download PDF" style="color:#D97706;">
                                        <i data-lucide="download" style="width:15px;height:15px;"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div style="text-align:center; padding:32px 16px; color:#94A3B8;">
                            <i data-lucide="inbox" style="width:32px;height:32px; margin-bottom:8px;"></i>
                            <div style="font-weight:700; font-size:13px; color:#475569;">No purchase invoices recorded yet</div>
                        </div>
                    @endforelse
                    <div id="purchaseMobileEmptyFilterRow" style="display:none; text-align:center; padding:28px 16px; color:#64748B;">
                        <div style="font-weight:700; color:#1E293B; font-size:13px; margin-bottom:4px;">No purchase invoices found for this date range</div>
                        <div style="font-size:11.5px; color:#64748B; margin-bottom:10px;">Try selecting 7 Days, This Month, or All Time</div>
                        <button type="button" onclick="setPurchaseDatePreset('all')" class="filter-pill" style="cursor:pointer; background:#5E6AD2; color:#fff; border:none; padding:5px 14px; border-radius:6px; font-weight:700; font-size:11.5px;">
                            Show All Invoices
                        </button>
                    </div>
                </div>
                <div id="purchaseInvoicesPagination"></div>
            </div>

            <!-- Defective Items & Returns Registry Container (Switched View) -->
            <div id="defectiveItemsContainer" style="display:none;">
                <!-- Defective Sub-Toolbar -->
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; padding:12px 16px; background:#FEF2F2; border-bottom:1px solid #FECACA;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <span style="font-size:12px; font-weight:800; color:#991B1B;">Filter Status:</span>
                        <div style="display:inline-flex; gap:6px; flex-wrap:wrap;">
                            <button type="button" onclick="filterDefectiveStatus('all', this)" class="btn btn-sm defective-status-pill active" style="font-size:11px; font-weight:700; height:24px; padding:0 8px; border-radius:4px; background:#991B1B; color:#fff; border:none; cursor:pointer;">All ({{ count($defectiveItems ?? []) }})</button>
                            <button type="button" onclick="filterDefectiveStatus('pending_supplier_return', this)" class="btn btn-sm btn-outline defective-status-pill" style="font-size:11px; font-weight:700; height:24px; padding:0 8px; border-radius:4px; background:#fff; color:#B45309; border-color:#FDE68A; cursor:pointer;">Pending Return ({{ ($defectiveItems ?? collect())->where('status', 'pending_supplier_return')->count() }})</button>
                            <button type="button" onclick="filterDefectiveStatus('returned_to_supplier', this)" class="btn btn-sm btn-outline defective-status-pill" style="font-size:11px; font-weight:700; height:24px; padding:0 8px; border-radius:4px; background:#fff; color:#1D4ED8; border-color:#BFDBFE; cursor:pointer;">Returned ({{ ($defectiveItems ?? collect())->where('status', 'returned_to_supplier')->count() }})</button>
                            <button type="button" onclick="filterDefectiveStatus('replaced_by_supplier', this)" class="btn btn-sm btn-outline defective-status-pill" style="font-size:11px; font-weight:700; height:24px; padding:0 8px; border-radius:4px; background:#fff; color:#15803D; border-color:#BBF7D0; cursor:pointer;">Replaced ({{ ($defectiveItems ?? collect())->where('status', 'replaced_by_supplier')->count() }})</button>
                            <button type="button" onclick="filterDefectiveStatus('scrap_written_off', this)" class="btn btn-sm btn-outline defective-status-pill" style="font-size:11px; font-weight:700; height:24px; padding:0 8px; border-radius:4px; background:#fff; color:#64748B; border-color:#CBD5E1; cursor:pointer;">Scrap ({{ ($defectiveItems ?? collect())->where('status', 'scrap_written_off')->count() }})</button>
                        </div>
                    </div>
                    <div style="font-size:12px; font-weight:700; color:#991B1B;">
                        Total Defective Units: <span style="font-size:14px; font-weight:900;">{{ ($defectiveItems ?? collect())->sum('qty') }}</span>
                    </div>
                </div>

                <!-- Defective Items Table (Desktop) -->
                <table class="data-table" id="defectiveItemsTable" style="margin:0; width:100%;">
                    <thead style="background:#F8FAFC;">
                        <tr>
                            <th style="padding:12px 16px;">Date</th>
                            <th>Source</th>
                            <th>Defective Item</th>
                            <th>Supplier / Customer</th>
                            <th style="text-align:center;">Defect Qty</th>
                            <th style="text-align:right;">Cost (₹)</th>
                            <th>Reason & Remarks</th>
                            <th style="text-align:center;">Status</th>
                            <th style="text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="defectiveItemsTableBody">
                        @forelse($defectiveItems ?? [] as $defect)
                        @php
                            $statusStyle = match($defect->status) {
                                'returned_to_supplier' => ['bg' => '#EFF6FF', 'color' => '#1D4ED8', 'label' => '🚚 Dispatched to Vendor'],
                                'replaced_by_supplier' => ['bg' => '#DCFCE7', 'color' => '#15803D', 'label' => '✅ Replaced by Vendor'],
                                'scrap_written_off'    => ['bg' => '#F1F5F9', 'color' => '#64748B', 'label' => '🗑️ Written Off / Scrap'],
                                default                => ['bg' => '#FEF3C7', 'color' => '#B45309', 'label' => '⚠️ Pending Vendor Return'],
                            };
                            $sourceBadge = ($defect->source_type === 'sale_return')
                                ? ['bg' => '#EDE9FE', 'color' => '#6D28D9', 'label' => 'Sold Item Return']
                                : ['bg' => '#EFF6FF', 'color' => '#1E40AF', 'label' => 'Purchase Return'];
                        @endphp
                        <tr class="defective-data-row" data-status="{{ $defect->status }}">
                            <td style="font-size:11px; color:#64748B; padding:12px 16px; white-space:nowrap;">
                                {{ \Carbon\Carbon::parse($defect->created_at)->format('d M Y') }}
                            </td>
                            <td>
                                <span class="badge" style="background:{{ $sourceBadge['bg'] }}; color:{{ $sourceBadge['color'] }}; font-size:10px; font-weight:700;">{{ $sourceBadge['label'] }}</span>
                                @if($defect->source_ref)
                                    <div style="font-size:11px; font-family:monospace; font-weight:700; color:#475569; margin-top:2px;">#{{ $defect->source_ref }}</div>
                                @endif
                            </td>
                            <td>
                                <div style="font-weight:800; color:#0F172A; font-size:13px;">{{ $defect->item_name }}</div>
                            </td>
                            <td>
                                <div style="font-weight:700; color:#334155; font-size:12px;">{{ $defect->supplier_name ?: ($defect->customer_name ?: 'Vendor / Customer') }}</div>
                                @if($defect->customer_name && $defect->source_type === 'sale_return')
                                    <div style="font-size:10.5px; color:#7C3AED; font-weight:600;">Customer Return</div>
                                @endif
                            </td>
                            <td style="text-align:center;">
                                <span class="badge badge-red" style="font-weight:800; font-size:12px; padding:3px 8px;">{{ $defect->qty }}</span>
                            </td>
                            <td style="text-align:right; font-weight:800; font-size:12px; color:#0F172A;">
                                ₹{{ number_format($defect->total_cost, 2) }}
                                @if($defect->unit_cost > 0)
                                    <div style="font-size:10px; color:#64748B; font-weight:600;">@ ₹{{ number_format($defect->unit_cost, 2) }}</div>
                                @endif
                            </td>
                            <td style="max-width:260px;">
                                <div style="font-weight:700; color:#991B1B; font-size:12px;">{{ $defect->defect_reason }}</div>
                                @if($defect->notes)
                                    <div style="font-size:11px; color:#64748B; white-space:pre-wrap; margin-top:2px;">{{ $defect->notes }}</div>
                                @endif
                            </td>
                            <td style="text-align:center; white-space:nowrap;">
                                <span class="badge" style="background:{{ $statusStyle['bg'] }}; color:{{ $statusStyle['color'] }}; font-weight:700; font-size:11px;">
                                    {{ $statusStyle['label'] }}
                                </span>
                            </td>
                            <td style="text-align:center; white-space:nowrap;">
                                <button type="button" class="btn btn-outline btn-sm" style="font-size:11px; font-weight:700; padding:4px 8px; color:var(--brand-700); border-color:#CBD5E1;"
                                        data-id="{{ $defect->id }}"
                                        data-name="{{ $defect->item_name }}"
                                        data-status="{{ $defect->status }}"
                                        data-qty="{{ $defect->qty }}"
                                        onclick="openUpdateDefectiveStatusModal(this)">
                                    <i data-lucide="edit-3" style="width:12px;height:12px;"></i> Update Status
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr id="emptyDefectiveRow">
                            <td colspan="9" style="text-align:center; padding:36px; color:#94A3B8;">
                                <i data-lucide="shield-check" style="width:36px;height:36px; stroke-width:1.5; margin-bottom:8px; color:#16A34A;"></i>
                                <div style="font-weight:700; font-size:14px; color:#475569;">No Defective Items Recorded</div>
                                <div style="font-size:12px; margin-top:4px;">You can record defective accessories directly from any purchase invoice using the <strong style="color:#DC2626;">Return / Defect</strong> button, or through customer sales returns.</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- Defective Items Mobile View -->
                <div class="mobile-purchase-cards" style="display:none;" id="defectiveMobileCards">
                    @forelse($defectiveItems ?? [] as $defect)
                    @php
                        $statusStyle = match($defect->status) {
                            'returned_to_supplier' => ['bg' => '#EFF6FF', 'color' => '#1D4ED8', 'label' => 'Dispatched to Vendor'],
                            'replaced_by_supplier' => ['bg' => '#DCFCE7', 'color' => '#15803D', 'label' => 'Replaced by Vendor'],
                            'scrap_written_off'    => ['bg' => '#F1F5F9', 'color' => '#64748B', 'label' => 'Scrap / Written Off'],
                            default                => ['bg' => '#FEF3C7', 'color' => '#B45309', 'label' => 'Pending Vendor Return'],
                        };
                    @endphp
                    <div class="purchase-flat-row defective-data-row" data-status="{{ $defect->status }}">
                        <div class="row-line1">
                            <span class="inv-num" style="color:#991B1B;">
                                {{ $defect->qty }}x Defective
                            </span>
                            <span class="pay-badge" style="background:{{ $statusStyle['bg'] }}; color:{{ $statusStyle['color'] }};">
                                {{ $statusStyle['label'] }}
                            </span>
                            <div class="row-amount">₹{{ number_format($defect->total_cost, 2) }}</div>
                        </div>
                        <div class="row-line2">
                            <div class="cust-name">{{ $defect->item_name }}</div>
                            <div class="cust-phone">{{ \Carbon\Carbon::parse($defect->created_at)->format('d M Y') }} • {{ $defect->supplier_name ?: ($defect->customer_name ?: 'Vendor') }}</div>
                        </div>
                        <div class="row-line3">
                            <div class="items-summary" style="color:#991B1B;">
                                <strong>Reason:</strong> {{ $defect->defect_reason }}
                            </div>
                            <div class="row-actions">
                                <button type="button" class="compact-action-btn"
                                        data-id="{{ $defect->id }}"
                                        data-name="{{ $defect->item_name }}"
                                        data-status="{{ $defect->status }}"
                                        data-qty="{{ $defect->qty }}"
                                        onclick="openUpdateDefectiveStatusModal(this)"
                                        title="Update Status">
                                    <i data-lucide="edit-3" style="width:14px;height:14px;"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div style="text-align:center; padding:32px 16px; color:#94A3B8;">
                        <i data-lucide="shield-check" style="width:32px;height:32px; margin-bottom:8px; color:#16A34A;"></i>
                        <div style="font-weight:700; font-size:13px; color:#475569;">No Defective Items Recorded</div>
                    </div>
                    @endforelse
                </div>
            </div>
            <!-- End Defective Items Container -->
        </div>
    </div>

    <!-- Mobile Floating Action Button (FAB) — Quick Stock Entry -->
    <div class="mobile-fab-container">
        <div id="purchaseFabMenu" class="fab-dropup-menu" style="display: none;">
            <a href="{{ route('mobileshop.accessories.purchase') }}" class="fab-menu-item" style="color: var(--color-primary); text-decoration:none;">
                <i data-lucide="plus" style="width:16px;height:16px;"></i>
                <span>Restock Accessories &amp; Parts</span>
            </a>
        </div>
        <button type="button" class="btn-purchase-fab" onclick="togglePurchaseFabMenu(event)" title="Quick Actions">
            <i data-lucide="plus" id="purchaseFabIcon" style="width:22px;height:22px;transition:transform 0.2s ease;"></i>
        </button>
    </div>

    <!-- Mobile Date Filter Drawer -->
    <div id="purchaseDateFilterDrawer" style="display:none; position: fixed; inset: 0; z-index: 1050; background: rgba(15,23,42,0.5); backdrop-filter: blur(2px); align-items: flex-end; justify-content: center;">
        <div style="background: #FFFFFF; width: 100%; max-width: 500px; border-radius: 16px 16px 0 0; padding: 18px 20px; box-shadow: 0 -10px 30px rgba(0,0,0,0.15);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                <div style="font-weight: 800; font-size: 14px; color: #0F172A; display: flex; align-items: center; gap: 6px;">
                    <i data-lucide="calendar" style="width: 16px; height: 16px; color: #5E6AD2;"></i>
                    Filter Purchases by Date Range
                </div>
                <button type="button" onclick="closePurchaseDateFilterDrawer()" style="background: #F1F5F9; border: none; width: 28px; height: 28px; border-radius: 50%; color: #64748B; font-weight: 800; cursor: pointer; display: flex; align-items: center; justify-content: center;">✕</button>
            </div>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <div>
                    <label style="font-size: 11px; font-weight: 700; color: #64748B; margin-bottom: 4px; display: block;">From Date</label>
                    <input type="date" id="mobPurchaseFromDate" onchange="syncMobPurchaseDates()" class="form-control" style="width: 100%; font-size: 13px; font-weight: 600; padding: 8px 12px; border: 1px solid #CBD5E1; border-radius: 8px;">
                </div>
                <div>
                    <label style="font-size: 11px; font-weight: 700; color: #64748B; margin-bottom: 4px; display: block;">To Date</label>
                    <input type="date" id="mobPurchaseToDate" onchange="syncMobPurchaseDates()" class="form-control" style="width: 100%; font-size: 13px; font-weight: 600; padding: 8px 12px; border: 1px solid #CBD5E1; border-radius: 8px;">
                </div>
                <div style="display: flex; gap: 8px; margin-top: 6px;">
                    <button type="button" onclick="resetPurchaseDates(); closePurchaseDateFilterDrawer();" style="flex: 1; padding: 10px; border-radius: 8px; border: 1px solid #CBD5E1; background: #F8FAFC; color: #475569; font-size: 12px; font-weight: 700; cursor: pointer;">Reset</button>
                    <button type="button" onclick="closePurchaseDateFilterDrawer()" style="flex: 2; padding: 10px; border-radius: 8px; border: none; background: #5E6AD2; color: #FFFFFF; font-size: 12px; font-weight: 800; cursor: pointer;">Apply Filter</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- MODAL: View Purchased Invoice Items -->
    <!-- ══════════════════════════════════════════════════════════ -->
    <div id="viewPurchaseItemsModal" style="display:none; position: fixed; inset: 0; z-index: 1200; background: rgba(15,23,42,0.6); backdrop-filter: blur(4px); align-items:center; justify-content:center; padding: 16px;">
        <div class="card" style="max-width: 900px; width: 100%; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); max-height: 90vh; display:flex; flex-direction:column; overflow:hidden; border-radius:14px;">
            <div class="card-header" style="background:var(--brand-700); color:#fff; padding:16px 20px;">
                <div style="display:flex; justify-content:space-between; align-items:center; width:100%;">
                    <div>
                        <div class="card-title" style="color:#fff; display:flex; align-items:center; gap:8px;">
                            <i data-lucide="shopping-bag" style="width:18px;height:18px;"></i>
                            Purchase Invoice Details: <span id="lblModalPoNumber" style="font-family:monospace; color:#DDD6FE;"></span>
                        </div>
                        <div class="card-subtitle" style="color:rgba(255,255,255,0.85);" id="lblModalSupplierInfo">Supplier Details</div>
                    </div>
                    <button onclick="closeViewPurchaseModal()" style="background:none; border:none; color:#fff; font-size:22px; cursor:pointer; line-height:1;">✕</button>
                </div>
            </div>

            <div class="card-body" style="overflow-y:auto; padding:20px; display:flex; flex-direction:column; gap:16px;">
                <!-- Summary Details Header -->
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; background: #F8FAFC; border:1px solid #E2E8F0; border-radius: 10px; padding: 14px 16px;">
                    <div>
                        <div style="font-size:10px; font-weight:700; text-transform:uppercase; color:#64748B;">Order Date</div>
                        <div style="font-size:13px; font-weight:800; color:#0F172A; margin-top:2px;" id="lblModalOrderDate">—</div>
                    </div>
                    <div>
                        <div style="font-size:10px; font-weight:700; text-transform:uppercase; color:#64748B;">Supplier / Vendor</div>
                        <div style="font-size:13px; font-weight:800; color:#0F172A; margin-top:2px;" id="lblModalSupplierName">—</div>
                    </div>
                    <div>
                        <div style="font-size:10px; font-weight:700; text-transform:uppercase; color:#64748B;">Payment Status</div>
                        <div style="font-size:13px; font-weight:800; color:#15803D; margin-top:2px;" id="lblModalPaymentStatus">Paid in Full</div>
                    </div>
                    <div>
                        <div style="font-size:10px; font-weight:700; text-transform:uppercase; color:#64748B;">Invoice Total</div>
                        <div style="font-size:16px; font-weight:900; color:var(--brand-700); margin-top:2px;" id="lblModalTotalAmount">₹0.00</div>
                    </div>
                </div>

                <!-- Items Table -->
                <div style="border: 1px solid #E2E8F0; border-radius: 10px; overflow:hidden;">
                    <div style="background:#F1F5F9; padding:10px 16px; border-bottom:1px solid #E2E8F0; display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-weight:800; font-size:12px; color:#475569; text-transform:uppercase; letter-spacing:0.5px;">All Items Purchased in this Invoice</span>
                        <span id="lblModalItemCountBadge" class="badge badge-purple" style="font-size:11px; font-weight:800;">0 Items</span>
                    </div>
                    <div style="max-height:340px; overflow-y:auto;">
                        <table class="data-table" style="margin:0; border:none; width:100%;">
                            <thead style="position:sticky; top:0; background:#F8FAFC; z-index:2;">
                                <tr>
                                    <th style="width:36px; padding:10px 14px;">#</th>
                                    <th>Item Name & Description</th>
                                    <th>Category / Variant / IMEI</th>
                                    <th style="width:90px; text-align:center;">Qty</th>
                                    <th style="width:120px; text-align:right;">Unit Cost (₹)</th>
                                    <th style="width:130px; text-align:right; padding-right:16px;">Line Total (₹)</th>
                                </tr>
                            </thead>
                            <tbody id="viewPurchaseItemsTableBody">
                                <!-- Dynamically populated by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Footer Summary Bar -->
                <div style="display:flex; justify-content:space-between; align-items:center; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:10px; padding:12px 18px; flex-wrap:wrap; gap:12px;">
                    <div style="display:flex; gap:24px; align-items:center;">
                        <div>
                            <span style="font-size:11px; color:#64748B; font-weight:700; text-transform:uppercase;">Total Units Acquired</span>
                            <div style="font-size:15px; font-weight:800; color:#0F172A;" id="lblModalTotalUnits">0 units</div>
                        </div>
                        <div>
                            <span style="font-size:11px; color:#64748B; font-weight:700; text-transform:uppercase;">Grand Total Bill Value</span>
                            <div style="font-size:17px; font-weight:900; color:var(--brand-700);" id="lblModalGrandTotal">₹0.00</div>
                        </div>
                    </div>

                    <div style="display:flex; gap:8px;">
                        <button type="button" onclick="closeViewPurchaseModal()" class="btn btn-outline" style="font-weight:700;">Close</button>
                        <a href="#" id="lnkModalPrintInvoice" target="_blank" class="btn btn-primary" style="font-weight:800; display:inline-flex; align-items:center; gap:6px; background:var(--brand-700);">
                            <i data-lucide="printer" style="width:14px;height:14px;"></i> Print Formal Invoice
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- MODAL: Record Defective / Purchase Return -->
    <!-- ══════════════════════════════════════════════════════════ -->
    <div id="purchaseReturnDefectModal" style="display:none; position: fixed; inset: 0; z-index: 1200; background: rgba(15,23,42,0.6); backdrop-filter: blur(4px); align-items:center; justify-content:center; padding: 16px;">
        <div class="card" style="max-width: 520px; width: 100%; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); border-radius:14px; background:#fff; overflow:hidden;">
            <div class="card-header" style="background:#FEF2F2; border-bottom:1px solid #FECACA; padding:16px 20px; display:flex; justify-content:space-between; align-items:center;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div style="width:34px; height:34px; border-radius:8px; background:#FEE2E2; color:#DC2626; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <i data-lucide="alert-triangle" style="width:18px;height:18px;"></i>
                    </div>
                    <div>
                        <div class="card-title" style="color:#991B1B; font-weight:800; font-size:15px; margin:0;">Record Defective / Return Items</div>
                        <div id="lblDefectModalPoSub" style="font-size:11.5px; color:#B91C1C; font-weight:600; margin-top:2px;">PO # — Vendor</div>
                    </div>
                </div>
                <button type="button" onclick="closePurchaseDefectModal()" style="background:none; border:none; color:#991B1B; font-size:20px; cursor:pointer; line-height:1; padding:4px;">✕</button>
            </div>

            <form action="{{ route('mobileshop.purchase.defect') }}" method="POST" id="purchaseDefectForm">
                @csrf
                <input type="hidden" name="purchase_order_id" id="defectPurchaseOrderId" value="">

                <div class="card-body" style="padding:20px; display:flex; flex-direction:column; gap:14px;">
                    <!-- Select Item from Invoice -->
                    <div>
                        <label style="font-size:12px; font-weight:700; color:#334155; margin-bottom:6px; display:block;">Select Item from this Purchase Order *</label>
                        <select name="po_item_id" id="defectPoItemId" class="form-control" style="width:100%; padding:8px 12px; font-size:13px; border:1px solid #CBD5E1; border-radius:8px; font-weight:600; color:#0F172A;" onchange="onDefectItemSelectChange()" required>
                            <!-- Dynamically populated -->
                        </select>
                    </div>

                    <!-- Selected Item Info Card -->
                    <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:10px 14px; font-size:12px;">
                        <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                            <span style="color:#64748B;">Total Purchased Qty:</span>
                            <strong id="lblDefectItemPurchasedQty" style="color:#0F172A;">0 Units</strong>
                        </div>
                        <div style="display:flex; justify-content:space-between;">
                            <span style="color:#64748B;">Unit Purchase Cost:</span>
                            <strong id="lblDefectItemUnitCost" style="color:#0F172A;">₹0.00</strong>
                        </div>
                    </div>

                    <!-- Defect Quantity -->
                    <div>
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                            <label style="font-size:12px; font-weight:700; color:#334155; margin:0;">Defective Quantity *</label>
                            <span id="lblDefectMaxQtyNote" style="font-size:11px; color:#DC2626; font-weight:700;">Max: 1</span>
                        </div>
                        <input type="number" name="defect_qty" id="defectQtyInput" min="1" max="1" value="1" class="form-control" style="width:100%; padding:8px 12px; font-size:14px; font-weight:800; border:1px solid #CBD5E1; border-radius:8px;" required>
                    </div>

                    <!-- Reason for Defect -->
                    <div>
                        <label style="font-size:12px; font-weight:700; color:#334155; margin-bottom:6px; display:block;">Reason for Defect *</label>
                        <select name="defect_reason" id="defectReasonSelect" class="form-control" style="width:100%; padding:8px 12px; font-size:13px; border:1px solid #CBD5E1; border-radius:8px; font-weight:600; color:#0F172A;" required>
                            <option value="Dead on Arrival (DOA) / Not Working">Dead on Arrival (DOA) / Not Working</option>
                            <option value="Broken / Cracked / Physical Damage">Broken / Cracked / Physical Damage</option>
                            <option value="Touch / Display Faulty">Touch / Display Faulty</option>
                            <option value="Wrong Piece / Model Mismatch">Wrong Piece / Model Mismatch</option>
                            <option value="Manufacturing / Quality Defect">Manufacturing / Quality Defect</option>
                            <option value="Customer Warranty Return to Vendor">Customer Warranty Return to Vendor</option>
                            <option value="Other Defect">Other Defect</option>
                        </select>
                    </div>

                    <!-- Additional Notes -->
                    <div>
                        <label style="font-size:12px; font-weight:700; color:#334155; margin-bottom:6px; display:block;">Defect Notes / Remarks (Optional)</label>
                        <textarea name="notes" id="defectNotesInput" rows="2" placeholder="Reason" style="width:100%; padding:8px 12px; font-size:12px; border:1px solid #CBD5E1; border-radius:8px; resize:vertical; font-family:inherit;"></textarea>
                    </div>

                    <!-- Stock Adjustment Notice -->
                    <div style="background:#FFFBEB; border:1px solid #FDE68A; border-radius:8px; padding:10px 12px; display:flex; align-items:flex-start; gap:10px;">
                        <input type="checkbox" name="deduct_stock" id="defectDeductStockCheck" value="1" checked style="margin-top:3px; cursor:pointer;">
                        <label for="defectDeductStockCheck" style="font-size:11.5px; color:#92400E; font-weight:600; margin:0; cursor:pointer;">
                            <strong>Deduct from Sellable Stock:</strong> Automatically remove this defective quantity from current store inventory.
                        </label>
                    </div>
                </div>

                <div style="padding:14px 20px; border-top:1px solid #E2E8F0; background:#F8FAFC; display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" onclick="closePurchaseDefectModal()" class="btn btn-outline" style="font-size:12px; font-weight:700; padding:6px 14px;">Cancel</button>
                    <button type="submit" class="btn" style="background:#DC2626; color:#fff; border:none; font-size:12px; font-weight:800; padding:6px 18px; border-radius:6px; display:inline-flex; align-items:center; gap:6px; cursor:pointer;">
                        <i data-lucide="check" style="width:14px; height:14px;"></i> Confirm Defect Return
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- MODAL: Update Defective Item Status -->
    <!-- ══════════════════════════════════════════════════════════ -->
    <div id="updateDefectiveStatusModal" style="display:none; position: fixed; inset: 0; z-index: 1200; background: rgba(15,23,42,0.6); backdrop-filter: blur(4px); align-items:center; justify-content:center; padding: 16px;">
        <div class="card" style="max-width: 480px; width: 100%; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); border-radius:14px; background:#fff; overflow:hidden;">
            <div class="card-header" style="border-bottom:1px solid #E2E8F0; padding:16px 20px; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h3 style="margin:0; font-size:15px; font-weight:800; color:#0F172A;">Update Defective Item Status</h3>
                    <div id="lblStatusModalItemName" style="font-size:12px; color:#64748B; font-weight:600; margin-top:2px;">Item Name</div>
                </div>
                <button type="button" onclick="closeUpdateDefectiveStatusModal()" style="border:none; background:transparent; font-size:18px; color:#64748B; cursor:pointer; padding:4px 8px;">✕</button>
            </div>

            <form action="" method="POST" id="updateDefectiveStatusForm">
                @csrf
                <div style="padding:20px; display:flex; flex-direction:column; gap:14px;">
                    <div>
                        <label style="font-size:12px; font-weight:700; color:#334155; margin-bottom:6px; display:block;">Defective Item Status *</label>
                        <select name="status" id="defectStatusSelect" class="form-control" style="width:100%; padding:8px 12px; font-size:13px; border:1px solid #CBD5E1; border-radius:8px; font-weight:700;" onchange="onStatusSelectChange(this.value)" required>
                            <option value="pending_supplier_return">⚠️ Pending Return to Supplier</option>
                            <option value="returned_to_supplier">🚚 Returned / Dispatched to Supplier</option>
                            <option value="replaced_by_supplier">✅ Replaced by Supplier (New Piece Received)</option>
                            <option value="scrap_written_off">🗑️ Scrapped / Written Off (No Supplier Warranty)</option>
                        </select>
                    </div>

                    <div id="restockReplacementOption" style="display:none; background:#F0FDF4; border:1px solid #BBF7D0; border-radius:8px; padding:10px 12px;">
                        <label style="font-size:11.5px; color:#166534; font-weight:700; display:flex; align-items:center; gap:8px; margin:0; cursor:pointer;">
                            <input type="checkbox" name="restock_replacement" value="1" checked style="cursor:pointer;">
                            Add Replacement Piece to Current Sellable Stock
                        </label>
                    </div>

                    <div>
                        <label style="font-size:12px; font-weight:700; color:#334155; margin-bottom:6px; display:block;">Action Note / Tracking # (Optional)</label>
                        <input type="text" name="notes" id="statusNotesInput" placeholder="Notes" class="form-control" style="width:100%; padding:8px 12px; font-size:12px; border:1px solid #CBD5E1; border-radius:8px;">
                    </div>
                </div>

                <div style="padding:14px 20px; border-top:1px solid #E2E8F0; background:#F8FAFC; display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" onclick="closeUpdateDefectiveStatusModal()" class="btn btn-outline" style="font-size:12px; font-weight:700; padding:6px 14px;">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="font-size:12px; font-weight:800; padding:6px 18px; border-radius:6px;">Update Status</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Supplier Payment / Advance / Settlement -->
    <div id="paymentModal" style="display:none; position: fixed; inset: 0; z-index: 1200; background: rgba(15,23,42,0.45); backdrop-filter: blur(4px); align-items:center; justify-content:center; padding: 16px;">
        <div class="card" style="max-width: 460px; width: 100%; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); border-radius:14px; background:#fff;">
            <div class="card-header" style="border-bottom:1px solid #E2E8F0; padding:14px 18px; display:flex; justify-content:space-between; align-items:center;">
                <div class="card-title" style="font-weight:700; font-size:15px; color:#0F172A; display:flex; align-items:center; gap:8px;">
                    <i data-lucide="wallet" style="width:18px;height:18px;color:#5E6AD2;"></i>
                    Record Supplier Payment
                </div>
                <button type="button" onclick="closePaymentModal()" class="btn-icon" style="background:none; border:none; font-size:16px; cursor:pointer; color:#64748B;">✕</button>
            </div>
            <div class="card-body" style="padding:16px 18px;">
                <form action="{{ route('mobileshop.purchase_orders.payment') }}" method="POST">
                    @csrf
                    <input type="hidden" name="supplier_id" id="paySupplierId">
                    <input type="hidden" name="purchase_order_id" id="payPOId">

                    <div style="margin-bottom: 14px; padding: 12px 14px; background:#EFF6FF; border:1px solid #BFDBFE; border-radius:10px;">
                        <span style="font-size:10px; color:#1E40AF; text-transform:uppercase; font-weight:800;">Paying To</span>
                        <div style="font-size:14px; font-weight:800; color:#0F172A; margin-top:2px;" id="paySupplierName">-</div>
                    </div>

                    <div class="form-group" style="margin-bottom:12px;">
                        <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Select Target Supplier</label>
                        <select class="form-control" onchange="onSelectPaySupplier(this)" style="font-size:13px;">
                            @foreach($suppliers as $s)
                                <option value="{{ $s->id }}" data-name="{{ $s->name }}">{{ $s->name }} (Prepaid Wallet: ₹{{ number_format($s->credit_balance ?? 0, 2) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom:12px;">
                        <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Amount Sent (₹) *</label>
                        <input type="number" step="0.01" inputmode="decimal" name="amount" id="payAmount" required placeholder="Amount" class="form-control" style="font-size:16px; font-weight:800; color:#16A34A;">
                    </div>

                    <div class="form-group" style="margin-bottom:12px;">
                        <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Payment Mode *</label>
                        <select name="payment_mode" required class="form-control" style="font-weight:600; font-size:13px;">
                            <option value="bank_transfer">🏦 Bank Transfer / NEFT / RTGS</option>
                            <option value="upi">📱 UPI Transfer</option>
                            <option value="cheque">📝 Cheque</option>
                            <option value="cash">💵 Cash</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom:14px;">
                        <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Reference / UTR Number</label>
                        <input type="text" name="reference_no" placeholder="Reference" class="form-control" style="font-size:13px;">
                    </div>

                    <div class="modal-sticky-footer" style="display:flex; justify-content:flex-end; gap: 10px; padding-top: 14px; border-top: 1px solid #E2E8F0;">
                        <button type="button" onclick="closePaymentModal()" class="btn btn-outline" style="font-size:12px;">Cancel</button>
                        <button type="submit" class="btn btn-primary" style="font-size:12px;">Save Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL: Edit Supplier Details & Wallet -->
    <div id="editSupplierModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.55); z-index:1200; align-items:center; justify-content:center; backdrop-filter:blur(3px); padding:16px;">
        <div class="card" style="max-width: 440px; width: 100%; background:#fff; border-radius:14px; overflow:hidden; box-shadow:0 20px 40px rgba(0,0,0,0.25);">
            <div class="card-header" style="background:#5E6AD2; color:#fff; padding:14px 18px; display:flex; justify-content:space-between; align-items:center;">
                <div style="font-weight:700; font-size:15px; display:flex; align-items:center; gap:8px;">
                    <i data-lucide="truck" style="width:17px;height:17px;"></i> Edit Supplier & Wallet
                </div>
                <button type="button" onclick="closeEditSupplierModal()" style="background:none; border:none; color:#fff; cursor:pointer; font-size:20px; line-height:1;">&times;</button>
            </div>
            <div class="card-body" style="padding: 18px;">
                <form action="{{ route('mobileshop.supplier.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="supplier_id" id="editSupplierId">

                    <div class="form-group" style="margin-bottom:12px;">
                        <label class="form-label" style="font-weight:700; font-size:12px; margin-bottom:4px; display:block;">Supplier Name *</label>
                        <input type="text" name="name" id="editSupplierName" required class="form-control" style="font-size:13px;">
                    </div>

                    <div class="modal-form-grid-2">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-weight:700; font-size:12px; margin-bottom:4px; display:block;">Phone</label>
                            <input type="text" name="phone" id="editSupplierPhone" inputmode="numeric" class="form-control" style="font-size:13px;">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-weight:700; font-size:12px; margin-bottom:4px; display:block;">GSTIN</label>
                            <input type="text" name="gstin" id="editSupplierGstin" class="form-control" style="font-size:13px;">
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom:12px;">
                        <label class="form-label" style="font-weight:700; font-size:12px; margin-bottom:4px; display:block;">Address</label>
                        <input type="text" name="address" id="editSupplierAddress" class="form-control" style="font-size:13px;">
                    </div>

                    <div style="background:#F0FDF4; border:1px solid #BBF7D0; border-radius:8px; padding:12px; margin-bottom:12px;">
                        <label class="form-label" style="font-weight:700; font-size:12px; color:#166534; margin-bottom:4px; display:block;">Prepaid Advance Wallet (₹)</label>
                        <input type="number" step="0.01" min="0" inputmode="decimal" name="credit_balance" id="editSupplierCredit" class="form-control" style="font-weight:800; font-size:16px; color:#15803D;">
                        <div style="font-size:10.5px; color:#166534; margin-top:4px;">Direct balance modification records an audit transaction in the supplier credit ledger.</div>
                    </div>

                    <div class="form-group" style="margin-bottom:14px;">
                        <label class="form-label" style="font-weight:700; font-size:12px; margin-bottom:4px; display:block;">Adjustment Reason / Note</label>
                        <input type="text" name="adjustment_notes" class="form-control" placeholder="Notes" style="font-size:13px;">
                    </div>

                    <div class="modal-sticky-footer" style="display:flex; justify-content:flex-end; gap: 10px; padding-top: 14px; border-top: 1px solid #E2E8F0;">
                        <button type="button" onclick="closeEditSupplierModal()" class="btn btn-outline" style="font-size:12px;">Cancel</button>
                        <button type="submit" class="btn btn-primary" style="font-size:12px;">Update Supplier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>





@endsection

@push('scripts')
<script>
    function escapeHtml(str) {
        if (!str && str !== 0) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
    window.escapeHtml = escapeHtml;

    const companyId = {{ json_encode(company_id()) }};
    function openBulkRestockModal() {
        window.location.href = "{{ route('mobileshop.accessories.purchase') }}";
    }

    function closeBulkRestockModal() {
        // noop
    }

    /* ── Purchase FAB Menu Toggle ── */
    function togglePurchaseFabMenu(e) {
        if (e && e.stopPropagation) e.stopPropagation();
        var menu = document.getElementById('purchaseFabMenu');
        var icon = document.getElementById('purchaseFabIcon');
        if (!menu) return;
        var isOpen = menu.style.display === 'flex';
        menu.style.display = isOpen ? 'none' : 'flex';
        if (icon) {
            icon.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(45deg)';
        }
    }

    function closePurchaseFabMenu() {
        var menu = document.getElementById('purchaseFabMenu');
        var icon = document.getElementById('purchaseFabIcon');
        if (menu) menu.style.display = 'none';
        if (icon) {
            icon.style.transform = 'rotate(0deg)';
        }
    }

    document.addEventListener('click', function (e) {
        var fabCont = document.querySelector('.mobile-fab-container');
        var menu = document.getElementById('purchaseFabMenu');
        if (menu && menu.style.display !== 'none' && fabCont && !fabCont.contains(e.target)) {
            closePurchaseFabMenu();
        }
    });

    // Close purchase inline modals and FAB menu on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeViewPurchaseModal();
            closePurchaseFabMenu();
        }
    });

    function openPaymentModal(supplierId, supplierName) {
        const modal = document.getElementById('paymentModal');
        if (!modal) return;
        document.getElementById('paySupplierId').value = supplierId;
        document.getElementById('payPOId').value = '';
        document.getElementById('paySupplierName').textContent = supplierName + ' (Advance / Settlement)';
        document.getElementById('payAmount').value = '';
        modal.style.display = 'flex';
        if (window.lucide) window.lucide.createIcons();
    }
    function closePaymentModal() {
        const modal = document.getElementById('paymentModal');
        if (modal) modal.style.display = 'none';
    }
    function onSelectPaySupplier(select) {
        const opt = select.options[select.selectedIndex];
        document.getElementById('paySupplierId').value = select.value;
        document.getElementById('paySupplierName').textContent = (opt.dataset.name || opt.text) + ' (Advance / Settlement)';
    }

    function openEditSupplierModal(id, name, phone, gstin, address, credit) {
        const modal = document.getElementById('editSupplierModal');
        if (!modal) return;
        document.getElementById('editSupplierId').value = id;
        document.getElementById('editSupplierName').value = name;
        document.getElementById('editSupplierPhone').value = phone || '';
        document.getElementById('editSupplierGstin').value = gstin || '';
        document.getElementById('editSupplierAddress').value = address || '';
        document.getElementById('editSupplierCredit').value = credit || 0;
        modal.style.display = 'flex';
        if (window.lucide) window.lucide.createIcons();
    }
    function closeEditSupplierModal() {
        const modal = document.getElementById('editSupplierModal');
        if (modal) modal.style.display = 'none';
    }

    let currentPurchaseDatePreset = 'all';

    function calculatePurchaseDateRange(preset) {
        if (typeof window.getDateRangePreset === 'function') {
            try {
                const res = window.getDateRangePreset(preset);
                if (res && (res.from !== undefined || res.to !== undefined)) return res;
            } catch (e) { /* fallback below */ }
        }
        const now = new Date();
        const pad = n => (n < 10 ? '0' : '') + n;
        const toIso = d => d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
        const todayStr = toIso(now);
        const y = now.getFullYear();
        const m = now.getMonth();
        const d = now.getDate();

        switch (String(preset || '').toLowerCase()) {
            case 'today':
                return { from: todayStr, to: todayStr };
            case 'yesterday':
                const yest = new Date(y, m, d - 1);
                return { from: toIso(yest), to: toIso(yest) };
            case 'week':
            case '7days':
            case '7_days':
            case '7-days':
                const weekAgo = new Date(y, m, d - 6);
                return { from: toIso(weekAgo), to: todayStr };
            case 'month':
            case 'this_month':
            case 'this-month':
                const start = new Date(y, m, 1);
                const end = new Date(y, m + 1, 0);
                return { from: toIso(start), to: toIso(end) };
            case 'all':
            default:
                return { from: '', to: '' };
        }
    }

    function setPurchaseDatePreset(preset) {
        currentPurchaseDatePreset = preset;
        const mainFrom = document.getElementById('purchaseFromDate');
        const mainTo = document.getElementById('purchaseToDate');
        const mobFrom = document.getElementById('mobPurchaseFromDate');
        const mobTo = document.getElementById('mobPurchaseToDate');

        document.querySelectorAll('.purchase-date-pill').forEach(el => el.classList.remove('active'));
        const targetId = 'purchaseDateBtn_' + (preset === '7days' ? 'week' : preset);
        const btn = document.getElementById(targetId) || document.getElementById('purchaseDateBtn_' + preset);
        if (btn) btn.classList.add('active');

        const range = calculatePurchaseDateRange(preset);

        if (mainFrom) mainFrom.value = range.from || '';
        if (mainTo) mainTo.value = range.to || '';
        if (mobFrom) mobFrom.value = range.from || '';
        if (mobTo) mobTo.value = range.to || '';

        const indicator = document.getElementById('purchaseDateFilterActiveIndicator');
        if (indicator) {
            indicator.style.display = (range.from || range.to) ? 'inline-block' : 'none';
        }

        filterPurchaseTables();
    }

    let activePoTypeFilter = 'all';

    function setPurchaseTypeFilter(type) {
        activePoTypeFilter = type;
        document.getElementById('btnPoFilterAll')?.classList.remove('active');
        document.getElementById('btnPoFilterAccessories')?.classList.remove('active');

        if (type === 'all') document.getElementById('btnPoFilterAll')?.classList.add('active');
        else if (type === 'accessories') document.getElementById('btnPoFilterAccessories')?.classList.add('active');

        filterPurchaseTables();
    }

    function openViewPurchaseModal(btn) {
        let invoice = {};
        let items = [];
        try {
            invoice = JSON.parse(btn.getAttribute('data-invoice') || '{}');
            items = JSON.parse(btn.getAttribute('data-items') || '[]');
        } catch(e) {
            invoice = {};
            items = [];
        }

        document.getElementById('lblModalPoNumber').textContent = '#' + (invoice.po_number || 'PO-RECORD');
        document.getElementById('lblModalSupplierInfo').textContent = (invoice.supplier_name || 'Vendor') + (invoice.supplier_phone ? ' • Phone: ' + invoice.supplier_phone : '');
        document.getElementById('lblModalOrderDate').textContent = invoice.order_date || '—';
        document.getElementById('lblModalSupplierName').textContent = invoice.supplier_name || 'Vendor / Distributor';
        document.getElementById('lblModalPaymentStatus').textContent = (invoice.status === 'paid' || invoice.status === 'received') ? 'Paid in Full' : (invoice.status || 'Pending');
        document.getElementById('lblModalTotalAmount').textContent = '₹' + parseFloat(invoice.total_amount || 0).toLocaleString('en-IN', {minimumFractionDigits: 2});
        document.getElementById('lblModalItemCountBadge').textContent = items.length + ' ' + (items.length === 1 ? 'Item' : 'Items');

        const tbody = document.getElementById('viewPurchaseItemsTableBody');
        tbody.innerHTML = '';

        let totalUnits = 0;
        let grandTotal = 0;

        if (items.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; padding:24px; color:#94A3B8;">No line items found for this invoice.</td></tr>`;
        } else {
            items.forEach((item, idx) => {
                const qty = parseInt(item.qty || 1);
                const unitCost = parseFloat(item.unit_cost || 0);
                const lineTotal = parseFloat(item.line_total || (qty * unitCost));

                totalUnits += qty;
                grandTotal += lineTotal;

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td style="color:#64748B; font-weight:700; padding:10px 14px;">${idx + 1}</td>
                    <td>
                        <div style="font-weight:800; color:#0F172A; font-size:13px;">${escapeHtml(item.brand || '')} ${escapeHtml(item.model || 'Item')}</div>
                        ${item.variant ? `<div style="font-size:11px; color:#64748B;">${escapeHtml(item.variant)}</div>` : ''}
                    </td>
                    <td>
                        <span class="badge badge-gray" style="font-size:11px;">${escapeHtml(item.category || item.variant || 'Standard')}</span>
                    </td>
                    <td style="text-align:center; font-weight:800; color:#0F172A;">
                        ${qty}
                    </td>
                    <td style="text-align:right; font-weight:600; color:#64748B;">
                        ₹${unitCost.toLocaleString('en-IN', {minimumFractionDigits: 2})}
                    </td>
                    <td style="text-align:right; font-weight:900; color:#0F172A; padding-right:16px;">
                        ₹${lineTotal.toLocaleString('en-IN', {minimumFractionDigits: 2})}
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        document.getElementById('lblModalTotalUnits').textContent = totalUnits + ' units';
        document.getElementById('lblModalGrandTotal').textContent = '₹' + (grandTotal > 0 ? grandTotal : parseFloat(invoice.total_amount || 0)).toLocaleString('en-IN', {minimumFractionDigits: 2});

        const printLink = document.getElementById('lnkModalPrintInvoice');
        if (printLink && invoice.id) {
            printLink.href = `/${companyId}/mobileshop/invoice/purchase/${invoice.id}`;
        }

        document.getElementById('viewPurchaseItemsModal').style.display = 'flex';
        if (window.refreshIcons) window.refreshIcons();
        else if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons();
    }

    function closeViewPurchaseModal() {
        document.getElementById('viewPurchaseItemsModal').style.display = 'none';
    }

    let currentDefectInvoice = null;
    let currentDefectItems = [];

    function openPurchaseDefectModal(btn) {
        try {
            currentDefectInvoice = JSON.parse(btn.getAttribute('data-invoice') || '{}');
            currentDefectItems = JSON.parse(btn.getAttribute('data-items') || '[]');
        } catch(e) {
            currentDefectInvoice = {};
            currentDefectItems = [];
        }

        document.getElementById('defectPurchaseOrderId').value = currentDefectInvoice.id || '';
        document.getElementById('lblDefectModalPoSub').textContent = '#' + (currentDefectInvoice.po_number || '') + ' • ' + (currentDefectInvoice.supplier_name || 'Vendor');

        const select = document.getElementById('defectPoItemId');
        select.innerHTML = '';

        if (currentDefectItems.length === 0) {
            const opt = document.createElement('option');
            opt.value = '';
            opt.textContent = 'No items found on this invoice';
            select.appendChild(opt);
        } else {
            currentDefectItems.forEach((item) => {
                const opt = document.createElement('option');
                opt.value = item.id;
                const title = (item.brand || '') + ' ' + (item.model || 'Item') + (item.variant && item.variant !== 'Standard' ? ' (' + item.variant + ')' : '');
                opt.textContent = `${title} — Qty: ${item.qty} @ ₹${parseFloat(item.unit_cost || 0).toLocaleString('en-IN')}`;
                opt.setAttribute('data-qty', item.qty || 1);
                opt.setAttribute('data-cost', item.unit_cost || 0);
                select.appendChild(opt);
            });
        }

        onDefectItemSelectChange();
        document.getElementById('purchaseReturnDefectModal').style.display = 'flex';
        if (window.refreshIcons) window.refreshIcons();
        else if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons();
    }

    function onDefectItemSelectChange() {
        const select = document.getElementById('defectPoItemId');
        const selectedOpt = select.options[select.selectedIndex];
        if (!selectedOpt || !selectedOpt.value) return;

        const maxQty = parseInt(selectedOpt.getAttribute('data-qty') || 1);
        const unitCost = parseFloat(selectedOpt.getAttribute('data-cost') || 0);

        document.getElementById('lblDefectItemPurchasedQty').textContent = maxQty + ' Units';
        document.getElementById('lblDefectItemUnitCost').textContent = '₹' + unitCost.toLocaleString('en-IN', {minimumFractionDigits: 2});
        document.getElementById('lblDefectMaxQtyNote').textContent = 'Max: ' + maxQty + ' units';

        const qtyInput = document.getElementById('defectQtyInput');
        qtyInput.max = maxQty;
        qtyInput.value = Math.min(parseInt(qtyInput.value || 1), maxQty) || 1;
    }

    function closePurchaseDefectModal() {
        document.getElementById('purchaseReturnDefectModal').style.display = 'none';
    }

    function openUpdateDefectiveStatusModal(btn) {
        const id = btn.getAttribute('data-id');
        const name = btn.getAttribute('data-name');
        const status = btn.getAttribute('data-status');
        const form = document.getElementById('updateDefectiveStatusForm');

        form.action = `/${companyId}/mobileshop/defective-items/${id}/status`;
        document.getElementById('lblStatusModalItemName').textContent = name || 'Item';
        document.getElementById('defectStatusSelect').value = status || 'pending_supplier_return';
        onStatusSelectChange(status);

        document.getElementById('updateDefectiveStatusModal').style.display = 'flex';
        if (window.refreshIcons) window.refreshIcons();
        else if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons();
    }

    function closeUpdateDefectiveStatusModal() {
        document.getElementById('updateDefectiveStatusModal').style.display = 'none';
    }

    function onStatusSelectChange(val) {
        const opt = document.getElementById('restockReplacementOption');
        if (opt) {
            opt.style.display = (val === 'replaced_by_supplier') ? 'block' : 'none';
        }
    }

    function switchPurchaseView(view) {
        const purchasesBox = document.getElementById('purchaseInvoicesContainer');
        const defectiveBox = document.getElementById('defectiveItemsContainer');
        const tabPurchases = document.getElementById('tabBtnPurchases');
        const tabDefective = document.getElementById('tabBtnDefective');
        const heading = document.getElementById('purchaseRegistryHeading');

        if (view === 'defective') {
            if (purchasesBox) purchasesBox.style.display = 'none';
            if (defectiveBox) defectiveBox.style.display = 'block';
            if (heading) heading.textContent = 'Defective Items & Returns Registry';

            if (tabPurchases) {
                tabPurchases.style.background = 'transparent';
                tabPurchases.style.color = '#475569';
            }
            if (tabDefective) {
                tabDefective.style.background = '#DC2626';
                tabDefective.style.color = '#FFFFFF';
            }
        } else {
            if (purchasesBox) purchasesBox.style.display = 'block';
            if (defectiveBox) defectiveBox.style.display = 'none';
            if (heading) heading.textContent = 'Live Purchase Invoice Registry';

            if (tabPurchases) {
                tabPurchases.style.background = '#5E6AD2';
                tabPurchases.style.color = '#FFFFFF';
            }
            if (tabDefective) {
                tabDefective.style.background = 'transparent';
                tabDefective.style.color = '#DC2626';
            }
        }
        if (window.refreshIcons) window.refreshIcons();
        else if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons();
    }

    function toggleDefectiveRegistry() {
        switchPurchaseView('defective');
        const el = document.getElementById('defectiveItemsContainer');
        if (el) {
            el.scrollIntoView({ behavior: 'smooth' });
        }
    }

    let activeDefectiveStatusFilter = 'all';
    function filterDefectiveStatus(status, btn) {
        activeDefectiveStatusFilter = status;
        document.querySelectorAll('.defective-status-pill').forEach(b => {
            b.classList.remove('active');
            b.style.background = '#fff';
            if (b.textContent.includes('Pending')) b.style.color = '#B45309';
            else if (b.textContent.includes('Returned')) b.style.color = '#1D4ED8';
            else if (b.textContent.includes('Replaced')) b.style.color = '#15803D';
            else if (b.textContent.includes('Scrap')) b.style.color = '#64748B';
            else b.style.color = '#475569';
        });

        btn.classList.add('active');
        btn.style.background = '#991B1B';
        btn.style.color = '#fff';

        filterDefectiveTable();
    }

    function filterDefectiveTable() {
        const rows = document.querySelectorAll('.defective-data-row');
        rows.forEach(row => {
            const rowStatus = row.getAttribute('data-status') || '';
            const matchesStatus = (activeDefectiveStatusFilter === 'all') || (rowStatus === activeDefectiveStatusFilter);
            row.style.display = matchesStatus ? '' : 'none';
        });
    }

    function openPurchaseDateFilterDrawer() {
        const drawer = document.getElementById('purchaseDateFilterDrawer');
        if (drawer) {
            const mainFrom = document.getElementById('purchaseFromDate')?.value || '';
            const mainTo = document.getElementById('purchaseToDate')?.value || '';
            const mobFrom = document.getElementById('mobPurchaseFromDate');
            const mobTo = document.getElementById('mobPurchaseToDate');
            if (mobFrom) mobFrom.value = mainFrom;
            if (mobTo) mobTo.value = mainTo;
            drawer.style.display = 'flex';
        }
    }

    function closePurchaseDateFilterDrawer() {
        const drawer = document.getElementById('purchaseDateFilterDrawer');
        if (drawer) drawer.style.display = 'none';
    }

    function syncMobPurchaseDates() {
        const mobFrom = document.getElementById('mobPurchaseFromDate')?.value || '';
        const mobTo = document.getElementById('mobPurchaseToDate')?.value || '';
        const mainFrom = document.getElementById('purchaseFromDate');
        const mainTo = document.getElementById('purchaseToDate');
        if (mainFrom) mainFrom.value = mobFrom;
        if (mainTo) mainTo.value = mobTo;

        const indicator = document.getElementById('purchaseDateFilterActiveIndicator');
        if (indicator) {
            indicator.style.display = (mobFrom || mobTo) ? 'inline-block' : 'none';
        }

        document.querySelectorAll('.purchase-date-pill').forEach(el => el.classList.remove('active'));
        filterPurchaseTables();
    }

    function resetPurchaseDates() {
        const mainFrom = document.getElementById('purchaseFromDate');
        const mainTo = document.getElementById('purchaseToDate');
        const mobFrom = document.getElementById('mobPurchaseFromDate');
        const mobTo = document.getElementById('mobPurchaseToDate');
        if (mainFrom) mainFrom.value = '';
        if (mainTo) mainTo.value = '';
        if (mobFrom) mobFrom.value = '';
        if (mobTo) mobTo.value = '';

        const indicator = document.getElementById('purchaseDateFilterActiveIndicator');
        if (indicator) indicator.style.display = 'none';

        setPurchaseDatePreset('all');
    }

    function clearPurchaseSearch() {
        const input = document.getElementById('purchaseSearchInput');
        if (input) {
            input.value = '';
            const btn = document.getElementById('btnClearPurchaseSearch');
            if (btn) btn.style.display = 'none';
            filterPurchaseTables();
        }
    }

    function onPurchaseCustomDateChange() {
        document.querySelectorAll('.purchase-date-pill').forEach(el => el.classList.remove('active'));
        const indicator = document.getElementById('purchaseDateFilterActiveIndicator');
        const fromDate = document.getElementById('purchaseFromDate')?.value || '';
        const toDate = document.getElementById('purchaseToDate')?.value || '';
        if (indicator) {
            indicator.style.display = (fromDate || toDate) ? 'inline-block' : 'none';
        }
        filterPurchaseTables();
    }

    function filterPurchaseTables() {
        const term = (document.getElementById('purchaseSearchInput')?.value || '').toLowerCase().trim();
        const fromDate = document.getElementById('purchaseFromDate')?.value || '';
        const toDate = document.getElementById('purchaseToDate')?.value || '';

        const clearBtn = document.getElementById('btnClearPurchaseSearch');
        if (clearBtn) {
            clearBtn.style.display = term ? 'inline-block' : 'none';
        }

        let visibleCount = 0;

        // Desktop Table Rows
        document.querySelectorAll('tr.purchase-invoice-row').forEach(row => {
            const rowText = row.textContent.toLowerCase();
            const rawDate = (row.dataset.date || '').trim();
            const cleanRowDate = rawDate.length >= 10 ? rawDate.slice(0, 10) : rawDate;
            const rowType = row.dataset.type || '';

            const matchesType = (activePoTypeFilter === 'all') || (rowType === activePoTypeFilter);
            const matchesText = !term || rowText.includes(term);
            let matchesDate = true;

            if (fromDate) {
                matchesDate = matchesDate && (cleanRowDate !== '' && cleanRowDate >= fromDate);
            }
            if (toDate) {
                matchesDate = matchesDate && (cleanRowDate !== '' && cleanRowDate <= toDate);
            }

            const isVisible = matchesType && matchesText && matchesDate;
            row.dataset.mobiHidden = isVisible ? '0' : '1';
            row.style.display = isVisible ? '' : 'none';
            if (isVisible) visibleCount++;
        });

        // Mobile Flat Cards
        let visibleMobileCount = 0;
        document.querySelectorAll('.purchase-flat-row').forEach(row => {
            const rowText = row.textContent.toLowerCase();
            const rawDate = (row.dataset.date || '').trim();
            const cleanRowDate = rawDate.length >= 10 ? rawDate.slice(0, 10) : rawDate;
            const rowType = row.dataset.type || '';

            const matchesType = (activePoTypeFilter === 'all') || (rowType === activePoTypeFilter);
            const matchesText = !term || rowText.includes(term);
            let matchesDate = true;

            if (fromDate) {
                matchesDate = matchesDate && (cleanRowDate !== '' && cleanRowDate >= fromDate);
            }
            if (toDate) {
                matchesDate = matchesDate && (cleanRowDate !== '' && cleanRowDate <= toDate);
            }

            const isCardVisible = matchesType && matchesText && matchesDate;
            row.dataset.mobiHidden = isCardVisible ? '0' : '1';
            row.style.display = isCardVisible ? '' : 'none';
            if (isCardVisible) visibleMobileCount++;
        });

        // Empty state toggles
        const emptyRow = document.getElementById('purchaseEmptyFilterRow');
        if (emptyRow) {
            emptyRow.style.display = (visibleCount === 0) ? '' : 'none';
        }
        const mobEmptyRow = document.getElementById('purchaseMobileEmptyFilterRow');
        if (mobEmptyRow) {
            mobEmptyRow.style.display = (visibleMobileCount === 0) ? 'block' : 'none';
        }

        if (window.purchaseInvoicesPager && typeof window.purchaseInvoicesPager.refresh === 'function') {
            window.purchaseInvoicesPager.refresh(true);
        }

        const badge = document.getElementById('purchaseVisibleCountBadge');
        if (badge) {
            badge.textContent = `${visibleCount} invoices showing`;
        }
    }

    function initPurchasePage() {
        if (window.setupMobiTablePagination) {
            window.purchaseInvoicesPager = window.setupMobiTablePagination({
                tableId: 'purchaseInvoicesTable',
                cardsContainerId: 'purchaseMobileCards',
                paginationContainerId: 'purchaseInvoicesPagination',
                rowSelector: 'tbody tr.purchase-invoice-row',
                cardSelector: '.purchase-flat-row',
                pageSize: 25,
                itemName: 'purchase invoices'
            });
        }
        filterPurchaseTables();
        if (window.refreshIcons) window.refreshIcons();
        else if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons();

        var urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('action') === 'restock' || urlParams.get('restock') === '1') {
            openBulkRestockModal();
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPurchasePage);
    } else {
        initPurchasePage();
    }
</script>
@endpush
