<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Statement of Account — {{ $customer->name }}</title>
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
        <div class="company-name">{{ setting('company.name', 'Maurya Mobile Store') }}</div>
        <div class="company-meta">{{ setting('company.address', 'Store Location, Commercial Complex') }}</div>
        <div class="company-meta">Phone: {{ setting('company.phone', '+91 98765 43210') }} &middot; GSTIN: {{ setting('company.tax_number', setting('company.gstin', '09AAACA1234F1Z5')) }}</div>
    </div>

    <!-- DOTTED DIVIDER -->
    <div class="dotted-line"></div>

    <!-- 2. DOCUMENT TITLE -->
    <div class="header-section" style="margin-bottom: 16px;">
        <div class="doc-title">Statement of account</div>
        <div class="doc-date">As of {{ date('d M Y') }}</div>
    </div>

    <!-- 3. ACCOUNT HOLDER METADATA -->
    <table class="info-table">
        <tr>
            <td style="color: #6b7280; width: 40%;">Account Holder</td>
            <td class="text-right font-bold" style="color: #111827; width: 60%; font-size: 12.5px;">{{ $customer->name }}</td>
        </tr>
        <tr>
            <td style="color: #6b7280;">Mobile Number</td>
            <td class="text-right font-mono" style="color: #111827;">{{ $customer->phone ?: '—' }}</td>
        </tr>
    </table>

    <!-- 4. TRANSACTION LEDGER TABLE (5 COLUMNS) -->
    <table class="ledger-table">
        <thead>
            <tr>
                <th class="text-left" style="width: 12%;">Date</th>
                <th class="text-left" style="width: 38%;">Particulars</th>
                <th class="text-right" style="width: 16%;">Total Amount (₹)</th>
                <th class="text-right" style="width: 17%;">Amount Received (₹)</th>
                <th class="text-right" style="width: 17%;">Balance (₹)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ledger as $entry)
            @php
                $isRepayment = $entry->paid > 0;
                $particularsText = $entry->particulars ?: ($entry->billed > 0 ? 'Bill ' . $entry->ref_no : 'Khata repayment received');
                $dateFormatted = date('d/m/Y', strtotime($entry->date));
            @endphp
            <tr>
                <td class="font-mono" style="color: #4b5563;">{{ $dateFormatted }}</td>
                <td style="{{ $isRepayment ? 'font-style: italic; color: #4b5563;' : 'font-weight: 500; color: #111827;' }}">
                    {{ $particularsText }}
                </td>
                <td class="text-right font-mono">
                    {{ $entry->billed > 0 ? number_format($entry->billed, 2) : '—' }}
                </td>
                <td class="text-right font-mono">
                    {{ $entry->paid > 0 ? number_format($entry->paid, 2) : '—' }}
                </td>
                <td class="text-right font-mono font-bold" style="color: #111827;">
                    {{ number_format($entry->balance_left, 2) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- 5. SUMMARY TOTALS -->
    <table class="totals-table">
        <tr>
            <td class="font-bold" style="color: #111827;">Total Billed</td>
            <td class="text-right font-mono font-bold" style="color: #111827; font-size: 12px;">₹{{ number_format($totalBilled, 2) }}</td>
        </tr>
        <tr>
            <td class="font-bold" style="color: #111827;">Total Received / Paid</td>
            <td class="text-right font-mono font-bold" style="color: #111827; font-size: 12px;">₹{{ number_format($totalPaid, 2) }}</td>
        </tr>
    </table>

    <!-- 6. NET OUTSTANDING BALANCE BOX -->
    <table class="balance-box">
        <tr>
            <td class="balance-title" style="vertical-align: middle;">Net Outstanding Balance</td>
            <td class="balance-amt font-mono" style="vertical-align: middle;">₹{{ number_format($closingBalance, 2) }}</td>
        </tr>
    </table>

    <!-- 7. FOOTER SECTION -->
    <div class="footer-section">
        <div>Computer-generated statement &bull; Report discrepancies within 7 business days</div>

        <div style="margin-top: 22px; font-weight: bold; color: #111827; font-size: 10.5px;">
            For {{ setting('company.name', 'Maurya Mobile Store') }}
        </div>

        <div class="sign-line"></div>
        <div style="font-size: 9px; color: #6b7280;">Authorised Signatory</div>
    </div>

</div>

</body>
</html>
