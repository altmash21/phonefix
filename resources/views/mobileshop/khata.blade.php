@extends('mobileshop.layout')

@section('title', 'Customer Khata (Udhari) — MobiTrack')
@section('page-title', 'Customer Khata & Repayments')

@section('page-actions')
    <button type="button" onclick="openDebtorsModal()" class="btn btn-outline" style="font-weight:700; color:#854D0E; border-color:#FEF08A; background:#FEFCE8; display:inline-flex; align-items:center; gap:6px; cursor:pointer;" title="View all debtors and send WhatsApp payment reminders">
        <i data-lucide="bell" style="width:16px;height:16px; color:#CA8A04;"></i> Active Debtors ({{ $customers->where('udhari_balance', '>', 0)->count() }})
    </button>
    <button onclick="openRepayModal()" class="btn btn-primary" style="background:#16A34A; color:#fff; font-weight:800; border:none; display:inline-flex; align-items:center; gap:6px;">
        <i data-lucide="plus-circle" style="width:16px;height:16px;"></i> Record Customer Repayment
    </button>
@endsection

@section('content')

<!-- Mobile Horizontal Stat Strip -->
<div class="mobile-stat-strip">
    <div class="stat-strip-item" onclick="openDebtorsModal()">
        <span class="stat-label">Receivables</span>
        <span class="stat-val" style="color:#DC2626;">₹{{ number_format($customers->sum('udhari_balance'), 0) }}</span>
    </div>
    <div class="stat-divider"></div>
    <div class="stat-strip-item" onclick="openDebtorsModal()">
        <span class="stat-label">Debtors</span>
        <span class="stat-val" style="color:#CA8A04;">{{ $customers->where('udhari_balance', '>', 0)->count() }}</span>
    </div>
    <div class="stat-divider"></div>
    <div class="stat-strip-item">
        <span class="stat-label">Settled</span>
        <span class="stat-val" style="color:#16A34A;">{{ $customers->where('udhari_balance', '<=', 0)->count() }}</span>
    </div>
</div>

<!-- Desktop Stat Grid (Hidden on Mobile) -->
<div class="stat-grid" id="khataDesktopStatGrid" style="margin-bottom: 12px; display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:10px;">
    <div class="stat-card" style="background:#FFF1F2; border:1px solid #FECDD3; padding:10px 14px; border-radius:8px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
            <span style="font-size:10.5px; font-weight:800; text-transform:uppercase; color:#991B1B;">Total Receivables</span>
            <i data-lucide="alert-circle" style="width:15px;height:15px; color:#DC2626;"></i>
        </div>
        <div style="font-size:18px; font-weight:800; color:#991B1B;">₹{{ number_format($customers->sum('udhari_balance'), 2) }}</div>
        <div style="font-size:10.5px; color:#B91C1C; margin-top:2px;">Live Outstanding Across Store</div>
    </div>
    <div class="stat-card" onclick="openDebtorsModal()" style="background:#FEFCE8; border:1px solid #FEF08A; padding:10px 14px; border-radius:8px; cursor:pointer; transition:transform 0.15s ease;" title="Click to view all active debtors & WhatsApp reminders">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
            <span style="font-size:10.5px; font-weight:800; text-transform:uppercase; color:#854D0E;">Active Debtors (Click to View)</span>
            <i data-lucide="bell" style="width:15px;height:15px; color:#CA8A04;"></i>
        </div>
        <div style="font-size:18px; font-weight:800; color:#854D0E;">{{ $customers->where('udhari_balance', '>', 0)->count() }}</div>
        <div style="font-size:10.5px; color:#A16207; margin-top:2px;">Customers with Pending Khata • Click to Remind</div>
    </div>
    <div class="stat-card" style="background:#F0FDF4; border:1px solid #BBF7D0; padding:10px 14px; border-radius:8px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
            <span style="font-size:10.5px; font-weight:800; text-transform:uppercase; color:#166534;">Settled Accounts</span>
            <i data-lucide="check-circle" style="width:15px;height:15px; color:#16A34A;"></i>
        </div>
        <div style="font-size:18px; font-weight:800; color:#166534;">{{ $customers->where('udhari_balance', '<=', 0)->count() }}</div>
        <div style="font-size:10.5px; color:#15803D; margin-top:2px;">Cleared / Zero Debt Customers</div>
    </div>
</div>

