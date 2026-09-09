<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tax Invoice #{{ $sale->invoice_number }}</title>
    @php
        $storeName = setting('company.name', 'Maurya Mobile Retail Store');
        $storePhone = setting('company.phone', '+91 98765 43210');
        $storeEmail = setting('company.email', 'support@mauryamobile.local');
        $storeAddress = setting('company.address', 'Shop #14, Linking Road Commercial Complex, Bandra West, Mumbai - 400050');
        $storeGstin = setting('company.tax_number', setting('company.gstin', '09AAACA1234F1Z5'));
        $storeState = setting('company.state', 'Maharashtra');
        $storeStateCode = '27';
        if (stripos($storeGstin, '09') === 0) {
            $storeState = 'Uttar Pradesh';
            $storeStateCode = '09';
        }
        $isFullyPaid = ($sale->udhari_amount <= 0 && $sale->amount_paid >= $sale->total_amount);
    @endphp
    <style>
        @page {
            size: a4 portrait;
            margin: 8mm 10mm 8mm 10mm;
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
            color: #0f172a;
            background: #ffffff;
        }
        .container {
            width: 100%;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .top-accent {
            height: 4px;
            width: 100%;
            background-color: #5e6ad2;
            margin-bottom: 16px;
        }
        .header-table {
            width: 100%;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 14px;
            margin-bottom: 14px;
        }
        .company-name {
            font-size: 19px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: -0.3px;
        }
        .tagline {
            font-size: 9.5px;
            font-weight: bold;
            color: #5e6ad2;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }
        .doc-title {
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-paid {
            background-color: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-due {
            background-color: #fff1f2;
            color: #be123c;
            border: 1px solid #fecdd3;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .party-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px 0;
            margin-bottom: 16px;
        }
        .party-card {
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            border-radius: 6px;
            padding: 10px 12px;
            vertical-align: top;
        }
        .party-label {
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
            color: #5e6ad2;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-bottom: 16px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
        }
        .items-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: bold;
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 8px 10px;
            text-align: left;
        }
        .items-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
            color: #0f172a;
        }
        .summary-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 12px 0;
            margin-bottom: 16px;
        }
        .tax-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            margin-bottom: 8px;
        }
        .tax-table th {
            background-color: #f8fafc;
            padding: 4px 6px;
            font-size: 8.5px;
            font-weight: bold;
            color: #475569;
            border-bottom: 1px solid #e2e8f0;
        }
        .tax-table td {
            padding: 4px 6px;
            border-bottom: 1px solid #f1f5f9;
        }
        .totals-card {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            background-color: #ffffff;
            overflow: hidden;
        }
        .grand-total-bar {
            background-color: #0f172a;
            color: #ffffff;
            padding: 8px 12px;
            font-size: 14px;
            font-weight: bold;
        }
        .trust-strip {
            width: 100%;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background-color: #f8fafc;
            padding: 6px;
            margin-bottom: 14px;
            text-align: center;
            font-size: 9.5px;
            font-weight: bold;
            color: #0f172a;
        }
        .footer-table {
            width: 100%;
            border-top: 1px solid #e2e8f0;
            padding-top: 12px;
        }
        .stamp-box {
            border: 1.5px dashed #cbd5e1;
            border-radius: 6px;
            background-color: #f8fafc;
            padding: 6px 14px;
            display: inline-block;
            text-align: center;
            margin: 6px 0;
        }
    </style>
