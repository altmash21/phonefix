@extends('mobileshop.public.layout')

@section('title', 'Terms & Conditions of Sale — PhoneFix Azamgarh')
@section('meta_description', 'Review the Terms & Conditions of sale, accessories standards, spare parts quality tiers, warranty terms, and repair service guidelines at PhoneFix Azamgarh.')

@section('subnav_title', 'Legal Terms')
@section('subnav_links')
    <a href="#purchasing" class="hover:text-apple-ink">Invoicing & Sales</a>
    <a href="#grading" class="hover:text-apple-ink">Parts Quality Standards</a>
    <a href="#payment" class="hover:text-apple-ink">Billing & Khata</a>
    <a href="#repairs" class="hover:text-apple-ink">Repair Job Terms</a>
    <a href="#jurisdiction" class="hover:text-apple-ink">Legal Jurisdiction</a>
@endsection
@section('subnav_cta')
    <a href="{{ route('public.store') }}" class="apple-btn-primary text-[13px] py-1.5 px-3.5">
        Browse Catalog
    </a>
@endsection

@section('content')

    <!-- ════ 1. HERO BANNER ════ -->
    <section class="bg-apple-parchment text-apple-ink py-16 sm:py-24 border-b border-apple-hairline text-center overflow-hidden">
        <div class="max-w-[1024px] mx-auto px-4 space-y-4">
            <span class="apple-caption-strong text-apple-primary uppercase tracking-widest text-[12px]">
                Transparent Retail & Service Governance
            </span>
            <h1 class="apple-hero-display text-apple-ink max-w-3xl mx-auto">
                Terms & Conditions of Sale.
            </h1>
            <p class="apple-lead-airy text-apple-muted-80 max-w-2xl mx-auto pt-2">
                These terms govern all in-store showroom purchases, accessories orders, spare parts procurement, and workbench repairs executed by PhoneFix Azamgarh.
            </p>
            <div class="pt-2 flex items-center justify-center gap-4 text-xs text-apple-muted-48">
                <span>Updated: September {{ date('Y') }}</span>
                <span>&bull;</span>
                <span>Governed under Laws of India</span>
            </div>
        </div>
    </section>

    <!-- ════ 2. CONTENT SECTIONS ════ -->
    <div class="max-w-[920px] mx-auto px-4 py-16 sm:py-20 space-y-16 text-apple-ink">

        <!-- SECTION 1: PURCHASING & GENUINE TAX INVOICES -->
        <section id="purchasing" class="space-y-4 scroll-mt-24">
            <span class="apple-caption-strong text-apple-primary uppercase tracking-wider text-[11px]">Clause 01</span>
            <h2 class="apple-display-md text-apple-ink">1. Retail Purchasing & Tax Invoicing</h2>
            <p class="apple-body text-apple-muted-80">
                All mobile accessories, chargers, protective gear, and spare parts supplied or installed by PhoneFix Azamgarh are genuine, brand-tested, and accompanied by a detailed computer-generated GST tax invoice containing:
            </p>
            <ul class="space-y-2.5 apple-body text-apple-muted-80 list-disc list-inside">
                <li>Exact product description, brand, model compatibility, and SKU identification.</li>
                <li>HSN Codes (85177090 for smartphone spare parts, cables, chargers & accessories).</li>
                <li>Applicable CGST and SGST statutory tax breakdown.</li>
                <li>Authorized repair workbench technician signature and warranty stamp.</li>
            </ul>
            <p class="apple-body text-apple-muted-80">
                Customers are advised to preserve their tax invoice or digital receipt copy for verification during replacement requests or workbench warranty claims.
            </p>
        </section>

        <hr class="border-apple-hairline">

        <!-- SECTION 2: SPARE PARTS & ACCESSORIES QUALITY STANDARDS -->
        <section id="grading" class="space-y-6 scroll-mt-24">
            <span class="apple-caption-strong text-apple-primary uppercase tracking-wider text-[11px]">Clause 02</span>
            <h2 class="apple-display-md text-apple-ink">2. Spare Parts & Accessories Quality Standards</h2>
            <p class="apple-body text-apple-muted-80">
                To ensure complete technical transparency, all replacement parts and accessories in our workshop are cataloged under transparent quality tiers:
            </p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-2">
                <!-- Tier 1: OEM Original -->
                <div class="p-6 rounded-2xl bg-apple-parchment border border-apple-hairline space-y-3">
                    <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300">
                        OEM-Grade Original
                    </div>
                    <p class="text-xs text-apple-muted-80 leading-relaxed">
                        Top-tier replacement screens (OLED / Super AMOLED), high-density cobalt battery cells, and genuine charging boards identical to factory specifications in refresh rate, color depth, and touch sampling.
                    </p>
                    <div class="text-[11px] font-mono text-apple-ink font-semibold">Warranty: Up to 90 Days</div>
                </div>

                <!-- Tier 2: Premium Certified -->
                <div class="p-6 rounded-2xl bg-apple-parchment border border-apple-hairline space-y-3">
                    <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-blue-100 text-blue-800 border border-blue-300">
                        Premium Certified Spares
                    </div>
                    <p class="text-xs text-apple-muted-80 leading-relaxed">
                        High-quality aftermarket display panels (In-Cell FHD), reinforced charging ports, and replacement camera modules rigorously stress-tested for seamless compatibility and long-term durability.
                    </p>
                    <div class="text-[11px] font-mono text-apple-ink font-semibold">Warranty: 30 Days</div>
                </div>

                <!-- Tier 3: Certified Accessories -->
                <div class="p-6 rounded-2xl bg-apple-parchment border border-apple-hairline space-y-3">
                    <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-purple-100 text-purple-800 border border-purple-300">
                        Certified Accessories
                    </div>
                    <p class="text-xs text-apple-muted-80 leading-relaxed">
                        Branded GaN fast charging bricks, military-grade drop-tested back cases, 9H tempered glass protectors, and braided data cables with over-voltage and thermal surge protection.
                    </p>
                    <div class="text-[11px] font-mono text-apple-ink font-semibold">Warranty: Up to 6 Months</div>
                </div>
            </div>
        </section>

        <hr class="border-apple-hairline">

        <!-- SECTION 3: BILLING & STORE KHATA -->
        <section id="payment" class="space-y-4 scroll-mt-24">
            <span class="apple-caption-strong text-apple-primary uppercase tracking-wider text-[11px]">Clause 03</span>
            <h2 class="apple-display-md text-apple-ink">3. Payments, Settlement & Store Khata</h2>
            <div class="space-y-3 apple-body text-apple-muted-80">
                <p>
                    <strong class="text-apple-ink">Accepted Payment Methods:</strong> Cash, instant UPI (Google Pay, PhonePe, Paytm, BHIM), debit/credit cards, and direct bank transfers.
                </p>
                <p>
                    <strong class="text-apple-ink">Customer Khata Ledger Accounts:</strong> Store credit granted to pre-approved repeat clientele or wholesale accessory buyers must be settled within the agreed invoice timeframe. Overdue khata balances must be cleared prior to booking new repair tickets or orders.
                </p>
            </div>
        </section>

        <hr class="border-apple-hairline">

        <!-- SECTION 4: REPAIR SERVICE BENCH TERMS -->
        <section id="repairs" class="space-y-4 scroll-mt-24">
            <span class="apple-caption-strong text-apple-primary uppercase tracking-wider text-[11px]">Clause 04</span>
            <h2 class="apple-display-md text-apple-ink">4. Express Repair Work Orders & Lab Guidelines</h2>
            <div class="space-y-3 apple-body text-apple-muted-80">
                <p>
                    <strong class="text-apple-ink">Job Sheet Intake:</strong> Every service device received is cataloged with a unique ticket number (e.g. <span class="font-mono text-xs font-bold text-apple-ink">REP-2026-0001</span>), intake timestamp, and reported fault list. Pre-existing cosmetic damage is documented at intake.
                </p>
                <p>
                    <strong class="text-apple-ink">60-Day Collection Window:</strong> Customers must retrieve their serviced device within 60 calendar days of receiving a job completion notification. After 60 days of uncollected storage and multiple unanswered notices, PhoneFix Azamgarh reserves the right to recycle the unclaimed hardware to recover bench lab costs.
                </p>
                <p>
                    <strong class="text-apple-ink">Water & Liquid Damage Disclaimer:</strong> Handsets submitted with prior water ingress or corrosive oxidation carry no warranty on secondary components, as microscopic corrosion may degrade unaffected traces over time.
                </p>
            </div>
        </section>

        <hr class="border-apple-hairline">

        <!-- SECTION 5: DISPUTE JURISDICTION -->
        <section id="jurisdiction" class="space-y-4 scroll-mt-24">
            <span class="apple-caption-strong text-apple-primary uppercase tracking-wider text-[11px]">Clause 05</span>
            <h2 class="apple-display-md text-apple-ink">5. Legal Jurisdiction</h2>
            <p class="apple-body text-apple-muted-80">
                All agreements, commercial transactions, repair contracts, and dispute resolutions arising out of services rendered by PhoneFix Azamgarh shall be governed strictly by the laws of India and subject to the exclusive jurisdiction of the competent courts in Azamgarh, Uttar Pradesh.
            </p>
        </section>

    </div>

@endsection
