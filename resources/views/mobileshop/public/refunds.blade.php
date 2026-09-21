@extends('mobileshop.public.layout')

@section('title', 'Refunds, Returns & Replacements — PhoneFix Azamgarh')
@section('meta_description', 'Clear, customer-centric return and replacement policies for mobile accessories, chargers, spare parts, and express repair workbench warranties at PhoneFix Azamgarh.')

@section('subnav_title', 'Policy Guide')
@section('subnav_links')
    <a href="#accessories" class="hover:text-apple-ink">Accessories & Fast Chargers</a>
    <a href="#repairs" class="hover:text-apple-ink">Repair Workbench Warranties</a>
    <a href="#spares" class="hover:text-apple-ink">Spare Parts & Displays</a>
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
                Fairness, Quality & Customer Confidence
            </span>
            <h1 class="apple-hero-display text-apple-ink max-w-3xl mx-auto">
                Refund, Return & Replacement Policy.
            </h1>
            <p class="apple-lead-airy text-apple-muted-80 max-w-2xl mx-auto pt-2">
                We guarantee the authenticity of every accessory and the craftsmanship of every express repair. Here is our transparent guide to replacement windows, repair warranties, and refund settlements.
            </p>
            <div class="pt-2 flex items-center justify-center gap-4 text-xs text-apple-muted-48">
                <span>Direct Showroom Support</span>
                <span>&bull;</span>
                <span>No Hidden Clauses</span>
                <span>&bull;</span>
                <span>GST Tax Invoices</span>
            </div>
        </div>
    </section>

    <!-- ════ 2. CONTENT SECTIONS ════ -->
    <div class="max-w-[920px] mx-auto px-4 py-16 sm:py-20 space-y-16 text-apple-ink">

        <!-- SECTION 1: ACCESSORIES & FAST CHARGERS -->
        <section id="accessories" class="space-y-6 scroll-mt-24">
            <div class="p-8 rounded-3xl bg-apple-parchment border border-apple-hairline space-y-4">
                <span class="apple-caption-strong text-apple-primary uppercase tracking-wider text-[11px]">Retail Accessories Guarantee</span>
                <h2 class="apple-display-md text-apple-ink">
                    1. Over-the-Counter Replacement for Accessories & Chargers
                </h2>
                <p class="apple-body text-apple-muted-80">
                    All premium accessories purchased at {{ store_name() }} — including GaN fast chargers, braided USB-C / Lightning cables, wireless magnetic pads, and audio adapters — are backed by an immediate replacement guarantee against manufacturing defects.
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 text-xs">
                    <div class="p-4 rounded-xl bg-white border border-apple-hairline space-y-1.5">
                        <strong class="text-apple-ink font-bold flex items-center gap-1.5">
                            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i> 7-Day Defect Swap
                        </strong>
                        <span class="text-apple-muted-80">If an accessory exhibits charging drops, loose pin connections, or audio distortion within 7 days, we replace it immediately over the counter with original packaging.</span>
                    </div>
                    <div class="p-4 rounded-xl bg-white border border-apple-hairline space-y-1.5">
                        <strong class="text-apple-ink font-bold flex items-center gap-1.5">
                            <i data-lucide="refresh-cw" class="w-4 h-4 text-indigo-600"></i> Store Credit / Reversal
                        </strong>
                        <span class="text-apple-muted-80">If an identical product is out of stock, customers receive 100% store credit or a full refund reversal without deduction.</span>
                    </div>
                </div>
            </div>
        </section>

        <hr class="border-apple-hairline">

        <!-- SECTION 2: REPAIR WORKBENCH WARRANTY -->
        <section id="repairs" class="space-y-4 scroll-mt-24">
            <span class="apple-caption-strong text-apple-primary uppercase tracking-wider text-[11px]">Lab Workbench Guarantee</span>
            <h2 class="apple-display-md text-apple-ink">2. Express Repair Workbench Warranties & Touch Guarantee</h2>
            <p class="apple-body text-apple-muted-80">
                Every repair job performed at our technical workshop is backed by certified technician testing and clear warranty protection:
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div class="p-5 rounded-2xl bg-apple-parchment border border-apple-hairline space-y-2">
                    <span class="apple-pill bg-indigo-100 text-indigo-800 text-[11px]">30-Day Display Guarantee</span>
                    <h3 class="apple-caption-strong text-apple-ink text-sm">Screen & Digitizer Touch Warranty</h3>
                    <p class="apple-caption text-apple-muted-80 text-xs">
                        Covers touch unresponsiveness, digitizer latency, and display flickering. Internal liquid ingress or external glass cracking occurring after delivery are excluded.
                    </p>
                </div>
                <div class="p-5 rounded-2xl bg-apple-parchment border border-apple-hairline space-y-2">
                    <span class="apple-pill bg-emerald-100 text-emerald-800 text-[11px]">90-Day Battery Guarantee</span>
                    <h3 class="apple-caption-strong text-apple-ink text-sm">Battery Replacement Guarantee</h3>
                    <p class="apple-caption text-apple-muted-80 text-xs">
                        Covers sudden percentage drops, failure to charge beyond 80%, or cell swelling. Free replacement provided if diagnostic tests confirm a defective cell.
                    </p>
                </div>
            </div>
        </section>

        <hr class="border-apple-hairline">

        <!-- SECTION 3: SPARE PARTS & CONSUMABLES -->
        <section id="spares" class="space-y-4 scroll-mt-24">
            <span class="apple-caption-strong text-apple-primary uppercase tracking-wider text-[11px]">Parts Policy</span>
            <h2 class="apple-display-md text-apple-ink">3. Spare Parts, Protective Glass & Back Covers</h2>
            <div class="space-y-3 apple-body text-apple-muted-80">
                <p>
                    <strong class="text-apple-ink">Replacement Spare Parts (Motherboard ICs, Cameras, Charging Flex):</strong> All internal spare parts installed during service come with bench testing verification. Any latent part failure within the warranty period is rectified without labour charges.
                </p>
                <p>
                    <strong class="text-apple-ink">Tempered Glass & Applied Screen Protectors:</strong> Once the adhesive backing of a 9H tempered glass, privacy filter, or UV liquid glue protector has been peeled and applied to a customer device, it is considered consumable and non-returnable.
                </p>
            </div>
        </section>

        <hr class="border-apple-hairline">

        <!-- SECTION 4: REFUND TIMELINES -->
        <section id="timelines" class="space-y-4 scroll-mt-24">
            <span class="apple-caption-strong text-apple-primary uppercase tracking-wider text-[11px]">Settlement Process</span>
            <h2 class="apple-display-md text-apple-ink">4. Mode of Refund & Processing Timelines</h2>
            <p class="apple-body text-apple-muted-80">
                Approved in-store cash transactions are refunded immediately at our counter. Digital payments (UPI, credit/debit cards, net banking) are reversed directly to the originating payment method within <strong>3 to 5 banking business days</strong> as per RBI payment settlement clearing cycles.
            </p>
        </section>

    </div>

@endsection
