@extends('mobileshop.layout')

@section('title', 'Repair Bill #' . $ticket->ticket_number . ' — ' . store_name('PhoneFix'))
@section('page-title', 'Repair Service Bill & Job Sheet')

@php
    $storeName = store_name('PhoneFix Azamgarh');
    $storePhone = store_phone('+91 94508 92080');
    $storeAddress = store_address('Main Road, Near Chowk, Azamgarh, UP 276001');
    $storeGstin = store_gstin();
    $storeUpi = store_upi_id();
    $storeLandline = store_landline();

    $cleanPhone = preg_replace('/[^0-9]/', '', $ticket->customer_phone ?? '');
    if (strlen($cleanPhone) === 10) {
        $cleanPhone = '91' . $cleanPhone;
    }

    $laborCharge = (float)($ticket->labor_charge ?? 0);
    $partsCost = (float)($ticket->parts_cost ?? 0);
    $grandTotal = (float)($ticket->total_amount ?? $ticket->estimated_cost ?? ($laborCharge + $partsCost));
    $advancePaid = (float)($ticket->advance_paid ?? 0);
    $balanceDue = (float)($ticket->balance_due ?? max(0, $grandTotal - $advancePaid));

    $trackUrl = route('public.track_repair', ['ticket_number' => $ticket->ticket_number]);

    $waMsg = "*{$storeName} — Repair Service Bill*\n";
    $waMsg .= "Ticket #*{$ticket->ticket_number}*\n\n";
    $waMsg .= "Dear *" . ($ticket->customer_name ?: 'Customer') . "*,\n";
    $waMsg .= "Here is the repair bill for your *{$ticket->brand} {$ticket->model}*:\n\n";
    $waMsg .= "• *Fault:* " . ($ticket->reported_faults ?: 'General Service') . "\n";
    $waMsg .= "• *Labor Charge:* ₹" . number_format($laborCharge, 0) . "\n";
    if ($partsCost > 0) {
        $waMsg .= "• *Parts Replaced:* ₹" . number_format($partsCost, 0) . "\n";
    }
    $waMsg .= "• *Total Bill:* ₹" . number_format($grandTotal, 0) . "\n";
    $waMsg .= "• *Advance Paid:* ₹" . number_format($advancePaid, 0) . "\n";
    if ($balanceDue > 0) {
        $waMsg .= "• *Balance Due:* ₹" . number_format($balanceDue, 0) . "\n";
        if (!empty($storeUpi)) {
            $waMsg .= "• *Pay via UPI:* `{$storeUpi}`\n";
        }
    } else {
        $waMsg .= "• *Payment Status:* PAID IN FULL (✓)\n";
    }
    $waMsg .= "\n🔍 *Live Track Your Repair Status:*\n" . $trackUrl . "\n\n";
    $waMsg .= "Thank you for trusting {$storeName}! Call: {$storePhone}";
@endphp

@section('page-actions')
    <div class="invoice-action-buttons" style="display:flex; gap: 8px; align-items:center; flex-wrap:wrap;">
        <button type="button" onclick="window.print()" class="btn btn-primary btn-sm" style="font-weight:700; background:#5E6AD2; border-color:#5E6AD2;">
            <i data-lucide="printer" style="width:13px;height:13px;"></i> Print Bill
        </button>
        <a href="{{ route('mobileshop.repairs.pdf', ['id' => $ticket->id]) }}" class="btn btn-outline btn-sm" style="font-weight:700; color:#D97706; border-color:#FDE68A; background:#FFFBEB;">
            <i data-lucide="download" style="width:13px;height:13px;"></i> Download PDF
        </a>
        @if($cleanPhone)
            <a href="https://wa.me/{{ $cleanPhone }}?text={{ urlencode($waMsg) }}" target="_blank" class="btn btn-outline btn-sm" style="font-weight:700; color:#15803D; border-color:#BBF7D0; background:#F0FDF4;">
                <i data-lucide="message-circle" style="width:13px;height:13px;"></i> WhatsApp Bill
            </a>
        @endif
        <a href="{{ route('mobileshop.repairs') }}" class="btn btn-outline btn-sm" style="font-weight:700;">
            <i data-lucide="arrow-left" style="width:13px;height:13px;"></i> Back to Queue
        </a>
    </div>
@endsection

