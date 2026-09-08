@extends('mobileshop.layout')

@section('title', 'Repair Desk — MobiTrack')
@section('page-title', 'Repair Service Desk & Job Sheets')

@section('page-actions')
    <div style="display:flex;align-items:center;gap:10px;">
        <select class="page-view-select" onchange="if(this.value) window.location=this.value">
            <option value="{{ route('mobileshop.repairs') }}">All Repairs</option>
            <option value="?status=received" {{ request('status')=='received' ? 'selected' : '' }}>Received</option>
            <option value="?status=in_diagnosis" {{ request('status')=='in_diagnosis' ? 'selected' : '' }}>In Diagnosis</option>
            <option value="?status=waiting_for_parts" {{ request('status')=='waiting_for_parts' ? 'selected' : '' }}>Waiting Parts</option>
            <option value="?status=in_repair" {{ request('status')=='in_repair' ? 'selected' : '' }}>In Repair</option>
            <option value="?status=ready" {{ request('status')=='ready' ? 'selected' : '' }}>Ready for Pickup</option>
            <option value="?status=delivered" {{ request('status')=='delivered' ? 'selected' : '' }}>Delivered</option>
        </select>
        <button class="btn btn-primary btn-sm" onclick="toggleForm()">
            <i data-lucide="plus" style="width:14px;height:14px;"></i> Log New Repair
        </button>
    </div>
