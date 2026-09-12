@extends('mobileshop.layout')

@php
    $isCoverGlass = in_array(strtolower($presetCategory ?? ''), ['back_cover', 'cover', 'tempered', 'tempered_glass']);
    $pageHeading = $isCoverGlass ? 'Sell Back Cover & Tempered' : 'Sell Accessories';
@endphp

@section('title', $pageHeading . ' — Maurya Mobile')
@section('page-title', $pageHeading)

@section('page-actions')
    <a href="{{ route('mobileshop.sales') }}" class="btn btn-outline btn-sm" style="display:inline-flex; align-items:center; gap:6px;">
        <i data-lucide="arrow-left" style="width:14px;height:14px;"></i> Back to Sales Hub
    </a>
@endsection

@push('styles')
<style>
    /* ── Native Mobile App Form Container ── */
    .app-pos-container {
        max-width: 680px;
        margin: 0 auto;
    }

    /* ── Top App Bar Navigation ── */
    .app-top-header {
        background: #0F172A;
        color: #FFFFFF;
        border-radius: 16px;
        padding: 16px 20px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.25);
    }
    .app-top-title {
        font-size: 16px;
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: 8px;
        color: #FFFFFF;
    }
    .app-top-sub {
        font-size: 12px;
        color: #94A3B8;
        margin-top: 2px;
    }
    .app-cart-pill-badge {
        background: #2563EB;
        color: #FFFFFF;
        font-size: 12px;
        font-weight: 700;
        padding: 6px 12px;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        cursor: pointer;
        transition: transform 0.15s ease;
    }
    .app-cart-pill-badge:hover {
        transform: scale(1.04);
    }

    /* ── Mobile Form Cards ── */
    .app-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        padding: 18px 20px;
        margin-bottom: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
    }
    .app-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 14px;
        padding-bottom: 10px;
        border-bottom: 1px solid #F1F5F9;
    }
    .app-card-title {
        font-size: 13px;
        font-weight: 800;
        color: #0F172A;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* ── Large Mobile Touch Inputs ── */
    .app-input-label {
        font-size: 12px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 6px;
        display: block;
    }
    .app-input-text {
        width: 100%;
        height: 46px;
        font-size: 15px;
        font-weight: 600;
        color: #0F172A;
        background: #F8FAFC;
        border: 1.5px solid #CBD5E1;
        border-radius: 10px;
        padding: 0 14px;
        transition: all 0.2s ease;
        outline: none;
    }
    .app-input-text:focus {
        background: #FFFFFF;
        border-color: #2563EB;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }

    /* ── Category Filter Pills (Horizontal Scroll) ── */
    .category-pills-row {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        padding-bottom: 6px;
        margin-bottom: 14px;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    .category-pills-row::-webkit-scrollbar {
        display: none;
    }
    .category-pill {
        white-space: nowrap;
        font-size: 12px;
        font-weight: 700;
        padding: 7px 14px;
        border-radius: 9999px;
        border: 1.5px solid #E2E8F0;
        background: #F8FAFC;
        color: #475569;
        cursor: pointer;
        transition: all 0.15s ease;
        user-select: none;
    }
    .category-pill.active {
        background: #2563EB;
        border-color: #2563EB;
        color: #FFFFFF;
        box-shadow: 0 4px 10px rgba(37, 99, 235, 0.25);
    }

    /* ── Live Search Dropdown ── */
    .app-search-dropdown {
        position: absolute;
        left: 0;
        right: 0;
        top: calc(100% + 4px);
        max-height: 260px;
        overflow-y: auto;
        background: #FFFFFF;
        border: 1.5px solid #CBD5E1;
        border-radius: 12px;
        box-shadow: 0 16px 32px -4px rgba(0, 0, 0, 0.15);
        z-index: 1000;
        padding: 4px;
    }
    .search-result-row {
        padding: 10px 14px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        transition: background 0.15s ease;
        gap: 10px;
        border-bottom: 1px solid #F1F5F9;
    }
    .search-result-row:last-child {
        border-bottom: none;
    }
    .search-result-row:hover, .search-result-row.selected {
        background: #EFF6FF;
    }

    /* ── Cart Items App Tile ── */
    .app-cart-item-card {
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 12px 14px;
        margin-bottom: 10px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        transition: all 0.2s ease;
    }
    .app-cart-item-card:hover {
        border-color: #CBD5E1;
        box-shadow: 0 2px 6px rgba(0,0,0,0.04);
    }
    .app-cart-item-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 8px;
    }
    .app-cart-item-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        border-top: 1px dashed #E2E8F0;
        padding-top: 8px;
    }

    /* ── Touch Stepper Button ── */
    .touch-step-btn {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        border: 1px solid #CBD5E1;
        background: #FFFFFF;
        color: #0F172A;
        font-size: 16px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        user-select: none;
        transition: all 0.15s ease;
    }
    .touch-step-btn:hover {
        background: #F1F5F9;
        border-color: #94A3B8;
    }
    .touch-step-btn:active {
        transform: scale(0.92);
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
        border-color: #2563EB;
        background: #EFF6FF;
        color: #1D4ED8;
        box-shadow: 0 2px 6px rgba(37, 99, 235, 0.15);
    }

    /* ── Bottom Nav & Sticky Checkout Dock ── */
    /* Ensure the mobile bottom navigation bar is permanently fixed and never hides */
    .mobile-bottom-nav {
        transform: none !important;
        opacity: 1 !important;
        pointer-events: auto !important;
        position: fixed !important;
        bottom: 0 !important;
        z-index: 1000 !important;
    }

    /* Sticky Bottom Checkout Dock: Sits directly above the fixed bottom nav on mobile */
    .app-sticky-dock {
        position: fixed;
        left: 0;
        right: 0;
        background: #FFFFFF;
        border-top: 1.5px solid #E2E8F0;
        box-shadow: 0 -8px 24px rgba(15, 23, 42, 0.08);
        padding: 12px 16px;
        z-index: 990;
    }

    @media (max-width: 1023px) {
        .app-sticky-dock {
            bottom: calc(var(--bottom-nav-height, 58px) + env(safe-area-inset-bottom, 0px)) !important;
            border-bottom: 1px solid #E2E8F0;
        }
        .app-pos-container {
            padding-bottom: calc(var(--bottom-nav-height, 58px) + env(safe-area-inset-bottom, 0px) + 120px) !important;
        }
    }

    @media (min-width: 1024px) {
        .app-sticky-dock {
            bottom: 0 !important;
        }
        .app-pos-container {
            padding-bottom: 100px !important;
        }
    }
    .app-sticky-dock-inner {
        max-width: 680px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
    }
    .app-btn-checkout {
        flex: 1;
        height: 48px;
        background: #16A34A;
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
        box-shadow: 0 4px 14px rgba(22, 163, 74, 0.35);
        transition: all 0.15s ease;
    }
    .app-btn-checkout:disabled {
        background: #94A3B8 !important;
        box-shadow: none !important;
        cursor: not-allowed !important;
    }
    .app-btn-checkout:not(:disabled):hover {
        background: #15803D;
        transform: translateY(-1px);
    }

    /* iOS Style Toggle */
    .toggle-switch-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 14px;
        background: #F8FAFC;
        border: 1.5px solid #E2E8F0;
        border-radius: 10px;
        cursor: pointer;
    }
