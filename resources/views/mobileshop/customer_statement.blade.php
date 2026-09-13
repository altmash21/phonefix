@extends('mobileshop.layout')

@section('title', 'Statement of Account — ' . $customer->name . ' — Maurya Mobile')
@section('page-title', 'Statement of Account')

@php
    $storeName = store_name();
    $storePhone = store_phone();
    $storeAddress = store_address();
    $storeGstin = store_gstin();
    $storeUpi = store_upi_id();
    $storeLandline = store_landline();

    $cleanPhone = preg_replace('/[^0-9]/', '', $customer->phone ?? '');
    if (strlen($cleanPhone) === 10) {
        $cleanPhone = '91' . $cleanPhone;
    }

    $waMsg = "📊 *STATEMENT OF ACCOUNT*\n";
    $waMsg .= "🏪 *{$storeName}*\n";
    $waMsg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    $waMsg .= "👤 *Account Holder:* *{$customer->name}*\n";
    if (!empty($customer->phone)) {
        $waMsg .= "📱 *Mobile:* {$customer->phone}\n";
    }
    $waMsg .= "📅 *Statement Date:* " . date('d M Y') . "\n";
    $waMsg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    $waMsg .= "📋 *CREDIT & TRANSACTION LEDGER*\n\n";
    
    foreach ($ledger as $item) {
        $part = $item->particulars ?: ($item->billed > 0 ? 'Bill #' . $item->ref_no : 'Khata Repayment Received');
        $waMsg .= "▪️ *{$item->date}* — {$part}\n";
        if ($item->billed > 0) {
            $waMsg .= "   • Billed: ₹" . number_format($item->billed, 2) . "\n";
        }
        if ($item->paid > 0) {
            $waMsg .= "   • Received: ₹" . number_format($item->paid, 2) . "\n";
        }
        $waMsg .= "   • Remaining Balance: *₹" . number_format($item->balance_left, 2) . "*\n\n";
    }
    
    $waMsg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    $waMsg .= "📈 *ACCOUNT FINANCIAL SUMMARY*\n";
    $waMsg .= "• *Total Credit Billed:* ₹" . number_format($totalBilled, 2) . "\n";
    $waMsg .= "• *Total Amount Received:* ₹" . number_format($totalPaid, 2) . "\n";
    $waMsg .= "👉 *NET OUTSTANDING BALANCE:* *₹" . number_format($closingBalance, 2) . "*\n";
    $waMsg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    if (!empty($storeUpi)) {
        $waMsg .= "💡 _Please clear the pending balance via UPI (`{$storeUpi}`) or Cash at our store counter at your earliest convenience._\n\n";
    } else {
        $waMsg .= "💡 _Please clear the pending balance via UPI or Cash at our store counter at your earliest convenience._\n\n";
    }
    $waMsg .= "📞 *Accounts Desk:* {$storePhone}\n";
    if (!empty($storeLandline)) {
        $waMsg .= "☎️ *Landline:* {$storeLandline}\n";
    }
    $waMsg .= "🏢 *Showroom:* {$storeAddress}\n";
    $waMsg .= "_Note: Please report any discrepancies within 7 business days._";

    $waStatementUrl = 'https://wa.me/' . $cleanPhone . '?text=' . rawurlencode($waMsg);
@endphp

