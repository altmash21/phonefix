<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta charset="UTF-8">
    <title>Statement of Account - {{ $customer->name }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 12mm 15mm 12mm 15mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10.5px;
            line-height: 1.4;
            color: #111827;
            background: #ffffff;
        }
        .container {
            width: 100%;
            margin: 0;
            padding: 0;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .font-mono { font-family: 'Courier New', Courier, monospace; }

        .header-section {
            text-align: center;
            margin-bottom: 12px;
        }
        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #111827;
            letter-spacing: -0.3px;
        }
        .company-meta {
            font-size: 10px;
            color: #4b5563;
            margin-top: 3px;
        }
        .dotted-line {
            border-top: 1px dotted #9ca3af;
            margin: 14px 0;
        }
        .doc-title {
            font-size: 15px;
            font-weight: bold;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .doc-date {
            font-size: 10px;
            color: #6b7280;
            margin-top: 2px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11.5px;
            margin-bottom: 12px;
        }
        .info-table td {
            padding: 3px 0;
        }
        .ledger-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-bottom: 16px;
        }
        .ledger-table th {
            border-top: 1.5px solid #374151;
            border-bottom: 1.5px solid #374151;
            background-color: #f3f4f6;
            padding: 6px 6px;
            font-size: 9.5px;
            color: #1f2937;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .ledger-table td {
            padding: 6px 6px;
            border-bottom: 1px solid #e5e7eb;
            color: #111827;
        }
        .totals-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11.5px;
            margin-bottom: 16px;
        }
        .totals-table td {
            padding: 4px 0;
        }
        .balance-box {
            width: 100%;
            border: 1.5px solid #111827;
            background-color: #f9fafb;
            padding: 12px 16px;
            margin-bottom: 22px;
        }
        .balance-title {
            font-size: 11.5px;
            color: #374151;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .balance-amt {
            font-size: 18px;
            font-weight: bold;
            color: #111827;
            text-align: right;
        }
        .footer-section {
            text-align: center;
            font-size: 9.5px;
            color: #6b7280;
            line-height: 1.5;
            margin-top: 20px;
        }
        .sign-line {
            width: 200px;
            margin: 36px auto 4px;
            border-top: 1px solid #9ca3af;
        }
    </style>
</head>
<body>