@endsection

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
            <div class="form-row" style="margin-bottom:14px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label required">Customer Name</label>
                    <input type="text" name="customer_name" class="form-control" placeholder="Customer Full Name" required>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label required">Phone Number</label>
                    <input type="text" name="customer_phone" class="form-control" placeholder="10-digit Mobile Number" required>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label required">Device Brand</label>
                    <input type="text" name="brand" class="form-control" placeholder="e.g. Samsung, Apple, Vivo" required>
                </div>
            </div>

            <div class="form-row" style="margin-bottom:14px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label required">Device Model</label>
                    <input type="text" name="model" class="form-control" placeholder="e.g. Galaxy S23 Ultra / iPhone 14" required>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">IMEI / Serial No.</label>
                    <input type="text" name="imei_serial" class="form-control" placeholder="15-digit IMEI or Serial Number">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label required">Reported Fault / Problem</label>
                    <input type="text" name="reported_faults" class="form-control" placeholder="e.g. Display Broken, No Charging, Battery Draining" required>
                </div>
            </div>

            <div class="form-row" style="margin-bottom:14px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Estimated Repair Cost (₹)</label>
                    <input type="number" step="0.01" name="estimated_cost" class="form-control" placeholder="0.00">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Advance Deposit Received (₹)</label>
                    <input type="number" step="0.01" name="advance_paid" class="form-control" placeholder="0.00">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Physical Condition / Remarks</label>
                    <input type="text" name="physical_condition" class="form-control" placeholder="e.g. Minor scratches, back glass intact">
                </div>
            </div>

            <div class="form-row" style="margin-bottom:14px;">
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
            <div class="card-subtitle">Showing {{ count($tickets ?? []) }} active & historical repair job sheets</div>
        </div>
        <div style="display:flex;gap:10px;align-items:center;">
            <div class="search-bar">
                <i data-lucide="search" style="width:15px;height:15px;"></i>
                <input type="text" id="repairSearchInput" placeholder="Search ticket, customer, device, fault..." oninput="onRepairSearch(this.value)">
            </div>
        </div>
    </div>

    <!-- Interactive Status Filter Bar -->
    <div class="filter-bar">
        <span style="font-size:11px; font-weight:800; color:var(--text-secondary); text-transform:uppercase; margin-right:4px;">Status:</span>
        <button type="button" class="filter-pill active" id="pill-rep-all" onclick="applyRepairFilter('all', this)">
            <i data-lucide="layers" style="width:13px;height:13px;"></i> All Jobs <span class="pill-count">{{ count($tickets ?? []) }}</span>
        </button>
        <button type="button" class="filter-pill" id="pill-rep-received" onclick="applyRepairFilter('received', this)">
            <i data-lucide="inbox" style="width:13px;height:13px;"></i> Received <span class="pill-count">{{ collect($tickets ?? [])->where('status', 'received')->count() }}</span>
        </button>
        <button type="button" class="filter-pill filter-pill-warning" id="pill-rep-in-repair" onclick="applyRepairFilter('in_repair', this)">
            <i data-lucide="wrench" style="width:13px;height:13px;"></i> In Diagnosis / Repair <span class="pill-count">{{ collect($tickets ?? [])->whereIn('status', ['in_diagnosis', 'in_repair'])->count() }}</span>
        </button>
        <button type="button" class="filter-pill filter-pill-danger" id="pill-rep-waiting" onclick="applyRepairFilter('waiting_for_parts', this)">
            <i data-lucide="clock" style="width:13px;height:13px;"></i> Waiting Parts <span class="pill-count">{{ collect($tickets ?? [])->where('status', 'waiting_for_parts')->count() }}</span>
        </button>
        <button type="button" class="filter-pill filter-pill-success" id="pill-rep-ready" onclick="applyRepairFilter('ready', this)">
            <i data-lucide="check-circle" style="width:13px;height:13px;"></i> Ready for Pickup <span class="pill-count">{{ collect($tickets ?? [])->where('status', 'ready')->count() }}</span>
        </button>
        <button type="button" class="filter-pill" id="pill-rep-delivered" onclick="applyRepairFilter('delivered', this)">
            <i data-lucide="package-check" style="width:13px;height:13px;"></i> Delivered <span class="pill-count">{{ collect($tickets ?? [])->where('status', 'delivered')->count() }}</span>
        </button>
    </div>

    <div class="data-table-wrap">
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
                        <div>Labor: <strong>₹{{ number_format($repair->labor_charge ?? 0, 2) }}</strong></div>
                        <div>Parts: <strong>₹{{ number_format($repair->parts_cost ?? 0, 2) }}</strong></div>
                        <div style="font-weight:800;color:#0F172A;border-top:1px dashed #CBD5E1;padding-top:2px;margin-top:2px;">
                            Total: ₹{{ number_format($repair->total_amount ?? $repair->estimated_cost ?? 0, 2) }}
                        </div>
                        <div style="font-size:11px;color:var(--lama-green-dark);font-weight:600;">Paid: ₹{{ number_format($repair->advance_paid ?? 0, 2) }}</div>
                        @if(($repair->balance_due ?? 0) > 0)
                            <div style="font-size:11px;color:#DC2626;font-weight:700;">Due: ₹{{ number_format($repair->balance_due, 2) }}</div>
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
                            elseif ($st === 'in_repair') $badgeClass = 'badge-orange';
                            elseif ($st === 'ready') $badgeClass = 'badge-green';
                            elseif ($st === 'delivered') $badgeClass = 'badge-green';
                            elseif ($st === 'cancelled') $badgeClass = 'badge-red';
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
                        <div style="display:flex;gap:6px;justify-content:center;">
                            <button class="btn btn-primary btn-sm" style="padding:4px 10px;font-size:11px;"
                                onclick="openUpdateModal({{ json_encode($repair) }})">
                                <i data-lucide="wrench" style="width:12px;height:12px;"></i> Service / Update
                            </button>
                            <a href="{{ route('public.track_repair', ['ticket_number' => $repair->ticket_number]) }}" target="_blank" class="btn-icon" title="Public Tracking View">
                                <i data-lucide="external-link" style="width:14px;height:14px;"></i>
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
            <form id="updateRepairForm" method="POST" action="" onsubmit="if(typeof MT !== 'undefined'){ MT.enqueue('update', 'ms_repair_tickets', {source:'updateRepairForm', action:'updateRepairStatus'}); }">
                @csrf

                <!-- Status Selection -->
                <div class="form-group" style="margin-bottom:16px;">
                    <label class="form-label required" style="font-weight:700;">Repair Stage / Status</label>
                    <select name="status" id="modalStatusSelect" class="form-control" style="font-weight:600;" required>
                        <option value="received">Received (Intake / Awaiting Inspection)</option>
                        <option value="in_diagnosis">In Diagnosis (Checking Faults)</option>
                        <option value="waiting_for_parts">Waiting for Spare Parts</option>
                        <option value="in_repair">In Repair (On Technician Bench)</option>
                        <option value="ready">Ready for Pickup (Repair Completed)</option>
                        <option value="delivered">Delivered to Customer (Closed)</option>
                        <option value="cancelled">Cancelled / Returned Unfixed</option>
                    </select>
                </div>

                <!-- Part Consumption Section -->
                <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:14px; margin-bottom:16px;">
                    <div style="font-weight:700; color:#0F172A; margin-bottom:8px; display:flex; align-items:center; gap:6px;">
                        <i data-lucide="package-minus" style="width:16px;height:16px;color:var(--brand-600);"></i>
                        Use Spare Part from Shop Inventory (Auto-Deducts Stock)
                    </div>
                    <div style="font-size:12px; color:#64748B; margin-bottom:10px;">
                        Select any replacement screen, folder, battery or accessory from inventory. It will deduct from stock and add its price to the customer's repair bill.
                    </div>

                    <div class="repair-part-picker" style="position:relative; margin-bottom:12px;">
                        <input type="hidden" name="consumed_part_id" id="modalPartId" value="">
                        
                        <div style="position:relative;">
                            <input type="text" id="modalPartSearchInput" class="form-control" placeholder="🔍 Search spare part by name, brand, model, folder (OG/Normal)..." autocomplete="off" style="width:100%; padding-right:34px; font-size:13px;">
                            <button type="button" id="modalPartClearBtn" onclick="clearSelectedRepairPart()" style="display:none; position:absolute; right:8px; top:50%; transform:translateY(-50%); background:none; border:none; color:#64748B; cursor:pointer; font-size:16px; padding:2px 6px;">✕</button>
                        </div>

                        <!-- Selected Part Preview Card -->
                        <div id="modalSelectedPartCard" style="display:none; margin-top:8px; padding:10px 12px; background:#EFF6FF; border:1px solid #BFDBFE; border-radius:8px; align-items:center; justify-content:space-between;">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <span class="badge badge-blue" id="selectedPartCatBadge" style="font-size:10px; font-weight:700;">PART</span>
                                <div>
                                    <div id="selectedPartTitle" style="font-weight:700; color:#1E3A8A; font-size:12.5px;"></div>
                                    <div id="selectedPartSub" style="font-size:11px; color:#3B82F6;"></div>
                                </div>
                            </div>
                            <div style="text-align:right;">
                                <div id="selectedPartPrice" style="font-weight:800; font-size:13px; color:#1D4ED8;">₹0.00</div>
                                <div id="selectedPartStock" style="font-size:10px; color:#64748B;"></div>
                            </div>
                        </div>

                        <!-- Live Search Results Dropdown -->
                        <div id="modalPartSearchResults" style="display:none; position:absolute; left:0; right:0; top:100%; z-index:1050; background:#fff; border:1px solid #CBD5E1; border-radius:8px; box-shadow:0 10px 25px rgba(0,0,0,0.15); max-height:240px; overflow-y:auto; margin-top:4px;">
                        </div>
                    </div>

                    <div style="display:flex; gap:12px; align-items:center;">
                        <div style="flex:1;">
                            <label class="form-label" style="font-size:11px;">Part Quantity</label>
                            <input type="number" name="part_qty" id="modalPartQty" value="1" min="1" max="10" class="form-control" oninput="calculateModalTotals()">
                        </div>
                        <div style="flex:2;">
                            <label class="form-label" style="font-size:11px;">Selected Part Cost (₹)</label>
                            <input type="text" id="modalPartCostDisplay" value="₹0.00" readonly class="form-control" style="background:#F1F5F9; font-weight:700; color:var(--brand-700);">
                        </div>
                    </div>
                </div>

                <!-- Financial & Labor Charges -->
                <div class="form-row" style="margin-bottom:16px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Labor / Service Fee (₹)</label>
                        <input type="number" step="0.01" name="labor_charge" id="modalLaborInput" class="form-control" placeholder="0.00" oninput="calculateModalTotals()">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Collect Additional Payment (₹)</label>
                        <input type="number" step="0.01" name="additional_payment" id="modalPaymentInput" class="form-control" placeholder="0.00" oninput="calculateModalTotals()">
                    </div>
                </div>

                <!-- Financial Calculation Summary Card -->
                <div style="background:#F1F5F9; border:1px solid #CBD5E1; border-radius:8px; padding:12px 16px; margin-bottom:18px;">
                    <div style="font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;">Job Sheet Billing Summary:</div>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:8px; font-size:13px;">
                        <div>Existing Parts Cost: <strong id="lblPrevParts">₹0.00</strong></div>
                        <div>+ New Part Added: <strong id="lblNewPart">₹0.00</strong></div>
                        <div>+ Labor Charge: <strong id="lblLabor">₹0.00</strong></div>
                        <div>- Advance Paid: <strong id="lblAdvance" style="color:var(--lama-green-dark);">₹0.00</strong></div>
                        <div style="grid-column: span 2; border-top: 1px solid #94A3B8; padding-top: 6px; display:flex; justify-content:space-between; font-weight:800; font-size:14px;">
                            <span>Grand Total Bill: <span id="lblGrandTotal" style="color:var(--brand-700);">₹0.00</span></span>
                            <span>Balance Due: <span id="lblBalanceDue" style="color:#DC2626;">₹0.00</span></span>
                        </div>
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px; padding-top:12px; border-top:1px solid var(--card-border);">
                    <button type="button" onclick="closeUpdateModal()" class="btn btn-outline">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i data-lucide="save" style="width:14px;height:14px;"></i> Save & Update Ticket
                    </button>
                </div>
            </form>
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

