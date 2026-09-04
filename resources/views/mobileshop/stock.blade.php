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

@section('title', $stockPageTitle . ' — MobiTrack ERP')
@section('page-title', $stockPageTitle)

@section('page-actions')
    <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        @if($canManagePhones ?? false)
        <a href="{{ route('mobileshop.new_mobiles') }}" class="btn btn-primary btn-sm" style="background:#2563EB;">
            <i data-lucide="plus" style="width:14px;height:14px;"></i> Add New Phone
        </a>
        @endif
        @if($canManageSecondhand ?? false)
        <a href="{{ route('mobileshop.second_hand') }}" class="btn btn-primary btn-sm" style="background:#EA580C;">
            <i data-lucide="plus" style="width:14px;height:14px;"></i> Intake Pre-Owned
        </a>
        @endif
        @if(($canManageAccessories ?? false) || ($canManageCovers ?? false))
        <a href="{{ route('mobileshop.purchase') }}" class="btn btn-primary btn-sm" style="background:#16A34A;">
            <i data-lucide="plus" style="width:14px;height:14px;"></i> Add Part / Accessory
        </a>
        @endif
        @if($canManageRepairs ?? false)
        <a href="{{ route('mobileshop.repairs') }}" class="btn btn-primary btn-sm" style="background:#D97706;">
            <i data-lucide="wrench" style="width:14px;height:14px;"></i> New Job Sheet
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
    <div id="stockDesktopKpiGrid" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:16px; margin-bottom:24px;">
        @if(($canManagePhones ?? false) || in_array($niche ?? '', ['admin', 'phones']))
        <div class="card kpi-card kpi-info" style="margin:0;">
            <div class="card-body" style="padding:16px;">
                <div style="font-size:11px; font-weight:700; color:var(--color-text-secondary); text-transform:uppercase;">Brand New Phones in Stock</div>
                <div style="font-size:24px; font-weight:900; color:var(--color-info); margin-top:4px;">{{ $totalNewPhonesInStock }} Units</div>
                <div style="font-size:11px; color:var(--color-text-muted); margin-top:2px;">Sealed boxed units with IMEI serials</div>
            </div>
        </div>
        @endif

        @if(($canManageSecondhand ?? false) || in_array($niche ?? '', ['admin', 'secondhand']))
        <div class="card kpi-card kpi-warning" style="margin:0;">
            <div class="card-body" style="padding:16px;">
                <div style="font-size:11px; font-weight:700; color:var(--color-text-secondary); text-transform:uppercase;">Pre-Owned Phones in Stock</div>
                <div style="font-size:24px; font-weight:900; color:var(--color-warning); margin-top:4px;">{{ $totalSecondHandInStock }} Units</div>
                <div style="font-size:11px; color:var(--color-text-muted); margin-top:2px;">Certified refurbished devices</div>
            </div>
        </div>
        @endif

        @if(($canManageAccessories ?? false) || ($canManageCovers ?? false) || in_array($niche ?? '', ['admin', 'accessories', 'covers']))
        <div class="card kpi-card kpi-success" style="margin:0;">
            <div class="card-body" style="padding:16px;">
                <div style="font-size:11px; font-weight:700; color:var(--color-text-secondary); text-transform:uppercase;">Parts & Accessories Stock</div>
                <div style="font-size:24px; font-weight:900; color:var(--color-success); margin-top:4px;">{{ number_format($totalPartsInStock) }} Units</div>
                <div style="font-size:11px; color:var(--color-text-muted); margin-top:2px;">Displays, tempered glass, cables & ICs</div>
            </div>
        </div>
        @endif

        <div class="card kpi-card kpi-primary" style="margin:0;">
            <div class="card-body" style="padding:16px;">
                <div style="font-size:11px; font-weight:700; color:var(--color-text-secondary); text-transform:uppercase;">Total Stock Valuation</div>
                <div style="font-size:22px; font-weight:900; color:var(--color-text-primary); margin-top:4px;">₹{{ number_format($valuationRetail, 2) }}</div>
                <div style="font-size:11px; color:var(--color-text-muted); margin-top:2px;">Purchase Cost: ₹{{ number_format($valuationCost, 2) }}</div>
            </div>
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
    <div style="margin-bottom: 16px; background:#fff; border-radius:10px; border:1px solid #E2E8F0; padding:8px 14px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
        <div style="display:flex; gap:5px; align-items:center; flex-wrap:wrap;">
            <span style="font-size:11px; font-weight:800; color:#475569; text-transform:uppercase; margin-right:4px;">Stock Inflow:</span>
            <button type="button" onclick="setStockDatePreset('all')" id="stockDateBtn_all" class="filter-pill stock-date-pill active" style="padding:3px 9px; font-size:11px; font-weight:700; border:none; cursor:pointer;">All Time</button>
            <button type="button" onclick="setStockDatePreset('today')" id="stockDateBtn_today" class="filter-pill stock-date-pill" style="padding:3px 9px; font-size:11px; font-weight:700; border:none; cursor:pointer;">Today</button>
            <button type="button" onclick="setStockDatePreset('yesterday')" id="stockDateBtn_yesterday" class="filter-pill stock-date-pill" style="padding:3px 9px; font-size:11px; font-weight:700; border:none; cursor:pointer;">Yesterday</button>
            <button type="button" onclick="setStockDatePreset('week')" id="stockDateBtn_week" class="filter-pill stock-date-pill" style="padding:3px 9px; font-size:11px; font-weight:700; border:none; cursor:pointer;">Last 7 Days</button>
            <button type="button" onclick="setStockDatePreset('month')" id="stockDateBtn_month" class="filter-pill stock-date-pill" style="padding:3px 9px; font-size:11px; font-weight:700; border:none; cursor:pointer;">This Month</button>
        </div>

        <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
            <div style="display:flex; align-items:center; gap:4px;">
                <label for="stockFromDate" style="font-size:11px; font-weight:700; color:#64748B; margin:0;">From:</label>
                <input type="date" id="stockFromDate" onchange="onStockCustomDateChange()" class="form-control" style="font-size:11px; padding:3px 6px; height:auto; width:auto; font-weight:600; color:#0F172A;">
            </div>
            <div style="display:flex; align-items:center; gap:4px;">
                <label for="stockToDate" style="font-size:11px; font-weight:700; color:#64748B; margin:0;">To:</label>
                <input type="date" id="stockToDate" onchange="onStockCustomDateChange()" class="form-control" style="font-size:11px; padding:3px 6px; height:auto; width:auto; font-weight:600; color:#0F172A;">
            </div>
            <button type="button" onclick="setStockDatePreset('all')" title="Reset Date Filter" style="background:#F1F5F9; border:1px solid #CBD5E1; color:#64748B; border-radius:6px; padding:3px 6px; font-size:11px; font-weight:700; cursor:pointer;">Reset</button>
        </div>
    </div>

    <div id="stockDesktopTables">
        @if(($canManagePhones ?? false) || in_array($niche ?? '', ['admin', 'phones']))
        <!-- ════ TAB 1: BRAND NEW PHONES ════ -->
        <div id="stockSection_new_phones" class="stock-section card" style="margin-bottom:24px;">
            <div class="card-header" style="background:#F8FAFC; border-bottom:1px solid #E2E8F0;">
                <div>
                    <div class="card-title" style="color:var(--color-text-primary);">Brand New Smartphones</div>
                    <div class="card-subtitle">Boxed stock with individual IMEI 1 & IMEI 2 tracking</div>
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
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($newPhones as $phone)
                        <tr class="stock-row" data-type="new_phone" data-low="0" data-date="{{ \Carbon\Carbon::parse($phone->created_at)->format('Y-m-d') }}">
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
                            <td style="text-align:center;">
                                @if($phone->status === 'in_stock')
                                    <span class="badge badge-green">In Stock</span>
                                @else
                                    <span class="badge badge-gray">Sold Out</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="text-align:center; padding:24px; color:#94A3B8;">No new mobile phones registered.</td>
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
        <div id="stockSection_second_hand" class="stock-section card" style="margin-bottom:24px;">
            <div class="card-header" style="background:#F8FAFC; border-bottom:1px solid #E2E8F0;">
                <div>
                    <div class="card-title" style="color:var(--color-text-primary);">Pre-Owned & Second Hand Hub</div>
                    <div class="card-subtitle">Verified devices with condition grading and battery health</div>
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
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($secondHandPhones as $sh)
                        <tr class="stock-row" data-type="second_hand" data-low="0" data-date="{{ \Carbon\Carbon::parse($sh->created_at)->format('Y-m-d') }}">
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
                            <td style="text-align:center;">
                                @if($sh->status === 'in_stock')
                                    <span class="badge badge-green">In Stock</span>
                                @else
                                    <span class="badge badge-gray">Sold Out</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="text-align:center; padding:24px; color:#94A3B8;">No pre-owned phones in stock.</td>
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
                    <div class="card-subtitle">Displays, Folders, Front Glass, Charging Pins, ICs & Covers</div>
                </div>
            </div>
            <div class="card-body" style="padding:0; overflow-x:auto;">
                <table class="data-table" id="stockPartsTable">
                    <thead>
                        <tr>
                            <th>Part Name & SKU</th>
                            <th>Category</th>
                            <th>Compatible Model</th>
                            <th style="text-align:center;">In-Stock Qty</th>
                            <th style="text-align:right;">Unit Cost (₹)</th>
                            <th style="text-align:right;">Retail Price (₹)</th>
                            <th style="text-align:center;">Gift Eligible</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($parts as $part)
                        @php $isLow = $part->stock_qty <= ($part->min_stock_alert ?? 3); @endphp
                        <tr class="stock-row" data-type="part" data-low="{{ $isLow ? '1' : '0' }}" data-date="{{ \Carbon\Carbon::parse($part->created_at)->format('Y-m-d') }}" data-name="{{ $part->name }}" data-category="{{ $part->category }}" data-model="{{ $part->compatible_model ?: 'Universal' }}" data-stock="{{ $part->stock_qty }}" data-cost="{{ $part->unit_cost }}" data-price="{{ $part->selling_price }}" data-alert="{{ $part->min_stock_alert ?? 3 }}">
                            <td>
                                <div style="font-weight:800; color:#0F172A; font-size:13px;">{{ $part->name }}</div>
                                <div style="font-size:10px; color:#64748B;">HSN: {{ $part->hsn_code ?: '85177090' }}</div>
                            </td>
                            <td>
                                <span class="badge badge-gray" style="font-size:10px; text-transform:capitalize;">
                                    {{ str_replace('_', ' ', $part->category) }}
                                </span>
                            </td>
                            <td style="font-size:12px; color:#475569;">{{ $part->compatible_model ?: 'Universal' }}</td>
                            <td style="text-align:center;">
                                @if($isLow)
                                    <span class="badge badge-red" style="font-weight:900;">⚠️ {{ $part->stock_qty }} units</span>
                                @else
                                    <span class="badge badge-green">{{ $part->stock_qty }} units</span>
                                @endif
                            </td>
                            <td style="text-align:right; color:#64748B;">₹{{ number_format($part->unit_cost, 2) }}</td>
                            <td style="text-align:right; font-weight:900; color:#0F172A;">₹{{ number_format($part->selling_price, 2) }}</td>
                            <td style="text-align:center;">
                                @if($part->is_gift_eligible)
                                    <span class="badge badge-purple" style="font-size:10px;">🎁 Free Gift</span>
                                @else
                                    <span style="color:#CBD5E1; font-size:11px;">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="text-align:center; padding:24px; color:#94A3B8;">No parts or accessories in catalog.</td>
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
            <div class="app-flat-row stock-card" data-type="new_phone" data-low="0" data-date="{{ \Carbon\Carbon::parse($phone->created_at)->format('Y-m-d') }}" data-search="{{ strtolower(($phone->brand ?? '') . ' ' . ($phone->model ?? '') . ' ' . ($phone->imei_1 ?? '') . ' ' . ($phone->color ?? '')) }}">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; width:100%;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <div style="width:36px; height:36px; border-radius:8px; background:#EFF6FF; color:#2563EB; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            <i data-lucide="smartphone" style="width:18px;height:18px;"></i>
                        </div>
                        <div>
                            <div style="font-weight:800; font-size:13px; color:#0F172A;">{{ $phone->brand }} {{ $phone->model }}</div>
                            <div style="font-size:10px; color:#64748B;">IMEI: <span style="font-family:monospace; font-weight:700;">{{ $phone->imei_1 }}</span> • {{ $phone->ram ?: '' }}/{{ $phone->storage ?: '' }}</div>
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-weight:900; font-size:14px; color:#0F172A;">₹{{ number_format($phone->selling_price, 0) }}</div>
                        <span class="badge {{ $phone->status === 'in_stock' ? 'badge-green' : 'badge-gray' }}" style="font-size:10px; padding:1px 6px;">
                            {{ $phone->status === 'in_stock' ? 'In Stock' : 'Sold Out' }}
                        </span>
                    </div>
                </div>
            </div>
            @endforeach
        @endif

        @if(($canManageSecondhand ?? false) || in_array($niche ?? '', ['admin', 'secondhand']))
            @foreach($secondHandPhones as $sh)
            <div class="app-flat-row stock-card" data-type="second_hand" data-low="0" data-date="{{ \Carbon\Carbon::parse($sh->created_at)->format('Y-m-d') }}" data-search="{{ strtolower(($sh->brand ?? '') . ' ' . ($sh->model ?? '') . ' ' . ($sh->imei_1 ?? '')) }}">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; width:100%;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <div style="width:36px; height:36px; border-radius:8px; background:#FFF7ED; color:#EA580C; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            <i data-lucide="refresh-cw" style="width:18px;height:18px;"></i>
                        </div>
                        <div>
                            <div style="font-weight:800; font-size:13px; color:#0F172A;">{{ $sh->brand }} {{ $sh->model }}</div>
                            <div style="font-size:10px; color:#64748B;">IMEI: <span style="font-family:monospace; font-weight:700;">{{ $sh->imei_1 }}</span> @if($sh->battery_health) • 🔋{{ $sh->battery_health }}% @endif</div>
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-weight:900; font-size:14px; color:#0F172A;">₹{{ number_format($sh->selling_price, 0) }}</div>
                        <span class="badge badge-orange" style="font-size:10px; padding:1px 6px;">
                            {{ str_replace('_', ' ', $sh->condition_grade) }}
                        </span>
                    </div>
                </div>
            </div>
            @endforeach
        @endif

        @if(($canManageAccessories ?? false) || ($canManageCovers ?? false) || in_array($niche ?? '', ['admin', 'accessories', 'covers']))
            @foreach($parts as $part)
            @php $isLow = $part->stock_qty <= ($part->min_stock_alert ?? 3); @endphp
            <div class="app-flat-row stock-card" data-type="part" data-low="{{ $isLow ? '1' : '0' }}" data-date="{{ \Carbon\Carbon::parse($part->created_at)->format('Y-m-d') }}" data-search="{{ strtolower(($part->name ?? '') . ' ' . ($part->category ?? '') . ' ' . ($part->compatible_model ?? '')) }}">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; width:100%;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <div style="width:36px; height:36px; border-radius:8px; background:#F0FDF4; color:#16A34A; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            <i data-lucide="package" style="width:18px;height:18px;"></i>
                        </div>
                        <div>
                            <div style="font-weight:800; font-size:13px; color:#0F172A;">{{ $part->name }}</div>
                            <div style="font-size:10px; color:#64748B;">{{ $part->compatible_model ?: 'Universal' }} • {{ str_replace('_', ' ', $part->category) }}</div>
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-weight:900; font-size:14px; color:#0F172A;">₹{{ number_format($part->selling_price, 0) }}</div>
                        @if($isLow)
                            <span class="badge badge-red" style="font-size:10px; padding:1px 6px;">⚠️ {{ $part->stock_qty }} left</span>
                        @else
                            <span class="badge badge-green" style="font-size:10px; padding:1px 6px;">{{ $part->stock_qty }} in stock</span>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        @endif
    </div>

    <!-- Mobile Floating Action Button -->
    <div class="mobile-fab-container">
        <a href="{{ route('mobileshop.new_mobiles') }}" class="btn-app-fab" title="Manage Stock Hub" style="background:#2563EB; text-decoration:none;">
            <i data-lucide="plus" style="width:20px;height:20px;"></i>
            <span>Add Stock</span>
        </a>
    </div>

