@extends('mobileshop.layout')

@section('title', 'Procurement & POs — PhoneFix Azamgarh')
@section('page-title', 'Procurement & Supplier Ledgers')

@section('page-actions')
    <button onclick="openPaymentModal({{ $suppliers->first()->id ?? 0 }}, '{{ addslashes($suppliers->first()->name ?? 'Supplier') }}')" class="btn btn-primary btn-sm">
        <i data-lucide="plus" style="width:14px;height:14px;"></i> Record Supplier Payment
    </button>
@endsection

@section('content')

<!-- Supplier Summary Cards -->
<div class="stat-grid" style="margin-bottom: 12px;">
    @foreach($suppliers as $s)
        <div class="stat-card stat-pastel-purple">
            <div class="stat-card-top" style="display:flex; justify-content:space-between; align-items:center;">
                <span class="stat-card-tag">Supplier</span>
                <div style="display:flex; gap:6px;">
                    <button onclick="openPaymentModal({{ $s->id }}, '{{ addslashes($s->name) }}')" class="btn btn-primary btn-sm" style="padding: 3px 8px; font-size:11px;">
                        + Advance
                    </button>
                    <button onclick="openEditSupplierModal({{ $s->id }}, '{{ addslashes($s->name) }}', '{{ addslashes($s->phone ?? '') }}', '{{ addslashes($s->gstin ?? '') }}', '{{ addslashes($s->address ?? '') }}', {{ (float) ($s->credit_balance ?? 0) }})" class="btn btn-outline btn-sm" style="padding: 3px 8px; font-size:11px;">
                        Edit
                    </button>
                </div>
            </div>
            <div style="font-size:17px; font-weight:800; color:#0F172A;">{{ $s->name }}</div>
            <div style="font-size:11px; color:var(--text-secondary); font-family:monospace; margin-top:2px;">GSTIN: {{ $s->gstin ?? 'N/A' }}</div>
            <div style="margin-top:8px; padding-top:8px; border-top:1px solid var(--lama-purple); display:flex; justify-content:space-between; align-items:center;">
                <span style="font-size:11px; color:var(--text-secondary); font-weight:600;">Prepaid Wallet:</span>
                <span class="badge badge-green" style="font-size:11px; font-weight:700;">₹{{ number_format($s->credit_balance ?? 0, 2) }}</span>
            </div>
        </div>
    @endforeach
</div>

<!-- Purchase Orders List Table -->
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">Purchase Orders & Goods Receipt Batches</div>
        </div>
        <div class="search-bar">
            <i data-lucide="search" style="width:15px;height:15px;"></i>
            <input type="text" placeholder="Search PO #, supplier..." oninput="onPOSearch(this.value)">
        </div>
    </div>

    <div class="data-table-wrap">
        <table class="data-table" id="poTable">
            <thead>
                <tr>
                    <th>PO Number</th>
                    <th>Supplier</th>
                    <th>Order Date</th>
                    <th style="text-align:right;">PO Total</th>
                    <th style="text-align:right;">Paid</th>
                    <th style="text-align:right;">Balance Due</th>
                    <th style="text-align:center;">Status</th>
                    <th style="text-align:center;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchaseOrders as $po)
                    <tr class="po-row">
                        <td style="font-weight:800; color:var(--brand-700);">{{ $po->po_number }}</td>
                        <td>
                            <div style="font-weight:700; color:#0F172A;">{{ $po->supplier_name }}</div>
                            <div style="font-size:11px; color:var(--text-secondary);">{{ $po->supplier_phone }}</div>
                        </td>
                        <td style="font-family:monospace; color:var(--text-secondary); font-size:12px;">{{ date('d M Y', strtotime($po->order_date)) }}</td>
                        <td style="text-align:right; font-weight:800; color:#0F172A;">₹{{ number_format($po->total_amount, 2) }}</td>
                        <td style="text-align:right; font-weight:700; color:var(--lama-green-dark);">₹{{ number_format($po->amount_paid, 2) }}</td>
                        <td style="text-align:right; font-weight:800; color: {{ $po->balance_due > 0 ? 'var(--lama-rose-dark)' : 'var(--text-secondary)' }};">
                            ₹{{ number_format($po->balance_due, 2) }}
                        </td>
                        <td style="text-align:center;">
                            <span class="badge {{ $po->status === 'paid' || $po->status === 'received' ? 'badge-green' : ($po->status === 'partially_paid' ? 'badge-orange' : 'badge-gray') }}">
                                {{ strtoupper(str_replace('_', ' ', $po->status)) }}
                            </span>
                        </td>
                        <td style="text-align:center;">
                            @if($po->balance_due > 0)
                                <button onclick="openPaymentForPO({{ $po->supplier_id }}, '{{ addslashes($po->supplier_name) }}', {{ $po->id }}, {{ $po->balance_due }})" class="btn btn-outline btn-sm">
                                    Pay PO
                                </button>
                            @else
                                <span class="badge badge-green">Settled</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align:center; padding:56px 20px;">
                            <div style="max-width:380px; margin:0 auto; display:flex; flex-direction:column; align-items:center; gap:12px;">
                                <div style="width:56px; height:56px; border-radius:50%; background:var(--lama-purple-light); display:flex; align-items:center; justify-content:center; color:var(--brand-700);">
                                    <i data-lucide="truck" style="width:28px;height:28px;"></i>
                                </div>
                                <div style="font-weight:800; font-size:16px; color:#0F172A;">No Purchase Orders Recorded</div>
                                <div style="font-size:13px; color:var(--text-secondary); line-height:1.5;">Manage procurement shipments, supplier credit wallets, and advance payments.</div>
                                <button onclick="openPaymentModal(1, 'Primary Supplier')" class="btn btn-primary btn-sm" style="margin-top:6px;">
                                    <i data-lucide="plus" style="width:14px;height:14px;"></i> Record Supplier Advance
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div id="poPagination"></div>
</div>

