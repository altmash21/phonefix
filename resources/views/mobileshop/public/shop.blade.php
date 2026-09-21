@extends('mobileshop.public.layout')

@section('title', 'Accessories & Spares Catalog — Fast Chargers, Cases & OEM Parts | ' . store_name())
@section('meta_description', 'Browse authentic mobile accessories, GaN fast chargers, 11D tempered glass, heavy-duty phone cases, and OEM spare parts in ' . store_city() . '.')

@section('content')

    <!-- ════ 1. CATALOG HEADER & FILTERS (White Canvas with Hairline Divider) ════ -->
    <div class="bg-white border-b border-[#e5e7eb] py-12 sm:py-16">
        <div class="max-w-[1200px] mx-auto px-4 sm:px-6">
            
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-widest text-[#6b7280] block mb-2">
                        {{ store_name() }} Tested Inventory
                    </span>
                    <h1 class="cal-display-lg text-[#111111]">
                        Accessories & Spares.
                    </h1>
                    <p class="text-[#374151] text-base mt-2 max-w-xl">
                        In-stock fast GaN chargers, military-grade cases, 11D glass, and OEM batteries in {{ store_city() }}.
                    </p>
                </div>

                <!-- Cal.com Styled Search Form (40px, 8px radius) -->
                <form action="{{ route('public.store') }}" method="GET" class="w-full md:w-80">
                    <input type="hidden" name="category" value="{{ $categorySlug }}">
                    <input type="hidden" name="brand" value="{{ $brandFilter }}">
                    <div class="relative">
                        <input type="text" name="q" value="{{ $query }}" placeholder="Search charger, cover, battery..." 
                               class="w-full pl-10 pr-4 text-sm bg-white text-[#111111] placeholder:text-[#898989] border border-[#e5e7eb] rounded-[8px] h-[40px] focus:border-[#111111] focus:ring-1 focus:ring-[#111111] outline-none transition-all">
                        <svg class="w-4 h-4 text-[#6b7280] absolute left-3.5 top-1/2 -translate-y-1/2 stroke-current fill-none stroke-2" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </div>
                </form>
            </div>

            <!-- Cal.com Nav Pill Group: Category Filter Tabs -->
            <div class="pt-8 flex items-center">
                <div class="cal-nav-pill-group max-w-full overflow-x-auto no-scrollbar">
                    <a href="{{ route('public.store', ['brand' => $brandFilter, 'q' => $query]) }}" 
                       class="cal-category-tab {{ $categorySlug === 'all' || empty($categorySlug) ? 'active' : '' }}">
                        All Categories
                    </a>

                    @foreach($categories as $cat)
                    <a href="{{ route('public.store', ['category' => $cat->slug, 'brand' => $brandFilter, 'q' => $query]) }}" 
                       class="cal-category-tab {{ $categorySlug === $cat->slug ? 'active' : '' }}">
                        {{ $cat->name }}
                    </a>
                    @endforeach
                </div>
            </div>

            <!-- Brand Filter Ribbon -->
            @if(count($brands) > 0)
            <div class="mt-4 pt-3 border-t border-[#f3f4f6] flex items-center gap-2 text-xs text-[#6b7280] overflow-x-auto no-scrollbar">
                <span class="shrink-0 font-medium uppercase tracking-wider text-[11px] text-[#898989]">Brand:</span>
                <a href="{{ route('public.store', ['category' => $categorySlug, 'brand' => 'all', 'q' => $query]) }}" 
                   class="px-2.5 py-1 rounded-[6px] transition-colors {{ $brandFilter === 'all' ? 'bg-[#111111] text-white font-semibold' : 'text-[#6b7280] hover:text-[#111111]' }}">
                    All Brands
                </a>
                @foreach($brands as $b)
                <a href="{{ route('public.store', ['category' => $categorySlug, 'brand' => $b, 'q' => $query]) }}" 
                   class="px-2.5 py-1 rounded-[6px] transition-colors shrink-0 {{ $brandFilter === $b ? 'bg-[#111111] text-white font-semibold' : 'text-[#6b7280] hover:text-[#111111]' }}">
                    {{ $b }}
                </a>
                @endforeach
            </div>
            @endif

        </div>
    </div>

    <!-- ════ 2. PRODUCTS CATALOG GRID (Cal.com Product Cards) ════ -->
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 py-12 sm:py-16">
        
        <!-- Active Filter Bar -->
        @if(!empty($query) || $categorySlug !== 'all' || $brandFilter !== 'all')
        <div class="mb-8 flex items-center justify-between bg-[#f8f9fa] p-3 rounded-[8px] border border-[#e5e7eb]">
            <div class="text-xs text-[#374151] space-x-2">
                <span class="font-medium text-[#898989] uppercase tracking-wider text-[10px]">Active Filters:</span>
                @if(!empty($query))
                    <span class="font-medium text-[#111111] bg-white px-2 py-0.5 rounded border border-[#e5e7eb]">Search: "{{ $query }}"</span>
                @endif
                @if($categorySlug !== 'all')
                    <span class="font-medium text-[#111111] bg-white px-2 py-0.5 rounded border border-[#e5e7eb]">Category: {{ ucfirst(str_replace('_', ' ', $categorySlug)) }}</span>
                @endif
                @if($brandFilter !== 'all')
                    <span class="font-medium text-[#111111] bg-white px-2 py-0.5 rounded border border-[#e5e7eb]">Brand: {{ $brandFilter }}</span>
                @endif
            </div>
            <a href="{{ route('public.store') }}" class="text-xs text-[#111111] underline hover:text-[#3b82f6] font-semibold">Reset</a>
        </div>
        @endif

        @if($items->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($items as $item)
            <div class="cal-product-card flex flex-col justify-between group">
                <div>
                    <!-- Badge Header with Binary Stock Status (NO numeric quantity) -->
                    <div class="flex items-center justify-between text-xs mb-3">
                        <span class="font-medium text-[11px] text-[#6b7280] bg-[#f5f5f5] px-2 py-0.5 rounded">
                            {{ $item->brand ?? 'OEM Original' }}
                        </span>
                        @if($item->stock_qty > 0)
                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-[#10b981]">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#10b981]"></span>
                                <span>In Stock</span>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-[#ef4444]">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#ef4444]"></span>
                                <span>Out of Stock</span>
                            </span>
                        @endif
                    </div>

                    @if(!empty($item->category_name))
                    <span class="text-[10px] font-medium uppercase tracking-wider text-[#898989] block mb-1">
                        {{ $item->category_name }}
                    </span>
                    @endif

                    <!-- Product Name -->
                    <h3 class="font-semibold text-[#111111] text-base group-hover:underline line-clamp-2">
                        <a href="{{ route('public.product.show', $item->id) }}" class="text-[#111111]">
                            {{ $item->name }}
                        </a>
                    </h3>

                    @if(!empty($item->compatible_model))
                    <div class="text-xs text-[#6b7280] mt-2 flex items-center gap-1.5 line-clamp-1">
                        <svg class="w-3.5 h-3.5 shrink-0 stroke-current fill-none stroke-2 text-[#898989]" viewBox="0 0 24 24"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect></svg>
                        <span>Compatible: {{ $item->compatible_model }}</span>
                    </div>
                    @endif

                    @if(!empty($item->display_type))
                    <div class="mt-2">
                        <span class="text-[11px] font-medium text-[#111111] bg-[#f5f5f5] px-2 py-0.5 rounded inline-block">
                            {{ $item->display_type }}
                        </span>
                    </div>
                    @endif

                    @if(!empty($item->description))
                    <p class="text-xs text-[#6b7280] mt-2 line-clamp-2 leading-relaxed">
                        {{ $item->description }}
                    </p>
                    @endif
                </div>

                <!-- Price & Action CTA -->
                <div class="mt-6 pt-4 border-t border-[#f3f4f6] flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-semibold text-[#898989] block uppercase">Price</span>
                        <span class="font-bold text-[#111111] text-xl">₹{{ number_format($item->selling_price, 0) }}</span>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <a href="{{ route('public.product.show', $item->id) }}" class="cal-btn-secondary text-xs h-[36px] px-3.5" title="View Specs">
                            Specs
                        </a>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', store_whatsapp()) }}?text={{ urlencode('Hi ' . store_name() . ', I want to order/inquire about ' . $item->name . ' (₹' . $item->selling_price . ')') }}" 
                           target="_blank" 
                           class="cal-btn-primary text-xs h-[36px] px-3.5" 
                           title="Order on WhatsApp">
                            Buy
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-12">
            {{ $items->links() }}
        </div>

        @else
        <!-- Empty State -->
        <div class="text-center py-20 bg-[#f5f5f5] rounded-[12px] border border-[#e5e7eb] p-8">
            <div class="w-12 h-12 rounded-full bg-white text-[#6b7280] border border-[#e5e7eb] flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 stroke-current fill-none stroke-2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            </div>
            <h3 class="cal-display-sm text-[#111111]">No items found</h3>
            <p class="text-[#374151] text-sm mt-1 max-w-sm mx-auto">
                We regularly restock components. Please check back or WhatsApp our parts desk to special-order any accessory or display.
            </p>
            <div class="mt-6 flex items-center justify-center gap-3">
                <a href="{{ route('public.store') }}" class="cal-btn-primary text-xs">
                    View All Accessories
                </a>
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', store_whatsapp()) }}?text={{ urlencode('Hi ' . store_name() . ', I am looking for a spare part/accessory that is not listed on your website') }}" target="_blank" class="cal-btn-secondary text-xs">
                    Special Order via WhatsApp
                </a>
            </div>
        </div>
        @endif

    </div>

    <!-- ════ 3. PRE-FOOTER CTA CARD (cta-band-light) ════ -->
    <section class="py-12 bg-white border-t border-[#e5e7eb]">
        <div class="max-w-[1200px] mx-auto px-4 sm:px-6">
            <div class="bg-[#f5f5f5] rounded-[12px] p-8 sm:p-10 flex flex-col sm:flex-row items-center justify-between gap-6 text-left">
                <div>
                    <h3 class="cal-display-sm text-[#111111]">Looking for express screen or battery replacement?</h3>
                    <p class="text-[#374151] text-sm mt-1">We install all spare parts on our certified cleanroom bench while you wait in {{ store_city() }}.</p>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    <a href="{{ route('public.landing') }}#repairs" class="cal-btn-primary text-xs">
                        View Repair Services
                    </a>
                    <a href="{{ route('public.track_repair') }}" class="cal-btn-secondary text-xs">
                        Track Ongoing Job
                    </a>
                </div>
            </div>
        </div>
    </section>

@endsection
