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
            <button type="button" onclick="shareRepairPdfWhatsApp()" class="btn btn-outline btn-sm" style="font-weight:700; color:#15803D; border-color:#BBF7D0; background:#F0FDF4; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">
                <i data-lucide="message-circle" style="width:13px;height:13px;"></i> WhatsApp Bill
            </button>
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
                <button type="button" onclick="shareRepairPdfWhatsApp()" class="btn btn-sm btn-outline" style="font-weight:700; font-size:11.5px; padding:6px 12px; border-radius:7px; color:#15803D; border-color:#BBF7D0; background:#F0FDF4; display:inline-flex; align-items:center; gap:4px; cursor:pointer;">
                    <i data-lucide="message-circle" style="width:13px; height:13px;"></i> WhatsApp
                </button>
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

<!-- DESKTOP WHATSAPP GUIDANCE MODAL -->
<div id="waDesktopModal" class="no-print" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.65); backdrop-filter:blur(4px); z-index:99999; align-items:center; justify-content:center; padding:16px;">
    <div style="background:#ffffff; border-radius:14px; max-width:480px; width:100%; padding:24px 26px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); border:1px solid #E2E8F0; text-align:center;">
        <div style="width:52px; height:52px; border-radius:50%; background:#DCFCE7; color:#16A34A; display:flex; align-items:center; justify-content:center; margin:0 auto 14px;">
            <svg style="width:28px;height:28px;fill:currentColor;" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
        </div>
        <h3 style="font-size:16px; font-weight:800; color:#0F172A; margin-bottom:6px;">PDF Repair Bill Downloaded</h3>
        <p style="font-size:13px; color:#475569; line-height:1.5; margin-bottom:14px;">
            The PDF bill <strong id="waModalFilename" style="color:#0F172A; word-break:break-all;">Repair-Bill.pdf</strong> has been downloaded to your computer and WhatsApp Web is opened.
        </p>
        <div style="background:#F8FAFC; border:1px dashed #CBD5E1; border-radius:8px; padding:12px 14px; font-size:12px; color:#334155; text-align:left; margin-bottom:18px; line-height:1.6;">
            <strong>👉 Send the actual PDF on WhatsApp:</strong><br>
            1. Switch to the WhatsApp Web tab.<br>
            2. Simply <strong>drag & drop</strong> the downloaded PDF into the chat window (or click <strong>📎 &gt; Document</strong>).<br>
            3. Press <strong>Enter</strong> to send the PDF file!
        </div>
        <div style="display:flex; gap:8px; justify-content:center; flex-wrap:wrap;">
            <button type="button" onclick="openWaAgain()" class="btn btn-sm" style="font-weight:700; background:#25D366; color:#ffffff; border:none; padding:7px 16px; border-radius:7px; cursor:pointer;">
                Open WhatsApp Web Again
            </button>
            <button type="button" onclick="closeDesktopWaGuide()" class="btn btn-outline btn-sm" style="font-weight:700; padding:7px 16px; border-radius:7px; cursor:pointer;">
                Got it
            </button>
        </div>
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

@push('scripts')
<script>
    let activeWaTargetUrl = '';

    async function shareRepairPdfWhatsApp() {
        const pdfUrl = "{{ route('mobileshop.repairs.pdf', ['id' => $ticket->id]) }}";
        const customerPhone = "{{ $cleanPhone }}";
        const filename = "Repair-{{ $ticket->ticket_number }}.pdf";
        const waTextUrl = "https://wa.me/{{ $cleanPhone }}?text={{ urlencode($waMsg) }}";
        const isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);

        try {
            const resp = await fetch(pdfUrl);
            if (!resp.ok) throw new Error('PDF download failed');
            const blob = await resp.blob();
            const file = new File([blob], filename, { type: 'application/pdf' });

            if (navigator.canShare && navigator.canShare({ files: [file] })) {
                await navigator.share({
                    title: "Repair Bill #{{ $ticket->ticket_number }}",
                    text: "Repair Bill #{{ $ticket->ticket_number }} from {{ addslashes($storeName) }}. Total: ₹{{ number_format($ticket->final_amount, 2) }}",
                    files: [file]
                });
                return;
            }

            // Desktop Fallback:
            // Download the actual PDF file so user has it ready
            const downloadUrl = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = downloadUrl;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            a.remove();
            setTimeout(() => URL.revokeObjectURL(downloadUrl), 4000);

            // Open WhatsApp Web with the chat preloaded
            activeWaTargetUrl = customerPhone
                ? (isMobile ? 'https://wa.me/' + customerPhone : 'https://web.whatsapp.com/send?phone=' + customerPhone)
                : 'https://web.whatsapp.com/';
            window.open(activeWaTargetUrl, '_blank');

            // Show instructional modal on screen
            showDesktopWaGuide(filename);

        } catch (err) {
            console.warn('PDF share notice:', err);
            if (err.name !== 'AbortError') {
                window.open(waTextUrl, '_blank');
            }
        }
    }

    function showDesktopWaGuide(fname) {
        const modal = document.getElementById('waDesktopModal');
        const fnameEl = document.getElementById('waModalFilename');
        if (fnameEl && fname) fnameEl.textContent = fname;
        if (modal) modal.style.display = 'flex';
    }

    function closeDesktopWaGuide() {
        const modal = document.getElementById('waDesktopModal');
        if (modal) modal.style.display = 'none';
    }

    function openWaAgain() {
        if (activeWaTargetUrl) {
            window.open(activeWaTargetUrl, '_blank');
        } else {
            window.open('https://web.whatsapp.com/', '_blank');
        }
    }
</script>
@endpush
