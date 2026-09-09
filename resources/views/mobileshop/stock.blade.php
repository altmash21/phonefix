@extends('mobileshop.layout')

@php
    $stockPageTitle = match($niche ?? 'admin') {
        'phones'      => 'New Phones Inventory',
        'secondhand'  => 'Pre-Owned Stock Register',
        'accessories' => 'Accessories & Parts Stock',
        'covers'      => 'Back Cover & Tempered Inventory',
        'repairs'     => 'Service Desk & Job Tracker',
        default       => 'Unified Inventory & Stock Hub',
    };
@endphp

@section('title', $stockPageTitle . ' — Maurya Mobile ERP')
@section('page-title', $stockPageTitle)

@section('page-actions')
    <div class="flex items-center gap-2 flex-wrap">
        @if($canManagePhones ?? false)
        <button type="button" onclick="openAddMobileModal()" class="btn btn-primary btn-sm">
            <i data-lucide="plus" style="width:13px;height:13px;"></i> Add New Phone
        </button>
        @endif
        @if($canManageSecondhand ?? false)
        <button type="button" onclick="openBuybackModal()" class="btn btn-outline btn-sm">
            <i data-lucide="plus" style="width:13px;height:13px;"></i> Intake Pre-Owned
        </button>
        @endif
        @if(($canManageAccessories ?? false) || ($canManageCovers ?? false))
        <button type="button" onclick="openAddPartModal()" class="btn btn-outline btn-sm">
            <i data-lucide="plus" style="width:13px;height:13px;"></i> Add Part / Accessory
        </button>
        @endif
        @if($canManageRepairs ?? false)
        <a href="{{ route('mobileshop.repairs') }}" class="btn btn-outline btn-sm">
            <i data-lucide="wrench" style="width:13px;height:13px;"></i> New Job Sheet
        </a>
        @endif
    </div>
@endsection