@section('page-actions')
    <div style="display:flex; gap: 8px; align-items:center; flex-wrap:wrap;">
        <button onclick="window.print()" class="btn btn-outline btn-sm" style="font-weight:700; color:#0F172A; border-color:#94A3B8;">
            <i data-lucide="printer" style="width:13px;height:13px;"></i> Print / Save as PDF
        </button>
        <a href="{{ route('mobileshop.khata.customer_statement_pdf', ['id' => $customer->id]) }}" class="btn btn-primary btn-sm" style="font-weight:700; background:#5E6AD2;">
            <i data-lucide="download" style="width:13px;height:13px;"></i> Download PDF
        </a>
        <a href="{{ $waStatementUrl }}" target="_blank" class="btn btn-sm" style="font-weight:700; background:#25D366; color:#ffffff; display:inline-flex; align-items:center; gap:6px; border:none; box-shadow:0 2px 5px rgba(37,211,102,0.3);">
            <svg style="width:14px;height:14px;fill:currentColor;" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
            Send on WhatsApp
        </a>
        <a href="{{ route('mobileshop.khata') }}" class="btn btn-outline btn-sm" style="font-weight:700;">
            <i data-lucide="arrow-left" style="width:13px;height:13px;"></i> Back to Khata Hub
        </a>
    </div>
@endsection