<!-- Supplier Payment Modal -->
<div id="paymentModal" style="display:none; position: fixed; inset: 0; z-index: 1200; background: rgba(15,23,42,0.45); backdrop-filter: blur(4px); align-items:center; justify-content:center; padding: 16px;">
    <div class="card" style="max-width: 460px; width: 100%; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
        <div class="card-header">
            <div class="card-title">Record Supplier Payment</div>
            <button onclick="closePaymentModal()" class="btn-icon">✕</button>
        </div>
        <div class="card-body">
            <form action="{{ route('mobileshop.purchase_orders.payment') }}" method="POST">
                @csrf
                <input type="hidden" name="supplier_id" id="paySupplierId">
                <input type="hidden" name="purchase_order_id" id="payPOId">

                <div style="margin-bottom: 14px; padding: 12px 14px; background:var(--lama-purple-light); border:1px solid var(--lama-purple); border-radius:10px;">
                    <span style="font-size:10px; color:var(--brand-700); text-transform:uppercase; font-weight:800;">Paying To</span>
                    <div style="font-size:14px; font-weight:800; color:#0F172A; margin-top:2px;" id="paySupplierName">-</div>
                </div>

                <div class="form-group">
                    <label class="form-label required">Amount Sent (₹)</label>
                    <input type="number" step="0.01" name="amount" id="payAmount" required placeholder="0.00" class="form-control" style="font-size:16px; font-weight:800; color:var(--lama-green-dark);">
                </div>

                <div class="form-group">
                    <label class="form-label required">Payment Mode</label>
                    <select name="payment_mode" required class="form-control" style="font-weight:600;">
                        <option value="bank_transfer">🏦 Bank Transfer / NEFT / RTGS</option>
                        <option value="upi">📱 UPI Transfer</option>
                        <option value="cheque">📝 Cheque</option>
                        <option value="cash">💵 Cash</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Reference / UTR Number</label>
                    <input type="text" name="reference_no" placeholder="e.g. UTR-948102948" class="form-control">
                </div>

                <div style="display:flex; justify-content:flex-end; gap: 10px; padding-top: 14px; border-top: 1px solid var(--card-border);">
                    <button type="button" onclick="closePaymentModal()" class="btn btn-outline">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Supplier Details & Wallet Modal -->
