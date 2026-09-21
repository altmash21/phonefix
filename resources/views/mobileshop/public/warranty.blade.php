@extends('mobileshop.public.layout')

@section('title', 'Warranty & Guarantee Policy — Maurya Mobile Mumbai')
@section('meta_description', 'Detailed warranty guidelines for mobile accessories, fast chargers, spare parts, and express repair workbench coverage at Maurya Mobile Mumbai.')

@section('subnav_title', 'Warranty Terms')
@section('subnav_links')
    <a href="#repairs" class="hover:text-apple-ink">Repair Workbench Guarantee</a>
    <a href="#accessories" class="hover:text-apple-ink">Accessories Coverage</a>
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
                Every repair executed at our workshop and every genuine accessory purchased from our showroom is backed by explicit warranty protection and certified testing.
            </p>
            <div class="pt-2 flex items-center justify-center gap-4 text-xs text-apple-muted-48">
                <span>100% Genuine Tax Invoices</span>
                <span>&bull;</span>
                <span>Certified Spares Guarantee</span>
                <span>&bull;</span>
                <span>Trained Technicians</span>
            </div>
        </div>
    </section>

    <!-- ════ 2. CONTENT SECTIONS ════ -->
    <div class="max-w-[920px] mx-auto px-4 py-16 sm:py-20 space-y-16 text-apple-ink">

        <!-- SECTION 1: REPAIR WORKBENCH GUARANTEE -->
        <section id="repairs" class="space-y-6 scroll-mt-24">
            <span class="apple-caption-strong text-apple-primary uppercase tracking-wider text-[11px]">Laboratory Benchmark</span>
            <h2 class="apple-display-md text-apple-ink">1. 90-Day Express Repair Workbench Warranty</h2>
            <p class="apple-body text-apple-muted-80">
                Because every repaired device undergoes rigorous bench testing before pickup, we back our hardware services with robust warranty coverage:
            </p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                <div class="p-6 rounded-2xl bg-apple-parchment border border-apple-hairline space-y-3">
                    <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                        90-Day Battery & Charging Flex Warranty
                    </div>
                    <p class="text-xs text-apple-muted-80 leading-relaxed">
                        Covers internal cell performance, charging pin connectivity, power IC stability, and abnormal drainage. Free replacement cell provided if diagnostic capacity fails standard thresholds.
                    </p>
                </div>
                <div class="p-6 rounded-2xl bg-apple-parchment border border-apple-hairline space-y-3">
                    <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-300">
                        30-Day Display & Digitizer Touch Warranty
                    </div>
                    <p class="text-xs text-apple-muted-80 leading-relaxed">
                        Covers screen digitizer latency, dead touch zones, ghost touches, and frame adhesive seal integrity. Display must be free of physical cracks or liquid penetration.
                    </p>
                </div>
            </div>
        </section>

        <hr class="border-apple-hairline">

        <!-- SECTION 2: ACCESSORIES COVERAGE -->
        <section id="accessories" class="space-y-4 scroll-mt-24">
            <span class="apple-caption-strong text-apple-primary uppercase tracking-wider text-[11px]">Retail Assurance</span>
            <h2 class="apple-display-md text-apple-ink">2. Genuine Mobile Accessories Guarantee</h2>
            <p class="apple-body text-apple-muted-80">
                All retail accessories (GaN adapters, braided high-wattage cables, car chargers, power banks, and magnetic mounts) sold by {{ store_name() }} carry manufacturer-backed warranty periods:
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 text-xs">
                <div class="p-5 rounded-2xl bg-apple-parchment border border-apple-hairline space-y-1.5">
                    <strong class="text-apple-ink block text-sm">6-Month Fast Charger Replacement</strong>
                    <span class="text-apple-muted-80">Covers sudden circuit failure, overheating cutoff issues, and voltage irregularities on branded power adapters.</span>
                </div>
                <div class="p-5 rounded-2xl bg-apple-parchment border border-apple-hairline space-y-1.5">
                    <strong class="text-apple-ink block text-sm">Over-The-Counter Swaps</strong>
                    <span class="text-apple-muted-80">Walk into our showroom with your bill and product box for quick diagnostic testing and over-the-counter replacement.</span>
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
                    Warranty guarantees cover internal component defects and workbench craftsmanship only. The following conditions void warranty coverage:
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2 text-xs">
                    <div class="p-4 rounded-xl bg-white border border-rose-200 space-y-1">
                        <strong class="text-rose-900 font-bold block">Water & Liquid Immersion</strong>
                        <span class="text-rose-800/80">Triggered internal Liquid Contact Indicators (LCI) or corrosion on circuit components post-delivery.</span>
                    </div>
                    <div class="p-4 rounded-xl bg-white border border-rose-200 space-y-1">
                        <strong class="text-rose-900 font-bold block">Physical Impact & Cracks</strong>
                        <span class="text-rose-800/80">Glass breakage, frame bends, corner chipping, or LCD ink bleeding from accidental drops after service completion.</span>
                    </div>
                    <div class="p-4 rounded-xl bg-white border border-rose-200 space-y-1">
                        <strong class="text-rose-900 font-bold block">Unauthorized Tampering</strong>
                        <span class="text-rose-800/80">Third-party disassembly, broken warranty tamper stickers, or altered internal hardware seals.</span>
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
                    <strong>Step 1:</strong> Locate your printed or digital GST Tax Invoice or Job Sheet number issued by {{ store_name() }}.
                </p>
                <p>
                    <strong>Step 2:</strong> Visit our {{ store_city() }} service desk or reach out via WhatsApp at <strong>{{ store_phone() }}</strong>.
                </p>
                <p>
                    <strong>Step 3:</strong> Our certified technician will conduct a 10-minute diagnostic check to verify warranty validity and inspect hardware for priority service.
                </p>
            </div>
        </section>

    </div>

@endsection