<div class="container">

    <!-- 1. CENTERED STORE HEADER -->
    <div class="header-section">
        <div class="company-name">{{ store_name('PhoneFix Azamgarh') }}</div>
        <div class="company-meta">{{ store_address() }}</div>
        <div class="company-meta">Phone: {{ store_phone() }}@if(!empty(store_gstin())) &middot; GSTIN: {{ store_gstin() }}@endif</div>
    </div>

    <!-- DOTTED DIVIDER -->
    <div class="dotted-line"></div>

    <!-- 2. DOCUMENT TITLE -->
    <div class="header-section" style="margin-bottom: 16px;">
        <div class="doc-title">Statement of account</div>
        <div class="doc-date" style="font-weight: bold; color: #4338ca;">{{ $periodLabel ?? 'Active Unsettled Dues' }} &bull; As of {{ date('d M Y') }}</div>
    </div>

    <!-- 3. ACCOUNT HOLDER METADATA -->
    <table class="info-table">
        <tr>
            <td style="color: #6b7280; width: 40%;">Account Holder</td>
            <td class="text-right font-bold" style="color: #111827; width: 60%; font-size: 12.5px;">{{ $customer->name }}</td>
        </tr>
        <tr>
            <td style="color: #6b7280;">Mobile Number</td>
            <td class="text-right font-mono" style="color: #111827;">{{ $customer->phone ?: '-' }}</td>
        </tr>
    </table>

    <!-- 4. TRANSACTION LEDGER TABLE (5 COLUMNS) -->
    <table class="ledger-table">
        <thead>
            <tr>
                <th class="text-left" style="width: 12%;">Date</th>
                <th class="text-left" style="width: 40%;">Items Purchased / Particulars</th>
                <th class="text-right" style="width: 16%;">Total Amount (Rs.)</th>
                <th class="text-right" style="width: 16%;">Amount Received (Rs.)</th>
                <th class="text-right" style="width: 16%;">Balance (Rs.)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ledger as $entry)
            @php
                $isOpening = !empty($entry->is_opening);
                $isOldUdhar = in_array($entry->entry_type ?? '', ['opening_balance', 'old_udhar', 'debit_adjustment']) && !$isOpening;
                $isRepayment = (($entry->paid > 0 && $entry->billed <= 0) || in_array($entry->entry_type ?? '', ['adjustment', 'refund_credit', 'payment_received'])) && !$isOpening;
                $itemName = $entry->item_name ?? $entry->particulars;
                $dateFormatted = !empty($entry->date) ? $entry->date : (isset($entry->created_at) ? date('d/m/Y', strtotime($entry->created_at)) : '-');
            @endphp
            @if($isOpening)
            <tr style="background-color: #fffbeb;">
                <td class="font-mono font-bold" style="color: #92400e; vertical-align: top;">{{ $dateFormatted }}</td>
                <td style="vertical-align: top;">
                    <div style="font-weight: bold; color: #b45309; font-size: 10px;">
                        Opening Balance Brought Forward
                    </div>
                    <div style="font-size: 8.5px; color: #92400e; margin-top: 1px;">
                        Accumulated balance prior to {{ $entry->date }}
                    </div>
                </td>
                <td class="text-right font-mono font-bold" style="vertical-align: top; color: #92400e;">
                    {{ number_format($entry->billed, 2) }}
                </td>
                <td class="text-right font-mono" style="vertical-align: top; color: #6b7280;">
                    -
                </td>
                <td class="text-right font-mono font-bold" style="color: #92400e; vertical-align: top;">
                    {{ number_format($entry->balance_left, 2) }}
                </td>
            </tr>
            @continue
            @endif
            <tr>
                <td class="font-mono" style="color: #4b5563; vertical-align: top;">{{ $dateFormatted }}</td>
                <td style="vertical-align: top;">
                    @if($isOldUdhar)
                        <div style="font-weight: bold; color: #b45309; font-size: 10px;">
                            Old Udhar / Opening Balance
                        </div>
                        @if(!empty($entry->description) && !in_array(strtolower($entry->description), ['old udhar / opening balance', 'old udhar', 'opening balance']))
                            <div style="font-size: 9px; color: #111827; margin-top: 1px; font-weight: normal;">
                                <strong>Details:</strong> {{ $entry->description }}
                            </div>
                        @endif
                        @if(!empty($entry->ref_no))
                            <div style="font-size: 8.5px; color: #6b7280; font-family: monospace; margin-top: 1px;">
                                Ref: #{{ $entry->ref_no }}
                            </div>
                        @endif
                    @elseif($isRepayment)
                        @php
                            $rawModePdf = strtolower(trim((string)($entry->payment_mode ?? '')));
                            $rawTextPdf = strtolower(trim((string)($entry->raw_remarks ?? '') . ' ' . (string)($entry->description ?? '') . ' ' . (string)($entry->particulars ?? '')));
                            $isReversalPdf = ($entry->entry_type ?? '') === 'adjustment' 
                                           || ($entry->entry_type ?? '') === 'refund_credit'
                                           || str_contains($rawTextPdf, 'reversal')
                                           || str_contains($rawTextPdf, 'returned items')
                                           || str_contains($rawTextPdf, 'returned:');
                            
                            $modeKeyPdf = 'cash';
                            if ($isReversalPdf) {
                                $modeKeyPdf = 'reversal';
                            } elseif (in_array($rawModePdf, ['upi', 'online', 'gpay', 'phonepe', 'paytm']) || str_contains($rawTextPdf, '(upi)') || str_contains($rawTextPdf, '[upi portion]') || str_contains($rawTextPdf, 'gpay') || str_contains($rawTextPdf, 'phonepe') || str_contains($rawTextPdf, 'paytm') || str_contains($rawTextPdf, 'online')) {
                                $modeKeyPdf = 'upi';
                            } elseif ($rawModePdf === 'bank_transfer' || str_contains($rawTextPdf, 'bank')) {
                                $modeKeyPdf = 'bank';
                            } elseif ($rawModePdf === 'cheque' || str_contains($rawTextPdf, 'cheque')) {
                                $modeKeyPdf = 'cheque';
                            } elseif ($rawModePdf === 'split' || $rawModePdf === 'cash+upi') {
                                $modeKeyPdf = 'split';
                            } elseif ($rawModePdf === 'cash' || str_contains($rawTextPdf, '(cash)') || str_contains($rawTextPdf, '[cash portion]')) {
                                $modeKeyPdf = 'cash';
                            }
                        @endphp
                        <div style="font-weight: bold; color: {{ $isReversalPdf ? '#059669' : '#16a34a' }}; font-size: 10px;">
                            {{ $itemName ?: ($isReversalPdf ? 'Return Adjustment' : 'Khata Repayment Received') }}
                            @if(!$isReversalPdf)
                                @if($modeKeyPdf === 'cash')
                                    <span style="background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; font-size: 7.5px; font-weight: bold; padding: 1px 4px; border-radius: 2px; text-transform: uppercase; margin-left: 3px;">CASH</span>
                                @elseif($modeKeyPdf === 'upi')
                                    <span style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-size: 7.5px; font-weight: bold; padding: 1px 4px; border-radius: 2px; text-transform: uppercase; margin-left: 3px;">ONLINE (UPI)</span>
                                @elseif($modeKeyPdf === 'bank')
                                    <span style="background: #faf5ff; color: #7e22ce; border: 1px solid #e9d5ff; font-size: 7.5px; font-weight: bold; padding: 1px 4px; border-radius: 2px; text-transform: uppercase; margin-left: 3px;">BANK TRANSFER</span>
                                @elseif($modeKeyPdf === 'cheque')
                                    <span style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a; font-size: 7.5px; font-weight: bold; padding: 1px 4px; border-radius: 2px; text-transform: uppercase; margin-left: 3px;">CHEQUE</span>
                                @elseif($modeKeyPdf === 'split')
                                    <span style="background: #f0fdf4; color: #047857; border: 1px solid #a7f3d0; font-size: 7.5px; font-weight: bold; padding: 1px 4px; border-radius: 2px; text-transform: uppercase; margin-left: 3px;">CASH + ONLINE</span>
                                @endif
                            @else
                                <span style="background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; font-size: 7.5px; font-weight: bold; padding: 1px 4px; border-radius: 2px; text-transform: uppercase; margin-left: 3px;">RETURN CREDIT</span>
                            @endif
                        </div>
                        @if(!empty($entry->description) && !in_array($entry->description, [$itemName, $entry->particulars, $entry->ref_no, 'Payment / Settlement Received', 'Khata repayment received', 'Khata repayment received (Cash)', 'Khata repayment received (UPI)', 'Return Adjustment']))
                            <div style="font-size: 8.5px; color: #4b5563; margin-top: 1px;">
                                {{ $entry->description }}
                            </div>
                        @endif
                        @if(!empty($entry->ref_no))
                            <div style="font-size: 8.5px; color: #6b7280; font-family: monospace; margin-top: 1px;">
                                Ref: #{{ $entry->ref_no }}
                            </div>
                        @endif
                    @else
                        @php
                            $isVoidedEntryPdf = !empty($entry->is_voided) || str_contains($itemName, '[Full Invoice Returned]');
                            $cleanItemNamePdf = trim(str_replace('[Full Invoice Returned]', '', $itemName));
                        @endphp
                        <div style="font-weight: bold; color: #111827; font-size: 10px;">
                            {{ $cleanItemNamePdf ?: 'Counter Purchase' }}
                            @if($isVoidedEntryPdf)
                                <span style="color: #dc2626; font-size: 8.5px; font-weight: bold;">[FULL INVOICE RETURNED]</span>
                            @endif
                        </div>
                        @if(!empty($entry->ref_no))
                            <div style="font-size: 8.5px; color: #4338ca; font-family: monospace; font-weight: bold; margin-top: 1px;">
                                Bill #{{ $entry->ref_no }}
                            </div>
                        @endif
                    @endif
                </td>
                <td class="text-right font-mono" style="vertical-align: top;">
                    {{ $entry->billed > 0 ? number_format($entry->billed, 2) : '-' }}
                </td>
                <td class="text-right font-mono" style="vertical-align: top; color: #16a34a;">
                    {{ $entry->paid > 0 ? number_format($entry->paid, 2) : '-' }}
                </td>
                <td class="text-right font-mono font-bold" style="color: #111827; vertical-align: top;">
                    {{ number_format($entry->balance_left, 2) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- 5. SUMMARY TOTALS -->
    <table class="totals-table">
        @if(!empty($openingBalance) && $openingBalance > 0)
        <tr style="background-color: #fffbeb;">
            <td class="font-bold" style="color: #92400e; padding: 4px 6px;">Opening Balance Brought Forward</td>
            <td class="text-right font-mono font-bold" style="color: #92400e; font-size: 12px; padding: 4px 6px;">Rs. {{ number_format($openingBalance, 2) }}</td>
        </tr>
        @endif
        <tr>
            <td class="font-bold" style="color: #111827;">{{ (!empty($openingBalance) && $openingBalance > 0) ? 'Period Purchases / Debits' : 'Total Billed' }}</td>
            <td class="text-right font-mono font-bold" style="color: #111827; font-size: 12px;">Rs. {{ number_format($totalBilled, 2) }}</td>
        </tr>
        <tr>
            <td class="font-bold" style="color: #111827;">{{ (!empty($openingBalance) && $openingBalance > 0) ? 'Period Received / Paid' : 'Total Received / Paid' }}</td>
            <td class="text-right font-mono font-bold" style="color: #16a34a; font-size: 12px;">Rs. {{ number_format($totalPaid, 2) }}</td>
        </tr>
        @if($totalPaid > 0)
        <tr>
            <td colspan="2" style="text-align: right; font-size: 8.5px; color: #4b5563; padding-top: 1px; padding-bottom: 3px; font-family: monospace;">
                @php
                    $pdfBreakdown = [];
                    if (!empty($totalPaidCash) && $totalPaidCash > 0) $pdfBreakdown[] = 'Cash: Rs. ' . number_format($totalPaidCash, 2);
                    if (!empty($totalPaidOnline) && $totalPaidOnline > 0) $pdfBreakdown[] = 'Online (UPI): Rs. ' . number_format($totalPaidOnline, 2);
                    if (!empty($totalPaidBank) && $totalPaidBank > 0) $pdfBreakdown[] = 'Bank: Rs. ' . number_format($totalPaidBank, 2);
                    if (!empty($totalPaidCheque) && $totalPaidCheque > 0) $pdfBreakdown[] = 'Cheque: Rs. ' . number_format($totalPaidCheque, 2);
                    if (!empty($totalReturnCredits) && $totalReturnCredits > 0) $pdfBreakdown[] = 'Return Credits: Rs. ' . number_format($totalReturnCredits, 2);
                @endphp
                {{ implode('   •   ', $pdfBreakdown) }}
            </td>
        </tr>
        @endif
    </table>

    <!-- 6. NET OUTSTANDING BALANCE BOX -->
    <table class="balance-box">
        <tr>
            <td class="balance-title" style="vertical-align: middle;">Net Outstanding Balance</td>
            <td class="balance-amt font-mono" style="vertical-align: middle;">Rs. {{ number_format($closingBalance, 2) }}</td>
        </tr>
    </table>

    <!-- 7. FOOTER SECTION -->
    <div class="footer-section">
        <div>Computer-generated statement &bull; Report discrepancies within 7 business days</div>

        <div style="margin-top: 22px; font-weight: bold; color: #111827; font-size: 10.5px;">
            For {{ setting('company.name', 'PhoneFix Azamgarh') }}
        </div>

        <div class="sign-line"></div>
        <div style="font-size: 9px; color: #6b7280;">Authorised Signatory</div>
    </div>

</div>

</body>
</html>
