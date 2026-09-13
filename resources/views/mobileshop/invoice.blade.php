@extends('mobileshop.layout')

@php
    $storeName = setting('company.name', 'Maurya Mobile Retail Store');
    $storePhone = setting('company.phone', '+91 98765 43210');
    $cleanPhone = preg_replace('/[^0-9]/', '', $sale->customer_phone ?? '');
    if (strlen($cleanPhone) === 10) {
        $cleanPhone = '91' . $cleanPhone;
    }

    // Determine GST status: Any new phone sale or gst bill type is a GST Tax Invoice
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

    $discountAmount = (float) ($sale->discount_amount ?? 0);
    $originalPrice = (float) ($sale->original_price ?? 0);
    if ($discountAmount <= 0 && $originalPrice > (float)$sale->total_amount) {
        $discountAmount = round($originalPrice - (float)$sale->total_amount, 2);
    }
    if ($originalPrice <= 0 && $discountAmount > 0) {
        $originalPrice = round((float)$sale->total_amount + $discountAmount, 2);
    }

    $pdfBillUrl = url('bill/' . $sale->invoice_number . '/pdf');

    $waMsg = "*{$storeName}*\n";
    $waMsg .= ($isGstBill ? "Tax Invoice #" : "Estimate #") . $sale->invoice_number . "\n\n";
    $waMsg .= "Dear *" . ($sale->customer_name ?: 'Customer') . "*,\n";
    $waMsg .= "Thank you for purchasing at {$storeName}!\n\n";
    $waMsg .= "• *Device:* {$sale->brand} {$sale->model}" . ($sale->storage ? " ({$sale->storage}" . ($sale->color ? ", {$sale->color}" : "") . ")" : "") . "\n";
    $waMsg .= "• *IMEI:* `{$sale->imei_1}`\n";
    if (!empty($sale->imei_2)) {
        $waMsg .= "• *IMEI 2:* `{$sale->imei_2}`\n";
    }
    $waMsg .= "• *Date:* " . date('d M Y', strtotime($sale->created_at)) . "\n";
    if ($discountAmount > 0) {
        $waMsg .= "• *Original Price:* ₹" . number_format(round($originalPrice)) . "\n";
        $waMsg .= "• *Discount:* -₹" . number_format(round($discountAmount)) . "\n";
    }
    $waMsg .= "• *Total Amount:* ₹" . number_format(round($sale->total_amount)) . " (" . strtoupper(str_replace('_', ' ', $sale->payment_mode)) . ")\n";
    if ($sale->udhari_amount > 0) {
        $waMsg .= "• *Balance Due:* ₹" . number_format(round($sale->udhari_amount)) . "\n";
    }
    $waMsg .= "\n📄 *Download / View PDF Bill:*\n";
    $waMsg .= $pdfBillUrl . "\n\n";
    $waMsg .= "Support: {$storePhone}\n";
    $waMsg .= "Shop #14, Linking Road, Bandra West, Mumbai";

    $waInvoiceUrl = 'https://wa.me/' . $cleanPhone . '?text=' . rawurlencode($waMsg);
@endphp

@section('title', ($isGstBill ? 'Tax Invoice' : 'Estimate') . ' #' . $sale->invoice_number . ' — Maurya Mobile')
@section('page-title', $isGstBill ? 'Tax Invoice & Receipt' : 'Estimate & Retail Bill')

@section('page-actions')
    <div class="invoice-action-buttons" style="display:flex; gap: 8px; align-items:center; flex-wrap:wrap;">
        <button type="button" onclick="switchFormat('a4')" id="btnA4" class="btn btn-primary btn-sm" style="font-weight:700;">
            Standard A4
        </button>
        <button type="button" onclick="switchFormat('thermal')" id="btnThermal" class="btn btn-outline btn-sm" style="font-weight:700;">
            80mm POS Thermal
        </button>
        <button type="button" onclick="window.print()" class="btn btn-outline btn-sm" style="font-weight:700; color:#0F172A; border-color:#94A3B8;">
            <i data-lucide="printer" style="width:13px;height:13px;"></i> Print / Save as PDF
        </button>
        <a href="{{ route('mobileshop.invoice.pdf', ['company_id' => company_id(), 'id' => $sale->id]) }}" class="btn btn-primary btn-sm" style="font-weight:700; background:#5E6AD2;">
            <i data-lucide="download" style="width:13px;height:13px;"></i> Download PDF
        </a>
        <button type="button" onclick="sharePdfWhatsApp()" class="btn btn-sm" style="font-weight:700; background:#25D366; color:#ffffff; display:inline-flex; align-items:center; gap:6px; border:none; box-shadow:0 2px 5px rgba(37,211,102,0.3); cursor:pointer;">
            <svg style="width:14px;height:14px;fill:currentColor;" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
            Share on WhatsApp
        </button>
        <a href="{{ route('mobileshop.sales', ['company_id' => company_id()]) }}" class="btn btn-outline btn-sm" style="font-weight:700;">
            <i data-lucide="arrow-left" style="width:13px;height:13px;"></i> Back to Sales
        </a>
    </div>