function filterTable(val) {
    document.querySelectorAll('#repairTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(val.toLowerCase()) ? '' : 'none';
    });
}

function openUpdateModal(repair) {
    currentTicket = repair;
    const modal = document.getElementById('updateRepairModal');
    
    // Set form action URL
    const updateUrl = "{{ route('mobileshop.repairs.update', ['id' => ':id']) }}".replace(':id', repair.id);
    document.getElementById('updateRepairForm').action = updateUrl;

    // Set title and subtitle
    document.getElementById('modalTicketTitle').textContent = `Service Desk — ${repair.ticket_number || ('#REP-' + repair.id)}`;
    document.getElementById('modalCustomerDevice').textContent = `${repair.customer_name} (${repair.customer_phone}) • ${repair.brand} ${repair.model}`;

    // Prefill fields
    document.getElementById('modalStatusSelect').value = repair.status || 'received';
    document.getElementById('modalLaborInput').value = repair.labor_charge || 0;
    document.getElementById('modalPartQty').value = 1;
    document.getElementById('modalPaymentInput').value = '';
    clearSelectedRepairPart();

    calculateModalTotals();

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
    var results = document.getElementById('modalPartSearchResults');
    if (results) results.style.display = 'none';
    calculateModalTotals();
}

function selectRepairPart(p) {
    selectedRepairPart = p;
    document.getElementById('modalPartId').value = p.id;
    document.getElementById('modalPartSearchInput').value = p.name;
    document.getElementById('modalPartClearBtn').style.display = 'block';

    document.getElementById('selectedPartCatBadge').textContent = (p.category || 'PART').toUpperCase();
    var quality = p.display_type ? ' [' + p.display_type + ']' : '';
    document.getElementById('selectedPartTitle').textContent = p.name + quality;
    var brandModel = [p.brand, p.compatible_model].filter(Boolean).join(' ');
    document.getElementById('selectedPartSub').textContent = brandModel ? 'Fits: ' + brandModel : (p.description || '');
    document.getElementById('selectedPartPrice').textContent = '₹' + parseFloat(p.selling_price || 0).toFixed(2);
    document.getElementById('selectedPartStock').textContent = 'Stock: ' + p.stock_qty;

    document.getElementById('modalSelectedPartCard').style.display = 'flex';
    document.getElementById('modalPartSearchResults').style.display = 'none';

    calculateModalTotals();
}