<div class="modal" id="editSupplierModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.55); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(3px);">
    <div class="modal-dialog" style="max-width: 440px; width: 100%; margin:20px; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 20px 40px rgba(0,0,0,0.25);">
        <div class="modal-header" style="background:#5E6AD2; color:#fff; padding:14px 18px; display:flex; justify-content:space-between; align-items:center;">
            <div style="font-weight:700; font-size:15px; display:flex; align-items:center; gap:8px;">
                <i data-lucide="truck" style="width:17px;height:17px;"></i> Edit Supplier & Wallet
            </div>
            <button type="button" onclick="closeEditSupplierModal()" style="background:none; border:none; color:#fff; cursor:pointer; font-size:20px; line-height:1;">&times;</button>
        </div>
        <div class="modal-body" style="padding: 18px;">
            <form action="{{ route('mobileshop.supplier.update') }}" method="POST">
                @csrf
                <input type="hidden" name="supplier_id" id="editSupplierId">

                <div class="form-group" style="margin-bottom:12px;">
                    <label class="form-label required" style="font-weight:700; font-size:12px;">Supplier Name</label>
                    <input type="text" name="name" id="editSupplierName" required class="form-control" style="font-size:13px;">
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:12px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight:700; font-size:12px;">Phone</label>
                        <input type="text" name="phone" id="editSupplierPhone" class="form-control" style="font-size:13px;">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight:700; font-size:12px;">GSTIN</label>
                        <input type="text" name="gstin" id="editSupplierGstin" class="form-control" style="font-size:13px;">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:12px;">
                    <label class="form-label" style="font-weight:700; font-size:12px;">Address</label>
                    <input type="text" name="address" id="editSupplierAddress" class="form-control" style="font-size:13px;">
                </div>

                <div style="background:#F0FDF4; border:1px solid #BBF7D0; border-radius:8px; padding:12px; margin-bottom:12px;">
                    <label class="form-label" style="font-weight:700; font-size:12px; color:#166534;">Prepaid Advance Wallet (₹)</label>
                    <input type="number" step="0.01" min="0" name="credit_balance" id="editSupplierCredit" class="form-control" style="font-weight:800; font-size:16px; color:#15803D;">
                    <div style="font-size:10.5px; color:#166534; margin-top:4px;">Direct balance modification records an audit transaction in the supplier credit ledger.</div>
                </div>

                <div class="form-group" style="margin-bottom:14px;">
                    <label class="form-label" style="font-weight:700; font-size:12px;">Adjustment Reason / Note</label>
                    <input type="text" name="adjustment_notes" class="form-control" placeholder="e.g. Ledger reconciliation" style="font-size:13px;">
                </div>

                <div style="display:flex; justify-content:flex-end; gap: 10px; padding-top: 14px; border-top: 1px solid var(--card-border);">
                    <button type="button" onclick="closeEditSupplierModal()" class="btn btn-outline btn-sm">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm" style="background:#5E6AD2;">Update Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function onPOSearch(term) {
        const q = (term || '').toLowerCase().trim();
        document.querySelectorAll('#poTable tbody tr.po-row').forEach(row => {
            row.dataset.mobiHidden = (!q || row.textContent.toLowerCase().includes(q)) ? '0' : '1';
        });
        if (window.poPager) window.poPager.refresh();
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (window.setupMobiTablePagination) {
            window.poPager = window.setupMobiTablePagination({
                tableId: 'poTable',
                paginationContainerId: 'poPagination',
                rowSelector: 'tbody tr.po-row',
                pageSize: 25,
                itemName: 'purchase orders'
            });
        }
        if (window.lucide) window.lucide.createIcons();
    });

    function openPaymentModal(supplierId, supplierName) {
        document.getElementById('paySupplierId').value = supplierId;
        document.getElementById('payPOId').value = '';
        document.getElementById('paySupplierName').textContent = supplierName + ' (Direct Advance to Wallet)';
        document.getElementById('payAmount').value = '';
        document.getElementById('paymentModal').style.display = 'flex';
        if (window.lucide) window.lucide.createIcons();
    }

    function openPaymentForPO(supplierId, supplierName, poId, due) {
        document.getElementById('paySupplierId').value = supplierId;
        document.getElementById('payPOId').value = poId;
        document.getElementById('paySupplierName').textContent = supplierName + ' (PO #' + poId + ' — Due: ₹' + due.toFixed(2) + ')';
        document.getElementById('payAmount').value = due.toFixed(2);
        document.getElementById('paymentModal').style.display = 'flex';
        if (window.lucide) window.lucide.createIcons();
    }

    function closePaymentModal() {
        document.getElementById('paymentModal').style.display = 'none';
    }

    function openEditSupplierModal(id, name, phone, gstin, address, balance) {
        document.getElementById('editSupplierId').value = id;
        document.getElementById('editSupplierName').value = name;
        document.getElementById('editSupplierPhone').value = phone || '';
        document.getElementById('editSupplierGstin').value = gstin || '';
        document.getElementById('editSupplierAddress').value = address || '';
        document.getElementById('editSupplierCredit').value = balance || 0;
        document.getElementById('editSupplierModal').style.display = 'flex';
        if (window.lucide) window.lucide.createIcons();
    }

    function closeEditSupplierModal() {
        document.getElementById('editSupplierModal').style.display = 'none';
    }

</script>
@endpush
