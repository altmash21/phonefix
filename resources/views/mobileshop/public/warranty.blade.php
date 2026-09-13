@extends('mobileshop.public.layout')

@section('title', 'Warranty & Guarantee Policy — Maurya Mobile Mumbai')
@section('meta_description', 'Detailed warranty guidelines for brand-new phones, certified pre-owned testing guarantees, and replacement screen coverage at Maurya Mobile Mumbai.')

@section('subnav_title', 'Warranty Terms')
@section('subnav_links')
    <a href="#brand" class="hover:text-apple-ink">Brand Warranty</a>
    <a href="#preowned" class="hover:text-apple-ink">Certified Shop Guarantee</a>
    <a href="#repairs" class="hover:text-apple-ink">Repair Coverage</a>
    <a href="#exclusions" class="hover:text-apple-ink">Exclusions & Voids</a>
    <a href="#claim" class="hover:text-apple-ink">How to Claim</a>
@endsection
@section('subnav_cta')
    <a href="{{ route('public.contact') }}" class="apple-btn-primary text-[13px] py-1.5 px-3.5">
        Service Counter
    </a>
@endsection

@section('content')

    <!-- ════ 1. HERO BANNER ════ -->
    <section class="bg-apple-parchment text-apple-ink py-16 sm:py-24 border-b border-apple-hairline text-center overflow-hidden">
        <div class="max-w-[1024px] mx-auto px-4 space-y-4">
            <span class="apple-caption-strong text-apple-primary uppercase tracking-widest text-[12px]">
                Authenticity & Reliability Assured
            </span>
            <h1 class="apple-hero-display text-apple-ink max-w-3xl mx-auto">
                Comprehensive Warranty & Guarantee.
            </h1>
            <p class="apple-lead-airy text-apple-muted-80 max-w-2xl mx-auto pt-2">
                Every smartphone and hardware repair handled by Maurya Mobile is backed by defined manufacturer or laboratory testing protections.
            </p>
            <div class="pt-2 flex items-center justify-center gap-4 text-xs text-apple-muted-48">
                <span>100% Genuine Tax Invoices</span>
                <span>&bull;</span>
                <span>Certified Parts Guarantee</span>
            </div>
        </div>
    </section>

    <!-- ════ 2. CONTENT SECTIONS ════ -->
    <div class="max-w-[920px] mx-auto px-4 py-16 sm:py-20 space-y-16 text-apple-ink">

        <!-- SECTION 1: MANUFACTURER BRAND WARRANTY -->
        <section id="brand" class="space-y-4 scroll-mt-24">
            <span class="apple-caption-strong text-apple-primary uppercase tracking-wider text-[11px]">New Inventory</span>
            <h2 class="apple-display-md text-apple-ink">1. Manufacturer Official 1-Year Warranty</h2>
            <p class="apple-body text-apple-muted-80">
                All brand-new sealed handsets purchased from Maurya Mobile carry the complete, official 1-Year Pan-India Limited Manufacturer Warranty (Apple India, Samsung India, OnePlus, Xiaomi, Vivo, Oppo, Realme):
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 text-xs">
                <div class="p-5 rounded-2xl bg-apple-parchment border border-apple-hairline space-y-1.5">
                    <strong class="text-apple-ink block text-sm">Pan-India Service Network</strong>
                    <span class="text-apple-muted-80">Eligible for walk-in claims across all authorized brand service centers anywhere in India with our printed GST invoice.</span>
                </div>
                <div class="p-5 rounded-2xl bg-apple-parchment border border-apple-hairline space-y-1.5">
                    <strong class="text-apple-ink block text-sm">Online Serial Activation</strong>
                    <span class="text-apple-muted-80">Device warranty starts upon first electronic activation and is verifiable directly on official OEM portals (e.g. checkcoverage.apple.com).</span>
                </div>
            </div>
        </section>

        <hr class="border-apple-hairline">

        <!-- SECTION 2: CERTIFIED SHOP GUARANTEE -->
        <section id="preowned" class="space-y-6 scroll-mt-24">
            <span class="apple-caption-strong text-apple-primary uppercase tracking-wider text-[11px]">Pre-Owned Coverage</span>
            <h2 class="apple-display-md text-apple-ink">2. Maurya Certified In-House Testing Guarantee</h2>
            <p class="apple-body text-apple-muted-80">
                Because we subject every pre-owned handset to a 50-point hardware verification before sale, we proudly provide our own in-house testing guarantees:
            </p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                <div class="p-6 rounded-2xl bg-apple-parchment border border-apple-hairline space-y-3">
                    <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                        90-Day Coverage (Grade A+ Devices)
                    </div>
                    <p class="text-xs text-apple-muted-80 leading-relaxed">
                        Covers the motherboard, processor, modem radios, Face ID / Touch ID sensors, microphone, charging ports, and internal flash memory against unexpected component failure.
                    </p>
                </div>
                <div class="p-6 rounded-2xl bg-apple-parchment border border-apple-hairline space-y-3">
                    <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-300">
                        30-Day Coverage (Grade A & B Devices)
                    </div>
                    <p class="text-xs text-apple-muted-80 leading-relaxed">
                        Comprehensive testing guarantee covering all core hardware functionalities. In the rare event of a component defect, repair or replacement is executed free of charge.
                    </p>
                </div>
            </div>
        </section>

        <hr class="border-apple-hairline">

        <!-- SECTION 3: WARRANTY EXCLUSIONS -->
        <section id="exclusions" class="space-y-4 scroll-mt-24">
            <div class="p-6 sm:p-8 rounded-3xl bg-rose-50/60 border border-rose-200/80 text-rose-950 space-y-4">
                <span class="apple-caption-strong text-rose-700 uppercase tracking-wider text-[11px]">Critical Exclusions</span>
                <h2 class="apple-display-md text-rose-950">3. What Voids or Excludes Warranty Coverage</h2>
                <p class="text-xs sm:text-sm text-rose-900/90 leading-relaxed">
                    Warranty guarantees cover internal component and manufacturing defects only. The following situations completely void warranty coverage across all device categories:
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2 text-xs">
                    <div class="p-4 rounded-xl bg-white border border-rose-200 space-y-1">
                        <strong class="text-rose-900 font-bold block">Water & Liquid Immersion</strong>
                        <span class="text-rose-800/80">Triggered internal Liquid Contact Indicators (LCI) or visible corrosion on board traces.</span>
                    </div>
                    <div class="p-4 rounded-xl bg-white border border-rose-200 space-y-1">
                        <strong class="text-rose-900 font-bold block">Physical Impact & Cracks</strong>
                        <span class="text-rose-800/80">Glass breakage, dented aluminum chassis, frame bends, or LCD ink bleeding from accidental drops.</span>
                    </div>
                    <div class="p-4 rounded-xl bg-white border border-rose-200 space-y-1">
                        <strong class="text-rose-900 font-bold block">Unauthorized Tampering</strong>
                        <span class="text-rose-800/80">Third-party service attempts, missing internal security brackets, or broken tamper seals.</span>
                    </div>
                </div>
            </div>
        </section>

        <hr class="border-apple-hairline">

        <!-- SECTION 4: HOW TO CLAIM -->
        <section id="claim" class="space-y-4 scroll-mt-24">
            <span class="apple-caption-strong text-apple-primary uppercase tracking-wider text-[11px]">Filing a Claim</span>
            <h2 class="apple-display-md text-apple-ink">4. How to File a Warranty Claim</h2>
            <div class="space-y-3 apple-body text-apple-muted-80">
                <p>
                    <strong>Step 1:</strong> Locate your printed or digital GST Tax Invoice issued by {{ store_name() }} showing the handset dual-IMEI numbers.
                </p>
                <p>
                    <strong>Step 2:</strong> Visit our {{ store_city() }} showroom service desk or contact our helpline at <strong>{{ store_phone() }}</strong>.
                </p>
                <p>
                    <strong>Step 3:</strong> Our certified technician will conduct a 15-minute intake diagnosis to verify IMEI matching, check warranty status, and inspect hardware.
                </p>
            </div>
        </section>

    </div>

@endsection
