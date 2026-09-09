@extends('mobileshop.layout')

@section('title', 'Second Hand Hub & POS — Maurya Mobile')
@section('page-title', 'Second Hand Mobiles & POS Hub')

@section('page-actions')
    <div style="display:flex; gap:10px; align-items:center;">
        <button onclick="openSellModal()" class="btn btn-primary btn-sm" style="background:var(--lama-purple-dark); box-shadow:0 2px 6px rgba(109,40,217,0.25);">
            <i data-lucide="shopping-bag" style="width:14px;height:14px;"></i> Sell Pre-Owned Phone
        </button>
        <button onclick="openBuybackModal()" class="btn btn-outline btn-sm">
            <i data-lucide="plus" style="width:14px;height:14px;"></i> Register Buyback
        </button>
    </div>
@endsection

@section('content')

@php
    $inStockCount = $mobiles->where('status', 'in_stock')->count();
    $soldCount = $mobiles->where('status', 'sold')->count();
    $totalValuation = $mobiles->where('status', 'in_stock')->sum('selling_price');
    $batteryGoodCount = $mobiles->filter(fn($m) => (int)$m->battery_health >= 85)->count();
@endphp

<!-- Mobile Horizontal Stat Strip -->
<div class="mobile-stat-strip">
    <div class="stat-strip-item" onclick="applyShFilter('in_stock')">
        <span class="stat-label">In Stock</span>
        <span class="stat-val" style="color:var(--lama-purple-dark);">{{ $inStockCount }} Units</span>
    </div>
    <div class="stat-divider"></div>
    <div class="stat-strip-item" onclick="applyShFilter('sold')">
        <span class="stat-label">Sold Out</span>
        <span class="stat-val" style="color:#0284C7;">{{ $soldCount }}</span>
    </div>
    <div class="stat-divider"></div>
    <div class="stat-strip-item" onclick="applyShFilter('all')">
        <span class="stat-label">Valuation</span>
        <span class="stat-val" style="color:#16A34A;">₹{{ number_format($totalValuation, 0) }}</span>
    </div>
</div>

<!-- Desktop Stat Grid (Hidden on Mobile) -->
<div class="stat-grid" id="shDesktopStatGrid" style="margin-bottom: 12px;">
    <div class="stat-card stat-pastel-purple" style="cursor:pointer;" onclick="applyShFilter('all')">
        <div class="stat-card-top">
            <span class="stat-card-tag">Stock Count</span>
            <i data-lucide="refresh-cw" style="width:18px;height:18px;color:var(--lama-purple-dark);"></i>
        </div>
        <div class="stat-card-value">{{ $inStockCount }}</div>
        <div class="stat-card-label">Active Pre-Owned Stock</div>
    </div>
    <div class="stat-card stat-pastel-green">
        <div class="stat-card-top">
            <span class="stat-card-tag">Valuation</span>
            <i data-lucide="shield-check" style="width:18px;height:18px;color:var(--lama-green-dark);"></i>
        </div>
        <div class="stat-card-value">₹{{ number_format($totalValuation, 0) }}</div>
        <div class="stat-card-label">Holding Stock Value</div>
    </div>
    <div class="stat-card stat-pastel-sky" style="cursor:pointer;" onclick="applyShFilter('sold')">
        <div class="stat-card-top">
            <span class="stat-card-tag">Completed</span>
            <i data-lucide="shopping-bag" style="width:18px;height:18px;color:var(--lama-sky-dark);"></i>
        </div>
        <div class="stat-card-value">{{ $soldCount }}</div>
        <div class="stat-card-label">Sold Out to Customers</div>
    </div>
</div>

