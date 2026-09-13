@extends('mobileshop.public.layout')

@section('title', 'Official Bill #' . $invoice_number . ' — Maurya Mobile Store')
@section('meta_description', 'View and download official tax invoice receipt from Maurya Mobile Store.')

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
                        {{ setting('company.name', 'Maurya Mobile Store') }}
                    </h1>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Invoice #{{ $invoice_number }} &bull; {{ date('d M Y, h:i A', strtotime($sale->created_at)) }}
                    </p>
                </div>
                <a href="{{ $pdfUrl }}" target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-md transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Download PDF
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
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                <a href="{{ $pdfUrl }}" target="_blank"
                   class="w-full py-3 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 flex items-center justify-center gap-2 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Download Official PDF
                </a>
                <button type="button" onclick="window.print()"
                        class="w-full py-3 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs flex items-center justify-center gap-2 transition-all border border-slate-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Print Receipt
                </button>
            </div>

            <!-- Store Info Footer -->
            <div class="text-center pt-4 border-t border-slate-100 text-[11px] text-slate-400 space-y-1">
                <div>{{ setting('company.name', 'Maurya Mobile Store') }} &bull; Linking Road, Bandra West, Mumbai</div>
                <div>Customer Support: <a href="tel:{{ setting('company.phone', '+919876543210') }}" class="font-bold text-slate-600">{{ setting('company.phone', '+91 98765 43210') }}</a></div>
            </div>

        </div>

    </div>
</div>
@endsection
