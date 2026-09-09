@extends('mobileshop.layout')

@section('title', ($sale->bill_type === 'non_gst' ? 'Estimate' : 'Tax Invoice') . ' #' . $sale->invoice_number . ' — Maurya Mobile')
@section('page-title', $sale->bill_type === 'non_gst' ? 'Estimate & Retail Bill' : 'Accessory Tax Invoice & Receipt')

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

    $cleanPhone = preg_replace('/[^0-9]/', '', $sale->customer_phone ?? '');
    if (strlen($cleanPhone) === 10) {
        $cleanPhone = '91' . $cleanPhone;
    }

    $isFullyPaid = ($sale->udhari_amount <= 0 && $sale->amount_paid >= $sale->total_amount);

    $waMsg = "🧾 *" . ($sale->bill_type === 'non_gst' ? 'ESTIMATE & RETAIL BILL' : 'TAX INVOICE RECEIPT') . "*\n";
    $waMsg .= "🏬 *{$storeName}*\n";
    $waMsg .= "------------------------------------\n";
    $waMsg .= "Hello *" . ($sale->customer_name ?: 'Valued Customer') . "*,\n";
    $waMsg .= "Thank you for shopping with us! Here is your bill summary:\n\n";
    $waMsg .= "📌 *Invoice #:* {$sale->invoice_number}\n";
    $waMsg .= "📅 *Date:* " . date('d M Y, h:i A', strtotime($sale->created_at)) . "\n";
    $waMsg .= "📦 *Items Purchased:*\n";
    foreach ($items as $idx => $it) {
        $waMsg .= "• {$it->part_name} (Qty: {$it->quantity}) - ₹" . number_format($it->line_total, 2) . "\n";
    }
    $waMsg .= "------------------------------------\n";
    $waMsg .= "💰 *This Bill Total:* ₹" . number_format($sale->total_amount, 2) . "\n";
    $waMsg .= "✅ *Amount Paid Now:* ₹" . number_format($sale->amount_paid, 2) . "\n";
    if ($sale->udhari_amount > 0) {
        $waMsg .= "⚠️ *This Bill Due:* ₹" . number_format($sale->udhari_amount, 2) . "\n";
    }

    if (!empty($customerStatement) && count($customerStatement['ledger']) > 0) {
        $waMsg .= "\n------------------------------------\n";
        $waMsg .= "📊 *CUSTOMER ACCOUNT STATEMENT (KHATA)*\n";
        $waMsg .= "------------------------------------\n";
        $waMsg .= "Date | Bill/Ref | Billed | Paid | Balance\n";
        foreach ($customerStatement['ledger'] as $entry) {
            $b = $entry->billed > 0 ? "₹" . number_format($entry->billed, 0) : "—";
            $p = $entry->paid > 0 ? "₹" . number_format($entry->paid, 0) : "—";
            $bl = "₹" . number_format($entry->balance_left, 0);
            $waMsg .= "{$entry->date} | {$entry->ref_no} | {$b} | {$p} | {$bl}\n";
        }
        $waMsg .= "------------------------------------\n";
        $waMsg .= "💰 *Total Billed:* ₹" . number_format($customerStatement['totalBilled'], 2) . "\n";
        $waMsg .= "✅ *Total Paid:*   ₹" . number_format($customerStatement['totalPaid'], 2) . "\n";
        $waMsg .= "🔴 *TOTAL NET BALANCE DUE:* ₹" . number_format($customerStatement['closingBalance'], 2) . "\n";
    }

    $waMsg .= "\n💳 *Mode:* " . strtoupper(str_replace('_', ' ', $sale->payment_mode)) . "\n";
    $waMsg .= "Thank you for your business! Visit us again soon.\n";
    $waMsg .= "📞 *Store Support:* {$storePhone}";

    $waInvoiceUrl = 'https://wa.me/' . $cleanPhone . '?text=' . rawurlencode($waMsg);
@endphp

