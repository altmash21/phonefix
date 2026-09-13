@php
    $isGstBill = ($sale->bill_type === 'gst' || ($sale->device_type ?? 'new') === 'new' || ($sale->cgst_amount + $sale->sgst_amount + $sale->igst_amount) > 0);
    $taxRate = (float) ($sale->tax_rate ?: 18.00);

    if ($isGstBill) {
        if (($sale->cgst_amount + $sale->sgst_amount + $sale->igst_amount) > 0) {
            $cgstAmount = (float) $sale->cgst_amount;
            $sgstAmount = (float) $sale->sgst_amount;
            $igstAmount = (float) $sale->igst_amount;
            $totalTaxAmount = $cgstAmount + $sgstAmount + $igstAmount;
            $taxableAmount = round((float) $sale->total_amount - $totalTaxAmount, 2);
        } else {
            $taxableAmount = round((float) $sale->total_amount / (1 + ($taxRate / 100)), 2);
            $totalTaxAmount = round((float) $sale->total_amount - $taxableAmount, 2);
            if (($sale->tax_type ?? 'intra_state') === 'inter_state') {
                $cgstAmount = 0.00;
                $sgstAmount = 0.00;
                $igstAmount = $totalTaxAmount;
            } else {
                $cgstAmount = round($totalTaxAmount / 2, 2);
                $sgstAmount = round($totalTaxAmount - $cgstAmount, 2);
                $igstAmount = 0.00;
            }
        }
    } else {
        $taxableAmount = (float) $sale->total_amount;
        $totalTaxAmount = 0.00;
        $cgstAmount = 0.00;
        $sgstAmount = 0.00;
        $igstAmount = 0.00;
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $isGstBill ? 'Tax Invoice' : 'Estimate' }} #{{ $sale->invoice_number }}</title>
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
        .tax-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
            border: 1px solid #d1d5db;
            margin-bottom: 10px;
        }
        .tax-table th {
            background-color: #f3f4f6;
            padding: 4px 6px;
            font-size: 8.5px;
            font-weight: bold;
            color: #1f2937;
            border-bottom: 1px solid #d1d5db;
        }
        .tax-table td {
            padding: 4px 6px;
            border-bottom: 1px solid #e5e7eb;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5px;
            border: 1px solid #d1d5db;
        }
        .summary-table td {
            padding: 5px 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-mono { font-family: 'Courier New', Courier, monospace; }
    </style>
</head>
<body>

<div class="container">
    <!-- TOP CORPORATE HEADER -->
    <table class="table header-table">
        <tr>
            <td style="width: 58%; vertical-align: top;">
                <div class="company-name">{{ setting('company.name', 'Maurya Mobile Retail Store') }}</div>
                <div style="font-size: 9.5px; color: #4b5563; margin-top: 3px;">
                    {{ setting('company.address', 'Store Location, Commercial Complex') }}<br>
                    Phone: {{ setting('company.phone', '+91 98765 43210') }} &bull; Email: {{ setting('company.email', 'support@mobitrack.local') }}
                </div>
                <div style="font-size: 9.5px; font-weight: bold; color: #111827; margin-top: 3px;">
                    GSTIN: <span class="font-mono">{{ setting('company.tax_number', setting('company.gstin', '09AAACA1234F1Z5')) }}</span>
                    &nbsp;|&nbsp; State: {{ setting('company.state', 'Uttar Pradesh') }} (09)
                </div>
            </td>
            <td style="width: 42%; vertical-align: top; text-align: right;">
                <div class="doc-title">{{ $isGstBill ? 'TAX INVOICE' : 'ESTIMATE & RETAIL BILL' }}</div>
                <div style="font-size: 9px; color: #6b7280; margin-top: 2px; text-transform: uppercase;">Original for Recipient</div>
                <div style="font-size: 13px; font-weight: bold; color: #111827; margin-top: 6px;">
                    Invoice #: <span class="font-mono">{{ $sale->invoice_number }}</span>
                </div>
                <div style="font-size: 10px; color: #374151; margin-top: 2px;">
                    Date: <strong>{{ date('d M Y', strtotime($sale->created_at)) }}</strong> &bull; {{ date('h:i A', strtotime($sale->created_at)) }}
                </div>
            </td>
        </tr>
    </table>

    <!-- PARTY & DISPATCH GRID -->
    <table class="table party-box">
        <tr>
            <td style="width: 50%; border-right: 1px solid #d1d5db;">
                <div style="font-size: 8.5px; font-weight: bold; text-transform: uppercase; color: #6b7280; letter-spacing: 0.5px;">Details of Receiver (Billed To)</div>
                <div style="font-size: 13px; font-weight: bold; color: #111827; margin-top: 2px;">{{ $sale->customer_name }}</div>
                <div style="font-size: 10px; color: #374151; margin-top: 2px;">Phone: <strong>{{ $sale->customer_phone ?: '—' }}</strong></div>
                @if($sale->customer_gstin)
                    <div style="font-size: 9.5px; color: #111827; font-weight: bold; margin-top: 2px;">GSTIN: <span class="font-mono">{{ $sale->customer_gstin }}</span></div>
                @endif
                <div style="font-size: 9.5px; color: #4b5563; margin-top: 2px;">{{ $sale->customer_address ?: 'Walk-in Retail Customer' }}</div>
                <div style="font-size: 8.5px; color: #6b7280; margin-top: 2px;">Place of Supply: {{ setting('company.state', 'Uttar Pradesh') }} (09)</div>
            </td>
            <td style="width: 50%;">
                <div style="font-size: 8.5px; font-weight: bold; text-transform: uppercase; color: #6b7280; letter-spacing: 0.5px;">Payment &amp; Dispatch Information</div>
                <div style="font-size: 10px; color: #111827; margin-top: 2px;">Mode: <strong>{{ strtoupper(str_replace('_', ' ', $sale->payment_mode)) }}</strong></div>
                @if($sale->payment_mode === 'emi' && $emiProvider)
                    <div style="font-size: 9.5px; color: #4b5563; margin-top: 2px;">Financier: <strong>{{ $emiProvider->name }}</strong> (Loan: {{ $sale->emi_loan_no ?: 'N/A' }})</div>
                @endif
                @if($isGstBill)
                    <div style="font-size: 9.5px; color: #15803D; font-weight: bold; margin-top: 2px;">Tax Regime: <strong>{{ strtoupper(str_replace('_', ' ', $sale->tax_type ?: 'intra_state')) }} (18% GST)</strong></div>
                @else
                    <div style="font-size: 9.5px; color: #4b5563; margin-top: 2px;">Bill Category: <strong>Retail / Non-GST Estimate</strong></div>
                @endif
            </td>
        </tr>
    </table>

    <!-- LINE ITEMS TABLE -->
    <table class="table items-table">
        <thead>
            <tr>
                <th style="width: 4%; text-align: center;">#</th>
                <th style="width: 38%;">Description of Goods / Handset</th>
                <th style="width: 12%; text-align: center;">HSN Code</th>
                <th style="width: 5%; text-align: center;">Qty</th>
                <th style="width: 13%; text-align: right;">Rate (Rs.)</th>
                <th style="width: 13%; text-align: right;">Taxable (Rs.)</th>
                @if($isGstBill)
                <th style="width: 13%; text-align: right;">GST 18% (Rs.)</th>
                @endif
                <th style="width: 15%; text-align: right;">Total (Rs.)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center" style="color: #4b5563;">1</td>
                <td>
                    <div style="font-weight: bold; font-size: 11px; color: #111827;">{{ $sale->brand }} {{ $sale->model }}</div>
                    <div style="font-size: 9px; color: #4b5563; margin-top: 1px;">
                        Spec: {{ $sale->ram ?: 'Std' }} RAM / {{ $sale->storage ?: 'Std' }} Storage &bull; Color: {{ $sale->color ?: 'Standard' }}
                    </div>
                    <div class="font-mono" style="font-size: 9px; font-weight: bold; color: #111827; margin-top: 2px;">
                        IMEI 1: {{ $sale->imei_1 }}
                        @if($sale->imei_2) &bull; IMEI 2: {{ $sale->imei_2 }} @endif
                    </div>
                </td>
                <td class="text-center font-mono" style="font-size: 9.5px;">{{ $sale->hsn_code ?: '85171300' }}</td>
                <td class="text-center font-mono" style="font-weight: bold;">1</td>
                <td class="text-right font-mono">{{ number_format($taxableAmount, 2) }}</td>
                <td class="text-right font-mono">{{ number_format($taxableAmount, 2) }}</td>
                @if($isGstBill)
                <td class="text-right font-mono" style="color:#111827;">{{ number_format($totalTaxAmount, 2) }}</td>
                @endif
                <td class="text-right font-mono" style="font-weight: bold;">{{ number_format($sale->total_amount, 2) }}</td>
            </tr>

            @if(isset($gifts) && $gifts->count() > 0)
                @foreach($gifts as $idx => $g)
                <tr style="background-color: #fafafa;">
                    <td class="text-center" style="color: #6b7280;">{{ $idx + 2 }}</td>
                    <td>
                        <div style="font-weight: bold; color: #111827; font-size: 9.5px;">Promotional Free Gift: {{ $g->name }}</div>
                        <div style="font-size: 8.5px; color: #6b7280;">Bundled accessory item</div>
                    </td>
                    <td class="text-center font-mono" style="font-size: 9px; color: #6b7280;">85177090</td>
                    <td class="text-center font-mono" style="font-weight: bold;">{{ $g->qty }}</td>
                    <td class="text-right font-mono" style="color: #6b7280;">0.00</td>
                    <td class="text-right font-mono" style="color: #6b7280;">0.00</td>
                    @if($isGstBill)
                    <td class="text-right font-mono" style="color: #6b7280;">0.00</td>
                    @endif
                    <td class="text-right font-mono" style="font-weight: bold; color: #111827;">FREE</td>
                </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    <!-- TOTALS & GST SUMMARY -->
    <table class="table" style="margin-bottom: 16px;">
        <tr>
            <td style="width: 55%; vertical-align: top; padding-right: 14px;">
                @if($isGstBill)
                <div style="font-size: 8.5px; font-weight: bold; text-transform: uppercase; color: #111827; margin-bottom: 4px;">Tax Calculation (GST @ 18% HSN 8517)</div>
                <table class="tax-table">
                    <thead>
                        <tr>
                            <th>Tax Component</th>
                            <th style="text-align: right;">Rate</th>
                            <th style="text-align: right;">Tax Amount (Rs.)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(($sale->tax_type ?? 'intra_state') === 'intra_state')
                            <tr>
                                <td>Central GST (CGST)</td>
                                <td class="text-right">9.00%</td>
                                <td class="text-right font-mono">{{ number_format($cgstAmount, 2) }}</td>
                            </tr>
                            <tr>
                                <td>State GST (SGST)</td>
                                <td class="text-right">9.00%</td>
                                <td class="text-right font-mono">{{ number_format($sgstAmount, 2) }}</td>
                            </tr>
                        @else
                            <tr>
                                <td>Integrated GST (IGST)</td>
                                <td class="text-right">18.00%</td>
                                <td class="text-right font-mono">{{ number_format($igstAmount, 2) }}</td>
                            </tr>
                        @endif
                        <tr style="background-color: #f9fafb; font-weight: bold;">
                            <td>Total GST Liability</td>
                            <td class="text-right">18.00%</td>
                            <td class="text-right font-mono" style="color:#4F46E5;">Rs. {{ number_format($totalTaxAmount, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
                @endif

                <div style="background-color: #f9fafb; border: 1px solid #d1d5db; padding: 6px 8px;">
                    <span style="font-size: 8.5px; font-weight: bold; text-transform: uppercase; color: #6b7280;">Amount Chargeable (in Words):</span>
                    <div style="font-size: 10px; font-weight: bold; color: #111827; margin-top: 2px;">
                        {{ $amountInWords ?? 'Rupees Only' }}
                    </div>
                </div>
            </td>

            <td style="width: 45%; vertical-align: top;">
                <table class="summary-table">
                    <tr>
                        <td style="background-color: #f9fafb; color: #4b5563; width: 55%;">Subtotal (Taxable Value)</td>
                        <td style="text-align: right;" class="font-mono">{{ number_format($taxableAmount, 2) }}</td>
                    </tr>
                    @if($isGstBill)
                    <tr>
                        <td style="background-color: #f9fafb; color: #4b5563;">CGST (9%)</td>
                        <td style="text-align: right;" class="font-mono">{{ number_format($cgstAmount, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="background-color: #f9fafb; color: #4b5563;">SGST (9%)</td>
                        <td style="text-align: right;" class="font-mono">{{ number_format($sgstAmount, 2) }}</td>
                    </tr>
                    <tr style="background-color: #f9fafb;">
                        <td style="color: #111827; font-weight: bold;">Total Tax (GST 18%)</td>
                        <td style="text-align: right; color:#111827; font-weight: bold;" class="font-mono">{{ number_format($totalTaxAmount, 2) }}</td>
                    </tr>
                    @endif
                    <tr style="background-color: #f3f4f6; font-size: 12px; font-weight: bold; border-top: 1.5px solid #374151; border-bottom: 1.5px solid #374151;">
                        <td style="color: #111827;">Invoice Grand Total</td>
                        <td style="text-align: right; color: #111827;" class="font-mono">Rs. {{ number_format($sale->total_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="color: #111827; font-weight: bold;">Amount Paid</td>
                        <td style="text-align: right; color: #111827; font-weight: bold;" class="font-mono">Rs. {{ number_format($sale->amount_paid, 2) }}</td>
                    </tr>
                    @if($sale->udhari_amount > 0)
                    <tr style="background-color: #f9fafb;">
                        <td style="color: #111827; font-weight: bold;">Balance Due (Khata)</td>
                        <td style="text-align: right; color: #111827; font-weight: bold;" class="font-mono">Rs. {{ number_format($sale->udhari_amount, 2) }}</td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <!-- TERMS & SIGNATURES SECTION -->
    <table class="table footer-table">
        <tr>
            <td style="width: 60%; vertical-align: top; padding-right: 15px;">
                <div style="font-size: 8.5px; font-weight: bold; text-transform: uppercase; color: #6b7280; margin-bottom: 2px;">Terms &amp; Conditions:</div>
                <ol style="font-size: 8px; color: #4b5563; padding-left: 12px; line-height: 1.35; margin: 0;">
                    <li>1-Year manufacturer warranty on brand new handsets as per brand ASC policy.</li>
                    <li>Physical damage, water intrusion, or unauthorized repairs void warranty.</li>
                    <li>Replacement for hardware defects within 7 days of invoice subject to brand approval.</li>
                    <li>Goods once sold will not be refunded in cash. Disputes subject to local jurisdiction.</li>
                </ol>
            </td>
            <td style="width: 40%; vertical-align: top; text-align: right;">
                <div style="font-size: 10px; font-weight: bold; color: #111827;">For {{ setting('company.name', 'Maurya Mobile Retail Store') }}</div>
                <div style="height: 42px;"></div>
                <div style="border-top: 1px solid #4b5563; display: inline-block; padding-top: 3px; font-size: 9px; color: #4b5563; min-width: 160px; text-align: center;">
                    Authorised Signatory
                </div>
            </td>
        </tr>
    </table>
</div>

</body>
</html>
