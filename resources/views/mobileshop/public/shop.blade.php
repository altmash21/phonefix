@extends('mobileshop.public.layout')

@section('title', 'Store — Flagship Smartphones & Certified Pre-Owned Catalog | Maurya Mobile')
@section('meta_description', 'Explore brand new sealed smartphones and 50-point certified pre-owned devices in stock at Maurya Mobile Mumbai.')

@section('subnav_title', 'Store')
@section('subnav_links')
    <a href="{{ route('public.store', ['tab' => 'new', 'q' => $query]) }}" class="{{ $tab === 'new' ? 'text-apple-ink font-bold' : 'hover:text-apple-ink transition-colors' }}">New Phones</a>
    <a href="{{ route('public.store', ['tab' => 'second_hand', 'q' => $query]) }}" class="{{ $tab === 'second_hand' ? 'text-apple-ink font-bold' : 'hover:text-apple-ink transition-colors' }}">Second Hand</a>
    <a href="{{ route('public.store', ['tab' => 'all', 'q' => $query]) }}" class="{{ $tab === 'all' ? 'text-apple-ink font-bold' : 'hover:text-apple-ink transition-colors' }}">Shop</a>
    <a href="{{ route('public.track_repair') }}" class="hover:text-apple-ink transition-colors">Repair</a>
@endsection