</style>
@endpush

@section('content')
<div class="app-pos-container">

    <!-- Main Sale Form -->
    <form action="{{ route('mobileshop.accessories.sale') }}" method="POST" id="accSaleForm" onsubmit="return validateAndSubmitPosSale(this);">
        @csrf
        <input type="hidden" name="idempotency_key" value="{{ \Illuminate\Support\Str::uuid() }}">
        <input type="hidden" name="bill_type" id="accBillType" value="non_gst">

        <!-- ── 2. Customer Information Card ── -->
        <div class="app-card">
            <div class="app-card-header">
                <div class="app-card-title">
                    <i data-lucide="user" style="width:16px;height:16px; color:#2563EB;"></i> Customer Details
                </div>
                <span id="khataIndicator" style="display:none; font-size:11px; font-weight:800; color:#DC2626; background:#FEF2F2; padding:3px 8px; border-radius:6px; border:1px solid #FECACA;">
                    ⚠️ Khata Due: ₹<span id="khataAmountText">0</span>
                </span>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                <!-- Customer Mobile Phone -->
                <div>
                    <label class="app-input-label">Customer Mobile *</label>
                    <input type="tel" name="customer_phone" id="accCustomerPhone" list="accCustomerList" placeholder="10-digit Mobile Number" required class="app-input-text" autocomplete="off" oninput="handlePhoneInput(this.value)">
                    <datalist id="accCustomerList">
                        @foreach($customers ?? [] as $c)
                            <option value="{{ $c->phone }}" data-name="{{ $c->name }}" data-balance="{{ $c->udhari_balance ?? 0 }}" data-gstin="{{ $c->gstin ?? '' }}">
                                {{ $c->name }} (Pending Khata: ₹{{ number_format($c->udhari_balance ?? 0, 2) }})
                            </option>
                        @endforeach
                    </datalist>
                </div>

                <!-- Customer Full Name -->
                <div>
                    <label class="app-input-label">Customer Name *</label>
                    <input type="text" name="customer_name" id="accCustomerName" placeholder="Full Name" required class="app-input-text">
                </div>
            </div>

            <!-- GST Bill Toggle -->
            <label class="toggle-switch-card">
                <div>
                    <span style="font-size:13px; font-weight:700; color:#0F172A;">Make GST Bill (18% Tax Invoice)</span>
                    <p style="font-size:11px; color:#64748B; margin:2px 0 0 0;">Toggle on for B2B GST tax invoice (Standard estimate by default)</p>
                </div>
                <input type="checkbox" name="is_gst" id="accIsGstCheckbox" value="1" style="width:20px; height:20px; accent-color:#2563EB; cursor:pointer;" onchange="toggleGstBilling(this.checked)">
            </label>
        </div>

        <!-- ── 3. Product Picker & Fast Search Card ── -->
        <div class="app-card">
            <div class="app-card-header">
                <div class="app-card-title">
                    <i data-lucide="package" style="width:16px;height:16px; color:#059669;"></i> Select Product to Bill
                </div>
                <span style="font-size:11px; font-weight:700; color:#64748B;">
                    {{ count($partsList ?? []) }} In-Stock Products
                </span>
            </div>

            <!-- Horizontal Scrollable Category Chips -->
            <div class="category-pills-row" id="categoryPillContainer">
                <div class="category-pill active" data-slug="" onclick="selectCategoryFilter('')">All Items</div>
                @php
                    $allCatsInStore = collect($partsList ?? [])->pluck('category')->filter()->unique()->values();
                @endphp
                @foreach($allCatsInStore as $cSlug)
                    <div class="category-pill {{ ($presetCategory ?? '') === $cSlug ? 'active' : '' }}" data-slug="{{ $cSlug }}" onclick="selectCategoryFilter('{{ $cSlug }}')">
                        {{ ucwords(str_replace('_', ' ', $cSlug)) }}
                    </div>
                @endforeach
            </div>

            <!-- Product Search Input with Live Dropdown -->
            <div style="position:relative;" id="productSearchWrapper">
                <label class="app-input-label">Search Product by Name, Model or Brand</label>
                <div style="position:relative;">
                    <input type="text" id="accSearchInput" placeholder="e.g. iPhone 15 glass, Type-C cable, AMOLED folder..." class="app-input-text" style="padding-left:38px; padding-right:38px;" autocomplete="off" oninput="onLiveSearch(this.value)" onfocus="onLiveSearch(this.value)">
                    <i data-lucide="search" style="position:absolute; left:12px; top:14px; width:18px; height:18px; color:#94A3B8;"></i>
                    <button type="button" id="btnClearSearch" onclick="clearSearch()" style="display:none; position:absolute; right:10px; top:12px; background:none; border:none; color:#94A3B8; font-size:16px; cursor:pointer;">✕</button>
                </div>

                <!-- Dropdown Results -->
                <div id="accSearchDropdown" class="app-search-dropdown" style="display:none;"></div>
            </div>

            <!-- Quick Add Stepper Toolbar -->
            <div id="quickAddDock" style="display:none; background:#EFF6FF; border:1px solid #BFDBFE; border-radius:12px; padding:12px 14px; margin-top:12px; align-items:center; justify-content:space-between; gap:10px;">
                <div style="flex:1; min-width:0;">
                    <div id="qaItemName" style="font-size:13px; font-weight:800; color:#1E3A8A; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">Item Selected</div>
                    <div style="font-size:12px; color:#3B82F6; font-weight:700;">Rate: ₹<span id="qaItemPrice">0</span> • <span id="qaItemStock" style="color:#059669;">In Stock: 0</span></div>
                </div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="display:flex; align-items:center; gap:4px; background:#FFFFFF; border:1px solid #CBD5E1; border-radius:8px; padding:2px 4px;">
                        <button type="button" class="touch-step-btn" onclick="stepQaQty(-1)" style="width:28px; height:28px; font-size:14px;">−</button>
                        <span id="qaQtyDisplay" style="font-size:14px; font-weight:800; min-width:24px; text-align:center;">1</span>
                        <button type="button" class="touch-step-btn" onclick="stepQaQty(1)" style="width:28px; height:28px; font-size:14px;">+</button>
                    </div>
                    <button type="button" onclick="confirmAddSelectedToCart()" style="background:#2563EB; color:#FFFFFF; border:none; border-radius:8px; padding:8px 16px; font-weight:800; font-size:13px; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                        <i data-lucide="plus" style="width:14px;height:14px;"></i> Add to Bill
                    </button>
                </div>
            </div>
        </div>

        <!-- ── 4. Cart / Billed Items Card ── -->
        <div class="app-card" id="cartSectionCard">
            <div class="app-card-header">
                <div class="app-card-title">
                    <i data-lucide="shopping-bag" style="width:16px;height:16px; color:#D97706;"></i> Billed Items
                    <span id="cartCountBadge" style="background:#E2E8F0; color:#334155; font-size:11px; font-weight:800; padding:2px 7px; border-radius:9999px; margin-left:4px;">0</span>
                </div>
                <button type="button" onclick="clearEntireCart()" id="btnClearCart" style="display:none; background:none; border:none; color:#DC2626; font-size:12px; font-weight:700; cursor:pointer;">
                    Clear All
                </button>
            </div>

            <div id="cartItemsList">
                <!-- Empty Cart State -->
                <div id="emptyCartMessage" style="text-align:center; padding:32px 16px; color:#64748B;">
                    <i data-lucide="shopping-cart" style="width:36px; height:36px; color:#CBD5E1; margin:0 auto 10px; display:block;"></i>
                    <div style="font-size:14px; font-weight:700; color:#475569;">Your bill is currently empty</div>
                    <p style="font-size:12px; color:#94A3B8; margin:4px 0 0 0;">Tap or search products above to add items to this sale</p>
                </div>
            </div>
        </div>

        <!-- ── 5. Payment & Settlement Card ── -->
        <div class="app-card">
            <div class="app-card-header">
                <div class="app-card-title">
                    <i data-lucide="credit-card" style="width:16px;height:16px; color:#7C3AED;"></i> Payment & Settlement
                </div>
            </div>

            <!-- Payment Mode Selector -->
            <label class="app-input-label">Select Payment Method</label>
            <div class="payment-chip-grid">
                <div class="payment-chip-btn active" data-mode="cash" onclick="selectPaymentMode('cash')">💵 Cash</div>
                <div class="payment-chip-btn" data-mode="upi" onclick="selectPaymentMode('upi')">📱 UPI / QR</div>
                <div class="payment-chip-btn" data-mode="card" onclick="selectPaymentMode('card')">💳 Card</div>
                <div class="payment-chip-btn" data-mode="credit_udhari" onclick="selectPaymentMode('credit_udhari')">📒 Full Khata</div>
                <div class="payment-chip-btn" data-mode="split" onclick="selectPaymentMode('split')">⚖️ Split</div>
            </div>
            <input type="hidden" name="payment_mode" id="accPaymentMode" value="cash">

            <!-- Amount Paid Input & Fast Chips -->
            <div style="margin-bottom: 14px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                    <label class="app-input-label" style="margin-bottom:0;">Amount Paid Now (₹) *</label>
                    <div style="display:flex; gap:6px;">
                        <button type="button" onclick="setFullPayment()" style="font-size:11px; padding:3px 10px; border-radius:6px; font-weight:800; color:#16A34A; border:1px solid #BBF7D0; background:#F0FDF4; cursor:pointer;">
                            Full Paid
                        </button>
                        <button type="button" onclick="setZeroPayment()" style="font-size:11px; padding:3px 10px; border-radius:6px; font-weight:800; color:#DC2626; border:1px solid #FECDD3; background:#FFF1F2; cursor:pointer;">
                            Udhari (₹0)
                        </button>
                    </div>
                </div>
                <input type="number" step="0.01" name="amount_paid" id="accAmountPaid" required placeholder="0.00" class="app-input-text" style="font-size:20px; font-weight:900; color:#16A34A;" oninput="onAmountPaidManualInput()">
            </div>

            <!-- Calculation Summary -->
            <div style="background:#F8FAFC; border:1.5px solid #E2E8F0; border-radius:12px; padding:14px 16px;">
                <div style="display:flex; justify-content:space-between; font-size:13px; color:#475569; margin-bottom:6px;">
                    <span>Items Total:</span>
                    <strong id="lblItemsTotal" style="color:#0F172A;">₹0.00</strong>
                </div>
                <div id="gstSummaryRow" style="display:none; justify-content:space-between; font-size:13px; color:#475569; margin-bottom:6px;">
                    <span>GST (18% Included):</span>
                    <strong id="lblGstAmount" style="color:#0F172A;">₹0.00</strong>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:14px; font-weight:800; color:#0F172A; margin-bottom:6px;">
                    <span>Bill Grand Total:</span>
                    <strong id="lblGrandTotal" style="font-size:16px; color:#2563EB;">₹0.00</strong>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:13px; font-weight:700; color:#16A34A; margin-bottom:6px;">
                    <span>Paid Now:</span>
                    <strong id="lblPaidAmount">₹0.00</strong>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:13px; font-weight:800; border-top:1px dashed #CBD5E1; padding-top:8px;">
                    <span style="color:#475569;">Added to Khata (Remaining Due):</span>
                    <strong id="lblDueAmount" style="color:#DC2626; font-size:15px;">₹0.00</strong>
                </div>
            </div>
        </div>

        <!-- ── 6. Fixed Bottom App Checkout Dock ── -->
        <div class="app-sticky-dock">
            <div class="app-sticky-dock-inner">
                <div>
                    <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase;">Grand Total</div>
                    <div style="font-size:20px; font-weight:900; color:#0F172A;" id="dockGrandTotal">₹0.00</div>
                </div>
                <button type="submit" id="btnSubmitPosSale" class="app-btn-checkout" disabled>
                    <i data-lucide="check-circle-2" style="width:18px;height:18px;"></i>
                    <span>Complete Sale & Bill</span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // ── Raw Inventory Data from Controller ──
    const ALL_PARTS = @json($partsList ?? []);
    let activeCategoryFilter = "{{ $presetCategory ?? '' }}";
    let cart = [];
    let selectedSearchItem = null;
    let selectedQaQty = 1;

    // ── Initialize Lucide Icons ──
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
        if (activeCategoryFilter) {
            selectCategoryFilter(activeCategoryFilter);
        }
    });

    // ── Customer Autocomplete & Khata Balance Lookup ──
    function handlePhoneInput(phoneVal) {
        const clean = phoneVal.trim();
        const datalist = document.getElementById('accCustomerList');
        if (!datalist) return;

        const options = datalist.querySelectorAll('option');
        for (let opt of options) {
            if (opt.value === clean) {
                const name = opt.getAttribute('data-name');
                const bal = parseFloat(opt.getAttribute('data-balance') || 0);
                if (name) {
                    document.getElementById('accCustomerName').value = name;
                }
                const khataIndicator = document.getElementById('khataIndicator');
                const khataAmountText = document.getElementById('khataAmountText');
                if (bal > 0) {
                    khataAmountText.innerText = bal.toFixed(2);
                    khataIndicator.style.display = 'inline-flex';
                } else {
                    khataIndicator.style.display = 'none';
                }
                return;
            }
        }
        document.getElementById('khataIndicator').style.display = 'none';
    }

    // ── Category Filter Pills ──
    function selectCategoryFilter(slug) {
        activeCategoryFilter = slug;
        document.querySelectorAll('.category-pill').forEach(pill => {
            pill.classList.toggle('active', pill.getAttribute('data-slug') === slug);
        });
        const searchInput = document.getElementById('accSearchInput');
        onLiveSearch(searchInput.value);
    }

    // ── Live Product Search ──
    function onLiveSearch(query) {
        const q = (query || '').trim().toLowerCase();
        const dropdown = document.getElementById('accSearchDropdown');
        const clearBtn = document.getElementById('btnClearSearch');

        clearBtn.style.display = q.length > 0 ? 'block' : 'none';

        let filtered = ALL_PARTS.filter(p => {
            const matchesCat = !activeCategoryFilter || p.category === activeCategoryFilter;
            if (!matchesCat) return false;
            if (!q) return true;
            const nameMatch = (p.name || '').toLowerCase().includes(q);
            const modelMatch = (p.compatible_model || '').toLowerCase().includes(q);
            const brandMatch = (p.brand || '').toLowerCase().includes(q);
            return nameMatch || modelMatch || brandMatch;
        }).slice(0, 20);

        if (filtered.length === 0) {
            dropdown.innerHTML = `<div style="padding:16px; text-align:center; color:#64748B; font-size:13px;">No in-stock products found ${q ? `matching "${q}"` : ''} in this category.</div>`;
            dropdown.style.display = 'block';
            return;
        }

        let html = '';
        filtered.forEach(item => {
            const price = parseFloat(item.selling_price || 0).toFixed(2);
            const modelText = item.compatible_model ? ` (${item.compatible_model})` : '';
            html += `
                <div class="search-result-row" onclick="onPickProductFromSearch(${item.id})">
                    <div style="flex:1; min-width:0;">
                        <div style="font-weight:700; font-size:13px; color:#0F172A; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                            ${escapeHtml(item.name)}${escapeHtml(modelText)}
                        </div>
                        <div style="font-size:11px; color:#64748B; margin-top:1px;">
                            ${escapeHtml(item.category || 'Accessory')} • <span style="color:#059669; font-weight:700;">● ${item.stock_qty} in stock</span>
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:14px; font-weight:900; color:#2563EB;">₹${price}</div>
                        <button type="button" style="background:#EFF6FF; border:1px solid #BFDBFE; color:#1D4ED8; font-size:11px; font-weight:800; padding:2px 8px; border-radius:6px; margin-top:2px;">+ Add</button>
                    </div>
                </div>
            `;
        });

        dropdown.innerHTML = html;
        dropdown.style.display = 'block';
    }

    function clearSearch() {
        const input = document.getElementById('accSearchInput');
        input.value = '';
        document.getElementById('btnClearSearch').style.display = 'none';
        document.getElementById('accSearchDropdown').style.display = 'none';
        document.getElementById('quickAddDock').style.display = 'none';
    }

    // Close dropdown on click outside
    document.addEventListener('click', function(e) {
        const wrapper = document.getElementById('productSearchWrapper');
        if (wrapper && !wrapper.contains(e.target)) {
            document.getElementById('accSearchDropdown').style.display = 'none';
        }
    });

    // ── Pick Product From Search ──
    function onPickProductFromSearch(partId) {
        const item = ALL_PARTS.find(p => p.id === partId);
        if (!item) return;

        selectedSearchItem = item;
        selectedQaQty = 1;

        document.getElementById('accSearchDropdown').style.display = 'none';
        document.getElementById('accSearchInput').value = item.name + (item.compatible_model ? ` (${item.compatible_model})` : '');

        document.getElementById('qaItemName').innerText = item.name + (item.compatible_model ? ` (${item.compatible_model})` : '');
        document.getElementById('qaItemPrice').innerText = parseFloat(item.selling_price || 0).toFixed(2);
        document.getElementById('qaItemStock').innerText = `In Stock: ${item.stock_qty}`;
        document.getElementById('qaQtyDisplay').innerText = '1';
        document.getElementById('quickAddDock').style.display = 'flex';

        // Auto-add directly if user wants 1 unit
        confirmAddSelectedToCart();
    }

    function stepQaQty(delta) {
        if (!selectedSearchItem) return;
        selectedQaQty = Math.max(1, Math.min(selectedSearchItem.stock_qty, selectedQaQty + delta));
        document.getElementById('qaQtyDisplay').innerText = selectedQaQty;
    }

    function confirmAddSelectedToCart() {
        if (!selectedSearchItem) return;

        // Check if item already exists in cart
        const existing = cart.find(c => c.part_id === selectedSearchItem.id);
        if (existing) {
            const newQty = existing.quantity + selectedQaQty;
            if (newQty > selectedSearchItem.stock_qty) {
                alert(`Cannot add more than available stock (${selectedSearchItem.stock_qty}) for ${selectedSearchItem.name}.`);
                return;
            }
            existing.quantity = newQty;
        } else {
            cart.push({
                part_id: selectedSearchItem.id,
                name: selectedSearchItem.name,
                model: selectedSearchItem.compatible_model || '',
                category: selectedSearchItem.category || '',
                unit_price: parseFloat(selectedSearchItem.selling_price || 0),
                quantity: selectedQaQty,
                max_stock: selectedSearchItem.stock_qty
            });
        }

        renderCart();
        clearSearch();
    }

    // ── Render Cart Items ──
    function renderCart() {
        const container = document.getElementById('cartItemsList');
        const countBadge = document.getElementById('cartCountBadge');
        const topBadge = document.getElementById('topCartBadge');
        const clearBtn = document.getElementById('btnClearCart');
        const checkoutBtn = document.getElementById('btnSubmitPosSale');

        const totalItemsCount = cart.reduce((sum, item) => sum + item.quantity, 0);
        countBadge.innerText = totalItemsCount;
        if (topBadge) topBadge.innerText = `${totalItemsCount} Items`;

        if (cart.length === 0) {
            container.innerHTML = `
                <div id="emptyCartMessage" style="text-align:center; padding:32px 16px; color:#64748B;">
                    <i data-lucide="shopping-cart" style="width:36px; height:36px; color:#CBD5E1; margin:0 auto 10px; display:block;"></i>
                    <div style="font-size:14px; font-weight:700; color:#475569;">Your bill is currently empty</div>
                    <p style="font-size:12px; color:#94A3B8; margin:4px 0 0 0;">Tap or search products above to add items to this sale</p>
                </div>
            `;
            clearBtn.style.display = 'none';
            checkoutBtn.disabled = true;
            updateCalculations();
            if (typeof lucide !== 'undefined') lucide.createIcons();
            return;
        }

        clearBtn.style.display = 'inline-block';
        checkoutBtn.disabled = false;

        let html = '';
        cart.forEach((item, index) => {
            const itemTotal = (item.quantity * item.unit_price).toFixed(2);
            html += `
                <div class="app-cart-item-card">
                    <input type="hidden" name="items[${index}][part_id]" value="${item.part_id}">
                    <input type="hidden" name="items[${index}][quantity]" value="${item.quantity}" id="cartQtyInput_${index}">
                    
                    <div class="app-cart-item-header">
                        <div style="flex:1; min-width:0;">
                            <div style="font-size:13px; font-weight:800; color:#0F172A; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                ${escapeHtml(item.name)}${item.model ? ` (${escapeHtml(item.model)})` : ''}
                            </div>
                            <span style="font-size:11px; color:#64748B; font-weight:600;">${escapeHtml(item.category)} • Stock: ${item.max_stock}</span>
                        </div>
                        <button type="button" onclick="removeCartItem(${index})" style="background:#FEE2E2; border:none; color:#DC2626; border-radius:6px; width:26px; height:26px; display:flex; align-items:center; justify-content:center; cursor:pointer; font-weight:800; font-size:13px;">✕</button>
                    </div>

                    <div class="app-cart-item-footer">
                        <!-- Unit Rate (Editable) -->
                        <div style="display:flex; align-items:center; gap:6px;">
                            <span style="font-size:11px; font-weight:700; color:#64748B;">Rate: ₹</span>
                            <input type="number" step="0.01" name="items[${index}][unit_price]" value="${item.unit_price.toFixed(2)}" class="app-input-text" style="width:84px; height:32px; font-size:13px; font-weight:700; padding:0 6px;" onchange="updateCartItemRate(${index}, this.value)">
                        </div>

                        <!-- Touch Stepper -->
                        <div style="display:flex; align-items:center; gap:6px;">
                            <button type="button" class="touch-step-btn" onclick="updateCartItemQty(${index}, -1)">−</button>
                            <span style="font-size:14px; font-weight:800; min-width:24px; text-align:center;">${item.quantity}</span>
                            <button type="button" class="touch-step-btn" onclick="updateCartItemQty(${index}, 1)">+</button>
                        </div>

                        <!-- Item Total -->
                        <div style="font-size:15px; font-weight:900; color:#2563EB;">
                            ₹${itemTotal}
                        </div>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
        updateCalculations();
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function updateCartItemQty(index, delta) {
        if (!cart[index]) return;
        const newQty = cart[index].quantity + delta;
        if (newQty <= 0) {
            removeCartItem(index);
            return;
        }
        if (newQty > cart[index].max_stock) {
            alert(`Only ${cart[index].max_stock} units available in stock.`);
            return;
        }
        cart[index].quantity = newQty;
        renderCart();
    }

    function updateCartItemRate(index, newRate) {
        if (!cart[index]) return;
        const rate = parseFloat(newRate) || 0;
        cart[index].unit_price = Math.max(0, rate);
        renderCart();
    }

    function removeCartItem(index) {
        cart.splice(index, 1);
        renderCart();
    }

    function clearEntireCart() {
        if (cart.length === 0) return;
        if (confirm('Clear all items from this bill?')) {
            cart = [];
            renderCart();
        }
    }

    // ── Payment Mode & Calculation Updates ──
    function selectPaymentMode(mode) {
        document.getElementById('accPaymentMode').value = mode;
        document.querySelectorAll('.payment-chip-btn').forEach(btn => {
            btn.classList.toggle('active', btn.getAttribute('data-mode') === mode);
        });

        if (mode === 'credit_udhari') {
            setZeroPayment();
        } else {
            setFullPayment();
        }
    }

    function toggleGstBilling(isGst) {
        document.getElementById('accBillType').value = isGst ? 'gst' : 'non_gst';
        document.getElementById('gstSummaryRow').style.display = isGst ? 'flex' : 'none';
        updateCalculations();
    }

    function getGrandTotal() {
        return cart.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);
    }

    function updateCalculations() {
        const total = getGrandTotal();
        const isGst = document.getElementById('accIsGstCheckbox').checked;

        document.getElementById('lblItemsTotal').innerText = `₹${total.toFixed(2)}`;
        if (isGst) {
            const gst = (total * 0.18 / 1.18); // Inclusive 18%
            document.getElementById('lblGstAmount').innerText = `₹${gst.toFixed(2)}`;
        }
        document.getElementById('lblGrandTotal').innerText = `₹${total.toFixed(2)}`;
        document.getElementById('dockGrandTotal').innerText = `₹${total.toFixed(2)}`;

        // If amount paid is empty or equals previous total, auto-update
        const paidInput = document.getElementById('accAmountPaid');
        const mode = document.getElementById('accPaymentMode').value;
        if (mode !== 'credit_udhari' && (!paidInput.dataset.manual || paidInput.dataset.manual === "false")) {
            paidInput.value = total.toFixed(2);
        }

        onAmountPaidManualInput();
    }

    function onAmountPaidManualInput() {
        const total = getGrandTotal();
        const paid = parseFloat(document.getElementById('accAmountPaid').value) || 0;
        const due = Math.max(0, total - paid);

        document.getElementById('lblPaidAmount').innerText = `₹${paid.toFixed(2)}`;
        document.getElementById('lblDueAmount').innerText = `₹${due.toFixed(2)}`;
        document.getElementById('accAmountPaid').dataset.manual = "true";
    }

    function setFullPayment() {
        const total = getGrandTotal();
        document.getElementById('accAmountPaid').value = total.toFixed(2);
        document.getElementById('accAmountPaid').dataset.manual = "false";
        onAmountPaidManualInput();
    }

    function setZeroPayment() {
        document.getElementById('accAmountPaid').value = "0.00";
        document.getElementById('accAmountPaid').dataset.manual = "true";
        onAmountPaidManualInput();
    }

    function scrollToCartSection() {
        const el = document.getElementById('cartSectionCard');
        if (el) el.scrollIntoView({ behavior: 'smooth' });
    }

    function validateAndSubmitPosSale(form) {
        if (cart.length === 0) {
            alert('Please add at least one item to the bill before completing sale.');
            return false;
        }

        const phone = document.getElementById('accCustomerPhone').value.trim();
        const name = document.getElementById('accCustomerName').value.trim();
        if (!phone || !name) {
            alert('Please enter both customer mobile number and name.');
            return false;
        }

        const btn = document.getElementById('btnSubmitPosSale');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Recording Sale...';
        return true;
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
</script>
@endpush
