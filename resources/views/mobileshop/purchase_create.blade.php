@extends('mobileshop.layout')

@section('title', 'Register Purchase — Bulk Stock Inward — Maurya Mobile')
@section('page-title', 'Register Purchase / Stock Inward')

@section('page-actions')
    <a href="{{ route('mobileshop.purchase') }}" class="btn btn-outline btn-sm">
        <i data-lucide="arrow-left" style="width:13px;height:13px;"></i> Back to Purchase
    </a>
@endsection

@section('content')
<div style="max-width: 1200px; margin: 0 auto;">

    @if(session('success'))
        <div class="flash-success" style="border-radius:8px; margin-bottom:16px;">
            <i data-lucide="check-circle-2" style="width:18px;height:18px;"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flash-error" style="border-radius:8px; margin-bottom:16px;">
            <i data-lucide="alert-circle" style="width:18px;height:18px;"></i> {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('mobileshop.purchase.store_bulk') }}" id="bulkPurchaseForm">
        @csrf

        <!-- ─── SUPPLIER SECTION ─── -->
        <div class="card" style="margin-bottom:18px;">
            <div class="card-header">
                <div>
                    <div class="card-title"><i data-lucide="truck" style="width:16px;height:16px; vertical-align:-2px; color:var(--color-primary);"></i> Supplier / Company Details</div>
                </div>
            </div>
            <div class="card-body">
                <div class="form-row" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));">
                    <div class="form-group">
                        <label class="form-label">Existing Supplier</label>
                        <select class="form-control" name="supplier_id" id="supplierSelect" onchange="onSupplierChange()">
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
                    <div class="form-group" id="newSupplierFields" style="display:none;">
                        <label class="form-label">New Supplier Name <span style="color:#EF4444;">*</span></label>
                        <input type="text" class="form-control" name="new_supplier_name" placeholder="e.g. Samsung Distributors Pvt Ltd">
                    </div>
                    <div class="form-group" id="newSupplierPhone" style="display:none;">
                        <label class="form-label">Supplier Phone</label>
                        <input type="text" class="form-control" name="new_supplier_phone" inputmode="tel" placeholder="10-digit mobile">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Supplier Invoice No.</label>
                        <input type="text" class="form-control" name="supplier_invoice_no" placeholder="e.g. INV-2026-0871">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Invoice / Shipment Date <span style="color:#EF4444;">*</span></label>
                        <input type="date" class="form-control" name="order_date" value="{{ now()->toDateString() }}" required>
                    </div>
                </div>

                <!-- Supplier Balance Panel -->
                <div id="supplierBalancePanel" style="display:none; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:14px 18px; margin-top:4px;">
                    <div style="display:flex; gap:32px; flex-wrap:wrap;">
                        <div>
                            <div style="font-size:10.5px; font-weight:800; text-transform:uppercase; letter-spacing:0.5px; color:#64748B;">Previous Balance Due</div>
                            <div style="font-size:20px; font-weight:900; color:#DC2626;" id="supplierOutstanding">₹0.00</div>
                            <div style="font-size:10.5px; color:#64748B; margin-top:2px;">Carried from earlier shipments</div>
                        </div>
                        <div>
                            <div style="font-size:10.5px; font-weight:800; text-transform:uppercase; letter-spacing:0.5px; color:#64748B;">Advance / Credit Available</div>
                            <div style="font-size:20px; font-weight:900; color:#16A34A;" id="supplierAdvance">₹0.00</div>
                            <div style="font-size:10.5px; color:#64748B; margin-top:2px;">Can be applied to this bill</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ─── LINE ITEMS (BULK STOCK) ─── -->
        <div class="card" style="margin-bottom:18px;">
            <div class="card-header">
                <div>
                    <div class="card-title"><i data-lucide="package" style="width:16px;height:16px; vertical-align:-2px; color:var(--color-primary);"></i> Stock Items (Bulk)</div>
                </div>
                <button type="button" class="btn btn-primary btn-sm" onclick="addPurchaseItemRow()">
                    <i data-lucide="plus" style="width:13px;height:13px;"></i> Add Item Row
                </button>
            </div>
            <div class="card-body" style="padding:0; overflow-x:auto;">
                <table class="data-table" id="purchaseItemsTable" style="min-width:1000px;">
                    <thead>
                        <tr>
                            <th style="width:26%;">Brand</th>
                            <th style="width:26%;">Model</th>
                            <th style="width:6%;">Qty</th>
                            <th style="width:10%;">Unit Cost (₹)</th>
                            <th style="width:10%;">Selling Price (₹)</th>
                            <th style="width:8%;">RAM</th>
                            <th style="width:8%;">Storage</th>
                            <th style="width:6%;">Total (₹)</th>
                            <th style="width:20%;">IMEIs (one per line)</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="purchaseItemsBody"></tbody>
                </table>
            </div>
        </div>

        <!-- ─── BILL SUMMARY & PAYMENT ─── -->
        <div class="card" style="margin-bottom:18px;">
            <div class="card-header">
                <div>
                    <div class="card-title"><i data-lucide="wallet" style="width:16px;height:16px; vertical-align:-2px; color:var(--color-primary);"></i> Bill & Payment</div>
                </div>
            </div>
            <div class="card-body">
                <div class="form-row" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
                    <div class="form-group">
                        <label class="form-label">Bill Type</label>
                        <select class="form-control" name="bill_type">
                            <option value="gst">GST (18% inclusive)</option>
                            <option value="non_gst">Non-GST / Estimate</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Total Bill Amount (₹) <span style="color:#EF4444;">*</span></label>
                        <input type="number" step="0.01" min="1" class="form-control" name="bill_total" id="billTotal" inputmode="decimal" oninput="recalcPurchase()" required placeholder="e.g. 700000">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Paid Now (₹) <span style="color:#EF4444;">*</span></label>
                        <input type="number" step="0.01" min="0" class="form-control" name="amount_paid" id="amountPaid" inputmode="decimal" oninput="recalcPurchase()" required placeholder="e.g. 500000">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Apply Advance Credit (₹)</label>
                        <input type="number" step="0.01" min="0" class="form-control" name="use_advance_credit" id="useAdvance" inputmode="decimal" oninput="recalcPurchase()" placeholder="0.00">
                        <div style="font-size:10.5px; color:#64748B; margin-top:4px;">Uses supplier's advance/credit wallet</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Payment Mode</label>
                        <select class="form-control" name="payment_mode">
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer / RTGS</option>
                            <option value="cheque">Cheque</option>
                            <option value="upi">UPI</option>
                        </select>
                    </div>
                </div>

                <!-- Live Balance Summary -->
                <div style="background:#F8FAFC; border:1px solid var(--color-border); border-radius:8px; padding:16px 20px; display:flex; gap:40px; flex-wrap:wrap; margin-top:6px;">
                    <div>
                        <div style="font-size:10.5px; font-weight:800; text-transform:uppercase; letter-spacing:0.5px; color:#64748B;">Bill Total</div>
                        <div style="font-size:22px; font-weight:900; color:#0F172A;" id="sumBillTotal">₹0.00</div>
                    </div>
                    <div>
                        <div style="font-size:10.5px; font-weight:800; text-transform:uppercase; letter-spacing:0.5px; color:#64748B;">Paid (Cash + Advance)</div>
                        <div style="font-size:22px; font-weight:900; color:#16A34A;" id="sumPaid">₹0.00</div>
                    </div>
                    <div>
                        <div style="font-size:10.5px; font-weight:800; text-transform:uppercase; letter-spacing:0.5px; color:#64748B;">Balance Due → Supplier Ledger</div>
                        <div style="font-size:22px; font-weight:900; color:#DC2626;" id="sumBalance">₹0.00</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="purchase-create-actions" style="display:flex; gap:12px; justify-content:flex-end; margin-bottom:40px;">
            <a href="{{ route('mobileshop.purchase') }}" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary" id="submitPurchaseBtn" style="min-width:220px;">
                <i data-lucide="save" style="width:15px;height:15px;"></i> Save Purchase & Add Stock
            </button>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