@section('content')

    <!-- ════ 1. STORE HERO HEADLINE & SEARCH PILL ════ -->
    <div class="bg-apple-parchment border-b border-apple-hairline py-12 sm:py-16">
        <div class="max-w-[1024px] mx-auto px-4">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div>
                    <h1 class="apple-display-lg text-apple-ink">
                        Store. <span class="text-apple-muted-48 font-normal">The finest technology, verified.</span>
                    </h1>
                    <p class="apple-body text-apple-muted-48 mt-2">
                        {{ store_name() }} &bull; {{ store_city() }} &bull; Live inventory available for instant counter inspection.
                    </p>
                </div>

                <!-- Apple Pill Search Input -->
                <form action="{{ route('public.store') }}" method="GET" class="w-full md:w-80">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <div class="relative">
                        <input type="text" name="q" value="{{ $query }}" placeholder="Search model, brand, color..." 
                               class="apple-search-input w-full pl-10 pr-4 text-[15px]">
                        <svg class="w-4 h-4 text-apple-muted-48 absolute left-3.5 top-1/2 -translate-y-1/2 stroke-current fill-none stroke-[2.2]" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </div>
                </form>
            </div>

            <!-- Category Filter Chips (Pill grammar) -->
            <div class="flex items-center gap-2 pt-8 overflow-x-auto no-scrollbar">
                <a href="{{ route('public.store', ['tab' => 'all', 'q' => $query]) }}" 
                   class="px-4 py-2 rounded-full text-[14px] transition-all shrink-0 {{ $tab === 'all' ? 'bg-apple-ink text-white font-medium' : 'bg-white text-apple-ink border border-apple-hairline hover:border-apple-ink' }}">
                    All Smartphones ({{ $newPhones->count() + $secondHandPhones->count() }})
                </a>
                <a href="{{ route('public.store', ['tab' => 'new', 'q' => $query]) }}" 
                   class="px-4 py-2 rounded-full text-[14px] transition-all shrink-0 {{ $tab === 'new' ? 'bg-apple-ink text-white font-medium' : 'bg-white text-apple-ink border border-apple-hairline hover:border-apple-ink' }}">
                    Brand New Flagships ({{ $newPhones->count() }})
                </a>
                <a href="{{ route('public.store', ['tab' => 'second_hand', 'q' => $query]) }}" 
                   class="px-4 py-2 rounded-full text-[14px] transition-all shrink-0 {{ $tab === 'second_hand' ? 'bg-apple-ink text-white font-medium' : 'bg-white text-apple-ink border border-apple-hairline hover:border-apple-ink' }}">
                    Certified Pre-Owned ({{ $secondHandPhones->count() }})
                </a>
            </div>
        </div>
    </div>

    <!-- ════ 2. MAIN CATALOG PRODUCT GRID (Store Utility Cards, 18px Radius, Single Product Shadow) ════ -->
    <div class="max-w-[1024px] mx-auto px-4 py-16">

        @if(!empty($query))
        <div class="mb-8 flex items-center justify-between">
            <span class="apple-body text-apple-muted-48">
                Search results for "<strong class="text-apple-ink">{{ $query }}</strong>"
            </span>
            <a href="{{ route('public.store', ['tab' => $tab]) }}" class="apple-text-link text-[14px]">Clear Search</a>
        </div>
        @endif

        <!-- SECTION A: BRAND NEW SEALED (When tab is 'all' or 'new') -->
        @if(($tab === 'all' || $tab === 'new') && $newPhones->count() > 0)
        <div class="mb-16">
            <div class="flex items-center justify-between pb-4 mb-6 border-b border-apple-hairline">
                <div>
                    <h2 class="apple-tagline text-apple-ink">Brand New Sealed Smartphones</h2>
                    <p class="apple-caption text-apple-muted-48 mt-0.5">100% genuine factory sealed with brand warranty and GST invoice.</p>
                </div>
                <span class="apple-caption text-apple-muted-48">{{ $newPhones->count() }} In Stock</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($newPhones as $item)
                <div class="apple-utility-card flex flex-col justify-between group">
                    <div>
                        <!-- 1:1 Product Image Pedestal -->
                        <div class="w-full aspect-square bg-apple-parchment rounded-[8px] p-6 flex items-center justify-center relative overflow-hidden mb-4">
                            <div class="absolute top-3 left-3">
                                <span class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                                    ● Sealed
                                </span>
                            </div>
                            @php
                                $itemPhoto = $item->photo_path ?? $item->box_photo_path ?? null;
                            @endphp
                            <img src="{{ $itemPhoto ? asset($itemPhoto) : asset('img/hero-smartphones.jpg') }}" 
                                 alt="{{ $item->brand }} {{ $item->model }}" 
                                 class="max-h-[160px] object-contain apple-product-shadow transition-transform duration-300 group-hover:scale-105">
                        </div>

                        <!-- Card Meta -->
                        <div class="text-[12px] text-apple-muted-48 uppercase tracking-wider font-semibold">
                            {{ $item->brand }}
                        </div>
                        <h3 class="apple-body-strong text-apple-ink group-hover:text-apple-primary transition-colors truncate mt-1">
                            {{ $item->brand }} {{ $item->model }}
                        </h3>
                        <div class="apple-caption text-apple-muted-48 mt-0.5">
                            {{ $item->storage ?? '128GB' }} · {{ $item->color ?? 'Original' }}
                        </div>
                    </div>

                    <!-- Price & Action Link -->
                    <div class="mt-6 pt-4 border-t border-apple-hairline flex items-center justify-between">
                        <div>
                            <span class="text-[11px] text-apple-muted-48 block">Showroom Price</span>
                            <div class="flex items-baseline gap-1">
                                <span class="apple-body-strong text-apple-ink text-[19px]">₹{{ number_format($item->selling_price, 0) }}</span>
                            </div>
                            <span class="text-[10.5px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-1.5 py-0.5 rounded inline-block mt-0.5">
                                0% EMI Available
                            </span>
                        </div>
                        <a href="{{ route('public.product.show', $item->id) }}" class="apple-btn-primary text-[13px] py-1.5 px-4">
                            Buy Now
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- SECTION B: CERTIFIED PRE-OWNED (When tab is 'all' or 'second_hand') -->
        @if(($tab === 'all' || $tab === 'second_hand') && $secondHandPhones->count() > 0)
        <div class="mb-16">
            <div class="flex items-center justify-between pb-4 mb-6 border-b border-apple-hairline">
                <div>
                    <h2 class="apple-tagline text-apple-ink">Certified Pre-Owned</h2>
                    <p class="apple-caption text-apple-muted-48 mt-0.5">50-point diagnostic seal, 85%+ battery health, and 30-day warranty.</p>
                </div>
                <span class="apple-caption text-apple-muted-48">{{ $secondHandPhones->count() }} In Stock</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($secondHandPhones as $cpo)
                <div class="apple-utility-card flex flex-col justify-between group">
                    <div>
                        <!-- 1:1 Product Image Pedestal -->
                        <div class="w-full aspect-square bg-apple-parchment rounded-[8px] p-6 flex items-center justify-center relative overflow-hidden mb-4">
                            @php
                                $gradeText = match($cpo->condition_grade ?? '') {
                                    'like_new_A_plus' => 'A+ Like New',
                                    'good_A'          => 'A Good',
                                    'fair_B'          => 'B Fair',
                                    default           => 'Certified A+'
                                };
                                $cpoPhoto = $cpo->photo_path ?? $cpo->box_photo_path ?? null;
                            @endphp
                            <div class="absolute top-3 left-3">
                                <span class="text-[11px] font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-full border border-blue-200">
                                    Grade {{ $gradeText }}
                                </span>
                            </div>
                            <img src="{{ $cpoPhoto ? asset($cpoPhoto) : asset('img/hero-smartphones.jpg') }}" 
                                 alt="{{ $cpo->brand }} {{ $cpo->model }}" 
                                 class="max-h-[160px] object-contain apple-product-shadow transition-transform duration-300 group-hover:scale-105">
                        </div>

                        <!-- Card Meta -->
                        <div class="text-[12px] text-apple-muted-48 uppercase tracking-wider font-semibold">
                            {{ $cpo->brand }}
                        </div>
                        <h3 class="apple-body-strong text-apple-ink group-hover:text-apple-primary transition-colors truncate mt-1">
                            {{ $cpo->brand }} {{ $cpo->model }}
                        </h3>
                        <div class="apple-caption text-apple-muted-48 mt-0.5">
                            {{ $cpo->storage ?? '128GB' }} · {{ $cpo->color ?? 'Original' }}
                        </div>
                    </div>

                    <!-- Price & Action Link -->
                    <div class="mt-6 pt-4 border-t border-apple-hairline flex items-center justify-between">
                        <div>
                            <span class="text-[11px] text-apple-muted-48 block">Pre-Owned Value</span>
                            <div class="flex items-baseline gap-1">
                                <span class="apple-body-strong text-apple-ink text-[19px]">₹{{ number_format($cpo->selling_price, 0) }}</span>
                            </div>
                            <span class="text-[10.5px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-1.5 py-0.5 rounded inline-block mt-0.5">
                                0% EMI Available
                            </span>
                        </div>
                        <a href="{{ route('public.product.show', $cpo->id) }}" class="apple-btn-primary text-[13px] py-1.5 px-4">
                            Buy Now
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Empty State -->
        @if($newPhones->count() === 0 && $secondHandPhones->count() === 0)
        <div class="text-center py-24 bg-apple-parchment rounded-[18px] border border-apple-hairline p-8 space-y-4">
            <h3 class="apple-display-md text-apple-ink">No devices found.</h3>
            <p class="apple-body text-apple-muted-48 max-w-md mx-auto">
                We could not find any in-stock smartphones matching your criteria. Try searching for a broader term or contact our store desk.
            </p>
            <div class="pt-2">
                <a href="{{ route('public.store') }}" class="apple-btn-primary">
                    View All Smartphones
                </a>
            </div>
        </div>
        @endif

    </div>

@endsection
