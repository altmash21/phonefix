@extends('mobileshop.layout')

@section('title', 'Register New Phone Sale — MobiTrack')
@section('page-title', 'Register Sale')

@section('page-actions')
    <button type="button" class="btn btn-primary btn-sm" onclick="triggerEmiScan()" style="display:inline-flex; align-items:center; gap:6px; background:linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%); border:none; box-shadow:0 2px 6px rgba(79,70,229,0.35); font-weight:600;">
        <i data-lucide="scan" style="width:14px;height:14px;"></i> ⚡ Scan EMI Slip / Bill
    </button>
    <a href="{{ route('mobileshop.sales') }}" class="btn btn-outline btn-sm">
        <i data-lucide="arrow-left" style="width:13px;height:13px;"></i> Back to Sales
    </a>
@endsection

@section('content')
<div style="max-width: 1280px; margin: 0 auto; padding-bottom: 50px;">

    <!-- ═══════════ AI OCR SLIP SCAN BANNER ═══════════ -->
    <div style="background: linear-gradient(135deg, #EEF2FF 0%, #FAF5FF 100%); border: 1px solid #C7D2FE; border-radius: 12px; padding: 14px 18px; margin-bottom: 18px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; box-shadow: 0 2px 8px rgba(79,70,229,0.06);">
        <div style="display:flex; align-items:center; gap:12px;">
            <div style="width:40px; height:40px; border-radius:10px; background:linear-gradient(135deg, #4F46E5, #7C3AED); display:flex; align-items:center; justify-content:center; color:#fff; flex-shrink:0;">
                <i data-lucide="scan-line" style="width:20px; height:20px;"></i>
            </div>
            <div>
                <div style="font-weight:700; font-size:14px; color:#1E1B4B; display:flex; align-items:center; gap:6px;">
                    Instant EMI Bill & Receipt Auto-Fill (AI OCR)
                    <span class="badge" style="background:#4F46E5; color:#fff; font-size:10px; padding:2px 7px; border-radius:999px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">Gemini 1.5</span>
                </div>
                <div style="font-size:12px; color:#4338CA; margin-top:2px;">
                    Upload customer finance slip, challan, or invoice photo/PDF. Review and edit extracted fields, confirm, and boom — form is populated!
                </div>
            </div>
        </div>
        <div style="display:flex; gap:8px;">
            <input type="file" id="emiBillFileInput" accept="image/*,application/pdf" style="display:none;" onchange="handleEmiBillUpload(this)">
            <button type="button" class="btn btn-sm" onclick="triggerEmiScan()" style="background:#4F46E5; color:#fff; border:none; font-weight:600; padding:8px 16px; border-radius:8px; display:flex; align-items:center; gap:6px; box-shadow:0 2px 6px rgba(79,70,229,0.3); cursor:pointer;">
                <i data-lucide="upload-cloud" style="width:14px; height:14px;"></i> Upload Slip / Invoice
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="flash-success" style="border-radius:10px; margin-bottom:16px; padding:12px 16px; background:#ECFDF5; border:1px solid #A7F3D0; color:#065F46; display:flex; align-items:center; gap:8px;">
            <i data-lucide="check-circle-2" style="width:18px;height:18px; color:#10B981;"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flash-error" style="border-radius:10px; margin-bottom:16px; padding:12px 16px; background:#FEF2F2; border:1px solid #FECACA; color:#991B1B; display:flex; align-items:center; gap:8px;">
            <i data-lucide="alert-circle" style="width:18px;height:18px; color:#EF4444;"></i> {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('mobileshop.sales.store_multi') }}" id="newPhoneSaleForm" onsubmit="return validateSaleForm()">
        @csrf

        <div style="display: flex; gap: 22px; align-items: flex-start; flex-wrap: wrap;">

            <!-- ════════════════════ LEFT COLUMN: CUSTOMER, DEVICE SELECTION & GIFTS ════════════════════ -->
            <div style="flex: 1 1 680px; min-width: 0; display: flex; flex-direction: column; gap: 18px;">

                <!-- ── 1. CUSTOMER DETAILS ── -->
                <div class="card" style="box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #E2E8F0; border-radius: 12px;">
                    <div class="card-header" style="background:#F8FAFC; border-bottom:1px solid #E2E8F0; padding:12px 18px;">
                        <div class="card-title" style="font-size:14px; font-weight:700; color:#1E293B; display:flex; align-items:center; gap:8px;">
                            <i data-lucide="user" style="width:16px;height:16px; color:var(--color-primary);"></i> Customer Details
                        </div>
                    </div>
                    <div class="card-body" style="padding:16px 18px;">
                        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap:14px;">
                            <div class="form-group" style="margin:0;">
                                <label class="form-label" style="font-size:12px; font-weight:600; color:#475569; margin-bottom:5px;">Customer Phone <span style="color:#EF4444;">*</span></label>
                                <div style="position:relative;">
                                    <input type="text" class="form-control" name="customer_phone" id="customerPhone" list="customerPhoneList" required placeholder="10-digit mobile number" style="font-weight:600; padding-left:34px;">
                                    <i data-lucide="phone" style="width:14px;height:14px; position:absolute; left:11px; top:11px; color:#94A3B8;"></i>
                                </div>
                                <datalist id="customerPhoneList">
                                    @foreach($customers as $c)
                                        <option value="{{ $c->phone }}">{{ $c->name }} — Bal: ₹{{ number_format($c->udhari_balance, 0) }}</option>
                                    @endforeach
                                </datalist>
                            </div>
                            <div class="form-group" style="margin:0;">
                                <label class="form-label" style="font-size:12px; font-weight:600; color:#475569; margin-bottom:5px;">Customer Name <span style="color:#EF4444;">*</span></label>
                                <input type="text" class="form-control" name="customer_name" id="customerName" required placeholder="Full name of customer" style="font-weight:600;">
                            </div>
                            <div class="form-group" style="margin:0;">
                                <label class="form-label" style="font-size:12px; font-weight:600; color:#475569; margin-bottom:5px;">Current Khata Balance</label>
                                <input type="text" class="form-control" id="customerBalanceDisplay" readonly value="—" style="background:#F8FAFC; font-weight:800; color:#475569;">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── 2. PHONE SELECTION (HIERARCHICAL & SEARCH) ── -->
                <div class="card" style="box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #E2E8F0; border-radius: 12px;">
                    <div class="card-header" style="background:#F8FAFC; border-bottom:1px solid #E2E8F0; padding:12px 18px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                        <div class="card-title" style="font-size:14px; font-weight:700; color:#1E293B; display:flex; align-items:center; gap:8px;">
                            <i data-lucide="smartphone" style="width:16px;height:16px; color:var(--color-primary);"></i> Select Phone(s)
                            <span class="badge" style="background:#EEF2FF; color:#4F46E5; font-size:11px; padding:3px 8px; border-radius:6px; font-weight:700;">{{ $inStockDevices->count() }} In Stock</span>
                        </div>
                        <div style="position:relative; width:260px;">
                            <input type="text" class="form-control" id="quickImeiSearch" placeholder="⚡ Scan / Type IMEI or Model…" style="font-size:12px; padding-left:30px; height:34px;" oninput="onQuickSearchInput()">
                            <i data-lucide="search" style="width:14px;height:14px; position:absolute; left:9px; top:10px; color:#94A3B8;"></i>
                            <div id="quickSearchResults" style="display:none; position:absolute; top:36px; left:0; right:0; background:#fff; border:1px solid #CBD5E1; border-radius:8px; box-shadow:0 8px 20px rgba(0,0,0,0.12); max-height:220px; overflow-y:auto; z-index:50;"></div>
                        </div>
                    </div>

                    <div class="card-body" style="padding:16px 18px;">

                        <!-- STEP-BY-STEP CASCADING SELECTOR -->
                        <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:10px; padding:14px 16px; margin-bottom:16px;">
                            <div style="font-size:11px; font-weight:800; color:#64748B; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:10px;">
                                Step-by-Step Device Selector (100s of Phones Made Easy)
                            </div>
                            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) 120px; gap:10px; align-items:flex-end;">
                                <div>
                                    <label class="form-label" style="font-size:11px; font-weight:700; color:#334155; margin-bottom:4px;">1. Brand</label>
                                    <select class="form-control" id="brandSelect" onchange="onBrandChange()" style="font-weight:600;">
                                        <option value="">— Select Brand —</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label" style="font-size:11px; font-weight:700; color:#334155; margin-bottom:4px;">2. Phone Model</label>
                                    <select class="form-control" id="modelSelect" onchange="onModelChange()" disabled style="font-weight:600;">
                                        <option value="">— Select Model —</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label" style="font-size:11px; font-weight:700; color:#334155; margin-bottom:4px;">3. Variant & IMEI</label>
                                    <select class="form-control" id="variantSelect" disabled style="font-weight:600;">
                                        <option value="">— Select Variant / IMEI —</option>
                                    </select>
                                </div>
                                <div>
                                    <button type="button" class="btn btn-primary" id="btnAddDevice" onclick="addSelectedDevice()" disabled style="width:100%; height:38px; font-size:12px; font-weight:700; display:flex; align-items:center; justify-content:center; gap:5px;">
                                        <i data-lucide="plus" style="width:14px;height:14px;"></i> Add Phone
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- SELECTED DEVICES LIST / CART -->
                        <div>
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                                <span style="font-size:12px; font-weight:700; color:#1E293B;">Selected Device(s) in this Sale:</span>
                                <span id="selectedCountBadge" style="font-size:11px; font-weight:700; color:#64748B;">0 devices selected</span>
                            </div>

                            <div id="emptyDevicesState" style="text-align:center; padding:32px 20px; border:2px dashed #E2E8F0; border-radius:10px; background:#FAFAFA;">
                                <i data-lucide="smartphone" style="width:28px;height:28px; color:#94A3B8; margin-bottom:6px;"></i>
                                <div style="font-size:13px; font-weight:600; color:#64748B;">No device added yet</div>
                                <div style="font-size:11.5px; color:#94A3B8;">Choose a Brand, Model & Variant above or scan an IMEI to add to this sale.</div>
                            </div>

                            <div id="selectedDevicesTableWrap" style="display:none; overflow-x:auto;">
                                <table class="data-table" style="width:100%; font-size:12.5px;">
                                    <thead>
                                        <tr style="background:#F1F5F9; color:#475569;">
                                            <th style="width:22%;">Brand & Model</th>
                                            <th style="width:18%;">Variant & Color</th>
                                            <th style="width:20%;">IMEI 1</th>
                                            <th style="width:14%; text-align:right;">Cost (₹)</th>
                                            <th style="width:18%; text-align:right;">Sale Price (₹)</th>
                                            <th style="width:8%; text-align:center;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="selectedDevicesList">
                                        <!-- Dynamically added rows -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- ── 3. PROMOTIONAL GIFTS & FREEBIES ── -->
                <div class="card" style="box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #E2E8F0; border-radius: 12px;">
                    <div class="card-header" style="background:#F8FAFC; border-bottom:1px solid #E2E8F0; padding:12px 18px; display:flex; justify-content:space-between; align-items:center;">
                        <div class="card-title" style="font-size:14px; font-weight:700; color:#1E293B; display:flex; align-items:center; gap:8px;">
                            <i data-lucide="gift" style="width:16px;height:16px; color:#EC4899;"></i> Complimentary Gift / Freebie
                        </div>
                        <label style="display:flex; align-items:center; gap:6px; font-size:12px; font-weight:700; color:#475569; cursor:pointer;">
                            <input type="checkbox" name="has_gift" id="hasGiftToggle" value="1" onchange="toggleGiftSection(this.checked)" style="width:16px; height:16px; accent-color:#EC4899; cursor:pointer;">
                            Add Gift to Sale
                        </label>
                    </div>

                    <div class="card-body" id="giftBodySection" style="display:none; padding:16px 18px; background:#FDF2F8; border-top:1px solid #FCE7F3;">
                        <div style="margin-bottom:12px; display:flex; gap:16px;">
                            <label style="font-size:12px; font-weight:700; color:#334155; cursor:pointer; display:flex; align-items:center; gap:5px;">
                                <input type="radio" name="gift_source" value="inventory" checked onchange="switchGiftSource('inventory')" style="accent-color:#EC4899;">
                                From Shop Accessories Stock
                            </label>
                            <label style="font-size:12px; font-weight:700; color:#334155; cursor:pointer; display:flex; align-items:center; gap:5px;">
                                <input type="radio" name="gift_source" value="custom" onchange="switchGiftSource('custom')" style="accent-color:#EC4899;">
                                Custom Freebie (Smartwatch / Speaker / Bag)
                            </label>
                        </div>

                        <div id="giftInventorySource" style="display:grid; grid-template-columns: 2fr 1fr; gap:12px; align-items:flex-end;">
                            <div>
                                <label class="form-label" style="font-size:11px; font-weight:700; color:#475569; margin-bottom:4px;">Select In-Stock Accessory</label>
                                <select class="form-control" name="gift_inventory_id" id="giftInventorySelect" onchange="onGiftInventoryChange()">
                                    <option value="">— Select an accessory/item —</option>
                                    @foreach($giftInventory as $gi)
                                        <option value="{{ $gi->id }}" data-cost="{{ $gi->unit_cost }}" data-name="{{ $gi->name }}" data-stock="{{ $gi->stock_qty }}">
                                            {{ $gi->name }} (Stock: {{ $gi->stock_qty }} | Cost: ₹{{ number_format($gi->unit_cost, 0) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label" style="font-size:11px; font-weight:700; color:#475569; margin-bottom:4px;">Gift Purchase Cost (₹)</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="gift_cost" id="giftCostInventory" value="0.00" oninput="recalcSaleFinancials()" style="font-weight:700;">
                            </div>
                        </div>

                        <div id="giftCustomSource" style="display:none; grid-template-columns: 2fr 1fr; gap:12px; align-items:flex-end;">
                            <div>
                                <label class="form-label" style="font-size:11px; font-weight:700; color:#475569; margin-bottom:4px;">Custom Gift Item Description</label>
                                <input type="text" class="form-control" name="gift_custom_name" id="giftCustomName" placeholder="e.g. Ultra Smart Watch, Bluetooth Earbuds" oninput="recalcSaleFinancials()">
                            </div>
                            <div>
                                <label class="form-label" style="font-size:11px; font-weight:700; color:#475569; margin-bottom:4px;">Gift Purchase Cost (₹)</label>
                                <input type="number" step="0.01" min="0" class="form-control" id="giftCostCustom" placeholder="Cost to store" oninput="recalcSaleFinancials()" style="font-weight:700;">
                            </div>
                        </div>

                        <div style="margin-top:10px; font-size:11.5px; color:#BE185D; display:flex; align-items:center; gap:6px;">
                            <i data-lucide="info" style="width:13px;height:13px;"></i>
                            <span>Billed to customer as <strong>₹0.00 (Promotional Free Gift)</strong>. Purchase cost will be automatically factored in to compute true net sale profit.</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ════════════════════ RIGHT COLUMN: PAYMENT MODES, EMI & PROFIT ════════════════════ -->
            <div style="flex: 0 0 380px; width: 380px; max-width: 100%; display: flex; flex-direction: column; gap: 18px; position: sticky; top: 20px;">

                <!-- ── PAYMENT SELECTION CARD ── -->
                <div class="card" style="box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #E2E8F0; border-radius: 12px;">
                    <div class="card-header" style="background:#F8FAFC; border-bottom:1px solid #E2E8F0; padding:12px 18px;">
                        <div class="card-title" style="font-size:14px; font-weight:700; color:#1E293B; display:flex; align-items:center; gap:8px;">
                            <i data-lucide="wallet" style="width:16px;height:16px; color:var(--color-primary);"></i> Payment Mode
                        </div>
                    </div>
                    <div class="card-body" style="padding:16px 18px;">

                        <!-- Mode Segmented Selector -->
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:8px; margin-bottom:16px;">
                            <label class="pay-mode-card" id="modeCardCash">
                                <input type="radio" name="payment_mode" value="cash" checked onchange="onPaymentModeToggle('cash')">
                                <span class="mode-title"><i data-lucide="banknote" style="width:16px;height:16px;"></i> Cash</span>
                            </label>
                            <label class="pay-mode-card" id="modeCardOnline">
                                <input type="radio" name="payment_mode" value="online" onchange="onPaymentModeToggle('online')">
                                <span class="mode-title"><i data-lucide="qr-code" style="width:16px;height:16px;"></i> Online / UPI</span>
                            </label>
                            <label class="pay-mode-card" id="modeCardEmi">
                                <input type="radio" name="payment_mode" value="emi" onchange="onPaymentModeToggle('emi')">
                                <span class="mode-title"><i data-lucide="landmark" style="width:16px;height:16px;"></i> EMI Finance</span>
                            </label>
                            <label class="pay-mode-card" id="modeCardKhata">
                                <input type="radio" name="payment_mode" value="credit_udhari" onchange="onPaymentModeToggle('credit_udhari')">
                                <span class="mode-title"><i data-lucide="book-open" style="width:16px;height:16px;"></i> Full Khata</span>
                            </label>
                        </div>

                        <!-- CASH / ONLINE VIEW -->
                        <div id="panelCashOnline">
                            <div class="form-group" style="margin-bottom:12px;">
                                <label class="form-label" style="font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Amount Paid Now (₹) <span style="color:#EF4444;">*</span></label>
                                <input type="number" step="0.01" min="0" class="form-control" name="amount_paid" id="amountPaid" value="0.00" oninput="recalcSaleFinancials()" required style="font-size:16px; font-weight:800;">
                            </div>
                            <div class="form-group" style="margin-bottom:6px;">
                                <label class="form-label" style="font-size:12px; font-weight:600; color:#64748B; margin-bottom:4px;">Balance to Khata (Udhari)</label>
                                <input type="text" class="form-control" id="cashBalanceDisplay" readonly value="₹0.00" style="background:#FEF2F2; font-weight:800; color:#DC2626; font-size:15px;">
                            </div>
                        </div>

                        <!-- EMI VIEW -->
                        <div id="panelEmi" style="display:none;">
                            <div class="form-group" style="margin-bottom:10px;">
                                <label class="form-label" style="font-size:11.5px; font-weight:700; color:#334155; margin-bottom:3px;">EMI Finance Company <span style="color:#EF4444;">*</span></label>
                                <select class="form-control" name="emi_provider_id" id="emiProviderSelect" onchange="onEmiProviderSelectChange()" style="font-weight:700;">
                                    <option value="">— Select Finance Partner —</option>
                                    @foreach($emiProviders as $p)
                                        <option value="{{ $p->id }}"
                                            data-flat="{{ $p->processing_fee_flat ?? 0 }}"
                                            data-pct="{{ $p->processing_fee_pct ?? 0 }}"
                                            data-tenure="{{ $p->default_tenure_months ?? 12 }}">
                                            {{ $p->name }} (Pool: ₹{{ number_format($p->advance_balance, 0) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:8px; margin-bottom:10px;">
                                <div class="form-group" style="margin:0;">
                                    <label class="form-label" style="font-size:11px; font-weight:700; color:#334155; margin-bottom:3px;">EMI Tenure</label>
                                    <select class="form-control" name="emi_tenure_months" id="emiTenureSelect" style="font-weight:700;">
                                        <option value="3">3 Months</option>
                                        <option value="6">6 Months</option>
                                        <option value="8">8 Months</option>
                                        <option value="9">9 Months</option>
                                        <option value="10">10 Months</option>
                                        <option value="12" selected>12 Months</option>
                                        <option value="18">18 Months</option>
                                        <option value="24">24 Months</option>
                                        <option value="36">36 Months</option>
                                    </select>
                                </div>
                                <div class="form-group" style="margin:0;">
                                    <label class="form-label" style="font-size:11px; font-weight:700; color:#334155; margin-bottom:3px;">Processing Fee</label>
                                    <div style="display:flex; gap:4px;">
                                        <select class="form-control" name="emi_fee_type" id="emiFeeType" onchange="recalcSaleFinancials()" style="width:75px; padding:6px; font-size:11px; font-weight:700;">
                                            <option value="flat">₹ Flat</option>
                                            <option value="percent">% Pct</option>
                                        </select>
                                        <input type="number" step="0.01" min="0" class="form-control" name="emi_fee_value" id="emiFeeValue" placeholder="0" oninput="recalcSaleFinancials()" style="font-weight:700; padding:6px 8px;">
                                    </div>
                                </div>
                            </div>

                            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:8px; margin-bottom:10px;">
                                <div class="form-group" style="margin:0;">
                                    <label class="form-label" style="font-size:11px; font-weight:700; color:#334155; margin-bottom:3px;">Downpayment Req. (₹)</label>
                                    <input type="number" step="0.01" min="0" class="form-control" name="emi_downpayment_required" id="emiDownpaymentRequired" value="0.00" oninput="onEmiDownpaymentReqChange()" style="font-weight:700;">
                                </div>
                                <div class="form-group" style="margin:0;">
                                    <label class="form-label" style="font-size:11px; font-weight:700; color:#334155; margin-bottom:3px;">Loan Financed (₹)</label>
                                    <input type="text" class="form-control" id="emiFinancedDisplay" readonly value="₹0.00" style="background:#F1F5F9; font-weight:800; color:#1E293B;">
                                </div>
                            </div>

                            <div class="form-group" style="margin-bottom:10px;">
                                <label class="form-label" style="font-size:11.5px; font-weight:700; color:#334155; margin-bottom:3px;">Customer Paid Now / DP Paid (₹) <span style="color:#EF4444;">*</span></label>
                                <input type="number" step="0.01" min="0" class="form-control" id="emiAmountPaid" value="0.00" oninput="onEmiAmountPaidChange()" style="font-size:15px; font-weight:800; color:#059669;">
                            </div>

                            <!-- Partial Downpayment Alert -->
                            <div id="emiShortfallNotice" style="display:none; background:#FFFBEB; border:1px solid #FCD34D; border-radius:8px; padding:10px; margin-bottom:10px; font-size:11.5px; color:#92400E;">
                                <div style="font-weight:700; display:flex; align-items:center; gap:4px; margin-bottom:2px;">
                                    <i data-lucide="alert-triangle" style="width:14px;height:14px; color:#D97706;"></i> Partial Downpayment Detected
                                </div>
                                <div>Customer is short <strong id="emiShortfallAmount">₹0.00</strong> on downpayment. This amount will be added to <strong>Customer Khata (Udhari)</strong>. Financed amount will balance out with the EMI partner ledger.</div>
                            </div>

                            <div class="form-group" style="margin:0;">
                                <label class="form-label" style="font-size:11px; font-weight:600; color:#64748B; margin-bottom:3px;">Loan / File Reference No.</label>
                                <input type="text" class="form-control" name="emi_loan_no" id="emiLoanNo" placeholder="e.g. BJF-982310" style="font-size:12px;">
                            </div>
                        </div>

                    </div>
                </div>

                <!-- ── BILL TOTAL & LIVE PROFIT BREAKDOWN ── -->
                <div class="card" style="box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #E2E8F0; border-radius: 12px;">
                    <div class="card-header" style="background:#F8FAFC; border-bottom:1px solid #E2E8F0; padding:12px 18px;">
                        <div class="card-title" style="font-size:14px; font-weight:700; color:#1E293B; display:flex; align-items:center; gap:8px;">
                            <i data-lucide="calculator" style="width:16px;height:16px; color:#10B981;"></i> Bill & Financial Summary
                        </div>
                    </div>
                    <div class="card-body" style="padding:16px 18px;">

                        <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:13px; color:#64748B;">
                            <span>Devices Sale Total:</span>
                            <strong id="summaryDevicesTotal" style="color:#1E293B;">₹0.00</strong>
                        </div>
                        <div id="summaryGiftRow" style="display:none; justify-content:space-between; margin-bottom:8px; font-size:12.5px; color:#EC4899;">
                            <span>🎁 Free Gift Added:</span>
                            <span>₹0.00 <span style="font-size:11px; color:#64748B;">(Cost: <strong id="summaryGiftCost">₹0.00</strong>)</span></span>
                        </div>
                        <div style="border-top:1px solid #E2E8F0; padding-top:10px; margin-top:8px; display:flex; justify-content:space-between; align-items:baseline; margin-bottom:14px;">
                            <span style="font-size:14px; font-weight:700; color:#1E293B;">Total Bill Amount:</span>
                            <span id="summaryBillTotal" style="font-size:22px; font-weight:900; color:var(--color-primary);">₹0.00</span>
                        </div>

                        <!-- NET PROFIT LIVE METRIC -->
                        <div style="background:#ECFDF5; border:1px solid #A7F3D0; border-radius:10px; padding:12px 14px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                                <span style="font-size:11.5px; font-weight:700; color:#065F46; text-transform:uppercase; letter-spacing:0.5px;">Net Store Profit</span>
                                <span id="summaryProfitPercent" style="font-size:11px; font-weight:800; background:#D1FAE5; color:#065F46; padding:2px 6px; border-radius:4px;">0% Margin</span>
                            </div>
                            <div style="display:flex; justify-content:space-between; align-items:baseline;">
                                <div id="summaryNetProfit" style="font-size:20px; font-weight:900; color:#059669;">₹0.00</div>
                                <div style="font-size:11px; color:#047857;">(Price - Phone Cost - Gift - Fees)</div>
                            </div>
                        </div>

                        <!-- CTA BUTTON -->
                        <div style="margin-top:16px;">
                            <button type="submit" class="btn btn-primary" id="btnSubmitSale" style="width:100%; height:46px; font-size:14px; font-weight:800; border-radius:10px; display:flex; align-items:center; justify-content:center; gap:8px;">
                                <i data-lucide="receipt" style="width:18px;height:18px;"></i> Complete Sale & Invoice
                            </button>
                        </div>

                    </div>
                </div>

            </div>

        </div>
    </form>
</div>

<!-- ═══════════ OCR LOADING MODAL ═══════════ -->
<div id="ocrLoadingModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.65); backdrop-filter:blur(4px); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:16px; padding:32px 36px; max-width:440px; width:90%; text-align:center; box-shadow:0 20px 40px rgba(0,0,0,0.25);">
        <div style="width:52px; height:52px; border:4px solid #EEF2FF; border-top-color:#4F46E5; border-radius:50%; margin:0 auto 18px; animation:spin 1s linear infinite;"></div>
        <h3 style="font-size:17px; font-weight:800; color:#1E293B; margin-bottom:6px;">Analyzing Document with AI</h3>
        <p style="font-size:13px; color:#64748B; margin:0; line-height:1.5;">Reading customer details, device IMEI, EMI partner, loan reference number, and downpayment…</p>
        <div style="margin-top:14px; display:inline-flex; align-items:center; gap:6px; font-size:11px; font-weight:700; color:#4F46E5; background:#EEF2FF; padding:4px 10px; border-radius:999px;">
            <i data-lucide="sparkles" style="width:13px; height:13px;"></i> Powered by Gemini 1.5 Flash AI
        </div>
    </div>
</div>

<!-- ═══════════ OCR REVIEW & CONFIRM MODAL ═══════════ -->
<div id="ocrReviewModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.65); backdrop-filter:blur(4px); z-index:9999; align-items:center; justify-content:center; padding:16px;">
    <div style="background:#fff; border-radius:16px; max-width:640px; width:100%; box-shadow:0 20px 40px rgba(0,0,0,0.25); overflow:hidden; display:flex; flex-direction:column; max-height:90vh;">
        
        <!-- Modal Header -->
        <div style="background:linear-gradient(135deg, #1E1B4B 0%, #312E81 100%); color:#fff; padding:16px 22px; display:flex; justify-content:space-between; align-items:center;">
            <div style="display:flex; align-items:center; gap:10px;">
                <div style="width:34px; height:34px; border-radius:8px; background:rgba(255,255,255,0.15); display:flex; align-items:center; justify-content:center;">
                    <i data-lucide="scan-text" style="width:18px; height:18px;"></i>
                </div>
                <div>
                    <div style="font-size:15px; font-weight:800;">Review Extracted Bill Details</div>
                    <div style="font-size:11.5px; color:#C7D2FE;">Verify or edit fields before applying to the sale form</div>
                </div>
            </div>
            <button type="button" onclick="closeOcrModal()" style="background:transparent; border:none; color:#CBD5E1; cursor:pointer; padding:4px;">
                <i data-lucide="x" style="width:20px; height:20px;"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div style="padding:20px 22px; overflow-y:auto; flex:1;">
            
            <!-- Notice / Demo Banner -->
            <div id="ocrModalNotice" style="display:none; background:#EFF6FF; border:1px solid #BFDBFE; border-radius:8px; padding:10px 14px; margin-bottom:14px; font-size:12px; color:#1E40AF;">
                <span id="ocrNoticeText"></span>
            </div>

            <!-- Stock Device Match Status Card -->
            <div id="ocrStockMatchCard" style="background:#F0FDF4; border:1px solid #BBF7D0; border-radius:10px; padding:12px 14px; margin-bottom:16px; display:flex; align-items:center; justify-content:space-between; gap:12px;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div id="ocrMatchIcon" style="width:32px; height:32px; border-radius:50%; background:#DCFCE7; color:#16A34A; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <i data-lucide="check" style="width:18px; height:18px;"></i>
                    </div>
                    <div>
                        <div id="ocrMatchTitle" style="font-size:13px; font-weight:700; color:#166534;">Device Matched in Stock</div>
                        <div id="ocrMatchSubtitle" style="font-size:11.5px; color:#15803D;">Ready to auto-add to phone cart</div>
                    </div>
                </div>
                <span id="ocrMatchBadge" class="badge" style="background:#22C55E; color:#fff; font-size:11px; padding:3px 8px; border-radius:6px; font-weight:700;">In Stock</span>
            </div>

            <!-- Form Fields Grid -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                
                <div>
                    <label style="font-size:11px; font-weight:700; color:#475569; display:block; margin-bottom:3px;">Customer Phone <span style="color:#EF4444;">*</span></label>
                    <input type="text" id="ocrCustomerPhone" class="form-control" style="font-weight:600; font-size:13px;">
                </div>

                <div>
                    <label style="font-size:11px; font-weight:700; color:#475569; display:block; margin-bottom:3px;">Customer Name <span style="color:#EF4444;">*</span></label>
                    <input type="text" id="ocrCustomerName" class="form-control" style="font-weight:600; font-size:13px;">
                </div>

                <div>
                    <label style="font-size:11px; font-weight:700; color:#475569; display:block; margin-bottom:3px;">Phone Brand</label>
                    <input type="text" id="ocrBrand" class="form-control" style="font-weight:600; font-size:13px;">
                </div>

                <div>
                    <label style="font-size:11px; font-weight:700; color:#475569; display:block; margin-bottom:3px;">Phone Model / Variant</label>
                    <input type="text" id="ocrModel" class="form-control" style="font-weight:600; font-size:13px;">
                </div>

                <div>
                    <label style="font-size:11px; font-weight:700; color:#475569; display:block; margin-bottom:3px;">Device IMEI</label>
                    <input type="text" id="ocrImei" class="form-control" style="font-family:monospace; font-weight:600; font-size:13px;">
                </div>

                <div>
                    <label style="font-size:11px; font-weight:700; color:#475569; display:block; margin-bottom:3px;">Sale / Invoiced Price (₹)</label>
                    <input type="number" step="0.01" id="ocrSalePrice" class="form-control" style="font-weight:700; font-size:13px; color:#1E293B;">
                </div>

                <div>
                    <label style="font-size:11px; font-weight:700; color:#475569; display:block; margin-bottom:3px;">EMI Finance Partner</label>
                    <select id="ocrProviderSelect" class="form-control" style="font-weight:700; font-size:12px;">
                        <option value="">— Select Partner —</option>
                        @foreach($emiProviders as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="font-size:11px; font-weight:700; color:#475569; display:block; margin-bottom:3px;">Loan Ref / Agreement No.</label>
                    <input type="text" id="ocrLoanNo" class="form-control" style="font-size:13px;">
                </div>

                <div style="grid-column: span 2;">
                    <label style="font-size:11px; font-weight:700; color:#475569; display:block; margin-bottom:3px;">Downpayment (₹)</label>
                    <input type="number" step="0.01" id="ocrDownpayment" class="form-control" style="font-size:15px; font-weight:800; color:#059669;">
                </div>

            </div>

        </div>

        <!-- Modal Footer -->
        <div style="background:#F8FAFC; border-top:1px solid #E2E8F0; padding:14px 22px; display:flex; justify-content:space-between; align-items:center;">
            <button type="button" onclick="closeOcrModal()" class="btn btn-outline btn-sm" style="font-weight:600;">
                Cancel
            </button>
            <button type="button" onclick="applyOcrDataToForm()" class="btn btn-sm" style="background:linear-gradient(135deg, #10B981 0%, #059669 100%); color:#fff; border:none; font-weight:800; font-size:13px; padding:9px 20px; border-radius:8px; display:inline-flex; align-items:center; gap:6px; box-shadow:0 2px 8px rgba(16,185,129,0.35); cursor:pointer;">
                <i data-lucide="zap" style="width:15px; height:15px;"></i> Apply & Auto-Fill ("Boom!")
            </button>
        </div>

    </div>
</div>

<style>
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    .pay-mode-card {
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        padding: 10px 8px;
        text-align: center;
        cursor: pointer;
        transition: all 0.15s ease;
        background: #FFFFFF;
        display: block;
        margin: 0;
    }
    .pay-mode-card input {
        display: none;
    }
    .pay-mode-card .mode-title {
        font-size: 12px;
        font-weight: 700;
        color: #475569;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }
    .pay-mode-card.active {
        border-color: var(--color-primary);
        background: #EEF2FF;
    }
    .pay-mode-card.active .mode-title {
        color: var(--color-primary);
    }
    .search-result-item {
        padding: 8px 12px;
        cursor: pointer;
        border-bottom: 1px solid #F1F5F9;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 12px;
    }
    .search-result-item:hover {
        background: #F8FAFC;
    }
</style>
@endsection

@push('scripts')
<script>
    // In-Stock Devices Data Source
    var rawDevices = @json($inStockDevices);
    var customersData = @json($customers->map(function($c) { return ['phone' => $c->phone, 'name' => $c->name, 'bal' => (float)$c->udhari_balance]; }));
    var selectedDevices = [];

    // Initialize Page
    document.addEventListener('DOMContentLoaded', function() {
        populateBrandDropdown();
        onPaymentModeToggle('cash');

        // Customer auto-fill on phone input
        var phoneInput = document.getElementById('customerPhone');
        phoneInput.addEventListener('input', checkCustomerPhone);
        phoneInput.addEventListener('change', checkCustomerPhone);

        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }
    });

    function checkCustomerPhone() {
        var phone = document.getElementById('customerPhone').value.trim();
        var match = customersData.find(function(c){ return c.phone === phone; });
        if (match) {
            document.getElementById('customerName').value = match.name;
            var bal = match.bal;
            var balDisplay = document.getElementById('customerBalanceDisplay');
            balDisplay.value = (bal > 0 ? '₹' + bal.toLocaleString('en-IN', {minimumFractionDigits: 2}) + ' Due' : '₹0.00 (Clear)');
            balDisplay.style.color = bal > 0 ? '#DC2626' : '#16A34A';
        } else {
            document.getElementById('customerBalanceDisplay').value = 'New Customer';
            document.getElementById('customerBalanceDisplay').style.color = '#64748B';
        }
    }

    /* ─── CASCADING PHONE SELECTOR ─── */
    function populateBrandDropdown() {
        var brandSelect = document.getElementById('brandSelect');
        var brands = {};
        rawDevices.forEach(function(d) {
            if (!isDeviceSelected(d.id)) {
                brands[d.brand] = (brands[d.brand] || 0) + 1;
            }
        });

        brandSelect.innerHTML = '<option value="">— Select Brand —</option>';
        Object.keys(brands).sort().forEach(function(b) {
            var opt = document.createElement('option');
            opt.value = b;
            opt.textContent = b + ' (' + brands[b] + ')';
            brandSelect.appendChild(opt);
        });
        onBrandChange();
    }

    function onBrandChange() {
        var brand = document.getElementById('brandSelect').value;
        var modelSelect = document.getElementById('modelSelect');
        var variantSelect = document.getElementById('variantSelect');
        var btnAdd = document.getElementById('btnAddDevice');

        modelSelect.innerHTML = '<option value="">— Select Model —</option>';
        variantSelect.innerHTML = '<option value="">— Select Variant / IMEI —</option>';
        variantSelect.disabled = true;
        btnAdd.disabled = true;

        if (!brand) {
            modelSelect.disabled = true;
            return;
        }

        var models = {};
        rawDevices.filter(function(d) { return d.brand === brand && !isDeviceSelected(d.id); }).forEach(function(d) {
            models[d.model] = (models[d.model] || 0) + 1;
        });

        Object.keys(models).sort().forEach(function(m) {
            var opt = document.createElement('option');
            opt.value = m;
            opt.textContent = m + ' (' + models[m] + ')';
            modelSelect.appendChild(opt);
        });
        modelSelect.disabled = false;
    }

    function onModelChange() {
        var brand = document.getElementById('brandSelect').value;
        var model = document.getElementById('modelSelect').value;
        var variantSelect = document.getElementById('variantSelect');
        var btnAdd = document.getElementById('btnAddDevice');

        variantSelect.innerHTML = '<option value="">— Select Variant / IMEI —</option>';
        btnAdd.disabled = true;

        if (!brand || !model) {
            variantSelect.disabled = true;
            return;
        }

        var available = rawDevices.filter(function(d) {
            return d.brand === brand && d.model === model && !isDeviceSelected(d.id);
        });

        available.forEach(function(d) {
            var opt = document.createElement('option');
            opt.value = d.id;
            var spec = (d.ram ? d.ram + '/' + d.storage : 'Std') + ' ' + (d.color || '');
            opt.textContent = spec + ' | IMEI: ' + d.imei_1 + ' — ₹' + Number(d.selling_price).toLocaleString('en-IN');
            variantSelect.appendChild(opt);
        });

        variantSelect.disabled = available.length === 0;
        if (available.length > 0) {
            variantSelect.selectedIndex = 1;
            btnAdd.disabled = false;
        }
    }

    document.getElementById('variantSelect').addEventListener('change', function() {
        document.getElementById('btnAddDevice').disabled = !this.value;
    });

    function isDeviceSelected(deviceId) {
        return selectedDevices.some(function(d) { return d.id === deviceId; });
    }

    function addSelectedDevice() {
        var deviceId = parseInt(document.getElementById('variantSelect').value);
        if (!deviceId) return;
        var device = rawDevices.find(function(d) { return d.id === deviceId; });
        if (!device) return;

        selectedDevices.push({
            id: device.id,
            brand: device.brand,
            model: device.model,
            variant: (device.ram ? device.ram + '/' + device.storage : 'Std') + ' ' + (device.color || ''),
            imei: device.imei_1,
            cost: parseFloat(device.purchase_cost) || 0,
            sale_price: parseFloat(device.selling_price) || 0
        });

        renderSelectedDevices();
        populateBrandDropdown();
    }

    function addDeviceById(id) {
        var device = rawDevices.find(function(d) { return d.id === id; });
        if (!device || isDeviceSelected(id)) return;
        selectedDevices.push({
            id: device.id,
            brand: device.brand,
            model: device.model,
            variant: (device.ram ? device.ram + '/' + device.storage : 'Std') + ' ' + (device.color || ''),
            imei: device.imei_1,
            cost: parseFloat(device.purchase_cost) || 0,
            sale_price: parseFloat(device.selling_price) || 0
        });
        renderSelectedDevices();
        populateBrandDropdown();
        document.getElementById('quickImeiSearch').value = '';
        document.getElementById('quickSearchResults').style.display = 'none';
    }

    function removeDevice(index) {
        selectedDevices.splice(index, 1);
        renderSelectedDevices();
        populateBrandDropdown();
    }

    function onSalePriceChange(index, input) {
        var val = parseFloat(input.value) || 0;
        selectedDevices[index].sale_price = val;
        recalcSaleFinancials();
    }

    function renderSelectedDevices() {
        var listEl = document.getElementById('selectedDevicesList');
        var wrapEl = document.getElementById('selectedDevicesTableWrap');
        var emptyEl = document.getElementById('emptyDevicesState');
        var countBadge = document.getElementById('selectedCountBadge');

        countBadge.textContent = selectedDevices.length + (selectedDevices.length === 1 ? ' device selected' : ' devices selected');

        if (selectedDevices.length === 0) {
            wrapEl.style.display = 'none';
            emptyEl.style.display = 'block';
            recalcSaleFinancials();
            return;
        }

        emptyEl.style.display = 'none';
        wrapEl.style.display = 'block';
        listEl.innerHTML = '';

        selectedDevices.forEach(function(dev, idx) {
            var tr = document.createElement('tr');
            tr.innerHTML = `
                <td style="font-weight:700; color:#0F172A;">
                    <input type="hidden" name="device_ids[]" value="${dev.id}">
                    ${dev.brand} ${dev.model}
                </td>
                <td style="font-size:11.5px; color:#475569;">${dev.variant}</td>
                <td style="font-family:monospace; font-size:12px; font-weight:600; color:#334155;">${dev.imei}</td>
                <td style="text-align:right; font-family:monospace; color:#64748B;">₹${dev.cost.toLocaleString('en-IN', {minimumFractionDigits: 0})}</td>
                <td style="text-align:right;">
                    <input type="number" step="0.01" min="1" class="form-control" name="sale_prices[${dev.id}]" value="${dev.sale_price}"
                           oninput="onSalePriceChange(${idx}, this)" style="padding:4px 8px; max-width:120px; text-align:right; font-weight:700; margin-left:auto;">
                </td>
                <td style="text-align:center;">
                    <button type="button" class="btn btn-outline btn-sm" onclick="removeDevice(${idx})" title="Remove Device" style="padding:4px 7px; color:#EF4444; border-color:#FCA5A5;">
                        <i data-lucide="trash-2" style="width:13px;height:13px;"></i>
                    </button>
                </td>
            `;
            listEl.appendChild(tr);
        });

        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }

        recalcSaleFinancials();
    }

    /* ─── QUICK SEARCH / BARCODE SCAN ─── */
    function onQuickSearchInput() {
        var query = document.getElementById('quickImeiSearch').value.trim().toLowerCase();
        var resBox = document.getElementById('quickSearchResults');
        if (!query) {
            resBox.style.display = 'none';
            return;
        }

        var matches = rawDevices.filter(function(d) {
            if (isDeviceSelected(d.id)) return false;
            var text = (d.brand + ' ' + d.model + ' ' + d.imei_1 + ' ' + (d.imei_2 || '')).toLowerCase();
            return text.indexOf(query) !== -1;
        });

        if (matches.length === 0) {
            resBox.innerHTML = '<div style="padding:10px; font-size:12px; color:#94A3B8; text-align:center;">No matching device found in stock</div>';
            resBox.style.display = 'block';
            return;
        }

        resBox.innerHTML = '';
        matches.slice(0, 8).forEach(function(d) {
            var item = document.createElement('div');
            item.className = 'search-result-item';
            item.innerHTML = `
                <div>
                    <strong>${d.brand} ${d.model}</strong>
                    <div style="font-size:11px; color:#64748B;">IMEI: ${d.imei_1} | ${d.ram ? d.ram + '/' + d.storage : 'Std'} ${d.color || ''}</div>
                </div>
                <div style="font-weight:700; color:var(--color-primary);">₹${Number(d.selling_price).toLocaleString('en-IN')}</div>
            `;
            item.onclick = function() { addDeviceById(d.id); };
            resBox.appendChild(item);
        });
        resBox.style.display = 'block';
    }

    /* ─── PROMOTIONAL GIFTS ─── */
    function toggleGiftSection(checked) {
        document.getElementById('giftBodySection').style.display = checked ? 'block' : 'none';
        document.getElementById('summaryGiftRow').style.display = checked ? 'flex' : 'none';
        recalcSaleFinancials();
    }

    function switchGiftSource(source) {
        document.getElementById('giftInventorySource').style.display = source === 'inventory' ? 'grid' : 'none';
        document.getElementById('giftCustomSource').style.display = source === 'custom' ? 'grid' : 'none';
        recalcSaleFinancials();
    }

    function onGiftInventoryChange() {
        var sel = document.getElementById('giftInventorySelect');
        var opt = sel.options[sel.selectedIndex];
        if (opt && opt.value) {
            var cost = parseFloat(opt.dataset.cost) || 0;
            document.getElementById('giftCostInventory').value = cost.toFixed(2);
        } else {
            document.getElementById('giftCostInventory').value = '0.00';
        }
        recalcSaleFinancials();
    }

    /* ─── PAYMENT MODE & EMI BREAKDOWN ─── */
    function onPaymentModeToggle(mode) {
        ['Cash', 'Online', 'Emi', 'Khata'].forEach(function(m) {
            var card = document.getElementById('modeCard' + m);
            if (card) card.classList.remove('active');
        });

        var activeCard = document.getElementById('modeCard' + mode.charAt(0).toUpperCase() + mode.slice(1));
        if (activeCard) {
            activeCard.classList.add('active');
            var radio = activeCard.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
        }

        var isEmi = (mode === 'emi');
        var isKhata = (mode === 'credit_udhari');

        document.getElementById('panelCashOnline').style.display = isEmi ? 'none' : 'block';
        document.getElementById('panelEmi').style.display = isEmi ? 'block' : 'none';

        var billTotal = getDevicesTotal();

        if (isKhata) {
            document.getElementById('amountPaid').value = '0.00';
        } else if (!isEmi) {
            document.getElementById('amountPaid').value = billTotal.toFixed(2);
        } else {
            // Default EMI Downpayment to 20%
            var defaultDp = Math.round(billTotal * 0.2);
            document.getElementById('emiDownpaymentRequired').value = defaultDp.toFixed(2);
            document.getElementById('emiAmountPaid').value = defaultDp.toFixed(2);
            document.getElementById('amountPaid').value = defaultDp.toFixed(2);
            onEmiProviderSelectChange();
        }

        recalcSaleFinancials();
    }

    function onEmiProviderSelectChange() {
        var sel = document.getElementById('emiProviderSelect');
        if (!sel) return;
        var opt = sel.options[sel.selectedIndex];
        if (opt && opt.value) {
            var flat = parseFloat(opt.dataset.flat || 0);
            var pct = parseFloat(opt.dataset.pct || 0);
            var tenure = opt.dataset.tenure || '12';

            if (pct > 0) {
                document.getElementById('emiFeeType').value = 'percent';
                document.getElementById('emiFeeValue').value = pct;
            } else if (flat > 0) {
                document.getElementById('emiFeeType').value = 'flat';
                document.getElementById('emiFeeValue').value = flat;
            }
            if (tenure) {
                document.getElementById('emiTenureSelect').value = tenure;
            }
        }
        recalcSaleFinancials();
    }

    function onEmiDownpaymentReqChange() {
        recalcSaleFinancials();
    }

    function onEmiAmountPaidChange() {
        var paid = parseFloat(document.getElementById('emiAmountPaid').value) || 0;
        document.getElementById('amountPaid').value = paid.toFixed(2);
        recalcSaleFinancials();
    }

    function getDevicesTotal() {
        return selectedDevices.reduce(function(acc, d) { return acc + d.sale_price; }, 0);
    }

    function getDevicesCost() {
        return selectedDevices.reduce(function(acc, d) { return acc + d.cost; }, 0);
    }

    function getGiftCost() {
        var hasGift = document.getElementById('hasGiftToggle').checked;
        if (!hasGift) return 0;
        var isInventory = document.querySelector('input[name="gift_source"]:checked')?.value === 'inventory';
        if (isInventory) {
            return parseFloat(document.getElementById('giftCostInventory').value) || 0;
        } else {
            return parseFloat(document.getElementById('giftCostCustom').value) || 0;
        }
    }

    function recalcSaleFinancials() {
        var billTotal = getDevicesTotal();
        var devicesCost = getDevicesCost();
        var giftCost = getGiftCost();

        // Update displays
        document.getElementById('summaryDevicesTotal').textContent = '₹' + billTotal.toLocaleString('en-IN', {minimumFractionDigits: 2});
        document.getElementById('summaryBillTotal').textContent = '₹' + billTotal.toLocaleString('en-IN', {minimumFractionDigits: 2});
        document.getElementById('summaryGiftCost').textContent = '₹' + giftCost.toLocaleString('en-IN', {minimumFractionDigits: 2});

        var currentMode = document.querySelector('input[name="payment_mode"]:checked')?.value || 'cash';
        var isEmi = (currentMode === 'emi');

        var emiFee = 0;
        if (isEmi) {
            var dpReq = parseFloat(document.getElementById('emiDownpaymentRequired').value) || 0;
            dpReq = Math.min(billTotal, Math.max(0, dpReq));

            var financed = Math.max(0, billTotal - dpReq);
            document.getElementById('emiFinancedDisplay').value = '₹' + financed.toLocaleString('en-IN', {minimumFractionDigits: 2});

            // Calculate fee
            var feeType = document.getElementById('emiFeeType').value;
            var feeVal = parseFloat(document.getElementById('emiFeeValue').value) || 0;
            emiFee = feeType === 'percent' ? Math.round((financed * feeVal) / 100) : feeVal;

            // Actual cash downpayment paid now by customer
            var dpPaid = parseFloat(document.getElementById('emiAmountPaid').value) || 0;
            document.getElementById('amountPaid').value = dpPaid.toFixed(2);

            // Shortfall logic
            var shortfall = Math.max(0, dpReq - dpPaid);
            var noticeBox = document.getElementById('emiShortfallNotice');
            if (shortfall > 0) {
                document.getElementById('emiShortfallAmount').textContent = '₹' + shortfall.toLocaleString('en-IN', {minimumFractionDigits: 2});
                noticeBox.style.display = 'block';
            } else {
                noticeBox.style.display = 'none';
            }
        } else {
            var paidNow = parseFloat(document.getElementById('amountPaid').value) || 0;
            var khataBal = Math.max(0, billTotal - paidNow);
            var khataEl = document.getElementById('cashBalanceDisplay');
            khataEl.value = '₹' + khataBal.toLocaleString('en-IN', {minimumFractionDigits: 2});
            khataEl.style.color = khataBal > 0 ? '#DC2626' : '#16A34A';
        }

        // Net Profit Calculation: Sale Price - Device Purchase Cost - Gift Cost - EMI Processing Fee
        var netProfit = billTotal - devicesCost - giftCost - emiFee;
        var profitMargin = billTotal > 0 ? Math.round((netProfit / billTotal) * 100) : 0;

        var profitEl = document.getElementById('summaryNetProfit');
        profitEl.textContent = (netProfit < 0 ? '-' : '') + '₹' + Math.abs(netProfit).toLocaleString('en-IN', {minimumFractionDigits: 2});
        profitEl.style.color = netProfit >= 0 ? '#059669' : '#DC2626';

        var marginBadge = document.getElementById('summaryProfitPercent');
        marginBadge.textContent = profitMargin + '% Margin';
        marginBadge.style.background = netProfit >= 0 ? '#D1FAE5' : '#FEE2E2';
        marginBadge.style.color = netProfit >= 0 ? '#065F46' : '#991B1B';
    }

    function validateSaleForm() {
        if (selectedDevices.length === 0) {
            alert('Please select at least 1 phone to complete the sale.');
            return false;
        }
        var mode = document.querySelector('input[name="payment_mode"]:checked')?.value;
        if (mode === 'emi') {
            var prov = document.getElementById('emiProviderSelect').value;
            if (!prov) {
                alert('Please select an EMI Finance Partner.');
                document.getElementById('emiProviderSelect').focus();
                return false;
            }
        }
        return true;
    }

    /* ─── AI OCR EMI BILL / RECEIPT UPLOAD & AUTO-FILL ─── */
    var currentOcrData = null;
    var currentOcrMatchedDevice = null;
    var currentOcrMatchedProviderId = null;

    function triggerEmiScan() {
        document.getElementById('emiBillFileInput').click();
    }

    function handleEmiBillUpload(input) {
        if (!input.files || !input.files[0]) return;
        var file = input.files[0];

        // Check file size (10MB max)
        if (file.size > 10 * 1024 * 1024) {
            alert('File size exceeds 10MB limit. Please upload a smaller image or document.');
            input.value = '';
            return;
        }

        var loadingModal = document.getElementById('ocrLoadingModal');
        loadingModal.style.display = 'flex';
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }

        var formData = new FormData();
        formData.append('bill_image', file);

        fetch("{{ route('mobileshop.pos.scan_emi_bill') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(function(res) {
            return res.json().then(function(json) {
                return { status: res.status, ok: res.ok, data: json };
            });
        })
        .then(function(res) {
            loadingModal.style.display = 'none';
            if (!res.ok || !res.data.success) {
                alert(res.data.message || 'Failed to scan receipt. Please verify image clarity.');
                input.value = '';
                return;
            }

            populateOcrModal(res.data);
            input.value = '';
        })
        .catch(function(err) {
            loadingModal.style.display = 'none';
            alert('Network error while processing document: ' + err.message);
            input.value = '';
        });
    }

    function populateOcrModal(res) {
        currentOcrData = res.data || {};
        currentOcrMatchedDevice = res.matched_device || null;
        currentOcrMatchedProviderId = res.matched_provider_id || null;

        // If matched device not provided by server, try local match against rawDevices by IMEI
        if (!currentOcrMatchedDevice && currentOcrData.imei) {
            var cleanImei = String(currentOcrData.imei).replace(/\D/g, '');
            if (cleanImei) {
                currentOcrMatchedDevice = rawDevices.find(function(d) {
                    return String(d.imei_1).replace(/\D/g, '') === cleanImei ||
                           String(d.imei_2 || '').replace(/\D/g, '') === cleanImei;
                }) || null;
            }
        }

        // Fill review modal inputs
        document.getElementById('ocrCustomerPhone').value = currentOcrData.customer_phone || '';
        document.getElementById('ocrCustomerName').value = currentOcrData.customer_name || '';
        document.getElementById('ocrBrand').value = currentOcrData.brand || '';
        document.getElementById('ocrModel').value = currentOcrData.model || '';
        document.getElementById('ocrImei').value = currentOcrData.imei || '';
        document.getElementById('ocrSalePrice').value = currentOcrData.sale_price || '';
        document.getElementById('ocrLoanNo').value = currentOcrData.emi_loan_no || '';
        document.getElementById('ocrDownpayment').value = currentOcrData.emi_downpayment || '';

        // Provider match
        var provSelect = document.getElementById('ocrProviderSelect');
        if (currentOcrMatchedProviderId) {
            provSelect.value = currentOcrMatchedProviderId;
        } else if (currentOcrData.emi_provider) {
            var matchProv = false;
            var searchName = currentOcrData.emi_provider.toLowerCase();
            for (var i = 0; i < provSelect.options.length; i++) {
                if (provSelect.options[i].text.toLowerCase().indexOf(searchName) !== -1) {
                    provSelect.selectedIndex = i;
                    matchProv = true;
                    break;
                }
            }
            if (!matchProv) provSelect.value = '';
        } else {
            provSelect.value = '';
        }

        // Stock match card display
        var matchCard = document.getElementById('ocrStockMatchCard');
        var matchTitle = document.getElementById('ocrMatchTitle');
        var matchSubtitle = document.getElementById('ocrMatchSubtitle');
        var matchBadge = document.getElementById('ocrMatchBadge');
        var matchIcon = document.getElementById('ocrMatchIcon');

        if (currentOcrMatchedDevice) {
            matchCard.style.background = '#F0FDF4';
            matchCard.style.borderColor = '#BBF7D0';
            matchIcon.style.background = '#DCFCE7';
            matchIcon.style.color = '#16A34A';
            matchIcon.innerHTML = '<i data-lucide="check" style="width:18px; height:18px;"></i>';
            matchTitle.textContent = 'Matched: ' + currentOcrMatchedDevice.brand + ' ' + currentOcrMatchedDevice.model;
            matchSubtitle.textContent = 'IMEI: ' + currentOcrMatchedDevice.imei_1 + ' • ₹' + Number(currentOcrMatchedDevice.selling_price).toLocaleString('en-IN');
            matchBadge.textContent = 'In Stock';
            matchBadge.style.background = '#22C55E';
        } else {
            matchCard.style.background = '#FFFBEB';
            matchCard.style.borderColor = '#FDE68A';
            matchIcon.style.background = '#FEF3C7';
            matchIcon.style.color = '#D97706';
            matchIcon.innerHTML = '<i data-lucide="alert-triangle" style="width:18px; height:18px;"></i>';
            matchTitle.textContent = 'Device Not Found in Active Stock';
            matchSubtitle.textContent = 'You can select the phone manually from stock after confirming';
            matchBadge.textContent = 'Manual Pick';
            matchBadge.style.background = '#F59E0B';
        }

        // Notice text
        var noticeEl = document.getElementById('ocrModalNotice');
        if (res.notice) {
            document.getElementById('ocrNoticeText').textContent = res.notice;
            noticeEl.style.display = 'block';
        } else {
            noticeEl.style.display = 'none';
        }

        var reviewModal = document.getElementById('ocrReviewModal');
        reviewModal.style.display = 'flex';

        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }
    }

    function closeOcrModal() {
        document.getElementById('ocrReviewModal').style.display = 'none';
    }

    function applyOcrDataToForm() {
        var phone = document.getElementById('ocrCustomerPhone').value.trim();
        var name = document.getElementById('ocrCustomerName').value.trim();
        var salePrice = parseFloat(document.getElementById('ocrSalePrice').value) || 0;
        var provId = document.getElementById('ocrProviderSelect').value;
        var loanNo = document.getElementById('ocrLoanNo').value.trim();
        var dp = parseFloat(document.getElementById('ocrDownpayment').value) || 0;
        var imeiVal = document.getElementById('ocrImei').value.trim();

        // 1. Customer
        if (phone) {
            document.getElementById('customerPhone').value = phone;
            checkCustomerPhone();
        }
        if (name) {
            document.getElementById('customerName').value = name;
        }

        // 2. Device Cart
        var targetDevice = currentOcrMatchedDevice;
        if (!targetDevice && imeiVal) {
            var cleanImei = imeiVal.replace(/\D/g, '');
            targetDevice = rawDevices.find(function(d) {
                return String(d.imei_1).replace(/\D/g, '') === cleanImei;
            });
        }

        if (targetDevice) {
            if (!isDeviceSelected(targetDevice.id)) {
                addDeviceById(targetDevice.id);
            }
            if (salePrice > 0) {
                var found = selectedDevices.find(function(d) { return d.id === targetDevice.id; });
                if (found) {
                    found.sale_price = salePrice;
                    renderSelectedDevices();
                }
            }
        }

        // 3. Switch to EMI Mode
        onPaymentModeToggle('emi');

        // 4. Select EMI Provider
        if (provId) {
            document.getElementById('emiProviderSelect').value = provId;
            onEmiProviderSelectChange();
        }

        // 5. Loan Ref Number
        if (loanNo) {
            var loanInput = document.getElementById('emiLoanNo');
            if (loanInput) loanInput.value = loanNo;
        }

        // 6. Downpayment
        if (dp > 0) {
            document.getElementById('emiDownpaymentRequired').value = dp.toFixed(2);
            document.getElementById('emiAmountPaid').value = dp.toFixed(2);
            document.getElementById('amountPaid').value = dp.toFixed(2);
        }

        // 7. Recalculate
        recalcSaleFinancials();

        // Close Modal
        closeOcrModal();

        // Display success banner
        var successBanner = document.createElement('div');
        successBanner.className = 'flash-success';
        successBanner.style.cssText = 'border-radius:10px; margin-bottom:16px; padding:12px 16px; background:#ECFDF5; border:1px solid #A7F3D0; color:#065F46; display:flex; align-items:center; gap:8px;';
        successBanner.innerHTML = '<i data-lucide="check-circle-2" style="width:18px;height:18px; color:#10B981;"></i> <strong>Boom! Extracted slip details applied successfully!</strong> Customer, device, and EMI setup are populated.';
        
        var container = document.querySelector('div[style*="max-width: 1280px"]');
        if (container) {
            container.insertBefore(successBanner, container.firstChild);
            setTimeout(function() {
                if (successBanner.parentNode) successBanner.remove();
            }, 6000);
        }

        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }

        // Scroll to customer details
        document.getElementById('customerPhone').scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
</script>
@endpush