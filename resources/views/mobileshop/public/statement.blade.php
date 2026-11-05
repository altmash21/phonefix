<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Statement — {{ $customer->name ?? 'Customer' }} — {{ store_name() }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #F1F5F9;
            color: #0F172A;
            line-height: 1.5;
            padding: 20px 12px 60px;
        }
        .statement-wrapper {
            max-width: 800px;
            margin: 0 auto;
        }
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 16px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 700;
            border-radius: 8px;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.15s ease;
        }
        .btn-primary {
            background: #4F46E5;
            color: #FFFFFF;
        }
        .btn-primary:hover { background: #4338CA; }
        .btn-outline {
            background: #FFFFFF;
            color: #334155;
            border: 1px solid #CBD5E1;
        }
        .btn-outline:hover { background: #F8FAFC; }
        .statement-card {
            background: #FFFFFF;
            border-radius: 12px;
            border: 1px solid #E2E8F0;
            box-shadow: 0 4px 15px -3px rgba(0,0,0,0.07);
            padding: 32px 36px;
        }
        @media (max-width: 640px) {
            body { padding: 10px 8px 40px; }
            .statement-card { padding: 20px 16px; border-radius: 10px; }
        }
        @media print {
            body { background: #fff; padding: 0; }
            .action-bar { display: none !important; }
            .statement-card { border: none !important; box-shadow: none !important; padding: 0 !important; }
        }
    </style>
</head>
<body>

<div class="statement-wrapper">

    <!-- Action Bar -->
    <div class="action-bar">
        <div style="font-size: 12px; font-weight: 700; color: #64748B;">
            Official Account Statement &bull; {{ store_name() }}
        </div>
        <div style="display:flex; gap:8px;">
            <button onclick="window.print()" class="btn btn-outline">
                <i data-lucide="printer" style="width:14px;height:14px;"></i> Print
            </button>
            <a href="{{ $pdfUrl ?? route('public.statement.pdf', ['id' => $customer->id]) }}" class="btn btn-primary">
                <i data-lucide="download" style="width:14px;height:14px;"></i> Download PDF
            </a>
        </div>
    </div>

    <!-- Filter Presets & Date Range Selector (Screen Only) -->
    <div class="action-bar" style="background:#ffffff; border:1px solid #E2E8F0; border-radius:10px; padding:10px 14px; margin-bottom:14px; justify-content:space-between;">
        <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
            <span style="font-size:11px; font-weight:800; color:#64748B; text-transform:uppercase;">Period:</span>
            
            <a href="{{ route('public.statement.show', ['id' => $customer->id, 'filter' => 'active']) }}" 
               class="btn {{ ($currentFilter ?? 'active') === 'active' ? 'btn-primary' : 'btn-outline' }}" 
               style="font-size:11.5px; padding:4px 10px; border-radius:999px; text-decoration:none; @if(($currentFilter ?? 'active') === 'active') background:#16A34A; @endif">
                ⚡ Active Dues
            </a>

            <a href="{{ route('public.statement.show', ['id' => $customer->id, 'filter' => 'last_payment']) }}" 
               class="btn {{ ($currentFilter ?? '') === 'last_payment' ? 'btn-primary' : 'btn-outline' }}" 
               style="font-size:11.5px; padding:4px 10px; border-radius:999px; text-decoration:none; @if(($currentFilter ?? '') === 'last_payment') background:#059669; @endif"
               title="From Last Payment Received (Includes active purchases and payment received)">
                💵 From Last Payment
            </a>

            <a href="{{ route('public.statement.show', ['id' => $customer->id, 'filter' => 'this_month']) }}" 
               class="btn {{ ($currentFilter ?? '') === 'this_month' ? 'btn-primary' : 'btn-outline' }}" 
               style="font-size:11.5px; padding:4px 10px; border-radius:999px; text-decoration:none;">
                📅 This Month
            </a>

            <a href="{{ route('public.statement.show', ['id' => $customer->id, 'filter' => 'last_30_days']) }}" 
               class="btn {{ ($currentFilter ?? '') === 'last_30_days' ? 'btn-primary' : 'btn-outline' }}" 
               style="font-size:11.5px; padding:4px 10px; border-radius:999px; text-decoration:none;">
                ⏳ Last 30 Days
            </a>

            <a href="{{ route('public.statement.show', ['id' => $customer->id, 'filter' => 'all']) }}" 
               class="btn {{ ($currentFilter ?? '') === 'all' ? 'btn-primary' : 'btn-outline' }}" 
               style="font-size:11.5px; padding:4px 10px; border-radius:999px; text-decoration:none;">
                📖 All Time
            </a>
        </div>

        <form method="GET" action="{{ route('public.statement.show', ['id' => $customer->id]) }}" style="display:flex; align-items:center; gap:6px; flex-wrap:wrap; margin:0;">
            <input type="hidden" name="filter" value="custom">
            <input type="date" name="from" value="{{ $fromDate ?? date('Y-m-01') }}" required style="font-size:11px; padding:3px 6px; border:1px solid #CBD5E1; border-radius:6px;">
            <span style="font-size:11px; color:#64748B;">to</span>
            <input type="date" name="to" value="{{ $toDate ?? date('Y-m-d') }}" required style="font-size:11px; padding:3px 6px; border:1px solid #CBD5E1; border-radius:6px;">
            <button type="submit" class="btn btn-outline" style="font-size:11px; padding:4px 10px;">Filter</button>
        </form>
    </div>

    <!-- Main Card -->
    <div class="statement-card">
        <!-- Store Header -->
        <div style="text-align: center; margin-bottom: 20px;">
            <div style="font-size: 22px; font-weight: 900; color: #0F172A; letter-spacing: -0.5px;">
                {{ store_name('PhoneFix Azamgarh') }}
            </div>
            <div style="font-size: 12.5px; color: #475569; margin-top: 4px;">
                {{ store_address() }}
            </div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">
                Phone: {{ store_phone() }} @if(store_gstin()) &bull; GSTIN: {{ store_gstin() }} @endif
            </div>
        </div>

        <div style="border-top: 1px dotted #CBD5E1; margin: 16px 0;"></div>

        <!-- Document Heading -->
        <div style="display:flex; justify-content:space-between; align-items:flex-end; flex-wrap:wrap; gap:10px; margin-bottom: 20px;">
            <div>
                <div style="font-size: 11px; font-weight: 800; color: #4F46E5; text-transform: uppercase; letter-spacing: 0.5px;">Account Statement</div>
                <div style="font-size: 18px; font-weight: 800; color: #0F172A; margin-top: 2px;">
                    {{ $customer->name }}
                </div>
                <div style="font-size: 12.5px; color: #64748B; font-family: monospace; margin-top: 2px;">
                    Mobile: {{ $customer->phone ?: '—' }}
                </div>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">{{ $periodLabel ?? 'Active Unsettled Dues' }}</div>
                <div style="font-size: 14px; font-weight: 800; color: #0F172A; margin-top: 2px;">As of {{ date('d M Y') }}</div>
            </div>
        </div>

        <!-- Ledger Table -->
        <div style="overflow-x: auto; margin-bottom: 20px;">
            <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                <thead>
                    <tr style="background: #F8FAFC; border-top: 1.5px solid #0F172A; border-bottom: 1.5px solid #0F172A;">
                        <th style="text-align: left; padding: 10px 8px; font-weight: 800; color: #0F172A; font-size: 11px; text-transform: uppercase; width: 14%;">Date</th>
                        <th style="text-align: left; padding: 10px 8px; font-weight: 800; color: #0F172A; font-size: 11px; text-transform: uppercase;">Particulars &amp; Details</th>
                        <th style="text-align: right; padding: 10px 8px; font-weight: 800; color: #0F172A; font-size: 11px; text-transform: uppercase; width: 16%;">Billed (₹)</th>
                        <th style="text-align: right; padding: 10px 8px; font-weight: 800; color: #0F172A; font-size: 11px; text-transform: uppercase; width: 16%;">Received (₹)</th>
                        <th style="text-align: right; padding: 10px 8px; font-weight: 800; color: #0F172A; font-size: 11px; text-transform: uppercase; width: 16%;">Balance (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ledger as $entry)
                        @php
                            $isOpening = !empty($entry->is_opening);
                            $isOldUdhar = in_array($entry->entry_type ?? '', ['opening_balance', 'old_udhar', 'debit_adjustment']) && !$isOpening;
                            $isRepayment = (($entry->paid > 0 && $entry->billed <= 0) || in_array($entry->entry_type ?? '', ['adjustment', 'refund_credit', 'payment_received'])) && !$isOpening;
                            $itemName = $entry->item_name ?? $entry->particulars;
                            $dateFormatted = !empty($entry->date) ? $entry->date : (isset($entry->created_at) ? date('d/m/Y', strtotime($entry->created_at)) : '—');
                        @endphp
                        @if($isOpening)
                        <tr style="border-bottom: 1.5px solid #F59E0B; background: #FFFBEB;">
                            <td style="padding: 10px 8px; color: #92400E; font-family: monospace; font-size: 12px; vertical-align: top; font-weight: 700;">
                                {{ $dateFormatted }}
                            </td>
                            <td style="padding: 10px 8px; vertical-align: top;">
                                <div style="font-weight: 800; color: #B45309; font-size: 13px;">
                                    🏛️ Opening Balance Brought Forward
                                </div>
                                <div style="font-size: 11px; color: #92400E; margin-top: 1px;">
                                    Net balance accumulated prior to {{ $entry->date }}
                                </div>
                            </td>
                            <td style="padding: 10px 8px; text-align: right; font-family: monospace; font-size: 12.5px; color: #92400E; font-weight: 800; vertical-align: top;">
                                {{ number_format($entry->billed, 2) }}
                            </td>
                            <td style="padding: 10px 8px; text-align: right; font-family: monospace; font-size: 12px; color: #64748B; vertical-align: top;">
                                —
                            </td>
                            <td style="padding: 10px 8px; text-align: right; font-family: monospace; font-size: 13px; font-weight: 900; color: #92400E; vertical-align: top;">
                                ₹{{ number_format($entry->balance_left, 2) }}
                            </td>
                        </tr>
                        @continue
                        @endif
                        <tr style="border-bottom: 1px solid #E2E8F0;">
                            <td style="padding: 10px 8px; color: #475569; font-family: monospace; font-size: 12px; vertical-align: top;">
                                {{ $dateFormatted }}
                            </td>
                            <td style="padding: 10px 8px; vertical-align: top;">
                                @if($isOldUdhar)
                                    <div style="font-weight: 800; color: #B45309; font-size: 13px;">
                                        📜 Old Udhar / Opening Balance
                                    </div>
                                    @if(!empty($entry->description) && !in_array(strtolower($entry->description), ['old udhar / opening balance', 'old udhar', 'opening balance']))
                                        <div style="font-size: 12px; color: #1E293B; margin-top: 2px; font-weight: 600;">
                                            {{ $entry->description }}
                                        </div>
                                    @endif
                                    @if(!empty($entry->ref_no))
                                        <div style="font-size: 11px; color: #64748B; font-family: monospace; margin-top: 2px;">
                                            Ref: #{{ $entry->ref_no }}
                                        </div>
                                    @endif
                                @elseif($isRepayment)
                                    <div style="color: #16A34A; font-weight: 700; font-size: 13px;">
                                        💵 {{ $itemName ?: 'Payment / Settlement Received' }}
                                    </div>
                                    @if(!empty($entry->description) && !in_array($entry->description, [$itemName, $entry->particulars, $entry->ref_no, 'Payment / Settlement Received', 'Khata repayment received', 'Khata repayment received (Cash)', 'Khata repayment received (UPI)']))
                                        <div style="font-size: 11px; color: #475569; margin-top: 1px; font-weight: 500;">
                                            {{ $entry->description }}
                                        </div>
                                    @endif
                                    @if(!empty($entry->ref_no))
                                        <div style="font-size: 11px; color: #64748B; font-family: monospace; margin-top: 2px;">
                                            Ref: #{{ $entry->ref_no }}
                                        </div>
                                    @endif
                                @else
                                    <div style="font-weight: 700; color: #0F172A; font-size: 13px;">
                                        🛍️ {{ $itemName ?: 'Counter Purchase' }}
                                    </div>
                                    @if(!empty($entry->ref_no))
                                        <div style="font-size: 11.5px; color: #4F46E5; font-family: monospace; font-weight: 700; margin-top: 2px;">
                                            Bill #{{ $entry->ref_no }}
                                        </div>
                                    @endif
                                @endif
                            </td>
                            <td style="padding: 10px 8px; text-align: right; font-family: monospace; font-size: 12.5px; color: #0F172A; vertical-align: top;">
                                {{ $entry->billed > 0 ? number_format($entry->billed, 2) : '—' }}
                            </td>
                            <td style="padding: 10px 8px; text-align: right; font-family: monospace; font-size: 12.5px; color: #16A34A; font-weight: 700; vertical-align: top;">
                                {{ $entry->paid > 0 ? number_format($entry->paid, 2) : '—' }}
                            </td>
                            <td style="padding: 10px 8px; text-align: right; font-family: monospace; font-size: 13px; font-weight: 900; color: #0F172A; vertical-align: top;">
                                ₹{{ number_format($entry->balance_left, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 24px; color: #94A3B8;">No transactions found on this account.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Summary Totals Table -->
        <table style="width: 100%; border-collapse: collapse; font-size: 13px; margin-bottom: 20px;">
            @if(!empty($openingBalance) && $openingBalance > 0)
            <tr style="background:#FFFBEB;">
                <td style="font-weight: 700; color: #92400E; padding: 5px 6px;">Opening Balance Brought Forward:</td>
                <td style="text-align: right; font-family: monospace; font-size: 14px; font-weight: 700; color: #92400E; padding: 5px 6px;">
                    ₹{{ number_format($openingBalance, 2) }}
                </td>
            </tr>
            @endif
            <tr>
                <td style="font-weight: 700; color: #475569; padding: 4px 0;">{{ (!empty($openingBalance) && $openingBalance > 0) ? 'Period Purchases / Billed:' : 'Total Credit Billed:' }}</td>
                <td style="text-align: right; font-family: monospace; font-size: 14px; font-weight: 700; color: #0F172A; padding: 4px 0;">
                    ₹{{ number_format($totalBilled, 2) }}
                </td>
            </tr>
            <tr>
                <td style="font-weight: 700; color: #475569; padding: 4px 0;">{{ (!empty($openingBalance) && $openingBalance > 0) ? 'Period Received / Settled:' : 'Total Received / Settled:' }}</td>
                <td style="text-align: right; font-family: monospace; font-size: 14px; font-weight: 700; color: #16A34A; padding: 4px 0;">
                    ₹{{ number_format($totalPaid, 2) }}
                </td>
            </tr>
        </table>

        <!-- Outstanding Due Box -->
        <div style="border: 1.5px solid #0F172A; border-radius: 8px; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; background: #F8FAFC;">
            <div>
                <div style="font-size: 12px; color: #64748B; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">
                    Current Outstanding Balance Due
                </div>
                <div style="font-size: 11px; color: #94A3B8; margin-top: 2px;">Please clear via UPI or Store Counter</div>
            </div>
            <div style="font-size: 24px; font-weight: 900; color: {{ $closingBalance > 0 ? '#DC2626' : '#16A34A' }}; font-family: monospace;">
                ₹{{ number_format($closingBalance, 2) }}
            </div>
        </div>

        @if(store_upi_id())
        <div style="background:#EEF2FF; border:1px solid #C7D2FE; border-radius:8px; padding:12px 14px; margin-bottom:20px; font-size:12px; color:#312E81; text-align:center;">
            💳 Pay instantly via UPI: <strong>{{ store_upi_id() }}</strong>
        </div>
        @endif

        <!-- Footer -->
        <div style="text-align: center; margin-top: 24px; font-size: 11px; color: #94A3B8;">
            <div>Computer generated statement &bull; Report any discrepancy within 7 business days.</div>
            <div style="font-weight: 700; color: #475569; margin-top: 6px;">{{ store_name() }}</div>
        </div>
    </div>
</div>

<script>
    lucide.createIcons();
</script>
</body>
</html>
