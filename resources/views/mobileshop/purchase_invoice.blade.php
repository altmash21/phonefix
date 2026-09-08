@extends('mobileshop.layout')

@section('title', ($po->bill_type === 'non_gst' ? 'Estimate Purchase' : 'Purchase Invoice') . ' #' . $po->po_number . ' — MobiTrack')
@section('page-title', $po->bill_type === 'non_gst' ? 'Estimate & Retail Purchase Bill' : 'Purchase Invoice & Delivery Bill')

@section('page-actions')
    <div style="display:flex; gap: 8px; align-items:center; flex-wrap:wrap;">
        <button onclick="window.print()" class="btn btn-outline btn-sm" style="font-weight:700; color:#0F172A; border-color:#94A3B8;">
            <i data-lucide="printer" style="width:13px;height:13px;"></i> Print / Save as PDF
        </button>
        <a href="{{ route('mobileshop.purchase.invoice.pdf', ['id' => $po->id]) }}" class="btn btn-primary btn-sm" style="font-weight:700; background:#5E6AD2;">
            <i data-lucide="download" style="width:13px;height:13px;"></i> Download PDF
        </a>
        <a href="{{ route('mobileshop.purchase') }}" class="btn btn-outline btn-sm" style="font-weight:700;">
            <i data-lucide="arrow-left" style="width:13px;height:13px;"></i> Back to Purchase Hub
        </a>
    </div>
@endsection