@endsection

@push('scripts')
<style>
@media (max-width: 768px) {
    #stockDesktopKpiGrid { display: none !important; }
    #stockDesktopTables { display: none !important; }
    #stockMobileCards { display: flex !important; }
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

    function setStockDatePreset(preset) {
        currentStockDatePreset = preset;
        const fromInput = document.getElementById('stockFromDate');
        const toInput = document.getElementById('stockToDate');

        document.querySelectorAll('.stock-date-pill').forEach(el => el.classList.remove('active'));
        const btn = document.getElementById('stockDateBtn_' + preset);
        if (btn) btn.classList.add('active');

        const range = window.getDateRangePreset(preset);
        fromInput.value = range.from;
        toInput.value = range.to;

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
            const rowDate = row.dataset.date || '';
            const isLow = row.dataset.low === '1';

            const matchesText = !term || rowText.includes(term);
            let matchesDate = true;
            if (fromDate && rowDate) matchesDate = matchesDate && (rowDate >= fromDate);
            if (toDate && rowDate) matchesDate = matchesDate && (rowDate <= toDate);
            let matchesLow = (currentStockTab !== 'low') || isLow;

            row.dataset.mobiHidden = (matchesText && matchesDate && matchesLow) ? '0' : '1';
        });

        // Mobile Cards
        document.querySelectorAll('#stockMobileCards .stock-card').forEach(card => {
            const cardText = (card.dataset.search || card.textContent).toLowerCase();
            const cardDate = card.dataset.date || '';
            const cardType = card.dataset.type || '';
            const isLow = card.dataset.low === '1';

            const matchesText = !term || cardText.includes(term);
            let matchesDate = true;
            if (fromDate && cardDate) matchesDate = matchesDate && (cardDate >= fromDate);
            if (toDate && cardDate) matchesDate = matchesDate && (cardDate <= toDate);

            let matchesTab = true;
            if (currentStockTab === 'new_phones') matchesTab = (cardType === 'new_phone');
            else if (currentStockTab === 'second_hand') matchesTab = (cardType === 'second_hand');
            else if (currentStockTab === 'parts') matchesTab = (cardType === 'part');
            else if (currentStockTab === 'low') matchesTab = isLow;

            card.dataset.mobiHidden = (matchesText && matchesDate && matchesTab) ? '0' : '1';
        });

        if (window.stockNewPager) window.stockNewPager.refresh();
        if (window.stockShPager) window.stockShPager.refresh();
        if (window.stockPartsPager) window.stockPartsPager.refresh();
    }

    document.addEventListener('DOMContentLoaded', function() {
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
        if (window.lucide) window.lucide.createIcons();
    });

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
        link.setAttribute("download", `mobitrack_low_stock_report_${today}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    }
</script>
@endpush
