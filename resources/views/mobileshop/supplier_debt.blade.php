@extends('mobileshop.layout')
@section('title', 'Supplier Debt & Ledger — PhoneFix Azamgarh')
@section('page-title', 'Supplier Debt & Ledger')

@section('page-actions')
    <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        <button type="button" onclick="openAddSupplierModal()" class="btn btn-outline btn-sm" style="display:inline-flex; align-items:center; gap:6px;">
            <i data-lucide="user-plus" style="width:14px;height:14px;"></i> Add Supplier
        </button>
        <a href="{{ route('mobileshop.purchase') }}" class="btn btn-primary btn-sm" style="display:inline-flex; align-items:center; gap:6px;">
            <i data-lucide="truck" style="width:14px;height:14px;"></i> Go to Purchase Hub
        </a>
    </div>
@endsection

@push('styles')
<style>
.sdebt-kpi-row { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:22px; }
@media(max-width:900px){ .sdebt-kpi-row { grid-template-columns:repeat(2,1fr); } }
.sdebt-kpi { background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:14px 16px; box-shadow:0 1px 3px rgba(0,0,0,.04); }
.sdebt-kpi-label { font-size:10.5px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#94a3b8; margin-bottom:6px; }
.sdebt-kpi-value { font-size:22px; font-weight:800; color:#0f172a; line-height:1; }
.sdebt-kpi-value.red{ color:#dc2626; } .sdebt-kpi-value.green{ color:#16a34a; } .sdebt-kpi-value.indigo{ color:#4f46e5; }
.sdebt-kpi-sub { font-size:11px; color:#94a3b8; margin-top:4px; }
.sdebt-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(320px,1fr)); gap:16px; margin-bottom:24px; }
.sdebt-card { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:18px 20px; box-shadow:0 1px 3px rgba(0,0,0,.05); transition:box-shadow .15s; position:relative; overflow:hidden; }
.sdebt-card:hover { box-shadow:0 4px 16px rgba(0,0,0,.09); }
.sdebt-card-badge { position:absolute; top:14px; right:14px; font-size:10px; font-weight:800; padding:3px 8px; border-radius:20px; letter-spacing:.04em; text-transform:uppercase; }
.badge-debt-red { background:#fef2f2; color:#dc2626; border:1px solid #fecaca; }
.badge-debt-green { background:#f0fdf4; color:#16a34a; border:1px solid #bbf7d0; }
.badge-debt-gray { background:#f8fafc; color:#64748b; border:1px solid #e2e8f0; }
.sdebt-name { font-size:16px; font-weight:800; color:#0f172a; margin-bottom:2px; padding-right:70px; }
.sdebt-meta { font-size:11px; color:#94a3b8; margin-bottom:14px; }
.sdebt-row { display:flex; justify-content:space-between; align-items:center; padding:7px 0; border-top:1px solid #f1f5f9; }
.sdebt-row-label { font-size:12px; color:#64748b; font-weight:600; }
.sdebt-row-val { font-size:14px; font-weight:800; color:#0f172a; }
.sdebt-row-val.negative{ color:#dc2626; } .sdebt-row-val.positive{ color:#16a34a; }
.sdebt-actions { display:flex; gap:8px; margin-top:14px; padding-top:12px; border-top:1px solid #f1f5f9; }
.sdebt-table-wrap { overflow-x:auto; }
.sdebt-table { width:100%; border-collapse:collapse; font-size:13px; }
.sdebt-table thead th { background:#f8fafc; padding:9px 12px; text-align:left; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#64748b; border-bottom:1px solid #e2e8f0; white-space:nowrap; }
.sdebt-table tbody tr { border-bottom:1px solid #f1f5f9; transition:background .1s; }
.sdebt-table tbody tr:hover{ background:#fafafe; }
.sdebt-table td { padding:9px 12px; vertical-align:middle; }
.sdebt-modal-backdrop { display:none; position:fixed; inset:0; background:rgba(15,23,42,.45); backdrop-filter:blur(4px); z-index:1300; align-items:center; justify-content:center; padding:16px; }
.sdebt-modal-box { background:#fff; border-radius:14px; box-shadow:0 20px 40px rgba(0,0,0,.2); max-width:480px; width:100%; overflow:hidden; }
.sdebt-modal-header { display:flex; justify-content:space-between; align-items:center; padding:16px 20px; background:#4f46e5; color:#fff; }
.sdebt-modal-title { font-size:15px; font-weight:800; display:flex; align-items:center; gap:8px; }
.sdebt-modal-close { background:none; border:none; color:#fff; font-size:22px; cursor:pointer; line-height:1; padding:0; }
.sdebt-modal-body { padding:20px; }
.sdebt-form-group { margin-bottom:13px; }
.sdebt-form-label { display:block; font-size:11.5px; font-weight:700; color:#475569; text-transform:uppercase; letter-spacing:.04em; margin-bottom:5px; }
.sdebt-form-input { width:100%; padding:9px 11px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; font-family:inherit; background:#fff; color:#0f172a; outline:none; box-sizing:border-box; transition:border-color .15s; }
.sdebt-form-input:focus{ border-color:#4f46e5; box-shadow:0 0 0 3px rgba(79,70,229,.08); }
.sdebt-form-row { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
.sdebt-modal-footer { display:flex; justify-content:flex-end; gap:10px; padding:14px 20px; border-top:1px solid #f1f5f9; }
.sdebt-filter-bar { display:flex; gap:10px; align-items:center; margin-bottom:16px; flex-wrap:wrap; }
.sdebt-filter-input { padding:8px 12px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; font-family:inherit; color:#0f172a; outline:none; background:#fff; width:220px; }
.sdebt-filter-input:focus{ border-color:#4f46e5; }
</style>
@endpush

@section('content')

@if(session('success'))
<div style="margin-bottom:16px; padding:12px 16px; background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; border-radius:8px; font-size:13px; font-weight:600;">
    <i data-lucide="check-circle" style="width:14px;height:14px;vertical-align:middle;margin-right:6px;"></i>{{ session('success') }}
</div>
@endif
@if(session('error'))
<div style="margin-bottom:16px; padding:12px 16px; background:#fef2f2; border:1px solid #fecaca; color:#dc2626; border-radius:8px; font-size:13px; font-weight:600;">
    <i data-lucide="alert-circle" style="width:14px;height:14px;vertical-align:middle;margin-right:6px;"></i>{{ session('error') }}
</div>
@endif

<div class="sdebt-kpi-row">
    <div class="sdebt-kpi">
        <div class="sdebt-kpi-label">Total Suppliers</div>
        <div class="sdebt-kpi-value indigo">{{ $suppliers->count() }}</div>
        <div class="sdebt-kpi-sub">Active supplier accounts</div>
    </div>
    <div class="sdebt-kpi">
        <div class="sdebt-kpi-label">Total Outstanding Debt</div>
        <div class="sdebt-kpi-value red">₹{{ number_format($totalOutstanding, 2) }}</div>
        <div class="sdebt-kpi-sub">Sum of all unpaid PO balances</div>
    </div>
    <div class="sdebt-kpi">
        <div class="sdebt-kpi-label">Prepaid Wallets</div>
        <div class="sdebt-kpi-value green">₹{{ number_format($totalWallets, 2) }}</div>
        <div class="sdebt-kpi-sub">Advance credited across suppliers</div>
    </div>
    <div class="sdebt-kpi">
        <div class="sdebt-kpi-label">Net Payable</div>
        <div class="sdebt-kpi-value {{ ($totalOutstanding - $totalWallets) > 0 ? 'red' : 'green' }}">
            ₹{{ number_format(max(0, $totalOutstanding - $totalWallets), 2) }}
        </div>
        <div class="sdebt-kpi-sub">After netting advance wallets</div>
    </div>
</div>

@if($suppliers->isEmpty())
<div class="card" style="padding:60px 24px; text-align:center;">
    <div style="width:60px;height:60px;border-radius:50%;background:#eef2ff;display:flex;align-items:center;justify-content:center;color:#4f46e5;margin:0 auto 14px;">
        <i data-lucide="truck" style="width:28px;height:28px;"></i>
    </div>
    <div style="font-size:17px;font-weight:800;color:#0f172a;margin-bottom:6px;">No Suppliers Found</div>
    <div style="font-size:13px;color:#94a3b8;max-width:380px;margin:0 auto 18px;">Add your first supplier to track purchase debt and manage payments.</div>
    <button onclick="openAddSupplierModal()" class="btn btn-primary btn-sm">
        <i data-lucide="user-plus" style="width:14px;height:14px;"></i> Add Supplier
    </button>
</div>
@else
<div class="sdebt-filter-bar">
    <input type="text" id="supplierSearchInput" class="sdebt-filter-input" placeholder="Search supplier..." oninput="filterSupplierCards()">
    <select id="debtStatusFilter" class="sdebt-filter-input" style="width:160px;" onchange="filterSupplierCards()">
        <option value="">All Suppliers</option>
        <option value="outstanding">With Debt</option>
        <option value="settled">Settled</option>
    </select>
</div>

<div class="sdebt-grid" id="supplierCardsGrid">
    @foreach($suppliers as $s)
    @php
        $outstanding = (float)($s->outstanding_balance ?? 0);
        $wallet      = (float)($s->advance_balance ?? 0);
        $netPayable  = max(0, $outstanding - $wallet);
        $statusBadge = $outstanding > 0 ? 'badge-debt-red' : ($wallet > 0 ? 'badge-debt-green' : 'badge-debt-gray');
        $statusText  = $outstanding > 0 ? 'Has Debt' : ($wallet > 0 ? 'Credit Available' : 'Settled');
    @endphp
    <div class="sdebt-card" data-name="{{ strtolower($s->name) }}" data-debt="{{ $outstanding > 0 ? 'outstanding' : 'settled' }}">
        <span class="sdebt-card-badge {{ $statusBadge }}">{{ $statusText }}</span>
        <div class="sdebt-name">{{ $s->name }}</div>
        <div class="sdebt-meta">
            @if($s->phone)📞 {{ $s->phone }}@endif
            @if($s->gstin) &nbsp;|&nbsp; GSTIN: {{ $s->gstin }}@endif
            @if($s->address)<br>📍 {{ Str::limit($s->address, 60) }}@endif
        </div>
        <div class="sdebt-row">
            <span class="sdebt-row-label">Unpaid PO Balance</span>
            <span class="sdebt-row-val {{ $outstanding > 0 ? 'negative' : '' }}">₹{{ number_format($outstanding, 2) }}</span>
        </div>
        <div class="sdebt-row">
            <span class="sdebt-row-label">Prepaid Wallet (Advance)</span>
            <span class="sdebt-row-val {{ $wallet > 0 ? 'positive' : '' }}">₹{{ number_format($wallet, 2) }}</span>
        </div>
        <div class="sdebt-row" style="border-top:2px solid #e2e8f0; margin-top:2px; padding-top:9px;">
            <span class="sdebt-row-label" style="font-weight:800; color:#0f172a;">Net Payable</span>
            <span class="sdebt-row-val {{ $netPayable > 0 ? 'negative' : 'positive' }}" style="font-size:16px;">
                ₹{{ number_format($netPayable, 2) }}
            </span>
        </div>
        <div class="sdebt-actions">
            <button onclick="openPaymentModal({{ $s->id }}, {{ json_encode($s->name) }}, 0, {{ $netPayable }})"
                    class="btn btn-primary btn-sm" style="flex:1; display:inline-flex; align-items:center; justify-content:center; gap:6px; font-size:12px;">
                <i data-lucide="credit-card" style="width:13px;height:13px;"></i> Pay Debt
            </button>
            <button onclick="openAdvanceModal({{ $s->id }}, {{ json_encode($s->name) }})"
                    class="btn btn-outline btn-sm" style="flex:1; display:inline-flex; align-items:center; justify-content:center; gap:6px; font-size:12px;">
                <i data-lucide="wallet" style="width:13px;height:13px;"></i> Add Advance
            </button>
            <button onclick="openEditSupplierModal({{ $s->id }}, {{ json_encode($s->name) }}, {{ json_encode($s->phone ?? '') }}, {{ json_encode($s->gstin ?? '') }}, {{ json_encode($s->address ?? '') }}, {{ $wallet }})"
                    class="btn btn-outline btn-sm" style="display:inline-flex; align-items:center; gap:4px; font-size:12px; padding:6px 10px;">
                <i data-lucide="edit-2" style="width:13px;height:13px;"></i>
            </button>
        </div>
    </div>
    @endforeach
</div>
@endif

<div class="card">
    <div class="card-header" style="flex-wrap:wrap; gap:10px;">
        <div>
            <div class="card-title">Payment History</div>
            <div style="font-size:11px; color:var(--text-secondary); margin-top:2px;">All supplier payments recorded</div>
        </div>
        <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
            <input type="text" id="payHistorySearch" class="sdebt-filter-input" style="width:180px;" placeholder="Search..." oninput="filterPayHistory()">
            <select id="payHistorySupplier" class="sdebt-filter-input" style="width:160px;" onchange="filterPayHistory()">
                <option value="">All Suppliers</option>
                @foreach($suppliers as $s)
                <option value="{{ strtolower($s->name) }}">{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="sdebt-table-wrap">
        @if($payments->isEmpty())
        <div style="padding:50px 24px; text-align:center;">
            <div style="font-size:15px; font-weight:700; color:#94a3b8; margin-bottom:6px;">No payments recorded yet</div>
            <div style="font-size:12px; color:#cbd5e1;">Use "Pay Debt" or "Add Advance" on a supplier card.</div>
        </div>
        @else
        <table class="sdebt-table" id="payHistoryTable">
            <thead>
                <tr>
                    <th>Date</th><th>Supplier</th><th>Mode</th>
                    <th style="text-align:right;">Amount</th>
                    <th>Linked PO</th><th>Reference</th><th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $pay)
                @php
                    $ml = match($pay->mode) {
                        'bank_transfer' => '🏦 Bank',
                        'upi' => '📱 UPI',
                        'cheque' => '📝 Cheque',
                        'cash' => '💵 Cash',
                        default => ucfirst($pay->mode ?? '—'),
                    };
                @endphp
                <tr class="pay-row"
                    data-supplier="{{ strtolower($pay->supplier_name ?? '') }}"
                    data-text="{{ strtolower(($pay->supplier_name ?? '').' '.($pay->reference_no ?? '').' '.($pay->remarks ?? '')) }}">
                    <td style="white-space:nowrap;font-family:monospace;color:#64748b;font-size:12px;">{{ date('d M Y', strtotime($pay->payment_date)) }}</td>
                    <td style="font-weight:700;color:#0f172a;">{{ $pay->supplier_name ?? '—' }}</td>
                    <td><span style="font-size:12px;font-weight:600;color:#475569;">{{ $ml }}</span></td>
                    <td style="text-align:right;font-weight:800;color:#16a34a;font-size:14px;">₹{{ number_format($pay->amount, 2) }}</td>
                    <td style="font-family:monospace;font-size:12px;color:#4f46e5;">
                        @if($pay->po_number)
                            <span style="background:#eef2ff;padding:2px 8px;border-radius:4px;font-weight:700;">{{ $pay->po_number }}</span>
                        @else
                            <span style="color:#cbd5e1;">Advance</span>
                        @endif
                    </td>
                    <td style="font-size:12px;color:#64748b;font-family:monospace;">{{ $pay->reference_no ?: '—' }}</td>
                    <td style="font-size:12px;color:#94a3b8;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $pay->remarks ?? '' }}">{{ Str::limit($pay->remarks ?? '—', 40) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>

{{-- Pay Debt Modal --}}
<div class="sdebt-modal-backdrop" id="payDebtModal" style="display:none;">
    <div class="sdebt-modal-box">
        <div class="sdebt-modal-header" style="background:#dc2626;">
            <div class="sdebt-modal-title"><i data-lucide="credit-card" style="width:16px;height:16px;"></i> Pay Supplier Debt</div>
            <button class="sdebt-modal-close" onclick="closePayDebtModal()">x</button>
        </div>
        <div class="sdebt-modal-body">
            <form action="{{ route('mobileshop.purchase_orders.payment', ['company_id' => company_id()]) }}" method="POST">
                @csrf
                <input type="hidden" name="supplier_id" id="payDebtSupplierId">
                <input type="hidden" name="purchase_order_id" id="payDebtPoId" value="">
                <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px 14px;margin-bottom:16px;">
                    <div style="font-size:10px;font-weight:800;color:#dc2626;text-transform:uppercase;margin-bottom:2px;">Paying To</div>
                    <div style="font-size:15px;font-weight:800;color:#0f172a;" id="payDebtSupplierName">—</div>
                    <div style="font-size:11px;color:#dc2626;margin-top:4px;">Outstanding: <b id="payDebtAmountDue">₹0.00</b></div>
                </div>
                <div class="sdebt-form-group">
                    <label class="sdebt-form-label">Amount (₹) *</label>
                    <input type="number" step="0.01" min="0.01" name="amount" id="payDebtAmount" required class="sdebt-form-input" placeholder="Amount to pay">
                </div>
                <div class="sdebt-form-group">
                    <label class="sdebt-form-label">Payment Mode *</label>
                    <select name="payment_mode" required class="sdebt-form-input">
                        <option value="cash">Cash</option>
                        <option value="upi">UPI</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="cheque">Cheque</option>
                    </select>
                </div>
                <div class="sdebt-form-group">
                    <label class="sdebt-form-label">Reference / UTR</label>
                    <input type="text" name="reference_no" class="sdebt-form-input" placeholder="Optional">
                </div>
                <div class="sdebt-form-group">
                    <label class="sdebt-form-label">Remarks</label>
                    <input type="text" name="remarks" class="sdebt-form-input" placeholder="Optional">
                </div>
                <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:4px;">
                    <button type="button" onclick="closePayDebtModal()" class="btn btn-outline">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="background:#dc2626;border-color:#dc2626;">Record Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Add Advance Modal --}}
<div class="sdebt-modal-backdrop" id="addAdvanceModal" style="display:none;">
    <div class="sdebt-modal-box">
        <div class="sdebt-modal-header" style="background:#16a34a;">
            <div class="sdebt-modal-title"><i data-lucide="wallet" style="width:16px;height:16px;"></i> Add Advance to Wallet</div>
            <button class="sdebt-modal-close" onclick="closeAdvanceModal()">x</button>
        </div>
        <div class="sdebt-modal-body">
            <form action="{{ route('mobileshop.purchase_orders.payment', ['company_id' => company_id()]) }}" method="POST">
                @csrf
                <input type="hidden" name="supplier_id" id="advanceSupplierId">
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px 14px;margin-bottom:16px;">
                    <div style="font-size:10px;font-weight:800;color:#16a34a;text-transform:uppercase;margin-bottom:2px;">Adding Advance For</div>
                    <div style="font-size:15px;font-weight:800;color:#0f172a;" id="advanceSupplierName">—</div>
                    <div style="font-size:11px;color:#16a34a;margin-top:4px;">Amount will be credited to their prepaid wallet.</div>
                </div>
                <div class="sdebt-form-group">
                    <label class="sdebt-form-label">Advance Amount (₹) *</label>
                    <input type="number" step="0.01" min="0.01" name="amount" required class="sdebt-form-input" placeholder="Amount">
                </div>
                <div class="sdebt-form-group">
                    <label class="sdebt-form-label">Payment Mode *</label>
                    <select name="payment_mode" required class="sdebt-form-input">
                        <option value="cash">Cash</option>
                        <option value="upi">UPI</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="cheque">Cheque</option>
                    </select>
                </div>
                <div class="sdebt-form-group">
                    <label class="sdebt-form-label">Reference / UTR</label>
                    <input type="text" name="reference_no" class="sdebt-form-input" placeholder="Optional">
                </div>
                <div class="sdebt-form-group">
                    <label class="sdebt-form-label">Remarks</label>
                    <input type="text" name="remarks" class="sdebt-form-input" placeholder="Optional">
                </div>
                <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:4px;">
                    <button type="button" onclick="closeAdvanceModal()" class="btn btn-outline">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="background:#16a34a;border-color:#16a34a;">Add Advance</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Supplier Modal --}}
<div class="sdebt-modal-backdrop" id="editSupplierModal" style="display:none;">
    <div class="sdebt-modal-box">
        <div class="sdebt-modal-header">
            <div class="sdebt-modal-title"><i data-lucide="edit-2" style="width:16px;height:16px;"></i> Edit Supplier</div>
            <button class="sdebt-modal-close" onclick="closeEditSupplierModal()">x</button>
        </div>
        <div class="sdebt-modal-body">
            <form action="{{ route('mobileshop.supplier.update', ['company_id' => company_id()]) }}" method="POST">
                @csrf
                <input type="hidden" name="supplier_id" id="editSupplierId">
                <div class="sdebt-form-group">
                    <label class="sdebt-form-label">Supplier Name *</label>
                    <input type="text" name="name" id="editSupplierName" required class="sdebt-form-input">
                </div>
                <div class="sdebt-form-row">
                    <div class="sdebt-form-group">
                        <label class="sdebt-form-label">Phone</label>
                        <input type="text" name="phone" id="editSupplierPhone" class="sdebt-form-input">
                    </div>
                    <div class="sdebt-form-group">
                        <label class="sdebt-form-label">GSTIN</label>
                        <input type="text" name="gstin" id="editSupplierGstin" class="sdebt-form-input">
                    </div>
                </div>
                <div class="sdebt-form-group">
                    <label class="sdebt-form-label">Address</label>
                    <input type="text" name="address" id="editSupplierAddress" class="sdebt-form-input">
                </div>
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px 14px;margin-bottom:13px;">
                    <label class="sdebt-form-label" style="color:#166534;">Prepaid Wallet Balance (₹)</label>
                    <input type="number" step="0.01" min="0" name="credit_balance" id="editSupplierCredit" class="sdebt-form-input" style="font-weight:800;font-size:16px;color:#15803d;">
                    <div style="font-size:10.5px;color:#166534;margin-top:5px;">Adjusting this records an audit transaction.</div>
                </div>
                <div class="sdebt-form-group">
                    <label class="sdebt-form-label">Adjustment Reason / Note</label>
                    <input type="text" name="adjustment_notes" class="sdebt-form-input" placeholder="e.g. Opening balance correction">
                </div>
                <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:4px;">
                    <button type="button" onclick="closeEditSupplierModal()" class="btn btn-outline">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Add New Supplier Modal --}}
<div class="sdebt-modal-backdrop" id="addSupplierModal" style="display:none;">
    <div class="sdebt-modal-box">
        <div class="sdebt-modal-header">
            <div class="sdebt-modal-title"><i data-lucide="user-plus" style="width:16px;height:16px;"></i> Add New Supplier</div>
            <button class="sdebt-modal-close" onclick="closeAddSupplierModal()">x</button>
        </div>
        <div class="sdebt-modal-body">
            <form action="{{ route('mobileshop.supplier.store', ['company_id' => company_id()]) }}" method="POST">
                @csrf
                <div class="sdebt-form-group">
                    <label class="sdebt-form-label">Supplier Name *</label>
                    <input type="text" name="name" required class="sdebt-form-input" placeholder="e.g. Rashid Traders">
                </div>
                <div class="sdebt-form-row">
                    <div class="sdebt-form-group">
                        <label class="sdebt-form-label">Phone</label>
                        <input type="text" name="phone" class="sdebt-form-input" placeholder="Phone number">
                    </div>
                    <div class="sdebt-form-group">
                        <label class="sdebt-form-label">GSTIN</label>
                        <input type="text" name="gstin" class="sdebt-form-input" placeholder="Optional">
                    </div>
                </div>
                <div class="sdebt-form-group">
                    <label class="sdebt-form-label">Address</label>
                    <input type="text" name="address" class="sdebt-form-input" placeholder="Supplier city / address">
                </div>
                <div class="sdebt-form-group">
                    <label class="sdebt-form-label">Notes</label>
                    <input type="text" name="notes" class="sdebt-form-input" placeholder="Any notes">
                </div>
                <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:4px;">
                    <button type="button" onclick="closeAddSupplierModal()" class="btn btn-outline">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function filterSupplierCards() {
    const q = (document.getElementById('supplierSearchInput')?.value || '').toLowerCase();
    const status = document.getElementById('debtStatusFilter')?.value || '';
    document.querySelectorAll('#supplierCardsGrid .sdebt-card').forEach(card => {
        const name = card.dataset.name || '';
        const debt = card.dataset.debt || '';
        card.style.display = ((!q || name.includes(q)) && (!status || debt === status)) ? '' : 'none';
    });
}
function filterPayHistory() {
    const q = (document.getElementById('payHistorySearch')?.value || '').toLowerCase();
    const s = (document.getElementById('payHistorySupplier')?.value || '').toLowerCase();
    document.querySelectorAll('#payHistoryTable .pay-row').forEach(row => {
        const text = row.dataset.text || '';
        const supplier = row.dataset.supplier || '';
        row.style.display = ((!q || text.includes(q)) && (!s || supplier === s)) ? '' : 'none';
    });
}
function openPaymentModal(supplierId, supplierName, poId, due) {
    document.getElementById('payDebtSupplierId').value = supplierId;
    document.getElementById('payDebtPoId').value = poId || '';
    document.getElementById('payDebtSupplierName').textContent = supplierName;
    document.getElementById('payDebtAmountDue').textContent = '₹' + (due || 0).toFixed(2);
    document.getElementById('payDebtAmount').value = due > 0 ? due.toFixed(2) : '';
    document.getElementById('payDebtModal').style.display = 'flex';
    if (window.lucide) window.lucide.createIcons();
    setTimeout(() => document.getElementById('payDebtAmount')?.focus(), 100);
}
function closePayDebtModal() { document.getElementById('payDebtModal').style.display = 'none'; }
function openAdvanceModal(supplierId, supplierName) {
    document.getElementById('advanceSupplierId').value = supplierId;
    document.getElementById('advanceSupplierName').textContent = supplierName;
    document.getElementById('addAdvanceModal').style.display = 'flex';
    if (window.lucide) window.lucide.createIcons();
}
function closeAdvanceModal() { document.getElementById('addAdvanceModal').style.display = 'none'; }
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
function closeEditSupplierModal() { document.getElementById('editSupplierModal').style.display = 'none'; }
function openAddSupplierModal() {
    document.getElementById('addSupplierModal').style.display = 'flex';
    if (window.lucide) window.lucide.createIcons();
}
function closeAddSupplierModal() { document.getElementById('addSupplierModal').style.display = 'none'; }
['payDebtModal','addAdvanceModal','editSupplierModal','addSupplierModal'].forEach(id => {
    document.getElementById(id)?.addEventListener('click', function(e) { if (e.target === this) this.style.display = 'none'; });
});
document.addEventListener('DOMContentLoaded', function() { if (window.lucide) window.lucide.createIcons(); });
</script>
@endpush
