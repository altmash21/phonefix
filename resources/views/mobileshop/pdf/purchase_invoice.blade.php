<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchase Order / Invoice #{{ $po->po_number }}</title>
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
            font-size: 10.5px;
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
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: -0.3px;
        }
        .doc-title {
            font-size: 16px;
            font-weight: bold;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .party-box {
            width: 100%;
            border: 1px solid #d1d5db;
            background-color: #f9fafb;
            margin-bottom: 18px;
        }
        .party-box td {
            padding: 10px 14px;
            vertical-align: top;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-bottom: 18px;
        }
        .items-table th {
            background-color: #f3f4f6;
            color: #1f2937;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding: 6px 8px;
            border-top: 1.5px solid #374151;
            border-bottom: 1.5px solid #374151;
            text-align: left;
        }
        .items-table td {
            padding: 7px 8px;
            border-bottom: 1px solid #e5e7eb;
            color: #111827;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5px;
            border: 1px solid #d1d5db;
        }
        .summary-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-mono { font-family: 'Courier New', Courier, monospace; }
        .footer-table {
            width: 100%;
            margin-top: 18px;
            border-top: 1px solid #e5e7eb;
            padding-top: 12px;
        }
    </style>
</head>
<body>

<div class="container">
    <!-- TOP CORPORATE HEADER -->
    <table class="table header-table">
        <tr>
            <td style="width: 58%; vertical-align: top;">
                <div class="company-name">{{ store_name('PhoneFix Azamgarh') }}</div>
                <div style="font-size: 9.5px; color: #4b5563; margin-top: 3px;">
                    {{ store_address() }}<br>
                    Phone: {{ store_phone() }} &bull; Email: {{ setting('company.email', 'procurement@mobitrack.local') }}
                </div>
                <div style="font-size: 9.5px; font-weight: bold; color: #111827; margin-top: 3px;">
                    Store GSTIN: <span class="font-mono">{{ store_gstin() }}</span>
                    &nbsp;|&nbsp; State: {{ store_state('Uttar Pradesh') }} (09)
                </div>
            </td>
            <td style="width: 42%; vertical-align: top; text-align: right;">
                <div class="doc-title">{{ $po->bill_type === 'non_gst' ? 'PURCHASE ESTIMATE' : 'PURCHASE INVOICE' }}</div>
                <div style="font-size: 9px; color: #6b7280; margin-top: 2px; text-transform: uppercase;">Inward Stock Procurement Bill</div>
                <div style="font-size: 13px; font-weight: bold; color: #111827; margin-top: 6px;">
                    PO / Bill #: <span class="font-mono">{{ $po->po_number }}</span>
                </div>
                <div style="font-size: 10px; color: #374151; margin-top: 2px;">
                    Date: <strong>{{ date('d M Y', strtotime($po->order_date)) }}</strong>
                </div>
            </td>
        </tr>
    </table>

    <!-- SUPPLIER & PROCUREMENT GRID -->
    <table class="table party-box">
        <tr>
            <td style="width: 50%; border-right: 1px solid #d1d5db;">
                <div style="font-size: 8.5px; font-weight: bold; text-transform: uppercase; color: #6b7280; letter-spacing: 0.5px;">Supplier / Vendor (Purchased From)</div>
                <div style="font-size: 13px; font-weight: bold; color: #111827; margin-top: 2px;">{{ $po->supplier_name ?: 'Distributor / Vendor' }}</div>
                <div style="font-size: 10px; color: #374151; margin-top: 2px;">Phone: <strong>{{ $po->supplier_phone ?: '—' }}</strong></div>
                @if($po->supplier_gstin)
                    <div style="font-size: 9.5px; color: #111827; margin-top: 2px;">Vendor GSTIN: <span class="font-mono">{{ $po->supplier_gstin }}</span></div>
                @endif
                <div style="font-size: 9.5px; color: #4b5563; margin-top: 2px;">{{ $po->supplier_address ?: 'Authorized Wholesale Distributor' }}</div>
            </td>
            <td style="width: 50%;">
                <div style="font-size: 8.5px; font-weight: bold; text-transform: uppercase; color: #6b7280; letter-spacing: 0.5px;">Procurement &amp; Settlement Status</div>
                @if($po->bill_type === 'gst')
                <div style="font-size: 10px; color: #111827; margin-top: 2px;">Tax Mode: <strong>{{ strtoupper(str_replace('_', ' ', $po->tax_type)) }}</strong></div>
                @endif
                <div style="font-size: 10px; font-weight: bold; color: #111827; margin-top: 2px;">
                    Bill Status: {{ strtoupper(str_replace('_', ' ', $po->status)) }}
                </div>
                <div style="font-size: 10px; color: #374151; margin-top: 3px;">
                    Balance Payable: <strong class="font-mono" style="color: #111827;">Rs. {{ number_format($po->balance_due, 2) }}</strong>
                </div>
            </td>
        </tr>
    </table>

    <!-- LINE ITEMS TABLE -->
    <table class="table items-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">#</th>
                <th style="width: 44%;">Item Name &amp; Description</th>
                <th style="width: 16%;">Variant / Spec</th>
                <th style="width: 8%; text-align: center;">Qty</th>
                <th style="width: 13%; text-align: right;">Unit Cost (Rs.)</th>
                <th style="width: 14%; text-align: right;">Line Total (Rs.)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $idx => $it)
            <tr>
                <td class="text-center" style="color: #4b5563;">{{ $idx + 1 }}</td>
                <td>
                    <div style="font-weight: bold; font-size: 11px; color: #111827;">{{ $it->brand }} {{ $it->model }}</div>
                </td>
                <td style="font-size: 9.5px; color: #4b5563;">{{ $it->variant ?: 'Standard' }}</td>
                <td class="text-center font-mono" style="font-weight: bold;">{{ $it->qty }}</td>
                <td class="text-right font-mono">{{ number_format($it->unit_cost, 2) }}</td>
                <td class="text-right font-mono" style="font-weight: bold;">{{ number_format($it->line_total, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center" style="padding: 24px; color: #9ca3af;">No line items found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- TOTALS & SUMMARY GRID -->
    <table class="table" style="margin-bottom: 16px;">
        <tr>
            <td style="width: 55%; vertical-align: top; padding-right: 14px;">
                <div style="background-color: #f9fafb; border: 1px solid #d1d5db; padding: 8px 10px;">
                    <span style="font-size: 8.5px; font-weight: bold; text-transform: uppercase; color: #6b7280;">Total Bill Value (in Words):</span>
                    <div style="font-size: 10.5px; font-weight: bold; color: #111827; margin-top: 2px;">
                        {{ $amountInWords ?? 'Rupees Only' }}
                    </div>
                </div>

                <div style="margin-top: 10px; font-size: 8.5px; color: #6b7280; line-height: 1.4;">
                    &bull; All inventory quantities received and booked into PhoneFix Azamgarh inventory ledger.<br>
                    &bull; Handset IMEI numbers logged into active inventory tracking database.
                </div>
            </td>

            <td style="width: 45%; vertical-align: top;">
                <table class="summary-table">
                    <tr>
                        <td style="background-color: #f9fafb; color: #4b5563; width: 55%;">Subtotal (Taxable Value)</td>
                        <td style="text-align: right;" class="font-mono">{{ number_format($po->subtotal, 2) }}</td>
                    </tr>
                    @if($po->cgst_amount > 0 || $po->sgst_amount > 0 || $po->igst_amount > 0)
                    <tr>
                        <td style="background-color: #f9fafb; color: #4b5563;">Tax (CGST + SGST)</td>
                        <td style="text-align: right;" class="font-mono">{{ number_format($po->cgst_amount + $po->sgst_amount + $po->igst_amount, 2) }}</td>
                    </tr>
                    @endif
                    <tr style="background-color: #f3f4f6; font-size: 12px; font-weight: bold; border-top: 1.5px solid #374151; border-bottom: 1.5px solid #374151;">
                        <td style="color: #111827;">Total Invoice Value</td>
                        <td style="text-align: right; color: #111827;" class="font-mono">Rs. {{ number_format($po->total_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="color: #374151; font-weight: bold;">Amount Paid / Settled</td>
                        <td style="text-align: right; font-weight: bold; color: #111827;" class="font-mono">{{ number_format($po->amount_paid, 2) }}</td>
                    </tr>
                    @if($po->balance_due > 0)
                    <tr style="background-color: #fafafa;">
                        <td style="font-weight: bold; color: #111827;">Balance Due to Vendor</td>
                        <td style="text-align: right; font-weight: bold; color: #111827;" class="font-mono">{{ number_format($po->balance_due, 2) }}</td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <!-- SIGNATURES SECTION -->
    <table class="table footer-table">
        <tr>
            <td style="width: 50%; vertical-align: bottom;">
                <div style="border-top: 1px solid #4b5563; display: inline-block; padding-top: 3px; font-size: 9px; color: #4b5563; min-width: 170px; text-align: center;">
                    Store Inventory Receiver Signature
                </div>
            </td>
            <td style="width: 50%; vertical-align: top; text-align: right;">
                <div style="font-size: 10px; font-weight: bold; color: #111827;">For {{ setting('company.name', 'PhoneFix Azamgarh') }}</div>
                <div style="height: 42px;"></div>
                <div style="border-top: 1px solid #4b5563; display: inline-block; padding-top: 3px; font-size: 9px; color: #4b5563; min-width: 170px; text-align: center;">
                    Authorized Procurement &amp; Stamp
                </div>
            </td>
        </tr>
    </table>
</div>

</body>
</html>
