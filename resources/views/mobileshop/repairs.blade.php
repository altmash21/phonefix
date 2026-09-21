@extends('mobileshop.layout')

@section('title', 'Repair Desk — PhoneFix Azamgarh')
@section('page-title', 'Repair Service Desk & Job Sheets')

@section('page-actions')
    <button class="btn btn-primary btn-sm" onclick="toggleForm()">
        <i data-lucide="plus" style="width:14px;height:14px;"></i> Log New Repair
    </button>
@endsection

@push('styles')
<style>
    /* Repair Status Rail: horizontal smooth swipe on mobile */
    .repair-status-rail {
        display: flex;
        align-items: center;
        gap: 8px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        padding: 8px 12px;
        background: #F8FAFC;
        border-bottom: 1px solid #E2E8F0;
        scrollbar-width: none;
    }
    .repair-status-rail::-webkit-scrollbar {
        display: none;
    }
    .repair-status-rail .filter-pill {
        white-space: nowrap !important;
        flex-shrink: 0 !important;
    }

    /* Responsive Form Grid for Add Repair Ticket */
    .repair-form-grid-3 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin-bottom: 12px;
    }
    .repair-form-grid-2 {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
        margin-bottom: 12px;
    }

    /* Mobile Repairs Cards Container */
    .mobile-repairs-cards {
        display: none;
        flex-direction: column;
        gap: 0;
        background: #FFFFFF;
        border-top: none;
    }

    .repair-flat-row {
        padding: 12px 14px;
        border-bottom: 1px solid #F1F5F9;
        display: flex;
        flex-direction: column;
        gap: 6px;
        background: #FFFFFF;
        transition: background 0.15s;
    }
    .repair-flat-row:active {
        background: #F8FAFC;
    }
    .repair-flat-row:last-child {
        border-bottom: none;
    }

    /* Breakpoint Rules */
    @media (max-width: 767px) {
        .hide-on-mobile,
        .desktop-repair-table-wrap {
            display: none !important;
        }
        .mobile-repairs-cards {
            display: flex !important;
        }
        .repair-form-grid-3,
        .repair-form-grid-2 {
            grid-template-columns: 1fr !important;
            gap: 10px !important;
        }
        /* Mobile Bottom Sheet for updateRepairModal */
        #updateRepairModal {
            align-items: flex-end !important;
            padding: 0 !important;
        }
        #updateRepairModal .card {
            max-width: 100% !important;
            width: 100% !important;
            border-radius: 16px 16px 0 0 !important;
            max-height: 92vh !important;
            margin: 0 !important;
        }
        #updateRepairModal .modal-sticky-footer {
            position: sticky;
            bottom: 0;
            background: #FFFFFF;
            z-index: 10;
            padding: 12px 16px;
            border-top: 1px solid #E2E8F0;
        }
    }

    @media (min-width: 768px) {
        .desktop-repair-table-wrap {
            display: block !important;
        }
        .mobile-repairs-cards {
            display: none !important;
        }
    }
</style>
@endpush

@section('content')

