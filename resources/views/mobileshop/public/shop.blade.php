@extends('mobileshop.public.layout')

@section('title', 'Explore Marketplace Catalog — MobiTrack Store')
@section('meta_description', 'Browse official brand new smartphones and 50-point certified pre-owned devices in stock at our Mumbai showroom.')

@section('content')

    <!-- ════ 1. EXPLORE CATALOG HEADER & SEARCH PILL ════ -->
    <div class="border-b border-hairline-soft bg-canvas py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h1 class="text-[26px] font-bold text-ink tracking-tight">Explore In-Stock Catalog</h1>
                <p class="text-[14px] text-muted mt-1">Live physical showroom inventory available at Linking Road, Bandra West.</p>
            </div>

            <!-- Clean Search Input (Airbnb Text Input Spec) -->
            <form action="{{ route('public.store') }}" method="GET" class="w-full md:w-80">
                <input type="hidden" name="tab" value="{{ request('tab', 'all') }}">
                <div class="relative flex items-center">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search brand or model..."
                           class="w-full pl-11 pr-4 py-3 bg-canvas border border-hairline hover:border-ink focus:border-ink rounded-full text-[14px] text-ink placeholder:text-muted focus:outline-none transition-airbnb shadow-airbnb-tier">
                    <svg class="w-4 h-4 text-muted absolute left-4 pointer-events-none stroke-current fill-none stroke-[2]" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>
            </form>
        </div>
    </div>

    <!-- ════ 2. AIRBNB CATEGORY FILTER PILLS ════ -->
    <div class="bg-canvas border-b border-hairline-soft sticky top-20 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            @php $currentTab = request('tab', 'all'); @endphp
            <div class="flex items-center gap-2 overflow-x-auto no-scrollbar">
                <a href="{{ route('public.store', ['tab' => 'all', 'q' => request('q')]) }}" 
                   class="px-4 py-2 rounded-full text-[13px] font-medium transition-airbnb shrink-0 border {{ $currentTab === 'all' ? 'bg-ink text-white border-ink' : 'bg-canvas text-ink border-hairline hover:border-ink' }}">
                    All Smartphones ({{ ($newPhones->count() + $secondHandPhones->count()) }})
                </a>
                <a href="{{ route('public.store', ['tab' => 'new', 'q' => request('q')]) }}" 
                   class="px-4 py-2 rounded-full text-[13px] font-medium transition-airbnb shrink-0 border {{ $currentTab === 'new' ? 'bg-ink text-white border-ink' : 'bg-canvas text-ink border-hairline hover:border-ink' }}">
                    Brand New ({{ $newPhones->count() }})
                </a>
                <a href="{{ route('public.store', ['tab' => 'second_hand', 'q' => request('q')]) }}" 
                   class="px-4 py-2 rounded-full text-[13px] font-medium transition-airbnb shrink-0 border {{ $currentTab === 'second_hand' ? 'bg-ink text-white border-ink' : 'bg-canvas text-ink border-hairline hover:border-ink' }}">
                    Certified Pre-Owned ({{ $secondHandPhones->count() }})
                </a>
            </div>
        </div>
    </div>

    <!-- ════ 3. MAIN CATALOG CONTAINER ════ -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-12">

        <!-- BRAND NEW SMARTPHONES SECTION -->
        @if(in_array($currentTab, ['all', 'new']))
        <section class="space-y-6">
            <div class="flex items-baseline justify-between border-b border-hairline-soft pb-3">
                <div class="flex items-center gap-2">
                    <h2 class="text-[20px] font-semibold text-ink">Brand New Sealed Smartphones</h2>
                    <span class="text-[12px] text-muted">({{ $newPhones->count() }} available)</span>
                </div>
                <span class="text-[12px] font-medium text-muted">Official Brand Warranty</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @forelse($newPhones as $np)
                    <div class="group flex flex-col cursor-pointer">
                        <!-- Photo Plate -->
                        <div class="relative aspect-square w-full rounded-[14px] overflow-hidden bg-surface-soft border border-hairline-soft mb-3">
                            <div class="w-full h-full flex flex-col items-center justify-center p-6 text-center bg-gradient-to-b from-surface-soft to-surface-strong group-hover:scale-105 transition-transform duration-300">
                                <div class="w-20 h-20 rounded-2xl bg-white shadow-airbnb-tier flex items-center justify-center text-ink mb-2">
                                    <svg class="w-10 h-10 stroke-current fill-none stroke-[1.5]" viewBox="0 0 24 24">
                                        <rect x="5" y="2" width="14" height="20" rx="3"></rect>
                                        <line x1="12" y1="18" x2="12.01" y2="18"></line>
                                    </svg>
                                </div>
                                <span class="text-[11px] font-semibold tracking-wider text-muted uppercase">{{ $np->brand }}</span>
                                <span class="text-[13px] font-semibold text-ink">{{ $np->model }}</span>
                            </div>

                            <!-- Top-Left Badge -->
                            <div class="absolute top-3 left-3 bg-canvas text-ink text-[11px] font-semibold px-2.5 py-1 rounded-full shadow-airbnb-tier">
                                100% Sealed
                            </div>

                            <!-- Top-Right Heart Button -->
                            <button type="button" 
                                    class="absolute top-3 right-3 w-8 h-8 rounded-full bg-white/90 hover:bg-white text-ink flex items-center justify-center transition-airbnb shadow-airbnb-tier hover:scale-110"
                                    title="Save to wishlist">
                                <svg class="w-4 h-4 stroke-current fill-none hover:fill-rausch hover:text-rausch stroke-[2]" viewBox="0 0 24 24">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- Metadata Block -->
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-[15px]">
                                <span class="font-semibold text-ink truncate">{{ $np->brand }} {{ $np->model }}</span>
                                <span class="flex items-center gap-1 text-ink font-semibold shrink-0">
                                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                    <span>4.95</span>
                                </span>
                            </div>

                            <p class="text-[14px] text-muted truncate">
                                {{ $np->storage ?? '128GB' }} · {{ $np->ram ?? '8GB' }} RAM · {{ $np->color ?? 'Official Edition' }}
                            </p>

                            <p class="text-[14px] text-muted truncate">
                                Free 9D Tempered Glass + Protective Case
                            </p>

                            <div class="pt-1 flex items-baseline justify-between">
                                <div class="text-[15px] font-semibold text-ink">
                                    <span>₹{{ number_format($np->selling_price, 2) }}</span>
                                    <span class="font-normal text-muted text-[13px]"> incl. GST</span>
                                </div>
                                <a href="https://wa.me/919876543210?text={{ urlencode('Hello MobiTrack, I would like to buy brand new ' . $np->brand . ' ' . $np->model . ' for ₹' . number_format($np->selling_price, 2)) }}" 
                                   target="_blank"
                                   class="text-[13px] font-semibold text-rausch hover:underline">
                                    Buy / Inquire →
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-10 text-center bg-surface-soft rounded-[14px] border border-hairline-soft text-muted">
                        <p class="font-medium text-ink">No brand new phone models matched your search.</p>
                    </div>
                @endforelse
            </div>
        </section>
        @endif

        <!-- CERTIFIED PRE-OWNED SECTION -->
        @if(in_array($currentTab, ['all', 'second_hand']))
        <section class="space-y-6">
            <div class="flex items-baseline justify-between border-b border-hairline-soft pb-3">
                <div class="flex items-center gap-2">
                    <h2 class="text-[20px] font-semibold text-ink">Certified Pre-Owned Devices</h2>
                    <span class="text-[12px] text-muted">({{ $secondHandPhones->count() }} available)</span>
                </div>
                <span class="text-[12px] font-medium text-muted">50-Point Inspection Guaranteed</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @forelse($secondHandPhones as $sp)
                    <div class="group flex flex-col cursor-pointer">
                        <!-- Photo Plate -->
                        <div class="relative aspect-square w-full rounded-[14px] overflow-hidden bg-surface-soft border border-hairline-soft mb-3">
                            <div class="w-full h-full flex flex-col items-center justify-center p-6 text-center bg-gradient-to-b from-surface-soft to-surface-strong group-hover:scale-105 transition-transform duration-300">
                                <div class="w-20 h-20 rounded-2xl bg-white shadow-airbnb-tier flex items-center justify-center text-ink mb-2">
                                    <svg class="w-10 h-10 stroke-current fill-none stroke-[1.5]" viewBox="0 0 24 24">
                                        <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"></path>
                                    </svg>
                                </div>
                                <span class="text-[11px] font-semibold tracking-wider text-muted uppercase">{{ $sp->brand }}</span>
                                <span class="text-[13px] font-semibold text-ink">{{ $sp->model }}</span>
                            </div>

                            <!-- Top-Left Badge -->
                            <div class="absolute top-3 left-3 bg-canvas text-ink text-[11px] font-semibold px-2.5 py-1 rounded-full shadow-airbnb-tier">
                                Grade {{ strtoupper($sp->condition_grade ?? 'A+') }}
                            </div>

                            <!-- Top-Right Heart Button -->
                            <button type="button" 
                                    class="absolute top-3 right-3 w-8 h-8 rounded-full bg-white/90 hover:bg-white text-ink flex items-center justify-center transition-airbnb shadow-airbnb-tier hover:scale-110"
                                    title="Save to wishlist">
                                <svg class="w-4 h-4 stroke-current fill-none hover:fill-rausch hover:text-rausch stroke-[2]" viewBox="0 0 24 24">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                </svg>
                            </button>

                            @if($sp->battery_health)
                                <div class="absolute bottom-3 left-3 bg-canvas/90 backdrop-blur-sm text-ink text-[10px] font-semibold px-2 py-0.5 rounded-md shadow-sm">
                                    {{ $sp->battery_health }}% Battery Health
                                </div>
                            @endif
                        </div>

                        <!-- Metadata Block -->
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-[15px]">
                                <span class="font-semibold text-ink truncate">{{ $sp->brand }} {{ $sp->model }}</span>
                                <span class="flex items-center gap-1 text-ink font-semibold shrink-0">
                                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                    <span>4.90</span>
                                </span>
                            </div>

                            <p class="text-[14px] text-muted truncate">
                                {{ $sp->storage ?? '128GB' }} · {{ $sp->color ?? 'Clean Condition' }}
                            </p>

                            <p class="text-[14px] text-muted truncate">
                                30-Day Testing & Replacement Warranty
                            </p>

                            <div class="pt-1 flex items-baseline justify-between">
                                <div class="text-[15px] font-semibold text-ink">
                                    <span>₹{{ number_format($sp->selling_price, 2) }}</span>
                                    <span class="font-normal text-muted text-[13px]"> tested</span>
                                </div>
                                <a href="https://wa.me/919876543210?text={{ urlencode('Hello MobiTrack, I want to reserve pre-owned ' . $sp->brand . ' ' . $sp->model . ' for ₹' . number_format($sp->selling_price, 2)) }}" 
                                   target="_blank"
                                   class="text-[13px] font-semibold text-rausch hover:underline">
                                    Reserve →
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-10 text-center bg-surface-soft rounded-[14px] border border-hairline-soft text-muted">
                        <p class="font-medium text-ink">No certified pre-owned devices matched your search.</p>
                    </div>
                @endforelse
            </div>
        </section>
        @endif

    </div>

@endsection
