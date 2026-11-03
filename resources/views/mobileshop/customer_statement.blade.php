@extends('mobileshop.layout')

@section('title', 'Statement of Account — ' . $customer->name . ' — PhoneFix Azamgarh')
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

    $currentFilter = $currentFilter ?? 'active';
    $periodLabel = $periodLabel ?? 'Active Unsettled Dues';
    $openingBalance = (float) ($openingBalance ?? 0.00);
    $hasPriorHistory = (bool) ($hasPriorHistory ?? false);
    $priorSettledCount = (int) ($priorSettledCount ?? 0);

    $pdfQuery = array_filter([
        'filter' => $currentFilter !== 'active' ? $currentFilter : null,
        'from'   => $fromDate ?? null,
        'to'     => $toDate ?? null,
    ]);
    $adminPdfUrl = route('mobileshop.khata.customer_statement_pdf', array_merge(['id' => $customer->id], $pdfQuery));
    $publicPdfUrl = route('public.statement.pdf', array_merge(['id' => $customer->id], $pdfQuery));
    $publicStatementUrl = route('public.statement.show', array_merge(['id' => $customer->id], $pdfQuery));

    $waMsg = "📊 *STATEMENT OF ACCOUNT*\n";
    $waMsg .= "🏪 *{$storeName}*\n";
    $waMsg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    $waMsg .= "👤 *Account Holder:* *{$customer->name}*\n";
    if (!empty($customer->phone)) {
        $waMsg .= "📱 *Mobile:* {$customer->phone}\n";
    }
    $waMsg .= "📅 *Period:* *{$periodLabel}*\n";
    $waMsg .= "📅 *Statement Date:* " . date('d M Y') . "\n";
    $waMsg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    $waMsg .= "📋 *CREDIT & TRANSACTION LEDGER*\n\n";
    
    if ($openingBalance > 0) {
        $waMsg .= "🏛️ *Opening Balance Brought Forward:* *₹" . number_format($openingBalance, 2) . "*\n\n";
    }

    foreach ($ledger as $item) {
        if (!empty($item->is_opening)) continue;
        $itemName = $item->item_name ?? $item->particulars;
        $refStr = !empty($item->ref_no) ? " [Ref #{$item->ref_no}]" : "";
        $isOldUdhar = in_array($item->entry_type ?? '', ['opening_balance', 'old_udhar', 'debit_adjustment']);
        $isRepay = ($item->paid > 0 && $item->billed <= 0) || in_array($item->entry_type ?? '', ['adjustment', 'refund_credit', 'payment_received']);
        
        if ($isOldUdhar) {
            $descStr = (!empty($item->description) && !in_array(strtolower($item->description), ['old udhar / opening balance', 'old udhar', 'opening balance']))
                ? " — Details: {$item->description}"
                : "";
            $part = "📜 Old Udhar{$refStr}{$descStr}";
        } elseif ($isRepay) {
            $part = "💵 " . ($itemName ?: 'Payment Received') . "{$refStr}";
        } else {
            $part = "🛍️ {$itemName}{$refStr}";
        }
        
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
    if ($openingBalance > 0) {
        $waMsg .= "• *Opening Balance Brought Forward:* ₹" . number_format($openingBalance, 2) . "\n";
        $waMsg .= "• *Period Billed:* ₹" . number_format($totalBilled, 2) . "\n";
        $waMsg .= "• *Period Received:* ₹" . number_format($totalPaid, 2) . "\n";
    } else {
        $waMsg .= "• *Total Credit Billed:* ₹" . number_format($totalBilled, 2) . "\n";
        $waMsg .= "• *Total Amount Received:* ₹" . number_format($totalPaid, 2) . "\n";
    }
    $waMsg .= "👉 *NET OUTSTANDING BALANCE:* *₹" . number_format($closingBalance, 2) . "*\n";
    $waMsg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    $waMsg .= "📄 *Download Complete PDF Statement:*\n" . $publicPdfUrl . "\n\n";
    if (!empty($storeUpi)) {
        $waMsg .= "💡 _Please clear the pending balance via UPI (`{$storeUpi}`) or Cash at our store counter at your earliest convenience._\n\n";
    } else {
        $waMsg .= "💡 _Please clear the pending balance via UPI or Cash at our store counter at your earliest convenience._\n\n";
    }
    $waMsg .= "📞 *Accounts Desk:* {$storePhone}\n";
    $waMsg .= "🏢 *Showroom:* {$storeAddress}\n";
    $waMsg .= "_Note: Please report any discrepancies within 7 business days._";

    $waStatementUrl = 'https://wa.me/' . $cleanPhone . '?text=' . rawurlencode($waMsg);
@endphp

@section('page-actions')
    <div style="display:flex; gap: 8px; align-items:center; flex-wrap:wrap;">
        <button onclick="window.print()" class="btn btn-outline btn-sm" style="font-weight:700; color:#0F172A; border-color:#94A3B8;">
            <i data-lucide="printer" style="width:13px;height:13px;"></i> Print / Save as PDF
        </button>
        <a href="{{ $adminPdfUrl }}" class="btn btn-primary btn-sm" style="font-weight:700; background:#5E6AD2;">
            <i data-lucide="download" style="width:13px;height:13px;"></i> Download PDF
        </a>
        <button type="button" id="btnSendWaPdf" onclick="shareStatementPdfWhatsApp()" class="btn btn-sm" style="font-weight:700; background:#25D366; color:#ffffff; display:inline-flex; align-items:center; gap:6px; border:none; box-shadow:0 2px 5px rgba(37,211,102,0.3); cursor:pointer;">
            <svg id="waBtnIcon" style="width:14px;height:14px;fill:currentColor;" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
            <span id="waBtnLabel">Send on WhatsApp</span>
        </button>
        <a href="{{ route('mobileshop.khata') }}" class="btn btn-outline btn-sm" style="font-weight:700;">
            <i data-lucide="arrow-left" style="width:13px;height:13px;"></i> Back to Khata Hub
        </a>
    </div>
@endsection

@section('content')
<div class="statement-page-wrapper" style="width: 100%; margin: 0; padding-bottom: 40px; display: flex; flex-direction: column; align-items: center;">

    <!-- Filter Presets & Date Picker Bar (Screen Only) -->
    <div class="statement-filter-bar no-print" style="width: 100%; background: #ffffff; border: 1px solid #E2E8F0; border-radius: 8px; padding: 12px 18px; margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
            <span style="font-size: 11.5px; font-weight: 800; color: #475569; text-transform: uppercase; margin-right: 4px; letter-spacing: 0.3px;">Period:</span>
            
            <a href="{{ route('mobileshop.khata.customer_statement', ['id' => $customer->id, 'filter' => 'active']) }}" 
               class="btn btn-sm {{ $currentFilter === 'active' ? 'btn-primary' : 'btn-outline' }}" 
               style="font-size: 11.5px; font-weight: 700; border-radius: 999px; padding: 4px 12px; text-decoration: none; @if($currentFilter === 'active') background: #16A34A; border-color: #16A34A; @endif"
               title="Current Unsettled Dues Only (Khatabook Style)">
                ⚡ Active Dues Only
            </a>

            <a href="{{ route('mobileshop.khata.customer_statement', ['id' => $customer->id, 'filter' => 'this_month']) }}" 
               class="btn btn-sm {{ $currentFilter === 'this_month' ? 'btn-primary' : 'btn-outline' }}" 
               style="font-size: 11.5px; font-weight: 700; border-radius: 999px; padding: 4px 12px; text-decoration: none; @if($currentFilter === 'this_month') background: #4F46E5; border-color: #4F46E5; @endif">
                📅 This Month
            </a>

            <a href="{{ route('mobileshop.khata.customer_statement', ['id' => $customer->id, 'filter' => 'last_30_days']) }}" 
               class="btn btn-sm {{ $currentFilter === 'last_30_days' ? 'btn-primary' : 'btn-outline' }}" 
               style="font-size: 11.5px; font-weight: 700; border-radius: 999px; padding: 4px 12px; text-decoration: none; @if($currentFilter === 'last_30_days') background: #4F46E5; border-color: #4F46E5; @endif">
                ⏳ Last 30 Days
            </a>

            <a href="{{ route('mobileshop.khata.customer_statement', ['id' => $customer->id, 'filter' => 'all']) }}" 
               class="btn btn-sm {{ $currentFilter === 'all' ? 'btn-primary' : 'btn-outline' }}" 
               style="font-size: 11.5px; font-weight: 700; border-radius: 999px; padding: 4px 12px; text-decoration: none; @if($currentFilter === 'all') background: #4F46E5; border-color: #4F46E5; @endif">
                📖 All Time
            </a>
        </div>

        <!-- Custom Date Range Picker Form -->
        <form method="GET" action="{{ route('mobileshop.khata.customer_statement', ['id' => $customer->id]) }}" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin: 0;">
            <input type="hidden" name="filter" value="custom">
            <div style="display: flex; align-items: center; gap: 4px; font-size: 11.5px; font-weight: 600; color: #475569;">
                <span>From:</span>
                <input type="date" name="from" value="{{ $fromDate ?: date('Y-m-01') }}" required class="form-control" style="font-size: 11.5px; padding: 4px 8px; height: 30px; width: 130px; border-radius: 6px; border: 1px solid #CBD5E1;">
            </div>
            <div style="display: flex; align-items: center; gap: 4px; font-size: 11.5px; font-weight: 600; color: #475569;">
                <span>To:</span>
                <input type="date" name="to" value="{{ $toDate ?: date('Y-m-d') }}" required class="form-control" style="font-size: 11.5px; padding: 4px 8px; height: 30px; width: 130px; border-radius: 6px; border: 1px solid #CBD5E1;">
            </div>
            <button type="submit" class="btn btn-sm {{ $currentFilter === 'custom' ? 'btn-primary' : 'btn-outline' }}" style="font-size: 11.5px; font-weight: 700; height: 30px; padding: 0 12px; border-radius: 6px;">
                Filter Range
            </button>
        </form>
    </div>

    @if($currentFilter === 'active' && $hasPriorHistory && $priorSettledCount > 0)
    <div class="no-print" style="width: 100%; background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 8px; padding: 10px 14px; margin-bottom: 16px; font-size: 12.5px; color: #1E40AF; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
        <div>
            ⚡ <strong>Current Unsettled Dues Only (Khatabook Style):</strong> Showing bills and repayments since the last zero balance. <strong>{{ $priorSettledCount }}</strong> earlier settled transactions are hidden.
        </div>
        <a href="{{ route('mobileshop.khata.customer_statement', ['id' => $customer->id, 'filter' => 'all']) }}" style="font-weight: 700; color: #2563EB; text-decoration: underline;">
            View Full History &rarr;
        </a>
    </div>
    @endif

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
                Phone: {{ $storePhone }}@if(!empty($storeGstin)) &middot; GSTIN: {{ $storeGstin }}@endif
            </div>
        </div>

        <!-- DOTTED DIVIDER -->
        <div style="border-top: 1px dotted #9CA3AF; margin: 16px 0;"></div>

        <!-- 2. DOCUMENT TITLE -->
        <div style="text-align: center; margin-bottom: 20px;">
            <div style="font-size: 16px; font-weight: 800; color: #111827; letter-spacing: 0.5px; text-transform: uppercase;">
                Statement of account
            </div>
            <div style="font-size: 12px; font-weight: 600; color: #4F46E5; margin-top: 3px;">
                {{ $periodLabel }} &bull; As of {{ date('d M Y') }}
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
                    <th style="text-align: left; padding: 8px 6px; font-weight: 700; color: #1F2937; font-size: 11px; text-transform: uppercase; width: 40%;">Items Purchased / Particulars</th>
                    <th style="text-align: right; padding: 8px 6px; font-weight: 700; color: #1F2937; font-size: 11px; text-transform: uppercase; width: 16%;">Total Amount (₹)</th>
                    <th style="text-align: right; padding: 8px 6px; font-weight: 700; color: #1F2937; font-size: 11px; text-transform: uppercase; width: 16%;">Amount Received (₹)</th>
                    <th style="text-align: right; padding: 8px 6px; font-weight: 700; color: #1F2937; font-size: 11px; text-transform: uppercase; width: 16%;">Balance (₹)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ledger as $entry)
                @php
                    $isOpening = !empty($entry->is_opening);
                    $isOldUdhar = in_array($entry->entry_type ?? '', ['opening_balance', 'old_udhar', 'debit_adjustment']) && !$isOpening;
                    $isRepayment = (($entry->paid > 0 && $entry->billed <= 0) || in_array($entry->entry_type ?? '', ['adjustment', 'refund_credit', 'payment_received'])) && !$isOpening;
                    $itemName = $entry->item_name ?? $entry->particulars;
                    $dateFormatted = !empty($entry->date) ? $entry->date : (isset($entry->created_at) ? date('d/m/Y', strtotime($entry->created_at)) : '—');
                @endphp
                @if($isOpening)
                <tr style="border-bottom: 1.5px solid #F59E0B; background: #FFFBEB;">
                    <td style="padding: 9px 6px; color: #92400E; font-family: monospace; font-size: 12px; vertical-align: top; font-weight: 700;">
                        {{ $dateFormatted }}
                    </td>
                    <td style="padding: 9px 6px; vertical-align: top;">
                        <div style="font-weight: 800; color: #B45309; font-size: 13px;">
                            🏛️ Opening Balance Brought Forward
                        </div>
                        <div style="font-size: 11px; color: #92400E; margin-top: 1px;">
                            Net balance accumulated prior to {{ $entry->date }}
                        </div>
                    </td>
                    <td style="padding: 9px 6px; text-align: right; font-family: monospace; font-size: 12.5px; color: #92400E; font-weight: 800; vertical-align: top;">
                        {{ number_format($entry->billed, 2) }}
                    </td>
                    <td style="padding: 9px 6px; text-align: right; font-family: monospace; font-size: 12px; color: #6B7280; vertical-align: top;">
                        —
                    </td>
                    <td style="padding: 9px 6px; text-align: right; font-family: monospace; font-size: 12.5px; font-weight: 900; color: #92400E; vertical-align: top;">
                        {{ number_format($entry->balance_left, 2) }}
                    </td>
                </tr>
                @continue
                @endif
                <tr style="border-bottom: 1px solid #E5E7EB;">
                    <td style="padding: 9px 6px; color: #4B5563; font-family: monospace; font-size: 12px; vertical-align: top;">
                        {{ $dateFormatted }}
                    </td>
                    <td style="padding: 9px 6px; vertical-align: top;">
                        @if($isOldUdhar)
                            <div style="font-weight: 800; color: #B45309; font-size: 13px;">
                                📜 Old Udhar / Opening Balance
                            </div>
                            @if(!empty($entry->description) && !in_array(strtolower($entry->description), ['old udhar / opening balance', 'old udhar', 'opening balance']))
                                <div style="font-size: 12px; color: #1E293B; margin-top: 2px; font-weight: 600;">
                                    <strong>Details:</strong> {{ $entry->description }}
                                </div>
                            @endif
                            @if(!empty($entry->ref_no))
                                <div style="font-size: 10.5px; color: #64748B; font-family: monospace; margin-top: 2px;">
                                    Ref: #{{ $entry->ref_no }}
                                </div>
                            @endif
                        @elseif($isRepayment)
                            <div style="color: #16A34A; font-weight: 600; font-size: 12.5px;">
                                💵 {{ $itemName ?: 'Payment / Settlement Received' }}
                            </div>
                            @if(!empty($entry->ref_no))
                                <div style="font-size: 10.5px; color: #64748B; font-family: monospace; margin-top: 2px;">
                                    Ref: #{{ $entry->ref_no }}
                                </div>
                            @endif
                        @else
                            <div style="font-weight: 700; color: #0F172A; font-size: 12.5px; line-height: 1.35;">
                                🛍️ {{ $itemName ?: 'Counter Purchase' }}
                            </div>
                            @if(!empty($entry->ref_no))
                                <div style="font-size: 11px; color: #4F46E5; font-family: monospace; font-weight: 700; margin-top: 2px;">
                                    Bill #{{ $entry->ref_no }}
                                </div>
                            @endif
                        @endif
                    </td>
                    <td style="padding: 9px 6px; text-align: right; font-family: monospace; font-size: 12px; color: #111827; vertical-align: top;">
                        {{ $entry->billed > 0 ? number_format($entry->billed, 2) : '—' }}
                    </td>
                    <td style="padding: 9px 6px; text-align: right; font-family: monospace; font-size: 12px; color: #16A34A; font-weight: 600; vertical-align: top;">
                        {{ $entry->paid > 0 ? number_format($entry->paid, 2) : '—' }}
                    </td>
                    <td style="padding: 9px 6px; text-align: right; font-family: monospace; font-size: 12.5px; font-weight: 800; color: #111827; vertical-align: top;">
                        {{ number_format($entry->balance_left, 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- 5. SUMMARY TOTALS -->
        <table style="width: 100%; border-collapse: collapse; font-size: 13px; margin-bottom: 20px;">
            @if($openingBalance > 0)
            <tr style="background: #FFFBEB; border-bottom: 1px solid #FEF3C7;">
                <td style="font-weight: 800; color: #92400E; padding: 5px 6px;">Opening Balance Brought Forward</td>
                <td style="text-align: right; font-family: monospace; font-size: 14px; font-weight: 800; color: #92400E; padding: 5px 6px;">
                    ₹{{ number_format($openingBalance, 2) }}
                </td>
            </tr>
            @endif
            <tr>
                <td style="font-weight: 800; color: #111827; padding: 4px 6px;">{{ $openingBalance > 0 ? 'Period Purchases / Debits' : 'Total Billed' }}</td>
                <td style="text-align: right; font-family: monospace; font-size: 14px; font-weight: 700; color: #111827; padding: 4px 6px;">
                    ₹{{ number_format($totalBilled, 2) }}
                </td>
            </tr>
            <tr>
                <td style="font-weight: 800; color: #111827; padding: 4px 6px;">{{ $openingBalance > 0 ? 'Period Amount Received' : 'Total Received / Paid' }}</td>
                <td style="text-align: right; font-family: monospace; font-size: 14px; font-weight: 700; color: #16A34A; padding: 4px 6px;">
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

<!-- DESKTOP WHATSAPP GUIDANCE MODAL -->
<div id="waDesktopModal" class="no-print" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.65); backdrop-filter:blur(4px); z-index:99999; align-items:center; justify-content:center; padding:16px;">
    <div style="background:#ffffff; border-radius:14px; max-width:480px; width:100%; padding:24px 26px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); border:1px solid #E2E8F0; text-align:center;">
        <div style="width:52px; height:52px; border-radius:50%; background:#DCFCE7; color:#16A34A; display:flex; align-items:center; justify-content:center; margin:0 auto 14px;">
            <svg style="width:28px;height:28px;fill:currentColor;" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
        </div>
        <h3 style="font-size:16px; font-weight:800; color:#0F172A; margin-bottom:6px;">PDF Statement Downloaded</h3>
        <p style="font-size:13px; color:#475569; line-height:1.5; margin-bottom:14px;">
            The PDF file <strong id="waModalFilename" style="color:#0F172A; word-break:break-all;">Statement.pdf</strong> has been downloaded to your computer and WhatsApp Web is opened.
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

@push('scripts')
<script>
    let activeWaTargetUrl = '';

    async function shareStatementPdfWhatsApp() {
        const btn = document.getElementById('btnSendWaPdf');
        const label = document.getElementById('waBtnLabel');
        const originalLabel = 'Send on WhatsApp';
        const pdfUrl = "{{ $adminPdfUrl }}";
        const customerPhone = "{{ $cleanPhone }}";
        const customerName = "{{ addslashes($customer->name) }}";
        const filename = "Statement-{{ \Illuminate\Support\Str::slug($customer->name, '_') }}-{{ $currentFilter === 'active' ? 'ActiveDues' : \Illuminate\Support\Str::slug($periodLabel, '_') }}.pdf";
        const waTextUrl = "{{ $waStatementUrl }}";
        const isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);

        if (btn) btn.disabled = true;
        if (label) label.textContent = 'Preparing PDF...';

        try {
            const resp = await fetch(pdfUrl);
            if (!resp.ok) throw new Error('PDF generation request failed.');
            const blob = await resp.blob();
            const file = new File([blob], filename, { type: 'application/pdf' });

            // 1. Mobile & Web Share API Level 2 (Direct PDF file attachment)
            if (navigator.canShare && navigator.canShare({ files: [file] })) {
                if (label) label.textContent = 'Opening WhatsApp...';
                await navigator.share({
                    title: 'Statement of Account - ' + customerName,
                    text: 'Statement of Account for ' + customerName + ' from {{ addslashes($storeName) }}. Balance Due: ₹{{ number_format($closingBalance, 2) }}',
                    files: [file]
                });
                if (label) label.textContent = 'Shared on WhatsApp';
                setTimeout(() => {
                    if (label) label.textContent = originalLabel;
                    if (btn) btn.disabled = false;
                }, 2000);
                return;
            }

            // 2. Desktop Fallback:
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
            console.warn('PDF share handling notice:', err);
            // If user simply closed the share sheet, do not navigate away
            if (err.name !== 'AbortError') {
                window.open(waTextUrl, '_blank');
            }
        } finally {
            if (btn) btn.disabled = false;
            if (label && label.textContent !== 'Shared on WhatsApp') {
                label.textContent = originalLabel;
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