<!-- Add Repair Form Card (collapsible) -->
<div class="card" style="margin-bottom:12px; display:none;" id="add-repair-card">
    <div class="card-header" style="background:var(--lama-purple-light);">
        <div class="card-title" style="color:var(--brand-700);">
            <i data-lucide="plus-circle" style="width:18px;height:18px;display:inline;vertical-align:-3px;margin-right:6px;"></i>
            Log New Device Repair Job Sheet
        </div>
        <button onclick="toggleForm()" class="btn btn-outline btn-sm">
            <i data-lucide="x" style="width:14px;height:14px;"></i> Close
        </button>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('mobileshop.repairs.store') }}">
            @csrf
            <div class="repair-form-grid-3">
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label required">Customer Name</label>
                    <input type="text" name="customer_name" class="form-control" placeholder="Customer Full Name" required>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label required">Phone Number</label>
                    <input type="text" name="customer_phone" inputmode="numeric" pattern="[0-9]*" class="form-control" placeholder="10-digit Mobile Number" required>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label required">Device Brand</label>
                    <input type="text" name="brand" class="form-control" placeholder="e.g. Samsung, Apple, Vivo" required>
                </div>
            </div>

            <div class="repair-form-grid-3">
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label required">Device Model</label>
                    <input type="text" name="model" class="form-control" placeholder="e.g. Galaxy S23 Ultra / iPhone 14" required>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">IMEI / Serial No.</label>
                    <input type="text" name="imei_serial" inputmode="numeric" class="form-control" placeholder="15-digit IMEI or Serial Number">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label required">Reported Fault / Problem</label>
                    <input type="text" name="reported_faults" class="form-control" placeholder="e.g. Display Broken, No Charging, Battery Draining" required>
                </div>
            </div>

            <div class="repair-form-grid-3">
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Estimated Repair Cost (₹)</label>
                    <input type="number" step="1" inputmode="numeric" name="estimated_cost" class="form-control" placeholder="0">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Advance Deposit Received (₹)</label>
                    <input type="number" step="1" inputmode="numeric" name="advance_paid" class="form-control" placeholder="0">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Physical Condition / Remarks</label>
                    <input type="text" name="physical_condition" class="form-control" placeholder="e.g. Minor scratches, back glass intact">
                </div>
            </div>

            <div class="repair-form-grid-2">
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Device Passcode / PIN / Pattern</label>
                    <input type="text" name="passcode" class="form-control" placeholder="e.g. 1234 or Pattern: L-Shape">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Technician Assignment</label>
                    <input type="text" name="technician" class="form-control" placeholder="e.g. Senior Tech / Bench 1">
                </div>
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end;padding-top:10px;border-top:1px solid var(--card-border);">
                <button type="button" onclick="toggleForm()" class="btn btn-outline">Discard</button>
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="save" style="width:14px;height:14px;"></i> Save Repair Job Ticket
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Repairs List Card -->
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">Repair Service Desk Queue</div>
        </div>
        <div class="search-bar">
            <i data-lucide="search" style="width:15px;height:15px;"></i>
            <input type="text" id="repairSearchInput" placeholder="Search ticket, customer, device, fault..." oninput="onRepairSearch(this.value)">
        </div>
    </div>

    <!-- Interactive Status Filter Bar (Scrollable Rail on Mobile) -->
    <div class="repair-status-rail">
        <span style="font-size:11px; font-weight:800; color:var(--text-secondary); text-transform:uppercase; margin-right:4px; flex-shrink:0;">Stage:</span>
        <button type="button" class="filter-pill active" id="pill-rep-all" onclick="applyRepairFilter('all', this)">
            <i data-lucide="layers" style="width:13px;height:13px;"></i> All <span class="pill-count">{{ array_sum($statusCounts ?? []) ?: count($tickets ?? []) }}</span>
        </button>
        <button type="button" class="filter-pill" id="pill-rep-received" onclick="applyRepairFilter('received', this)">
            <i data-lucide="inbox" style="width:13px;height:13px;"></i> Received <span class="pill-count">{{ $statusCounts['received'] ?? 0 }}</span>
        </button>
        <button type="button" class="filter-pill" id="pill-rep-in_diagnosis" onclick="applyRepairFilter('in_diagnosis', this)">
            <i data-lucide="microscope" style="width:13px;height:13px;"></i> In Diagnosis <span class="pill-count">{{ $statusCounts['in_diagnosis'] ?? 0 }}</span>
        </button>
        <button type="button" class="filter-pill filter-pill-danger" id="pill-rep-waiting_for_parts" onclick="applyRepairFilter('waiting_for_parts', this)">
            <i data-lucide="clock" style="width:13px;height:13px;"></i> Waiting Parts <span class="pill-count">{{ $statusCounts['waiting_for_parts'] ?? 0 }}</span>
        </button>
        <button type="button" class="filter-pill filter-pill-warning" id="pill-rep-waiting_approval" onclick="applyRepairFilter('waiting_approval', this)">
            <i data-lucide="help-circle" style="width:13px;height:13px;"></i> Waiting Approval <span class="pill-count">{{ $statusCounts['waiting_approval'] ?? 0 }}</span>
        </button>
        <button type="button" class="filter-pill filter-pill-warning" id="pill-rep-in_repair" onclick="applyRepairFilter('in_repair', this)">
            <i data-lucide="wrench" style="width:13px;height:13px;"></i> In Repair <span class="pill-count">{{ $statusCounts['in_repair'] ?? 0 }}</span>
        </button>
        <button type="button" class="filter-pill filter-pill-success" id="pill-rep-ready" onclick="applyRepairFilter('ready', this)">
            <i data-lucide="check-circle" style="width:13px;height:13px;"></i> Ready for Pickup <span class="pill-count">{{ $statusCounts['ready'] ?? 0 }}</span>
        </button>
        <button type="button" class="filter-pill" id="pill-rep-delivered" onclick="applyRepairFilter('delivered', this)">
            <i data-lucide="package-check" style="width:13px;height:13px;"></i> Delivered <span class="pill-count">{{ $statusCounts['delivered'] ?? 0 }}</span>
        </button>
        <button type="button" class="filter-pill" id="pill-rep-cancelled" onclick="applyRepairFilter('cancelled', this)">
            <i data-lucide="ban" style="width:13px;height:13px;"></i> Cancelled <span class="pill-count">{{ $statusCounts['cancelled'] ?? 0 }}</span>
        </button>
    </div>

    <div class="data-table-wrap desktop-repair-table-wrap">
        <table class="data-table" id="repairTable">
            <thead>
                <tr>
                    <th>Ticket #</th>
                    <th>Customer</th>
                    <th>Device</th>
                    <th>Reported Fault</th>
                    <th>Parts Consumed from Shop</th>
                    <th>Billing Breakdown</th>
                    <th>Status</th>
                    <th>Intake Date</th>
                    <th style="text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets ?? [] as $repair)
                <tr class="repair-row" data-status="{{ $repair->status ?? 'received' }}">
                    <td style="font-weight:800;color:var(--brand-700);white-space:nowrap;">
                        {{ $repair->ticket_number ?? ('#REP-' . str_pad($repair->id, 4, '0', STR_PAD_LEFT)) }}
                        @if($repair->decrypted_pin && $repair->decrypted_pin !== 'None')
                            <div style="font-size:11px;color:#64748B;font-family:monospace;" title="Device PIN/Passcode">
                                🔑 {{ $repair->decrypted_pin }}
                            </div>
                        @endif
                    </td>
                    <td>
                        <div style="font-weight:700;color:#0F172A;">{{ $repair->customer_name }}</div>
                        <div style="color:var(--text-secondary);font-size:12px;">{{ $repair->customer_phone }}</div>
                    </td>
                    <td>
                        <div style="font-weight:700;color:#1E293B;">{{ $repair->brand }} {{ $repair->model }}</div>
                        @if($repair->imei_serial)
                            <div style="font-size:11px;color:#64748B;font-family:monospace;">IMEI: {{ $repair->imei_serial }}</div>
                        @endif
                    </td>
                    <td>
                        <div style="color:var(--lama-rose-dark);font-weight:600;font-size:13px;">
                            {{ $repair->reported_faults }}
                        </div>
                        @if($repair->physical_condition)
                            <div style="font-size:11px;color:var(--text-secondary);">Cond: {{ $repair->physical_condition }}</div>
                        @endif
                    </td>
                    <td>
                        @if(isset($repair->used_parts) && $repair->used_parts->count() > 0)
                            <div style="display:flex;flex-direction:column;gap:4px;">
                                @foreach($repair->used_parts as $partItem)
                                    <span class="badge badge-purple" style="font-size:11px;padding:3px 8px;text-align:left;">
                                        📦 {{ $partItem->part_name }} ({{ $partItem->quantity }}x ₹{{ number_format($partItem->unit_price, 0) }})
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <span class="badge badge-gray" style="font-size:11px;color:#94A3B8;">No parts deducted</span>
                        @endif
                    </td>
                    <td style="font-size:12px;white-space:nowrap;">
                        <div>Labor: <strong>₹{{ number_format($repair->labor_charge ?? 0, 0) }}</strong></div>
                        <div>Parts: <strong>₹{{ number_format($repair->parts_cost ?? 0, 0) }}</strong></div>
                        <div style="font-weight:800;color:#0F172A;border-top:1px dashed #CBD5E1;padding-top:2px;margin-top:2px;">
                            Total: ₹{{ number_format($repair->total_amount ?? $repair->estimated_cost ?? 0, 0) }}
                        </div>
                        <div style="font-size:11px;color:var(--lama-green-dark);font-weight:600;">Paid: ₹{{ number_format($repair->advance_paid ?? 0, 0) }}</div>
                        @if(($repair->balance_due ?? 0) > 0)
                            <div style="font-size:11px;color:#DC2626;font-weight:700;">Due: ₹{{ number_format($repair->balance_due, 0) }}</div>
                        @else
                            <div style="font-size:11px;color:#16A34A;font-weight:700;">✓ Fully Paid</div>
                        @endif
                    </td>
                    <td>
                        @php
                            $st = $repair->status ?? 'received';
                            $badgeClass = 'badge-gray';
                            if ($st === 'received') $badgeClass = 'badge-purple';
                            elseif ($st === 'in_diagnosis') $badgeClass = 'badge-blue';
                            elseif ($st === 'waiting_for_parts') $badgeClass = 'badge-red';
                            elseif ($st === 'waiting_approval') $badgeClass = 'badge-yellow';
                            elseif ($st === 'in_repair') $badgeClass = 'badge-orange';
                            elseif ($st === 'ready') $badgeClass = 'badge-green';
                            elseif ($st === 'delivered') $badgeClass = 'badge-green';
                            elseif ($st === 'cancelled') $badgeClass = 'badge-gray';
                        @endphp
                        <span class="badge {{ $badgeClass }}" style="text-transform:uppercase;font-size:10px;letter-spacing:0.5px;">
                            {{ str_replace('_', ' ', $st) }}
                        </span>
                    </td>
                    <td style="color:var(--text-secondary);font-size:12px;white-space:nowrap;">
                        {{ \Carbon\Carbon::parse($repair->created_at)->format('d M Y') }}
                        <div style="font-size:10px;color:#94A3B8;">{{ \Carbon\Carbon::parse($repair->created_at)->format('h:i A') }}</div>
                    </td>
                    <td style="text-align:center;white-space:nowrap;">
                        <div style="display:flex;gap:5px;justify-content:center;align-items:center;">
                            @if($st === 'received')
                                <button type="button" class="btn btn-outline btn-sm" style="padding:3px 8px;font-size:11px;color:#2563EB;border-color:#BFDBFE;" onclick="quickTransition({{ $repair->id }}, 'in_diagnosis', '{{ $repair->ticket_number }}')" title="Start Diagnosis">
                                    <i data-lucide="microscope" style="width:11px;height:11px;"></i> Diagnose
                                </button>
                            @elseif($st === 'in_diagnosis')
                                <button type="button" class="btn btn-outline btn-sm" style="padding:3px 8px;font-size:11px;color:#EA580C;border-color:#FED7AA;" onclick="quickTransition({{ $repair->id }}, 'in_repair', '{{ $repair->ticket_number }}')" title="Move to Bench Repair">
                                    <i data-lucide="wrench" style="width:11px;height:11px;"></i> To Bench
                                </button>
                            @elseif($st === 'waiting_for_parts')
                                <button type="button" class="btn btn-outline btn-sm" style="padding:3px 8px;font-size:11px;color:#EA580C;border-color:#FED7AA;" onclick="quickTransition({{ $repair->id }}, 'in_repair', '{{ $repair->ticket_number }}')" title="Parts Received, Start Repair">
                                    <i data-lucide="play" style="width:11px;height:11px;"></i> Start Repair
                                </button>
                            @elseif($st === 'waiting_approval')
                                <button type="button" class="btn btn-outline btn-sm" style="padding:3px 8px;font-size:11px;color:#16A34A;border-color:#BBF7D0;" onclick="quickTransition({{ $repair->id }}, 'in_repair', '{{ $repair->ticket_number }}')" title="Customer Approved, Start Repair">
                                    <i data-lucide="check" style="width:11px;height:11px;"></i> Approved
                                </button>
                            @elseif($st === 'in_repair')
                                <button type="button" class="btn btn-outline btn-sm" style="padding:3px 8px;font-size:11px;color:#16A34A;border-color:#BBF7D0;" onclick="quickTransition({{ $repair->id }}, 'ready', '{{ $repair->ticket_number }}')" title="Mark Ready for Pickup">
                                    <i data-lucide="check-circle" style="width:11px;height:11px;"></i> Ready
                                </button>
                            @elseif($st === 'ready')
                                <button type="button" class="btn btn-sm" style="padding:3px 8px;font-size:11px;background:#16A34A;color:#fff;border:none;" onclick="openUpdateModal({{ json_encode($repair) }}, 'delivered')" title="Deliver and Settle Balance">
                                    <i data-lucide="handshake" style="width:11px;height:11px;"></i> Deliver
                                </button>
                            @endif
                            <button class="btn btn-primary btn-sm" style="padding:4px 9px;font-size:11px;"
                                onclick="openUpdateModal({{ json_encode($repair) }})">
                                <i data-lucide="edit-3" style="width:11px;height:11px;"></i> Update
                            </button>
                            <a href="{{ route('public.track_repair', ['ticket_number' => $repair->ticket_number]) }}" target="_blank" class="btn-icon" title="Public Tracking View">
                                <i data-lucide="external-link" style="width:13px;height:13px;"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align:center; padding:56px 20px;">
                        <div style="max-width:400px; margin:0 auto; display:flex; flex-direction:column; align-items:center; gap:12px;">
                            <div style="width:56px; height:56px; border-radius:50%; background:var(--lama-purple-light); display:flex; align-items:center; justify-content:center; color:var(--brand-700);">
                                <i data-lucide="wrench" style="width:28px;height:28px;"></i>
                            </div>
                            <div style="font-weight:800; font-size:16px; color:#0F172A;">No Repair Job Sheets in Queue</div>
                            <div style="font-size:13px; color:var(--text-secondary); line-height:1.5;">Intake customer devices for screen, battery, or motherboard repairs, record passcodes, and track SLA status.</div>
                            <button onclick="toggleForm()" class="btn btn-primary" style="margin-top:6px;">
                                <i data-lucide="plus" style="width:15px;height:15px;"></i> Create First Repair Ticket
                            </button>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Repairs Cards List (Shown on Mobile < 768px) -->
    <div id="repairsMobileCards" class="mobile-repairs-cards">
        @forelse($tickets ?? [] as $repair)
            @php
                $st = $repair->status ?? 'received';
                $badgeBg = '#F1F5F9';
                $badgeColor = '#475569';
                if ($st === 'received') { $badgeBg = '#EDE9FE'; $badgeColor = '#6D28D9'; }
                elseif ($st === 'in_diagnosis') { $badgeBg = '#DBEAFE'; $badgeColor = '#1D4ED8'; }
                elseif ($st === 'waiting_for_parts') { $badgeBg = '#FEE2E2'; $badgeColor = '#B91C1C'; }
                elseif ($st === 'waiting_approval') { $badgeBg = '#FEF3C7'; $badgeColor = '#92400E'; }
                elseif ($st === 'in_repair') { $badgeBg = '#FFEDD5'; $badgeColor = '#C2410C'; }
                elseif ($st === 'ready') { $badgeBg = '#DCFCE7'; $badgeColor = '#15803D'; }
                elseif ($st === 'delivered') { $badgeBg = '#DCFCE7'; $badgeColor = '#15803D'; }
                elseif ($st === 'cancelled') { $badgeBg = '#F1F5F9'; $badgeColor = '#64748B'; }
                
                $balanceDue = (float)($repair->balance_due ?? 0);
                $totalAmount = (float)($repair->total_amount ?? $repair->estimated_cost ?? 0);
            @endphp
            <div class="repair-flat-row" data-status="{{ $repair->status ?? 'received' }}" id="repairCard_{{ $repair->id }}">
                <div class="row-line1" style="display:flex; justify-content:space-between; align-items:center;">
                    <span class="inv-num" style="font-weight:800; color:var(--brand-700);">
                        {{ $repair->ticket_number ?? ('#REP-' . str_pad($repair->id, 4, '0', STR_PAD_LEFT)) }}
                    </span>
                    <span class="pay-badge" style="background:{{ $badgeBg }}; color:{{ $badgeColor }}; font-weight:700; padding:2px 6px; border-radius:4px; font-size:10px; text-transform:uppercase;">
                        {{ str_replace('_', ' ', $st) }}
                    </span>
                    @if($balanceDue > 0)
                        <span class="pay-badge" style="background:#FEF2F2; color:#DC2626; font-weight:700; padding:2px 6px; border-radius:4px; font-size:10px;">
                            Due: ₹{{ number_format($balanceDue, 0) }}
                        </span>
                    @else
                        <span class="pay-badge" style="background:#DCFCE7; color:#16A34A; font-weight:700; padding:2px 6px; border-radius:4px; font-size:10px;">
                            ✓ Paid
                        </span>
                    @endif
                    <div class="row-amount" style="font-weight:800; color:#0F172A;">₹{{ number_format($totalAmount, 0) }}</div>
                </div>
                <div class="row-line2" style="display:flex; justify-content:space-between; align-items:center;">
                    <div class="cust-name" style="font-weight:700; color:#0F172A; font-size:13px;">
                        {{ $repair->customer_name }}
                        <span style="font-weight:600; color:#1E293B; font-size:12px;">• {{ $repair->brand }} {{ $repair->model }}</span>
                    </div>
                    <a href="tel:{{ $repair->customer_phone }}" class="cust-phone" style="color:var(--brand-700); text-decoration:none; display:inline-flex; align-items:center; gap:4px; font-weight:700; font-size:12px;">
                        <i data-lucide="phone" style="width:12px;height:12px;"></i> {{ $repair->customer_phone }}
                    </a>
                </div>
                <div class="row-line3" style="display:flex; justify-content:space-between; align-items:center;">
                    <div class="items-summary" style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                        <span style="color:#C2410C; font-weight:600; font-size:12px;">⚠ {{ $repair->reported_faults }}</span>
                        @if($repair->decrypted_pin && $repair->decrypted_pin !== 'None')
                            <span style="background:#F1F5F9; border-radius:4px; padding:1px 5px; font-size:10px; font-family:monospace; color:#475569;">
                                🔑 {{ $repair->decrypted_pin }}
                            </span>
                        @endif
                    </div>
                    <div class="row-actions" style="display:flex; gap:5px; align-items:center;">
                        @if($st === 'received')
                            <button type="button" class="btn btn-outline btn-sm" style="padding:3px 7px;font-size:10.5px;color:#2563EB;border-color:#BFDBFE;" onclick="quickTransition({{ $repair->id }}, 'in_diagnosis', '{{ $repair->ticket_number }}')">Diagnose</button>
                        @elseif($st === 'in_diagnosis')
                            <button type="button" class="btn btn-outline btn-sm" style="padding:3px 7px;font-size:10.5px;color:#EA580C;border-color:#FED7AA;" onclick="quickTransition({{ $repair->id }}, 'in_repair', '{{ $repair->ticket_number }}')">To Bench</button>
                        @elseif($st === 'waiting_for_parts')
                            <button type="button" class="btn btn-outline btn-sm" style="padding:3px 7px;font-size:10.5px;color:#EA580C;border-color:#FED7AA;" onclick="quickTransition({{ $repair->id }}, 'in_repair', '{{ $repair->ticket_number }}')">Repair</button>
                        @elseif($st === 'waiting_approval')
                            <button type="button" class="btn btn-outline btn-sm" style="padding:3px 7px;font-size:10.5px;color:#16A34A;border-color:#BBF7D0;" onclick="quickTransition({{ $repair->id }}, 'in_repair', '{{ $repair->ticket_number }}')">Approved</button>
                        @elseif($st === 'in_repair')
                            <button type="button" class="btn btn-outline btn-sm" style="padding:3px 7px;font-size:10.5px;color:#16A34A;border-color:#BBF7D0;" onclick="quickTransition({{ $repair->id }}, 'ready', '{{ $repair->ticket_number }}')">Ready</button>
                        @elseif($st === 'ready')
                            <button type="button" class="btn btn-sm" style="padding:3px 7px;font-size:10.5px;background:#16A34A;color:#fff;border:none;" onclick="openUpdateModal({{ json_encode($repair) }}, 'delivered')">Deliver</button>
                        @endif
                        <button type="button" class="btn btn-primary btn-sm" style="padding:4px 8px; font-size:11px; height:28px;"
                            onclick="openUpdateModal({{ json_encode($repair) }})">
                            <i data-lucide="edit-3" style="width:11px;height:11px;"></i>
                        </button>
                        <a href="{{ route('public.track_repair', ['ticket_number' => $repair->ticket_number]) }}" target="_blank" class="compact-action-btn" title="Public Tracking View" style="padding:4px 6px; border:1px solid #CBD5E1; border-radius:6px; color:#475569; display:inline-flex; align-items:center; justify-content:center; text-decoration:none;">
                            <i data-lucide="external-link" style="width:13px;height:13px;"></i>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div style="text-align:center; padding:32px 16px; color:#94A3B8;">
                <i data-lucide="inbox" style="width:32px;height:32px; margin-bottom:8px;"></i>
                <div style="font-weight:700; font-size:13px; color:#475569;">No repair tickets found</div>
            </div>
        @endforelse
        <div id="repairMobileEmptyFilterRow" style="display:none; text-align:center; padding:28px 16px; color:#64748B;">
            <div style="font-weight:700; color:#1E293B; font-size:13px; margin-bottom:4px;">No repairs match this filter</div>
            <button type="button" onclick="applyRepairFilter('all')" class="filter-pill" style="cursor:pointer; background:var(--brand-700); color:#fff; border:none; padding:5px 14px; border-radius:6px; font-weight:700; font-size:11.5px; margin-top:8px;">
                Show All Repairs
            </button>
        </div>
    </div>
    <div id="repairsPagination"></div>