function performRepairPartsSearch(q) {
    var resultsContainer = document.getElementById('modalPartSearchResults');
    if (!resultsContainer) return;
    resultsContainer.innerHTML = '<div style="padding:12px; text-align:center; color:#94A3B8; font-size:12px;">Searching inventory...</div>';
    resultsContainer.style.display = 'block';

    var searchUrl = (window.mobiShopRoutes && window.mobiShopRoutes.partsSearch) 
        ? window.mobiShopRoutes.partsSearch + '?q=' + encodeURIComponent(q)
        : "{{ route('mobileshop.parts.search') }}?q=" + encodeURIComponent(q);

    fetch(searchUrl, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (!data.success || !data.parts || data.parts.length === 0) {
            resultsContainer.innerHTML = '<div style="padding:14px; text-align:center; color:#94A3B8; font-size:12px;">No matching parts found in stock.</div>';
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
                    '<div style="font-weight:800; font-size:12.5px; color:#0F172A;">₹' + parseFloat(p.selling_price).toFixed(2) + '</div>' +
                    '<div style="font-size:10px; color:' + (outOfStock ? '#DC2626' : '#16A34A') + '; font-weight:600;">' + (outOfStock ? 'Out of Stock' : 'Stock: ' + p.stock_qty) + '</div>' +
                '</div>' +
            '</div>';
        });
        resultsContainer.innerHTML = html;
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

    document.getElementById('modalPartCostDisplay').value = `₹${newPartCost.toFixed(2)}`;

    const labor = parseFloat(document.getElementById('modalLaborInput').value || 0);
    const additionalPay = parseFloat(document.getElementById('modalPaymentInput').value || 0);

    const totalParts = prevParts + newPartCost;
    let grandTotal = labor + totalParts;
    if (grandTotal === 0 && parseFloat(currentTicket.estimated_cost || 0) > 0) {
        grandTotal = parseFloat(currentTicket.estimated_cost);
    }
    const totalPaid = prevAdvance + additionalPay;
    const balanceDue = Math.max(0, grandTotal - totalPaid);

    document.getElementById('lblPrevParts').textContent = `₹${prevParts.toFixed(2)}`;
    document.getElementById('lblNewPart').textContent = `₹${newPartCost.toFixed(2)}`;
    document.getElementById('lblLabor').textContent = `₹${labor.toFixed(2)}`;
    document.getElementById('lblAdvance').textContent = `₹${totalPaid.toFixed(2)}`;
    document.getElementById('lblGrandTotal').textContent = `₹${grandTotal.toFixed(2)}`;
    document.getElementById('lblBalanceDue').textContent = `₹${balanceDue.toFixed(2)}`;
}

