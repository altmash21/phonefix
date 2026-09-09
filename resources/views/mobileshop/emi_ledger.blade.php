@extends('mobileshop.layout')

@section('title', 'EMI Finance Companies Ledger — Maurya Mobile ERP')
@section('page-title', 'EMI Companies & Finance Partner Ledger')

@section('page-actions')
    <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
        <button type="button" class="btn btn-primary btn-sm" onclick="openDepositModal()">
            <i data-lucide="plus-circle" style="width:14px;height:14px;"></i> Deposit Advance Pool
        </button>
        <button type="button" class="btn btn-outline btn-sm" onclick="openAddProviderModal()">
            <i data-lucide="building-2" style="width:14px;height:14px;"></i> Add EMI Partner
        </button>
        <a href="{{ route('mobileshop.sales.create') }}" class="btn btn-outline btn-sm">
            <i data-lucide="shopping-cart" style="width:14px;height:14px;"></i> Register Sale
        </a>
    </div>
@endsection

@section('content')

    @if(session('success'))
        <div class="flash-success" style="border-radius:8px; margin-bottom:12px; display:flex; align-items:center; gap:8px; padding:10px 14px;">
            <i data-lucide="check-circle-2" style="width:16px;height:16px; flex-shrink:0;"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="flash-error" style="border-radius:8px; margin-bottom:12px; display:flex; align-items:center; gap:8px; padding:10px 14px;">
            <i data-lucide="alert-circle" style="width:16px;height:16px; flex-shrink:0;"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Mobile Quick Stat Strip (Hidden on Desktop) -->
    <div class="stat-strip mobile-stat-strip hide-on-desktop">
        <div class="stat-strip-item">
            <span class="stat-label">EMI Partners</span>
            <span class="stat-val" style="color:var(--color-primary);">{{ $providers->count() }}</span>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-strip-item">
            <span class="stat-label">Advance Pool</span>
            <span class="stat-val" style="color:#16A34A;">₹{{ number_format($providers->sum('advance_balance'), 0) }}</span>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-strip-item">
            <span class="stat-label">Ledger Records</span>
            <span class="stat-val" style="color:#2563EB;">{{ $transactions->count() }}</span>
        </div>
    </div>

    <!-- Desktop Stat Grid (Hidden on Mobile) -->
    <div class="stat-grid" id="emiDesktopStatGrid" style="margin-bottom: 12px; display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:10px;">
        <!-- Card 1: Active Partners -->
        <div class="stat-card" style="background:#F8FAFC; border:1px solid #E2E8F0; padding:10px 14px; border-radius:8px; cursor:pointer;" onclick="openAddProviderModal()" title="Click to add new finance partner">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                <span style="font-size:10.5px; font-weight:800; text-transform:uppercase; color:#475569;">Active Partners</span>
                <i data-lucide="building-2" style="width:15px;height:15px; color:var(--color-primary);"></i>
            </div>
            <div style="font-size:18px; font-weight:800; color:#0F172A;">{{ $providers->count() }} <span style="font-size:12px; font-weight:600; color:#64748B;">Companies</span></div>
            <div style="font-size:10.5px; color:#64748B; margin-top:2px;">Bajaj, TVS, Home Credit & more</div>
        </div>

        <!-- Card 2: Advance Balance Pool -->
        <div class="stat-card" style="background:#F0FDF4; border:1px solid #BBF7D0; padding:10px 14px; border-radius:8px; cursor:pointer;" onclick="openDepositModal()" title="Click to deposit advance funds">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                <span style="font-size:10.5px; font-weight:800; text-transform:uppercase; color:#166534;">Advance Pool Available</span>
                <i data-lucide="wallet" style="width:15px;height:15px; color:#16A34A;"></i>
            </div>
            <div style="font-size:18px; font-weight:800; color:#166534;">₹{{ number_format($providers->sum('advance_balance'), 2) }}</div>
            <div style="font-size:10.5px; color:#15803D; margin-top:2px;">Auto-deducts on customer phone EMIs</div>
        </div>

        <!-- Card 3: Ledger Transactions -->
        <div class="stat-card" style="background:#EFF6FF; border:1px solid #BFDBFE; padding:10px 14px; border-radius:8px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                <span style="font-size:10.5px; font-weight:800; text-transform:uppercase; color:#1E40AF;">Ledger Transactions</span>
                <i data-lucide="receipt" style="width:15px;height:15px; color:#2563EB;"></i>
            </div>
            <div style="font-size:18px; font-weight:800; color:#1E40AF;">{{ $transactions->count() }} <span style="font-size:12px; font-weight:600; color:#64748B;">Audits</span></div>
            <div style="font-size:10.5px; color:#2563EB; margin-top:2px;">Recent deposits & sales deductions</div>
        </div>

        <!-- Card 4: Average Pool Reserve -->
        @php
            $avgPool = $providers->count() > 0 ? ($providers->sum('advance_balance') / $providers->count()) : 0;
        @endphp
        <div class="stat-card" style="background:#FFFBEB; border:1px solid #FDE68A; padding:10px 14px; border-radius:8px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                <span style="font-size:10.5px; font-weight:800; text-transform:uppercase; color:#92400E;">Avg Partner Reserve</span>
                <i data-lucide="shield-check" style="width:15px;height:15px; color:#D97706;"></i>
            </div>
            <div style="font-size:18px; font-weight:800; color:#92400E;">₹{{ number_format($avgPool, 0) }}</div>
            <div style="font-size:10.5px; color:#B45309; margin-top:2px;">Liquidity reserve coverage</div>
        </div>
    </div>

    <!-- ════ SECTION 1: REGISTERED FINANCE PARTNERS ════ -->
    <div class="card" style="margin-bottom:12px; border-radius:8px; border:1px solid #E2E8F0; overflow:hidden;">
        <div class="card-header" style="background:#F8FAFC; border-bottom:1px solid #E2E8F0; padding:10px 14px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
            <div>
                <div class="card-title" style="display:flex; align-items:center; gap:8px;">
                    <i data-lucide="building-2" style="width:16px;height:16px; color:var(--color-primary);"></i>
                    Registered Finance Partners
                    <span class="badge badge-blue" style="font-size:10.5px; font-weight:700;">{{ $providers->count() }} Companies</span>
                </div>
            </div>
            <button type="button" class="btn btn-primary btn-sm" onclick="openAddProviderModal()">
                <i data-lucide="plus" style="width:13px;height:13px;"></i> Add Partner
            </button>
        </div>

        <div class="card-body" style="padding:12px 14px;">
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap:10px;">
                @forelse($providers as $p)
                <div class="card" style="margin:0; border-radius:8px; border:1px solid #E2E8F0; background:#FFFFFF; transition:transform 0.15s ease, box-shadow 0.15s ease;">
                    <div class="card-body" style="padding:12px 14px;">
                        <!-- Top Name & Status -->
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px;">
                            <div>
                                <div style="font-size:14px; font-weight:800; color:#0F172A; display:flex; align-items:center; gap:6px;">
                                    {{ $p->name }}
                                    @if($p->code)
                                        <span class="badge badge-gray" style="font-size:9.5px; font-weight:700; padding:1px 5px;">{{ $p->code }}</span>
                                    @endif
                                </div>
                                <div style="font-size:11px; color:#64748B; margin-top:2px;">
                                    <i data-lucide="user" style="width:12px;height:12px; vertical-align:-1px;"></i> {{ $p->contact_person ?: 'No executive listed' }}
                                    @if($p->phone)
                                        • <span style="font-family:monospace;">{{ $p->phone }}</span>
                                    @endif
                                </div>
                            </div>
                            <span class="badge {{ $p->enabled ? 'badge-green' : 'badge-gray' }}" style="font-size:10px;">
                                {{ $p->enabled ? 'Active' : 'Disabled' }}
                            </span>
                        </div>

                        <!-- Advance Balance Box -->
                        <div style="background:#F0FDF4; border:1px solid #BBF7D0; border-radius:6px; padding:8px 10px; margin-bottom:8px;">
                            <div style="font-size:10px; font-weight:700; color:#166534; text-transform:uppercase; letter-spacing:0.3px;">Advance Balance Pool</div>
                            <div style="font-size:18px; font-weight:900; color:#15803D; margin-top:1px;">₹{{ number_format($p->advance_balance, 2) }}</div>
                            <div style="font-size:10px; color:#166534; margin-top:1px;">Auto-deducts when phones are billed via EMI</div>
                        </div>

                        <!-- Configured Terms Pills -->
                        <div style="font-size:10.5px; color:#475569; margin-bottom:10px; display:flex; gap:5px; flex-wrap:wrap;">
                            @if(!empty($p->processing_fee_flat) && $p->processing_fee_flat > 0)
                                <span class="badge badge-blue" style="font-size:9.5px; padding:2px 6px;">Fee: ₹{{ number_format($p->processing_fee_flat, 2) }}</span>
                            @endif
                            @if(!empty($p->processing_fee_pct) && $p->processing_fee_pct > 0)
                                <span class="badge badge-purple" style="font-size:9.5px; padding:2px 6px;">Fee: {{ $p->processing_fee_pct }}%</span>
                            @endif
                            @if(!empty($p->default_tenure_months))
                                <span class="badge badge-gray" style="font-size:9.5px; padding:2px 6px;">{{ $p->default_tenure_months }} Mo Tenure</span>
                            @endif
                            @if(!empty($p->interest_rate_pct) && $p->interest_rate_pct > 0)
                                <span class="badge badge-yellow" style="font-size:9.5px; padding:2px 6px;">{{ $p->interest_rate_pct }}% Interest</span>
                            @endif
                        </div>

                        <!-- Action Buttons -->
                        <div style="display:flex; gap:6px;">
                            <button type="button" class="btn btn-outline btn-sm" style="flex:1; padding:4px 8px; font-size:11px;" onclick="openDepositModal({{ $p->id }}, '{{ addslashes($p->name) }}')">
                                <i data-lucide="plus-circle" style="width:12px;height:12px;color:#16A34A;"></i> + Deposit
                            </button>
                            <button type="button" class="btn btn-outline btn-sm" style="flex:1; padding:4px 8px; font-size:11px;" onclick="openEditProviderModal({{ $p->id }}, '{{ addslashes($p->name) }}', '{{ addslashes($p->code ?? '') }}', '{{ addslashes($p->contact_person ?? '') }}', '{{ addslashes($p->phone ?? '') }}', {{ (float) ($p->advance_balance ?? 0) }}, {{ (float) ($p->processing_fee_flat ?? 0) }}, {{ (float) ($p->processing_fee_pct ?? 0) }}, {{ (int) ($p->default_tenure_months ?? 0) }}, {{ (float) ($p->interest_rate_pct ?? 0) }}, '{{ addslashes($p->notes ?? '') }}')">
                                <i data-lucide="edit-3" style="width:12px;height:12px;color:var(--color-primary);"></i> Edit
                            </button>
                        </div>
                    </div>
                </div>
                @empty
                <div class="card" style="grid-column: 1 / -1; padding:24px; text-align:center; color:#94A3B8; border:1px dashed #CBD5E1; border-radius:8px;">
                    <i data-lucide="building" style="width:36px;height:36px;margin-bottom:6px;opacity:0.5;"></i>
                    <div style="font-size:14px; font-weight:700; color:#475569;">No EMI Companies Registered Yet</div>
                    <div style="font-size:11px; margin-top:2px;">Add your finance partners like Bajaj Finserv, TVS Credit, or Home Credit to track loans and advance balances.</div>
                    <button type="button" class="btn btn-primary btn-sm" onclick="openAddProviderModal()" style="margin-top:10px;">
                        <i data-lucide="plus" style="width:13px;height:13px;"></i> Register First EMI Partner
                    </button>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- ════ SECTION 2: MASTER AUDITED EMI TRANSACTION LEDGER ════ -->
    <div class="card" style="margin-bottom:12px; border-radius:8px; border:1px solid #E2E8F0; overflow:hidden;">
        <div class="card-header" style="background:#F8FAFC; border-bottom:1px solid #E2E8F0; padding:10px 14px; display:flex; flex-direction:column; gap:8px;">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <div>
                    <div class="card-title" style="display:flex; align-items:center; gap:8px;">
                        <i data-lucide="receipt" style="width:16px;height:16px; color:var(--color-primary);"></i>
                        EMI Ledger Transaction Log
                        <span id="emiVisibleCountBadge" class="badge badge-blue" style="font-size:10.5px; font-weight:700;">{{ $transactions->count() }} records</span>
                    </div>
                    <div style="font-size:11px; color:#64748B;">Live running record of advance deposits, customer loans, and balance adjustments</div>
                </div>

                <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                    <!-- Filter By Partner Dropdown -->
                    <select id="emiPartnerFilterSelect" onchange="filterEmiLedger()" class="form-control" style="font-size:11.5px; font-weight:700; width:auto; border-color:#CBD5E1; padding:5px 10px; height:auto; color:#0F172A;">
                        <option value="all">🏦 All Finance Companies</option>
                        @foreach($providers as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>

                    <!-- Search Input -->
                    <div class="search-bar" style="background:#fff; border:1px solid #CBD5E1; border-radius:6px; padding:4px 10px; display:flex; align-items:center; gap:6px;">
                        <i data-lucide="search" style="width:13px;height:13px; color:#94A3B8;"></i>
                        <input type="text" id="emiSearchInput" placeholder="Search ref #, company, notes..." oninput="filterEmiLedger()" style="border:none; outline:none; font-size:11.5px; color:#0F172A; width:180px;">
                    </div>
                </div>
            </div>

            <!-- Filter Controls Row: Type Pills & Date Presets -->
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; background:#fff; border:1px solid #E2E8F0; padding:6px 10px; border-radius:6px;">
                <!-- Type Filter Pills -->
                @php
                    $countDeposits = $transactions->filter(fn($t) => in_array($t->type, ['advance_deposit', 'deposit', 'opening_balance']))->count();
                    $countLoans = $transactions->filter(fn($t) => in_array($t->type, ['sale_deduction', 'loan']))->count();
                    $countAdjustments = $transactions->filter(fn($t) => $t->type === 'adjustment')->count();
                @endphp
                <div style="display:flex; gap:4px; background:#E2E8F0; padding:2px; border-radius:6px; flex-wrap:wrap;">
                    <button type="button" onclick="setEmiFilterType('all')" id="btnFilterAll" class="filter-pill active" style="padding:3px 8px; border-radius:5px; font-size:10.5px; font-weight:800; border:none; cursor:pointer;">
                        All ({{ $transactions->count() }})
                    </button>
                    <button type="button" onclick="setEmiFilterType('deposit')" id="btnFilterDeposit" class="filter-pill" style="padding:3px 8px; border-radius:5px; font-size:10.5px; font-weight:700; border:none; cursor:pointer;">
                        🟢 Deposits ({{ $countDeposits }})
                    </button>
                    <button type="button" onclick="setEmiFilterType('sale_deduction')" id="btnFilterLoan" class="filter-pill" style="padding:3px 8px; border-radius:5px; font-size:10.5px; font-weight:700; border:none; cursor:pointer;">
                        🔴 Loans Deducted ({{ $countLoans }})
                    </button>
                    @if($countAdjustments > 0)
                    <button type="button" onclick="setEmiFilterType('adjustment')" id="btnFilterAdjustment" class="filter-pill" style="padding:3px 8px; border-radius:5px; font-size:10.5px; font-weight:700; border:none; cursor:pointer;">
                        🔵 Adjustments ({{ $countAdjustments }})
                    </button>
                    @endif
                </div>

                <!-- Date Range Filters -->
                <div style="display:flex; gap:6px; align-items:center; flex-wrap:wrap;">
                    <div style="display:flex; gap:3px; align-items:center;">
                        <button type="button" onclick="setEmiDatePreset('all')" id="emiDateBtn_all" class="filter-pill emi-date-pill active" style="padding:3px 6px; font-size:10.5px; font-weight:700; border:none; cursor:pointer;">All Time</button>
                        <button type="button" onclick="setEmiDatePreset('today')" id="emiDateBtn_today" class="filter-pill emi-date-pill" style="padding:3px 6px; font-size:10.5px; font-weight:700; border:none; cursor:pointer;">Today</button>
                        <button type="button" onclick="setEmiDatePreset('yesterday')" id="emiDateBtn_yesterday" class="filter-pill emi-date-pill" style="padding:3px 6px; font-size:10.5px; font-weight:700; border:none; cursor:pointer;">Yesterday</button>
                        <button type="button" onclick="setEmiDatePreset('week')" id="emiDateBtn_week" class="filter-pill emi-date-pill" style="padding:3px 6px; font-size:10.5px; font-weight:700; border:none; cursor:pointer;">7 Days</button>
                        <button type="button" onclick="setEmiDatePreset('month')" id="emiDateBtn_month" class="filter-pill emi-date-pill" style="padding:3px 6px; font-size:10.5px; font-weight:700; border:none; cursor:pointer;">This Month</button>
                    </div>

                    <div style="display:flex; align-items:center; gap:4px;">
                        <label for="emiFromDate" style="font-size:10.5px; font-weight:700; color:#64748B; margin:0;">From:</label>
                        <input type="date" id="emiFromDate" onchange="onEmiCustomDateChange()" class="form-control" style="font-size:10.5px; padding:2px 5px; height:auto; width:auto; font-weight:600; color:#0F172A;">
                    </div>
                    <div style="display:flex; align-items:center; gap:4px;">
                        <label for="emiToDate" style="font-size:10.5px; font-weight:700; color:#64748B; margin:0;">To:</label>
                        <input type="date" id="emiToDate" onchange="onEmiCustomDateChange()" class="form-control" style="font-size:10.5px; padding:2px 5px; height:auto; width:auto; font-weight:600; color:#0F172A;">
                    </div>
                    <button type="button" onclick="setEmiDatePreset('all')" title="Reset Date Filter" style="background:#F1F5F9; border:1px solid #CBD5E1; color:#64748B; border-radius:5px; padding:2px 6px; font-size:10.5px; font-weight:700; cursor:pointer;">Reset</button>
                </div>
            </div>
        </div>

        <div class="card-body" style="padding:0; overflow-x:auto;">
            <table class="data-table" id="emiMasterTable" style="margin:0; width:100%;">
                <thead>
                    <tr>
                        <th style="padding:8px 12px; font-size:10.5px;">Date & Time</th>
                        <th style="padding:8px 12px; font-size:10.5px;">EMI Partner</th>
                        <th style="padding:8px 12px; font-size:10.5px;">Transaction Type</th>
                        <th style="padding:8px 12px; font-size:10.5px;">Loan / Ref #</th>
                        <th style="padding:8px 12px; font-size:10.5px; text-align:right;">Amount (₹)</th>
                        <th style="padding:8px 12px; font-size:10.5px; text-align:right;">Balance After (₹)</th>
                        <th style="padding:8px 12px; font-size:10.5px;">Notes & Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $t)
                    @php
                        $isDeposit = in_array($t->type, ['advance_deposit', 'deposit', 'opening_balance']);
                        $isLoan = in_array($t->type, ['sale_deduction', 'loan']);
                        $filterCategory = $isDeposit ? 'deposit' : ($isLoan ? 'sale_deduction' : 'adjustment');
                    @endphp
                    <tr class="emi-row" data-type="{{ $filterCategory }}" data-provider-id="{{ $t->emi_provider_id }}" data-date="{{ date('Y-m-d', strtotime($t->created_at)) }}" data-search="{{ strtolower(($t->provider_name ?? '') . ' ' . ($t->reference_no ?? '') . ' ' . ($t->notes ?? '')) }}">
                        <td style="padding:8px 12px; font-size:11.5px; color:#64748B; white-space:nowrap;">
                            {{ \Carbon\Carbon::parse($t->created_at)->format('d M Y, h:i A') }}
                        </td>
                        <td style="padding:8px 12px; font-size:12px; font-weight:700; color:#0F172A;">
                            {{ $t->provider_name }}
                        </td>
                        <td style="padding:8px 12px;">
                            @if($isDeposit)
                                <span class="badge badge-green" style="font-size:10px;">Deposit / Top-up</span>
                            @elseif($isLoan)
                                <span class="badge badge-blue" style="font-size:10px;">Customer EMI Loan</span>
                            @else
                                <span class="badge badge-purple" style="font-size:10px;">Adjustment</span>
                            @endif
                        </td>
                        <td style="padding:8px 12px; font-family:monospace; font-size:11.5px; font-weight:600; color:#334155;">
                            {{ $t->reference_no ?: '—' }}
                        </td>
                        <td style="padding:8px 12px; text-align:right; font-size:12px; font-weight:800; color:{{ $isDeposit ? '#16A34A' : '#DC2626' }}; white-space:nowrap;">
                            {{ $isDeposit ? '+' : '-' }}₹{{ number_format($t->amount, 2) }}
                        </td>
                        <td style="padding:8px 12px; text-align:right; font-size:12px; font-weight:800; color:#0F172A; white-space:nowrap;">
                            ₹{{ number_format($t->balance_after, 2) }}
                        </td>
                        <td style="padding:8px 12px; font-size:11px; color:#64748B;">
                            {{ $t->notes ?: '—' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align:center; padding:24px; color:#94A3B8;">
                            No EMI transactions recorded yet. Deposits and customer EMI phone sales will automatically stream here.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div id="emiLedgerPagination" style="padding:8px 14px; background:#F8FAFC; border-top:1px solid #E2E8F0;"></div>
    </div>

<!-- ════ MODAL 1: ADD EMI COMPANY ════ -->
<div id="addEmiModal" class="mobi-modal-backdrop" style="display:none; position:fixed; inset:0; z-index:1200; background:rgba(15,23,42,0.55); backdrop-filter:blur(4px); align-items:center; justify-content:center; padding:16px;">
    <div class="card" style="max-width:500px; width:100%; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); border-radius:10px; margin:0; overflow:hidden; background:#FFFFFF;">
        <div class="card-header" style="background:#F8FAFC; border-bottom:1px solid #E2E8F0; padding:12px 16px; display:flex; justify-content:space-between; align-items:center;">
            <div>
                <div class="card-title" style="font-size:14px; font-weight:800; color:#0F172A; margin:0; display:flex; align-items:center; gap:6px;">
                    <i data-lucide="building-2" style="width:16px;height:16px; color:var(--color-primary);"></i>
                    Add EMI Finance Partner
                </div>
                <div class="card-subtitle" style="font-size:11px; color:#64748B; margin-top:1px;">Register a new loan / finance provider</div>
            </div>
            <button type="button" onclick="closeAddProviderModal()" style="background:transparent; border:none; color:#64748B; font-size:20px; cursor:pointer; padding:2px 6px; line-height:1;">✕</button>
        </div>
        <form method="POST" action="{{ route('mobileshop.emi.provider.store') }}">
            @csrf
            <div class="card-body" style="padding:14px 16px;">
                <div class="form-group" style="margin-bottom:10px;">
                    <label class="form-label required" style="font-size:11px; font-weight:700;">Company Name</label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g. Bajaj Finserv, TVS Credit, Home Credit" style="font-size:12px;">
                </div>
                <div class="form-row" style="margin-bottom:10px; display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                    <div class="form-group" style="margin:0;">
                        <label class="form-label" style="font-size:11px; font-weight:700;">Short Code</label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. BAJAJ" style="font-size:12px;">
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label" style="font-size:11px; font-weight:700;">Contact Phone</label>
                        <input type="text" name="phone" class="form-control" placeholder="Contact number" style="font-size:12px;">
                    </div>
                </div>
                <div class="form-group" style="margin-bottom:10px;">
                    <label class="form-label" style="font-size:11px; font-weight:700;">Contact Person / Executive</label>
                    <input type="text" name="contact_person" class="form-control" placeholder="e.g. Area Executive Name" style="font-size:12px;">
                </div>
                <div class="form-row" style="margin-bottom:10px; display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                    <div class="form-group" style="margin:0;">
                        <label class="form-label" style="font-size:11px; font-weight:700;">Processing Fee (Flat ₹)</label>
                        <input type="number" step="0.01" min="0" name="processing_fee_flat" class="form-control" placeholder="0.00" style="font-size:12px;">
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label" style="font-size:11px; font-weight:700;">Processing Fee (%)</label>
                        <input type="number" step="0.01" min="0" max="100" name="processing_fee_pct" class="form-control" placeholder="0.00" style="font-size:12px;">
                    </div>
                </div>
                <div class="form-row" style="margin-bottom:10px; display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                    <div class="form-group" style="margin:0;">
                        <label class="form-label" style="font-size:11px; font-weight:700;">Default Tenure (Months)</label>
                        <input type="number" min="1" max="60" name="default_tenure_months" class="form-control" placeholder="12" style="font-size:12px;">
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label" style="font-size:11px; font-weight:700;">Interest Rate (%)</label>
                        <input type="number" step="0.01" min="0" max="100" name="interest_rate_pct" class="form-control" placeholder="0.00" style="font-size:12px;">
                    </div>
                </div>
                <div class="form-group" style="margin-bottom:10px;">
                    <label class="form-label" style="font-size:11px; font-weight:700;">Opening Advance Balance Pool (₹)</label>
                    <input type="number" step="0.01" min="0" name="opening_balance" class="form-control" placeholder="0.00" value="0" style="font-size:12px; font-weight:800; color:#15803D;">
                    <div style="font-size:10px; color:#64748B; margin-top:2px;">Initial advance funds deposited or approved credit line</div>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" style="font-size:11px; font-weight:700;">Notes / T&C</label>
                    <textarea name="notes" rows="2" class="form-control" placeholder="Optional financier terms..." style="font-size:12px;"></textarea>
                </div>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:8px; padding:10px 16px; background:#F8FAFC; border-top:1px solid #E2E8F0;">
                <button type="button" onclick="closeAddProviderModal()" class="btn btn-outline btn-sm">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i data-lucide="save" style="width:13px;height:13px;"></i> Save Partner
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ════ MODAL 2: DEPOSIT ADVANCE FUNDS ════ -->
<div id="depositModal" class="mobi-modal-backdrop" style="display:none; position:fixed; inset:0; z-index:1200; background:rgba(15,23,42,0.55); backdrop-filter:blur(4px); align-items:center; justify-content:center; padding:16px;">
    <div class="card" style="max-width:480px; width:100%; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); border-radius:10px; margin:0; overflow:hidden; background:#FFFFFF;">
        <div class="card-header" style="background:#F8FAFC; border-bottom:1px solid #E2E8F0; padding:12px 16px; display:flex; justify-content:space-between; align-items:center;">
            <div>
                <div class="card-title" style="font-size:14px; font-weight:800; color:#0F172A; margin:0; display:flex; align-items:center; gap:6px;">
                    <i data-lucide="plus-circle" style="width:16px;height:16px; color:#16A34A;"></i>
                    Deposit / Top-up Advance Pool
                </div>
                <div class="card-subtitle" style="font-size:11px; color:#64748B; margin-top:1px;">Add funds received from or credited by EMI partner</div>
            </div>
            <button type="button" onclick="closeDepositModal()" style="background:transparent; border:none; color:#64748B; font-size:20px; cursor:pointer; padding:2px 6px; line-height:1;">✕</button>
        </div>
        <form method="POST" action="{{ route('mobileshop.emi.deposit') }}">
            @csrf
            <div class="card-body" style="padding:14px 16px;">
                <div class="form-group" style="margin-bottom:10px;">
                    <label class="form-label required" style="font-size:11px; font-weight:700;">Select EMI Partner</label>
                    <select name="emi_provider_id" id="depositProviderSelect" class="form-control" required style="font-weight:600; font-size:12px;">
                        <option value="">— Select Company —</option>
                        @foreach($providers as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} (Current Pool: ₹{{ number_format($p->advance_balance, 0) }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:10px;">
                    <label class="form-label required" style="font-size:11px; font-weight:700;">Deposit Amount (₹)</label>
                    <input type="number" step="0.01" min="1" name="amount" class="form-control" placeholder="e.g. 100000" required style="font-weight:800; font-size:15px; color:#15803D;">
                </div>
                <div class="form-group" style="margin-bottom:10px;">
                    <label class="form-label" style="font-size:11px; font-weight:700;">Reference / UTR / Cheque No.</label>
                    <input type="text" name="reference_no" class="form-control" placeholder="Transaction ref or RTGS UTR" style="font-size:12px;">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" style="font-size:11px; font-weight:700;">Notes / Remarks</label>
                    <textarea name="notes" rows="2" class="form-control" placeholder="Optional deposit remarks..." style="font-size:12px;"></textarea>
                </div>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:8px; padding:10px 16px; background:#F8FAFC; border-top:1px solid #E2E8F0;">
                <button type="button" onclick="closeDepositModal()" class="btn btn-outline btn-sm">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i data-lucide="check" style="width:13px;height:13px;"></i> Confirm Deposit
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ════ MODAL 3: EDIT EMI PARTNER ════ -->
<div id="editProviderModal" class="mobi-modal-backdrop" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.55); z-index:1200; align-items:center; justify-content:center; backdrop-filter:blur(4px); padding:16px;">
    <div class="card" style="width:100%; max-width:480px; margin:0; border-radius:10px; overflow:hidden; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); background:#FFFFFF;">
        <div class="card-header" style="background:#F8FAFC; border-bottom:1px solid #E2E8F0; padding:12px 16px; display:flex; justify-content:space-between; align-items:center;">
            <div>
                <div class="card-title" style="font-size:14px; font-weight:800; color:#0F172A; margin:0; display:flex; align-items:center; gap:6px;">
                    <i data-lucide="edit-3" style="width:16px;height:16px; color:var(--color-primary);"></i>
                    Edit EMI Finance Partner
                </div>
                <div class="card-subtitle" style="font-size:11px; color:#64748B; margin-top:1px;">Update company details and advance pool balance</div>
            </div>
            <button type="button" onclick="closeEditProviderModal()" style="background:none; border:none; color:#64748B; cursor:pointer; font-size:20px; line-height:1; padding:2px 6px;">&times;</button>
        </div>
        <form method="POST" action="{{ route('mobileshop.emi.provider.update') }}">
            @csrf
            <input type="hidden" name="emi_provider_id" id="editEmiPartnerId">
            <div class="card-body" style="padding:14px 16px; display:flex; flex-direction:column; gap:10px;">
                <div>
                    <label class="form-label" style="font-weight:700; font-size:11px;">Company / Partner Name *</label>
                    <input type="text" name="name" id="editEmiPartnerName" class="form-control" required style="font-size:12px;">
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    <div>
                        <label class="form-label" style="font-weight:700; font-size:11px;">Short Code</label>
                        <input type="text" name="code" id="editEmiPartnerCode" class="form-control" style="font-size:12px;">
                    </div>
                    <div>
                        <label class="form-label" style="font-weight:700; font-size:11px;">Contact Phone</label>
                        <input type="text" name="phone" id="editEmiPartnerPhone" class="form-control" style="font-size:12px;">
                    </div>
                </div>
                <div>
                    <label class="form-label" style="font-weight:700; font-size:11px;">Contact Person</label>
                    <input type="text" name="contact_person" id="editEmiPartnerContact" class="form-control" style="font-size:12px;">
                </div>
                <div style="background:#F0FDF4; border:1px solid #BBF7D0; border-radius:6px; padding:10px;">
                    <label class="form-label" style="font-weight:700; font-size:11px; color:#166534;">Advance Pool Balance (₹)</label>
                    <input type="number" step="0.01" min="0" name="advance_balance" id="editEmiPartnerBalance" class="form-control" style="font-weight:800; font-size:15px; color:#15803D;">
                    <div style="font-size:10px; color:#166534; margin-top:2px;">Direct balance modification records an audit transaction in the ledger.</div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    <div>
                        <label class="form-label" style="font-weight:700; font-size:11px;">Processing Fee (Flat ₹)</label>
                        <input type="number" step="0.01" min="0" name="processing_fee_flat" id="editEmiPartnerFlatFee" class="form-control" placeholder="0.00" style="font-size:12px;">
                    </div>
                    <div>
                        <label class="form-label" style="font-weight:700; font-size:11px;">Processing Fee (%)</label>
                        <input type="number" step="0.01" min="0" max="100" name="processing_fee_pct" id="editEmiPartnerPctFee" class="form-control" placeholder="0.00" style="font-size:12px;">
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    <div>
                        <label class="form-label" style="font-weight:700; font-size:11px;">Default Tenure (Months)</label>
                        <input type="number" min="1" max="60" name="default_tenure_months" id="editEmiPartnerTenure" class="form-control" placeholder="12" style="font-size:12px;">
                    </div>
                    <div>
                        <label class="form-label" style="font-weight:700; font-size:11px;">Interest Rate (%)</label>
                        <input type="number" step="0.01" min="0" max="100" name="interest_rate_pct" id="editEmiPartnerInterest" class="form-control" placeholder="0.00" style="font-size:12px;">
                    </div>
                </div>
                <div>
                    <label class="form-label" style="font-weight:700; font-size:11px;">Notes / T&C</label>
                    <textarea name="notes" id="editEmiPartnerNotes" class="form-control" rows="2" placeholder="Fee deduction policy, terms..." style="font-size:12px;"></textarea>
                </div>
                <div>
                    <label class="form-label" style="font-weight:700; font-size:11px;">Adjustment Reason / Note</label>
                    <input type="text" name="adjustment_notes" class="form-control" placeholder="e.g. Reconciliation adjustment" style="font-size:12px;">
                </div>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:8px; padding:10px 16px; background:#F8FAFC; border-top:1px solid #E2E8F0;">
                <button type="button" onclick="closeEditProviderModal()" class="btn btn-outline btn-sm">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i data-lucide="check" style="width:13px;height:13px;"></i> Update Partner
                </button>
            </div>
        </form>
    </div>
</div>

    <!-- Mobile Floating Action Button -->
    <div class="mobile-fab-container">
        <button type="button" class="btn-app-fab" onclick="openDepositModal()" title="Deposit Advance Pool">
            <i data-lucide="plus"></i>
            <span>Deposit Pool</span>
        </button>
    </div>

@endsection

@push('scripts')
<script>
    let activeEmiTypeFilter = 'all';

    function setEmiFilterType(type) {
        activeEmiTypeFilter = type;
        const btnAll = document.getElementById('btnFilterAll');
        const btnDep = document.getElementById('btnFilterDeposit');
        const btnLoan = document.getElementById('btnFilterLoan');
        const btnAdj = document.getElementById('btnFilterAdjustment');

        [btnAll, btnDep, btnLoan, btnAdj].forEach(btn => {
            if (btn) btn.classList.remove('active');
        });

        if (type === 'all' && btnAll) btnAll.classList.add('active');
        else if (type === 'deposit' && btnDep) btnDep.classList.add('active');
        else if (type === 'sale_deduction' && btnLoan) btnLoan.classList.add('active');
        else if (type === 'adjustment' && btnAdj) btnAdj.classList.add('active');

        filterEmiLedger();
    }

    function setEmiDatePreset(preset) {
        document.querySelectorAll('.emi-date-pill').forEach(b => b.classList.remove('active'));
        const targetId = 'emiDateBtn_' + (preset === '7days' ? 'week' : preset);
        const btn = document.getElementById(targetId) || document.getElementById('emiDateBtn_' + preset);
        if (btn) btn.classList.add('active');

        const fromInput = document.getElementById('emiFromDate');
        const toInput = document.getElementById('emiToDate');
        const range = (window.getDateRangePreset && typeof window.getDateRangePreset === 'function')
            ? window.getDateRangePreset(preset)
            : { from: '', to: '' };

        if (fromInput) fromInput.value = range.from || '';
        if (toInput) toInput.value = range.to || '';

        filterEmiLedger();
    }

    function onEmiCustomDateChange() {
        document.querySelectorAll('.emi-date-pill').forEach(b => b.classList.remove('active'));
        filterEmiLedger();
    }

    function filterEmiLedger() {
        const selectedPartner = document.getElementById('emiPartnerFilterSelect')?.value || 'all';
        const searchVal = (document.getElementById('emiSearchInput')?.value || '').toLowerCase().trim();
        const fromDate = document.getElementById('emiFromDate')?.value || '';
        const toDate = document.getElementById('emiToDate')?.value || '';

        let visibleCount = 0;

        document.querySelectorAll('#emiMasterTable tbody tr.emi-row').forEach(row => {
            const rowType = row.dataset.type;
            const rowPartnerId = row.dataset.providerId;
            const rowText = (row.dataset.search || row.textContent).toLowerCase();
            const rawDate = (row.dataset.date || '').trim();
            const cleanRowDate = rawDate.length >= 10 ? rawDate.slice(0, 10) : rawDate;

            const matchesType = (activeEmiTypeFilter === 'all') || (rowType === activeEmiTypeFilter);
            const matchesPartner = (selectedPartner === 'all') || (rowPartnerId === selectedPartner);
            const matchesSearch = !searchVal || rowText.includes(searchVal);

            let matchesDate = true;
            if (fromDate) {
                matchesDate = matchesDate && (cleanRowDate !== '' && cleanRowDate >= fromDate);
            }
            if (toDate) {
                matchesDate = matchesDate && (cleanRowDate !== '' && cleanRowDate <= toDate);
            }

            const isVisible = matchesType && matchesPartner && matchesSearch && matchesDate;
            row.dataset.mobiHidden = isVisible ? '0' : '1';
            row.style.display = isVisible ? '' : 'none';
            if (isVisible) visibleCount++;
        });

        if (window.emiPager && typeof window.emiPager.refresh === 'function') {
            window.emiPager.refresh();
        }

        const countEl = document.getElementById('emiVisibleCountBadge');
        if (countEl) {
            countEl.textContent = `${visibleCount} records`;
        }
    }

    function initEmiPage() {
        if (window.setupMobiTablePagination) {
            window.emiPager = window.setupMobiTablePagination({
                tableId: 'emiMasterTable',
                paginationContainerId: 'emiLedgerPagination',
                rowSelector: 'tbody tr.emi-row',
                pageSize: 25,
                itemName: 'transactions'
            });
        }
        filterEmiLedger();
        if (window.refreshIcons) window.refreshIcons();
        else if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initEmiPage);
    } else {
        initEmiPage();
    }

    function openAddProviderModal() {
        const el = document.getElementById('addEmiModal');
        el.classList.add('show');
        el.style.display = 'flex';
        if (window.lucide) window.lucide.createIcons();
    }
    function closeAddProviderModal() {
        const el = document.getElementById('addEmiModal');
        el.classList.remove('show');
        el.style.display = 'none';
    }

    function openDepositModal(providerId, providerName) {
        if (providerId) {
            document.getElementById('depositProviderSelect').value = providerId;
        }
        const el = document.getElementById('depositModal');
        el.classList.add('show');
        el.style.display = 'flex';
        if (window.lucide) window.lucide.createIcons();
    }
    function closeDepositModal() {
        const el = document.getElementById('depositModal');
        el.classList.remove('show');
        el.style.display = 'none';
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
        const el = document.getElementById('editProviderModal');
        el.classList.add('show');
        el.style.display = 'flex';
        if (window.lucide) window.lucide.createIcons();
    }
    function closeEditProviderModal() {
        const el = document.getElementById('editProviderModal');
        el.classList.remove('show');
        el.style.display = 'none';
    }

    // Backdrop click outside to close
    document.addEventListener('DOMContentLoaded', function() {
        ['addEmiModal', 'depositModal', 'editProviderModal'].forEach(id => {
            const modal = document.getElementById(id);
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        this.classList.remove('show');
                        this.style.display = 'none';
                    }
                });
            }
        });
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAddProviderModal();
            closeDepositModal();
            closeEditProviderModal();
        }
    });
</script>
@endpush
