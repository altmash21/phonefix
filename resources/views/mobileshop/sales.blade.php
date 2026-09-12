@extends('mobileshop.layout')

@php
    $salesPageTitle = match($niche ?? 'admin') {
        'phones'      => 'New Phone Sales',
        'secondhand'  => 'Pre-Owned Sales',
        'accessories' => 'Accessories Sales',
        'covers'      => 'Back Cover & Tempered Sales',
        'repairs'     => 'Completed Repairs',
        default       => 'Sales Hub & Invoice Register',
    };
@endphp

@section('title', $salesPageTitle . ' — Maurya Mobile ERP')
@section('page-title', $salesPageTitle)

@section('page-actions')
    <div class="flex items-center gap-2 flex-wrap">
        @if($canCreatePhones ?? false)
        <a href="{{ route('mobileshop.sales.create') }}" class="btn btn-primary btn-sm">
            <i data-lucide="plus" style="width:13px;height:13px;"></i> <span class="desktop-btn-label">Register Sale</span><span class="mobile-btn-label">Sale</span>
        </a>
        @endif
        @if($canCreateAccessories ?? false)
        <a href="{{ route('mobileshop.accessories.pos') }}" class="btn btn-outline btn-sm">
            <i data-lucide="zap" style="width:13px;height:13px; color:#2563EB;"></i> <span class="desktop-btn-label">Counter POS Sale</span><span class="mobile-btn-label">POS</span>
        </a>
        @endif
        @if(($canCreateCovers ?? false) && !($canCreateAccessories ?? false))
        <a href="{{ route('mobileshop.accessories.pos', ['category' => 'back_cover']) }}" class="btn btn-outline btn-sm">
            <i data-lucide="package" style="width:13px;height:13px;"></i> <span class="desktop-btn-label">Add Cover / Glass</span><span class="mobile-btn-label">Cover</span>
        </a>
        @endif
        @if($canCreateSecondhand ?? false)
        <button type="button" onclick="openSellShModal()" class="btn btn-outline btn-sm">
            <i data-lucide="refresh-cw" style="width:13px;height:13px;"></i> <span class="desktop-btn-label">Sell Pre-Owned</span><span class="mobile-btn-label">Pre-Owned</span>
        </button>
        @endif
        <a href="{{ route('mobileshop.emi.ledger') }}" class="btn btn-outline btn-sm">
            <i data-lucide="building-2" style="width:13px;height:13px;"></i> <span class="desktop-btn-label">EMI Ledger</span><span class="mobile-btn-label">EMI</span>
        </a>
    </div>
@endsection