@endsection

@section('content')
<div class="invoice-page-wrapper" style="width: 100%; margin: 0; padding-bottom: 40px;">

    <!-- ─── MOBILE PROMINENT ACTION TOOLBAR (Always shown on small screens) ─── -->
    <div class="invoice-mobile-toolbar no-print" style="margin-bottom: 12px; display: flex; gap: 8px; flex-wrap: wrap; align-items: center; justify-content: space-between; background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 10px; padding: 10px 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
            <button type="button" onclick="window.print()" class="btn btn-primary btn-sm" style="font-weight: 800; font-size: 12px; padding: 7px 14px; border-radius: 7px; display: inline-flex; align-items: center; gap: 6px; background:#5E6AD2; color:#fff; border:none; box-shadow:0 2px 6px rgba(94,106,210,0.3);">
                <i data-lucide="printer" style="width: 14px; height: 14px;"></i> Print / PDF
            </button>
            <button type="button" onclick="switchFormat('a4')" id="mobBtnA4" class="btn btn-sm" style="font-weight: 700; font-size: 11.5px; padding: 6px 10px; border-radius: 7px; border:1px solid #5E6AD2; background:#EEF2FF; color:#4F46E5;">
                A4
            </button>
            <button type="button" onclick="switchFormat('thermal')" id="mobBtnThermal" class="btn btn-sm btn-outline" style="font-weight: 700; font-size: 11.5px; padding: 6px 10px; border-radius: 7px; border:1px solid #CBD5E1; color:#475569;">
                80mm Thermal
            </button>
        </div>
        <div style="display: flex; gap: 6px; align-items: center;">
            <button type="button" onclick="sharePdfWhatsApp()" class="btn btn-sm" style="font-weight: 700; font-size: 11.5px; background: #25D366; color: #fff; padding: 6px 11px; border-radius: 7px; display: inline-flex; align-items: center; gap: 4px; text-decoration: none; border:none; cursor:pointer;">
                <svg style="width: 13px; height: 13px; fill: currentColor;" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg> WhatsApp
            </button>
            <a href="{{ route('mobileshop.invoice.pdf', ['company_id' => company_id(), 'id' => $sale->id]) }}" class="btn btn-sm btn-outline" style="font-weight: 700; font-size: 11.5px; padding: 6px 10px; border-radius: 7px; color:#5E6AD2; border-color:#C7D2FE;">
                <i data-lucide="download" style="width:13px;height:13px;"></i> PDF
            </a>
        </div>
    </div>

    <!-- ─── RESPONSIVE PREVIEW SCROLL WRAPPER ─── -->
    <div class="invoice-scroll-wrapper">
        <!-- ─── STANDARD A4 GST TAX INVOICE ─── -->
        <div id="viewA4" class="printable-invoice-container" style="background: #ffffff; border: 1px solid #D1D5DB; border-radius: 4px; padding: 28px 32px; color: #111827; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">

        <!-- TOP CORPORATE HEADER -->
        <table class="inv-header-table" style="width: 100%; border-collapse: collapse; margin-bottom: 20px; border-bottom: 2px solid #1F2937; padding-bottom: 16px;">
            <tr>
                <td class="inv-header-left" style="vertical-align: top; width: 55%; padding-bottom: 12px;">
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
                <td class="inv-header-right" style="vertical-align: top; width: 45%; text-align: right; padding-bottom: 12px;">
                    <div style="font-size: 18px; font-weight: 800; color: #111827; letter-spacing: 0.5px; text-transform: uppercase;">
                        {{ $isGstBill ? 'TAX INVOICE' : 'ESTIMATE & RETAIL BILL' }}
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
        <table class="inv-party-table" style="width: 100%; border-collapse: collapse; margin-bottom: 22px; border: 1px solid #E5E7EB; background: #F9FAFB;">
            <tr>
                <td class="inv-party-left" style="width: 50%; padding: 14px 18px; vertical-align: top; border-right: 1px solid #E5E7EB;">
                    <div style="font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #6B7280; margin-bottom: 4px;">
                        Details of Receiver (Billed To)
                    </div>
                    <div style="font-size: 15px; font-weight: 800; color: #111827;">{{ $sale->customer_name }}</div>
                    <div style="font-size: 11.5px; color: #374151; margin-top: 3px;">
                        Phone: <strong>{{ $sale->customer_phone ?: '—' }}</strong>
                    </div>
                    @if($sale->customer_gstin)
                        <div style="font-size: 11px; color: #111827; font-weight: 700; margin-top: 2px; font-family: monospace;">GSTIN: {{ $sale->customer_gstin }}</div>
                    @endif
                    <div style="font-size: 11px; color: #4B5563; margin-top: 2px;">{{ $sale->customer_address ?: 'Walk-in Retail Customer' }}</div>
                    <div style="font-size: 10px; color: #6B7280; margin-top: 3px;">Place of Supply: {{ setting('company.state', 'Uttar Pradesh') }} (09)</div>
                </td>
                <td class="inv-party-right" style="width: 50%; padding: 14px 18px; vertical-align: top;">
                    <div style="font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #6B7280; margin-bottom: 4px;">
                        Payment &amp; Dispatch Details
                    </div>
                    <div style="font-size: 11.5px; color: #111827; margin-bottom: 3px;">
                        Payment Mode: <strong>{{ strtoupper(str_replace('_', ' ', $sale->payment_mode)) }}</strong>
                    </div>
                    @if($sale->payment_mode === 'emi' && $emiProvider)
                        <div style="font-size: 11px; color: #374151;">Financier: <strong>{{ $emiProvider->name }}</strong> (Loan No: {{ $sale->emi_loan_no ?: 'N/A' }})</div>
                    @endif
                    @if($isGstBill)
                        <div style="font-size: 11px; color: #111827; font-weight:700; margin-top: 2px;">Tax Regime: <strong>{{ strtoupper(str_replace('_', ' ', $sale->tax_type ?: 'intra_state')) }} @ 18% GST</strong></div>
                    @else
                        <div style="font-size: 11px; color: #374151; margin-top: 2px;">Bill Category: <strong>Retail / Non-GST Estimate</strong></div>
                    @endif
                </td>
            </tr>
        </table>

        <!-- DESKTOP / PRINT LINE ITEMS TABLE (Standard A4 Table) -->
        <table class="inv-items-table desktop-only-table" style="width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 22px;">
            <thead>
                <tr style="background: #F3F4F6; border-top: 1.5px solid #374151; border-bottom: 1.5px solid #374151;">
                    <th style="padding: 8px 8px; text-align: center; font-weight: 700; color: #1F2937; font-size: 9.5px; text-transform: uppercase; width: 4%;">#</th>
                    <th style="padding: 8px 10px; text-align: left; font-weight: 700; color: #1F2937; font-size: 9.5px; text-transform: uppercase; width: 38%;">Description of Goods / Handset</th>
                    <th style="padding: 8px 8px; text-align: center; font-weight: 700; color: #1F2937; font-size: 9.5px; text-transform: uppercase; width: 11%;">HSN Code</th>
                    <th style="padding: 8px 6px; text-align: center; font-weight: 700; color: #1F2937; font-size: 9.5px; text-transform: uppercase; width: 5%;">Qty</th>
                    <th style="padding: 8px 8px; text-align: right; font-weight: 700; color: #1F2937; font-size: 9.5px; text-transform: uppercase; width: 13%;">Rate (₹)</th>
                    <th style="padding: 8px 8px; text-align: right; font-weight: 700; color: #1F2937; font-size: 9.5px; text-transform: uppercase; width: 13%;">Taxable (₹)</th>
                    @if($isGstBill)
                    <th style="padding: 8px 8px; text-align: right; font-weight: 700; color: #1F2937; font-size: 9.5px; text-transform: uppercase; width: 13%;">GST 18% (₹)</th>
                    @endif
                    <th style="padding: 8px 10px; text-align: right; font-weight: 700; color: #1F2937; font-size: 9.5px; text-transform: uppercase; width: 16%;">Total (₹)</th>
                </tr>
            </thead>
            <tbody>
                <tr style="border-bottom: 1px solid #E5E7EB;">
                    <td style="padding: 10px 8px; text-align: center; color: #374151;">1</td>
                    <td style="padding: 10px;">
                        <div style="font-weight: 800; font-size: 12px; color: #111827;">{{ $sale->brand }} {{ $sale->model }}</div>
                        <div style="font-size: 10.5px; color: #4B5563; margin-top: 2px;">
                            {{ $sale->ram ? $sale->ram . ' RAM / ' . $sale->storage . ' Storage' : 'Standard Spec' }}
                            @if($sale->color) &bull; {{ $sale->color }} @endif
                        </div>
                        <div style="font-family: monospace; font-size: 10px; color: #111827; font-weight: 700; margin-top: 3px;">
                            IMEI 1: {{ $sale->imei_1 }}
                            @if($sale->imei_2) &bull; IMEI 2: {{ $sale->imei_2 }} @endif
                        </div>
                        @if($discountAmount > 0)
                        <div style="font-size: 9.5px; color: #111827; font-weight: 700; margin-top: 2px;">
                            Original Price: ₹{{ number_format($originalPrice, 2) }} &bull; Discount: -₹{{ number_format($discountAmount, 2) }}
                        </div>
                        @endif
                    </td>
                    <td style="padding: 10px 8px; text-align: center; font-family: monospace; font-size: 11px;">{{ $sale->hsn_code ?: '85171300' }}</td>
                    <td style="padding: 10px 6px; text-align: center; font-weight: 700; color: #111827;">1</td>
                    <td style="padding: 10px 8px; text-align: right; font-family: monospace;">{{ number_format($taxableAmount, 2) }}</td>
                    <td style="padding: 10px 8px; text-align: right; font-family: monospace;">{{ number_format($taxableAmount, 2) }}</td>
                    @if($isGstBill)
                    <td style="padding: 10px 8px; text-align: right; font-family: monospace; color:#111827; font-weight:600;">{{ number_format($totalTaxAmount, 2) }}</td>
                    @endif
                    <td style="padding: 10px; text-align: right; font-family: monospace; font-weight: 800; font-size: 12px; color: #111827;">{{ number_format($sale->total_amount, 2) }}</td>
                </tr>

                @if(isset($gifts) && $gifts->count() > 0)
                    @foreach($gifts as $idx => $g)
                    <tr style="border-bottom: 1px solid #E5E7EB; background: #FAFAFA;">
                        <td style="padding: 8px; text-align: center; color: #6B7280;">{{ $idx + 2 }}</td>
                        <td style="padding: 8px 10px;">
                            <div style="font-weight: 700; color: #111827; font-size: 11px;">Promotional Free Gift: {{ $g->name }}</div>
                            <div style="font-size: 9.5px; color: #6B7280;">Zero charge bundled store item</div>
                        </td>
                        <td style="padding: 8px; text-align: center; font-family: monospace; color: #6B7280;">85177090</td>
                        <td style="padding: 8px 6px; text-align: center; font-weight: 700; color: #111827;">{{ $g->qty }}</td>
                        <td style="padding: 8px; text-align: right; font-family: monospace; color: #6B7280;">0.00</td>
                        <td style="padding: 8px; text-align: right; font-family: monospace; color: #6B7280;">0.00</td>
                        @if($isGstBill)
                        <td style="padding: 8px; text-align: right; font-family: monospace; color: #6B7280;">0.00</td>
                        @endif
                        <td style="padding: 8px 10px; text-align: right; font-weight: 700; font-family: monospace; color: #111827;">FREE</td>
                    </tr>
                    @endforeach
                @endif
            </tbody>
        </table>

        <!-- ─── MOBILE RESPONSIVE ITEM VIEW (100% width on small screens, hidden in print) ─── -->
        <div class="inv-items-mobile mobile-only-block" style="margin-bottom: 16px;">
            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 12px; margin-bottom: 10px;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:8px;">
                    <div>
                        <div style="font-weight: 800; font-size: 13.5px; color: #111827;">{{ $sale->brand }} {{ $sale->model }}</div>
                        <div style="font-size: 11px; color: #4B5563; margin-top: 2px;">
                            {{ $sale->ram ? $sale->ram . ' RAM / ' . $sale->storage . ' Storage' : 'Standard Spec' }}
                            @if($sale->color) &bull; {{ $sale->color }} @endif
                        </div>
                        <div style="font-family: monospace; font-size: 10.5px; color: #1E293B; font-weight: 700; margin-top: 4px; background: #EEF2FF; padding: 3px 7px; border-radius: 5px; display: inline-block;">
                            IMEI 1: {{ $sale->imei_1 }}
                            @if($sale->imei_2) | 2: {{ $sale->imei_2 }} @endif
                        </div>
                        @if($discountAmount > 0)
                        <div style="font-size: 10.5px; color: #111827; font-weight: 700; margin-top: 4px;">
                            Orig: ₹{{ number_format($originalPrice, 2) }} &bull; Discount: -₹{{ number_format($discountAmount, 2) }}
                        </div>
                        @endif
                    </div>
                    <div style="text-align: right; flex-shrink: 0;">
                        <span style="font-size: 9.5px; font-weight: 700; color: #64748B; text-transform: uppercase;">HSN</span>
                        <div style="font-family: monospace; font-size: 11px; font-weight: 700; color: #334155;">{{ $sale->hsn_code ?: '85171300' }}</div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 10px; padding-top: 10px; border-top: 1px dashed #CBD5E1; font-size: 11.5px;">
                    <div><span style="color: #64748B;">Quantity:</span> <strong>1 Unit</strong></div>
                    <div style="text-align: right;"><span style="color: #64748B;">Taxable:</span> <strong style="font-family: monospace;">₹{{ number_format($taxableAmount, 2) }}</strong></div>
                    @if($isGstBill)
                    <div><span style="color: #64748B;">GST (18%):</span> <strong style="font-family: monospace; color:#111827;">₹{{ number_format($totalTaxAmount, 2) }}</strong></div>
                    @endif
                    <div style="text-align: right;"><span style="color: #64748B;">Total:</span> <strong style="font-family: monospace; font-size: 13px; color: #111827;">₹{{ number_format($sale->total_amount, 2) }}</strong></div>
                </div>
            </div>

            @if(isset($gifts) && $gifts->count() > 0)
                @foreach($gifts as $g)
                <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 10px 12px; margin-bottom: 8px; display: flex; justify-content: space-between; align-items: center; font-size: 11.5px;">
                    <div>
                        <div style="font-weight: 700; color: #111827;">🎁 Free Gift: {{ $g->name }}</div>
                        <div style="font-size: 10px; color: #4B5563;">HSN: 85177090 &bull; Qty: {{ $g->qty }}</div>
                    </div>
                    <span class="badge" style="background:#E2E8F0; color:#111827; font-weight:800; font-size:11px;">FREE</span>
                </div>
                @endforeach
            @endif
        </div>

        <!-- TOTALS & GST SUMMARY GRID -->
        <table class="inv-totals-table" style="width: 100%; border-collapse: collapse; margin-bottom: 22px;">
            <tr>
                <td class="inv-totals-tax-cell" style="width: 55%; vertical-align: top; padding-right: 16px;">
                    @if($isGstBill)
                    <div style="font-size: 9.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #111827; margin-bottom: 6px; display:flex; align-items:center; gap:5px;">
                        <span>📋</span> GST Tax Breakdown (18% Mobile Handset HSN 8517)
                    </div>
                    <table style="width: 100%; border-collapse: collapse; font-size: 10.5px; margin-bottom: 12px; border: 1px solid #E5E7EB; border-radius: 6px; overflow: hidden;">
                        <thead>
                            <tr style="background: #F3F4F6; border-bottom: 1px solid #D1D5DB;">
                                <th style="padding: 6px 8px; text-align: left; font-weight: 700; color: #111827; font-size: 9.5px;">Tax Component</th>
                                <th style="padding: 6px 8px; text-align: right; font-weight: 700; color: #111827; font-size: 9.5px;">Rate</th>
                                <th style="padding: 6px 8px; text-align: right; font-weight: 700; color: #111827; font-size: 9.5px;">Amount (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(($sale->tax_type ?? 'intra_state') === 'intra_state')
                                <tr style="border-bottom: 1px solid #E5E7EB;">
                                    <td style="padding: 6px 8px; color: #374151;">Central GST (CGST)</td>
                                    <td style="padding: 6px 8px; text-align: right; color: #374151;">9.00%</td>
                                    <td style="padding: 6px 8px; text-align: right; font-family: monospace; font-weight: 700; color: #111827;">{{ number_format($cgstAmount, 2) }}</td>
                                </tr>
                                <tr style="border-bottom: 1px solid #E5E7EB;">
                                    <td style="padding: 6px 8px; color: #374151;">State GST (SGST)</td>
                                    <td style="padding: 6px 8px; text-align: right; color: #374151;">9.00%</td>
                                    <td style="padding: 6px 8px; text-align: right; font-family: monospace; font-weight: 700; color: #111827;">{{ number_format($sgstAmount, 2) }}</td>
                                </tr>
                            @else
                                <tr style="border-bottom: 1px solid #E5E7EB;">
                                    <td style="padding: 6px 8px; color: #374151;">Integrated GST (IGST)</td>
                                    <td style="padding: 6px 8px; text-align: right; color: #374151;">18.00%</td>
                                    <td style="padding: 6px 8px; text-align: right; font-family: monospace; font-weight: 700; color: #111827;">{{ number_format($igstAmount, 2) }}</td>
                                </tr>
                            @endif
                            <tr style="background: #F8FAFC; font-weight: 800;">
                                <td style="padding: 6px 8px; color: #111827;">Total GST Tax</td>
                                <td style="padding: 6px 8px; text-align: right; color: #111827;">18.00%</td>
                                <td style="padding: 6px 8px; text-align: right; font-family: monospace; color: #111827;">₹{{ number_format($totalTaxAmount, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                    @endif

                    <div style="background: #F9FAFB; border: 1px solid #E5E7EB; border-radius: 6px; padding: 8px 12px;">
                        <span style="font-size: 9px; font-weight: 700; text-transform: uppercase; color: #6B7280;">Amount Chargeable (in Words):</span>
                        <div style="font-size: 11.5px; font-weight: 700; color: #111827; margin-top: 2px;">
                            {{ $amountInWords ?? 'Rupees Only' }}
                        </div>
                    </div>
                </td>

                <td class="inv-totals-summary-cell" style="width: 45%; vertical-align: top;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 11.5px; border: 1px solid #E5E7EB; border-radius: 6px; overflow: hidden;">
                        @if($discountAmount > 0)
                        <tr style="border-bottom: 1px solid #E5E7EB;">
                            <td style="padding: 7px 10px; background: #F9FAFB; color: #4B5563; width: 55%;">Gross Original Price</td>
                            <td style="padding: 7px 10px; text-align: right; font-family: monospace; font-weight: 600; color: #111827;">₹{{ number_format($originalPrice, 2) }}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #E5E7EB;">
                            <td style="padding: 7px 10px; background: #F9FAFB; color: #111827; font-weight: 700;">Discount Given</td>
                            <td style="padding: 7px 10px; text-align: right; font-family: monospace; font-weight: 700; color: #111827;">-₹{{ number_format($discountAmount, 2) }}</td>
                        </tr>
                        @endif
                        <tr style="border-bottom: 1px solid #E5E7EB;">
                            <td style="padding: 7px 10px; background: #F9FAFB; color: #4B5563; width: 55%;">Subtotal (Taxable)</td>
                            <td style="padding: 7px 10px; text-align: right; font-family: monospace; font-weight: 600; color: #111827;">₹{{ number_format($taxableAmount, 2) }}</td>
                        </tr>
                        @if($isGstBill)
                        <tr style="border-bottom: 1px solid #E5E7EB;">
                            <td style="padding: 7px 10px; background: #F9FAFB; color: #4B5563;">CGST (9%)</td>
                            <td style="padding: 7px 10px; text-align: right; font-family: monospace; font-weight: 600; color: #111827;">₹{{ number_format($cgstAmount, 2) }}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #E5E7EB;">
                            <td style="padding: 7px 10px; background: #F9FAFB; color: #4B5563;">SGST (9%)</td>
                            <td style="padding: 7px 10px; text-align: right; font-family: monospace; font-weight: 600; color: #111827;">₹{{ number_format($sgstAmount, 2) }}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #E5E7EB; background: #F3F4F6;">
                            <td style="padding: 7px 10px; font-weight: 700; color: #111827;">Total GST (18%)</td>
                            <td style="padding: 7px 10px; text-align: right; font-family: monospace; font-weight: 700; color: #111827;">₹{{ number_format($totalTaxAmount, 2) }}</td>
                        </tr>
                        @endif
                        <tr style="background: #F3F4F6; border-top: 1.5px solid #374151; border-bottom: 1.5px solid #374151;">
                            <td style="padding: 9px 10px; font-weight: 800; font-size: 13px; color: #111827;">Grand Total</td>
                            <td style="padding: 9px 10px; text-align: right; font-weight: 900; font-size: 15px; font-family: monospace; color: #111827;">₹{{ number_format($sale->total_amount, 2) }}</td>
                        </tr>
                        <tr style="border-top: 1px solid #E5E7EB;">
                            <td style="padding: 6px 10px; color: #111827; font-weight: 700;">Paid Now</td>
                            <td style="padding: 6px 10px; text-align: right; font-family: monospace; font-weight: 700; color: #111827;">₹{{ number_format($sale->amount_paid, 2) }}</td>
                        </tr>
                        @if($sale->udhari_amount > 0)
                        <tr style="border-top: 1px solid #D1D5DB; background: #F9FAFB;">
                            <td style="padding: 6px 10px; color: #111827; font-weight: 800;">Balance Due (Khata)</td>
                            <td style="padding: 6px 10px; text-align: right; font-family: monospace; font-weight: 900; color: #111827;">₹{{ number_format($sale->udhari_amount, 2) }}</td>
                        </tr>
                        @endif
                    </table>
                </td>
            </tr>
        </table>

        <!-- TERMS & SIGNATURES SECTION -->
        <table class="inv-terms-table" style="width: 100%; border-collapse: collapse; margin-top: 20px;">
            <tr>
                <td class="inv-terms-cell" style="width: 60%; vertical-align: top; padding-right: 20px;">
                    <div style="font-size: 9.5px; font-weight: 700; text-transform: uppercase; color: #6B7280; margin-bottom: 4px;">Terms &amp; Conditions</div>
                    <ol style="font-size: 9px; color: #4B5563; padding-left: 14px; line-height: 1.5; margin: 0;">
                        <li>1-Year manufacturer warranty applies on brand new mobile phones as per authorized brand service policy.</li>
                        <li>Physical damage, liquid intrusion, or unauthorized repair attempts void warranty.</li>
                        <li>Defective unit replacement within 7 days of invoice subject to brand inspection.</li>
                        <li>Goods once sold are not refundable in cash. Disputes subject to local jurisdiction.</li>
                    </ol>
                </td>
                <td class="inv-signature-cell" style="width: 40%; vertical-align: top; text-align: right;">
                    <div style="font-size: 11px; font-weight: 700; color: #111827;">For {{ setting('company.name', 'Maurya Mobile Retail Store') }}</div>
                    <div style="height: 48px;"></div>
                    <div style="border-top: 1px solid #4B5563; display: inline-block; padding-top: 4px; font-size: 9.5px; color: #4B5563; min-width: 170px; text-align: center;">
                        Authorised Signatory
                    </div>
                </td>
            </tr>
        </table>

        <!-- BOTTOM DISCLAIMER STRIP -->
        <div style="margin-top: 24px; padding-top: 8px; border-top: 1px solid #E5E7EB; display: flex; justify-content: space-between; font-size: 9px; color: #9CA3AF; flex-wrap: wrap; gap: 4px;">
            <div>Computer generated invoice &bull; Original for Recipient</div>
            <div>{{ setting('company.name', 'Maurya Mobile') }} &bull; Powered by Maurya Mobile ERP</div>
        </div>

        </div><!-- /#viewA4 -->

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
                @if($sale->customer_gstin)
                    <div>GSTIN: {{ $sale->customer_gstin }}</div>
                @endif
            </div>
            <div style="border-top: 1px dashed #CBD5E1; padding-top: 6px; margin-top: 6px;">
                <div style="display:flex; justify-content:space-between; font-weight:700;">
                    <span>{{ $sale->brand }} {{ $sale->model }}</span>
                    <span>₹{{ number_format($sale->total_amount, 2) }}</span>
                </div>
                <div style="font-size:9px; color:#6B7280;">IMEI: {{ $sale->imei_1 }}</div>
                @if(isset($gifts))
                    @foreach($gifts as $g)
                        <div style="display:flex; justify-content:space-between; font-size:10px; color:#111827; margin-top:2px;">
                            <span>+ Free: {{ $g->name }}</span><span>FREE</span>
                        </div>
                    @endforeach
                @endif
            </div>
            <div style="border-top: 1px dashed #CBD5E1; padding-top: 6px; margin-top: 6px; font-size:10px;">
                <div style="display:flex; justify-content:space-between;"><span>Taxable Value:</span><span>₹{{ number_format($taxableAmount, 2) }}</span></div>
                @if($isGstBill)
                <div style="display:flex; justify-content:space-between;"><span>CGST (9%):</span><span>₹{{ number_format($cgstAmount, 2) }}</span></div>
                <div style="display:flex; justify-content:space-between;"><span>SGST (9%):</span><span>₹{{ number_format($sgstAmount, 2) }}</span></div>
                <div style="display:flex; justify-content:space-between; font-weight:700;"><span>Total GST (18%):</span><span>₹{{ number_format($totalTaxAmount, 2) }}</span></div>
                @endif
                <div style="display:flex; justify-content:space-between; font-weight:800; font-size:12px; border-top: 1px solid #CBD5E1; padding-top: 4px; margin-top: 4px;"><span>TOTAL:</span><span>₹{{ number_format($sale->total_amount, 2) }}</span></div>
                <div style="display:flex; justify-content:space-between; font-weight:700; color:#111827;"><span>PAID:</span><span>₹{{ number_format($sale->amount_paid, 2) }}</span></div>
                @if($sale->udhari_amount > 0)
                    <div style="display:flex; justify-content:space-between; font-weight:700; color:#111827;"><span>UDHARI DUE:</span><span>₹{{ number_format($sale->udhari_amount, 2) }}</span></div>
                @endif
            </div>
            <div style="border-top: 1px dashed #CBD5E1; padding-top: 8px; margin-top: 8px; text-align:center; font-size:9px; color:#6B7280; line-height:1.4;">
                <div>Mode: {{ strtoupper(str_replace('_', ' ', $sale->payment_mode)) }}</div>
                <div>7 Days Replacement | 1 Year Brand Warranty</div>
                <div style="font-weight:800; margin-top:4px;">*** THANK YOU - VISIT AGAIN ***</div>
            </div>
        </div><!-- /#viewThermal -->

    </div><!-- /.invoice-scroll-wrapper -->

</div><!-- /.invoice-page-wrapper -->
@endsection

@push('styles')
<style>
    .invoice-scroll-wrapper {
        width: 100%;
        box-sizing: border-box;
    }

    /* ─── MOBILE PHONE SCREEN FIT (NO HORIZONTAL SCROLL) ─── */
    @media (max-width: 767px) {
        .invoice-page-wrapper {
            padding: 0 4px 40px 4px !important;
        }
        .invoice-scroll-wrapper {
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
            background: transparent !important;
            overflow-x: hidden !important;
            box-shadow: none !important;
        }
        #viewA4 {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            padding: 14px 10px !important;
            margin: 0 auto !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05) !important;
            border-radius: 8px !important;
            overflow: hidden !important;
            box-sizing: border-box !important;
        }
        .desktop-only-table {
            display: none !important;
        }
        .mobile-only-block {
            display: block !important;
        }
        .inv-header-table, .inv-party-table, .inv-totals-table, .inv-terms-table {
            display: block !important;
            width: 100% !important;
        }
        .inv-header-table tbody, .inv-party-table tbody, .inv-totals-table tbody, .inv-terms-table tbody,
        .inv-header-table tr, .inv-party-table tr, .inv-totals-table tr, .inv-terms-table tr {
            display: block !important;
            width: 100% !important;
        }
        .inv-header-left, .inv-header-right,
        .inv-party-left, .inv-party-right,
        .inv-totals-tax-cell, .inv-totals-summary-cell,
        .inv-terms-cell, .inv-signature-cell {
            display: block !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }
        .inv-header-right {
            text-align: left !important;
            margin-top: 10px !important;
            padding-top: 10px !important;
            border-top: 1px dashed #E5E7EB !important;
        }
        .inv-party-left {
            border-right: none !important;
            border-bottom: 1px solid #E5E7EB !important;
            padding: 10px 12px !important;
        }
        .inv-party-right {
            padding: 10px 12px !important;
        }
        .inv-totals-tax-cell {
            padding-right: 0 !important;
            margin-bottom: 14px !important;
        }
        .inv-terms-cell {
            padding-right: 0 !important;
            margin-bottom: 16px !important;
        }
        .inv-signature-cell {
            text-align: right !important;
        }
        #viewThermal {
            width: 100% !important;
            max-width: 320px !important;
            margin: 0 auto !important;
        }
    }

    /* ─── TABLET & DESKTOP SCREENS ─── */
    @media (min-width: 768px) {
        .invoice-mobile-toolbar {
            display: none !important;
        }
        .mobile-only-block {
            display: none !important;
        }
        .desktop-only-table {
            display: table !important;
        }
        .invoice-scroll-wrapper {
            background: #F1F5F9;
            border-radius: 8px;
            padding: 16px;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.03);
        }
        #viewA4 {
            max-width: 820px;
            margin: 0 auto;
        }
    }

    /* ─── PHYSICAL PRINT & PDF EXPORT ─── */
    @media print {
        *, #viewA4 *, #viewThermal * {
            color: #000000 !important;
            text-shadow: none !important;
        }
        #viewA4, #viewThermal {
            color: #000000 !important;
            background: #ffffff !important;
        }
        table, th, td {
            color: #000000 !important;
            border-color: #374151 !important;
        }
        .invoice-mobile-toolbar,
        .invoice-action-buttons {
            display: none !important;
        }
        .mobile-only-block {
            display: none !important;
        }
        .desktop-only-table {
            display: table !important;
        }
        .invoice-scroll-wrapper {
            padding: 0 !important;
            margin: 0 !important;
            background: transparent !important;
            overflow: visible !important;
            box-shadow: none !important;
        }
        #viewA4 {
            min-width: 0 !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
        }
        .inv-header-table, .inv-party-table, .inv-totals-table, .inv-terms-table {
            display: table !important;
        }
        .inv-header-table tbody, .inv-party-table tbody, .inv-totals-table tbody, .inv-terms-table tbody {
            display: table-row-group !important;
        }
        .inv-header-table tr, .inv-party-table tr, .inv-totals-table tr, .inv-terms-table tr {
            display: table-row !important;
        }
        .inv-header-left, .inv-header-right,
        .inv-party-left, .inv-party-right,
        .inv-totals-tax-cell, .inv-totals-summary-cell,
        .inv-terms-cell, .inv-signature-cell {
            display: table-cell !important;
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
        const mobBtnA4 = document.getElementById('mobBtnA4');
        const mobBtnThermal = document.getElementById('mobBtnThermal');
        let dynamicPageStyle = document.getElementById('dynamicThermalPageStyle');

        if (mode === 'thermal') {
            a4.style.display = 'none';
            thermal.style.display = 'block';
            document.body.classList.add('thermal-mode');
            if (btnThermal) btnThermal.className = 'btn btn-primary btn-sm';
            if (btnA4) btnA4.className = 'btn btn-outline btn-sm';
            if (mobBtnThermal) {
                mobBtnThermal.style.background = '#EEF2FF';
                mobBtnThermal.style.color = '#4F46E5';
                mobBtnThermal.style.borderColor = '#5E6AD2';
            }
            if (mobBtnA4) {
                mobBtnA4.style.background = 'transparent';
                mobBtnA4.style.color = '#475569';
                mobBtnA4.style.borderColor = '#CBD5E1';
            }

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
            if (mobBtnA4) {
                mobBtnA4.style.background = '#EEF2FF';
                mobBtnA4.style.color = '#4F46E5';
                mobBtnA4.style.borderColor = '#5E6AD2';
            }
            if (mobBtnThermal) {
                mobBtnThermal.style.background = 'transparent';
                mobBtnThermal.style.color = '#475569';
                mobBtnThermal.style.borderColor = '#CBD5E1';
            }

            if (dynamicPageStyle) {
                dynamicPageStyle.remove();
            }
        }
    }

    async function sharePdfWhatsApp() {
        const pdfUrl = "{{ route('public.bill.pdf', ['invoice_number' => $sale->invoice_number]) }}";
        const waUrl = "{{ $waInvoiceUrl }}";
        const invoiceNumber = "{{ $sale->invoice_number }}";
        const shareText = {!! json_encode($waMsg) !!};

        if (navigator.canShare && navigator.userAgent.match(/Android|iPhone|iPad|iPod/i)) {
            try {
                const resp = await fetch(pdfUrl);
                const blob = await resp.blob();
                const file = new File([blob], `Invoice-${invoiceNumber}.pdf`, { type: 'application/pdf' });
                if (navigator.canShare({ files: [file] })) {
                    await navigator.share({
                        title: `Invoice #${invoiceNumber}`,
                        text: shareText,
                        files: [file]
                    });
                    return;
                }
            } catch (err) {
                console.log('Native share cancelled or failed, falling back to WhatsApp link', err);
            }
        }
        window.open(waUrl, '_blank');
    }
</script>
@endpush
