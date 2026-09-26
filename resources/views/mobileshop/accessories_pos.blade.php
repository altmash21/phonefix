@extends('mobileshop.layout')

@section('title', 'Counter POS Sale — PhoneFix Azamgarh')
@section('page-title', 'Counter POS Billing')

@section('page-actions')
    <a href="{{ route('mobileshop.sales') }}" class="btn btn-outline btn-sm" style="display:inline-flex; align-items:center; gap:6px;">
        <i data-lucide="arrow-left" style="width:14px;height:14px;"></i> Back to Sales Hub
    </a>
@endsection

@push('styles')
<style>
    /* ── Responsive POS Container (Mobile & PC Desktop) ── */
    .app-pos-container {
        max-width: 680px;
        margin: 0 auto;
    }
    @media (min-width: 1024px) {
        .app-pos-container {
            max-width: 1400px;
            margin: 0 auto;
        }
        .pos-desktop-layout {
            display: grid;
            grid-template-columns: 1fr 440px;
            gap: 20px;
            align-items: flex-start;
        }
        .pos-col-left {
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .pos-col-right {
            position: sticky;
            top: 16px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
    }
    @media (max-width: 1023px) {
        .pos-desktop-layout {
            display: block;
        }
    }
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
        font-size: 10px;
        font-weight: 700;
        color: #64748B;
        padding: 2px 6px;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.15s ease;
        line-height: 1.2;
    }
    .disc-pill-btn:hover {
        color: #1E293B;
    }
    .disc-pill-btn.active {
        background: #2563EB;
        color: #FFFFFF;
        box-shadow: 0 1px 2px rgba(37, 99, 235, 0.3);
    }
    .pos-product-card {
        background: #FFFFFF;
        border: 1.5px solid #E2E8F0;
        border-radius: 10px;
        padding: 10px 12px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .pos-product-card:hover {
        border-color: #2563EB;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.12);
        transform: translateY(-1px);
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

    /* ── Whole Invoice Discount Pills ── */
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
        font-size: 11px;
        font-weight: 700;
        color: #64748B;
        padding: 4px 9px;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .disc-pill-btn:hover {
        color: #0F172A;
    }
    .disc-pill-btn.active {
        background: #0F172A;
        color: #FFFFFF;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.2);
    }

    /* ── Live Search Dropdown ── */
    .app-search-dropdown {
        position: absolute;
        left: 0;
        right: 0;
        top: calc(100% + 6px);
        max-height: 380px;
        overflow-y: auto;
        background: #FFFFFF;
        border: 2px solid #3B82F6;
        border-radius: 12px;
        box-shadow: 0 20px 40px -8px rgba(15, 23, 42, 0.22);
        z-index: 1000;
        padding: 6px;
    }
    .search-result-row {
        padding: 10px 14px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        transition: all 0.12s ease;
        gap: 12px;
        border-bottom: 1px solid #F1F5F9;
    }
    .search-result-row:last-child {
        border-bottom: none;
    }
    .search-result-row:hover, .search-result-row.selected {
        background: #EFF6FF;
        border-color: #BFDBFE;
    }
    .search-result-row.selected {
        background: #DBEAFE !important;
        box-shadow: inset 0 0 0 2px #2563EB;
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
        .desktop-checkout-wrap {
            display: none !important;
        }
    }

    @media (min-width: 1024px) {
        .app-sticky-dock {
            display: none !important;
        }
        .app-pos-container {
            padding-bottom: 60px !important;
        }
        .desktop-checkout-wrap {
            display: block !important;
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

        <div class="pos-desktop-layout">

            <!-- ═══════════ LEFT COLUMN: CUSTOMER & PRODUCT CATALOG ═══════════ -->
            <div class="pos-col-left">

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
                                        {{ $c->name }} (Pending Khata: ₹{{ number_format($c->udhari_balance ?? 0, 0) }})
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

                <!-- ── 3. Product Picker & Fast Search Dropdown Card ── -->
                <div class="app-card" id="productPickerCard">
                    <div class="app-card-header" style="margin-bottom:12px;">
                        <div class="app-card-title">
                            <i data-lucide="search" style="width:16px;height:16px; color:#2563EB;"></i> Select Product to Bill
                        </div>
                        <span id="productTotalCountBadge" style="font-size:11px; font-weight:700; color:#2563EB; background:#EFF6FF; padding:3px 9px; border-radius:12px; border:1px solid #DBEAFE;">
                            {{ count($partsList ?? []) }} In-Stock Products
                        </span>
                    </div>

                    <!-- Category Selector Dropdown & Filter Pills -->
                    <div style="margin-bottom:12px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                            <label class="app-input-label" style="margin:0;">Filter by Category</label>
                            <span style="font-size:11px; color:#64748B;">Active: <strong id="currentCategoryLabel" style="color:#0F172A;">All Categories</strong></span>
                        </div>
                        <div class="category-pills-row" id="categoryPillContainer">
                            <div class="category-pill active" data-slug="" onclick="selectCategoryFilter('')">All Categories</div>
                            @php
                                $allCatsInStore = collect($partsList ?? [])->pluck('category')->filter()->unique()->values();
                            @endphp
                            @foreach($allCatsInStore as $cSlug)
                                <div class="category-pill {{ ($presetCategory ?? '') === $cSlug ? 'active' : '' }}" data-slug="{{ $cSlug }}" onclick="selectCategoryFilter('{{ $cSlug }}')">
                                    {{ ucwords(str_replace('_', ' ', $cSlug)) }}
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Product Search Input with Live Dropdown -->
                    <div style="position:relative;" id="productSearchWrapper">
                        <label class="app-input-label" style="display:flex; justify-content:space-between; align-items:center;">
                            <span>Search & Select Item</span>
                            <span style="font-size:11px; font-weight:500; color:#64748B;">Type name, model, brand, or category</span>
                        </label>
                        <div style="position:relative;">
                            <input type="text" id="accSearchInput" 
                                placeholder="Type to search e.g. iPhone 15 glass, Type-C cable, 20W charger..." 
                                class="app-input-text" 
                                style="padding-left:42px; padding-right:42px; height:44px; font-size:13.5px; font-weight:600; border-radius:10px; border:1.5px solid #CBD5E1;" 
                                autocomplete="off" 
                                oninput="onLiveSearch(this.value)" 
                                onfocus="onLiveSearch(this.value, true)"
                                onkeydown="handleSearchKeyNavigation(event)">
                            <i data-lucide="search" style="position:absolute; left:14px; top:13px; width:18px; height:18px; color:#64748B;"></i>
                            <button type="button" id="btnClearSearch" onclick="clearSearch()" style="display:none; position:absolute; right:12px; top:12px; background:#E2E8F0; border:none; color:#475569; width:20px; height:20px; border-radius:50%; font-size:12px; font-weight:bold; cursor:pointer; align-items:center; justify-content:center;">✕</button>
                        </div>

                        <!-- Dropdown Results -->
                        <div id="accSearchDropdown" class="app-search-dropdown" style="display:none;"></div>
                    </div>

                    <!-- Keyboard navigation & tips hint bar -->
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:10px; padding:8px 12px; background:#F8FAFC; border-radius:8px; border:1px solid #F1F5F9; font-size:11px; color:#64748B;">
                        <span style="display:inline-flex; align-items:center; gap:5px;">
                            <kbd style="background:#fff; border:1px solid #CBD5E1; border-radius:4px; padding:1px 5px; font-size:10px; font-family:monospace; font-weight:700;">↑</kbd>
                            <kbd style="background:#fff; border:1px solid #CBD5E1; border-radius:4px; padding:1px 5px; font-size:10px; font-family:monospace; font-weight:700;">↓</kbd>
                            Navigate &bull;
                            <kbd style="background:#fff; border:1px solid #CBD5E1; border-radius:4px; padding:1px 5px; font-size:10px; font-family:monospace; font-weight:700;">Enter</kbd>
                            Add to Bill &bull;
                            <kbd style="background:#fff; border:1px solid #CBD5E1; border-radius:4px; padding:1px 5px; font-size:10px; font-family:monospace; font-weight:700;">Esc</kbd>
                            Close
                        </span>
                        <span id="quickItemMatchCount" style="font-weight:700; color:#2563EB;">Click any item to add</span>
                    </div>

                    <!-- Instant Added Feedback Toast (Inline) -->
                    <div id="itemAddedNotice" style="display:none; margin-top:10px; background:#ECFDF5; border:1px solid #A7F3D0; color:#065F46; padding:8px 12px; border-radius:8px; font-size:12px; font-weight:700; align-items:center; gap:6px;">
                        <i data-lucide="check-circle" style="width:14px;height:14px; color:#10B981;"></i>
                        <span id="itemAddedNoticeText">Item added to bill</span>
                    </div>
                </div>

            </div>

            <!-- ═══════════ RIGHT COLUMN: CART & CHECKOUT ═══════════ -->
            <div class="pos-col-right">

                <!-- ── 4. Cart / Billed Items Card ── -->
                <div class="app-card" id="cartSectionCard" style="margin-bottom:0;">
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
                            <p style="font-size:12px; color:#94A3B8; margin:4px 0 0 0;">Tap or search products to add items</p>
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
                    <div class="payment-chip-grid" style="grid-template-columns: repeat(3, 1fr);">
                        <div class="payment-chip-btn active" data-mode="cash" onclick="selectPaymentMode('cash')">💵 Cash</div>
                        <div class="payment-chip-btn" data-mode="upi" onclick="selectPaymentMode('upi')">📱 UPI</div>
                        <div class="payment-chip-btn" data-mode="cash+upi" onclick="selectPaymentMode('cash+upi')">💵+📱 Cash + UPI</div>
                        <div class="payment-chip-btn" data-mode="cash+udhari" onclick="selectPaymentMode('cash+udhari')">💵+📒 Cash + Udhari</div>
                        <div class="payment-chip-btn" data-mode="upi+udhari" onclick="selectPaymentMode('upi+udhari')">📱+📒 UPI + Udhari</div>
                        <div class="payment-chip-btn" data-mode="credit_udhari" onclick="selectPaymentMode('credit_udhari')">📒 Full Khata</div>
                    </div>
                    <input type="hidden" name="payment_mode" id="accPaymentMode" value="cash">

                    <!-- Custom Price Tickbox (All discount buttons removed) -->
                    <div style="background:#F8FAFC; border:1.5px solid #E2E8F0; border-radius:12px; padding:12px 14px; margin-bottom:14px;">
                        <label style="display:flex; align-items:center; gap:9px; cursor:pointer; margin:0; user-select:none;">
                            <input type="checkbox" id="chkCustomPrice" onchange="toggleCustomPrice(this.checked)" style="width:18px; height:18px; accent-color:#4F46E5; cursor:pointer;">
                            <span style="font-size:14px; font-weight:800; color:#0F172A;">Custom Price</span>
                            <span style="font-size:11.5px; font-weight:600; color:#64748B;">(Click to enter custom bill amount)</span>
                        </label>

                        <!-- Custom Price Input (Opens when ticked) -->
                        <div id="customPriceBox" style="display:none; margin-top:10px; padding-top:10px; border-top:1px dashed #CBD5E1;">
                            <label class="app-input-label" style="font-size:11.5px; margin-bottom:4px; color:#475569;">Enter Custom Bill Amount (₹)</label>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <input type="number" id="accCustomPriceInput" min="0" step="1" class="app-input-text"
                                    style="height:38px; font-size:16px; font-weight:900; color:#4F46E5; padding:6px 10px;"
                                    placeholder="Enter custom bill amount"
                                    oninput="onCustomPriceInput(this.value)">
                                <span id="customPriceDiscountBadge" style="font-size:12px; font-weight:800; color:#16A34A; white-space:nowrap;"></span>
                            </div>
                            <div style="font-size:11px; color:#64748B; margin-top:4px;">
                                Difference from full price will automatically be recorded as discount on the bill.
                            </div>
                        </div>
                    </div>

                    <!-- Amount Paid Section & Mode-Specific Sub-Inputs -->
                    <div style="margin-bottom: 14px;">
                        <!-- For Split Cash + UPI -->
                        <div id="wrapSplitCashUpi" style="display:none; margin-bottom:10px; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:10px; padding:10px 12px;">
                            <div style="font-size:11.5px; font-weight:800; color:#334155; margin-bottom:6px;">Split Payment Breakdown</div>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                                <div>
                                    <label class="app-input-label" style="font-size:11px; margin-bottom:2px;">💵 Cash (₹)</label>
                                    <input type="number" id="splitCashInput" min="0" step="1" class="app-input-text" style="height:36px; font-size:14px; font-weight:800;" placeholder="0" oninput="onSplitCashInput(this.value)">
                                </div>
                                <div>
                                    <label class="app-input-label" style="font-size:11px; margin-bottom:2px;">📱 UPI (₹)</label>
                                    <input type="number" id="splitUpiInput" min="0" step="1" class="app-input-text" style="height:36px; font-size:14px; font-weight:800;" placeholder="0" oninput="onSplitUpiInput(this.value)">
                                </div>
                            </div>
                        </div>

                        <!-- Standard / Cash+Udhari / UPI+Udhari Amount Paid Input -->
                        <div id="wrapMainAmountPaid">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                                <label class="app-input-label" id="lblAmountPaidTitle" style="margin-bottom:0;">Amount Paid Now (₹) *</label>
                                <div id="payActionButtons" style="display:flex; gap:6px;">
                                    <button type="button" onclick="setFullPayment()" style="font-size:11px; padding:3px 10px; border-radius:6px; font-weight:800; color:#111827; border:1px solid #CBD5E1; background:#F1F5F9; cursor:pointer;">
                                        Full Paid
                                    </button>
                                    <button type="button" onclick="setZeroPayment()" style="font-size:11px; padding:3px 10px; border-radius:6px; font-weight:800; color:#111827; border:1px solid #CBD5E1; background:#F1F5F9; cursor:pointer;">
                                        Udhari (₹0)
                                    </button>
                                </div>
                            </div>
                            <input type="number" step="1" name="amount_paid" id="accAmountPaid" required placeholder="0" class="app-input-text" style="font-size:20px; font-weight:900; color:#111827;" oninput="onAmountPaidManualInput()">
                        </div>

                        <!-- Full Khata Notice -->
                        <div id="fullKhataNotice" style="display:none; background:#FEF3C7; border:1px solid #FCD34D; color:#92400E; border-radius:8px; padding:10px 12px; font-size:12px; font-weight:700;">
                            📒 Entire bill amount will be added to Customer Khata (Udhari).
                        </div>
                    </div>

                    <!-- Calculation Summary -->
                    <div style="background:#F8FAFC; border:1.5px solid #E2E8F0; border-radius:12px; padding:14px 16px;">
                        <div style="display:flex; justify-content:space-between; font-size:13px; color:#475569; margin-bottom:6px;">
                            <span>Full Price (Gross Items Total):</span>
                            <strong id="lblItemsGross" style="color:#0F172A;">₹0</strong>
                        </div>
                        <div id="lblDiscountRow" style="display:none; justify-content:space-between; font-size:13px; color:#16A34A; margin-bottom:6px;">
                            <span style="font-weight:700;">Discount (Custom Price):</span>
                            <strong id="lblDiscountAmount" style="color:#16A34A;">-₹0</strong>
                        </div>
                        <div id="gstSummaryRow" style="display:none; justify-content:space-between; font-size:13px; color:#475569; margin-bottom:6px;">
                            <span>GST (18% Included):</span>
                            <strong id="lblGstAmount" style="color:#0F172A;">₹0</strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:15px; font-weight:800; color:#0F172A; margin-bottom:6px; border-top:1px solid #E2E8F0; padding-top:6px;">
                            <span>Bill Grand Total:</span>
                            <strong id="lblGrandTotal" style="font-size:18px; color:#111827;">₹0</strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:13px; font-weight:700; color:#111827; margin-bottom:6px;">
                            <span>Paid Now:</span>
                            <strong id="lblPaidAmount" style="color:#16A34A;">₹0</strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:13px; font-weight:800; border-top:1px dashed #CBD5E1; padding-top:8px;">
                            <span style="color:#475569;">Added to Khata (Remaining Due):</span>
                            <strong id="lblDueAmount" style="color:#111827; font-size:15px;">₹0</strong>
                        </div>
                    </div>

                    <!-- Desktop Dedicated Checkout Button -->
                    <div class="desktop-checkout-wrap" style="margin-top:14px;">
                        <button type="submit" id="btnSubmitPosSaleDesktop" class="app-btn-checkout" style="width:100%;" disabled>
                            <i data-lucide="check-circle-2" style="width:18px;height:18px;"></i>
                            <span>Sell Accessories</span>
                        </button>
                    </div>

                </div>

            </div>

        </div>

        <!-- ── 6. Fixed Bottom App Checkout Dock (Mobile Only) ── -->
        <div class="app-sticky-dock">
            <div class="app-sticky-dock-inner">
                <div>
                    <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase;">Grand Total</div>
                    <div style="font-size:20px; font-weight:900; color:#0F172A;" id="dockGrandTotal">₹0</div>
                </div>
                <button type="submit" id="btnSubmitPosSale" class="app-btn-checkout" disabled>
                    <i data-lucide="check-circle-2" style="width:18px;height:18px;"></i>
                    <span>Sell Accessories</span>
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

    // ── Initialize Lucide Icons & Search Dropdown ──
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
        if (activeCategoryFilter) {
            selectCategoryFilter(activeCategoryFilter);
        }
        const searchInput = document.getElementById('accSearchInput');
        if (searchInput) {
            setTimeout(() => searchInput.focus(), 150);
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
                    khataAmountText.innerText = Math.round(bal).toLocaleString('en-IN');
                    khataIndicator.style.display = 'inline-flex';
                } else {
                    khataIndicator.style.display = 'none';
                }
                return;
            }
        }
        document.getElementById('khataIndicator').style.display = 'none';
    }

    let keyboardHighlightedIndex = -1;
    let currentFilteredItems = [];

    // ── Category Filter Pills ──
    function selectCategoryFilter(slug) {
        activeCategoryFilter = slug;
        document.querySelectorAll('.category-pill').forEach(pill => {
            pill.classList.toggle('active', pill.getAttribute('data-slug') === slug);
        });
        const label = document.getElementById('currentCategoryLabel');
        if (label) {
            label.innerText = slug ? slug.replace(/_/g, ' ') : 'All Categories';
        }
        const searchInput = document.getElementById('accSearchInput');
        onLiveSearch(searchInput.value, true);
    }

    // ── Live Product Search & Dropdown ──
    function onLiveSearch(query, isFocus = false) {
        const q = (query || '').trim().toLowerCase();
        const dropdown = document.getElementById('accSearchDropdown');
        const clearBtn = document.getElementById('btnClearSearch');

        if (clearBtn) clearBtn.style.display = q.length > 0 ? 'flex' : 'none';

        // Filter products across Name, Compatible Model, Brand, Category
        const words = q.split(/\s+/).filter(Boolean);
        currentFilteredItems = ALL_PARTS.filter(p => {
            const matchesCat = !activeCategoryFilter || p.category === activeCategoryFilter;
            if (!matchesCat) return false;
            if (words.length === 0) return true; // show all available in category on focus

            const name = (p.name || '').toLowerCase();
            const model = (p.compatible_model || '').toLowerCase();
            const brand = (p.brand || '').toLowerCase();
            const cat = (p.category || '').toLowerCase();
            const fullText = `${name} ${model} ${brand} ${cat}`;

            return words.every(w => fullText.includes(w));
        }).slice(0, 40);

        keyboardHighlightedIndex = -1;

        if (currentFilteredItems.length === 0) {
            dropdown.innerHTML = `
                <div style="padding:22px; text-align:center; color:#64748B; font-size:13px;">
                    <i data-lucide="package-x" style="width:28px;height:28px; margin:0 auto 8px; display:block; opacity:0.6; color:#94A3B8;"></i>
                    No matching in-stock products found.
                </div>`;
            if (typeof lucide !== 'undefined') lucide.createIcons();
            dropdown.style.display = 'block';
            return;
        }

        let html = '';
        if (words.length === 0) {
            html += `<div style="padding:6px 12px; font-size:11px; font-weight:800; color:#64748B; text-transform:uppercase; background:#F8FAFC; border-radius:6px; margin-bottom:4px; display:flex; justify-content:space-between;">
                <span>In-Stock Products (${currentFilteredItems.length} available)</span>
                <span style="font-weight:600; color:#2563EB;">Type to filter</span>
            </div>`;
        } else {
            html += `<div style="padding:6px 12px; font-size:11px; font-weight:800; color:#2563EB; text-transform:uppercase; background:#EFF6FF; border-radius:6px; margin-bottom:4px; display:flex; justify-content:space-between;">
                <span>Matches for "${escapeHtml(q)}" (${currentFilteredItems.length})</span>
                <span style="font-weight:600; color:#64748B;">Press Enter or click to add</span>
            </div>`;
        }

        currentFilteredItems.forEach((item, idx) => {
            const price = Math.round(parseFloat(item.selling_price || 0));
            const modelText = item.compatible_model ? ` (${item.compatible_model})` : '';
            const isLowStock = item.stock_qty <= 3;
            html += `
                <div class="search-result-row" data-index="${idx}" onclick="onPickProductFromSearch(${item.id})">
                    <div style="flex:1; min-width:0;">
                        <div style="font-weight:700; font-size:13px; color:#0F172A; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                            ${escapeHtml(item.name)}${escapeHtml(modelText)}
                        </div>
                        <div style="display:flex; align-items:center; gap:6px; margin-top:2px; font-size:11px; color:#64748B;">
                            <span style="background:#F1F5F9; color:#475569; font-weight:700; padding:1px 6px; border-radius:4px; font-size:10px;">${escapeHtml(item.category || 'Accessory')}</span>
                            ${item.brand ? `<span>${escapeHtml(item.brand)}</span> &bull;` : ''}
                            <span style="color:${isLowStock ? '#DC2626' : '#059669'}; font-weight:700;">● ${item.stock_qty} in stock</span>
                        </div>
                    </div>
                    <div style="text-align:right; display:flex; align-items:center; gap:8px;">
                        <div style="font-size:14px; font-weight:900; color:#2563EB; font-family:'JetBrains Mono', monospace;">₹${price.toLocaleString('en-IN')}</div>
                        <button type="button" style="background:#2563EB; color:#fff; border:none; font-size:11.5px; font-weight:700; padding:4px 10px; border-radius:6px; cursor:pointer; pointer-events:none;">+ Add</button>
                    </div>
                </div>
            `;
        });

        dropdown.innerHTML = html;
        dropdown.style.display = 'block';
    }

    // ── Keyboard Navigation (Arrow Keys & Enter) ──
    function handleSearchKeyNavigation(e) {
        const dropdown = document.getElementById('accSearchDropdown');
        if (!dropdown || dropdown.style.display === 'none') {
            if (e.key === 'ArrowDown' || e.key === 'Enter') {
                onLiveSearch(document.getElementById('accSearchInput').value, true);
                e.preventDefault();
            }
            return;
        }

        const rows = dropdown.querySelectorAll('.search-result-row');
        if (!rows.length) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            keyboardHighlightedIndex = (keyboardHighlightedIndex + 1) % rows.length;
            updateDropdownHighlight(rows);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            keyboardHighlightedIndex = (keyboardHighlightedIndex - 1 + rows.length) % rows.length;
            updateDropdownHighlight(rows);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (keyboardHighlightedIndex >= 0 && keyboardHighlightedIndex < rows.length) {
                rows[keyboardHighlightedIndex].click();
            } else if (rows.length > 0) {
                rows[0].click();
            }
        } else if (e.key === 'Escape') {
            dropdown.style.display = 'none';
            keyboardHighlightedIndex = -1;
        }
    }

    function updateDropdownHighlight(rows) {
        rows.forEach((r, i) => {
            if (i === keyboardHighlightedIndex) {
                r.classList.add('selected');
                r.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            } else {
                r.classList.remove('selected');
            }
        });
    }

    function clearSearch() {
        const input = document.getElementById('accSearchInput');
        if (input) input.value = '';
        const clearBtn = document.getElementById('btnClearSearch');
        if (clearBtn) clearBtn.style.display = 'none';
        const dropdown = document.getElementById('accSearchDropdown');
        if (dropdown) dropdown.style.display = 'none';
        keyboardHighlightedIndex = -1;
    }

    // Close dropdown on click outside
    document.addEventListener('click', function(e) {
        const wrapper = document.getElementById('productSearchWrapper');
        if (wrapper && !wrapper.contains(e.target)) {
            const dropdown = document.getElementById('accSearchDropdown');
            if (dropdown) dropdown.style.display = 'none';
            keyboardHighlightedIndex = -1;
        }
    });

    // ── Instant Add Product by ID to Cart ──
    function addProductByIdToCart(partId) {
        const item = ALL_PARTS.find(p => p.id === partId);
        if (!item) return;

        const existing = cart.find(c => c.part_id === item.id);
        if (existing) {
            if (existing.quantity >= item.stock_qty) {
                alert(`Cannot add more than available stock (${item.stock_qty}) for ${item.name}.`);
                return;
            }
            existing.quantity += 1;
        } else {
            const origPrice = Math.round(parseFloat(item.selling_price || 0));
            cart.push({
                part_id: item.id,
                name: item.name,
                model: item.compatible_model || '',
                category: item.category || '',
                original_price: origPrice,
                discount_type: 'none',
                discount_val: 0,
                unit_price: origPrice,
                quantity: 1,
                max_stock: item.stock_qty
            });
        }
        renderCart();
    }

    // ── Pick Product From Search Dropdown (One-Click Direct Add) ──
    function onPickProductFromSearch(partId) {
        const item = ALL_PARTS.find(p => p.id === partId);
        if (!item) return;

        addProductByIdToCart(partId);
        showItemAddedNotice(item.name + (item.compatible_model ? ` (${item.compatible_model})` : ''));

        const input = document.getElementById('accSearchInput');
        if (input) {
            input.value = '';
            input.focus();
        }
        const clearBtn = document.getElementById('btnClearSearch');
        if (clearBtn) clearBtn.style.display = 'none';
        const dropdown = document.getElementById('accSearchDropdown');
        if (dropdown) dropdown.style.display = 'none';
        keyboardHighlightedIndex = -1;
    }

    function showItemAddedNotice(itemName) {
        const notice = document.getElementById('itemAddedNotice');
        const text = document.getElementById('itemAddedNoticeText');
        if (!notice || !text) return;
        text.innerText = `Added to bill: ${itemName}`;
        notice.style.display = 'flex';
        clearTimeout(notice._timer);
        notice._timer = setTimeout(() => {
            notice.style.display = 'none';
        }, 1800);
    }

    // ── Custom Bill Price Tickbox & Input ──
    let isCustomPriceActive = false;
    let customPriceValue = null;
    let currentPaymentMode = 'cash';

    function toggleCustomPrice(checked) {
        isCustomPriceActive = checked;
        const box = document.getElementById('customPriceBox');
        const input = document.getElementById('accCustomPriceInput');
        if (box) box.style.display = checked ? 'block' : 'none';

        if (checked) {
            const gross = getGrossTotal();
            if (!customPriceValue || customPriceValue === 0) {
                customPriceValue = gross;
                if (input) input.value = gross > 0 ? gross : '';
            }
            if (input) setTimeout(() => input.focus(), 50);
        } else {
            customPriceValue = null;
            if (input) input.value = '';
        }
        updateCalculations();
    }
    window.toggleCustomPrice = toggleCustomPrice;

    function onCustomPriceInput(val) {
        if (!isCustomPriceActive) return;
        const parsed = parseFloat(val);
        customPriceValue = isNaN(parsed) ? null : Math.max(0, Math.round(parsed));
        updateCalculations();
    }
    window.onCustomPriceInput = onCustomPriceInput;

    // ── Render Cart Items ──
    function renderCart() {
        const container = document.getElementById('cartItemsList');
        const countBadge = document.getElementById('cartCountBadge');
        const topBadge = document.getElementById('topCartBadge');
        const clearBtn = document.getElementById('btnClearCart');
        const checkoutBtn = document.getElementById('btnSubmitPosSale');
        const checkoutBtnDesktop = document.getElementById('btnSubmitPosSaleDesktop');

        const totalItemsCount = cart.reduce((sum, item) => sum + item.quantity, 0);
        countBadge.innerText = totalItemsCount;
        if (topBadge) topBadge.innerText = `${totalItemsCount} Items`;

        if (cart.length === 0) {
            container.innerHTML = `
                <div id="emptyCartMessage" style="text-align:center; padding:32px 16px; color:#64748B;">
                    <i data-lucide="shopping-cart" style="width:36px; height:36px; color:#CBD5E1; margin:0 auto 10px; display:block;"></i>
                    <div style="font-size:14px; font-weight:700; color:#475569;">Your bill is currently empty</div>
                    <p style="font-size:12px; color:#94A3B8; margin:4px 0 0 0;">Tap or search products to add items</p>
                </div>
            `;
            clearBtn.style.display = 'none';
            if (checkoutBtn) checkoutBtn.disabled = true;
            if (checkoutBtnDesktop) checkoutBtnDesktop.disabled = true;
            updateCalculations();
            if (typeof lucide !== 'undefined') lucide.createIcons();
            return;
        }

        clearBtn.style.display = 'inline-block';
        if (checkoutBtn) checkoutBtn.disabled = false;
        if (checkoutBtnDesktop) checkoutBtnDesktop.disabled = false;

        let html = '';
        cart.forEach((item, index) => {
            const itemTotal = Math.round(item.quantity * item.unit_price);
            html += `
                <div class="app-cart-item-card">
                    <input type="hidden" name="items[${index}][part_id]" value="${item.part_id}">
                    <input type="hidden" name="items[${index}][quantity]" value="${item.quantity}" id="cartQtyInput_${index}">
                    <input type="hidden" name="items[${index}][unit_price]" value="${item.unit_price}" id="cartUnitPrice_${index}">
                    
                    <div class="app-cart-item-header">
                        <div style="flex:1; min-width:0;">
                            <div style="font-size:13px; font-weight:800; color:#0F172A; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                ${escapeHtml(item.name)}${item.model ? ` (${escapeHtml(item.model)})` : ''}
                            </div>
                            <span style="font-size:11px; color:#64748B; font-weight:600;">${escapeHtml(item.category)} • Stock: ${item.max_stock}</span>
                        </div>
                        <button type="button" onclick="removeCartItem(${index})" style="background:#F1F5F9; border:1px solid #CBD5E1; color:#334155; border-radius:6px; width:26px; height:26px; display:flex; align-items:center; justify-content:center; cursor:pointer; font-weight:800; font-size:13px;" title="Remove Item">✕</button>
                    </div>

                    <div class="app-cart-item-footer">
                        <!-- Rate Display with inline Custom Price edit -->
                        <div style="display:flex; align-items:center; gap:4px; font-size:11.5px; font-weight:700; color:#334155;">
                            <span>Rate: ₹</span>
                            <input type="number" min="0" step="1" value="${Math.round(item.unit_price)}" 
                                class="app-input-text" 
                                style="width:72px; height:26px; padding:2px 6px; font-size:12px; font-weight:800; color:#0F172A; border:1px solid #CBD5E1; border-radius:4px; background:#FFFFFF;"
                                onchange="updateCartItemRate(${index}, this.value)"
                                oninput="updateCartItemRate(${index}, this.value)"
                                title="Custom Item Rate">
                        </div>

                        <!-- Touch Stepper -->
                        <div style="display:flex; align-items:center; gap:6px;">
                            <button type="button" class="touch-step-btn" onclick="updateCartItemQty(${index}, -1)">−</button>
                            <span style="font-size:14px; font-weight:800; min-width:24px; text-align:center;">${item.quantity}</span>
                            <button type="button" class="touch-step-btn" onclick="updateCartItemQty(${index}, 1)">+</button>
                        </div>

                        <!-- Item Total -->
                        <div id="cartItemTotal_${index}" style="font-size:15px; font-weight:900; color:#0F172A; font-family:'JetBrains Mono', monospace;">
                            ₹${itemTotal.toLocaleString('en-IN')}
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
        currentPaymentMode = mode;
        document.getElementById('accPaymentMode').value = mode;
        document.querySelectorAll('.payment-chip-btn').forEach(btn => {
            btn.classList.toggle('active', btn.getAttribute('data-mode') === mode);
        });

        const wrapSplit = document.getElementById('wrapSplitCashUpi');
        const wrapMain = document.getElementById('wrapMainAmountPaid');
        const khataNotice = document.getElementById('fullKhataNotice');
        const lblTitle = document.getElementById('lblAmountPaidTitle');
        const payBtns = document.getElementById('payActionButtons');
        const paidInput = document.getElementById('accAmountPaid');

        if (mode === 'cash+upi') {
            if (wrapSplit) wrapSplit.style.display = 'block';
            if (wrapMain) wrapMain.style.display = 'none';
            if (khataNotice) khataNotice.style.display = 'none';
            const total = getGrandTotal();
            const half = Math.round(total / 2);
            const cashInput = document.getElementById('splitCashInput');
            const upiInput = document.getElementById('splitUpiInput');
            if (cashInput) cashInput.value = half;
            if (upiInput) upiInput.value = Math.max(0, total - half);
            if (paidInput) {
                paidInput.value = total;
                paidInput.dataset.manual = "false";
            }
        } else if (mode === 'credit_udhari') {
            if (wrapSplit) wrapSplit.style.display = 'none';
            if (wrapMain) wrapMain.style.display = 'none';
            if (khataNotice) khataNotice.style.display = 'block';
            setZeroPayment();
        } else {
            if (wrapSplit) wrapSplit.style.display = 'none';
            if (wrapMain) wrapMain.style.display = 'block';
            if (khataNotice) khataNotice.style.display = 'none';

            if (mode === 'cash') {
                if (lblTitle) lblTitle.innerText = "Cash Paid Now (₹) *";
                if (payBtns) payBtns.style.display = 'flex';
                setFullPayment();
            } else if (mode === 'upi') {
                if (lblTitle) lblTitle.innerText = "UPI Paid Now (₹) *";
                if (payBtns) payBtns.style.display = 'flex';
                setFullPayment();
            } else if (mode === 'cash+udhari') {
                if (lblTitle) lblTitle.innerText = "Cash Paid (₹) — Rest in Khata *";
                if (payBtns) payBtns.style.display = 'flex';
                if (!paidInput.value || paidInput.dataset.manual !== "true") {
                    paidInput.value = Math.round(getGrandTotal() / 2);
                }
            } else if (mode === 'upi+udhari') {
                if (lblTitle) lblTitle.innerText = "UPI Paid (₹) — Rest in Khata *";
                if (payBtns) payBtns.style.display = 'flex';
                if (!paidInput.value || paidInput.dataset.manual !== "true") {
                    paidInput.value = Math.round(getGrandTotal() / 2);
                }
            }
        }

        updateCalculations();
    }
    window.selectPaymentMode = selectPaymentMode;

    function onSplitCashInput(val) {
        const total = getGrandTotal();
        let cash = Math.max(0, Math.min(total, parseFloat(val) || 0));
        let upi = Math.max(0, total - cash);
        const upiInput = document.getElementById('splitUpiInput');
        if (upiInput) upiInput.value = Math.round(upi);
        const paidInput = document.getElementById('accAmountPaid');
        if (paidInput) {
            paidInput.value = total;
            paidInput.dataset.manual = "false";
        }
        updateCalculations();
    }
    window.onSplitCashInput = onSplitCashInput;

    function onSplitUpiInput(val) {
        const total = getGrandTotal();
        let upi = Math.max(0, Math.min(total, parseFloat(val) || 0));
        let cash = Math.max(0, total - upi);
        const cashInput = document.getElementById('splitCashInput');
        if (cashInput) cashInput.value = Math.round(cash);
        const paidInput = document.getElementById('accAmountPaid');
        if (paidInput) {
            paidInput.value = total;
            paidInput.dataset.manual = "false";
        }
        updateCalculations();
    }
    window.onSplitUpiInput = onSplitUpiInput;

    function toggleGstBilling(isGst) {
        document.getElementById('accBillType').value = isGst ? 'gst' : 'non_gst';
        document.getElementById('gstSummaryRow').style.display = isGst ? 'flex' : 'none';
        updateCalculations();
    }

    function getGrossTotal() {
        return cart.reduce((sum, item) => sum + Math.round(item.quantity * item.original_price), 0);
    }

    function getGrandTotal() {
        const gross = getGrossTotal();
        if (isCustomPriceActive && customPriceValue !== null && customPriceValue >= 0) {
            return customPriceValue;
        }
        return gross;
    }

    function updateCalculations() {
        const gross = getGrossTotal();
        const total = getGrandTotal();
        const discountAmt = Math.max(0, gross - total);
        const isGst = document.getElementById('accIsGstCheckbox') ? document.getElementById('accIsGstCheckbox').checked : false;

        // Update Custom Price Discount badge
        const badge = document.getElementById('customPriceDiscountBadge');
        if (badge) {
            if (isCustomPriceActive && customPriceValue !== null) {
                if (discountAmt > 0) {
                    badge.innerText = `Discount: -₹${discountAmt.toLocaleString('en-IN')}`;
                    badge.style.color = '#16A34A';
                } else if (customPriceValue > gross) {
                    badge.innerText = `+₹${(customPriceValue - gross).toLocaleString('en-IN')}`;
                    badge.style.color = '#4F46E5';
                } else {
                    badge.innerText = 'Same as Full Price';
                    badge.style.color = '#64748B';
                }
            } else {
                badge.innerText = '';
            }
        }

        // Proportionally distribute grandTotal to cart items
        if (cart.length > 0 && gross > 0) {
            let allocatedTotal = 0;
            cart.forEach((item, idx) => {
                const itemGross = item.quantity * item.original_price;
                const itemShare = itemGross / gross;
                let itemNetTotal;
                if (idx === cart.length - 1) {
                    itemNetTotal = Math.max(0, total - allocatedTotal);
                } else {
                    itemNetTotal = Math.round(total * itemShare);
                    allocatedTotal += itemNetTotal;
                }
                const unitPrice = item.quantity > 0 ? (itemNetTotal / item.quantity) : item.original_price;
                item.unit_price = unitPrice;

                const hiddenPriceInput = document.getElementById(`cartUnitPrice_${idx}`);
                if (hiddenPriceInput) hiddenPriceInput.value = unitPrice;

                const rateDisplay = document.getElementById(`cartItemRate_${idx}`);
                if (rateDisplay) rateDisplay.innerText = `₹${Number(Math.round(unitPrice)).toLocaleString('en-IN')}`;

                const totalDisplay = document.getElementById(`cartItemTotal_${idx}`);
                if (totalDisplay) totalDisplay.innerText = `₹${Number(Math.round(itemNetTotal)).toLocaleString('en-IN')}`;
            });
        }

        const grossLbl = document.getElementById('lblItemsGross');
        if (grossLbl) grossLbl.innerText = `₹${gross.toLocaleString('en-IN')}`;

        const discRow = document.getElementById('lblDiscountRow');
        const discAmt = document.getElementById('lblDiscountAmount');
        if (discRow && discAmt) {
            if (discountAmt > 0) {
                discAmt.innerText = `-₹${discountAmt.toLocaleString('en-IN')}`;
                discRow.style.display = 'flex';
            } else {
                discRow.style.display = 'none';
            }
        }

        if (isGst) {
            const gst = Math.round(total * 0.18 / 1.18); // Inclusive 18%
            const gstLbl = document.getElementById('lblGstAmount');
            if (gstLbl) gstLbl.innerText = `₹${gst.toLocaleString('en-IN')}`;
        }
        const grandTotalLbl = document.getElementById('lblGrandTotal');
        if (grandTotalLbl) grandTotalLbl.innerText = `₹${total.toLocaleString('en-IN')}`;

        const dockGrandTotalLbl = document.getElementById('dockGrandTotal');
        if (dockGrandTotalLbl) dockGrandTotalLbl.innerText = `₹${total.toLocaleString('en-IN')}`;

        // Auto-update amount paid if in full payment modes
        const paidInput = document.getElementById('accAmountPaid');
        const mode = currentPaymentMode;
        if (mode === 'credit_udhari') {
            paidInput.value = "0";
        } else if (mode === 'cash+upi') {
            paidInput.value = total;
            const cashInput = document.getElementById('splitCashInput');
            const upiInput = document.getElementById('splitUpiInput');
            if (cashInput && upiInput) {
                const currentCash = parseFloat(cashInput.value) || 0;
                if (currentCash > total) {
                    cashInput.value = total;
                    upiInput.value = 0;
                } else {
                    upiInput.value = Math.max(0, total - currentCash);
                }
            }
        } else if (mode === 'cash' || mode === 'upi') {
            if (!paidInput.dataset.manual || paidInput.dataset.manual === "false") {
                paidInput.value = total;
            }
        } else if (mode === 'cash+udhari' || mode === 'upi+udhari') {
            if (!paidInput.dataset.manual || paidInput.dataset.manual === "false") {
                paidInput.value = Math.round(total / 2);
            }
        }

        onAmountPaidManualInput();
    }

    function onAmountPaidManualInput() {
        const total = getGrandTotal();
        let paid = Math.round(parseFloat(document.getElementById('accAmountPaid').value) || 0);
        if (currentPaymentMode === 'credit_udhari') {
            paid = 0;
        }
        const due = Math.max(0, total - paid);

        document.getElementById('lblPaidAmount').innerText = `₹${paid.toLocaleString('en-IN')}`;
        const lblDue = document.getElementById('lblDueAmount');
        if (lblDue) {
            lblDue.innerText = `₹${due.toLocaleString('en-IN')}`;
            lblDue.style.color = due > 0 ? '#DC2626' : '#16A34A';
        }
    }

    function updateCartItemRate(index, newRate) {
        if (!cart[index]) return;
        const rate = Math.max(0, parseFloat(newRate) || 0);
        cart[index].unit_price = rate;
        cart[index].original_price = rate;

        const hiddenPrice = document.getElementById(`cartUnitPrice_${index}`);
        if (hiddenPrice) hiddenPrice.value = rate;

        const totalDisplay = document.getElementById(`cartItemTotal_${index}`);
        if (totalDisplay) {
            const itemTotal = Math.round(cart[index].quantity * rate);
            totalDisplay.innerText = `₹${itemTotal.toLocaleString('en-IN')}`;
        }

        updateCalculations();
    }
    window.updateCartItemRate = updateCartItemRate;

    function setFullPayment() {
        const total = getGrandTotal();
        document.getElementById('accAmountPaid').value = total;
        document.getElementById('accAmountPaid').dataset.manual = "false";
        onAmountPaidManualInput();
    }

    function setZeroPayment() {
        document.getElementById('accAmountPaid').value = "0";
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
        const btnDesktop = document.getElementById('btnSubmitPosSaleDesktop');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Recording Sale...';
        }
        if (btnDesktop) {
            btnDesktop.disabled = true;
            btnDesktop.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Recording Sale...';
        }
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