</head>
<body>
<div class="container">

    <div class="top-accent"></div>

    <!-- HEADER TABLE -->
    <table class="header-table">
        <tr>
            <td style="width: 60%; vertical-align: top;">
                <div class="company-name">{{ $storeName }}</div>
                <div class="tagline">Flagship Retail &amp; Authorized Smartphone Hub</div>
                <div style="font-size: 9.5px; color: #475569; margin-top: 4px; line-height: 1.4;">
                    {{ $storeAddress }}<br>
                    Phone: <strong>{{ $storePhone }}</strong> &bull; Email: {{ $storeEmail }}
                </div>
                <div style="margin-top: 6px; font-size: 9.5px;">
                    <span style="background: #f1f5f9; border: 1px solid #e2e8f0; padding: 2px 6px; border-radius: 4px; font-weight: bold;">
                        GSTIN: {{ $storeGstin }}
                    </span>
                    &nbsp;
                    <span style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 2px 6px; border-radius: 4px;">
                        State: <strong>{{ $storeState }} ({{ $storeStateCode }})</strong>
                    </span>
                </div>
            </td>
            <td style="width: 40%; text-align: right; vertical-align: top;">
                <div style="margin-bottom: 6px;">
                    @if($isFullyPaid)
                        <span class="badge-paid">&bull; Paid in Full</span>
                    @else
                        <span class="badge-due">&bull; Due: ₹{{ number_format($sale->udhari_amount, 2) }}</span>
                    @endif
                </div>
                <div class="doc-title">{{ $sale->bill_type === 'non_gst' ? 'ESTIMATE & RETAIL BILL' : 'TAX INVOICE' }}</div>
                <div style="font-size: 8.5px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Original for Recipient</div>
                
                <div style="margin-top: 6px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 4px 8px; display: inline-block;">
                    <div style="font-size: 8px; color: #64748b; text-transform: uppercase;">Invoice Number</div>
                    <div style="font-size: 13px; font-weight: bold; color: #0f172a; font-family: monospace;">{{ $sale->invoice_number }}</div>
                </div>
                <div style="font-size: 9.5px; color: #64748b; margin-top: 4px;">
                    Date: <strong>{{ date('d M Y', strtotime($sale->created_at)) }}</strong> &bull; Time: {{ date('h:i A', strtotime($sale->created_at)) }}
                </div>
            </td>
        </tr>
    </table>

    <!-- PARTY DETAILS (CUSTOMER & PAYMENT) -->
    <table class="party-table">
        <tr>
            <td class="party-card" style="width: 50%;">
                <div class="party-label">Billed To &bull; Customer Details</div>
                <div style="font-size: 14px; font-weight: bold; color: #0f172a;">{{ $sale->customer_name }}</div>
                <div style="font-size: 10.5px; color: #334155; margin-top: 2px;">Phone: <strong>{{ $sale->customer_phone ?: 'Walk-in Customer' }}</strong></div>
                @if($sale->customer_gstin)
                    <div style="font-size: 9.5px; font-family: monospace; font-weight: bold; margin-top: 2px;">GSTIN: {{ $sale->customer_gstin }}</div>
                @endif
                <div style="font-size: 9.5px; color: #64748b; margin-top: 2px;">{{ $sale->customer_address ?: 'Store Counter Delivery · Mumbai Metro' }}</div>
                <div style="font-size: 9px; color: #64748b; margin-top: 2px;">Place of Supply: <strong>{{ $storeState }} ({{ $storeStateCode }})</strong></div>
            </td>
            <td class="party-card" style="width: 50%;">
                <div class="party-label">Payment &amp; Terms</div>
                <div style="font-size: 10.5px; color: #334155; line-height: 1.5;">
                    <div>Payment Method: <strong>{{ strtoupper(str_replace('_', ' ', $sale->payment_mode)) }}</strong></div>
                    @if($sale->payment_mode === 'emi' && $emiProvider)
                        <div style="color: #4338ca; font-weight: bold;">Financier: {{ $emiProvider->name }} (Loan: {{ $sale->emi_loan_no ?: 'N/A' }})</div>
                    @endif
                    <div>Tax Regime: <strong>{{ strtoupper(str_replace('_', ' ', $sale->tax_type)) }} @ 18% GST</strong></div>
                    <div style="color: #047857; font-weight: bold; margin-top: 2px;">✓ 1 Year Official Manufacturer Warranty</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- LINE ITEMS -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">#</th>
                <th style="width: 45%;">Item Description &amp; Hardware Details</th>
                <th style="width: 12%; text-align: center;">HSN</th>
                <th style="width: 6%; text-align: center;">Qty</th>
                <th style="width: 11%; text-align: right;">Rate (₹)</th>
                <th style="width: 10%; text-align: right;">Taxable (₹)</th>
                <th style="width: 11%; text-align: right;">Total (₹)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center; color: #64748b;">1</td>
                <td>
                    <div style="font-size: 12px; font-weight: bold; color: #0f172a;">{{ $sale->brand }} {{ $sale->model }}</div>
                    <div style="font-size: 9px; color: #475569; margin-top: 1px;">
                        {{ $sale->ram ? $sale->ram . ' RAM / ' . $sale->storage : 'Standard' }}
                        @if($sale->color) &bull; {{ $sale->color }} @endif
                        &bull; <span style="color: #047857; font-weight: bold;">1-Yr Warranty</span>
                    </div>
                    <div style="margin-top: 3px; font-family: monospace; font-size: 9px; background: #f8fafc; padding: 2px 5px; border: 1px solid #e2e8f0; display: inline-block;">
                        IMEI 1: <strong>{{ $sale->imei_1 }}</strong>
                        @if($sale->imei_2) &bull; IMEI 2: <strong>{{ $sale->imei_2 }}</strong> @endif
                    </div>
                </td>
                <td style="text-align: center; font-family: monospace;">{{ $sale->hsn_code ?: '85171300' }}</td>
                <td style="text-align: center; font-weight: bold;">1</td>
                <td style="text-align: right; font-family: monospace;">{{ number_format($sale->sale_price / 1.18, 2) }}</td>
                <td style="text-align: right; font-family: monospace;">{{ number_format($sale->sale_price / 1.18, 2) }}</td>
                <td style="text-align: right; font-family: monospace; font-weight: bold; font-size: 11px;">{{ number_format($sale->total_amount, 2) }}</td>
            </tr>

            @if(isset($gifts) && $gifts->count() > 0)
                @foreach($gifts as $idx => $g)
                <tr style="background-color: #f0fdf4;">
                    <td style="text-align: center; color: #166534;">{{ $idx + 2 }}</td>
                    <td>
                        <div style="font-weight: bold; color: #166534; font-size: 10px;">🎁 Free Gift Promo: {{ $g->name }}</div>
                        <div style="font-size: 8.5px; color: #15803d;">Store complimentary bundled accessory</div>
                    </td>
                    <td style="text-align: center; font-family: monospace; color: #166534;">85177090</td>
                    <td style="text-align: center; font-weight: bold; color: #166534;">{{ $g->qty }}</td>
                    <td style="text-align: right; font-family: monospace; color: #166534;">0.00</td>
                    <td style="text-align: right; font-family: monospace; color: #166534;">0.00</td>
                    <td style="text-align: right; font-weight: bold; font-family: monospace; color: #15803d;">FREE</td>
                </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    <!-- SUMMARY & TOTALS -->
    <table class="summary-table">
        <tr>
            <!-- Left Column: Tax & Words -->
            <td style="width: 55%; vertical-align: top;">
                @if($sale->bill_type === 'gst')
                <div style="font-size: 8.5px; font-weight: bold; text-transform: uppercase; color: #475569; margin-bottom: 3px;">
                    GST Tax Computation Matrix (@ 18.00%)
                </div>
                <table class="tax-table">
                    <thead>
                        <tr>
                            <th>Tax Type</th>
                            <th style="text-align: right;">Rate</th>
                            <th style="text-align: right;">Taxable (₹)</th>
                            <th style="text-align: right;">Tax (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($sale->tax_type === 'intra_state')
                        <tr>
                            <td>Central GST (CGST)</td>
                            <td style="text-align: right;">9.00%</td>
                            <td style="text-align: right; font-family: monospace;">{{ number_format($sale->sale_price / 1.18, 2) }}</td>
                            <td style="text-align: right; font-family: monospace; font-weight: bold;">{{ number_format($sale->cgst_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td>State GST (SGST)</td>
                            <td style="text-align: right;">9.00%</td>
                            <td style="text-align: right; font-family: monospace;">{{ number_format($sale->sale_price / 1.18, 2) }}</td>
                            <td style="text-align: right; font-family: monospace; font-weight: bold;">{{ number_format($sale->sgst_amount, 2) }}</td>
                        </tr>
                        @else
                        <tr>
                            <td>Integrated GST (IGST)</td>
                            <td style="text-align: right;">18.00%</td>
                            <td style="text-align: right; font-family: monospace;">{{ number_format($sale->sale_price / 1.18, 2) }}</td>
                            <td style="text-align: right; font-family: monospace; font-weight: bold;">{{ number_format($sale->igst_amount, 2) }}</td>
                        </tr>
                        @endif
                        <tr style="background: #f8fafc; font-weight: bold;">
                            <td colspan="3" style="text-align: right;">Total GST Collected</td>
                            <td style="text-align: right; font-family: monospace;">₹{{ number_format($sale->cgst_amount + $sale->sgst_amount + $sale->igst_amount, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
                @endif

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 6px 8px; margin-top: 4px;">
                    <div style="font-size: 8px; font-weight: bold; text-transform: uppercase; color: #64748b;">Amount in Words:</div>
                    <div style="font-size: 10px; font-weight: bold; color: #0f172a; font-style: italic; margin-top: 1px;">
                        {{ $amountInWords ?? 'Rupees Only' }}
                    </div>
                </div>
            </td>

            <!-- Right Column: Grand Totals -->
            <td style="width: 45%; vertical-align: top;">
                <div class="totals-card">
                    <table style="width: 100%; font-size: 10px; padding: 6px 10px;">
                        <tr>
                            <td style="color: #475569; padding: 3px 0;">Subtotal (Taxable):</td>
                            <td style="text-align: right; font-family: monospace; font-weight: bold;">₹{{ number_format($sale->bill_type === 'gst' ? ($sale->sale_price / 1.18) : $sale->total_amount, 2) }}</td>
                        </tr>
                        @if($sale->bill_type === 'gst')
                        <tr>
                            <td style="color: #475569; padding: 3px 0;">Total GST (18%):</td>
                            <td style="text-align: right; font-family: monospace; font-weight: bold;">₹{{ number_format($sale->cgst_amount + $sale->sgst_amount + $sale->igst_amount, 2) }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td style="color: #166534; padding: 3px 0;">Discount:</td>
                            <td style="text-align: right; font-family: monospace; font-weight: bold; color: #166534;">₹0.00</td>
                        </tr>
                    </table>

                    <div class="grand-total-bar">
                        <table style="width: 100%; color: #ffffff;">
                            <tr>
                                <td>GRAND TOTAL</td>
                                <td style="text-align: right; font-family: monospace; font-size: 16px;">₹{{ number_format($sale->total_amount, 2) }}</td>
                            </tr>
                        </table>
                    </div>

                    <table style="width: 100%; font-size: 9.5px; padding: 6px 10px; background: #fafafa;">
                        <tr>
                            <td style="color: #475569;">Amount Paid:</td>
                            <td style="text-align: right; font-family: monospace; font-weight: bold; color: #059669;">₹{{ number_format($sale->amount_paid, 2) }}</td>
                        </tr>
                        @if($sale->udhari_amount > 0)
                        <tr>
                            <td style="color: #dc2626; font-weight: bold;">Balance Remaining:</td>
                            <td style="text-align: right; font-family: monospace; font-weight: bold; color: #dc2626;">₹{{ number_format($sale->udhari_amount, 2) }}</td>
                        </tr>
                        @else
                        <tr>
                            <td style="color: #64748b;">Balance:</td>
                            <td style="text-align: right; font-family: monospace; color: #059669;">₹0.00 (Settled)</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <!-- TRUST STRIP -->
    <div class="trust-strip">
        🛡️ 100% Genuine Handset &nbsp;&bull;&nbsp; ⚡ 7-Day Replacement Support &nbsp;&bull;&nbsp; 🧾 GST Paid Legal Bill
    </div>

    <!-- TERMS & SIGNATURE -->
    <table class="footer-table">
        <tr>
            <td style="width: 65%; vertical-align: top; padding-right: 14px;">
                <div style="font-size: 8.5px; font-weight: bold; text-transform: uppercase; color: #64748b; margin-bottom: 2px;">
                    Terms &amp; Conditions of Sale
                </div>
                <div style="font-size: 8px; color: #64748b; line-height: 1.4;">
                    1. 1-Year manufacturer warranty on brand new smartphones is honored directly at official brand service centers.<br>
                    2. Physical drop damage, screen cracks, or liquid intrusion void the manufacturer warranty.<br>
                    3. Dead-on-Arrival (DOA) replacement is covered within 7 days from purchase date subject to brand inspection.<br>
                    4. Goods once sold cannot be returned for cash refund. All disputes subject to local Mumbai jurisdiction.
                </div>
            </td>
            <td style="width: 35%; text-align: right; vertical-align: top;">
                <div style="font-size: 10px; font-weight: bold; color: #0f172a;">For {{ $storeName }}</div>
                <div class="stamp-box">
                    <span style="font-size: 7.5px; font-weight: bold; color: #5e6ad2; letter-spacing: 0.5px; text-transform: uppercase;">AUTHORIZED SEAL</span><br>
                    <span style="font-size: 8.5px; font-weight: bold; color: #0f172a;">MAURYA MOBILE</span>
                </div>
                <div style="font-size: 8.5px; font-weight: bold; color: #475569; text-transform: uppercase;">Authorised Signatory</div>
            </td>
        </tr>
    </table>

    <div style="margin-top: 14px; padding-top: 8px; border-top: 1px solid #f1f5f9; font-size: 8px; color: #94a3b8; text-align: center;">
        Computer generated tax invoice &bull; Original for Recipient &bull; Powered by Maurya Mobile Retail System
    </div>

</div>
</body>
</html>
