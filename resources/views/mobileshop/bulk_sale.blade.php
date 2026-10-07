@extends('mobileshop.layout')

@section('title', 'Bulk Customer Sales Register — PhoneFix Azamgarh')
@section('page-title', 'Bulk Sales & Multi-Customer Register')

@section('page-actions')
    <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
        <a href="{{ route('mobileshop.sales') }}" class="btn btn-outline btn-sm" style="font-weight:700;">
            <i data-lucide="arrow-left" style="width:13px;height:13px;"></i> Back to Sales Register
        </a>
        <a href="{{ route('mobileshop.accessories.pos') }}" class="btn btn-outline btn-sm" style="font-weight:700;">
            <i data-lucide="zap" style="width:13px;height:13px;"></i> POS Counter
        </a>
        <a href="{{ route('mobileshop.accessories.purchase') }}" class="btn btn-outline btn-sm" style="font-weight:700;">
            <i data-lucide="truck" style="width:13px;height:13px;"></i> Bulk Purchase
        </a>
    </div>
@endsection

@section('content')
<style>
    /* ── Hide browser number spinners ── */
    .bulk-num-input::-webkit-outer-spin-button,
    .bulk-num-input::-webkit-inner-spin-button { -webkit-appearance: none !important; margin: 0 !important; }
    .bulk-num-input { -moz-appearance: textfield !important; }

    /* ── Container Layout (Matches Bulk Purchase) ── */
    .bulk-sale-container {
        max-width: 1420px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    /* ── High-Contrast Form Inputs ── */
    .sale-input {
        width: 100%;
        color: #111827 !important;
        font-weight: 600 !important;
        background: #ffffff !important;
        border: 1px solid #CBD5E1 !important;
        border-radius: 6px !important;
        padding: 6px 10px !important;
        font-size: 13px !important;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
        box-sizing: border-box;
    }
    .sale-input:focus {
        outline: none !important;
        border-color: #6366F1 !important;
        box-shadow: 0 0 0 2.5px rgba(99, 102, 241, 0.15) !important;
        background: #ffffff !important;
    }
    .sale-input.num-field {
        font-family: 'JetBrains Mono', monospace !important;
        font-weight: 700 !important;
    }

    /* ── Stepper Component ── */
    .stepper-wrap {
        display: inline-flex;
        align-items: center;
        width: 100%;
        border: 1px solid #CBD5E1;
        border-radius: 6px;
        background: #ffffff;
        overflow: hidden;
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .stepper-wrap:focus-within {
        border-color: #6366F1 !important;
        box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.15);
    }
    .stepper-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        background: #F8FAFC;
        color: #475569;
        font-size: 15px;
        font-weight: 800;
        border: none;
        cursor: pointer;
        user-select: none;
        transition: background 0.1s, color 0.1s;
        padding: 0 9px;
        height: 32px;
        flex-shrink: 0;
    }
    .stepper-btn:hover {
        background: #EDE9FE;
        color: #4F46E5;
    }
    .stepper-btn-minus { border-right: 1px solid #E2E8F0; }
    .stepper-btn-plus  { border-left:  1px solid #E2E8F0; }
    .stepper-input {
        flex: 1;
        border: none !important;
        border-radius: 0 !important;
        text-align: center !important;
        font-family: 'JetBrains Mono', monospace !important;
        font-weight: 800 !important;
        color: #111827 !important;
        padding: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
        min-width: 0;
        height: 32px;
    }

    /* ── Ghost Buttons ── */
    .btn-ghost-add {
        height: 32px;
        padding: 0 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        border-radius: 6px;
        background: #EEF2FF;
        border: 1px solid #C7D2FE;
        color: #4F46E5;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .btn-ghost-add:hover {
        background: #4F46E5 !important;
        border-color: #4F46E5 !important;
        color: #FFFFFF !important;
    }

    .btn-ghost-delete {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        background: #F1F5F9;
        border: 1px solid #CBD5E1;
        color: #475569;
        cursor: pointer;
        padding: 0;
        transition: all 0.15s ease;
        flex-shrink: 0;
    }
    .btn-ghost-delete:hover {
        background: #FEF2F2 !important;
        border-color: #FECACA !important;
        color: #DC2626 !important;
    }

    /* ── Autocomplete Dropdown ── */
    .search-picker-dropdown {
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        background: #FFFFFF;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        box-shadow: 0 12px 28px -4px rgba(15, 23, 42, 0.16);
        max-height: 240px;
        overflow-y: auto;
        z-index: 50;
        display: none;
    }
    .search-picker-item {
        padding: 8px 12px;
        cursor: pointer;
        border-bottom: 1px solid #F1F5F9;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: background 0.1s ease;
    }
    .search-picker-item:hover {
        background: #F0FDF4;
    }

    /* ── Customer Card Layout ── */
    .customer-sale-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        margin-bottom: 14px;
        overflow: visible;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .customer-sale-card:hover {
        border-color: #CBD5E1;
        box-shadow: 0 4px 12px rgba(0,0,0,0.04);
    }
    .customer-card-header {
        background: #FAFAFA;
        border-bottom: 1px solid #E2E8F0;
        padding: 10px 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
    }
    .customer-card-body {
        padding: 14px 16px;
    }

    /* ── Items Grid / Table ── */
    .sale-items-table-header {
        display: grid;
        grid-template-columns: 2.2fr 85px 120px 110px 110px 42px;
        gap: 10px;
        padding: 6px 12px;
        background: #F1F5F9;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        margin-bottom: 8px;
    }
    .sale-item-row {
        display: grid;
        grid-template-columns: 2.2fr 85px 120px 110px 110px 42px;
        gap: 10px;
        align-items: center;
        margin-bottom: 8px;
    }

    /* ── Responsive adjustments ── */
    @media (max-width: 900px) {
        .sale-items-table-header { display: none; }
        .sale-item-row {
            grid-template-columns: 1fr;
            background: #F8FAFC;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #E2E8F0;
            gap: 8px;
        }
    }
</style>

<div class="bulk-sale-container">

    <!-- ─── TOP NOTIFICATION / ALERT BOX ─── -->
    <div id="bulkSaleAlertBox" style="display:none; background:#FEF2F2; border:1px solid #FECDD3; border-radius:8px; padding:12px 16px; color:#991B1B; font-size:13px; font-weight:600; box-shadow:0 1px 3px rgba(0,0,0,0.04);"></div>

    <!-- ─── HERO TOOLBAR / INTAKE HEADER (Identical to Bulk Purchase) ─── -->
    <div class="card" style="border-radius:8px; border:1px solid #E2E8F0; background:#FFFFFF; margin-bottom: 2px;">
        <div class="card-body" style="padding: 14px 18px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:14px;">
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="background:#EEF2FF; border:1px solid #C7D2FE; width:44px; height:44px; border-radius:10px; display:flex; align-items:center; justify-content:center; color:#4F46E5; flex-shrink:0;">
                    <i data-lucide="layers" style="width:22px;height:22px;"></i>
                </div>
                <div>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <h2 style="font-size: 17px; font-weight: 800; color: #0F172A; margin:0;">Bulk Multi-Customer Sales Register</h2>
                        <span style="background:#E0E7FF; color:#3730A3; font-size:11px; font-weight:800; padding:2px 8px; border-radius:5px;">Batch POS</span>
                    </div>
                    <p style="font-size: 12px; color: #64748B; margin: 2px 0 0 0;">
                        Record multiple customer sales in one unified screen with flexible payment modes (Cash, UPI, Udhari / Khata, Split Payments).
                    </p>
                </div>
            </div>

            <!-- Toolbar Quick Action Buttons -->
            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                <button type="button" onclick="addCustomerCard()" class="btn btn-primary btn-sm" style="background:#4F46E5; border-color:#4F46E5; font-weight:800; padding:6px 14px; border-radius:6px; font-size:12px; display:inline-flex; align-items:center; gap:6px;">
                    <i data-lucide="user-plus" style="width:14px;height:14px;"></i> + Add Customer Sale
                </button>
                <button type="button" onclick="addMultipleCustomerCards(3)" class="btn btn-outline btn-sm" style="font-weight:700; font-size:12px; padding:6px 12px; border-radius:6px; color:#475569;">
                    +3 Customers
                </button>
                <button type="button" onclick="loadSampleBulkSalesData()" class="btn btn-outline btn-sm" style="font-weight:700; font-size:12px; padding:6px 12px; border-radius:6px; color:#059669; border-color:#A7F3D0; background:#ECFDF5;" title="Fill sample walk-in customers and items to test">
                    <i data-lucide="sparkles" style="width:13px;height:13px;"></i> Demo Data
                </button>
                <button type="button" onclick="clearAllCustomerCards()" class="btn btn-outline btn-sm" style="color:#DC2626; border-color:#FECACA; font-weight:700; font-size:12px; padding:6px 12px; border-radius:6px;">
                    <i data-lucide="trash-2" style="width:13px;height:13px;"></i> Clear All
                </button>
            </div>
        </div>
    </div>

    <!-- ─── CUSTOMER SALES CARDS CONTAINER ─── -->
    <div id="bulkSalesContainer">
        <!-- Injected dynamically via JavaScript -->
    </div>

    <!-- Add Customer Footer Button -->
    <div style="text-align: center; margin-bottom: 10px;">
        <button type="button" onclick="addCustomerCard()" style="background:#FFFFFF; color:#4F46E5; border:1.5px dashed #818CF8; border-radius:8px; padding:12px 24px; font-size:13px; font-weight:800; width:100%; display:flex; align-items:center; justify-content:center; gap:8px; cursor:pointer; box-shadow:0 1px 3px rgba(0,0,0,0.03); transition: background 0.15s ease;">
            <i data-lucide="user-plus" style="width:16px;height:16px;"></i> + Click to Add Another Customer Sale Entry
        </button>
    </div>

    <!-- ─── DESKTOP SUMMARY & SETTLEMENT CARD (Identical to Bulk Purchase Lines 923-958) ─── -->
    <div class="card" style="border:1px solid #E2E8F0; background:#FFFFFF; border-radius:10px; box-shadow:0 2px 6px rgba(0,0,0,0.04); margin-bottom:24px;">
        <div class="card-body" style="padding:14px 18px;">
            <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px; padding-bottom:14px; border-bottom:1px solid #F1F5F9;">
                
                <!-- Metrics Bar -->
                <div style="display:flex; align-items:center; gap:20px; flex-wrap:wrap;">
                    <div>
                        <span style="font-size:10px; color:#64748B; text-transform:uppercase; font-weight:700; letter-spacing:0.4px;">Customers</span>
                        <div style="font-size:15px; font-weight:800; color:#0F172A;" id="lblGrandCustomersCount">0 customers</div>
                    </div>
                    <div style="border-left:1px solid #E2E8F0; padding-left:20px;">
                        <span style="font-size:10px; color:#64748B; text-transform:uppercase; font-weight:700; letter-spacing:0.4px;">Line Items</span>
                        <div style="font-size:15px; font-weight:800; color:#0F172A;" id="lblGrandItemsCount">0 items</div>
                    </div>
                    <div style="border-left:1px solid #E2E8F0; padding-left:20px;">
                        <span style="font-size:10px; color:#64748B; text-transform:uppercase; font-weight:700; letter-spacing:0.4px;">Total Units</span>
                        <div style="font-size:15px; font-weight:800; color:#0F172A;" id="lblGrandUnitsCount">0 units</div>
                    </div>
                    <div style="border-left:1px solid #E2E8F0; padding-left:20px;">
                        <span style="font-size:10px; color:#64748B; text-transform:uppercase; font-weight:700; letter-spacing:0.4px;">Grand Revenue</span>
                        <div style="font-size:19px; font-weight:900; color:#4F46E5; font-family:'JetBrains Mono', monospace;" id="lblGrandTotalRevenue">₹0.00</div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div style="display:flex; align-items:center; gap:10px;">
                    <a href="{{ route('mobileshop.sales') }}" class="btn btn-outline" style="font-weight:700; padding:8px 18px; border-radius:6px; font-size:12.5px; border-color:#CBD5E1; color:#475569;">
                        Cancel
                    </a>
                    <button type="button" onclick="submitAllBulkSales()" id="btnProcessAllSales" class="btn btn-primary" style="background:#4F46E5; border-color:#4F46E5; font-weight:800; padding:9px 24px; font-size:13px; border-radius:6px; box-shadow:0 3px 8px rgba(79, 70, 229, 0.3); display:inline-flex; align-items:center; gap:8px;">
                        <i data-lucide="check-check" style="width:16px;height:16px;"></i> Process All Sales &amp; Generate Invoices
                    </button>
                </div>
            </div>

            <!-- Expected Collections Strip (Cash / UPI / Khata breakdown) -->
            <div style="margin-top:12px; display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:10px;">
                <div style="background:#F0FDF4; border:1px solid #BBF7D0; border-radius:6px; padding:8px 12px; display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <span style="font-size:10px; font-weight:700; color:#166534; text-transform:uppercase;">💵 Cash Total</span>
                        <div id="lblExpectedCash" style="font-size:15px; font-weight:900; color:#15803D; font-family:'JetBrains Mono', monospace;">₹0.00</div>
                    </div>
                    <span style="font-size:10.5px; color:#166534; font-weight:600;">Counter drawer</span>
                </div>

                <div style="background:#EFF6FF; border:1px solid #BFDBFE; border-radius:6px; padding:8px 12px; display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <span style="font-size:10px; font-weight:700; color:#1E40AF; text-transform:uppercase;">📱 UPI / QR Total</span>
                        <div id="lblExpectedUpi" style="font-size:15px; font-weight:900; color:#1D4ED8; font-family:'JetBrains Mono', monospace;">₹0.00</div>
                    </div>
                    <span style="font-size:10.5px; color:#1E40AF; font-weight:600;">Bank / QR Soundbox</span>
                </div>

                <div style="background:#FFF1F2; border:1px solid #FECDD3; border-radius:6px; padding:8px 12px; display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <span style="font-size:10px; font-weight:700; color:#991B1B; text-transform:uppercase;">📒 Khata / Udhari Due</span>
                        <div id="lblExpectedUdhari" style="font-size:15px; font-weight:900; color:#DC2626; font-family:'JetBrains Mono', monospace;">₹0.00</div>
                    </div>
                    <span style="font-size:10.5px; color:#991B1B; font-weight:600;">Customer ledger</span>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Customer & Item Autocomplete Datalists -->
<datalist id="allCustomersList">
    @foreach($customers as $c)
        <option value="{{ $c->name }}" data-phone="{{ $c->phone }}" data-balance="{{ $c->udhari_balance ?? 0 }}">{{ $c->name }} ({{ $c->phone }})</option>
    @endforeach
</datalist>

<datalist id="allPhonesList">
    @foreach($customers as $c)
        <option value="{{ $c->phone }}" data-name="{{ $c->name }}" data-balance="{{ $c->udhari_balance ?? 0 }}">{{ $c->phone }} — {{ $c->name }}</option>
    @endforeach
</datalist>

@endsection

@push('scripts')
<script>
    const companyId = {{ json_encode(company_id()) }};
    const allPartsData = @json($parts ?? []);
    const customersData = @json($customers ?? []);
    let customerCardCounter = 0;

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function showPageWarning(msg, targetCard = null, targetInput = null) {
        const alertBox = document.getElementById('bulkSaleAlertBox');
        if (alertBox) {
            alertBox.innerHTML = `
                <div style="display:flex; align-items:center; gap:8px; justify-content:space-between; width:100%;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <span style="font-size:16px;">⚠️</span>
                        <span>${escapeHtml(msg)}</span>
                    </div>
                    <button type="button" onclick="this.closest('#bulkSaleAlertBox').style.display='none'" style="background:none;border:none;color:#991B1B;font-weight:800;font-size:14px;cursor:pointer;">✕</button>
                </div>
            `;
            alertBox.style.display = 'block';
            alertBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } else {
            alert(msg);
        }

        if (targetCard) {
            targetCard.style.outline = '2px solid #EF4444';
            targetCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() => { targetCard.style.outline = ''; }, 4500);
        }
        if (targetInput) {
            targetInput.focus();
            targetInput.style.borderColor = '#EF4444';
            targetInput.style.backgroundColor = '#FFF5F5';
            setTimeout(() => {
                targetInput.style.borderColor = '';
                targetInput.style.backgroundColor = '';
            }, 4500);
        }
    }

    /* ── Customer Card Creator ── */
    function addCustomerCard(initialData = null) {
        const container = document.getElementById('bulkSalesContainer');
        if (!container) return;

        customerCardCounter++;
        const cIdx = customerCardCounter;
        const card = document.createElement('div');
        card.className = 'customer-sale-card';
        card.dataset.customerIndex = cIdx;
        card.id = `custCard_${cIdx}`;

        card.innerHTML = `
            <!-- Customer Card Header -->
            <div class="customer-card-header">
                <div style="display:flex; align-items:center; gap:10px;">
                    <span class="badge cust-number-badge" style="background:#4F46E5; color:#fff; font-size:12px; font-weight:800; padding:4px 10px; border-radius:6px;">
                        Customer #${container.children.length + 1}
                    </span>
                    <span class="cust-header-title" style="font-size:13.5px; font-weight:700; color:#1E293B;">Sale Entry</span>
                    <span class="cust-khata-due-pill" style="display:none; font-size:11px; font-weight:800; background:#FEE2E2; color:#DC2626; padding:2px 8px; border-radius:4px; border:1px solid #FECACA;"></span>
                </div>

                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="display:flex; align-items:center; gap:6px;">
                        <span style="font-size:11px; color:#64748B; font-weight:700; text-transform:uppercase;">Customer Total:</span>
                        <span class="cust-total-badge" style="font-size:15px; font-weight:900; color:#4F46E5; font-family:'JetBrains Mono', monospace;">₹0.00</span>
                    </div>
                    <button type="button" onclick="removeCustomerCard(this)" class="btn-ghost-delete" title="Remove this customer sale" style="color:#DC2626;">
                        ✕
                    </button>
                </div>
            </div>

            <div class="customer-card-body">
                <!-- Customer Details & Payment Mode Row -->
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap:12px; margin-bottom:14px;">
                    <div>
                        <label style="font-size:11px; font-weight:700; color:#475569; display:block; margin-bottom:4px;">
                            Customer Name <span style="font-weight:400; color:#94A3B8;">(Walk-in by default)</span>
                        </label>
                        <input type="text" class="sale-input cust-input-name" placeholder="Customer Name" list="allCustomersList" oninput="onCustomerNameInput(this)">
                    </div>

                    <div>
                        <label style="font-size:11px; font-weight:700; color:#475569; display:block; margin-bottom:4px;">
                            Mobile Number <span style="font-weight:400; color:#94A3B8;">(Optional)</span>
                        </label>
                        <input type="tel" class="sale-input cust-input-phone" placeholder="10-digit Mobile" list="allPhonesList" oninput="onCustomerPhoneInput(this)">
                    </div>

                    <div>
                        <label style="font-size:11px; font-weight:700; color:#475569; display:block; margin-bottom:4px;">
                            Payment Mode <span style="color:#DC2626;">*</span>
                        </label>
                        <select class="sale-input cust-input-mode" onchange="onCustomerModeChange(this)" style="font-weight:700;">
                            <option value="cash" selected>💵 Cash Payment</option>
                            <option value="upi">📱 UPI / QR</option>
                            <option value="udhari">📒 Full Udhari (Khata)</option>
                            <option value="cash+upi">💵+📱 Cash + UPI Split</option>
                            <option value="upi+cash">📱+💵 UPI + Cash Split</option>
                            <option value="cash+udhari">💵+📒 Cash + Udhari Split</option>
                            <option value="upi+udhari">📱+📒 UPI + Udhari Split</option>
                        </select>
                    </div>
                </div>

                <!-- Dynamic Split Row -->
                <div class="cust-split-row" style="display:none; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:10px 14px; margin-bottom:14px;">
                    <!-- Rendered by onCustomerModeChange() -->
                </div>

                <!-- Customer Items Section -->
                <div style="background:#FAFAFA; border:1px solid #E2E8F0; border-radius:8px; padding:12px; margin-bottom:10px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                        <span style="font-size:11.5px; font-weight:800; color:#334155; text-transform:uppercase; letter-spacing:0.3px; display:flex; align-items:center; gap:6px;">
                            <i data-lucide="package" style="width:13px;height:13px; color:#4F46E5;"></i> Customer Items (<span class="cust-items-count">1</span>)
                        </span>
                        <button type="button" onclick="addItemRowToCustomer(this)" class="btn-ghost-add" style="height:28px; padding:0 10px; font-size:11.5px;">
                            + Add Item
                        </button>
                    </div>

                    <!-- Items Table Header -->
                    <div class="sale-items-table-header">
                        <div>Product / Item Name &amp; Search</div>
                        <div style="text-align:center;">Stock</div>
                        <div style="text-align:center;">Quantity</div>
                        <div style="text-align:right;">Rate (₹)</div>
                        <div style="text-align:right;">Total (₹)</div>
                        <div style="text-align:center;"></div>
                    </div>

                    <!-- Items Rows Container -->
                    <div class="cust-items-container">
                        <!-- Initial Item Row -->
                    </div>
                </div>

                <!-- Card Subtotal Bar -->
                <div style="display:flex; justify-content:space-between; align-items:center; background:#F1F5F9; padding:8px 14px; border-radius:6px;">
                    <div style="font-size:12px; color:#64748B; font-weight:600;">
                        Items: <strong class="lbl-cust-item-lines" style="color:#1E293B;">1</strong> &bull; Units: <strong class="lbl-cust-units" style="color:#1E293B;">1</strong>
                    </div>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <span style="font-size:12px; color:#475569; font-weight:700;">Customer Total:</span>
                        <strong class="cust-total-badge" style="font-size:15px; color:#0F172A; font-family:'JetBrains Mono', monospace;">₹0.00</strong>
                    </div>
                </div>
            </div>
        `;

        container.appendChild(card);
        updateAllCardsUI();

        // Add initial item row
        const itemsContainer = card.querySelector('.cust-items-container');
        if (initialData && initialData.items && initialData.items.length > 0) {
            if (initialData.customer_name) card.querySelector('.cust-input-name').value = initialData.customer_name;
            if (initialData.customer_phone) card.querySelector('.cust-input-phone').value = initialData.customer_phone;
            if (initialData.payment_mode) card.querySelector('.cust-input-mode').value = initialData.payment_mode;
            onCustomerNameInput(card.querySelector('.cust-input-name'));

            initialData.items.forEach(it => {
                createItemRow(itemsContainer, it);
            });
        } else {
            createItemRow(itemsContainer);
        }

        recalcCustomerCard(card);
        if (window.refreshIcons) window.refreshIcons();
        else if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons();
    }

    function addMultipleCustomerCards(count = 3) {
        for (let i = 0; i < count; i++) {
            addCustomerCard();
        }
    }

    function removeCustomerCard(btnEl) {
        const card = btnEl.closest('.customer-sale-card');
        if (!card) return;
        const container = document.getElementById('bulkSalesContainer');
        if (container.children.length <= 1) {
            // Keep at least one card
            card.querySelector('.cust-input-name').value = '';
            card.querySelector('.cust-input-phone').value = '';
            card.querySelector('.cust-input-mode').value = 'cash';
            const itemsContainer = card.querySelector('.cust-items-container');
            itemsContainer.innerHTML = '';
            createItemRow(itemsContainer);
            recalcCustomerCard(card);
            updateAllCardsUI();
            return;
        }

        card.remove();
        updateAllCardsUI();
        recalcGrandTotals();
    }

    function clearAllCustomerCards() {
        if (!confirm('Are you sure you want to clear all customer entries?')) return;
        const container = document.getElementById('bulkSalesContainer');
        container.innerHTML = '';
        addCustomerCard();
    }

    function updateAllCardsUI() {
        const container = document.getElementById('bulkSalesContainer');
        if (!container) return;
        const cards = container.querySelectorAll('.customer-sale-card');
        cards.forEach((c, idx) => {
            const badge = c.querySelector('.cust-number-badge');
            if (badge) badge.textContent = `Customer #${idx + 1}`;
        });
        const countEl = document.getElementById('lblGrandCustomersCount');
        if (countEl) countEl.textContent = `${cards.length} ${cards.length === 1 ? 'customer' : 'customers'}`;
    }

    /* ── Customer Info Interactivity ── */
    function onCustomerNameInput(inputEl) {
        const card = inputEl.closest('.customer-sale-card');
        if (!card) return;
        const val = (inputEl.value || '').trim();
        const titleEl = card.querySelector('.cust-header-title');
        if (titleEl) {
            titleEl.textContent = val ? `— ${val}` : 'Sale Entry';
        }

        // Check if matched in datalist
        const matched = customersData.find(c => c.name.toLowerCase() === val.toLowerCase());
        if (matched) {
            const phoneInput = card.querySelector('.cust-input-phone');
            if (phoneInput && !phoneInput.value && matched.phone) {
                phoneInput.value = matched.phone;
            }
            updateCustomerKhataPill(card, matched);
        }
    }

    function onCustomerPhoneInput(inputEl) {
        const card = inputEl.closest('.customer-sale-card');
        if (!card) return;
        const phone = (inputEl.value || '').trim();
        const matched = customersData.find(c => String(c.phone).trim() === phone);
        if (matched) {
            const nameInput = card.querySelector('.cust-input-name');
            if (nameInput && !nameInput.value) {
                nameInput.value = matched.name;
                onCustomerNameInput(nameInput);
            }
            updateCustomerKhataPill(card, matched);
        }
    }

    function updateCustomerKhataPill(card, cust) {
        const pill = card.querySelector('.cust-khata-due-pill');
        if (!pill) return;
        const bal = parseFloat(cust.udhari_balance || 0);
        if (bal > 0) {
            pill.textContent = `Pending Khata: ₹${bal.toFixed(2)}`;
            pill.style.display = 'inline-block';
        } else {
            pill.style.display = 'none';
        }
    }

    /* ── Item Rows inside Customer Card ── */
    function addItemRowToCustomer(btnEl) {
        const card = btnEl.closest('.customer-sale-card');
        const container = card?.querySelector('.cust-items-container');
        if (container) {
            createItemRow(container);
            recalcCustomerCard(card);
        }
    }

    function createItemRow(container, initialItem = null) {
        const row = document.createElement('div');
        row.className = 'sale-item-row';
        row.innerHTML = `
            <!-- Item Name Search -->
            <div style="position:relative;" class="item-search-wrapper">
                <input type="text"
                       class="sale-input input-item-name"
                       placeholder="Type item name to search..."
                       autocomplete="off"
                       oninput="onItemSearchInput(this)"
                       style="padding-right: 28px;">
                <input type="hidden" class="input-part-id">
                <button type="button"
                        onclick="clearItemSelection(this)"
                        class="btn-clear-item"
                        title="Clear item"
                        style="display:none; position:absolute; right:8px; top:50%; transform:translateY(-50%); background:#E2E8F0; border:none; border-radius:50%; width:18px; height:18px; font-size:10px; line-height:18px; text-align:center; color:#475569; cursor:pointer; padding:0;">✕</button>
                <div class="search-picker-dropdown"></div>
            </div>

            <!-- In-Stock Badge -->
            <div style="text-align:center;">
                <span class="stock-pill" style="font-size:11px; font-weight:700; color:#64748B; background:#F1F5F9; padding:4px 8px; border-radius:5px; display:inline-block; width:100%; box-sizing:border-box;">
                    —
                </span>
            </div>

            <!-- Quantity Stepper -->
            <div>
                <div class="stepper-wrap">
                    <button type="button" class="stepper-btn stepper-btn-minus" onclick="stepItemQty(this, -1)">−</button>
                    <input type="number" class="stepper-input input-quantity" value="1" min="1" oninput="onItemRowCalc(this)">
                    <button type="button" class="stepper-btn stepper-btn-plus" onclick="stepItemQty(this, 1)">+</button>
                </div>
            </div>

            <!-- Rate / Price -->
            <div>
                <input type="number" step="0.01" min="0" class="sale-input num-field input-unit-price" placeholder="Rate (₹)" oninput="onItemRowCalc(this)" style="text-align:right;">
            </div>

            <!-- Total -->
            <div style="text-align:right; font-weight:800; font-size:13px; color:#0F172A; font-family:'JetBrains Mono', monospace;" class="lbl-line-total">
                ₹0.00
            </div>

            <!-- Action -->
            <div style="text-align:center;">
                <button type="button" onclick="removeItemRow(this)" class="btn-ghost-delete" title="Remove item row">
                    ✕
                </button>
            </div>
        `;

        container.appendChild(row);

        if (initialItem) {
            const nameInput = row.querySelector('.input-item-name');
            const partIdInput = row.querySelector('.input-part-id');
            const qtyInput = row.querySelector('.input-quantity');
            const priceInput = row.querySelector('.input-unit-price');
            const stockPill = row.querySelector('.stock-pill');

            if (nameInput) nameInput.value = initialItem.part_name || initialItem.name || '';
            if (partIdInput) partIdInput.value = initialItem.part_id || initialItem.id || '';
            if (qtyInput) qtyInput.value = initialItem.quantity || initialItem.qty || 1;
            if (priceInput) priceInput.value = initialItem.unit_price || initialItem.price || 0;
            if (stockPill && initialItem.stock !== undefined) {
                stockPill.textContent = `${initialItem.stock} in stock`;
                stockPill.style.color = initialItem.stock > 0 ? '#059669' : '#DC2626';
                stockPill.style.background = initialItem.stock > 0 ? '#ECFDF5' : '#FEF2F2';
            }
            onItemRowCalc(qtyInput);
        }

        updateItemRowsUI(container);
    }

    function removeItemRow(btnEl) {
        const row = btnEl.closest('.sale-item-row');
        const container = row?.parentElement;
        const card = btnEl.closest('.customer-sale-card');
        if (!row || !container || !card) return;

        if (container.children.length <= 1) {
            // Keep at least 1 row
            row.querySelector('.input-item-name').value = '';
            row.querySelector('.input-part-id').value = '';
            row.querySelector('.input-unit-price').value = '';
            row.querySelector('.input-quantity').value = '1';
            row.querySelector('.lbl-line-total').textContent = '₹0.00';
            row.querySelector('.stock-pill').textContent = '—';
            row.querySelector('.stock-pill').style.color = '#64748B';
            row.querySelector('.stock-pill').style.background = '#F1F5F9';
            recalcCustomerCard(card);
            return;
        }

        row.remove();
        updateItemRowsUI(container);
        recalcCustomerCard(card);
    }

    function updateItemRowsUI(container) {
        const card = container.closest('.customer-sale-card');
        const rows = container.querySelectorAll('.sale-item-row');
        const countSpan = card?.querySelector('.cust-items-count');
        const lineCountSpan = card?.querySelector('.lbl-cust-item-lines');
        if (countSpan) countSpan.textContent = rows.length;
        if (lineCountSpan) lineCountSpan.textContent = rows.length;
    }

    function stepItemQty(btnEl, delta) {
        const row = btnEl.closest('.sale-item-row');
        const input = row?.querySelector('.input-quantity');
        if (!input) return;
        const cur = Math.max(1, parseInt(input.value || 1));
        const next = Math.max(1, cur + delta);
        input.value = next;
        onItemRowCalc(input);
    }

    function onItemRowCalc(el) {
        const row = el.closest('.sale-item-row');
        const card = el.closest('.customer-sale-card');
        if (!row || !card) return;

        const qty = Math.max(1, parseInt(row.querySelector('.input-quantity')?.value || 1));
        const price = parseFloat(row.querySelector('.input-unit-price')?.value) || 0;
        const lineTot = qty * price;

        const totalCell = row.querySelector('.lbl-line-total');
        if (totalCell) totalCell.textContent = '₹' + lineTot.toFixed(2);

        recalcCustomerCard(card);
    }

    /* ── Autocomplete / Live Search ── */
    function onItemSearchInput(inputEl) {
        const row = inputEl.closest('.sale-item-row');
        const wrapper = inputEl.closest('.item-search-wrapper');
        const dropdown = wrapper?.querySelector('.search-picker-dropdown');
        const clearBtn = wrapper?.querySelector('.btn-clear-item');
        const term = (inputEl.value || '').trim().toLowerCase();

        if (!dropdown) return;

        if (!term) {
            dropdown.style.display = 'none';
            if (clearBtn) clearBtn.style.display = 'none';
            row.querySelector('.input-part-id').value = '';
            row.querySelector('.stock-pill').textContent = '—';
            row.querySelector('.stock-pill').style.color = '#64748B';
            row.querySelector('.stock-pill').style.background = '#F1F5F9';
            onItemRowCalc(inputEl);
            return;
        }

        if (clearBtn) clearBtn.style.display = 'block';

        const matches = allPartsData.filter(p => {
            const name = (p.name || '').toLowerCase();
            const cat = (p.category || '').toLowerCase();
            return name.includes(term) || cat.includes(term);
        }).slice(0, 25);

        if (matches.length === 0) {
            dropdown.innerHTML = `
                <div style="padding: 10px 12px; text-align: center; color: #64748B; font-size: 12px;">
                    No inventory match for "<strong>${escapeHtml(inputEl.value)}</strong>"
                    <div style="font-size: 11px; color:#4F46E5; margin-top: 3px; font-weight: 600;">Custom retail item will be created automatically</div>
                </div>
            `;
        } else {
            dropdown.innerHTML = matches.map(p => {
                const stock = parseInt(p.stock_qty || 0);
                const price = parseFloat(p.selling_price || 0);
                const sColor = stock > 0 ? '#059669' : '#DC2626';
                const sBg = stock > 0 ? '#ECFDF5' : '#FEF2F2';
                return `
                    <div class="search-picker-item" onclick="onPickItemRow(${row.parentElement.children ? Array.from(row.parentElement.children).indexOf(row) : 0}, ${p.id}, this)">
                        <div style="text-align:left;">
                            <div style="font-weight:700; color:#0F172A; font-size:12.5px;">${escapeHtml(p.name)}</div>
                            <div style="font-size:11px; color:#64748B; display:flex; align-items:center; gap:6px; margin-top:1px;">
                                <span style="background:#F1F5F9; padding:1px 5px; border-radius:4px;">${escapeHtml(p.category || 'General')}</span>
                                <span>•</span>
                                <span style="background:${sBg}; color:${sColor}; padding:1px 5px; border-radius:4px; font-weight:700;">Stock: ${stock}</span>
                            </div>
                        </div>
                        <div style="font-weight:900; color:#16A34A; font-size:13px; font-family:'JetBrains Mono', monospace;">
                            ₹${price.toFixed(2)}
                        </div>
                    </div>
                `;
            }).join('');
        }

        dropdown.style.display = 'block';
    }

    function onPickItemRow(rowIndex, partId, dropdownItemEl) {
        const row = dropdownItemEl.closest('.sale-item-row');
        const part = allPartsData.find(p => p.id === partId);
        if (!row || !part) return;

        const nameInput = row.querySelector('.input-item-name');
        const partIdInput = row.querySelector('.input-part-id');
        const priceInput = row.querySelector('.input-unit-price');
        const stockPill = row.querySelector('.stock-pill');
        const dropdown = row.querySelector('.search-picker-dropdown');
        const clearBtn = row.querySelector('.btn-clear-item');

        if (nameInput) nameInput.value = part.name;
        if (partIdInput) partIdInput.value = part.id;
        if (clearBtn) clearBtn.style.display = 'block';
        if (dropdown) dropdown.style.display = 'none';

        const stock = parseInt(part.stock_qty || 0);
        if (stockPill) {
            stockPill.textContent = `${stock} in stock`;
            stockPill.style.color = stock > 0 ? '#059669' : '#DC2626';
            stockPill.style.background = stock > 0 ? '#ECFDF5' : '#FEF2F2';
        }

        const price = parseFloat(part.selling_price || 0);
        if (priceInput) {
            priceInput.value = price > 0 ? price : '';
            priceInput.placeholder = '₹' + price.toFixed(2);
        }

        onItemRowCalc(priceInput);
    }

    function clearItemSelection(btnEl) {
        const row = btnEl.closest('.sale-item-row');
        if (!row) return;

        row.querySelector('.input-item-name').value = '';
        row.querySelector('.input-part-id').value = '';
        row.querySelector('.input-unit-price').value = '';
        row.querySelector('.stock-pill').textContent = '—';
        row.querySelector('.stock-pill').style.color = '#64748B';
        row.querySelector('.stock-pill').style.background = '#F1F5F9';
        btnEl.style.display = 'none';

        onItemRowCalc(row.querySelector('.input-quantity'));
        row.querySelector('.input-item-name').focus();
    }

    // Dismiss search dropdowns on outside click
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.item-search-wrapper')) {
            document.querySelectorAll('.search-picker-dropdown').forEach(d => d.style.display = 'none');
        }
    });

    /* ── Recalculation & Splits ── */
    function recalcCustomerCard(card) {
        if (!card) return;

        let total = 0;
        let units = 0;
        const rows = card.querySelectorAll('.cust-items-container .sale-item-row');

        rows.forEach(r => {
            const qty = Math.max(1, parseInt(r.querySelector('.input-quantity')?.value || 1));
            const price = parseFloat(r.querySelector('.input-unit-price')?.value) || 0;
            total += (qty * price);
            units += qty;
        });

        card.querySelectorAll('.cust-total-badge').forEach(b => {
            b.textContent = '₹' + total.toFixed(2);
        });

        const unitsEl = card.querySelector('.lbl-cust-units');
        if (unitsEl) unitsEl.textContent = units;

        const mode = card.querySelector('.cust-input-mode')?.value || 'cash';
        syncCustomerSplitFields(card, mode, total);

        recalcGrandTotals();
    }

    function onCustomerModeChange(selectEl) {
        const card = selectEl.closest('.customer-sale-card');
        if (!card) return;
        const mode = selectEl.value;
        const total = getCustomerCurrentTotal(card);
        const splitRow = card.querySelector('.cust-split-row');
        if (!splitRow) return;

        if (mode === 'cash+upi' || mode === 'upi+cash') {
            splitRow.style.display = 'grid';
            splitRow.style.gridTemplateColumns = 'repeat(auto-fit, minmax(180px, 1fr))';
            splitRow.style.gap = '10px';
            const half = (total / 2).toFixed(2);
            const rem = (total - parseFloat(half)).toFixed(2);
            splitRow.innerHTML = `
                <div>
                    <label style="font-size:11px; font-weight:700; color:#475569; display:block; margin-bottom:3px;">Cash Amount (₹)</label>
                    <input type="number" step="0.01" class="sale-input num-field cust-split-cash" placeholder="Cash Amount" value="${half}" oninput="onCustomerCashSplitChange(this, ${total})">
                </div>
                <div>
                    <label style="font-size:11px; font-weight:700; color:#475569; display:block; margin-bottom:3px;">UPI Amount (₹)</label>
                    <input type="number" step="0.01" class="sale-input num-field cust-split-upi" placeholder="UPI Amount" value="${rem}" oninput="onCustomerUpiSplitChange(this, ${total})">
                </div>
            `;
        } else if (mode === 'cash+udhari') {
            splitRow.style.display = 'grid';
            splitRow.style.gridTemplateColumns = 'repeat(auto-fit, minmax(180px, 1fr))';
            splitRow.style.gap = '10px';
            const paid = (total > 0 ? (total * 0.5) : 0).toFixed(2);
            const rem = (total - paid).toFixed(2);
            splitRow.innerHTML = `
                <div>
                    <label style="font-size:11px; font-weight:700; color:#475569; display:block; margin-bottom:3px;">Cash Paid (₹)</label>
                    <input type="number" step="0.01" class="sale-input num-field cust-split-cash" placeholder="Cash Paid" value="${paid}" oninput="onCustomerCashUdhariChange(this, ${total})">
                </div>
                <div style="display:flex; flex-direction:column; justify-content:center;">
                    <span style="font-size:11px; color:#475569; font-weight:700; margin-bottom:3px;">Remaining to Khata</span>
                    <span class="cust-udhari-pill" style="font-size:13px; font-weight:800; color:#DC2626; font-family:'JetBrains Mono', monospace;">Udhari: ₹${rem}</span>
                </div>
            `;
        } else if (mode === 'upi+udhari') {
            splitRow.style.display = 'grid';
            splitRow.style.gridTemplateColumns = 'repeat(auto-fit, minmax(180px, 1fr))';
            splitRow.style.gap = '10px';
            const paid = (total > 0 ? (total * 0.5) : 0).toFixed(2);
            const rem = (total - paid).toFixed(2);
            splitRow.innerHTML = `
                <div>
                    <label style="font-size:11px; font-weight:700; color:#475569; display:block; margin-bottom:3px;">UPI Paid (₹)</label>
                    <input type="number" step="0.01" class="sale-input num-field cust-split-upi" placeholder="UPI Paid" value="${paid}" oninput="onCustomerUpiUdhariChange(this, ${total})">
                </div>
                <div style="display:flex; flex-direction:column; justify-content:center;">
                    <span style="font-size:11px; color:#475569; font-weight:700; margin-bottom:3px;">Remaining to Khata</span>
                    <span class="cust-udhari-pill" style="font-size:13px; font-weight:800; color:#DC2626; font-family:'JetBrains Mono', monospace;">Udhari: ₹${rem}</span>
                </div>
            `;
        } else if (mode === 'udhari') {
            splitRow.style.display = 'block';
            splitRow.innerHTML = `
                <div style="font-size:12px; color:#92400E; background:#FFFBEB; border:1px solid #FDE68A; border-radius:6px; padding:8px 12px; display:flex; align-items:center; gap:8px;">
                    <i data-lucide="info" style="width:16px;height:16px; color:#D97706; flex-shrink:0;"></i>
                    <span>Full invoice total (<strong>₹${total.toFixed(2)}</strong>) will be recorded as <strong>Udhari (Khata Debt)</strong> in customer ledger.</span>
                </div>
            `;
            if (window.refreshIcons) window.refreshIcons();
            else if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons();
        } else {
            splitRow.style.display = 'none';
            splitRow.innerHTML = '';
        }

        recalcGrandTotals();
    }

    function getCustomerCurrentTotal(card) {
        let total = 0;
        card.querySelectorAll('.cust-items-container .sale-item-row').forEach(r => {
            const qty = Math.max(1, parseInt(r.querySelector('.input-quantity')?.value || 1));
            const price = parseFloat(r.querySelector('.input-unit-price')?.value) || 0;
            total += (qty * price);
        });
        return total;
    }

    function syncCustomerSplitFields(card, mode, total) {
        if (mode === 'cash+upi' || mode === 'upi+cash') {
            const cashEl = card.querySelector('.cust-split-cash');
            const upiEl = card.querySelector('.cust-split-upi');
            if (cashEl && upiEl) {
                const cash = Math.min(total, parseFloat(cashEl.value) || 0);
                upiEl.value = Math.max(0, total - cash).toFixed(2);
            }
        } else if (mode === 'cash+udhari') {
            const cashEl = card.querySelector('.cust-split-cash');
            const pill = card.querySelector('.cust-udhari-pill');
            if (cashEl && pill) {
                const cash = Math.min(total, parseFloat(cashEl.value) || 0);
                pill.textContent = 'Udhari: ₹' + Math.max(0, total - cash).toFixed(2);
            }
        } else if (mode === 'upi+udhari') {
            const upiEl = card.querySelector('.cust-split-upi');
            const pill = card.querySelector('.cust-udhari-pill');
            if (upiEl && pill) {
                const upi = Math.min(total, parseFloat(upiEl.value) || 0);
                pill.textContent = 'Udhari: ₹' + Math.max(0, total - upi).toFixed(2);
            }
        }
    }

    function onCustomerCashSplitChange(el, total) {
        const card = el.closest('.customer-sale-card');
        const cash = Math.max(0, Math.min(total, parseFloat(el.value) || 0));
        const upiEl = card?.querySelector('.cust-split-upi');
        if (upiEl) upiEl.value = Math.max(0, total - cash).toFixed(2);
        recalcGrandTotals();
    }

    function onCustomerUpiSplitChange(el, total) {
        const card = el.closest('.customer-sale-card');
        const upi = Math.max(0, Math.min(total, parseFloat(el.value) || 0));
        const cashEl = card?.querySelector('.cust-split-cash');
        if (cashEl) cashEl.value = Math.max(0, total - upi).toFixed(2);
        recalcGrandTotals();
    }

    function onCustomerCashUdhariChange(el, total) {
        const card = el.closest('.customer-sale-card');
        const cash = Math.max(0, parseFloat(el.value) || 0);
        const pill = card?.querySelector('.cust-udhari-pill');
        if (pill) pill.textContent = 'Udhari: ₹' + Math.max(0, total - cash).toFixed(2);
        recalcGrandTotals();
    }

    function onCustomerUpiUdhariChange(el, total) {
        const card = el.closest('.customer-sale-card');
        const upi = Math.max(0, parseFloat(el.value) || 0);
        const pill = card?.querySelector('.cust-udhari-pill');
        if (pill) pill.textContent = 'Udhari: ₹' + Math.max(0, total - upi).toFixed(2);
        recalcGrandTotals();
    }

    /* ── Grand Totals Calculation ── */
    function recalcGrandTotals() {
        const container = document.getElementById('bulkSalesContainer');
        if (!container) return;

        let grandRevenue = 0;
        let totalItems = 0;
        let totalUnits = 0;
        let expCash = 0;
        let expUpi = 0;
        let expUdhari = 0;

        const cards = container.querySelectorAll('.customer-sale-card');

        cards.forEach(card => {
            const mode = card.querySelector('.cust-input-mode')?.value || 'cash';
            let custTotal = 0;

            card.querySelectorAll('.cust-items-container .sale-item-row').forEach(r => {
                const qty = Math.max(1, parseInt(r.querySelector('.input-quantity')?.value || 1));
                const price = parseFloat(r.querySelector('.input-unit-price')?.value) || 0;
                const line = qty * price;
                custTotal += line;
                totalUnits += qty;
                totalItems += 1;
            });

            grandRevenue += custTotal;

            if (mode === 'cash') {
                expCash += custTotal;
            } else if (mode === 'upi') {
                expUpi += custTotal;
            } else if (mode === 'udhari') {
                expUdhari += custTotal;
            } else if (mode === 'cash+upi' || mode === 'upi+cash') {
                const cVal = parseFloat(card.querySelector('.cust-split-cash')?.value) || (custTotal / 2);
                const uVal = parseFloat(card.querySelector('.cust-split-upi')?.value) || (custTotal - cVal);
                expCash += cVal;
                expUpi += uVal;
            } else if (mode === 'cash+udhari') {
                const cVal = Math.min(custTotal, parseFloat(card.querySelector('.cust-split-cash')?.value) || 0);
                expCash += cVal;
                expUdhari += Math.max(0, custTotal - cVal);
            } else if (mode === 'upi+udhari') {
                const uVal = Math.min(custTotal, parseFloat(card.querySelector('.cust-split-upi')?.value) || 0);
                expUpi += uVal;
                expUdhari += Math.max(0, custTotal - uVal);
            }
        });

        // Update DOM Labels
        const lblItems = document.getElementById('lblGrandItemsCount');
        const lblUnits = document.getElementById('lblGrandUnitsCount');
        const lblRev = document.getElementById('lblGrandTotalRevenue');
        const lblCash = document.getElementById('lblExpectedCash');
        const lblUpi = document.getElementById('lblExpectedUpi');
        const lblUdhari = document.getElementById('lblExpectedUdhari');

        if (lblItems) lblItems.textContent = `${totalItems} ${totalItems === 1 ? 'item' : 'items'}`;
        if (lblUnits) lblUnits.textContent = `${totalUnits} ${totalUnits === 1 ? 'unit' : 'units'}`;
        if (lblRev) lblRev.textContent = '₹' + grandRevenue.toFixed(2);
        if (lblCash) lblCash.textContent = '₹' + expCash.toFixed(2);
        if (lblUpi) lblUpi.textContent = '₹' + expUpi.toFixed(2);
        if (lblUdhari) lblUdhari.textContent = '₹' + expUdhari.toFixed(2);
    }

    /* ── Demo Sample Data ── */
    function loadSampleBulkSalesData() {
        const container = document.getElementById('bulkSalesContainer');
        container.innerHTML = '';

        const sample1Parts = allPartsData.slice(0, 2);
        const sample2Parts = allPartsData.slice(2, 4);

        addCustomerCard({
            customer_name: 'Rahul Sharma',
            customer_phone: '9876543210',
            payment_mode: 'cash+upi',
            items: sample1Parts.length > 0 ? sample1Parts.map(p => ({
                id: p.id,
                name: p.name,
                unit_price: parseFloat(p.selling_price || 250),
                quantity: 1,
                stock: p.stock_qty
            })) : [
                { name: '11D Matte Glass Samsung A14', unit_price: 150, quantity: 1, stock: 15 },
                { name: '20W Fast Charger Adapter Type-C', unit_price: 499, quantity: 1, stock: 8 }
            ]
        });

        addCustomerCard({
            customer_name: 'Amit Verma',
            customer_phone: '9123456780',
            payment_mode: 'cash+udhari',
            items: sample2Parts.length > 0 ? sample2Parts.map(p => ({
                id: p.id,
                name: p.name,
                unit_price: parseFloat(p.selling_price || 350),
                quantity: 2,
                stock: p.stock_qty
            })) : [
                { name: 'Smoke Silicone Shockproof Cover iPhone 15', unit_price: 299, quantity: 1, stock: 12 }
            ]
        });

        recalcGrandTotals();
    }

    /* ── Batch Submission via API ── */
    async function submitAllBulkSales() {
        const alertBox = document.getElementById('bulkSaleAlertBox');
        if (alertBox) alertBox.style.display = 'none';

        const btn = document.getElementById('btnProcessAllSales');
        const container = document.getElementById('bulkSalesContainer');
        const cards = container ? container.querySelectorAll('.customer-sale-card') : [];

        if (cards.length === 0) {
            showPageWarning('Please add at least one customer sale entry.');
            return;
        }

        const salesPayload = [];

        for (let cIdx = 0; cIdx < cards.length; cIdx++) {
            const card = cards[cIdx];
            const nameInput = card.querySelector('.cust-input-name');
            const phoneInput = card.querySelector('.cust-input-phone');
            const name = (nameInput?.value || '').trim() || 'Walk-in Customer';
            const phone = (phoneInput?.value || '').trim() || '9999999999';
            const mode = card.querySelector('.cust-input-mode')?.value || 'cash';
            const cashAmount = parseFloat(card.querySelector('.cust-split-cash')?.value) || 0;
            const upiAmount = parseFloat(card.querySelector('.cust-split-upi')?.value) || 0;

            const itemRows = card.querySelectorAll('.cust-items-container .sale-item-row');
            if (itemRows.length === 0) {
                showPageWarning(`Customer #${cIdx + 1} (${name}): Please add at least one item.`, card);
                return;
            }

            const items = [];
            let custTotal = 0;

            for (let rIdx = 0; rIdx < itemRows.length; rIdx++) {
                const r = itemRows[rIdx];
                const pId = parseInt(r.querySelector('.input-part-id')?.value || 0);
                const pNameInput = r.querySelector('.input-item-name');
                const pName = (pNameInput?.value || '').trim();
                const qtyInput = r.querySelector('.input-quantity');
                const qty = parseInt(qtyInput?.value || 0);
                const priceInput = r.querySelector('.input-unit-price');
                const price = parseFloat(priceInput?.value);

                if (!pName) {
                    showPageWarning(`Customer #${cIdx + 1} (${name}), Item #${rIdx + 1}: Please enter or search an item name.`, card, pNameInput);
                    return;
                }
                if (isNaN(qty) || qty < 1) {
                    showPageWarning(`Customer #${cIdx + 1} (${name}), Item #${rIdx + 1} (${pName}): Quantity must be at least 1.`, card, qtyInput);
                    return;
                }
                if (isNaN(price) || price < 0) {
                    showPageWarning(`Customer #${cIdx + 1} (${name}), Item #${rIdx + 1} (${pName}): Rate must be a valid non-negative number.`, card, priceInput);
                    return;
                }

                items.push({
                    part_id: pId,
                    part_name: pName,
                    quantity: qty,
                    unit_price: price,
                });
                custTotal += (qty * price);
            }

            if (items.length === 0) {
                showPageWarning(`Customer #${cIdx + 1} (${name}): Please add at least one valid item.`, card);
                return;
            }
            if (custTotal <= 0) {
                showPageWarning(`Customer #${cIdx + 1} (${name}): Total sale amount must be greater than zero.`, card);
                return;
            }

            // Split validation per customer
            if (mode === 'cash+upi' || mode === 'upi+cash') {
                const cashInput = card.querySelector('.cust-split-cash');
                const upiInput = card.querySelector('.cust-split-upi');
                if (cashAmount < 0 || upiAmount < 0) {
                    showPageWarning(`Customer #${cIdx + 1} (${name}): Split amounts cannot be negative.`, card, cashAmount < 0 ? cashInput : upiInput);
                    return;
                }
                if (Math.abs((cashAmount + upiAmount) - custTotal) > 0.05) {
                    showPageWarning(`Customer #${cIdx + 1} (${name}): Split Cash (₹${cashAmount.toFixed(2)}) + UPI (₹${upiAmount.toFixed(2)}) total ₹${(cashAmount + upiAmount).toFixed(2)}, which does not match total amount ₹${custTotal.toFixed(2)}. Please balance.`, card, cashInput);
                    return;
                }
            } else if (mode === 'cash+udhari') {
                const cashInput = card.querySelector('.cust-split-cash');
                if (cashAmount < 0) {
                    showPageWarning(`Customer #${cIdx + 1} (${name}): Cash paid cannot be negative.`, card, cashInput);
                    return;
                }
                if (cashAmount > custTotal) {
                    showPageWarning(`Customer #${cIdx + 1} (${name}): Cash paid (₹${cashAmount.toFixed(2)}) cannot exceed total bill (₹${custTotal.toFixed(2)}).`, card, cashInput);
                    return;
                }
            } else if (mode === 'upi+udhari') {
                const upiInput = card.querySelector('.cust-split-upi');
                if (upiAmount < 0) {
                    showPageWarning(`Customer #${cIdx + 1} (${name}): UPI paid cannot be negative.`, card, upiInput);
                    return;
                }
                if (upiAmount > custTotal) {
                    showPageWarning(`Customer #${cIdx + 1} (${name}): UPI paid (₹${upiAmount.toFixed(2)}) cannot exceed total bill (₹${custTotal.toFixed(2)}).`, card, upiInput);
                    return;
                }
            }

            salesPayload.push({
                customer_name: name,
                customer_phone: phone,
                payment_mode: mode,
                cash_amount: cashAmount,
                upi_amount: upiAmount,
                items: items,
            });
        }

        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processing Batch Sales...';
        }

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                              document.querySelector('input[name="_token"]')?.value;

            const response = await fetch("{{ route('mobileshop.sales.bulk_store', ['company_id' => company_id()]) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ sales: salesPayload })
            });

            const res = await response.json();

            if (res.success) {
                alert(res.message || 'Bulk sales recorded successfully!');
                window.location.href = "{{ route('mobileshop.sales') }}";
            } else {
                showPageWarning(res.message || 'Error occurred while processing bulk sales.');
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i data-lucide="check-check" style="width:16px;height:16px;"></i> Process All Sales &amp; Generate Invoices';
                    if (window.refreshIcons) window.refreshIcons();
                }
            }
        } catch (err) {
            showPageWarning('Request failed or network connection error: ' + (err.message || 'Unknown error'));
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i data-lucide="check-check" style="width:16px;height:16px;"></i> Process All Sales &amp; Generate Invoices';
                if (window.refreshIcons) window.refreshIcons();
            }
        }
    }

    // Initialize first customer card on page load
    document.addEventListener('DOMContentLoaded', function() {
        addCustomerCard();
    });
</script>
@endpush
