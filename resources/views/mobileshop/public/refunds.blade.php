@extends('mobileshop.public.layout')

@section('title', 'Refunds, Returns & Replacements — Maurya Mobile Mumbai')
@section('meta_description', 'Clear, customer-centric return policies for new phones, 7-day pre-owned testing replacements, and repair warranty coverage at Maurya Mobile Mumbai.')

@section('subnav_title', 'Policy Guide')
@section('subnav_links')
    <a href="#new-phones" class="hover:text-apple-ink">New Phones DOA</a>
    <a href="#preowned" class="hover:text-apple-ink">7-Day Testing Exchange</a>
    <a href="#repairs" class="hover:text-apple-ink">Repair Touch Warranty</a>
    <a href="#accessories" class="hover:text-apple-ink">Accessories & Glass</a>
    <a href="#timelines" class="hover:text-apple-ink">Refund Timelines</a>
@endsection
@section('subnav_cta')
    <a href="{{ route('public.contact') }}" class="apple-btn-primary text-[13px] py-1.5 px-3.5">
        Initiate Claim
    </a>
@endsection

@section('content')

    <!-- ════ 1. HERO BANNER ════ -->
    <section class="bg-apple-parchment text-apple-ink py-16 sm:py-24 border-b border-apple-hairline text-center overflow-hidden">
        <div class="max-w-[1024px] mx-auto px-4 space-y-4">
            <span class="apple-caption-strong text-apple-primary uppercase tracking-widest text-[12px]">
                Fairness, Clarity & Customer Confidence
            </span>
            <h1 class="apple-hero-display text-apple-ink max-w-3xl mx-auto">
                Refund, Return & Replacement Policy.
            </h1>
            <p class="apple-lead-airy text-apple-muted-80 max-w-2xl mx-auto pt-2">
                We believe peace of mind is part of every transaction. Here is our straightforward, transparent guide to replacement windows, repair warranties, and refund handling.
            </p>
            <div class="pt-2 flex items-center justify-center gap-4 text-xs text-apple-muted-48">
                <span>Direct Showroom Support</span>
                <span>&bull;</span>
                <span>No Hidden Clauses</span>
            </div>
        </div>
    </section>

    <!-- ════ 2. CONTENT SECTIONS ════ -->
    <div class="max-w-[920px] mx-auto px-4 py-16 sm:py-20 space-y-16 text-apple-ink">

        <!-- SECTION 1: PRE-OWNED SMARTPHONE 7-DAY EXCHANGE -->
        <section id="preowned" class="space-y-6 scroll-mt-24">
            <div class="p-8 rounded-3xl bg-apple-parchment border border-apple-hairline space-y-4">
                <span class="apple-caption-strong text-apple-primary uppercase tracking-wider text-[11px]">Primary In-House Guarantee</span>
                <h2 class="apple-display-md text-apple-ink">
                    1. 7-Day Hardware Testing & Exchange on Pre-Owned Phones
                </h2>
                <p class="apple-body text-apple-muted-80">
                    When you purchase any Certified Pre-Owned smartphone from Maurya Mobile, you receive a full <strong>7-Day Testing Window</strong>. Test the handset in your daily routine — cameras, 5G calling, Wi-Fi connectivity, speakers, and battery drain.
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 text-xs">
                    <div class="p-4 rounded-xl bg-white border border-apple-hairline space-y-1.5">
                        <strong class="text-apple-ink font-bold flex items-center gap-1.5">
                            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i> Free Hardware Replacement
                        </strong>
                        <span class="text-apple-muted-80">If any genuine internal hardware defect is diagnosed within 7 days, we immediately replace the device with an identical grade unit or upgrade.</span>
                    </div>
                    <div class="p-4 rounded-xl bg-white border border-apple-hairline space-y-1.5">
                        <strong class="text-apple-ink font-bold flex items-center gap-1.5">
                            <i data-lucide="refresh-cw" class="w-4 h-4 text-indigo-600"></i> 100% Store Credit
                        </strong>
                        <span class="text-apple-muted-80">If an equivalent model is unavailable in stock, you receive 100% store credit or full purchase price reversal without cancellation fee deductions.</span>
                    </div>
                </div>
            </div>
        </section>

        <hr class="border-apple-hairline">

        <!-- SECTION 2: NEW SEALED PHONES (DOA POLICY) -->
        <section id="new-phones" class="space-y-4 scroll-mt-24">
            <span class="apple-caption-strong text-apple-primary uppercase tracking-wider text-[11px]">Manufacturer Policy</span>
            <h2 class="apple-display-md text-apple-ink">2. Brand-New Sealed Box Smartphones (DOA)</h2>
            <p class="apple-body text-apple-muted-80">
                Brand-new smartphones (Apple, Samsung, OnePlus, Vivo, Oppo, Realme, Xiaomi) come in tamper-evident factory sealed retail packaging. Under standard electronic industry guidelines:
            </p>
            <ul class="space-y-3 apple-body text-apple-muted-80 list-disc list-inside">
                <li><strong class="text-apple-ink">Factory Sealed Reversals:</strong> Unopened, sealed boxes in pristine factory condition can be returned or exchanged within 48 hours with original purchase receipt.</li>
                <li><strong class="text-apple-ink">Dead-On-Arrival (DOA) Claims:</strong> If a newly unboxed handset exhibits a factory defect out of the box (display failure, dead motherboard), official brand service centers issue a direct replacement slip under their 7 to 14-day DOA policy. Maurya Mobile staff will personally assist you with priority service center submission.</li>
            </ul>
        </section>

        <hr class="border-apple-hairline">

        <!-- SECTION 3: REPAIR WORK WARRANTY -->
        <section id="repairs" class="space-y-4 scroll-mt-24">
            <span class="apple-caption-strong text-apple-primary uppercase tracking-wider text-[11px]">Lab Workbench Guarantee</span>
            <h2 class="apple-display-md text-apple-ink">3. Repair Workbench Warranties & Touch Coverage</h2>
            <p class="apple-body text-apple-muted-80">
                Every repair job executed at our technical laboratory is backed by clear warranty terms:
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div class="p-5 rounded-2xl bg-apple-parchment border border-apple-hairline space-y-2">
                    <span class="apple-pill bg-indigo-100 text-indigo-800 text-[11px]">30-Day Display Guarantee</span>
                    <h3 class="apple-caption-strong text-apple-ink text-sm">Screen & Digitizer Touch</h3>
                    <p class="apple-caption text-apple-muted-80 text-xs">
                        Covers touch unresponsiveness, ghost touch anomalies, and digitizer latency. Physical glass cracking, corner impact chips, and internal liquid ingress occurring post-delivery are excluded.
                    </p>
                </div>
                <div class="p-5 rounded-2xl bg-apple-parchment border border-apple-hairline space-y-2">
                    <span class="apple-pill bg-emerald-100 text-emerald-800 text-[11px]">90-Day Battery Guarantee</span>
                    <h3 class="apple-caption-strong text-apple-ink text-sm">Battery Capacity & Charging</h3>
                    <p class="apple-caption text-apple-muted-80 text-xs">
                        Covers sudden percentage drops, failure to charge beyond 80%, or abnormal battery swelling. Free replacement provided if laboratory test confirms defective cell.
                    </p>
                </div>
            </div>
        </section>

        <hr class="border-apple-hairline">

        <!-- SECTION 4: ACCESSORIES & GLASS -->
        <section id="accessories" class="space-y-4 scroll-mt-24">
            <span class="apple-caption-strong text-apple-primary uppercase tracking-wider text-[11px]">Accessories Policy</span>
            <h2 class="apple-display-md text-apple-ink">4. Accessories, Chargers & Tempered Glass</h2>
            <div class="space-y-3 apple-body text-apple-muted-80">
                <p>
                    <strong class="text-apple-ink">Fast Chargers & Cables:</strong> Defective charging adapters or USB cables can be swapped over the counter within 48 hours with packaging and invoice.
                </p>
                <p>
                    <strong class="text-apple-ink">Tempered Glass & Back Covers:</strong> Installed tempered screen protectors, camera lens protectors, and customized back skins are consumable items and strictly non-returnable once the protective adhesive peel is removed.
                </p>
            </div>
        </section>

        <hr class="border-apple-hairline">

        <!-- SECTION 5: REFUND TIMELINES -->
        <section id="timelines" class="space-y-4 scroll-mt-24">
            <span class="apple-caption-strong text-apple-primary uppercase tracking-wider text-[11px]">Settlement Process</span>
            <h2 class="apple-display-md text-apple-ink">5. Mode of Refund & Processing Timelines</h2>
            <p class="apple-body text-apple-muted-80">
                Approved cash transactions can be refunded instantly in showroom. Digital payments (UPI, credit/debit cards, net banking) are reversed directly to the originating payment method within <strong>3 to 5 banking business days</strong> as per Reserve Bank of India settlement clearing cycles.
            </p>
        </section>

    </div>

@endsection
