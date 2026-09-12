@extends('mobileshop.layout')

@section('title', 'Register Purchase — Bulk Stock Inward — Maurya Mobile')
@section('page-title', 'Register Purchase / Stock Inward')

@section('page-actions')
    <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
        <input type="file" id="purchaseBillFileInput" accept="image/*,application/pdf" style="display:none;" onchange="handlePurchaseOcrFile(this)">
        <button type="button" class="btn btn-sm" onclick="document.getElementById('purchaseBillFileInput').click()" style="background:linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%); color:#fff; border:none; font-weight:700; font-size:12px; padding:6px 14px; border-radius:6px; display:inline-flex; align-items:center; gap:6px; box-shadow:0 2px 8px rgba(99,102,241,0.35); cursor:pointer;">
            <i data-lucide="sparkles" style="width:14px;height:14px;"></i> AI Scan Vendor Bill (Auto-Fill)
        </button>
        <a href="{{ route('mobileshop.purchase') }}" class="btn btn-outline btn-sm" style="font-weight:700; font-size:12px; padding:5px 12px;">
            <i data-lucide="arrow-left" style="width:13px;height:13px;"></i> Back to Purchase
        </a>
    </div>
@endsection

@section('content')
<div class="purchase-create-wrapper" style="max-width: 1200px; margin: 0 auto;">

    <!-- ─── AI SCAN BANNER ─── -->
    <div style="margin-bottom:12px; background:linear-gradient(135deg, #EEF2FF 0%, #FAF5FF 100%); border:1px solid #C7D2FE; border-radius:8px; padding:8px 14px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
        <div style="display:flex; align-items:center; gap:8px;">
            <span style="font-size:12px; font-weight:800; color:#3730A3; display:flex; align-items:center; gap:6px;">
                <i data-lucide="sparkles" style="width:15px; height:15px; color:#6366F1;"></i>
                AI Vendor Invoice & Challan Scanner
                <span style="background:#4F46E5; color:#fff; font-size:9px; padding:1.5px 6px; border-radius:4px; font-weight:700;">Gemini 2.0</span>
            </span>
            <span style="font-size:11px; color:#4338CA;" class="hide-on-mobile">— Snap or upload distributor invoice to auto-extract supplier, phones, IMEIs & accessories</span>
        </div>
        <div>
            <button type="button" onclick="document.getElementById('purchaseBillFileInput').click()" style="background:#4F46E5; color:#fff; border:none; font-weight:700; font-size:11.5px; padding:4px 12px; border-radius:6px; display:inline-flex; align-items:center; gap:5px; cursor:pointer;">
                <i data-lucide="upload-cloud" style="width:13px; height:13px;"></i> Upload / Camera Photo
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="flash-success" style="border-radius:8px; margin-bottom:12px; padding:8px 14px; font-size:12.5px;">
            <i data-lucide="check-circle-2" style="width:16px;height:16px;"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flash-error" style="border-radius:8px; margin-bottom:12px; padding:8px 14px; font-size:12.5px;">
            <i data-lucide="alert-circle" style="width:16px;height:16px;"></i> {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('mobileshop.purchase.store_bulk') }}" id="bulkPurchaseForm">
        @csrf

        <!-- ─── SUPPLIER SECTION ─── -->
        <div class="card" style="margin-bottom:10px; border-radius:8px; border:1px solid #E2E8F0;">
            <div class="card-header" style="padding:7px 12px; background:#FAFAFA; border-bottom:1px solid #E2E8F0;">
                <div>
                    <div class="card-title" style="font-size:12px; font-weight:700; display:flex; align-items:center; gap:6px;">
                        <i data-lucide="truck" style="width:14px;height:14px; color:var(--color-primary);"></i> Supplier & Shipment Details
                    </div>
                </div>
            </div>
            <div class="card-body" style="padding:10px 12px;">
                <div class="form-row" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:10px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" style="font-size:10px; font-weight:700; color:#64748B; text-transform:uppercase; letter-spacing:0.3px; margin-bottom:2px;">Existing Supplier</label>
                        <select class="form-control" name="supplier_id" id="supplierSelect" onchange="onSupplierChange()" style="height:32px; font-size:12px; padding:4px 8px; border-radius:6px;">
                            <option value="">— Add New Supplier —</option>
                            @foreach($suppliers as $s)
                                <option value="{{ $s->id }}"
                                        data-outstanding="{{ $s->outstanding_balance }}"
                                        data-advance="{{ $s->advance_balance }}">
                                    {{ $s->name }} @if($s->phone) ({{ $s->phone }}) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" id="newSupplierFields" style="display:none; margin-bottom:0;">
                        <label class="form-label" style="font-size:10px; font-weight:700; color:#64748B; text-transform:uppercase; letter-spacing:0.3px; margin-bottom:2px;">New Supplier Name <span style="color:#EF4444;">*</span></label>
                        <input type="text" class="form-control" name="new_supplier_name" placeholder="e.g. Samsung Distributors" style="height:32px; font-size:12px; padding:4px 8px; border-radius:6px;">
                    </div>
                    <div class="form-group" id="newSupplierPhone" style="display:none; margin-bottom:0;">
                        <label class="form-label" style="font-size:10px; font-weight:700; color:#64748B; text-transform:uppercase; letter-spacing:0.3px; margin-bottom:2px;">Supplier Phone</label>
                        <input type="text" class="form-control" name="new_supplier_phone" inputmode="tel" placeholder="10-digit mobile" style="height:32px; font-size:12px; padding:4px 8px; border-radius:6px;">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" style="font-size:10px; font-weight:700; color:#64748B; text-transform:uppercase; letter-spacing:0.3px; margin-bottom:2px;">Supplier Invoice No.</label>
                        <input type="text" class="form-control" name="supplier_invoice_no" placeholder="e.g. INV-2026-0871" style="height:32px; font-size:12px; padding:4px 8px; border-radius:6px; font-family:monospace; font-weight:700;">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" style="font-size:10px; font-weight:700; color:#64748B; text-transform:uppercase; letter-spacing:0.3px; margin-bottom:2px;">Invoice Date <span style="color:#EF4444;">*</span></label>
                        <input type="date" class="form-control" name="order_date" value="{{ now()->toDateString() }}" required style="height:32px; font-size:12px; padding:4px 8px; border-radius:6px; font-weight:600;">
                    </div>
                </div>

                <!-- Supplier Balance Panel (Compact Stat Strip) -->
                <div id="supplierBalancePanel" style="display:none; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:6px; padding:8px 14px; margin-top:8px;">
                    <div style="display:flex; gap:24px; flex-wrap:wrap; align-items:center;">
                        <div>
                            <div style="font-size:9.5px; font-weight:700; text-transform:uppercase; letter-spacing:0.4px; color:#64748B;">Previous Balance Due</div>
                            <div style="font-size:15px; font-weight:900; color:#DC2626; font-family:'JetBrains Mono', monospace;" id="supplierOutstanding">₹0.00</div>
                        </div>
                        <div style="border-left:1px solid #E2E8F0; padding-left:24px;">
                            <div style="font-size:9.5px; font-weight:700; text-transform:uppercase; letter-spacing:0.4px; color:#64748B;">Advance / Credit Available</div>
                            <div style="font-size:15px; font-weight:900; color:#16A34A; font-family:'JetBrains Mono', monospace;" id="supplierAdvance">₹0.00</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ─── LINE ITEMS (BULK STOCK) ─── -->
        <div class="card" style="margin-bottom:10px; border-radius:8px; border:1px solid #E2E8F0; overflow:hidden;">
            <div class="card-header" style="padding:7px 12px; background:#FAFAFA; border-bottom:1px solid #E2E8F0; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                <div>
                    <div class="card-title" style="font-size:12px; font-weight:700; display:flex; align-items:center; gap:6px;">
                        <i data-lucide="package" style="width:14px;height:14px; color:var(--color-primary);"></i> Stock Items (Bulk Inward)
                    </div>
                </div>
                <button type="button" class="btn btn-primary btn-sm" onclick="addPurchaseItemRow()" style="font-weight:700; font-size:11.5px; padding:4px 10px; border-radius:6px;">
                    <i data-lucide="plus" style="width:12px;height:12px;"></i> Add Item Row
                </button>
            </div>
            <div class="card-body" style="padding:0; overflow-x:auto;">
                <table class="data-table purchase-table" id="purchaseItemsTable">
                    <thead>
                        <tr>
                            <th style="width:18%;">Brand</th>
                            <th style="width:20%;">Model</th>
                            <th style="width:7%; text-align:center;">Qty</th>
                            <th style="width:11%; text-align:right;">Cost (₹)</th>
                            <th style="width:11%; text-align:right;">Sell (₹)</th>
                            <th style="width:7%; text-align:center;">RAM</th>
                            <th style="width:7%; text-align:center;">Storage</th>
                            <th style="width:10%; text-align:right;">Total (₹)</th>
                            <th style="width:17%;">IMEIs (one per line)</th>
                            <th style="width:4%; text-align:center;"></th>
                        </tr>
                    </thead>
                    <tbody id="purchaseItemsBody"></tbody>
                </table>
            </div>
        </div>

        <!-- ─── BILL SUMMARY & PAYMENT ─── -->
        <div class="card" style="margin-bottom:10px; border-radius:8px; border:1px solid #E2E8F0;">
            <div class="card-header" style="padding:7px 12px; background:#FAFAFA; border-bottom:1px solid #E2E8F0;">
                <div>
                    <div class="card-title" style="font-size:12px; font-weight:700; display:flex; align-items:center; gap:6px;">
                        <i data-lucide="wallet" style="width:14px;height:14px; color:var(--color-primary);"></i> Bill & Payment Details
                    </div>
                </div>
            </div>
            <div class="card-body" style="padding:10px 12px;">
                <div class="form-row" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:10px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" style="font-size:10px; font-weight:700; color:#64748B; text-transform:uppercase; letter-spacing:0.3px; margin-bottom:2px;">Bill Type</label>
                        <select class="form-control" name="bill_type" style="height:32px; font-size:12px; padding:4px 8px; border-radius:6px; font-weight:700;">
                            <option value="gst">GST (18% inclusive)</option>
                            <option value="non_gst">Non-GST / Estimate</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" style="font-size:10px; font-weight:700; color:#64748B; text-transform:uppercase; letter-spacing:0.3px; margin-bottom:2px;">Total Bill (₹) <span style="color:#EF4444;">*</span></label>
                        <input type="number" step="0.01" min="1" class="form-control" name="bill_total" id="billTotal" inputmode="decimal" oninput="recalcPurchase()" required placeholder="0.00" style="height:32px; font-size:12px; padding:4px 8px; border-radius:6px; font-family:monospace; font-weight:700;">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" style="font-size:10px; font-weight:700; color:#64748B; text-transform:uppercase; letter-spacing:0.3px; margin-bottom:2px;">Paid Now (₹) <span style="color:#EF4444;">*</span></label>
                        <input type="number" step="0.01" min="0" class="form-control" name="amount_paid" id="amountPaid" inputmode="decimal" oninput="recalcPurchase()" required placeholder="0.00" style="height:32px; font-size:12px; padding:4px 8px; border-radius:6px; font-family:monospace; font-weight:700;">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" style="font-size:10px; font-weight:700; color:#64748B; text-transform:uppercase; letter-spacing:0.3px; margin-bottom:2px;">Apply Advance (₹)</label>
                        <input type="number" step="0.01" min="0" class="form-control" name="use_advance_credit" id="useAdvance" inputmode="decimal" oninput="recalcPurchase()" placeholder="0.00" style="height:32px; font-size:12px; padding:4px 8px; border-radius:6px; font-family:monospace;">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" style="font-size:10px; font-weight:700; color:#64748B; text-transform:uppercase; letter-spacing:0.3px; margin-bottom:2px;">Payment Mode</label>
                        <select class="form-control" name="payment_mode" style="height:32px; font-size:12px; padding:4px 8px; border-radius:6px; font-weight:600;">
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer / RTGS</option>
                            <option value="cheque">Cheque</option>
                            <option value="upi">UPI</option>
                        </select>
                    </div>
                </div>

                <!-- Live Balance Summary (Matching Sales Hub stat strip) -->
                <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:6px; padding:8px 14px; display:flex; gap:24px; flex-wrap:wrap; margin-top:8px; align-items:center;">
                    <div>
                        <div style="font-size:9.5px; font-weight:700; text-transform:uppercase; letter-spacing:0.4px; color:#64748B;">Bill Total</div>
                        <div style="font-size:15px; font-weight:900; color:#0F172A; font-family:'JetBrains Mono', monospace;" id="sumBillTotal">₹0.00</div>
                    </div>
                    <div style="border-left:1px solid #E2E8F0; padding-left:24px;">
                        <div style="font-size:9.5px; font-weight:700; text-transform:uppercase; letter-spacing:0.4px; color:#64748B;">Paid Amount</div>
                        <div style="font-size:15px; font-weight:900; color:#16A34A; font-family:'JetBrains Mono', monospace;" id="sumPaid">₹0.00</div>
                    </div>
                    <div style="border-left:1px solid #E2E8F0; padding-left:24px;">
                        <div style="font-size:9.5px; font-weight:700; text-transform:uppercase; letter-spacing:0.4px; color:#64748B;">Balance Due &rarr; Ledger</div>
                        <div style="font-size:15px; font-weight:900; color:#DC2626; font-family:'JetBrains Mono', monospace;" id="sumBalance">₹0.00</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="purchase-create-actions" style="display:flex; gap:8px; justify-content:flex-end; margin-bottom:30px;">
            <a href="{{ route('mobileshop.purchase') }}" class="btn btn-outline btn-sm" style="font-weight:700; padding:6px 14px; border-radius:6px; font-size:12px;">Cancel</a>
            <button type="submit" class="btn btn-primary btn-sm" id="submitPurchaseBtn" style="font-weight:800; padding:7px 18px; font-size:12px; border-radius:6px; box-shadow:0 2px 6px rgba(94,106,210,0.3);">
                <i data-lucide="save" style="width:14px;height:14px;"></i> Purchase New Phone
            </button>
        </div>

        <!-- ─── MOBILE STICKY PURCHASE FOOTER ─── -->
        <div class="mobile-sticky-purchase-footer" id="mobilePurchaseStickyFooter" style="display:none;">
            <div>
                <div style="font-size:9.5px; font-weight:700; color:#64748B; text-transform:uppercase; letter-spacing:0.3px;">TOTAL BILL</div>
                <div style="font-size:18px; font-weight:900; color:#4F46E5; font-family:'JetBrains Mono', monospace; line-height:1.2;" id="mobileStickyPurchaseTotal">₹0.00</div>
            </div>
            <button type="submit" class="btn btn-primary" id="btnMobileSubmitPurchase" style="height:44px; padding:0 18px; font-size:13.5px; font-weight:800; border-radius:8px; display:inline-flex; align-items:center; gap:6px; box-shadow:0 2px 8px rgba(94, 106, 210, 0.35);">
                <i data-lucide="save" style="width:16px;height:16px;"></i> Purchase New Phone
            </button>
        </div>
    </form>

    <!-- ════ AI INVOICE OCR LOADING OVERLAY ════ -->
    <div id="purchaseOcrLoadingModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(15, 23, 42, 0.75); backdrop-filter:blur(5px); align-items:center; justify-content:center; flex-direction:column; gap:16px;">
        <div style="background:#FFFFFF; border-radius:14px; padding:28px 36px; box-shadow:0 20px 40px rgba(0,0,0,0.3); text-align:center; max-width:420px; width:90%; border:1px solid #E2E8F0;">
            <div style="width:56px; height:56px; border-radius:50%; background:linear-gradient(135deg, #EEF2FF 0%, #E0E7FF 100%); margin:0 auto 16px; display:flex; align-items:center; justify-content:center; color:#4F46E5; box-shadow:0 4px 12px rgba(79,70,229,0.2);">
                <i data-lucide="sparkles" style="width:28px; height:28px;"></i>
            </div>
            <h3 style="font-size:16px; font-weight:800; color:#1E1B4B; margin-bottom:6px;">Analyzing Vendor Invoice</h3>
            <p style="font-size:12px; color:#64748B; line-height:1.5; margin-bottom:14px;">Gemini 2.0 Flash is reading supplier details, phone models, dual IMEIs, and accessories from your bill...</p>
            <div style="height:4px; width:100%; background:#E2E8F0; border-radius:4px; overflow:hidden;">
                <div style="height:100%; width:60%; background:linear-gradient(90deg, #4F46E5, #7C3AED); border-radius:4px;"></div>
            </div>
        </div>
    </div>

    <!-- ════ AI INVOICE PREVIEW & APPROVAL MODAL ════ -->
    <div id="purchaseOcrReviewModal" style="display:none; position:fixed; inset:0; z-index:9998; background:rgba(15, 23, 42, 0.7); backdrop-filter:blur(4px); align-items:center; justify-content:center; padding:16px;">
        <div style="background:#FFFFFF; border-radius:14px; box-shadow:0 24px 48px rgba(0,0,0,0.25); max-width:850px; width:100%; max-height:90vh; display:flex; flex-direction:column; overflow:hidden; border:1px solid #CBD5E1;">
            
            <!-- Modal Header -->
            <div style="padding:14px 20px; background:#F8FAFC; border-bottom:1px solid #E2E8F0; display:flex; justify-content:space-between; align-items:center;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:32px; height:32px; border-radius:8px; background:#EEF2FF; display:flex; align-items:center; justify-content:center; color:#4F46E5;">
                        <i data-lucide="sparkles" style="width:18px; height:18px;"></i>
                    </div>
                    <div>
                        <h3 style="font-size:14px; font-weight:800; color:#1E1B4B; margin:0;">AI Scanned Invoice Preview</h3>
                        <div style="font-size:11px; color:#64748B;" id="ocrModelBadge">Processed with Gemini 2.0 Flash AI</div>
                    </div>
                </div>
                <button type="button" onclick="closePurchaseOcrModal()" style="border:none; background:transparent; cursor:pointer; color:#64748B; padding:4px;">
                    <i data-lucide="x" style="width:18px; height:18px;"></i>
                </button>
            </div>

            <!-- Modal Body (Scrollable) -->
            <div style="padding:16px 20px; overflow-y:auto; flex:1; display:flex; flex-direction:column; gap:14px;">
                
                <!-- Supplier Info Strip -->
                <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:12px 14px;">
                    <div style="font-size:10px; font-weight:700; color:#64748B; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:8px;">Detected Supplier & Bill Summary</div>
                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:10px; font-size:12px;">
                        <div>
                            <span style="color:#64748B; font-size:10.5px; display:block;">Supplier Name:</span>
                            <strong style="color:#0F172A;" id="ocrReviewSupplierName">—</strong>
                        </div>
                        <div>
                            <span style="color:#64748B; font-size:10.5px; display:block;">Phone / Mobile:</span>
                            <strong style="color:#0F172A;" id="ocrReviewSupplierPhone">—</strong>
                        </div>
                        <div>
                            <span style="color:#64748B; font-size:10.5px; display:block;">Invoice / Challan No:</span>
                            <strong style="color:#4F46E5; font-family:monospace;" id="ocrReviewInvoiceNo">—</strong>
                        </div>
                        <div>
                            <span style="color:#64748B; font-size:10.5px; display:block;">Invoice Date:</span>
                            <strong style="color:#0F172A;" id="ocrReviewInvoiceDate">—</strong>
                        </div>
                        <div>
                            <span style="color:#64748B; font-size:10.5px; display:block;">Bill Total Amount:</span>
                            <strong style="color:#16A34A; font-family:monospace; font-size:13px;" id="ocrReviewBillTotal">₹0.00</strong>
                        </div>
                    </div>
                </div>

                <!-- Items Preview Header -->
                <div>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                        <span style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase; letter-spacing:0.3px;">Detected Line Items</span>
                        <span id="ocrItemsCountBadge" style="font-size:11px; font-weight:700; color:#4F46E5; background:#EEF2FF; padding:2px 8px; border-radius:12px;">0 items</span>
                    </div>

                    <!-- Items Table -->
                    <div style="border:1px solid #E2E8F0; border-radius:8px; overflow:hidden; max-height:280px; overflow-y:auto;">
                        <table style="width:100%; border-collapse:collapse; font-size:11.5px; text-align:left;">
                            <thead style="background:#F1F5F9; color:#475569; position:sticky; top:0; z-index:2;">
                                <tr>
                                    <th style="padding:6px 10px; width:12%;">Type</th>
                                    <th style="padding:6px 10px; width:30%;">Brand / Model / Item</th>
                                    <th style="padding:6px 10px; width:8%; text-align:center;">Qty</th>
                                    <th style="padding:6px 10px; width:15%; text-align:right;">Cost (₹)</th>
                                    <th style="padding:6px 10px; width:15%; text-align:right;">Sell (₹)</th>
                                    <th style="padding:6px 10px; width:20%;">IMEIs / Barcode</th>
                                </tr>
                            </thead>
                            <tbody id="ocrReviewItemsBody">
                                <!-- Dynamic rows -->
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- Modal Footer Actions -->
            <div style="padding:12px 20px; background:#F8FAFC; border-top:1px solid #E2E8F0; display:flex; justify-content:space-between; align-items:center; gap:10px;">
                <button type="button" onclick="closePurchaseOcrModal()" class="btn btn-outline btn-sm" style="font-weight:700; font-size:12px; padding:6px 14px;">
                    Discard / Cancel
                </button>
                <button type="button" onclick="applyPurchaseOcrData()" class="btn btn-primary btn-sm" style="background:linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%); border:none; font-weight:800; font-size:12.5px; padding:7px 18px; border-radius:6px; box-shadow:0 3px 10px rgba(99,102,241,0.35); display:inline-flex; align-items:center; gap:6px;">
                    <i data-lucide="check" style="width:14px; height:14px;"></i> Apply to Stock Intake Form
                </button>
            </div>

        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Table styling for desktop */
    .purchase-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }
    .purchase-table thead th {
        background: #F8FAFC;
        color: #64748B;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        padding: 6px 8px;
        border-bottom: 1px solid #E2E8F0;
    }
    .purchase-table tbody td {
        padding: 4px 6px;
        vertical-align: middle;
        border-bottom: 1px solid #E2E8F0;
    }
    .mobile-row-label {
        display: none;
    }
    .btn-del-row {
        width: 28px;
        height: 28px;
        border: 1px solid #CBD5E1;
        background: #F1F5F9;
        color: #475569;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        padding: 0;
        transition: all 0.15s ease;
        flex-shrink: 0;
    }
    .btn-del-row:hover {
        background: #FEF2F2 !important;
        border-color: #FECACA !important;
        color: #DC2626 !important;
    }

    /* Mobile view: flat composed rows like Sales Hub */
    @media (max-width: 767px) {
        .purchase-create-wrapper {
            padding-bottom: 100px !important;
        }
        .mobile-sticky-purchase-footer {
            display: flex !important;
            position: fixed;
            bottom: var(--bottom-nav-height, 58px);
            left: 0;
            right: 0;
            z-index: 960;
            background: #FFFFFF;
            border-top: 1px solid #E2E8F0;
            box-shadow: 0 -4px 16px rgba(0, 0, 0, 0.08);
            padding: 10px 14px;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .purchase-table, .purchase-table thead, .purchase-table tbody, .purchase-table tr, .purchase-table td {
            display: block;
            width: 100% !important;
        }
        .purchase-table thead {
            display: none !important;
        }
        .purchase-item-row {
            padding: 8px 10px 8px 12px;
            border-bottom: 1px solid #E2E8F0 !important;
            background: #FFFFFF;
            position: relative;
        }
        .purchase-item-row::before {
            content: '';
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 3px;
            background: #E2E8F0;
        }
        .purchase-item-row:hover::before {
            background: #5E6AD2;
        }
        .mobile-row-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4px 6px;
        }
        .mobile-row-label {
            display: block;
            font-size: 9px;
            font-weight: 700;
            color: #64748B;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 2px;
        }
        .purchase-create-actions {
            flex-direction: column-reverse;
            width: 100%;
        }
        .purchase-create-actions .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    var rowIdx = 0;

    function addPurchaseItemRow() {
        rowIdx++;
        var tr = document.createElement('tr');
        tr.className = 'purchase-item-row';
        tr.innerHTML =
            '<td><label class="mobile-row-label">Brand & Model</label><div style="display:flex;gap:4px;"><input type="text" class="form-control" name="items[' + rowIdx + '][brand]" placeholder="Brand (e.g. Samsung)" required style="height:30px; font-size:12px; padding:3px 6px; border-radius:5px; flex:1;"><input type="text" class="form-control" name="items[' + rowIdx + '][model]" placeholder="Model (e.g. A15)" required style="height:30px; font-size:12px; padding:3px 6px; border-radius:5px; flex:1.2;"></div></td>' +
            '<td class="hide-on-mobile-cell" style="display:none;"></td>' +
            '<td><label class="mobile-row-label">Qty & Cost & Selling</label><div style="display:flex;gap:4px;"><div style="flex:0.8;"><input type="number" class="form-control item-qty" name="items[' + rowIdx + '][qty]" min="1" value="1" inputmode="numeric" required oninput="recalcPurchase()" style="height:30px; font-size:12px; text-align:center; padding:3px 2px; border-radius:5px;" placeholder="Qty"></div><div style="flex:1.1;"><input type="number" class="form-control item-cost" name="items[' + rowIdx + '][unit_cost]" step="0.01" min="0" inputmode="decimal" required oninput="recalcPurchase()" placeholder="Cost ₹" style="height:30px; font-size:12px; font-family:monospace; font-weight:700; text-align:right; padding:3px 6px; border-radius:5px;"></div><div style="flex:1.1;"><input type="number" class="form-control" name="items[' + rowIdx + '][selling_price]" step="0.01" min="1" inputmode="decimal" required placeholder="Sell ₹" style="height:30px; font-size:12px; font-family:monospace; font-weight:700; color:#16A34A; text-align:right; padding:3px 6px; border-radius:5px;"></div></div></td>' +
            '<td class="hide-on-mobile-cell" style="display:none;"></td>' +
            '<td class="hide-on-mobile-cell" style="display:none;"></td>' +
            '<td><label class="mobile-row-label">Specs & IMEIs</label><div style="display:flex;gap:4px;align-items:center;"><input type="text" class="form-control" name="items[' + rowIdx + '][ram]" placeholder="RAM" style="height:30px; font-size:11px; padding:3px 4px; border-radius:5px; width:54px;"><input type="text" class="form-control" name="items[' + rowIdx + '][storage]" placeholder="ROM" style="height:30px; font-size:11px; padding:3px 4px; border-radius:5px; width:54px;"><input type="text" class="form-control" name="items[' + rowIdx + '][imeis]" placeholder="IMEIs" style="height:30px; padding:3px 6px; font-family:monospace; font-size:11px; border-radius:5px; flex:1;"></div></td>' +
            '<td class="hide-on-mobile-cell" style="display:none;"></td>' +
            '<td style="font-weight:800; font-family:monospace; text-align:right; font-size:12.5px; color:#0F172A;" class="line-total">₹0.00</td>' +
            '<td class="hide-on-mobile-cell" style="display:none;"></td>' +
            '<td style="text-align:center;"><button type="button" class="btn-del-row" onclick="this.closest(\'tr\').remove(); recalcPurchase();" title="Remove row"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button></td>';

        // Check if desktop: if desktop (≥768px), format as clean spreadsheet row
        if (window.innerWidth >= 768) {
            tr.innerHTML =
                '<td><input type="text" class="form-control" name="items[' + rowIdx + '][brand]" placeholder="e.g. Samsung" required style="height:30px; font-size:12px; padding:3px 6px; border-radius:5px;"></td>' +
                '<td><input type="text" class="form-control" name="items[' + rowIdx + '][model]" placeholder="e.g. Galaxy A15" required style="height:30px; font-size:12px; padding:3px 6px; border-radius:5px;"></td>' +
                '<td><input type="number" class="form-control item-qty" name="items[' + rowIdx + '][qty]" min="1" value="1" inputmode="numeric" required oninput="recalcPurchase()" style="height:30px; font-size:12px; text-align:center; padding:3px 4px; border-radius:5px;"></td>' +
                '<td><input type="number" class="form-control item-cost" name="items[' + rowIdx + '][unit_cost]" step="0.01" min="0" inputmode="decimal" required oninput="recalcPurchase()" placeholder="0.00" style="height:30px; font-size:12px; font-family:monospace; font-weight:700; text-align:right; padding:3px 6px; border-radius:5px;"></td>' +
                '<td><input type="number" class="form-control" name="items[' + rowIdx + '][selling_price]" step="0.01" min="1" inputmode="decimal" required placeholder="0.00" style="height:30px; font-size:12px; font-family:monospace; font-weight:700; color:#16A34A; text-align:right; padding:3px 6px; border-radius:5px;"></td>' +
                '<td><input type="text" class="form-control" name="items[' + rowIdx + '][ram]" placeholder="8GB" style="height:30px; font-size:11px; padding:3px 4px; border-radius:5px; text-align:center;"></td>' +
                '<td><input type="text" class="form-control" name="items[' + rowIdx + '][storage]" placeholder="128GB" style="height:30px; font-size:11px; padding:3px 4px; border-radius:5px; text-align:center;"></td>' +
                '<td style="font-weight:800; font-family:monospace; text-align:right; font-size:12.5px; color:#0F172A;" class="line-total">₹0.00</td>' +
                '<td><textarea class="form-control" name="items[' + rowIdx + '][imeis]" rows="1" placeholder="IMEIs (comma separated)" style="height:30px; padding:3px 6px; font-family:monospace; font-size:11px; border-radius:5px;"></textarea></td>' +
                '<td style="text-align:center;"><button type="button" class="btn-del-row" onclick="this.closest(\'tr\').remove(); recalcPurchase();" title="Remove row"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button></td>';
        }

        document.getElementById('purchaseItemsBody').appendChild(tr);
        if (window.refreshIcons) window.refreshIcons();
        else if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons();
        recalcPurchase();
    }

    function onSupplierChange() {
        var sel = document.getElementById('supplierSelect');
        var panel = document.getElementById('supplierBalancePanel');
        var newFields = document.getElementById('newSupplierFields');
        var newPhone = document.getElementById('newSupplierPhone');
        if (sel.value) {
            var opt = sel.options[sel.selectedIndex];
            document.getElementById('supplierOutstanding').textContent = '₹' + parseFloat(opt.dataset.outstanding || 0).toLocaleString('en-IN', {minimumFractionDigits: 2});
            document.getElementById('supplierAdvance').textContent = '₹' + parseFloat(opt.dataset.advance || 0).toLocaleString('en-IN', {minimumFractionDigits: 2});
            panel.style.display = 'block';
            newFields.style.display = 'none';
            newPhone.style.display = 'none';
        } else {
            panel.style.display = 'none';
            newFields.style.display = 'block';
            newPhone.style.display = 'block';
        }
    }

    function recalcPurchase() {
        var grand = 0;
        document.querySelectorAll('.purchase-item-row').forEach(function(row) {
            var qty = parseFloat(row.querySelector('.item-qty')?.value) || 0;
            var cost = parseFloat(row.querySelector('.item-cost')?.value) || 0;
            var line = qty * cost;
            grand += line;
            var cell = row.querySelector('.line-total');
            if (cell) cell.textContent = '₹' + line.toLocaleString('en-IN', {minimumFractionDigits: 2});
        });
        document.getElementById('sumBillTotal').textContent = '₹' + grand.toLocaleString('en-IN', {minimumFractionDigits: 2});

        var billInput = document.getElementById('billTotal');
        // Auto-fill bill total from line items unless user manually typed a value
        if (!billInput.dataset.touched) {
            billInput.value = grand > 0 ? grand.toFixed(2) : '';
        }
        var billTotal = parseFloat(billInput.value) || grand;
        var paidCash = parseFloat(document.getElementById('amountPaid').value) || 0;
        var advance = parseFloat(document.getElementById('useAdvance').value) || 0;
        var paid = Math.min(paidCash + advance, billTotal);
        var balance = Math.max(0, billTotal - paid);

        document.getElementById('sumBillTotal').textContent = '₹' + billTotal.toLocaleString('en-IN', {minimumFractionDigits: 2});
        document.getElementById('sumPaid').textContent = '₹' + paid.toLocaleString('en-IN', {minimumFractionDigits: 2});
        document.getElementById('sumBalance').textContent = '₹' + balance.toLocaleString('en-IN', {minimumFractionDigits: 2});
        document.getElementById('sumBalance').style.color = balance > 0 ? '#DC2626' : '#16A34A';

        var mobTotalEl = document.getElementById('mobileStickyPurchaseTotal');
        if (mobTotalEl) {
            mobTotalEl.textContent = '₹' + billTotal.toLocaleString('en-IN', {minimumFractionDigits: 2});
        }
    }

    document.getElementById('billTotal').addEventListener('input', function(){ this.dataset.touched = '1'; });

    /* ─── AI INVOICE OCR SCANNING & AUTO-POPULATION ─── */
    var currentPurchaseOcrData = null;

    function handlePurchaseOcrFile(input) {
        if (!input.files || !input.files[0]) return;
        var file = input.files[0];

        // Max 12MB limit
        if (file.size > 12 * 1024 * 1024) {
            alert('File size exceeds 12MB limit. Please upload a clearer, compressed image or PDF.');
            input.value = '';
            return;
        }

        var loadingModal = document.getElementById('purchaseOcrLoadingModal');
        loadingModal.style.display = 'flex';
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }

        var formData = new FormData();
        formData.append('invoice_image', file);

        fetch("{{ route('mobileshop.purchase.scan_invoice') }}", {
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
                alert(res.data.message || 'Failed to analyze vendor invoice. Please verify picture quality.');
                input.value = '';
                return;
            }

            populatePurchaseOcrModal(res.data);
            input.value = '';
        })
        .catch(function(err) {
            loadingModal.style.display = 'none';
            alert('Network error while processing document: ' + err.message);
            input.value = '';
        });
    }

    function populatePurchaseOcrModal(res) {
        currentPurchaseOcrData = res.data || {};
        var matchedSupplierId = res.matched_supplier_id || null;
        var items = currentPurchaseOcrData.items || [];

        document.getElementById('ocrReviewSupplierName').textContent = currentPurchaseOcrData.supplier_name || 'Not detected';
        document.getElementById('ocrReviewSupplierPhone').textContent = currentPurchaseOcrData.supplier_phone || 'Not detected';
        document.getElementById('ocrReviewInvoiceNo').textContent = currentPurchaseOcrData.supplier_invoice_no || 'Not detected';
        document.getElementById('ocrReviewInvoiceDate').textContent = currentPurchaseOcrData.invoice_date || '{{ date("Y-m-d") }}';
        
        var totalVal = parseFloat(currentPurchaseOcrData.bill_total) || 0;
        document.getElementById('ocrReviewBillTotal').textContent = '₹' + totalVal.toLocaleString('en-IN', {minimumFractionDigits: 2});

        var modelBadge = document.getElementById('ocrModelBadge');
        if (res.model_used) {
            modelBadge.textContent = 'Processed with ' + res.model_used + ' AI';
        }

        var tbody = document.getElementById('ocrReviewItemsBody');
        tbody.innerHTML = '';

        var phoneCount = 0;
        var accCount = 0;

        items.forEach(function(item) {
            var isPhone = (item.type === 'phone');
            if (isPhone) phoneCount++;
            else accCount++;

            var tr = document.createElement('tr');
            tr.style.borderBottom = '1px solid #E2E8F0';

            var typeBadge = isPhone
                ? '<span style="background:#EEF2FF;color:#4F46E5;padding:2px 6px;border-radius:4px;font-size:10px;font-weight:700;">PHONE</span>'
                : '<span style="background:#F0FDF4;color:#16A34A;padding:2px 6px;border-radius:4px;font-size:10px;font-weight:700;">ACCESSORY</span>';

            var brandModelText = isPhone
                ? '<strong>' + escapeHtml(item.brand || '') + '</strong> ' + escapeHtml(item.model || '') +
                  (item.storage ? ' <span style="color:#64748B;">(' + escapeHtml(item.storage) + ')</span>' : '')
                : '<strong>' + escapeHtml(item.category || 'Accessory') + '</strong> — ' + escapeHtml(item.name || '');

            var costNum = parseFloat(item.unit_cost) || 0;
            var sellNum = parseFloat(item.selling_price) || (costNum > 0 ? Math.round(costNum * 1.15) : 0);
            var qtyNum = parseInt(item.qty) || 1;

            var extraInfo = '';
            if (isPhone && Array.isArray(item.imeis) && item.imeis.length > 0) {
                extraInfo = '<div style="font-family:monospace;font-size:10.5px;color:#475569;word-break:break-all;">IMEI: ' + escapeHtml(item.imeis.join(', ')) + '</div>';
            } else if (!isPhone && item.barcode) {
                extraInfo = '<div style="font-family:monospace;font-size:10.5px;color:#64748B;">Barcode: ' + escapeHtml(item.barcode) + '</div>';
            }

            tr.innerHTML =
                '<td style="padding:8px 10px;">' + typeBadge + '</td>' +
                '<td style="padding:8px 10px;">' + brandModelText + '</td>' +
                '<td style="padding:8px 10px;text-align:center;font-weight:700;">' + qtyNum + '</td>' +
                '<td style="padding:8px 10px;text-align:right;font-family:monospace;font-weight:700;color:#0F172A;">₹' + costNum.toLocaleString('en-IN', {minimumFractionDigits: 2}) + '</td>' +
                '<td style="padding:8px 10px;text-align:right;font-family:monospace;font-weight:700;color:#16A34A;">₹' + sellNum.toLocaleString('en-IN', {minimumFractionDigits: 2}) + '</td>' +
                '<td style="padding:8px 10px;">' + extraInfo + '</td>';

            tbody.appendChild(tr);
        });

        document.getElementById('ocrItemsCountBadge').textContent = items.length + ' item(s) (' + phoneCount + ' phones, ' + accCount + ' accessories)';

        document.getElementById('purchaseOcrReviewModal').style.display = 'flex';
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }
    }

    function closePurchaseOcrModal() {
        document.getElementById('purchaseOcrReviewModal').style.display = 'none';
    }

    function applyPurchaseOcrData() {
        if (!currentPurchaseOcrData) return;

        // 1. Supplier Auto-selection
        var supplierSelect = document.getElementById('supplierSelect');
        var matched = false;
        var detectedName = (currentPurchaseOcrData.supplier_name || '').toLowerCase().trim();

        if (detectedName) {
            for (var i = 0; i < supplierSelect.options.length; i++) {
                var optText = supplierSelect.options[i].text.toLowerCase();
                if (optText.indexOf(detectedName) !== -1 || detectedName.indexOf(optText) !== -1) {
                    supplierSelect.selectedIndex = i;
                    matched = true;
                    break;
                }
            }
        }

        if (matched) {
            onSupplierChange();
        } else {
            // Fill as new supplier
            supplierSelect.value = '';
            onSupplierChange();
            var newNameInput = document.querySelector('input[name="new_supplier_name"]');
            var newPhoneInput = document.querySelector('input[name="new_supplier_phone"]');
            if (newNameInput && currentPurchaseOcrData.supplier_name) {
                newNameInput.value = currentPurchaseOcrData.supplier_name;
            }
            if (newPhoneInput && currentPurchaseOcrData.supplier_phone) {
                newPhoneInput.value = currentPurchaseOcrData.supplier_phone;
            }
        }

        // 2. Invoice Details
        if (currentPurchaseOcrData.supplier_invoice_no) {
            var invInput = document.querySelector('input[name="supplier_invoice_no"]');
            if (invInput) invInput.value = currentPurchaseOcrData.supplier_invoice_no;
        }
        if (currentPurchaseOcrData.invoice_date) {
            var dateInput = document.querySelector('input[name="order_date"]');
            if (dateInput) dateInput.value = currentPurchaseOcrData.invoice_date;
        }

        // 3. Clear default empty rows and populate with scanned items
        var items = currentPurchaseOcrData.items || [];
        if (items.length > 0) {
            var tbody = document.getElementById('purchaseItemsBody');
            tbody.innerHTML = '';
            rowIdx = 0;

            items.forEach(function(item) {
                addPurchaseItemRowWithData(item);
            });
        }

        // 4. Update Bill Total
        if (currentPurchaseOcrData.bill_total && parseFloat(currentPurchaseOcrData.bill_total) > 0) {
            var billInput = document.getElementById('billTotal');
            billInput.value = parseFloat(currentPurchaseOcrData.bill_total).toFixed(2);
            billInput.dataset.touched = '1';
        }

        recalcPurchase();
        closePurchaseOcrModal();

        // Scroll to items table smoothly
        document.getElementById('purchaseItemsTable').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function addPurchaseItemRowWithData(data) {
        rowIdx++;
        var tr = document.createElement('tr');
        tr.className = 'purchase-item-row';

        var brand = data.brand || (data.category || 'General');
        var model = data.model || (data.name || 'Stock Item');
        var qty = parseInt(data.qty) || 1;
        var cost = parseFloat(data.unit_cost) || 0;
        var sell = parseFloat(data.selling_price) || (cost > 0 ? Math.round(cost * 1.15) : 0);
        var ram = data.ram || '';
        var storage = data.storage || '';
        var imeis = Array.isArray(data.imeis) ? data.imeis.join(', ') : (data.imeis || data.barcode || '');

        if (window.innerWidth >= 768) {
            tr.innerHTML =
                '<td><input type="text" class="form-control" name="items[' + rowIdx + '][brand]" value="' + escapeHtml(brand) + '" required style="height:30px; font-size:12px; padding:3px 6px; border-radius:5px;"></td>' +
                '<td><input type="text" class="form-control" name="items[' + rowIdx + '][model]" value="' + escapeHtml(model) + '" required style="height:30px; font-size:12px; padding:3px 6px; border-radius:5px;"></td>' +
                '<td><input type="number" class="form-control item-qty" name="items[' + rowIdx + '][qty]" min="1" value="' + qty + '" inputmode="numeric" required oninput="recalcPurchase()" style="height:30px; font-size:12px; text-align:center; padding:3px 4px; border-radius:5px;"></td>' +
                '<td><input type="number" class="form-control item-cost" name="items[' + rowIdx + '][unit_cost]" step="0.01" min="0" value="' + cost + '" inputmode="decimal" required oninput="recalcPurchase()" style="height:30px; font-size:12px; font-family:monospace; font-weight:700; text-align:right; padding:3px 6px; border-radius:5px;"></td>' +
                '<td><input type="number" class="form-control" name="items[' + rowIdx + '][selling_price]" step="0.01" min="1" value="' + sell + '" inputmode="decimal" required style="height:30px; font-size:12px; font-family:monospace; font-weight:700; color:#16A34A; text-align:right; padding:3px 6px; border-radius:5px;"></td>' +
                '<td><input type="text" class="form-control" name="items[' + rowIdx + '][ram]" value="' + escapeHtml(ram) + '" placeholder="8GB" style="height:30px; font-size:11px; padding:3px 4px; border-radius:5px; text-align:center;"></td>' +
                '<td><input type="text" class="form-control" name="items[' + rowIdx + '][storage]" value="' + escapeHtml(storage) + '" placeholder="128GB" style="height:30px; font-size:11px; padding:3px 4px; border-radius:5px; text-align:center;"></td>' +
                '<td style="font-weight:800; font-family:monospace; text-align:right; font-size:12.5px; color:#0F172A;" class="line-total">₹0.00</td>' +
                '<td><textarea class="form-control" name="items[' + rowIdx + '][imeis]" rows="1" placeholder="IMEIs" style="height:30px; padding:3px 6px; font-family:monospace; font-size:11px; border-radius:5px;">' + escapeHtml(imeis) + '</textarea></td>' +
                '<td style="text-align:center;"><button type="button" class="btn-del-row" onclick="this.closest(\'tr\').remove(); recalcPurchase();" title="Remove row"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button></td>';
        } else {
            tr.innerHTML =
                '<td><label class="mobile-row-label">Brand & Model</label><div style="display:flex;gap:4px;"><input type="text" class="form-control" name="items[' + rowIdx + '][brand]" value="' + escapeHtml(brand) + '" required style="height:30px; font-size:12px; padding:3px 6px; border-radius:5px; flex:1;"><input type="text" class="form-control" name="items[' + rowIdx + '][model]" value="' + escapeHtml(model) + '" required style="height:30px; font-size:12px; padding:3px 6px; border-radius:5px; flex:1.2;"></div></td>' +
                '<td class="hide-on-mobile-cell" style="display:none;"></td>' +
                '<td><label class="mobile-row-label">Qty & Cost & Selling</label><div style="display:flex;gap:4px;"><div style="flex:0.8;"><input type="number" class="form-control item-qty" name="items[' + rowIdx + '][qty]" min="1" value="' + qty + '" inputmode="numeric" required oninput="recalcPurchase()" style="height:30px; font-size:12px; text-align:center; padding:3px 2px; border-radius:5px;"></div><div style="flex:1.1;"><input type="number" class="form-control item-cost" name="items[' + rowIdx + '][unit_cost]" step="0.01" min="0" value="' + cost + '" inputmode="decimal" required oninput="recalcPurchase()" style="height:30px; font-size:12px; font-family:monospace; font-weight:700; text-align:right; padding:3px 6px; border-radius:5px;"></div><div style="flex:1.1;"><input type="number" class="form-control" name="items[' + rowIdx + '][selling_price]" step="0.01" min="1" value="' + sell + '" inputmode="decimal" required style="height:30px; font-size:12px; font-family:monospace; font-weight:700; color:#16A34A; text-align:right; padding:3px 6px; border-radius:5px;"></div></div></td>' +
                '<td class="hide-on-mobile-cell" style="display:none;"></td>' +
                '<td class="hide-on-mobile-cell" style="display:none;"></td>' +
                '<td><label class="mobile-row-label">Specs & IMEIs</label><div style="display:flex;gap:4px;align-items:center;"><input type="text" class="form-control" name="items[' + rowIdx + '][ram]" value="' + escapeHtml(ram) + '" placeholder="RAM" style="height:30px; font-size:11px; padding:3px 4px; border-radius:5px; width:54px;"><input type="text" class="form-control" name="items[' + rowIdx + '][storage]" value="' + escapeHtml(storage) + '" placeholder="ROM" style="height:30px; font-size:11px; padding:3px 4px; border-radius:5px; width:54px;"><input type="text" class="form-control" name="items[' + rowIdx + '][imeis]" value="' + escapeHtml(imeis) + '" placeholder="IMEIs" style="height:30px; padding:3px 6px; font-family:monospace; font-size:11px; border-radius:5px; flex:1;"></div></td>' +
                '<td class="hide-on-mobile-cell" style="display:none;"></td>' +
                '<td style="font-weight:800; font-family:monospace; text-align:right; font-size:12.5px; color:#0F172A;" class="line-total">₹0.00</td>' +
                '<td class="hide-on-mobile-cell" style="display:none;"></td>' +
                '<td style="text-align:center;"><button type="button" class="btn-del-row" onclick="this.closest(\'tr\').remove(); recalcPurchase();" title="Remove row"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button></td>';
        }

        document.getElementById('purchaseItemsBody').appendChild(tr);
        if (window.refreshIcons) window.refreshIcons();
        else if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons();
        recalcPurchase();
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    // Start with 2 item rows
    addPurchaseItemRow();
    addPurchaseItemRow();
</script>
@endpush