@extends('mobileshop.layout')

@php
    $salesPageTitle = match($niche ?? 'admin') {
        'accessories' => 'Accessories Sales Hub',
        'repairs'     => 'Completed Repairs Hub',
        default       => 'Sales Hub & Invoice Register',
    };
@endphp

@section('title', $salesPageTitle . ' — PhoneFix Azamgarh ERP')
@section('page-title', $salesPageTitle)

@section('page-actions')
    <div class="flex items-center gap-2 flex-wrap">
        <a href="{{ route('mobileshop.sales.bulk') }}" class="btn btn-primary btn-sm" style="background:#4F46E5; border-color:#4338CA; color:#ffffff; text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
            <i data-lucide="layers" style="width:13px;height:13px;"></i> <span class="desktop-btn-label">Bulk Multi-Customer Sale</span><span class="mobile-btn-label">Bulk Sale</span>
        </a>
        <a href="{{ route('mobileshop.accessories.pos') }}" class="btn btn-outline btn-sm">
            <i data-lucide="zap" style="width:13px;height:13px;"></i> <span class="desktop-btn-label">Sell Accessories</span><span class="mobile-btn-label">Sell Accessories</span>
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

    /* ─── FULL-WIDTH QUICK SALE PANEL (App Theme Matched) ─── */
    .quick-sales-top-grid {
        display: block;
        width: 100%;
        margin-bottom: 20px;
    }

    .quick-sale-cust-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 12px;
        margin-bottom: 12px;
    }
    @media (max-width: 768px) {
        .quick-sale-cust-grid {
            grid-template-columns: 1fr;
        }
    }
    .quick-items-header {
        display: grid;
        grid-template-columns: 3.2fr 100px 140px 110px 40px;
        gap: 10px;
        padding: 6px 12px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 0.5px;
    }
    .quick-item-row {
        display: grid;
        grid-template-columns: 3.2fr 100px 140px 110px 40px;
        gap: 10px;
        align-items: center;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 8px 12px;
        margin-bottom: 8px;
        transition: all 0.15s ease;
    }
    .quick-item-row:hover {
        border-color: #cbd5e1;
        background: #f1f5f9;
    }
    @media (max-width: 768px) {
        .quick-items-header {
            display: none !important;
        }
        .quick-item-row {
            grid-template-columns: 1fr 1fr !important;
            gap: 8px !important;
            padding: 10px !important;
        }
        .quick-item-row .col-item-search {
            grid-column: 1 / -1;
        }
        .quick-item-row .col-total-remove {
            grid-column: 1 / -1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 4px;
            border-top: 1px dashed #e2e8f0;
        }
    }
    .bulk-customer-card {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 14px 16px;
        margin-bottom: 14px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        position: relative;
    }
    .bulk-customer-card:hover {
        border-color: #94a3b8;
    }

    .light-sale-panel {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 16px 20px;
        color: #0f172a;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04), 0 1px 2px rgba(0, 0, 0, 0.02);
    }
    .panel-header-box {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 14px;
    }
    .panel-header-icon {
        width: 30px;
        height: 30px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .panel-header-icon.sale-icon {
        background: #eef2ff;
        color: #4f46e5;
    }
    .panel-header-icon.search-icon {
        background: #f1f5f9;
        color: #475569;
    }
    .panel-header-title {
        font-size: 15px;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        letter-spacing: -0.2px;
    }
    .app-form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
    @media (max-width: 580px) {
        .app-form-grid {
            grid-template-columns: 1fr;
        }
    }
    .app-input-field {
        width: 100%;
        height: 38px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 7px;
        padding: 6px 12px;
        font-size: 13px;
        color: #0f172a;
        outline: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
        box-sizing: border-box;
    }
    .app-input-field:focus {
        border-color: #5e6ad2;
        box-shadow: 0 0 0 3px rgba(94, 106, 210, 0.12);
    }
    .app-input-field::placeholder {
        color: #94a3b8;
    }
    .btn-app-primary {
        background: #5e6ad2;
        color: #ffffff;
        border: none;
        border-radius: 7px;
        padding: 8px 18px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: background 0.15s ease, transform 0.1s ease;
    }
    .btn-app-primary:hover {
        background: #4f5bc2;
    }
    .btn-app-primary:active {
        transform: scale(0.98);
    }
    .btn-app-success {
        background: #10b981;
        color: #ffffff;
        border: none;
        border-radius: 7px;
        padding: 8px 18px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: background 0.15s ease, transform 0.1s ease;
    }
    .btn-app-success:hover {
        background: #059669;
    }
    .btn-app-success:active {
        transform: scale(0.98);
    }
    .split-row-light {
        grid-column: 1 / -1;
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 7px;
        padding: 10px 12px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }
    .search-picker-wrapper {
        position: relative;
    }
    .search-picker-dropdown {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 1050;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        margin-top: 4px;
        max-height: 240px;
        overflow-y: auto;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
    .search-picker-item {
        padding: 8px 12px;
        border-bottom: 1px solid #f1f5f9;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: background 0.12s ease;
    }
    .search-picker-item:last-child {
        border-bottom: none;
    }
    .search-picker-item:hover {
        background: #f8fafc;
    }
</style>
@endpush

@section('content')

<div class="sales-page-wrapper">

    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- QUICK SALE & SEARCH DUAL PANELS (TOP OF SALES PAGE)        -->
    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- FULL-WIDTH QUICK SALE PANEL (MULTI-ITEM & BULK READY)      -->
    <!-- ══════════════════════════════════════════════════════════ -->
    <div class="quick-sales-top-grid">
        <div class="light-sale-panel" style="width: 100%; box-sizing: border-box;">
            <!-- Panel Header: Title + Bulk Sale Trigger Button -->
            <div class="panel-header-box" style="justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div class="panel-header-icon sale-icon">
                        <i data-lucide="shopping-cart" style="width:16px;height:16px;"></i>
                    </div>
                    <div>
                        <h3 class="panel-header-title">Quick Sale</h3>
                        <p style="margin: 0; font-size: 11px; color: #64748b;">Fast counter billing — add multiple items in a single bill</p>
                    </div>
                </div>

                <div style="display:flex; align-items:center; gap:8px;">
                    <a href="{{ route('mobileshop.sales.bulk') }}" class="btn-app-primary" style="background: #4f46e5; padding: 6px 14px; font-size: 12px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                        <i data-lucide="layers" style="width:13px;height:13px;"></i> Bulk Multi-Customer Sale
                    </a>
                </div>
            </div>

            <form action="{{ route('mobileshop.sales.store', ['company_id' => company_id()]) }}" method="POST" id="quickSaleForm" onsubmit="return validateQuickSaleForm(event)">
                @csrf
                <input type="hidden" name="sale_type" value="accessory">
                <input type="hidden" name="amount_paid" id="quickSaleAmountPaid" value="0">

                <!-- Quick Sale Inline Alert / Warning Box -->
                <div id="quickSaleAlertBox" style="display:none; background:#fef2f2; border:1px solid #fecdd3; border-radius:8px; padding:10px 14px; margin-bottom:12px; color:#991b1b; font-size:12.5px; font-weight:600;"></div>

                <!-- 1. Customer & Payment Details Bar -->
                <div class="quick-sale-cust-grid">
                    <div>
                        <label style="font-size:11px; font-weight:700; color:#475569; display:block; margin-bottom:4px;">Customer Name</label>
                        <input type="text" name="customer_name" id="quickSaleCustName" class="app-input-field" placeholder="Customer Name (Walk-in Customer)" list="quickCustNames">
                        <datalist id="quickCustNames">
                            @foreach($customers as $c)
                                <option value="{{ $c->name }}">{{ $c->phone }}</option>
                            @endforeach
                        </datalist>
                    </div>

                    <div>
                        <label style="font-size:11px; font-weight:700; color:#475569; display:block; margin-bottom:4px;">Mobile Number</label>
                        <input type="tel" name="customer_phone" id="quickSaleCustPhone" class="app-input-field" placeholder="Mobile Number (Optional)" list="quickCustPhones" onchange="onQuickPhoneChange(this)">
                        <datalist id="quickCustPhones">
                            @foreach($customers as $c)
                                <option value="{{ $c->phone }}">{{ $c->name }}</option>
                            @endforeach
                        </datalist>
                    </div>

                    <div>
                        <label style="font-size:11px; font-weight:700; color:#475569; display:block; margin-bottom:4px;">Payment Mode</label>
                        <select name="payment_mode" id="quickSalePaymentMode" class="app-input-field" required onchange="onQuickPaymentModeChange(this)">
                            <option value="cash" selected>Cash</option>
                            <option value="upi">UPI</option>
                            <option value="udhari">Pay Later (Balance Due)</option>
                            <option value="cash+upi">Cash + UPI</option>
                            <option value="upi+cash">UPI + Cash</option>
                            <option value="cash+udhari">Cash + Balance Due</option>
                            <option value="upi+udhari">UPI + Balance Due</option>
                        </select>
                    </div>
                </div>

                <!-- Dynamic Split Payment Fields (Cash+UPI, Cash+Udhari, UPI+Udhari, Udhari) -->
                <div id="quickSplitRow" class="split-row-light" style="display: none; margin-bottom: 14px;">
                    <!-- Configured dynamically by onQuickPaymentModeChange() -->
                </div>

                <!-- 2. Multi-Item Cart Repeater Section -->
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-bottom: 14px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span style="font-size: 12px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 6px;">
                            <i data-lucide="package" style="width:14px;height:14px; color: #4f46e5;"></i>
                            Sale Items (<span id="quickItemCountText">1</span>)
                        </span>
                        <button type="button" onclick="addQuickSaleItemRow()" style="background: #eef2ff; color: #4f46e5; border: 1px solid #c7d2fe; border-radius: 6px; padding: 4px 10px; font-size: 11px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                            <i data-lucide="plus" style="width:12px;height:12px;"></i> Add Another Item
                        </button>
                    </div>

                    <!-- Column Headers (Desktop) -->
                    <div class="quick-items-header">
                        <div>Item Name / Search (Type letters)</div>
                        <div style="text-align:center;">Qty</div>
                        <div style="text-align:right;">Price (₹)</div>
                        <div style="text-align:right;">Total (₹)</div>
                        <div style="text-align:center;"></div>
                    </div>

                    <!-- Item Rows Container -->
                    <div id="quickSaleItemsContainer">
                        <!-- Initial Row 0 -->
                        <div class="quick-item-row item-search-row" data-index="0" id="quickItemRow_0">
                            <div class="search-picker-wrapper col-item-search" style="position: relative;">
                                <input type="text"
                                       name="items[0][part_name]"
                                       class="app-input-field input-item-name"
                                       style="padding-right: 30px;"
                                       placeholder="Type item name to search or enter custom item..."
                                       autocomplete="off"
                                       required
                                       oninput="onRowItemSearchInput(this)">
                                <input type="hidden" name="items[0][part_id]" class="input-part-id">
                                <button type="button"
                                        class="btn-item-clear"
                                        onclick="clearRowItemSelection(this)"
                                        title="Clear item"
                                        style="display:none; position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: #e2e8f0; border: none; border-radius: 50%; width: 20px; height: 20px; font-size: 11px; line-height: 20px; text-align: center; color: #475569; cursor: pointer; padding: 0;">✕</button>
                                <div class="search-picker-dropdown"></div>
                            </div>

                            <div>
                                <input type="number"
                                       name="items[0][quantity]"
                                       class="app-input-field input-quantity"
                                       placeholder="Qty"
                                       value="1"
                                       min="1"
                                       required
                                       oninput="recalcQuickSaleRow(this)">
                            </div>

                            <div>
                                <input type="number"
                                       step="0.01"
                                       name="items[0][unit_price]"
                                       class="app-input-field input-unit-price"
                                       placeholder="Price (₹)"
                                       required
                                       oninput="recalcQuickSaleRow(this)">
                            </div>

                            <div class="line-total-cell" style="text-align: right; font-weight: 700; font-size: 13px; color: #0f172a; white-space: nowrap;">
                                ₹0.00
                            </div>

                            <div class="col-total-remove" style="text-align: center;">
                                <button type="button"
                                        class="btn-remove-row"
                                        onclick="removeQuickSaleItemRow(this)"
                                        title="Remove item"
                                        disabled
                                        style="background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 6px; color: #94a3b8; width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; cursor: not-allowed; font-size: 12px;">✕</button>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Add Item Button Strip -->
                    <div style="margin-top: 8px; text-align: left;">
                        <button type="button" onclick="addQuickSaleItemRow()" style="background: transparent; color: #4f46e5; border: 1px dashed #c7d2fe; border-radius: 6px; padding: 6px 14px; font-size: 12px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; width: 100%; justify-content: center;">
                            <i data-lucide="plus-circle" style="width:14px;height:14px;"></i> + Click to Add Another Item
                        </button>
                    </div>
                </div>

                <!-- 3. Submit Button Row & Total Badges -->
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap: wrap; gap: 10px; padding-top: 10px; border-top: 1px solid #f1f5f9;">
                    <button type="submit" class="btn-app-primary" id="quickSaleSubmitBtn">
                        <i data-lucide="check-circle" style="width:15px;height:15px;"></i> Complete Sale
                    </button>

                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span id="quickItemCountBadge" style="font-size: 12px; color: #64748b; font-weight: 600;">1 Item</span>
                        <div id="quickSaleTotalBadge" style="background:#eef2ff; color:#4338ca; border:1px solid #c7d2fe; border-radius:6px; padding:6px 16px; font-size: 15px; font-weight: 800;">
                            Total: ₹0.00
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

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
            <span class="stat-val">{{ number_format($availableParts) }}</span>
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
            <div class="kpi-num">{{ number_format($availableParts) }} <span style="font-size:11px; font-weight:400; color:var(--color-ink-muted);">Units</span></div>
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
                    <input type="text" id="salesSearchInput" oninput="filterSalesTable()" placeholder="Search invoice, customer, item..." class="sales-search-input">
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
                    @forelse($accSales as $asale)
                    <tr class="sales-row" data-type="accessory" data-date="{{ \Carbon\Carbon::parse($asale->created_at)->format('Y-m-d') }}">
                        <td>
                            <a href="{{ route('mobileshop.accessories.invoice', ['id' => $asale->id]) }}" target="_blank" rel="noopener" style="font-family:monospace; font-weight:800; color:var(--brand-700); text-decoration:none;" title="View Invoice in New Tab">
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
                            @if(($asale->status ?? '') === 'partially_returned')
                                <span class="badge" style="background:#FEF3C7; color:#B45309; font-size:9.5px; font-weight:800; display:block; margin-top:3px;">Partially Returned</span>
                            @elseif(($asale->status ?? '') === 'voided')
                                <span class="badge" style="background:#FEE2E2; color:#DC2626; font-size:9.5px; font-weight:800; display:block; margin-top:3px;">Returned</span>
                            @endif
                        </td>
                        <td style="text-align:right; font-weight:900; color:#0F172A; font-size:14px;">
                            @php
                                $refAmt = (float)($asale->refund_amount ?? 0);
                            @endphp
                            @if(($asale->status ?? '') === 'voided')
                                <span style="text-decoration:line-through; font-size:11.5px; color:#94A3B8;">₹{{ number_format($asale->total_amount, 2) }}</span>
                                <div style="color:#DC2626; font-size:12px; font-weight:800;">₹0.00 <span style="font-size:9.5px;">(Returned)</span></div>
                            @elseif(($asale->status ?? '') === 'partially_returned' || $refAmt > 0)
                                <span style="text-decoration:line-through; font-size:11px; color:#94A3B8;">₹{{ number_format($asale->total_amount, 2) }}</span>
                                <div style="color:#0F172A; font-size:13px; font-weight:900;">₹{{ number_format(max(0, (float)$asale->total_amount - $refAmt), 2) }}</div>
                                <div style="font-size:10px; color:#DC2626; font-weight:700;">-₹{{ number_format($refAmt, 2) }} ret</div>
                            @else
                                ₹{{ number_format($asale->total_amount, 2) }}
                            @endif
                        </td>
                        <td style="text-align:center; white-space:nowrap;">
                            <div style="display:inline-flex; gap:6px; align-items:center;">
                                <a href="{{ route('mobileshop.accessories.invoice', ['id' => $asale->id, 'print' => 1]) }}" target="_blank" rel="noopener" class="btn btn-outline btn-icon" title="Print Invoice" style="cursor:pointer;">
                                    <i data-lucide="printer" style="width:14px;height:14px;pointer-events:none;"></i>
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

                        $refAmt = (float)($asale->refund_amount ?? 0);
                    @endphp
                    <div class="sales-flat-row sales-row" data-type="accessory" data-date="{{ \Carbon\Carbon::parse($asale->created_at)->format('Y-m-d') }}">
                        <!-- Row 1: Invoice # + Payment Badge + Total Amount -->
                        <div class="row-line1">
                            <a href="{{ route('mobileshop.accessories.invoice', ['id' => $asale->id]) }}" target="_blank" rel="noopener" class="inv-num" title="View Invoice in New Tab">
                                {{ $asale->invoice_number }}
                            </a>
                            <span class="pay-badge" style="background:{{ $badgeBg }}; color:{{ $badgeColor }};">
                                {{ $asale->payment_mode }}
                                @if(($asale->status ?? '') === 'partially_returned')
                                    • Ret
                                @elseif(($asale->status ?? '') === 'voided')
                                    • Void
                                @endif
                            </span>
                            @if(($asale->status ?? '') === 'voided')
                                <div class="row-amount" style="color:#DC2626; text-decoration:line-through; font-size:12px;">₹{{ number_format($asale->total_amount, 2) }}</div>
                            @elseif(($asale->status ?? '') === 'partially_returned' || $refAmt > 0)
                                <div class="row-amount" style="color:#0F172A;">₹{{ number_format(max(0, (float)$asale->total_amount - $refAmt), 2) }}</div>
                            @else
                                <div class="row-amount">₹{{ number_format($asale->total_amount, 2) }}</div>
                            @endif
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
                                <a href="{{ route('mobileshop.accessories.invoice', ['id' => $asale->id, 'print' => 1]) }}" target="_blank" rel="noopener" class="compact-action-btn mobile-print-btn" title="Print Invoice" style="cursor:pointer;">
                                    <i data-lucide="printer" style="width:15px;height:15px;pointer-events:none;"></i>
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
                    <input type="hidden" name="reason_label" id="returnReasonLabel" value="Customer Return (Restock to Store Inventory)">
                    <input type="hidden" name="should_restock" id="returnShouldRestock" value="1">

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

                    <!-- 7 Return Reason Options (Restock Default) -->
                    <div class="form-group" style="margin-bottom:12px;">
                        <label class="form-label required" style="font-weight:700; color:#0F172A; font-size:12px; margin-bottom:4px;">Select Reason for Return</label>
                        <select name="return_reason_code" id="returnReasonSelect" class="form-control" onchange="onReturnReasonSelectChange(this.value)" style="font-weight:700; color:#0F172A; border-color:#CBD5E1;">
                            <option value="customer_return" data-restock="1" data-label="Customer Return (Restock to Store Inventory)" selected>1. Customer Return (Restock to Store Inventory)</option>
                            <option value="exchange" data-restock="1" data-label="Exchange for Different Product">2. Exchange for Different Product</option>
                            <option value="mind_change" data-restock="1" data-label="Customer Changed Mind / Unwanted">3. Customer Changed Mind / Unwanted</option>
                            <option value="model_mismatch" data-restock="1" data-label="Incorrect Model / Size Mismatch">4. Incorrect Model / Size Mismatch</option>
                            <option value="billing_error" data-restock="1" data-label="Billing Error / Duplicate Entry">5. Billing Error / Duplicate Entry</option>
                            <option value="defective_doa" data-restock="0" data-label="Defective / Faulty Item (Dead on Arrival)">6. Defective / Faulty Item (Dead on Arrival)</option>
                            <option value="warranty_issue" data-restock="0" data-label="Warranty / Quality Issue">7. Warranty / Quality Issue</option>
                        </select>
                    </div>

                    <!-- Dynamic Action Preview Notice -->
                    <div id="returnActionNotice" style="background:#F0FDF4; border:1px solid #BBF7D0; border-radius:8px; padding:10px 12px; margin-bottom:12px; font-size:12px; color:#166534; display:flex; align-items:flex-start; gap:8px;">
                        <i data-lucide="check-circle" style="width:16px;height:16px; flex-shrink:0; margin-top:1px; color:#16A34A;"></i>
                        <span id="returnActionText"><strong>Customer Return:</strong> Item WILL be added back into sellable store inventory and stock count increased.</span>
                    </div>

                    <!-- Restock Override Checkbox -->
                    <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:10px 12px; margin-bottom:14px; display:flex; align-items:center; gap:8px;">
                        <input type="checkbox" id="chkRestockOverride" checked onchange="onRestockOverrideChange(this.checked)" style="width:16px; height:16px; cursor:pointer;">
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
                                        {{ $c->name }} (Pending Balance: ₹{{ number_format($c->udhari_balance ?? 0, 2) }})
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
                                <option value="credit_udhari">📒 Credit / Balance Due</option>
                                <option value="split">⚖️ Split Payment</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                                <label class="form-label required" style="font-weight:700; color:#0F172A; font-size:12px; margin-bottom:0;">Amount Paid Now (₹)</label>
                                <div style="display:flex; gap:4px;">
                                    <button type="button" onclick="setFullPayment()" style="font-size:10px; padding:2px 8px; border-radius:4px; font-weight:700; color:#16A34A; border:1px solid #BBF7D0; background:#F0FDF4; cursor:pointer;">Full Paid</button>
                                    <button type="button" onclick="setZeroPayment()" style="font-size:10px; padding:2px 8px; border-radius:4px; font-weight:700; color:#DC2626; border:1px solid #FECDD3; background:#FFF1F2; cursor:pointer;">Pay Later (₹0)</button>
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
                            <span style="color:#475569;">Net Payable / Balance Due:</span>
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

        <!-- FAB Dropup Menu -->
        <div id="fabDropupMenu" class="fab-dropup-menu" style="display:none;">
            <a href="{{ route('mobileshop.accessories.pos') }}" class="fab-menu-item" style="color: #16A34A;">
                <i data-lucide="zap" style="width:16px;height:16px;"></i>
                <span>Sell Accessories & Parts</span>
            </a>
            <a href="{{ route('mobileshop.accessories.pos', ['category' => 'back_cover']) }}" class="fab-menu-item" style="color: #7C3AED;">
                <i data-lucide="package" style="width:16px;height:16px;"></i>
                <span>Back Covers & Tempered</span>
            </a>
            <a href="{{ route('mobileshop.accessories.purchase') }}" class="fab-menu-item" style="color: #5E6AD2;">
                <i data-lucide="truck" style="width:16px;height:16px;"></i>
                <span>Restock Inventory</span>
            </a>
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
        'customer_return': {
            restock: true,
            label: 'Customer Return (Restock to Store Inventory)',
            notice: '<strong>Customer Return:</strong> Item WILL be added back into sellable store inventory and stock count increased.',
            bg: '#F0FDF4', border: '#BBF7D0', text: '#166534'
        },
        'exchange': {
            restock: true,
            label: 'Exchange for Different Product',
            notice: '<strong>Exchange Return:</strong> Original item restocked to inventory so customer can be billed for exchange.',
            bg: '#F0FDF4', border: '#BBF7D0', text: '#166534'
        },
        'mind_change': {
            restock: true,
            label: 'Customer Changed Mind / Unwanted',
            notice: '<strong>Unwanted Return (Unopened):</strong> Item will be restocked into available sellable inventory.',
            bg: '#F0FDF4', border: '#BBF7D0', text: '#166534'
        },
        'model_mismatch': {
            restock: true,
            label: 'Incorrect Model / Size Mismatch',
            notice: '<strong>Model Mismatch (Brand New Item):</strong> Item will be restocked into available sellable inventory.',
            bg: '#F0FDF4', border: '#BBF7D0', text: '#166534'
        },
        'billing_error': {
            restock: true,
            label: 'Billing Error / Duplicate Entry',
            notice: '<strong>Billing Mistake Reversal:</strong> Mistake voided and item stock restored to original inventory count.',
            bg: '#F0FDF4', border: '#BBF7D0', text: '#166534'
        },
        'defective_doa': {
            restock: false,
            label: 'Defective / Faulty Item (Dead on Arrival)',
            notice: '<strong>Defective / Damaged Item:</strong> Item will NOT be added to sellable stock. Quarantined as damaged / scrap.',
            bg: '#FFF1F2', border: '#FECDD3', text: '#991B1B'
        },
        'warranty_issue': {
            restock: false,
            label: 'Warranty / Quality Issue',
            notice: '<strong>Warranty Claim:</strong> Defective item will NOT be added to sellable stock. Routed to Supplier RMA.',
            bg: '#FFF1F2', border: '#FECDD3', text: '#991B1B'
        }
    };

    function onReturnReasonSelectChange(reasonCode) {
        const info = returnReasonActions[reasonCode] || returnReasonActions['customer_return'];
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

        // Reset to default option: Customer Return (Restock)
        document.getElementById('returnReasonSelect').value = 'customer_return';
        document.getElementById('returnReasonInput').value = '';
        onReturnReasonSelectChange('customer_return');

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
    window.addEventListener('pageshow', function () {
        initSalesPage();
    });

    /* ─── Global Inventory & Customer Data ─── */
    window.allPartsData = @json($parts ?? []);
    window.customersList = @json($customers ?? []);

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    /* ─── Generic Row Item Autocomplete (Quick Sale & Bulk Sale) ─── */
    function onRowItemSearchInput(inputEl) {
        const wrapper = inputEl.closest('.search-picker-wrapper');
        const dropdown = wrapper?.querySelector('.search-picker-dropdown');
        const clearBtn = wrapper?.querySelector('.btn-item-clear');
        const row = inputEl.closest('.item-search-row');
        const partIdInput = row?.querySelector('.input-part-id');
        const rawVal = inputEl.value || '';
        const query = rawVal.trim().toLowerCase();

        if (!dropdown) return;

        if (query.length === 0) {
            dropdown.style.display = 'none';
            dropdown.innerHTML = '';
            if (clearBtn) clearBtn.style.display = 'none';
            if (partIdInput) partIdInput.value = '';
            if (row.closest('#bulkSaleModal')) {
                recalcBulkCustomerTotals(row.closest('.bulk-customer-card'));
            } else {
                recalcQuickSaleRow(row);
            }
            return;
        }

        if (clearBtn) clearBtn.style.display = 'block';

        const matches = (window.allPartsData || []).filter(item => {
            const name = (item.name || '').toLowerCase();
            const cat = (item.category || '').toLowerCase();
            return name.includes(query) || cat.includes(query);
        }).slice(0, 30);

        if (matches.length === 0) {
            dropdown.innerHTML = `
                <div style="padding: 10px 12px; text-align: center; color: #64748b; font-size: 12px;">
                    No inventory match for "<strong>${escapeHtml(rawVal.trim())}</strong>"
                    <div style="font-size: 11px; color:#4338ca; margin-top: 4px; font-weight: 600;">Custom item will be created with custom price</div>
                </div>
            `;
        } else {
            dropdown.innerHTML = matches.map(item => {
                const price = parseFloat(item.selling_price || 0);
                const stock = parseInt(item.stock_qty || 0);
                const stockBadgeColor = stock > 0 ? '#059669' : '#dc2626';
                const stockBadgeBg = stock > 0 ? '#ecfdf5' : '#fef2f2';
                const categoryText = item.category ? escapeHtml(item.category) : 'General';
                const safeName = escapeHtml(item.name);
                return `
                    <div class="search-picker-item" onclick="selectRowItem(this, ${item.id})">
                        <div style="display:flex; flex-direction:column; gap:2px; text-align:left; overflow:hidden;">
                            <span style="font-weight:600; font-size:13px; color:#0f172a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${safeName}</span>
                            <div style="display:flex; align-items:center; gap:6px; font-size:11px; color:#64748b;">
                                <span style="background:#f1f5f9; padding:1px 5px; border-radius:4px;">${categoryText}</span>
                                <span>•</span>
                                <span style="background:${stockBadgeBg}; color:${stockBadgeColor}; padding:1px 5px; border-radius:4px; font-weight:600;">Stock: ${stock}</span>
                            </div>
                        </div>
                        <div style="text-align:right; flex-shrink:0; padding-left:12px;">
                            <div style="font-weight:700; font-size:13px; color:#059669;">₹${price.toFixed(2)}</div>
                        </div>
                    </div>
                `;
            }).join('');
        }
        dropdown.style.display = 'block';
    }

    function selectRowItem(dropdownItemEl, partId) {
        const row = dropdownItemEl.closest('.item-search-row');
        const item = (window.allPartsData || []).find(p => String(p.id) === String(partId));
        if (!row || !item) return;

        const input = row.querySelector('.input-item-name');
        const partIdInput = row.querySelector('.input-part-id');
        const priceInput = row.querySelector('.input-unit-price');
        const dropdown = row.querySelector('.search-picker-dropdown');
        const clearBtn = row.querySelector('.btn-item-clear');

        if (input) input.value = item.name;
        if (partIdInput) partIdInput.value = item.id;
        if (dropdown) {
            dropdown.style.display = 'none';
            dropdown.innerHTML = '';
        }
        if (clearBtn) clearBtn.style.display = 'block';

        const price = parseFloat(item.selling_price || 0);
        if (priceInput) {
            priceInput.value = price > 0 ? price : '';
            priceInput.placeholder = '₹' + price.toFixed(2);
        }

        if (row.closest('#bulkSaleModal')) {
            recalcBulkCustomerTotals(row.closest('.bulk-customer-card'));
        } else {
            recalcQuickSaleRow(row);
        }
    }

    function clearRowItemSelection(btnEl) {
        const row = btnEl.closest('.item-search-row');
        if (!row) return;

        const input = row.querySelector('.input-item-name');
        const partIdInput = row.querySelector('.input-part-id');
        const priceInput = row.querySelector('.input-unit-price');
        const dropdown = row.querySelector('.search-picker-dropdown');

        if (input) {
            input.value = '';
            input.focus();
        }
        if (partIdInput) partIdInput.value = '';
        if (priceInput) {
            priceInput.value = '';
            priceInput.placeholder = 'Price (₹)';
        }
        if (dropdown) {
            dropdown.style.display = 'none';
            dropdown.innerHTML = '';
        }
        btnEl.style.display = 'none';

        if (row.closest('#bulkSaleModal')) {
            recalcBulkCustomerTotals(row.closest('.bulk-customer-card'));
        } else {
            recalcQuickSaleRow(row);
        }
    }

    // Dismiss open search dropdowns on document click
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.search-picker-wrapper')) {
            document.querySelectorAll('.search-picker-dropdown').forEach(d => {
                d.style.display = 'none';
            });
        }
    });

    /* ─── Multi-Item Quick Sale Handlers ─── */
    let quickItemRowCounter = 1;

    function addQuickSaleItemRow() {
        const container = document.getElementById('quickSaleItemsContainer');
        if (!container) return;

        const idx = quickItemRowCounter++;
        const row = document.createElement('div');
        row.className = 'quick-item-row item-search-row';
        row.dataset.index = idx;
        row.id = `quickItemRow_${idx}`;
        row.innerHTML = `
            <div class="search-picker-wrapper col-item-search" style="position: relative;">
                <input type="text"
                       name="items[${idx}][part_name]"
                       class="app-input-field input-item-name"
                       style="padding-right: 30px;"
                       placeholder="Type item name to search or enter custom item..."
                       autocomplete="off"
                       required
                       oninput="onRowItemSearchInput(this)">
                <input type="hidden" name="items[${idx}][part_id]" class="input-part-id">
                <button type="button"
                        class="btn-item-clear"
                        onclick="clearRowItemSelection(this)"
                        title="Clear item"
                        style="display:none; position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: #e2e8f0; border: none; border-radius: 50%; width: 20px; height: 20px; font-size: 11px; line-height: 20px; text-align: center; color: #475569; cursor: pointer; padding: 0;">✕</button>
                <div class="search-picker-dropdown"></div>
            </div>

            <div>
                <input type="number"
                       name="items[${idx}][quantity]"
                       class="app-input-field input-quantity"
                       placeholder="Qty"
                       value="1"
                       min="1"
                       required
                       oninput="recalcQuickSaleRow(this)">
            </div>

            <div>
                <input type="number"
                       step="0.01"
                       name="items[${idx}][unit_price]"
                       class="app-input-field input-unit-price"
                       placeholder="Price (₹)"
                       required
                       oninput="recalcQuickSaleRow(this)">
            </div>

            <div class="line-total-cell" style="text-align: right; font-weight: 700; font-size: 13px; color: #0f172a; white-space: nowrap;">
                ₹0.00
            </div>

            <div class="col-total-remove" style="text-align: center;">
                <button type="button"
                        class="btn-remove-row"
                        onclick="removeQuickSaleItemRow(this)"
                        title="Remove item"
                        style="background: #fee2e2; border: 1px solid #fecdd3; border-radius: 6px; color: #dc2626; width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; font-size: 12px;">✕</button>
            </div>
        `;

        container.appendChild(row);
        updateQuickSaleRemoveButtons();
        recalcAllQuickSale();
        row.querySelector('.input-item-name')?.focus();
        if (window.refreshIcons) window.refreshIcons();
    }

    function removeQuickSaleItemRow(btnEl) {
        const row = btnEl.closest('.quick-item-row');
        if (!row) return;
        const container = document.getElementById('quickSaleItemsContainer');
        const allRows = container ? container.querySelectorAll('.quick-item-row') : [];
        if (allRows.length <= 1) return; // Keep at least one row

        row.remove();
        updateQuickSaleRemoveButtons();
        recalcAllQuickSale();
    }

    function updateQuickSaleRemoveButtons() {
        const container = document.getElementById('quickSaleItemsContainer');
        if (!container) return;
        const allRows = container.querySelectorAll('.quick-item-row');
        allRows.forEach(r => {
            const btn = r.querySelector('.btn-remove-row');
            if (btn) {
                if (allRows.length <= 1) {
                    btn.disabled = true;
                    btn.style.background = '#f1f5f9';
                    btn.style.color = '#94a3b8';
                    btn.style.borderColor = '#e2e8f0';
                    btn.style.cursor = 'not-allowed';
                } else {
                    btn.disabled = false;
                    btn.style.background = '#fee2e2';
                    btn.style.color = '#dc2626';
                    btn.style.borderColor = '#fecdd3';
                    btn.style.cursor = 'pointer';
                }
            }
        });

        const countText = document.getElementById('quickItemCountText');
        const countBadge = document.getElementById('quickItemCountBadge');
        if (countText) countText.textContent = allRows.length;
        if (countBadge) countBadge.textContent = `${allRows.length} ${allRows.length === 1 ? 'Item' : 'Items'}`;
    }

    function recalcQuickSaleRow(el) {
        const row = el.closest('.quick-item-row');
        if (!row) return;

        const qty = Math.max(1, parseInt(row.querySelector('.input-quantity')?.value || 1));
        const price = parseFloat(row.querySelector('.input-unit-price')?.value) || 0;
        const lineTotal = qty * price;

        const cell = row.querySelector('.line-total-cell');
        if (cell) cell.textContent = '₹' + lineTotal.toFixed(2);

        recalcAllQuickSale();
    }

    function recalcAllQuickSale() {
        const container = document.getElementById('quickSaleItemsContainer');
        if (!container) return;

        let grandTotal = 0;
        container.querySelectorAll('.quick-item-row').forEach(row => {
            const qty = Math.max(1, parseInt(row.querySelector('.input-quantity')?.value || 1));
            const price = parseFloat(row.querySelector('.input-unit-price')?.value) || 0;
            grandTotal += qty * price;
        });

        const badge = document.getElementById('quickSaleTotalBadge');
        if (badge) badge.textContent = 'Total: ₹' + grandTotal.toFixed(2);

        const mode = document.getElementById('quickSalePaymentMode')?.value || 'cash';
        const amountPaidInput = document.getElementById('quickSaleAmountPaid');
        if (amountPaidInput) {
            if (['cash', 'upi', 'card', 'cash+upi', 'upi+cash'].includes(mode)) {
                amountPaidInput.value = grandTotal.toFixed(2);
            } else if (mode === 'udhari') {
                amountPaidInput.value = '0';
            }
        }
        syncQuickSplitAmounts(mode, grandTotal);
    }

    function getQuickSaleCurrentTotal() {
        const container = document.getElementById('quickSaleItemsContainer');
        if (!container) return 0;
        let grandTotal = 0;
        container.querySelectorAll('.quick-item-row').forEach(row => {
            const qty = Math.max(1, parseInt(row.querySelector('.input-quantity')?.value || 1));
            const price = parseFloat(row.querySelector('.input-unit-price')?.value) || 0;
            grandTotal += qty * price;
        });
        return grandTotal;
    }

    function onQuickPhoneChange(inputEl) {
        const phone = (inputEl.value || '').trim();
        if (!phone) return;
        const datalist = document.getElementById('quickCustPhones');
        if (datalist) {
            for (let opt of datalist.options) {
                if (opt.value === phone && opt.textContent) {
                    const nameInput = document.getElementById('quickSaleCustName');
                    if (nameInput && (!nameInput.value || nameInput.value === 'Walk-in Customer')) {
                        nameInput.value = opt.textContent;
                    }
                    break;
                }
            }
        }
    }

    function onQuickPaymentModeChange(selectEl) {
        const mode = selectEl.value;
        const total = getQuickSaleCurrentTotal();
        const splitRow = document.getElementById('quickSplitRow');
        if (!splitRow) return;

        if (mode === 'cash+upi' || mode === 'upi+cash') {
            splitRow.style.display = 'grid';
            const half = (total / 2).toFixed(2);
            const rem = (total - parseFloat(half)).toFixed(2);
            splitRow.innerHTML = `
                <div>
                    <label style="font-size:11px;color:#475569;font-weight:700;display:block;margin-bottom:4px;">Cash Amount (₹)</label>
                    <input type="number" step="0.01" name="cash_amount" id="quickSplitCash" class="app-input-field" placeholder="Cash Amount" value="${half}" oninput="onQuickCashSplitChange(this, ${total})">
                </div>
                <div>
                    <label style="font-size:11px;color:#475569;font-weight:700;display:block;margin-bottom:4px;">UPI Amount (₹)</label>
                    <input type="number" step="0.01" name="upi_amount" id="quickSplitUpi" class="app-input-field" placeholder="UPI Amount" value="${rem}" oninput="onQuickUpiSplitChange(this, ${total})">
                </div>
            `;
        } else if (mode === 'cash+udhari') {
            splitRow.style.display = 'grid';
            const paid = (total > 0 ? (total * 0.5) : 0).toFixed(2);
            const udhari = (total - paid).toFixed(2);
            splitRow.innerHTML = `
                <div>
                    <label style="font-size:11px;color:#475569;font-weight:700;display:block;margin-bottom:4px;">Cash Paid (₹)</label>
                    <input type="number" step="0.01" name="cash_amount" id="quickSplitCashPaid" class="app-input-field" placeholder="Cash Paid" value="${paid}" oninput="onQuickCashUdhariChange(this, ${total})">
                </div>
                <div style="display:flex;flex-direction:column;justify-content:center;">
                    <span style="font-size:11px;color:#475569;font-weight:700;margin-bottom:4px;">Net Payable / Balance Due</span>
                    <span id="quickUdhariBadge" style="font-size:14px;font-weight:800;color:#dc2626;">Balance Due: ₹${udhari}</span>
                </div>
            `;
        } else if (mode === 'upi+udhari') {
            splitRow.style.display = 'grid';
            const paid = (total > 0 ? (total * 0.5) : 0).toFixed(2);
            const udhari = (total - paid).toFixed(2);
            splitRow.innerHTML = `
                <div>
                    <label style="font-size:11px;color:#475569;font-weight:700;display:block;margin-bottom:4px;">UPI Paid (₹)</label>
                    <input type="number" step="0.01" name="upi_amount" id="quickSplitUpiPaid" class="app-input-field" placeholder="UPI Paid" value="${paid}" oninput="onQuickUpiUdhariChange(this, ${total})">
                </div>
                <div style="display:flex;flex-direction:column;justify-content:center;">
                    <span style="font-size:11px;color:#475569;font-weight:700;margin-bottom:4px;">Net Payable / Balance Due</span>
                    <span id="quickUdhariBadge" style="font-size:14px;font-weight:800;color:#dc2626;">Balance Due: ₹${udhari}</span>
                </div>
            `;
        } else if (mode === 'udhari') {
            splitRow.style.display = 'grid';
            splitRow.innerHTML = `
                <div style="grid-column: 1 / -1; font-size:12px; color:#92400e; background:#fffbeb; border:1px solid #fde68a; border-radius:6px; padding: 10px 14px; display:flex; align-items:center; gap:8px;">
                    <i data-lucide="info" style="width:16px;height:16px;color:#d97706;flex-shrink:0;"></i>
                    <span>Full invoice amount (<strong>₹${total.toFixed(2)}</strong>) will be recorded as <strong>Balance Due</strong>.</span>
                </div>
            `;
            if (window.refreshIcons) window.refreshIcons();
        } else {
            splitRow.style.display = 'none';
            splitRow.innerHTML = '';
        }
    }

    function syncQuickSplitAmounts(mode, total) {
        if (mode === 'cash+upi' || mode === 'upi+cash') {
            const cashEl = document.getElementById('quickSplitCash');
            const upiEl = document.getElementById('quickSplitUpi');
            if (cashEl && upiEl) {
                const cash = Math.min(total, parseFloat(cashEl.value) || 0);
                upiEl.value = Math.max(0, total - cash).toFixed(2);
            }
        } else if (mode === 'cash+udhari') {
            const cashEl = document.getElementById('quickSplitCashPaid');
            const udhariBadge = document.getElementById('quickUdhariBadge');
            if (cashEl && udhariBadge) {
                const cash = Math.min(total, parseFloat(cashEl.value) || 0);
                udhariBadge.textContent = 'Balance Due: ₹' + Math.max(0, total - cash).toFixed(2);
            }
        } else if (mode === 'upi+udhari') {
            const upiEl = document.getElementById('quickSplitUpiPaid');
            const udhariBadge = document.getElementById('quickUdhariBadge');
            if (upiEl && udhariBadge) {
                const upi = Math.min(total, parseFloat(upiEl.value) || 0);
                udhariBadge.textContent = 'Balance Due: ₹' + Math.max(0, total - upi).toFixed(2);
            }
        }
    }

    function onQuickCashSplitChange(inputEl, total) {
        const cash = Math.max(0, Math.min(total, parseFloat(inputEl.value) || 0));
        const upiEl = document.getElementById('quickSplitUpi');
        if (upiEl) upiEl.value = Math.max(0, total - cash).toFixed(2);
    }

    function onQuickUpiSplitChange(inputEl, total) {
        const upi = Math.max(0, Math.min(total, parseFloat(inputEl.value) || 0));
        const cashEl = document.getElementById('quickSplitCash');
        if (cashEl) cashEl.value = Math.max(0, total - upi).toFixed(2);
    }

    function onQuickCashUdhariChange(inputEl, total) {
        const cash = Math.max(0, parseFloat(inputEl.value) || 0);
        const udhariBadge = document.getElementById('quickUdhariBadge');
        if (udhariBadge) {
            udhariBadge.textContent = 'Balance Due: ₹' + Math.max(0, total - cash).toFixed(2);
        }
    }

    function onQuickUpiUdhariChange(inputEl, total) {
        const upi = Math.max(0, parseFloat(inputEl.value) || 0);
        const udhariBadge = document.getElementById('quickUdhariBadge');
        if (udhariBadge) {
            udhariBadge.textContent = 'Balance Due: ₹' + Math.max(0, total - upi).toFixed(2);
        }
    }

    function showQuickSaleWarning(msg, targetInput = null) {
        const alertBox = document.getElementById('quickSaleAlertBox');
        if (alertBox) {
            alertBox.innerHTML = `
                <div style="display:flex; align-items:center; gap:8px; justify-content:space-between; width:100%;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <span style="font-size:16px;">⚠️</span>
                        <span>${escapeHtml(msg)}</span>
                    </div>
                    <button type="button" onclick="this.closest('#quickSaleAlertBox').style.display='none'" style="background:none;border:none;color:#991b1b;font-weight:800;font-size:14px;cursor:pointer;">✕</button>
                </div>
            `;
            alertBox.style.display = 'block';
            alertBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } else {
            alert(msg);
        }
        if (targetInput) {
            targetInput.focus();
            targetInput.style.borderColor = '#ef4444';
            targetInput.style.backgroundColor = '#fff5f5';
            setTimeout(() => {
                targetInput.style.borderColor = '';
                targetInput.style.backgroundColor = '';
            }, 4500);
        }
    }

    function validateQuickSaleForm(e) {
        const alertBox = document.getElementById('quickSaleAlertBox');
        if (alertBox) alertBox.style.display = 'none';

        const container = document.getElementById('quickSaleItemsContainer');
        const rows = container ? container.querySelectorAll('.quick-item-row') : [];
        if (rows.length === 0) {
            showQuickSaleWarning('Please add at least one item to sell.');
            if (e) e.preventDefault();
            return false;
        }

        for (let i = 0; i < rows.length; i++) {
            const r = rows[i];
            const nameInput = r.querySelector('.input-item-name');
            const name = (nameInput?.value || '').trim();
            if (!name) {
                showQuickSaleWarning(`Item #${i + 1}: Please select or enter an item name.`, nameInput);
                if (e) e.preventDefault();
                return false;
            }

            const qtyInput = r.querySelector('.input-quantity');
            const qty = parseInt(qtyInput?.value || 0);
            if (isNaN(qty) || qty < 1) {
                showQuickSaleWarning(`Item #${i + 1} (${name}): Quantity must be at least 1.`, qtyInput);
                if (e) e.preventDefault();
                return false;
            }

            const priceInput = r.querySelector('.input-unit-price');
            const price = parseFloat(priceInput?.value);
            if (isNaN(price) || price < 0) {
                showQuickSaleWarning(`Item #${i + 1} (${name}): Unit price cannot be empty or negative.`, priceInput);
                if (e) e.preventDefault();
                return false;
            }
        }

        const total = getQuickSaleCurrentTotal();
        if (total <= 0) {
            showQuickSaleWarning('Total bill amount must be greater than zero.');
            if (e) e.preventDefault();
            return false;
        }

        const mode = document.getElementById('quickSalePaymentMode')?.value || 'cash';
        const amountPaidInput = document.getElementById('quickSaleAmountPaid');

        if (mode === 'cash+upi' || mode === 'upi+cash') {
            const cashInput = document.getElementById('quickSplitCash');
            const upiInput = document.getElementById('quickSplitUpi');
            const cashVal = parseFloat(cashInput?.value) || 0;
            const upiVal = parseFloat(upiInput?.value) || 0;

            if (cashVal < 0 || upiVal < 0) {
                showQuickSaleWarning('Split cash and UPI amounts cannot be negative.', cashVal < 0 ? cashInput : upiInput);
                if (e) e.preventDefault();
                return false;
            }
            if (Math.abs((cashVal + upiVal) - total) > 0.05) {
                showQuickSaleWarning(`Split Cash (₹${cashVal.toFixed(2)}) + UPI (₹${upiVal.toFixed(2)}) total ₹${(cashVal + upiVal).toFixed(2)}, which does not match total amount ₹${total.toFixed(2)}. Please balance the split.`, cashInput);
                if (e) e.preventDefault();
                return false;
            }
            if (amountPaidInput) amountPaidInput.value = total.toFixed(2);
        } else if (mode === 'cash+udhari') {
            const cashInput = document.getElementById('quickSplitCashPaid');
            const cashPaid = parseFloat(cashInput?.value) || 0;
            if (cashPaid < 0) {
                showQuickSaleWarning('Cash paid cannot be negative.', cashInput);
                if (e) e.preventDefault();
                return false;
            }
            if (cashPaid > total) {
                showQuickSaleWarning(`Cash paid (₹${cashPaid.toFixed(2)}) cannot exceed total sale amount (₹${total.toFixed(2)}).`, cashInput);
                if (e) e.preventDefault();
                return false;
            }
            if (amountPaidInput) amountPaidInput.value = cashPaid.toFixed(2);
        } else if (mode === 'upi+udhari') {
            const upiInput = document.getElementById('quickSplitUpiPaid');
            const upiPaid = parseFloat(upiInput?.value) || 0;
            if (upiPaid < 0) {
                showQuickSaleWarning('UPI paid cannot be negative.', upiInput);
                if (e) e.preventDefault();
                return false;
            }
            if (upiPaid > total) {
                showQuickSaleWarning(`UPI paid (₹${upiPaid.toFixed(2)}) cannot exceed total sale amount (₹${total.toFixed(2)}).`, upiInput);
                if (e) e.preventDefault();
                return false;
            }
            if (amountPaidInput) amountPaidInput.value = upiPaid.toFixed(2);
        } else if (mode === 'udhari') {
            if (amountPaidInput) amountPaidInput.value = '0';
        } else {
            if (amountPaidInput) amountPaidInput.value = total.toFixed(2);
        }

        const custNameInput = document.getElementById('quickSaleCustName');
        if (custNameInput && !custNameInput.value.trim()) {
            custNameInput.value = 'Walk-in Customer';
        }

        const custPhoneInput = document.getElementById('quickSaleCustPhone');
        if (custPhoneInput && !custPhoneInput.value.trim()) {
            custPhoneInput.value = '9999999999';
        }

        const submitBtn = document.getElementById('quickSaleSubmitBtn');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processing Sale...';
        }

        return true;
    }


</script>
@endpush