</div>

<!-- Interactive Update Repair & Consume Parts Modal -->
<div id="updateRepairModal" style="display:none; position: fixed; inset: 0; z-index: 1200; background: rgba(15,23,42,0.55); backdrop-filter: blur(4px); align-items:center; justify-content:center; padding: 16px;">
    <div class="card" style="max-width: 620px; width: 100%; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); max-height: 90vh; overflow-y: auto;">
        <div class="card-header" style="background:var(--brand-700); color:#fff;">
            <div>
                <div class="card-title" style="color:#fff;" id="modalTicketTitle">Update Repair Job Sheet</div>
                <div class="card-subtitle" style="color:rgba(255,255,255,0.8);" id="modalCustomerDevice">Customer & Device Info</div>
            </div>
            <button onclick="closeUpdateModal()" style="background:transparent; border:none; color:#fff; font-size:18px; cursor:pointer; padding:4px 8px;">✕</button>
        </div>
        <div class="card-body">
            <form id="updateRepairForm" method="POST" action="">
                @csrf
                <!-- Status Selection -->
                <div class="form-group" style="margin-bottom:16px;">
                    <label class="form-label required" style="font-weight:700;">Repair Stage / Status</label>
                    <select name="status" id="modalStatusSelect" class="form-control" style="font-weight:600;" required onchange="calculateModalTotals()">
                        <option value="received">1. Received (Intake / Awaiting Inspection)</option>
                        <option value="in_diagnosis">2. In Diagnosis (Hardware / Circuit Testing)</option>
                        <option value="waiting_for_parts">3. Waiting for Spare Parts</option>
                        <option value="waiting_approval">4. Waiting for Customer Approval</option>
                        <option value="in_repair">5. In Repair (On Service Bench)</option>
                        <option value="ready">6. Ready for Pickup (Repair Completed & Tested)</option>
                        <option value="delivered">7. Delivered to Customer (Closed)</option>
                        <option value="cancelled">8. Cancelled / Returned Unfixed</option>
                    </select>
                </div>

                <!-- Part Consumption Section -->
                <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:14px; margin-bottom:16px;">
                    <div style="font-weight:700; color:#0F172A; margin-bottom:8px; display:flex; align-items:center; gap:6px;">
                        <i data-lucide="package-minus" style="width:16px;height:16px;color:var(--brand-600);"></i>
                        Use Spare Part from Shop Inventory (Auto-Deducts Stock)
                    </div>
                    <div class="repair-part-picker" style="position:relative; margin-bottom:12px;">
                        <input type="hidden" name="consumed_part_id" id="modalPartId" value="">
                        <div style="position:relative;">
                            <input type="text" id="modalPartSearchInput" class="form-control" placeholder="🔍 Search spare part..." autocomplete="off" style="width:100%; padding-right:34px; font-size:13px;">
                            <button type="button" id="modalPartClearBtn" onclick="clearSelectedRepairPart()" style="display:none; position:absolute; right:8px; top:50%; transform:translateY(-50%); background:none; border:none; color:#64748B; cursor:pointer; font-size:16px; padding:2px 6px;">✕</button>
                        </div>
                        <div id="modalSelectedPartCard" style="display:none; margin-top:8px; padding:10px 12px; background:#EFF6FF; border:1px solid #BFDBFE; border-radius:8px; align-items:center; justify-content:space-between;">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <span class="badge badge-blue" id="selectedPartCatBadge" style="font-size:10px; font-weight:700;">PART</span>
                                <div>
                                    <div id="selectedPartTitle" style="font-weight:700; color:#1E3A8A; font-size:12.5px;"></div>
                                    <div id="selectedPartSub" style="font-size:11px; color:#3B82F6;"></div>
                                </div>
                            </div>
                            <div style="text-align:right;">
                                <div id="selectedPartPrice" style="font-weight:800; font-size:13px; color:#1D4ED8;">₹0</div>
                                <div id="selectedPartStock" style="font-size:10px; color:#64748B;"></div>
                            </div>
                        </div>
                        <div id="modalPartSearchResults" style="display:none; position:absolute; left:0; right:0; top:100%; z-index:1050; background:#fff; border:1px solid #CBD5E1; border-radius:8px; box-shadow:0 10px 25px rgba(0,0,0,0.15); max-height:240px; overflow-y:auto; margin-top:4px;">
                        </div>
                    </div>

                    <div class="repair-form-grid-2" style="margin-bottom:0;">
                        <div>
                            <label class="form-label" style="font-size:11px;">Part Quantity</label>
                            <input type="number" name="part_qty" id="modalPartQty" value="1" min="1" max="10" inputmode="numeric" class="form-control" oninput="calculateModalTotals()">
                        </div>
                        <div>
                            <label class="form-label" style="font-size:11px;">Selected Part Cost (₹)</label>
                            <input type="text" id="modalPartCostDisplay" value="₹0" readonly class="form-control" style="background:#F1F5F9; font-weight:700; color:var(--brand-700);">
                        </div>
                    </div>
                </div>

                <!-- Financial & Labor Charges -->
                <div class="repair-form-grid-2" style="margin-bottom:16px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Labor / Service Fee (₹)</label>
                        <input type="number" step="1" inputmode="numeric" name="labor_charge" id="modalLaborInput" class="form-control" placeholder="0" oninput="calculateModalTotals()">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Collect Additional Payment (₹)</label>
                        <input type="number" step="1" inputmode="numeric" name="additional_payment" id="modalPaymentInput" class="form-control" placeholder="0" oninput="calculateModalTotals()">
                    </div>
                </div>

                <!-- Financial Calculation Summary Card -->
                <div style="background:#F1F5F9; border:1px solid #CBD5E1; border-radius:8px; padding:12px 16px; margin-bottom:18px;">
                    <div style="font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;">Job Sheet Billing Summary:</div>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:8px; font-size:13px;">
                        <div>Existing Parts Cost: <strong id="lblPrevParts">₹0</strong></div>
                        <div>+ New Part Added: <strong id="lblNewPart">₹0</strong></div>
                        <div>+ Labor Charge: <strong id="lblLabor">₹0</strong></div>
                        <div>- Total Advance Paid: <strong id="lblAdvance" style="color:var(--lama-green-dark);">₹0</strong></div>
                        <div style="grid-column: span 2; border-top: 1px solid #94A3B8; padding-top: 6px; display:flex; justify-content:space-between; font-weight:800; font-size:14px;">
                            <span>Grand Total: <span id="lblGrandTotal" style="color:var(--brand-700);">₹0</span></span>
                            <span>Balance Due: <span id="lblBalanceDue" style="color:#DC2626;">₹0</span></span>
                        </div>
                    </div>
                    <div style="margin-top:10px; padding-top:8px; border-top:1px dashed #CBD5E1; display:flex; justify-content:space-between; align-items:center;">
                        <button type="button" id="btnQuickPayBalance" onclick="quickFillBalancePayment()" class="btn btn-sm btn-outline" style="display:none; font-size:11px; padding:3px 10px; color:#16A34A; border-color:#86EFAC; background:#F0FDF4; font-weight:700;">
                            ⚡ Settle Remaining Balance (Collect ₹<span id="quickPayAmt">0</span>)
                        </button>
                        <span id="cancelledNotice" style="display:none; font-size:11px; font-weight:700; color:#DC2626;">
                            🚫 Job Cancelled: Balance due is waived.
                        </span>
                    </div>
                </div>

                <div class="modal-sticky-footer" style="display:flex; justify-content:flex-end; gap:10px; padding-top:12px; border-top:1px solid var(--card-border);">
                    <button type="button" onclick="closeUpdateModal()" class="btn btn-outline">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i data-lucide="save" style="width:14px;height:14px;"></i> Save & Update Ticket
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Mobile Floating Action Button -->
<div class="mobile-fab-container">
    <button type="button" class="btn-app-fab" onclick="toggleForm()" title="Log New Repair">
        <i data-lucide="plus"></i>
        <span>Log Repair</span>
    </button>
