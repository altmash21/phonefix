@extends('mobileshop.layout')

@section('title', 'Restock Accessories & Spare Parts — PhoneFix Azamgarh')
@section('page-title', 'Restock Accessories & Spare Parts')

@section('page-actions')
    <div style="display:flex; gap:8px; align-items:center;">
        <a href="{{ route('mobileshop.purchase') }}" class="btn btn-outline btn-sm">
            <i data-lucide="arrow-left" style="width:13px;height:13px;"></i> Back to Purchase Hub
        </a>
        <a href="{{ route('mobileshop.stock') }}" class="btn btn-outline btn-sm">
            <i data-lucide="package" style="width:13px;height:13px;"></i> Inventory Stock
        </a>
    </div>
@endsection

@section('content')
@include('mobileshop.partials.accessories_purchase_styles')

<div class="restock-container">

    @if(session('success'))
        <div class="flash-success" style="border-radius:10px; padding: 12px 18px; display:flex; align-items:center; gap:10px; background:#ECFDF5; border:1px solid #A7F3D0; color:#065F46; font-weight:600;">
            <i data-lucide="check-circle-2" style="width:18px;height:18px; flex-shrink:0;"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flash-error" style="border-radius:10px; padding: 12px 18px; display:flex; align-items:center; gap:10px; background:#FEF2F2; border:1px solid #FECACA; color:#991B1B; font-weight:600;">
            <i data-lucide="alert-circle" style="width:18px;height:18px; flex-shrink:0;"></i> {{ session('error') }}
        </div>
    @endif
    @if(isset($errors) && $errors->any())
        <div class="flash-error" style="border-radius:10px; padding: 14px 18px; margin-bottom: 12px; background:#FEF2F2; border:1.5px solid #FCA5A5; color:#991B1B;">
            <div style="display:flex; align-items:center; gap:8px; font-weight:800; font-size:13px; margin-bottom:6px;">
                <i data-lucide="alert-triangle" style="width:18px;height:18px; color:#DC2626; flex-shrink:0;"></i>
                <span>Cannot Process Restock — Please correct the following:</span>
            </div>
            <ul style="margin:0; padding-left:22px; font-size:12px; line-height:1.6; font-weight:600;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- AI INVOICE SCANNER — COMPACT OCR TOOLBAR -->
    <!-- ══════════════════════════════════════════════════════════ -->
    <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap; padding:9px 14px; background:#F5F3FF; border:1px solid #DDD6FE; border-radius:8px;">
        <i data-lucide="sparkles" style="width:15px;height:15px; color:#7C3AED; flex-shrink:0;"></i>
        <span style="font-size:12px; font-weight:700; color:#4C1D95; flex:1; min-width:160px;">AI Invoice OCR &mdash; Scan wholesale vendor paper invoice to auto-fill items</span>
        <input type="file" id="invoiceFileInput" accept="image/*" style="display:none;" onchange="handleInvoiceFile(this.files[0])">
        <button type="button" onclick="document.getElementById('invoiceFileInput').click()" class="btn btn-sm" style="background:#6D28D9; color:#fff; border:none; font-weight:700; padding:5px 12px; border-radius:6px; font-size:11.5px; display:inline-flex; align-items:center; gap:5px;">
            <i data-lucide="camera" style="width:13px;height:13px;"></i> Scan Bill
        </button>
        <button type="button" onclick="loadSampleInvoiceData()" class="btn btn-sm btn-outline" style="font-weight:700; font-size:11.5px; padding:5px 10px; border-radius:6px; color:#6D28D9; border-color:#DDD6FE;">
            Demo Data
        </button>
        <!-- Inline OCR Progress -->
        <div id="ocrProgressBox" style="display:none; width:100%; margin-top:6px;">
            <div style="display:flex; justify-content:space-between; font-size:11px; font-weight:700; color:#5B21B6; margin-bottom:3px;">
                <span id="ocrStatusText">🔍 OCR running...</span>
                <span id="ocrPercentText" style="font-family:monospace;">0%</span>
            </div>
            <div style="width:100%; height:4px; background:#EDE9FE; border-radius:4px; overflow:hidden;">
                <div id="ocrProgressBar" style="width:0%; height:100%; background:linear-gradient(90deg,#7C3AED,#5E6AD2); transition:width 0.25s ease;"></div>
            </div>
        </div>
    </div>

    <!-- ════ QUICK EDIT INVENTORY STOCK MODAL (PURCHASE INTAKE) ════ -->
    @include('mobileshop.partials.accessories_purchase_quick_edit_modal')

    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- BATCH RESTOCK FORM -->
    <!-- ══════════════════════════════════════════════════════════ -->
    <form action="{{ route('mobileshop.accessories.bulk_restock') }}" method="POST" id="bulkRestockForm" onsubmit="if(typeof MT !== 'undefined'){ MT.enqueue('create', 'ms_parts_inventory_history', {source:'bulkRestockForm', action:'bulkRestock'}); } return validateAndSubmitBulkRestock(this);">
        @csrf

        <!-- ─── SUPPLIER & SHIPMENT METADATA CARD ─── -->
        <div class="card" style="margin-bottom: 12px; border-radius:8px; border:1px solid #E2E8F0; background:#FFFFFF;">
            <div class="card-header" style="border-bottom: 1px solid #E2E8F0; padding: 7px 12px; background:#FAFAFA;">
                <div class="card-title" style="display:flex; align-items:center; gap:6px; font-size:12px; font-weight:700; color:#1E293B;">
                    <i data-lucide="truck" style="width:14px;height:14px; color:var(--color-primary);"></i> Supplier & Shipment Details
                </div>
            </div>
            <div class="card-body" style="padding: 10px 12px;">
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap:10px;">
                    <div>
                        <label class="mobile-card-label" style="display:block;">Supplier / Distributor <span style="color:#EF4444;">*</span></label>
                        <input type="text" name="supplier_name" id="bulkSupplierName" list="suppliersList" placeholder="Supplier" class="restock-input" required oninput="onSupplierSelected()" onchange="onSupplierSelected()">
                        <datalist id="suppliersList">
                            @if(isset($suppliers))
                                @foreach($suppliers as $s)
                                    <option value="{{ $s->name }}">{{ $s->name }} @if($s->phone)({{ $s->phone }})@endif @if(!empty($s->outstanding_balance) && $s->outstanding_balance > 0)[Due: ₹{{ number_format($s->outstanding_balance, 2) }}]@endif</option>
                                @endforeach
                            @endif
                        </datalist>
                        <div id="supplierLedgerInfo" style="display:none; margin-top:6px; padding:6px 10px; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:6px; font-size:11.5px;"></div>
                    </div>

                    <div>
                        <label class="mobile-card-label" style="display:block;">Invoice / PO #</label>
                        <input type="text" name="invoice_no" id="bulkInvoiceNo" placeholder="Invoice#" class="restock-input num-field">
                    </div>

                    <div>
                        <label class="mobile-card-label" style="display:block;">Bill Type <span style="color:#EF4444;">*</span></label>
                        <select name="bill_type" required class="restock-input">
                            <option value="gst">📜 GST Tax Invoice (18% incl.)</option>
                            <option value="non_gst">📄 Estimate / Cash Slip (0% Tax)</option>
                        </select>
                    </div>

                    <div>
                        <label class="mobile-card-label" style="display:block;">Intake Date</label>
                        <input type="date" name="order_date" class="restock-input" value="{{ now()->toDateString() }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- ─── BATCH ITEMS CARD ─── -->
        <div class="card" style="border-radius:8px; border:1px solid #E2E8F0; background:#FFFFFF; overflow:visible; margin-bottom:12px;">
            <!-- Header Toolbar -->
            <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px; border-bottom:1px solid #E2E8F0; padding:8px 14px; background:#FAFAFA;">
                <div style="display:flex; align-items:center; gap:8px; font-size:12px; font-weight:700; color:#1E293B; flex-wrap:wrap;">
                    <div style="display:flex; align-items:center; gap:6px;">
                        <i data-lucide="layers" style="width:14px;height:14px; color:var(--color-primary);"></i>
                        Batch Items &mdash; <span id="bulkRowCount" style="color:var(--color-primary); font-weight:800;">0</span> rows
                    </div>
                    <div id="batchHealthSummary" style="display:inline-flex; align-items:center;"></div>
                </div>
                <div style="display:flex; align-items:center; gap:6px;">
                    <button type="button" onclick="addBulkRowAndFocus()" class="btn btn-primary btn-sm" style="font-weight:700; font-size:11.5px; padding:4px 10px; border-radius:6px;">
                        <i data-lucide="plus" style="width:12px;height:12px;"></i> Add Row
                    </button>
                    <button type="button" onclick="addMultipleRows(5)" class="btn btn-outline btn-sm" style="font-weight:700; font-size:11.5px; padding:4px 9px; border-radius:6px;">
                        +5 Rows
                    </button>
                    <button type="button" onclick="clearAllRows()" class="btn btn-outline btn-sm" style="color:#DC2626; border-color:#FECACA; font-weight:700; font-size:11.5px; padding:4px 9px; border-radius:6px;">
                        <i data-lucide="trash-2" style="width:12px;height:12px;"></i> Clear
                    </button>
                </div>
            </div>

            <!-- 1. DESKTOP ALIGNED TABLE HEADER -->
            <div class="batch-table-header batch-grid-row" id="batchTableHeader">
                <div>Category (Search) <span style="color:#EF4444;">*</span></div>
                <div>Item &amp; Current Stock <span style="color:#EF4444;">*</span></div>
                <div>Brand</div>
                <div>Description / Specs</div>
                <div style="text-align:center;">Qty <span style="color:#EF4444;">*</span></div>
                <div style="text-align:right;">Cost ₹ <span style="color:#EF4444;">*</span></div>
                <div style="text-align:right;">Sell ₹ <span style="color:#EF4444;">*</span></div>
                <div style="text-align:center;">Alert</div>
                <div style="text-align:right;">Total</div>
                <div style="text-align:center;">Action</div>
            </div>

            <!-- Autocomplete datalists -->
            <datalist id="bulkBrandsDatalist">
                @if(isset($knownBrands))
                    @foreach($knownBrands as $b)<option value="{{ $b }}">{{ $b }}</option>@endforeach
                @endif
                <option value="Samsung">Samsung</option>
                <option value="Apple">Apple / iPhone</option>
                <option value="Xiaomi">Xiaomi / Redmi / POCO</option>
                <option value="Vivo">Vivo / iQOO</option>
                <option value="Oppo">Oppo</option>
                <option value="Realme">Realme</option>
                <option value="OnePlus">OnePlus</option>
                <option value="Motorola">Motorola</option>
                <option value="Google">Google Pixel</option>
                <option value="Universal">Universal / All Brands</option>
            </datalist>
            <datalist id="bulkModelsDatalist">
                @if(isset($knownModels))
                    @foreach($knownModels as $m)<option value="{{ $m }}">{{ $m }}</option>@endforeach
                @endif
                <option value="Universal">Universal / All Models</option>
            </datalist>

            <!-- 2. BATCH ROWS CONTAINER -->
            <div id="bulkTableBody" class="batch-items-container">
                <!-- Rows injected dynamically by JS -->
            </div>

            <!-- Mobile-only Add Row Bar below cards -->
            <div class="mobile-add-row-bar" style="display:none; padding:10px 14px; background:#F8FAFC; border-top:1px dashed #E2E8F0;">
                <button type="button" onclick="addBulkRowAndFocus()" class="btn btn-outline" style="width:100%; height:42px; display:inline-flex; align-items:center; justify-content:center; gap:8px; font-weight:700; font-size:13px; border-radius:8px; border:1.5px dashed #6366F1; color:#4F46E5; background:#EEF2FF;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>+ Add Another Item Row</span>
                </button>
            </div>
        </div>

        <!-- ─── DESKTOP SUMMARY & SUPPLIER SETTLEMENT CARD ─── -->
        <div class="card desktop-summary-card" style="border:1px solid #E2E8F0; background:#FFFFFF; border-radius:8px; box-shadow:0 1px 3px rgba(0,0,0,0.03); margin-bottom:20px;">
            <div class="card-body" style="padding:12px 16px;">
                <!-- Row 1: Metrics & Totals -->
                <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px; padding-bottom:12px; border-bottom:1px solid #F1F5F9;">
                    <div style="display:flex; align-items:center; gap:20px; flex-wrap:wrap;">
                        <div>
                            <span style="font-size:10px; color:#64748B; text-transform:uppercase; font-weight:700; letter-spacing:0.4px;">Items</span>
                            <div style="font-size:15px; font-weight:800; color:#0F172A;" id="lblBulkLineCount">0 lines</div>
                        </div>
                        <div style="border-left:1px solid #E2E8F0; padding-left:20px;">
                            <span style="font-size:10px; color:#64748B; text-transform:uppercase; font-weight:700; letter-spacing:0.4px;">Units</span>
                            <div style="font-size:15px; font-weight:800; color:#0F172A;" id="lblBulkTotalUnits">0 units</div>
                        </div>
                        <div style="border-left:1px solid #E2E8F0; padding-left:20px;">
                            <span style="font-size:10px; color:#64748B; text-transform:uppercase; font-weight:700; letter-spacing:0.4px;">Invoice Cost</span>
                            <div style="font-size:18px; font-weight:900; color:#4F46E5; font-family:'JetBrains Mono', monospace;" id="lblBulkTotalCost">₹0.00</div>
                        </div>
                        <div style="border-left:1px solid #E2E8F0; padding-left:20px;">
                            <span style="font-size:10px; color:#15803D; text-transform:uppercase; font-weight:700; letter-spacing:0.4px;">Total Sell Value</span>
                            <div style="font-size:16px; font-weight:900; color:#15803D; font-family:'JetBrains Mono', monospace;" id="lblBulkTotalSellValue">₹0.00</div>
                        </div>
                        <div id="supplierDueSummaryBlock" style="display:none; border-left:1px solid #E2E8F0; padding-left:20px;">
                            <span style="font-size:10px; color:#DC2626; text-transform:uppercase; font-weight:700; letter-spacing:0.4px;">Supplier Past Due</span>
                            <div style="font-size:16px; font-weight:800; color:#DC2626; font-family:'JetBrains Mono', monospace;" id="lblSupplierPastDue">₹0.00</div>
                        </div>
                    </div>

                    <div style="display:flex; align-items:center; gap:8px;">
                        <a href="{{ route('mobileshop.purchase') }}" class="btn btn-outline btn-sm" style="font-weight:700; padding:7px 14px; border-radius:6px; font-size:12px;">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary btn-sm" id="btnSubmitBulkRestock" style="background:#5E6AD2; border-color:#5E6AD2; font-weight:800; padding:8px 20px; font-size:12px; border-radius:6px; box-shadow:0 2px 6px rgba(94, 106, 210, 0.3);">
                            <i data-lucide="check-circle-2" style="width:14px;height:14px;"></i> Purchase Accessories &amp; Settle
                        </button>
                    </div>
                </div>

                <!-- Custom Price / Bill Negotiation Option -->
                <div style="margin-top:10px; padding:8px 12px; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:6px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                        <span style="font-size:11px; font-weight:800; text-transform:uppercase; color:#475569;">Purchase Bill Price:</span>
                        <div style="display:inline-flex; gap:4px;">
                            <button type="button" class="btn btn-outline btn-xs" id="btnPriceModeAuto" onclick="setBulkPriceMode('auto')" style="font-size:11px; font-weight:700; padding:3px 10px; border-radius:5px; background:#EEF2FF; color:#4F46E5; border-color:#C7D2FE;">
                                Items Total (<span id="lblAutoTotalTag">₹0</span>)
                            </button>
                            <button type="button" class="btn btn-outline btn-xs" id="btnPriceModeCustom" onclick="setBulkPriceMode('custom')" style="font-size:11px; font-weight:700; padding:3px 10px; border-radius:5px; color:#475569;">
                                Custom Price
                            </button>
                        </div>
                    </div>

                    <!-- Custom Price Input Field -->
                    <div id="bulkCustomPriceWrap" style="display:none; align-items:center; gap:8px;">
                        <label for="bulkCustomTotalCost" style="font-size:11px; font-weight:700; color:#334155; margin:0;">Set Custom Bill (₹):</label>
                        <div style="position:relative;">
                            <span style="position:absolute; left:8px; top:50%; transform:translateY(-50%); font-weight:700; color:#64748B; font-size:12px;">₹</span>
                            <input type="number" step="0.01" min="0" name="custom_total_cost" id="bulkCustomTotalCost"
                                placeholder="Amount"
                                class="restock-input num-field"
                                style="width:130px; padding:3px 8px 3px 22px !important; font-size:13px !important; font-weight:800 !important; color:#4F46E5 !important; height:28px;"
                                oninput="onCustomPriceChanged(this.value)">
                        </div>
                        <button type="button" onclick="resetCustomPriceToAuto()" class="btn btn-outline btn-xs" style="font-size:10.5px; font-weight:700; padding:3px 8px;" title="Reset to total of all items">
                            Reset to Items Total
                        </button>
                    </div>

                    <div style="font-size:11.5px; color:#15803D; font-weight:700;">
                        Can be sold for: <span id="lblBulkRetailTotal" style="font-weight:900; font-family:'JetBrains Mono', monospace;">₹0</span>
                    </div>
                </div>

                <!-- Live Supplier Debt & Settlement Breakdown -->
                <div id="supplierDebtBreakdownBox" style="margin-top:12px; padding:10px 14px; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px;">
                    <div style="font-size:11px; font-weight:800; text-transform:uppercase; color:#64748B; margin-bottom:6px; display:flex; align-items:center; gap:6px;">
                        <i data-lucide="calculator" style="width:13px;height:13px; color:#4F46E5;"></i>
                        Supplier Debt &amp; Payment Ledger
                    </div>
                    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap:8px;">
                        <div style="background:#FFFFFF; border:1px solid #CBD5E1; border-radius:6px; padding:6px 10px;">
                            <div style="font-size:10px; font-weight:700; color:#64748B;">Previous Debt</div>
                            <div id="valSupplierPastDebt" style="font-size:13px; font-weight:800; color:#DC2626;">₹0.00</div>
                        </div>
                        <div style="background:#FFFFFF; border:1px solid #CBD5E1; border-radius:6px; padding:6px 10px;">
                            <div style="font-size:10px; font-weight:700; color:#64748B;">Current Bill</div>
                            <div id="valCurrentBillAmt" style="font-size:13px; font-weight:800; color:#0F172A;">₹0.00</div>
                        </div>
                        <div style="background:#FFFFFF; border:1px solid #CBD5E1; border-radius:6px; padding:6px 10px;">
                            <div style="font-size:10px; font-weight:700; color:#64748B;">Total Due</div>
                            <div id="valTotalPayable" style="font-size:13px; font-weight:800; color:#4338CA;">₹0.00</div>
                        </div>
                        <div style="background:#FFFFFF; border:1px solid #CBD5E1; border-radius:6px; padding:6px 10px;">
                            <div style="font-size:10px; font-weight:700; color:#64748B;">Amount Paid</div>
                            <div id="valAmountPaidDisplay" style="font-size:13px; font-weight:800; color:#16A34A;">₹0.00</div>
                        </div>
                        <div style="background:#FFF1F2; border:1px solid #FECDD3; border-radius:6px; padding:6px 10px;">
                            <div style="font-size:10px; font-weight:700; color:#991B1B;">Remaining Debt</div>
                            <div id="valSupplierRemainingDebt" style="font-size:13px; font-weight:900; color:#DC2626;">₹0.00</div>
                        </div>
                    </div>
                </div>

                <!-- Row 2: Amount Submitted / Paid to Settle Supplier -->
                <div style="margin-top:12px; display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:12px; align-items:center;">
                    <div>
                        <label style="font-size:11px; font-weight:700; color:#334155; margin-bottom:4px; display:flex; align-items:center; justify-content:space-between;">
                            <span>Amount Paid (₹)</span>
                            <span style="font-size:10.5px; font-weight:600; color:#64748B;">Clear balance or pay invoice</span>
                        </label>
                        <div style="position:relative;">
                            <span style="position:absolute; left:10px; top:50%; transform:translateY(-50%); font-weight:700; color:#64748B; font-family:'JetBrains Mono', monospace;">₹</span>
                            <input type="number" step="0.01" min="0" name="amount_paid" id="bulkAmountPaid"
                                class="restock-input num-field"
                                style="padding-left:26px !important; font-size:14px !important; font-weight:800 !important; color:#0F172A !important;"
                                placeholder="Amount" oninput="onAmountPaidChanged()">
                        </div>
                    </div>

                    <div>
                        <label style="font-size:11px; font-weight:700; color:#334155; margin-bottom:4px; display:block;">
                            Payment Mode
                        </label>
                        <select name="payment_mode" id="bulkPaymentMode" class="restock-input" style="font-size:12.5px !important; font-weight:700 !important;">
                            <option value="cash">💵 Cash Payment</option>
                            <option value="upi">📱 UPI / QR Transfer</option>
                            <option value="bank_transfer">🏦 Bank Transfer (NEFT/IMPS)</option>
                            <option value="cheque">📝 Cheque</option>
                        </select>
                    </div>

                    <div style="grid-column: 1 / -1; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px;">
                        <!-- Quick settlement preset buttons -->
                        <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                            <span style="font-size:10.5px; font-weight:700; color:#64748B; text-transform:uppercase;">Quick Pay:</span>
                            <button type="button" class="btn btn-outline btn-xs" onclick="setBulkAmountPaid('invoice')" style="font-size:11px; font-weight:700; padding:3px 8px; border-radius:5px;" id="btnQuickPayInvoice">
                                Pay Bill (<span id="btnLblBillAmt">₹0</span>)
                            </button>
                            <button type="button" class="btn btn-outline btn-xs" id="btnQuickClearAll" onclick="setBulkAmountPaid('clear_all')" style="display:none; font-size:11px; font-weight:700; padding:3px 8px; border-radius:5px; color:#059669; border-color:#6EE7B7; background:#ECFDF5;">
                                🎯 Clear Out All Balance (<span id="btnLblClearAll">₹0</span>)
                            </button>
                            <button type="button" class="btn btn-outline btn-xs" onclick="setBulkAmountPaid('zero')" style="font-size:11px; font-weight:700; padding:3px 8px; border-radius:5px; color:#64748B;">
                                Unpaid / Credit (₹0)
                            </button>
                        </div>

                        <!-- Live settlement feedback pill -->
                        <div id="bulkSettlementPill" style="font-size:11.5px; font-weight:700; padding:4px 10px; border-radius:6px; background:#F1F5F9; color:#475569;">
                            Enter amount submitted to update supplier ledger
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ─── MOBILE STICKY BOTTOM DOCK ─── -->
        <div class="mobile-sticky-footer" id="mobileStickyFooter" style="display:none;">
            <div style="flex:1; min-width:0;">
                <div style="font-size:9.5px; font-weight:700; color:#64748B; text-transform:uppercase; letter-spacing:0.3px;" id="mobileStickyCount">0 ITEMS &bull; 0 UNITS</div>
                <div style="font-size:16px; font-weight:900; color:#4F46E5; font-family:'JetBrains Mono', monospace; line-height:1.2;" id="mobileStickyTotal">₹0.00</div>
                <div style="font-size:10px; font-weight:700; color:#059669;" id="mobileStickySettlement">Paid: ₹0.00</div>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
                <button type="button" onclick="addBulkRowAndFocus()" class="btn btn-outline" style="height:44px; width:44px; padding:0; display:inline-flex; align-items:center; justify-content:center; border-radius:8px; border:1.5px solid #6366F1; color:#4F46E5; background:#EEF2FF; flex-shrink:0;" title="Add Row (+)">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                </button>
                <button type="submit" class="btn btn-primary" id="btnMobileSubmitRestock" style="height:44px; padding:0 16px; font-size:13px; font-weight:800; border-radius:8px; display:inline-flex; align-items:center; gap:6px; box-shadow:0 2px 8px rgba(94, 106, 210, 0.35); flex-shrink:0;">
                    <i data-lucide="check-circle-2" style="width:16px;height:16px;"></i> Purchase &amp; Settle
                </button>
            </div>
        </div>

    </form>

</div>
@endsection

@push('scripts')
    @include('mobileshop.partials.accessories_purchase_scripts')
@endpush