@section('content')    <!-- Mobile Horizontal Stat Strip -->
    <div class="mobile-stat-strip">
        <div class="stat-strip-item" onclick="switchStockTab('new_phones')">
            <span class="stat-label">New Phones</span>
            <span class="stat-val" style="color:#2563EB;">{{ $totalNewPhonesInStock }}</span>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-strip-item" onclick="switchStockTab('second_hand')">
            <span class="stat-label">Pre-Owned</span>
            <span class="stat-val" style="color:#EA580C;">{{ $totalSecondHandInStock }}</span>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-strip-item" onclick="switchStockTab('parts')">
            <span class="stat-label">Parts & Acc</span>
            <span class="stat-val" style="color:#16A34A;">{{ number_format($totalPartsInStock) }}</span>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-strip-item" onclick="switchStockTab('all')">
            <span class="stat-label">Valuation</span>
            <span class="stat-val" style="color:#0F172A;">₹{{ number_format($valuationRetail, 0) }}</span>
        </div>
    </div>

    <!-- Top Stock KPI Cards (Desktop Only) -->
    <div id="stockDesktopKpiGrid" class="kpi-grid">
        @if(($canManagePhones ?? false) || in_array($niche ?? '', ['admin', 'phones']))
        <div class="kpi-card">
            <div class="kpi-label">Brand New Phones in Stock</div>
            <div class="kpi-num">{{ $totalNewPhonesInStock }} <span style="font-size:11px; font-weight:400; color:var(--color-ink-muted);">Units</span></div>
        </div>
        @endif

        @if(($canManageSecondhand ?? false) || in_array($niche ?? '', ['admin', 'secondhand']))
        <div class="kpi-card">
            <div class="kpi-label">Pre-Owned Phones in Stock</div>
            <div class="kpi-num">{{ $totalSecondHandInStock }} <span style="font-size:11px; font-weight:400; color:var(--color-ink-muted);">Units</span></div>
        </div>
        @endif

        @if(($canManageAccessories ?? false) || ($canManageCovers ?? false) || in_array($niche ?? '', ['admin', 'accessories', 'covers']))
        <div class="kpi-card">
            <div class="kpi-label">Parts & Accessories Stock</div>
            <div class="kpi-num">{{ number_format($totalPartsInStock) }} <span style="font-size:11px; font-weight:400; color:var(--color-ink-muted);">Units</span></div>
        </div>
        @endif

        <div class="kpi-card">
            <div class="kpi-label">Total Stock Valuation</div>
            <div class="kpi-num">₹{{ number_format($valuationRetail, 2) }}</div>
        </div>
    </div>

    <!-- Inventory Filter Tabs & Quick Actions -->
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:14px;">
        <div class="pills-scroll-rail" style="margin-bottom:0; background:transparent; padding:0; border-bottom:none; display:flex; align-items:center; gap:8px;">
            <button onclick="switchStockTab('all')" id="tabBtn_all" class="filter-pill-btn active">
                All Stock
            </button>
            @if(($canManagePhones ?? false) || in_array($niche ?? '', ['admin', 'phones']))
            <button onclick="switchStockTab('new_phones')" id="tabBtn_new_phones" class="filter-pill-btn">
                Brand New ({{ $totalNewPhonesInStock }})
            </button>
            @endif
            @if(($canManageSecondhand ?? false) || in_array($niche ?? '', ['admin', 'secondhand']))
            <button onclick="switchStockTab('second_hand')" id="tabBtn_second_hand" class="filter-pill-btn">
                Pre-Owned ({{ $totalSecondHandInStock }})
            </button>
            @endif
            @if(($canManageAccessories ?? false) || ($canManageCovers ?? false) || in_array($niche ?? '', ['admin', 'accessories', 'covers']))
            <button onclick="switchStockTab('parts')" id="tabBtn_parts" class="filter-pill-btn">
                Parts & Acc ({{ count($parts) }})
            </button>
            @endif
            @if($lowStockCount > 0)
            <button onclick="filterLowStockOnly()" id="tabBtn_low" class="filter-pill-btn" style="color:#DC2626; border-color:#FCA5A5; background:#FEF2F2; display:inline-flex; align-items:center; gap:5px;">
                <i data-lucide="alert-triangle" style="width:13px;height:13px;"></i> Low Stock ({{ $lowStockCount }})
            </button>
            @endif
        </div>

        <div style="display:flex; align-items:center; gap:8px; width:100%; max-width:380px;">
            <div class="search-bar" style="width:100%;">
                <i data-lucide="search" style="width:15px;height:15px;"></i>
                <input type="text" id="stockLiveSearch" placeholder="Search brand, model, IMEI, SKU..." oninput="filterStockRows()">
            </div>
            <button type="button" onclick="downloadLowStockCSV()" class="btn btn-outline btn-sm" id="btnDownloadLowStock" style="color:#BE123C; border-color:#FECDD3; background:#FFF1F2; font-weight:700; display:inline-flex; align-items:center; gap:6px; flex-shrink:0;">
                <i data-lucide="download" style="width:14px;height:14px;"></i> Low Stock CSV
            </button>
        </div>
    </div>

    <!-- Date Filter Toolbar for Stock Items -->
    <div style="margin-bottom: 16px; background:var(--color-surface); border-radius:var(--radius-card); border:1px solid var(--color-border-subtle); box-shadow:var(--shadow-card); padding:10px 16px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
        <div style="display:flex; gap:6px; align-items:center; flex-wrap:wrap;">
            <span style="font-size:11px; font-weight:800; color:#475569; text-transform:uppercase; margin-right:4px;">Stock Inflow:</span>
            <button type="button" onclick="setStockDatePreset('all')" id="stockDateBtn_all" class="filter-pill stock-date-pill active">All Time</button>
            <button type="button" onclick="setStockDatePreset('today')" id="stockDateBtn_today" class="filter-pill stock-date-pill">Today</button>
            <button type="button" onclick="setStockDatePreset('yesterday')" id="stockDateBtn_yesterday" class="filter-pill stock-date-pill">Yesterday</button>
            <button type="button" onclick="setStockDatePreset('week')" id="stockDateBtn_week" class="filter-pill stock-date-pill">Last 7 Days</button>
            <button type="button" onclick="setStockDatePreset('month')" id="stockDateBtn_month" class="filter-pill stock-date-pill">This Month</button>
        </div>

        <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
            <div style="display:flex; align-items:center; gap:5px;">
                <label for="stockFromDate" style="font-size:11px; font-weight:700; color:#64748B; margin:0;">From:</label>
                <input type="date" id="stockFromDate" onchange="onStockCustomDateChange()" class="form-control" style="font-size:11px; padding:4px 8px; height:auto; width:auto; font-weight:600; color:#0F172A;">
            </div>
            <div style="display:flex; align-items:center; gap:5px;">
                <label for="stockToDate" style="font-size:11px; font-weight:700; color:#64748B; margin:0;">To:</label>
                <input type="date" id="stockToDate" onchange="onStockCustomDateChange()" class="form-control" style="font-size:11px; padding:4px 8px; height:auto; width:auto; font-weight:600; color:#0F172A;">
            </div>
            <button type="button" onclick="setStockDatePreset('all')" title="Reset Date Filter" class="btn btn-outline btn-xs" style="padding:4px 8px;">Reset</button>
        </div>
    </div>

    <div id="stockDesktopTables">
        @if(($canManagePhones ?? false) || in_array($niche ?? '', ['admin', 'phones']))
        <!-- ════ TAB 1: BRAND NEW PHONES ════ -->
        <div id="stockSection_new_phones" class="stock-section card" style="margin-bottom:12px;">
            <div class="card-header" style="background:#F8FAFC; border-bottom:1px solid #E2E8F0;">
                <div>
                    <div class="card-title" style="color:var(--color-text-primary);">Brand New Smartphones</div>
                </div>
            </div>
            <div class="card-body" style="padding:0; overflow-x:auto;">
                <table class="data-table" id="stockNewPhonesTable">
                    <thead>
                        <tr>
                            <th>Brand & Model</th>
                            <th>Variant (RAM/Storage/Color)</th>
                            <th>IMEI 1</th>
                            <th>IMEI 2</th>
                            <th style="text-align:right;">Purchase Cost (₹)</th>
                            <th style="text-align:right;">Selling Price (₹)</th>
                            <th style="text-align:center;">Status</th>
                            <th style="width:90px; text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($newPhones as $phone)
                        <tr class="stock-row" id="stockRow_new_phone_{{ $phone->id }}" data-type="new_phone" data-low="0" data-date="{{ \Carbon\Carbon::parse($phone->created_at)->format('Y-m-d') }}">
                            <td style="font-weight:700; color:#0F172A;">{{ $phone->brand }} {{ $phone->model }}</td>
                            <td>
                                <span class="badge badge-gray" style="font-size:11px;">
                                    {{ $phone->ram ?: '—' }}/{{ $phone->storage ?: '—' }} • {{ $phone->color ?: 'Standard' }}
                                </span>
                            </td>
                            <td style="font-family:monospace; font-weight:600; color:var(--color-text-primary);">{{ $phone->imei_1 }}</td>
                            <td style="font-family:monospace; color:#64748B;">{{ $phone->imei_2 ?: '—' }}</td>
                            <td style="text-align:right; color:#64748B;">₹{{ number_format($phone->purchase_cost, 2) }}</td>
                            <td style="text-align:right; font-weight:700; color:#0F172A;">₹{{ number_format($phone->selling_price, 2) }}</td>
                            <td style="text-align:center;" id="phoneStatusCell_new_phone_{{ $phone->id }}">
                                @if($phone->status === 'in_stock')
                                    <span class="badge badge-green">In Stock</span>
                                @elseif($phone->status === 'deleted')
                                    <span class="badge badge-red">Deleted</span>
                                @else
                                    <span class="badge badge-gray">Sold Out</span>
                                @endif
                            </td>
                            <td style="text-align:center; white-space:nowrap;">
                                <button type="button" 
                                        class="btn btn-outline btn-xs" 
                                        title="View Inventory History" 
                                        style="padding:3px 7px; margin-right:4px;"
                                        onclick="openStockHistoryModal({
                                            type: 'new_phone',
                                            id: {{ $phone->id }},
                                            name: '{{ addslashes($phone->brand . ' ' . $phone->model) }}',
                                            subtext: 'IMEI: {{ $phone->imei_1 }}'
                                        })">
                                    <i data-lucide="history" style="width:13px;height:13px;"></i>
                                </button>
                                @if($phone->status !== 'sold' && $phone->status !== 'deleted')
                                <button type="button" 
                                        class="btn btn-outline btn-xs" 
                                        title="Delete from Stock" 
                                        style="color:#DC2626; border-color:#FCA5A5; padding:3px 7px;"
                                        onclick="openDeleteStockModal({
                                            type: 'new_phone',
                                            id: {{ $phone->id }},
                                            name: '{{ addslashes($phone->brand . ' ' . $phone->model) }}',
                                            subtext: 'IMEI: {{ $phone->imei_1 }}',
                                            stock: {{ $phone->status === 'in_stock' ? 1 : 0 }},
                                            is_phone: 1
                                        })">
                                    <i data-lucide="trash-2" style="width:13px;height:13px;"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" style="text-align:center; padding:24px; color:#94A3B8;">No new mobile phones registered.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div id="stockNewPhonesPagination"></div>
        </div>
        @endif

        @if(($canManageSecondhand ?? false) || in_array($niche ?? '', ['admin', 'secondhand']))
        <!-- ════ TAB 2: SECOND HAND PHONES ════ -->
        <div id="stockSection_second_hand" class="stock-section card" style="margin-bottom:12px;">
            <div class="card-header" style="background:#F8FAFC; border-bottom:1px solid #E2E8F0;">
                <div>
                    <div class="card-title" style="color:var(--color-text-primary);">Pre-Owned & Second Hand Hub</div>
                </div>
            </div>
            <div class="card-body" style="padding:0; overflow-x:auto;">
                <table class="data-table" id="stockSecondHandTable">
                    <thead>
                        <tr>
                            <th>Brand & Model</th>
                            <th>Grade</th>
                            <th>Battery Health</th>
                            <th>IMEI Serial</th>
                            <th style="text-align:right;">Buyback Cost (₹)</th>
                            <th style="text-align:right;">Selling Price (₹)</th>
                            <th style="text-align:center;">Status</th>
                            <th style="width:90px; text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($secondHandPhones as $sh)
                        <tr class="stock-row" id="stockRow_second_hand_{{ $sh->id }}" data-type="second_hand" data-low="0" data-date="{{ \Carbon\Carbon::parse($sh->created_at)->format('Y-m-d') }}">
                            <td style="font-weight:700; color:#0F172A;">{{ $sh->brand }} {{ $sh->model }}</td>
                            <td>
                                <span class="badge badge-orange" style="text-transform:capitalize; font-size:10px;">
                                    {{ str_replace('_', ' ', $sh->condition_grade) }}
                                </span>
                            </td>
                            <td style="font-weight:600; color:#15803D;">{{ $sh->battery_health ? $sh->battery_health . '%' : '—' }}</td>
                            <td style="font-family:monospace; font-weight:600; color:var(--color-text-primary);">{{ $sh->imei_1 }}</td>
                            <td style="text-align:right; color:#64748B;">₹{{ number_format($sh->purchase_cost, 2) }}</td>
                            <td style="text-align:right; font-weight:700; color:#0F172A;">₹{{ number_format($sh->selling_price, 2) }}</td>
                            <td style="text-align:center;" id="phoneStatusCell_second_hand_{{ $sh->id }}">
                                @if($sh->status === 'in_stock')
                                    <span class="badge badge-green">In Stock</span>
                                @elseif($sh->status === 'deleted')
                                    <span class="badge badge-red">Deleted</span>
                                @else
                                    <span class="badge badge-gray">Sold Out</span>
                                @endif
                            </td>
                            <td style="text-align:center; white-space:nowrap;">
                                <button type="button" 
                                        class="btn btn-outline btn-xs" 
                                        title="View Inventory History" 
                                        style="padding:3px 7px; margin-right:4px;"
                                        onclick="openStockHistoryModal({
                                            type: 'second_hand',
                                            id: {{ $sh->id }},
                                            name: '{{ addslashes($sh->brand . ' ' . $sh->model) }}',
                                            subtext: 'IMEI: {{ $sh->imei_1 }}'
                                        })">
                                    <i data-lucide="history" style="width:13px;height:13px;"></i>
                                </button>
                                @if($sh->status !== 'sold' && $sh->status !== 'deleted')
                                <button type="button" 
                                        class="btn btn-outline btn-xs" 
                                        title="Delete from Stock" 
                                        style="color:#DC2626; border-color:#FCA5A5; padding:3px 7px;"
                                        onclick="openDeleteStockModal({
                                            type: 'second_hand',
                                            id: {{ $sh->id }},
                                            name: '{{ addslashes($sh->brand . ' ' . $sh->model) }}',
                                            subtext: 'IMEI: {{ $sh->imei_1 }}',
                                            stock: {{ $sh->status === 'in_stock' ? 1 : 0 }},
                                            is_phone: 1
                                        })">
                                    <i data-lucide="trash-2" style="width:13px;height:13px;"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" style="text-align:center; padding:24px; color:#94A3B8;">No pre-owned phones in stock.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div id="stockSecondHandPagination"></div>
        </div>
        @endif

        @if(($canManageAccessories ?? false) || ($canManageCovers ?? false) || in_array($niche ?? '', ['admin', 'accessories', 'covers']))
        <!-- ════ TAB 3: SPARE PARTS & ACCESSORIES ════ -->
        <div id="stockSection_parts" class="stock-section card">
            <div class="card-header" style="background:#F8FAFC; border-bottom:1px solid #E2E8F0;">
                <div>
                    <div class="card-title" style="color:var(--color-text-primary);">Spare Parts & Retail Accessories Catalog</div>
                </div>
            </div>
            <div class="card-body" style="padding:0; overflow-x:auto;">
                <table class="data-table" id="stockPartsTable">
                    <thead>
                        <tr>
                            <th>Part Name & SKU</th>
                            <th>Category</th>
                            <th>Fits Brand & Model</th>
                            <th style="text-align:center;">Quality / Type</th>
                            <th style="text-align:center;">In-Stock Qty</th>
                            <th style="text-align:right;">Unit Cost (₹)</th>
                            <th style="text-align:right;">Retail Price (₹)</th>
                            <th style="width:90px; text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($parts as $part)
                        @php $isLow = $part->stock_qty <= ($part->min_stock_alert ?? 3); @endphp
                        <tr class="stock-row" id="stockRow_part_{{ $part->id }}" data-type="part" data-low="{{ $isLow ? '1' : '0' }}" data-date="{{ \Carbon\Carbon::parse($part->created_at)->format('Y-m-d') }}" data-name="{{ $part->name }}" data-category="{{ $part->category }}" data-model="{{ $part->compatible_model ?: 'Universal' }}" data-stock="{{ $part->stock_qty }}" data-cost="{{ $part->unit_cost }}" data-price="{{ $part->selling_price }}" data-alert="{{ $part->min_stock_alert ?? 3 }}">
                            <td>
                                <div style="font-weight:800; color:#0F172A; font-size:13px;">{{ $part->name }}</div>
                                @if(!empty($part->description))
                                    <div style="font-size:11px; color:#64748B; margin-top:2px;">{{ Str::limit($part->description, 50) }}</div>
                                @endif
                                <div style="font-size:10px; color:#94A3B8; margin-top:2px;">HSN: {{ $part->hsn_code ?: '85177090' }}</div>
                            </td>
                            <td>
                                <span class="badge badge-gray" style="font-size:10px; text-transform:capitalize;">
                                    {{ str_replace('_', ' ', $part->category) }}
                                </span>
                            </td>
                            <td>
                                <div style="font-weight:700; color:#0F172A; font-size:12px;">{{ $part->brand ?: 'Universal' }}</div>
                                <div style="font-size:11px; color:#64748B;">{{ $part->compatible_model ?: 'Universal / Multi-Model' }}</div>
                            </td>
                            <td style="text-align:center;">
                                @if($part->display_type && $part->display_type !== 'na')
                                    <span class="badge {{ strtolower($part->display_type) === 'og' ? 'badge-blue' : 'badge-gray' }}" style="font-size:10px; font-weight:800;">
                                        {{ strtoupper($part->display_type) }}
                                    </span>
                                @else
                                    <span style="color:#94A3B8; font-size:11px;">Standard</span>
                                @endif
                            </td>
                            <td style="text-align:center;" id="partStockCell_{{ $part->id }}">
                                @if($part->stock_qty <= 0)
                                    <span class="badge badge-gray">0 units</span>
                                @elseif($isLow)
                                    <span class="badge badge-red" style="font-weight:900;">⚠️ {{ $part->stock_qty }} units</span>
                                    <div style="font-size:10px; color:#DC2626; margin-top:2px;">Alert &le; {{ $part->min_stock_alert ?? 3 }}</div>
                                @else
                                    <span class="badge badge-green">{{ $part->stock_qty }} units</span>
                                    <div style="font-size:10px; color:#64748B; margin-top:2px;">Min: {{ $part->min_stock_alert ?? 3 }}</div>
                                @endif
                            </td>
                            <td style="text-align:right; color:#64748B;">₹{{ number_format($part->unit_cost, 2) }}</td>
                            <td style="text-align:right; font-weight:900; color:#0F172A;">₹{{ number_format($part->selling_price, 2) }}</td>
                            <td style="text-align:center; white-space:nowrap;">
                                <button type="button" 
                                        class="btn btn-outline btn-xs" 
                                        title="View Inventory History" 
                                        style="padding:3px 7px; margin-right:4px;"
                                        onclick="openStockHistoryModal({
                                            type: 'part',
                                            id: {{ $part->id }},
                                            name: '{{ addslashes($part->name) }}',
                                            subtext: '{{ addslashes($part->compatible_model ?: 'Universal') }} • {{ strtoupper($part->category) }}'
                                        })">
                                    <i data-lucide="history" style="width:13px;height:13px;"></i>
                                </button>
                                <button type="button" 
                                        class="btn btn-outline btn-xs" 
                                        title="Delete from Stock" 
                                        style="color:#DC2626; border-color:#FCA5A5; padding:3px 7px;"
                                        onclick="openDeleteStockModal({
                                            type: 'part',
                                            id: {{ $part->id }},
                                            name: '{{ addslashes($part->name) }}',
                                            subtext: '{{ addslashes($part->compatible_model ?: 'Universal') }} • Stock: {{ (int)$part->stock_qty }}',
                                            stock: {{ (int)$part->stock_qty }},
                                            is_phone: 0
                                        })">
                                    <i data-lucide="trash-2" style="width:13px;height:13px;"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" style="text-align:center; padding:24px; color:#94A3B8;">No parts or accessories in catalog.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div id="stockPartsPagination"></div>
        </div>
        @endif
    </div>

    <!-- Mobile Zero-Depth Flat Cards Container -->
    <div id="stockMobileCards" style="display:none; flex-direction:column; gap:8px;">
        @if(($canManagePhones ?? false) || in_array($niche ?? '', ['admin', 'phones']))
            @foreach($newPhones as $phone)
            <div class="app-flat-row stock-card" id="stockCard_new_phone_{{ $phone->id }}" data-type="new_phone" data-low="0" data-date="{{ \Carbon\Carbon::parse($phone->created_at)->format('Y-m-d') }}" data-search="{{ strtolower(($phone->brand ?? '') . ' ' . ($phone->model ?? '') . ' ' . ($phone->imei_1 ?? '') . ' ' . ($phone->color ?? '')) }}">
                <div style="display:flex; justify-content:space-between; align-items:center; width:100%;">
                    <div style="display:flex; align-items:center; gap:8px; flex:1; min-width:0;">
                        <div style="width:36px; height:36px; border-radius:8px; background:#EFF6FF; color:#2563EB; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            <i data-lucide="smartphone" style="width:18px;height:18px;"></i>
                        </div>
                        <div style="min-width:0;">
                            <div style="font-weight:800; font-size:13px; color:#0F172A; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $phone->brand }} {{ $phone->model }}</div>
                            <div style="font-size:10px; color:#64748B;">IMEI: <span style="font-family:monospace; font-weight:700;">{{ $phone->imei_1 }}</span> • {{ $phone->ram ?: '' }}/{{ $phone->storage ?: '' }}</div>
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; gap:8px; flex-shrink:0;">
                        <div style="text-align:right;">
                            <div style="font-weight:900; font-size:14px; color:#0F172A;">₹{{ number_format($phone->selling_price, 0) }}</div>
                            <span class="badge {{ $phone->status === 'in_stock' ? 'badge-green' : ($phone->status === 'deleted' ? 'badge-red' : 'badge-gray') }}" style="font-size:10px; padding:1px 6px;">
                                {{ $phone->status === 'in_stock' ? 'In Stock' : ($phone->status === 'deleted' ? 'Deleted' : 'Sold Out') }}
                            </span>
                        </div>
                        <button type="button" 
                                class="stock-action-trigger-btn" 
                                title="Stock options" 
                                onclick="openStockActionMenu(event, {
                                    type: 'new_phone',
                                    id: {{ $phone->id }},
                                    name: '{{ addslashes($phone->brand . ' ' . $phone->model) }}',
                                    subtext: 'IMEI: {{ $phone->imei_1 }}',
                                    stock: {{ $phone->status === 'in_stock' ? 1 : 0 }},
                                    is_sold: {{ $phone->status === 'sold' ? 1 : 0 }},
                                    is_phone: 1
                                })">
                            <i data-lucide="more-vertical" style="width:16px;height:16px;"></i>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        @endif

        @if(($canManageSecondhand ?? false) || in_array($niche ?? '', ['admin', 'secondhand']))
            @foreach($secondHandPhones as $sh)
            <div class="app-flat-row stock-card" id="stockCard_second_hand_{{ $sh->id }}" data-type="second_hand" data-low="0" data-date="{{ \Carbon\Carbon::parse($sh->created_at)->format('Y-m-d') }}" data-search="{{ strtolower(($sh->brand ?? '') . ' ' . ($sh->model ?? '') . ' ' . ($sh->imei_1 ?? '')) }}">
                <div style="display:flex; justify-content:space-between; align-items:center; width:100%;">
                    <div style="display:flex; align-items:center; gap:8px; flex:1; min-width:0;">
                        <div style="width:36px; height:36px; border-radius:8px; background:#FFF7ED; color:#EA580C; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            <i data-lucide="refresh-cw" style="width:18px;height:18px;"></i>
                        </div>
                        <div style="min-width:0;">
                            <div style="font-weight:800; font-size:13px; color:#0F172A; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $sh->brand }} {{ $sh->model }}</div>
                            <div style="font-size:10px; color:#64748B;">IMEI: <span style="font-family:monospace; font-weight:700;">{{ $sh->imei_1 }}</span> @if($sh->battery_health) • 🔋{{ $sh->battery_health }}% @endif</div>
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; gap:8px; flex-shrink:0;">
                        <div style="text-align:right;">
                            <div style="font-weight:900; font-size:14px; color:#0F172A;">₹{{ number_format($sh->selling_price, 0) }}</div>
                            <span class="badge badge-orange" style="font-size:10px; padding:1px 6px;">
                                {{ str_replace('_', ' ', $sh->condition_grade) }}
                            </span>
                        </div>
                        <button type="button" 
                                class="stock-action-trigger-btn" 
                                title="Stock options" 
                                onclick="openStockActionMenu(event, {
                                    type: 'second_hand',
                                    id: {{ $sh->id }},
                                    name: '{{ addslashes($sh->brand . ' ' . $sh->model) }}',
                                    subtext: 'IMEI: {{ $sh->imei_1 }}',
                                    stock: {{ $sh->status === 'in_stock' ? 1 : 0 }},
                                    is_sold: {{ $sh->status === 'sold' ? 1 : 0 }},
                                    is_phone: 1
                                })">
                            <i data-lucide="more-vertical" style="width:16px;height:16px;"></i>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        @endif

        @if(($canManageAccessories ?? false) || ($canManageCovers ?? false) || in_array($niche ?? '', ['admin', 'accessories', 'covers']))
            @foreach($parts as $part)
            @php $isLow = $part->stock_qty <= ($part->min_stock_alert ?? 3); @endphp
            <div class="app-flat-row stock-card" id="stockCard_part_{{ $part->id }}" data-type="part" data-low="{{ $isLow ? '1' : '0' }}" data-date="{{ \Carbon\Carbon::parse($part->created_at)->format('Y-m-d') }}" data-search="{{ strtolower(($part->name ?? '') . ' ' . ($part->category ?? '') . ' ' . ($part->compatible_model ?? '')) }}">
                <div style="display:flex; justify-content:space-between; align-items:center; width:100%;">
                    <div style="display:flex; align-items:center; gap:8px; flex:1; min-width:0;">
                        <div style="width:36px; height:36px; border-radius:8px; background:#F0FDF4; color:#16A34A; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            <i data-lucide="package" style="width:18px;height:18px;"></i>
                        </div>
                        <div style="min-width:0;">
                            <div style="font-weight:800; font-size:13px; color:#0F172A; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $part->name }}</div>
                            <div style="font-size:10px; color:#64748B;">{{ $part->compatible_model ?: 'Universal' }} • {{ str_replace('_', ' ', $part->category) }}</div>
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; gap:8px; flex-shrink:0;">
                        <div style="text-align:right;">
                            <div style="font-weight:900; font-size:14px; color:#0F172A;">₹{{ number_format($part->selling_price, 0) }}</div>
                            <div id="partStockBadgeMobile_{{ $part->id }}">
                                @if($part->stock_qty <= 0)
                                    <span class="badge badge-gray" style="font-size:10px; padding:1px 6px;">0 left</span>
                                @elseif($isLow)
                                    <span class="badge badge-red" style="font-size:10px; padding:1px 6px;">⚠️ {{ $part->stock_qty }} left</span>
                                @else
                                    <span class="badge badge-green" style="font-size:10px; padding:1px 6px;">{{ $part->stock_qty }} in stock</span>
                                @endif
                            </div>
                        </div>
                        <button type="button" 
                                class="stock-action-trigger-btn" 
                                title="Stock options" 
                                onclick="openStockActionMenu(event, {
                                    type: 'part',
                                    id: {{ $part->id }},
                                    name: '{{ addslashes($part->name) }}',
                                    subtext: '{{ addslashes($part->compatible_model ?: 'Universal') }} • {{ strtoupper($part->category) }}',
                                    stock: {{ (int)$part->stock_qty }},
                                    is_sold: 0,
                                    is_phone: 0
                                })">
                            <i data-lucide="more-vertical" style="width:16px;height:16px;"></i>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        @endif
    </div>

    <!-- Floating 3-Dot Context Menu -->
    <div id="stockFloatingMenu">
        <div style="padding: 6px 12px 4px 12px; font-size: 11px; font-weight: 800; color: #94A3B8; text-transform: uppercase; letter-spacing: 0.5px; border-bottom:1px solid #F1F5F9; margin-bottom:4px;" id="menuItemTitle">
            Stock Actions
        </div>
        <button type="button" class="stock-dropdown-item" onclick="triggerStockHistory()">
            <i data-lucide="history" style="width:15px;height:15px;color:#2563EB;"></i>
            <span>Stock History</span>
        </button>
        <button type="button" class="stock-dropdown-item text-danger" id="menuDeleteBtn" onclick="triggerStockDelete()">
            <i data-lucide="trash-2" style="width:15px;height:15px;color:#DC2626;"></i>
            <span id="menuDeleteBtnText">Delete Item</span>
        </button>
    </div>

    <!-- Stock History Timeline Modal -->
    <div id="stockHistoryModal" style="display:none; position:fixed; inset:0; z-index:1200; background:rgba(15,23,42,0.55); backdrop-filter:blur(4px); align-items:center; justify-content:center; padding:16px;">
        <div class="card" style="max-width:680px; width:100%; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); max-height:90vh; display:flex; flex-direction:column; overflow:hidden; border-radius:12px; margin:0;">
            <div class="card-header" style="background:linear-gradient(135deg, #1E293B 0%, #0F172A 100%); color:#fff; border-bottom:1px solid #334155; padding:16px 20px;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div style="width:36px; height:36px; border-radius:8px; background:rgba(59,130,246,0.2); color:#60A5FA; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <i data-lucide="history" style="width:20px;height:20px;"></i>
                    </div>
                    <div>
                        <div class="card-title" style="color:#fff; font-size:16px; margin:0;" id="historyModalTitle">Stock History & Audit Trail</div>
                        <div class="card-subtitle" style="color:#94A3B8; font-size:12px; margin-top:2px;" id="historyModalSubtitle">Timeline of additions, sales, and deletions</div>
                    </div>
                </div>
                <button type="button" onclick="closeStockHistoryModal()" style="background:transparent; border:none; color:#94A3B8; font-size:20px; cursor:pointer; padding:4px 8px; line-height:1; border-radius:6px;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#94A3B8'">✕</button>
            </div>
            <div class="card-body" style="padding:20px; overflow-y:auto; flex:1;">
                <!-- Summary KPI bar inside modal -->
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap:10px; margin-bottom:20px; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:10px; padding:12px;">
                    <div>
                        <div style="font-size:10px; font-weight:700; color:#64748B; text-transform:uppercase;">Current Stock</div>
                        <div style="font-size:16px; font-weight:900; color:#0F172A; margin-top:2px;" id="histKpiStock">—</div>
                    </div>
                    <div>
                        <div style="font-size:10px; font-weight:700; color:#64748B; text-transform:uppercase;">Unit Cost / Buy</div>
                        <div style="font-size:16px; font-weight:900; color:#475569; margin-top:2px;" id="histKpiCost">—</div>
                    </div>
                    <div>
                        <div style="font-size:10px; font-weight:700; color:#64748B; text-transform:uppercase;">Retail Price</div>
                        <div style="font-size:16px; font-weight:900; color:#2563EB; margin-top:2px;" id="histKpiPrice">—</div>
                    </div>
                    <div>
                        <div style="font-size:10px; font-weight:700; color:#64748B; text-transform:uppercase;">Status</div>
                        <div style="margin-top:4px;" id="histKpiStatus">—</div>
                    </div>
                </div>

                <!-- Section Header -->
                <div style="font-size:12px; font-weight:800; color:#475569; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:12px; display:flex; align-items:center; gap:6px;">
                    <i data-lucide="git-commit" style="width:14px;height:14px;color:#64748B;"></i>
                    Activity Audit Log (Chronological)
                </div>

                <!-- Loading Spinner -->
                <div id="historyLoadingState" style="padding:40px; text-align:center; color:#64748B;">
                    <div style="display:inline-block; animation: spin 1s linear infinite; margin-bottom:8px;">
                        <i data-lucide="loader-2" style="width:28px;height:28px;color:#2563EB;"></i>
                    </div>
                    <div style="font-size:13px; font-weight:600;">Loading stock history audit trail...</div>
                </div>

                <!-- Empty State -->
                <div id="historyEmptyState" style="display:none; padding:36px 16px; text-align:center; color:#94A3B8;">
                    <i data-lucide="inbox" style="width:36px;height:36px;margin-bottom:8px;opacity:0.6;"></i>
                    <div style="font-size:14px; font-weight:600; color:#64748B;">No recorded history events found for this item.</div>
                </div>

                <!-- Timeline Container -->
                <div id="historyTimelineContainer" class="stock-timeline" style="display:none;"></div>
            </div>
            <div style="display:flex; justify-content:flex-end; padding:12px 20px; background:#F8FAFC; border-top:1px solid #E2E8F0;">
                <button type="button" onclick="closeStockHistoryModal()" class="btn btn-outline btn-sm">Close</button>
            </div>
        </div>
    </div>

    <!-- Delete / Reduce Stock Modal -->
    <div id="deleteStockModal" style="display:none; position:fixed; inset:0; z-index:1200; background:rgba(15,23,42,0.55); backdrop-filter:blur(4px); align-items:center; justify-content:center; padding:16px;">
        <div class="card" style="max-width:520px; width:100%; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); border-radius:12px; margin:0; overflow:hidden;">
            <div class="card-header" style="background:#DC2626; color:#fff; padding:16px 20px;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div style="width:36px; height:36px; border-radius:8px; background:rgba(255,255,255,0.2); color:#fff; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <i data-lucide="trash-2" style="width:20px;height:20px;"></i>
                    </div>
                    <div>
                        <div class="card-title" style="color:#fff; font-size:16px; margin:0;">Delete / Reduce Stock</div>
                        <div class="card-subtitle" style="color:rgba(255,255,255,0.85); font-size:12px; margin-top:2px;">Write-off inventory with reason & quantity tracking</div>
                    </div>
                </div>
                <button type="button" onclick="closeDeleteStockModal()" style="background:transparent; border:none; color:rgba(255,255,255,0.8); font-size:20px; cursor:pointer; padding:4px 8px; line-height:1; border-radius:6px;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.8)'">✕</button>
            </div>
            <form id="deleteStockForm" onsubmit="submitStockDelete(event)">
                @csrf
                <input type="hidden" name="item_type" id="deleteItemType">
                <input type="hidden" name="item_id" id="deleteItemId">

                <div class="card-body" style="padding:20px;">
                    <!-- Item Overview Banner -->
                    <div style="background:#FEF2F2; border:1px solid #FCA5A5; border-radius:8px; padding:12px 14px; margin-bottom:16px;">
                        <div style="font-size:14px; font-weight:800; color:#991B1B;" id="deleteItemName">Item Name</div>
                        <div style="font-size:12px; color:#B91C1C; margin-top:2px;" id="deleteItemSubtext">Details</div>
                        <div style="font-size:12px; font-weight:700; color:#7F1D1D; margin-top:4px;" id="deleteItemStockInfo">Available in stock: 1 unit</div>
                    </div>

                    <!-- Quantity Input (For Parts: dynamic stepper; For Phones: 1 fixed) -->
                    <div class="form-group" style="margin-bottom:16px;" id="deleteQtyGroup">
                        <label class="form-label required" style="font-weight:700; font-size:12px; color:#0F172A;">
                            Quantity to Delete / Remove
                        </label>
                        <div style="display:flex; gap:8px; align-items:center;">
                            <button type="button" class="btn btn-outline" style="padding:6px 12px; font-weight:800;" onclick="adjustDeleteQty(-1)">-</button>
                            <input type="number" name="quantity" id="deleteItemQty" value="1" min="1" class="form-control" style="font-weight:700; text-align:center; font-size:15px;" required>
                            <button type="button" class="btn btn-outline" style="padding:6px 12px; font-weight:800;" onclick="adjustDeleteQty(1)">+</button>
                            <button type="button" class="btn btn-outline btn-sm" id="btnDeleteAllQty" onclick="setDeleteAllQty()" style="font-size:11px; font-weight:700; color:#DC2626; border-color:#FCA5A5; white-space:nowrap;">
                                Delete All
                            </button>
                        </div>
                        <div style="font-size:11px; color:#64748B; margin-top:4px;" id="deleteQtyHint">
                            Specify how many items to write off from stock.
                        </div>
                    </div>

                    <!-- Fixed Phone Note (Shown only for phones) -->
                    <div id="deletePhoneNotice" style="display:none; background:#FFFBEB; border:1px solid #FDE68A; border-radius:8px; padding:10px 12px; margin-bottom:16px; font-size:12px; color:#92400E;">
                        <i data-lucide="info" style="width:14px;height:14px;display:inline;margin-right:4px;vertical-align:middle;"></i>
                        Unique serial/IMEI device unit. Exactly <strong>1 unit</strong> will be marked as deleted and removed from stock.
                    </div>

                    <!-- Reason Dropdown -->
                    <div class="form-group" style="margin-bottom:16px;">
                        <label class="form-label required" style="font-weight:700; font-size:12px; color:#0F172A;">
                            Reason for Deletion / Write-off
                        </label>
                        <select name="reason" id="deleteReasonSelect" class="form-control" required style="font-weight:600; font-size:13px;">
                            <option value="">-- Select Reason --</option>
                            <option value="Damaged / Broken in Shop">Damaged / Broken in Shop</option>
                            <option value="Defective / Dead on Arrival (DOA)">Defective / Dead on Arrival (DOA)</option>
                            <option value="Returned to Supplier / RMA">Returned to Supplier / RMA</option>
                            <option value="Physical Stock Loss / Recount Variance">Physical Stock Loss / Recount Variance</option>
                            <option value="Obsolete / Unsellable Stock">Obsolete / Unsellable Stock</option>
                            <option value="Wrong Entry / Duplicate Record">Wrong Entry / Duplicate Record</option>
                            <option value="Other / Manual Write-off">Other / Manual Write-off</option>
                        </select>
                    </div>

                    <!-- Optional Detailed Notes -->
                    <div class="form-group" style="margin-bottom:16px;">
                        <label class="form-label" style="font-weight:700; font-size:12px; color:#0F172A;">
                            Additional Notes / Remarks <span style="font-weight:400; color:#64748B;">(Optional)</span>
                        </label>
                        <textarea name="notes" id="deleteItemNotes" rows="2" class="form-control" placeholder="Provide extra context (e.g. courier damage, replacement tracking #, customer return...)" style="font-size:12px; resize:vertical;"></textarea>
                    </div>

                    <!-- User Audit Assurance Notice -->
                    <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:10px 12px; font-size:11px; color:#475569; display:flex; align-items:center; gap:8px; margin-bottom:14px;">
                        <i data-lucide="shield-alert" style="width:16px;height:16px;color:#DC2626;flex-shrink:0;"></i>
                        <span>This action will be permanently logged in the audit trail under your user account: <strong>{{ auth()->user()->name ?? 'User' }}</strong>.</span>
                    </div>

                    @php
                        $isStoreAdmin = auth()->check() && (auth()->user()->hasRole('store-admin') || auth()->user()->hasRole('admin'));
                    @endphp
                    @if(!$isStoreAdmin)
                    <div id="otpSectionInDeleteModal" style="background:#EFF6FF; border:1px solid #BFDBFE; border-radius:8px; padding:12px 14px; margin-bottom:14px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                            <div style="font-size:12px; font-weight:700; color:#1E40AF; display:flex; align-items:center; gap:6px;">
                                <i data-lucide="shield-alert" style="width:16px;height:16px;color:#2563EB;"></i>
                                Owner OTP Required
                            </div>
                            <button type="button" class="btn btn-sm btn-primary" id="btnSendDeleteOtp" onclick="requestDeleteStockOtp()" style="font-size:11px; padding:3px 10px; background:#2563EB; border-color:#2563EB;">
                                Send OTP to Owner
                            </button>
                        </div>
                        <div style="font-size:11px; color:#3B82F6; margin-bottom:8px;" id="deleteOtpStatusText">
                            Only the Store Owner can authorize deleting stock. Click above to send a 6-digit OTP code to the owner's email.
                        </div>
                        <div style="display:flex; gap:8px; align-items:center;">
                            <input type="text" name="otp_code" id="deleteStockOtpCode" class="form-control" placeholder="6-digit OTP" maxlength="6" style="letter-spacing:4px; font-weight:800; font-size:16px; text-align:center; max-width:180px;">
                            <span style="font-size:11px; color:#64748B;">Obtain code from Store Owner</span>
                        </div>
                    </div>
                    @else
                    <div style="background:#F0FDF4; border:1px solid #BBF7D0; border-radius:8px; padding:10px 12px; margin-bottom:14px; font-size:12px; color:#166534; display:flex; align-items:center; gap:8px;">
                        <i data-lucide="shield-check" style="width:16px;height:16px;color:#16A34A;flex-shrink:0;"></i>
                        <span><strong>Owner Privileges:</strong> Direct deletion authorized (OTP bypass enabled).</span>
                    </div>
                    @endif

                    <!-- Error Alert inside modal -->
                    <div id="deleteModalError" style="display:none; margin-top:12px; background:#FEF2F2; border:1px solid #F87171; border-radius:8px; padding:10px; font-size:12px; color:#B91C1C;"></div>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px; padding:14px 20px; background:#F8FAFC; border-top:1px solid #E2E8F0;">
                    <button type="button" onclick="closeDeleteStockModal()" class="btn btn-outline">Cancel</button>
                    <button type="submit" id="btnConfirmDeleteStock" class="btn btn-primary" style="background:#DC2626; border-color:#DC2626; display:inline-flex; align-items:center; gap:6px;">
                        <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                        <span id="btnConfirmDeleteStockText">Confirm Deletion</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Mobile Floating Action Button -->
    <div class="mobile-fab-container">
        <button type="button" onclick="openStockQuickAddDrawer()" class="btn-app-fab" title="Add Stock Item" style="background:#2563EB; border:none; cursor:pointer;">
            <i data-lucide="plus" style="width:20px;height:20px;"></i>
            <span>Add Stock</span>
        </button>
    </div>

    <!-- MODAL: Add Brand New Phone -->
    <div id="addMobileModal" style="display:none; position: fixed; inset: 0; z-index: 1200; background: rgba(15,23,42,0.45); backdrop-filter: blur(4px); align-items:center; justify-content:center; padding: 16px;">
        <div class="card" style="max-width: 520px; width: 100%; max-height:90vh; overflow-y:auto; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); border-radius:14px; background:#fff;">
            <div class="card-header" style="border-bottom:1px solid #E2E8F0; padding:14px 18px; display:flex; justify-content:space-between; align-items:center;">
                <div class="card-title" style="font-weight:700; font-size:15px; color:#0F172A; display:flex; align-items:center; gap:8px;">
                    <i data-lucide="smartphone" style="width:18px;height:18px;color:#2563EB;"></i>
                    Add Brand New Mobile to Stock
                </div>
                <button type="button" onclick="closeAddMobileModal()" class="btn-icon" style="background:none; border:none; font-size:16px; cursor:pointer; color:#64748B;">✕</button>
            </div>
            <div class="card-body" style="padding:16px 18px;">
                <form action="{{ route('mobileshop.new_mobiles.store') }}" method="POST" enctype="multipart/form-data" id="newMobilesForm">
                    @csrf
                    <input type="hidden" name="redirect_to" value="{{ route('mobileshop.stock', ['tab' => 'new_phones']) }}">

                    <!-- Photo Upload Field with Live Preview -->
                    <div style="margin-bottom: 14px; background:#F8FAFC; border:1px dashed #CBD5E1; border-radius:10px; padding:12px; text-align:center;">
                        <div id="photoPreviewBox" style="display:none; margin-bottom:8px;">
                            <img id="newPhonePreviewImg" src="" alt="Preview" style="max-height:120px; border-radius:8px; object-fit:contain; border:1px solid #E2E8F0;">
                        </div>
                        <label style="display:inline-flex; align-items:center; gap:6px; cursor:pointer; font-size:12px; font-weight:700; color:#2563EB; background:#EFF6FF; padding:6px 14px; border-radius:8px; border:1px solid #BFDBFE;">
                            <i data-lucide="camera" style="width:14px;height:14px;"></i> Upload Phone / Box Photo
                            <input type="file" name="photo" accept="image/*" style="display:none;" onchange="previewSelectedPhoto(this, 'newPhonePreviewImg', 'photoPreviewBox')">
                        </label>
                        <div style="font-size:10px; color:#64748B; margin-top:4px;">JPG, PNG, WebP up to 5MB</div>
                    </div>

                    <div class="form-row" style="margin-bottom: 12px; display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Brand *</label>
                            <input type="text" name="brand" placeholder="e.g. Samsung / Apple" required class="form-control" style="font-size:13px;">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Model *</label>
                            <input type="text" name="model" placeholder="e.g. Galaxy S24 Ultra" required class="form-control" style="font-size:13px;">
                        </div>
                    </div>

                    <div class="form-row" style="margin-bottom: 12px; display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Color</label>
                            <input type="text" name="color" placeholder="e.g. Titanium Black" class="form-control" style="font-size:13px;">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">RAM & Storage</label>
                            <input type="text" name="storage" placeholder="e.g. 12GB / 256GB" class="form-control" style="font-size:13px;">
                        </div>
                    </div>

                    <div class="form-row" style="margin-bottom: 12px; display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Primary IMEI (IMEI 1) *</label>
                            <input type="text" name="imei_1" required placeholder="15-digit IMEI" class="form-control" style="font-family:monospace; font-weight:700; font-size:13px;">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Secondary IMEI 2</label>
                            <input type="text" name="imei_2" placeholder="Optional" class="form-control" style="font-family:monospace; font-size:13px;">
                        </div>
                    </div>

                    <div class="form-row" style="margin-bottom: 14px; display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Purchase Cost (₹) *</label>
                            <input type="number" step="0.01" name="purchase_cost" required placeholder="0.00" class="form-control" style="font-size:13px;">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Selling Price (₹) *</label>
                            <input type="number" step="0.01" name="selling_price" required placeholder="0.00" class="form-control" style="font-weight:700; color:#16A34A; font-size:13px;">
                        </div>
                    </div>

                    <div style="display:flex; justify-content:flex-end; gap: 10px; padding-top: 14px; border-top: 1px solid #E2E8F0;">
                        <button type="button" onclick="closeAddMobileModal()" class="btn btn-outline" style="font-size:12px;">Cancel</button>
                        <button type="submit" class="btn btn-primary" style="font-size:12px;">Add Phone to Inventory</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL: Intake / Register Pre-Owned Buyback -->
    <div id="buybackModal" style="display:none; position: fixed; inset: 0; z-index: 1200; background: rgba(15,23,42,0.45); backdrop-filter: blur(4px); align-items:center; justify-content:center; padding: 16px;">
        <div class="card" style="max-width: 540px; width: 100%; max-height: 90vh; overflow-y:auto; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); border-radius:14px; background:#fff;">
            <div class="card-header" style="border-bottom:1px solid #E2E8F0; padding:14px 18px; display:flex; justify-content:space-between; align-items:center;">
                <div class="card-title" style="font-weight:700; font-size:15px; color:#0F172A; display:flex; align-items:center; gap:8px;">
                    <i data-lucide="refresh-cw" style="width:18px;height:18px;color:#EA580C;"></i>
                    Customer Device Buyback Intake
                </div>
                <button type="button" onclick="closeBuybackModal()" class="btn-icon" style="background:none; border:none; font-size:16px; cursor:pointer; color:#64748B;">✕</button>
            </div>
            <div class="card-body" style="padding:16px 18px;">
                <form action="{{ route('mobileshop.second_hand.buyback') }}" method="POST" enctype="multipart/form-data" id="buybackForm">
                    @csrf
                    <input type="hidden" name="redirect_to" value="{{ route('mobileshop.stock', ['tab' => 'second_hand']) }}">

                    <!-- Photo Upload Field with Live Preview -->
                    <div style="margin-bottom: 14px; background:#F8FAFC; border:1px dashed #CBD5E1; border-radius:10px; padding:12px; text-align:center;">
                        <div id="shPhotoPreviewBox" style="display:none; margin-bottom:8px;">
                            <img id="shPreviewImg" src="" alt="Preview" style="max-height:120px; border-radius:8px; object-fit:contain; border:1px solid #E2E8F0;">
                        </div>
                        <label style="display:inline-flex; align-items:center; gap:6px; cursor:pointer; font-size:12px; font-weight:700; color:#EA580C; background:#FFF7ED; padding:6px 14px; border-radius:8px; border:1px solid #FED7AA;">
                            <i data-lucide="camera" style="width:14px;height:14px;"></i> Upload Device Condition Photo
                            <input type="file" name="photo" accept="image/*" style="display:none;" onchange="previewSelectedPhoto(this, 'shPreviewImg', 'shPhotoPreviewBox')">
                        </label>
                        <div style="font-size:10px; color:#64748B; margin-top:4px;">Capture screen/body condition (JPG, PNG, WebP up to 5MB)</div>
                    </div>

                    <div class="form-row" style="margin-bottom: 12px; display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Brand *</label>
                            <input type="text" name="brand" placeholder="e.g. Apple" required class="form-control" style="font-size:13px;">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Model Name *</label>
                            <input type="text" name="model" placeholder="e.g. iPhone 13 Pro" required class="form-control" style="font-size:13px;">
                        </div>
                    </div>

                    <div class="form-row" style="margin-bottom: 12px; display:grid; grid-template-columns:1fr 1fr 1fr; gap:8px;">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Color</label>
                            <input type="text" name="color" placeholder="e.g. Blue" class="form-control" style="font-size:13px;">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Storage</label>
                            <input type="text" name="storage" placeholder="e.g. 128GB" class="form-control" style="font-size:13px;">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Battery %</label>
                            <input type="number" name="battery_health" placeholder="88" class="form-control" style="font-size:13px;">
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom:12px;">
                        <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">IMEI 1 Number *</label>
                        <input type="text" name="imei_1" placeholder="15-digit IMEI" required class="form-control" style="font-family:monospace; font-weight:700; font-size:13px;">
                    </div>

                    <div class="form-row" style="margin-bottom: 12px; display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Buyback Cost (₹) *</label>
                            <input type="number" step="0.01" name="purchase_cost" placeholder="42000" required class="form-control" style="font-size:13px;">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Target Resale Price (₹) *</label>
                            <input type="number" step="0.01" name="selling_price" placeholder="54999" required class="form-control" style="font-weight:700; color:#16A34A; font-size:13px;">
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
                        <div class="form-row" style="margin-bottom: 8px; display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                            <input type="text" name="customer_buyback_name" placeholder="Customer Name *" required class="form-control" style="font-size:12px;">
                            <input type="text" name="customer_buyback_phone" placeholder="Customer Phone *" required class="form-control" style="font-size:12px;">
                        </div>
                        <input type="text" name="customer_buyback_id_proof" placeholder="Aadhaar / ID Details (Optional)" class="form-control" style="font-size:12px;">
                    </div>

                    <div class="form-group" style="margin-bottom:14px;">
                        <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Inspection Remarks</label>
                        <textarea name="checklist_notes" rows="2" placeholder="e.g. Original display, FaceID verified" class="form-control" style="font-size:12px;"></textarea>
                    </div>

                    <div style="display:flex; justify-content:flex-end; gap: 10px; padding-top: 14px; border-top: 1px solid #E2E8F0;">
                        <button type="button" onclick="closeBuybackModal()" class="btn btn-outline" style="font-size:12px;">Cancel</button>
                        <button type="submit" class="btn btn-primary" style="background:#EA580C; border-color:#EA580C; font-size:12px;">Save Pre-Owned to Stock</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL: Add Part / Accessory -->
    <div id="addPartModal" style="display:none; position: fixed; inset: 0; z-index: 1200; background: rgba(15,23,42,0.45); backdrop-filter: blur(4px); align-items:center; justify-content:center; padding: 16px;">
        <div class="card" style="max-width: 520px; width: 100%; max-height: 90vh; overflow-y:auto; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); border-radius:14px; background:#fff;">
            <div class="card-header" style="border-bottom:1px solid #E2E8F0; padding:14px 18px; display:flex; justify-content:space-between; align-items:center;">
                <div class="card-title" style="font-weight:700; font-size:15px; color:#0F172A; display:flex; align-items:center; gap:8px;">
                    <i data-lucide="package" style="width:18px;height:18px;color:#16A34A;"></i>
                    Add Part or Accessory to Inventory
                </div>
                <button type="button" onclick="closeAddPartModal()" class="btn-icon" style="background:none; border:none; font-size:16px; cursor:pointer; color:#64748B;">✕</button>
            </div>
            <div class="card-body" style="padding:16px 18px;">
                <form action="{{ route('mobileshop.accessories.store') }}" method="POST" id="addPartForm">
                    @csrf
                    <input type="hidden" name="redirect_to" value="{{ route('mobileshop.stock', ['tab' => 'parts']) }}">

                    <div class="form-row" style="margin-bottom: 12px; display:grid; grid-template-columns:1fr 1fr; gap:10px;">
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
                            <input type="text" name="brand" placeholder="e.g. Boat / Anker / Generic" class="form-control" style="font-size:13px;">
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

                    <div class="form-row" style="margin-bottom: 12px; display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Unit Cost (₹) *</label>
                            <input type="number" step="0.01" name="unit_cost" placeholder="250.00" required class="form-control" style="font-size:13px;">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Selling Price (₹) *</label>
                            <input type="number" step="0.01" name="selling_price" placeholder="499.00" required class="form-control" style="font-weight:700; color:#16A34A; font-size:13px;">
                        </div>
                    </div>

                    <div class="form-row" style="margin-bottom: 14px; display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Initial Stock Qty *</label>
                            <input type="number" name="stock_qty" value="10" required class="form-control" style="font-size:13px;">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px; display:block;">Low Stock Alert</label>
                            <input type="number" name="min_stock_alert" value="3" class="form-control" style="font-size:13px;">
                        </div>
                    </div>

                    <div style="display:flex; justify-content:flex-end; gap: 10px; padding-top: 14px; border-top: 1px solid #E2E8F0;">
                        <button type="button" onclick="closeAddPartModal()" class="btn btn-outline" style="font-size:12px;">Cancel</button>
                        <button type="submit" class="btn btn-primary" style="background:#16A34A; border-color:#16A34A; font-size:12px;">Add Part to Stock</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Mobile Quick-Add Drawer -->
    <div id="stockQuickAddDrawer" style="display:none; position: fixed; inset: 0; z-index: 1200; background: rgba(15,23,42,0.45); backdrop-filter: blur(4px); align-items:flex-end; justify-content:center;" onclick="closeStockQuickAddDrawer()">
        <div style="width: 100%; max-width: 500px; background: #fff; border-radius: 16px 16px 0 0; padding: 20px; box-shadow: 0 -10px 25px rgba(0,0,0,0.1);" onclick="event.stopPropagation()">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <div style="font-weight:800; font-size:15px; color:#0F172A;">Add Stock Inflow</div>
                <button type="button" onclick="closeStockQuickAddDrawer()" style="background:none; border:none; font-size:16px; cursor:pointer; color:#64748B;">✕</button>
            </div>
            <div style="display:flex; flex-direction:column; gap:10px;">
                @if($canManagePhones ?? false)
                <button type="button" onclick="closeStockQuickAddDrawer(); openAddMobileModal();" class="btn btn-outline" style="display:flex; align-items:center; gap:10px; justify-content:flex-start; padding:12px 14px; text-align:left; border-radius:8px;">
                    <div style="width:32px; height:32px; border-radius:8px; background:#EFF6FF; color:#2563EB; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <i data-lucide="smartphone" style="width:16px;height:16px;"></i>
                    </div>
                    <div>
                        <div style="font-weight:700; font-size:13px; color:#0F172A;">Add Brand New Phone</div>
                        <div style="font-size:11px; color:#64748B;">IMEI barcode register and inventory intake</div>
                    </div>
                </button>
                @endif
                @if($canManageSecondhand ?? false)
                <button type="button" onclick="closeStockQuickAddDrawer(); openBuybackModal();" class="btn btn-outline" style="display:flex; align-items:center; gap:10px; justify-content:flex-start; padding:12px 14px; text-align:left; border-radius:8px;">
                    <div style="width:32px; height:32px; border-radius:8px; background:#FFF7ED; color:#EA580C; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <i data-lucide="refresh-cw" style="width:16px;height:16px;"></i>
                    </div>
                    <div>
                        <div style="font-weight:700; font-size:13px; color:#0F172A;">Intake Pre-Owned Device</div>
                        <div style="font-size:11px; color:#64748B;">Customer buyback intake & diagnostic grading</div>
                    </div>
                </button>
                @endif
                @if(($canManageAccessories ?? false) || ($canManageCovers ?? false))
                <button type="button" onclick="closeStockQuickAddDrawer(); openAddPartModal();" class="btn btn-outline" style="display:flex; align-items:center; gap:10px; justify-content:flex-start; padding:12px 14px; text-align:left; border-radius:8px;">
                    <div style="width:32px; height:32px; border-radius:8px; background:#F0FDF4; color:#16A34A; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <i data-lucide="package" style="width:16px;height:16px;"></i>
                    </div>
                    <div>
                        <div style="font-weight:700; font-size:13px; color:#0F172A;">Add Part / Accessory</div>
                        <div style="font-size:11px; color:#64748B;">Covers, tempered glass, cables, chargers, spares</div>
                    </div>
                </button>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<style>