</div>

@endsection

@push('scripts')
<script>
let currentTicket = null;

function toggleForm() {
    const card = document.getElementById('add-repair-card');
    card.style.display = card.style.display === 'none' || !card.style.display ? 'block' : 'none';
    if (card.style.display === 'block') card.scrollIntoView({ behavior: 'smooth', block: 'start' });
    if (window.refreshIcons) window.refreshIcons();
    else if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons();
}

function openUpdateModal(repair, targetStatus = null) {
    currentTicket = repair;
    const modal = document.getElementById('updateRepairModal');
    
    // Set form action URL
    const updateUrl = "{{ route('mobileshop.repairs.update', ['id' => ':id']) }}".replace(':id', repair.id);
    document.getElementById('updateRepairForm').action = updateUrl;

    // Set title and subtitle
    document.getElementById('modalTicketTitle').textContent = `Service Desk — ${repair.ticket_number || ('#REP-' + repair.id)}`;
    document.getElementById('modalCustomerDevice').textContent = `${repair.customer_name} (${repair.customer_phone}) • ${repair.brand} ${repair.model}`;

    // Prefill fields
    const currentStatus = targetStatus || repair.status || 'received';
    document.getElementById('modalStatusSelect').value = currentStatus;

    // Preserve labor charge and ensure estimate balance is not wiped out
    let labor = parseFloat(repair.labor_charge || 0);
    if (labor <= 0 && parseFloat(repair.estimated_cost || 0) > 0) {
        const existingParts = parseFloat(repair.parts_cost || 0);
        labor = Math.max(0, parseFloat(repair.estimated_cost) - existingParts);
    }
    document.getElementById('modalLaborInput').value = labor > 0 ? Math.round(labor) : (repair.labor_charge !== null && repair.labor_charge !== undefined && repair.labor_charge > 0 ? Math.round(repair.labor_charge) : '');

    document.getElementById('modalPartQty').value = 1;
    document.getElementById('modalPaymentInput').value = '';
    clearSelectedRepairPart();

    calculateModalTotals();

    // If opening directly to deliver stage, auto-suggest remaining balance payment
    if (targetStatus === 'delivered') {
        quickFillBalancePayment();
    }

    modal.style.display = 'flex';
    if (window.refreshIcons) window.refreshIcons();
    else if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons();
}