<!-- SINGLE UNIFIED KHATA MASTER LIST -->
<div class="card" style="margin-bottom: 12px; border-radius:8px; border:1px solid #E2E8F0; overflow:hidden;">
    <div class="card-header" style="background:#F8FAFC; border-bottom:1px solid #E2E8F0; padding:10px 14px; display:flex; flex-direction:column; gap:8px;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
            <div>
                <div class="card-title" style="display:flex; align-items:center; gap:8px;">
                    Customer Khata & Credit Ledger
                    <span id="khataVisibleCountBadge" class="badge badge-blue" style="font-size:11px; font-weight:700;"></span>
                </div>
                <div style="font-size:12px; color:#64748B;">Unified register of all credit sales, payments, and ledger balances</div>
            </div>

            <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                <!-- Filter By Customer Dropdown -->
                <select id="customerFilterSelect" onchange="filterKhataLedger()" class="form-control" style="font-size:12px; font-weight:700; width:auto; border-color:#CBD5E1; padding:6px 12px; height:auto; color:#0F172A;">
                    <option value="all">👤 All Customers</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} (Due: ₹{{ number_format($c->udhari_balance, 2) }})</option>
                    @endforeach
                </select>

                <!-- Search input -->
                <div class="search-bar" style="background:#fff; border:1px solid #CBD5E1; border-radius:8px; padding:5px 12px; display:flex; align-items:center; gap:6px;">
                    <i data-lucide="search" style="width:14px;height:14px; color:#94A3B8;"></i>
                    <input type="text" id="khataSearchInput" placeholder="Search bill #, customer, phone..." oninput="filterKhataLedger()" style="border:none; outline:none; font-size:12px; color:#0F172A; width:180px;">
                </div>
            </div>
        </div>

        <!-- Filter Controls Row: Type Pills & Date Toolbar -->
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; background:#fff; border:1px solid #E2E8F0; padding:8px 12px; border-radius:8px;">
            <!-- Type Filter Pills -->
            <div style="display:flex; gap:5px; background:#E2E8F0; padding:3px; border-radius:8px; flex-wrap:wrap;">
                <button type="button" onclick="setKhataFilterType('all')" id="btnFilterAll" class="filter-pill active" style="padding:4px 10px; border-radius:6px; font-size:11px; font-weight:800; border:none; cursor:pointer;">
                    All ({{ $transactions->count() }})
                </button>
                <button type="button" onclick="setKhataFilterType('udhari_sale')" id="btnFilterUdhari" class="filter-pill" style="padding:4px 10px; border-radius:6px; font-size:11px; font-weight:700; border:none; cursor:pointer;">
                    🔴 Udhari Dues ({{ $transactions->where('type', 'udhari_sale')->count() }})
                </button>
                <button type="button" onclick="setKhataFilterType('payment_received')" id="btnFilterPayment" class="filter-pill" style="padding:4px 10px; border-radius:6px; font-size:11px; font-weight:700; border:none; cursor:pointer;">
                    🟢 Repayments ({{ $transactions->where('type', 'payment_received')->count() }})
                </button>
                <button type="button" onclick="setKhataFilterType('adjustment')" id="btnFilterAdjustment" class="filter-pill" style="padding:4px 10px; border-radius:6px; font-size:11px; font-weight:700; border:none; cursor:pointer;">
                    🔵 Adjustments ({{ $transactions->where('type', 'adjustment')->count() }})
                </button>
            </div>

            <!-- Date Range Filters -->
            <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                <!-- Date Presets -->
                <div style="display:flex; gap:4px; align-items:center;">
                    <button type="button" onclick="setKhataDatePreset('all')" id="khataDateBtn_all" class="filter-pill khata-date-pill active" style="padding:4px 8px; font-size:11px; font-weight:700; border:none; cursor:pointer;">All Time</button>
                    <button type="button" onclick="setKhataDatePreset('today')" id="khataDateBtn_today" class="filter-pill khata-date-pill" style="padding:4px 8px; font-size:11px; font-weight:700; border:none; cursor:pointer;">Today</button>
                    <button type="button" onclick="setKhataDatePreset('yesterday')" id="khataDateBtn_yesterday" class="filter-pill khata-date-pill" style="padding:4px 8px; font-size:11px; font-weight:700; border:none; cursor:pointer;">Yesterday</button>
                    <button type="button" onclick="setKhataDatePreset('week')" id="khataDateBtn_week" class="filter-pill khata-date-pill" style="padding:4px 8px; font-size:11px; font-weight:700; border:none; cursor:pointer;">7 Days</button>
                    <button type="button" onclick="setKhataDatePreset('month')" id="khataDateBtn_month" class="filter-pill khata-date-pill" style="padding:4px 8px; font-size:11px; font-weight:700; border:none; cursor:pointer;">This Month</button>
                </div>

                <!-- Custom Range -->
                <div style="display:flex; align-items:center; gap:4px;">
                    <label for="khataFromDate" style="font-size:11px; font-weight:700; color:#64748B; margin:0;">From:</label>
                    <input type="date" id="khataFromDate" onchange="onKhataCustomDateChange()" class="form-control" style="font-size:11px; padding:3px 6px; height:auto; width:auto; font-weight:600; color:#0F172A;">
                </div>
                <div style="display:flex; align-items:center; gap:4px;">
                    <label for="khataToDate" style="font-size:11px; font-weight:700; color:#64748B; margin:0;">To:</label>
                    <input type="date" id="khataToDate" onchange="onKhataCustomDateChange()" class="form-control" style="font-size:11px; padding:3px 6px; height:auto; width:auto; font-weight:600; color:#0F172A;">
                </div>
                <button type="button" onclick="setKhataDatePreset('all')" title="Reset Date Filter" style="background:#F1F5F9; border:1px solid #CBD5E1; color:#64748B; border-radius:6px; padding:3px 6px; font-size:11px; font-weight:700; cursor:pointer;">Reset</button>
            </div>
        </div>
    </div>

    <div class="data-table-wrap" id="khataDesktopTableWrap">
        <table class="data-table" id="khataMasterTable" style="margin:0; width:100%;">
            <thead style="background:#F1F5F9;">
                <tr>
                    <th style="color:#475569; font-weight:700; font-size:12px; padding:12px 16px;">Date & Time</th>
                    <th style="color:#475569; font-weight:700; font-size:12px;">Customer</th>
                    <th style="color:#475569; font-weight:700; font-size:12px;">Details & Reference</th>
                    <th style="color:#475569; font-weight:700; font-size:12px;">Transaction Type</th>
                    <th style="color:#475569; font-weight:700; font-size:12px;">Payment Mode</th>
                    <th style="text-align:right; color:#475569; font-weight:700; font-size:12px;">Amount</th>
                    <th style="text-align:right; color:#475569; font-weight:700; font-size:12px;">Balance After</th>
                    <th style="text-align:center; color:#475569; font-weight:700; font-size:12px;">Action / Settlement</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $t)
                    @php
                        $invoiceId = null;
                        $invoiceType = null;
                        if (!empty($t->reference_no)) {
                            if (str_starts_with($t->reference_no, 'INV-') || str_starts_with($t->reference_no, 'PHONE-')) {
                                $invoiceType = 'phone';
                                $saleRec = \Illuminate\Support\Facades\DB::table('ms_mobile_sales')->where('invoice_number', $t->reference_no)->first();
                                if ($saleRec) $invoiceId = $saleRec->id;
                            } elseif (str_starts_with($t->reference_no, 'ACC-')) {
                                $invoiceType = 'accessory';
                                $saleRec = \Illuminate\Support\Facades\DB::table('ms_accessory_sales')->where('invoice_number', $t->reference_no)->first();
                                if ($saleRec) $invoiceId = $saleRec->id;
                            }
                        }
                    @endphp
                    <tr class="khata-row" data-type="{{ $t->type }}" data-customer-id="{{ $t->customer_id }}" data-date="{{ date('Y-m-d', strtotime($t->created_at)) }}" data-search="{{ strtolower(($t->customer_name ?? '') . ' ' . ($t->customer_phone ?? '') . ' ' . ($t->reference_no ?? '') . ' ' . ($t->remarks ?? '')) }}">
                        <td style="color:#64748B; font-size:12px; font-weight:500; white-space:nowrap; padding:12px 16px;">
                            {{ date('d M Y, H:i', strtotime($t->created_at)) }}
                        </td>
                        <td>
                            <div style="font-weight:800; color:#0F172A; font-size:13px;">{{ $t->customer_name }}</div>
                            <div style="font-size:11px; color:#64748B; font-family:monospace;">{{ $t->customer_phone ?? ($t->phone ?? '—') }}</div>
                        </td>
                        <td>
                            @if($invoiceNum = $t->reference_no)
                                <div style="display:inline-flex; align-items:center; gap:6px; margin-bottom:2px;">
                                    <span style="font-family:monospace; font-weight:800; color:#0F172A; font-size:12px;">#{{ $invoiceNum }}</span>
                                    <span class="badge" style="background:#EFF6FF; color:#2563EB; font-size:10px; font-weight:700;">{{ strtoupper($invoiceType ?: 'REF') }}</span>
                                </div>
                            @endif
                            <div style="font-size:12px; color:#334155; font-weight:500;">
                                {{ $t->remarks ?: 'Khata transaction' }}
                            </div>
                        </td>
                        <td>
                            @if($t->type === 'payment_received')
                                <span class="badge" style="background:#DCFCE7; color:#15803D; font-weight:700; font-size:10px;">CREDIT / REPAYMENT</span>
                            @elseif($t->type === 'udhari_sale')
                                <span class="badge" style="background:#FEE2E2; color:#B91C1C; font-weight:700; font-size:10px;">DEBIT / UDHARI SALE</span>
                            @else
                                <span class="badge" style="background:#E0F2FE; color:#0369A1; font-weight:700; font-size:10px;">ADJUSTMENT / REVERSAL</span>
                            @endif
                        </td>
                        <td style="color:#475569; text-transform:uppercase; font-size:11px; font-weight:600; white-space:nowrap;">
                            {{ $t->payment_mode ?: 'Khata Credit' }}
                        </td>
                        <td style="text-align:right; font-weight:800; font-size:13px; color: {{ $t->type === 'payment_received' ? '#16A34A' : '#DC2626' }}; white-space:nowrap;">
                            {{ $t->type === 'payment_received' ? '-' : '+' }}₹{{ number_format($t->amount, 2) }}
                        </td>
                        <td style="text-align:right; font-weight:900; color:#0F172A; font-size:13px; white-space:nowrap;">
                            ₹{{ number_format($t->balance_after, 2) }}
                        </td>
                        <td style="text-align:center; white-space:nowrap;">
                            <div style="display:inline-flex; gap:4px; align-items:center;">
                                <a href="{{ route('mobileshop.khata.customer_statement', ['id' => $t->customer_id]) }}" class="btn btn-outline btn-sm" style="font-size:11px; padding:4px 8px; font-weight:700; color:#0F172A; text-decoration:none;" title="View Full Tally Statement / Ledger">
                                    <i data-lucide="file-text" style="width:12px;height:12px;"></i> Statement
                                </a>
                                @if(($t->current_customer_due ?? 0) > 0)
                                    <button type="button" onclick="openRepayForCustomer({{ $t->customer_id }}, '{{ addslashes($t->customer_name) }}', {{ $t->current_customer_due }})" class="btn btn-primary btn-sm" style="background:#16A34A; color:#fff; font-weight:700; border:none; padding:5px 10px; border-radius:6px; font-size:11px; cursor:pointer;" title="Collect pending balance (Due: ₹{{ number_format($t->current_customer_due, 2) }})">
                                        <i data-lucide="wallet" style="width:12px;height:12px;"></i> Collect
                                    </button>
                                @else
                                    <span class="badge" style="background:#DCFCE7; color:#15803D; font-weight:700; font-size:10px; padding:3px 6px;">Settled</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align:center; padding: 48px 20px;">
                            <div style="max-width:380px; margin:0 auto; display:flex; flex-direction:column; align-items:center; gap:10px;">
                                <div style="width:48px; height:48px; border-radius:50%; background:#DCFCE7; display:flex; align-items:center; justify-content:center; color:#16A34A;">
                                    <i data-lucide="check-circle" style="width:24px;height:24px;"></i>
                                </div>
                                <div style="font-weight:800; font-size:15px; color:#0F172A;">No Khata Transactions Yet</div>
                                <div style="font-size:12px; color:#64748B;">Credit sales and repayments will automatically show in this unified ledger.</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Zero-Depth Flat Cards List -->
    <div id="khataMobileCards" style="display:none; padding:10px 12px; flex-direction:column; gap:8px;">
        @forelse($transactions as $t)
        <div class="app-flat-row khata-card" data-type="{{ $t->type }}" data-customer-id="{{ $t->customer_id }}" data-date="{{ date('Y-m-d', strtotime($t->created_at)) }}" data-search="{{ strtolower(($t->customer_name ?? '') . ' ' . ($t->customer_phone ?? '') . ' ' . ($t->reference_no ?? '') . ' ' . ($t->remarks ?? '')) }}">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; width:100%;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:34px; height:34px; border-radius:8px; display:flex; align-items:center; justify-content:center; background:{{ $t->type === 'payment_received' ? '#DCFCE7' : '#FEE2E2' }}; color:{{ $t->type === 'payment_received' ? '#15803D' : '#DC2626' }}; flex-shrink:0;">
                        <i data-lucide="{{ $t->type === 'payment_received' ? 'arrow-down-left' : 'arrow-up-right' }}" style="width:16px;height:16px;"></i>
                    </div>
                    <div>
                        <div style="font-weight:800; font-size:13px; color:#0F172A;">{{ $t->customer_name }}</div>
                        <div style="font-size:10px; color:#64748B;">{{ date('d M Y, H:i', strtotime($t->created_at)) }} @if($t->reference_no) • #{{ $t->reference_no }} @endif</div>
                    </div>
                </div>
                <div style="text-align:right;">
                    <div style="font-weight:900; font-size:14px; color:{{ $t->type === 'payment_received' ? '#16A34A' : '#DC2626' }};">
                        {{ $t->type === 'payment_received' ? '-' : '+' }}₹{{ number_format($t->amount, 0) }}
                    </div>
                    <div style="font-size:10px; color:#64748B;">Bal: ₹{{ number_format($t->balance_after, 0) }}</div>
                </div>
            </div>
            @if(($t->current_customer_due ?? 0) > 0)
            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:6px; padding-top:6px; border-top:1px solid #F1F5F9; width:100%;">
                <span style="font-size:11px; font-weight:700; color:#DC2626;">Due: ₹{{ number_format($t->current_customer_due, 0) }}</span>
                <button type="button" onclick="openRepayForCustomer({{ $t->customer_id }}, '{{ addslashes($t->customer_name) }}', {{ $t->current_customer_due }})" class="btn btn-primary btn-sm" style="font-size:11px; padding:3px 8px; height:auto; background:#16A34A;">
                    <i data-lucide="wallet" style="width:12px;height:12px;"></i> Collect
                </button>
            </div>
            @endif
        </div>
        @empty
        <div style="text-align:center; padding:30px 16px; color:#94A3B8; font-size:12px;">No khata transactions found.</div>
        @endforelse
    </div>
    <div id="khataPagination"></div>
