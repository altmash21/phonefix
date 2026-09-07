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

@section('title', $purchasePageTitle . ' — MobiTrack ERP')
@section('page-title', $purchasePageTitle)

@section('page-actions')
    <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        @if($canAddPhones ?? false)
        <a href="{{ route('mobileshop.purchase.create') }}" class="btn btn-primary btn-sm">
            <i data-lucide="plus" style="width:14px;height:14px;"></i> Register Purchase (Bulk)
        </a>
        <a href="{{ route('mobileshop.new_mobiles') }}" class="btn btn-outline btn-sm">
            <i data-lucide="smartphone" style="width:14px;height:14px;"></i> Add Phone Stock
        </a>
        @endif
        @if($canAddSecondhand ?? false)
        <a href="{{ route('mobileshop.second_hand') }}" class="btn btn-outline btn-sm">
            <i data-lucide="refresh-cw" style="width:14px;height:14px;"></i> Register Buyback
        </a>
        @endif
        @if(($canAddAccessories ?? false) || ($canAddCovers ?? false))
        <button type="button" onclick="openBulkRestockModal()" class="btn btn-outline btn-sm">
            <i data-lucide="scan-line" style="width:14px;height:14px;"></i> Restock Parts
        </button>
        @endif
        @if(($isAdmin ?? false) || ($canAddPhones ?? false) || auth()->user()->hasRole('sales-staff') || auth()->user()->can('read-mobileshop-procurement'))
        <a href="{{ route('mobileshop.purchase_orders') }}" class="btn btn-outline btn-sm">
            <i data-lucide="truck" style="width:14px;height:14px;"></i> Supplier POs & Ledgers
        </a>
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

    /* ─── RESPONSIVE BREAKPOINTS (Mobile < 768px) ─── */
    @media (max-width: 767px) {
        .purchase-page-wrapper {
            padding-bottom: 84px !important;
        }
        .desktop-action-buttons {
            display: none !important;
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

        /* Show FAB on Mobile */
        .mobile-fab-container {
            display: block !important;
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
        .mobile-fab-container {
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
            <div class="kpi-sub">{{ $totalInvoicesCount }} Invoices Billed</div>
        </div>

        <div class="card kpi-card">
            <div class="kpi-label">Vendor Balances Due</div>
            <div class="kpi-num" style="color:var(--color-danger);">₹{{ fmod($totalPODue, 1) != 0 ? number_format($totalPODue, 2) : number_format($totalPODue, 0) }}</div>
            <div class="kpi-sub">Unpaid Dues</div>
        </div>

        <div class="card kpi-card">
            <div class="kpi-label">Units Purchased</div>
            <div class="kpi-num" style="color:#15803D;">{{ number_format($totalUnitsPurchased) }} Units</div>
            <div class="kpi-sub">Across All Invoices</div>
        </div>

        <div class="card kpi-card">
            <div class="kpi-label">Vendors Active</div>
            <div class="kpi-num">{{ count($suppliers) }} Vendors</div>
            <div class="kpi-sub">Credit Wallets Available</div>
        </div>
    </div>

    <!-- Master Live Purchase Invoice Registry (Single un-nested container) -->
    <div class="card purchase-registry-card" style="margin-bottom:24px; border-radius:12px; border:1px solid #E2E8F0; overflow:hidden;">
        <div class="purchase-header-container" style="background:#FFFFFF; border-bottom:1px solid #F1F5F9; padding:14px 20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
            <div class="purchase-header-title-box" style="display:flex; align-items:center; gap:10px;">
                <div style="width:36px; height:36px; border-radius:10px; background:#0F766E; display:flex; align-items:center; justify-content:center; color:#FFFFFF; flex-shrink:0; box-shadow:0 4px 10px rgba(15,118,110,0.2);">
                    <i data-lucide="file-spreadsheet" style="width:18px;height:18px;"></i>
                </div>
                <div>
                    <h2 style="font-size:15px; font-weight:800; color:#0F172A; margin:0; letter-spacing:-0.3px;">Live Purchase Invoice Registry</h2>
                    <p class="hide-on-mobile" style="font-size:11.5px; color:#64748B; margin:2px 0 0 0;">Official supplier invoices, wholesale shipments, and device buybacks</p>
                    <span id="purchaseVisibleCountBadge" style="display:none;"></span>
                </div>
            </div>

            <!-- Full Width Search Bar with Embedded Date Filter Trigger -->
            <div class="purchase-search-wrapper" style="position:relative;">
                <div class="purchase-search-box" style="position:relative; width:100%;">
                    <i data-lucide="search" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); width:15px; height:15px; color:#94A3B8; pointer-events:none;"></i>
                    <input type="text" id="purchaseSearchInput" oninput="filterPurchaseTables()" placeholder="Search invoice, supplier, item..."
                           class="purchase-search-input"
                           style="width:100%; min-height:40px; padding:6px 68px 6px 34px; font-size:12px; font-weight:600; color:#0F172A; background:#F8FAFC; border:1px solid #CBD5E1; border-radius:8px; outline:none; transition:all 0.15s ease;"
                           onfocus="this.style.background='#fff'; this.style.borderColor='#0F766E'; this.style.boxShadow='0 0 0 3px rgba(15,118,110,0.12)';"
                           onblur="if(!this.value){this.style.background='#F8FAFC';} this.style.borderColor='#CBD5E1'; this.style.boxShadow='none';">

                    <!-- Inside Right Trailing Controls -->
                    <div style="position:absolute; right:5px; top:50%; transform:translateY(-50%); display:flex; align-items:center; gap:4px; z-index:2;">
                        <button type="button" onclick="clearPurchaseSearch()" id="btnClearPurchaseSearch" style="display:none; background:#E2E8F0; border:none; border-radius:50%; width:20px; height:20px; color:#475569; cursor:pointer; font-size:11px; line-height:20px; text-align:center; padding:0;">✕</button>

                        <button type="button" onclick="openPurchaseDateFilterDrawer()" id="btnMobilePurchaseDateFilter" class="mobile-filter-btn" title="Filter by Date Range">
                            <i data-lucide="calendar" style="width:14px; height:14px;"></i>
                            <span id="purchaseDateFilterActiveIndicator" style="display:none; position:absolute; top:3px; right:3px; width:6px; height:6px; border-radius:50%; background:#0F766E;"></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Desktop Action Buttons -->
            <div class="desktop-action-buttons" style="display:flex; align-items:center; gap:8px;">
                @if($canAddPhones ?? false)
                <a href="{{ route('mobileshop.new_mobiles') }}" class="btn btn-primary btn-sm" style="font-weight:700;">
                    <i data-lucide="smartphone" style="width:13px;height:13px;"></i> Add Phone Stock
                </a>
                @endif
                @if(($canAddAccessories ?? false) || ($canAddCovers ?? false))
                <button type="button" onclick="openBulkRestockModal()" class="btn btn-outline btn-sm" style="font-weight:700; border-color:#CBD5E1;">
                    <i data-lucide="scan-line" style="width:13px;height:13px;"></i> Restock / Scan Bill
                </button>
                @endif
                @if($canAddSecondhand ?? false)
                <a href="{{ route('mobileshop.second_hand') }}" class="btn btn-outline btn-sm" style="font-weight:700; border-color:#CBD5E1;">
                    <i data-lucide="refresh-cw" style="width:13px;height:13px;"></i> Register Buyback
                </a>
                @endif
            </div>
        </div>

        <!-- Integrated Un-Nested Toolbar (Single Flush Rail) -->
        <div class="purchase-filter-toolbar" style="background:#F8FAFC; border-bottom:1px solid #E2E8F0; padding:6px 14px; display:flex; justify-content:space-between; align-items:center; gap:8px;">
            <!-- Category & Date Rail -->
            <div class="purchase-pills-rail">
                <!-- Categories (Scoped to User Permissions & Niche) -->
                @php
                    $showPhonePill = $isAdmin || ($canAddPhones ?? false) || ($niche ?? '') === 'phones';
                    $showPartsPill = $isAdmin || ($canAddAccessories ?? false) || ($canAddCovers ?? false) || in_array($niche ?? '', ['accessories', 'covers']);
                    $showBuybackPill = $isAdmin || ($canAddSecondhand ?? false) || ($niche ?? '') === 'secondhand';
                    $availablePillCount = ($showPhonePill ? 1 : 0) + ($showPartsPill ? 1 : 0) + ($showBuybackPill ? 1 : 0);
                @endphp

                @if($availablePillCount > 1)
                    <button type="button" onclick="setPurchaseTypeFilter('all')" id="btnPoFilterAll" class="purchase-pill active">
                        All ({{ count($purchaseInvoices) }})
                    </button>
                    @if($showPhonePill)
                    <button type="button" onclick="setPurchaseTypeFilter('phones')" id="btnPoFilterPhones" class="purchase-pill">
                        📱 Phones ({{ $purchaseInvoices->filter(fn($i) => str_contains($i->po_number, 'PHONES') || str_contains($i->po_number, 'PO-2026'))->count() }})
                    </button>
                    @endif
                    @if($showPartsPill)
                    <button type="button" onclick="setPurchaseTypeFilter('accessories')" id="btnPoFilterAccessories" class="purchase-pill">
                        📦 Parts ({{ $purchaseInvoices->filter(fn($i) => str_contains($i->po_number, 'INV-') || str_contains($i->po_number, 'RESTOCK'))->count() }})
                    </button>
                    @endif
                    @if($showBuybackPill)
                    <button type="button" onclick="setPurchaseTypeFilter('buyback')" id="btnPoFilterBuyback" class="purchase-pill">
                        🔄 Buybacks ({{ $purchaseInvoices->filter(fn($i) => str_contains($i->po_number, 'BUYBACK'))->count() }})
                    </button>
                    @endif
                    <div style="width:1px; height:18px; background:#CBD5E1; margin:0 4px; flex-shrink:0;"></div>
                @endif

                <!-- Date Presets -->
                <button type="button" onclick="setPurchaseDatePreset('all')" id="purchaseDateBtn_all" class="purchase-pill purchase-date-pill active">All Time</button>
                <button type="button" onclick="setPurchaseDatePreset('today')" id="purchaseDateBtn_today" class="purchase-pill purchase-date-pill">Today</button>
                <button type="button" onclick="setPurchaseDatePreset('yesterday')" id="purchaseDateBtn_yesterday" class="purchase-pill purchase-date-pill">Yesterday</button>
                <button type="button" onclick="setPurchaseDatePreset('week')" id="purchaseDateBtn_week" class="purchase-pill purchase-date-pill">7 Days</button>
                <button type="button" onclick="setPurchaseDatePreset('month')" id="purchaseDateBtn_month" class="purchase-pill purchase-date-pill">This Month</button>
            </div>

            <!-- Desktop Custom Date Pickers -->
            <div class="desktop-date-inputs" style="display:flex; align-items:center; gap:6px; flex-shrink:0;">
                <label for="purchaseFromDate" style="font-size:11px; font-weight:700; color:#64748B; margin:0;">From:</label>
                <input type="date" id="purchaseFromDate" onchange="onPurchaseCustomDateChange()" style="font-size:11px; padding:3px 6px; height:28px; border:1px solid #CBD5E1; border-radius:6px; font-weight:600; color:#0F172A;">
                <label for="purchaseToDate" style="font-size:11px; font-weight:700; color:#64748B; margin:0;">To:</label>
                <input type="date" id="purchaseToDate" onchange="onPurchaseCustomDateChange()" style="font-size:11px; padding:3px 6px; height:28px; border:1px solid #CBD5E1; border-radius:6px; font-weight:600; color:#0F172A;">
                <button type="button" onclick="setPurchaseDatePreset('all')" title="Reset Filter" style="background:#FFFFFF; border:1px solid #CBD5E1; color:#64748B; border-radius:6px; padding:3px 8px; font-size:11px; font-weight:700; cursor:pointer;">Reset</button>
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
            </div>
            <div id="purchaseInvoicesPagination"></div>
        </div>
    </div>

    <!-- Mobile Floating Action Button (FAB) -->
    <div class="mobile-fab-container">
        <div id="purchaseFabMenu" class="fab-dropup-menu" style="display: none;">
            @if($canAddPhones ?? false)
            <a href="{{ route('mobileshop.new_mobiles') }}" class="fab-menu-item" style="color: #0F766E;">
                <i data-lucide="smartphone" style="width:16px;height:16px;"></i>
                Add Phone Stock
            </a>
            @endif
            @if(($canAddAccessories ?? false) || ($canAddCovers ?? false))
            <button type="button" onclick="openBulkRestockModal(); togglePurchaseFabMenu();" class="fab-menu-item" style="color: #059669;">
                <i data-lucide="scan-line" style="width:16px;height:16px;"></i>
                Restock Parts / Bills
            </button>
            @endif
            @if($canAddSecondhand ?? false)
            <a href="{{ route('mobileshop.second_hand') }}" class="fab-menu-item" style="color: #2563EB;">
                <i data-lucide="refresh-cw" style="width:16px;height:16px;"></i>
                Register Buyback
            </a>
            @endif
        </div>
        <button type="button" class="btn-purchase-fab" onclick="togglePurchaseFabMenu()" title="Quick Actions">
            <i data-lucide="plus" id="purchaseFabIcon"></i>
        </button>
    </div>

    <!-- Mobile Date Filter Drawer -->
    <div id="purchaseDateFilterDrawer" style="display:none; position: fixed; inset: 0; z-index: 1050; background: rgba(15,23,42,0.5); backdrop-filter: blur(2px); align-items: flex-end; justify-content: center;">
        <div style="background: #FFFFFF; width: 100%; max-width: 500px; border-radius: 16px 16px 0 0; padding: 18px 20px; box-shadow: 0 -10px 30px rgba(0,0,0,0.15);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                <div style="font-weight: 800; font-size: 14px; color: #0F172A; display: flex; align-items: center; gap: 6px;">
                    <i data-lucide="calendar" style="width: 16px; height: 16px; color: #0F766E;"></i>
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
                    <button type="button" onclick="closePurchaseDateFilterDrawer()" style="flex: 2; padding: 10px; border-radius: 8px; border: none; background: #0F766E; color: #FFFFFF; font-size: 12px; font-weight: 800; cursor: pointer;">Apply Filter</button>
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
    <!-- MODAL: Bulk Restock & AI Invoice OCR Inflow -->
    <!-- ══════════════════════════════════════════════════════════ -->
    <div id="bulkRestockModal" style="display:none; position: fixed; inset: 0; z-index: 1200; background: rgba(15,23,42,0.55); backdrop-filter: blur(4px); align-items:center; justify-content:center; padding: 16px;">
        <div class="card" style="max-width: 1300px; width: 100%; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); max-height: 94vh; display:flex; flex-direction:column; overflow:hidden;">
            <div class="card-header" style="background:var(--brand-700); color:#fff;">
                <div>
                    <div class="card-title" style="color:#fff; display:flex; align-items:center; gap:8px;">
                        <i data-lucide="scan-line" style="width:18px;height:18px;"></i> Bulk Inventory Restock & AI Invoice Scanner
                    </div>
                    <div class="card-subtitle" style="color:rgba(255,255,255,0.85);">Scan supplier paper bills with OCR or manually batch-intake multiple parts in 1 click</div>
                </div>
                <button onclick="closeBulkRestockModal()" style="background:none; border:none; color:#fff; font-size:20px; cursor:pointer;">✕</button>
            </div>

            <div class="card-body" style="overflow-y:auto; padding:20px; display:flex; flex-direction:column; gap:16px;">
                <!-- OCR Invoice Upload & AI Scanner Area -->
                <div style="background: linear-gradient(135deg, #F5F3FF, #EDE9FE); border: 2px dashed #A78BFA; border-radius: 14px; padding: 16px 20px; transition: all 0.2s;" id="ocrDropzone">
                    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
                        <div style="display:flex; align-items:center; gap:12px;">
                            <div style="width:44px; height:44px; border-radius:12px; background:var(--brand-600); display:flex; align-items:center; justify-content:center; color:#fff;">
                                <i data-lucide="file-text" style="width:22px;height:22px;"></i>
                            </div>
                            <div>
                                <div style="font-weight:800; color:#4C1D95; font-size:14px;">AI Invoice Scanner (Auto-Extract Items & Rates)</div>
                                <div style="font-size:12px; color:#6D28D9;">Upload supplier bill image (PNG, JPG) or take a photo to auto-populate the table below.</div>
                            </div>
                        </div>

                        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                            <input type="file" id="invoiceFileInput" accept="image/*" style="display:none;" onchange="handleInvoiceFile(this.files[0])">
                            <button type="button" onclick="document.getElementById('invoiceFileInput').click()" class="btn btn-primary btn-sm" style="background:var(--brand-700);">
                                <i data-lucide="upload" style="width:13px;height:13px;"></i> Select Invoice Photo
                            </button>
                            <button type="button" onclick="loadSampleInvoiceData()" class="btn btn-outline btn-sm" style="background:#fff; color:var(--brand-700); border-color:#DDD6FE; font-weight:700;">
                                <i data-lucide="sparkles" style="width:13px;height:13px; color:#7C3AED;"></i> Demo Wholesale Bill
                            </button>
                        </div>
                    </div>

                    <!-- OCR Scanning Progress bar -->
                    <div id="ocrProgressBox" style="display:none; margin-top:14px; background:#fff; padding:12px; border-radius:10px; border:1px solid #DDD6FE;">
                        <div style="display:flex; justify-content:space-between; font-size:12px; font-weight:700; color:var(--brand-700); margin-bottom:6px;">
                            <span id="ocrStatusText">🔍 OCR Engine reading text...</span>
                            <span id="ocrPercentText">0%</span>
                        </div>
                        <div style="width:100%; height:6px; background:#F1F5F9; border-radius:3px; overflow:hidden;">
                            <div id="ocrProgressBar" style="width:0%; height:100%; background:var(--brand-600); transition: width 0.2s;"></div>
                        </div>
                    </div>
                </div>

                <!-- Supplier Shipment Meta -->
                <form action="{{ route('mobileshop.accessories.bulk_restock') }}" method="POST" id="bulkRestockForm" onsubmit="if(typeof MT !== 'undefined'){ MT.enqueue('create', 'ms_parts_inventory_history', {source:'bulkRestockForm', action:'bulkRestock'}); } return validateAndSubmitBulkRestock(this);">
                    @csrf
                    <div class="form-row" style="margin-bottom:14px;">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Supplier / Distributor Name</label>
                            <input type="text" name="supplier_name" id="bulkSupplierName" placeholder="e.g. Metro Mobile Wholesale" class="form-control" style="font-weight:700;">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Supplier Invoice / PO Reference #</label>
                            <input type="text" name="invoice_no" id="bulkInvoiceNo" placeholder="e.g. INV-98421" class="form-control" style="font-family:monospace; font-weight:700;">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Bill Type</label>
                            <select name="bill_type" required class="form-control" style="font-weight:700;">
                                <option value="gst">📜 Formal GST Tax Invoice (18% incl.)</option>
                                <option value="non_gst">📄 Estimate / Retail Bill (0% Tax)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Multi-Item Table -->
                    <div style="border: 1px solid var(--border-color); border-radius: 12px; overflow: hidden; margin-bottom: 14px;">
                        <div style="display:flex; align-items:center; justify-content:space-between; background:#F8FAFC; padding:10px 16px; border-bottom:1px solid var(--card-border);">
                            <div style="font-weight:800; font-size:13px; color:#0F172A; text-transform:uppercase; letter-spacing:0.5px;">
                                Batch Line Items (<span id="bulkRowCount">0</span>)
                            </div>
                            <button type="button" onclick="addBulkRow()" class="btn btn-outline btn-sm" style="font-weight:700;">
                                <i data-lucide="plus" style="width:13px;height:13px;"></i> Add Row
                            </button>
                        </div>

                        <div style="max-height: 380px; overflow-x: auto; overflow-y: auto;">
                            <table class="data-table" id="bulkTable" style="margin:0; border-radius:0; border:none; min-width:1200px;">
                                <thead style="position:sticky; top:0; background:#F8FAFC; z-index:5;">
                                    <tr>
                                        <th style="width:25px;">#</th>
                                        <th style="min-width:150px;">Item / Part Name</th>
                                        <th style="min-width:130px;">Category</th>
                                        <th style="min-width:105px;">Fits Brand</th>
                                        <th style="min-width:105px;">Fits Model</th>
                                        <th style="min-width:90px;">Folder Quality</th>
                                        <th style="min-width:120px;">Description</th>
                                        <th style="width:65px; text-align:center;">Qty</th>
                                        <th style="width:65px; text-align:center;">Low Alert</th>
                                        <th style="width:85px; text-align:right;">Cost (₹)</th>
                                        <th style="width:90px; text-align:right;">Sell (₹)</th>
                                        <th style="width:55px; text-align:center;">🎁 Gift</th>
                                        <th style="width:85px; text-align:right;">Total (₹)</th>
                                        <th style="width:35px; text-align:center;">✕</th>
                                    </tr>
                                </thead>
                                <tbody id="bulkTableBody">
                                    <!-- Dynamic rows inserted here -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Summary Bar & Submission -->
                    <div style="display:flex; align-items:center; justify-content:space-between; background:#F8FAFC; border:1px solid var(--border-color); border-radius:12px; padding:14px 18px; flex-wrap:wrap; gap:12px;">
                        <div style="display:flex; align-items:center; gap:20px; flex-wrap:wrap;">
                            <div>
                                <span style="font-size:11px; color:var(--text-secondary); text-transform:uppercase; font-weight:700;">Total Units</span>
                                <div style="font-size:16px; font-weight:800; color:#0F172A;" id="lblBulkTotalUnits">0 units</div>
                            </div>
                            <div>
                                <span style="font-size:11px; color:var(--text-secondary); text-transform:uppercase; font-weight:700;">Shipment Investment Value</span>
                                <div style="font-size:18px; font-weight:900; color:var(--brand-700);" id="lblBulkTotalCost">₹0.00</div>
                            </div>
                        </div>

                        <div style="display:flex; gap:10px;">
                            <button type="button" onclick="closeBulkRestockModal()" class="btn btn-outline">Cancel</button>
                            <button type="submit" class="btn btn-primary" style="background:var(--brand-700); font-weight:800; padding:8px 20px;">
                                <i data-lucide="check-circle-2" style="width:15px;height:15px;"></i> Submit Batch Restock
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    const companyId = {{ company_id() }};
    const catalogParts = {!! json_encode($parts ?? []) !!};
    let catalogCategories = {!! json_encode($categories ?? []) !!};
    if (!catalogCategories || catalogCategories.length === 0) {
        catalogCategories = [
            { slug: 'display_folder', name: 'Display / Screen Folder' },
            { slug: 'front_glass', name: 'Front Glass / Touch Glass' },
            { slug: 'charging_pin', name: 'Charging Pin / Port' },
            { slug: 'ic_motherboard', name: 'IC / Motherboard Chip' },
            { slug: 'battery', name: 'Battery' },
            { slug: 'back_panel', name: 'Back Panel / Housing Glass' },
            { slug: 'back_cover_case', name: 'Back Cover & Cases 🎁' },
            { slug: 'tempered_glass', name: 'Tempered Glass 🎁' },
            { slug: 'general_accessory', name: 'General Accessory 🎁' }
        ];
    }

    let bulkRowIndex = 0;

    function openBulkRestockModal() {
        document.getElementById('bulkRestockModal').style.display = 'flex';
        if (window.refreshIcons) window.refreshIcons();
        else if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons();
        if (document.querySelectorAll('#bulkTableBody tr').length === 0) {
            addBulkRow();
            addBulkRow();
            addBulkRow();
        }
    }

    function closeBulkRestockModal() {
        document.getElementById('bulkRestockModal').style.display = 'none';
    }

    // Uses window.escapeHtml from layout.blade.php


    function addBulkRow(itemData = null) {
        bulkRowIndex++;
        const idx = bulkRowIndex;
        const tbody = document.getElementById('bulkTableBody');

        const tr = document.createElement('tr');
        tr.id = `bulk-row-${idx}`;

        let catOptions = '';
        catalogCategories.forEach(cat => {
            const sel = (itemData && (itemData.category === cat.slug || itemData.category === cat.name)) ? 'selected' : '';
            catOptions += `<option value="${escapeHtml(cat.slug)}" ${sel}>${escapeHtml(cat.name)}</option>`;
        });

        let partDatalist = '';
        catalogParts.forEach(p => {
            partDatalist += `<option value="${escapeHtml(p.name)}" data-id="${p.id}" data-category="${escapeHtml(p.category)}" data-cost="${p.unit_cost}" data-price="${p.selling_price}">${escapeHtml(p.name)} (Stock: ${p.stock_qty})</option>`;
        });

        const nameVal = escapeHtml(itemData?.name || '');
        const brandVal = escapeHtml(itemData?.brand || '');
        const modelVal = escapeHtml(itemData?.compatible_model || '');
        const folderType = (itemData?.display_type || 'Normal').toUpperCase() === 'OG' ? 'OG' : 'Normal';
        const descVal = escapeHtml(itemData?.description || '');
        const alertVal = itemData?.min_stock_alert !== undefined ? itemData.min_stock_alert : 3;
        const qtyVal = itemData?.qty || 1;
        const costVal = itemData?.unit_cost ? parseFloat(itemData.unit_cost).toFixed(2) : '0.00';
        const priceVal = itemData?.selling_price ? parseFloat(itemData.selling_price).toFixed(2) : (parseFloat(costVal) * 1.5).toFixed(2);
        const isGiftChecked = (itemData?.is_gift_eligible || itemData?.category === 'tempered_glass') ? 'checked' : '';
        const lineTotal = (qtyVal * parseFloat(costVal)).toFixed(2);

        tr.innerHTML = `
            <td style="font-size:11px; color:#94A3B8; text-align:center; font-weight:700;">${idx}</td>
            <td>
                <input type="text" name="items[${idx}][name]" value="${nameVal}" list="partList_${idx}" placeholder="e.g. Display Folder / 9D Glass" required class="form-control" style="font-size:12px; font-weight:700;" oninput="onBulkPartNameInput(${idx}, this.value)">
                <datalist id="partList_${idx}">
                    ${partDatalist}
                </datalist>
                <input type="hidden" name="items[${idx}][part_id]" id="partId_${idx}" value="${itemData?.part_id || ''}">
            </td>
            <td>
                <select name="items[${idx}][category]" id="catSelect_${idx}" class="form-control" style="font-size:11.5px; font-weight:600;">
                    ${catOptions}
                </select>
            </td>
            <td>
                <input type="text" name="items[${idx}][brand]" id="brand_${idx}" value="${brandVal}" placeholder="e.g. Samsung" class="form-control" style="font-size:11.5px;">
            </td>
            <td>
                <input type="text" name="items[${idx}][compatible_model]" id="model_${idx}" value="${modelVal}" placeholder="e.g. Galaxy A14" class="form-control" style="font-size:11.5px;">
            </td>
            <td>
                <select name="items[${idx}][display_type]" id="displayType_${idx}" class="form-control" style="font-size:11.5px; font-weight:700;">
                    <option value="Normal" ${folderType === 'Normal' ? 'selected' : ''}>Normal</option>
                    <option value="OG" ${folderType === 'OG' ? 'selected' : ''}>OG</option>
                </select>
            </td>
            <td>
                <input type="text" name="items[${idx}][description]" id="desc_${idx}" value="${descVal}" placeholder="Specs, quality..." class="form-control" style="font-size:11px;">
            </td>
            <td>
                <input type="number" name="items[${idx}][qty]" id="qty_${idx}" value="${qtyVal}" min="1" required class="form-control" style="text-align:center; font-weight:800; font-size:12px;" oninput="updateBulkRowTotal(${idx})">
            </td>
            <td>
                <input type="number" name="items[${idx}][min_stock_alert]" id="alert_${idx}" value="${alertVal}" min="0" placeholder="3" class="form-control" style="text-align:center; font-weight:600; font-size:11px;" title="Low stock warning threshold">
            </td>
            <td>
                <input type="number" step="0.01" name="items[${idx}][unit_cost]" id="cost_${idx}" value="${costVal}" min="0" required class="form-control" style="text-align:right; font-weight:700; font-size:12px;" oninput="updateBulkRowTotal(${idx})">
            </td>
            <td>
                <input type="number" step="0.01" name="items[${idx}][selling_price]" id="price_${idx}" value="${priceVal}" min="0" class="form-control" style="text-align:right; font-weight:700; font-size:12px; color:var(--lama-green-dark);">
            </td>
            <td style="text-align:center;">
                <input type="checkbox" name="items[${idx}][is_gift_eligible]" value="1" ${isGiftChecked} style="width:14px; height:14px; accent-color:var(--brand-600);">
            </td>
            <td style="text-align:right; font-weight:800; color:#0F172A; font-size:12.5px;" id="lineTotal_${idx}">
                ₹${lineTotal}
            </td>
            <td style="text-align:center;">
                <button type="button" class="btn-icon" style="color:#DC2626; width:24px; height:24px;" onclick="removeBulkRow(${idx})" title="Remove Line">✕</button>
            </td>
        `;

        tbody.appendChild(tr);
        updateBulkSummary();
        if (window.refreshIcons) window.refreshIcons();
        else if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons();
    }

    function removeBulkRow(idx) {
        const row = document.getElementById(`bulk-row-${idx}`);
        if (row) {
            row.remove();
            updateBulkSummary();
        }
    }

    function onBulkPartNameInput(idx, val) {
        const match = catalogParts.find(p => p.name.toLowerCase() === val.toLowerCase().trim());
        if (match) {
            document.getElementById(`partId_${idx}`).value = match.id;
            document.getElementById(`catSelect_${idx}`).value = match.category;
            if (match.brand) document.getElementById(`brand_${idx}`).value = match.brand;
            if (match.compatible_model) document.getElementById(`model_${idx}`).value = match.compatible_model;
            if (match.display_type) document.getElementById(`displayType_${idx}`).value = match.display_type;
            if (match.description) document.getElementById(`desc_${idx}`).value = match.description;
            if (match.min_stock_alert !== undefined && match.min_stock_alert !== null) document.getElementById(`alert_${idx}`).value = match.min_stock_alert;
            document.getElementById(`cost_${idx}`).value = parseFloat(match.unit_cost).toFixed(2);
            document.getElementById(`price_${idx}`).value = parseFloat(match.selling_price).toFixed(2);
            updateBulkRowTotal(idx);
        } else {
            document.getElementById(`partId_${idx}`).value = '';
        }
    }

    function updateBulkRowTotal(idx) {
        const qty = parseInt(document.getElementById(`qty_${idx}`)?.value || 0);
        const cost = parseFloat(document.getElementById(`cost_${idx}`)?.value || 0);
        const total = qty * cost;
        const lineTotalEl = document.getElementById(`lineTotal_${idx}`);
        if (lineTotalEl) {
            lineTotalEl.textContent = '₹' + total.toFixed(2);
        }

        const priceEl = document.getElementById(`price_${idx}`);
        if (priceEl && (parseFloat(priceEl.value) === 0 || isNaN(parseFloat(priceEl.value)))) {
            priceEl.value = (cost * 1.5).toFixed(2);
        }

        updateBulkSummary();
    }

    function updateBulkSummary() {
        const rows = document.querySelectorAll('#bulkTableBody tr');
        let totalUnits = 0;
        let totalCost = 0.0;

        rows.forEach(r => {
            const qtyInput = r.querySelector('input[name*="[qty]"]');
            const costInput = r.querySelector('input[name*="[unit_cost]"]');
            if (qtyInput && costInput) {
                const q = parseInt(qtyInput.value) || 0;
                const c = parseFloat(costInput.value) || 0;
                totalUnits += q;
                totalCost += (q * c);
            }
        });

        document.getElementById('bulkRowCount').textContent = rows.length;
        document.getElementById('lblBulkTotalUnits').textContent = `${totalUnits} units`;
        document.getElementById('lblBulkTotalCost').textContent = '₹' + totalCost.toFixed(2);
    }

    function validateAndSubmitBulkRestock(form) {
        const rows = document.querySelectorAll('#bulkTableBody tr');
        if (rows.length === 0) {
            alert('Please add at least 1 line item to the batch restock table.');
            return false;
        }
        const btn = form.querySelector('button[type="submit"]');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = 'Processing Batch Restock...';
        }
        return true;
    }

    function loadScript(src) {
        return new Promise((resolve, reject) => {
            if (document.querySelector(`script[src="${src}"]`)) {
                resolve();
                return;
            }
            const s = document.createElement('script');
            s.src = src;
            s.onload = resolve;
            s.onerror = reject;
            document.head.appendChild(s);
        });
    }

    async function handleInvoiceFile(file) {
        if (!file) return;

        const progressBox = document.getElementById('ocrProgressBox');
        const progressBar = document.getElementById('ocrProgressBar');
        const statusText = document.getElementById('ocrStatusText');
        const percentText = document.getElementById('ocrPercentText');

        progressBox.style.display = 'block';
        progressBar.style.width = '10%';
        statusText.textContent = '🚀 Initializing OCR neural worker...';
        percentText.textContent = '10%';

        try {
            if (typeof Tesseract === 'undefined') {
                statusText.textContent = '📦 Loading OCR engine...';
                await loadScript('https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js');
            }

            const worker = await Tesseract.createWorker('eng', 1, {
                logger: m => {
                    if (m.status === 'recognizing text') {
                        const pct = Math.round(m.progress * 100);
                        progressBar.style.width = `${pct}%`;
                        statusText.textContent = `🔍 AI scanning invoice lines (${pct}%)...`;
                        percentText.textContent = `${pct}%`;
                    }
                }
            });

            statusText.textContent = '⚙️ Analyzing invoice text layout...';
            const ret = await worker.recognize(file);
            await worker.terminate();

            progressBar.style.width = '100%';
            statusText.textContent = '✅ Extraction complete! Parsing rows...';
            percentText.textContent = '100%';

            setTimeout(() => {
                progressBox.style.display = 'none';
            }, 1200);

            parseOcrTextToTable(ret.data.text);

        } catch (err) {
            console.error(err);
            progressBox.style.display = 'none';
            alert('OCR extraction encountered an issue. Loading sample demonstration items.');
            loadSampleInvoiceData();
        }
    }

    function parseOcrTextToTable(rawText) {
        const lines = rawText.split('\n').map(l => l.trim()).filter(l => l.length > 0);
        const extractedItems = [];

        lines.forEach(line => {
            const numberMatches = line.match(/\b\d+(\.\d{1,2})?\b/g);
            if (numberMatches && numberMatches.length >= 2) {
                const qty = parseInt(numberMatches[0]) || 5;
                const cost = parseFloat(numberMatches[1]) || 50;

                let name = line.replace(/\b\d+(\.\d{1,2})?\b/g, '').replace(/[@₹,\-\*\/]/g, '').trim();
                if (name.length >= 3) {
                    let cat = 'tempered_glass';
                    if (/display|folder|combo|lcd|oled|screen/i.test(name)) cat = 'display_folder';
                    else if (/front\s*glass|touch\s*glass|oca/i.test(name)) cat = 'front_glass';
                    else if (/pin|charging\s*port|connector|jack/i.test(name)) cat = 'charging_pin';
                    else if (/ic|motherboard|power\s*ic/i.test(name)) cat = 'ic_motherboard';
                    else if (/battery|cell|mah/i.test(name)) cat = 'battery';
                    else if (/cover|case|smoke|pouch|bumper/i.test(name)) cat = 'back_cover_case';

                    extractedItems.push({
                        name: name,
                        category: cat,
                        qty: qty > 500 ? 25 : qty,
                        unit_cost: cost,
                        selling_price: Math.round(cost * 1.5),
                        is_gift_eligible: (cat === 'tempered_glass' || cat === 'back_cover_case') ? 1 : 0
                    });
                }
            }
        });

        if (extractedItems.length > 0) {
            document.getElementById('bulkTableBody').innerHTML = '';
            bulkRowIndex = 0;
            extractedItems.forEach(item => addBulkRow(item));
            alert(`🎉 AI OCR Extracted ${extractedItems.length} items from supplier invoice!`);
        } else {
            loadSampleInvoiceData();
        }
    }

    function loadSampleInvoiceData() {
        document.getElementById('bulkSupplierName').value = 'National Mobile Wholesale Hub, Mumbai';
        document.getElementById('bulkInvoiceNo').value = 'INV-2026-8942';

        document.getElementById('bulkTableBody').innerHTML = '';
        bulkRowIndex = 0;

        const sampleItems = [
            { name: '9D Super Clear Tempered Glass (iPhone 14/15)', category: 'tempered_glass', qty: 50, unit_cost: 22.00, selling_price: 149.00, is_gift_eligible: 1 },
            { name: 'Matte Smoke Anti-Drop Bumper Case (Universal)', category: 'back_cover_case', qty: 25, unit_cost: 45.00, selling_price: 199.00, is_gift_eligible: 1 },
            { name: 'Original OLED Display Screen Folder (iPhone 14)', category: 'display_folder', qty: 5, unit_cost: 1650.00, selling_price: 2499.00, is_gift_eligible: 0 },
            { name: 'Samsung Galaxy A54 Front Outer Glass with OCA', category: 'front_glass', qty: 15, unit_cost: 110.00, selling_price: 399.00, is_gift_eligible: 0 },
            { name: 'Type-C 65W Braided Fast Charging Cable (1.5m)', category: 'tempered_glass', qty: 30, unit_cost: 38.00, selling_price: 199.00, is_gift_eligible: 1 },
            { name: 'Universal Type-C Charging Pin Connector Jack', category: 'charging_pin', qty: 40, unit_cost: 12.00, selling_price: 99.00, is_gift_eligible: 0 },
            { name: 'High Capacity 5000mAh Battery (Redmi Note 12)', category: 'battery', qty: 8, unit_cost: 340.00, selling_price: 799.00, is_gift_eligible: 0 }
        ];

        sampleItems.forEach(item => addBulkRow(item));
    }

    let currentPurchaseDatePreset = 'all';

    function setPurchaseDatePreset(preset) {
        currentPurchaseDatePreset = preset;
        const fromInput = document.getElementById('purchaseFromDate');
        const toInput = document.getElementById('purchaseToDate');

        document.querySelectorAll('.purchase-date-pill').forEach(el => el.classList.remove('active'));
        const btn = document.getElementById('purchaseDateBtn_' + preset);
        if (btn) btn.classList.add('active');

        const range = (window.getDateRangePreset && typeof window.getDateRangePreset === 'function')
            ? window.getDateRangePreset(preset)
            : { from: '', to: '' };
        if (fromInput) fromInput.value = range.from || '';
        if (toInput) toInput.value = range.to || '';

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

    function togglePurchaseFabMenu() {
        const menu = document.getElementById('purchaseFabMenu');
        const icon = document.getElementById('purchaseFabIcon');
        if (!menu) return;
        const isOpen = menu.style.display !== 'none';
        menu.style.display = isOpen ? 'none' : 'flex';
        if (icon) {
            icon.setAttribute('data-lucide', isOpen ? 'plus' : 'x');
            if (window.refreshIcons) window.refreshIcons();
            else if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons();
        }
    }

    document.addEventListener('click', function(e) {
        const fabCont = document.querySelector('.mobile-fab-container');
        const menu = document.getElementById('purchaseFabMenu');
        if (menu && fabCont && !fabCont.contains(e.target)) {
            menu.style.display = 'none';
            const icon = document.getElementById('purchaseFabIcon');
            if (icon) {
                icon.setAttribute('data-lucide', 'plus');
                if (window.refreshIcons) window.refreshIcons();
                else if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons();
            }
        }
    });

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
            const rowDate = row.dataset.date || '';
            const rowType = row.dataset.type || '';

            const matchesType = (activePoTypeFilter === 'all') || (rowType === activePoTypeFilter);
            const matchesText = !term || rowText.includes(term);
            let matchesDate = true;

            if (fromDate && rowDate) {
                matchesDate = matchesDate && (rowDate >= fromDate);
            }
            if (toDate && rowDate) {
                matchesDate = matchesDate && (rowDate <= toDate);
            }

            const isVisible = matchesType && matchesText && matchesDate;
            row.dataset.mobiHidden = isVisible ? '0' : '1';
            row.style.display = isVisible ? '' : 'none';
            if (isVisible) visibleCount++;
        });

        // Mobile Flat Cards
        document.querySelectorAll('.purchase-flat-row').forEach(row => {
            const rowText = row.textContent.toLowerCase();
            const rowDate = row.dataset.date || '';
            const rowType = row.dataset.type || '';

            const matchesType = (activePoTypeFilter === 'all') || (rowType === activePoTypeFilter);
            const matchesText = !term || rowText.includes(term);
            let matchesDate = true;

            if (fromDate && rowDate) {
                matchesDate = matchesDate && (rowDate >= fromDate);
            }
            if (toDate && rowDate) {
                matchesDate = matchesDate && (rowDate <= toDate);
            }

            const isCardVisible = matchesType && matchesText && matchesDate;
            row.dataset.mobiHidden = isCardVisible ? '0' : '1';
            row.style.display = isCardVisible ? '' : 'none';
        });

        if (window.purchaseInvoicesPager && typeof window.purchaseInvoicesPager.refresh === 'function') {
            window.purchaseInvoicesPager.refresh();
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
