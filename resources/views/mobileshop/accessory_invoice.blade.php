@extends('mobileshop.layout')

@section('title', ($sale->bill_type === 'non_gst' ? 'Estimate' : 'Tax Invoice') . ' #' . $sale->invoice_number . ' — Maurya Mobile')
@section('page-title', $sale->bill_type === 'non_gst' ? 'Estimate & Retail Bill' : 'Accessory Tax Invoice & Receipt')

@php
    $storeName = setting('company.name', 'Maurya Mobile Retail Store');
    $storePhone = setting('company.phone', '+91 98765 43210');
    $cleanPhone = preg_replace('/[^0-9]/', '', $sale->customer_phone ?? '');
    if (strlen($cleanPhone) === 10) {
        $cleanPhone = '91' . $cleanPhone;
    }

    $waMsg = "🧾 *" . ($sale->bill_type === 'non_gst' ? 'ESTIMATE & RETAIL BILL' : 'TAX INVOICE & RECEIPT') . "*\n";
    $waMsg .= "🏪 *{$storeName}*\n";
    $waMsg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    $waMsg .= "Dear *" . ($sale->customer_name ?: 'Valued Customer') . "*,\n";
    $waMsg .= "Thank you for shopping at *{$storeName}*! Here is your itemized bill:\n\n";
    
    $waMsg .= "📋 *INVOICE DETAILS*\n";
    $waMsg .= "• *Invoice #:* {$sale->invoice_number}\n";
    $waMsg .= "• *Date:* " . date('d M Y, h:i A', strtotime($sale->created_at)) . "\n\n";
    
    $waMsg .= "📦 *ITEMS PURCHASED*\n";
    foreach ($items as $idx => $it) {
        $waMsg .= "• {$it->part_name} × {$it->quantity} — ₹" . number_format($it->line_total, 2) . "\n";
    }
    
    $waMsg .= "\n💰 *PAYMENT BREAKDOWN*\n";
    $waMsg .= "• *Total Bill Amount:* ₹" . number_format($sale->total_amount, 2) . "\n";
    $waMsg .= "• *Amount Paid Now:* ₹" . number_format($sale->amount_paid, 2) . " (" . strtoupper(str_replace('_', ' ', $sale->payment_mode)) . ")\n";
    if ($sale->udhari_amount > 0) {
        $waMsg .= "• *Balance Due (Udhari):* *₹" . number_format($sale->udhari_amount, 2) . "*\n";
    }

    if (!empty($customerStatement) && count($customerStatement['ledger']) > 0) {
        $waMsg .= "\n━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $waMsg .= "📊 *CUSTOMER KHATA STATEMENT SUMMARY*\n\n";
        foreach ($customerStatement['ledger'] as $entry) {
            $b = $entry->billed > 0 ? "Billed: ₹" . number_format($entry->billed, 2) : "";
            $p = $entry->paid > 0 ? "Paid: ₹" . number_format($entry->paid, 2) : "";
            $bp = array_filter([$b, $p]);
            $waMsg .= "▪️ {$entry->date} — {$entry->ref_no}\n";
            $waMsg .= "   " . implode(' | ', $bp) . " → Bal: ₹" . number_format($entry->balance_left, 2) . "\n";
        }
        $waMsg .= "\n👉 *TOTAL OUTSTANDING BALANCE:* *₹" . number_format($customerStatement['closingBalance'], 2) . "*\n";
    }

    $waMsg .= "\n━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    $waMsg .= "📞 *Store Support:* {$storePhone}\n";
    $waMsg .= "🏢 *Showroom:* Shop #14, Linking Road, Bandra West, Mumbai\n";
    $waMsg .= "Thank you for choosing Maurya Mobile! Visit us again.";

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
        <button onclick="window.print()" class="btn btn-outline btn-sm" style="font-weight:700; color:#0F172A; border-color:#94A3B8;">
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
<div class="invoice-page-wrapper" style="width: 100%; margin: 0; padding-bottom: 40px;">

    <!-- ─── STANDARD A4 GST TAX INVOICE (ACCESSORIES) ─── -->
    <div id="viewA4" class="printable-invoice-container" style="background: #ffffff; border: 1px solid #D1D5DB; border-radius: 4px; padding: 28px 32px; color: #111827; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">

        <!-- TOP CORPORATE HEADER -->
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; border-bottom: 2px solid #1F2937; padding-bottom: 16px;">
            <tr>
                <td style="vertical-align: top; width: 55%; padding-bottom: 12px;">
                    <div style="font-size: 20px; font-weight: 800; color: #111827; letter-spacing: -0.3px; text-transform: uppercase;">
                        {{ setting('company.name', 'Maurya Mobile Retail Store') }}
                    </div>
                    <div style="font-size: 11px; color: #4B5563; margin-top: 4px; line-height: 1.5;">
                        {{ setting('company.address', 'Store Location, Commercial Complex') }}<br>
                        Phone: {{ setting('company.phone', '+91 98765 43210') }} &bull; Email: {{ setting('company.email', 'support@mobitrack.local') }}
                    </div>
                    <div style="font-size: 11px; font-weight: 700; color: #111827; margin-top: 4px;">
                        GSTIN: <span style="font-family: monospace; font-weight: 700;">{{ setting('company.tax_number', setting('company.gstin', '09AAACA1234F1Z5')) }}</span>
                        &nbsp;|&nbsp; State: {{ setting('company.state', 'Uttar Pradesh') }} (09)
                    </div>
                </td>
                <td style="vertical-align: top; width: 45%; text-align: right; padding-bottom: 12px;">
                    <div style="font-size: 18px; font-weight: 800; color: #111827; letter-spacing: 0.5px; text-transform: uppercase;">
                        {{ $sale->bill_type === 'non_gst' ? 'ESTIMATE & RETAIL BILL' : 'TAX INVOICE' }}
                    </div>
                    <div style="font-size: 10.5px; color: #6B7280; margin-top: 2px; text-transform: uppercase; letter-spacing: 0.5px;">
                        Original for Recipient
                    </div>
                    <div style="font-size: 14px; font-weight: 800; color: #111827; margin-top: 6px;">
                        Invoice #: <span style="font-family: monospace;">{{ $sale->invoice_number }}</span>
                    </div>
                    <div style="font-size: 11px; color: #374151; margin-top: 2px;">
                        Date: <strong>{{ date('d M Y', strtotime($sale->created_at)) }}</strong> &bull; Time: {{ date('h:i A', strtotime($sale->created_at)) }}
                    </div>
                </td>
            </tr>
        </table>

        <!-- PARTY & TRANSACTION GRID -->
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 22px; border: 1px solid #E5E7EB; background: #F9FAFB;">
            <tr>
                <td style="width: 50%; padding: 14px 18px; vertical-align: top; border-right: 1px solid #E5E7EB;">
                    <div style="font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #6B7280; margin-bottom: 4px;">
                        Details of Receiver (Billed To)
                    </div>
                    <div style="font-size: 15px; font-weight: 800; color: #111827;">{{ $sale->customer_name ?: 'Walk-in Retail Customer' }}</div>
                    <div style="font-size: 11.5px; color: #374151; margin-top: 3px;">
                        Phone: <strong>{{ $sale->customer_phone ?: '—' }}</strong>
                    </div>
                    @if($sale->customer_gstin)
                        <div style="font-size: 11px; color: #111827; font-weight: 600; margin-top: 2px; font-family: monospace;">GSTIN: {{ $sale->customer_gstin }}</div>
                    @endif
                    <div style="font-size: 11px; color: #4B5563; margin-top: 2px;">{{ $sale->customer_address ?: 'Walk-in Retail Customer' }}</div>
                    <div style="font-size: 10px; color: #6B7280; margin-top: 3px;">Place of Supply: {{ setting('company.state', 'Uttar Pradesh') }} (09)</div>
                </td>
                <td style="width: 50%; padding: 14px 18px; vertical-align: top;">
                    <div style="font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #6B7280; margin-bottom: 4px;">
                        Payment &amp; Dispatch Details
                    </div>
                    <div style="font-size: 11.5px; color: #111827; margin-bottom: 3px;">
                        Payment Mode: <strong>{{ strtoupper(str_replace('_', ' ', $sale->payment_mode)) }}</strong>
                    </div>
                    @if($sale->bill_type === 'gst')
                        <div style="font-size: 11px; color: #374151; margin-top: 2px;">Tax Regime: <strong>Intra-State GST @ 18%</strong></div>
                    @else
                        <div style="font-size: 11px; color: #374151; margin-top: 2px;">Bill Category: <strong>Retail / Non-GST Estimate</strong></div>
                    @endif
                </td>
            </tr>
        </table>

        <!-- LINE ITEMS TABLE -->
        <table style="width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 22px;">
            <thead>
                <tr style="background: #F3F4F6; border-top: 1.5px solid #374151; border-bottom: 1.5px solid #374151;">
                    <th style="padding: 8px 10px; text-align: center; font-weight: 700; color: #1F2937; font-size: 9.5px; text-transform: uppercase; width: 5%;">#</th>
                    <th style="padding: 8px 10px; text-align: left; font-weight: 700; color: #1F2937; font-size: 9.5px; text-transform: uppercase; width: 44%;">Item Description</th>
                    <th style="padding: 8px 10px; text-align: center; font-weight: 700; color: #1F2937; font-size: 9.5px; text-transform: uppercase; width: 12%;">HSN Code</th>
                    <th style="padding: 8px 10px; text-align: center; font-weight: 700; color: #1F2937; font-size: 9.5px; text-transform: uppercase; width: 7%;">Qty</th>
                    <th style="padding: 8px 10px; text-align: right; font-weight: 700; color: #1F2937; font-size: 9.5px; text-transform: uppercase; width: 14%;">Unit Price (₹)</th>
                    <th style="padding: 8px 10px; text-align: right; font-weight: 700; color: #1F2937; font-size: 9.5px; text-transform: uppercase; width: 18%;">Line Total (₹)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $idx => $it)
                <tr style="border-bottom: 1px solid #E5E7EB;">
                    <td style="padding: 10px; text-align: center; color: #374151;">{{ $idx + 1 }}</td>
                    <td style="padding: 10px;">
                        <div style="font-weight: 800; font-size: 12px; color: #111827;">{{ $it->part_name }}</div>
                        <div style="font-size: 9.5px; color: #6B7280; margin-top: 1px;">Genuine Accessory / Spare Part</div>
                    </td>
                    <td style="padding: 10px; text-align: center; font-family: monospace; font-size: 11px;">85177090</td>
                    <td style="padding: 10px; text-align: center; font-weight: 700; color: #111827;">{{ $it->quantity }}</td>
                    <td style="padding: 10px; text-align: right; font-family: monospace;">{{ number_format($it->unit_price, 2) }}</td>
                    <td style="padding: 10px; text-align: right; font-family: monospace; font-weight: 800; font-size: 12px; color: #111827;">{{ number_format($it->line_total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- TOTALS & GST SUMMARY GRID -->
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 22px;">
            <tr>
                <td style="width: 55%; vertical-align: top; padding-right: 16px;">
                    @if($sale->bill_type === 'gst')
                    <div style="font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #6B7280; margin-bottom: 6px;">
                        Tax Breakdown (GST @ 18%)
                    </div>
                    <table style="width: 100%; border-collapse: collapse; font-size: 10.5px; margin-bottom: 12px; border: 1px solid #E5E7EB;">
                        <thead>
                            <tr style="background: #F3F4F6; border-bottom: 1px solid #E5E7EB;">
                                <th style="padding: 6px 8px; text-align: left; font-weight: 700; color: #1F2937; font-size: 9.5px;">Tax Component</th>
                                <th style="padding: 6px 8px; text-align: right; font-weight: 700; color: #1F2937; font-size: 9.5px;">Rate</th>
                                <th style="padding: 6px 8px; text-align: right; font-weight: 700; color: #1F2937; font-size: 9.5px;">Amount (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="border-bottom: 1px solid #E5E7EB;">
                                <td style="padding: 5px 8px; color: #374151;">Central GST (CGST)</td>
                                <td style="padding: 5px 8px; text-align: right; color: #374151;">9.00%</td>
                                <td style="padding: 5px 8px; text-align: right; font-family: monospace; font-weight: 600; color: #111827;">{{ number_format($sale->cgst_amount, 2) }}</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #E5E7EB;">
                                <td style="padding: 5px 8px; color: #374151;">State GST (SGST)</td>
                                <td style="padding: 5px 8px; text-align: right; color: #374151;">9.00%</td>
                                <td style="padding: 5px 8px; text-align: right; font-family: monospace; font-weight: 600; color: #111827;">{{ number_format($sale->sgst_amount, 2) }}</td>
                            </tr>
                            <tr style="background: #F9FAFB; font-weight: 700;">
                                <td style="padding: 5px 8px; color: #111827;">Total GST Liability</td>
                                <td style="padding: 5px 8px; text-align: right; color: #111827;">18.00%</td>
                                <td style="padding: 5px 8px; text-align: right; font-family: monospace; color: #111827;">{{ number_format($sale->tax_amount, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                    @endif

                    <div style="background: #F9FAFB; border: 1px solid #E5E7EB; border-radius: 4px; padding: 8px 12px;">
                        <span style="font-size: 9px; font-weight: 700; text-transform: uppercase; color: #6B7280;">Amount Chargeable (in Words):</span>
                        <div style="font-size: 11.5px; font-weight: 700; color: #111827; margin-top: 2px;">
                            {{ $amountInWords ?? 'Rupees Only' }}
                        </div>
                    </div>
                </td>

                <td style="width: 45%; vertical-align: top;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 11.5px; border: 1px solid #E5E7EB;">
                        <tr style="border-bottom: 1px solid #E5E7EB;">
                            <td style="padding: 7px 10px; background: #F9FAFB; color: #4B5563; width: 55%;">Subtotal (Taxable)</td>
                            <td style="padding: 7px 10px; text-align: right; font-family: monospace; font-weight: 600; color: #111827;">₹{{ number_format($sale->bill_type === 'gst' ? ($sale->subtotal - $sale->tax_amount) : $sale->subtotal, 2) }}</td>
                        </tr>
                        @if($sale->bill_type === 'gst')
                        <tr style="border-bottom: 1px solid #E5E7EB;">
                            <td style="padding: 7px 10px; background: #F9FAFB; color: #4B5563;">Total GST (18%)</td>
                            <td style="padding: 7px 10px; text-align: right; font-family: monospace; font-weight: 600; color: #111827;">₹{{ number_format($sale->tax_amount, 2) }}</td>
                        </tr>
                        @endif
                        <tr style="background: #F3F4F6; border-top: 1.5px solid #374151; border-bottom: 1.5px solid #374151;">
                            <td style="padding: 9px 10px; font-weight: 800; font-size: 13px; color: #111827;">Grand Total</td>
                            <td style="padding: 9px 10px; text-align: right; font-weight: 900; font-size: 15px; font-family: monospace; color: #111827;">₹{{ number_format($sale->total_amount, 2) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- TERMS & SIGNATURES SECTION -->
        <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
            <tr>
                <td style="width: 60%; vertical-align: top; padding-right: 20px;">
                    <div style="font-size: 9.5px; font-weight: 700; text-transform: uppercase; color: #6B7280; margin-bottom: 4px;">Terms &amp; Conditions</div>
                    <ol style="font-size: 9px; color: #4B5563; padding-left: 14px; line-height: 1.5; margin: 0;">
                        <li>Accessories and tempered glass once installed or opened cannot be returned.</li>
                        <li>Electronic accessories carry standard warranty as specified on the manufacturer package.</li>
                        <li>Disputes subject to local jurisdiction only.</li>
                    </ol>
                </td>
                <td style="width: 40%; vertical-align: top; text-align: right;">
                    <div style="font-size: 11px; font-weight: 700; color: #111827;">For {{ setting('company.name', 'Maurya Mobile Retail Store') }}</div>
                    <div style="height: 48px;"></div>
                    <div style="border-top: 1px solid #4B5563; display: inline-block; padding-top: 4px; font-size: 9.5px; color: #4B5563; min-width: 170px; text-align: center;">
                        Authorised Signatory
                    </div>
                </td>
            </tr>
        </table>

        <!-- BOTTOM DISCLAIMER STRIP -->
        <div style="margin-top: 24px; padding-top: 8px; border-top: 1px solid #E5E7EB; display: flex; justify-content: space-between; font-size: 9px; color: #9CA3AF;">
            <div>Computer generated invoice &bull; Original for Recipient</div>
            <div>{{ setting('company.name', 'Maurya Mobile') }} &bull; Powered by Maurya Mobile ERP</div>
        </div>

    </div>

    <!-- ─── 80MM THERMAL RECEIPT (COUNTER POS) ─── -->
    <div id="viewThermal" class="card" style="display:none; max-width: 320px; margin: 0 auto; padding: 18px; font-family: 'Courier New', monospace; font-size: 11px; color: #111827; background:#fff; border:1px dashed #9CA3AF;">
        <div style="text-align:center; margin-bottom: 8px;">
            <div style="font-weight:800; font-size:14px; text-transform:uppercase;">{{ setting('company.name', 'Maurya Mobile ERP') }}</div>
            <div style="font-size:10px; color:#6B7280;">{{ setting('company.address', '') }}</div>
            <div style="font-size:10px; color:#6B7280;">Ph: {{ setting('company.phone', '') }}</div>
            <div style="font-size:10px; font-weight:700; color:#1E293B;">GSTIN: {{ setting('company.tax_number', setting('company.gstin', '09AAACA1234F1Z5')) }}</div>
        </div>
        <div style="border-top: 1px dashed #CBD5E1; padding-top: 6px; font-size:10px; line-height:1.4;">
            <div>Inv: <strong>{{ $sale->invoice_number }}</strong></div>
            <div>Date: {{ date('d-m-Y H:i', strtotime($sale->created_at)) }}</div>
            <div>Cust: {{ $sale->customer_name }} ({{ $sale->customer_phone }})</div>
        </div>
        <div style="border-top: 1px dashed #CBD5E1; padding-top: 6px; margin-top: 6px;">
            @foreach($items as $it)
            <div style="display:flex; justify-content:space-between; font-weight:700; margin-bottom:2px;">
                <span>{{ $it->part_name }} (x{{ $it->quantity }})</span>
                <span>₹{{ number_format($it->line_total, 2) }}</span>
            </div>
            @endforeach
        </div>
        <div style="border-top: 1px dashed #CBD5E1; padding-top: 6px; margin-top: 6px; font-size:10px;">
            <div style="display:flex; justify-content:space-between;"><span>Taxable:</span><span>₹{{ number_format($sale->subtotal - $sale->tax_amount, 2) }}</span></div>
            <div style="display:flex; justify-content:space-between;"><span>GST (18%):</span><span>₹{{ number_format($sale->tax_amount, 2) }}</span></div>
            <div style="display:flex; justify-content:space-between; font-weight:800; font-size:12px; border-top: 1px solid #CBD5E1; padding-top: 4px; margin-top: 4px;"><span>TOTAL:</span><span>₹{{ number_format($sale->total_amount, 2) }}</span></div>
            <div style="display:flex; justify-content:space-between; font-weight:700; color:#10B981;"><span>PAID:</span><span>₹{{ number_format($sale->amount_paid, 2) }}</span></div>
            @if($sale->udhari_amount > 0)
                <div style="display:flex; justify-content:space-between; font-weight:700; color:#DC2626;"><span>UDHARI DUE:</span><span>₹{{ number_format($sale->udhari_amount, 2) }}</span></div>
            @endif
        </div>
        <div style="border-top: 1px dashed #CBD5E1; padding-top: 8px; margin-top: 8px; text-align:center; font-size:9px; color:#6B7280; line-height:1.4;">
            <div>Mode: {{ strtoupper(str_replace('_', ' ', $sale->payment_mode)) }}</div>
            <div style="font-weight:800; margin-top:4px;">*** THANK YOU - VISIT AGAIN ***</div>
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
    @media print {
        #viewA4 {
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
        }
        #viewA4 table {
            border-color: #374151 !important;
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
