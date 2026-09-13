@extends('mobileshop.layout')

@section('title', 'Sell Second-Hand Phone — Maurya Mobile')
@section('page-title', 'Pre-Owned Phone POS Billing')

@section('page-actions')
    <div style="display:flex; gap:10px; align-items:center;">
        <a href="{{ route('mobileshop.sales') }}" class="btn btn-outline btn-sm" style="display:inline-flex; align-items:center; gap:6px;">
            <i data-lucide="arrow-left" style="width:14px;height:14px;"></i> Sales Hub
        </a>
        <a href="{{ route('mobileshop.stock', ['tab' => 'second_hand']) }}" class="btn btn-outline btn-sm" style="display:inline-flex; align-items:center; gap:6px;">
            <i data-lucide="smartphone" style="width:14px;height:14px;"></i> Stock Inventory
        </a>
    </div>
@endsection

@push('styles')
<style>
    /* ── Responsive Container ── */
    .sh-pos-container {
        max-width: 680px;
        margin: 0 auto;
    }
    @media (min-width: 1024px) {
        .sh-pos-container {
            max-width: 1400px;
            margin: 0 auto;
        }
        .sh-pos-grid {
            display: grid;
            grid-template-columns: 1fr 440px;
            gap: 20px;
            align-items: flex-start;
        }
        .sh-col-left {
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .sh-col-right {
            position: sticky;
            top: 16px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .sh-mobile-dock {
            display: none !important;
        }
        .sh-desktop-checkout {
            display: block !important;
        }
    }
    @media (max-width: 1023px) {
        .sh-pos-grid {
            display: block;
        }
        .sh-desktop-checkout {
            display: none !important;
        }
        .sh-pos-container {
            padding-bottom: calc(var(--bottom-nav-height, 58px) + env(safe-area-inset-bottom, 0px) + 120px) !important;
        }
    }

    /* ── Form Cards ── */
    .sh-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        padding: 18px 20px;
        margin-bottom: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
    }
    .sh-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 14px;
        padding-bottom: 10px;
        border-bottom: 1px solid #F1F5F9;
    }
    .sh-card-title {
        font-size: 13px;
        font-weight: 800;
        color: #0F172A;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* ── Inputs ── */
    .sh-input-label {
        font-size: 12px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 6px;
        display: block;
    }
    .sh-input-text {
        width: 100%;
        height: 44px;
        font-size: 14px;
        font-weight: 600;
        color: #0F172A;
        background: #F8FAFC;
        border: 1.5px solid #CBD5E1;
        border-radius: 10px;
        padding: 0 14px;
        transition: all 0.2s ease;
        outline: none;
    }
    .sh-input-text:focus {
        background: #FFFFFF;
        border-color: #7C3AED;
        box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.15);
    }

    /* ── Stock Device Card ── */
    .sh-device-card {
        background: #FFFFFF;
        border: 1.5px solid #E2E8F0;
        border-radius: 12px;
        padding: 12px 14px;
        cursor: pointer;
        transition: all 0.18s ease;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
    }
    .sh-device-card:hover {
        border-color: #7C3AED;
        box-shadow: 0 6px 16px -2px rgba(124, 58, 237, 0.15);
        transform: translateY(-2px);
    }
    .sh-device-card.selected {
        border-color: #7C3AED;
        background: #F5F3FF;
        box-shadow: 0 0 0 2px #7C3AED;
    }

    /* ── Condition Badges ── */
    .grade-badge {
        font-size: 10px;
        font-weight: 800;
        padding: 2px 7px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        text-transform: uppercase;
    }
    .grade-badge-a_plus { background: #EDE9FE; color: #6D28D9; border: 1px solid #DDD6FE; }
    .grade-badge-a { background: #DCFCE7; color: #15803D; border: 1px solid #BBF7D0; }
    .grade-badge-b { background: #FEF9C3; color: #854D0E; border: 1px solid #FEF08A; }
    .grade-badge-c { background: #FEE2E2; color: #B91C1C; border: 1px solid #FECACA; }

    /* ── Discount Switcher ── */
    .disc-pill-group {
        display: inline-flex;
        background: #F1F5F9;
        border-radius: 6px;
        padding: 2px;
        gap: 2px;
        border: 1px solid #E2E8F0;
    }
    .disc-pill-btn {
        border: none;
        background: transparent;
        font-size: 10.5px;
        font-weight: 700;
        color: #64748B;
        padding: 3px 8px;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .disc-pill-btn:hover {
        color: #1E293B;
    }
    .disc-pill-btn.active {
        background: #7C3AED;
        color: #FFFFFF;
        box-shadow: 0 1px 2px rgba(124, 58, 237, 0.3);
    }

    /* ── Payment Mode Chips ── */
    .payment-chip-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
        gap: 8px;
        margin-bottom: 14px;
    }
    .payment-chip-btn {
        padding: 10px 8px;
        border-radius: 10px;
        border: 1.5px solid #E2E8F0;
        background: #F8FAFC;
        color: #334155;
        font-size: 12px;
        font-weight: 700;
        text-align: center;
        cursor: pointer;
        transition: all 0.15s ease;
        user-select: none;
    }
    .payment-chip-btn.active {
        border-color: #7C3AED;
        background: #F5F3FF;
        color: #6D28D9;
        box-shadow: 0 2px 6px rgba(124, 58, 237, 0.15);
    }

    /* ── Bottom Mobile Dock ── */
    .sh-mobile-dock {
        position: fixed;
        left: 0;
        right: 0;
        bottom: calc(var(--bottom-nav-height, 58px) + env(safe-area-inset-bottom, 0px)) !important;
        background: #FFFFFF;
        border-top: 1.5px solid #E2E8F0;
        border-bottom: 1px solid #E2E8F0;
        box-shadow: 0 -8px 24px rgba(15, 23, 42, 0.08);
        padding: 12px 16px;
        z-index: 990;
    }
    .sh-btn-checkout {
        width: 100%;
        height: 48px;
        background: #7C3AED;
        color: #FFFFFF;
        font-size: 15px;
        font-weight: 800;
        border: none;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        cursor: pointer;
        box-shadow: 0 4px 14px rgba(124, 58, 237, 0.35);
        transition: all 0.15s ease;
    }
    .sh-btn-checkout:disabled {
        background: #94A3B8 !important;
        box-shadow: none !important;
        cursor: not-allowed !important;
    }
    .sh-btn-checkout:not(:disabled):hover {
        background: #6D28D9;
        transform: translateY(-1px);
    }

    /* Filter Pills */
    .category-pills-row {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        padding-bottom: 6px;
        margin-bottom: 12px;
        scrollbar-width: none;
    }
    .category-pills-row::-webkit-scrollbar { display: none; }
    .category-pill {
        white-space: nowrap;
        font-size: 12px;
        font-weight: 700;
        padding: 6px 12px;
        border-radius: 9999px;
        border: 1.5px solid #E2E8F0;
        background: #F8FAFC;
        color: #475569;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .category-pill.active {
        background: #7C3AED;
        border-color: #7C3AED;
        color: #FFFFFF;
        box-shadow: 0 3px 8px rgba(124, 58, 237, 0.25);
    }
</style>
@endpush

@section('content')
<div class="sh-pos-container">

    <form action="{{ route('mobileshop.second_hand.sale') }}" method="POST" id="shSaleForm" onsubmit="return validateShSaleForm(this)">
        @csrf
        <input type="hidden" name="idempotency_key" value="{{ \Illuminate\Support\Str::uuid() }}">
        <input type="hidden" name="device_id" id="shSelectedDeviceId" value="">
        <input type="hidden" name="sale_price" id="shFinalSalePrice" value="0">
        <input type="hidden" name="bill_type" id="shBillType" value="non_gst">

        <div class="sh-pos-grid">

            <!-- ═══════════ LEFT COLUMN: CUSTOMER & PHONE INVENTORY ═══════════ -->
            <div class="sh-col-left">

                <!-- ── 1. Customer Details ── -->
                <div class="sh-card">
                    <div class="sh-card-header">
                        <div class="sh-card-title">
                            <i data-lucide="user" style="width:16px;height:16px; color:#7C3AED;"></i> Buyer / Customer Information
                        </div>
                        <span id="shKhataIndicator" style="display:none; font-size:11px; font-weight:800; color:#DC2626; background:#FEF2F2; padding:3px 8px; border-radius:6px; border:1px solid #FECACA;">
                            ⚠️ Khata Due: ₹<span id="shKhataDueText">0</span>
                        </span>
                    </div>

                    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:12px; margin-bottom:12px;">
                        <div>
                            <label class="sh-input-label">Customer Mobile *</label>
                            <input type="tel" name="customer_phone" id="shCustomerPhone" list="shCustomerList" placeholder="10-digit Mobile Number" required class="sh-input-text" autocomplete="off" oninput="handleShPhoneInput(this.value)">
                            <datalist id="shCustomerList">
                                @foreach($customers as $c)
                                    <option value="{{ $c->phone }}" data-name="{{ $c->name }}" data-balance="{{ $c->udhari_balance ?? 0 }}" data-gstin="{{ $c->gstin ?? '' }}" data-address="{{ $c->address ?? '' }}">
                                        {{ $c->name }} (Khata: ₹{{ number_format($c->udhari_balance ?? 0, 0) }})
                                    </option>
                                @endforeach
                            </datalist>
                        </div>
                        <div>
                            <label class="sh-input-label">Customer Name *</label>
                            <input type="text" name="customer_name" id="shCustomerName" placeholder="Full Customer Name" required class="sh-input-text">
                        </div>
                        <div>
                            <label class="sh-input-label">Address / City (Optional)</label>
                            <input type="text" name="customer_address" id="shCustomerAddress" placeholder="Customer City" class="sh-input-text">
                        </div>
                    </div>

                    <!-- GST Invoice Toggle -->
                    <label style="display:flex; align-items:center; justify-content:space-between; padding:10px 14px; background:#F8FAFC; border:1.5px solid #E2E8F0; border-radius:10px; cursor:pointer;">
                        <div>
                            <span style="font-size:13px; font-weight:700; color:#0F172A;">Issue GST Tax Invoice (18% GST)</span>
                            <p style="font-size:11px; color:#64748B; margin:2px 0 0 0;">Toggle on for formal GST invoice with Margin Scheme GST calculation</p>
                        </div>
                        <input type="checkbox" name="is_gst" id="shIsGstToggle" value="1" style="width:18px; height:18px; accent-color:#7C3AED; cursor:pointer;" onchange="toggleShGst(this.checked)">
                    </label>
                </div>

                <!-- ── 2. Select Second-Hand Phone from In-Stock Catalog ── -->
                <div class="sh-card">
                    <div class="sh-card-header">
                        <div class="sh-card-title">
                            <i data-lucide="smartphone" style="width:16px;height:16px; color:#7C3AED;"></i> Select Pre-Owned Phone to Sell
                        </div>
                        <span style="font-size:11px; font-weight:800; color:#7C3AED; background:#EDE9FE; padding:2px 8px; border-radius:6px;">
                            {{ $devices->count() }} In Stock
                        </span>
                    </div>

                    <!-- Filter Bar -->
                    <div style="display:flex; gap:10px; align-items:center; margin-bottom:12px; flex-wrap:wrap;">
                        <div style="position:relative; flex:1; min-width:200px;">
                            <input type="text" id="shCatalogSearch" placeholder="Search brand, model, IMEI or variant..." class="sh-input-text" style="padding-left:36px; height:38px; font-size:13px;" oninput="onShCatalogSearch(this.value)">
                            <i data-lucide="search" style="position:absolute; left:10px; top:11px; width:16px; height:16px; color:#94A3B8;"></i>
                        </div>
                        <div class="category-pills-row" style="margin:0; padding:0;">
                            <div class="category-pill active" data-grade="" onclick="filterShByGrade('')">All Units ({{ $devices->count() }})</div>
                            <div class="category-pill" data-grade="a_plus" onclick="filterShByGrade('a_plus')">Grade A+</div>
                            <div class="category-pill" data-grade="a" onclick="filterShByGrade('a')">Grade A</div>
                            <div class="category-pill" data-grade="b" onclick="filterShByGrade('b')">Grade B</div>
                            <div class="category-pill" data-grade="c" onclick="filterShByGrade('c')">Grade C</div>
                        </div>
                    </div>

                    <!-- Selected Device Spotlight Card (Appears when selected) -->
                    <div id="shSelectedSpotlight" style="display:none; background:#F5F3FF; border:1.5px solid #C4B5FD; border-radius:12px; padding:12px 14px; margin-bottom:14px;">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:10px;">
                            <div>
                                <div style="font-size:11px; font-weight:800; color:#6D28D9; text-transform:uppercase; letter-spacing:0.4px;">Currently Selected for Sale:</div>
                                <div id="spotlightModelName" style="font-size:15px; font-weight:900; color:#1E1B4B; margin-top:2px;">Phone Name</div>
                                <div id="spotlightSpecs" style="font-size:12px; color:#475569; margin-top:2px;">Variant specs</div>
                                <div style="margin-top:4px; display:flex; gap:6px; align-items:center; flex-wrap:wrap;">
                                    <span id="spotlightImei" style="font-family:'JetBrains Mono', monospace; font-size:11px; font-weight:700; color:#334155; background:#EDE9FE; padding:1px 6px; border-radius:4px;">IMEI</span>
                                    <span id="spotlightGradeBadge" class="grade-badge">Grade</span>
                                    <span id="spotlightBattery" style="font-size:11px; font-weight:700; color:#15803D; background:#DCFCE7; padding:1px 6px; border-radius:4px;">Battery</span>
                                </div>
                            </div>
                            <div style="text-align:right;">
                                <div style="font-size:11px; color:#64748B;">Listed Price</div>
                                <div id="spotlightPrice" style="font-size:18px; font-weight:900; color:#7C3AED; font-family:'JetBrains Mono', monospace;">₹0</div>
                                <div style="font-size:10.5px; color:#64748B; margin-top:2px;">Cost: <strong id="spotlightCost" style="font-family:'JetBrains Mono', monospace;">₹0</strong></div>
                            </div>
                        </div>
                    </div>

                    <!-- In-Stock Devices Grid -->
                    <div id="shDevicesGrid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:12px; max-height:480px; overflow-y:auto; padding:2px;">
                        @forelse($devices as $dev)
                            @php
                                $grade = $dev->condition_grade ?? 'a';
                                $gradeSlug = strtolower(str_replace([' ', '+'], ['_', '_plus'], $grade));
                                $gradeLabel = 'Grade ' . strtoupper(str_replace('_', ' ', $grade));
                                $cost = round((float)($dev->purchase_cost ?? 0));
                                $price = round((float)($dev->selling_price ?? 0));
                                $battery = (int)($dev->battery_health ?? 0);
                            @endphp
                            <div class="sh-device-card" id="shCard_{{ $dev->id }}" data-id="{{ $dev->id }}" data-brand="{{ $dev->brand }}" data-model="{{ $dev->model }}" data-imei="{{ $dev->imei_1 }}" data-grade="{{ $gradeSlug }}" data-cost="{{ (float)$dev->purchase_cost }}" data-price="{{ (float)$dev->selling_price }}" data-battery="{{ $battery }}" data-variant="{{ ($dev->ram ? $dev->ram.'/' : '').($dev->storage ?? 'Std').' '.($dev->color ?? '') }}" onclick="selectShDevice({{ $dev->id }})">
                                <div>
                                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:4px;">
                                        <span class="grade-badge grade-badge-{{ $gradeSlug }}">{{ $gradeLabel }}</span>
                                        @if($battery > 0)
                                            <span style="font-size:10.5px; font-weight:700; color:#16A34A; background:#F0FDF4; padding:1px 5px; border-radius:4px;">🔋 {{ $battery }}%</span>
                                        @endif
                                    </div>
                                    <div style="font-size:13px; font-weight:800; color:#0F172A; line-height:1.3; margin-bottom:2px;">{{ $dev->brand }} {{ $dev->model }}</div>
                                    <div style="font-size:11.5px; color:#64748B;">{{ ($dev->ram ? $dev->ram.'/' : '').($dev->storage ?? 'Std') }} {{ $dev->color ?? '' }}</div>
                                    <div style="font-size:10.5px; font-family:'JetBrains Mono', monospace; color:#475569; margin-top:2px;">IMEI: {{ $dev->imei_1 }}</div>
                                </div>
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-top:10px; padding-top:6px; border-top:1px dashed #E2E8F0;">
                                    <span style="font-size:15px; font-weight:900; color:#7C3AED; font-family:'JetBrains Mono', monospace;">₹{{ number_format($price, 0) }}</span>
                                    <button type="button" style="background:#EDE9FE; border:1px solid #DDD6FE; color:#6D28D9; border-radius:6px; padding:3px 10px; font-size:11px; font-weight:800; cursor:pointer;">Select</button>
                                </div>
                            </div>
                        @empty
                            <div style="grid-column:1/-1; padding:32px; text-align:center; color:#94A3B8;">
                                <i data-lucide="smartphone" style="width:36px; height:36px; margin:0 auto 8px; display:block; color:#CBD5E1;"></i>
                                <div style="font-size:14px; font-weight:700; color:#64748B;">No Second-Hand Mobiles in Stock</div>
                                <p style="font-size:12px; color:#94A3B8; margin:4px 0 0 0;">Register pre-owned phones via buyback to begin selling</p>
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>

            <!-- ═══════════ RIGHT COLUMN: FINANCIALS, DISCOUNT & SETTLEMENT ═══════════ -->
            <div class="sh-col-right">

                <div class="sh-card">
                    <div class="sh-card-header">
                        <div class="sh-card-title">
                            <i data-lucide="calculator" style="width:16px;height:16px; color:#10B981;"></i> Pricing & Multi-Mode Discount
                        </div>
                    </div>

                    <!-- Original Listing Price -->
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                        <span style="font-size:13px; color:#64748B; font-weight:600;">Listed Sale Price:</span>
                        <strong id="lblShOriginalPrice" style="font-size:15px; color:#0F172A; font-family:'JetBrains Mono', monospace;">₹0</strong>
                    </div>

                    <!-- Discount Mode Segmented Selector -->
                    <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:10px; padding:10px 12px; margin-bottom:12px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                            <label class="sh-input-label" style="margin:0;">Discount Mode</label>
                            <div class="disc-pill-group">
                                <button type="button" class="disc-pill-btn active" id="btnDiscNone" onclick="setShDiscountMode('none')">None</button>
                                <button type="button" class="disc-pill-btn" id="btnDiscPercent" onclick="setShDiscountMode('percent')">% Pct</button>
                                <button type="button" class="disc-pill-btn" id="btnDiscFlat" onclick="setShDiscountMode('flat')">₹ Flat</button>
                                <button type="button" class="disc-pill-btn" id="btnDiscCustom" onclick="setShDiscountMode('custom')">Custom</button>
                            </div>
                        </div>

                        <!-- Dynamic Input for Discount -->
                        <div id="shDiscountInputWrap" style="display:none; align-items:center; gap:8px;">
                            <span id="shDiscountPrefix" style="font-size:13px; font-weight:700; color:#64748B;">₹</span>
                            <input type="number" id="shDiscountValInput" min="0" value="0" class="sh-input-text" style="height:36px; font-size:14px; font-weight:800; text-align:right;" oninput="onShDiscountValInput(this.value)">
                            <span id="shDiscountSuffix" style="font-size:12px; font-weight:700; color:#DC2626;"></span>
                        </div>
                    </div>

                    <!-- Financial Breakdown Box -->
                    <div style="background:#F8FAFC; border:1.5px solid #E2E8F0; border-radius:12px; padding:12px 14px; margin-bottom:14px;">
                        <div id="shDiscountSummaryRow" style="display:none; justify-content:space-between; font-size:13px; color:#DC2626; margin-bottom:6px;">
                            <span>Discount Given:</span>
                            <strong id="lblShDiscountTotal">-₹0</strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; align-items:baseline; margin-bottom:8px; border-top:1px solid #E2E8F0; padding-top:6px;">
                            <span style="font-size:13px; font-weight:700; color:#0F172A;">Net Bill Amount:</span>
                            <strong id="lblShFinalTotal" style="font-size:20px; font-weight:900; color:#059669; font-family:'JetBrains Mono', monospace;">₹0</strong>
                        </div>

                        <!-- Staff Margin Indicator -->
                        <div style="background:#ECFDF5; border:1px solid #A7F3D0; border-radius:6px; padding:6px 10px; display:flex; justify-content:space-between; align-items:center;">
                            <span style="font-size:11px; font-weight:700; color:#065F46;">Store Margin:</span>
                            <span id="lblShProfitMargin" style="font-size:12px; font-weight:800; color:#059669; font-family:'JetBrains Mono', monospace;">₹0 (0%)</span>
                        </div>
                    </div>

                    <!-- Payment Mode Selection -->
                    <label class="sh-input-label">Payment Method</label>
                    <div class="payment-chip-grid">
                        <div class="payment-chip-btn active" data-mode="cash" onclick="selectShPaymentMode('cash')">💵 Cash</div>
                        <div class="payment-chip-btn" data-mode="upi" onclick="selectShPaymentMode('upi')">📱 UPI / QR</div>
                        <div class="payment-chip-btn" data-mode="card" onclick="selectShPaymentMode('card')">💳 Card</div>
                        <div class="payment-chip-btn" data-mode="credit_udhari" onclick="selectShPaymentMode('credit_udhari')">📒 Khata</div>
                        <div class="payment-chip-btn" data-mode="split" onclick="selectShPaymentMode('split')">⚖️ Split</div>
                    </div>
                    <input type="hidden" name="payment_mode" id="shPaymentMode" value="cash">

                    <!-- Amount Paid Now -->
                    <div style="margin-bottom:12px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                            <label class="sh-input-label" style="margin:0;">Amount Paid Now (₹) *</label>
                            <div style="display:flex; gap:6px;">
                                <button type="button" onclick="setShFullPaid()" style="font-size:10.5px; padding:2px 8px; border-radius:4px; font-weight:800; color:#16A34A; border:1px solid #BBF7D0; background:#F0FDF4; cursor:pointer;">Full Paid</button>
                                <button type="button" onclick="setShZeroPaid()" style="font-size:10.5px; padding:2px 8px; border-radius:4px; font-weight:800; color:#DC2626; border:1px solid #FECDD3; background:#FFF1F2; cursor:pointer;">Udhari (₹0)</button>
                            </div>
                        </div>
                        <input type="number" step="1" name="amount_paid" id="shAmountPaid" required placeholder="0" class="sh-input-text" style="font-size:18px; font-weight:900; color:#16A34A;" oninput="onShAmountPaidInput()">
                    </div>

                    <div style="display:flex; justify-content:space-between; font-size:13px; font-weight:700; margin-bottom:12px; background:#FEF2F2; border:1px solid #FECACA; border-radius:8px; padding:6px 10px;">
                        <span style="color:#991B1B;">Balance to Khata (Udhari):</span>
                        <strong id="lblShDueAmount" style="color:#DC2626; font-family:'JetBrains Mono', monospace;">₹0</strong>
                    </div>

                    <div style="margin-bottom:14px;">
                        <label class="sh-input-label">Sale Remarks / Warranty</label>
                        <input type="text" name="notes" placeholder="e.g. 7-day testing warranty, with original charger" class="sh-input-text" style="height:36px; font-size:12px;">
                    </div>

                    <!-- Desktop Checkout CTA -->
                    <div class="sh-desktop-checkout">
                        <button type="submit" id="btnShSubmitDesktop" class="sh-btn-checkout" disabled>
                            <i data-lucide="check-circle-2" style="width:18px;height:18px;"></i>
                            <span>Complete Second-Hand Sale</span>
                        </button>
                    </div>

                </div>

            </div>

        </div>

        <!-- ── Fixed Bottom Checkout Dock (Mobile Only) ── -->
        <div class="sh-mobile-dock">
            <div style="display:flex; justify-content:space-between; align-items:center; gap:12px;">
                <div>
                    <div style="font-size:10px; font-weight:700; color:#64748B; text-transform:uppercase;">Bill Total</div>
                    <div style="font-size:18px; font-weight:900; color:#0F172A;" id="shMobileDockTotal">₹0</div>
                </div>
                <button type="submit" id="btnShSubmitMobile" class="sh-btn-checkout" style="flex:1; height:44px;" disabled>
                    <i data-lucide="check-circle-2" style="width:16px;height:16px;"></i>
                    <span>Sell Second-Hand</span>
                </button>
            </div>
        </div>

    </form>

</div>
@endsection

@push('scripts')
<script>
    const ALL_DEVICES = @json($devices);
    let selectedDevice = null;
    let discountMode = 'none'; // 'none', 'percent', 'flat', 'custom'
    let discountVal = 0;

    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });

    // ── Customer Autocomplete & Khata Balance Lookup ──
    function handleShPhoneInput(phoneVal) {
        const clean = phoneVal.trim();
        const datalist = document.getElementById('shCustomerList');
        if (!datalist) return;

        const options = datalist.querySelectorAll('option');
        for (let opt of options) {
            if (opt.value === clean) {
                const name = opt.getAttribute('data-name');
                const bal = parseFloat(opt.getAttribute('data-balance') || 0);
                const address = opt.getAttribute('data-address');
                if (name) document.getElementById('shCustomerName').value = name;
                if (address) document.getElementById('shCustomerAddress').value = address;

                const ind = document.getElementById('shKhataIndicator');
                const txt = document.getElementById('shKhataDueText');
                if (bal > 0) {
                    txt.innerText = Math.round(bal).toLocaleString('en-IN');
                    ind.style.display = 'inline-flex';
                } else {
                    ind.style.display = 'none';
                }
                return;
            }
        }
        document.getElementById('shKhataIndicator').style.display = 'none';
    }

    // ── Select Device from Catalog ──
    function selectShDevice(deviceId) {
        const dev = ALL_DEVICES.find(d => d.id === deviceId);
        if (!dev) return;

        selectedDevice = dev;
        document.getElementById('shSelectedDeviceId').value = dev.id;

        // Highlight selected card
        document.querySelectorAll('.sh-device-card').forEach(c => c.classList.remove('selected'));
        const activeCard = document.getElementById(`shCard_${dev.id}`);
        if (activeCard) activeCard.classList.add('selected');

        // Populate spotlight banner
        const spotlight = document.getElementById('shSelectedSpotlight');
        document.getElementById('spotlightModelName').innerText = `${dev.brand} ${dev.model}`;
        document.getElementById('spotlightSpecs').innerText = `${(dev.ram ? dev.ram + '/' : '') + (dev.storage || 'Std')} ${dev.color || ''}`;
        document.getElementById('spotlightImei').innerText = `IMEI: ${dev.imei_1}`;

        const grade = (dev.condition_grade || 'A').toUpperCase();
        const gradeSlug = strSlug(dev.condition_grade || 'a');
        const badge = document.getElementById('spotlightGradeBadge');
        badge.className = `grade-badge grade-badge-${gradeSlug}`;
        badge.innerText = `Grade ${grade}`;

        const battEl = document.getElementById('spotlightBattery');
        const batt = parseInt(dev.battery_health || 0);
        if (batt > 0) {
            battEl.innerText = `🔋 ${batt}% Battery`;
            battEl.style.display = 'inline-flex';
        } else {
            battEl.style.display = 'none';
        }

        const origPrice = Math.round(parseFloat(dev.selling_price || 0));
        const costPrice = Math.round(parseFloat(dev.purchase_cost || 0));
        document.getElementById('spotlightPrice').innerText = `₹${origPrice.toLocaleString('en-IN')}`;
        document.getElementById('spotlightCost').innerText = `₹${costPrice.toLocaleString('en-IN')}`;
        document.getElementById('lblShOriginalPrice').innerText = `₹${origPrice.toLocaleString('en-IN')}`;

        spotlight.style.display = 'block';

        // Enable buttons
        const btnD = document.getElementById('btnShSubmitDesktop');
        const btnM = document.getElementById('btnShSubmitMobile');
        if (btnD) btnD.disabled = false;
        if (btnM) btnM.disabled = false;

        // Reset discount mode to none unless already customized
        discountMode = 'none';
        discountVal = 0;
        updateDiscountPills();

        recalcShTotals();
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    // ── Discount Mode Selection ──
    function setShDiscountMode(mode) {
        if (!selectedDevice) {
            alert('Please select a phone first.');
            return;
        }
        discountMode = mode;
        const origPrice = Math.round(parseFloat(selectedDevice.selling_price || 0));

        if (mode === 'none') {
            discountVal = 0;
        } else if (mode === 'percent') {
            discountVal = 5; // Default 5%
        } else if (mode === 'flat') {
            discountVal = 500; // Default ₹500 off
        } else if (mode === 'custom') {
            discountVal = origPrice; // Default to listed price
        }

        updateDiscountPills();
        recalcShTotals();
    }

    function updateDiscountPills() {
        ['None', 'Percent', 'Flat', 'Custom'].forEach(m => {
            const btn = document.getElementById(`btnDisc${m}`);
            if (btn) btn.classList.toggle('active', m.toLowerCase() === discountMode);
        });

        const wrap = document.getElementById('shDiscountInputWrap');
        const prefix = document.getElementById('shDiscountPrefix');
        const suffix = document.getElementById('shDiscountSuffix');
        const valInput = document.getElementById('shDiscountValInput');

        if (discountMode === 'none') {
            wrap.style.display = 'none';
        } else {
            wrap.style.display = 'flex';
            if (discountMode === 'percent') {
                prefix.innerText = '';
                suffix.innerText = '% off';
                valInput.value = discountVal;
                valInput.placeholder = 'e.g. 10';
            } else if (discountMode === 'flat') {
                prefix.innerText = '₹';
                suffix.innerText = 'off';
                valInput.value = discountVal;
                valInput.placeholder = 'e.g. 1000';
            } else if (discountMode === 'custom') {
                prefix.innerText = '₹';
                suffix.innerText = 'Set Price';
                valInput.value = discountVal;
                valInput.placeholder = 'Custom Sale Price';
            }
        }
    }

    function onShDiscountValInput(val) {
        discountVal = Math.max(0, Math.round(parseFloat(val) || 0));
        recalcShTotals();
    }

    // ── Recalculate Totals & Profit ──
    function recalcShTotals() {
        if (!selectedDevice) return;

        const origPrice = Math.round(parseFloat(selectedDevice.selling_price || 0));
        const costPrice = Math.round(parseFloat(selectedDevice.purchase_cost || 0));
        let finalPrice = origPrice;
        let discountAmt = 0;

        if (discountMode === 'percent') {
            discountAmt = Math.round((origPrice * discountVal) / 100);
            finalPrice = Math.max(0, origPrice - discountAmt);
        } else if (discountMode === 'flat') {
            discountAmt = Math.round(discountVal);
            finalPrice = Math.max(0, origPrice - discountAmt);
        } else if (discountMode === 'custom') {
            finalPrice = Math.max(0, Math.round(discountVal));
            discountAmt = Math.max(0, origPrice - finalPrice);
        }

        document.getElementById('shFinalSalePrice').value = finalPrice;
        document.getElementById('lblShFinalTotal').innerText = `₹${finalPrice.toLocaleString('en-IN')}`;
        document.getElementById('shMobileDockTotal').innerText = `₹${finalPrice.toLocaleString('en-IN')}`;

        const discRow = document.getElementById('shDiscountSummaryRow');
        const discLbl = document.getElementById('lblShDiscountTotal');
        if (discountAmt > 0) {
            discLbl.innerText = `-₹${discountAmt.toLocaleString('en-IN')}`;
            discRow.style.display = 'flex';
        } else {
            discRow.style.display = 'none';
        }

        // Store Profit Calculation
        const profit = finalPrice - costPrice;
        const marginPct = finalPrice > 0 ? Math.round((profit / finalPrice) * 100) : 0;
        const profitLbl = document.getElementById('lblShProfitMargin');
        profitLbl.innerText = `${profit >= 0 ? '' : '-'}₹${Math.abs(profit).toLocaleString('en-IN')} (${marginPct}% margin)`;
        profitLbl.style.color = profit >= 0 ? '#059669' : '#DC2626';

        // Update Amount Paid if not manual
        const paidInput = document.getElementById('shAmountPaid');
        const mode = document.getElementById('shPaymentMode').value;
        if (mode !== 'credit_udhari' && (!paidInput.dataset.manual || paidInput.dataset.manual === "false")) {
            paidInput.value = finalPrice;
        }

        onShAmountPaidInput();
    }

    function onShAmountPaidInput() {
        const finalPrice = Math.round(parseFloat(document.getElementById('shFinalSalePrice').value) || 0);
        const paid = Math.round(parseFloat(document.getElementById('shAmountPaid').value) || 0);
        const due = Math.max(0, finalPrice - paid);

        document.getElementById('lblShDueAmount').innerText = `₹${due.toLocaleString('en-IN')}`;
        document.getElementById('shAmountPaid').dataset.manual = "true";
    }

    function setShFullPaid() {
        const finalPrice = Math.round(parseFloat(document.getElementById('shFinalSalePrice').value) || 0);
        document.getElementById('shAmountPaid').value = finalPrice;
        document.getElementById('shAmountPaid').dataset.manual = "false";
        onShAmountPaidInput();
    }

    function setShZeroPaid() {
        document.getElementById('shAmountPaid').value = 0;
        document.getElementById('shAmountPaid').dataset.manual = "true";
        onShAmountPaidInput();
    }

    function selectShPaymentMode(mode) {
        document.getElementById('shPaymentMode').value = mode;
        document.querySelectorAll('.payment-chip-btn').forEach(b => {
            b.classList.toggle('active', b.getAttribute('data-mode') === mode);
        });

        if (mode === 'credit_udhari') {
            setShZeroPaid();
        } else {
            setShFullPaid();
        }
    }

    function toggleShGst(isGst) {
        document.getElementById('shBillType').value = isGst ? 'gst' : 'non_gst';
    }

    // ── Catalog Filter & Search ──
    let activeGradeFilter = '';
    function filterShByGrade(grade) {
        activeGradeFilter = grade;
        document.querySelectorAll('.category-pill').forEach(p => {
            p.classList.toggle('active', p.getAttribute('data-grade') === grade);
        });
        filterShGrid();
    }

    function onShCatalogSearch(val) {
        filterShGrid();
    }

    function filterShGrid() {
        const q = (document.getElementById('shCatalogSearch')?.value || '').trim().toLowerCase();
        document.querySelectorAll('#shDevicesGrid .sh-device-card').forEach(card => {
            const grade = card.getAttribute('data-grade');
            const brand = (card.getAttribute('data-brand') || '').toLowerCase();
            const model = (card.getAttribute('data-model') || '').toLowerCase();
            const imei = (card.getAttribute('data-imei') || '').toLowerCase();
            const variant = (card.getAttribute('data-variant') || '').toLowerCase();

            const matchGrade = !activeGradeFilter || grade === activeGradeFilter;
            const matchSearch = !q || brand.includes(q) || model.includes(q) || imei.includes(q) || variant.includes(q);

            card.style.display = (matchGrade && matchSearch) ? 'flex' : 'none';
        });
    }

    function validateShSaleForm(form) {
        if (!selectedDevice) {
            alert('Please select a pre-owned phone from the catalog.');
            return false;
        }
        const phone = document.getElementById('shCustomerPhone').value.trim();
        const name = document.getElementById('shCustomerName').value.trim();
        if (!phone || !name) {
            alert('Please enter both customer mobile number and name.');
            return false;
        }

        const btnD = document.getElementById('btnShSubmitDesktop');
        const btnM = document.getElementById('btnShSubmitMobile');
        if (btnD) {
            btnD.disabled = true;
            btnD.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Recording Pre-Owned Sale...';
        }
        if (btnM) {
            btnM.disabled = true;
            btnM.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Processing...';
        }
        return true;
    }

    function strSlug(str) {
        return (str || '').toLowerCase().replace(/[\s\+]+/g, '_');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const preselectId = "{{ request('device_id') }}";
        if (preselectId) {
            const card = document.getElementById('shCard_' + preselectId);
            if (card) {
                card.click();
                card.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
</script>
@endpush
