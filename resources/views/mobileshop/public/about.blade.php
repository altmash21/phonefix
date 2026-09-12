@extends('mobileshop.public.layout')

@section('title', 'Environment & Values — Maurya Mobile Mumbai')
@section('meta_description', 'Learn about Maurya Mobile’s commitment to device longevity, electronic waste reduction, 50-point diagnostics, and consumer transparency in Mumbai.')

@section('subnav_title', 'Values & Trust')

@section('content')

    <!-- ════ 1. EDITORIAL HERO TILE ════ -->
    <section class="bg-apple-parchment text-apple-ink py-16 sm:py-24 border-b border-apple-hairline text-center overflow-hidden">
        <div class="max-w-[1024px] mx-auto px-4 space-y-4">
            <span class="apple-caption-strong text-apple-primary uppercase tracking-widest text-[12px]">
                About Maurya Mobile &bull; Bandra West, Mumbai
            </span>
            <h1 class="apple-hero-display text-apple-ink max-w-3xl mx-auto">
                Honest Deals, Quality Phones & Reliable Service.
            </h1>
            <p class="apple-lead-airy text-apple-muted-80 max-w-2xl mx-auto pt-2">
                Serving Mumbai since 2018. We help you find the right smartphone at the best price, offer guaranteed pre-owned devices, and deliver fast repairs with genuine parts.
            </p>
        </div>
    </section>

    <!-- ════ 2. TILE: THOROUGH TESTING ════ -->
    <section id="diagnostics" class="bg-apple-canvas text-apple-ink py-16 sm:py-24 border-b border-apple-hairline">
        <div class="max-w-[1024px] mx-auto px-4">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                
                <div class="lg:col-span-7 space-y-6">
                    <span class="apple-caption-strong text-apple-primary uppercase tracking-wider text-[12px]">Complete Peace of Mind</span>
                    <h2 class="apple-display-lg text-apple-ink">
                        Every pre-owned phone is thoroughly tested before you buy.
                    </h2>
                    <p class="apple-body text-apple-muted-80">
                        Before any smartphone is placed in our showroom display, our technicians test every essential feature: original display quality, camera lenses, microphones, speakers, Face ID / fingerprint sensors, and charging ports.
                    </p>
                    <p class="apple-body text-apple-muted-80">
                        You get a 100% functional phone with tested battery health, accompanied by our 30-day replacement warranty and a printed GST tax invoice.
                    </p>

                    <!-- 3 Metric Columns -->
                    <div class="grid grid-cols-3 gap-6 pt-6 border-t border-apple-hairline text-left">
                        <div>
                            <div class="apple-display-md text-apple-ink">50+</div>
                            <div class="apple-caption text-apple-muted-48 mt-1">Checkpoints Tested</div>
                        </div>
                        <div>
                            <div class="apple-display-md text-apple-ink">85%+</div>
                            <div class="apple-caption text-apple-muted-48 mt-1">Minimum Battery Health</div>
                        </div>
                        <div>
                            <div class="apple-display-md text-apple-ink">30-Day</div>
                            <div class="apple-caption text-apple-muted-48 mt-1">Replacement Guarantee</div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-5 bg-apple-parchment rounded-[18px] border border-apple-hairline p-8 space-y-4">
                    <h3 class="apple-tagline text-apple-ink">What We Inspect</h3>
                    <ul class="space-y-3 apple-caption text-apple-ink">
                        <li class="flex items-start gap-2.5">
                            <i data-lucide="check" class="w-4 h-4 text-apple-primary shrink-0 mt-0.5"></i>
                            <span><strong>Original Screen & Touch:</strong> Crisp display, accurate colors, and zero dead pixels.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <i data-lucide="check" class="w-4 h-4 text-apple-primary shrink-0 mt-0.5"></i>
                            <span><strong>Cameras & Microphones:</strong> Clear front & back photos, zoom stability, and crisp calling audio.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <i data-lucide="check" class="w-4 h-4 text-apple-primary shrink-0 mt-0.5"></i>
                            <span><strong>Biometrics:</strong> Instant Face ID and fingerprint unlock response.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <i data-lucide="check" class="w-4 h-4 text-apple-primary shrink-0 mt-0.5"></i>
                            <span><strong>Battery Health:</strong> Verified healthy capacity and normal fast charging behavior.</span>
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    </section>

    <!-- ════ 3. TILE: REPAIR SAVINGS & LONGEVITY ════ -->
    <section id="ewaste" class="bg-apple-tile-1 text-apple-body-dark py-16 sm:py-24 border-b border-white/10 text-center overflow-hidden">
        <div class="max-w-[1024px] mx-auto px-4">
            
            <div class="max-w-3xl mx-auto space-y-4">
                <span class="apple-caption-strong text-apple-primary-dark uppercase tracking-widest text-[12px]">
                    Smart Repairs & Less Waste
                </span>
                <h2 class="apple-display-lg text-white">
                    We repair what's broken to save your phone and your money.
                </h2>
                <p class="apple-lead text-white/70 max-w-2xl mx-auto">
                    Instead of telling you to replace your entire phone or pay for full motherboard swaps, we isolate and repair the exact damaged component. You save up to 70% on repair costs.
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