@push('styles')
<style>
    /* Safe container padding */
    .sales-page-wrapper {
        position: relative;
    }

    /* Executive KPI Card Design */
    .executive-kpi-card {
        border: 1px solid #E2E8F0;
        border-radius: 8px;
        padding: 10px 14px;
        background: #FFFFFF;
        position: relative;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .executive-kpi-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        border-color: #CBD5E1;
    }
    .kpi-icon-box {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .kpi-icon-box svg, .kpi-icon-box i {
        width: 16px;
        height: 16px;
    }


    .sales-search-wrapper {
        width: 320px;
    }
    .sales-search-box {
        width: 100%;
    }

    /* ─── RESPONSIVE BREAKPOINTS (Mobile < 768px) ─── */
    @media (max-width: 767px) {
        .sales-page-wrapper {
            padding-bottom: 84px !important;
        }
        .desktop-btn-label {
            display: none !important;
        }
        .mobile-btn-label {
            display: inline !important;
        }
        .hide-on-mobile {
            display: none !important;
        }

        /* Hide heavy 2x2 KPI grid on mobile, show compact strip */
        .kpi-cards-grid {
            display: none !important;
        }
        .mobile-stat-strip {
            display: flex !important;
            margin: 0 0 8px 0 !important;
        }

        /* Remove Box-in-Box: Strip outer card border & radius */
        .sales-registry-card {
            border: none !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            background: transparent !important;
        }
        .sales-header-container {
            padding: 8px 10px !important;
            background: #FFFFFF !important;
            border: 1px solid #E2E8F0 !important;
            border-radius: 10px 10px 0 0 !important;
            border-bottom: none !important;
        }
        .sales-header-title-box {
            display: none !important; /* Save vertical space on mobile; registry is main view */
        }
        .sales-search-wrapper {
            width: 100% !important;
        }
        .sales-search-box {
            width: 100% !important;
        }
        .sales-search-input {
            min-height: 38px !important;
            font-size: 12px !important;
            border-radius: 8px !important;
            padding: 6px 68px 6px 34px !important;
        }
        .sales-filter-toolbar {
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
        .desktop-sales-table {
            display: none !important;
        }
        .mobile-sales-cards {
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

        .acc-product-picker-grid {
            grid-template-columns: 170px 1fr auto;
        }
        @media (max-width: 600px) {
            .acc-product-picker-grid {
                grid-template-columns: 1fr !important;
                gap: 8px !important;
            }
            .acc-product-picker-grid button {
                width: 100% !important;
                justify-content: center;
            }
        }
    }

    @media (min-width: 768px) {
        .sales-search-input {
            padding-right: 36px !important;
        }
        .mobile-filter-btn {
            display: none !important;
        }
        .desktop-btn-label {
            display: inline !important;
        }
        .mobile-btn-label {
            display: none !important;
        }
        .desktop-date-inputs {
            display: flex !important;
        }
        .desktop-sales-table {
            display: table !important;
        }
        .mobile-sales-cards {
            display: none !important;
        }
    }
</style>
@endpush

@section('content')

<div class="sales-page-wrapper">

    <!-- Mobile Compact Stat Strip (shown only on mobile) -->
    <div class="mobile-stat-strip">
        <div class="stat-strip-item">
            <span class="stat-label">Today</span>
            <span class="stat-val">₹{{ fmod($todaySalesTotal, 1) != 0 ? number_format($todaySalesTotal, 2) : number_format($todaySalesTotal, 0) }}</span>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-strip-item">
            <span class="stat-label">Month</span>
            <span class="stat-val">₹{{ fmod($monthSalesTotal, 1) != 0 ? number_format($monthSalesTotal, 2) : number_format($monthSalesTotal, 0) }}</span>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-strip-item">
            <span class="stat-label">Invoices</span>
            <span class="stat-val">{{ number_format($salesCount) }}</span>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-strip-item">
            <span class="stat-label">Stock</span>
            <span class="stat-val">{{ $availableNewPhones + $availableSecondHand + $availableParts }}</span>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- TOP KPI METRIC CARDS (Desktop View)                        -->
    <!-- ══════════════════════════════════════════════════════════ -->
    <div class="kpi-cards-grid">
        <!-- Card 1: Today's Revenue -->
        <div class="kpi-card">
            <div class="kpi-top">
                <div class="kpi-label">Today's Revenue</div>
                <div class="kpi-icon-box"><i data-lucide="banknote"></i></div>
            </div>
            <div class="kpi-num">₹{{ fmod($todaySalesTotal, 1) != 0 ? number_format($todaySalesTotal, 2) : number_format($todaySalesTotal, 0) }}</div>
        </div>

        <!-- Card 2: Monthly Inflow -->
        <div class="kpi-card">
            <div class="kpi-top">
                <div class="kpi-label">Monthly Inflow</div>
                <div class="kpi-icon-box"><i data-lucide="trending-up"></i></div>
            </div>
            <div class="kpi-num">₹{{ fmod($monthSalesTotal, 1) != 0 ? number_format($monthSalesTotal, 2) : number_format($monthSalesTotal, 0) }}</div>
        </div>

        <!-- Card 3: Total Invoices -->
        <div class="kpi-card">
            <div class="kpi-top">
                <div class="kpi-label">Total Invoices</div>
                <div class="kpi-icon-box"><i data-lucide="receipt"></i></div>
            </div>
            <div class="kpi-num">{{ number_format($salesCount) }}</div>
        </div>

        <!-- Card 4: Ready Stock -->
        <div class="kpi-card">
            <div class="kpi-top">
                <div class="kpi-label">Ready Stock</div>
                <div class="kpi-icon-box"><i data-lucide="package"></i></div>
            </div>
            <div class="kpi-num">{{ $availableNewPhones + $availableSecondHand + $availableParts }} <span style="font-size:11px; font-weight:400; color:var(--color-ink-muted);">Units</span></div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- SALES REGISTRY TABLE CONTAINER                           -->
    <!-- ══════════════════════════════════════════════════════════ -->
    <div class="card sales-registry-card">
        <!-- Top Title & Search Bar -->
        <div class="sales-header-container">
            <div class="sales-header-title-box flex items-center gap-2">
                <div class="w-6 h-6 rounded-sm bg-primary-tint text-primary flex items-center justify-center flex-shrink-0">
                    <i data-lucide="receipt" style="width: 14px; height: 14px;"></i>
                </div>
                <div>
                    <h2 class="text-xs font-semibold text-ink leading-tight m-0">Live Sales Invoice Registry</h2>
                </div>
            </div>

            <!-- Full Width Search Bar with Embedded Date Filter Trigger -->
            <div class="sales-search-wrapper relative">
                <div class="search-bar">
                    <i data-lucide="search"></i>
                    <input type="text" id="salesSearchInput" oninput="filterSalesTable()" placeholder="Search invoice, customer, IMEI..." class="sales-search-input">
                    <button type="button" onclick="clearSalesSearch()" id="btnClearSearch" style="display:none; background: #e2e4e8; border: none; border-radius: 50%; width: 16px; height: 16px; color: #4f535b; cursor: pointer; font-size: 10px; line-height: 16px; text-align: center; padding: 0;">✕</button>
                    <button type="button" onclick="openDateFilterDrawer()" id="btnMobileDateFilter" class="mobile-filter-btn" title="Filter by Date Range" style="height: 22px; width: 22px; padding: 0; border-radius: 4px; background: #ffffff; border: 1px solid #e2e4e8; color: #5e6ad2; align-items: center; justify-content: center; cursor: pointer; position: relative;">
                        <i data-lucide="calendar" style="width: 12px; height: 12px;"></i>
                        <span id="dateFilterActiveIndicator" style="display:none; position: absolute; top: 2px; right: 2px; width: 4px; height: 4px; border-radius: 50%; background: #5e6ad2;"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Integrated Category & Date Presets & Custom Range Toolbar -->
        <div class="filter-bar sales-filter-toolbar">
            <!-- Left: Horizontal Scrollable Category & Date Preset Pills -->
            <div class="date-pills-scroll-rail">
                @php
                    $showSalesPhonePill = ($isAdmin ?? false) || ($canCreatePhones ?? false) || ($niche ?? '') === 'phones';
                    $showSalesPartsPill = ($isAdmin ?? false) || ($canCreateAccessories ?? false) || ($canCreateCovers ?? false) || in_array($niche ?? '', ['accessories', 'covers']);
                    $showSalesSecondhandPill = ($isAdmin ?? false) || ($canCreateSecondhand ?? false) || ($niche ?? '') === 'secondhand';
                    $availableSalesPills = ($showSalesPhonePill ? 1 : 0) + ($showSalesPartsPill ? 1 : 0) + ($showSalesSecondhandPill ? 1 : 0);
                @endphp

                @if($availableSalesPills > 1)
                    <button type="button" onclick="setSalesCategoryFilter('all', this)" class="filter-pill sales-cat-pill active">All ({{ count($mobileSales) + count($accSales) }})</button>
                    @if($showSalesPhonePill)
                    <button type="button" onclick="setSalesCategoryFilter('phone', this)" class="filter-pill sales-cat-pill">📱 Phones ({{ $mobileSales->where('device_type', '!=', 'second_hand')->count() }})</button>
                    @endif
                    @if($showSalesPartsPill)
                    <button type="button" onclick="setSalesCategoryFilter('accessory', this)" class="filter-pill sales-cat-pill">📦 Parts ({{ count($accSales) }})</button>
                    @endif
                    @if($showSalesSecondhandPill)
                    <button type="button" onclick="setSalesCategoryFilter('secondhand', this)" class="filter-pill sales-cat-pill">🔄 Pre-Owned ({{ $mobileSales->where('device_type', 'second_hand')->count() }})</button>
                    @endif
                    <div style="width:1px; height:18px; background:#CBD5E1; margin:0 4px; flex-shrink:0; display:inline-block; vertical-align:middle;"></div>
                @endif

                <span style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: #64748B; margin-right: 4px; display: inline-flex; align-items: center; gap: 4px; flex-shrink: 0;">
                    <i data-lucide="calendar" style="width: 12px; height: 12px;"></i>
                </span>
                <button type="button" onclick="setSalesDatePreset('all')" id="salesDateBtn_all" class="filter-pill sales-date-pill active">All Time</button>
                <button type="button" onclick="setSalesDatePreset('today')" id="salesDateBtn_today" class="filter-pill sales-date-pill">Today</button>
                <button type="button" onclick="setSalesDatePreset('yesterday')" id="salesDateBtn_yesterday" class="filter-pill sales-date-pill">Yesterday</button>
                <button type="button" onclick="setSalesDatePreset('week')" id="salesDateBtn_week" class="filter-pill sales-date-pill">Last 7 Days</button>
                <button type="button" onclick="setSalesDatePreset('month')" id="salesDateBtn_month" class="filter-pill sales-date-pill">This Month</button>
            </div>

            <!-- Right: Desktop Custom Date Range Inputs & Reset Button -->
            <div class="desktop-date-inputs" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; background: #FFFFFF; border: 1px solid #CBD5E1; border-radius: 8px; padding: 3px 8px; gap: 6px; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                    <span style="font-size: 11px; font-weight: 700; color: #64748B;">From:</span>
                    <input type="date" id="salesFromDate" onchange="onSalesCustomDateChange()" style="border: none; outline: none; font-size: 11px; font-weight: 700; color: #0F172A; background: transparent; cursor: pointer;">
                </div>
                <div style="display: flex; align-items: center; background: #FFFFFF; border: 1px solid #CBD5E1; border-radius: 8px; padding: 3px 8px; gap: 6px; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                    <span style="font-size: 11px; font-weight: 700; color: #64748B;">To:</span>
                    <input type="date" id="salesToDate" onchange="onSalesCustomDateChange()" style="border: none; outline: none; font-size: 11px; font-weight: 700; color: #0F172A; background: transparent; cursor: pointer;">
                </div>
                <button type="button" onclick="setSalesDatePreset('all')" title="Reset Date Filter" style="background: #FFFFFF; border: 1px solid #CBD5E1; color: #475569; font-weight: 700; font-size: 11px; border-radius: 8px; padding: 5px 10px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; transition: all 0.15s ease;" onmouseover="this.style.background='#F1F5F9';" onmouseout="this.style.background='#FFFFFF';">
                    <i data-lucide="rotate-ccw" style="width: 12px; height: 12px;"></i> Reset
                </button>
            </div>
        </div>
        <div class="card-body" style="padding:0; overflow-x:auto;">
            <table class="data-table desktop-sales-table" id="salesTable">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Customer</th>
                        <th>Item Details</th>
                        <th>Payment</th>
                        <th style="text-align:right;">Amount (₹)</th>
                        <th style="text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mobileSales as $sale)
                    <tr class="sales-row" data-type="{{ ($sale->device_type ?? '') === 'second_hand' ? 'secondhand' : 'phone' }}" data-date="{{ \Carbon\Carbon::parse($sale->created_at)->format('Y-m-d') }}">
                        <td>
                            <a href="{{ route('mobileshop.invoice', ['id' => $sale->id]) }}" style="font-family:monospace; font-weight:800; color:var(--brand-700); text-decoration:none;">
                                {{ $sale->invoice_number }}
                            </a>
                        </td>
                        <td>
                            <div style="font-weight:700; color:#0F172A; font-size:13px;">{{ $sale->customer_name }}</div>
                            <div style="font-size:11px; color:#64748B;">{{ !empty($isOwner) ? ($sale->customer_phone ?: '—') : \App\Http\Controllers\MobileShop\BaseMobileShopController::maskPhone($sale->customer_phone) }}</div>
                        </td>
                        <td>
                            <div style="font-weight:700; font-size:12px;">{{ $sale->brand }} {{ $sale->model }}</div>
                            <div style="font-size:10px; color:#64748B; font-family:monospace;">IMEI: {{ !empty($isOwner) ? $sale->imei_1 : \App\Http\Controllers\MobileShop\BaseMobileShopController::maskImei($sale->imei_1) }}</div>
                        </td>
                        <td>
                            <span class="badge badge-blue" style="text-transform:uppercase; font-size:10px;">{{ $sale->payment_mode }}</span>
                        </td>
                        <td style="text-align:right; font-weight:900; color:#0F172A; font-size:14px;">
                            ₹{{ number_format($sale->total_amount, 2) }}
                        </td>
                        <td style="text-align:center; white-space:nowrap;">
                            <div style="display:inline-flex; gap:6px; align-items:center;">
                                <a href="{{ route('mobileshop.invoice', ['id' => $sale->id]) }}" class="btn btn-outline btn-icon" title="View Bill & Print">
                                    <i data-lucide="printer" style="width:14px;height:14px;"></i>
                                </a>
                                <a href="{{ route('mobileshop.sales.whatsapp', ['id' => $sale->id]) }}" target="_blank" class="btn btn-outline btn-icon" style="color:#16A34A; border-color:#BBF7D0; background:#F0FDF4;" title="Share Invoice on WhatsApp">
                                    <svg style="width:14px;height:14px;fill:currentColor;" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                </a>
                                @if(($sale->status ?? '') !== 'voided')
                                    <button type="button" onclick="handleReturnButtonClick(this, 'mobile', {{ $sale->id }}, '{{ $sale->invoice_number }}', '{{ number_format($sale->total_amount, 2) }}')" data-items="{{ json_encode($sale->items ?? []) }}" class="btn btn-outline btn-icon" style="color:#DC2626; border-color:#FECDD3; background:#FFF1F2; cursor:pointer;" title="Process Return & Restock">
                                        <i data-lucide="rotate-ccw" style="width:14px;height:14px;"></i>
                                    </button>
                                @else
                                    <span class="badge" style="background:#FEE2E2; color:#DC2626; font-size:10px; font-weight:700;">Returned</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    @endforelse

                    @forelse($accSales as $asale)
                    <tr class="sales-row" data-type="accessory" data-date="{{ \Carbon\Carbon::parse($asale->created_at)->format('Y-m-d') }}">
                        <td>
                            <a href="{{ route('mobileshop.accessories.invoice', ['id' => $asale->id]) }}" style="font-family:monospace; font-weight:800; color:var(--brand-700); text-decoration:none;">
                                {{ $asale->invoice_number }}
                            </a>
                        </td>
                        <td>
                            <div style="font-weight:700; color:#0F172A; font-size:13px;">{{ $asale->customer_name ?: 'Walk-in Retail' }}</div>
                            <div style="font-size:11px; color:#64748B;">{{ !empty($isOwner) ? ($asale->customer_phone ?: '—') : \App\Http\Controllers\MobileShop\BaseMobileShopController::maskPhone($asale->customer_phone) }}</div>
                        </td>
                        <td>
                            @if(!empty($asale->items) && count($asale->items) > 0)
                                <div style="font-weight:700; font-size:12px; color:#0F172A;">
                                    {{ $asale->items->pluck('part_name')->take(2)->implode(', ') }}{{ count($asale->items) > 2 ? ' (+' . (count($asale->items) - 2) . ' more)' : '' }}
                                </div>
                                <div style="font-size:10.5px; color:#64748B;">{{ count($asale->items) }} item(s) ordered</div>
                            @else
                                <div style="font-weight:700; font-size:12px;">Parts & Accessories Order</div>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-blue" style="text-transform:uppercase; font-size:10px;">{{ $asale->payment_mode }}</span>
                        </td>
                        <td style="text-align:right; font-weight:900; color:#0F172A; font-size:14px;">
                            ₹{{ number_format($asale->total_amount, 2) }}
                        </td>
                        <td style="text-align:center; white-space:nowrap;">
                            <div style="display:inline-flex; gap:6px; align-items:center;">
                                <a href="{{ route('mobileshop.accessories.invoice', ['id' => $asale->id]) }}" class="btn btn-outline btn-icon" title="View Bill & Print">
                                    <i data-lucide="printer" style="width:14px;height:14px;"></i>
                                </a>
                                <a href="{{ route('mobileshop.accessories.whatsapp', ['id' => $asale->id]) }}" target="_blank" class="btn btn-outline btn-icon" style="color:#16A34A; border-color:#BBF7D0; background:#F0FDF4;" title="Share Invoice on WhatsApp">
                                    <svg style="width:14px;height:14px;fill:currentColor;" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                </a>
                                @if(($asale->status ?? '') !== 'voided')
                                    <button type="button" onclick="handleReturnButtonClick(this, 'accessory', {{ $asale->id }}, '{{ $asale->invoice_number }}', '{{ number_format($asale->total_amount, 2) }}')" data-items="{{ json_encode($asale->items ?? []) }}" class="btn btn-outline btn-icon" style="color:#DC2626; border-color:#FECDD3; background:#FFF1F2; cursor:pointer;" title="Process Return & Restock">
                                        <i data-lucide="rotate-ccw" style="width:14px;height:14px;"></i>
                                    </button>
                                @else
                                    <span class="badge" style="background:#FEE2E2; color:#DC2626; font-size:10px; font-weight:700;">Returned</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    @endforelse
                    <tr id="salesEmptyFilterRow" style="display:none;">
                        <td colspan="6" style="text-align:center; padding:36px 16px; color:#64748B;">
                            <div style="display:inline-flex; flex-direction:column; align-items:center; gap:8px;">
                                <div style="width:38px; height:38px; border-radius:50%; background:#F1F5F9; display:flex; align-items:center; justify-content:center; color:#64748B;">
                                    <i data-lucide="calendar-x" style="width:20px; height:20px;"></i>
                                </div>
                                <div style="font-weight:700; color:#1E293B; font-size:13px;">No sales found for this date range</div>
                                <div style="font-size:11.5px; color:#64748B;">Try selecting a different date preset (e.g. Yesterday, This Month) or reset filters</div>
                                <button type="button" onclick="setSalesDatePreset('all')" class="filter-pill" style="margin-top:6px; cursor:pointer; background:#5E6AD2; color:#fff; border:none; padding:4px 12px; border-radius:6px; font-weight:700; font-size:11.5px;">
                                    Show All Sales
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- ══════════════════════════════════════════════════════════ -->
            <!-- MOBILE FLAT LIST VIEW (Displayed on mobile screens < 768px)-->
            <!-- ══════════════════════════════════════════════════════════ -->
            <div id="salesMobileCards" class="mobile-sales-cards">
                @forelse($mobileSales as $sale)
                    @php
                        $pm = strtolower($sale->payment_mode ?? 'cash');
                        if (str_contains($pm, 'cash')) {
                            $badgeBg = '#DCFCE7'; $badgeColor = '#15803D';
                        } elseif (str_contains($pm, 'upi')) {
                            $badgeBg = '#EFF6FF'; $badgeColor = '#1D4ED8';
                        } elseif (str_contains($pm, 'udhari') || str_contains($pm, 'credit')) {
                            $badgeBg = '#FEF2F2'; $badgeColor = '#B91C1C';
                        } elseif (str_contains($pm, 'card')) {
                            $badgeBg = '#F5F3FF'; $badgeColor = '#7C3AED';
                        } else {
                            $badgeBg = '#F1F5F9'; $badgeColor = '#475569';
                        }
                    @endphp
                    <div class="sales-flat-row sales-row" data-type="{{ ($sale->device_type ?? '') === 'second_hand' ? 'secondhand' : 'phone' }}" data-date="{{ \Carbon\Carbon::parse($sale->created_at)->format('Y-m-d') }}">
                        <!-- Row 1: Invoice # + Payment Badge + Total Amount -->
                        <div class="row-line1">
                            <a href="{{ route('mobileshop.invoice', ['id' => $sale->id]) }}" class="inv-num">
                                {{ $sale->invoice_number }}
                            </a>
                            <span class="pay-badge" style="background:{{ $badgeBg }}; color:{{ $badgeColor }};">
                                {{ $sale->payment_mode }}
                            </span>
                            <div class="row-amount">₹{{ number_format($sale->total_amount, 2) }}</div>
                        </div>

                        <!-- Row 2: Customer Name + Phone -->
                        <div class="row-line2">
                            <div class="cust-name">{{ $sale->customer_name }}</div>
                            <div class="cust-phone">{{ !empty($isOwner) ? ($sale->customer_phone ?: '—') : \App\Http\Controllers\MobileShop\BaseMobileShopController::maskPhone($sale->customer_phone) }}</div>
                        </div>

                        <!-- Row 3: Items Summary + Compact Actions -->
                        <div class="row-line3">
                            <div class="items-summary">
                                <strong>{{ $sale->brand }} {{ $sale->model }}</strong>
                                @if(!empty($sale->ram))<span>({{ $sale->ram }}/{{ $sale->storage ?? '' }})</span>@endif
                                • IMEI: {{ !empty($isOwner) ? $sale->imei_1 : \App\Http\Controllers\MobileShop\BaseMobileShopController::maskImei($sale->imei_1) }}
                            </div>
                            <div class="row-actions">
                                <a href="{{ route('mobileshop.sales.whatsapp', ['id' => $sale->id]) }}" target="_blank" class="compact-action-btn mobile-whatsapp-btn" title="Share on WhatsApp">
                                    <svg style="width:16px;height:16px;fill:currentColor;" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                </a>
                                <a href="{{ route('mobileshop.invoice', ['id' => $sale->id]) }}" class="compact-action-btn mobile-print-btn" title="View Bill & Print">
                                    <i data-lucide="printer" style="width:15px;height:15px;"></i>
                                </a>
                                @if(($sale->status ?? '') !== 'voided')
                                    <button type="button" onclick="handleReturnButtonClick(this, 'mobile', {{ $sale->id }}, '{{ $sale->invoice_number }}', '{{ number_format($sale->total_amount, 2) }}')" data-items="{{ json_encode($sale->items ?? []) }}" class="compact-action-btn mobile-return-btn" title="Process Return & Restock">
                                        <i data-lucide="rotate-ccw" style="width:14px;height:14px;"></i>
                                    </button>
                                @else
                                    <span style="font-size:9.5px; font-weight:700; color:#DC2626; background:#FEE2E2; padding:3px 6px; border-radius:4px;">Void</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                @endforelse

                @forelse($accSales as $asale)
                    @php
                        $apm = strtolower($asale->payment_mode ?? 'cash');
                        if (str_contains($apm, 'cash')) {
                            $badgeBg = '#DCFCE7'; $badgeColor = '#15803D';
                        } elseif (str_contains($apm, 'upi')) {
                            $badgeBg = '#EFF6FF'; $badgeColor = '#1D4ED8';
                        } elseif (str_contains($apm, 'udhari') || str_contains($apm, 'credit')) {
                            $badgeBg = '#FEF2F2'; $badgeColor = '#B91C1C';
                        } elseif (str_contains($apm, 'card')) {
                            $badgeBg = '#F5F3FF'; $badgeColor = '#7C3AED';
                        } else {
                            $badgeBg = '#F1F5F9'; $badgeColor = '#475569';
                        }

                        $itemsSummary = 'Parts & Accessories Order';
                        if (!empty($asale->items) && count($asale->items) > 0) {
                            $itemsSummary = count($asale->items) . ' item(s) • ' . $asale->items->pluck('part_name')->take(2)->implode(', ');
                            if (count($asale->items) > 2) {
                                $itemsSummary .= ' (+' . (count($asale->items) - 2) . ' more)';
                            }
                        }
                    @endphp
                    <div class="sales-flat-row sales-row" data-type="accessory" data-date="{{ \Carbon\Carbon::parse($asale->created_at)->format('Y-m-d') }}">
                        <!-- Row 1: Invoice # + Payment Badge + Total Amount -->
                        <div class="row-line1">
                            <a href="{{ route('mobileshop.accessories.invoice', ['id' => $asale->id]) }}" class="inv-num">
                                {{ $asale->invoice_number }}
                            </a>
                            <span class="pay-badge" style="background:{{ $badgeBg }}; color:{{ $badgeColor }};">
                                {{ $asale->payment_mode }}
                            </span>
                            <div class="row-amount">₹{{ number_format($asale->total_amount, 2) }}</div>
                        </div>

                        <!-- Row 2: Customer Name + Phone -->
                        <div class="row-line2">
                            <div class="cust-name">{{ $asale->customer_name ?: 'Walk-in Retail' }}</div>
                            <div class="cust-phone">{{ !empty($isOwner) ? ($asale->customer_phone ?: '—') : \App\Http\Controllers\MobileShop\BaseMobileShopController::maskPhone($asale->customer_phone) }}</div>
                        </div>

                        <!-- Row 3: Items Summary + Compact Actions -->
                        <div class="row-line3">
                            <div class="items-summary">
                                {{ $itemsSummary }}
                            </div>
                            <div class="row-actions">
                                <a href="{{ route('mobileshop.accessories.whatsapp', ['id' => $asale->id]) }}" target="_blank" class="compact-action-btn mobile-whatsapp-btn" title="Share on WhatsApp">
                                    <svg style="width:16px;height:16px;fill:currentColor;" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                </a>
                                <a href="{{ route('mobileshop.accessories.invoice', ['id' => $asale->id]) }}" class="compact-action-btn mobile-print-btn" title="View Bill & Print">
                                    <i data-lucide="printer" style="width:15px;height:15px;"></i>
                                </a>
                                @if(($asale->status ?? '') !== 'voided')
                                    <button type="button" onclick="handleReturnButtonClick(this, 'accessory', {{ $asale->id }}, '{{ $asale->invoice_number }}', '{{ number_format($asale->total_amount, 2) }}')" data-items="{{ json_encode($asale->items ?? []) }}" class="compact-action-btn mobile-return-btn" title="Process Return & Restock">
                                        <i data-lucide="rotate-ccw" style="width:14px;height:14px;"></i>
                                    </button>
                                @else
                                    <span style="font-size:9.5px; font-weight:700; color:#DC2626; background:#FEE2E2; padding:3px 6px; border-radius:4px;">Void</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                @endforelse
                <div id="salesMobileEmptyFilterRow" style="display:none; text-align:center; padding:28px 16px; color:#64748B;">
                    <div style="font-weight:700; color:#1E293B; font-size:13px; margin-bottom:4px;">No sales found for this date range</div>
                    <div style="font-size:11.5px; color:#64748B; margin-bottom:10px;">Try selecting Yesterday, This Month, or All Time</div>
                    <button type="button" onclick="setSalesDatePreset('all')" class="filter-pill" style="cursor:pointer; background:#5E6AD2; color:#fff; border:none; padding:5px 14px; border-radius:6px; font-weight:700; font-size:11.5px;">
                        Show All Sales
                    </button>
                </div>
            </div>
            <div id="salesPagination"></div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- MODAL: Sales Return / Restock Void Confirmation -->
    <!-- ══════════════════════════════════════════════════════════ -->
    <div id="salesReturnModal" style="display:none; position: fixed; inset: 0; z-index: 1200; background: rgba(15,23,42,0.65); backdrop-filter: blur(4px); align-items:center; justify-content:center; padding: 16px;">
        <div class="card" style="max-width: 520px; width: 100%; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); border-radius:14px; border:none; background:#fff;">
            <div style="background:#DC2626; color:#fff; padding:16px 20px; border-top-left-radius:14px; border-top-right-radius:14px; display:flex; justify-content:space-between; align-items:center;">
                <div style="font-size:15px; font-weight:800; display:flex; align-items:center; gap:8px;">
                    <i data-lucide="rotate-ccw" style="width:18px;height:18px;"></i>
                    Process Sales Return & Refund
                </div>
                <button type="button" onclick="closeReturnModal()" style="background:rgba(255,255,255,0.15); border:none; color:#FFFFFF; font-size:16px; width:28px; height:28px; border-radius:6px; cursor:pointer; display:flex; align-items:center; justify-content:center;">✕</button>
            </div>
            <div class="card-body" style="padding:20px;">
                <form id="salesReturnForm" method="POST" action="">
                    @csrf
                    <input type="hidden" name="reason_label" id="returnReasonLabel" value="Defective / Faulty Item (Dead on Arrival)">
                    <input type="hidden" name="should_restock" id="returnShouldRestock" value="0">

                    <!-- Invoice & Refund Summary -->
                    <div style="background:#FEF2F2; border:1px solid #FECDD3; border-radius:8px; padding:12px 14px; margin-bottom:14px; display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <div style="font-size:11px; color:#991B1B; font-weight:700; text-transform:uppercase;">Invoice Number</div>
                            <span id="lblReturnInvoice" style="font-family:monospace; font-weight:800; font-size:14px; color:#0F172A;"></span>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-size:11px; color:#991B1B; font-weight:700; text-transform:uppercase;">Total Refund Amount</div>
                            <strong id="lblReturnAmount" style="font-size:16px; color:#DC2626; font-weight:900;"></strong>
                        </div>
                    </div>

                    <!-- Dynamic Line-Item Selection for Multi-Item Invoices -->
                    <div id="returnItemsSection" style="margin-bottom:14px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                            <label style="font-weight:800; color:#0F172A; font-size:12px; margin:0; display:flex; align-items:center; gap:4px;">
                                <i data-lucide="layers" style="width:14px;height:14px; color:#DC2626;"></i>
                                Select Items to Return
                            </label>
                            <span id="lblItemCountNotice" style="font-size:11px; color:#64748B; font-weight:600;"></span>
                        </div>
                        <div id="returnItemsList" style="border:1px solid #CBD5E1; border-radius:8px; background:#F8FAFC; max-height:190px; overflow-y:auto; padding:6px; display:flex; flex-direction:column; gap:6px;">
                            <!-- Injected dynamically via JS -->
                        </div>
                    </div>

                    <!-- 6 Return Reason Options -->
                    <div class="form-group" style="margin-bottom:12px;">
                        <label class="form-label required" style="font-weight:700; color:#0F172A; font-size:12px; margin-bottom:4px;">Select Reason for Return</label>
                        <select name="return_reason_code" id="returnReasonSelect" class="form-control" onchange="onReturnReasonSelectChange(this.value)" style="font-weight:700; color:#0F172A; border-color:#CBD5E1;">
                            <option value="defective_doa" data-restock="0" data-label="Defective / Faulty Item (Dead on Arrival)">1. Defective / Faulty Item (Dead on Arrival)</option>
                            <option value="model_mismatch" data-restock="1" data-label="Incorrect Model / Size Mismatch">2. Incorrect Model / Size Mismatch</option>
                            <option value="mind_change" data-restock="1" data-label="Customer Changed Mind / Unwanted">3. Customer Changed Mind / Unwanted</option>
                            <option value="billing_error" data-restock="1" data-label="Billing Error / Duplicate Entry">4. Billing Error / Duplicate Entry</option>
                            <option value="exchange" data-restock="1" data-label="Exchange for Different Product">5. Exchange for Different Product</option>
                            <option value="warranty_issue" data-restock="0" data-label="Warranty / Quality Issue">6. Warranty / Quality Issue</option>
                        </select>
                    </div>

                    <!-- Dynamic Action Preview Notice -->
                    <div id="returnActionNotice" style="background:#FFF1F2; border:1px solid #FECDD3; border-radius:8px; padding:10px 12px; margin-bottom:12px; font-size:12px; color:#991B1B; display:flex; align-items:flex-start; gap:8px;">
                        <i data-lucide="alert-triangle" style="width:16px;height:16px; flex-shrink:0; margin-top:1px;"></i>
                        <span id="returnActionText"><strong>Defective / Damaged Item:</strong> Item will NOT be added to sellable stock. Marked as Quarantined / Scrap.</span>
                    </div>

                    <!-- Restock Override Checkbox -->
                    <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:10px 12px; margin-bottom:14px; display:flex; align-items:center; gap:8px;">
                        <input type="checkbox" id="chkRestockOverride" onchange="onRestockOverrideChange(this.checked)" style="width:16px; height:16px; cursor:pointer;">
                        <label for="chkRestockOverride" style="font-size:12px; font-weight:700; color:#1E293B; margin-bottom:0; cursor:pointer;">
                            Add items back into sellable store inventory
                        </label>
                    </div>

                    <!-- Optional Remarks / Note -->
                    <div class="form-group" style="margin-bottom:16px;">
                        <label class="form-label" style="font-weight:700; color:#475569; font-size:12px; margin-bottom:4px;">Additional Remarks / Note (Optional)</label>
                        <input type="text" name="void_reason" id="returnReasonInput" placeholder="e.g. Glass cracked inside pack / Exchanged for Matte..." class="form-control" style="font-weight:600; color:#0F172A; border-color:#CBD5E1;">
                    </div>

                    <div style="display:flex; justify-content:flex-end; gap: 10px; padding-top: 12px; border-top: 1px solid #E2E8F0;">
                        <button type="button" onclick="closeReturnModal()" class="btn btn-outline" style="border:1px solid #CBD5E1; color:#475569; font-weight:700; padding:8px 18px; border-radius:8px; cursor:pointer;">Cancel</button>
                        <button type="submit" class="btn btn-primary" style="background:#DC2626; color:#FFFFFF; font-weight:800; padding:8px 20px; border:none; border-radius:8px; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                            <i data-lucide="rotate-ccw" style="width:14px;height:14px;"></i> Confirm Return & Refund
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- MODAL: Quick Sell / Retail POS (Multi-Item Cart) -->
    <!-- ══════════════════════════════════════════════════════════ -->
    <div id="sellAccessoryModal" style="display:none; position: fixed; inset: 0; z-index: 1200; background: rgba(15,23,42,0.6); backdrop-filter: blur(4px); align-items:center; justify-content:center; padding: 16px;">
        <div class="card" style="max-width: 720px; width: 100%; max-height: 92vh; overflow-y:auto; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); border-radius:14px; border:none; background:#fff;">
            <div style="background:#0F172A; color:#fff; padding:18px 22px; border-top-left-radius:14px; border-top-right-radius:14px; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <div style="color:#FFFFFF; font-size:16px; font-weight:800; display:flex; align-items:center; gap:8px;">
                        <i data-lucide="zap" style="width:18px;height:18px; color:#FACC15;"></i>
                        Accessories, Glass & Covers Counter POS
                    </div>
                    <div style="color:#94A3B8; font-size:12px; margin-top:2px;">Fast retail billing for accessories, tempered glass, back covers, chargers & parts</div>
                </div>
                <button type="button" onclick="closeSellAccessoryModal()" style="background:rgba(255,255,255,0.1); border:none; color:#FFFFFF; font-size:16px; width:30px; height:30px; border-radius:6px; cursor:pointer; display:flex; align-items:center; justify-content:center;">✕</button>
            </div>
            <div class="card-body" style="padding:22px;">
                <form action="{{ route('mobileshop.accessories.sale') }}" method="POST" id="accSaleForm" onsubmit="if(typeof MT !== 'undefined'){ MT.enqueue('create', 'ms_accessory_sales', {source:'accSaleForm', action:'sellAccessory'}); } return validateAndSubmitAccSale(this);">
                    @csrf
                    <input type="hidden" name="idempotency_key" value="{{ \Illuminate\Support\Str::uuid() }}">

                    <!-- Customer Details -->
                    <div class="form-row" style="margin-bottom: 16px; display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label required" style="font-weight:700; color:#0F172A; font-size:12px;">Customer Mobile Number</label>
                            <input type="text" name="customer_phone" id="accCustomerPhone" list="accCustomerList" placeholder="10-digit Mobile Number" required class="form-control" style="font-weight:700; color:#0F172A; border-color:#CBD5E1;">
                            <datalist id="accCustomerList">
                                @foreach($customers ?? [] as $c)
                                    <option value="{{ $c->phone }}" data-name="{{ $c->name }}" data-gstin="{{ $c->gstin ?? '' }}" data-address="{{ $c->address ?? '' }}" data-balance="{{ $c->udhari_balance ?? 0 }}">
                                        {{ $c->name }} (Pending Khata: ₹{{ number_format($c->udhari_balance ?? 0, 2) }})
                                    </option>
                                @endforeach
                            </datalist>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label required" style="font-weight:700; color:#0F172A; font-size:12px;">Customer Full Name</label>
                            <input type="text" name="customer_name" id="accCustomerName" placeholder="Full Name" required class="form-control" style="font-weight:700; color:#0F172A; border-color:#CBD5E1;">
                        </div>
                    </div>

                    <!-- Bill Type / GST Checkbox -->
                    <div style="background: #F8FAFC; border: 1.5px solid #E2E8F0; border-radius: 10px; padding: 12px 14px; margin-bottom: 16px;">
                        <label style="display:flex; align-items:center; gap:10px; cursor:pointer; margin:0; user-select:none;">
                            <input type="checkbox" name="is_gst" id="accIsGstCheckbox" value="1" style="width:18px; height:18px; accent-color:#5E6AD2; cursor:pointer;" onchange="document.getElementById('accBillType').value = this.checked ? 'gst' : 'non_gst'">
                            <div>
                                <span style="font-size:13px; font-weight:700; color:#0F172A;">Make GST Bill (18% Tax Invoice)</span>
                                <p style="font-size:11px; color:#64748B; margin:1px 0 0 0;">Unchecked by default (Standard Retail / Non-GST Estimate)</p>
                            </div>
                        </label>
                        <input type="hidden" name="bill_type" id="accBillType" value="non_gst">
                    </div>

                    <!-- Category Filter & Searchable Item Input -->
                    <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:12px; padding:16px; margin-bottom:16px;">
                        <div style="font-size:11px; font-weight:800; color:#475569; text-transform:uppercase; margin-bottom:10px; display:flex; align-items:center; gap:6px;">
                            <i data-lucide="search" style="width:14px;height:14px; color:#64748B;"></i> Select Product to Bill
                        </div>
                        <div class="acc-product-picker-grid" style="display:grid; gap:10px; align-items:flex-end;">
                            <!-- Category Filter Dropdown -->
                            <div>
                                <label class="form-label" style="font-size:11px; font-weight:700; color:#475569; margin-bottom:4px;">Category</label>
                                @php
                                    $allCatsInStore = collect($partsList ?? [])->pluck('category')->filter()->unique()->values();
                                @endphp
                                <select id="accCategoryFilter" class="form-control" onchange="onCategoryFilterChange(this.value)" style="font-weight:600; color:#0F172A; border-color:#CBD5E1;">
                                    <option value="">-- All Accessories, Glass & Covers --</option>
                                    @foreach($allCatsInStore as $cSlug)
                                        <option value="{{ $cSlug }}">{{ ucwords(str_replace('_', ' ', $cSlug)) }}</option>
                                    @endforeach
                                    @foreach($categories ?? [] as $cat)
                                        @if(!empty($cat->slug) && !$allCatsInStore->contains($cat->slug))
                                            <option value="{{ $cat->slug }}">{{ $cat->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            <!-- Searchable Item Input Field with Custom Dropdown -->
                            <div style="position:relative;" id="accItemSearchWrapper">
                                <label class="form-label" style="font-size:11px; font-weight:700; color:#475569; margin-bottom:4px;">Type & Search Item</label>
                                <input type="text" id="accItemSearchInput" placeholder="Type name, brand, or model..." class="form-control" style="font-weight:700; color:#0F172A; border-color:#CBD5E1;" autocomplete="off" oninput="onLiveSearchInput(this.value)" onfocus="onLiveSearchInput(this.value)" onkeydown="handleSearchInputKey(event)">
                                
                                <!-- Custom Styled Search Dropdown Menu -->
                                <div id="accSearchDropdownMenu" style="display:none; position:absolute; left:0; right:0; top:calc(100% + 4px); max-height:220px; overflow-y:auto; background:#ffffff; border:1px solid #CBD5E1; border-radius:8px; box-shadow:0 12px 28px -4px rgba(0,0,0,0.18); z-index:300;">
                                    <!-- Dynamic items populated by JavaScript -->
                                </div>
                            </div>

                            <!-- Add to Cart Button -->
                            <div>
                                <button type="button" class="btn btn-primary" onclick="addSearchedItemToCart()" style="background:#2563EB; color:#FFFFFF; font-weight:800; padding:9px 18px; display:inline-flex; align-items:center; gap:6px; border:none; border-radius:8px; cursor:pointer;">
                                    <i data-lucide="plus" style="width:15px;height:15px;"></i> Add to Bill
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Multi-Item Cart Table -->
                    <div style="margin-bottom: 16px; border:1px solid #E2E8F0; border-radius:10px; overflow:hidden;">
                        <table class="data-table" style="margin:0; width:100%;">
                            <thead style="background:#F1F5F9;">
                                <tr>
                                    <th style="color:#475569; font-weight:700; font-size:12px;">Item Description</th>
                                    <th style="width:80px; text-align:center; color:#475569; font-weight:700; font-size:12px;">Qty</th>
                                    <th style="width:110px; text-align:right; color:#475569; font-weight:700; font-size:12px;">Rate (₹)</th>
                                    <th style="width:110px; text-align:right; color:#475569; font-weight:700; font-size:12px;">Total (₹)</th>
                                    <th style="width:40px; text-align:center;"></th>
                                </tr>
                            </thead>
                            <tbody id="accCartTableBody">
                                <tr id="emptyCartRow">
                                    <td colspan="5" style="text-align:center; padding:24px; color:#64748B; font-size:13px;">
                                        No items in cart yet. Select a product above to add to this counter sale.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Payment Details -->
                    <div class="form-row" style="margin-bottom: 14px; display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label required" style="font-weight:700; color:#0F172A; font-size:12px;">Payment Method</label>
                            <select name="payment_mode" id="accPaymentMode" required class="form-control" onchange="onAccPaymentModeChange(this)" style="font-weight:700; color:#0F172A; border-color:#CBD5E1;">
                                <option value="cash">💵 Cash</option>
                                <option value="upi">📱 UPI / QR</option>
                                <option value="card">💳 Card</option>
                                <option value="credit_udhari">📒 Full Udhari (Khata)</option>
                                <option value="split">⚖️ Split Payment</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                                <label class="form-label required" style="font-weight:700; color:#0F172A; font-size:12px; margin-bottom:0;">Amount Paid Now (₹)</label>
                                <div style="display:flex; gap:4px;">
                                    <button type="button" onclick="setFullPayment()" style="font-size:10px; padding:2px 8px; border-radius:4px; font-weight:700; color:#16A34A; border:1px solid #BBF7D0; background:#F0FDF4; cursor:pointer;">Full Paid</button>
                                    <button type="button" onclick="setZeroPayment()" style="font-size:10px; padding:2px 8px; border-radius:4px; font-weight:700; color:#DC2626; border:1px solid #FECDD3; background:#FFF1F2; cursor:pointer;">Udhari (₹0)</button>
                                </div>
                            </div>
                            <input type="number" step="0.01" name="amount_paid" id="accAmountPaid" required placeholder="0.00" class="form-control" style="font-size:16px; font-weight:800; color:#16A34A; border-color:#CBD5E1;" oninput="onAmountPaidManualInput()">
                        </div>
                    </div>

                    <!-- Bill Calculation Summary Card -->
                    <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:10px; padding:14px 18px; margin-bottom:16px;">
                        <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:6px; color:#475569;">
                            <span>Grand Total (18% GST Incl.):</span>
                            <strong id="lblAccGrandTotal" style="font-size:16px; font-weight:900; color:#0F172A;">₹0.00</strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:6px; color:#475569;">
                            <span>Paid Now:</span>
                            <strong id="lblAccPaid" style="font-size:15px; font-weight:800; color:#16A34A;">₹0.00</strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:13px; border-top:1px dashed #CBD5E1; padding-top:6px; font-weight:800;">
                            <span style="color:#475569;">Added to Khata (Remaining Balance / Udhari):</span>
                            <span id="lblAccDue" style="color:#DC2626; font-size:15px;">₹0.00</span>
                        </div>
                    </div>

                    <div style="display:flex; justify-content:flex-end; gap: 12px; padding-top: 14px; border-top: 1px solid #E2E8F0;">
                        <button type="button" onclick="closeSellAccessoryModal()" class="btn btn-outline" style="border:1px solid #CBD5E1; color:#475569; font-weight:700; padding:9px 20px; border-radius:8px; cursor:pointer;">Cancel</button>
                        <button type="submit" id="btnSubmitAccSale" class="btn btn-primary" style="background:#16A34A; color:#FFFFFF; font-weight:800; padding:10px 24px; border:none; border-radius:8px; cursor:pointer; display:inline-flex; align-items:center; gap:6px;" disabled>
                            <i data-lucide="check-circle" style="width:16px;height:16px;"></i> Complete Sale & Bill
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- MODAL: Sell Pre-Owned Device at POS Counter                -->
    <!-- ══════════════════════════════════════════════════════════ -->
    <div id="sellShModal" style="display:none; position: fixed; inset: 0; z-index: 1200; background: rgba(15,23,42,0.45); backdrop-filter: blur(4px); align-items:center; justify-content:center; padding: 16px;">
        <div class="card" style="max-width: 500px; width: 100%; max-height: 90vh; overflow-y:auto; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); border-radius:14px; background:#fff;">
            <div class="card-header" style="border-bottom:1px solid #E2E8F0; padding:14px 18px; display:flex; justify-content:space-between; align-items:center;">
                <div class="card-title" style="font-weight:700; font-size:15px; color:#0F172A; display:flex; align-items:center; gap:8px;">
                    <i data-lucide="refresh-cw" style="width:18px;height:18px;color:#EA580C;"></i>
                    Sell Pre-Owned Device
                </div>
                <button type="button" onclick="closeSellShModal()" class="btn-icon" style="background:none; border:none; font-size:16px; cursor:pointer; color:#64748B;">✕</button>
            </div>
            <div class="card-body" style="padding:16px 18px;">
                <form action="{{ route('mobileshop.second_hand.sale') }}" method="POST" id="sellShForm">
                    @csrf
                    <input type="hidden" name="redirect_to" value="{{ route('mobileshop.sales') }}">

                    <div class="form-group" style="margin-bottom: 12px;">
                        <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Select In-Stock Pre-Owned Phone *</label>
                        <select name="device_id" id="shDeviceSelect" required class="form-control" onchange="onSelectUsedPhone(this)" style="font-size:13px;">
                            <option value="">-- Choose Pre-Owned Phone --</option>
                            @foreach($secondHandPhones ?? [] as $phone)
                                <option value="{{ $phone->id }}" data-price="{{ $phone->selling_price }}">
                                    {{ $phone->brand }} {{ $phone->model }} (IMEI: {{ $phone->imei_1 }}) — ₹{{ number_format($phone->selling_price, 2) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-row" style="margin-bottom: 12px; display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Customer Mobile *</label>
                            <input type="text" name="customer_phone" id="shCustomerPhone" placeholder="10-digit number" required class="form-control" list="shCustomerList" style="font-size:13px;">
                            <datalist id="shCustomerList">
                                @foreach($customers ?? [] as $c)
                                    <option value="{{ $c->phone }}" data-name="{{ $c->name }}">{{ $c->name }}</option>
                                @endforeach
                            </datalist>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Customer Name *</label>
                            <input type="text" name="customer_name" id="shCustomerName" placeholder="Full name" required class="form-control" style="font-size:13px;">
                        </div>
                    </div>

                    <div class="form-row" style="margin-bottom: 12px; display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Agreed Sale Price (₹) *</label>
                            <input type="number" step="0.01" name="sale_price" id="shSalePrice" required class="form-control" oninput="updateShSummary()" style="font-size:13px; font-weight:700;">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Payment Mode *</label>
                            <select name="payment_mode" class="form-control" onchange="onShPaymentModeChange(this)" style="font-size:13px;">
                                <option value="cash">💵 Cash</option>
                                <option value="upi">📱 UPI / QR</option>
                                <option value="card">💳 Card</option>
                                <option value="credit_udhari">📒 Full Udhari (Khata)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row" style="margin-bottom: 14px; display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Amount Paid Now (₹) *</label>
                            <input type="number" step="0.01" name="amount_paid" id="shAmountPaid" required class="form-control" oninput="updateShSummary()" style="font-size:13px; font-weight:700; color:#16A34A;">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Balance Due (₹)</label>
                            <input type="text" id="shBalanceDueDisplay" readonly value="₹0.00" class="form-control" style="background:#F1F5F9; font-weight:700; color:#DC2626; font-size:13px;">
                        </div>
                    </div>

                    <div style="display:flex; justify-content:flex-end; gap: 10px; padding-top: 14px; border-top: 1px solid #E2E8F0;">
                        <button type="button" onclick="closeSellShModal()" class="btn btn-outline" style="font-size:12px;">Cancel</button>
                        <button type="submit" class="btn btn-primary" style="background:#EA580C; border-color:#EA580C; font-size:12px;">Complete Sale & Generate Bill</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- DATE FILTER BOTTOM SHEET / MODAL (Mobile Responsive)       -->
    <!-- ══════════════════════════════════════════════════════════ -->
    <div id="dateFilterModal" style="display:none; position: fixed; inset: 0; z-index: 998; background: rgba(15,23,42,0.6); backdrop-filter: blur(3px); align-items: flex-end; justify-content: center;">
        <div style="background: #FFFFFF; width: 100%; max-width: 480px; border-top-left-radius: 20px; border-top-right-radius: 20px; padding: 20px 20px 32px 20px; box-shadow: 0 -10px 30px rgba(0,0,0,0.2); animation: fabSlideUp 0.2s ease-out;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <div style="font-size: 16px; font-weight: 800; color: #0F172A; display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="calendar" style="width: 18px; height: 18px; color: #5E6AD2;"></i>
                    Filter by Date Range
                </div>
                <button type="button" onclick="closeDateFilterDrawer()" style="background: #F1F5F9; border: none; width: 34px; height: 34px; border-radius: 50%; font-size: 14px; color: #475569; cursor: pointer; display: flex; align-items: center; justify-content: center;">✕</button>
            </div>

            <div style="display: flex; flex-direction: column; gap: 14px; margin-bottom: 18px;">
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 800; color: #475569; text-transform: uppercase; margin-bottom: 6px;">From Date</label>
                    <input type="date" id="drawerFromDate" style="width: 100%; min-height: 44px; padding: 10px 14px; font-size: 14px; font-weight: 700; border: 1px solid #CBD5E1; border-radius: 10px; background: #F8FAFC; color: #0F172A; outline: none;">
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 800; color: #475569; text-transform: uppercase; margin-bottom: 6px;">To Date</label>
                    <input type="date" id="drawerToDate" style="width: 100%; min-height: 44px; padding: 10px 14px; font-size: 14px; font-weight: 700; border: 1px solid #CBD5E1; border-radius: 10px; background: #F8FAFC; color: #0F172A; outline: none;">
                </div>
            </div>

            <!-- Quick Presets Inside Drawer -->
            <div style="margin-bottom: 18px;">
                <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; margin-bottom: 8px;">Quick Presets</div>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;">
                    <button type="button" onclick="setDrawerPreset('today')" style="min-height: 42px; background: #F8FAFC; border: 1px solid #CBD5E1; border-radius: 8px; font-size: 12px; font-weight: 700; color: #0F172A; cursor: pointer;">Today</button>
                    <button type="button" onclick="setDrawerPreset('yesterday')" style="min-height: 42px; background: #F8FAFC; border: 1px solid #CBD5E1; border-radius: 8px; font-size: 12px; font-weight: 700; color: #0F172A; cursor: pointer;">Yesterday</button>
                    <button type="button" onclick="setDrawerPreset('week')" style="min-height: 42px; background: #F8FAFC; border: 1px solid #CBD5E1; border-radius: 8px; font-size: 12px; font-weight: 700; color: #0F172A; cursor: pointer;">Last 7 Days</button>
                    <button type="button" onclick="setDrawerPreset('month')" style="min-height: 42px; background: #F8FAFC; border: 1px solid #CBD5E1; border-radius: 8px; font-size: 12px; font-weight: 700; color: #0F172A; cursor: pointer;">This Month</button>
                </div>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="button" onclick="resetDrawerFilter()" style="flex: 1; min-height: 44px; background: #F1F5F9; border: 1px solid #CBD5E1; border-radius: 10px; font-size: 13px; font-weight: 800; color: #475569; cursor: pointer;">Reset</button>
                <button type="button" onclick="applyDrawerFilter()" style="flex: 2; min-height: 44px; background: #5E6AD2; border: none; border-radius: 10px; font-size: 13px; font-weight: 800; color: #FFFFFF; cursor: pointer; box-shadow: 0 4px 12px rgba(94,106,210,0.3);">Apply Filter</button>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- FLOATING ACTION BUTTON (FAB) FOR MOBILE QUICK ENTRY        -->
    <!-- ══════════════════════════════════════════════════════════ -->
    <div id="mobileSalesFabContainer" class="mobile-fab-container">
        <!-- FAB Dropup Menu -->
        <div id="fabDropupMenu" class="fab-dropup-menu" style="display:none;">
            @if(($isAdmin ?? false) || ($canCreatePhones ?? false))
                <a href="{{ route('mobileshop.sales.create') }}" class="fab-menu-item" style="color: #5E6AD2;">
                    <i data-lucide="shopping-cart" style="width:16px;height:16px;"></i>
                    <span>New Phone Sale</span>
                </a>
            @endif
            @if(($isAdmin ?? false) || ($canCreateAccessories ?? false))
                <a href="{{ route('mobileshop.accessories.pos') }}" class="fab-menu-item" style="color: #16A34A;">
                    <i data-lucide="zap" style="width:16px;height:16px;"></i>
                    <span>Counter POS (Accessories)</span>
                </a>
            @endif
            @if(($isAdmin ?? false) || ($canCreateCovers ?? false))
                <a href="{{ route('mobileshop.accessories.pos', ['category' => 'back_cover']) }}" class="fab-menu-item" style="color: #7C3AED;">
                    <i data-lucide="package" style="width:16px;height:16px;"></i>
                    <span>Add Cover / Glass</span>
                </a>
            @endif
            @if(($isAdmin ?? false) || ($canCreateSecondhand ?? false))
                <button type="button" onclick="closeFabMenu(); openSellShModal()" class="fab-menu-item" style="color: #EA580C;">
                    <i data-lucide="refresh-cw" style="width:16px;height:16px;"></i>
                    <span>Sell Pre-Owned</span>
                </button>
            @endif
        </div>

        <!-- FAB Main Button -->
        <button type="button" onclick="toggleFabMenu()" id="btnSalesFab" class="btn-sales-fab" aria-label="Quick Sale">
            <i data-lucide="plus" id="fabIcon" style="width: 22px; height: 22px; transition: transform 0.2s ease;"></i>
        </button>
    </div>

</div><!-- /.sales-page-wrapper -->

@endsection

@push('scripts')
<script>
    const companyId = {{ json_encode(company_id()) }};
    let cart = [];
    let userEditedPaidAmount = false;
    const allCatalogParts = {!! json_encode($partsList ?? []) !!};

    function openSellAccessoryModal(presetCategory = null) {
        window.location.href = "{{ route('mobileshop.accessories.pos') }}" + (presetCategory ? '?category=' + encodeURIComponent(presetCategory) : '');
    }

    function closeSellAccessoryModal() {
        document.getElementById('sellAccessoryModal').style.display = 'none';
        hideSearchDropdown();
    }

    function openSellShModal() {
        const modal = document.getElementById('sellShModal');
        if (modal) modal.style.display = 'flex';
        if (window.refreshIcons) window.refreshIcons();
        else if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons();
    }

    function closeSellShModal() {
        const modal = document.getElementById('sellShModal');
        if (modal) modal.style.display = 'none';
    }

    function onSelectUsedPhone(selectEl) {
        const opt = selectEl.options[selectEl.selectedIndex];
        const price = opt ? parseFloat(opt.getAttribute('data-price')) || 0 : 0;
        const priceInput = document.getElementById('shSalePrice');
        const paidInput = document.getElementById('shAmountPaid');
        if (priceInput) priceInput.value = price > 0 ? price.toFixed(2) : '';
        if (paidInput) paidInput.value = price > 0 ? price.toFixed(2) : '';
        updateShSummary();
    }

    function updateShSummary() {
        const salePrice = parseFloat(document.getElementById('shSalePrice')?.value) || 0;
        const amountPaid = parseFloat(document.getElementById('shAmountPaid')?.value) || 0;
        const due = Math.max(0, salePrice - amountPaid);
        const dueDisplay = document.getElementById('shBalanceDueDisplay');
        if (dueDisplay) {
            dueDisplay.value = '₹' + due.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    }

    function onShPaymentModeChange(selectEl) {
        const paidInput = document.getElementById('shAmountPaid');
        if (!paidInput) return;
        if (selectEl.value === 'credit_udhari') {
            paidInput.value = '0';
        } else {
            const salePrice = parseFloat(document.getElementById('shSalePrice')?.value) || 0;
            paidInput.value = salePrice > 0 ? salePrice.toFixed(2) : '0';
        }
        updateShSummary();
    }

    function isCategoryMatch(partCategory, filterCategory) {
        if (!filterCategory) return true;
        if (!partCategory) return false;
        const p = String(partCategory).toLowerCase().replace(/[-_\s]/g, '');
        const f = String(filterCategory).toLowerCase().replace(/[-_\s]/g, '');
        if (p === f) return true;
        if ((f.includes('display') || f.includes('folder')) && (p.includes('display') || p.includes('folder'))) return true;
        if ((f.includes('cover') || f.includes('case')) && (p.includes('cover') || p.includes('case'))) return true;
        if ((f.includes('pin') || f.includes('port') || f.includes('charging')) && (p.includes('pin') || p.includes('port') || p.includes('charging'))) return true;
        if (f.includes('glass') && p.includes('glass')) return true;
        return p.includes(f) || f.includes(p);
    }

    function onCategoryFilterChange(selectedCategory) {
        const searchInput = document.getElementById('accItemSearchInput');
        if (searchInput) {
            searchInput.value = '';
            searchInput.focus();
            onLiveSearchInput('');
        }
    }

    function onLiveSearchInput(term) {
        const dropdown = document.getElementById('accSearchDropdownMenu');
        if (!dropdown) return;

        term = (term || '').toLowerCase().trim();
        const selectedCategory = document.getElementById('accCategoryFilter')?.value || '';

        let pool = allCatalogParts.filter(p => p.stock_qty > 0 && isCategoryMatch(p.category, selectedCategory));

        let matches = pool;
        if (term) {
            matches = pool.filter(p => 
                p.name.toLowerCase().includes(term) ||
                (p.compatible_model && p.compatible_model.toLowerCase().includes(term)) ||
                (p.category && p.category.toLowerCase().includes(term))
            );
        }

        if (matches.length === 0) {
            dropdown.innerHTML = `<div style="padding:12px; text-align:center; color:#94A3B8; font-size:12px;">No in-stock item matching "${escapeHtml(term)}" in this category</div>`;
            dropdown.style.display = 'block';
            return;
        }

        let html = '';
        matches.slice(0, 15).forEach((p) => {
            const modelStr = p.compatible_model ? `<span style="font-size:11px; color:#64748B;"> • ${escapeHtml(p.compatible_model)}</span>` : '';
            html += `
            <div class="acc-search-item" onclick="selectItemFromCustomDropdown(${p.id})" style="padding:9px 12px; cursor:pointer; border-bottom:1px solid #F1F5F9; display:flex; justify-content:space-between; align-items:center; transition: background 0.1s ease;" onmouseover="this.style.background='#F0FDF4'" onmouseout="this.style.background='transparent'">
                <div>
                    <div style="font-weight:700; color:#0F172A; font-size:13px;">${escapeHtml(p.name)} ${modelStr}</div>
                    <div style="font-size:10px; color:#94A3B8; text-transform:capitalize;">${(p.category || '').replace(/_/g, ' ')}</div>
                </div>
                <div style="text-align:right;">
                    <div style="font-weight:900; color:#16A34A; font-size:13px;">₹${parseFloat(p.selling_price).toFixed(2)}</div>
                    <span style="font-size:10px; font-weight:700; color:#475569; background:#E2E8F0; padding:2px 6px; border-radius:4px;">${p.stock_qty} in stock</span>
                </div>
            </div>`;
        });

        dropdown.innerHTML = html;
        dropdown.style.display = 'block';
    }

    function selectItemFromCustomDropdown(partId) {
        const part = allCatalogParts.find(p => p.id === partId);
        if (part) {
            addItemToCartList(part.id, part.name, parseFloat(part.selling_price), parseInt(part.stock_qty));
            const input = document.getElementById('accItemSearchInput');
            if (input) {
                input.value = '';
                input.focus();
            }
            hideSearchDropdown();
        }
    }

    function hideSearchDropdown() {
        const dropdown = document.getElementById('accSearchDropdownMenu');
        if (dropdown) dropdown.style.display = 'none';
    }

    function handleSearchInputKey(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addSearchedItemToCart();
        } else if (e.key === 'Escape') {
            hideSearchDropdown();
        }
    }

    function addSearchedItemToCart() {
        const input = document.getElementById('accItemSearchInput');
        const typedVal = input.value.trim().toLowerCase();
        if (!typedVal) return;

        const selectedCategory = document.getElementById('accCategoryFilter')?.value || '';
        let pool = allCatalogParts.filter(p => p.stock_qty > 0 && isCategoryMatch(p.category, selectedCategory));

        // 1. Exact match by name
        let matched = pool.find(p => p.name.toLowerCase() === typedVal);

        // 2. Partial search match
        if (!matched) {
            matched = pool.find(p => 
                p.name.toLowerCase().includes(typedVal) ||
                (p.compatible_model && p.compatible_model.toLowerCase().includes(typedVal))
            );
        }

        if (matched) {
            addItemToCartList(matched.id, matched.name, parseFloat(matched.selling_price), parseInt(matched.stock_qty));
            input.value = '';
            hideSearchDropdown();
            input.focus();
        } else {
            alert(`No in-stock item matching "${input.value}" found in catalog.`);
        }
    }

    // Hide custom dropdown when clicking outside
    document.addEventListener('click', function(e) {
        const searchWrapper = document.getElementById('accItemSearchWrapper');
        if (searchWrapper && !searchWrapper.contains(e.target)) {
            hideSearchDropdown();
        }
    });

    function addItemToCartList(id, name, price, stock) {
        const existing = cart.find(item => item.part_id === id);
        if (existing) {
            if (existing.quantity < stock) {
                existing.quantity += 1;
            } else {
                alert(`Cannot add more! Maximum available stock in store is ${stock}.`);
            }
        } else {
            cart.push({
                part_id: id,
                name: name,
                unit_price: price,
                quantity: 1,
                stock: stock
            });
        }
        renderCart();
    }

    // Uses window.escapeHtml from layout.blade.php


    function renderCart() {
        const tbody = document.getElementById('accCartTableBody');
        const submitBtn = document.getElementById('btnSubmitAccSale');

        if (cart.length === 0) {
            tbody.innerHTML = `
                <tr id="emptyCartRow">
                    <td colspan="5" style="text-align:center; padding:24px; color:#64748B; font-size:13px;">
                        No items in cart yet. Select a product above to add to this counter sale.
                    </td>
                </tr>`;
            submitBtn.disabled = true;
            updateAccBillSummary(false);
            return;
        }

        submitBtn.disabled = false;
        let html = '';
        cart.forEach((item, index) => {
            const lineTotal = item.quantity * item.unit_price;
            html += `
            <tr>
                <td>
                    <div style="font-weight:700; color:#0F172A; font-size:13px;">${escapeHtml(item.name)}</div>
                    <div style="font-size:10px; color:#64748B;">Max Stock: ${item.stock}</div>
                    <input type="hidden" name="items[${index}][part_id]" value="${item.part_id}">
                </td>
                <td style="text-align:center;">
                    <input type="number" name="items[${index}][quantity]" value="${item.quantity}" min="1" max="${item.stock}" class="form-control" style="padding:4px 8px; font-weight:700; text-align:center; width:65px; border-color:#CBD5E1; color:#0F172A;" oninput="onCartQtyChange(${index}, this.value, ${item.stock})">
                </td>
                <td style="text-align:right;">
                    <input type="number" step="0.01" name="items[${index}][unit_price]" value="${item.unit_price.toFixed(2)}" class="form-control" style="padding:4px 8px; font-weight:700; text-align:right; width:95px; border-color:#CBD5E1; color:#0F172A;" oninput="onCartPriceChange(${index}, this.value)">
                </td>
                <td style="text-align:right; font-weight:800; color:#0F172A;">
                    ₹${lineTotal.toFixed(2)}
                </td>
                <td style="text-align:center;">
                    <button type="button" class="btn-ghost-delete" onclick="removeCartItem(${index})" title="Remove item">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </td>
            </tr>`;
        });
        tbody.innerHTML = html;
        updateAccBillSummary(false);
    }

    function onCartQtyChange(index, val, maxStock) {
        let q = parseInt(val) || 1;
        if (q > maxStock) {
            alert(`Stock limit reached! Available stock: ${maxStock}`);
            q = maxStock;
        }
        if (q < 1) q = 1;
        cart[index].quantity = q;
        renderCart();
    }

    function onCartPriceChange(index, val) {
        let p = parseFloat(val) || 0;
        cart[index].unit_price = Math.max(0, p);
        // When custom price is entered, re-render and sync total
        renderCart();
    }

    function removeCartItem(index) {
        cart.splice(index, 1);
        renderCart();
    }

    function getSubtotal() {
        let subtotal = 0;
        cart.forEach(item => {
            subtotal += (item.quantity * item.unit_price);
        });
        return subtotal;
    }

    function updateAccBillSummary(forceAutoFill = false) {
        const subtotal = getSubtotal();
        const paymentMode = document.getElementById('accPaymentMode').value;
        const amountPaidInput = document.getElementById('accAmountPaid');

        // If user hasn't typed a custom partial payment, default to full amount or zero on credit
        if (forceAutoFill || !userEditedPaidAmount) {
            if (paymentMode === 'credit_udhari') {
                amountPaidInput.value = '0.00';
            } else {
                amountPaidInput.value = subtotal.toFixed(2);
            }
        }

        const paid = parseFloat(amountPaidInput.value) || 0;
        const due = Math.max(0, subtotal - paid);

        document.getElementById('lblAccGrandTotal').textContent = '₹' + subtotal.toFixed(2);
        document.getElementById('lblAccPaid').textContent = '₹' + paid.toFixed(2);
        document.getElementById('lblAccDue').textContent = '₹' + due.toFixed(2);

        if (due > 0) {
            amountPaidInput.style.color = '#EA580C'; // Partial orange
        } else {
            amountPaidInput.style.color = '#16A34A'; // Full green
        }
    }

    function onAmountPaidManualInput() {
        userEditedPaidAmount = true;
        updateAccBillSummary(false);
    }

    function onAccPaymentModeChange(select) {
        if (select.value === 'credit_udhari') {
            userEditedPaidAmount = true;
            document.getElementById('accAmountPaid').value = '0.00';
        } else {
            userEditedPaidAmount = false;
        }
        updateAccBillSummary(false);
    }

    function setFullPayment() {
        userEditedPaidAmount = false;
        updateAccBillSummary(true);
    }

    function setZeroPayment() {
        userEditedPaidAmount = true;
        document.getElementById('accPaymentMode').value = 'credit_udhari';
        document.getElementById('accAmountPaid').value = '0.00';
        updateAccBillSummary(false);
    }

    function validateAndSubmitAccSale(form) {
        if (cart.length === 0) {
            alert('Please add at least one item to the cart before submitting.');
            return false;
        }
        form.querySelector('button[type=submit]').disabled = true;
        form.querySelector('button[type=submit]').innerText = 'Processing Sale...';
        return true;
    }

    // Customer Datalist autofill
    document.getElementById('accCustomerPhone')?.addEventListener('input', function() {
        const val = this.value.trim();
        const option = document.querySelector(`#accCustomerList option[value="${val}"]`);
        if (option) {
            document.getElementById('accCustomerName').value = option.dataset.name || '';
        }
    });

    const returnReasonActions = {
        'defective_doa': {
            restock: false,
            label: 'Defective / Faulty Item (Dead on Arrival)',
            notice: '<strong>Defective / Damaged Item:</strong> Item will NOT be added to sellable stock. Quarantined as damaged / scrap.',
            bg: '#FFF1F2', border: '#FECDD3', text: '#991B1B'
        },
        'model_mismatch': {
            restock: true,
            label: 'Incorrect Model / Size Mismatch',
            notice: '<strong>Model Mismatch (Brand New Item):</strong> Item will be restocked into available sellable inventory.',
            bg: '#F0FDF4', border: '#BBF7D0', text: '#166534'
        },
        'mind_change': {
            restock: true,
            label: 'Customer Changed Mind / Unwanted',
            notice: '<strong>Unwanted Return (Unopened):</strong> Item will be restocked into available sellable inventory.',
            bg: '#F0FDF4', border: '#BBF7D0', text: '#166534'
        },
        'billing_error': {
            restock: true,
            label: 'Billing Error / Duplicate Entry',
            notice: '<strong>Billing Mistake Reversal:</strong> Mistake voided and item stock restored to original inventory count.',
            bg: '#F0FDF4', border: '#BBF7D0', text: '#166534'
        },
        'exchange': {
            restock: true,
            label: 'Exchange for Different Product',
            notice: '<strong>Exchange Return:</strong> Original item restocked to inventory so customer can be billed for exchange.',
            bg: '#F0FDF4', border: '#BBF7D0', text: '#166534'
        },
        'warranty_issue': {
            restock: false,
            label: 'Warranty / Quality Issue',
            notice: '<strong>Warranty Claim:</strong> Defective item will NOT be added to sellable stock. Routed to Supplier RMA.',
            bg: '#FFF1F2', border: '#FECDD3', text: '#991B1B'
        }
    };

    function onReturnReasonSelectChange(reasonCode) {
        const info = returnReasonActions[reasonCode] || returnReasonActions['defective_doa'];
        document.getElementById('returnReasonLabel').value = info.label;
        document.getElementById('returnShouldRestock').value = info.restock ? '1' : '0';
        document.getElementById('chkRestockOverride').checked = info.restock;

        const noticeBox = document.getElementById('returnActionNotice');
        const textSpan = document.getElementById('returnActionText');

        noticeBox.style.background = info.bg;
        noticeBox.style.borderColor = info.border;
        noticeBox.style.color = info.text;
        textSpan.innerHTML = info.notice;
    }

    function onRestockOverrideChange(checked) {
        document.getElementById('returnShouldRestock').value = checked ? '1' : '0';
        const noticeBox = document.getElementById('returnActionNotice');
        const textSpan = document.getElementById('returnActionText');

        if (checked) {
            noticeBox.style.background = '#F0FDF4';
            noticeBox.style.borderColor = '#BBF7D0';
            noticeBox.style.color = '#166534';
            textSpan.innerHTML = '<strong>Manual Restock Active:</strong> Item WILL be added back into sellable store inventory.';
        } else {
            noticeBox.style.background = '#FFF1F2';
            noticeBox.style.borderColor = '#FECDD3';
            noticeBox.style.color = '#991B1B';
            textSpan.innerHTML = '<strong>Quarantine Active:</strong> Item will NOT be added to sellable stock.';
        }
    }

    let currentReturnItems = [];

    function handleReturnButtonClick(btn, type, saleId, invoiceNumber, amount) {
        let items = [];
        try {
            items = JSON.parse(btn.getAttribute('data-items') || '[]');
        } catch(e) {
            items = [];
        }
        openReturnModal(type, saleId, invoiceNumber, amount, items);
    }

    function openReturnModal(type, saleId, invoiceNumber, amount, items) {
        const modal = document.getElementById('salesReturnModal');
        const form = document.getElementById('salesReturnForm');
        const lblInvoice = document.getElementById('lblReturnInvoice');
        const itemsList = document.getElementById('returnItemsList');
        const countNotice = document.getElementById('lblItemCountNotice');

        lblInvoice.textContent = invoiceNumber;
        currentReturnItems = (Array.isArray(items) && items.length > 0) ? items : [
            { id: 1, part_name: 'Standard Order Item', quantity: 1, unit_price: parseFloat(amount.replace(/,/g, '')) || 0, line_total: parseFloat(amount.replace(/,/g, '')) || 0 }
        ];

        // Reset to default option
        document.getElementById('returnReasonSelect').value = 'defective_doa';
        document.getElementById('returnReasonInput').value = '';
        onReturnReasonSelectChange('defective_doa');

        // Render line items
        itemsList.innerHTML = '';
        if (currentReturnItems.length > 1) {
            countNotice.textContent = `(${currentReturnItems.length} items in invoice — select items being returned)`;
            countNotice.style.color = '#DC2626';
        } else {
            countNotice.textContent = `(1 item in invoice)`;
            countNotice.style.color = '#64748B';
        }

        currentReturnItems.forEach((item, idx) => {
            const unitPrice = parseFloat(item.unit_price) || (parseFloat(item.line_total) / Math.max(1, item.quantity));
            const row = document.createElement('div');
            row.style.cssText = 'background:#fff; border:1px solid #E2E8F0; border-radius:6px; padding:8px 10px; display:flex; align-items:center; justify-content:space-between; gap:10px;';
            row.innerHTML = `
                <div style="display:flex; align-items:center; gap:8px; flex:1; min-width:0;">
                    <input type="checkbox" id="chk_item_${item.id}" class="return-item-chk" checked onchange="recalcReturnRefund()" style="width:16px; height:16px; cursor:pointer; accent-color:#DC2626;">
                    <div style="min-width:0;">
                        <label for="chk_item_${item.id}" style="font-size:12px; font-weight:700; color:#0F172A; margin:0; cursor:pointer; display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                            ${item.part_name || 'Item #' + (idx+1)}
                        </label>
                        <div style="font-size:11px; color:#64748B;">Unit: ₹${unitPrice.toFixed(2)} | Billed Qty: ${item.quantity}</div>
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:6px; flex-shrink:0;">
                    <span style="font-size:11px; font-weight:700; color:#475569;">Return Qty:</span>
                    <input type="number" name="returned_items[${item.id}]" id="qty_item_${item.id}" min="1" max="${item.quantity}" value="${item.quantity}" oninput="recalcReturnRefund()" data-unit-price="${unitPrice}" style="width:55px; padding:3px 6px; font-size:12px; font-weight:800; text-align:center; border:1px solid #CBD5E1; border-radius:4px; color:#0F172A;">
                    <span id="sub_item_${item.id}" style="font-size:12px; font-weight:800; color:#0F172A; min-width:65px; text-align:right;">₹${(unitPrice * item.quantity).toFixed(2)}</span>
                </div>
            `;
            itemsList.appendChild(row);
        });

        recalcReturnRefund();

        if (type === 'accessory') {
            form.action = `/${companyId}/mobileshop/accessories/${saleId}/void`;
        } else {
            form.action = `/${companyId}/mobileshop/sales/${saleId}/void`;
        }

        modal.style.display = 'flex';
        if (window.refreshIcons) window.refreshIcons();
        else if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons();
    }

    function recalcReturnRefund() {
        let totalRefund = 0;
        let selectedCount = 0;

        currentReturnItems.forEach(item => {
            const chk = document.getElementById(`chk_item_${item.id}`);
            const qtyInput = document.getElementById(`qty_item_${item.id}`);
            const subSpan = document.getElementById(`sub_item_${item.id}`);

            if (chk && qtyInput) {
                const isChecked = chk.checked;
                qtyInput.disabled = !isChecked;
                
                if (isChecked) {
                    const unitPrice = parseFloat(qtyInput.dataset.unitPrice) || 0;
                    let qty = parseInt(qtyInput.value) || 0;
                    if (qty > item.quantity) {
                        qty = item.quantity;
                        qtyInput.value = qty;
                    }
                    if (qty < 1) {
                        qty = 1;
                        qtyInput.value = qty;
                    }
                    const sub = unitPrice * qty;
                    totalRefund += sub;
                    selectedCount++;
                    if (subSpan) subSpan.textContent = '₹' + sub.toFixed(2);
                } else {
                    if (subSpan) subSpan.textContent = '₹0.00';
                }
            }
        });

        document.getElementById('lblReturnAmount').textContent = '₹' + totalRefund.toFixed(2);
    }

    function closeReturnModal() {
        const modal = document.getElementById('salesReturnModal');
        if (modal) modal.style.display = 'none';
    }

    let currentSalesDatePreset = 'all';

    function calculateSalesDateRange(preset) {
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

    function setSalesDatePreset(preset) {
        currentSalesDatePreset = preset;
        const mainFrom = document.getElementById('salesFromDate');
        const mainTo = document.getElementById('salesToDate');
        const drawerFrom = document.getElementById('drawerFromDate');
        const drawerTo = document.getElementById('drawerToDate');

        document.querySelectorAll('.sales-date-pill').forEach(el => el.classList.remove('active'));
        const targetId = 'salesDateBtn_' + (preset === '7days' ? 'week' : preset);
        const btn = document.getElementById(targetId) || document.getElementById('salesDateBtn_' + preset);
        if (btn) btn.classList.add('active');

        const range = calculateSalesDateRange(preset);

        if (mainFrom) mainFrom.value = range.from || '';
        if (mainTo) mainTo.value = range.to || '';
        if (drawerFrom) drawerFrom.value = range.from || '';
        if (drawerTo) drawerTo.value = range.to || '';

        const indicator = document.getElementById('salesDateFilterActiveIndicator') || document.getElementById('dateFilterActiveIndicator');
        if (indicator) {
            indicator.style.display = (range.from || range.to) ? 'inline-block' : 'none';
        }

        filterSalesTable();
    }

    let currentSalesCategoryFilter = 'all';

    function setSalesCategoryFilter(cat, btnEl) {
        currentSalesCategoryFilter = cat;
        document.querySelectorAll('.sales-cat-pill').forEach(el => el.classList.remove('active'));
        if (btnEl) btnEl.classList.add('active');
        filterSalesTable();
    }

    function onSalesCustomDateChange() {
        document.querySelectorAll('.sales-date-pill').forEach(el => el.classList.remove('active'));
        filterSalesTable();
    }

    function filterSalesTable() {
        const term = (document.getElementById('salesSearchInput')?.value || '').toLowerCase().trim();
        const fromDate = document.getElementById('salesFromDate')?.value || '';
        const toDate = document.getElementById('salesToDate')?.value || '';

        let visibleCount = 0;

        // 1. Filter Desktop Table Rows
        document.querySelectorAll('#salesTable tbody tr.sales-row').forEach(row => {
            const rowText = row.textContent.toLowerCase();
            const rawDate = (row.dataset.date || '').trim();
            const cleanRowDate = rawDate.length >= 10 ? rawDate.slice(0, 10) : rawDate;
            const rowType = row.dataset.type || '';

            const matchesCategory = (currentSalesCategoryFilter === 'all') || (rowType === currentSalesCategoryFilter);
            const matchesText = !term || rowText.includes(term);
            let matchesDate = true;

            if (fromDate) {
                matchesDate = matchesDate && (cleanRowDate !== '' && cleanRowDate >= fromDate);
            }
            if (toDate) {
                matchesDate = matchesDate && (cleanRowDate !== '' && cleanRowDate <= toDate);
            }

            const isVisible = matchesCategory && matchesText && matchesDate;
            row.dataset.mobiHidden = isVisible ? '0' : '1';
            row.style.display = isVisible ? '' : 'none';
            if (isVisible) visibleCount++;
        });

        // 2. Filter Mobile Cards View
        let visibleMobileCount = 0;
        document.querySelectorAll('#salesMobileCards .sales-flat-row').forEach(card => {
            const cardText = card.textContent.toLowerCase();
            const rawDate = (card.dataset.date || '').trim();
            const cleanCardDate = rawDate.length >= 10 ? rawDate.slice(0, 10) : rawDate;
            const cardType = card.dataset.type || '';

            const matchesCategory = (currentSalesCategoryFilter === 'all') || (cardType === currentSalesCategoryFilter);
            const matchesText = !term || cardText.includes(term);
            let matchesDate = true;

            if (fromDate) {
                matchesDate = matchesDate && (cleanCardDate !== '' && cleanCardDate >= fromDate);
            }
            if (toDate) {
                matchesDate = matchesDate && (cleanCardDate !== '' && cleanCardDate <= toDate);
            }

            const isCardVisible = matchesCategory && matchesText && matchesDate;
            card.dataset.mobiHidden = isCardVisible ? '0' : '1';
            card.style.display = isCardVisible ? '' : 'none';
            if (isCardVisible) visibleMobileCount++;
        });

        // Empty state toggles
        const emptyRow = document.getElementById('salesEmptyFilterRow');
        if (emptyRow) {
            emptyRow.style.display = (visibleCount === 0) ? '' : 'none';
        }
        const mobEmptyRow = document.getElementById('salesMobileEmptyFilterRow');
        if (mobEmptyRow) {
            mobEmptyRow.style.display = (visibleMobileCount === 0) ? 'block' : 'none';
        }

        if (window.salesPager && typeof window.salesPager.refresh === 'function') {
            window.salesPager.refresh(true);
        }

        const clearBtn = document.getElementById('btnClearSearch');
        if (clearBtn) {
            clearBtn.style.display = term ? 'block' : 'none';
        }

        const activeDateInd = document.getElementById('dateFilterActiveIndicator');
        if (activeDateInd) {
            activeDateInd.style.display = (fromDate || toDate) ? 'inline-block' : 'none';
        }
    }

    function clearSalesSearch() {
        const input = document.getElementById('salesSearchInput');
        if (input) {
            input.value = '';
            filterSalesTable();
            input.focus();
        }
    }

    /* ─── Mobile FAB Menu Controls ─── */
    function toggleFabMenu() {
        const menu = document.getElementById('fabDropupMenu');
        const icon = document.getElementById('fabIcon');
        if (!menu) return;

        if (menu.style.display === 'none' || !menu.style.display) {
            menu.style.display = 'flex';
            if (icon) icon.style.transform = 'rotate(45deg)';
        } else {
            closeFabMenu();
        }
    }

    function closeFabMenu() {
        const menuEl = document.getElementById('fabDropupMenu');
        const icon = document.getElementById('fabIcon');
        if (menuEl) menuEl.style.display = 'none';
        if (icon) icon.style.transform = 'rotate(0deg)';
    }

    /* ─── Mobile Date Filter Drawer Controls ─── */
    function openDateFilterDrawer() {
        const modal = document.getElementById('dateFilterModal');
        if (modal) {
            const fromInput = document.getElementById('salesFromDate');
            const toInput = document.getElementById('salesToDate');
            const drawerFrom = document.getElementById('drawerFromDate');
            const drawerTo = document.getElementById('drawerToDate');

            if (drawerFrom && fromInput) drawerFrom.value = fromInput.value;
            if (drawerTo && toInput) drawerTo.value = toInput.value;

            modal.style.display = 'flex';
            if (window.refreshIcons) window.refreshIcons();
            else if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons();
        }
    }

    function closeDateFilterDrawer() {
        const modal = document.getElementById('dateFilterModal');
        if (modal) modal.style.display = 'none';
    }

    function setDrawerPreset(preset) {
        const drawerFrom = document.getElementById('drawerFromDate');
        const drawerTo = document.getElementById('drawerToDate');
        const range = calculateSalesDateRange(preset);
        if (drawerFrom) drawerFrom.value = range.from || '';
        if (drawerTo) drawerTo.value = range.to || '';
    }

    function applyDrawerFilter() {
        const drawerFrom = document.getElementById('drawerFromDate');
        const drawerTo = document.getElementById('drawerToDate');
        const fromInput = document.getElementById('salesFromDate');
        const toInput = document.getElementById('salesToDate');

        if (fromInput && drawerFrom) fromInput.value = drawerFrom.value;
        if (toInput && drawerTo) toInput.value = drawerTo.value;

        document.querySelectorAll('.sales-date-pill').forEach(el => el.classList.remove('active'));
        closeDateFilterDrawer();
        filterSalesTable();
    }

    function resetDrawerFilter() {
        const drawerFrom = document.getElementById('drawerFromDate');
        const drawerTo = document.getElementById('drawerToDate');
        if (drawerFrom) drawerFrom.value = '';
        if (drawerTo) drawerTo.value = '';

        setSalesDatePreset('all');
        closeDateFilterDrawer();
    }

    // Close FAB or Drawer when clicking outside
    document.addEventListener('click', function(e) {
        const fabContainer = document.getElementById('mobileSalesFabContainer');
        if (fabContainer && !fabContainer.contains(e.target)) {
            closeFabMenu();
        }

        const dateModal = document.getElementById('dateFilterModal');
        if (dateModal && e.target === dateModal) {
            closeDateFilterDrawer();
        }
    });

    // Initialize counts and pagination on page load
    function initSalesPage() {
        if (window.setupMobiTablePagination) {
            window.salesPager = window.setupMobiTablePagination({
                tableId: 'salesTable',
                cardsContainerId: 'salesMobileCards',
                paginationContainerId: 'salesPagination',
                rowSelector: 'tbody tr.sales-row',
                cardSelector: '.sales-flat-row',
                pageSize: 25,
                itemName: 'sales'
            });
        }
        filterSalesTable();
        if (window.refreshIcons) window.refreshIcons();
        else if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSalesPage);
    } else {
        initSalesPage();
    }
</script>
@endpush
