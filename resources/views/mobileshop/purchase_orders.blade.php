@extends('mobileshop.layout')

@section('title', 'Procurement & POs — MobiTrack')
@section('page-title', 'Procurement & Supplier Ledgers')

@section('page-actions')
    <button onclick="openPaymentModal({{ $suppliers->first()->id ?? 0 }}, '{{ addslashes($suppliers->first()->name ?? 'Supplier') }}')" class="btn btn-primary btn-sm">
        <i data-lucide="plus" style="width:14px;height:14px;"></i> Record Supplier Payment
    </button>
@endsection

@section('content')

<!-- Supplier Summary Cards -->
<div class="stat-grid" style="margin-bottom: 24px;">
    @foreach($suppliers as $s)
        <div class="stat-card stat-pastel-purple">
            <div class="stat-card-top">
                <span class="stat-card-tag">Supplier</span>
                <button onclick="openPaymentModal({{ $s->id }}, '{{ addslashes($s->name) }}')" class="btn btn-primary btn-sm" style="padding: 3px 8px; font-size:11px;">
                    + Advance
                </button>
            </div>
            <div style="font-size:17px; font-weight:800; color:#0F172A;">{{ $s->name }}</div>
            <div style="font-size:11px; color:var(--text-secondary); font-family:monospace; margin-top:2px;">GSTIN: {{ $s->gstin }}</div>
            <div style="margin-top:8px; padding-top:8px; border-top:1px solid var(--lama-purple); display:flex; justify-content:space-between; align-items:center;">
                <span style="font-size:11px; color:var(--text-secondary); font-weight:600;">Prepaid Wallet:</span>
                <span class="badge badge-green" style="font-size:11px; font-weight:700;">₹{{ number_format($s->credit_balance, 2) }}</span>
            </div>
        </div>
    @endforeach
</div>

<!-- Purchase Orders List Table -->
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">Purchase Orders & Goods Receipt Batches</div>
            <div class="card-subtitle">Showing {{ $purchaseOrders->count() }} procurement records</div>
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
<div id="paymentModal" style="display:none; position: fixed; inset: 0; z-index: 200; background: rgba(15,23,42,0.45); backdrop-filter: blur(4px); align-items:center; justify-content:center; padding: 16px;">
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
    }

    function openPaymentForPO(supplierId, supplierName, poId, due) {
        document.getElementById('paySupplierId').value = supplierId;
        document.getElementById('payPOId').value = poId;
        document.getElementById('paySupplierName').textContent = supplierName + ' (PO #' + poId + ' — Due: ₹' + due.toFixed(2) + ')';
        document.getElementById('payAmount').value = due.toFixed(2);
        document.getElementById('paymentModal').style.display = 'flex';
    }

    function closePaymentModal() {
        document.getElementById('paymentModal').style.display = 'none';
    }

</script>
@endpush
