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
    .day-end-container {
        max-width: 1480px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 14px;
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
        font-family: 'JetBrains Mono', monospace !important;
        font-weight: 700 !important;
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
    }
    .day-sales-table th {
        background: #F8FAFC;
        border-top: 1px solid #E2E8F0;
        border-bottom: 1px solid #E2E8F0;
        color: #475569;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        padding: 9px 10px;
        text-align: left;
    }
    .day-sales-table td {
        padding: 10px 10px;
        border-bottom: 1px solid #E2E8F0;
        vertical-align: top;
        background: #FFFFFF;
    }
    .day-sales-table tr:hover td {
        background: #FCFCFD;
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

    /* ── Buttons ── */
    .btn-add-row-action {
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
    .btn-add-row-action:hover {
        background: #4F46E5 !important;
        border-color: #4338CA !important;
        color: #FFFFFF !important;
    }
    .btn-trash-row {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        background: #FFFFFF;
        border: 1px solid #FECACA;
        color: #DC2626;
        cursor: pointer;
        transition: all 0.15s ease;
        padding: 0;
    }
    .btn-trash-row:hover {
        background: #FEF2F2 !important;
        border-color: #F87171 !important;
    }

    .btn-add-item-link {
        background: transparent;
        border: none;
        color: #4F46E5;
        font-size: 11.5px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 0 0 0;
        transition: color 0.15s ease;
    }
    .btn-add-item-link:hover {
        color: #3730A3;
        text-decoration: underline;
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
            <table class="day-sales-table">
                <thead>
                    <tr>
                        <th style="width:36px; text-align:center;">#</th>
                        <th style="width:230px;">CUSTOMER (OPTIONAL FOR PAID)</th>
                        <th style="min-width:320px;">ITEM / PRODUCT (SEARCH DROPDOWN)</th>
                        <th style="width:75px; text-align:center;">QTY</th>
                        <th style="width:115px;">UNIT PRICE (₹)</th>
                        <th style="width:110px; text-align:right;">TOTAL (₹)</th>
                        <th style="width:200px;">PAYMENT METHOD</th>
                        <th style="width:76px; text-align:center;">ACTION</th>
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
        const tr = document.createElement('tr');
        tr.className = 'day-customer-row';
        tr.dataset.customerIndex = cIdx;
        tr.id = `dayCustRow_${cIdx}`;

        const defaultPayment = document.getElementById('masterDefaultPayment')?.value || 'cash';

        tr.innerHTML = `
            <!-- # -->
            <td style="text-align:center; font-weight:700; color:#64748B; font-size:13px;" class="row-num-cell">
                ${tbody.children.length + 1}
            </td>

            <!-- CUSTOMER (OPTIONAL) -->
            <td>
                <div style="display:flex; flex-direction:column; gap:4px;">
                    <div style="position:relative;">
                        <span style="position:absolute; left:8px; top:50%; transform:translateY(-50%); font-size:11px; color:#94A3B8;">👤</span>
                        <input type="text" class="day-input cust-name-input" list="registeredCustomersDatalist"
                               placeholder="Customer Name (Optional)"
                               style="padding-left:24px !important; font-size:12px !important; height:30px;"
                               oninput="onCustNameInput(${cIdx}, this)">
                    </div>
                    <div style="position:relative;">
                        <span style="position:absolute; left:8px; top:50%; transform:translateY(-50%); font-size:11px; color:#94A3B8;">📞</span>
                        <input type="tel" class="day-input cust-phone-input" list="registeredPhonesDatalist"
                               placeholder="Mobile (Optional)"
                               style="padding-left:24px !important; font-size:12px !important; height:30px; font-family:'JetBrains Mono',monospace;"
                               oninput="onCustPhoneInput(${cIdx}, this)">
                    </div>
                    <div class="cust-khata-badge" style="display:none; font-size:10.5px; font-weight:700; color:#DC2626; background:#FEF2F2; padding:2px 6px; border-radius:4px;"></div>
                </div>
            </td>

            <!-- ITEMS CONTAINER (Can have multiple items per customer) -->
            <td colspan="3" style="padding:4px 6px !important;">
                <div class="customer-items-container" id="custItemsContainer_${cIdx}" style="display:flex; flex-direction:column; gap:6px;">
                    <!-- Items injected dynamically -->
                </div>
                <div style="margin-top:5px; padding-left:4px;">
                    <button type="button" onclick="addItemToCustomer(${cIdx})" class="btn-add-item-link">
                        <i data-lucide="plus-circle" style="width:12px;height:12px;"></i> + Add another item for this customer
                    </button>
                </div>
            </td>

            <!-- TOTAL (₹) -->
            <td style="text-align:right;">
                <div class="cust-bill-total" style="font-size:15px; font-weight:900; color:#0F172A; font-family:'JetBrains Mono',monospace;">
                    ₹0.00
                </div>
                <div class="cust-items-count-label" style="font-size:11px; color:#64748B; margin-top:2px;">
                    1 item
                </div>
            </td>

            <!-- PAYMENT METHOD (With Interactive Split Box) -->
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

                <!-- Interactive Split Box (Opens automatically for split payments) -->
                <div class="split-payment-box" id="splitBox_${cIdx}" style="display:none;"></div>
            </td>

            <!-- ACTION -->
            <td style="text-align:center; vertical-align:middle;">
                <div style="display:inline-flex; align-items:center; gap:5px; justify-content:center;">
                    <button type="button" class="btn-add-row-action" onclick="addCustomerSaleRow()" title="Add Sale Row">
                        <i data-lucide="plus" style="width:14px;height:14px;"></i>
                    </button>
                    <button type="button" class="btn-trash-row" onclick="removeCustomerRow(${cIdx})" title="Delete Customer Bill">
                        <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                    </button>
                </div>
            </td>
        `;

        tbody.appendChild(tr);

        // Add 1st item row to this customer
        addItemToCustomer(cIdx);

        // Render split box if default was split
        onPaymentModeChange(cIdx, tr.querySelector('.cust-payment-select'));

        renumberCustomerRows();
        recalcAllSummaryMetrics();

        if (window.lucide) {
            window.lucide.createIcons();
        }
    }

    /* ── Add Item Line to a Customer ── */
    function addItemToCustomer(cIdx) {
        const container = document.getElementById(`custItemsContainer_${cIdx}`);
        if (!container) return;

        globalItemCounter++;
        const itIdx = globalItemCounter;

        const itemRow = document.createElement('div');
        itemRow.className = 'cust-item-line';
        itemRow.id = `itemLine_${itIdx}`;
        itemRow.style.cssText = 'display:grid; grid-template-columns: minmax(240px, 1fr) 75px 115px 24px; gap:8px; align-items:center;';

        itemRow.innerHTML = `
            <!-- Product Search -->
            <div style="position:relative;">
                <input type="text" class="day-input item-search-input"
                       placeholder="Search Phone or Accessory..."
                       style="height:32px; font-weight:700; font-size:12.5px;"
                       autocomplete="off"
                       onfocus="onItemInputFocus(${itIdx}, this)"
                       oninput="onItemInputSearch(${itIdx}, this)">
                <input type="hidden" class="item-part-id" value="0">
                <input type="hidden" class="item-device-id" value="0">
                <input type="hidden" class="item-type" value="accessory">

                <!-- Floating Dropdown -->
                <div class="item-search-dropdown" id="itemSearchDropdown_${itIdx}"></div>
            </div>

            <!-- Qty -->
            <div>
                <input type="number" min="1" value="1" class="day-input num-field num-no-spin item-qty-input"
                       style="text-align:center; height:32px; font-size:12.5px;"
                       oninput="recalcCustomerTotal(${cIdx})">
            </div>

            <!-- Unit Price -->
            <div>
                <input type="number" min="0" step="0.01" value="0.00" class="day-input num-field item-price-input"
                       style="height:32px; font-size:12.5px;"
                       placeholder="0.00"
                       oninput="recalcCustomerTotal(${cIdx})">
            </div>

            <!-- Delete Item Button (Only shown if customer has >1 item) -->
            <div>
                <button type="button" onclick="removeItemLine(${cIdx}, ${itIdx})" class="btn-remove-item"
                        style="background:none; border:none; color:#94A3B8; font-size:14px; font-weight:800; cursor:pointer; padding:0; display:none; line-height:1;"
                        title="Remove this item">✕</button>
            </div>
        `;

        container.appendChild(itemRow);
        updateItemRemoveButtons(cIdx);
        recalcCustomerTotal(cIdx);

        if (window.lucide) {
            window.lucide.createIcons();
        }
    }

    /* ── Remove Item Line ── */
    function removeItemLine(cIdx, itIdx) {
        const itemLine = document.getElementById(`itemLine_${itIdx}`);
        if (!itemLine) return;

        const container = document.getElementById(`custItemsContainer_${cIdx}`);
        if (container && container.children.length > 1) {
            itemLine.remove();
            updateItemRemoveButtons(cIdx);
            recalcCustomerTotal(cIdx);
        }
    }

    function updateItemRemoveButtons(cIdx) {
        const container = document.getElementById(`custItemsContainer_${cIdx}`);
        if (!container) return;

        const lines = container.querySelectorAll('.cust-item-line');
        lines.forEach(l => {
            const btn = l.querySelector('.btn-remove-item');
            if (btn) {
                btn.style.display = lines.length > 1 ? 'block' : 'none';
            }
        });

        const custRow = document.getElementById(`dayCustRow_${cIdx}`);
        const countLabel = custRow?.querySelector('.cust-items-count-label');
        if (countLabel) {
            countLabel.innerText = `${lines.length} ${lines.length === 1 ? 'item' : 'items'}`;
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
        if (badge) badge.innerText = `${rows.length} Sales`;
    }

    /* ── Remove Customer Row ── */
    function removeCustomerRow(cIdx) {
        const row = document.getElementById(`dayCustRow_${cIdx}`);
        if (!row) return;

        const tbody = document.getElementById('daySalesRowsTbody');
        if (tbody.children.length <= 1) {
            // Keep at least 1 clean customer row
            row.querySelector('.cust-name-input').value = '';
            row.querySelector('.cust-phone-input').value = '';
            const itemsContainer = document.getElementById(`custItemsContainer_${cIdx}`);
            if (itemsContainer) itemsContainer.innerHTML = '';
            addItemToCustomer(cIdx);
            recalcCustomerTotal(cIdx);
            return;
        }

        row.remove();
        renumberCustomerRows();
        recalcAllSummaryMetrics();
    }

    /* ── Recalculate Customer Total ── */
    function recalcCustomerTotal(cIdx) {
        const row = document.getElementById(`dayCustRow_${cIdx}`);
        if (!row) return;

        const itemLines = row.querySelectorAll('.cust-item-line');
        let custTotal = 0;

        itemLines.forEach(l => {
            const qty = parseInt(l.querySelector('.item-qty-input')?.value || 1);
            const price = parseFloat(l.querySelector('.item-price-input')?.value || 0);
            if (qty > 0 && price >= 0) {
                custTotal += (qty * price);
            }
        });

        const totalDisplay = row.querySelector('.cust-bill-total');
        if (totalDisplay) {
            totalDisplay.innerText = `₹${custTotal.toFixed(2)}`;
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
        row.querySelectorAll('.cust-item-line').forEach(l => {
            const q = parseInt(l.querySelector('.item-qty-input')?.value || 1);
            const p = parseFloat(l.querySelector('.item-price-input')?.value || 0);
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
            const itemLines = r.querySelectorAll('.cust-item-line');
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
        const itemLine = document.getElementById(`itemLine_${itIdx}`);
        if (!itemLine) return;

        itemLine.querySelector('.item-search-input').value = partName;
        itemLine.querySelector('.item-part-id').value = partId;
        itemLine.querySelector('.item-device-id').value = 0;
        itemLine.querySelector('.item-type').value = 'accessory';
        itemLine.querySelector('.item-price-input').value = price.toFixed(2);

        document.getElementById(`itemSearchDropdown_${itIdx}`).style.display = 'none';

        const custRow = itemLine.closest('.day-customer-row');
        if (custRow) {
            recalcCustomerTotal(custRow.dataset.customerIndex);
        }
    }

    function onPickPhoneItem(itIdx, deviceId, phoneTitle, price) {
        const itemLine = document.getElementById(`itemLine_${itIdx}`);
        if (!itemLine) return;

        itemLine.querySelector('.item-search-input').value = phoneTitle;
        itemLine.querySelector('.item-part-id').value = 0;
        itemLine.querySelector('.item-device-id').value = deviceId;
        itemLine.querySelector('.item-type').value = 'phone';
        itemLine.querySelector('.item-price-input').value = price.toFixed(2);

        document.getElementById(`itemSearchDropdown_${itIdx}`).style.display = 'none';

        const custRow = itemLine.closest('.day-customer-row');
        if (custRow) {
            recalcCustomerTotal(custRow.dataset.customerIndex);
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

            const itemLines = r.querySelectorAll('.cust-item-line');
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
