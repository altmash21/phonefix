@extends('mobileshop.layout')

@section('title', 'Day-End Bulk Sales Register — PhoneFix Azamgarh')
@section('page-title', 'Day-End Bulk Sales Register')

@section('page-actions')
    <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
        <button type="button" onclick="addCustomerSaleRow()" class="btn btn-outline btn-sm" style="font-weight:700; border:1px solid #CBD5E1; color:#334155; padding:6px 14px; border-radius:6px; display:inline-flex; align-items:center; gap:6px;">
            <i data-lucide="plus" style="width:13px;height:13px;"></i> + Add Sale Row
        </button>
        <button type="button" onclick="submitDaySales()" class="btn btn-primary btn-sm" style="background:#4F46E5; border-color:#4338CA; color:#ffffff; font-weight:700; padding:6px 16px; border-radius:6px; display:inline-flex; align-items:center; gap:6px; box-shadow:0 1px 3px rgba(79,70,229,0.3);">
            <i data-lucide="check-circle" style="width:13px;height:13px;"></i> Save All Day Sales
        </button>
        <a href="{{ route('mobileshop.sales') }}" class="btn btn-outline btn-sm" style="border:1px solid #CBD5E1; color:#334155; font-weight:700; padding:6px 14px; border-radius:6px; display:inline-flex; align-items:center; gap:6px;">
            <i data-lucide="arrow-left" style="width:13px;height:13px;"></i> Back to Sales
        </a>
    </div>
@endsection

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap');

    .day-end-container {
        max-width: 1480px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 14px;
        font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
    }

    /* ── Hide browser number spinners ── */
    .num-no-spin::-webkit-outer-spin-button,
    .num-no-spin::-webkit-inner-spin-button { -webkit-appearance: none !important; margin: 0 !important; }
    .num-no-spin { -moz-appearance: textfield !important; }

    /* ── High-Contrast Form Inputs ── */
    .day-input {
        width: 100%;
        color: #0F172A !important;
        font-weight: 600 !important;
        background: #FFFFFF !important;
        border: 1px solid #CBD5E1 !important;
        border-radius: 6px !important;
        padding: 6px 10px !important;
        font-size: 13px !important;
        font-family: 'Plus Jakarta Sans', system-ui, sans-serif !important;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
        box-sizing: border-box;
    }
    .day-input:focus {
        outline: none !important;
        border-color: #6366F1 !important;
        box-shadow: 0 0 0 2.5px rgba(99, 102, 241, 0.15) !important;
        background: #FFFFFF !important;
    }
    .day-input.num-field {
        font-family: 'JetBrains Mono', -apple-system, BlinkMacSystemFont, monospace !important;
        font-weight: 700 !important;
        font-variant-numeric: tabular-nums;
    }

    /* ── Table Grid Layout ── */
    .day-sales-table-wrap {
        width: 100%;
        overflow-x: auto;
    }
    .day-sales-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        min-width: 1100px;
        table-layout: fixed;
    }
    .day-sales-table th {
        background: #F8FAFC;
        border-top: 1px solid #E2E8F0;
        border-bottom: 2px solid #E2E8F0;
        color: #475569;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        padding: 10px 10px;
        box-sizing: border-box;
    }
    .day-sales-table td {
        padding: 8px 8px;
        border-bottom: 1px solid #E2E8F0;
        vertical-align: middle;
        background: #FFFFFF;
        box-sizing: border-box;
    }
    .day-sales-table tr.day-customer-row:hover td {
        background: #F8FAFC;
    }
    .day-sales-table tr.day-customer-subitem-row td {
        background: #FBFBFE;
        border-bottom: 1px dashed #E2E8F0;
    }
    .day-sales-table tr.day-customer-subitem-row:hover td {
        background: #F5F5FD !important;
    }

    /* ── Autocomplete Search Dropdown ── */
    .item-search-dropdown {
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        background: #FFFFFF;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        box-shadow: 0 12px 28px -4px rgba(15, 23, 42, 0.18);
        max-height: 240px;
        overflow-y: auto;
        z-index: 60;
        display: none;
    }
    .item-search-item {
        padding: 8px 12px;
        cursor: pointer;
        border-bottom: 1px solid #F1F5F9;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: background 0.1s ease;
    }
    .item-search-item:hover {
        background: #EEF2FF;
    }

    /* ── Split Payment Box ── */
    .split-payment-box {
        margin-top: 6px;
        border-radius: 6px;
        padding: 6px 8px;
        display: flex;
        flex-direction: column;
        gap: 5px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    }
    .split-payment-box.theme-cash-upi {
        background: #F8FAFC;
        border: 1px solid #CBD5E1;
    }
    .split-payment-box.theme-udhari {
        background: #FFF1F2;
        border: 1px solid #FECDD3;
    }

    /* ── Action Buttons ── */
    .action-btn-group {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        justify-content: center;
    }
    .btn-action-add {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        background: #EEF2FF;
        border: 1px solid #C7D2FE;
        color: #4F46E5;
        cursor: pointer;
        transition: all 0.15s ease;
        padding: 0;
    }
    .btn-action-add:hover {
        background: #4F46E5 !important;
        border-color: #4338CA !important;
        color: #FFFFFF !important;
        box-shadow: 0 2px 6px rgba(79, 70, 229, 0.35);
    }
    .btn-action-del {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        background: #FFF1F2;
        border: 1px solid #FECDD3;
        color: #E11D48;
        cursor: pointer;
        transition: all 0.15s ease;
        padding: 0;
    }
    .btn-action-del:hover {
        background: #E11D48 !important;
        border-color: #BE123C !important;
        color: #FFFFFF !important;
        box-shadow: 0 2px 6px rgba(225, 29, 72, 0.35);
    }
    .btn-remove-subitem {
        width: 28px;
        height: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        background: #FFFFFF;
        border: 1px solid #FECACA;
        color: #EF4444;
        cursor: pointer;
        transition: all 0.15s ease;
        padding: 0;
    }
    .btn-remove-subitem:hover {
        background: #EF4444 !important;
        color: #FFFFFF !important;
    }
    .btn-add-item-chip {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 11px;
        font-weight: 700;
        color: #4F46E5;
        background: #EEF2FF;
        border: 1px dashed #A5B4FC;
        border-radius: 4px;
        padding: 2px 7px;
        cursor: pointer;
        transition: all 0.15s ease;
        line-height: 1.2;
    }
    .btn-add-item-chip:hover {
        background: #4F46E5;
        color: #FFFFFF;
        border-style: solid;
    }
    .badge-subitem {
        font-size: 11px;
        font-weight: 700;
        color: #4F46E5;
        background: #EEF2FF;
        border: 1px solid #E0E7FF;
        padding: 2px 7px;
        border-radius: 4px;
        display: inline-block;
    }
</style>