<!-- Main Inventory Card -->
<div class="card">
    <div class="card-header" style="border-bottom:1px solid var(--card-border); padding:16px 20px;">
        <div style="flex:1;">
            <div class="card-title">Certified Pre-Owned & Buyback Inventory</div>
        </div>
        <div style="display:flex; align-items:center; gap:10px; width:100%; max-width:340px;">
            <div class="search-bar" style="width:100%;">
                <i data-lucide="search" style="width:15px;height:15px;"></i>
                <input type="text" id="shSearchInput" placeholder="Search brand, model, IMEI..." oninput="onShSearch(this.value)">
            </div>
        </div>
    </div>

    <!-- Filter Bar Rail -->
    <div class="pills-scroll-rail" style="padding:10px 16px; border-bottom:1px solid var(--card-border); display:flex; align-items:center; gap:8px; overflow-x:auto;">
        <button type="button" class="filter-pill-btn active" id="pill-sh-all" onclick="applyShFilter('all', this)">
            <i data-lucide="layers" style="width:13px;height:13px;"></i> All Units ({{ $mobiles->count() }})
        </button>
        <button type="button" class="filter-pill-btn" id="pill-sh-stock" onclick="applyShFilter('in_stock', this)">
            <i data-lucide="check-circle" style="width:13px;height:13px;"></i> In Stock ({{ $inStockCount }})
        </button>
        <button type="button" class="filter-pill-btn" id="pill-sh-sold" onclick="applyShFilter('sold', this)">
            <i data-lucide="x-circle" style="width:13px;height:13px;"></i> Sold Out ({{ $soldCount }})
        </button>
        <button type="button" class="filter-pill-btn" id="pill-sh-battery" onclick="applyShFilter('battery_80', this)">
            <i data-lucide="battery-charging" style="width:13px;height:13px;"></i> Battery ≥ 85% ({{ $batteryGoodCount }})
        </button>

        <div style="margin-left:auto; display:flex; align-items:center; gap:6px; flex-shrink:0;">
            <span style="font-size:11px; font-weight:700; color:var(--text-secondary);">Grade:</span>
            <select id="shGradeSelect" class="form-control" style="padding:4px 8px; font-size:12px; font-weight:700; width:auto; border-radius:8px;" onchange="applyShGradeFilter(this.value)">
                <option value="all">-- All Grades --</option>
                <option value="like_new_A_plus">Grade A+ (Pristine)</option>
                <option value="good_A">Grade A (Minor Scratches)</option>
                <option value="fair_B">Grade B (Moderate Wear)</option>
            </select>
        </div>
    </div>

    <!-- Desktop Data Table View -->
    <div class="data-table-wrap" id="shDesktopTableWrap">
        <table class="data-table" id="secondHandTable">
            <thead>
                <tr>
                    <th style="width:50px; text-align:center;">Photo</th>
                    <th>Model & Spec</th>
                    <th>Condition Grade</th>
                    <th>Battery Health</th>
                    <th>IMEI</th>
                    <th>Customer Source</th>
                    <th style="text-align:right;">Buyback Cost</th>
                    <th style="text-align:right;">Selling Price</th>
                    <th style="text-align:center;">Status</th>
                    <th style="text-align:center;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mobiles as $m)
                    @php
                        $photo = $m->photo_path ?? $m->box_photo_path ?? null;
                    @endphp
                    <tr class="sh-row" data-status="{{ $m->status }}" data-grade="{{ $m->condition_grade }}" data-battery="{{ (int)$m->battery_health }}" data-brand="{{ strtolower($m->brand) }}" data-search="{{ strtolower(($m->brand ?? '') . ' ' . ($m->model ?? '') . ' ' . ($m->imei_1 ?? '') . ' ' . ($m->customer_buyback_name ?? '')) }}">
                        <td style="text-align:center;">
                            @if($photo)
                            <img src="{{ asset($photo) }}" alt="{{ $m->model ?? 'Phone' }}" style="width:36px; height:36px; object-fit:cover; border-radius:8px; border:1px solid #E2E8F0; cursor:pointer;" onclick="previewImage('{{ asset($photo) }}', '{{ $m->brand ?? '' }} {{ $m->model ?? '' }} (Pre-Owned)')">
                            @else
                            <div style="width:36px; height:36px; border-radius:8px; background:#FAF5FF; color:var(--lama-purple-dark); display:inline-flex; align-items:center; justify-content:center;">
                                <i data-lucide="smartphone" style="width:18px;height:18px;"></i>
                            </div>
                            @endif
                        </td>
                        <td>
                            <div style="font-weight:800; color:#0F172A; font-size:13px;">{{ $m->brand }} {{ $m->model }}</div>
                            <div style="font-size:11px; color:var(--text-secondary);">{{ $m->storage }} • {{ $m->color }}</div>
                        </td>
                        <td>
                            <span class="badge badge-purple" style="font-size:11px;">
                                {{ str_replace('_', ' ', $m->condition_grade) }}
                            </span>
                        </td>
                        <td style="font-weight:700; color:var(--text-primary); font-size:12px;">
                            @if($m->battery_health)
                                <span style="color:{{ (int)$m->battery_health >= 85 ? '#16A34A' : '#D97706' }};">
                                    🔋 {{ $m->battery_health }}%
                                </span>
                            @else
                                <span style="color:#94A3B8;">—</span>
                            @endif
                        </td>
                        <td style="font-family:monospace; color:var(--brand-700); font-size:12px; font-weight:700;">{{ $m->imei_1 }}</td>
                        <td style="font-size:12px;">
                            <div style="font-weight:700; color:#0F172A;">{{ $m->customer_buyback_name ?? 'Walk-in' }}</div>
                            <div style="font-size:11px; color:var(--text-secondary);">{{ $m->customer_buyback_phone ?? '' }}</div>
                        </td>
                        <td style="text-align:right; color:var(--text-secondary); font-size:12px;">₹{{ number_format($m->purchase_cost, 2) }}</td>
                        <td style="text-align:right; font-weight:800; color:#0F172A; font-size:13px;">₹{{ number_format($m->selling_price, 2) }}</td>
                        <td style="text-align:center;">
                            <span class="badge {{ $m->status === 'in_stock' ? 'badge-green' : 'badge-red' }}">
                                {{ strtoupper($m->status) }}
                            </span>
                        </td>
                        <td style="text-align:center;">
                            @if($m->status === 'in_stock')
                                <button onclick="quickSellPhone({{ json_encode($m) }})" class="btn btn-primary btn-sm" style="background:var(--lama-purple-dark); font-size:11px; padding:4px 10px;">
                                    <i data-lucide="shopping-bag" style="width:12px;height:12px;"></i> Sell
                                </button>
                            @else
                                <span style="font-size:11px; color:#94A3B8; font-weight:700;">SOLD</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align:center; padding:50px 20px;">
                            <div style="display:flex; flex-direction:column; align-items:center; gap:8px;">
                                <i data-lucide="smartphone" style="width:32px;height:32px;color:var(--text-muted);"></i>
                                <div style="font-weight:800; color:#0F172A;">No Pre-Owned Phones Found</div>
                                <div style="font-size:13px; color:var(--text-secondary);">Intake customer trade-ins or buybacks to build inventory.</div>
                                <button onclick="openBuybackModal()" class="btn btn-primary btn-sm" style="margin-top:6px; background:var(--lama-purple-dark);">
                                    <i data-lucide="plus" style="width:14px;height:14px;"></i> Register Buyback
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Zero-Depth Flat Cards List -->
    <div id="shMobileCards" style="display:none; padding:10px 12px; flex-direction:column; gap:8px;">
        @forelse($mobiles as $m)
        @php
            $photo = $m->photo_path ?? $m->box_photo_path ?? null;
        @endphp
        <div class="app-flat-row sh-card" data-status="{{ $m->status }}" data-grade="{{ $m->condition_grade }}" data-battery="{{ (int)$m->battery_health }}" data-brand="{{ strtolower($m->brand) }}" data-search="{{ strtolower(($m->brand ?? '') . ' ' . ($m->model ?? '') . ' ' . ($m->imei_1 ?? '') . ' ' . ($m->customer_buyback_name ?? '')) }}">
            <div style="display:flex; align-items:center; gap:10px; width:100%;">
                @if($photo)
                <img src="{{ asset($photo) }}" alt="{{ $m->model ?? 'Phone' }}" style="width:44px; height:44px; object-fit:cover; border-radius:10px; border:1px solid #E2E8F0; flex-shrink:0; cursor:pointer;" onclick="previewImage('{{ asset($photo) }}', '{{ $m->brand ?? '' }} {{ $m->model ?? '' }} (Pre-Owned)')">
                @else
                <div style="width:44px; height:44px; border-radius:10px; background:#FAF5FF; color:var(--lama-purple-dark); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <i data-lucide="smartphone" style="width:22px;height:22px;"></i>
                </div>
                @endif
                <div style="flex:1; min-width:0;">
                    <div style="font-weight:800; font-size:13px; color:#0F172A; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                        {{ $m->brand }} {{ $m->model }}
                    </div>
                    <div style="font-size:11px; color:#64748B; margin-top:2px;">
                        IMEI: <span style="font-family:monospace; font-weight:700; color:var(--brand-700);">{{ $m->imei_1 }}</span>
                        @if($m->battery_health) • 🔋{{ $m->battery_health }}% @endif
                    </div>
                </div>
                <div style="text-align:right; flex-shrink:0;">
                    <div style="font-weight:900; font-size:14px; color:#0F172A;">₹{{ number_format($m->selling_price, 0) }}</div>
                    <span class="badge {{ $m->status === 'in_stock' ? 'badge-green' : 'badge-red' }}" style="font-size:10px; padding:1px 6px;">
                        {{ strtoupper($m->status) }}
                    </span>
                </div>
            </div>
            @if($m->status === 'in_stock')
            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:6px; padding-top:6px; border-top:1px solid #F1F5F9; width:100%;">
                <span class="badge badge-purple" style="font-size:10px;">{{ str_replace('_', ' ', $m->condition_grade) }}</span>
                <button onclick="quickSellPhone({{ json_encode($m) }})" class="btn btn-primary btn-sm" style="font-size:11px; padding:4px 10px; height:auto; background:var(--lama-purple-dark);">
                    <i data-lucide="shopping-bag" style="width:12px;height:12px;"></i> Sell Device
                </button>
            </div>
            @endif
        </div>
        @empty
        <div style="text-align:center; padding:30px 16px; color:#94A3B8; font-size:12px;">No pre-owned phones registered.</div>
        @endforelse
    </div>
    <div id="secondHandPagination"></div>