function closeUpdateModal() {
    document.getElementById('updateRepairModal').style.display = 'none';
}

let selectedRepairPart = null;
let repairPartsSearchDebounce = null;

function clearSelectedRepairPart() {
    selectedRepairPart = null;
    var idInput = document.getElementById('modalPartId');
    if (idInput) idInput.value = '';
    var searchInput = document.getElementById('modalPartSearchInput');
    if (searchInput) searchInput.value = '';
    var clearBtn = document.getElementById('modalPartClearBtn');
    if (clearBtn) clearBtn.style.display = 'none';
    var card = document.getElementById('modalSelectedPartCard');
    if (card) card.style.display = 'none';
    calculateModalTotals();
}

function selectRepairPart(part) {
    selectedRepairPart = part;
    document.getElementById('modalPartId').value = part.id;
    document.getElementById('modalPartSearchInput').value = part.name;
    document.getElementById('modalPartSearchResults').style.display = 'none';
    
    var clearBtn = document.getElementById('modalPartClearBtn');
    if (clearBtn) clearBtn.style.display = 'block';

    var card = document.getElementById('modalSelectedPartCard');
    if (card) {
        document.getElementById('selectedPartTitle').textContent = part.name;
        document.getElementById('selectedPartSub').textContent = [part.brand, part.compatible_model].filter(Boolean).join(' • ') || 'Universal';
        document.getElementById('selectedPartPrice').textContent = '₹' + Math.round(parseFloat(part.selling_price || 0));
        document.getElementById('selectedPartStock').textContent = 'In Stock: ' + part.stock_qty;
        document.getElementById('selectedPartCatBadge').textContent = (part.category || 'PART').toUpperCase();
        card.style.display = 'flex';
    }

    calculateModalTotals();
}