<div class="day-end-container">

    <!-- ─── TOP NOTIFICATION / ALERT BOX ─── -->
    <div id="daySaleAlertBox" style="display:none; background:#FEF2F2; border:1px solid #FECDD3; border-radius:8px; padding:12px 16px; color:#991B1B; font-size:13px; font-weight:600; box-shadow:0 1px 3px rgba(0,0,0,0.04);"></div>

    <!-- ─── INSTRUCTION BANNER (Matches User Screenshot) ─── -->
    <div style="background:#F0FDF4; border:1px solid #BBF7D0; border-radius:8px; padding:12px 18px;">
        <div style="display:flex; align-items:center; gap:8px;">
            <i data-lucide="book-open" style="width:16px;height:16px;color:#16A34A;flex-shrink:0;"></i>
            <span style="font-size:13px; font-weight:700; color:#15803D;">Shop End-of-Day Quick Register</span>
            <span style="font-size:13px; color:#166534; font-weight:500;">— Write down sales throughout the day on your paper register, then enter all items, different customers, and payment methods in one go here!</span>
        </div>
        <div style="margin-top:6px; display:flex; align-items:center; gap:6px; font-size:12px; color:#166534; padding-left:24px;">
            <span>⚡ <strong style="color:#15803D;">Quick Shortcut:</strong></span>
            <kbd style="background:#E2E8F0; border:1px solid #CBD5E1; border-radius:4px; padding:1px 6px; font-family:monospace; font-size:11px; font-weight:800; color:#334155;">Ctrl</kbd>
            <span>+</span>
            <kbd style="background:#E2E8F0; border:1px solid #CBD5E1; border-radius:4px; padding:1px 6px; font-family:monospace; font-size:11px; font-weight:800; color:#334155;">Enter</kbd>
            <span>to add new customer sale row</span>
        </div>
    </div>

    <!-- ─── MASTER CONTROLS BAR (4 Columns) ─── -->
    <div class="card" style="border:1px solid #E2E8F0; background:#FFFFFF; border-radius:8px; padding:14px 18px;">
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap:16px;">
            <!-- Sale Date -->
            <div>
                <label for="masterSaleDate" style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:5px;">
                    Sale Date (For All Entries)
                </label>
                <input type="date" id="masterSaleDate" value="{{ date('Y-m-d') }}" class="day-input" style="font-weight:700;">
            </div>

            <!-- Sold By Staff Member -->
            <div>
                <label for="masterStaffMember" style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:5px;">
                    Sold By Staff Member
                </label>
                <select id="masterStaffMember" class="day-input" style="font-weight:700; cursor:pointer;">
                    @foreach($staffUsers as $staff)
                        <option value="{{ $staff->id }}" {{ auth()->id() == $staff->id ? 'selected' : '' }}>
                            {{ $staff->name }}
                        </option>
                    @endforeach
                    @if($staffUsers->isEmpty())
                        <option value="{{ auth()->id() ?? 1 }}">{{ auth()->user()->name ?? 'Altmash' }}</option>
                    @endif
                </select>
            </div>

            <!-- Default Bill Type -->
            <div>
                <label for="masterBillType" style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:5px;">
                    Default Bill Type
                </label>
                <select id="masterBillType" class="day-input" style="font-weight:700; cursor:pointer;">
                    <option value="gst_18" selected>GST 18% Invoice</option>
                    <option value="non_gst">Non-GST / Retail Estimate</option>
                    <option value="bill_supply">Bill of Supply (Composition)</option>
                </select>
            </div>

            <!-- Auto-Set Default Payment -->
            <div>
                <label for="masterDefaultPayment" style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:5px;">
                    Auto-Set Default Payment
                </label>
                <select id="masterDefaultPayment" onchange="applyDefaultPaymentToAllRows()" class="day-input" style="font-weight:700; cursor:pointer;">
                    <option value="cash" selected>💵 Cash (Default for new rows)</option>
                    <option value="upi">📱 UPI / QR</option>
                    <option value="cash+upi">💵+📱 Cash + UPI</option>
                    <option value="cash+udhari">💵+📝 Cash + Udhari</option>
                    <option value="upi+udhari">📱+📝 UPI + Udhari</option>
                    <option value="emi">⚡ EMI Financed</option>
                    <option value="udhari">📝 Customer Khata (Udhari)</option>
                </select>
            </div>
        </div>
    </div>

    <!-- ─── DAY SALES ROWS CARD (Grid Table) ─── -->
    <div class="card" style="border:1px solid #E2E8F0; background:#FFFFFF; border-radius:10px; overflow:visible;">
        <!-- Card Header -->
        <div style="padding:12px 18px; border-bottom:1px solid #E2E8F0; display:flex; justify-content:space-between; align-items:center;">
            <div style="display:flex; align-items:center; gap:8px;">
                <i data-lucide="layers" style="width:18px;height:18px;color:#4F46E5;"></i>
                <h3 style="font-size:16px; font-weight:800; color:#0F172A; margin:0;">Day Sales Rows</h3>
                <span id="lblRowsCountBadge" style="background:#E0E7FF; color:#3730A3; font-size:11px; font-weight:800; padding:2px 10px; border-radius:9999px;">
                    1 Sale
                </span>
            </div>
            <button type="button" onclick="addCustomerSaleRow()" class="btn btn-primary btn-sm" style="background:#4F46E5; border-color:#4338CA; color:#ffffff; font-weight:700; padding:6px 14px; border-radius:6px; display:inline-flex; align-items:center; gap:6px;">
                <i data-lucide="plus" style="width:13px;height:13px;"></i> Add Sale Row
            </button>
        </div>

        <!-- Table Container -->
        <div class="day-sales-table-wrap">
            <table class="day-sales-table" id="daySalesTable">
                <colgroup>
                    <col style="width:42px;">
                    <col style="width:220px;">
                    <col style="width:auto;">
                    <col style="width:75px;">
                    <col style="width:115px;">
                    <col style="width:105px;">
                    <col style="width:190px;">
                    <col style="width:85px;">
                </colgroup>
                <thead>
                    <tr>
                        <th style="text-align:center;">#</th>
                        <th>CUSTOMER (OPTIONAL FOR PAID)</th>
                        <th>ITEM / PRODUCT (SEARCH DROPDOWN)</th>
                        <th style="text-align:center;">QTY</th>
                        <th style="text-align:right;">UNIT PRICE (₹)</th>
                        <th style="text-align:right;">TOTAL (₹)</th>
                        <th>PAYMENT METHOD</th>
                        <th style="text-align:center;">ACTION</th>
                    </tr>
                </thead>
                <tbody id="daySalesRowsTbody">
                    <!-- Populated dynamically via JS -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- ─── BOTTOM STICKY SUMMARY & SAVE CARD (Matches Screenshot) ─── -->
    <div class="card" style="border:1px solid #E2E8F0; background:#FFFFFF; border-radius:10px; box-shadow:0 2px 6px rgba(0,0,0,0.04); margin-bottom:28px;">
        <div class="card-body" style="padding:14px 18px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px;">
            
            <!-- Metrics Strip -->
            <div style="display:flex; align-items:center; gap:20px; flex-wrap:wrap;">
                <div>
                    <span style="font-size:10px; color:#64748B; text-transform:uppercase; font-weight:700; letter-spacing:0.4px;">TOTAL BILLS</span>
                    <div id="lblFooterTotalBills" style="font-size:16px; font-weight:800; color:#0F172A;">0</div>
                </div>

                <div style="border-left:1px solid #E2E8F0; padding-left:20px;">
                    <span style="font-size:10px; color:#64748B; text-transform:uppercase; font-weight:700; letter-spacing:0.4px;">PHONES / ACC</span>
                    <div id="lblFooterPhonesAcc" style="font-size:13px; font-weight:700; color:#0F172A;">0 Phones / 0 Acc</div>
                </div>

                <div style="border-left:1px solid #E2E8F0; padding-left:20px;">
                    <span style="font-size:10px; color:#64748B; text-transform:uppercase; font-weight:700; letter-spacing:0.4px;">TOTAL AMOUNT</span>
                    <div id="lblFooterTotalAmount" style="font-size:20px; font-weight:900; color:#16A34A; font-family:'JetBrains Mono', monospace;">₹0.00</div>
                </div>

                <div style="border-left:1px solid #E2E8F0; padding-left:20px;">
                    <span style="font-size:10px; color:#15803D; text-transform:uppercase; font-weight:700; letter-spacing:0.4px;">💵 CASH</span>
                    <div id="lblFooterCash" style="font-size:14px; font-weight:800; color:#16A34A; font-family:'JetBrains Mono', monospace;">₹0</div>
                </div>

                <div style="border-left:1px solid #E2E8F0; padding-left:20px;">
                    <span style="font-size:10px; color:#1D4ED8; text-transform:uppercase; font-weight:700; letter-spacing:0.4px;">📱 UPI</span>
                    <div id="lblFooterUpi" style="font-size:14px; font-weight:800; color:#2563EB; font-family:'JetBrains Mono', monospace;">₹0</div>
                </div>

                <div style="border-left:1px solid #E2E8F0; padding-left:20px;">
                    <span style="font-size:10px; color:#B45309; text-transform:uppercase; font-weight:700; letter-spacing:0.4px;">⚡ EMI FINANCED</span>
                    <div id="lblFooterEmi" style="font-size:14px; font-weight:800; color:#D97706; font-family:'JetBrains Mono', monospace;">₹0</div>
                </div>

                <div style="border-left:1px solid #E2E8F0; padding-left:20px;">
                    <span style="font-size:10px; color:#B91C1C; text-transform:uppercase; font-weight:700; letter-spacing:0.4px;">📝 CUSTOMER KHATA</span>
                    <div id="lblFooterKhata" style="font-size:14px; font-weight:800; color:#DC2626; font-family:'JetBrains Mono', monospace;">₹0</div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div style="display:flex; align-items:center; gap:10px;">
                <button type="button" onclick="addCustomerSaleRow()" class="btn btn-outline" style="font-weight:700; padding:8px 16px; border-radius:6px; font-size:13px; border-color:#CBD5E1; color:#334155; display:inline-flex; align-items:center; gap:6px;">
                    <i data-lucide="plus" style="width:14px;height:14px;"></i> Add Row
                </button>
                <button type="button" onclick="submitDaySales()" id="btnSaveAllDaySales" class="btn btn-primary" style="background:#4F46E5; border-color:#4338CA; color:#ffffff; font-weight:800; padding:8px 24px; font-size:13px; border-radius:6px; box-shadow:0 3px 8px rgba(79, 70, 229, 0.3); display:inline-flex; align-items:center; gap:8px;">
                    <i data-lucide="check-check" style="width:16px;height:16px;"></i> Post &amp; Save All Day Sales
                </button>
            </div>

        </div>
    </div>

