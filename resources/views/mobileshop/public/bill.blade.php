@extends('mobileshop.public.layout')

@section('title', 'Official Bill #' . $invoice_number . ' — PhoneFix Azamgarh')
@section('meta_description', 'View and download official tax invoice receipt from PhoneFix Azamgarh.')

@section('subnav_title', 'Digital Receipt')

@section('content')
<div class="bg-apple-parchment min-h-[80vh] py-10 sm:py-16 px-4">
    <div class="max-w-[640px] mx-auto">

        <!-- Top Status Card -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xl border border-slate-200/80 space-y-6">

            <!-- Header -->
            <div class="flex items-start justify-between border-b border-slate-100 pb-6">
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-800 border border-slate-300 mb-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        Official Paid Receipt
                    </span>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                        {{ store_name() }}
                    </h1>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Invoice #{{ $invoice_number }} &bull; {{ date('d M Y, h:i A', strtotime($sale->created_at)) }}
                    </p>
                </div>
                <a href="{{ $pdfUrl }}" download="Invoice-{{ $invoice_number }}.pdf" onclick="handleDownloadFeedback(this)"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-md transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Download PDF</span>
                </a>
            </div>

            <!-- Customer Details -->
            <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-2xl text-xs">
                <div>
                    <span class="text-slate-400 font-semibold block uppercase tracking-wider text-[10px]">Billed To</span>
                    <span class="font-bold text-slate-800 text-sm block mt-0.5">{{ $sale->customer_name ?: 'Valued Customer' }}</span>
                    @if(!empty($sale->customer_phone))
                        <span class="text-slate-500 font-medium block">+91 {{ substr($sale->customer_phone, -10) }}</span>
                    @endif
                </div>
                <div class="text-right">
                    <span class="text-slate-400 font-semibold block uppercase tracking-wider text-[10px]">Payment Mode</span>
                    <span class="font-bold text-slate-800 text-sm block mt-0.5">{{ strtoupper(str_replace('_', ' ', $sale->payment_mode)) }}</span>
                    <span class="text-slate-700 font-semibold text-xs block">Verified Payment</span>
                </div>
            </div>

            <!-- Items Purchased -->
            <div class="space-y-3">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Purchase Details</h3>

                @php
                    $discountAmount = (float) ($sale->discount_amount ?? 0);
                    $grossTotal = (float) ($sale->gross_total ?? ($sale->original_price ?? 0));
                    if ($discountAmount <= 0 && $grossTotal > (float)$sale->total_amount) {
                        $discountAmount = round($grossTotal - (float)$sale->total_amount, 2);
                    }
                    if ($grossTotal <= 0 && $discountAmount > 0) {
                        $grossTotal = round((float)$sale->total_amount + $discountAmount, 2);
                    }
                @endphp

                @if($type === 'phone')
                    <div class="p-4 rounded-2xl border border-slate-200 bg-white space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-black text-slate-900 text-base">
                                {{ $sale->brand }} {{ $sale->model }}
                            </span>
                            <span class="font-black text-slate-900 text-base">
                                ₹{{ number_format(round($sale->total_amount)) }}
                            </span>
                        </div>
                        @if($discountAmount > 0)
                            <div class="text-xs text-slate-700 font-medium">
                                Discount Applied: <span class="font-bold text-slate-900">-₹{{ number_format(round($discountAmount)) }}</span>
                                <span class="text-slate-400 line-through ml-1">₹{{ number_format(round($grossTotal)) }}</span>
                            </div>
                        @endif
                        <div class="text-xs text-slate-600 space-y-1">
                            @if(!empty($sale->storage) || !empty($sale->color))
                                <div><span class="text-slate-400">Variant:</span> {{ $sale->storage }} {{ $sale->color }}</div>
                            @endif
                            <div><span class="text-slate-400">IMEI 1:</span> <code class="bg-slate-100 px-1.5 py-0.5 rounded text-slate-800 font-mono">{{ $sale->imei_1 }}</code></div>
                            @if(!empty($sale->imei_2))
                                <div><span class="text-slate-400">IMEI 2:</span> <code class="bg-slate-100 px-1.5 py-0.5 rounded text-slate-800 font-mono">{{ $sale->imei_2 }}</code></div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="border border-slate-200 rounded-2xl overflow-hidden">
                        <table class="w-full text-xs">
                            <thead class="bg-slate-50 text-slate-400 border-b border-slate-200 text-left">
                                <tr>
                                    <th class="p-3">Item</th>
                                    <th class="p-3 text-center">Qty</th>
                                    <th class="p-3 text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($items as $item)
                                    @php
                                        $itemDisc = (float) ($item->discount_amount ?? 0);
                                        $itemOrig = (float) ($item->original_price ?? 0);
                                    @endphp
                                    <tr>
                                        <td class="p-3">
                                            <div class="font-semibold text-slate-800">{{ $item->part_name }}</div>
                                            @if($itemDisc > 0)
                                                <div class="text-[10px] text-slate-600 font-medium">Disc: -₹{{ number_format(round($itemDisc)) }} (MRP: ₹{{ number_format(round($itemOrig > 0 ? $itemOrig : ($item->unit_price + $itemDisc))) }})</div>
                                            @endif
                                        </td>
                                        <td class="p-3 text-center text-slate-600">{{ $item->quantity }}</td>
                                        <td class="p-3 text-right font-bold text-slate-900">₹{{ number_format(round($item->line_total)) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- Financial Summary -->
            <div class="bg-slate-50 p-4 rounded-2xl space-y-2 text-xs">
                @if($discountAmount > 0)
                    <div class="flex justify-between text-slate-500">
                        <span>Gross Items Total</span>
                        <span class="font-bold text-slate-700">₹{{ number_format(round($grossTotal)) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-900 font-bold">
                        <span>Discount Given</span>
                        <span class="font-bold text-slate-900">-₹{{ number_format(round($discountAmount)) }}</span>
                    </div>
                @endif
                <div class="flex justify-between font-bold text-slate-900 text-base border-b border-slate-200 pb-2">
                    <span>Total Bill Amount</span>
                    <span class="text-slate-900 font-black">₹{{ number_format(round($sale->total_amount)) }}</span>
                </div>
                <div class="flex justify-between text-slate-600 pt-1">
                    <span>Amount Paid</span>
                    <span class="font-bold text-slate-800">₹{{ number_format(round($sale->amount_paid)) }}</span>
                </div>
                @if($sale->udhari_amount > 0)
                    <div class="flex justify-between text-slate-900 font-bold">
                        <span>Balance Due (Khata)</span>
                        <span>₹{{ number_format(round($sale->udhari_amount)) }}</span>
                    </div>
                @endif
            </div>

            <!-- Primary Action Buttons -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
                <a href="{{ $pdfUrl }}" download="Invoice-{{ $invoice_number }}.pdf" onclick="handleDownloadFeedback(this)"
                   class="w-full py-3 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 flex items-center justify-center gap-2 transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Download Official PDF</span>
                </a>
                <button type="button" onclick="sharePublicPdfWhatsApp()"
                        class="w-full py-3 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-lg shadow-emerald-600/30 flex items-center justify-center gap-2 transition-all cursor-pointer">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                    <span>Send on WhatsApp</span>
                </button>
                <button type="button" onclick="window.print()"
                        class="w-full py-3 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs flex items-center justify-center gap-2 transition-all border border-slate-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Print Receipt
                </button>
            </div>

            <!-- Store Info Footer -->
            <div class="text-center pt-4 border-t border-slate-100 text-[11px] text-slate-400 space-y-1">
                <div>{{ store_name() }} &bull; {{ store_address() }}</div>
                <div>Customer Support: <a href="tel:{{ preg_replace('/[^0-9]/', '', store_phone()) }}" class="font-bold text-slate-600">{{ store_phone() }}</a>@if(!empty(store_landline())) &bull; Landline: <a href="tel:{{ preg_replace('/[^0-9]/', '', store_landline()) }}" class="font-bold text-slate-600">{{ store_landline() }}</a>@endif</div>
            </div>

        </div>

    </div>
</div>
</div>

<!-- DESKTOP WHATSAPP GUIDANCE MODAL -->
<div id="waDesktopModal" class="no-print" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.65); backdrop-filter:blur(4px); z-index:99999; align-items:center; justify-content:center; padding:16px;">
    <div style="background:#ffffff; border-radius:14px; max-width:480px; width:100%; padding:24px 26px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); border:1px solid #E2E8F0; text-align:center;">
        <div style="width:52px; height:52px; border-radius:50%; background:#DCFCE7; color:#16A34A; display:flex; align-items:center; justify-content:center; margin:0 auto 14px;">
            <svg style="width:28px;height:28px;fill:currentColor;" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
        </div>
        <h3 style="font-size:16px; font-weight:800; color:#0F172A; margin-bottom:6px;">PDF Receipt Downloaded</h3>
        <p style="font-size:13px; color:#475569; line-height:1.5; margin-bottom:14px;">
            The PDF file <strong id="waModalFilename" style="color:#0F172A; word-break:break-all;">Invoice.pdf</strong> has been downloaded to your computer and WhatsApp is opened.
        </p>
        <div style="background:#F8FAFC; border:1px dashed #CBD5E1; border-radius:8px; padding:12px 14px; font-size:12px; color:#334155; text-align:left; margin-bottom:18px; line-height:1.6;">
            <strong>👉 Send the actual PDF on WhatsApp:</strong><br>
            1. Switch to the WhatsApp tab.<br>
            2. Simply <strong>drag & drop</strong> the downloaded PDF into the chat window (or click <strong>📎 &gt; Document</strong>).<br>
            3. Press <strong>Enter</strong> to send the PDF file!
        </div>
        <div style="display:flex; gap:8px; justify-content:center; flex-wrap:wrap;">
            <button type="button" onclick="openWaAgain()" class="btn btn-sm" style="font-weight:700; background:#25D366; color:#ffffff; border:none; padding:7px 16px; border-radius:7px; cursor:pointer;">
                Open WhatsApp Again
            </button>
            <button type="button" onclick="closeDesktopWaGuide()" class="btn btn-outline btn-sm" style="font-weight:700; padding:7px 16px; border-radius:7px; cursor:pointer;">
                Got it
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let activeWaTargetUrl = '';

function handleDownloadFeedback(el) {
    const span = el.querySelector('span');
    if (!span) return;
    const oldText = span.textContent;
    span.textContent = 'Downloading PDF...';
    setTimeout(() => {
        span.textContent = oldText;
    }, 4500);
}

async function sharePublicPdfWhatsApp() {
    const pdfUrl = "{{ $pdfUrl }}";
    const invoiceNumber = "{{ $invoice_number }}";
    const filename = `Invoice-${invoiceNumber}.pdf`;
    const customerPhone = "{{ preg_replace('/[^0-9]/', '', $sale->customer_phone ?? '') }}";
    const isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);

    try {
        const resp = await fetch(pdfUrl);
        if (!resp.ok) throw new Error('PDF download failed');
        const blob = await resp.blob();
        const file = new File([blob], filename, { type: 'application/pdf' });

        if (navigator.canShare && navigator.canShare({ files: [file] })) {
            await navigator.share({
                title: `Invoice #${invoiceNumber}`,
                text: `Invoice #${invoiceNumber} from {{ addslashes(store_name()) }}. Amount: ₹{{ number_format(round($sale->total_amount)) }}`,
                files: [file]
            });
            return;
        }

        // Desktop Fallback: Download file
        const downloadUrl = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = downloadUrl;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        a.remove();
        setTimeout(() => URL.revokeObjectURL(downloadUrl), 4000);

        // Open WhatsApp
        let phoneParam = customerPhone;
        if (phoneParam.length === 10) phoneParam = '91' + phoneParam;
        activeWaTargetUrl = phoneParam
            ? (isMobile ? 'https://wa.me/' + phoneParam : 'https://web.whatsapp.com/send?phone=' + phoneParam)
            : (isMobile ? 'https://wa.me/' : 'https://web.whatsapp.com/');
        window.open(activeWaTargetUrl, '_blank');

        showDesktopWaGuide(filename);

    } catch (err) {
        console.warn('Share notice:', err);
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