function performRepairPartsSearch(query) {
    var resultsContainer = document.getElementById('modalPartSearchResults');
    if (!query || query.length < 1) {
        resultsContainer.style.display = 'none';
        return;
    }

    resultsContainer.style.display = 'block';
    resultsContainer.innerHTML = '<div style="padding:12px; text-align:center; color:#64748B; font-size:12px;"><i data-lucide="loader-2" style="width:14px;height:14px;display:inline-block;animation:spin 1s linear infinite;"></i> Searching parts inventory...</div>';
    if (window.refreshIcons) window.refreshIcons();

    fetch("{{ route('mobileshop.parts.search') }}?q=" + encodeURIComponent(query))
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (!data.success || !data.parts || data.parts.length === 0) {
            resultsContainer.innerHTML = '<div style="padding:12px; text-align:center; color:#94A3B8; font-size:12px;">No spare parts found matching "' + escapeHtml(query) + '".</div>';
            return;
        }

        var html = '';
        data.parts.forEach(function(p) {
            var outOfStock = p.stock_qty <= 0;
            var qualityBadge = p.display_type ? '<span class="badge ' + (p.display_type === 'OG' ? 'badge-purple' : 'badge-blue') + '" style="font-size:9.5px; padding:1px 5px; margin-left:4px;">' + p.display_type + '</span>' : '';
            var brandModel = [p.brand, p.compatible_model].filter(Boolean).join(' ');
            var jsonStr = JSON.stringify(p).replace(/"/g, '&quot;');

            html += '<div class="part-search-item" style="padding:9px 12px; border-bottom:1px solid #F1F5F9; cursor:' + (outOfStock ? 'not-allowed' : 'pointer') + '; opacity:' + (outOfStock ? '0.6' : '1') + '; display:flex; justify-content:space-between; align-items:center;" ' +
                'onmouseover="if(!' + outOfStock + ') this.style.background=\'#F8FAFC\'" onmouseout="this.style.background=\'#fff\'" ' +
                (outOfStock ? '' : 'onclick=\'selectRepairPart(' + jsonStr + ')\'') + '>' +
                '<div>' +
                    '<div style="font-weight:700; color:#0F172A; font-size:12.5px;">' + p.name + qualityBadge + '</div>' +
                    '<div style="font-size:11px; color:#64748B; margin-top:2px;">' +
                        '<span style="text-transform:uppercase; font-size:9.5px; font-weight:700; color:#6366F1;">[' + (p.category || 'PART') + ']</span> ' +
                        (brandModel ? '• Fits: <strong>' + brandModel + '</strong>' : '') +
                    '</div>' +
                '</div>' +
                '<div style="text-align:right;">' +
                    '<div style="font-weight:800; font-size:12.5px; color:#0F172A;">₹' + Math.round(parseFloat(p.selling_price || 0)) + '</div>' +
                    '<div style="font-size:10px; color:' + (outOfStock ? '#DC2626' : '#16A34A') + '; font-weight:600;">' + (outOfStock ? 'Out of Stock' : 'Stock: ' + p.stock_qty) + '</div>' +
                '</div>' +
            '</div>';
        });
        resultsContainer.innerHTML = html;
        if (window.refreshIcons) window.refreshIcons();
    })
    .catch(function(err) {
        console.error(err);
        resultsContainer.innerHTML = '<div style="padding:12px; text-align:center; color:#EF4444; font-size:12px;">Failed to load inventory.</div>';
    });
}