</div>

<!-- Customer Autocomplete Datalist -->
<datalist id="registeredCustomersDatalist">
    @foreach($customers as $c)
        <option value="{{ $c->name }}" data-phone="{{ $c->phone }}">{{ $c->name }} ({{ $c->phone }})</option>
    @endforeach
</datalist>

<datalist id="registeredPhonesDatalist">
    @foreach($customers as $c)
        <option value="{{ $c->phone }}" data-name="{{ $c->name }}">{{ $c->phone }} — {{ $c->name }}</option>
    @endforeach
</datalist>

@endsection

@push('scripts')
<script>
    const allPartsData = @json($parts ?? []);
    const allPhonesData = @json($phones ?? []);
    const customersData = @json($customers ?? []);
    let customerCardCounter = 0;
    let globalItemCounter = 0;

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function showPageWarning(msg, targetInput = null) {
        const alertBox = document.getElementById('daySaleAlertBox');
        if (alertBox) {
            alertBox.innerHTML = `
                <div style="display:flex; align-items:center; gap:8px; justify-content:space-between; width:100%;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <span style="font-size:16px;">⚠️</span>
                        <span>${escapeHtml(msg)}</span>
                    </div>
                    <button type="button" onclick="this.closest('#daySaleAlertBox').style.display='none'" style="background:none;border:none;color:#991B1B;font-weight:800;font-size:14px;cursor:pointer;">✕</button>
                </div>
            `;
            alertBox.style.display = 'block';
            alertBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } else {
            alert(msg);
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

    /* ── Add Customer Sale Row ── */
    function addCustomerSaleRow() {
        const tbody = document.getElementById('daySalesRowsTbody');
        if (!tbody) return;

        customerCardCounter++;
        const cIdx = customerCardCounter;
        globalItemCounter++;
        const itIdx = globalItemCounter;

        const tr = document.createElement('tr');
        tr.className = 'day-customer-row';
        tr.dataset.customerIndex = cIdx;
        tr.id = `dayCustRow_${cIdx}`;

        const defaultPayment = document.getElementById('masterDefaultPayment')?.value || 'cash';

        tr.innerHTML = `
            <!-- 1: # -->
            <td style="text-align:center; font-weight:700; color:#64748B; font-size:13px;" class="row-num-cell">
                ${document.querySelectorAll('#daySalesRowsTbody .day-customer-row').length + 1}
            </td>

            <!-- 2: CUSTOMER -->
            <td>
                <div style="display:flex; flex-direction:column; gap:4px;">
                    <input type="text" class="day-input cust-name-input" list="registeredCustomersDatalist"
                           placeholder="Customer Name (Optional)"
                           style="font-size:12px !important; height:30px;"
                           oninput="onCustNameInput(${cIdx}, this)">
                    <input type="tel" class="day-input cust-phone-input num-field" list="registeredPhonesDatalist"
                           placeholder="Mobile (Optional)"
                           style="font-size:12px !important; height:30px;"
                           oninput="onCustPhoneInput(${cIdx}, this)">
                    <div class="cust-khata-badge" style="display:none; font-size:10.5px; font-weight:700; color:#DC2626; background:#FEF2F2; padding:2px 6px; border-radius:4px;"></div>
                </div>
            </td>

            <!-- 3: ITEM / PRODUCT -->
            <td>
                <div style="position:relative;">
                    <input type="text" class="day-input item-search-input"
                           placeholder="Search Phone or Accessory..."
                           style="height:34px; font-weight:600; font-size:13px;"
                           autocomplete="off"
                           onfocus="onItemInputFocus(${itIdx}, this)"
                           oninput="onItemInputSearch(${itIdx}, this)">
                    <input type="hidden" class="item-part-id" value="0">
                    <input type="hidden" class="item-device-id" value="0">
                    <input type="hidden" class="item-type" value="accessory">
                    <div class="item-search-dropdown" id="itemSearchDropdown_${itIdx}"></div>
                </div>
                <div style="margin-top:4px;">
                    <button type="button" onclick="addItemToCustomer(${cIdx})" class="btn-add-item-chip" title="Add another item for this customer">
                        <i data-lucide="plus" style="width:11px;height:11px;"></i> Add item
                    </button>
                </div>
            </td>

            <!-- 4: QTY -->
            <td style="text-align:center;">
                <input type="number" min="1" value="1" class="day-input num-field num-no-spin item-qty-input"
                       style="text-align:center; height:34px; font-size:13px;"
                       oninput="recalcCustomerTotal(${cIdx})">
            </td>

            <!-- 5: UNIT PRICE -->
            <td style="text-align:right;">
                <input type="number" min="0" step="0.01" value="0.00" class="day-input num-field item-price-input"
                       style="text-align:right; height:34px; font-size:13px;"
                       placeholder="0.00"
                       oninput="recalcCustomerTotal(${cIdx})">
            </td>

            <!-- 6: TOTAL -->
            <td style="text-align:right;">
                <div class="cust-bill-total" style="font-size:15px; font-weight:800; color:#0F172A;">
                    ₹0.00
                </div>
                <div class="cust-items-count-label" style="font-size:11px; color:#64748B; margin-top:2px;">
                    1 item
                </div>
            </td>

            <!-- 7: PAYMENT METHOD -->
            <td>
                <select class="day-input cust-payment-select" style="height:34px; font-weight:700; cursor:pointer;" onchange="onPaymentModeChange(${cIdx}, this)">
                    <option value="cash" ${defaultPayment === 'cash' ? 'selected' : ''}>💵 Cash</option>
                    <option value="upi" ${defaultPayment === 'upi' ? 'selected' : ''}>📱 UPI</option>
                    <option value="cash+upi" ${defaultPayment === 'cash+upi' ? 'selected' : ''}>💵+📱 Cash + UPI</option>
                    <option value="cash+udhari" ${defaultPayment === 'cash+udhari' ? 'selected' : ''}>💵+📝 Cash + Udhari</option>
                    <option value="upi+udhari" ${defaultPayment === 'upi+udhari' ? 'selected' : ''}>📱+📝 UPI + Udhari</option>
                    <option value="emi" ${defaultPayment === 'emi' ? 'selected' : ''}>⚡ EMI Financed</option>
                    <option value="udhari" ${defaultPayment === 'udhari' ? 'selected' : ''}>📝 Customer Khata</option>
                </select>
                <div class="split-payment-box" id="splitBox_${cIdx}" style="display:none;"></div>
            </td>

            <!-- 8: ACTION -->
            <td style="text-align:center; vertical-align:middle;">
                <div class="action-btn-group">
                    <button type="button" class="btn-action-add" onclick="addCustomerSaleRow()" title="Add New Sale Row (+)">
                        <i data-lucide="plus" style="width:14px;height:14px;"></i>
                    </button>
                    <button type="button" class="btn-action-del" onclick="removeCustomerRow(${cIdx})" title="Delete This Sale">
                        <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                    </button>
                </div>
            </td>
        `;

        tbody.appendChild(tr);

        // Render split box if default was split
        onPaymentModeChange(cIdx, tr.querySelector('.cust-payment-select'));

        renumberCustomerRows();
        recalcAllSummaryMetrics();

        if (window.lucide) {
            window.lucide.createIcons();
        }
    }

    /* ── Add Extra Item Line to a Customer ── */
    function addItemToCustomer(cIdx) {
        const parentRow = document.getElementById(`dayCustRow_${cIdx}`);
        if (!parentRow) return;

        globalItemCounter++;
        const itIdx = globalItemCounter;

        // Find insertion point: after the last existing subitem row for this customer, or after parentRow
        const existingSubitems = document.querySelectorAll(`.day-customer-subitem-row[data-parent-customer="${cIdx}"]`);
        const insertAfterEl = existingSubitems.length > 0 ? existingSubitems[existingSubitems.length - 1] : parentRow;
        const itemNumber = existingSubitems.length + 2;

        const subTr = document.createElement('tr');
        subTr.className = 'day-customer-subitem-row';
        subTr.dataset.parentCustomer = cIdx;
        subTr.id = `subItemRow_${itIdx}`;

        subTr.innerHTML = `
            <td></td>
            <td style="text-align:right; vertical-align:middle; padding-right:10px;">
                <span class="badge-subitem">↳ Item #${itemNumber}</span>
            </td>
            <td>
                <div style="position:relative;">
                    <input type="text" class="day-input item-search-input"
                           placeholder="Search another item..."
                           style="height:34px; font-weight:600; font-size:13px;"
                           autocomplete="off"
                           onfocus="onItemInputFocus(${itIdx}, this)"
                           oninput="onItemInputSearch(${itIdx}, this)">
                    <input type="hidden" class="item-part-id" value="0">
                    <input type="hidden" class="item-device-id" value="0">
                    <input type="hidden" class="item-type" value="accessory">
                    <div class="item-search-dropdown" id="itemSearchDropdown_${itIdx}"></div>
                </div>
            </td>
            <td style="text-align:center;">
                <input type="number" min="1" value="1" class="day-input num-field num-no-spin item-qty-input"
                       style="text-align:center; height:34px; font-size:13px;"
                       oninput="recalcCustomerTotal(${cIdx})">
            </td>
            <td style="text-align:right;">
                <input type="number" min="0" step="0.01" value="0.00" class="day-input num-field item-price-input"
                       style="text-align:right; height:34px; font-size:13px;"
                       placeholder="0.00"
                       oninput="recalcCustomerTotal(${cIdx})">
            </td>
            <td style="text-align:right; vertical-align:middle;">
                <div class="subitem-line-total" style="font-size:13px; font-weight:700; color:#475569;">₹0.00</div>
            </td>
            <td style="text-align:center; vertical-align:middle; color:#94A3B8; font-size:12px;">
                —
            </td>
            <td style="text-align:center; vertical-align:middle;">
                <button type="button" class="btn-remove-subitem" onclick="removeSubitem(${cIdx}, ${itIdx})" title="Remove this item">
                    <i data-lucide="x" style="width:13px;height:13px;"></i>
                </button>
            </td>
        `;

        insertAfterEl.after(subTr);
        recalcCustomerTotal(cIdx);
        if (window.lucide) window.lucide.createIcons();
    }

    /* ── Remove Extra Item Line ── */
    function removeSubitem(cIdx, itIdx) {
        const subRow = document.getElementById(`subItemRow_${itIdx}`);
        if (subRow) {
            subRow.remove();
            const subitems = document.querySelectorAll(`.day-customer-subitem-row[data-parent-customer="${cIdx}"]`);
            subitems.forEach((sr, idx) => {
                const badge = sr.querySelector('.badge-subitem');
                if (badge) badge.innerText = `↳ Item #${idx + 2}`;
            });
            recalcCustomerTotal(cIdx);
        }
    }

    /* ── Renumber Customer Rows ── */
    function renumberCustomerRows() {
        const rows = document.querySelectorAll('#daySalesRowsTbody .day-customer-row');
        rows.forEach((r, idx) => {
            const numCell = r.querySelector('.row-num-cell');
            if (numCell) numCell.innerText = idx + 1;
        });
        const badge = document.getElementById('lblRowsCountBadge');
        if (badge) {
            badge.innerText = `${rows.length} ${rows.length === 1 ? 'Sale' : 'Sales'}`;
        }
    }

    /* ── Remove Customer Row ── */
    function removeCustomerRow(cIdx) {
        const row = document.getElementById(`dayCustRow_${cIdx}`);
        if (!row) return;

        const tbody = document.getElementById('daySalesRowsTbody');
        const allCustRows = tbody.querySelectorAll('.day-customer-row');
        if (allCustRows.length <= 1) {
            // Keep at least 1 clean customer row
            row.querySelector('.cust-name-input').value = '';
            row.querySelector('.cust-phone-input').value = '';
            row.querySelector('.item-search-input').value = '';
            row.querySelector('.item-part-id').value = 0;
            row.querySelector('.item-device-id').value = 0;
            row.querySelector('.item-type').value = 'accessory';
            row.querySelector('.item-qty-input').value = 1;
            row.querySelector('.item-price-input').value = '0.00';
            document.querySelectorAll(`.day-customer-subitem-row[data-parent-customer="${cIdx}"]`).forEach(sr => sr.remove());
            recalcCustomerTotal(cIdx);
            return;
        }

        document.querySelectorAll(`.day-customer-subitem-row[data-parent-customer="${cIdx}"]`).forEach(sr => sr.remove());
        row.remove();
        renumberCustomerRows();
        recalcAllSummaryMetrics();
    }

    /* ── Recalculate Customer Total ── */
    function recalcCustomerTotal(cIdx) {
        const row = document.getElementById(`dayCustRow_${cIdx}`);
        if (!row) return;

        let custTotal = 0;
        let count = 0;

        // Primary row item
        const pQty = parseInt(row.querySelector('.item-qty-input')?.value || 0);
        const pPrice = parseFloat(row.querySelector('.item-price-input')?.value || 0);
        const pLineTotal = (pQty > 0 && pPrice >= 0) ? (pQty * pPrice) : 0;
        custTotal += pLineTotal;
        count++;

        // Child subitem rows
        const subRows = document.querySelectorAll(`.day-customer-subitem-row[data-parent-customer="${cIdx}"]`);
        subRows.forEach(sr => {
            const sQty = parseInt(sr.querySelector('.item-qty-input')?.value || 0);
            const sPrice = parseFloat(sr.querySelector('.item-price-input')?.value || 0);
            const sLineTotal = (sQty > 0 && sPrice >= 0) ? (sQty * sPrice) : 0;
            const subtotalLbl = sr.querySelector('.subitem-line-total');
            if (subtotalLbl) subtotalLbl.innerText = `₹${sLineTotal.toFixed(2)}`;
            custTotal += sLineTotal;
            count++;
        });

        const totalDisplay = row.querySelector('.cust-bill-total');
        if (totalDisplay) {
            totalDisplay.innerText = `₹${custTotal.toFixed(2)}`;
        }

        const countDisplay = row.querySelector('.cust-items-count-label');
        if (countDisplay) {
            countDisplay.innerText = `${count} ${count === 1 ? 'item' : 'items'}`;
        }

        // Auto-update split box inputs if split mode is active
        syncSplitBoxValues(cIdx, custTotal);

        recalcAllSummaryMetrics();
    }

    /* ── Payment Mode Change (Opens Split Box) ── */
    function onPaymentModeChange(cIdx, selectEl) {
        const splitBox = document.getElementById(`splitBox_${cIdx}`);
        if (!splitBox) return;

        const mode = selectEl.value;
        const row = document.getElementById(`dayCustRow_${cIdx}`);
        const total = getCustomerTotal(cIdx);

        if (mode === 'cash+upi') {
            splitBox.className = 'split-payment-box theme-cash-upi';
            splitBox.style.display = 'flex';
            const halfCash = Math.round((total / 2) * 100) / 100;
            const halfUpi = Math.round((total - halfCash) * 100) / 100;
            splitBox.innerHTML = `
                <div style="display:flex; align-items:center; justify-content:space-between; gap:4px; font-size:11px;">
                    <span style="color:#15803D; font-weight:700;">💵 Cash:</span>
                    <div style="display:flex; align-items:center;">
                        <span style="font-size:11px; color:#64748B; margin-right:2px;">₹</span>
                        <input type="number" step="0.01" min="0" class="split-cash-input day-input num-field"
                               style="width:78px; height:26px; padding:2px 6px !important; font-size:11.5px !important;"
                               value="${halfCash.toFixed(2)}"
                               oninput="onSplitCashInput(${cIdx}, this)">
                    </div>
                </div>
                <div style="display:flex; align-items:center; justify-content:space-between; gap:4px; font-size:11px;">
                    <span style="color:#1D4ED8; font-weight:700;">📱 UPI:</span>
                    <div style="display:flex; align-items:center;">
                        <span style="font-size:11px; color:#64748B; margin-right:2px;">₹</span>
                        <input type="number" step="0.01" min="0" class="split-upi-input day-input num-field"
                               style="width:78px; height:26px; padding:2px 6px !important; font-size:11.5px !important;"
                               value="${halfUpi.toFixed(2)}"
                               oninput="onSplitUpiInput(${cIdx}, this)">
                    </div>
                </div>
            `;
        } else if (mode === 'cash+udhari') {
            splitBox.className = 'split-payment-box theme-udhari';
            splitBox.style.display = 'flex';
            splitBox.innerHTML = `
                <div style="display:flex; align-items:center; justify-content:space-between; gap:4px; font-size:11px;">
                    <span style="color:#15803D; font-weight:700;">💵 Cash Paid:</span>
                    <div style="display:flex; align-items:center;">
                        <span style="font-size:11px; color:#64748B; margin-right:2px;">₹</span>
                        <input type="number" step="0.01" min="0" class="split-cash-input day-input num-field"
                               style="width:78px; height:26px; padding:2px 6px !important; font-size:11.5px !important;"
                               value="0.00"
                               oninput="onCashUdhariInput(${cIdx}, this)">
                    </div>
                </div>
                <div style="display:flex; align-items:center; justify-content:space-between; gap:4px; font-size:11px;">
                    <span style="color:#DC2626; font-weight:700;">📝 Khata Due:</span>
                    <div style="display:flex; align-items:center;">
                        <span style="font-size:11px; color:#64748B; margin-right:2px;">₹</span>
                        <input type="number" step="0.01" min="0" class="split-udhari-input day-input num-field"
                               style="width:78px; height:26px; padding:2px 6px !important; font-size:11.5px !important;"
                               value="${total.toFixed(2)}"
                               readonly>
                    </div>
                </div>
            `;
        } else if (mode === 'upi+udhari') {
            splitBox.className = 'split-payment-box theme-udhari';
            splitBox.style.display = 'flex';
            splitBox.innerHTML = `
                <div style="display:flex; align-items:center; justify-content:space-between; gap:4px; font-size:11px;">
                    <span style="color:#1D4ED8; font-weight:700;">📱 UPI Paid:</span>
                    <div style="display:flex; align-items:center;">
                        <span style="font-size:11px; color:#64748B; margin-right:2px;">₹</span>
                        <input type="number" step="0.01" min="0" class="split-upi-input day-input num-field"
                               style="width:78px; height:26px; padding:2px 6px !important; font-size:11.5px !important;"
                               value="0.00"
                               oninput="onUpiUdhariInput(${cIdx}, this)">
                    </div>
                </div>
                <div style="display:flex; align-items:center; justify-content:space-between; gap:4px; font-size:11px;">
                    <span style="color:#DC2626; font-weight:700;">📝 Khata Due:</span>
                    <div style="display:flex; align-items:center;">
                        <span style="font-size:11px; color:#64748B; margin-right:2px;">₹</span>
                        <input type="number" step="0.01" min="0" class="split-udhari-input day-input num-field"
                               style="width:78px; height:26px; padding:2px 6px !important; font-size:11.5px !important;"
                               value="${total.toFixed(2)}"
                               readonly>
                    </div>
                </div>
            `;
        } else {
            splitBox.style.display = 'none';
            splitBox.innerHTML = '';
        }

        recalcAllSummaryMetrics();
    }

    function getCustomerTotal(cIdx) {
        const row = document.getElementById(`dayCustRow_${cIdx}`);
        if (!row) return 0;
        let tot = 0;
        const q1 = parseInt(row.querySelector('.item-qty-input')?.value || 0);
        const p1 = parseFloat(row.querySelector('.item-price-input')?.value || 0);
        if (q1 > 0 && p1 >= 0) tot += (q1 * p1);

        document.querySelectorAll(`.day-customer-subitem-row[data-parent-customer="${cIdx}"]`).forEach(sr => {
            const q = parseInt(sr.querySelector('.item-qty-input')?.value || 0);
            const p = parseFloat(sr.querySelector('.item-price-input')?.value || 0);
            if (q > 0 && p >= 0) tot += (q * p);
        });
        return tot;
    }

    /* ── Split Box Live Balancing ── */
    function onSplitCashInput(cIdx, cashInput) {
        const total = getCustomerTotal(cIdx);
        const cashVal = parseFloat(cashInput.value) || 0;
        const upiInput = document.querySelector(`#splitBox_${cIdx} .split-upi-input`);
        if (upiInput) {
            const remainder = Math.max(0, total - cashVal);
            upiInput.value = remainder.toFixed(2);
        }
        recalcAllSummaryMetrics();
    }

    function onSplitUpiInput(cIdx, upiInput) {
        const total = getCustomerTotal(cIdx);
        const upiVal = parseFloat(upiInput.value) || 0;
        const cashInput = document.querySelector(`#splitBox_${cIdx} .split-cash-input`);
        if (cashInput) {
            const remainder = Math.max(0, total - upiVal);
            cashInput.value = remainder.toFixed(2);
        }
        recalcAllSummaryMetrics();
    }

    function onCashUdhariInput(cIdx, cashInput) {
        const total = getCustomerTotal(cIdx);
        const cashVal = parseFloat(cashInput.value) || 0;
        const udhariInput = document.querySelector(`#splitBox_${cIdx} .split-udhari-input`);
        if (udhariInput) {
            const due = Math.max(0, total - cashVal);
            udhariInput.value = due.toFixed(2);
        }
        recalcAllSummaryMetrics();
    }

    function onUpiUdhariInput(cIdx, upiInput) {
        const total = getCustomerTotal(cIdx);
        const upiVal = parseFloat(upiInput.value) || 0;
        const udhariInput = document.querySelector(`#splitBox_${cIdx} .split-udhari-input`);
        if (udhariInput) {
            const due = Math.max(0, total - upiVal);
            udhariInput.value = due.toFixed(2);
        }
        recalcAllSummaryMetrics();
    }

    function syncSplitBoxValues(cIdx, total) {
        const splitBox = document.getElementById(`splitBox_${cIdx}`);
        if (!splitBox || splitBox.style.display === 'none') return;

        const row = document.getElementById(`dayCustRow_${cIdx}`);
        const mode = row?.querySelector('.cust-payment-select')?.value;

        if (mode === 'cash+upi') {
            const cashInput = splitBox.querySelector('.split-cash-input');
            const upiInput = splitBox.querySelector('.split-upi-input');
            if (cashInput && upiInput) {
                const cVal = parseFloat(cashInput.value) || 0;
                if (cVal > total) {
                    cashInput.value = total.toFixed(2);
                    upiInput.value = '0.00';
                } else {
                    upiInput.value = Math.max(0, total - cVal).toFixed(2);
                }
            }
        } else if (mode === 'cash+udhari') {
            const cashInput = splitBox.querySelector('.split-cash-input');
            const udhariInput = splitBox.querySelector('.split-udhari-input');
            if (cashInput && udhariInput) {
                const cVal = parseFloat(cashInput.value) || 0;
                udhariInput.value = Math.max(0, total - cVal).toFixed(2);
            }
        } else if (mode === 'upi+udhari') {
            const upiInput = splitBox.querySelector('.split-upi-input');
            const udhariInput = splitBox.querySelector('.split-udhari-input');
            if (upiInput && udhariInput) {
                const uVal = parseFloat(upiInput.value) || 0;
                udhariInput.value = Math.max(0, total - uVal).toFixed(2);
            }
        }
    }

    /* ── Recalculate Summary Stats ── */
    function recalcAllSummaryMetrics() {
        const rows = document.querySelectorAll('#daySalesRowsTbody .day-customer-row');
        let billsCount = 0;
        let phonesCount = 0;
        let accCount = 0;
        let grandTotal = 0;
        let cashTotal = 0;
        let upiTotal = 0;
        let emiTotal = 0;
        let khataTotal = 0;

        rows.forEach(r => {
            const cIdx = r.dataset.customerIndex;
            const itemLines = [r, ...document.querySelectorAll(`.day-customer-subitem-row[data-parent-customer="${cIdx}"]`)];
            const payMode = r.querySelector('.cust-payment-select')?.value || 'cash';
            const splitBox = document.getElementById(`splitBox_${cIdx}`);

            let custBillTotal = 0;
            let hasAnyItem = false;

            itemLines.forEach(l => {
                const qty = parseInt(l.querySelector('.item-qty-input')?.value || 0);
                const price = parseFloat(l.querySelector('.item-price-input')?.value || 0);
                const itemType = l.querySelector('.item-type')?.value || 'accessory';
                const itemName = (l.querySelector('.item-search-input')?.value || '').trim();

                if (itemName || price > 0) {
                    hasAnyItem = true;
                    if (itemType === 'phone') {
                        phonesCount += qty;
                    } else {
                        accCount += qty;
                    }
                    custBillTotal += (qty * price);
                }
            });

            if (hasAnyItem && custBillTotal > 0) {
                billsCount++;
                grandTotal += custBillTotal;

                if (payMode === 'cash') {
                    cashTotal += custBillTotal;
                } else if (payMode === 'upi') {
                    upiTotal += custBillTotal;
                } else if (payMode === 'emi') {
                    emiTotal += custBillTotal;
                } else if (payMode === 'udhari') {
                    khataTotal += custBillTotal;
                } else if (payMode === 'cash+upi') {
                    const cVal = parseFloat(splitBox?.querySelector('.split-cash-input')?.value) || 0;
                    const uVal = parseFloat(splitBox?.querySelector('.split-upi-input')?.value) || 0;
                    cashTotal += cVal;
                    upiTotal += uVal;
                } else if (payMode === 'cash+udhari') {
                    const cVal = parseFloat(splitBox?.querySelector('.split-cash-input')?.value) || 0;
                    const kVal = parseFloat(splitBox?.querySelector('.split-udhari-input')?.value) || 0;
                    cashTotal += cVal;
                    khataTotal += kVal;
                } else if (payMode === 'upi+udhari') {
                    const uVal = parseFloat(splitBox?.querySelector('.split-upi-input')?.value) || 0;
                    const kVal = parseFloat(splitBox?.querySelector('.split-udhari-input')?.value) || 0;
                    upiTotal += uVal;
                    khataTotal += kVal;
                }
            }
        });

        // Update labels
        document.getElementById('lblFooterTotalBills').innerText = billsCount;
        document.getElementById('lblFooterPhonesAcc').innerText = `${phonesCount} Phones / ${accCount} Acc`;
        document.getElementById('lblFooterTotalAmount').innerText = `₹${grandTotal.toFixed(2)}`;
        document.getElementById('lblFooterCash').innerText = `₹${Math.round(cashTotal)}`;
        document.getElementById('lblFooterUpi').innerText = `₹${Math.round(upiTotal)}`;
        document.getElementById('lblFooterEmi').innerText = `₹${Math.round(emiTotal)}`;
        document.getElementById('lblFooterKhata').innerText = `₹${Math.round(khataTotal)}`;
    }

    /* ── Default Payment Apply ── */
    function applyDefaultPaymentToAllRows() {
        const newMode = document.getElementById('masterDefaultPayment')?.value || 'cash';
        const rows = document.querySelectorAll('.day-customer-row');
        rows.forEach(r => {
            const cIdx = r.dataset.customerIndex;
            const sel = r.querySelector('.cust-payment-select');
            if (sel) {
                sel.value = newMode;
                onPaymentModeChange(cIdx, sel);
            }
        });
        recalcAllSummaryMetrics();
    }

    /* ── Autocomplete Customer Helpers ── */
    function onCustNameInput(cIdx, input) {
        const row = document.getElementById(`dayCustRow_${cIdx}`);
        if (!row) return;
        const val = (input.value || '').trim().toLowerCase();
        if (!val) return;

        const match = customersData.find(c => (c.name || '').toLowerCase() === val);
        if (match) {
            const phoneInput = row.querySelector('.cust-phone-input');
            if (phoneInput && !phoneInput.value && match.phone) {
                phoneInput.value = match.phone;
            }
            const khataBadge = row.querySelector('.cust-khata-badge');
            if (khataBadge && match.udhari_balance > 0) {
                khataBadge.innerText = `Khata Due: ₹${parseFloat(match.udhari_balance).toFixed(2)}`;
                khataBadge.style.display = 'inline-block';
            }
        }
    }

    function onCustPhoneInput(cIdx, input) {
        const row = document.getElementById(`dayCustRow_${cIdx}`);
        if (!row) return;
        const val = (input.value || '').trim();
        if (!val) return;

        const match = customersData.find(c => (c.phone || '').trim() === val);
        if (match) {
            const nameInput = row.querySelector('.cust-name-input');
            if (nameInput && !nameInput.value && match.name) {
                nameInput.value = match.name;
            }
            const khataBadge = row.querySelector('.cust-khata-badge');
            if (khataBadge && match.udhari_balance > 0) {
                khataBadge.innerText = `Khata Due: ₹${parseFloat(match.udhari_balance).toFixed(2)}`;
                khataBadge.style.display = 'inline-block';
            }
        }
    }

    /* ── Item Search & Dropdown Picker ── */
    function onItemInputFocus(itIdx, input) {
        onItemInputSearch(itIdx, input);
    }

    function onItemInputSearch(itIdx, input) {
        const dropdown = document.getElementById(`itemSearchDropdown_${itIdx}`);
        if (!dropdown) return;

        const term = (input.value || '').toLowerCase().trim();

        // Close all other dropdowns
        document.querySelectorAll('.item-search-dropdown').forEach(d => {
            if (d !== dropdown) d.style.display = 'none';
        });

        // Search phones
        const matchedPhones = allPhonesData.filter(p => {
            const str = `${p.brand || ''} ${p.model || ''} ${p.imei_1 || ''}`.toLowerCase();
            return !term || str.includes(term);
        }).slice(0, 10);

        // Search parts
        const matchedParts = allPartsData.filter(p => {
            const str = `${p.name || ''} ${p.category || ''}`.toLowerCase();
            return !term || str.includes(term);
        }).slice(0, 20);

        if (matchedPhones.length === 0 && matchedParts.length === 0) {
            dropdown.innerHTML = `
                <div style="padding: 10px 12px; text-align: center; color: #64748B; font-size: 12px;">
                    No inventory match for "<strong>${escapeHtml(input.value)}</strong>"
                    <div style="font-size: 11px; color:#4F46E5; margin-top: 3px; font-weight: 600;">Custom retail product will be recorded directly</div>
                </div>
            `;
            dropdown.style.display = 'block';
            return;
        }

        let html = '';

        // Phones section
        if (matchedPhones.length > 0) {
            html += `<div style="padding:4px 10px; background:#F8FAFC; font-size:10px; font-weight:800; color:#64748B; text-transform:uppercase;">📱 Phones in Stock</div>`;
            matchedPhones.forEach(ph => {
                const title = `${ph.brand} ${ph.model}`;
                const imei = ph.imei_1 ? `IMEI: ${ph.imei_1}` : '';
                const price = parseFloat(ph.selling_price || 0);
                html += `
                    <div class="item-search-item" onclick="onPickPhoneItem(${itIdx}, ${ph.id}, '${escapeHtml(title)}', ${price})">
                        <div>
                            <div style="font-weight:700; color:#0F172A; font-size:12.5px;">${escapeHtml(title)}</div>
                            <div style="font-size:11px; color:#64748B;">
                                <span style="background:#EFF6FF; color:#1D4ED8; padding:1px 5px; border-radius:4px; font-weight:700;">Phone</span>
                                ${imei ? `<span style="margin-left:4px;">${escapeHtml(imei)}</span>` : ''}
                            </div>
                        </div>
                        <div style="font-weight:900; color:#16A34A; font-size:13px; font-family:'JetBrains Mono', monospace;">
                            ₹${price.toFixed(2)}
                        </div>
                    </div>
                `;
            });
        }

        // Parts section
        if (matchedParts.length > 0) {
            html += `<div style="padding:4px 10px; background:#F8FAFC; font-size:10px; font-weight:800; color:#64748B; text-transform:uppercase;">⚡ Accessories &amp; Spares</div>`;
            matchedParts.forEach(pt => {
                const price = parseFloat(pt.selling_price || 0);
                const stock = parseInt(pt.stock_qty || 0);
                const sColor = stock > 0 ? '#059669' : '#DC2626';
                const sBg = stock > 0 ? '#ECFDF5' : '#FEF2F2';
                html += `
                    <div class="item-search-item" onclick="onPickPartItem(${itIdx}, ${pt.id}, '${escapeHtml(pt.name)}', ${price})">
                        <div>
                            <div style="font-weight:700; color:#0F172A; font-size:12.5px;">${escapeHtml(pt.name)}</div>
                            <div style="font-size:11px; color:#64748B; display:flex; gap:6px; align-items:center;">
                                <span style="background:#F1F5F9; padding:1px 5px; border-radius:4px;">${escapeHtml(pt.category || 'Accessory')}</span>
                                <span style="background:${sBg}; color:${sColor}; padding:1px 5px; border-radius:4px; font-weight:700;">Stock: ${stock}</span>
                            </div>
                        </div>
                        <div style="font-weight:900; color:#16A34A; font-size:13px; font-family:'JetBrains Mono', monospace;">
                            ₹${price.toFixed(2)}
                        </div>
                    </div>
                `;
            });
        }

        dropdown.innerHTML = html;
        dropdown.style.display = 'block';
    }

    function onPickPartItem(itIdx, partId, partName, price) {
        const dropdown = document.getElementById(`itemSearchDropdown_${itIdx}`);
        const itemRow = dropdown?.closest('tr');
        if (!itemRow) return;

        itemRow.querySelector('.item-search-input').value = partName;
        itemRow.querySelector('.item-part-id').value = partId;
        itemRow.querySelector('.item-device-id').value = 0;
        itemRow.querySelector('.item-type').value = 'accessory';
        itemRow.querySelector('.item-price-input').value = price.toFixed(2);

        dropdown.style.display = 'none';

        const cIdx = itemRow.dataset.customerIndex || itemRow.dataset.parentCustomer;
        if (cIdx) {
            recalcCustomerTotal(cIdx);
        }
    }

    function onPickPhoneItem(itIdx, deviceId, phoneTitle, price) {
        const dropdown = document.getElementById(`itemSearchDropdown_${itIdx}`);
        const itemRow = dropdown?.closest('tr');
        if (!itemRow) return;

        itemRow.querySelector('.item-search-input').value = phoneTitle;
        itemRow.querySelector('.item-part-id').value = 0;
        itemRow.querySelector('.item-device-id').value = deviceId;
        itemRow.querySelector('.item-type').value = 'phone';
        itemRow.querySelector('.item-price-input').value = price.toFixed(2);

        dropdown.style.display = 'none';

        const cIdx = itemRow.dataset.customerIndex || itemRow.dataset.parentCustomer;
        if (cIdx) {
            recalcCustomerTotal(cIdx);
        }
    }

    // Close dropdown on outside click
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.item-search-input') && !e.target.closest('.item-search-dropdown')) {
            document.querySelectorAll('.item-search-dropdown').forEach(d => d.style.display = 'none');
        }
    });

    /* ── Keyboard Shortcut: Ctrl + Enter to add new customer row ── */
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            e.preventDefault();
            addCustomerSaleRow();
            setTimeout(() => {
                const rows = document.querySelectorAll('#daySalesRowsTbody .day-customer-row');
                const lastRow = rows[rows.length - 1];
                if (lastRow) {
                    lastRow.querySelector('.item-search-input')?.focus();
                }
            }, 60);
        }
    });

    /* ── Submit All Day Sales ── */
    async function submitDaySales() {
        const alertBox = document.getElementById('daySaleAlertBox');
        if (alertBox) alertBox.style.display = 'none';

        const btn = document.getElementById('btnSaveAllDaySales');
        const custRows = document.querySelectorAll('#daySalesRowsTbody .day-customer-row');

        const validSalesPayload = [];

        for (let i = 0; i < custRows.length; i++) {
            const r = custRows[i];
            const cIdx = r.dataset.customerIndex;
            const num = i + 1;
            const custName = (r.querySelector('.cust-name-input')?.value || '').trim() || 'Walk-in Customer';
            const custPhone = (r.querySelector('.cust-phone-input')?.value || '').trim() || '9999999999';
            const paymentMode = r.querySelector('.cust-payment-select')?.value || 'cash';
            const splitBox = document.getElementById(`splitBox_${cIdx}`);

            const itemLines = [r, ...document.querySelectorAll(`.day-customer-subitem-row[data-parent-customer="${cIdx}"]`)];
            const items = [];
            let custTotal = 0;

            for (let j = 0; j < itemLines.length; j++) {
                const l = itemLines[j];
                const itNum = j + 1;
                const itemName = (l.querySelector('.item-search-input')?.value || '').trim();
                const partId = parseInt(l.querySelector('.item-part-id')?.value || 0);
                const deviceId = parseInt(l.querySelector('.item-device-id')?.value || 0);
                const itemType = l.querySelector('.item-type')?.value || 'accessory';
                const qty = parseInt(l.querySelector('.item-qty-input')?.value || 0);
                const price = parseFloat(l.querySelector('.item-price-input')?.value || 0);

                if (!itemName && partId === 0 && deviceId === 0 && price <= 0) {
                    continue; // skip blank item
                }

                if (!itemName && partId === 0 && deviceId === 0) {
                    showPageWarning(`Customer #${num} (${custName}), Item #${itNum}: Please enter or search an item name.`, l.querySelector('.item-search-input'));
                    return;
                }

                if (isNaN(qty) || qty < 1) {
                    showPageWarning(`Customer #${num} (${custName}), Item #${itNum}: Quantity must be at least 1.`, l.querySelector('.item-qty-input'));
                    return;
                }

                if (isNaN(price) || price < 0) {
                    showPageWarning(`Customer #${num} (${custName}), Item #${itNum}: Price cannot be negative.`, l.querySelector('.item-price-input'));
                    return;
                }

                items.push({
                    part_id: partId,
                    device_id: deviceId,
                    part_name: itemName,
                    item_type: itemType,
                    quantity: qty,
                    unit_price: price,
                });
                custTotal += (qty * price);
            }

            // Skip customer row if completely empty
            if (items.length === 0 && custTotal <= 0) {
                continue;
            }

            if (items.length === 0) {
                showPageWarning(`Customer #${num} (${custName}): Please add at least one valid item.`);
                return;
            }

            // Validate split payment values
            let cashAmount = 0;
            let upiAmount = 0;

            if (paymentMode === 'cash+upi') {
                cashAmount = parseFloat(splitBox?.querySelector('.split-cash-input')?.value) || 0;
                upiAmount = parseFloat(splitBox?.querySelector('.split-upi-input')?.value) || 0;
                if (Math.abs((cashAmount + upiAmount) - custTotal) > 0.05) {
                    showPageWarning(`Customer #${num} (${custName}): Split Cash (₹${cashAmount.toFixed(2)}) + UPI (₹${upiAmount.toFixed(2)}) total ₹${(cashAmount + upiAmount).toFixed(2)}, which must equal total bill ₹${custTotal.toFixed(2)}.`);
                    return;
                }
            } else if (paymentMode === 'cash+udhari') {
                cashAmount = parseFloat(splitBox?.querySelector('.split-cash-input')?.value) || 0;
                if (cashAmount > custTotal) {
                    showPageWarning(`Customer #${num} (${custName}): Cash paid (₹${cashAmount.toFixed(2)}) cannot exceed total bill (₹${custTotal.toFixed(2)}).`);
                    return;
                }
            } else if (paymentMode === 'upi+udhari') {
                upiAmount = parseFloat(splitBox?.querySelector('.split-upi-input')?.value) || 0;
                if (upiAmount > custTotal) {
                    showPageWarning(`Customer #${num} (${custName}): UPI paid (₹${upiAmount.toFixed(2)}) cannot exceed total bill (₹${custTotal.toFixed(2)}).`);
                    return;
                }
            }

            validSalesPayload.push({
                customer_name: custName,
                customer_phone: custPhone,
                payment_mode: paymentMode,
                cash_amount: cashAmount,
                upi_amount: upiAmount,
                ref_note: '',
                items: items,
            });
        }

        if (validSalesPayload.length === 0) {
            showPageWarning('Please enter at least one customer sale entry before saving.');
            return;
        }

        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" style="width:14px;height:14px;border:2px solid #fff;border-top-color:transparent;border-radius:50%;display:inline-block;animation:spin 0.6s linear infinite;"></span> Saving All Sales...';
        }

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                              document.querySelector('input[name="_token"]')?.value;

            const saleDate = document.getElementById('masterSaleDate')?.value || '';
            const staffId = document.getElementById('masterStaffMember')?.value || '';
            const billType = document.getElementById('masterBillType')?.value || 'gst_18';

            const response = await fetch("{{ route('mobileshop.sales.bulk_store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    sales: validSalesPayload,
                    sale_date: saleDate,
                    staff_id: staffId,
                    bill_type: billType,
                })
            });

            const res = await response.json();

            if (res.success) {
                alert(res.message || 'All day sales recorded successfully!');
                window.location.href = "{{ route('mobileshop.sales') }}";
            } else {
                showPageWarning(res.message || 'Error occurred while saving sales.');
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i data-lucide="check-check" style="width:16px;height:16px;"></i> Post &amp; Save All Day Sales';
                    if (window.lucide) window.lucide.createIcons();
                }
            }
        } catch (err) {
            showPageWarning('Network or server error: ' + (err.message || 'Unknown error'));
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i data-lucide="check-check" style="width:16px;height:16px;"></i> Post &amp; Save All Day Sales';
                if (window.lucide) window.lucide.createIcons();
            }
        }
    }

    // Initialize with default 1 row on start (user can add as many as they want)
    document.addEventListener('DOMContentLoaded', () => {
        addCustomerSaleRow();
    });
</script>
<style>
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endpush
