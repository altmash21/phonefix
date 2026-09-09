@extends('mobileshop.public.layout')

@section('title', 'Environment & Values — Maurya Mobile Mumbai')
@section('meta_description', 'Learn about Maurya Mobile’s commitment to device longevity, electronic waste reduction, 50-point diagnostics, and consumer transparency in Mumbai.')

@section('subnav_title', 'Values & Trust')
@section('subnav_links')
    <a href="#longevity" class="hover:text-apple-ink">Device Longevity</a>
    <a href="#diagnostics" class="hover:text-apple-ink">50-Point Inspection</a>
    <a href="#ewaste" class="hover:text-apple-ink">E-Waste Reduction</a>
@endsection
@section('subnav_cta')
    <a href="{{ route('public.contact') }}" class="apple-btn-primary text-[13px] py-1.5 px-3.5">
        Visit Showroom
    </a>
@endsection

@section('content')

    <!-- ════ 1. EDITORIAL HERO TILE (Parchment Canvas, Lead-Airy Weight 300) ════ -->
    <section class="bg-apple-parchment text-apple-ink py-20 sm:py-32 border-b border-apple-hairline text-center overflow-hidden">
        <div class="max-w-[1024px] mx-auto px-4 space-y-4">
            <span class="apple-caption-strong text-apple-primary uppercase tracking-widest text-[12px]">
                Longevity & Environmental Responsibility
            </span>
            <h1 class="apple-hero-display text-apple-ink max-w-3xl mx-auto">
                The most sustainable phone is the one that lasts.
            </h1>
            <p class="apple-lead-airy text-apple-muted-80 max-w-2xl mx-auto pt-2">
                We believe consumer electronics shouldn't be discarded prematurely. Through precision micro-soldering, authentic parts, and rigorous battery renewals, we extend the operational life of every device.
            </p>
        </div>
    </section>

    <!-- ════ 2. TILE: 50-POINT DIAGNOSTIC SEAL (Pure White Canvas) ════ -->
    <section id="diagnostics" class="bg-apple-canvas text-apple-ink py-20 sm:py-28 border-b border-apple-hairline">
        <div class="max-w-[1024px] mx-auto px-4">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                
                <div class="lg:col-span-7 space-y-6">
                    <span class="apple-caption-strong text-apple-primary uppercase tracking-wider text-[12px]">Laboratory Verification</span>
                    <h2 class="apple-display-lg text-apple-ink">
                        Every pre-owned device passes our 50-point diagnostic seal.
                    </h2>
                    <p class="apple-body text-apple-muted-80">
                        Before any smartphone enters our showroom display, certified laboratory technicians test every transistor, sensor, and circuit pathway under factory simulation software.
                    </p>
                    <p class="apple-body text-apple-muted-80">
                        From display digitizer uniformity and Face ID infrared dot projectors to RF carrier transmission wattage and fast charging thermal dissipation, nothing is left to chance.
                    </p>

                    <!-- 3 Metric Columns -->
                    <div class="grid grid-cols-3 gap-6 pt-6 border-t border-apple-hairline text-left">
                        <div>
                            <div class="apple-display-md text-apple-ink">50+</div>
                            <div class="apple-caption text-apple-muted-48 mt-1">Verification Steps</div>
                        </div>
                        <div>
                            <div class="apple-display-md text-apple-ink">85%+</div>
                            <div class="apple-caption text-apple-muted-48 mt-1">Battery Minimum</div>
                        </div>
                        <div>
                            <div class="apple-display-md text-apple-ink">30-Day</div>
                            <div class="apple-caption text-apple-muted-48 mt-1">Store Replacement</div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-5 bg-apple-parchment rounded-[18px] border border-apple-hairline p-8 space-y-4">
                    <h3 class="apple-tagline text-apple-ink">Key Diagnostic Checkpoints</h3>
                    <ul class="space-y-3 apple-caption text-apple-ink">
                        <li class="flex items-start gap-2.5">
                            <i data-lucide="check" class="w-4 h-4 text-apple-primary shrink-0 mt-0.5"></i>
                            <span><strong>Motherboard Trace Integrity:</strong> Thermal imaging for short circuits and power leakages.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <i data-lucide="check" class="w-4 h-4 text-apple-primary shrink-0 mt-0.5"></i>
                            <span><strong>True Tone & Display OLEDS:</strong> Zero burn-in, dead pixels, or aftermarket glass digitizers.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <i data-lucide="check" class="w-4 h-4 text-apple-primary shrink-0 mt-0.5"></i>
                            <span><strong>Biometric Sensors:</strong> Face ID TrueDepth and Ultrasonic fingerprint response verification.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <i data-lucide="check" class="w-4 h-4 text-apple-primary shrink-0 mt-0.5"></i>
                            <span><strong>Battery Cell Chemistry:</strong> Genuine cycle count inspection and peak wattage performance.</span>
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    </section>

    <!-- ════ 3. TILE: E-WASTE REDUCTION (Near-Black #272729 Canvas) ════ -->
    <section id="ewaste" class="bg-apple-tile-1 text-apple-body-dark py-20 sm:py-28 border-b border-white/10 text-center overflow-hidden">
        <div class="max-w-[1024px] mx-auto px-4">
            
            <div class="max-w-3xl mx-auto space-y-4">
                <span class="apple-caption-strong text-apple-primary-dark uppercase tracking-widest text-[12px]">
                    Zero Electronic Waste Goal
                </span>
                <h2 class="apple-display-lg text-white">
                    Repairing the micro-components, not swapping the board.
                </h2>
                <p class="apple-lead text-white/70 max-w-2xl mx-auto">
                    Traditional service centers condemn entire motherboards over a 50-paise resistor. Our micro-soldering bench isolates individual BGA chips and SMD components to preserve your original device.
                </p>
            </div>

            <div class="mt-14 grid grid-cols-1 md:grid-cols-3 gap-8 text-left">
                <div class="bg-apple-tile-2 border border-white/10 rounded-[18px] p-8 space-y-3">
                    <h3 class="apple-body-strong text-white text-[18px]">15,000+ Saved Devices</h3>
                    <p class="apple-body text-white/70 text-[15px]">
                        Over 15,000 smartphones saved from landfills and restored to full active service since our showroom opened in 2018.
                    </p>
                </div>
                <div class="bg-apple-tile-2 border border-white/10 rounded-[18px] p-8 space-y-3">
                    <h3 class="apple-body-strong text-white text-[18px]">Safe Battery Recycling</h3>
                    <p class="apple-body text-white/70 text-[15px]">
                        Degraded lithium-ion battery packs are transferred to certified Indian green recycling refineries for mineral reclamation.
                    </p>
                </div>
                <div class="bg-apple-tile-2 border border-white/10 rounded-[18px] p-8 space-y-3">
                    <h3 class="apple-body-strong text-white text-[18px]">Zero-Wipe Data Ethics</h3>
                    <p class="apple-body text-white/70 text-[15px]">
                        Customer photos, contacts, and personal conversations are treated as private property and never wiped during repairs.
                    </p>
                </div>
            </div>

        </div>
    </section>

    <!-- ════ 4. TILE: SHOWROOM ACCOUNTABILITY & RETAIL STANDARD (Parchment Canvas) ════ -->
    <section id="longevity" class="bg-apple-parchment text-apple-ink py-20 sm:py-28 text-center">
        <div class="max-w-[1024px] mx-auto px-4 space-y-12">
            
            <div class="max-w-2xl mx-auto space-y-3">
                <h2 class="apple-display-lg text-apple-ink">
                    Bandra West Showroom.
                </h2>
                <p class="apple-lead text-apple-muted-48">
                    Experience physical products with full tactile testing and instant expert assistance.
                </p>
                <div class="pt-3">
                    <a href="{{ route('public.contact') }}" class="apple-btn-primary">
                        Get Store Directions
                    </a>
                </div>
            </div>

            <!-- 4 Value Pedestals -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-left">
                <div class="apple-utility-card">
                    <div class="apple-caption text-apple-primary uppercase font-semibold">Genuine</div>
                    <h3 class="apple-body-strong text-apple-ink mt-1">Official GST Invoice</h3>
                    <p class="apple-fine-print text-apple-muted-48 mt-1">Valid for all manufacturer warranty claims across India.</p>
                </div>
                <div class="apple-utility-card">
                    <div class="apple-caption text-apple-primary uppercase font-semibold">Pricing</div>
                    <h3 class="apple-body-strong text-apple-ink mt-1">No Hidden Charges</h3>
                    <p class="apple-fine-print text-apple-muted-48 mt-1">Transparent showroom price tags including all local taxes.</p>
                </div>
                <div class="apple-utility-card">
                    <div class="apple-caption text-apple-primary uppercase font-semibold">Financing</div>
                    <h3 class="apple-body-strong text-apple-ink mt-1">0% EMI Available</h3>
                    <p class="apple-fine-print text-apple-muted-48 mt-1">Bajaj Finserv, HDFC, ICICI, and IDFC instant counter approval.</p>
                </div>
                <div class="apple-utility-card">
                    <div class="apple-caption text-apple-primary uppercase font-semibold">Buyback</div>
                    <h3 class="apple-body-strong text-apple-ink mt-1">Instant Trade-in</h3>
                    <p class="apple-fine-print text-apple-muted-48 mt-1">Exchange your old device with immediate bank transfer or counter cash.</p>
                </div>
            </div>

        </div>
    </section>

@endsection