function calculateModalTotals() {
    if (!currentTicket) return;
    const prevParts = parseFloat(currentTicket.parts_cost || 0);
    const prevAdvance = parseFloat(currentTicket.advance_paid || 0);
    const unitPrice = parseFloat(selectedRepairPart?.selling_price || 0);
    const qty = parseInt(document.getElementById('modalPartQty').value || 1);
    const newPartCost = unitPrice * qty;
    document.getElementById('modalPartCostDisplay').value = `₹${Math.round(newPartCost)}`;

    const labor = parseFloat(document.getElementById('modalLaborInput').value || 0);
    const additionalPay = parseFloat(document.getElementById('modalPaymentInput').value || 0);
    const currentStatus = document.getElementById('modalStatusSelect').value;

    const totalParts = prevParts + newPartCost;
    let grandTotal = labor + totalParts;
    if (grandTotal === 0 && parseFloat(currentTicket.estimated_cost || 0) > 0) {
        grandTotal = parseFloat(currentTicket.estimated_cost);
    }

    const totalPaid = prevAdvance + additionalPay;
    const isCancelled = (currentStatus === 'cancelled');

    let balanceDue = 0;
    const cancelledNotice = document.getElementById('cancelledNotice');
    const btnQuickPay = document.getElementById('btnQuickPayBalance');
    const quickPayAmt = document.getElementById('quickPayAmt');

    if (isCancelled) {
        if (labor > 0 || totalParts > 0) {
            balanceDue = Math.max(0, grandTotal - totalPaid);
        } else {
            grandTotal = totalPaid;
            balanceDue = 0;
        }
        if (cancelledNotice) cancelledNotice.style.display = 'inline';
    } else {
        balanceDue = Math.max(0, grandTotal - totalPaid);
        if (cancelledNotice) cancelledNotice.style.display = 'none';
    }

    if (btnQuickPay && quickPayAmt) {
        const remainingDue = Math.max(0, grandTotal - prevAdvance);
        if (remainingDue > 0 && !isCancelled) {
            quickPayAmt.textContent = Math.round(remainingDue);
            btnQuickPay.style.display = 'inline-flex';
        } else {
            btnQuickPay.style.display = 'none';
        }
    }

    document.getElementById('lblPrevParts').textContent = `₹${Math.round(prevParts)}`;
    document.getElementById('lblNewPart').textContent = `₹${Math.round(newPartCost)}`;
    document.getElementById('lblLabor').textContent = `₹${Math.round(labor)}`;
    document.getElementById('lblAdvance').textContent = `₹${Math.round(totalPaid)}`;
    document.getElementById('lblGrandTotal').textContent = `₹${Math.round(grandTotal)}`;
    document.getElementById('lblBalanceDue').textContent = `₹${Math.round(balanceDue)}`;
}