@media (max-width: 768px) {
    #stockDesktopKpiGrid { display: none !important; }
    #stockDesktopTables { display: none !important; }
    #stockMobileCards { display: flex !important; }
}

/* 3-Dot Action Button */
.stock-action-trigger-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: 1px solid #E2E8F0;
    background: #FFFFFF;
    color: #475569;
    cursor: pointer;
    transition: all 0.15s ease-in-out;
    padding: 0;
    flex-shrink: 0;
}
.stock-action-trigger-btn:hover {
    background: #F1F5F9;
    color: #0F172A;
    border-color: #CBD5E1;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}
.stock-action-trigger-btn:active {
    transform: translateY(0);
}

/* Floating 3-Dot Menu */
#stockFloatingMenu {
    position: fixed;
    z-index: 1100;
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 10px;
    box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.18), 0 8px 10px -6px rgba(15, 23, 42, 0.1);
    min-width: 210px;
    padding: 6px;
    display: none;
    animation: stockMenuPop 0.12s ease-out;
}
@keyframes stockMenuPop {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}
.stock-dropdown-item {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 9px 12px;
    font-size: 13px;
    font-weight: 600;
    color: #334155;
    background: transparent;
    border: none;
    border-radius: 6px;
    text-align: left;
    cursor: pointer;
    transition: background 0.12s ease, color 0.12s ease;
}
.stock-dropdown-item:hover {
    background: #F8FAFC;
    color: #0F172A;
}
.stock-dropdown-item.text-danger {
    color: #DC2626;
}
.stock-dropdown-item.text-danger:hover {
    background: #FEF2F2;
    color: #B91C1C;
}

