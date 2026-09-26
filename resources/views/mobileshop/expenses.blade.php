@extends('mobileshop.layout')

@section('title', 'Shop Expenses Book — PhoneFix Azamgarh')
@section('page-title', 'Shop Expense Ledger')

@section('page-actions')
    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
        <button type="button" onclick="openExpenseModal()" class="btn btn-primary btn-sm" style="display:inline-flex; align-items:center; gap:6px; font-weight:700; border-radius:8px; padding:7px 14px; background:#4F46E5; color:#fff; border:none; cursor:pointer; box-shadow:0 2px 6px rgba(79, 70, 229, 0.3);">
            <i data-lucide="plus-circle" style="width:14px;height:14px;"></i> Record Expense
        </button>
        <a href="{{ route('mobileshop.expenses.export', request()->all()) }}" class="btn btn-outline btn-sm" style="display:inline-flex; align-items:center; gap:6px; font-weight:700; border-radius:8px; padding:7px 14px;">
            <i data-lucide="download" style="width:14px;height:14px;"></i> Export CSV
        </a>
    </div>
@endsection

@section('content')
<style>
    .expense-container {
        max-width: 1420px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .expense-kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 12px;
    }
    .expense-kpi-card {
        background: #ffffff;
        border: 1px solid #E2E8F0;
        border-radius: 10px;
        padding: 14px 18px;
        display: flex;
        flex-direction: column;
        gap: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
    .expense-kpi-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        color: #64748B;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .expense-kpi-val {
        font-size: 22px;
        font-weight: 900;
        font-family: 'JetBrains Mono', monospace;
        color: #0F172A;
    }
    .cat-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 700;
        border: 1px solid #E2E8F0;
        background: #FFFFFF;
        color: #334155;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.15s ease;
        white-space: nowrap;
    }
    .cat-chip:hover, .cat-chip.active {
        background: #EEF2FF;
        color: #4F46E5;
        border-color: #C7D2FE;
    }
    .expense-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12.5px;
    }
    .expense-table th {
        background: #F8FAFC;
        border-bottom: 2px solid #E2E8F0;
        padding: 10px 14px;
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        color: #64748B;
        text-align: left;
    }
    .expense-table td {
        padding: 12px 14px;
        border-bottom: 1px solid #F1F5F9;
        color: #1E293B;
        vertical-align: middle;
    }
    .expense-table tr:hover td {
        background: #F8FAFC;
    }
    .badge-cat {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        background: #F1F5F9;
        color: #334155;
        border: 1px solid #E2E8F0;
    }
    .badge-cat.rent        { background:#EFF6FF; color:#1D4ED8; border-color:#DBEAFE; }
    .badge-cat.electricity { background:#FEF3C7; color:#B45309; border-color:#FDE68A; }
    .badge-cat.salary      { background:#ECFDF5; color:#047857; border-color:#A7F3D0; }
    .badge-cat.tea         { background:#FFF7ED; color:#C2410C; border-color:#FFEDD5; }
    .badge-cat.transport   { background:#F3E8FF; color:#7E22CE; border-color:#E9D5FF; }
    .badge-cat.tools       { background:#F0FDF4; color:#15803D; border-color:#BBF7D0; }
    .badge-cat.maintenance { background:#F8FAFC; color:#475569; border-color:#CBD5E1; }
    .badge-mode {
        display: inline-block;
        padding: 2px 7px;
        border-radius: 4px;
        font-size: 10.5px;
        font-weight: 800;
        text-transform: uppercase;
        font-family: 'JetBrains Mono', monospace;
    }
    .badge-mode.cash { background:#ECFDF5; color:#065F46; border:1px solid #A7F3D0; }
    .badge-mode.upi  { background:#EFF6FF; color:#1E40AF; border:1px solid #BFDBFE; }
    .badge-mode.bank { background:#FAF5FF; color:#6B21A8; border:1px solid #E9D5FF; }
    .badge-mode.cheque { background:#FFFBEB; color:#92400E; border:1px solid #FDE68A; }

    /* Modal Backdrop & Dialog */
    .modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(2px);
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }
    .modal-dialog {
        background: #ffffff;
        border-radius: 12px;
        max-width: 540px;
        width: 100%;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2), 0 10px 10px -5px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        border: 1px solid #E2E8F0;
    }
</style>

<div class="expense-container">

    <!-- KPI Cards Row -->
    <div class="expense-kpi-grid">
        <div class="expense-kpi-card">
            <div class="expense-kpi-label">
                <i data-lucide="calendar" style="width:14px;height:14px; color:#4F46E5;"></i> This Month Total
            </div>
            <div class="expense-kpi-val" style="color:#4F46E5;">₹{{ number_format($monthExpenses, 2) }}</div>
        </div>

        <div class="expense-kpi-card">
            <div class="expense-kpi-label">
                <i data-lucide="clock" style="width:14px;height:14px; color:#D97706;"></i> Today's Spend
            </div>
            <div class="expense-kpi-val" style="color:#D97706;">₹{{ number_format($todayExpenses, 2) }}</div>
        </div>

        <div class="expense-kpi-card">
            <div class="expense-kpi-label">
                <i data-lucide="banknote" style="width:14px;height:14px; color:#059669;"></i> Cash Paid
            </div>
            <div class="expense-kpi-val" style="color:#059669;">₹{{ number_format($cashExpenses, 2) }}</div>
        </div>

        <div class="expense-kpi-card">
            <div class="expense-kpi-label">
                <i data-lucide="smartphone" style="width:14px;height:14px; color:#2563EB;"></i> UPI &amp; Digital
            </div>
            <div class="expense-kpi-val" style="color:#2563EB;">₹{{ number_format($upiExpenses, 2) }}</div>
        </div>
    </div>

    <!-- Category Chips Strip -->
    @if(isset($categoryBreakdown) && count($categoryBreakdown) > 0)
    <div class="card" style="padding:10px 14px; border:1px solid #E2E8F0; background:#FFFFFF; border-radius:8px;">
        <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase; margin-bottom:8px;">
            This Month Expense Breakdown:
        </div>
        <div style="display:flex; gap:8px; overflow-x:auto; padding-bottom:4px;">
            <a href="{{ route('mobileshop.expenses.index', array_merge(request()->except(['category', 'page']))) }}" class="cat-chip {{ empty($selectedCat) ? 'active' : '' }}">
                All Categories (₹{{ number_format($monthExpenses, 0) }})
            </a>
            @foreach($categoryBreakdown as $cb)
                @php
                    $cMeta = $categories[$cb->category] ?? ['label' => ucfirst(str_replace('_', ' ', $cb->category)), 'icon' => 'tag'];
                @endphp
                <a href="{{ route('mobileshop.expenses.index', array_merge(request()->except('page'), ['category' => $cb->category])) }}" class="cat-chip {{ $selectedCat === $cb->category ? 'active' : '' }}">
                    <i data-lucide="{{ $cMeta['icon'] }}" style="width:12px;height:12px;"></i>
                    <span>{{ $cMeta['label'] }}</span>
                    <strong style="font-family:'JetBrains Mono', monospace; font-size:11px;">₹{{ number_format($cb->total_amount, 0) }}</strong>
                </a>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Search & Filter Bar -->
    <div class="card" style="padding:12px 16px; border:1px solid #E2E8F0; background:#FFFFFF; border-radius:8px;">
        <form method="GET" action="{{ route('mobileshop.expenses.index') }}" id="expenseFilterForm">
            <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
                <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; flex:1;">
                    <!-- Period Filter Pills -->
                    <div style="display:inline-flex; border:1px solid #CBD5E1; border-radius:6px; overflow:hidden; background:#F8FAFC;">
                        <a href="{{ route('mobileshop.expenses.index', array_merge(request()->except(['period', 'from', 'to', 'page']), ['period' => 'this_month'])) }}"
                           style="padding:6px 12px; font-size:11.5px; font-weight:700; text-decoration:none; {{ $filterPeriod === 'this_month' ? 'background:#4F46E5; color:#fff;' : 'color:#475569;' }}">
                            This Month
                        </a>
                        <a href="{{ route('mobileshop.expenses.index', array_merge(request()->except(['period', 'from', 'to', 'page']), ['period' => 'today'])) }}"
                           style="padding:6px 12px; font-size:11.5px; font-weight:700; text-decoration:none; border-left:1px solid #CBD5E1; {{ $filterPeriod === 'today' ? 'background:#4F46E5; color:#fff;' : 'color:#475569;' }}">
                            Today
                        </a>
                        <a href="{{ route('mobileshop.expenses.index', array_merge(request()->except(['period', 'from', 'to', 'page']), ['period' => 'last_month'])) }}"
                           style="padding:6px 12px; font-size:11.5px; font-weight:700; text-decoration:none; border-left:1px solid #CBD5E1; {{ $filterPeriod === 'last_month' ? 'background:#4F46E5; color:#fff;' : 'color:#475569;' }}">
                            Last Month
                        </a>
                        <a href="{{ route('mobileshop.expenses.index', array_merge(request()->except(['period', 'from', 'to', 'page']), ['period' => 'all'])) }}"
                           style="padding:6px 12px; font-size:11.5px; font-weight:700; text-decoration:none; border-left:1px solid #CBD5E1; {{ $filterPeriod === 'all' ? 'background:#4F46E5; color:#fff;' : 'color:#475569;' }}">
                            All
                        </a>
                    </div>

                    <!-- Search Input -->
                    <div style="position:relative; min-width:180px; flex:1; max-width:320px;">
                        <input type="text" name="q" value="{{ $searchQuery }}" placeholder="Search" class="restock-input" style="padding-left:30px; height:34px; font-size:12.5px;">
                        <i data-lucide="search" style="position:absolute; left:9px; top:10px; width:14px; height:14px; color:#94A3B8;"></i>
                    </div>

                    <!-- Category Selector -->
                    <select name="category" class="restock-input" style="width:160px; height:34px; font-size:12px; font-weight:600;" onchange="document.getElementById('expenseFilterForm').submit()">
                        <option value="">All Categories</option>
                        @foreach($categories as $catKey => $catData)
                            <option value="{{ $catKey }}" {{ $selectedCat === $catKey ? 'selected' : '' }}>{{ $catData['label'] }}</option>
                        @endforeach
                    </select>

                    <!-- Payment Mode Selector -->
                    <select name="payment_mode" class="restock-input" style="width:140px; height:34px; font-size:12px; font-weight:600;" onchange="document.getElementById('expenseFilterForm').submit()">
                        <option value="">All Modes</option>
                        <option value="cash" {{ $selectedMode === 'cash' ? 'selected' : '' }}>💵 Cash</option>
                        <option value="upi" {{ $selectedMode === 'upi' ? 'selected' : '' }}>📱 UPI</option>
                        <option value="bank_transfer" {{ $selectedMode === 'bank_transfer' ? 'selected' : '' }}>🏦 Bank</option>
                        <option value="cheque" {{ $selectedMode === 'cheque' ? 'selected' : '' }}>📝 Cheque</option>
                    </select>
                </div>

                <div style="display:flex; align-items:center; gap:8px;">
                    <button type="submit" class="btn btn-primary btn-sm" style="font-weight:700; padding:6px 14px; height:34px; border-radius:6px;">
                        Filter
                    </button>
                    @if(request()->hasAny(['q', 'category', 'payment_mode', 'from', 'to']) || $filterPeriod !== 'this_month')
                        <a href="{{ route('mobileshop.expenses.index') }}" class="btn btn-outline btn-sm" style="font-weight:700; padding:6px 10px; height:34px; border-radius:6px; color:#64748B;">
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Expenses Table Card -->
    <div class="card" style="border:1px solid #E2E8F0; background:#FFFFFF; border-radius:8px; overflow:hidden;">
        <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 16px; border-bottom:1px solid #E2E8F0; background:#FAFAFA;">
            <div style="font-size:12px; font-weight:700; color:#1E293B; display:flex; align-items:center; gap:6px;">
                <i data-lucide="receipt" style="width:14px;height:14px; color:#4F46E5;"></i>
                Recorded Expenses &mdash; <span style="color:#4F46E5; font-weight:800;">{{ $expenses->total() }}</span> records
            </div>
            <div style="font-size:12px; font-weight:800; color:#0F172A;">
                Total: <span style="font-family:'JetBrains Mono', monospace; color:#4F46E5;">₹{{ number_format($filteredTotal, 2) }}</span>
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table class="expense-table">
                <thead>
                    <tr>
                        <th style="width:105px;">Date</th>
                        <th style="width:110px;">Expense #</th>
                        <th>Title / Description</th>
                        <th style="width:150px;">Category</th>
                        <th style="width:130px;">Paid To</th>
                        <th style="width:100px;">Mode</th>
                        <th style="width:115px; text-align:right;">Amount (₹)</th>
                        <th style="width:110px;">By</th>
                        <th style="width:60px; text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $exp)
                        @php
                            $catMeta = $categories[$exp->category] ?? ['label' => ucfirst(str_replace('_', ' ', $exp->category)), 'icon' => 'tag'];
                            $catClass = match($exp->category) {
                                'shop_rent' => 'rent',
                                'electricity_bills' => 'electricity',
                                'salary_wages' => 'salary',
                                'tea_refreshment' => 'tea',
                                'transport_freight' => 'transport',
                                'tools_equipment' => 'tools',
                                default => 'maintenance',
                            };
                            $modeClass = match($exp->payment_mode) {
                                'cash' => 'cash',
                                'upi' => 'upi',
                                'bank_transfer' => 'bank',
                                'cheque' => 'cheque',
                                default => 'cash',
                            };
                        @endphp
                        <tr>
                            <td style="font-weight:700; color:#475569; white-space:nowrap;">
                                {{ \Carbon\Carbon::parse($exp->expense_date)->format('d M Y') }}
                            </td>
                            <td>
                                <span style="font-family:'JetBrains Mono', monospace; font-size:11px; font-weight:700; color:#4F46E5;">
                                    {{ $exp->expense_number }}
                                </span>
                            </td>
                            <td>
                                <div style="font-weight:700; color:#0F172A;">{{ $exp->title }}</div>
                                @if(!empty($exp->notes))
                                    <div style="font-size:11px; color:#64748B; margin-top:2px;">{{ $exp->notes }}</div>
                                @endif
                                @if(!empty($exp->reference_no))
                                    <div style="font-size:10px; color:#6366F1; font-family:'JetBrains Mono', monospace; margin-top:2px;">Ref: {{ $exp->reference_no }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge-cat {{ $catClass }}">
                                    <i data-lucide="{{ $catMeta['icon'] }}" style="width:11px;height:11px;"></i>
                                    {{ $catMeta['label'] }}
                                </span>
                            </td>
                            <td style="color:#475569; font-weight:600;">
                                {{ $exp->paid_to ?: '—' }}
                            </td>
                            <td>
                                <span class="badge-mode {{ $modeClass }}">{{ $exp->payment_mode }}</span>
                            </td>
                            <td style="text-align:right;">
                                <strong style="font-family:'JetBrains Mono', monospace; font-size:13.5px; font-weight:900; color:#0F172A;">
                                    ₹{{ number_format($exp->amount, 2) }}
                                </strong>
                            </td>
                            <td style="font-size:11px; color:#64748B;">
                                {{ $exp->recorded_by_name ?: 'System' }}
                            </td>
                            <td style="text-align:center;">
                                @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin') || auth()->user()->can('read-admin-panel'))
                                    <form action="{{ route('mobileshop.expenses.destroy', $exp->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this expense record?');" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-ghost-delete" title="Delete Expense" style="width:26px; height:26px; display:inline-flex; align-items:center; justify-content:center; border:none; background:transparent; color:#94A3B8; cursor:pointer; border-radius:4px;">
                                            <i data-lucide="trash-2" style="width:13px;height:13px; color:#DC2626;"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align:center; padding:40px 20px; color:#64748B;">
                                <i data-lucide="inbox" style="width:36px;height:36px; margin:0 auto 10px; display:block; opacity:0.4; color:#94A3B8;"></i>
                                <div style="font-size:14px; font-weight:700; color:#334155;">No expense records found</div>
                                <p style="font-size:12px; color:#94A3B8; margin:4px 0 12px 0;">Track daily shop expenses like rent, tea, salaries, parts freight and power bills.</p>
                                <button type="button" onclick="openExpenseModal()" class="btn btn-primary btn-sm" style="font-weight:700; padding:6px 14px; border-radius:6px;">
                                    <i data-lucide="plus" style="width:13px;height:13px;"></i> Record Expense
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($expenses->hasPages())
            <div style="padding:12px 16px; border-top:1px solid #E2E8F0;">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>

</div>

<!-- ══════════════════════════════════════════════════════════ -->
<!-- RECORD EXPENSE MODAL -->
<!-- ══════════════════════════════════════════════════════════ -->
<div class="modal-backdrop" id="expenseModalBackdrop" onclick="if(event.target === this) closeExpenseModal()">
    <div class="modal-dialog">
        <form action="{{ route('mobileshop.expenses.store') }}" method="POST" id="recordExpenseForm">
            @csrf
            <div style="padding:14px 18px; border-bottom:1px solid #E2E8F0; display:flex; align-items:center; justify-content:space-between; background:#F8FAFC;">
                <div style="font-size:14px; font-weight:800; color:#0F172A; display:flex; align-items:center; gap:8px;">
                    <i data-lucide="plus-circle" style="width:16px;height:16px; color:#4F46E5;"></i> Record Shop Expense
                </div>
                <button type="button" onclick="closeExpenseModal()" style="background:none; border:none; color:#64748B; font-size:18px; font-weight:700; cursor:pointer; padding:0 4px;">✕</button>
            </div>

            <div style="padding:18px; display:flex; flex-direction:column; gap:12px;">
                <!-- Amount (Prominent) -->
                <div>
                    <label style="font-size:11px; font-weight:700; text-transform:uppercase; color:#475569; display:block; margin-bottom:4px;">
                        Expense Amount *
                    </label>
                    <div style="position:relative;">
                        <span style="position:absolute; left:12px; top:50%; transform:translateY(-50%); font-weight:900; color:#4F46E5; font-size:18px; font-family:'JetBrains Mono', monospace;">₹</span>
                        <input type="number" step="0.01" min="0.01" name="amount" id="modalExpenseAmount" required placeholder="Amount"
                               style="width:100%; height:44px; padding-left:30px; font-size:18px; font-weight:900; font-family:'JetBrains Mono', monospace; border:2px solid #CBD5E1; border-radius:8px; outline:none; box-sizing:border-box; color:#0F172A;"
                               onfocus="this.style.borderColor='#4F46E5'" onblur="this.style.borderColor='#CBD5E1'">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                    <!-- Category -->
                    <div>
                        <label style="font-size:11px; font-weight:700; text-transform:uppercase; color:#475569; display:block; margin-bottom:4px;">
                            Category *
                        </label>
                        <select name="category" required class="restock-input" style="height:38px; font-size:12.5px; font-weight:700;">
                            @foreach($categories as $catKey => $catData)
                                <option value="{{ $catKey }}">{{ $catData['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date -->
                    <div>
                        <label style="font-size:11px; font-weight:700; text-transform:uppercase; color:#475569; display:block; margin-bottom:4px;">
                            Expense Date *
                        </label>
                        <input type="date" name="expense_date" value="{{ now()->toDateString() }}" required class="restock-input" style="height:38px; font-size:12.5px;">
                    </div>
                </div>

                <!-- Title / Description -->
                <div>
                    <label style="font-size:11px; font-weight:700; text-transform:uppercase; color:#475569; display:block; margin-bottom:4px;">
                        Title / Reason *
                    </label>
                    <input type="text" name="title" required placeholder="Title" class="restock-input" style="height:38px; font-size:13px; font-weight:600;">
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                    <!-- Payment Mode -->
                    <div>
                        <label style="font-size:11px; font-weight:700; text-transform:uppercase; color:#475569; display:block; margin-bottom:4px;">
                            Payment Mode *
                        </label>
                        <select name="payment_mode" required class="restock-input" style="height:38px; font-size:12.5px; font-weight:700;">
                            <option value="cash">💵 Cash</option>
                            <option value="upi">📱 UPI / QR</option>
                            <option value="bank_transfer">🏦 Bank Transfer</option>
                            <option value="cheque">📝 Cheque</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <!-- Paid To (Payee) -->
                    <div>
                        <label style="font-size:11px; font-weight:700; text-transform:uppercase; color:#475569; display:block; margin-bottom:4px;">
                            Paid To (Vendor)
                        </label>
                        <input type="text" name="paid_to" placeholder="Vendor" class="restock-input" style="height:38px; font-size:12.5px;">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                    <!-- Reference / UTR -->
                    <div>
                        <label style="font-size:11px; font-weight:700; text-transform:uppercase; color:#475569; display:block; margin-bottom:4px;">
                            Reference / UTR
                        </label>
                        <input type="text" name="reference_no" placeholder="Reference" class="restock-input" style="height:38px; font-size:12px;">
                    </div>

                    <!-- Notes -->
                    <div>
                        <label style="font-size:11px; font-weight:700; text-transform:uppercase; color:#475569; display:block; margin-bottom:4px;">
                            Notes
                        </label>
                        <input type="text" name="notes" placeholder="Notes" class="restock-input" style="height:38px; font-size:12px;">
                    </div>
                </div>
            </div>

            <div style="padding:12px 18px; border-top:1px solid #E2E8F0; background:#F8FAFC; display:flex; justify-content:flex-end; gap:8px;">
                <button type="button" onclick="closeExpenseModal()" class="btn btn-outline btn-sm" style="font-weight:700; padding:8px 14px; border-radius:6px;">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary btn-sm" style="font-weight:800; padding:8px 20px; border-radius:6px; background:#4F46E5; color:#fff; border:none; box-shadow:0 2px 6px rgba(79, 70, 229, 0.3);">
                    Save Expense
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openExpenseModal() {
        const modal = document.getElementById('expenseModalBackdrop');
        if (modal) {
            modal.style.display = 'flex';
            setTimeout(() => {
                const amt = document.getElementById('modalExpenseAmount');
                if (amt) amt.focus();
            }, 100);
        }
    }

    function closeExpenseModal() {
        const modal = document.getElementById('expenseModalBackdrop');
        if (modal) modal.style.display = 'none';
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeExpenseModal();
    });
</script>
@endsection