@media (max-width: 767px) {
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
            '<td><input type="text" class="form-control" name="items[' + rowIdx + '][brand]" placeholder="e.g. Samsung" required style="padding:8px 10px;"></td>' +
            '<td><input type="text" class="form-control" name="items[' + rowIdx + '][model]" placeholder="e.g. Galaxy A15" required style="padding:8px 10px;"></td>' +
            '<td><input type="number" class="form-control item-qty" name="items[' + rowIdx + '][qty]" min="1" value="1" inputmode="numeric" required oninput="recalcPurchase()" style="padding:8px 10px;"></td>' +
            '<td><input type="number" class="form-control item-cost" name="items[' + rowIdx + '][unit_cost]" step="0.01" min="0" inputmode="decimal" required oninput="recalcPurchase()" placeholder="0.00" style="padding:8px 10px;"></td>' +
            '<td><input type="number" class="form-control" name="items[' + rowIdx + '][selling_price]" step="0.01" min="1" inputmode="decimal" required placeholder="0.00" style="padding:8px 10px;"></td>' +
            '<td><input type="text" class="form-control" name="items[' + rowIdx + '][ram]" placeholder="8GB" style="padding:8px 10px;"></td>' +
            '<td><input type="text" class="form-control" name="items[' + rowIdx + '][storage]" placeholder="128GB" style="padding:8px 10px;"></td>' +
            '<td style="font-weight:800; font-family:monospace; text-align:right; padding-right:14px;" class="line-total">₹0.00</td>' +
            '<td><textarea class="form-control" name="items[' + rowIdx + '][imeis]" rows="2" placeholder="Optional. 1 IMEI per line / comma" style="padding:8px 10px; font-family:monospace; font-size:11px;"></textarea></td>' +
            '<td><button type="button" class="btn-icon" onclick="this.closest(\'tr\').remove(); recalcPurchase();" title="Remove row"><i data-lucide="x"></i></button></td>';
        document.getElementById('purchaseItemsBody').appendChild(tr);
        if (window.refreshIcons) window.refreshIcons();
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
            panel.style.display = 'flex';
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
    }

    document.getElementById('billTotal').addEventListener('input', function(){ this.dataset.touched = '1'; });

    // Start with 3 item rows
    addPurchaseItemRow();
    addPurchaseItemRow();
    addPurchaseItemRow();
</script>
@endpush