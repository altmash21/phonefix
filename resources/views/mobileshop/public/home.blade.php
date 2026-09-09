@extends('mobileshop.public.layout')

@section('title', 'Maurya Mobile — Official Flagship Smartphones, Certified Pre-Owned & Express Repairs')
@section('meta_description', 'Discover official sealed flagship smartphones, 50-point certified pre-owned devices, and professional 45-minute express repair laboratory in Mumbai.')

@section('subnav_title', 'Maurya Mobile')
@section('subnav_links')
    <a href="#flagships" class="hover:text-apple-ink transition-colors">Flagships</a>
    <a href="#pre-owned" class="hover:text-apple-ink transition-colors">Pre-Owned</a>
    <a href="#repair-lab" class="hover:text-apple-ink transition-colors">Repair Lab</a>
    <a href="#accessories" class="hover:text-apple-ink transition-colors">Accessories</a>
@endsection
@section('subnav_cta')
    <a href="{{ route('public.store') }}" class="apple-btn-primary text-[14px] py-1.5 px-4">
        Explore Catalog
    </a>
@endsection

@section('content')

    <!-- ════ TILE 1: PURE WHITE CANVAS (Flagship Hero — iPhone / Titanium Theme) ════ -->
    <section id="flagships" class="bg-apple-canvas text-apple-ink pt-16 pb-20 sm:pt-24 sm:pb-28 border-b border-apple-hairline text-center overflow-hidden">
        <div class="max-w-[1024px] mx-auto px-4">
            
            <!-- Headline Stack (Negative letter-spacing, tight tracking) -->
            <div class="max-w-3xl mx-auto space-y-3">
                <div class="apple-tagline text-apple-ink/60 uppercase tracking-widest text-[13px]">
                    Brand New Sealed Flagships
                </div>
                <h1 class="apple-hero-display text-apple-ink">
                    Titanium. So strong. So light. So Pro.
                </h1>
                <p class="apple-lead text-apple-ink/70 max-w-2xl mx-auto pt-1">
                    Official 100% genuine sealed devices with manufacturer warranty, valid GST tax invoice, and 45-minute Mumbai store pickup.
                </p>
                
                <!-- Dual Action Blue Pill CTAs -->
                <div class="pt-5 flex items-center justify-center gap-4 flex-wrap">
                    <a href="{{ route('public.store', ['tab' => 'new']) }}" class="apple-btn-primary">
                        Explore Flagships ({{ $newCount }})
                    </a>
                    <a href="{{ route('public.store') }}" class="apple-btn-secondary-pill">
                        Learn More
                    </a>
                </div>
            </div>

            <!-- Product Showcase: Signature Single Drop Shadow Resting on Canvas -->
            <div class="mt-12 sm:mt-16 relative max-w-4xl mx-auto">
                <div class="relative inline-block w-full">
                    <img src="{{ asset('img/hero-smartphones.jpg') }}" 
                         alt="Flagship Smartphone Collection at Maurya Mobile" 
                         class="w-full max-h-[520px] object-contain rounded-2xl apple-product-shadow mx-auto transition-transform duration-500 hover:scale-[1.01]">
                </div>
            </div>

            <!-- Live Featured Devices Quick Bar -->
            @if($featuredNew->count() > 0)
            <div class="mt-16 pt-12 border-t border-apple-hairline/60">
                <div class="text-left mb-6">
                    <span class="apple-caption-strong text-apple-ink text-[13px] uppercase tracking-wider text-apple-muted-48">Now In Stock Showroom Display</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 text-left">
                    @foreach($featuredNew->take(3) as $phone)
                    <a href="{{ route('public.product.show', $phone->id) }}" class="apple-utility-card group block text-decoration-none">
                        <div class="flex items-center justify-between text-[12px] text-apple-muted-48 mb-3">
                            <span class="uppercase font-semibold tracking-wider">{{ $phone->brand }}</span>
                            <span class="apple-caption text-emerald-600 font-semibold">● 100% Sealed</span>
                        </div>
                        <h3 class="apple-body-strong text-apple-ink group-hover:text-apple-primary transition-colors truncate">
                            {{ $phone->brand }} {{ $phone->model }}
                        </h3>
                        <div class="apple-caption text-apple-muted-48 mt-0.5">
                            {{ $phone->storage ?? '128GB' }} · {{ $phone->color ?? 'Titanium' }}
                        </div>
                        <div class="mt-4 flex items-center justify-between pt-3 border-t border-apple-hairline">
                            <span class="apple-body-strong text-apple-ink text-[19px]">₹{{ number_format($phone->selling_price, 0) }}</span>
                            <span class="apple-text-link text-[14px]">Buy <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i></span>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </section>

    <!-- ════ TILE 2: NEAR-BLACK CANVAS (#272729 — Certified Pre-Owned) ════ -->
    <section id="pre-owned" class="bg-apple-tile-1 text-apple-body-dark pt-20 pb-24 sm:pt-28 sm:pb-32 border-b border-white/10 text-center overflow-hidden">
        <div class="max-w-[1024px] mx-auto px-4">
            
            <div class="max-w-3xl mx-auto space-y-3">
                <div class="apple-tagline text-apple-primary-dark uppercase tracking-widest text-[13px]">
                    Certified Pre-Owned
                </div>
                <h2 class="apple-display-lg text-white">
                    Pre-Owned. Re-engineered. Guaranteed battery health.
                </h2>
                <p class="apple-lead text-white/70 max-w-2xl mx-auto pt-1">
                    Every device passes our rigorous 50-point hardware diagnostic inspection. Backed by a 30-day replacement warranty and guaranteed 85%+ battery health.
                </p>

                <!-- Dual Action CTAs for Dark Canvas -->
                <div class="pt-5 flex items-center justify-center gap-4 flex-wrap">
                    <a href="{{ route('public.store', ['tab' => 'second_hand']) }}" class="apple-btn-primary">
                        Browse Pre-Owned ({{ $secondHandCount }})
                    </a>
                    <a href="{{ route('public.about') }}" class="apple-text-link-on-dark apple-body font-normal">
                        See 50-point diagnostic criteria <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>

            <!-- Pre-Owned Showcase Grid with Signature Shadow -->
            @if($featuredSecondHand->count() > 0)
            <div class="mt-14 sm:mt-20 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 text-left">
                @foreach($featuredSecondHand->take(3) as $cpo)
                <a href="{{ route('public.product.show', $cpo->id) }}" class="bg-apple-tile-2 border border-white/10 rounded-[18px] p-6 block text-decoration-none group hover:border-white/30 transition-all">
                    <div class="flex items-center justify-between text-[12px] text-white/50 mb-3">
                        <span class="uppercase tracking-wider font-semibold">{{ $cpo->brand }}</span>
                        @php
                            $gradeFormatted = match($cpo->condition_grade ?? '') {
                                'like_new_A_plus' => 'Grade A+ Like New',
                                'good_A'          => 'Grade A Excellent',
                                'fair_B'          => 'Grade B Fair',
                                default           => 'Certified Grade A'
                            };
                        @endphp
                        <span class="text-apple-primary-dark font-medium text-[11px] px-2 py-0.5 rounded-full border border-apple-primary-dark/30">
                            {{ $gradeFormatted }}
                        </span>
                    </div>

                    <h3 class="apple-body-strong text-white group-hover:text-apple-primary-dark transition-colors truncate">
                        {{ $cpo->brand }} {{ $cpo->model }}
                    </h3>
                    <div class="apple-caption text-white/60 mt-0.5">
                        {{ $cpo->storage ?? '128GB' }} · {{ $cpo->color ?? 'Original' }}
                    </div>

                    <div class="mt-6 pt-4 border-t border-white/10 flex items-center justify-between">
                        <div>
                            <span class="text-[12px] text-white/40 block">Pre-Owned Value</span>
                            <span class="apple-body-strong text-white text-[20px]">₹{{ number_format($cpo->selling_price, 0) }}</span>
                        </div>
                        <span class="apple-text-link-on-dark text-[14px]">View Device <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i></span>
                    </div>
                </a>
                @endforeach
            </div>
            @endif

        </div>
    </section>

    <!-- ════ TILE 3: PARCHMENT CANVAS (#f5f5f7 — Precision Service Lab) ════ -->
    <section id="repair-lab" class="bg-apple-parchment text-apple-ink pt-20 pb-24 sm:pt-28 sm:pb-32 border-b border-apple-hairline text-center overflow-hidden">
        <div class="max-w-[1024px] mx-auto px-4">
            
            <div class="max-w-3xl mx-auto space-y-3">
                <div class="apple-tagline text-apple-ink/60 uppercase tracking-widest text-[13px]">
                    Maurya Service Laboratory
                </div>
                <h2 class="apple-display-lg text-apple-ink">
                    45-Minute Precision Repair Lab.
                </h2>
                <p class="apple-lead text-apple-ink/70 max-w-2xl mx-auto pt-1">
                    Board-level micro-soldering, authentic OEM display assemblies, and genuine battery health renewals with zero customer data wipe.
                </p>

                <div class="pt-5 flex items-center justify-center gap-4 flex-wrap">
                    <a href="{{ route('public.track_repair') }}" class="apple-btn-primary">
                        Track Live Job Sheet
                    </a>
                    <a href="{{ route('public.contact') }}" class="apple-btn-secondary-pill">
                        Book Counter Slot
                    </a>
                </div>
            </div>

            <!-- Three Precision Pillars (Museum Layout) -->
            <div class="mt-14 sm:mt-18 grid grid-cols-1 md:grid-cols-3 gap-8 text-center pt-8 border-t border-apple-hairline">
                <div class="space-y-2">
                    <div class="apple-display-md text-apple-ink font-semibold">15,000+</div>
                    <div class="apple-body-strong text-apple-ink">Revived Smartphones</div>
                    <p class="apple-caption text-apple-muted-48 max-w-xs mx-auto">
                        Recovered from water damage, motherboard trace fractures, and crushed glass panels since 2018.
                    </p>
                </div>
                <div class="space-y-2">
                    <div class="apple-display-md text-apple-ink font-semibold">45 Min</div>
                    <div class="apple-body-strong text-apple-ink">Express Turnaround</div>
                    <p class="apple-caption text-apple-muted-48 max-w-xs mx-auto">
                        Display and battery replacements executed live while you wait inside our air-conditioned showroom.
                    </p>
                </div>
                <div class="space-y-2">
                    <div class="apple-display-md text-apple-ink font-semibold">99.2%</div>
                    <div class="apple-body-strong text-apple-ink">First-Time Resolution</div>
                    <p class="apple-caption text-apple-muted-48 max-w-xs mx-auto">
                        Zero data wipe policy. All storage and personal photos preserved intact under clean bench conditions.
                    </p>
                </div>
            </div>

        </div>
    </section>

    <!-- ════ TILE 4: DARK 2 CANVAS (#2a2a2c — 2-Up Genuine Accessories Gallery) ════ -->
    <section id="accessories" class="bg-apple-tile-2 text-apple-body-dark pt-20 pb-24 sm:pt-28 sm:pb-32 border-b border-white/10 overflow-hidden">
        <div class="max-w-[1024px] mx-auto px-4">
            
            <div class="text-center max-w-2xl mx-auto mb-14 space-y-3">
                <div class="apple-tagline text-apple-primary-dark uppercase tracking-widest text-[13px]">
                    Engineered Ecosystem
                </div>
                <h2 class="apple-display-lg text-white">
                    Genuine Accessories. MagSafe & Fast Power.
                </h2>
                <p class="apple-lead text-white/70">
                    Precision-matched silicone shells, 15W wireless pucks, and 120W GaN adapters.
                </p>
            </div>

            <!-- 2-Up Side-by-Side Canvas Tiles -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                
                <!-- Tile A: MagSafe & Covers -->
                <div class="bg-apple-tile-1 border border-white/10 rounded-[18px] p-8 sm:p-10 flex flex-col justify-between">
                    <div>
                        <span class="apple-caption text-apple-primary-dark uppercase tracking-wider font-semibold">MagSafe Ecosystem</span>
                        <h3 class="apple-display-md text-white mt-2">
                            Snaps into place.<br>Charges seamlessly.
                        </h3>
                        <p class="apple-body text-white/70 mt-3">
                            Official military-grade drop-certified silicone, carbon-fiber cases, and magnetic kickstand wallets for iPhone & Galaxy.
                        </p>
                    </div>
                    <div class="mt-8 pt-6 border-t border-white/10">
                        <a href="{{ route('public.store', ['tab' => 'covers']) }}" class="apple-btn-primary text-[14px]">
                            Explore Covers & Wallets
                        </a>
                    </div>
                </div>

                <!-- Tile B: High-Speed GaN Power -->
                <div class="bg-apple-tile-1 border border-white/10 rounded-[18px] p-8 sm:p-10 flex flex-col justify-between">
                    <div>
                        <span class="apple-caption text-apple-primary-dark uppercase tracking-wider font-semibold">GaN Fast Charging</span>
                        <h3 class="apple-display-md text-white mt-2">
                            Up to 120W.<br>Zero thermal throttle.
                        </h3>
                        <p class="apple-body text-white/70 mt-3">
                            Multi-port Gallium Nitride bricks, braided Kevlar Type-C cords, and shatterproof 9D edge-to-edge tempered glass.
                        </p>
                    </div>
                    <div class="mt-8 pt-6 border-t border-white/10">
                        <a href="{{ route('public.store') }}" class="apple-btn-primary text-[14px]">
                            Shop Fast Chargers & Audio
                        </a>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- ════ TILE 5: PURE WHITE CANVAS (#ffffff — Search & Store Finder Pill) ════ -->
    <section class="bg-apple-canvas text-apple-ink py-20 sm:py-24 text-center">
        <div class="max-w-[768px] mx-auto px-4 space-y-6">
            
            <h2 class="apple-display-md text-apple-ink">
                Looking for a specific device?
            </h2>
            <p class="apple-body text-apple-muted-48">
                Search our real-time physical inventory at our Linking Road showroom.
            </p>

            <!-- Apple Pill Search Input -->
            <form action="{{ route('public.store') }}" method="GET" class="relative max-w-lg mx-auto">
                <input type="text" name="q" placeholder="Search iPhone 16 Pro, S24 Ultra, OnePlus 12..." 
                       class="apple-search-input w-full pl-12 pr-28 text-[15px]">
                <svg class="w-4 h-4 text-apple-muted-48 absolute left-4 top-1/2 -translate-y-1/2 stroke-current fill-none stroke-[2.2]" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <button type="submit" class="apple-btn-primary text-[13px] py-1.5 px-4 absolute right-1.5 top-1/2 -translate-y-1/2">
                    Search
                </button>
            </form>

            <!-- Brand Quick Chips -->
            <div class="flex items-center justify-center gap-2 flex-wrap pt-3 text-[13px] text-apple-muted-48">
                <span>Popular:</span>
                <a href="{{ route('public.store', ['q' => 'Apple']) }}" class="hover:text-apple-primary underline">Apple iPhone</a>
                <span>·</span>
                <a href="{{ route('public.store', ['q' => 'Samsung']) }}" class="hover:text-apple-primary underline">Samsung Galaxy</a>
                <span>·</span>
                <a href="{{ route('public.store', ['q' => 'OnePlus']) }}" class="hover:text-apple-primary underline">OnePlus</a>
                <span>·</span>
                <a href="{{ route('public.store', ['q' => 'Google']) }}" class="hover:text-apple-primary underline">Google Pixel</a>
            </div>

        </div>
    </section>

@endsection
