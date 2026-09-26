<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Repair Bill #{{ $ticket->ticket_number }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 12mm 14mm 12mm 14mm;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #111827;
            background: #ffffff;
        }
        .container {
            width: 100%;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #1f2937;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: -0.3px;
        }
        .doc-title {
            font-size: 15px;
            font-weight: bold;
            color: #4f46e5;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: right;
        }
        .party-box {
            width: 100%;
            border: 1px solid #d1d5db;
            background-color: #f9fafb;
            margin-bottom: 14px;
        }
        .party-box td {
            padding: 8px 12px;
            vertical-align: top;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .items-table th {
            background-color: #f3f4f6;
            color: #374151;
            font-weight: bold;
            font-size: 9.5px;
            text-transform: uppercase;
            padding: 7px 8px;
            border-top: 1px solid #d1d5db;
            border-bottom: 2px solid #9ca3af;
        }
        .items-table td {
            padding: 7px 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 9.5px;
        }
        .totals-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .totals-table td {
            padding: 4px 6px;
            font-size: 10px;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            background: #ede9fe;
            color: #6d28d9;
        }
        .terms {
            font-size: 8.5px;
            color: #6b7280;
            line-height: 1.4;
        }
    </style>
</head>
<body>
@php
    $storeName = store_name('PhoneFix Azamgarh');
    $storePhone = store_phone('+91 94508 92080');
    $storeAddress = store_address('Main Road, Near Chowk, Azamgarh, UP 276001');
    $storeGstin = store_gstin();
    $storeUpi = store_upi_id();

    $laborCharge = (float)($ticket->labor_charge ?? 0);
    $partsCost = (float)($ticket->parts_cost ?? 0);
    $grandTotal = (float)($ticket->total_amount ?? $ticket->estimated_cost ?? ($laborCharge + $partsCost));
    $advancePaid = (float)($ticket->advance_paid ?? 0);
    $balanceDue = (float)($ticket->balance_due ?? max(0, $grandTotal - $advancePaid));
@endphp

<div class="container">
    <!-- Header -->
    <table class="header-table">
        <tr>
            <td style="width: 60%; vertical-align: top;">
                <div class="company-name">{{ $storeName }}</div>
                <div style="font-size: 10px; color: #4b5563; margin-top: 2px;">{{ $storeAddress }}</div>
                <div style="font-size: 10px; color: #4b5563;">Phone: {{ $storePhone }}</div>
                @if($storeGstin)
                    <div style="font-size: 9.5px; color: #6b7280;">GSTIN: {{ $storeGstin }}</div>
                @endif
            </td>
            <td style="width: 40%; vertical-align: top; text-align: right;">
                <div class="doc-title">REPAIR SERVICE BILL</div>
                <div style="font-size: 12px; font-weight: bold; margin-top: 2px;">#{{ $ticket->ticket_number }}</div>
                <div style="font-size: 9.5px; color: #6b7280; margin-top: 2px;">
                    Date: {{ date('d M Y, h:i A', strtotime($ticket->created_at)) }}
                </div>
                <div style="margin-top: 4px;">
                    <span class="status-badge">{{ strtoupper(str_replace('_', ' ', $ticket->status ?? 'received')) }}</span>
                </div>
            </td>
        </tr>
    </table>

    <!-- Customer & Device -->
    <table class="party-box">
        <tr>
            <td style="width: 50%; border-right: 1px solid #e5e7eb;">
                <div style="font-size: 9px; font-weight: bold; color: #6b7280; text-transform: uppercase;">Customer Details</div>
                <div style="font-size: 12px; font-weight: bold; color: #111827; margin-top: 2px;">{{ $ticket->customer_name }}</div>
                <div style="font-size: 10px; color: #374151;">Phone: {{ $ticket->customer_phone }}</div>
            </td>
            <td style="width: 50%;">
                <div style="font-size: 9px; font-weight: bold; color: #6b7280; text-transform: uppercase;">Device &amp; Job Sheet</div>
                <div style="font-size: 12px; font-weight: bold; color: #111827; margin-top: 2px;">{{ $ticket->brand }} {{ $ticket->model }}</div>
                @if($ticket->imei_serial)
                    <div style="font-size: 9.5px; color: #4b5563;">IMEI/Serial: {{ $ticket->imei_serial }}</div>
                @endif
                @if($ticket->physical_condition)
                    <div style="font-size: 9px; color: #6b7280;">Condition: {{ $ticket->physical_condition }}</div>
                @endif
            </td>
        </tr>
        <tr>
            <td colspan="2" style="border-top: 1px solid #e5e7eb; background: #fffbeb; padding: 6px 12px;">
                <span style="font-weight: bold; color: #92400e;">Reported Fault:</span>
                <span style="color: #78350f;">{{ $ticket->reported_faults }}</span>
            </td>
        </tr>
    </table>

    <!-- Line Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 30px; text-align: left;">#</th>
                <th style="text-align: left;">Service / Spare Part Description</th>
                <th style="width: 60px; text-align: center;">Type</th>
                <th style="width: 45px; text-align: center;">Qty</th>
                <th style="width: 80px; text-align: right;">Rate (₹)</th>
                <th style="width: 90px; text-align: right;">Amount (₹)</th>
            </tr>
        </thead>
        <tbody>
            @php $idx = 1; @endphp
            <tr>
                <td>{{ $idx++ }}</td>
                <td>
                    <strong>Labor, Diagnosis &amp; Bench Service Charge</strong>
                    <div style="font-size: 8.5px; color: #6b7280;">Hardware servicing, repair &amp; QA testing</div>
                </td>
                <td style="text-align: center;">Service</td>
                <td style="text-align: center;">1</td>
                <td style="text-align: right;">₹{{ number_format($laborCharge, 2) }}</td>
                <td style="text-align: right; font-weight: bold;">₹{{ number_format($laborCharge, 2) }}</td>
            </tr>

            @forelse($parts as $p)
                @php
                    $qty = (int)($p->quantity ?? 1);
                    $rate = (float)($p->unit_price ?? 0);
                    $lineTot = (float)($p->total_price ?? ($qty * $rate));
                @endphp
                <tr>
                    <td>{{ $idx++ }}</td>
                    <td>
                        <strong>{{ $p->part_name }}</strong>
                        @if($p->part_category)
                            <span style="font-size: 8.5px; color: #6b7280;">({{ ucwords(str_replace('_', ' ', $p->part_category)) }})</span>
                        @endif
                    </td>
                    <td style="text-align: center;">Part</td>
                    <td style="text-align: center;">{{ $qty }}</td>
                    <td style="text-align: right;">₹{{ number_format($rate, 2) }}</td>
                    <td style="text-align: right; font-weight: bold;">₹{{ number_format($lineTot, 2) }}</td>
                </tr>
            @empty
                @if($partsCost > 0)
                    <tr>
                        <td>{{ $idx++ }}</td>
                        <td><strong>Replaced Hardware Spare Parts</strong></td>
                        <td style="text-align: center;">Part</td>
                        <td style="text-align: center;">1</td>
                        <td style="text-align: right;">₹{{ number_format($partsCost, 2) }}</td>
                        <td style="text-align: right; font-weight: bold;">₹{{ number_format($partsCost, 2) }}</td>
                    </tr>
                @endif
            @endforelse
        </tbody>
    </table>

    <!-- Totals Table -->
    <table class="totals-table">
        <tr>
            <td style="width: 55%; vertical-align: top; padding-right: 15px;">
                <div style="background: #f9fafb; border: 1px solid #e5e7eb; padding: 8px 10px; border-radius: 4px;">
                    <div style="font-weight: bold; font-size: 9.5px; color: #374151; margin-bottom: 3px;">Payment Status</div>
                    @if($balanceDue <= 0.01)
                        <div style="font-weight: bold; color: #16a34a; font-size: 11px;">✓ FULLY PAID &amp; SETTLED</div>
                    @else
                        <div style="font-weight: bold; color: #dc2626; font-size: 11px;">⚠️ BALANCE DUE: ₹{{ number_format($balanceDue, 2) }}</div>
                        @if($storeUpi)
                            <div style="font-size: 9px; color: #4b5563; margin-top: 2px;">UPI: {{ $storeUpi }}</div>
                        @endif
                    @endif
                </div>
            </td>
            <td style="width: 45%; vertical-align: top;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="color: #4b5563;">Labor &amp; Service:</td>
                        <td style="text-align: right; font-weight: bold;">₹{{ number_format($laborCharge, 2) }}</td>
                    </tr>
                    @if($partsCost > 0)
                        <tr>
                            <td style="color: #4b5563;">Parts Total:</td>
                            <td style="text-align: right; font-weight: bold;">₹{{ number_format($partsCost, 2) }}</td>
                        </tr>
                    @endif
                    <tr style="border-top: 1.5px solid #d1d5db;">
                        <td style="font-weight: bold; font-size: 11px; padding-top: 4px;">Total Bill Amount:</td>
                        <td style="text-align: right; font-weight: bold; font-size: 12px; padding-top: 4px;">₹{{ number_format($grandTotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="color: #16a34a; font-weight: bold;">Advance Paid:</td>
                        <td style="text-align: right; color: #16a34a; font-weight: bold;">-₹{{ number_format($advancePaid, 2) }}</td>
                    </tr>
                    <tr style="border-top: 1px dashed #d1d5db;">
                        <td style="font-weight: bold; font-size: 11px; color: {{ $balanceDue > 0 ? '#dc2626' : '#16a34a' }}; padding-top: 4px;">Balance Due:</td>
                        <td style="text-align: right; font-weight: bold; font-size: 13px; color: {{ $balanceDue > 0 ? '#dc2626' : '#16a34a' }}; padding-top: 4px;">₹{{ number_format($balanceDue, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Terms & Signature -->
    <table style="width: 100%; margin-top: 16px; border-top: 1px solid #e5e7eb; padding-top: 10px;">
        <tr>
            <td style="width: 65%; vertical-align: top;" class="terms">
                <strong>Terms &amp; Conditions:</strong><br>
                1. 30 days testing warranty on replaced hardware parts only.<br>
                2. No warranty on physical damage, liquid ingress, screen cracks, or third-party repair.<br>
                3. Customer is requested to verify device condition and functionality before leaving the store.<br>
                4. Unclaimed devices after 30 days of completion may be disposed of or charged storage.
            </td>
            <td style="width: 35%; text-align: center; vertical-align: bottom;">
                <div style="font-size: 9px; color: #9ca3af; margin-bottom: 24px;">For {{ $storeName }}</div>
                <div style="border-top: 1px dashed #9ca3af; padding-top: 3px; font-weight: bold; font-size: 10px;">
                    Authorized Signatory
                </div>
            </td>
        </tr>
    </table>
</div>
</body>
</html>