@section('page-actions')
    <div style="display:flex; gap: 8px; align-items:center; flex-wrap:wrap;">
        <button type="button" onclick="switchFormat('a4')" id="btnA4" class="btn btn-primary btn-sm" style="font-weight:700;">
            Standard A4
        </button>
        <button type="button" onclick="switchFormat('thermal')" id="btnThermal" class="btn btn-outline btn-sm" style="font-weight:700;">
            80mm POS Thermal
        </button>
        <button onclick="window.print()" class="btn btn-outline btn-sm" style="font-weight:700; color:#0F172A; border-color:#CBD5E1;">
            <i data-lucide="printer" style="width:13px;height:13px;"></i> Print / Save as PDF
        </button>
        <a href="{{ route('mobileshop.accessories.invoice.pdf', ['id' => $sale->id]) }}" class="btn btn-primary btn-sm" style="font-weight:700; background:#5E6AD2;">
            <i data-lucide="download" style="width:13px;height:13px;"></i> Download PDF
        </a>
        <a href="{{ $waInvoiceUrl }}" target="_blank" class="btn btn-sm" style="font-weight:700; background:#25D366; color:#ffffff; display:inline-flex; align-items:center; gap:6px; border:none; box-shadow:0 2px 5px rgba(37,211,102,0.3);">
            <svg style="width:14px;height:14px;fill:currentColor;" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
            Share on WhatsApp
        </a>
        <a href="{{ route('mobileshop.sales') }}" class="btn btn-outline btn-sm" style="font-weight:700;">
            <i data-lucide="arrow-left" style="width:13px;height:13px;"></i> Back to Sales Hub
        </a>
    </div>
@endsection

