@extends('mobileshop.layout')

@php
    $purchasePageTitle = match($niche ?? 'admin') {
        'phones'      => 'New Phones Purchase',
        'secondhand'  => 'Buyback & Pre-Owned Intake',
        'accessories' => 'Accessories & Parts Restock',
        'covers'      => 'Cover & Tempered Glass Restock',
        'repairs'     => 'Parts Consumption',
        default       => 'Purchase & Stock Inflow Hub',
    };
@endphp

@section('title', $purchasePageTitle . ' — Maurya Mobile ERP')
@section('page-title', $purchasePageTitle)

@section('page-actions')
    <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        @if($canAddPhones ?? false)
        <a href="{{ route('mobileshop.purchase.create') }}" class="btn btn-primary btn-sm">
            <i data-lucide="plus" style="width:14px;height:14px;"></i> Register Purchase (Bulk)
        </a>
        <button type="button" onclick="openPurchaseAddMobileModal()" class="btn btn-outline btn-sm">
            <i data-lucide="smartphone" style="width:14px;height:14px;"></i> Add Phone Stock
        </button>
        @endif
        @if($canAddSecondhand ?? false)
        <button type="button" onclick="openPurchaseBuybackModal()" class="btn btn-outline btn-sm">
            <i data-lucide="refresh-cw" style="width:14px;height:14px;"></i> Register Buyback
        </button>
        @endif
        @if(($canAddAccessories ?? false) || ($canAddCovers ?? false))
        <a href="{{ route('mobileshop.accessories.purchase') }}" class="btn btn-outline btn-sm" style="color: #7C3AED; border-color: #DDD6FE;">
            <i data-lucide="sparkles" style="width:14px;height:14px;"></i> Bulk Accessories & Invoice Scan
        </a>
        <button type="button" onclick="openPurchaseAddPartModal()" class="btn btn-outline btn-sm">
            <i data-lucide="scan-line" style="width:14px;height:14px;"></i> Restock Part (Quick)
        </button>
        @endif
        @if(($isAdmin ?? false) || ($canAddPhones ?? false) || auth()->user()->hasRole('sales-staff') || auth()->user()->can('read-mobileshop-procurement'))
        <button type="button" onclick="openPaymentModal({{ $suppliers->first()->id ?? 0 }}, '{{ addslashes($suppliers->first()->name ?? 'Primary Supplier') }}')" class="btn btn-outline btn-sm">
            <i data-lucide="wallet" style="width:14px;height:14px;"></i> Supplier Payment / Advance
        </button>
        <a href="{{ route('mobileshop.emi.ledger') }}" class="btn btn-outline btn-sm" style="color:#2563EB; border-color:#BFDBFE;">
            <i data-lucide="building-2" style="width:14px;height:14px;"></i> EMI Ledger
        </a>
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
        #purchaseAddMobileModal,
        #purchaseBuybackModal,
        #purchaseAddPartModal,
        #paymentModal,
        #editSupplierModal {
            align-items: flex-end !important;
            padding: 0 !important;
        }
        #purchaseAddMobileModal .card,
        #purchaseBuybackModal .card,
        #purchaseAddPartModal .card,
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
        <div class="stat-strip-item">
            <span class="stat-label">Units</span>
            <span class="stat-val" style="color:#15803D;">{{ number_format($totalUnitsPurchased) }}</span>
        </div>
    </div>

    <!-- Top KPI Cards (Desktop View) -->
    <div class="kpi-grid">
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
    </div>

    <!-- Master Live Purchase Invoice Registry (Single un-nested container) -->
    <div class="card purchase-registry-card">
        <div class="purchase-header-container">
            <div class="purchase-header-title-box flex items-center gap-2">
                <div class="w-6 h-6 rounded-sm bg-primary-tint text-primary flex items-center justify-center flex-shrink-0">
                    <i data-lucide="file-spreadsheet" style="width:14px;height:14px;"></i>
                </div>
                <div>
                    <h2 class="text-xs font-semibold text-ink leading-tight m-0">Live Purchase Invoice Registry</h2>
                    <span id="purchaseVisibleCountBadge" style="display:none;"></span>
                </div>
            </div>

            <!-- Search Bar with Embedded Date Filter Trigger -->
            <div class="purchase-search-wrapper relative">
                <div class="search-bar purchase-search-box">
                    <i data-lucide="search"></i>
                    <input type="text" id="purchaseSearchInput" oninput="filterPurchaseTables()" placeholder="Search invoice, supplier, item..." class="purchase-search-input">
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
                <!-- Categories (Scoped to User Permissions & Niche) -->
                @php
                    $showPhonePill = $isAdmin || ($canAddPhones ?? false) || ($niche ?? '') === 'phones';
                    $showPartsPill = $isAdmin || ($canAddAccessories ?? false) || ($canAddCovers ?? false) || in_array($niche ?? '', ['accessories', 'covers']);
                    $showBuybackPill = $isAdmin || ($canAddSecondhand ?? false) || ($niche ?? '') === 'secondhand';
                    $availablePillCount = ($showPhonePill ? 1 : 0) + ($showPartsPill ? 1 : 0) + ($showBuybackPill ? 1 : 0);
                @endphp

                @if($availablePillCount > 1)
                    <button type="button" onclick="setPurchaseTypeFilter('all')" id="btnPoFilterAll" class="filter-pill purchase-pill active">
                        All ({{ count($purchaseInvoices) }})
                    </button>
                    @if($showPhonePill)
                    <button type="button" onclick="setPurchaseTypeFilter('phones')" id="btnPoFilterPhones" class="filter-pill purchase-pill">
                        📱 Phones ({{ $purchaseInvoices->filter(fn($i) => str_contains($i->po_number, 'PHONES') || str_contains($i->po_number, 'PO-2026'))->count() }})
                    </button>
                    @endif
                    @if($showPartsPill)
                    <button type="button" onclick="setPurchaseTypeFilter('accessories')" id="btnPoFilterAccessories" class="filter-pill purchase-pill">
                        📦 Parts ({{ $purchaseInvoices->filter(fn($i) => str_contains($i->po_number, 'INV-') || str_contains($i->po_number, 'RESTOCK'))->count() }})
                    </button>
                    @endif
                    @if($showBuybackPill)
                    <button type="button" onclick="setPurchaseTypeFilter('buyback')" id="btnPoFilterBuyback" class="filter-pill purchase-pill">
                        🔄 Buybacks ({{ $purchaseInvoices->filter(fn($i) => str_contains($i->po_number, 'BUYBACK'))->count() }})
                    </button>
                    @endif
                    <div style="width:1px; height:18px; background:var(--color-hairline); margin:0 4px; flex-shrink:0;"></div>
                @endif

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
            <table class="data-table" id="purchaseInvoicesTable" style="margin:0; width:100%;">
                <thead style="background:#F8FAFC;">
                    <tr>
                        <th style="padding:12px 16px;">Date</th>
                        <th>Invoice / PO #</th>
                        <th>Supplier / Vendor</th>
                        <th>Type</th>
                        <th>Purchased Items</th>
                        <th>Payment Status</th>
                        <th style="text-align:right;">Invoice Amount (₹)</th>
                        <th style="text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseInvoices as $inv)
                    @php
                        $poType = 'accessories';
                        $badgeLabel = 'Wholesale Parts';
                        $badgeClass = 'badge-blue';
                        if (str_contains($inv->po_number, 'PHONES') || str_contains($inv->po_number, 'PO-2026')) {
                            $poType = 'phones';
                            $badgeLabel = '📱 Smartphones';
                            $badgeClass = 'badge-green';
                        } elseif (str_contains($inv->po_number, 'BUYBACK')) {
                            $poType = 'buyback';
                            $badgeLabel = '🔄 Buyback';
                            $badgeClass = 'badge-purple';
                        }

                        // Preview text of first 2 items
                        $itemPreviews = $inv->items->take(2)->map(function($i) {
                            return $i->brand . ' ' . $i->model . ($i->qty > 1 ? " (x{$i->qty})" : "");
                        })->implode(', ');
                        if ($inv->item_count > 2) {
                            $itemPreviews .= ' +' . ($inv->item_count - 2) . ' more';
                        }
                    @endphp
                    <tr class="purchase-invoice-row purchase-data-row" data-type="{{ $poType }}" data-date="{{ \Carbon\Carbon::parse($inv->order_date)->format('Y-m-d') }}">
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
                            <span class="badge {{ $badgeClass }}" style="font-size:11px; font-weight:700;">{{ $badgeLabel }}</span>
                        </td>
                        <td>
                            <div style="display:inline-flex; align-items:center; gap:6px; margin-bottom:2px;">
                                <span class="badge badge-gray" style="font-weight:800; font-size:11px;">{{ $inv->item_count }} {{ Str::plural('Item', $inv->item_count) }}</span>
                                <span style="font-size:11px; font-weight:700; color:#15803D;">({{ $inv->total_units }} Units)</span>
                            </div>
                            <div style="font-size:11px; color:#64748B; max-width:280px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
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
                        <td style="text-align:center; white-space:nowrap;">
                            <div style="display:inline-flex; gap:6px; align-items:center;">
                                <button type="button" class="btn btn-outline btn-sm" style="padding:4px 10px; font-weight:700; font-size:11px; display:inline-flex; align-items:center; gap:4px; color:var(--brand-700); border-color:#CBD5E1;"
                                        data-invoice='@json($inv)'
                                        data-items='@json($inv->items)'
                                        onclick="openViewPurchaseModal(this)">
                                    <i data-lucide="eye" style="width:13px;height:13px;"></i> View Items
                                </button>
                                <a href="{{ route('mobileshop.purchase.invoice', ['id' => $inv->id]) }}" class="btn btn-outline btn-sm" style="padding:4px 8px; font-size:11px; font-weight:700; color:#475569; border-color:#CBD5E1;" title="Print Full Purchase Invoice">
                                    <i data-lucide="printer" style="width:13px;height:13px;"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align:center; padding:32px; color:#94A3B8;">
                            <i data-lucide="inbox" style="width:36px;height:36px; stroke-width:1.5; margin-bottom:8px;"></i>
                            <div style="font-weight:700; font-size:14px; color:#475569;">No purchase invoices recorded yet</div>
                            <div style="font-size:12px;">Add phone stock or import wholesale parts bills to start recording purchase invoices.</div>
                        </td>
                    </tr>
                    @endforelse
                    <tr id="purchaseEmptyFilterRow" style="display:none;">
                        <td colspan="7" style="text-align:center; padding:36px 16px; color:#64748B;">
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

            <!-- ══════════════════════════════════════════════════════════ -->
            <!-- MOBILE FLAT LIST VIEW (Displayed on mobile screens < 768px)-->
            <!-- ══════════════════════════════════════════════════════════ -->
            <div id="purchaseMobileCards" class="mobile-purchase-cards">
                @forelse($purchaseInvoices as $inv)
                    @php
                        $poType = 'accessories';
                        $badgeLabel = 'Parts';
                        $badgeBg = '#EFF6FF';
                        $badgeColor = '#1D4ED8';
                        if (str_contains($inv->po_number, 'PHONES') || str_contains($inv->po_number, 'PO-2026')) {
                            $poType = 'phones';
                            $badgeLabel = 'Phones';
                            $badgeBg = '#DCFCE7';
                            $badgeColor = '#15803D';
                        } elseif (str_contains($inv->po_number, 'BUYBACK')) {
                            $poType = 'buyback';
                            $badgeLabel = 'Buyback';
                            $badgeBg = '#F5F3FF';
                            $badgeColor = '#7C3AED';
                        }

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
                    <div class="purchase-flat-row purchase-data-row" data-type="{{ $poType }}" data-date="{{ \Carbon\Carbon::parse($inv->order_date)->format('Y-m-d') }}">
                        <!-- Row 1: PO # + Category Badge + Payment Status + Total Amount -->
                        <div class="row-line1">
                            <a href="{{ route('mobileshop.purchase.invoice', ['id' => $inv->id]) }}" class="inv-num">
                                #{{ $inv->po_number }}
                            </a>
                            <span class="pay-badge" style="background:{{ $badgeBg }}; color:{{ $badgeColor }};">
                                {{ $badgeLabel }}
                            </span>
                            <span class="pay-badge" style="background:{{ $statusBg }}; color:{{ $statusColor }};">
                                {{ $statusLabel }}
                            </span>
                            <div class="row-amount">₹{{ number_format($inv->total_amount, 2) }}</div>
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
                                <a href="{{ route('mobileshop.purchase.invoice', ['id' => $inv->id]) }}" class="compact-action-btn" title="Print Invoice">
                                    <i data-lucide="printer" style="width:15px;height:15px;"></i>
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
    </div>

    <!-- Mobile Floating Action Button (FAB) — Quick Stock Entry -->
    <div class="mobile-fab-container">
        <div id="purchaseFabMenu" class="fab-dropup-menu" style="display: none;">
            @if(($isAdmin ?? false) || ($canAddPhones ?? false))
            <button type="button" class="fab-menu-item" style="color: #5E6AD2;" onclick="closePurchaseFabMenu(); openPurchaseAddMobileModal();">
                <i data-lucide="smartphone" style="width:16px;height:16px;"></i>
                <span>Add Phone Stock (Quick)</span>
            </button>
            <a href="{{ route('mobileshop.purchase.create') }}" class="fab-menu-item" style="color: #4338CA; text-decoration:none;">
                <i data-lucide="truck" style="width:16px;height:16px;"></i>
                <span>Bulk Phones Inward</span>
            </a>
            @endif
            @if(($isAdmin ?? false) || ($canAddAccessories ?? false) || ($canAddCovers ?? false))
            <button type="button" class="fab-menu-item" style="color: #059669;" onclick="closePurchaseFabMenu(); openPurchaseAddPartModal();">
                <i data-lucide="package" style="width:16px;height:16px;"></i>
                <span>Add Part (Quick)</span>
            </button>
            <a href="{{ route('mobileshop.accessories.purchase') }}" class="fab-menu-item" style="color: #7C3AED; text-decoration:none;">
                <i data-lucide="sparkles" style="width:16px;height:16px;"></i>
                <span>Bulk Parts & Invoice Scan</span>
            </a>
            @endif
            @if(($isAdmin ?? false) || ($canAddSecondhand ?? false))
            <button type="button" class="fab-menu-item" style="color: #EA580C;" onclick="closePurchaseFabMenu(); openPurchaseBuybackModal();">
                <i data-lucide="refresh-cw" style="width:16px;height:16px;"></i>
                <span>Register Buyback</span>
            </button>
            @endif
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
                        <input type="number" step="0.01" inputmode="decimal" name="amount" id="payAmount" required placeholder="0.00" class="form-control" style="font-size:16px; font-weight:800; color:#16A34A;">
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
                        <input type="text" name="reference_no" placeholder="e.g. UTR-948102948" class="form-control" style="font-size:13px;">
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
                        <input type="text" name="adjustment_notes" class="form-control" placeholder="e.g. Ledger reconciliation" style="font-size:13px;">
                    </div>

                    <div class="modal-sticky-footer" style="display:flex; justify-content:flex-end; gap: 10px; padding-top: 14px; border-top: 1px solid #E2E8F0;">
                        <button type="button" onclick="closeEditSupplierModal()" class="btn btn-outline" style="font-size:12px;">Cancel</button>
                        <button type="submit" class="btn btn-primary" style="font-size:12px;">Update Supplier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════ --}}
    {{-- PURCHASE-PAGE INLINE MODAL: Add Brand New Phone to Stock --}}
    {{-- ══════════════════════════════════════════════════════════ --}}
    <div id="purchaseAddMobileModal" style="display:none; position: fixed; inset: 0; z-index: 1300; background: rgba(15,23,42,0.5); backdrop-filter: blur(4px); align-items:center; justify-content:center; padding: 16px;">
        <div class="card" style="max-width: 520px; width: 100%; max-height:90vh; overflow-y:auto; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); border-radius:14px; background:#fff;">
            <div class="card-header" style="border-bottom:1px solid #E2E8F0; padding:14px 18px; display:flex; justify-content:space-between; align-items:center;">
                <div class="card-title" style="font-weight:700; font-size:15px; color:#0F172A; display:flex; align-items:center; gap:8px;">
                    <i data-lucide="smartphone" style="width:18px;height:18px;color:#2563EB;"></i>
                    Add Brand New Mobile to Stock
                </div>
                <button type="button" onclick="closePurchaseAddMobileModal()" class="btn-icon" style="background:none; border:none; font-size:16px; cursor:pointer; color:#64748B;">✕</button>
            </div>
            <div class="card-body" style="padding:16px 18px;">
                <form action="{{ route('mobileshop.new_mobiles.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="redirect_to" value="{{ route('mobileshop.purchase') }}">

                    <div style="margin-bottom: 14px; background:#F8FAFC; border:1px dashed #CBD5E1; border-radius:10px; padding:12px; text-align:center;">
                        <div id="pAMPhotoPreviewBox" style="display:none; margin-bottom:8px;">
                            <img id="pAMPreviewImg" src="" alt="Preview" style="max-height:120px; border-radius:8px; object-fit:contain; border:1px solid #E2E8F0;">
                        </div>
                        <label style="display:inline-flex; align-items:center; gap:6px; cursor:pointer; font-size:12px; font-weight:700; color:#2563EB; background:#EFF6FF; padding:6px 14px; border-radius:8px; border:1px solid #BFDBFE;">
                            <i data-lucide="camera" style="width:14px;height:14px;"></i> Upload Phone / Box Photo
                            <input type="file" name="photo" accept="image/*" style="display:none;" onchange="previewSelectedPhoto(this, 'pAMPreviewImg', 'pAMPhotoPreviewBox')">
                        </label>
                        <div style="font-size:10px; color:#64748B; margin-top:4px;">JPG, PNG, WebP up to 5MB</div>
                    </div>

                    <div class="form-row modal-form-grid-2" style="margin-bottom: 12px;">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Brand *</label>
                            <input type="text" name="brand" placeholder="e.g. Samsung / Apple" required class="form-control" style="font-size:13px;">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Model *</label>
                            <input type="text" name="model" placeholder="e.g. Galaxy S24 Ultra" required class="form-control" style="font-size:13px;">
                        </div>
                    </div>

                    <div class="form-row modal-form-grid-2" style="margin-bottom: 12px;">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Color</label>
                            <input type="text" name="color" placeholder="e.g. Titanium Black" class="form-control" style="font-size:13px;">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">RAM &amp; Storage</label>
                            <input type="text" name="storage" placeholder="e.g. 12GB / 256GB" class="form-control" style="font-size:13px;">
                        </div>
                    </div>

                    <div class="form-row modal-form-grid-2" style="margin-bottom: 12px;">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Primary IMEI *</label>
                            <input type="text" name="imei_1" required placeholder="15-digit IMEI" inputmode="numeric" class="form-control" style="font-family:monospace; font-weight:700; font-size:13px;">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">IMEI 2 (Optional)</label>
                            <input type="text" name="imei_2" placeholder="Optional" inputmode="numeric" class="form-control" style="font-family:monospace; font-size:13px;">
                        </div>
                    </div>

                    <div class="form-row modal-form-grid-2" style="margin-bottom: 14px;">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Purchase Cost (₹) *</label>
                            <input type="number" step="0.01" name="purchase_cost" required placeholder="0.00" inputmode="decimal" class="form-control" style="font-size:13px;">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Selling Price (₹) *</label>
                            <input type="number" step="0.01" name="selling_price" required placeholder="0.00" inputmode="decimal" class="form-control" style="font-weight:700; color:#16A34A; font-size:13px;">
                        </div>
                    </div>

                    <div class="modal-sticky-footer" style="display:flex; justify-content:flex-end; gap: 10px; padding-top: 14px; border-top: 1px solid #E2E8F0;">
                        <button type="button" onclick="closePurchaseAddMobileModal()" class="btn btn-outline" style="font-size:12px;">Cancel</button>
                        <button type="submit" class="btn btn-primary" style="font-size:12px;">Add Phone to Inventory</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════ --}}
    {{-- PURCHASE-PAGE INLINE MODAL: Customer Device Buyback --}}
    {{-- ══════════════════════════════════════════════════════════ --}}
    <div id="purchaseBuybackModal" style="display:none; position: fixed; inset: 0; z-index: 1300; background: rgba(15,23,42,0.5); backdrop-filter: blur(4px); align-items:center; justify-content:center; padding: 16px;">
        <div class="card" style="max-width: 540px; width: 100%; max-height: 90vh; overflow-y:auto; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); border-radius:14px; background:#fff;">
            <div class="card-header" style="border-bottom:1px solid #E2E8F0; padding:14px 18px; display:flex; justify-content:space-between; align-items:center;">
                <div class="card-title" style="font-weight:700; font-size:15px; color:#0F172A; display:flex; align-items:center; gap:8px;">
                    <i data-lucide="refresh-cw" style="width:18px;height:18px;color:#EA580C;"></i>
                    Customer Device Buyback Intake
                </div>
                <button type="button" onclick="closePurchaseBuybackModal()" class="btn-icon" style="background:none; border:none; font-size:16px; cursor:pointer; color:#64748B;">✕</button>
            </div>
            <div class="card-body" style="padding:16px 18px;">
                <form action="{{ route('mobileshop.second_hand.buyback') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="redirect_to" value="{{ route('mobileshop.purchase') }}">

                    <div style="margin-bottom: 14px; background:#F8FAFC; border:1px dashed #CBD5E1; border-radius:10px; padding:12px; text-align:center;">
                        <div id="pBBPhotoPreviewBox" style="display:none; margin-bottom:8px;">
                            <img id="pBBPreviewImg" src="" alt="Preview" style="max-height:100px; border-radius:8px; object-fit:contain; border:1px solid #E2E8F0;">
                        </div>
                        <label style="display:inline-flex; align-items:center; gap:6px; cursor:pointer; font-size:12px; font-weight:700; color:#EA580C; background:#FFF7ED; padding:6px 14px; border-radius:8px; border:1px solid #FED7AA;">
                            <i data-lucide="camera" style="width:14px;height:14px;"></i> Upload Device Photo
                            <input type="file" name="photo" accept="image/*" style="display:none;" onchange="previewSelectedPhoto(this, 'pBBPreviewImg', 'pBBPhotoPreviewBox')">
                        </label>
                    </div>

                    <div class="form-row modal-form-grid-2" style="margin-bottom: 12px;">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Brand *</label>
                            <input type="text" name="brand" placeholder="e.g. Apple" required class="form-control" style="font-size:13px;">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Model *</label>
                            <input type="text" name="model" placeholder="e.g. iPhone 13 Pro" required class="form-control" style="font-size:13px;">
                        </div>
                    </div>

                    <div class="form-row modal-form-grid-3" style="margin-bottom: 12px;">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Color</label>
                            <input type="text" name="color" placeholder="Blue" class="form-control" style="font-size:13px;">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Storage</label>
                            <input type="text" name="storage" placeholder="128GB" class="form-control" style="font-size:13px;">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Battery %</label>
                            <input type="number" name="battery_health" placeholder="88" inputmode="numeric" class="form-control" style="font-size:13px;">
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom:12px;">
                        <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">IMEI 1 *</label>
                        <input type="text" name="imei_1" placeholder="15-digit IMEI" required inputmode="numeric" class="form-control" style="font-family:monospace; font-weight:700; font-size:13px;">
                    </div>

                    <div class="form-row modal-form-grid-2" style="margin-bottom: 12px;">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Buyback Cost (₹) *</label>
                            <input type="number" step="0.01" name="purchase_cost" placeholder="42000" required inputmode="decimal" class="form-control" style="font-size:13px;">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Resale Price (₹) *</label>
                            <input type="number" step="0.01" name="selling_price" placeholder="54999" required inputmode="decimal" class="form-control" style="font-weight:700; color:#16A34A; font-size:13px;">
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom:12px;">
                        <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Condition Grade *</label>
                        <select name="condition_grade" class="form-control" style="font-size:13px;">
                            <option value="like_new_A_plus">Grade A+ (Pristine / Like New)</option>
                            <option value="good_A">Grade A (Minor Micro-Scratches)</option>
                            <option value="fair_B">Grade B (Noticeable Wear / 100% Functional)</option>
                        </select>
                    </div>

                    <div style="padding: 12px; background: #FFF7ED; border: 1px solid #FED7AA; border-radius:10px; margin-bottom: 12px;">
                        <div style="font-size:11px; font-weight:800; color:#C2410C; margin-bottom: 8px; text-transform:uppercase;">Customer KYC / Seller Info</div>
                        <div class="form-row modal-form-grid-2" style="margin-bottom: 8px;">
                            <input type="text" name="customer_buyback_name" placeholder="Customer Name *" required class="form-control" style="font-size:12px;">
                            <input type="text" name="customer_buyback_phone" placeholder="Customer Phone *" required inputmode="tel" class="form-control" style="font-size:12px;">
                        </div>
                        <input type="text" name="customer_buyback_id_proof" placeholder="Aadhaar / ID Details (Optional)" class="form-control" style="font-size:12px;">
                    </div>

                    <div class="form-group" style="margin-bottom:14px;">
                        <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Inspection Remarks</label>
                        <textarea name="checklist_notes" rows="2" placeholder="e.g. Original display, FaceID verified" class="form-control" style="font-size:12px;"></textarea>
                    </div>

                    <div class="modal-sticky-footer" style="display:flex; justify-content:flex-end; gap: 10px; padding-top: 14px; border-top: 1px solid #E2E8F0;">
                        <button type="button" onclick="closePurchaseBuybackModal()" class="btn btn-outline" style="font-size:12px;">Cancel</button>
                        <button type="submit" class="btn btn-primary" style="background:#EA580C; border-color:#EA580C; font-size:12px;">Save Pre-Owned to Stock</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════ --}}
    {{-- PURCHASE-PAGE INLINE MODAL: Add Part / Accessory --}}
    {{-- ══════════════════════════════════════════════════════════ --}}
    <div id="purchaseAddPartModal" style="display:none; position: fixed; inset: 0; z-index: 1300; background: rgba(15,23,42,0.5); backdrop-filter: blur(4px); align-items:center; justify-content:center; padding: 16px;">
        <div class="card" style="max-width: 520px; width: 100%; max-height: 90vh; overflow-y:auto; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); border-radius:14px; background:#fff;">
            <div class="card-header" style="border-bottom:1px solid #E2E8F0; padding:14px 18px; display:flex; justify-content:space-between; align-items:center;">
                <div class="card-title" style="font-weight:700; font-size:15px; color:#0F172A; display:flex; align-items:center; gap:8px;">
                    <i data-lucide="package" style="width:18px;height:18px;color:#16A34A;"></i>
                    Add Part or Accessory to Stock
                </div>
                <button type="button" onclick="closePurchaseAddPartModal()" class="btn-icon" style="background:none; border:none; font-size:16px; cursor:pointer; color:#64748B;">✕</button>
            </div>
            <div class="card-body" style="padding:16px 18px;">
                <form action="{{ route('mobileshop.accessories.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="redirect_to" value="{{ route('mobileshop.purchase') }}">

                    <div class="form-row modal-form-grid-2" style="margin-bottom: 12px;">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Category *</label>
                            <select name="category" required class="form-control" style="font-size:13px;">
                                <option value="">-- Choose Category --</option>
                                @foreach($categories ?? [] as $cat)
                                    <option value="{{ $cat->slug ?? $cat->name }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Brand</label>
                            <input type="text" name="brand" placeholder="e.g. Boat / Anker" class="form-control" style="font-size:13px;">
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom:12px;">
                        <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Item Name *</label>
                        <input type="text" name="name" placeholder="e.g. 65W Fast Charger / Type-C Cable" required class="form-control" style="font-size:13px;">
                    </div>

                    <div class="form-group" style="margin-bottom:12px;">
                        <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Compatible Model</label>
                        <input type="text" name="compatible_model" placeholder="e.g. iPhone 15 / Universal" class="form-control" style="font-size:13px;">
                    </div>

                    <div class="form-row modal-form-grid-2" style="margin-bottom: 12px;">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Unit Cost (₹) *</label>
                            <input type="number" step="0.01" name="unit_cost" placeholder="250.00" required inputmode="decimal" class="form-control" style="font-size:13px;">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Selling Price (₹) *</label>
                            <input type="number" step="0.01" name="selling_price" placeholder="499.00" required inputmode="decimal" class="form-control" style="font-weight:700; color:#16A34A; font-size:13px;">
                        </div>
                    </div>

                    <div class="form-row modal-form-grid-2" style="margin-bottom: 14px;">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Initial Stock Qty *</label>
                            <input type="number" name="stock_qty" value="10" required inputmode="numeric" class="form-control" style="font-size:13px;">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Low Stock Alert</label>
                            <input type="number" name="min_stock_alert" value="3" inputmode="numeric" class="form-control" style="font-size:13px;">
                        </div>
                    </div>

                    <div class="modal-sticky-footer" style="display:flex; justify-content:flex-end; gap: 10px; padding-top: 14px; border-top: 1px solid #E2E8F0;">
                        <button type="button" onclick="closePurchaseAddPartModal()" class="btn btn-outline" style="font-size:12px;">Cancel</button>
                        <button type="submit" class="btn btn-primary" style="background:#16A34A; border-color:#16A34A; font-size:12px;">Add Part to Stock</button>
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
        window.location.href = "{{ route('mobileshop.purchase.create') }}";
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

    /* ── Purchase Page Inline Stock Modals ── */
    function openPurchaseAddMobileModal() {
        var modal = document.getElementById('purchaseAddMobileModal');
        if (modal) {
            modal.style.display = 'flex';
            if (window.refreshIcons) window.refreshIcons();
        }
    }
    function closePurchaseAddMobileModal() {
        var modal = document.getElementById('purchaseAddMobileModal');
        if (modal) modal.style.display = 'none';
    }

    function openPurchaseBuybackModal() {
        var modal = document.getElementById('purchaseBuybackModal');
        if (modal) {
            modal.style.display = 'flex';
            if (window.refreshIcons) window.refreshIcons();
        }
    }
    function closePurchaseBuybackModal() {
        var modal = document.getElementById('purchaseBuybackModal');
        if (modal) modal.style.display = 'none';
    }

    function openPurchaseAddPartModal() {
        var modal = document.getElementById('purchaseAddPartModal');
        if (modal) {
            modal.style.display = 'flex';
            if (window.refreshIcons) window.refreshIcons();
        }
    }
    function closePurchaseAddPartModal() {
        var modal = document.getElementById('purchaseAddPartModal');
        if (modal) modal.style.display = 'none';
    }

    function previewSelectedPhoto(input, imgId, boxId) {
        if (!input || !input.files || !input.files[0]) return;
        var reader = new FileReader();
        reader.onload = function (e) {
            var img = document.getElementById(imgId);
            var box = document.getElementById(boxId);
            if (img) img.src = e.target.result;
            if (box) box.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
    window.previewSelectedPhoto = window.previewSelectedPhoto || previewSelectedPhoto;

    // Close purchase inline modals on backdrop click
    ['purchaseAddMobileModal','purchaseBuybackModal','purchaseAddPartModal'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) {
            el.addEventListener('click', function(e) {
                if (e.target === el) {
                    el.style.display = 'none';
                }
            });
        }
    });

    // Close purchase inline modals and FAB menu on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closePurchaseAddMobileModal();
            closePurchaseBuybackModal();
            closePurchaseAddPartModal();
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
        document.getElementById('btnPoFilterPhones')?.classList.remove('active');
        document.getElementById('btnPoFilterAccessories')?.classList.remove('active');
        document.getElementById('btnPoFilterBuyback')?.classList.remove('active');

        if (type === 'all') document.getElementById('btnPoFilterAll')?.classList.add('active');
        else if (type === 'phones') document.getElementById('btnPoFilterPhones')?.classList.add('active');
        else if (type === 'accessories') document.getElementById('btnPoFilterAccessories')?.classList.add('active');
        else if (type === 'buyback') document.getElementById('btnPoFilterBuyback')?.classList.add('active');

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
