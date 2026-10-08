@extends('mobileshop.layout')

@section('title', 'Day-End Bulk Sales Register — PhoneFix Azamgarh')
@section('page-title', 'Day-End Bulk Sales Register')

@section('page-actions')
    <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
        <button type="button" onclick="addSaleRow()" class="btn btn-outline btn-sm" style="font-weight:700; border:1px solid #CBD5E1; color:#334155; padding:6px 14px; border-radius:6px; display:inline-flex; align-items:center; gap:6px;">
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
        max-width: 1460px;
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
        min-width: 1060px;
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
        padding: 8px 10px;
        border-bottom: 1px solid #F1F5F9;
        vertical-align: middle;
        background: #FFFFFF;
    }
    .day-sales-table tr:hover td {
        background: #FAFAFC;
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

    /* ── Delete Button ── */
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
            <span>to add new row</span>
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
                    3 Sales
                </span>
            </div>
            <button type="button" onclick="addSaleRow()" class="btn btn-primary btn-sm" style="background:#4F46E5; border-color:#4338CA; color:#ffffff; font-weight:700; padding:6px 14px; border-radius:6px; display:inline-flex; align-items:center; gap:6px;">
                <i data-lucide="plus" style="width:13px;height:13px;"></i> Add Sale Row
            </button>
        </div>

        <!-- Table Container -->
        <div class="day-sales-table-wrap">
            <table class="day-sales-table">
                <thead>
                    <tr>
                        <th style="width:40px; text-align:center;">#</th>
                        <th style="width:230px;">CUSTOMER (OPTIONAL FOR PAID)</th>
                        <th style="min-width:260px;">ITEM / PRODUCT (SEARCH DROPDOWN)</th>
                        <th style="width:75px; text-align:center;">QTY</th>
                        <th style="width:115px;">UNIT PRICE (₹)</th>
                        <th style="width:105px; text-align:right;">TOTAL (₹)</th>
                        <th style="width:170px;">PAYMENT METHOD</th>
                        <th style="width:150px;">REF / NOTE</th>
                        <th style="width:50px; text-align:center;">ACTION</th>
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
                <button type="button" onclick="addSaleRow()" class="btn btn-outline" style="font-weight:700; padding:8px 16px; border-radius:6px; font-size:13px; border-color:#CBD5E1; color:#334155; display:inline-flex; align-items:center; gap:6px;">
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
    let dayRowCounter = 0;

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

    /* ── Add Sale Row ── */
    function addSaleRow(initialData = null) {
        const tbody = document.getElementById('daySalesRowsTbody');
        if (!tbody) return;

        dayRowCounter++;
        const rIdx = dayRowCounter;
        const tr = document.createElement('tr');
        tr.className = 'day-sale-row';
        tr.dataset.rowIndex = rIdx;
        tr.id = `daySaleRow_${rIdx}`;

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
                               oninput="onCustNameInput(${rIdx}, this)">
                    </div>
                    <div style="position:relative;">
                        <span style="position:absolute; left:8px; top:50%; transform:translateY(-50%); font-size:11px; color:#94A3B8;">📞</span>
                        <input type="tel" class="day-input cust-phone-input" list="registeredPhonesDatalist"
                               placeholder="Mobile (Optional)"
                               style="padding-left:24px !important; font-size:12px !important; height:30px; font-family:'JetBrains Mono',monospace;"
                               oninput="onCustPhoneInput(${rIdx}, this)">
                    </div>
                </div>
            </td>

            <!-- ITEM / PRODUCT (SEARCH DROPDOWN) -->
            <td>
                <div style="position:relative;">
                    <input type="text" class="day-input item-search-input"
                           placeholder="Search Phone or Accessory..."
                           style="height:34px; font-weight:700;"
                           autocomplete="off"
                           onfocus="onItemInputFocus(${rIdx}, this)"
                           oninput="onItemInputSearch(${rIdx}, this)">
                    <input type="hidden" class="item-part-id" value="0">
                    <input type="hidden" class="item-device-id" value="0">
                    <input type="hidden" class="item-type" value="accessory">

                    <!-- Floating Search Dropdown -->
                    <div class="item-search-dropdown" id="itemSearchDropdown_${rIdx}"></div>
                </div>
            </td>

            <!-- QTY -->
            <td style="text-align:center;">
                <input type="number" min="1" value="1" class="day-input num-field num-no-spin item-qty-input"
                       style="text-align:center; width:65px; height:34px;"
                       oninput="recalcRowTotal(${rIdx})">
            </td>

            <!-- UNIT PRICE (₹) -->
            <td>
                <input type="number" min="0" step="0.01" value="0.00" class="day-input num-field item-price-input"
                       style="height:34px;"
                       placeholder="0.00"
                       oninput="recalcRowTotal(${rIdx})">
            </td>

            <!-- TOTAL (₹) -->
            <td style="text-align:right;">
                <div class="item-total-display" style="font-size:14px; font-weight:800; color:#0F172A; font-family:'JetBrains Mono',monospace;">
                    ₹0.00
                </div>
            </td>

            <!-- PAYMENT METHOD -->
            <td>
                <select class="day-input item-payment-select" style="height:34px; font-weight:700; cursor:pointer;" onchange="recalcAllSummaryMetrics()">
                    <option value="cash" ${defaultPayment === 'cash' ? 'selected' : ''}>💵 Cash</option>
                    <option value="upi" ${defaultPayment === 'upi' ? 'selected' : ''}>📱 UPI</option>
                    <option value="emi" ${defaultPayment === 'emi' ? 'selected' : ''}>⚡ EMI Financed</option>
                    <option value="udhari" ${defaultPayment === 'udhari' ? 'selected' : ''}>📝 Customer Khata</option>
                </select>
            </td>

            <!-- REF / NOTE -->
            <td>
                <input type="text" class="day-input item-note-input"
                       placeholder="UTR / Note"
                       style="height:34px; font-size:12px;">
            </td>

            <!-- ACTION -->
            <td style="text-align:center;">
                <button type="button" class="btn-trash-row" onclick="removeSaleRow(${rIdx})" title="Delete Row">
                    <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                </button>
            </td>
        `;

        tbody.appendChild(tr);
        renumberTableRows();
        recalcAllSummaryMetrics();

        if (window.lucide) {
            window.lucide.createIcons();
        }

        if (initialData) {
            populateRowData(rIdx, initialData);
        }
    }

    /* ── Renumber Rows ── */
    function renumberTableRows() {
        const rows = document.querySelectorAll('#daySalesRowsTbody .day-sale-row');
        rows.forEach((r, idx) => {
            const numCell = r.querySelector('.row-num-cell');
            if (numCell) numCell.innerText = idx + 1;
        });
        const badge = document.getElementById('lblRowsCountBadge');
        if (badge) badge.innerText = `${rows.length} Sales`;
    }

    /* ── Remove Row ── */
    function removeSaleRow(rIdx) {
        const row = document.getElementById(`daySaleRow_${rIdx}`);
        if (!row) return;

        const tbody = document.getElementById('daySalesRowsTbody');
        if (tbody.children.length <= 1) {
            // Keep at least 1 row clear
            row.querySelector('.cust-name-input').value = '';
            row.querySelector('.cust-phone-input').value = '';
            row.querySelector('.item-search-input').value = '';
            row.querySelector('.item-part-id').value = '0';
            row.querySelector('.item-device-id').value = '0';
            row.querySelector('.item-type').value = 'accessory';
            row.querySelector('.item-qty-input').value = '1';
            row.querySelector('.item-price-input').value = '0.00';
            row.querySelector('.item-note-input').value = '';
            recalcRowTotal(rIdx);
            return;
        }

        row.remove();
        renumberTableRows();
        recalcAllSummaryMetrics();
    }

    /* ── Recalculate Row Total ── */
    function recalcRowTotal(rIdx) {
        const row = document.getElementById(`daySaleRow_${rIdx}`);
        if (!row) return;

        const qty = parseInt(row.querySelector('.item-qty-input')?.value || 1);
        const price = parseFloat(row.querySelector('.item-price-input')?.value || 0);
        const total = (qty > 0 && price >= 0) ? (qty * price) : 0;

        const totalDisplay = row.querySelector('.item-total-display');
        if (totalDisplay) {
            totalDisplay.innerText = `₹${total.toFixed(2)}`;
        }

        recalcAllSummaryMetrics();
    }

    /* ── Recalculate Summary Stats ── */
    function recalcAllSummaryMetrics() {
        const rows = document.querySelectorAll('#daySalesRowsTbody .day-sale-row');
        let billsCount = 0;
        let phonesCount = 0;
        let accCount = 0;
        let grandTotal = 0;
        let cashTotal = 0;
        let upiTotal = 0;
        let emiTotal = 0;
        let khataTotal = 0;

        rows.forEach(r => {
            const qty = parseInt(r.querySelector('.item-qty-input')?.value || 0);
            const price = parseFloat(r.querySelector('.item-price-input')?.value || 0);
            const itemType = r.querySelector('.item-type')?.value || 'accessory';
            const payMode = r.querySelector('.item-payment-select')?.value || 'cash';
            const itemName = (r.querySelector('.item-search-input')?.value || '').trim();

            const rowTotal = (qty > 0 && price > 0) ? (qty * price) : 0;

            if (itemName || price > 0) {
                billsCount++;
                if (itemType === 'phone') {
                    phonesCount += qty;
                } else {
                    accCount += qty;
                }

                grandTotal += rowTotal;

                if (payMode === 'cash') cashTotal += rowTotal;
                else if (payMode === 'upi') upiTotal += rowTotal;
                else if (payMode === 'emi') emiTotal += rowTotal;
                else if (payMode === 'udhari') khataTotal += rowTotal;
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
        const selects = document.querySelectorAll('.item-payment-select');
        selects.forEach(s => {
            s.value = newMode;
        });
        recalcAllSummaryMetrics();
    }

    /* ── Autocomplete Customer Helpers ── */
    function onCustNameInput(rIdx, input) {
        const row = document.getElementById(`daySaleRow_${rIdx}`);
        if (!row) return;
        const val = (input.value || '').trim().toLowerCase();
        if (!val) return;

        const match = customersData.find(c => (c.name || '').toLowerCase() === val);
        if (match && match.phone) {
            const phoneInput = row.querySelector('.cust-phone-input');
            if (phoneInput && !phoneInput.value) {
                phoneInput.value = match.phone;
            }
        }
    }

    function onCustPhoneInput(rIdx, input) {
        const row = document.getElementById(`daySaleRow_${rIdx}`);
        if (!row) return;
        const val = (input.value || '').trim();
        if (!val) return;

        const match = customersData.find(c => (c.phone || '').trim() === val);
        if (match && match.name) {
            const nameInput = row.querySelector('.cust-name-input');
            if (nameInput && !nameInput.value) {
                nameInput.value = match.name;
            }
        }
    }

    /* ── Item Search & Dropdown Picker ── */
    function onItemInputFocus(rIdx, input) {
        onItemInputSearch(rIdx, input);
    }

    function onItemInputSearch(rIdx, input) {
        const dropdown = document.getElementById(`itemSearchDropdown_${rIdx}`);
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
                    <div class="item-search-item" onclick="onPickPhoneItem(${rIdx}, ${ph.id}, '${escapeHtml(title)}', ${price})">
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
                    <div class="item-search-item" onclick="onPickPartItem(${rIdx}, ${pt.id}, '${escapeHtml(pt.name)}', ${price})">
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

    function onPickPartItem(rIdx, partId, partName, price) {
        const row = document.getElementById(`daySaleRow_${rIdx}`);
        if (!row) return;

        row.querySelector('.item-search-input').value = partName;
        row.querySelector('.item-part-id').value = partId;
        row.querySelector('.item-device-id').value = 0;
        row.querySelector('.item-type').value = 'accessory';
        row.querySelector('.item-price-input').value = price.toFixed(2);

        document.getElementById(`itemSearchDropdown_${rIdx}`).style.display = 'none';
        recalcRowTotal(rIdx);
    }

    function onPickPhoneItem(rIdx, deviceId, phoneTitle, price) {
        const row = document.getElementById(`daySaleRow_${rIdx}`);
        if (!row) return;

        row.querySelector('.item-search-input').value = phoneTitle;
        row.querySelector('.item-part-id').value = 0;
        row.querySelector('.item-device-id').value = deviceId;
        row.querySelector('.item-type').value = 'phone';
        row.querySelector('.item-price-input').value = price.toFixed(2);

        document.getElementById(`itemSearchDropdown_${rIdx}`).style.display = 'none';
        recalcRowTotal(rIdx);
    }

    // Close dropdown on outside click
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.item-search-input') && !e.target.closest('.item-search-dropdown')) {
            document.querySelectorAll('.item-search-dropdown').forEach(d => d.style.display = 'none');
        }
    });

    /* ── Keyboard Shortcut: Ctrl + Enter to add new row ── */
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            e.preventDefault();
            addSaleRow();
            // Focus on newly added row's item search
            setTimeout(() => {
                const rows = document.querySelectorAll('#daySalesRowsTbody .day-sale-row');
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
        const rows = document.querySelectorAll('#daySalesRowsTbody .day-sale-row');

        const validSalesPayload = [];

        for (let i = 0; i < rows.length; i++) {
            const r = rows[i];
            const num = i + 1;
            const custName = (r.querySelector('.cust-name-input')?.value || '').trim() || 'Walk-in Customer';
            const custPhone = (r.querySelector('.cust-phone-input')?.value || '').trim() || '9999999999';
            const itemName = (r.querySelector('.item-search-input')?.value || '').trim();
            const partId = parseInt(r.querySelector('.item-part-id')?.value || 0);
            const deviceId = parseInt(r.querySelector('.item-device-id')?.value || 0);
            const itemType = r.querySelector('.item-type')?.value || 'accessory';
            const qty = parseInt(r.querySelector('.item-qty-input')?.value || 0);
            const price = parseFloat(r.querySelector('.item-price-input')?.value || 0);
            const paymentMode = r.querySelector('.item-payment-select')?.value || 'cash';
            const refNote = (r.querySelector('.item-note-input')?.value || '').trim();

            // Skip completely empty blank rows
            if (!itemName && price <= 0 && partId === 0 && deviceId === 0) {
                continue;
            }

            if (!itemName && partId === 0 && deviceId === 0) {
                showPageWarning(`Row #${num}: Please enter or search an item name.`, r.querySelector('.item-search-input'));
                return;
            }

            if (isNaN(qty) || qty < 1) {
                showPageWarning(`Row #${num}: Quantity must be at least 1.`, r.querySelector('.item-qty-input'));
                return;
            }

            if (isNaN(price) || price < 0) {
                showPageWarning(`Row #${num}: Price cannot be negative.`, r.querySelector('.item-price-input'));
                return;
            }

            validSalesPayload.push({
                customer_name: custName,
                customer_phone: custPhone,
                item_name: itemName,
                item_type: itemType,
                part_id: partId,
                device_id: deviceId,
                quantity: qty,
                unit_price: price,
                payment_mode: paymentMode,
                ref_note: refNote,
            });
        }

        if (validSalesPayload.length === 0) {
            showPageWarning('Please enter at least one sale item before saving.');
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

    // Initialize with 3 rows on start (matching user screenshot)
    document.addEventListener('DOMContentLoaded', () => {
        addSaleRow();
        addSaleRow();
        addSaleRow();
    });
</script>
<style>
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endpush