@section('content')
<div class="repair-invoice-wrapper" style="width:100%; margin:0; padding-bottom:40px;">

    <!-- Mobile Prompt Bar (Hidden in Print) -->
    <div class="no-print" style="margin-bottom:12px; display:flex; gap:8px; flex-wrap:wrap; align-items:center; justify-content:space-between; background:#FFFFFF; border:1px solid #E2E8F0; border-radius:10px; padding:10px 14px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
        <div style="display:flex; gap:6px; align-items:center;">
            <button type="button" onclick="window.print()" class="btn btn-primary btn-sm" style="font-weight:800; font-size:12px; padding:7px 14px; border-radius:7px; background:#5E6AD2; color:#fff; border:none; display:inline-flex; align-items:center; gap:6px;">
                <i data-lucide="printer" style="width:14px; height:14px;"></i> Print Bill
            </button>
            <a href="{{ route('mobileshop.repairs.pdf', ['id' => $ticket->id]) }}" class="btn btn-sm btn-outline" style="font-weight:700; font-size:11.5px; padding:6px 12px; border-radius:7px; color:#D97706; border-color:#FDE68A; background:#FFFBEB; display:inline-flex; align-items:center; gap:4px;">
                <i data-lucide="download" style="width:13px; height:13px;"></i> PDF
            </a>
        </div>
        <div style="display:flex; gap:6px; align-items:center;">
            @if($cleanPhone)
                <a href="https://wa.me/{{ $cleanPhone }}?text={{ urlencode($waMsg) }}" target="_blank" class="btn btn-sm btn-outline" style="font-weight:700; font-size:11.5px; padding:6px 12px; border-radius:7px; color:#15803D; border-color:#BBF7D0; background:#F0FDF4; display:inline-flex; align-items:center; gap:4px;">
                    <i data-lucide="message-circle" style="width:13px; height:13px;"></i> WhatsApp
                </a>
            @endif
            <a href="{{ route('mobileshop.repairs') }}" class="btn btn-sm btn-outline" style="font-weight:700; font-size:11.5px; padding:6px 12px; border-radius:7px; color:#475569; border-color:#CBD5E1;">
                Back
            </a>
        </div>
    </div>

    <!-- Printable Container -->
    <div class="printable-repair-sheet" style="background:#FFFFFF; border:1px solid #D1D5DB; border-radius:6px; padding:28px 32px; color:#111827; box-shadow:0 1px 3px rgba(0,0,0,0.05); max-width:860px; margin:0 auto;">

        <!-- Header -->
        <table style="width:100%; border-collapse:collapse; margin-bottom:18px; border-bottom:2px solid #1E293B; padding-bottom:14px;">
            <tr>
                <td style="vertical-align:top; width:60%;">
                    <div style="font-size:20px; font-weight:900; color:#0F172A; text-transform:uppercase; letter-spacing:-0.3px;">
                        {{ $storeName }}
                    </div>
                    <div style="font-size:12px; color:#475569; margin-top:2px;">{{ $storeAddress }}</div>
                    <div style="font-size:12px; color:#475569; margin-top:2px;">
                        <strong>Phone:</strong> {{ $storePhone }}
                        @if($storeLandline) • <strong>Landline:</strong> {{ $storeLandline }} @endif
                    </div>
                    @if($storeGstin)
                        <div style="font-size:11.5px; color:#64748B; font-family:monospace; margin-top:2px;">
                            <strong>GSTIN:</strong> {{ $storeGstin }}
                        </div>
                    @endif
                </td>
                <td style="vertical-align:top; width:40%; text-align:right;">
                    <div style="font-size:16px; font-weight:900; color:#5E6AD2; text-transform:uppercase; letter-spacing:0.5px;">
                        REPAIR SERVICE BILL
                    </div>
                    <div style="font-size:14px; font-weight:800; font-family:monospace; color:#0F172A; margin-top:4px;">
                        #{{ $ticket->ticket_number }}
                    </div>
                    <div style="font-size:11.5px; color:#64748B; margin-top:2px;">
                        <strong>Date:</strong> {{ date('d M Y, h:i A', strtotime($ticket->created_at)) }}
                    </div>
                    <div style="margin-top:6px;">
                        <span style="font-size:10px; font-weight:800; text-transform:uppercase; padding:3px 8px; border-radius:4px; background:#EDE9FE; color:#6D28D9; border:1px solid #DDD6FE;">
                            Status: {{ strtoupper(str_replace('_', ' ', $ticket->status ?? 'received')) }}
                        </span>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Customer & Device Details Grid -->
        <table style="width:100%; border-collapse:collapse; margin-bottom:18px; border:1px solid #CBD5E1; background:#F8FAFC; border-radius:6px; overflow:hidden;">
            <tr>
                <td style="width:50%; padding:10px 14px; vertical-align:top; border-right:1px solid #E2E8F0;">
                    <div style="font-size:10.5px; font-weight:800; text-transform:uppercase; color:#64748B; letter-spacing:0.5px; margin-bottom:4px;">
                        Customer Information
                    </div>
                    <div style="font-size:14px; font-weight:800; color:#0F172A;">{{ $ticket->customer_name }}</div>
                    <div style="font-size:12px; color:#334155; margin-top:2px; font-family:monospace;">📞 {{ $ticket->customer_phone }}</div>
                </td>
                <td style="width:50%; padding:10px 14px; vertical-align:top;">
                    <div style="font-size:10.5px; font-weight:800; text-transform:uppercase; color:#64748B; letter-spacing:0.5px; margin-bottom:4px;">
                        Device &amp; Job Details
                    </div>
                    <div style="font-size:14px; font-weight:800; color:#0F172A;">{{ $ticket->brand }} {{ $ticket->model }}</div>
                    @if($ticket->imei_serial)
                        <div style="font-size:11.5px; color:#475569; font-family:monospace; margin-top:2px;">IMEI/Serial: {{ $ticket->imei_serial }}</div>
                    @endif
                    @if($ticket->physical_condition)
                        <div style="font-size:11px; color:#64748B; margin-top:2px;">Condition: {{ $ticket->physical_condition }}</div>
                    @endif
                    @if(!empty($ticket->decrypted_pin) && $ticket->decrypted_pin !== 'None')
                        <div style="font-size:11px; color:#64748B; margin-top:2px; font-family:monospace;">Lock/Passcode: 🔑 {{ $ticket->decrypted_pin }}</div>
                    @endif
                </td>
            </tr>
            <tr>
                <td colspan="2" style="padding:8px 14px; border-top:1px solid #E2E8F0; background:#FFFBEB;">
                    <span style="font-size:11px; font-weight:800; color:#92400E; text-transform:uppercase;">Reported Fault:</span>
                    <span style="font-size:12px; font-weight:700; color:#78350F; margin-left:6px;">{{ $ticket->reported_faults }}</span>
                </td>
            </tr>
        </table>

        <!-- Billing Items Table -->
        <table style="width:100%; border-collapse:collapse; margin-bottom:18px;">
            <thead>
                <tr style="background:#F1F5F9; border-top:1px solid #CBD5E1; border-bottom:2px solid #CBD5E1;">
                    <th style="padding:8px 10px; font-size:11px; font-weight:800; text-transform:uppercase; color:#334155; text-align:left; width:36px;">#</th>
                    <th style="padding:8px 10px; font-size:11px; font-weight:800; text-transform:uppercase; color:#334155; text-align:left;">Service Description / Spare Part</th>
                    <th style="padding:8px 10px; font-size:11px; font-weight:800; text-transform:uppercase; color:#334155; text-align:center; width:80px;">Type</th>
                    <th style="padding:8px 10px; font-size:11px; font-weight:800; text-transform:uppercase; color:#334155; text-align:center; width:60px;">Qty</th>
                    <th style="padding:8px 10px; font-size:11px; font-weight:800; text-transform:uppercase; color:#334155; text-align:right; width:110px;">Rate (₹)</th>
                    <th style="padding:8px 10px; font-size:11px; font-weight:800; text-transform:uppercase; color:#334155; text-align:right; width:120px;">Amount (₹)</th>
                </tr>
            </thead>
            <tbody>
                @php $rowIdx = 1; @endphp

                <!-- Labor & Service Charge -->
                <tr style="border-bottom:1px solid #E2E8F0;">
                    <td style="padding:8px 10px; font-size:11.5px; color:#64748B;">{{ $rowIdx++ }}</td>
                    <td style="padding:8px 10px; font-size:12px; font-weight:700; color:#0F172A;">
                        Labor, Diagnostic &amp; Bench Service Charge
                        <div style="font-size:10.5px; color:#64748B; font-weight:400;">Professional hardware/software servicing &amp; testing</div>
                    </td>
                    <td style="padding:8px 10px; font-size:11px; color:#475569; text-align:center;">
                        <span style="background:#F1F5F9; border-radius:4px; padding:2px 6px; font-weight:700; font-size:10px;">Service</span>
                    </td>
                    <td style="padding:8px 10px; font-size:12px; color:#0F172A; text-align:center;">1</td>
                    <td style="padding:8px 10px; font-size:12px; color:#0F172A; text-align:right; font-family:'JetBrains Mono', monospace;">₹{{ number_format($laborCharge, 2) }}</td>
                    <td style="padding:8px 10px; font-size:12px; font-weight:800; color:#0F172A; text-align:right; font-family:'JetBrains Mono', monospace;">₹{{ number_format($laborCharge, 2) }}</td>
                </tr>

                <!-- Used Parts -->
                @forelse($parts as $p)
                    @php
                        $partQty = (int)($p->quantity ?? 1);
                        $partUnit = (float)($p->unit_price ?? 0);
                        $partTotal = (float)($p->total_price ?? ($partQty * $partUnit));
                    @endphp
                    <tr style="border-bottom:1px solid #E2E8F0;">
                        <td style="padding:8px 10px; font-size:11.5px; color:#64748B;">{{ $rowIdx++ }}</td>
                        <td style="padding:8px 10px; font-size:12px; font-weight:700; color:#0F172A;">
                            {{ $p->part_name }}
                            @if($p->part_category)
                                <div style="font-size:10.5px; color:#64748B; font-weight:400;">Category: {{ ucwords(str_replace('_', ' ', $p->part_category)) }}</div>
                            @endif
                        </td>
                        <td style="padding:8px 10px; font-size:11px; color:#475569; text-align:center;">
                            <span style="background:#EDE9FE; color:#6D28D9; border-radius:4px; padding:2px 6px; font-weight:700; font-size:10px;">Part</span>
                        </td>
                        <td style="padding:8px 10px; font-size:12px; color:#0F172A; text-align:center;">{{ $partQty }}</td>
                        <td style="padding:8px 10px; font-size:12px; color:#0F172A; text-align:right; font-family:'JetBrains Mono', monospace;">₹{{ number_format($partUnit, 2) }}</td>
                        <td style="padding:8px 10px; font-size:12px; font-weight:800; color:#0F172A; text-align:right; font-family:'JetBrains Mono', monospace;">₹{{ number_format($partTotal, 2) }}</td>
                    </tr>
                @empty
                    @if($partsCost > 0)
                        <tr style="border-bottom:1px solid #E2E8F0;">
                            <td style="padding:8px 10px; font-size:11.5px; color:#64748B;">{{ $rowIdx++ }}</td>
                            <td style="padding:8px 10px; font-size:12px; font-weight:700; color:#0F172A;">Replaced Spare Parts &amp; Hardware Components</td>
                            <td style="padding:8px 10px; font-size:11px; color:#475569; text-align:center;">
                                <span style="background:#EDE9FE; color:#6D28D9; border-radius:4px; padding:2px 6px; font-weight:700; font-size:10px;">Part</span>
                            </td>
                            <td style="padding:8px 10px; font-size:12px; color:#0F172A; text-align:center;">1</td>
                            <td style="padding:8px 10px; font-size:12px; color:#0F172A; text-align:right; font-family:'JetBrains Mono', monospace;">₹{{ number_format($partsCost, 2) }}</td>
                            <td style="padding:8px 10px; font-size:12px; font-weight:800; color:#0F172A; text-align:right; font-family:'JetBrains Mono', monospace;">₹{{ number_format($partsCost, 2) }}</td>
                        </tr>
                    @endif
                @endforelse
            </tbody>
        </table>

        <!-- Totals & Payment Summary -->
        <table style="width:100%; border-collapse:collapse; margin-bottom:20px;">
            <tr>
                <td style="width:55%; vertical-align:top; padding-right:20px;">
                    <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:6px; padding:12px 14px;">
                        <div style="font-size:11px; font-weight:800; text-transform:uppercase; color:#475569; margin-bottom:4px;">
                            Payment Status &amp; UPI
                        </div>
                        @if($balanceDue <= 0.01)
                            <div style="font-size:13px; font-weight:900; color:#15803D;">
                                ✓ FULLY PAID &amp; SETTLED
                            </div>
                        @else
                            <div style="font-size:13px; font-weight:900; color:#B91C1C;">
                                ⚠️ BALANCE DUE: ₹{{ number_format($balanceDue, 2) }}
                            </div>
                            @if($storeUpi)
                                <div style="font-size:11px; color:#475569; margin-top:4px;">
                                    Pay via UPI: <strong style="font-family:monospace; color:#0F172A;">{{ $storeUpi }}</strong>
                                </div>
                            @endif
                        @endif
                        <div style="font-size:10.5px; color:#64748B; margin-top:8px;">
                            Track repair online: <a href="{{ $trackUrl }}" target="_blank" style="color:#5E6AD2; text-decoration:none; font-weight:700;">{{ $trackUrl }}</a>
                        </div>
                    </div>
                </td>
                <td style="width:45%; vertical-align:top;">
                    <table style="width:100%; border-collapse:collapse; font-size:12px;">
                        <tr>
                            <td style="padding:5px 0; color:#475569;">Labor &amp; Service:</td>
                            <td style="padding:5px 0; text-align:right; font-family:'JetBrains Mono', monospace; font-weight:700; color:#0F172A;">
                                ₹{{ number_format($laborCharge, 2) }}
                            </td>
                        </tr>
                        @if($partsCost > 0)
                            <tr>
                                <td style="padding:5px 0; color:#475569;">Parts Total:</td>
                                <td style="padding:5px 0; text-align:right; font-family:'JetBrains Mono', monospace; font-weight:700; color:#0F172A;">
                                    ₹{{ number_format($partsCost, 2) }}
                                </td>
                            </tr>
                        @endif
                        <tr style="border-top:1.5px solid #CBD5E1;">
                            <td style="padding:7px 0; font-size:14px; font-weight:800; color:#0F172A;">Bill Grand Total:</td>
                            <td style="padding:7px 0; font-size:16px; font-weight:900; text-align:right; font-family:'JetBrains Mono', monospace; color:#0F172A;">
                                ₹{{ number_format($grandTotal, 2) }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:5px 0; color:#15803D; font-weight:700;">Advance Paid:</td>
                            <td style="padding:5px 0; text-align:right; font-family:'JetBrains Mono', monospace; font-weight:800; color:#15803D;">
                                -₹{{ number_format($advancePaid, 2) }}
                            </td>
                        </tr>
                        <tr style="border-top:1.5px dashed #CBD5E1;">
                            <td style="padding:7px 0; font-size:13px; font-weight:900; color:{{ $balanceDue > 0 ? '#DC2626' : '#15803D' }};">
                                {{ $balanceDue > 0 ? 'Remaining Balance Due:' : 'Net Balance Due:' }}
                            </td>
                            <td style="padding:7px 0; font-size:16px; font-weight:900; text-align:right; font-family:'JetBrains Mono', monospace; color:{{ $balanceDue > 0 ? '#DC2626' : '#15803D' }};">
                                ₹{{ number_format($balanceDue, 2) }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Terms & Signatures -->
        <table style="width:100%; border-collapse:collapse; margin-top:14px; padding-top:14px; border-top:1px solid #E2E8F0;">
            <tr>
                <td style="width:65%; vertical-align:top; font-size:10px; color:#64748B; line-height:1.45; padding-right:16px;">
                    <div style="font-weight:800; text-transform:uppercase; color:#334155; margin-bottom:3px;">Terms &amp; Conditions</div>
                    <div>1. 30 days testing warranty on replaced hardware parts only.</div>
                    <div>2. Physical damage, screen breakage, liquid/water logging, or third-party tampering voids all warranties.</div>
                    <div>3. Customer is requested to verify and check all device functionalities at the time of delivery.</div>
                    <div>4. Please collect your device within 30 days of repair completion.</div>
                </td>
                <td style="width:35%; vertical-align:bottom; text-align:center;">
                    <div style="margin-bottom:30px; font-size:11px; font-weight:700; color:#94A3B8;">For {{ $storeName }}</div>
                    <div style="border-top:1px dashed #94A3B8; padding-top:4px; font-size:11px; font-weight:800; color:#0F172A;">
                        Authorized Signatory
                    </div>
                </td>
            </tr>
        </table>

    </div>
</div>

<style>
@media print {
    /* Hide layout chrome, sidebars, headers */
    .sidebar, .topbar, .app-header, .no-print, .invoice-action-buttons, .navbar, footer {
        display: none !important;
    }
    body, html {
        background: #ffffff !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    .repair-invoice-wrapper {
        padding: 0 !important;
        margin: 0 !important;
    }
    .printable-repair-sheet {
        border: none !important;
        box-shadow: none !important;
        padding: 10px 15px !important;
        max-width: 100% !important;
    }
}
</style>
@endsection