@section('content')
<div class="invoice-page-wrapper" style="width: 100%; margin: 0; padding-bottom: 40px; display:flex; justify-content:center;">

    <!-- ─── DESIGNED A4 GST TAX INVOICE (ACCESSORIES & SPARE PARTS) ─── -->
    <div id="viewA4" class="printable-invoice-container" style="width: 100%; max-width: 860px; background: #ffffff; border: 1px solid #E2E8F0; border-radius: 12px; padding: 0; color: #0F172A; box-shadow: 0 4px 20px rgba(0,0,0,0.06); overflow: hidden; position: relative;">

        <!-- TOP VIBRANT ACCENT STRIP -->
        <div style="height: 5px; width: 100%; background: linear-gradient(90deg, #5E6AD2 0%, #7C3AED 40%, #2563EB 80%, #0F172A 100%);"></div>

        <div style="padding: 32px 36px;">

            <!-- ════ 1. HEADER SECTION (BRAND & DOCUMENT METADATA) ════ -->
            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 24px; padding-bottom: 24px; border-bottom: 1px solid #E2E8F0;">
                
                <!-- Left: Store Brand & Identity -->
                <div style="flex: 1; max-width: 58%;">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                        <div style="width: 42px; height: 42px; border-radius: 10px; background: #0F172A; color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 16px; letter-spacing: -0.5px; box-shadow: 0 2px 6px rgba(15,23,42,0.25);">
                            MM
                        </div>
                        <div>
                            <div style="font-size: 22px; font-weight: 800; color: #0F172A; letter-spacing: -0.5px; line-height: 1.1; text-transform: uppercase;">
                                {{ $storeName }}
                            </div>
                            <div style="font-size: 11px; font-weight: 600; color: #5E6AD2; letter-spacing: 0.5px; text-transform: uppercase; margin-top: 2px;">
                                Accessories, Spare Parts &amp; Express Repair Lab
                            </div>
                        </div>
                    </div>

                    <div style="font-size: 11.5px; color: #475569; line-height: 1.5; margin-top: 6px;">
                        {{ $storeAddress }}<br>
                        Phone: <strong style="color: #0F172A;">{{ $storePhone }}</strong> &bull; Email: <span style="color: #0F172A;">{{ $storeEmail }}</span>
                    </div>

                    <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px;">
                        <div style="display: inline-flex; align-items: center; gap: 5px; background: #F1F5F9; border: 1px solid #E2E8F0; padding: 3px 8px; border-radius: 6px; font-size: 10.5px; font-weight: 700; color: #0F172A;">
                            <span style="color: #64748B;">GSTIN:</span>
                            <span style="font-family: 'JetBrains Mono', monospace;">{{ $storeGstin }}</span>
                        </div>
                        <div style="display: inline-flex; align-items: center; gap: 4px; background: #F8FAFC; border: 1px solid #E2E8F0; padding: 3px 8px; border-radius: 6px; font-size: 10.5px; font-weight: 600; color: #475569;">
                            <span>State:</span>
                            <strong style="color: #0F172A;">{{ $storeState }} ({{ $storeStateCode }})</strong>
                        </div>
                    </div>
                </div>

                <!-- Right: Invoice Type, Number & Status Badge -->
                <div style="text-align: right; min-width: 38%;">
                    
                    <!-- Paid / Due Badge -->
                    <div style="margin-bottom: 8px;">
                        @if($isFullyPaid)
                            <span style="display: inline-flex; align-items: center; gap: 5px; background: #ECFDF5; color: #047857; border: 1px solid #A7F3D0; padding: 4px 12px; border-radius: 9999px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; box-shadow: 0 1px 2px rgba(16,185,129,0.1);">
                                <span style="width: 7px; height: 7px; border-radius: 50%; background: #10B981;"></span>
                                Paid in Full
                            </span>
                        @else
                            <span style="display: inline-flex; align-items: center; gap: 5px; background: #FFF1F2; color: #BE123C; border: 1px solid #FECDD3; padding: 4px 12px; border-radius: 9999px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">
                                <span style="width: 7px; height: 7px; border-radius: 50%; background: #E11D48;"></span>
                                Balance Due: ₹{{ number_format($sale->udhari_amount, 2) }}
                            </span>
                        @endif
                    </div>

                    <div style="font-size: 20px; font-weight: 900; color: #0F172A; letter-spacing: -0.2px; text-transform: uppercase; line-height: 1.2;">
                        {{ $sale->bill_type === 'non_gst' ? 'ESTIMATE & RETAIL BILL' : 'TAX INVOICE' }}
                    </div>
                    <div style="font-size: 10px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 1px; margin-top: 2px;">
                        Original for Recipient
                    </div>

                    <div style="margin-top: 10px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 8px 12px; display: inline-block; text-align: right;">
                        <div style="font-size: 10px; color: #64748B; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Invoice Number</div>
                        <div style="font-size: 15px; font-weight: 800; color: #0F172A; font-family: 'JetBrains Mono', monospace; letter-spacing: -0.3px; margin-top: 1px;">
                            {{ $sale->invoice_number }}
                        </div>
                    </div>

                    <div style="font-size: 11px; color: #64748B; margin-top: 8px;">
                        Date: <strong style="color:#0F172A;">{{ date('d M Y', strtotime($sale->created_at)) }}</strong>
                        &nbsp;&bull;&nbsp; Time: <span style="color:#0F172A;">{{ date('h:i A', strtotime($sale->created_at)) }}</span>
                    </div>
                </div>
            </div>

            <!-- ════ 2. PARTY & PAYMENT METRIC CARDS ════ -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 20px; margin-bottom: 24px;">
                
                <!-- Card 1: Billed To (Customer) -->
                <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px 16px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                        <span style="font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.8px; color: #5E6AD2;">
                            Billed To &bull; Customer Details
                        </span>
                        <span style="font-size: 10px; color: #64748B; background: #FFFFFF; border: 1px solid #E2E8F0; padding: 1px 6px; border-radius: 4px; font-weight: 600;">
                            Counter Bill
                        </span>
                    </div>
                    <div style="font-size: 16px; font-weight: 800; color: #0F172A; margin-bottom: 2px;">
                        {{ $sale->customer_name ?: 'Valued Retail Customer' }}
                    </div>
                    <div style="font-size: 12px; color: #334155; margin-bottom: 2px;">
                        Phone: <strong style="color: #0F172A;">{{ $sale->customer_phone ?: 'Walk-in Counter Customer' }}</strong>
                    </div>
                    @if($sale->customer_gstin)
                        <div style="font-size: 11px; color: #0F172A; font-weight: 700; margin-top: 3px; font-family: 'JetBrains Mono', monospace; background:#ffffff; display:inline-block; padding:1px 6px; border-radius:4px; border:1px solid #CBD5E1;">
                            GSTIN: {{ $sale->customer_gstin }}
                        </div>
                    @endif
                    <div style="font-size: 11px; color: #64748B; margin-top: 4px;">
                        {{ $sale->customer_address ?: 'Counter Collection · Mumbai Metro' }}
                    </div>
                    <div style="font-size: 10px; color: #64748B; margin-top: 4px; font-weight: 500;">
                        Place of Supply: <strong style="color: #0F172A;">{{ $storeState }} ({{ $storeStateCode }})</strong>
                    </div>
                </div>

                <!-- Card 2: Payment & Category Details -->
                <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px 16px; display: flex; flex-col; justify-content: space-between;">
                    <div>
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                            <span style="font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.8px; color: #5E6AD2;">
                                Payment &amp; Terms
                            </span>
                            <span style="font-size: 10px; font-weight: 700; background: #0F172A; color: #ffffff; padding: 2px 8px; border-radius: 4px; text-transform: uppercase;">
                                {{ strtoupper(str_replace('_', ' ', $sale->payment_mode)) }}
                            </span>
                        </div>

                        <div style="font-size: 12px; color: #334155; line-height: 1.6;">
                            <div>Payment Method: <strong style="color: #0F172A;">{{ strtoupper(str_replace('_', ' ', $sale->payment_mode)) }}</strong></div>

                            <div style="margin-top: 2px;">
                                @if($sale->bill_type === 'gst')
                                    Tax Regime: <strong style="color: #0F172A;">{{ ($sale->igst_amount ?? 0) > 0 ? 'INTER STATE (IGST)' : 'INTRA STATE (CGST + SGST)' }} @ 18% GST</strong>
                                @else
                                    Category: <strong style="color: #0F172A;">Retail / Non-GST Estimate</strong>
                                @endif
                            </div>

                            <div style="margin-top: 2px; font-size: 11px; color: #64748B;">
                                Store Guarantee: <strong style="color: #047857;">Original Genuine Accessories &bull; QC Tested</strong>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ════ 3. LINE ITEMS TABLE (ACCESSORIES & PARTS) ════ -->
            <div style="border: 1px solid #CBD5E1; border-radius: 8px; overflow: hidden; margin-bottom: 22px;">
                <table style="width: 100%; border-collapse: collapse; font-size: 11.5px;">
                    <thead>
                        <tr style="background: #0F172A; color: #FFFFFF;">
                            <th style="padding: 10px 12px; text-align: center; font-weight: 700; font-size: 9.5px; text-transform: uppercase; letter-spacing: 0.6px; width: 4%;">#</th>
                            <th style="padding: 10px 14px; text-align: left; font-weight: 700; font-size: 9.5px; text-transform: uppercase; letter-spacing: 0.6px; width: 44%;">Item Description / Spare Part</th>
                            <th style="padding: 10px 10px; text-align: center; font-weight: 700; font-size: 9.5px; text-transform: uppercase; letter-spacing: 0.6px; width: 11%;">HSN</th>
                            <th style="padding: 10px 8px; text-align: center; font-weight: 700; font-size: 9.5px; text-transform: uppercase; letter-spacing: 0.6px; width: 6%;">Qty</th>
                            <th style="padding: 10px 12px; text-align: right; font-weight: 700; font-size: 9.5px; text-transform: uppercase; letter-spacing: 0.6px; width: 12%;">Unit Price (₹)</th>
                            <th style="padding: 10px 12px; text-align: right; font-weight: 700; font-size: 9.5px; text-transform: uppercase; letter-spacing: 0.6px; width: 11%;">Taxable (₹)</th>
                            <th style="padding: 10px 14px; text-align: right; font-weight: 700; font-size: 9.5px; text-transform: uppercase; letter-spacing: 0.6px; width: 12%;">Total (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $idx => $it)
                        <tr style="border-bottom: 1px solid #E2E8F0; background: #FFFFFF;">
                            <td style="padding: 12px; text-align: center; font-weight: 700; color: #64748B;">{{ $idx + 1 }}</td>
                            <td style="padding: 12px 14px;">
                                <div style="font-size: 13px; font-weight: 800; color: #0F172A; letter-spacing: -0.2px;">
                                    {{ $it->part_name }}
                                </div>
                                <div style="display: flex; gap: 6px; margin-top: 3px;">
                                    <span style="background: #F1F5F9; color: #475569; font-size: 9.5px; font-weight: 600; padding: 1px 5px; border-radius: 4px; border: 1px solid #E2E8F0; text-transform: uppercase;">
                                        {{ $it->category ?? 'Accessory' }}
                                    </span>
                                    @if(isset($it->warranty_days) && $it->warranty_days > 0)
                                        <span style="background: #ECFDF5; color: #065F46; font-size: 9.5px; font-weight: 600; padding: 1px 5px; border-radius: 4px; border: 1px solid #A7F3D0;">
                                            ✓ {{ $it->warranty_days }} Days Warranty
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td style="padding: 12px 10px; text-align: center; font-family: 'JetBrains Mono', monospace; font-size: 11px; color: #334155;">
                                85177090
                            </td>
                            <td style="padding: 12px 8px; text-align: center; font-weight: 800; color: #0F172A; font-size: 12px;">
                                {{ $it->quantity }}
                            </td>
                            <td style="padding: 12px; text-align: right; font-family: 'JetBrains Mono', monospace; font-size: 11.5px; color: #334155;">
                                {{ number_format($it->unit_price, 2) }}
                            </td>
                            <td style="padding: 12px; text-align: right; font-family: 'JetBrains Mono', monospace; font-size: 11.5px; color: #334155;">
                                {{ number_format($sale->bill_type === 'gst' ? ($it->line_total / 1.18) : $it->line_total, 2) }}
                            </td>
                            <td style="padding: 12px 14px; text-align: right; font-family: 'JetBrains Mono', monospace; font-weight: 800; font-size: 13px; color: #0F172A;">
                                {{ number_format($it->line_total, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- ════ 4. FINANCIAL SUMMARY & TAX MATRIX ════ -->
            <div style="display: grid; grid-template-columns: 1.15fr 0.85fr; gap: 20px; align-items: flex-start; margin-bottom: 24px;">
                
                <!-- Left: Tax Matrix & Chargeable Words -->
                <div>
                    @if($sale->bill_type === 'gst')
                        <div style="font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.6px; color: #475569; margin-bottom: 6px;">
                            GST Tax Computation Matrix (@ 18.00%)
                        </div>
                        <div style="border: 1px solid #E2E8F0; border-radius: 8px; overflow: hidden; margin-bottom: 12px;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 10.5px;">
                                <thead>
                                    <tr style="background: #F8FAFC; border-bottom: 1px solid #E2E8F0; color: #475569;">
                                        <th style="padding: 6px 10px; text-align: left; font-weight: 700; font-size: 9.5px;">Tax Type</th>
                                        <th style="padding: 6px 10px; text-align: right; font-weight: 700; font-size: 9.5px;">Rate</th>
                                        <th style="padding: 6px 10px; text-align: right; font-weight: 700; font-size: 9.5px;">Taxable (₹)</th>
                                        <th style="padding: 6px 10px; text-align: right; font-weight: 700; font-size: 9.5px;">Tax Amount (₹)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(($sale->igst_amount ?? 0) <= 0)
                                        <tr style="border-bottom: 1px solid #F1F5F9;">
                                            <td style="padding: 6px 10px; font-weight: 600; color: #334155;">Central GST (CGST)</td>
                                            <td style="padding: 6px 10px; text-align: right; color: #64748B;">9.00%</td>
                                            <td style="padding: 6px 10px; text-align: right; font-family: 'JetBrains Mono', monospace; color: #334155;">{{ number_format($sale->total_amount / 1.18, 2) }}</td>
                                            <td style="padding: 6px 10px; text-align: right; font-family: 'JetBrains Mono', monospace; font-weight: 700; color: #0F172A;">{{ number_format($sale->cgst_amount, 2) }}</td>
                                        </tr>
                                        <tr style="border-bottom: 1px solid #F1F5F9;">
                                            <td style="padding: 6px 10px; font-weight: 600; color: #334155;">State GST (SGST)</td>
                                            <td style="padding: 6px 10px; text-align: right; color: #64748B;">9.00%</td>
                                            <td style="padding: 6px 10px; text-align: right; font-family: 'JetBrains Mono', monospace; color: #334155;">{{ number_format($sale->total_amount / 1.18, 2) }}</td>
                                            <td style="padding: 6px 10px; text-align: right; font-family: 'JetBrains Mono', monospace; font-weight: 700; color: #0F172A;">{{ number_format($sale->sgst_amount, 2) }}</td>
                                        </tr>
                                    @else
                                        <tr style="border-bottom: 1px solid #F1F5F9;">
                                            <td style="padding: 6px 10px; font-weight: 600; color: #334155;">Integrated GST (IGST)</td>
                                            <td style="padding: 6px 10px; text-align: right; color: #64748B;">18.00%</td>
                                            <td style="padding: 6px 10px; text-align: right; font-family: 'JetBrains Mono', monospace; color: #334155;">{{ number_format($sale->total_amount / 1.18, 2) }}</td>
                                            <td style="padding: 6px 10px; text-align: right; font-family: 'JetBrains Mono', monospace; font-weight: 700; color: #0F172A;">{{ number_format($sale->igst_amount, 2) }}</td>
                                        </tr>
                                    @endif
                                    <tr style="background: #F8FAFC; font-weight: 800;">
                                        <td colspan="3" style="padding: 6px 10px; text-align: right; color: #0F172A;">Total GST Collected</td>
                                        <td style="padding: 6px 10px; text-align: right; font-family: 'JetBrains Mono', monospace; color: #0F172A;">
                                            ₹{{ number_format($sale->cgst_amount + $sale->sgst_amount + $sale->igst_amount, 2) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <!-- Amount in Words Card -->
                    <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 10px 14px;">
                        <div style="font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.6px; color: #64748B;">
                            Invoice Total in Words:
                        </div>
                        <div style="font-size: 12px; font-weight: 700; color: #0F172A; margin-top: 2px; font-style: italic;">
                            {{ $amountInWords ?? 'Rupees Only' }}
                        </div>
                    </div>
                </div>

                <!-- Right: Financial Grand Totals Card -->
                <div style="background: #FFFFFF; border: 1px solid #CBD5E1; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                    
                    <div style="padding: 14px 16px; border-bottom: 1px solid #E2E8F0; background: #F8FAFC;">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; color: #475569; margin-bottom: 6px;">
                            <span>Subtotal (Taxable):</span>
                            <span style="font-family: 'JetBrains Mono', monospace; font-weight: 600; color: #0F172A;">
                                ₹{{ number_format($sale->bill_type === 'gst' ? ($sale->total_amount / 1.18) : $sale->total_amount, 2) }}
                            </span>
                        </div>

                        @if($sale->bill_type === 'gst')
                        <div style="display: flex; justify-content: space-between; font-size: 12px; color: #475569; margin-bottom: 6px;">
                            <span>GST (18% Total):</span>
                            <span style="font-family: 'JetBrains Mono', monospace; font-weight: 600; color: #0F172A;">
                                ₹{{ number_format($sale->cgst_amount + $sale->sgst_amount + $sale->igst_amount, 2) }}
                            </span>
                        </div>
                        @endif

                        <div style="display: flex; justify-content: space-between; font-size: 12px; color: #166534;">
                            <span>Discount / Promotion:</span>
                            <span style="font-family: 'JetBrains Mono', monospace; font-weight: 600;">₹0.00</span>
                        </div>
                    </div>

                    <!-- Grand Total Banner -->
                    <div style="background: #0F172A; color: #FFFFFF; padding: 14px 16px; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #94A3B8;">Grand Total</div>
                            <div style="font-size: 11px; color: #CBD5E1;">All taxes included</div>
                        </div>
                        <div style="font-size: 22px; font-weight: 900; font-family: 'JetBrains Mono', monospace; letter-spacing: -0.5px;">
                            ₹{{ number_format($sale->total_amount, 2) }}
                        </div>
                    </div>

                    <!-- Settlement Breakdown -->
                    <div style="padding: 12px 16px; background: #FAFAFA; font-size: 11.5px; border-top: 1px solid #E2E8F0;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                            <span style="color: #475569;">Amount Paid:</span>
                            <span style="font-family: 'JetBrains Mono', monospace; font-weight: 700; color: #059669;">
                                ₹{{ number_format($sale->amount_paid, 2) }}
                            </span>
                        </div>

                        @if($sale->udhari_amount > 0)
                            <div style="display: flex; justify-content: space-between; padding-top: 4px; border-top: 1px dashed #CBD5E1; color: #DC2626;">
                                <span style="font-weight: 700;">Remaining Balance (Udhari):</span>
                                <span style="font-family: 'JetBrains Mono', monospace; font-weight: 800;">
                                    ₹{{ number_format($sale->udhari_amount, 2) }}
                                </span>
                            </div>
                        @else
                            <div style="display: flex; justify-content: space-between; padding-top: 4px; border-top: 1px dashed #E2E8F0; color: #64748B; font-size: 11px;">
                                <span>Balance Remaining:</span>
                                <span style="font-family: 'JetBrains Mono', monospace; font-weight: 600; color: #059669;">₹0.00 (Settled)</span>
                            </div>
                        @endif
                    </div>
                </div>

            </div>

            <!-- ════ 5. STORE ASSURANCE & QUALITY BADGES ════ -->
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-bottom: 22px; padding: 10px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; text-align: center;">
                <div style="font-size: 11px; font-weight: 700; color: #0F172A; display: flex; align-items: center; justify-content: center; gap: 5px;">
                    <span>🛡️</span> 100% Genuine Certified Parts
                </div>
                <div style="font-size: 11px; font-weight: 700; color: #0F172A; display: flex; align-items: center; justify-content: center; gap: 5px; border-left: 1px solid #E2E8F0; border-right: 1px solid #E2E8F0;">
                    <span>⚡</span> Instant Counter Testing
                </div>
                <div style="font-size: 11px; font-weight: 700; color: #0F172A; display: flex; align-items: center; justify-content: center; gap: 5px;">
                    <span>🧾</span> GST Paid Legal Bill
                </div>
            </div>

            <!-- ════ 6. TERMS & SIGNATURE BLOCK ════ -->
            <div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 24px; padding-top: 16px; border-top: 1px solid #E2E8F0; align-items: flex-end;">
                
                <!-- Terms -->
                <div>
                    <div style="font-size: 9.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.6px; color: #64748B; margin-bottom: 4px;">
                        Terms &amp; Conditions of Sale
                    </div>
                    <ol style="font-size: 9px; color: #64748B; padding-left: 13px; line-height: 1.5; margin: 0;">
                        <li>Accessories &amp; replacement parts are tested at counter before handover.</li>
                        <li>Warranty on applicable chargers, cables, and parts as stated on bill.</li>
                        <li>Goods once sold are not refundable in cash. Physical damage voids warranty.</li>
                        <li>All disputes subject to local Mumbai jurisdiction.</li>
                    </ol>
                </div>

                <!-- Authorized Signatory Stamp -->
                <div style="text-align: right;">
                    <div style="font-size: 11px; font-weight: 800; color: #0F172A;">
                        For {{ $storeName }}
                    </div>
                    
                    <!-- Digital Seal Stamp Mockup -->
                    <div style="margin-top: 8px; margin-bottom: 6px; display: inline-flex; flex-direction: column; align-items: center; justify-content: center; width: 140px; height: 50px; border: 1.5px dashed #CBD5E1; border-radius: 6px; background: #F8FAFC;">
                        <span style="font-size: 8.5px; font-weight: 800; color: #5E6AD2; letter-spacing: 0.5px; text-transform: uppercase;">AUTHORIZED SEAL</span>
                        <span style="font-size: 9.5px; font-weight: 700; color: #0F172A; margin-top: 1px;">MAURYA MOBILE</span>
                    </div>

                    <div style="font-size: 10px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">
                        Authorised Signatory
                    </div>
                </div>

            </div>

            <!-- ════ 7. FOOTER BAR ════ -->
            <div style="margin-top: 24px; padding-top: 12px; border-top: 1px solid #F1F5F9; display: flex; justify-content: space-between; align-items: center; font-size: 9.5px; color: #94A3B8;">
                <div>
                    Computer generated tax invoice &bull; Original for Recipient &bull; Keep safe for warranty claims
                </div>
                <div style="font-family: 'JetBrains Mono', monospace; font-weight: 600;">
                    {{ $storeName }} &bull; Powered by Maurya Mobile Retail System
                </div>
            </div>

        </div>
    </div>

    <!-- ─── 80MM THERMAL RECEIPT (COUNTER POS) ─── -->
    <div id="viewThermal" class="card" style="display:none; width: 100%; max-width: 320px; margin: 0 auto; padding: 18px; font-family: 'Courier New', monospace; font-size: 11px; color: #111827; background:#fff; border:1px dashed #9CA3AF; border-radius: 6px;">
        <div style="text-align:center; margin-bottom: 8px;">
            <div style="font-weight:900; font-size:15px; text-transform:uppercase;">{{ $storeName }}</div>
            <div style="font-size:10px; color:#475569;">{{ $storeAddress }}</div>
            <div style="font-size:10px; color:#475569;">Ph: {{ $storePhone }}</div>
            <div style="font-size:10px; font-weight:700; color:#0F172A; margin-top:2px;">GSTIN: {{ $storeGstin }}</div>
        </div>
        <div style="border-top: 1px dashed #CBD5E1; padding-top: 6px; font-size:10px; line-height:1.4;">
            <div>Inv: <strong>{{ $sale->invoice_number }}</strong></div>
            <div>Date: {{ date('d-m-Y H:i', strtotime($sale->created_at)) }}</div>
            <div>Cust: {{ $sale->customer_name ?: 'Retail Customer' }} ({{ $sale->customer_phone }})</div>
        </div>
        <div style="border-top: 1px dashed #CBD5E1; padding-top: 6px; margin-top: 6px;">
            @foreach($items as $it)
            <div style="display:flex; justify-content:space-between; font-weight:700; margin-bottom:2px;">
                <span>{{ $it->part_name }} x{{ $it->quantity }}</span>
                <span>₹{{ number_format($it->line_total, 2) }}</span>
            </div>
            @endforeach
        </div>
        <div style="border-top: 1px dashed #CBD5E1; padding-top: 6px; margin-top: 6px; font-size:10px;">
            <div style="display:flex; justify-content:space-between;"><span>Taxable:</span><span>₹{{ number_format($sale->total_amount / 1.18, 2) }}</span></div>
            <div style="display:flex; justify-content:space-between;"><span>GST (18%):</span><span>₹{{ number_format($sale->cgst_amount + $sale->sgst_amount + $sale->igst_amount, 2) }}</span></div>
            <div style="display:flex; justify-content:space-between; font-weight:800; font-size:12px; border-top: 1px solid #CBD5E1; padding-top: 4px; margin-top: 4px;"><span>TOTAL:</span><span>₹{{ number_format($sale->total_amount, 2) }}</span></div>
            <div style="display:flex; justify-content:space-between; font-weight:700; color:#10B981;"><span>PAID:</span><span>₹{{ number_format($sale->amount_paid, 2) }}</span></div>
            @if($sale->udhari_amount > 0)
                <div style="display:flex; justify-content:space-between; font-weight:700; color:#DC2626;"><span>UDHARI DUE:</span><span>₹{{ number_format($sale->udhari_amount, 2) }}</span></div>
            @endif
        </div>
        <div style="border-top: 1px dashed #CBD5E1; padding-top: 8px; margin-top: 8px; text-align:center; font-size:9px; color:#6B7280; line-height:1.4;">
            <div>Mode: {{ strtoupper(str_replace('_', ' ', $sale->payment_mode)) }}</div>
            <div>Genuine Accessories &bull; QC Verified</div>
            <div style="font-weight:800; margin-top:4px;">*** THANK YOU - VISIT AGAIN ***</div>
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
    @media print {
        @page {
            size: A4 portrait;
            margin: 8mm 10mm 8mm 10mm;
        }
        body {
            background: #ffffff !important;
            color: #0F172A !important;
        }
        .page-header, .topbar, .sidebar, .sidebar-footer, #flash-msg, .no-print {
            display: none !important;
        }
        .content-area, .main-content, .invoice-page-wrapper {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
        }
        #viewA4 {
            border: none !important;
            box-shadow: none !important;
            border-radius: 0 !important;
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
        }
        #viewThermal {
            border: none !important;
            box-shadow: none !important;
            padding: 2mm 0 !important;
            margin: 0 auto !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    function switchFormat(mode) {
        const a4 = document.getElementById('viewA4');
        const thermal = document.getElementById('viewThermal');
        const btnA4 = document.getElementById('btnA4');
        const btnThermal = document.getElementById('btnThermal');
        let dynamicPageStyle = document.getElementById('dynamicThermalPageStyle');

        if (mode === 'thermal') {
            a4.style.display = 'none';
            thermal.style.display = 'block';
            document.body.classList.add('thermal-mode');
            if (btnThermal) btnThermal.className = 'btn btn-primary btn-sm';
            if (btnA4) btnA4.className = 'btn btn-outline btn-sm';

            if (!dynamicPageStyle) {
                dynamicPageStyle = document.createElement('style');
                dynamicPageStyle.id = 'dynamicThermalPageStyle';
                dynamicPageStyle.innerHTML = '@media print { @page { size: 80mm auto !important; margin: 2mm 3mm !important; } }';
                document.head.appendChild(dynamicPageStyle);
            }
        } else {
            thermal.style.display = 'none';
            a4.style.display = 'block';
            document.body.classList.remove('thermal-mode');
            if (btnA4) btnA4.className = 'btn btn-primary btn-sm';
            if (btnThermal) btnThermal.className = 'btn btn-outline btn-sm';

            if (dynamicPageStyle) {
                dynamicPageStyle.remove();
            }
        }
    }
</script>
@endpush
