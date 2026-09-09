@extends('mobileshop.public.layout')

@section('title', 'Maurya Mobile — Premium Smartphones, Certified Pre-Owned & Express Repairs')
@section('meta_description', 'Discover official sealed brand new smartphones, 50-point certified pre-owned devices, and professional 45-minute express repair lab in Mumbai.')

@section('content')

    <!-- ════ 1. PROPER EDITORIAL HERO SECTION (AIRBNB X CONSUMER TECH) ════ -->
    <section class="relative overflow-hidden bg-gradient-to-b from-surface-soft/80 via-canvas to-canvas border-b border-hairline-soft pt-8 pb-12 lg:pt-14 lg:pb-16">
        <!-- Ambient decorative glow elements -->
        <div class="pointer-events-none absolute -top-24 left-1/2 -translate-x-1/2 w-[700px] h-[350px] bg-gradient-to-tr from-rausch/10 via-amber-100/30 to-purple-100/20 blur-3xl opacity-70 -z-10 rounded-full"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">
                
                <!-- Left Column (Span 7): Copy, Badges, Search & Trust Points -->
                <div class="lg:col-span-7 flex flex-col items-start text-left space-y-6">
                    
                    
                    <!-- Main Hero Typography -->
                    <div class="space-y-2 sm:space-y-3">
                        <h1 class="text-[25px] sm:text-4xl lg:text-5xl font-bold tracking-tight text-ink leading-[1.18] sm:leading-[1.14]">
                            Buy Sealed. Upgrade Certified.<br/>
                            <span class="text-rausch">Repair in 45 Minutes.</span>
                        </h1>
                        <p class="text-[14px] sm:text-[16px] text-muted leading-relaxed max-w-xl">
                            Official sealed flagship smartphones with genuine GST invoices, 50-point certified pre-owned devices with guaranteed battery health, and high-precision express repairs.
                        </p>
                    </div>

                    <!-- Signature Airbnb Omni-Search Bar Pill -->
                    <div class="w-full max-w-2xl pt-1">
                        <form action="{{ route('public.store') }}" method="GET" 
                              class="bg-canvas border border-hairline rounded-2xl sm:rounded-full shadow-airbnb-tier hover:shadow-airbnb-hover transition-airbnb flex flex-col sm:flex-row items-stretch sm:items-center divide-y sm:divide-y-0 sm:divide-x divide-hairline p-1.5 sm:p-2">
                            
                            <!-- Segment 1: Search Device -->
                            <div class="flex-1 px-4 sm:px-5 py-2 hover:bg-surface-soft rounded-xl sm:rounded-full transition-airbnb cursor-pointer group">
                                <label for="search-input" class="block text-[11px] font-bold uppercase tracking-wider text-ink">Search Device</label>
                                <input type="text" id="search-input" name="q" value="{{ request('q') }}" placeholder="iPhone 15 Pro, S24 Ultra, OnePlus..."
                                       class="w-full bg-transparent border-none text-[13.5px] sm:text-[14px] text-body placeholder:text-muted focus:outline-none truncate mt-0.5">
                            </div>

                            <!-- Segment 2: Condition Filter -->
                            <div class="w-full sm:w-44 px-4 sm:px-5 py-2 hover:bg-surface-soft rounded-xl sm:rounded-full transition-airbnb cursor-pointer">
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-ink">Condition</label>
                                <select name="tab" class="w-full bg-transparent border-none text-[13.5px] sm:text-[14px] text-body focus:outline-none cursor-pointer mt-0.5">
                                    <option value="all">All Devices</option>
                                    <option value="new">Brand New (Sealed)</option>
                                    <option value="second_hand">Certified Pre-Owned</option>
                                </select>
                            </div>

                            <!-- Segment 3: Service Lab & Rausch Orb Button -->
                            <div class="w-full sm:w-auto flex items-center justify-between pl-4 pr-1 py-1 sm:py-1.5 gap-2">
                                <div class="hidden xl:block text-left pr-2">
                                    <span class="block text-[11px] font-bold uppercase tracking-wider text-ink">Lab Ready</span>
                                    <span class="text-[13px] text-muted font-normal">Express Service</span>
                                </div>

                                <button type="submit" 
                                        class="w-full sm:w-11 sm:h-11 h-10 rounded-xl sm:rounded-full bg-rausch hover:bg-rausch-active text-white flex items-center justify-center transition-airbnb shrink-0 shadow-sm font-semibold text-sm gap-2"
                                        title="Search Device Catalog">
                                    <svg class="w-4 h-4 stroke-current fill-none stroke-[2.5]" viewBox="0 0 24 24">
                                        <circle cx="11" cy="11" r="8"></circle>
                                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                    </svg>
                                    <span class="inline sm:hidden font-medium">Search Devices</span>
                                </button>
                            </div>
                        </form>

                        
                    </div>

                    <!-- Dual CTAs -->
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-1 w-full sm:w-auto">
                        <a href="{{ route('public.store') }}" 
                           class="px-5 py-3 rounded-full bg-rausch hover:bg-rausch-active text-white text-[14px] font-semibold transition-airbnb shadow-sm flex items-center justify-center gap-2">
                            <span>Explore Smartphones</span>
                            <svg class="w-4 h-4 stroke-current fill-none stroke-[2]" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                        </a>
                        <a href="{{ route('public.track_repair') }}" 
                           class="px-5 py-3 rounded-full bg-canvas hover:bg-surface-soft border border-hairline text-ink text-[14px] font-semibold transition-airbnb flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 stroke-current fill-none stroke-[2] text-rausch" viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>
                            <span>Book / Track Repair</span>
                        </a>
                    </div>

                   
                </div>

                <!-- Right Column (Span 5): Hero Visual Showcase Card with Floating Glass Elements -->
                <div class="lg:col-span-5 relative mt-4 lg:mt-0">
                    <div class="relative mx-auto max-w-md lg:max-w-none">
                        
                        <!-- Main Frame Card -->
                        <div class="relative rounded-3xl overflow-hidden bg-white border border-hairline shadow-airbnb-tier hover:shadow-airbnb-hover transition-airbnb p-3">
                            <!-- Image Container -->
                            <div class="relative aspect-[4/3] rounded-2xl overflow-hidden bg-surface-soft border border-hairline-soft">
                                <img src="{{ asset('img/hero-smartphones.jpg') }}" 
                                     alt="Latest Flagship Smartphones at Maurya Mobile Mumbai" 
                                     class="w-full h-full object-cover object-center transform hover:scale-105 transition-transform duration-700">
                                
                                <!-- Subtle Gradient Vignette -->
                                <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent pointer-events-none"></div>

                                <!-- In-image Live Badge (Bottom Left) -->
                                <div class="absolute bottom-3 left-3 bg-canvas/95 backdrop-blur-md px-3 py-1.5 rounded-full shadow-sm flex items-center gap-2 border border-hairline">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    <span class="text-[11px] font-semibold text-ink">In Stock &amp; Verified in Mumbai</span>
                                </div>
                            </div>

                            <!-- Showcase Sub-panel with Stats & Links (Clean & Zero Overlap) -->
                            <div class="p-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-canvas">
                                <div>
                                    <div class="text-[10.5px] font-bold uppercase tracking-wider text-muted">Live Inventory</div>
                                    <div class="text-[14px] font-bold text-ink mt-0.5">
                                        {{ $newCount ?? 18 }} Sealed Phones · {{ $secondHandCount ?? 12 }} Pre-Owned
                                    </div>
                                </div>
                                <a href="{{ route('public.store') }}" 
                                   class="px-4 py-2 rounded-full bg-surface-soft hover:bg-surface-strong border border-hairline text-ink text-[12px] font-semibold transition-airbnb shrink-0 text-center shadow-xs">
                                    View Live Catalog →
                                </a>
                            </div>
                        </div>

                        <!-- Floating Micro-Card 1: Certified Pre-Owned (Top Right) -->
                        <div class="hidden sm:flex absolute -top-3 -right-3 bg-canvas/95 backdrop-blur-md p-2.5 rounded-2xl border border-hairline shadow-airbnb-tier items-center gap-2.5 z-10">
                            <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-xs shrink-0">
                                A+
                            </div>
                            <div class="text-left pr-1">
                                <div class="text-[11.5px] font-bold text-ink leading-tight">Grade A+ Certified</div>
                                <div class="text-[10.5px] text-emerald-600 font-medium flex items-center gap-1 mt-0.5">
                                    <svg class="w-3 h-3 fill-current" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    <span>85%+ Battery Health</span>
                                </div>
                            </div>
                        </div>

                       

                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ════ 2. CATEGORY STRIP (SMARTPHONES & REPAIR ONLY) ════ -->
    <section class="border-b border-hairline-soft bg-canvas sticky top-20 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-8 overflow-x-auto no-scrollbar py-4 text-center">
                <!-- All Phones -->
                <a href="{{ route('public.store') }}" 
                   class="flex flex-col items-center gap-2 group shrink-0 pb-1 text-decoration-none border-b-2 transition-airbnb {{ !request('tab') ? 'border-ink text-ink font-semibold' : 'border-transparent text-muted hover:text-ink hover:border-hairline' }}">
                    <svg class="w-6 h-6 stroke-current fill-none stroke-[1.8]" viewBox="0 0 24 24">
                        <rect x="3" y="3" width="7" height="7" rx="1.5"></rect>
                        <rect x="14" y="3" width="7" height="7" rx="1.5"></rect>
                        <rect x="14" y="14" width="7" height="7" rx="1.5"></rect>
                        <rect x="3" y="14" width="7" height="7" rx="1.5"></rect>
                    </svg>
                    <span class="text-[12px] tracking-tight">All Smartphones</span>
                </a>

                <!-- Brand New Sealed -->
                <a href="{{ route('public.store', ['tab' => 'new']) }}" 
                   class="flex flex-col items-center gap-2 group shrink-0 pb-1 text-decoration-none border-b-2 transition-airbnb {{ request('tab') === 'new' ? 'border-ink text-ink font-semibold' : 'border-transparent text-muted hover:text-ink hover:border-hairline' }}">
                    <svg class="w-6 h-6 stroke-current fill-none stroke-[1.8]" viewBox="0 0 24 24">
                        <rect x="5" y="2" width="14" height="20" rx="3"></rect>
                        <line x1="12" y1="18" x2="12.01" y2="18"></line>
                    </svg>
                    <span class="text-[12px] tracking-tight">Brand New</span>
                </a>

                <!-- Certified Pre-Owned -->
                <a href="{{ route('public.store', ['tab' => 'second_hand']) }}" 
                   class="flex flex-col items-center gap-2 group shrink-0 pb-1 text-decoration-none border-b-2 transition-airbnb {{ request('tab') === 'second_hand' ? 'border-ink text-ink font-semibold' : 'border-transparent text-muted hover:text-ink hover:border-hairline' }}">
                    <div class="relative">
                        <svg class="w-6 h-6 stroke-current fill-none stroke-[1.8]" viewBox="0 0 24 24">
                            <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"></path>
                        </svg>
                        <span class="absolute -top-1 -right-2 w-2 h-2 rounded-full bg-rausch"></span>
                    </div>
                    <span class="text-[12px] tracking-tight">Certified Pre-Owned</span>
                </a>

                <!-- Express Repair Lab -->
                <a href="{{ route('public.track_repair') }}" 
                   class="flex flex-col items-center gap-2 group shrink-0 pb-1 text-decoration-none border-b-2 transition-airbnb {{ request()->routeIs('public.track_repair') ? 'border-ink text-ink font-semibold' : 'border-transparent text-muted hover:text-ink hover:border-hairline' }}">
                    <svg class="w-6 h-6 stroke-current fill-none stroke-[1.8]" viewBox="0 0 24 24">
                        <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                    </svg>
                    <span class="text-[12px] tracking-tight">Track Repair</span>
                </a>

                <!-- 0% EMI Financing -->
                <a href="{{ route('public.contact') }}" 
                   class="flex flex-col items-center gap-2 group shrink-0 pb-1 text-decoration-none border-b-2 transition-airbnb border-transparent text-muted hover:text-ink hover:border-hairline">
                    <svg class="w-6 h-6 stroke-current fill-none stroke-[1.8]" viewBox="0 0 24 24">
                        <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                        <line x1="1" y1="10" x2="23" y2="10"></line>
                    </svg>
                    <span class="text-[12px] tracking-tight">0% EMI Financing</span>
                </a>
            </div>
        </div>
    </section>

    <!-- ════ 3. SECTION 1: BRAND NEW SMARTPHONES (DEMO SHOWCASE + EXPLORE ALL) ════ -->
    <section class="py-12 bg-canvas">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            <div class="flex items-baseline justify-between border-b border-hairline-soft pb-4">
                <div>
                    <h2 class="text-[22px] font-semibold text-ink tracking-tight">Brand New Sealed Smartphones</h2>
                    <p class="text-[14px] text-muted mt-0.5">Official manufacturer warranty, GST invoice, and complimentary tempered glass & case.</p>
                </div>
                <a href="{{ route('public.store', ['tab' => 'new']) }}" 
                   class="px-4 py-2 bg-surface-soft hover:bg-surface-strong border border-hairline rounded-full text-[13px] font-semibold text-ink transition-airbnb flex items-center gap-1.5 shadow-sm">
                    <span>Explore All New Phones</span>
                    <span>→</span>
                </a>
            </div>

            <!-- Demo Grid (Limited to Top 4 Items) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @forelse($featuredNew->take(4) as $phone)
                    <div class="group flex flex-col cursor-pointer">
                        <div class="relative aspect-square w-full rounded-[14px] overflow-hidden bg-surface-soft border border-hairline-soft mb-3">
                            <div class="w-full h-full flex flex-col items-center justify-center p-6 text-center bg-gradient-to-b from-surface-soft to-surface-strong group-hover:scale-105 transition-transform duration-300">
                                <div class="w-20 h-20 rounded-2xl bg-white shadow-airbnb-tier flex items-center justify-center text-ink mb-2">
                                    <svg class="w-10 h-10 stroke-current fill-none stroke-[1.5]" viewBox="0 0 24 24">
                                        <rect x="5" y="2" width="14" height="20" rx="3"></rect>
                                        <line x1="12" y1="18" x2="12.01" y2="18"></line>
                                    </svg>
                                </div>
                                <span class="text-[11px] font-semibold tracking-wider text-muted uppercase">{{ $phone->brand }}</span>
                                <span class="text-[13px] font-semibold text-ink">{{ $phone->model }}</span>
                            </div>

                            <div class="absolute top-3 left-3 bg-canvas text-ink text-[11px] font-semibold px-2.5 py-1 rounded-full shadow-airbnb-tier">
                                100% Sealed
                            </div>

                            <button type="button" 
                                    class="absolute top-3 right-3 w-8 h-8 rounded-full bg-white/90 hover:bg-white text-ink flex items-center justify-center transition-airbnb shadow-airbnb-tier hover:scale-110"
                                    title="Save to wishlist">
                                <svg class="w-4 h-4 stroke-current fill-none hover:fill-rausch hover:text-rausch stroke-[2]" viewBox="0 0 24 24">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-[15px]">
                                <span class="font-semibold text-ink truncate">{{ $phone->brand }} {{ $phone->model }}</span>
                                <span class="flex items-center gap-1 text-ink font-semibold shrink-0">
                                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                    <span>4.95</span>
                                </span>
                            </div>

                            <p class="text-[14px] text-muted truncate">
                                {{ $phone->storage ?? '128GB' }} · {{ $phone->ram ?? '8GB' }} RAM · {{ $phone->color ?? 'Official Edition' }}
                            </p>

                            <p class="text-[14px] text-muted truncate">
                                Official 1-Year Brand Warranty
                            </p>

                            <div class="pt-1 flex items-baseline justify-between">
                                <div class="text-[15px] font-semibold text-ink">
                                    <span>₹{{ number_format($phone->selling_price, 2) }}</span>
                                    <span class="font-normal text-muted text-[13px]"> incl. GST</span>
                                </div>
                                <a href="https://wa.me/919876543210?text={{ urlencode('Hi Maurya Mobile, I want to inquire about ' . $phone->brand . ' ' . $phone->model . ' listed on your website.') }}" 
                                   target="_blank"
                                   class="text-[13px] font-semibold text-rausch hover:underline">
                                    Inquire →
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-10 text-center text-muted bg-surface-soft rounded-[14px] border border-hairline-soft">
                        <p class="font-semibold text-ink">New sealed inventory arriving this morning.</p>
                        <a href="{{ route('public.store', ['tab' => 'new']) }}" class="text-rausch underline text-sm mt-1 inline-block">Browse all available models</a>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- ════ 4. SECTION 2: CERTIFIED PRE-OWNED (DEMO SHOWCASE + EXPLORE ALL) ════ -->
    <section class="py-12 bg-canvas border-t border-hairline-soft">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            <div class="flex items-baseline justify-between border-b border-hairline-soft pb-4">
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-[22px] font-semibold text-ink tracking-tight">Certified Pre-Owned Devices</h2>
                        <span class="px-2 py-0.5 bg-surface-strong text-ink rounded-full text-[10px] font-bold tracking-wide uppercase">50-Point Checked</span>
                    </div>
                    <p class="text-[14px] text-muted mt-0.5">Laboratory tested with minimum 80%+ battery health and 30-day store replacement warranty.</p>
                </div>
                <a href="{{ route('public.store', ['tab' => 'second_hand']) }}" 
                   class="px-4 py-2 bg-surface-soft hover:bg-surface-strong border border-hairline rounded-full text-[13px] font-semibold text-ink transition-airbnb flex items-center gap-1.5 shadow-sm">
                    <span>Explore All Pre-Owned</span>
                    <span>→</span>
                </a>
            </div>

            <!-- Demo Grid (Limited to Top 4 Items) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @forelse($featuredSecondHand->take(4) as $phone)
                    <div class="group flex flex-col cursor-pointer">
                        <div class="relative aspect-square w-full rounded-[14px] overflow-hidden bg-surface-soft border border-hairline-soft mb-3">
                            <div class="w-full h-full flex flex-col items-center justify-center p-6 text-center bg-gradient-to-b from-surface-soft to-surface-strong group-hover:scale-105 transition-transform duration-300">
                                <div class="w-20 h-20 rounded-2xl bg-white shadow-airbnb-tier flex items-center justify-center text-ink mb-2">
                                    <svg class="w-10 h-10 stroke-current fill-none stroke-[1.5]" viewBox="0 0 24 24">
                                        <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"></path>
                                    </svg>
                                </div>
                                <span class="text-[11px] font-semibold tracking-wider text-muted uppercase">{{ $phone->brand }}</span>
                                <span class="text-[13px] font-semibold text-ink">{{ $phone->model }}</span>
                            </div>

                            <div class="absolute top-3 left-3 bg-canvas text-ink text-[11px] font-semibold px-2.5 py-1 rounded-full shadow-airbnb-tier">
                                Grade {{ strtoupper($phone->condition_grade ?? 'A+') }}
                            </div>

                            <button type="button" 
                                    class="absolute top-3 right-3 w-8 h-8 rounded-full bg-white/90 hover:bg-white text-ink flex items-center justify-center transition-airbnb shadow-airbnb-tier hover:scale-110"
                                    title="Save to wishlist">
                                <svg class="w-4 h-4 stroke-current fill-none hover:fill-rausch hover:text-rausch stroke-[2]" viewBox="0 0 24 24">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                </svg>
                            </button>

                            @if($phone->battery_health)
                                <div class="absolute bottom-3 left-3 bg-canvas/90 backdrop-blur-sm text-ink text-[10px] font-semibold px-2 py-0.5 rounded-md shadow-sm">
                                    {{ $phone->battery_health }}% Battery Health
                                </div>
                            @endif
                        </div>

                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-[15px]">
                                <span class="font-semibold text-ink truncate">{{ $phone->brand }} {{ $phone->model }}</span>
                                <span class="flex items-center gap-1 text-ink font-semibold shrink-0">
                                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                    <span>4.90</span>
                                </span>
                            </div>

                            <p class="text-[14px] text-muted truncate">
                                {{ $phone->storage ?? '128GB' }} · {{ $phone->color ?? 'Clean Finish' }}
                            </p>

                            <p class="text-[14px] text-muted truncate">
                                30-Day Store Replacement Warranty
                            </p>

                            <div class="pt-1 flex items-baseline justify-between">
                                <div class="text-[15px] font-semibold text-ink">
                                    <span>₹{{ number_format($phone->selling_price, 2) }}</span>
                                    <span class="font-normal text-muted text-[13px]"> tested</span>
                                </div>
                                <a href="https://wa.me/919876543210?text={{ urlencode('Hi Maurya Mobile, I want to reserve pre-owned ' . $phone->brand . ' ' . $phone->model . ' for ₹' . number_format($phone->selling_price, 2)) }}" 
                                   target="_blank"
                                   class="text-[13px] font-semibold text-rausch hover:underline">
                                    Reserve →
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-10 text-center text-muted bg-surface-soft rounded-[14px] border border-hairline-soft">
                        <p class="font-semibold text-ink">Pre-owned devices currently undergoing inspection.</p>
                        <a href="{{ route('public.store', ['tab' => 'second_hand']) }}" class="text-rausch underline text-sm mt-1 inline-block">Browse all pre-owned inventory</a>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- ════ 5. SECTION 3: REPAIR TRACK & SERVICE LAB ════ -->
    <section class="py-14 bg-surface-soft border-t border-hairline-soft">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-canvas rounded-[20px] p-8 md:p-12 border border-hairline shadow-airbnb-tier flex flex-col lg:flex-row items-center justify-between gap-8">
                <div class="space-y-3 max-w-xl text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-surface-soft border border-hairline rounded-full text-[11px] font-bold uppercase tracking-wide text-ink">
                        <span class="w-2 h-2 rounded-full bg-rausch"></span>
                        <span>Level 4 Micro-Soldering Lab</span>
                    </div>
                    <h2 class="text-[26px] font-bold text-ink tracking-tight">Fast, Precision Smartphone Repairs</h2>
                    <p class="text-[15px] text-muted leading-relaxed">
                        Broken OLED screen, draining battery, or liquid damage? Our certified lab fixes over 95% of issues in under 45 minutes with genuine parts.
                    </p>
                    <div class="flex flex-wrap items-center gap-4 text-[13px] text-ink font-semibold pt-1 justify-center lg:justify-start">
                        <span>✓ ~35 Min Screen Replacement</span>
                        <span>✓ ~25 Min Battery Replacement</span>
                        <span>✓ Chip-Level Board Diagnosis</span>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-3 shrink-0 w-full sm:w-auto">
                    <a href="{{ route('public.track_repair') }}" 
                       class="px-6 py-3.5 bg-rausch hover:bg-rausch-active text-white rounded-sm text-[14px] font-medium transition-airbnb text-center w-full sm:w-auto shadow-sm">
                        Track Live Repair Status →
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ════ 6. RATING DISPLAY MOMENT (AIRBNB GUEST FAVORITE) ════ -->
    <section class="py-16 bg-canvas border-t border-hairline-soft">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-12">
            <div class="space-y-4">
                <div class="flex items-center justify-center gap-4">
                    <svg class="w-12 h-20 text-ink fill-current hidden sm:block opacity-90" viewBox="0 0 48 80">
                        <path d="M40 70c-3-2-8-6-12-12-5-7-8-15-9-23-1-9 1-18 4-25 1-2 2-3 4-4l-3-4c-2 1-4 3-6 5-4 8-6 18-5 28 1 9 4 18 10 26 5 7 11 11 14 13l2-5zM22 28c-2 4-3 9-3 14 0 5 2 10 4 14l3-2c-2-3-3-7-3-11 0-4 1-8 2-12l-3-3z"/>
                    </svg>

                    <div class="flex flex-col items-center">
                        <div class="text-[64px] font-bold text-ink leading-none tracking-tight">4.92</div>
                        <div class="flex items-center gap-1 mt-2 text-ink">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                        </div>
                    </div>

                    <svg class="w-12 h-20 text-ink fill-current hidden sm:block opacity-90 scale-x-[-1]" viewBox="0 0 48 80">
                        <path d="M40 70c-3-2-8-6-12-12-5-7-8-15-9-23-1-9 1-18 4-25 1-2 2-3 4-4l-3-4c-2 1-4 3-6 5-4 8-6 18-5 28 1 9 4 18 10 26 5 7 11 11 14 13l2-5zM22 28c-2 4-3 9-3 14 0 5 2 10 4 14l3-2c-2-3-3-7-3-11 0-4 1-8 2-12l-3-3z"/>
                    </svg>
                </div>

                <div>
                    <h3 class="text-[22px] font-semibold text-ink">Guest favorite</h3>
                    <p class="text-[14px] text-muted max-w-lg mx-auto mt-1">
                        Over 1,850+ verified five-star ratings on Google Maps across Mumbai.
                    </p>
                </div>
            </div>

            <!-- 2-Column Review Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-left">
                <div class="bg-canvas p-6 rounded-[14px] border border-hairline-soft shadow-airbnb-tier space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-surface-strong text-ink font-bold flex items-center justify-center text-sm">
                            AK
                        </div>
                        <div>
                            <div class="text-[14px] font-semibold text-ink">Aditya Kulkarni</div>
                            <div class="text-[12px] text-muted">Mumbai · Sealed iPhone 15</div>
                        </div>
                    </div>
                    <p class="text-[14px] text-body leading-relaxed">
                        "Got my sealed iPhone within 20 minutes at the Linking Road showroom. Flawless data transfer and genuine warranty bill. Best smartphone buying experience in the city."
                    </p>
                </div>

                <div class="bg-canvas p-6 rounded-[14px] border border-hairline-soft shadow-airbnb-tier space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-surface-strong text-ink font-bold flex items-center justify-center text-sm">
                            SM
                        </div>
                        <div>
                            <div class="text-[14px] font-semibold text-ink">Sneha Mehta</div>
                            <div class="text-[12px] text-muted">Bandra West · Screen Replacement</div>
                        </div>
                    </div>
                    <p class="text-[14px] text-body leading-relaxed">
                        "Brought in a shattered Samsung display. Their master technician replaced the OLED panel in 35 minutes and gave me a live repair tracking sheet. Completely transparent."
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ════ 7. SECTION 4: END CTA — CONTACT (LEFT) & MAP LOCATION (RIGHT) ════ -->
    <section class="py-16 bg-surface-soft border-t border-hairline-soft">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="text-center max-w-xl mx-auto space-y-2">
                <h2 class="text-[26px] font-bold text-ink tracking-tight">Visit Our Showroom or Get In Touch</h2>
                <p class="text-[14px] text-muted">Located on Linking Road, Bandra West. Walk in for device demos, instant trade-in cash, or express repairs.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
                
                <!-- Left: Contact Details (6 Cols) -->
                <div class="lg:col-span-6 bg-canvas rounded-[14px] p-8 border border-hairline shadow-airbnb-tier flex flex-col justify-between space-y-6">
                    <div class="space-y-6">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <span class="text-[13px] font-bold text-ink uppercase tracking-wider">Counters Open · 7 Days a Week</span>
                        </div>

                        <!-- Address -->
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-surface-strong text-ink flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 stroke-current fill-none stroke-[1.8]" viewBox="0 0 24 24">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-[15px] text-ink">Bandra West Showroom</h4>
                                <p class="text-[13px] text-muted leading-relaxed mt-0.5">
                                    Shop #14, Linking Road, Near Bandra Station West,<br>
                                    Mumbai, Maharashtra 400050
                                </p>
                            </div>
                        </div>

                        <!-- Phone & Desk -->
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-surface-strong text-ink flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 stroke-current fill-none stroke-[1.8]" viewBox="0 0 24 24">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-[15px] text-ink">Phone & WhatsApp Direct</h4>
                                <p class="text-[13px] text-muted mt-0.5">
                                    Counter Desk: <a href="tel:9876543210" class="font-semibold text-ink hover:text-rausch">+91 98765 43210</a>
                                </p>
                                <p class="text-[13px] text-muted">
                                    Repair Desk: <a href="tel:9876543211" class="font-semibold text-ink hover:text-rausch">+91 98765 43211</a>
                                </p>
                            </div>
                        </div>

                        <!-- Timings -->
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-surface-strong text-ink flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 stroke-current fill-none stroke-[1.8]" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-[15px] text-ink">Working Hours</h4>
                                <p class="text-[13px] text-muted mt-0.5">Monday – Sunday: 10:00 AM – 9:30 PM</p>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-hairline-soft flex flex-wrap items-center gap-3">
                        <a href="https://wa.me/919876543210" target="_blank"
                           class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-sm text-[13px] font-medium transition-airbnb flex items-center gap-1.5 shadow-sm">
                            <span>Chat Live on WhatsApp</span>
                        </a>
                        <a href="{{ route('public.contact') }}"
                           class="px-5 py-2.5 bg-canvas border border-ink hover:bg-surface-soft text-ink rounded-sm text-[13px] font-medium transition-airbnb">
                            Send Online Inquiry →
                        </a>
                    </div>
                </div>

                <!-- Right: Map Location Card (6 Cols) -->
                <div class="lg:col-span-6 bg-canvas rounded-[14px] p-8 border border-hairline shadow-airbnb-tier flex flex-col justify-between space-y-6">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-[18px] font-bold text-ink">Store Location & Directions</h3>
                            <span class="text-[12px] font-semibold text-muted">5 min from Bandra Station</span>
                        </div>

                        <!-- Clean Map Plate Placeholder / Directions Guide -->
                        <div class="relative aspect-video w-full rounded-[12px] overflow-hidden bg-surface-soft border border-hairline-soft flex flex-col items-center justify-center text-center p-6 space-y-3">
                            <div class="w-12 h-12 rounded-full bg-rausch/10 text-rausch flex items-center justify-center">
                                <svg class="w-6 h-6 stroke-current fill-none stroke-[2]" viewBox="0 0 24 24">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-[15px] text-ink">Linking Road Commercial Hub</h4>
                                <p class="text-[13px] text-muted max-w-sm mx-auto mt-0.5">
                                    Easily accessible via Western Express Highway and SV Road. Dedicated valet and roadside parking available.
                                </p>
                            </div>
                        </div>

                        <div class="text-[13px] text-muted space-y-1">
                            <p><strong>Transit:</strong> Bandra Suburban Railway Station (Western & Harbour line) — 450 meters walk.</p>
                            <p><strong>Landmark:</strong> Directly opposite Linking Road Shoppers Stop lane.</p>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-hairline-soft">
                        <a href="https://maps.google.com/?q=Linking+Road+Bandra+West+Mumbai" target="_blank"
                           class="w-full py-3 bg-rausch hover:bg-rausch-active text-white rounded-sm text-[14px] font-medium transition-airbnb text-center flex items-center justify-center gap-2 shadow-sm">
                            <svg class="w-4 h-4 stroke-current fill-none stroke-[2]" viewBox="0 0 24 24">
                                <polygon points="3 11 22 2 13 21 11 13 3 11"></polygon>
                            </svg>
                            <span>Open Navigation in Google Maps</span>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>

@endsection