@section('content')
<div class="invoice-page-wrapper" style="width: 100%; margin: 0; padding-bottom: 40px;">

    <div class="printable-invoice-container" style="background: #ffffff; border: 1px solid #D1D5DB; border-radius: 4px; padding: 28px 32px; color: #111827; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">

        <!-- TOP CORPORATE HEADER -->
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; border-bottom: 2px solid #1F2937; padding-bottom: 16px;">
            <tr>
                <td style="vertical-align: top; width: 55%; padding-bottom: 12px;">
                    <div style="font-size: 20px; font-weight: 800; color: #111827; letter-spacing: -0.3px; text-transform: uppercase;">
                        {{ setting('company.name', 'MobiTrack Retail Store') }}
                    </div>
                    <div style="font-size: 11px; color: #4B5563; margin-top: 4px; line-height: 1.5;">
                        {{ setting('company.address', 'Store Location, Commercial Complex') }}<br>
                        Phone: {{ setting('company.phone', '+91 98765 43210') }} &bull; Email: {{ setting('company.email', 'procurement@mobitrack.local') }}
                    </div>
                    <div style="font-size: 11px; font-weight: 700; color: #111827; margin-top: 4px;">
                        Store GSTIN: <span style="font-family: monospace; font-weight: 700;">{{ setting('company.tax_number', setting('company.gstin', '09AAACA1234F1Z5')) }}</span>
                        &nbsp;|&nbsp; State: {{ setting('company.state', 'Uttar Pradesh') }} (09)
                    </div>
                </td>
                <td style="vertical-align: top; width: 45%; text-align: right; padding-bottom: 12px;">
                    <div style="font-size: 18px; font-weight: 800; color: #111827; letter-spacing: 0.5px; text-transform: uppercase;">
                        {{ $po->bill_type === 'non_gst' ? 'PURCHASE ESTIMATE' : 'PURCHASE INVOICE' }}
                    </div>
                    <div style="font-size: 10.5px; color: #6B7280; margin-top: 2px; text-transform: uppercase; letter-spacing: 0.5px;">
                        Inward Stock Procurement Bill
                    </div>
                    <div style="font-size: 14px; font-weight: 800; color: #111827; margin-top: 6px;">
                        PO / Bill #: <span style="font-family: monospace;">{{ $po->po_number }}</span>
                    </div>
                    <div style="font-size: 11px; color: #374151; margin-top: 2px;">
                        Date: <strong>{{ date('d M Y', strtotime($po->order_date)) }}</strong>
                    </div>
                </td>
            </tr>
        </table>

        <!-- SUPPLIER & PROCUREMENT GRID -->
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 22px; border: 1px solid #E5E7EB; background: #F9FAFB;">
            <tr>
                <td style="width: 50%; padding: 14px 18px; vertical-align: top; border-right: 1px solid #E5E7EB;">
                    <div style="font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #6B7280; margin-bottom: 4px;">
                        Supplier / Vendor (Purchased From)
                    </div>
                    <div style="font-size: 15px; font-weight: 800; color: #111827;">{{ $po->supplier_name ?: 'Distributor / Vendor' }}</div>
                    <div style="font-size: 11.5px; color: #374151; margin-top: 3px;">
                        Phone: <strong>{{ $po->supplier_phone ?: '—' }}</strong>
                    </div>
                    @if($po->supplier_gstin)
                        <div style="font-size: 11px; color: #111827; font-weight: 600; margin-top: 2px; font-family: monospace;">Vendor GSTIN: {{ $po->supplier_gstin }}</div>
                    @endif
                    <div style="font-size: 11px; color: #4B5563; margin-top: 2px;">{{ $po->supplier_address ?: 'Authorized Wholesale Distributor' }}</div>
                </td>
                <td style="width: 50%; padding: 14px 18px; vertical-align: top;">
                    <div style="font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #6B7280; margin-bottom: 4px;">
                        Procurement &amp; Settlement Status
                    </div>
                    @if($po->bill_type === 'gst')
                    <div style="font-size: 11.5px; color: #111827; margin-bottom: 3px;">
                        Tax Mode: <strong>{{ strtoupper(str_replace('_', ' ', $po->tax_type)) }}</strong>
                    </div>
                    @endif
                    <div style="font-size: 11px; color: #111827; font-weight: 700;">
                        Bill Status: {{ strtoupper(str_replace('_', ' ', $po->status)) }}
                    </div>
                    <div style="font-size: 11px; color: #374151; margin-top: 4px;">
                        Balance Payable: <strong style="color: #111827; font-family: monospace;">₹{{ number_format($po->balance_due, 2) }}</strong>
                    </div>
                </td>
            </tr>
        </table>

        <!-- LINE ITEMS TABLE -->
        <table style="width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 22px;">
            <thead>
                <tr style="background: #F3F4F6; border-top: 1.5px solid #374151; border-bottom: 1.5px solid #374151;">
                    <th style="padding: 8px 10px; text-align: center; font-weight: 700; color: #1F2937; font-size: 9.5px; text-transform: uppercase; width: 5%;">#</th>
                    <th style="padding: 8px 10px; text-align: left; font-weight: 700; color: #1F2937; font-size: 9.5px; text-transform: uppercase; width: 44%;">Item Name &amp; Description</th>
                    <th style="padding: 8px 10px; text-align: left; font-weight: 700; color: #1F2937; font-size: 9.5px; text-transform: uppercase; width: 16%;">Variant / Spec</th>
                    <th style="padding: 8px 10px; text-align: center; font-weight: 700; color: #1F2937; font-size: 9.5px; text-transform: uppercase; width: 8%;">Qty</th>
                    <th style="padding: 8px 10px; text-align: right; font-weight: 700; color: #1F2937; font-size: 9.5px; text-transform: uppercase; width: 13%;">Unit Cost (₹)</th>
                    <th style="padding: 8px 10px; text-align: right; font-weight: 700; color: #1F2937; font-size: 9.5px; text-transform: uppercase; width: 14%;">Line Total (₹)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $idx => $it)
                <tr style="border-bottom: 1px solid #E5E7EB;">
                    <td style="padding: 10px; text-align: center; color: #374151;">{{ $idx + 1 }}</td>
                    <td style="padding: 10px;">
                        <div style="font-weight: 800; font-size: 12px; color: #111827;">{{ $it->brand }} {{ $it->model }}</div>
                    </td>
                    <td style="padding: 10px; font-size: 11px; color: #4B5563;">{{ $it->variant ?: 'Standard' }}</td>
                    <td style="padding: 10px; text-align: center; font-weight: 700; color: #111827;">{{ $it->qty }}</td>
                    <td style="padding: 10px; text-align: right; font-family: monospace;">{{ number_format($it->unit_cost, 2) }}</td>
                    <td style="padding: 10px; text-align: right; font-family: monospace; font-weight: 800; font-size: 12px; color: #111827;">{{ number_format($it->line_total, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 24px; color: #9CA3AF;">No line items found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- TOTALS & SUMMARY GRID -->
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 22px;">
            <tr>
                <td style="width: 55%; vertical-align: top; padding-right: 16px;">
                    <div style="background: #F9FAFB; border: 1px solid #E5E7EB; border-radius: 4px; padding: 10px 14px;">
                        <span style="font-size: 9px; font-weight: 700; text-transform: uppercase; color: #6B7280;">Total Bill Value (in Words):</span>
                        <div style="font-size: 12px; font-weight: 700; color: #111827; margin-top: 3px;">
                            {{ $amountInWords ?? 'Rupees Only' }}
                        </div>
                    </div>

                    <div style="margin-top: 12px; font-size: 10px; color: #6B7280; line-height: 1.5;">
                        &bull; All inventory quantities received and booked into MobiTrack inventory ledger.<br>
                        &bull; Handset IMEI numbers logged into active inventory database.
                    </div>
                </td>

                <td style="width: 45%; vertical-align: top;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 11.5px; border: 1px solid #E5E7EB;">
                        <tr style="border-bottom: 1px solid #E5E7EB;">
                            <td style="padding: 7px 10px; background: #F9FAFB; color: #4B5563; width: 55%;">Subtotal (Taxable)</td>
                            <td style="padding: 7px 10px; text-align: right; font-family: monospace; font-weight: 600; color: #111827;">₹{{ number_format($po->subtotal, 2) }}</td>
                        </tr>
                        @if($po->cgst_amount > 0 || $po->sgst_amount > 0 || $po->igst_amount > 0)
                        <tr style="border-bottom: 1px solid #E5E7EB;">
                            <td style="padding: 7px 10px; background: #F9FAFB; color: #4B5563;">Tax (CGST + SGST)</td>
                            <td style="padding: 7px 10px; text-align: right; font-family: monospace; font-weight: 600; color: #111827;">₹{{ number_format($po->cgst_amount + $po->sgst_amount + $po->igst_amount, 2) }}</td>
                        </tr>
                        @endif
                        <tr style="background: #F3F4F6; border-top: 1.5px solid #374151; border-bottom: 1.5px solid #374151;">
                            <td style="padding: 9px 10px; font-weight: 800; font-size: 13px; color: #111827;">Total Invoice Value</td>
                            <td style="padding: 9px 10px; text-align: right; font-weight: 900; font-size: 15px; font-family: monospace; color: #111827;">₹{{ number_format($po->total_amount, 2) }}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #E5E7EB;">
                            <td style="padding: 7px 10px; color: #374151; font-weight: 600;">Amount Paid / Settled</td>
                            <td style="padding: 7px 10px; text-align: right; font-family: monospace; font-weight: 700; color: #111827;">₹{{ number_format($po->amount_paid, 2) }}</td>
                        </tr>
                        @if($po->balance_due > 0)
                        <tr style="background: #FAFAFA;">
                            <td style="padding: 7px 10px; color: #111827; font-weight: 800;">Balance Due to Vendor</td>
                            <td style="padding: 7px 10px; text-align: right; font-family: monospace; font-weight: 800; color: #111827;">₹{{ number_format($po->balance_due, 2) }}</td>
                        </tr>
                        @endif
                    </table>
                </td>
            </tr>
        </table>

        <!-- SIGNATURES SECTION -->
        <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
            <tr>
                <td style="width: 50%; vertical-align: bottom;">
                    <div style="border-top: 1px solid #4B5563; display: inline-block; padding-top: 4px; font-size: 9.5px; color: #4B5563; min-width: 180px; text-align: center;">
                        Store Inventory Receiver Signature
                    </div>
                </td>
                <td style="width: 50%; vertical-align: top; text-align: right;">
                    <div style="font-size: 11px; font-weight: 700; color: #111827;">For {{ setting('company.name', 'MobiTrack Retail Store') }}</div>
                    <div style="height: 48px;"></div>
                    <div style="border-top: 1px solid #4B5563; display: inline-block; padding-top: 4px; font-size: 9.5px; color: #4B5563; min-width: 180px; text-align: center;">
                        Authorized Procurement &amp; Stamp
                    </div>
                </td>
            </tr>
        </table>

        <!-- BOTTOM DISCLAIMER STRIP -->
        <div style="margin-top: 24px; padding-top: 8px; border-top: 1px solid #E5E7EB; display: flex; justify-content: space-between; font-size: 9px; color: #9CA3AF;">
            <div>Computer generated purchase record &bull; Official Inward Stock Bill</div>
            <div>{{ setting('company.name', 'MobiTrack') }} &bull; Powered by MobiTrack ERP</div>
        </div>

    </div>

</div>
@endsection