function quickFillBalancePayment() {
    if (!currentTicket) return;
    const prevParts = parseFloat(currentTicket.parts_cost || 0);
    const prevAdvance = parseFloat(currentTicket.advance_paid || 0);
    const unitPrice = parseFloat(selectedRepairPart?.selling_price || 0);
    const qty = parseInt(document.getElementById('modalPartQty').value || 1);
    const newPartCost = unitPrice * qty;
    const labor = parseFloat(document.getElementById('modalLaborInput').value || 0);
    const totalParts = prevParts + newPartCost;
    let grandTotal = labor + totalParts;
    if (grandTotal === 0 && parseFloat(currentTicket.estimated_cost || 0) > 0) {
        grandTotal = parseFloat(currentTicket.estimated_cost);
    }
    const remainingDue = Math.max(0, grandTotal - prevAdvance);
    document.getElementById('modalPaymentInput').value = remainingDue > 0 ? Math.round(remainingDue) : '';
    calculateModalTotals();
}

function quickTransition(ticketId, newStatus, ticketNumber) {
    const statusLabels = {
        'received': 'Received',
        'in_diagnosis': 'In Diagnosis',
        'waiting_for_parts': 'Waiting for Parts',
        'waiting_approval': 'Waiting Approval',
        'in_repair': 'In Repair',
        'ready': 'Ready for Pickup',
        'delivered': 'Delivered',
        'cancelled': 'Cancelled'
    };
    const targetName = statusLabels[newStatus] || newStatus;
    if (!confirm(`Move ticket ${ticketNumber || ('#' + ticketId)} to "${targetName}"?`)) {
        return;
    }

    const updateUrl = "{{ route('mobileshop.repairs.update', ['id' => ':id']) }}".replace(':id', ticketId);

    const formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('status', newStatus);

    fetch(updateUrl, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Failed to update repair status.');
        }
    })
    .catch(err => {
        console.error(err);
        // Fallback: standard form submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = updateUrl;
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        form.appendChild(csrfInput);
        const statusInput = document.createElement('input');
        statusInput.type = 'hidden';
        statusInput.name = 'status';
        statusInput.value = newStatus;
        form.appendChild(statusInput);
        document.body.appendChild(form);
        form.submit();
    });
}

let currentRepairStatus = 'all';
let currentRepairSearch = '';

function applyRepairFilter(status, btnElement) {
    currentRepairStatus = status;

    // Update active pill
    document.querySelectorAll('.repair-status-rail .filter-pill').forEach(p => p.classList.remove('active'));
    if (btnElement) {
        btnElement.classList.add('active');
    } else {
        const map = { 
            'all': 'pill-rep-all', 
            'received': 'pill-rep-received', 
            'in_diagnosis': 'pill-rep-in_diagnosis',
            'waiting_for_parts': 'pill-rep-waiting_for_parts', 
            'waiting_approval': 'pill-rep-waiting_approval',
            'in_repair': 'pill-rep-in_repair', 
            'ready': 'pill-rep-ready', 
            'delivered': 'pill-rep-delivered',
            'cancelled': 'pill-rep-cancelled'
        };
        if (map[status]) {
            const el = document.getElementById(map[status]);
            if (el) el.classList.add('active');
        }
    }

    renderFilteredRepairs();
}

function onRepairSearch(term) {
    currentRepairSearch = term.toLowerCase().trim();
    renderFilteredRepairs();
}

function renderFilteredRepairs() {
    // Desktop rows
    const rows = document.querySelectorAll('#repairTable tbody tr.repair-row');
    let visibleDesktop = 0;
    rows.forEach(row => {
        const status = row.dataset.status || 'received';
        const rowText = row.textContent.toLowerCase();

        let matchStatus = true;
        if (currentRepairStatus !== 'all') {
            matchStatus = (status === currentRepairStatus);
        }

        let matchSearch = true;
        if (currentRepairSearch) {
            matchSearch = rowText.includes(currentRepairSearch);
        }

        const isVisible = matchStatus && matchSearch;
        row.dataset.mobiHidden = isVisible ? '0' : '1';
        row.style.display = isVisible ? '' : 'none';
        if (isVisible) visibleDesktop++;
    });

    // Mobile cards
    const cards = document.querySelectorAll('#repairsMobileCards .repair-flat-row');
    let visibleMobile = 0;
    cards.forEach(card => {
        const status = card.dataset.status || 'received';
        const cardText = card.textContent.toLowerCase();

        let matchStatus = true;
        if (currentRepairStatus !== 'all') {
            matchStatus = (status === currentRepairStatus);
        }

        let matchSearch = true;
        if (currentRepairSearch) {
            matchSearch = cardText.includes(currentRepairSearch);
        }

        const isVisible = matchStatus && matchSearch;
        card.dataset.mobiHidden = isVisible ? '0' : '1';
        card.style.display = isVisible ? '' : 'none';
        if (isVisible) visibleMobile++;
    });

    const mobEmpty = document.getElementById('repairMobileEmptyFilterRow');
    if (mobEmpty) mobEmpty.style.display = (visibleMobile === 0) ? 'block' : 'none';

    if (window.repairsPager && typeof window.repairsPager.refresh === 'function') {
        window.repairsPager.refresh();
    }
}

function initRepairsPage() {
    if (window.setupMobiTablePagination) {
        window.repairsPager = window.setupMobiTablePagination({
            tableId: 'repairTable',
            cardsContainerId: 'repairsMobileCards',
            cardSelector: '.repair-flat-row',
            paginationContainerId: 'repairsPagination',
            rowSelector: 'tbody tr.repair-row',
            pageSize: 25,
            itemName: 'repair tickets'
        });
    }
    renderFilteredRepairs();
    if (window.refreshIcons) window.refreshIcons();
    else if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons();

    var searchInput = document.getElementById('modalPartSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            var q = e.target.value.trim();
            clearTimeout(repairPartsSearchDebounce);
            repairPartsSearchDebounce = setTimeout(function() {
                performRepairPartsSearch(q);
            }, 250);
        });

        searchInput.addEventListener('focus', function(e) {
            performRepairPartsSearch(e.target.value.trim());
        });
    }

    document.addEventListener('click', function(e) {
        var picker = document.querySelector('.repair-part-picker');
        var results = document.getElementById('modalPartSearchResults');
        if (picker && results && !picker.contains(e.target)) {
            results.style.display = 'none';
        }
    });
}

function escapeHtml(text) {
    var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initRepairsPage);
} else {
    initRepairsPage();
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeUpdateModal();
});
</script>
@endpush