let currentRepairStatus = 'all';
let currentRepairSearch = '';

function applyRepairFilter(status, btnElement) {
    currentRepairStatus = status;

    document.querySelectorAll('.filter-bar .filter-pill').forEach(p => p.classList.remove('active'));
    if (btnElement) {
        btnElement.classList.add('active');
    } else {
        const map = { 
            'all': 'pill-rep-all', 
            'received': 'pill-rep-received', 
            'in_repair': 'pill-rep-in-repair', 
            'waiting_for_parts': 'pill-rep-waiting', 
            'ready': 'pill-rep-ready', 
            'delivered': 'pill-rep-delivered' 
        };
        if (map[status]) document.getElementById(map[status])?.classList.add('active');
    }

    renderFilteredRepairs();
}

function onRepairSearch(term) {
    currentRepairSearch = term.toLowerCase().trim();
    renderFilteredRepairs();
}

function renderFilteredRepairs() {
    const rows = document.querySelectorAll('#repairTable tbody tr.repair-row');
    rows.forEach(row => {
        const status = row.dataset.status || 'received';
        const rowText = row.textContent.toLowerCase();

        let matchStatus = true;
        if (currentRepairStatus === 'in_repair') {
            matchStatus = (status === 'in_diagnosis' || status === 'in_repair');
        } else if (currentRepairStatus !== 'all') {
            matchStatus = (status === currentRepairStatus);
        }

        let matchSearch = true;
        if (currentRepairSearch) {
            matchSearch = rowText.includes(currentRepairSearch);
        }

        const isVisible = matchStatus && matchSearch;
        row.dataset.mobiHidden = isVisible ? '0' : '1';
        row.style.display = isVisible ? '' : 'none';
    });

    if (window.repairsPager && typeof window.repairsPager.refresh === 'function') {
        window.repairsPager.refresh();
    }
}

function initRepairsPage() {
    if (window.setupMobiTablePagination) {
        window.repairsPager = window.setupMobiTablePagination({
            tableId: 'repairTable',
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

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initRepairsPage);
} else {
    initRepairsPage();
}

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeUpdateModal();
});
</script>
@endpush