@section('content')
<div class="statement-page-wrapper" style="width: 100%; margin: 0; padding-bottom: 40px; display: flex; justify-content: center;">

    <div class="printable-invoice-container" style="background: #ffffff; border: 1px solid #E5E7EB; border-radius: 6px; padding: 36px 40px; color: #111827; width: 100%; box-shadow: 0 4px 12px rgba(0,0,0,0.06); font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">

        <!-- 1. CENTERED STORE HEADER -->
        <div style="text-align: center; margin-bottom: 16px;">
            <div style="font-size: 22px; font-weight: 800; color: #111827; letter-spacing: -0.3px;">
                {{ $storeName }}
            </div>
            <div style="font-size: 12px; color: #6B7280; margin-top: 4px;">
                {{ $storeAddress }}
            </div>
            <div style="font-size: 11.5px; color: #6B7280; margin-top: 2px;">
                Phone: {{ $storePhone }} &middot; GSTIN: {{ $storeGstin }}
            </div>
        </div>

        <!-- DOTTED DIVIDER -->
        <div style="border-top: 1px dotted #9CA3AF; margin: 16px 0;"></div>

        <!-- 2. DOCUMENT TITLE -->
        <div style="text-align: center; margin-bottom: 20px;">
            <div style="font-size: 16px; font-weight: 800; color: #111827; letter-spacing: 0.5px; text-transform: uppercase;">
                Statement of account
            </div>
            <div style="font-size: 11.5px; color: #6B7280; margin-top: 3px;">
                As of {{ date('d M Y') }}
            </div>
        </div>

        <!-- 3. ACCOUNT HOLDER INFO -->
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 16px; font-size: 13px;">
            <tr>
                <td style="color: #6B7280; padding: 3px 0; width: 40%;">Account Holder</td>
                <td style="text-align: right; font-weight: 800; color: #111827; padding: 3px 0; width: 60%; font-size: 14px;">{{ $customer->name }}</td>
            </tr>
            <tr>
                <td style="color: #6B7280; padding: 3px 0;">Mobile Number</td>
                <td style="text-align: right; font-weight: 500; color: #111827; padding: 3px 0; font-family: monospace; font-size: 13.5px;">{{ $customer->phone ?: '—' }}</td>
            </tr>
        </table>

        <!-- 4. TRANSACTION LEDGER TABLE (5 COLUMNS: Total Amount, Amount Received, Balance) -->
        <table style="width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 18px;">
            <thead>
                <tr style="background: #F3F4F6; border-top: 1.5px solid #374151; border-bottom: 1.5px solid #374151;">
                    <th style="text-align: left; padding: 8px 6px; font-weight: 700; color: #1F2937; font-size: 11px; text-transform: uppercase; width: 12%;">Date</th>
                    <th style="text-align: left; padding: 8px 6px; font-weight: 700; color: #1F2937; font-size: 11px; text-transform: uppercase; width: 38%;">Particulars</th>
                    <th style="text-align: right; padding: 8px 6px; font-weight: 700; color: #1F2937; font-size: 11px; text-transform: uppercase; width: 16%;">Total Amount (₹)</th>
                    <th style="text-align: right; padding: 8px 6px; font-weight: 700; color: #1F2937; font-size: 11px; text-transform: uppercase; width: 17%;">Amount Received (₹)</th>
                    <th style="text-align: right; padding: 8px 6px; font-weight: 700; color: #1F2937; font-size: 11px; text-transform: uppercase; width: 17%;">Balance (₹)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ledger as $entry)
                @php
                    $isRepayment = $entry->paid > 0;
                    $particularsText = $entry->particulars ?: ($entry->billed > 0 ? 'Bill ' . $entry->ref_no : 'Khata repayment received');
                    $dateFormatted = date('d/m/Y', strtotime($entry->date));
                @endphp
                <tr style="border-bottom: 1px solid #E5E7EB;">
                    <td style="padding: 9px 6px; color: #4B5563; font-family: monospace; font-size: 12px;">
                        {{ $dateFormatted }}
                    </td>
                    <td style="padding: 9px 6px; {{ $isRepayment ? 'font-style: italic; color: #4B5563;' : 'font-weight: 500; color: #111827;' }}">
                        {{ $particularsText }}
                    </td>
                    <td style="padding: 9px 6px; text-align: right; font-family: monospace; font-size: 12px; color: #111827;">
                        {{ $entry->billed > 0 ? number_format($entry->billed, 2) : '—' }}
                    </td>
                    <td style="padding: 9px 6px; text-align: right; font-family: monospace; font-size: 12px; color: #111827;">
                        {{ $entry->paid > 0 ? number_format($entry->paid, 2) : '—' }}
                    </td>
                    <td style="padding: 9px 6px; text-align: right; font-family: monospace; font-size: 12.5px; font-weight: 800; color: #111827;">
                        {{ number_format($entry->balance_left, 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- 5. SUMMARY TOTALS -->
        <table style="width: 100%; border-collapse: collapse; font-size: 13px; margin-bottom: 20px;">
            <tr>
                <td style="font-weight: 800; color: #111827; padding: 4px 0;">Total Billed</td>
                <td style="text-align: right; font-family: monospace; font-size: 14px; font-weight: 700; color: #111827; padding: 4px 0;">
                    ₹{{ number_format($totalBilled, 2) }}
                </td>
            </tr>
            <tr>
                <td style="font-weight: 800; color: #111827; padding: 4px 0;">Total Received / Paid</td>
                <td style="text-align: right; font-family: monospace; font-size: 14px; font-weight: 700; color: #111827; padding: 4px 0;">
                    ₹{{ number_format($totalPaid, 2) }}
                </td>
            </tr>
        </table>

        <!-- 6. NET OUTSTANDING BALANCE BOX -->
        <div style="border: 1.5px solid #111827; border-radius: 3px; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; background: #F9FAFB;">
            <div style="font-size: 13px; color: #374151; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px;">
                Net Outstanding Balance
            </div>
            <div style="font-size: 22px; font-weight: 900; color: #111827; letter-spacing: -0.5px;">
                ₹{{ number_format($closingBalance, 2) }}
            </div>
        </div>

        <!-- 7. FOOTER SECTION -->
        <div style="text-align: center; margin-top: 24px; font-size: 11px; color: #6B7280; line-height: 1.6;">
            <div>Computer-generated statement &bull; Report discrepancies within 7 business days</div>

            <div style="margin-top: 24px; font-size: 12px; font-weight: 700; color: #111827;">
                For {{ $storeName }}
            </div>

            <div style="margin: 36px auto 6px; width: 220px; border-top: 1px solid #9CA3AF;"></div>
            <div style="font-size: 11px; color: #6B7280;">
                Authorised Signatory
            </div>
        </div>

</div>
@endsection

@push('styles')
<style>
    @media print {
        .printable-invoice-container {
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
        }
        .statement-page-wrapper {
            padding: 0 !important;
            margin: 0 !important;
            display: block !important;
        }
        table {
            border-color: #374151 !important;
        }
    }
</style>
@endpush