/* Timeline in Stock History */
.stock-timeline {
    position: relative;
    padding-left: 28px;
    margin-top: 14px;
}
.stock-timeline::before {
    content: '';
    position: absolute;
    top: 12px;
    bottom: 12px;
    left: 11px;
    width: 2px;
    background: #E2E8F0;
}
.stock-timeline-item {
    position: relative;
    margin-bottom: 18px;
}
.stock-timeline-item:last-child {
    margin-bottom: 0;
}
.stock-timeline-node {
    position: absolute;
    left: -28px;
    top: 4px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #fff;
    border: 2px solid #CBD5E1;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2;
}
.stock-timeline-node.node-added {
    border-color: #16A34A;
    background: #DCFCE7;
    color: #16A34A;
}
.stock-timeline-node.node-sold {
    border-color: #2563EB;
    background: #DBEAFE;
    color: #2563EB;
}
.stock-timeline-node.node-deleted {
    border-color: #DC2626;
    background: #FEE2E2;
    color: #DC2626;
}
.stock-timeline-card {
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 8px;
    padding: 12px 14px;
    transition: all 0.15s ease;
}
.stock-timeline-card:hover {
    background: #FFFFFF;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
    border-color: #CBD5E1;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
<script>
    let currentStockTab = 'all';

    function switchStockTab(tab) {
        currentStockTab = tab;
        document.querySelectorAll('.filter-pill-btn').forEach(btn => btn.classList.remove('active'));
        const activeBtn = document.getElementById(`tabBtn_${tab}`);
        if (activeBtn) activeBtn.classList.add('active');

        const secNew = document.getElementById('stockSection_new_phones');
        const secSh = document.getElementById('stockSection_second_hand');
        const secParts = document.getElementById('stockSection_parts');

        if (tab === 'all') {
            if (secNew) secNew.style.display = 'block';
            if (secSh) secSh.style.display = 'block';
            if (secParts) secParts.style.display = 'block';
        } else if (tab === 'new_phones') {
            if (secNew) secNew.style.display = 'block';
            if (secSh) secSh.style.display = 'none';
            if (secParts) secParts.style.display = 'none';
        } else if (tab === 'second_hand') {
            if (secNew) secNew.style.display = 'none';
            if (secSh) secSh.style.display = 'block';
            if (secParts) secParts.style.display = 'none';
        } else if (tab === 'parts') {
            if (secNew) secNew.style.display = 'none';
            if (secSh) secSh.style.display = 'none';
            if (secParts) secParts.style.display = 'block';
        }

        filterStockRows();
    }

    function filterLowStockOnly() {
        currentStockTab = 'low';
        document.querySelectorAll('.filter-pill-btn').forEach(btn => btn.classList.remove('active'));
        const btnLow = document.getElementById('tabBtn_low');
        if (btnLow) btnLow.classList.add('active');

        const secNew = document.getElementById('stockSection_new_phones');
        const secSh = document.getElementById('stockSection_second_hand');
        const secParts = document.getElementById('stockSection_parts');

        if (secNew) secNew.style.display = 'none';
        if (secSh) secSh.style.display = 'none';
        if (secParts) secParts.style.display = 'block';

        filterStockRows();
    }

    let currentStockDatePreset = 'all';

    function calculateStockDateRange(preset) {
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

    function setStockDatePreset(preset) {
        currentStockDatePreset = preset;
        const fromInput = document.getElementById('stockFromDate');
        const toInput = document.getElementById('stockToDate');

        document.querySelectorAll('.stock-date-pill').forEach(el => el.classList.remove('active'));
        const targetId = 'stockDateBtn_' + (preset === '7days' ? 'week' : preset);
        const btn = document.getElementById(targetId) || document.getElementById('stockDateBtn_' + preset);
        if (btn) btn.classList.add('active');

        const range = calculateStockDateRange(preset);

        if (fromInput) fromInput.value = range.from || '';
        if (toInput) toInput.value = range.to || '';

        filterStockRows();
    }

    function onStockCustomDateChange() {
        document.querySelectorAll('.stock-date-pill').forEach(el => el.classList.remove('active'));
        filterStockRows();
    }

    function onStockSearch(term) {
        filterStockRows();
    }

    function filterStockRows() {
        const term = (document.getElementById('stockLiveSearch')?.value || '').toLowerCase().trim();
        const fromDate = document.getElementById('stockFromDate')?.value || '';
        const toDate = document.getElementById('stockToDate')?.value || '';

        // Desktop Rows
        document.querySelectorAll('.stock-row').forEach(row => {
            const rowText = row.textContent.toLowerCase();
            const rawDate = (row.dataset.date || '').trim();
            const cleanRowDate = rawDate.length >= 10 ? rawDate.slice(0, 10) : rawDate;
            const isLow = row.dataset.low === '1';

            const matchesText = !term || rowText.includes(term);
            let matchesDate = true;
            if (fromDate) matchesDate = matchesDate && (cleanRowDate !== '' && cleanRowDate >= fromDate);
            if (toDate) matchesDate = matchesDate && (cleanRowDate !== '' && cleanRowDate <= toDate);
            let matchesLow = (currentStockTab !== 'low') || isLow;

            const isVisible = matchesText && matchesDate && matchesLow;
            row.dataset.mobiHidden = isVisible ? '0' : '1';
            row.style.display = isVisible ? '' : 'none';
        });

        // Mobile Cards
        document.querySelectorAll('#stockMobileCards .stock-card').forEach(card => {
            const cardText = (card.dataset.search || card.textContent).toLowerCase();
            const rawDate = (card.dataset.date || '').trim();
            const cleanCardDate = rawDate.length >= 10 ? rawDate.slice(0, 10) : rawDate;
            const cardType = card.dataset.type || '';
            const isLow = card.dataset.low === '1';

            const matchesText = !term || cardText.includes(term);
            let matchesDate = true;
            if (fromDate) matchesDate = matchesDate && (cleanCardDate !== '' && cleanCardDate >= fromDate);
            if (toDate) matchesDate = matchesDate && (cleanCardDate !== '' && cleanCardDate <= toDate);

            let matchesTab = true;
            if (currentStockTab === 'new_phones') matchesTab = (cardType === 'new_phone');
            else if (currentStockTab === 'second_hand') matchesTab = (cardType === 'second_hand');
            else if (currentStockTab === 'parts') matchesTab = (cardType === 'part');
            else if (currentStockTab === 'low') matchesTab = isLow;

            const isCardVisible = matchesText && matchesDate && matchesTab;
            card.dataset.mobiHidden = isCardVisible ? '0' : '1';
            card.style.display = isCardVisible ? '' : 'none';
        });

        if (window.stockNewPager && typeof window.stockNewPager.refresh === 'function') window.stockNewPager.refresh();
        if (window.stockShPager && typeof window.stockShPager.refresh === 'function') window.stockShPager.refresh();
        if (window.stockPartsPager && typeof window.stockPartsPager.refresh === 'function') window.stockPartsPager.refresh();
    }

    function initStockPage() {
        if (window.setupMobiTablePagination) {
            if (document.getElementById('stockNewPhonesTable') && document.getElementById('stockNewPhonesPagination')) {
                window.stockNewPager = window.setupMobiTablePagination({
                    tableId: 'stockNewPhonesTable',
                    paginationContainerId: 'stockNewPhonesPagination',
                    rowSelector: 'tbody tr.stock-row',
                    pageSize: 25,
                    itemName: 'new phones'
                });
            }
            if (document.getElementById('stockSecondHandTable') && document.getElementById('stockSecondHandPagination')) {
                window.stockShPager = window.setupMobiTablePagination({
                    tableId: 'stockSecondHandTable',
                    paginationContainerId: 'stockSecondHandPagination',
                    rowSelector: 'tbody tr.stock-row',
                    pageSize: 25,
                    itemName: 'pre-owned phones'
                });
            }
            if (document.getElementById('stockPartsTable') && document.getElementById('stockPartsPagination')) {
                window.stockPartsPager = window.setupMobiTablePagination({
                    tableId: 'stockPartsTable',
                    paginationContainerId: 'stockPartsPagination',
                    rowSelector: 'tbody tr.stock-row',
                    pageSize: 25,
                    itemName: 'parts & accessories'
                });
            }
        }
        filterStockRows();

        // Auto-switch tab if ?tab= is passed in URL (e.g. from redirect)
        const urlParams = new URLSearchParams(window.location.search);
        const tabParam = urlParams.get('tab');
        if (tabParam && ['new_phones', 'second_hand', 'parts', 'low'].includes(tabParam)) {
            switchStockTab(tabParam);
        }

        if (window.refreshIcons) window.refreshIcons();
        else if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initStockPage);
    } else {
        initStockPage();
    }

    function downloadLowStockCSV() {
        const rows = document.querySelectorAll('.stock-row[data-low="1"]');
        if (!rows || rows.length === 0) {
            alert('No low stock items currently detected.');
            return;
        }

        let csvContent = "Item Name,Category,Compatible Model,Current Stock,Min Alert Level,Unit Cost (INR),Retail Price (INR),Recommended Order Qty\n";

        rows.forEach(r => {
            const name = `"${(r.dataset.name || '').replace(/"/g, '""')}"`;
            const cat = `"${(r.dataset.category || '').replace(/"/g, '""')}"`;
            const model = `"${(r.dataset.model || 'Universal').replace(/"/g, '""')}"`;
            const stock = parseInt(r.dataset.stock || 0);
            const alertLvl = parseInt(r.dataset.alert || 3);
            const cost = parseFloat(r.dataset.cost || 0).toFixed(2);
            const price = parseFloat(r.dataset.price || 0).toFixed(2);
            const needed = Math.max(5, (alertLvl * 3) - stock);

            csvContent += `${name},${cat},${model},${stock},${alertLvl},${cost},${price},${needed}\n`;
        });

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement("a");
        const today = new Date().toISOString().slice(0, 10);
        link.setAttribute("href", url);
        link.setAttribute("download", `mauryamobile_low_stock_report_${today}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    }

    // ══════════════════════════════════════════════════════
    // 3-DOT CONTEXT ACTION MENU & MODALS
    // ══════════════════════════════════════════════════════
    let currentStockActionItem = null;

    function openStockActionMenu(event, item) {
        event.preventDefault();
        event.stopPropagation();
        currentStockActionItem = item;

        const menu = document.getElementById('stockFloatingMenu');
        if (!menu) return;

        // Set title and delete button state
        const titleEl = document.getElementById('menuItemTitle');
        if (titleEl) {
            titleEl.textContent = item.name.length > 22 ? item.name.substring(0, 20) + '...' : item.name;
        }

        const delBtn = document.getElementById('menuDeleteBtn');
        const delText = document.getElementById('menuDeleteBtnText');
        if (delBtn && delText) {
            if (item.is_sold) {
                delBtn.disabled = true;
                delBtn.style.opacity = '0.4';
                delBtn.style.cursor = 'not-allowed';
                delText.textContent = 'Already Sold';
            } else if (item.stock <= 0 && !item.is_phone) {
                delBtn.disabled = true;
                delBtn.style.opacity = '0.4';
                delBtn.style.cursor = 'not-allowed';
                delText.textContent = 'Out of Stock';
            } else {
                delBtn.disabled = false;
                delBtn.style.opacity = '1';
                delBtn.style.cursor = 'pointer';
                delText.textContent = item.is_phone ? 'Delete Device' : 'Delete / Reduce Stock';
            }
        }

        // Position menu near button
        const btnRect = event.currentTarget.getBoundingClientRect();
        const menuWidth = 210;
        const menuHeight = 110;

        let left = btnRect.right - menuWidth;
        let top = btnRect.bottom + 6;

        if (top + menuHeight > window.innerHeight) {
            top = Math.max(10, btnRect.top - menuHeight - 6);
        }
        if (left < 10) {
            left = 10;
        }

        menu.style.left = left + 'px';
        menu.style.top = top + 'px';
        menu.style.display = 'block';

        if (window.lucide) window.lucide.createIcons();
    }

    function closeStockActionMenu() {
        const menu = document.getElementById('stockFloatingMenu');
        if (menu) menu.style.display = 'none';
    }

    document.addEventListener('click', function(e) {
        const menu = document.getElementById('stockFloatingMenu');
        if (menu && menu.style.display === 'block') {
            if (!menu.contains(e.target) && !e.target.closest('.stock-action-trigger-btn')) {
                closeStockActionMenu();
            }
        }
    });

    window.addEventListener('scroll', closeStockActionMenu, true);
    window.addEventListener('resize', closeStockActionMenu);

    function triggerStockHistory() {
        closeStockActionMenu();
        if (currentStockActionItem) {
            openStockHistoryModal(currentStockActionItem);
        }
    }

    function triggerStockDelete() {
        closeStockActionMenu();
        if (currentStockActionItem) {
            openDeleteStockModal(currentStockActionItem);
        }
    }

    // ── STOCK HISTORY MODAL ──
    function openStockHistoryModal(item) {
        const modal = document.getElementById('stockHistoryModal');
        if (!modal) return;

        document.getElementById('historyModalTitle').textContent = `Audit Trail — ${item.name}`;
        document.getElementById('historyModalSubtitle').textContent = item.subtext || 'Chronological inflow & outflow records';

        const loadingEl = document.getElementById('historyLoadingState');
        const emptyEl = document.getElementById('historyEmptyState');
        const container = document.getElementById('historyTimelineContainer');

        loadingEl.style.display = 'block';
        emptyEl.style.display = 'none';
        container.style.display = 'none';
        container.innerHTML = '';

        modal.style.display = 'flex';
        const historyUrlTemplate = "{{ route('mobileshop.stock.history', ['type' => 'ITEM_TYPE', 'id' => 'ITEM_ID'], false) }}";
        const historyUrl = historyUrlTemplate.replace('ITEM_TYPE', encodeURIComponent(item.type)).replace('ITEM_ID', encodeURIComponent(item.id));

        fetch(historyUrl, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            loadingEl.style.display = 'none';

            if (!data.success) {
                emptyEl.style.display = 'block';
                emptyEl.querySelector('div').textContent = data.message || 'Failed to load history';
                return;
            }

            // Populate summary KPI
            if (data.item) {
                document.getElementById('histKpiStock').textContent = data.item.current_stock || (data.item.stock_qty + ' units');
                document.getElementById('histKpiCost').textContent = data.item.unit_cost || '—';
                document.getElementById('histKpiPrice').textContent = data.item.selling_price || '—';
                
                const st = data.item.status;
                let stBadge = '<span class="badge badge-green">In Stock</span>';
                if (st === 'sold') stBadge = '<span class="badge badge-gray">Sold Out</span>';
                else if (st === 'deleted') stBadge = '<span class="badge badge-red">Deleted</span>';
                document.getElementById('histKpiStatus').innerHTML = stBadge;
            }

            if (!data.history || data.history.length === 0) {
                emptyEl.style.display = 'block';
                return;
            }

            // Render timeline
            let html = '';
            data.history.forEach(evt => {
                const isSold = evt.action === 'sold';
                const isDel = evt.action === 'deleted';

                let nodeClass = 'node-added';
                let iconName = 'package-plus';
                let qtyColor = '#16A34A';

                if (isSold) {
                    nodeClass = 'node-sold';
                    iconName = 'shopping-bag';
                    qtyColor = '#2563EB';
                } else if (isDel) {
                    nodeClass = 'node-deleted';
                    iconName = 'trash-2';
                    qtyColor = '#DC2626';
                }

                html += `
                    <div class="stock-timeline-item">
                        <div class="stock-timeline-node ${nodeClass}">
                            <i data-lucide="${iconName}" style="width:13px;height:13px;"></i>
                        </div>
                        <div class="stock-timeline-card">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:6px;">
                                <div>
                                    <span class="badge ${evt.badge_class || 'badge-gray'}" style="font-size:11px; font-weight:700;">
                                        ${evt.action_label}
                                    </span>
                                    <span style="font-weight:900; font-size:13px; color:${qtyColor}; margin-left:8px;">
                                        ${evt.quantity}
                                    </span>
                                </div>
                                <div style="font-size:11px; font-weight:600; color:#64748B;">
                                    🕒 ${evt.date} <span style="color:#94A3B8;">(${evt.relative_time})</span>
                                </div>
                            </div>

                            <div style="margin-top:8px; font-size:12px; color:#334155; line-height:1.5;">
                                ${evt.reference ? `<div style="margin-bottom:3px;"><strong>Details:</strong> ${evt.reference}</div>` : ''}
                                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; margin-top:6px; font-size:11px; color:#64748B; border-top:1px dashed #E2E8F0; padding-top:6px;">
                                    <span>👤 <strong>Logged by:</strong> <span style="color:#0F172A; font-weight:700;">${evt.user_name}</span></span>
                                    <span>Balance after: <strong style="color:#0F172A;">${evt.balance_after}</strong></span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
            container.style.display = 'block';
            if (window.lucide) window.lucide.createIcons();
        })
        .catch(err => {
            loadingEl.style.display = 'none';
            emptyEl.style.display = 'block';
            emptyEl.querySelector('div').textContent = 'Failed to load history audit log: ' + err.message;
        });
    }

    function closeStockHistoryModal() {
        const modal = document.getElementById('stockHistoryModal');
        if (modal) modal.style.display = 'none';
    }

    // ── DELETE STOCK MODAL ──
    function openDeleteStockModal(item) {
        const modal = document.getElementById('deleteStockModal');
        if (!modal) return;

        document.getElementById('deleteItemType').value = item.type;
        document.getElementById('deleteItemId').value = item.id;
        document.getElementById('deleteItemName').textContent = item.name;
        document.getElementById('deleteItemSubtext').textContent = item.subtext || '';
        document.getElementById('deleteItemNotes').value = '';
        document.getElementById('deleteReasonSelect').value = '';

        const errorBox = document.getElementById('deleteModalError');
        if (errorBox) {
            errorBox.style.display = 'none';
            errorBox.textContent = '';
        }

        const qtyGroup = document.getElementById('deleteQtyGroup');
        const phoneNotice = document.getElementById('deletePhoneNotice');
        const qtyInput = document.getElementById('deleteItemQty');
        const stockInfo = document.getElementById('deleteItemStockInfo');

        if (item.is_phone) {
            qtyGroup.style.display = 'none';
            phoneNotice.style.display = 'block';
            qtyInput.value = 1;
            qtyInput.max = 1;
            stockInfo.textContent = 'Device Status: ' + (item.stock > 0 ? 'In Stock (1 unit)' : 'Unavailable');
        } else {
            qtyGroup.style.display = 'block';
            phoneNotice.style.display = 'none';
            qtyInput.value = 1;
            qtyInput.max = Math.max(1, item.stock);
            stockInfo.textContent = `Available Stock: ${item.stock} units`;
            document.getElementById('deleteQtyHint').textContent = `Specify units to write off (Max: ${item.stock} units).`;
        }

        modal.style.display = 'flex';
        if (window.lucide) window.lucide.createIcons();
    }

    function closeDeleteStockModal() {
        const modal = document.getElementById('deleteStockModal');
        if (modal) modal.style.display = 'none';
    }

    // ── ADD MOBILE, BUYBACK, AND PART MODAL HANDLERS ──
    function openAddMobileModal() {
        const modal = document.getElementById('addMobileModal');
        if (modal) { modal.style.display = 'flex'; if (window.lucide) window.lucide.createIcons(); }
    }
    function closeAddMobileModal() {
        const modal = document.getElementById('addMobileModal');
        if (modal) modal.style.display = 'none';
    }

    function openBuybackModal() {
        const modal = document.getElementById('buybackModal');
        if (modal) { modal.style.display = 'flex'; if (window.lucide) window.lucide.createIcons(); }
    }
    function closeBuybackModal() {
        const modal = document.getElementById('buybackModal');
        if (modal) modal.style.display = 'none';
    }

    function openAddPartModal() {
        const modal = document.getElementById('addPartModal');
        if (modal) { modal.style.display = 'flex'; if (window.lucide) window.lucide.createIcons(); }
    }
    function closeAddPartModal() {
        const modal = document.getElementById('addPartModal');
        if (modal) modal.style.display = 'none';
    }

    function openStockQuickAddDrawer() {
        const drawer = document.getElementById('stockQuickAddDrawer');
        if (drawer) { drawer.style.display = 'flex'; if (window.lucide) window.lucide.createIcons(); }
    }
    function closeStockQuickAddDrawer() {
        const drawer = document.getElementById('stockQuickAddDrawer');
        if (drawer) drawer.style.display = 'none';
    }

    function previewSelectedPhoto(input, imgId, boxId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById(imgId);
                const box = document.getElementById(boxId);
                if (img) img.src = e.target.result;
                if (box) box.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function adjustDeleteQty(delta) {
        const qtyInput = document.getElementById('deleteItemQty');
        if (!qtyInput) return;
        const max = parseInt(qtyInput.max) || 9999;
        const current = parseInt(qtyInput.value) || 1;
        const next = Math.max(1, Math.min(max, current + delta));
        qtyInput.value = next;
    }

    function setDeleteAllQty() {
        const qtyInput = document.getElementById('deleteItemQty');
        if (!qtyInput) return;
        qtyInput.value = qtyInput.max || 1;
    }

    function requestDeleteStockOtp() {
        const btn = document.getElementById('btnSendDeleteOtp');
        const statusText = document.getElementById('deleteOtpStatusText');
        const type = document.getElementById('deleteItemType')?.value;
        const id = document.getElementById('deleteItemId')?.value;

        if (!btn || !statusText) return;
        btn.disabled = true;
        btn.textContent = 'Sending...';

        fetch("{{ route('mobileshop.otp.request') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                action: 'delete_stock',
                item_reference: `${type}:${id}`
            })
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.textContent = 'Resend OTP';
            if (data.is_owner) {
                statusText.innerHTML = '<span style="color:#16A34A;font-weight:700;">✓ You have owner privileges! No OTP required.</span>';
            } else if (data.success) {
                statusText.innerHTML = `<span style="color:#16A34A;font-weight:700;">✓ OTP sent to ${data.target_email}. Ask store owner for code.</span>`;
                const otpIn = document.getElementById('deleteStockOtpCode');
                if (otpIn) {
                    otpIn.focus();
                    otpIn.style.borderColor = '#2563EB';
                }
            } else {
                statusText.innerHTML = `<span style="color:#DC2626;">✕ ${data.message}</span>`;
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.textContent = 'Retry Send';
            statusText.innerHTML = `<span style="color:#DC2626;">✕ Error sending OTP. Please try again.</span>`;
        });
    }

    function submitStockDelete(e) {
        e.preventDefault();
        const btn = document.getElementById('btnConfirmDeleteStock');
        const btnText = document.getElementById('btnConfirmDeleteStockText');
        const errorBox = document.getElementById('deleteModalError');
        errorBox.style.display = 'none';

        btn.disabled = true;
        btnText.textContent = 'Deleting...';

        const form = document.getElementById('deleteStockForm');
        const formData = new FormData(form);

        fetch("{{ route('mobileshop.stock.delete') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btnText.textContent = 'Confirm Deletion';

            if (!data.success) {
                errorBox.style.display = 'block';
                errorBox.textContent = data.message || 'Error processing deletion.';
                if (data.otp_required) {
                    const otpIn = document.getElementById('deleteStockOtpCode');
                    if (otpIn) {
                        otpIn.focus();
                        otpIn.style.borderColor = '#DC2626';
                    }
                }
                return;
            }

            // Success
            closeDeleteStockModal();
            if (window.showToast) {
                window.showToast(data.message, 'success');
            } else {
                alert(data.message);
            }

            const type = data.item_type;
            const id = data.item_id;

            if (type === 'part') {
                const newStock = data.new_stock;
                // Update desktop row
                const dRow = document.getElementById(`stockRow_part_${id}`);
                if (dRow) {
                    dRow.dataset.stock = newStock;
                    const alertLvl = parseInt(dRow.dataset.alert || 3);
                    dRow.dataset.low = (newStock <= alertLvl) ? '1' : '0';

                    const cell = document.getElementById(`partStockCell_${id}`);
                    if (cell) {
                        if (newStock <= 0) {
                            cell.innerHTML = '<span class="badge badge-gray">0 units</span>';
                        } else if (newStock <= alertLvl) {
                            cell.innerHTML = `<span class="badge badge-red" style="font-weight:900;">⚠️ ${newStock} units</span>`;
                        } else {
                            cell.innerHTML = `<span class="badge badge-green">${newStock} units</span>`;
                        }
                    }
                }

                // Update mobile card
                const mBadge = document.getElementById(`partStockBadgeMobile_${id}`);
                if (mBadge) {
                    if (newStock <= 0) {
                        mBadge.innerHTML = '<span class="badge badge-gray" style="font-size:10px; padding:1px 6px;">0 left</span>';
                    } else if (newStock <= 3) {
                        mBadge.innerHTML = `<span class="badge badge-red" style="font-size:10px; padding:1px 6px;">⚠️ ${newStock} left</span>`;
                    } else {
                        mBadge.innerHTML = `<span class="badge badge-green" style="font-size:10px; padding:1px 6px;">${newStock} in stock</span>`;
                    }
                }
            } else {
                // Phone deleted
                const rowId = (type === 'new_phone') ? `stockRow_new_phone_${id}` : `stockRow_second_hand_${id}`;
                const cardId = (type === 'new_phone') ? `stockCard_new_phone_${id}` : `stockCard_second_hand_${id}`;

                const row = document.getElementById(rowId);
                if (row) {
                    row.style.transition = 'all 0.3s ease';
                    row.style.opacity = '0';
                    setTimeout(() => row.remove(), 300);
                }

                const card = document.getElementById(cardId);
                if (card) {
                    card.style.transition = 'all 0.3s ease';
                    card.style.opacity = '0';
                    setTimeout(() => card.remove(), 300);
                }
            }

            filterStockRows();
        })
        .catch(err => {
            btn.disabled = false;
            btnText.textContent = 'Confirm Deletion';
            errorBox.style.display = 'block';
            errorBox.textContent = 'Network or server error: ' + err.message;
        });
    }
</script>
@endpush