</div>

<!-- Mobile Floating Action Button -->
<div class="mobile-fab-container">
    <button type="button" class="btn-app-fab" onclick="openRepayModal()" title="Record Customer Repayment" style="background:#16A34A;">
        <i data-lucide="plus" style="width:20px;height:20px;"></i>
        <span>Repayment</span>
    </button>
</div>

<!-- Repayment Modal -->
<div id="repayModal" style="display:none; position: fixed; inset: 0; z-index: 1200; background: rgba(15,23,42,0.6); backdrop-filter: blur(4px); align-items:center; justify-content:center; padding: 16px;">
    <div class="card" style="max-width: 480px; width: 100%; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); border-radius:14px; border:none; background:#fff;">
        <div style="background:#0F172A; color:#fff; padding:16px 20px; border-top-left-radius:14px; border-top-right-radius:14px; display:flex; justify-content:space-between; align-items:center;">
            <div style="font-size:15px; font-weight:800; display:flex; align-items:center; gap:8px;">
                <i data-lucide="wallet" style="width:18px;height:18px; color:#4ADE80;"></i>
                Collect Khata / Udhari Payment
            </div>
            <button type="button" onclick="closeRepayModal()" style="background:rgba(255,255,255,0.1); border:none; color:#FFFFFF; font-size:16px; width:28px; height:28px; border-radius:6px; cursor:pointer; display:flex; align-items:center; justify-content:center;">✕</button>
        </div>
        <div class="card-body" style="padding:20px;">
            <form action="{{ route('mobileshop.khata.collect', ['company_id' => session('company_id', 1)]) }}" method="POST">
                @csrf
                <div class="form-group" style="margin-bottom:14px;">
                    <label class="form-label required" style="font-weight:700; color:#0F172A; font-size:12px;">Select Customer</label>
                    <select name="customer_id" id="repayCustomerSelect" required class="form-control" onchange="onRepayCustomerChange(this)" style="font-weight:700; color:#0F172A; border-color:#CBD5E1;">
                        <option value="">-- Choose Customer --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" data-due="{{ $c->udhari_balance }}">
                                {{ $c->name }} (Phone: {{ $c->phone }}) — Due: ₹{{ number_format($c->udhari_balance, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="margin-bottom:14px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                        <label class="form-label required" style="font-weight:700; color:#0F172A; font-size:12px; margin-bottom:0;">Payment Amount Received (₹)</label>
                        <div style="display:flex; gap:4px;">
                            <button type="button" onclick="setFullDueAmount()" style="font-size:10px; padding:2px 8px; border-radius:4px; font-weight:700; color:#16A34A; border:1px solid #BBF7D0; background:#F0FDF4; cursor:pointer;">Full Due</button>
                            <button type="button" onclick="setHalfDueAmount()" style="font-size:10px; padding:2px 8px; border-radius:4px; font-weight:700; color:#2563EB; border:1px solid #BFDBFE; background:#EFF6FF; cursor:pointer;">50% Partial</button>
                        </div>
                    </div>
                    <input type="number" step="0.01" name="amount" id="repayAmount" required placeholder="0.00" class="form-control" style="font-size:18px; font-weight:900; color:#16A34A; border-color:#CBD5E1;">
                </div>

                <div class="form-group" style="margin-bottom:14px;">
                    <label class="form-label required" style="font-weight:700; color:#0F172A; font-size:12px;">Payment Mode</label>
                    <select name="payment_mode" required class="form-control" style="font-weight:700; color:#0F172A; border-color:#CBD5E1;">
                        <option value="cash">💵 Cash Payment</option>
                        <option value="upi">📱 UPI / QR Transfer</option>
                        <option value="bank_transfer">🏦 Direct Bank Transfer</option>
                        <option value="cheque">📝 Cheque</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom:16px;">
                    <label class="form-label" style="font-weight:700; color:#475569; font-size:12px;">Transaction Reference / Notes</label>
                    <input type="text" name="reference_no" placeholder="e.g. UPI Ref / Cheque No / Notes..." class="form-control" style="font-weight:600; color:#0F172A; border-color:#CBD5E1;">
                </div>

                <div style="display:flex; justify-content:flex-end; gap: 10px; padding-top: 14px; border-top: 1px solid #E2E8F0;">
                    <button type="button" onclick="closeRepayModal()" class="btn btn-outline" style="border:1px solid #CBD5E1; color:#475569; font-weight:700; padding:8px 18px; border-radius:8px; cursor:pointer;">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="background:#16A34A; color:#FFFFFF; font-weight:800; padding:9px 22px; border:none; border-radius:8px; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                        <i data-lucide="check-circle" style="width:16px;height:16px;"></i> Record Repayment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Active Debtors Quick Drawer / Modal -->
<div id="debtorsModal" style="display:none; position: fixed; inset: 0; z-index: 1200; background: rgba(15,23,42,0.65); backdrop-filter: blur(4px); align-items:center; justify-content:center; padding: 16px;">
    <div class="card" style="max-width: 720px; width: 100%; max-height: 90vh; display:flex; flex-direction:column; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); border-radius:14px; border:none; background:#fff; overflow:hidden;">
        <!-- Header -->
        <div style="background:#854D0E; color:#fff; padding:16px 20px; display:flex; justify-content:space-between; align-items:center;">
            <div>
                <div style="font-size:16px; font-weight:800; display:flex; align-items:center; gap:8px;">
                    <i data-lucide="bell" style="width:18px;height:18px; color:#FEF08A;"></i>
                    Active Debtors & WhatsApp Reminders
                </div>
                <div style="font-size:11.5px; color:#FEF08A; margin-top:2px;">
                    1-Click payment reminders via WhatsApp Universal Web • Zero API charges
                </div>
            </div>
            <button type="button" onclick="closeDebtorsModal()" style="background:rgba(255,255,255,0.15); border:none; color:#FFFFFF; font-size:16px; width:28px; height:28px; border-radius:6px; cursor:pointer; display:flex; align-items:center; justify-content:center;">✕</button>
        </div>

        <!-- Search input -->
        <div style="padding:12px 20px; background:#FEFCE8; border-bottom:1px solid #FEF08A; display:flex; justify-content:space-between; align-items:center; gap:12px;">
            <div style="display:flex; align-items:center; gap:8px; width:100%; max-width:360px; background:#fff; border:1px solid #CBD5E1; border-radius:8px; padding:6px 12px;">
                <i data-lucide="search" style="width:14px;height:14px; color:#94A3B8;"></i>
                <input type="text" id="debtorSearchInput" oninput="filterDebtorModalList()" placeholder="Filter debtors by name or phone..." style="border:none; outline:none; font-size:12px; width:100%; color:#0F172A;">
            </div>
            <div style="font-size:12px; font-weight:800; color:#854D0E;">
                Total Outstanding: <span style="color:#DC2626;">₹{{ number_format($customers->sum('udhari_balance'), 2) }}</span>
            </div>
        </div>

        <!-- Table List -->
        <div style="padding:0; overflow-y:auto; flex:1;">
            <table class="data-table" id="debtorModalTable" style="margin:0; width:100%;">
                <thead style="background:#F8FAFC; position:sticky; top:0; z-index:2;">
                    <tr>
                        <th style="font-size:11px; padding:10px 16px;">Customer</th>
                        <th style="font-size:11px;">Phone</th>
                        <th style="text-align:right; font-size:11px;">Pending Due</th>
                        <th style="text-align:center; font-size:11px;">Quick Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $activeDebtors = $customers->where('udhari_balance', '>', 0)->sortByDesc('udhari_balance');
                        $stName = setting('company.name', 'MobiTrack');
                        $stPhone = setting('company.phone', '+91 98765 43210');
                    @endphp
                    @forelse($activeDebtors as $d)
                        @php
                            $dPhone = preg_replace('/[^0-9]/', '', $d->phone ?? '');
                            if (strlen($dPhone) === 10) $dPhone = '91' . $dPhone;
                            $dMsg = "🔔 *PAYMENT REMINDER*\n";
                            $dMsg .= "🏪 *{$stName}*\n";
                            $dMsg .= "------------------------------------\n";
                            $dMsg .= "Dear *{$d->name}*,\n\n";
                            $dMsg .= "This is a friendly reminder regarding your pending store credit balance:\n";
                            $dMsg .= "📌 *Outstanding Due Amount:* *₹" . number_format($d->udhari_balance, 2) . "*\n\n";
                            $dMsg .= "Kindly clear this balance at your earliest convenience via Cash or UPI at our counter.\n";
                            $dMsg .= "If already paid, please disregard this message.\n\n";
                            $dMsg .= "Thank you for your valued business!\n";
                            $dMsg .= "📞 *Store Support:* {$stPhone}";
                            $dWaUrl = 'https://wa.me/' . $dPhone . '?text=' . rawurlencode($dMsg);
                        @endphp
                        <tr class="debtor-item-row" data-name="{{ strtolower($d->name) }}" data-phone="{{ $d->phone }}">
                            <td style="padding:12px 16px;">
                                <div style="font-weight:800; font-size:13px; color:#0F172A;">{{ $d->name }}</div>
                                @if(!empty($d->address))
                                    <div style="font-size:10.5px; color:#64748B;">{{ Str::limit($d->address, 30) }}</div>
                                @endif
                            </td>
                            <td>
                                <div style="font-family:monospace; font-weight:700; color:#475569; font-size:12px;">{{ $d->phone ?: '—' }}</div>
                            </td>
                            <td style="text-align:right;">
                                <div style="font-weight:900; color:#DC2626; font-size:14px; font-family:monospace;">
                                    ₹{{ number_format($d->udhari_balance, 2) }}
                                </div>
                            </td>
                            <td style="text-align:center; white-space:nowrap;">
                                <div style="display:inline-flex; gap:6px; align-items:center;">
                                    <a href="{{ route('mobileshop.khata.customer_statement', ['id' => $d->id]) }}" class="btn btn-outline btn-sm" style="font-weight:700; padding:5px 10px; border-radius:6px; font-size:11px; text-decoration:none; color:#0F172A;" title="View & Print Tally Ledger Statement">
                                        <i data-lucide="file-text" style="width:12px;height:12px;"></i> Statement
                                    </a>
                                    <a href="{{ $dWaUrl }}" target="_blank" class="btn btn-sm" style="background:#25D366; color:#fff; font-weight:700; border:none; padding:5px 10px; border-radius:6px; font-size:11px; display:inline-flex; align-items:center; gap:4px; text-decoration:none;" title="Send WhatsApp payment reminder">
                                        <svg style="width:13px;height:13px;fill:currentColor;" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                        Remind
                                    </a>
                                    <button type="button" onclick="closeDebtorsModal(); openRepayForCustomer({{ $d->id }}, '{{ addslashes($d->name) }}', {{ $d->udhari_balance }})" class="btn btn-primary btn-sm" style="background:#16A34A; color:#fff; font-weight:700; border:none; padding:5px 10px; border-radius:6px; font-size:11px; cursor:pointer;" title="Collect pending balance">
                                        <i data-lucide="wallet" style="width:12px;height:12px;"></i> Collect
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align:center; padding: 36px 20px; color:#15803D; font-weight:700;">
                                🎉 All accounts are settled! There are no active debtors with outstanding balances.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="padding:12px 20px; background:#F8FAFC; border-top:1px solid #E2E8F0; text-align:right;">
            <button type="button" onclick="closeDebtorsModal()" class="btn btn-outline" style="border:1px solid #CBD5E1; color:#475569; font-weight:700; padding:6px 16px; border-radius:8px; cursor:pointer;">Close</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let activeTypeFilter = 'all';

    function setKhataFilterType(type) {
        activeTypeFilter = type;
        document.getElementById('btnFilterAll').classList.remove('active');
        document.getElementById('btnFilterUdhari').classList.remove('active');
        document.getElementById('btnFilterPayment').classList.remove('active');
        document.getElementById('btnFilterAdjustment').classList.remove('active');

        if (type === 'all') {
            document.getElementById('btnFilterAll').classList.add('active');
        } else if (type === 'udhari_sale') {
            document.getElementById('btnFilterUdhari').classList.add('active');
        } else if (type === 'payment_received') {
            document.getElementById('btnFilterPayment').classList.add('active');
        } else if (type === 'adjustment') {
            document.getElementById('btnFilterAdjustment').classList.add('active');
        }

        filterKhataLedger();
    }

    let currentKhataDatePreset = 'all';

    function setKhataDatePreset(preset) {
        currentKhataDatePreset = preset;
        const fromInput = document.getElementById('khataFromDate');
        const toInput = document.getElementById('khataToDate');

        document.querySelectorAll('.khata-date-pill').forEach(el => el.classList.remove('active'));
        const targetId = 'khataDateBtn_' + (preset === '7days' ? 'week' : preset);
        const btn = document.getElementById(targetId) || document.getElementById('khataDateBtn_' + preset);
        if (btn) btn.classList.add('active');

        const range = (window.getDateRangePreset && typeof window.getDateRangePreset === 'function')
            ? window.getDateRangePreset(preset)
            : { from: '', to: '' };

        if (fromInput) fromInput.value = range.from || '';
        if (toInput) toInput.value = range.to || '';

        filterKhataLedger();
    }

    function onKhataCustomDateChange() {
        document.querySelectorAll('.khata-date-pill').forEach(el => el.classList.remove('active'));
        filterKhataLedger();
    }

    function filterKhataLedger() {
        const selectedCustomer = document.getElementById('customerFilterSelect').value;
        const searchVal = document.getElementById('khataSearchInput').value.toLowerCase().trim();
        const fromDate = document.getElementById('khataFromDate')?.value || '';
        const toDate = document.getElementById('khataToDate')?.value || '';

        let visibleCount = 0;

        document.querySelectorAll('#khataMasterTable tbody tr.khata-row').forEach(row => {
            const rowType = row.dataset.type;
            const rowCustomerId = row.dataset.customerId;
            const rowText = (row.dataset.search || row.textContent).toLowerCase();
            const rawDate = (row.dataset.date || '').trim();
            const cleanRowDate = rawDate.length >= 10 ? rawDate.slice(0, 10) : rawDate;

            // Type filter
            const matchesType = (activeTypeFilter === 'all') || (rowType === activeTypeFilter);
            // Customer filter
            const matchesCustomer = (selectedCustomer === 'all') || (rowCustomerId === selectedCustomer);
            // Search filter
            const matchesSearch = !searchVal || rowText.includes(searchVal);
            // Date filter
            let matchesDate = true;
            if (fromDate) {
                matchesDate = matchesDate && (cleanRowDate !== '' && cleanRowDate >= fromDate);
            }
            if (toDate) {
                matchesDate = matchesDate && (cleanRowDate !== '' && cleanRowDate <= toDate);
            }

            const isVisible = matchesType && matchesCustomer && matchesSearch && matchesDate;
            row.dataset.mobiHidden = isVisible ? '0' : '1';
            row.style.display = isVisible ? '' : 'none';
            if (isVisible) visibleCount++;
        });

        // Filter mobile cards
        document.querySelectorAll('#khataMobileCards .khata-card').forEach(card => {
            const cardType = card.dataset.type;
            const cardCustomerId = card.dataset.customerId;
            const cardText = (card.dataset.search || card.textContent).toLowerCase();
            const rawDate = (card.dataset.date || '').trim();
            const cleanRowDate = rawDate.length >= 10 ? rawDate.slice(0, 10) : rawDate;

            const matchesType = (activeTypeFilter === 'all') || (cardType === activeTypeFilter);
            const matchesCustomer = (selectedCustomer === 'all') || (cardCustomerId === selectedCustomer);
            const matchesSearch = !searchVal || cardText.includes(searchVal);
            let matchesDate = true;
            if (fromDate) matchesDate = matchesDate && (cleanRowDate !== '' && cleanRowDate >= fromDate);
            if (toDate) matchesDate = matchesDate && (cleanRowDate !== '' && cleanRowDate <= toDate);

            const isCardVisible = matchesType && matchesCustomer && matchesSearch && matchesDate;
            card.dataset.mobiHidden = isCardVisible ? '0' : '1';
            card.style.display = isCardVisible ? '' : 'none';
        });

        if (window.khataPager && typeof window.khataPager.refresh === 'function') {
            window.khataPager.refresh();
        }

        const countEl = document.getElementById('khataVisibleCountBadge');
        if (countEl) {
            countEl.textContent = `${visibleCount} entries`;
        }
    }

    function initKhataPage() {
        if (window.setupMobiTablePagination) {
            window.khataPager = window.setupMobiTablePagination({
                tableId: 'khataMasterTable',
                cardsContainerId: 'khataMobileCards',
                paginationContainerId: 'khataPagination',
                rowSelector: 'tbody tr.khata-row',
                cardSelector: '.khata-card',
                pageSize: 25,
                itemName: 'transactions'
            });
        }
        filterKhataLedger();
        if (window.refreshIcons) window.refreshIcons();
        else if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initKhataPage);
    } else {
        initKhataPage();
    }
</script>
<style>
@media (max-width: 768px) {
    #khataDesktopStatGrid { display: none !important; }
    #khataDesktopTableWrap { display: none !important; }
    #khataMobileCards { display: flex !important; }
}
</style>
<script>

    function openRepayModal() {
        document.getElementById('repayModal').style.display = 'flex';
        if (window.refreshIcons) window.refreshIcons();
        else if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons();
    }

    function closeRepayModal() {
        document.getElementById('repayModal').style.display = 'none';
    }

    function openRepayForCustomer(id, name, due) {
        const select = document.getElementById('repayCustomerSelect');
        select.value = id;
        document.getElementById('repayAmount').value = Number(due).toFixed(2);
        openRepayModal();
    }

    function onRepayCustomerChange(select) {
        const opt = select.options[select.selectedIndex];
        if (opt && opt.dataset.due) {
            const due = parseFloat(opt.dataset.due) || 0;
            document.getElementById('repayAmount').value = due > 0 ? due.toFixed(2) : '';
        }
    }

    function setFullDueAmount() {
        const select = document.getElementById('repayCustomerSelect');
        const opt = select.options[select.selectedIndex];
        if (opt && opt.dataset.due) {
            const due = parseFloat(opt.dataset.due) || 0;
            document.getElementById('repayAmount').value = due.toFixed(2);
        }
    }

    function setHalfDueAmount() {
        const select = document.getElementById('repayCustomerSelect');
        const opt = select.options[select.selectedIndex];
        if (opt && opt.dataset.due) {
            const due = parseFloat(opt.dataset.due) || 0;
            document.getElementById('repayAmount').value = (due / 2).toFixed(2);
        }
    }

    function openDebtorsModal() {
        const modal = document.getElementById('debtorsModal');
        if (modal) {
            modal.style.display = 'flex';
            if (window.lucide) window.lucide.createIcons();
        }
    }

    function closeDebtorsModal() {
        const modal = document.getElementById('debtorsModal');
        if (modal) modal.style.display = 'none';
    }

    function filterDebtorModalList() {
        const query = (document.getElementById('debtorSearchInput')?.value || '').toLowerCase().trim();
        document.querySelectorAll('#debtorModalTable tbody tr.debtor-item-row').forEach(row => {
            const name = (row.dataset.name || '').toLowerCase();
            const phone = (row.dataset.phone || '').toLowerCase();
            if (!query || name.includes(query) || phone.includes(query)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
</script>
@endpush
