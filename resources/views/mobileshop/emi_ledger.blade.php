@extends('mobileshop.layout')

@section('title', 'EMI Finance Companies Ledger — MobiTrack ERP')
@section('page-title', 'EMI Companies & Finance Partner Ledger')

@section('page-actions')
    <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        <button type="button" class="btn btn-outline btn-sm" onclick="openDepositModal()">
            <i data-lucide="plus-circle" style="width:14px;height:14px;"></i> Top-up / Deposit Advance
        </button>
        <button type="button" class="btn btn-primary btn-sm" onclick="openAddProviderModal()" style="background:#2563EB;">
            <i data-lucide="plus" style="width:14px;height:14px;"></i> Add EMI Company
        </button>
        <a href="{{ route('mobileshop.sales.create') }}" class="btn btn-outline btn-sm">
            <i data-lucide="shopping-cart" style="width:14px;height:14px;"></i> Register Sale (Full Page)
        </a>
    </div>
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

    <!-- Top KPI Cards -->
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:10px; margin-bottom:12px;">
        <div class="card kpi-card kpi-primary" style="margin:0;">
            <div class="card-body" style="padding:10px 12px;">
                <div style="font-size:10.5px; font-weight:700; color:var(--color-text-secondary); text-transform:uppercase;">Active EMI Partners</div>
                <div style="font-size:18px; font-weight:800; color:var(--brand-700); margin-top:2px;">{{ $providers->count() }} Companies</div>
                <div style="font-size:10.5px; color:var(--color-text-muted); margin-top:1px;">Bajaj Finserv, TVS, Home Credit & more</div>
            </div>
        </div>

        <div class="card kpi-card kpi-success" style="margin:0;">
            <div class="card-body" style="padding:10px 12px;">
                <div style="font-size:10.5px; font-weight:700; color:var(--color-text-secondary); text-transform:uppercase;">Total Advance Pool Available</div>
                <div style="font-size:18px; font-weight:800; color:#16A34A; margin-top:2px;">₹{{ number_format($providers->sum('advance_balance'), 2) }}</div>
                <div style="font-size:10.5px; color:var(--color-text-muted); margin-top:1px;">Available to finance new customer phones</div>
            </div>
        </div>

        <div class="card kpi-card kpi-info" style="margin:0;">
            <div class="card-body" style="padding:10px 12px;">
                <div style="font-size:10.5px; font-weight:700; color:var(--color-text-secondary); text-transform:uppercase;">Ledger Transactions</div>
                <div style="font-size:18px; font-weight:800; color:#2563EB; margin-top:2px;">{{ $transactions->count() }} Records</div>
                <div style="font-size:10.5px; color:var(--color-text-muted); margin-top:1px;">Recent deposits & customer loan deductions</div>
            </div>
        </div>
    </div>

    <!-- EMI Companies Cards Grid -->
    <div style="margin-bottom:12px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
            <h3 style="font-size:15px; font-weight:800; color:#0F172A; margin:0; display:flex; align-items:center; gap:8px;">
                <i data-lucide="building-2" style="width:18px;height:18px;color:#2563EB;"></i>
                Registered Finance Partners
            </h3>
            <button type="button" class="btn btn-primary btn-sm" onclick="openAddProviderModal()" style="font-size:12px;">
                <i data-lucide="plus" style="width:13px;height:13px;"></i> Add Partner
            </button>
        </div>

        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:16px;">
            @forelse($providers as $p)
            <div class="card" style="margin:0; border-radius:10px; border:1px solid #E2E8F0; transition:transform 0.15s ease, box-shadow 0.15s ease;">
                <div class="card-body" style="padding:18px;">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px;">
                        <div>
                            <div style="font-size:15px; font-weight:800; color:#0F172A;">{{ $p->name }}</div>
                            @if($p->code)
                                <span class="badge badge-gray" style="font-size:10px; font-weight:700;">{{ $p->code }}</span>
                            @endif
                        </div>
                        <span class="badge {{ $p->enabled ? 'badge-green' : 'badge-gray' }}" style="font-size:10px;">
                            {{ $p->enabled ? 'Active' : 'Disabled' }}
                        </span>
                    </div>

                    <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:12px; margin-bottom:14px;">
                        <div style="font-size:10.5px; font-weight:700; color:#64748B; text-transform:uppercase;">Advance Balance Pool</div>
                        <div style="font-size:22px; font-weight:900; color:#16A34A; margin-top:2px;">₹{{ number_format($p->advance_balance, 2) }}</div>
                        <div style="font-size:10.5px; color:#64748B; margin-top:2px;">Auto-deducts when phones are sold via EMI</div>
                    </div>

                    <div style="font-size:12px; color:#475569; margin-bottom:8px;">
                        <div><strong>Contact:</strong> {{ $p->contact_person ?: '—' }}</div>
                        <div><strong>Phone:</strong> {{ $p->phone ?: '—' }}</div>
                    </div>

                    <div style="font-size:11px; color:#475569; margin-bottom:12px; display:flex; gap:6px; flex-wrap:wrap;">
                        @if(!empty($p->processing_fee_flat) && $p->processing_fee_flat > 0)
                            <span class="badge badge-blue" style="font-size:9.5px; padding:1px 6px;">Fee: ₹{{ number_format($p->processing_fee_flat, 2) }}</span>
                        @endif
                        @if(!empty($p->processing_fee_pct) && $p->processing_fee_pct > 0)
                            <span class="badge badge-purple" style="font-size:9.5px; padding:1px 6px;">Fee: {{ $p->processing_fee_pct }}%</span>
                        @endif
                        @if(!empty($p->default_tenure_months))
                            <span class="badge badge-gray" style="font-size:9.5px; padding:1px 6px;">{{ $p->default_tenure_months }} Mo</span>
                        @endif
                    </div>

                    <div style="display:flex; gap:8px;">
                        <button type="button" class="btn btn-outline btn-sm" style="flex:1;" onclick="openDepositModal({{ $p->id }}, '{{ addslashes($p->name) }}')">
                            <i data-lucide="plus-circle" style="width:13px;height:13px;color:#16A34A;"></i> Deposit
                        </button>
                        <button type="button" class="btn btn-outline btn-sm" style="flex:1; border-color:#CBD5E1;" onclick="openEditProviderModal({{ $p->id }}, '{{ addslashes($p->name) }}', '{{ addslashes($p->code ?? '') }}', '{{ addslashes($p->contact_person ?? '') }}', '{{ addslashes($p->phone ?? '') }}', {{ (float) ($p->advance_balance ?? 0) }}, {{ (float) ($p->processing_fee_flat ?? 0) }}, {{ (float) ($p->processing_fee_pct ?? 0) }}, {{ (int) ($p->default_tenure_months ?? 0) }}, {{ (float) ($p->interest_rate_pct ?? 0) }}, '{{ addslashes($p->notes ?? '') }}')">
                            <i data-lucide="edit-3" style="width:13px;height:13px;color:#2563EB;"></i> Edit Partner
                        </button>
                    </div>
                </div>
            </div>
            @empty
            <div class="card" style="grid-column: 1 / -1; padding:36px; text-align:center; color:#94A3B8;">
                <i data-lucide="building" style="width:40px;height:40px;margin-bottom:8px;opacity:0.5;"></i>
                <div style="font-size:15px; font-weight:700; color:#475569;">No EMI Companies Registered Yet</div>
                <div style="font-size:12px; margin-top:4px;">Add your finance partners like Bajaj Finserv, TVS Credit, or Home Credit to track loans and advance balances.</div>
                <button type="button" class="btn btn-primary btn-sm" onclick="openAddProviderModal()" style="margin-top:14px;">
                    <i data-lucide="plus" style="width:14px;height:14px;"></i> Register First EMI Partner
                </button>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Transaction Audit Ledger -->
    <div class="card" style="margin-bottom:12px;">
        <div class="card-header" style="background:#F8FAFC; border-bottom:1px solid #E2E8F0;">
            <div>
                <div class="card-title"><i data-lucide="receipt" style="width:16px;height:16px; vertical-align:-2px; color:#2563EB;"></i> EMI Ledger Transaction Log</div>
                <div class="card-subtitle">Running record of advances deposited and customer loans financed</div>
            </div>
        </div>
        <div class="card-body" style="padding:0; overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>EMI Company</th>
                        <th>Transaction Type</th>
                        <th>Loan / Ref #</th>
                        <th style="text-align:right;">Amount (₹)</th>
                        <th style="text-align:right;">Balance After (₹)</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $t)
                    @php
                        $isDeposit = in_array($t->type, ['advance_deposit', 'deposit']);
                    @endphp
                    <tr>
                        <td style="font-size:12px; color:#475569;">{{ \Carbon\Carbon::parse($t->created_at)->format('d M Y, h:i A') }}</td>
                        <td style="font-weight:700; color:#0F172A;">{{ $t->provider_name }}</td>
                        <td>
                            @if($isDeposit)
                                <span class="badge badge-green">Deposit / Top-up</span>
                            @else
                                <span class="badge badge-blue">Sale Loan Deduction</span>
                            @endif
                        </td>
                        <td style="font-family:monospace; font-weight:600;">{{ $t->reference_no ?: '—' }}</td>
                        <td style="text-align:right; font-weight:800; color:{{ $isDeposit ? '#16A34A' : '#DC2626' }};">
                            {{ $isDeposit ? '+' : '-' }}₹{{ number_format($t->amount, 2) }}
                        </td>
                        <td style="text-align:right; font-weight:800; color:#0F172A;">
                            ₹{{ number_format($t->balance_after, 2) }}
                        </td>
                        <td style="font-size:12px; color:#64748B;">{{ $t->notes ?: '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align:center; padding:32px; color:#94A3B8;">
                            No EMI transactions recorded yet. Deposits and customer EMI sales will appear here.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ════ MODAL 1: ADD EMI COMPANY ════ -->
<div id="addEmiModal" style="display:none; position:fixed; inset:0; z-index:1200; background:rgba(15,23,42,0.55); backdrop-filter:blur(4px); align-items:center; justify-content:center; padding:16px;">
    <div class="card" style="max-width:520px; width:100%; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); border-radius:12px; margin:0; overflow:hidden;">
        <div class="card-header" style="background:#2563EB; color:#fff; padding:16px 20px;">
            <div>
                <div class="card-title" style="color:#fff; font-size:16px; margin:0;">Add EMI Finance Partner</div>
                <div class="card-subtitle" style="color:rgba(255,255,255,0.85); font-size:12px; margin-top:2px;">Register a new loan / finance provider</div>
            </div>
            <button type="button" onclick="closeAddProviderModal()" style="background:transparent; border:none; color:#fff; font-size:20px; cursor:pointer; padding:4px 8px; line-height:1;">✕</button>
        </div>
        <form method="POST" action="{{ route('mobileshop.emi.provider.store') }}">
            @csrf
            <div class="card-body" style="padding:20px;">
                <div class="form-group" style="margin-bottom:14px;">
                    <label class="form-label required" style="font-weight:700;">Company Name</label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g. Bajaj Finserv, TVS Credit, Home Credit">
                </div>
                <div class="form-row" style="margin-bottom:14px; grid-template-columns: 1fr 1fr; gap:12px;">
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Company Code / Short Name</label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. BAJAJ">
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control" placeholder="Contact number">
                    </div>
                </div>
                <div class="form-group" style="margin-bottom:14px;">
                    <label class="form-label">Contact Person / Executive</label>
                    <input type="text" name="contact_person" class="form-control" placeholder="e.g. Area Executive Name">
                </div>
                <div class="form-row" style="margin-bottom:14px; grid-template-columns: 1fr 1fr; gap:12px;">
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Processing Fee (Flat ₹)</label>
                        <input type="number" step="0.01" min="0" name="processing_fee_flat" class="form-control" placeholder="0.00">
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Processing Fee (%)</label>
                        <input type="number" step="0.01" min="0" max="100" name="processing_fee_pct" class="form-control" placeholder="0.00">
                    </div>
                </div>
                <div class="form-row" style="margin-bottom:14px; grid-template-columns: 1fr 1fr; gap:12px;">
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Default Tenure (Months)</label>
                        <input type="number" min="1" max="60" name="default_tenure_months" class="form-control" placeholder="12">
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Interest Rate (%)</label>
                        <input type="number" step="0.01" min="0" max="100" name="interest_rate_pct" class="form-control" placeholder="0.00">
                    </div>
                </div>
                <div class="form-group" style="margin-bottom:14px;">
                    <label class="form-label">Opening Advance Balance Pool (₹)</label>
                    <input type="number" step="0.01" min="0" name="opening_balance" class="form-control" placeholder="0.00" value="0">
                    <div style="font-size:11px; color:#64748B; margin-top:4px;">Initial funds deposited or approved credit line</div>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Notes / T&C</label>
                    <textarea name="notes" rows="2" class="form-control" placeholder="Optional financier notes..."></textarea>
                </div>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:10px; padding:14px 20px; background:#F8FAFC; border-top:1px solid #E2E8F0;">
                <button type="button" onclick="closeAddProviderModal()" class="btn btn-outline">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background:#2563EB;">
                    <i data-lucide="save" style="width:14px;height:14px;"></i> Save Partner
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ════ MODAL 2: DEPOSIT ADVANCE FUNDS ════ -->
<div id="depositModal" style="display:none; position:fixed; inset:0; z-index:1200; background:rgba(15,23,42,0.55); backdrop-filter:blur(4px); align-items:center; justify-content:center; padding:16px;">
    <div class="card" style="max-width:500px; width:100%; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); border-radius:12px; margin:0; overflow:hidden;">
        <div class="card-header" style="background:#16A34A; color:#fff; padding:16px 20px;">
            <div>
                <div class="card-title" style="color:#fff; font-size:16px; margin:0;">Deposit / Top-up Advance Pool</div>
                <div class="card-subtitle" style="color:rgba(255,255,255,0.85); font-size:12px; margin-top:2px;">Add funds received from or credited by EMI partner</div>
            </div>
            <button type="button" onclick="closeDepositModal()" style="background:transparent; border:none; color:#fff; font-size:20px; cursor:pointer; padding:4px 8px; line-height:1;">✕</button>
        </div>
        <form method="POST" action="{{ route('mobileshop.emi.deposit') }}">
            @csrf
            <div class="card-body" style="padding:20px;">
                <div class="form-group" style="margin-bottom:14px;">
                    <label class="form-label required" style="font-weight:700;">Select EMI Partner</label>
                    <select name="emi_provider_id" id="depositProviderSelect" class="form-control" required style="font-weight:600;">
                        <option value="">— Select Company —</option>
                        @foreach($providers as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} (Current: ₹{{ number_format($p->advance_balance, 0) }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:14px;">
                    <label class="form-label required" style="font-weight:700;">Deposit Amount (₹)</label>
                    <input type="number" step="0.01" min="1" name="amount" class="form-control" placeholder="e.g. 100000" required style="font-weight:800; font-size:16px;">
                </div>
                <div class="form-group" style="margin-bottom:14px;">
                    <label class="form-label">Reference / UTR / Cheque No.</label>
                    <input type="text" name="reference_no" class="form-control" placeholder="Transaction ref or RTGS UTR">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Notes / Remarks</label>
                    <textarea name="notes" rows="2" class="form-control" placeholder="Optional notes..."></textarea>
                </div>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:10px; padding:14px 20px; background:#F8FAFC; border-top:1px solid #E2E8F0;">
                <button type="button" onclick="closeDepositModal()" class="btn btn-outline">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background:#16A34A; border-color:#16A34A;">
                    <i data-lucide="check" style="width:14px;height:14px;"></i> Confirm Deposit
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit EMI Partner Modal -->
<div id="editProviderModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(3px);">
    <div class="card" style="width:100%; max-width:460px; margin:20px; border-radius:12px; overflow:hidden; box-shadow:0 20px 40px rgba(0,0,0,0.25);">
        <div class="card-header" style="background:#2563EB; color:#fff; padding:16px 20px;">
            <div>
                <div class="card-title" style="color:#fff; font-size:16px;"><i data-lucide="building-2" style="width:18px;height:18px; vertical-align:-3px;"></i> Edit EMI Finance Partner</div>
                <div class="card-subtitle" style="color:#DBEAFE; font-size:11px;">Update company details and advance pool balance</div>
            </div>
            <button type="button" onclick="closeEditProviderModal()" style="background:none; border:none; color:#fff; cursor:pointer; font-size:22px; line-height:1;">&times;</button>
        </div>
        <form method="POST" action="{{ route('mobileshop.emi.provider.update') }}">
            @csrf
            <input type="hidden" name="emi_provider_id" id="editEmiPartnerId">
            <div class="card-body" style="padding:20px; display:flex; flex-direction:column; gap:12px;">
                <div>
                    <label class="form-label" style="font-weight:700; font-size:12px;">Company / Partner Name *</label>
                    <input type="text" name="name" id="editEmiPartnerName" class="form-control" required style="width:100%;">
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    <div>
                        <label class="form-label" style="font-weight:700; font-size:12px;">Short Code</label>
                        <input type="text" name="code" id="editEmiPartnerCode" class="form-control">
                    </div>
                    <div>
                        <label class="form-label" style="font-weight:700; font-size:12px;">Contact Phone</label>
                        <input type="text" name="phone" id="editEmiPartnerPhone" class="form-control">
                    </div>
                </div>
                <div>
                    <label class="form-label" style="font-weight:700; font-size:12px;">Contact Person</label>
                    <input type="text" name="contact_person" id="editEmiPartnerContact" class="form-control">
                </div>
                <div style="background:#F0FDF4; border:1px solid #BBF7D0; border-radius:8px; padding:12px;">
                    <label class="form-label" style="font-weight:700; font-size:12px; color:#166534;">Advance Pool Balance (₹)</label>
                    <input type="number" step="0.01" min="0" name="advance_balance" id="editEmiPartnerBalance" class="form-control" style="font-weight:800; font-size:16px; color:#15803D;">
                    <div style="font-size:10.5px; color:#166534; margin-top:4px;">Direct balance modification records an audit transaction in the ledger below.</div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    <div>
                        <label class="form-label" style="font-weight:700; font-size:12px;">Processing Fee (Flat ₹)</label>
                        <input type="number" step="0.01" min="0" name="processing_fee_flat" id="editEmiPartnerFlatFee" class="form-control" placeholder="0.00">
                    </div>
                    <div>
                        <label class="form-label" style="font-weight:700; font-size:12px;">Processing Fee (%)</label>
                        <input type="number" step="0.01" min="0" max="100" name="processing_fee_pct" id="editEmiPartnerPctFee" class="form-control" placeholder="0.00">
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    <div>
                        <label class="form-label" style="font-weight:700; font-size:12px;">Default Tenure (Months)</label>
                        <input type="number" min="1" max="60" name="default_tenure_months" id="editEmiPartnerTenure" class="form-control" placeholder="12">
                    </div>
                    <div>
                        <label class="form-label" style="font-weight:700; font-size:12px;">Interest Rate (%)</label>
                        <input type="number" step="0.01" min="0" max="100" name="interest_rate_pct" id="editEmiPartnerInterest" class="form-control" placeholder="0.00">
                    </div>
                </div>
                <div>
                    <label class="form-label" style="font-weight:700; font-size:12px;">Notes / T&C</label>
                    <textarea name="notes" id="editEmiPartnerNotes" class="form-control" rows="2" placeholder="Fee deduction policy, terms..."></textarea>
                </div>
                <div>
                    <label class="form-label" style="font-weight:700; font-size:12px;">Adjustment Reason / Note</label>
                    <input type="text" name="adjustment_notes" class="form-control" placeholder="e.g. Reconciliation adjustment">
                </div>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:10px; padding:14px 20px; background:#F8FAFC; border-top:1px solid #E2E8F0;">
                <button type="button" onclick="closeEditProviderModal()" class="btn btn-outline">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background:#2563EB; border-color:#2563EB;">
                    <i data-lucide="check" style="width:14px;height:14px;"></i> Update Partner
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openAddProviderModal() {
        document.getElementById('addEmiModal').style.display = 'flex';
        if (window.lucide) window.lucide.createIcons();
    }
    function closeAddProviderModal() {
        document.getElementById('addEmiModal').style.display = 'none';
    }

    function openDepositModal(providerId, providerName) {
        if (providerId) {
            document.getElementById('depositProviderSelect').value = providerId;
        }
        document.getElementById('depositModal').style.display = 'flex';
        if (window.lucide) window.lucide.createIcons();
    }
    function closeDepositModal() {
        document.getElementById('depositModal').style.display = 'none';
    }

    function openEditProviderModal(id, name, code, contact, phone, balance, flatFee, pctFee, tenure, interest, notes) {
        document.getElementById('editEmiPartnerId').value = id;
        document.getElementById('editEmiPartnerName').value = name;
        document.getElementById('editEmiPartnerCode').value = code || '';
        document.getElementById('editEmiPartnerContact').value = contact || '';
        document.getElementById('editEmiPartnerPhone').value = phone || '';
        document.getElementById('editEmiPartnerBalance').value = balance || 0;
        document.getElementById('editEmiPartnerFlatFee').value = flatFee || '';
        document.getElementById('editEmiPartnerPctFee').value = pctFee || '';
        document.getElementById('editEmiPartnerTenure').value = tenure || '';
        document.getElementById('editEmiPartnerInterest').value = interest || '';
        document.getElementById('editEmiPartnerNotes').value = notes || '';
        document.getElementById('editProviderModal').style.display = 'flex';
        if (window.lucide) window.lucide.createIcons();
    }
    function closeEditProviderModal() {
        document.getElementById('editProviderModal').style.display = 'none';
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAddProviderModal();
            closeDepositModal();
            closeEditProviderModal();
        }
    });
</script>
@endpush