</div>

<!-- Mobile Floating Action Button -->
<div class="mobile-fab-container">
    <button type="button" class="btn-app-fab" onclick="openBuybackModal()" title="Register Buyback" style="background:var(--lama-purple-dark);">
        <i data-lucide="plus" style="width:20px;height:20px;"></i>
        <span>Buyback</span>
    </button>
</div>

<!-- MODAL 1: Sell Pre-Owned Phone at POS Counter -->
<div id="sellModal" style="display:none; position: fixed; inset: 0; z-index: 1200; background: rgba(15,23,42,0.45); backdrop-filter: blur(4px); align-items:center; justify-content:center; padding: 16px;">
    <div class="card" style="max-width: 500px; width: 100%; max-height: 90vh; overflow-y:auto; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); border-radius:14px;">
        <div class="card-header" style="border-bottom:1px solid var(--card-border); padding:14px 18px;">
            <div class="card-title">Sell Pre-Owned Device</div>
            <button onclick="closeSellModal()" class="btn-icon">✕</button>
        </div>
        <div class="card-body" style="padding:16px 18px;">
            <form action="{{ route('mobileshop.second_hand.sale') }}" method="POST" id="sellShForm">
                @csrf
                <div class="form-group" style="margin-bottom: 12px;">
                    <label class="form-label required">Select In-Stock Phone</label>
                    <select name="device_id" id="shDeviceSelect" required class="form-control" onchange="onSelectUsedPhone(this)">
                        <option value="">-- Choose Pre-Owned Phone --</option>
                        @foreach($mobiles->where('status', 'in_stock') as $phone)
                            <option value="{{ $phone->id }}" data-price="{{ $phone->selling_price }}">
                                {{ $phone->brand }} {{ $phone->model }} (IMEI: {{ $phone->imei_1 }}) — ₹{{ number_format($phone->selling_price, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-row" style="margin-bottom: 12px; display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label required">Customer Mobile</label>
                        <input type="text" name="customer_phone" id="shCustomerPhone" placeholder="10-digit number" required class="form-control" list="shCustomerList">
                        <datalist id="shCustomerList">
                            @foreach($customers as $c)
                                <option value="{{ $c->phone }}" data-name="{{ $c->name }}">{{ $c->name }}</option>
                            @endforeach
                        </datalist>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label required">Customer Name</label>
                        <input type="text" name="customer_name" id="shCustomerName" placeholder="Full name" required class="form-control">
                    </div>
                </div>

                <!-- Bill Type / GST Checkbox -->
                <div style="background: #F8FAFC; border: 1.5px solid #E2E8F0; border-radius: 10px; padding: 10px 12px; margin-bottom: 12px;">
                    <label style="display:flex; align-items:center; gap:10px; cursor:pointer; margin:0; user-select:none;">
                        <input type="checkbox" name="is_gst" id="shIsGstCheckbox" value="1" style="width:18px; height:18px; accent-color:#5E6AD2; cursor:pointer;" onchange="document.getElementById('shBillType').value = this.checked ? 'gst' : 'non_gst'">
                        <div>
                            <span style="font-size:12px; font-weight:700; color:#0F172A;">Make GST Bill (18% Tax Invoice)</span>
                            <p style="font-size:10.5px; color:#64748B; margin:1px 0 0 0;">Unchecked by default (Standard Retail / Non-GST Estimate)</p>
                        </div>
                    </label>
                    <input type="hidden" name="bill_type" id="shBillType" value="non_gst">
                </div>

                <div class="form-row" style="margin-bottom: 12px; display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label required">Agreed Sale Price (₹)</label>
                        <input type="number" step="0.01" name="sale_price" id="shSalePrice" required class="form-control" oninput="updateShSummary()">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label required">Payment Mode</label>
                        <select name="payment_mode" class="form-control" onchange="onShPaymentModeChange(this)">
                            <option value="cash">💵 Cash</option>
                            <option value="upi">📱 UPI / QR</option>
                            <option value="card">💳 Card</option>
                            <option value="credit_udhari">📒 Full Udhari (Khata)</option>
                        </select>
                    </div>
                </div>

                <div class="form-row" style="margin-bottom: 14px; display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label required">Amount Paid Now (₹)</label>
                        <input type="number" step="0.01" name="amount_paid" id="shAmountPaid" required class="form-control" oninput="updateShSummary()">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Balance Due (₹)</label>
                        <input type="text" id="shBalanceDueDisplay" readonly value="₹0.00" class="form-control" style="background:#F1F5F9; font-weight:700; color:#DC2626;">
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end; gap: 10px; padding-top: 14px; border-top: 1px solid var(--card-border);">
                    <button type="button" onclick="closeSellModal()" class="btn btn-outline">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="background:var(--lama-purple-dark);">Complete Sale</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 2: Intake / Register Buyback (With Photo Upload) -->
<div id="buybackModal" style="display:none; position: fixed; inset: 0; z-index: 1200; background: rgba(15,23,42,0.45); backdrop-filter: blur(4px); align-items:center; justify-content:center; padding: 16px;">
    <div class="card" style="max-width: 540px; width: 100%; max-height: 90vh; overflow-y:auto; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); border-radius:14px;">
        <div class="card-header" style="border-bottom:1px solid var(--card-border); padding:14px 18px;">
            <div class="card-title">Customer Device Buyback Intake</div>
            <button onclick="closeBuybackModal()" class="btn-icon">✕</button>
        </div>
        <div class="card-body" style="padding:16px 18px;">
            <form action="{{ route('mobileshop.second_hand.buyback') }}" method="POST" enctype="multipart/form-data" id="buybackForm">
                @csrf
                <input type="hidden" name="redirect_to" value="{{ route('mobileshop.second_hand') }}">

                <!-- Photo Upload Field with Live Preview -->
                <div style="margin-bottom: 14px; background:#F8FAFC; border:1px dashed #CBD5E1; border-radius:10px; padding:12px; text-align:center;">
                    <div id="shPhotoPreviewBox" style="display:none; margin-bottom:8px;">
                        <img id="shPreviewImg" src="" alt="Preview" style="max-height:120px; border-radius:8px; object-fit:contain; border:1px solid #E2E8F0;">
                    </div>
                    <label style="display:inline-flex; align-items:center; gap:6px; cursor:pointer; font-size:12px; font-weight:700; color:var(--lama-purple-dark); background:#FAF5FF; padding:6px 14px; border-radius:8px; border:1px solid #E9D5FF;">
                        <i data-lucide="camera" style="width:14px;height:14px;"></i> Upload Device Condition Photo
                        <input type="file" name="photo" accept="image/*" style="display:none;" onchange="previewSelectedPhoto(this, 'shPreviewImg', 'shPhotoPreviewBox')">
                    </label>
                    <div style="font-size:10px; color:#64748B; margin-top:4px;">Capture screen/body condition (JPG, PNG, WebP up to 5MB)</div>
                </div>

                <div class="form-row" style="margin-bottom: 12px; display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label required">Brand</label>
                        <input type="text" name="brand" placeholder="e.g. Apple" required class="form-control">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label required">Model Name</label>
                        <input type="text" name="model" placeholder="e.g. iPhone 13 Pro" required class="form-control">
                    </div>
                </div>

                <div class="form-row" style="margin-bottom: 12px; display:grid; grid-template-columns:1fr 1fr 1fr; gap:8px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Color</label>
                        <input type="text" name="color" placeholder="e.g. Blue" class="form-control">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Storage</label>
                        <input type="text" name="storage" placeholder="e.g. 128GB" class="form-control">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Battery %</label>
                        <input type="number" name="battery_health" placeholder="88" class="form-control">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:12px;">
                    <label class="form-label required">IMEI 1 Number</label>
                    <input type="text" name="imei_1" placeholder="15-digit IMEI" required class="form-control" style="font-family:monospace; font-weight:700;">
                </div>

                <div class="form-row" style="margin-bottom: 12px; display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label required">Buyback Cost (₹)</label>
                        <input type="number" step="0.01" name="purchase_cost" placeholder="42000" required class="form-control">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label required">Target Resale Price (₹)</label>
                        <input type="number" step="0.01" name="selling_price" placeholder="54999" required class="form-control" style="font-weight:700; color:var(--lama-green-dark);">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:12px;">
                    <label class="form-label required">Condition Grade</label>
                    <select name="condition_grade" class="form-control">
                        <option value="like_new_A_plus">Grade A+ (Pristine / Like New)</option>
                        <option value="good_A">Grade A (Minor Micro-Scratches)</option>
                        <option value="fair_B">Grade B (Noticeable Wear / 100% Functional)</option>
                    </select>
                </div>

                <div style="padding: 12px; background: var(--lama-purple-light); border: 1px solid var(--lama-purple); border-radius:10px; margin-bottom: 12px;">
                    <div style="font-size:11px; font-weight:800; color:var(--brand-700); margin-bottom: 8px; text-transform:uppercase;">Customer KYC / Seller Info</div>
                    <div class="form-row" style="margin-bottom: 8px; display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                        <input type="text" name="customer_buyback_name" placeholder="Customer Name *" required class="form-control" style="font-size:12px;">
                        <input type="text" name="customer_buyback_phone" placeholder="Customer Phone *" required class="form-control" style="font-size:12px;">
                    </div>
                    <input type="text" name="customer_buyback_id_proof" placeholder="Aadhaar / ID Details" class="form-control" style="font-size:12px;">
                </div>

                <div class="form-group" style="margin-bottom:14px;">
                    <label class="form-label">Inspection Remarks</label>
                    <textarea name="checklist_notes" rows="2" placeholder="e.g. Original display, FaceID verified" class="form-control"></textarea>
                </div>

                <div style="display:flex; justify-content:flex-end; gap: 10px; padding-top: 14px; border-top: 1px solid var(--card-border);">
                    <button type="button" onclick="closeBuybackModal()" class="btn btn-outline">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="background:var(--lama-purple-dark);">Save to Stock</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Lightbox Modal for Photo Preview -->
<div id="imageLightboxModal" style="display:none; position:fixed; inset:0; z-index:300; background:rgba(15,23,42,0.85); backdrop-filter:blur(4px); align-items:center; justify-content:center; padding:16px;" onclick="closeImageLightbox()">
    <div style="max-width:90vw; max-height:85vh; position:relative; text-align:center;" onclick="event.stopPropagation()">
        <button onclick="closeImageLightbox()" style="position:absolute; top:-12px; right:-12px; background:#fff; border:none; border-radius:50%; width:32px; height:32px; font-weight:900; cursor:pointer; box-shadow:0 4px 10px rgba(0,0,0,0.3);">✕</button>
        <img id="lightboxImg" src="" alt="Device Photo" style="max-width:100%; max-height:75vh; border-radius:12px; box-shadow:0 20px 25px -5px rgba(0,0,0,0.5);">
        <div id="lightboxCaption" style="color:#fff; font-weight:800; font-size:14px; margin-top:10px;"></div>
    </div>
</div>

@endsection

@push('scripts')
<style>
@media (max-width: 768px) {
    #shDesktopStatGrid { display: none !important; }
    #shDesktopTableWrap { display: none !important; }
    #shMobileCards { display: flex !important; }
}
</style>
<script>
    function openBuybackModal() {
        document.getElementById('buybackModal').style.display = 'flex';
        if (window.lucide) window.lucide.createIcons();
    }
    function closeBuybackModal() {
        document.getElementById('buybackModal').style.display = 'none';
    }

    function openSellModal() {
        document.getElementById('sellModal').style.display = 'flex';
        if (window.lucide) window.lucide.createIcons();
    }
    function closeSellModal() {
        document.getElementById('sellModal').style.display = 'none';
    }

    function quickSellPhone(phone) {
        openSellModal();
        const select = document.getElementById('shDeviceSelect');
        if (select) {
            select.value = phone.id;
            onSelectUsedPhone(select);
        }
    }

    function onSelectUsedPhone(select) {
        const opt = select.options[select.selectedIndex];
        if (opt && opt.value) {
            const p = parseFloat(opt.dataset.price) || 0;
            document.getElementById('shSalePrice').value = p.toFixed(2);
            document.getElementById('shAmountPaid').value = p.toFixed(2);
            updateShSummary();
        }
    }

    function onShPaymentModeChange(select) {
        const mode = select.value;
        const price = document.getElementById('shSalePrice').value;
        if (mode === 'credit_udhari') {
            document.getElementById('shAmountPaid').value = '0.00';
        } else if (mode === 'cash' || mode === 'upi' || mode === 'card') {
            document.getElementById('shAmountPaid').value = price;
        }
        updateShSummary();
    }

    function updateShSummary() {
        const price = parseFloat(document.getElementById('shSalePrice').value) || 0;
        const paid = parseFloat(document.getElementById('shAmountPaid').value) || 0;
        const due = Math.max(0, price - paid);
        document.getElementById('shBalanceDueDisplay').value = '₹' + due.toFixed(2);
    }

    // Customer Datalist autofill
    document.getElementById('shCustomerPhone')?.addEventListener('input', function() {
        const val = this.value.trim();
        const option = document.querySelector(`#shCustomerList option[value="${val}"]`);
        if (option) {
            document.getElementById('shCustomerName').value = option.dataset.name || '';
        }
    });

    let currentShFilter = 'all';
    let currentShGrade = 'all';
    let currentShSearch = '';

    function applyShFilter(filterType, btnElement) {
        currentShFilter = filterType;

        document.querySelectorAll('.filter-pill-btn').forEach(p => p.classList.remove('active'));
        if (btnElement) {
            btnElement.classList.add('active');
        } else {
            const map = { 'all': 'pill-sh-all', 'in_stock': 'pill-sh-stock', 'sold': 'pill-sh-sold', 'battery_80': 'pill-sh-battery' };
            if (map[filterType]) document.getElementById(map[filterType])?.classList.add('active');
        }

        renderFilteredSh();
    }

    function applyShGradeFilter(grade) {
        currentShGrade = grade;
        renderFilteredSh();
    }

    function onShSearch(term) {
        currentShSearch = term.toLowerCase().trim();
        renderFilteredSh();
    }

    function renderFilteredSh() {
        // Desktop Rows
        document.querySelectorAll('#secondHandTable tbody tr.sh-row').forEach(row => {
            const status = row.dataset.status || 'in_stock';
            const grade = row.dataset.grade || '';
            const battery = parseInt(row.dataset.battery) || 0;
            const searchTarget = (row.dataset.search || row.textContent).toLowerCase();

            let matchStatus = (currentShFilter === 'all') || (currentShFilter === 'in_stock' && status === 'in_stock') || (currentShFilter === 'sold' && status === 'sold') || (currentShFilter === 'battery_80' && battery >= 85);
            let matchGrade = (currentShGrade === 'all') || (grade === currentShGrade);
            let matchSearch = !currentShSearch || searchTarget.includes(currentShSearch);

            const isVisible = matchStatus && matchGrade && matchSearch;
            row.dataset.mobiHidden = isVisible ? '0' : '1';
            row.style.display = isVisible ? '' : 'none';
        });

        // Mobile Cards
        document.querySelectorAll('#shMobileCards .sh-card').forEach(card => {
            const status = card.dataset.status || 'in_stock';
            const grade = card.dataset.grade || '';
            const battery = parseInt(card.dataset.battery) || 0;
            const searchTarget = (card.dataset.search || card.textContent).toLowerCase();

            let matchStatus = (currentShFilter === 'all') || (currentShFilter === 'in_stock' && status === 'in_stock') || (currentShFilter === 'sold' && status === 'sold') || (currentShFilter === 'battery_80' && battery >= 85);
            let matchGrade = (currentShGrade === 'all') || (grade === currentShGrade);
            let matchSearch = !currentShSearch || searchTarget.includes(currentShSearch);

            const isCardVisible = matchStatus && matchGrade && matchSearch;
            card.dataset.mobiHidden = isCardVisible ? '0' : '1';
            card.style.display = isCardVisible ? '' : 'none';
        });

        if (window.shPager && typeof window.shPager.refresh === 'function') {
            window.shPager.refresh();
        }
    }

    function initShPage() {
        if (window.setupMobiTablePagination) {
            window.shPager = window.setupMobiTablePagination({
                tableId: 'secondHandTable',
                cardsContainerId: 'shMobileCards',
                paginationContainerId: 'secondHandPagination',
                rowSelector: 'tbody tr.sh-row',
                cardSelector: '.sh-card',
                pageSize: 25,
                itemName: 'pre-owned phones'
            });
        }
        renderFilteredSh();
        if (window.refreshIcons) window.refreshIcons();
        else if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initShPage);
    } else {
        initShPage();
    }

    function previewSelectedPhoto(input, imgId, boxId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById(imgId);
                const box = document.getElementById(boxId);
                if (img && box) {
                    img.src = e.target.result;
                    box.style.display = 'block';
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function previewImage(url, caption) {
        const modal = document.getElementById('imageLightboxModal');
        const img = document.getElementById('lightboxImg');
        const cap = document.getElementById('lightboxCaption');
        if (modal && img) {
            img.src = url;
            if (cap) cap.textContent = caption || '';
            modal.style.display = 'flex';
        }
    }

    function closeImageLightbox() {
        const modal = document.getElementById('imageLightboxModal');
        if (modal) modal.style.display = 'none';
    }

</script>
@endpush
