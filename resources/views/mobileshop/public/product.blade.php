@extends('mobileshop.public.layout')

@section('title', $product->name . ' — Buy at ' . store_name())
@section('meta_description', 'Buy authentic ' . $product->name . ' (' . ($product->brand ?? 'OEM') . ') with GST invoice and instant store pickup in ' . store_city() . '.')

@section('content')

    <!-- ════ 1. BREADCRUMBS BAR (White Canvas with Hairline) ════ -->
    <div class="bg-white border-b border-[#e5e7eb] py-3 px-4">
        <div class="max-w-[1200px] mx-auto flex items-center gap-2 text-xs text-[#6b7280]">
            <a href="{{ route('public.landing') }}" class="hover:text-[#111111]">Home</a>
            <span>/</span>
            <a href="{{ route('public.store') }}" class="hover:text-[#111111]">Accessories Catalog</a>
            @if(!empty($product->category_slug))
            <span>/</span>
            <a href="{{ route('public.store', ['category' => $product->category_slug]) }}" class="hover:text-[#111111]">{{ $product->category_name }}</a>
            @endif
            <span>/</span>
            <span class="text-[#111111] font-semibold truncate">{{ $product->name }}</span>
        </div>
    </div>

    <!-- ════ 2. PRODUCT DETAIL VIEW (Cal.com 2-Col Layout) ════ -->
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 py-12 md:py-16">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-start">
            
            <!-- LEFT COLUMN: Product Visual Card & Trust Tiles -->
            <div class="lg:col-span-5 space-y-6 text-center">
                <div class="cal-product-card p-8 sm:p-12 relative flex flex-col items-center justify-center min-h-[340px]">
                    
                    <!-- Binary Stock Pill (Strictly NO Quantity Number) -->
                    <div class="absolute top-4 left-4">
                        @if($product->stock_qty > 0)
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#10b981] bg-[#f5f5f5] px-2.5 py-1 rounded-full border border-[#e5e7eb]">
                                <span class="w-2 h-2 rounded-full bg-[#10b981] animate-pulse"></span>
                                <span>In Stock</span>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#ef4444] bg-[#f5f5f5] px-2.5 py-1 rounded-full border border-[#e5e7eb]">
                                <span class="w-2 h-2 rounded-full bg-[#ef4444]"></span>
                                <span>Out of Stock</span>
                            </span>
                        @endif
                    </div>

                    <!-- Category Icon Glyph -->
                    <div class="w-24 h-24 rounded-[16px] bg-[#f8f9fa] border border-[#e5e7eb] flex items-center justify-center text-[#111111] mb-4">
                        @if(in_array($product->category, ['charger', 'cable', 'power_bank']))
                            <svg class="w-12 h-12 stroke-current fill-none stroke-2" viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                        @elseif(in_array($product->category, ['back_cover', 'tempered_glass']))
                            <svg class="w-12 h-12 stroke-current fill-none stroke-2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        @elseif(in_array($product->category, ['audio']))
                            <svg class="w-12 h-12 stroke-current fill-none stroke-2" viewBox="0 0 24 24"><path d="M3 18v-6a9 9 0 0 1 18 0v6"></path><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path></svg>
                        @elseif(in_array($product->category, ['battery']))
                            <svg class="w-12 h-12 stroke-current fill-none stroke-2" viewBox="0 0 24 24"><rect x="2" y="7" width="16" height="10" rx="2" ry="2"></rect><line x1="22" y1="11" x2="22" y2="13"></line></svg>
                        @elseif(in_array($product->category, ['folder_display']))
                            <svg class="w-12 h-12 stroke-current fill-none stroke-2" viewBox="0 0 24 24"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
                        @else
                            <svg class="w-12 h-12 stroke-current fill-none stroke-2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                        @endif
                    </div>

                    <span class="text-xs uppercase font-semibold tracking-wider text-[#6b7280]">
                        {{ $product->category_name ?? ucfirst($product->category) }}
                    </span>
                    <h3 class="font-semibold text-[#111111] text-base mt-1 max-w-sm">
                        {{ $product->name }}
                    </h3>
                </div>

                <!-- 3 Feature Assurance Badges (Cal.com light cards) -->
                <div class="grid grid-cols-3 gap-3 text-left">
                    <div class="p-3 bg-[#f5f5f5] rounded-[8px] border border-[#e5e7eb]">
                        <div class="w-7 h-7 rounded bg-white text-[#111111] border border-[#e5e7eb] flex items-center justify-center mb-1.5">
                            <svg class="w-4 h-4 stroke-current fill-none stroke-2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        </div>
                        <h4 class="text-xs font-semibold text-[#111111]">100% Genuine</h4>
                        <p class="text-[11px] text-[#6b7280]">Verified OEM parts</p>
                    </div>

                    <div class="p-3 bg-[#f5f5f5] rounded-[8px] border border-[#e5e7eb]">
                        <div class="w-7 h-7 rounded bg-white text-[#10b981] border border-[#e5e7eb] flex items-center justify-center mb-1.5">
                            <svg class="w-4 h-4 stroke-current fill-none stroke-2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                        <h4 class="text-xs font-semibold text-[#111111]">90-Day Warranty</h4>
                        <p class="text-[11px] text-[#6b7280]">Counter replacement</p>
                    </div>

                    <div class="p-3 bg-[#f5f5f5] rounded-[8px] border border-[#e5e7eb]">
                        <div class="w-7 h-7 rounded bg-white text-[#111111] border border-[#e5e7eb] flex items-center justify-center mb-1.5">
                            <svg class="w-4 h-4 stroke-current fill-none stroke-2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        </div>
                        <h4 class="text-xs font-semibold text-[#111111]">Store Pickup</h4>
                        <p class="text-[11px] text-[#6b7280]">Instant in {{ store_city() }}</p>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: Price, Specs Table & Actions -->
            <div class="lg:col-span-7 space-y-6 text-left">
                
                <!-- Product Headline & Pricing -->
                <div class="border-b border-[#e5e7eb] pb-6 space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold uppercase tracking-wider text-[#111111] bg-[#f5f5f5] px-2.5 py-0.5 rounded border border-[#e5e7eb]">
                            {{ $product->brand ?? 'OEM Original' }}
                        </span>
                        @if(!empty($product->category_name))
                        <span class="text-xs text-[#6b7280] font-medium">
                            {{ $product->category_name }}
                        </span>
                        @endif
                    </div>

                    <h1 class="cal-display-lg text-[#111111]">
                        {{ $product->name }}
                    </h1>

                    <div class="pt-2 flex items-baseline gap-3">
                        <span class="cal-display-md text-[#111111]">
                            ₹{{ number_format($product->selling_price, 0) }}
                        </span>
                        <span class="text-xs text-[#6b7280]">
                            Inclusive of all taxes & GST invoice
                        </span>
                    </div>

                    @if(!empty($product->description))
                    <p class="text-sm text-[#374151] leading-relaxed pt-2">
                        {{ $product->description }}
                    </p>
                    @endif
                </div>

                <!-- Primary Action Buttons (Cal.com Pitch Black Primary) -->
                <div class="space-y-3">
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', store_whatsapp()) }}?text={{ urlencode('Hi ' . store_name() . ', I would like to buy/reserve ' . $product->name . ' (₹' . $product->selling_price . '). Is it ready for pickup?') }}" 
                       target="_blank" 
                       class="cal-btn-primary w-full justify-center text-sm py-3 h-[44px]">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                        <span>Reserve & Buy via WhatsApp</span>
                    </a>

                    <div class="grid grid-cols-2 gap-3">
                        <a href="tel:{{ preg_replace('/[^0-9]/', '', store_phone()) }}" class="cal-btn-secondary text-xs justify-center">
                            Call Desk: {{ store_phone() }}
                        </a>
                        <a href="{{ route('public.contact') }}" class="cal-btn-secondary text-xs justify-center">
                            Store Location & Map
                        </a>
                    </div>
                </div>

                <!-- Technical Specifications Table (Cal.com Hairline Style) -->
                <div class="pt-4 border-t border-[#e5e7eb]">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-[#111111] mb-3">Product Specifications</h3>
                    
                    <div class="bg-white rounded-[8px] border border-[#e5e7eb] overflow-hidden divide-y divide-[#f3f4f6] text-xs">
                        <div class="grid grid-cols-3 p-3">
                            <span class="text-[#6b7280] font-medium">Brand</span>
                            <span class="col-span-2 text-[#111111] font-semibold">{{ $product->brand ?? 'OEM Original' }}</span>
                        </div>
                        <div class="grid grid-cols-3 p-3">
                            <span class="text-[#6b7280] font-medium">Category</span>
                            <span class="col-span-2 text-[#111111]">{{ $product->category_name ?? ucfirst($product->category) }}</span>
                        </div>
                        @if(!empty($product->compatible_model))
                        <div class="grid grid-cols-3 p-3">
                            <span class="text-[#6b7280] font-medium">Device Compatibility</span>
                            <span class="col-span-2 text-[#111111] font-semibold">{{ $product->compatible_model }}</span>
                        </div>
                        @endif
                        @if(!empty($product->display_type))
                        <div class="grid grid-cols-3 p-3">
                            <span class="text-[#6b7280] font-medium">Display Tier</span>
                            <span class="col-span-2 text-[#111111] font-semibold">{{ $product->display_type }}</span>
                        </div>
                        @endif
                        @if(!empty($product->hsn_code))
                        <div class="grid grid-cols-3 p-3">
                            <span class="text-[#6b7280] font-medium">HSN Code</span>
                            <span class="col-span-2 text-[#111111] font-mono">{{ $product->hsn_code }}</span>
                        </div>
                        @endif
                        <div class="grid grid-cols-3 p-3">
                            <span class="text-[#6b7280] font-medium">Warranty</span>
                            <span class="col-span-2 text-[#10b981] font-semibold">90-Day Hardware Replacement</span>
                        </div>
                        <div class="grid grid-cols-3 p-3">
                            <span class="text-[#6b7280] font-medium">Installation</span>
                            <span class="col-span-2 text-[#374151]">Free counter fitting for glass & covers. Certified bench install for displays & batteries.</span>
                        </div>
                    </div>
                </div>

                <!-- Installation Note if spare part -->
                @if(in_array($product->category, ['battery', 'folder_display', 'charging_port']))
                <div class="p-4 rounded-[8px] bg-[#f8f9fa] border border-[#e5e7eb] text-xs text-[#111111] space-y-1">
                    <span class="font-semibold flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-[#111111] stroke-current fill-none stroke-2" viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                        <span>Professional Bench Installation Available</span>
                    </span>
                    <p class="text-[#6b7280]">
                        Our technicians can install this {{ strtolower($product->category_name ?? $product->category) }} on your phone with express turnaround and zero data loss.
                    </p>
                </div>
                @endif

            </div>

        </div>

        <!-- ════ 3. RELATED ACCESSORIES (Cal.com Product Cards) ════ -->
        @if($relatedProducts->count() > 0)
        <div class="mt-20 pt-12 border-t border-[#e5e7eb]">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="cal-display-md text-[#111111]">Related Accessories & Spares</h3>
                    <p class="text-xs text-[#6b7280] mt-1">Matching components for {{ $product->brand }} devices.</p>
                </div>
                <a href="{{ route('public.store') }}" class="text-xs font-semibold text-[#111111] underline hover:text-[#3b82f6] flex items-center gap-1">
                    <span>View Entire Catalog</span>
                    <svg class="w-3.5 h-3.5 stroke-current fill-none stroke-2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($relatedProducts as $rel)
                <div class="cal-product-card flex flex-col justify-between group">
                    <div>
                        <div class="flex items-center justify-between text-[11px] mb-2">
                            <span class="font-medium text-[#6b7280] bg-[#f5f5f5] px-2 py-0.5 rounded uppercase">{{ $rel->brand }}</span>
                            @if($rel->stock_qty > 0)
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
                        <h4 class="font-semibold text-[#111111] text-sm group-hover:underline line-clamp-2">
                            <a href="{{ route('public.product.show', $rel->id) }}">
                                {{ $rel->name }}
                            </a>
                        </h4>
                        @if(!empty($rel->compatible_model))
                        <span class="text-[11px] text-[#6b7280] block mt-1 line-clamp-1">
                            Fits: {{ $rel->compatible_model }}
                        </span>
                        @endif
                    </div>
                    <div class="mt-4 pt-3 border-t border-[#f3f4f6] flex items-center justify-between">
                        <span class="font-bold text-[#111111] text-sm">₹{{ number_format($rel->selling_price, 0) }}</span>
                        <a href="{{ route('public.product.show', $rel->id) }}" class="cal-btn-secondary text-xs h-[30px] px-2.5">
                            Details
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>

@endsection
