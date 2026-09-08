@extends('mobileshop.layout')

@section('title', 'Register Sale — MobiTrack')
@section('page-title', 'Register Sale')

@section('page-actions')
    <a href="{{ route('mobileshop.sales') }}" class="btn btn-outline btn-sm">
        <i data-lucide="arrow-left" style="width:13px;height:13px;"></i> Back to Sales
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

    <form method="POST" action="{{ route('mobileshop.sales.store_multi') }}" id="multiSaleForm">
        @csrf

        <!-- ─── CUSTOMER SECTION ─── -->
        <div class="card" style="margin-bottom:18px;">
            <div class="card-header">
                <div>
                    <div class="card-title"><i data-lucide="user" style="width:16px;height:16px; vertical-align:-2px; color:var(--color-primary);"></i> Customer Details</div>
                </div>
            </div>
            <div class="card-body">
                <div class="form-row" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
                    <div class="form-group">
                        <label class="form-label">Customer Phone <span style="color:#EF4444;">*</span></label>
                        <input type="text" class="form-control" name="customer_phone" id="customerPhone" list="customerPhoneList" required placeholder="10-digit mobile">
                        <datalist id="customerPhoneList">
                            @foreach($customers as $c)
                                <option value="{{ $c->phone }}">{{ $c->name }} — Bal: ₹{{ number_format($c->udhari_balance, 0) }}</option>
                            @endforeach
                        </datalist>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Customer Name <span style="color:#EF4444;">*</span></label>
                        <input type="text" class="form-control" name="customer_name" id="customerName" required placeholder="Full name">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Current Khata Balance</label>
                        <input type="text" class="form-control" id="customerBalanceDisplay" readonly value="—" style="background:#F8FAFC; font-weight:800;">
                    </div>
                </div>
            </div>
        </div>

        <!-- ─── DEVICE SELECTION (FULL SPACE) ─── -->
        <div class="card" style="margin-bottom:18px;">
            <div class="card-header">
                <div>
                    <div class="card-title"><i data-lucide="smartphone" style="width:16px;height:16px; vertical-align:-2px; color:var(--color-primary);"></i> Select Devices In This Sale ({{ $inStockDevices->count() }} in stock)</div>
                </div>
                <input type="text" class="form-control" id="deviceSearch" placeholder="Search brand / model / IMEI…" style="max-width:280px;" oninput="filterDeviceTable()">
            </div>
            <div class="card-body" style="padding:0; overflow-x:auto; max-height: 460px; overflow-y:auto;">
                <table class="data-table" id="deviceTable" style="min-width:900px;">
                    <thead>
                        <tr>
                            <th style="width:5%;">Sell</th>
                            <th style="width:16%;">Brand</th>
                            <th style="width:20%;">Model</th>
                            <th style="width:8%;">Variant</th>
                            <th style="width:12%;">IMEI 1</th>
                            <th style="width:12%;">Cost (₹)</th>
                            <th style="width:14%;">Sale Price (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inStockDevices as $d)
                        <tr class="device-row" data-search="{{ strtolower($d->brand . ' ' . $d->model . ' ' . $d->imei_1) }}">
                            <td style="text-align:center;">
                                <input type="checkbox" class="dev-check" name="device_ids[]" value="{{ $d->id }}"
                                       data-price="{{ $d->selling_price }}" onchange="onDeviceToggle(this)">
                            </td>
                            <td style="font-weight:700;">{{ $d->brand }}</td>
                            <td>{{ $d->model }}</td>
                            <td style="font-size:11px; color:#64748B;">{{ $d->ram ? $d->ram . '/' . $d->storage : 'Std' }} {{ $d->color }}</td>
                            <td style="font-family:monospace; font-size:11px;">{{ $d->imei_1 }}</td>
                            <td style="font-family:monospace;">₹{{ number_format($d->purchase_cost, 0) }}</td>
                            <td>
                                <input type="number" class="form-control dev-price" name="sale_prices[{{ $d->id }}]"
                                       value="{{ $d->selling_price }}" step="0.01" min="1"
                                       disabled onchange="recalcSale()"
                                       style="padding:6px 8px; max-width:130px;">
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" style="text-align:center; padding:32px; color:#94A3B8;">No new phones in stock. Register a purchase first.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ─── PAYMENT ─── -->
        <div class="card" style="margin-bottom:18px;">
            <div class="card-header">
                <div>
                    <div class="card-title"><i data-lucide="wallet" style="width:16px;height:16px; vertical-align:-2px; color:var(--color-primary);"></i> Payment</div>
                </div>
            </div>
            <div class="card-body">
                <div class="form-row" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                    <div class="form-group">
                        <label class="form-label">Bill Total</label>
                        <input type="text" class="form-control" id="sumBillTotal" readonly value="₹0.00" style="background:#F8FAFC; font-weight:900; font-size:18px;">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Amount Paid Now (₹) <span style="color:#EF4444;">*</span></label>
                        <input type="number" step="0.01" min="0" class="form-control" name="amount_paid" id="amountPaid" value="0" oninput="recalcSale()" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Balance → Khata / EMI</label>
                        <input type="text" class="form-control" id="sumBalance" readonly value="₹0.00" style="background:#FEF2F2; font-weight:900; font-size:18px; color:#DC2626;">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Payment Mode</label>
                        <select class="form-control" name="payment_mode" id="paymentMode" onchange="onPaymentModeChange()">
                            <option value="cash">Cash</option>
                            <option value="upi">UPI</option>
                            <option value="card">Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="emi">EMI (Finance Company)</option>
                            <option value="credit_udhari">Khata / Udhari (Credit)</option>
                            <option value="split">Split Payment</option>
                        </select>
                    </div>
                    <div class="form-group" id="emiProviderGroup" style="display:none;">
                        <label class="form-label">EMI / Finance Partner</label>
                        <select class="form-control" name="emi_provider_id" id="emiProviderSelect" onchange="onEmiProviderChange()">
                            <option value="">— Select Finance Partner —</option>
                            @foreach($emiProviders as $p)
                                <option value="{{ $p->id }}"
                                    data-flat="{{ $p->processing_fee_flat ?? 0 }}"
                                    data-pct="{{ $p->processing_fee_pct ?? 0 }}"
                                    data-tenure="{{ $p->default_tenure_months ?? 12 }}">
                                    {{ $p->name }} (Pool: ₹{{ number_format($p->advance_balance, 0) }})
                                </option>
                            @endforeach
                        </select>
                        <div style="font-size:10.5px; margin-top:4px;">
                            <a href="{{ route('mobileshop.emi.ledger') }}" style="color:var(--color-primary); font-weight:700;">Manage EMI Ledger →</a>
                        </div>
                    </div>
                    <div class="form-group" id="emiFeeGroup" style="display:none;">
                        <label class="form-label">Processing Fee</label>
                        <div style="display:flex; gap:8px;">
                            <select class="form-control" name="emi_fee_type" id="emiFeeType" style="width:115px; font-weight:700;">
                                <option value="flat">Flat (₹)</option>
                                <option value="percent">Percent (%)</option>
                            </select>
                            <input type="number" step="0.01" min="0" class="form-control" name="emi_fee_value" id="emiFeeValue" placeholder="e.g. 500 or 2.5">
                        </div>
                    </div>
                    <div class="form-group" id="emiTenureGroup" style="display:none;">
                        <label class="form-label">EMI Tenure (Months)</label>
                        <select class="form-control" name="emi_tenure_months" id="emiTenureSelect">
                            <option value="3">3 Months</option>
                            <option value="6">6 Months</option>
                            <option value="9">9 Months</option>
                            <option value="12" selected>12 Months</option>
                            <option value="18">18 Months</option>
                            <option value="24">24 Months</option>
                            <option value="36">36 Months</option>
                        </select>
                    </div>
                    <div class="form-group" id="emiLoanGroup" style="display:none;">
                        <label class="form-label">EMI / Loan Reference No.</label>
                        <input type="text" class="form-control" name="emi_loan_no" placeholder="e.g. BJF-98231">
                    </div>
                </div>
            </div>
        </div>

        <div style="display:flex; gap:12px; justify-content:flex-end; margin-bottom:40px;">
            <a href="{{ route('mobileshop.sales') }}" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary" style="min-width:220px;">
                <i data-lucide="receipt" style="width:15px;height:15px;"></i> Complete Sale & Print
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // Auto-fill customer name & balance by phone
    var customersData = @json($customers->map(function($c) { return ['phone' => $c->phone, 'name' => $c->name, 'bal' => (float)$c->udhari_balance]; }));

    document.getElementById('customerPhone').addEventListener('change', function() {
        var phone = this.value.trim();
        var match = customersData.find(function(c){ return c.phone === phone; });
        if (match) {
            document.getElementById('customerName').value = match.name;
            document.getElementById('customerBalanceDisplay').value = '₹' + match.bal.toLocaleString('en-IN', {minimumFractionDigits: 2});
        } else {
            document.getElementById('customerBalanceDisplay').value = 'New Customer';
        }
    });

    function onDeviceToggle(cb) {
        var row = cb.closest('tr');
        var priceInput = row.querySelector('.dev-price');
        priceInput.disabled = !cb.checked;
        recalcSale();
    }

    function recalcSale() {
        var total = 0;
        document.querySelectorAll('.dev-check:checked').forEach(function(cb) {
            var row = cb.closest('tr');
            var price = parseFloat(row.querySelector('.dev-price').value) || 0;
            total += price;
        });
        document.getElementById('sumBillTotal').value = '₹' + total.toLocaleString('en-IN', {minimumFractionDigits: 2});
        var paid = parseFloat(document.getElementById('amountPaid').value) || 0;
        var balance = Math.max(0, total - paid);
        var balEl = document.getElementById('sumBalance');
        balEl.value = '₹' + balance.toLocaleString('en-IN', {minimumFractionDigits: 2});
        balEl.style.color = balance > 0 ? '#DC2626' : '#16A34A';
    }

    function onPaymentModeChange() {
        var mode = document.getElementById('paymentMode').value;
        var isEmi = (mode === 'emi');
        document.getElementById('emiProviderGroup').style.display = isEmi ? 'block' : 'none';
        document.getElementById('emiFeeGroup').style.display = isEmi ? 'block' : 'none';
        document.getElementById('emiTenureGroup').style.display = isEmi ? 'block' : 'none';
        document.getElementById('emiLoanGroup').style.display = isEmi ? 'block' : 'none';
        if (isEmi) {
            onEmiProviderChange();
        }
    }

    function onEmiProviderChange() {
        var select = document.getElementById('emiProviderSelect');
        if (!select) return;
        var opt = select.options[select.selectedIndex];
        if (opt && opt.value) {
            var flat = parseFloat(opt.dataset.flat || 0);
            var pct = parseFloat(opt.dataset.pct || 0);
            var tenure = opt.dataset.tenure || '12';

            if (pct > 0) {
                document.getElementById('emiFeeType').value = 'percent';
                document.getElementById('emiFeeValue').value = pct;
            } else if (flat > 0) {
                document.getElementById('emiFeeType').value = 'flat';
                document.getElementById('emiFeeValue').value = flat;
            }
            if (tenure) {
                var tenureSelect = document.getElementById('emiTenureSelect');
                if (tenureSelect) tenureSelect.value = tenure;
            }
        }
    }

    function filterDeviceTable() {
        var q = document.getElementById('deviceSearch').value.toLowerCase().trim();
        document.querySelectorAll('.device-row').forEach(function(row) {
            row.style.display = (!q || row.dataset.search.indexOf(q) !== -1) ? '' : 'none';
        });
    }
</script>
@endpush