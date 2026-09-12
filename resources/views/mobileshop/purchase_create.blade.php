@extends('mobileshop.layout')

@section('title', 'Register Purchase — Bulk Stock Inward — Maurya Mobile')
@section('page-title', 'Register Purchase / Stock Inward')

@section('page-actions')
    <a href="{{ route('mobileshop.purchase') }}" class="btn btn-outline btn-sm" style="font-weight:700; font-size:12px; padding:5px 12px;">
        <i data-lucide="arrow-left" style="width:13px;height:13px;"></i> Back to Purchase
    </a>
@endsection

@section('content')
<div class="purchase-create-wrapper" style="max-width: 1200px; margin: 0 auto;">

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
                <i data-lucide="save" style="width:14px;height:14px;"></i> Save Purchase & Add Stock
            </button>
        </div>

        <!-- ─── MOBILE STICKY PURCHASE FOOTER ─── -->
        <div class="mobile-sticky-purchase-footer" id="mobilePurchaseStickyFooter" style="display:none;">
            <div>
                <div style="font-size:9.5px; font-weight:700; color:#64748B; text-transform:uppercase; letter-spacing:0.3px;">TOTAL BILL</div>
                <div style="font-size:18px; font-weight:900; color:#4F46E5; font-family:'JetBrains Mono', monospace; line-height:1.2;" id="mobileStickyPurchaseTotal">₹0.00</div>
            </div>
            <button type="submit" class="btn btn-primary" id="btnMobileSubmitPurchase" style="height:44px; padding:0 18px; font-size:13.5px; font-weight:800; border-radius:8px; display:inline-flex; align-items:center; gap:6px; box-shadow:0 2px 8px rgba(94, 106, 210, 0.35);">
                <i data-lucide="save" style="width:16px;height:16px;"></i> Save Purchase
            </button>
        </div>
    </form>
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

    // Start with 2 item rows
    addPurchaseItemRow();
    addPurchaseItemRow();
</script>
@endpush