@extends('mobileshop.public.layout')

@section('title', 'Terms & Conditions of Sale — Maurya Mobile Mumbai')
@section('meta_description', 'Review the Terms & Conditions of sale, pre-owned grading standards, warranty terms, and repair service guidelines at Maurya Mobile Mumbai.')

@section('subnav_title', 'Legal Terms')
@section('subnav_links')
    <a href="#purchasing" class="hover:text-apple-ink">Device Sales</a>
    <a href="#grading" class="hover:text-apple-ink">Pre-Owned Grading</a>
    <a href="#payment" class="hover:text-apple-ink">Billing & EMI</a>
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
                These terms govern all in-store showroom purchases, digital reservations, pre-owned buybacks, and workbench repairs executed by Maurya Mobile.
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
            <h2 class="apple-display-md text-apple-ink">1. Device Purchasing & Tax Invoicing</h2>
            <p class="apple-body text-apple-muted-80">
                All smartphones and electronic accessories sold by Maurya Mobile are 100% authentic, legally procured, and accompanied by a detailed computer-generated GST tax invoice containing:
            </p>
            <ul class="space-y-2.5 apple-body text-apple-muted-80 list-disc list-inside">
                <li>Primary and Secondary IMEI numbers for dual-SIM handsets.</li>
                <li>Device exact specification (Capacity in GB, RAM, Colorway, and Serial Number).</li>
                <li>HSN Codes (8517 for cellular phones, 85177090 for smartphone spare parts & accessories).</li>
                <li>CGST and SGST statutory tax breakdown.</li>
            </ul>
            <p class="apple-body text-apple-muted-80">
                Customers must preserve their original purchase invoice. The invoice acts as primary proof of purchase required for official OEM service center warranty claims (Apple Care, Samsung Service, OnePlus Authorized Centers) or Maurya in-house guarantees.
            </p>
        </section>

        <hr class="border-apple-hairline">

        <!-- SECTION 2: PRE-OWNED SMARTPHONE GRADING STANDARDS -->
        <section id="grading" class="space-y-6 scroll-mt-24">
            <span class="apple-caption-strong text-apple-primary uppercase tracking-wider text-[11px]">Clause 02</span>
            <h2 class="apple-display-md text-apple-ink">2. Certified Pre-Owned Quality Grading Standards</h2>
            <p class="apple-body text-apple-muted-80">
                To guarantee complete buyer transparency, every second-hand device in our showcase is certified under one of three clearly categorized physical condition tiers:
            </p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-2">
                <!-- Grade A+ -->
                <div class="p-6 rounded-2xl bg-apple-parchment border border-apple-hairline space-y-3">
                    <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300">
                        Grade A+ (Mint / Like New)
                    </div>
                    <p class="text-xs text-apple-muted-80 leading-relaxed">
                        Indistinguishable from brand-new inventory. Display glass and chassis are pristine with zero visible scratches. Battery health tested at 90% or higher. Original box and charging cable included whenever noted.
                    </p>
                    <div class="text-[11px] font-mono text-apple-ink font-semibold">Testing Guarantee: 90 Days</div>
                </div>

                <!-- Grade A -->
                <div class="p-6 rounded-2xl bg-apple-parchment border border-apple-hairline space-y-3">
                    <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-blue-100 text-blue-800 border border-blue-300">
                        Grade A (Very Good Condition)
                    </div>
                    <p class="text-xs text-apple-muted-80 leading-relaxed">
                        Light, faint micro-scratches on side frame or back glass only visible under angled reflection. Display glass is clear with zero cracks. 100% functional hardware; battery health tested at 85% or higher.
                    </p>
                    <div class="text-[11px] font-mono text-apple-ink font-semibold">Testing Guarantee: 30 Days</div>
                </div>

                <!-- Grade B -->
                <div class="p-6 rounded-2xl bg-apple-parchment border border-apple-hairline space-y-3">
                    <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-amber-100 text-amber-800 border border-amber-300">
                        Grade B (Fair Value Choice)
                    </div>
                    <p class="text-xs text-apple-muted-80 leading-relaxed">
                        Noticeable exterior cosmetic wear, edge scuffs, or minor case marks. Zero functional compromises — all cameras, 5G modems, speakers, and processors pass 50-point diagnostic benchmarks.
                    </p>
                    <div class="text-[11px] font-mono text-apple-ink font-semibold">Testing Guarantee: 30 Days</div>
                </div>
            </div>
        </section>

        <hr class="border-apple-hairline">

        <!-- SECTION 3: BILLING, EMI & STORE KHATA -->
        <section id="payment" class="space-y-4 scroll-mt-24">
            <span class="apple-caption-strong text-apple-primary uppercase tracking-wider text-[11px]">Clause 03</span>
            <h2 class="apple-display-md text-apple-ink">3. Payments, EMI Financing & Khata Credit</h2>
            <div class="space-y-3 apple-body text-apple-muted-80">
                <p>
                    <strong class="text-apple-ink">Accepted Payment Methods:</strong> Cash, UPI (Google Pay, PhonePe, Paytm), Visa/Mastercard/RuPay cards, and direct NEFT/RTGS bank transfers.
                </p>
                <p>
                    <strong class="text-apple-ink">Paperless 0% EMI Schemes:</strong> Financing via Bajaj Finserv, IDFC First Bank, and HDFC Consumer Loans is subject to customer credit profile, active CIBIL score, and lender approval. Any down payment or processing fee is collected as per the chosen financing institution's schedule.
                </p>
                <p>
                    <strong class="text-apple-ink">Customer Khata Ledger Accounts:</strong> Store credit granted to pre-approved repeat clientele must be settled within the agreed invoice due date. Overdue khata balances are subject to ledger suspension until settled in full.
                </p>
            </div>
        </section>

        <hr class="border-apple-hairline">

        <!-- SECTION 4: REPAIR SERVICE BENCH TERMS -->
        <section id="repairs" class="space-y-4 scroll-mt-24">
            <span class="apple-caption-strong text-apple-primary uppercase tracking-wider text-[11px]">Clause 04</span>
            <h2 class="apple-display-md text-apple-ink">4. Repair Work Orders & Lab Guidelines</h2>
            <div class="space-y-3 apple-body text-apple-muted-80">
                <p>
                    <strong class="text-apple-ink">Job Sheet Intake:</strong> Every service device received is cataloged with a unique ticket number (e.g. <span class="font-mono text-xs font-bold text-apple-ink">REP-2026-0001</span>), intake timestamp, and reported fault list. Pre-existing cosmetic damage (cracked back glass, dented corners, missing screws) is documented at intake.
                </p>
                <p>
                    <strong class="text-apple-ink">60-Day Collection Window:</strong> Customers must retrieve their serviced device within 60 calendar days of receiving a job completion SMS/call. After 60 days of uncollected storage and multiple unanswered notices, Maurya Mobile reserves the right to dispose of or recycle the unclaimed hardware to recover bench lab costs.
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
                All agreements, commercial transactions, repair contracts, and dispute resolutions arising out of services rendered by Maurya Mobile shall be governed strictly by the laws of India and subject to the exclusive jurisdiction of the competent courts in Mumbai, Maharashtra.
            </p>
        </section>

    </div>

@endsection
