@extends('mobileshop.public.layout')

@section('title', $device->brand . ' ' . $device->model . ' — In Stock at Maurya Mobile Mumbai')
@section('meta_description', 'Buy authentic ' . $device->brand . ' ' . $device->model . ' (' . ($device->storage ?? '128GB') . ', ' . ($device->color ?? 'Original') . ') with GST invoice and showroom warranty in Mumbai.')

@section('content')
@php
    $gradeLabels = [
        'like_new_A_plus' => 'A+ (Like New)',
        'good_A'          => 'A (Good)',
        'fair_B'          => 'B (Fair)',
        'brand_new'       => 'Brand New'
    ];
@endphp

    <!-- ════ BREADCRUMBS ════ -->
    <div class="bg-surface-soft border-b border-hairline-soft py-3 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto flex items-center gap-2 text-[13px] text-muted overflow-x-auto no-scrollbar">
            <a href="{{ route('public.landing') }}" class="hover:text-ink transition-airbnb shrink-0">Home</a>
            <span>/</span>
            <a href="{{ route('public.store') }}" class="hover:text-ink transition-airbnb shrink-0">Shop Catalog</a>
            <span>/</span>
            <a href="{{ route('public.store', ['q' => $device->brand]) }}" class="hover:text-ink transition-airbnb shrink-0">{{ $device->brand }}</a>
            <span>/</span>
            <span class="text-ink font-medium truncate">{{ $device->model }}</span>
        </div>
    </div>

    <!-- ════ MAIN PRODUCT CONTAINER ════ -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">

            <!-- ─── LEFT: MEDIA & SHOWCASE PLATE (5 cols) ─── -->
            <div class="lg:col-span-5 space-y-4">
                <div class="relative aspect-square w-full rounded-[20px] overflow-hidden bg-gradient-to-b from-surface-soft via-white to-surface-strong border border-hairline p-8 flex flex-col items-center justify-center shadow-airbnb-tier">
                    
                    <!-- Floating Condition Pill -->
                    <div class="absolute top-4 left-4 z-10 flex items-center gap-1.5 bg-canvas/95 backdrop-blur-md text-ink text-[12px] font-semibold px-3 py-1.5 rounded-full border border-hairline-soft shadow-sm">
                        @if($device->type === 'new')
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>100% Brand New Sealed</span>
                        @else
                            @php
                                $gradeLabels = [
                                    'like_new_A_plus' => 'A+ (Like New)',
                                    'good_A' => 'A (Good)',
                                    'fair_B' => 'B (Fair)',
                                    'brand_new' => 'Brand New'
                                ];
                                $gradeFormatted = $gradeLabels[$device->condition_grade] ?? strtoupper(str_replace('_', ' ', $device->condition_grade ?? 'A+'));
                            @endphp
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            <span>Certified Pre-Owned · Grade {{ $gradeFormatted }}</span>
                        @endif
                    </div>

                    <!-- Wishlist Button -->
                    <button type="button" class="absolute top-4 right-4 z-10 w-9 h-9 rounded-full bg-white text-ink flex items-center justify-center hover:scale-105 transition-airbnb shadow-airbnb-tier" title="Save item">
                        <svg class="w-4 h-4 stroke-current fill-none hover:fill-rausch hover:text-rausch stroke-[2]" viewBox="0 0 24 24">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                    </button>

                    <!-- Device Visual Showcase Graphic -->
                    <div class="w-full h-full flex flex-col items-center justify-center text-center">
                        <div class="w-40 h-40 sm:w-48 sm:h-48 rounded-3xl bg-white shadow-airbnb border border-hairline-soft flex items-center justify-center text-ink mb-4 transition-transform hover:scale-105 duration-300">
                            @if(stripos($device->brand, 'apple') !== false)
                                <svg class="w-20 h-20 stroke-current fill-none stroke-[1.4]" viewBox="0 0 24 24">
                                    <rect x="5" y="2" width="14" height="20" rx="3"></rect>
                                    <line x1="12" y1="18" x2="12.01" y2="18"></line>
                                </svg>
                            @else
                                <svg class="w-20 h-20 stroke-current fill-none stroke-[1.4]" viewBox="0 0 24 24">
                                    <rect x="4" y="2" width="16" height="20" rx="2"></rect>
                                    <circle cx="12" cy="18" r="1"></circle>
                                </svg>
                            @endif
                        </div>

                        <span class="text-[12px] uppercase tracking-widest text-muted font-bold">{{ $device->brand }}</span>
                        <h2 class="text-[18px] font-bold text-ink mt-0.5">{{ $device->model }}</h2>
                        <span class="text-[13px] text-muted">{{ $device->color ?? 'Standard Edition' }}</span>
                    </div>

                    <!-- Battery Health Tag if Second Hand -->
                    @if($device->battery_health)
                        <div class="absolute bottom-4 left-4 bg-emerald-50 text-emerald-800 border border-emerald-200 text-[11px] font-bold px-2.5 py-1 rounded-lg">
                            🔋 {{ $device->battery_health }}% Battery Health
                        </div>
                    @endif

                    <div class="absolute bottom-4 right-4 bg-canvas/90 text-muted text-[11px] font-medium px-2 py-0.5 rounded border border-hairline-soft">
                        IMEI Verified
                    </div>
                </div>

                <!-- 4 Trust Assurance Badges -->
                <div class="grid grid-cols-2 gap-3 pt-2">
                    <div class="flex items-start gap-2.5 p-3 rounded-[12px] bg-surface-soft border border-hairline-soft">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                        <div>
                            <div class="text-[12px] font-bold text-ink">Genuine IMEI & Box</div>
                            <div class="text-[11px] text-muted">100% authentic serials</div>
                        </div>
                    </div>

                    <div class="flex items-start gap-2.5 p-3 rounded-[12px] bg-surface-soft border border-hairline-soft">
                        <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="m9 12 2 2 4-4"/>
                        </svg>
                        <div>
                            <div class="text-[12px] font-bold text-ink">{{ $device->type === 'new' ? '1-Yr Brand Warranty' : '30-Day Replacement' }}</div>
                            <div class="text-[11px] text-muted">Full store backing</div>
                        </div>
                    </div>

                    <div class="flex items-start gap-2.5 p-3 rounded-[12px] bg-surface-soft border border-hairline-soft">
                        <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect width="20" height="14" x="2" y="5" rx="2"/>
                            <line x1="2" x2="22" y1="10" y2="10"/>
                        </svg>
                        <div>
                            <div class="text-[12px] font-bold text-ink">GST Invoice Included</div>
                            <div class="text-[11px] text-muted">Claim 18% input tax</div>
                        </div>
                    </div>

                    <div class="flex items-start gap-2.5 p-3 rounded-[12px] bg-surface-soft border border-hairline-soft">
                        <svg class="w-5 h-5 text-purple-600 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="1" y="3" width="15" height="13"/>
                            <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                            <circle cx="5.5" cy="18.5" r="2.5"/>
                            <circle cx="18.5" cy="18.5" r="2.5"/>
                        </svg>
                        <div>
                            <div class="text-[12px] font-bold text-ink">Same-Day Mumbai Express</div>
                            <div class="text-[11px] text-muted">Hand-delivered in 3 hrs</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ─── RIGHT: SPECS, PRICING & ACTION CENTER (7 cols) ─── -->
            <div class="lg:col-span-7 space-y-6">

                <!-- Title & Header Meta -->
                <div class="space-y-2 border-b border-hairline-soft pb-5">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 bg-ink text-white text-[11px] font-semibold rounded-full uppercase">
                            {{ $device->brand }}
                        </span>
                        <div class="flex items-center gap-1 text-[13px] text-ink font-semibold">
                            <svg class="w-4 h-4 fill-amber-400 text-amber-400" viewBox="0 0 24 24">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                            </svg>
                            <span>4.95</span>
                            <span class="text-muted font-normal">· (142 verified Mumbai buyers)</span>
                        </div>
                    </div>

                    <h1 class="text-[28px] sm:text-[34px] font-bold text-ink tracking-tight leading-tight">
                        {{ $device->brand }} {{ $device->model }}
                    </h1>

                    <div class="flex items-center gap-3 text-[14px] text-muted flex-wrap">
                        <span class="flex items-center gap-1.5 text-emerald-700 font-medium">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            In Stock · Ready for Immediate Pickup / Delivery
                        </span>
                        <span>·</span>
                        <span>Showroom Unit #{{ $device->id }}</span>
                    </div>
                </div>

                <!-- Price Block -->
                <div class="p-5 rounded-[16px] bg-surface-soft border border-hairline flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <div class="text-[13px] text-muted uppercase tracking-wider font-semibold">Final Showroom Price</div>
                        <div class="flex items-baseline gap-2 mt-0.5">
                            <span class="text-[32px] font-bold text-ink tracking-tight">₹{{ number_format($device->selling_price, 2) }}</span>
                            <span class="text-[13px] font-normal text-muted">incl. 18% GST</span>
                        </div>
                        <p class="text-[12px] text-emerald-700 font-medium mt-1">
                            ✓ Best Mumbai offline counter price guarantee
                        </p>
                    </div>

                    <div class="text-left sm:text-right border-t sm:border-t-0 sm:border-l border-hairline pt-3 sm:pt-0 sm:pl-5">
                        <div class="text-[11px] text-muted uppercase font-semibold">Easy EMI Option</div>
                        <div class="text-[18px] font-bold text-ink">From ₹{{ number_format($emiPlans[0]['monthly'], 0) }}<span class="text-[13px] font-normal text-muted">/mo</span></div>
                        <span class="inline-block text-[11px] font-semibold text-rausch">0% Down Payment Available</span>
                    </div>
                </div>

                <!-- Key Hardware Specs Chips -->
                <div class="space-y-2">
                    <h3 class="text-[13px] font-bold text-muted uppercase tracking-wider">Device Configuration</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                        <div class="p-3 rounded-[12px] border border-hairline bg-canvas text-center">
                            <div class="text-[11px] text-muted">Storage</div>
                            <div class="text-[14px] font-bold text-ink">{{ $device->storage ?? '128 GB' }}</div>
                        </div>
                        <div class="p-3 rounded-[12px] border border-hairline bg-canvas text-center">
                            <div class="text-[11px] text-muted">RAM</div>
                            <div class="text-[14px] font-bold text-ink">{{ $device->ram ?: '8 GB' }}</div>
                        </div>
                        <div class="p-3 rounded-[12px] border border-hairline bg-canvas text-center">
                            <div class="text-[11px] text-muted">Color</div>
                            <div class="text-[14px] font-bold text-ink truncate">{{ $device->color ?? 'Titanium' }}</div>
                        </div>
                        <div class="p-3 rounded-[12px] border border-hairline bg-canvas text-center">
                            <div class="text-[11px] text-muted">Condition</div>
                            <div class="text-[14px] font-bold text-ink">{{ $device->type === 'new' ? 'Sealed' : 'Pre-Owned' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Zero-Cost EMI Calculator & Schedule Card -->
                <div class="border border-hairline rounded-[16px] overflow-hidden bg-canvas">
                    <div class="p-4 bg-surface-soft border-b border-hairline flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-ink" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect width="20" height="14" x="2" y="5" rx="2"/>
                                <line x1="2" x2="22" y1="10" y2="10"/>
                            </svg>
                            <span class="text-[13px] font-bold text-ink">Zero-Cost & Low-Interest EMI Plans</span>
                        </div>
                        <span class="text-[11px] font-medium text-muted">Bajaj · HDFC · ICICI · IDFC</span>
                    </div>
                    <div class="p-4 divide-y divide-hairline-soft text-[13px]">
                        @foreach($emiPlans as $plan)
                            <div class="py-2.5 first:pt-0 last:pb-0 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-ink">{{ $plan['months'] }} Months</span>
                                    <span class="text-[11px] text-muted bg-surface-strong px-2 py-0.5 rounded">{{ $plan['bank'] }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="font-bold text-ink">₹{{ number_format($plan['monthly'], 0) }}</span>
                                    <span class="text-muted text-[11px]"> / month</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- ACTION BUTTONS: WhatsApp & Call & Reserve -->
                <div class="space-y-3 pt-2">
                    <a href="https://wa.me/919876543210?text={{ rawurlencode("Hello Maurya Mobile Team,\n\nI would like to purchase the *" . $device->brand . ' ' . $device->model . "* (" . ($device->storage ?? '128GB') . ", " . ($device->color ?? 'Standard') . ") listed for ₹" . number_format($device->selling_price, 2) . ".\n\nPlease share payment modes and confirmed showroom availability. Thank you!") }}" 
                       target="_blank"
                       class="w-full py-4 px-6 rounded-full bg-[#25D366] hover:bg-[#20bd5a] text-white font-bold text-[15px] flex items-center justify-center gap-2.5 shadow-airbnb-tier hover:shadow-airbnb-hover transition-all">
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                        </svg>
                        <span>Buy on WhatsApp · Instant Showroom Reply</span>
                    </a>

                    <div class="grid grid-cols-2 gap-3">
                        <a href="tel:+919876543210" 
                           class="py-3 px-4 rounded-full border border-hairline hover:border-ink bg-canvas text-ink font-semibold text-[13px] flex items-center justify-center gap-2 transition-airbnb text-center">
                            <svg class="w-4 h-4 text-ink" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                            <span>Call Store</span>
                        </a>

                        <button type="button" 
                                onclick="document.getElementById('reserve-modal').classList.remove('hidden');"
                                class="py-3 px-4 rounded-full border border-hairline hover:border-ink bg-canvas text-ink font-semibold text-[13px] flex items-center justify-center gap-2 transition-airbnb text-center">
                            <svg class="w-4 h-4 text-ink" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                            <span>Hold for 2 Hrs</span>
                        </button>
                    </div>
                </div>

                <!-- Showroom Visit Card -->
                <div class="p-4 rounded-[14px] bg-surface-soft border border-hairline-soft flex items-start gap-3">
                    <svg class="w-5 h-5 text-ink shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                    <div class="text-[13px]">
                        <div class="font-bold text-ink">Physical Showroom Demonstration Available</div>
                        <p class="text-muted text-[12px] mt-0.5">
                            Linking Road, Bandra West, Mumbai. Open today until 9:30 PM. Feel free to inspect before paying.
                        </p>
                    </div>
                </div>

            </div>
        </div>

        <!-- ════ DETAILED SPECIFICATIONS SECTION ════ -->
        <div class="mt-16 border-t border-hairline-soft pt-12 space-y-6">
            <h2 class="text-[22px] font-bold text-ink">Full Technical Specifications</h2>
            <div class="border border-hairline rounded-[16px] overflow-hidden">
                <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-hairline">
                    <div class="divide-y divide-hairline-soft text-[13px]">
                        <div class="p-4 flex justify-between bg-surface-soft/50 font-medium">
                            <span class="text-muted">Brand & Model</span>
                            <span class="text-ink font-semibold">{{ $device->brand }} {{ $device->model }}</span>
                        </div>
                        <div class="p-4 flex justify-between font-medium">
                            <span class="text-muted">Internal Storage</span>
                            <span class="text-ink font-semibold">{{ $device->storage ?? '128 GB' }}</span>
                        </div>
                        <div class="p-4 flex justify-between bg-surface-soft/50 font-medium">
                            <span class="text-muted">RAM Memory</span>
                            <span class="text-ink font-semibold">{{ $device->ram ?: '8 GB' }}</span>
                        </div>
                        <div class="p-4 flex justify-between font-medium">
                            <span class="text-muted">Color Variant</span>
                            <span class="text-ink font-semibold">{{ $device->color ?? 'Official Finish' }}</span>
                        </div>
                    </div>

                    <div class="divide-y divide-hairline-soft text-[13px]">
                        <div class="p-4 flex justify-between bg-surface-soft/50 font-medium">
                            <span class="text-muted">Inventory Unit Type</span>
                            <span class="text-ink font-semibold">{{ $device->type === 'new' ? 'Brand New Sealed' : 'Certified Pre-Owned' }}</span>
                        </div>
                        <div class="p-4 flex justify-between font-medium">
                            <span class="text-muted">Condition Quality</span>
                            <span class="text-ink font-semibold">{{ $device->type === 'new' ? 'Factory Sealed' : 'Grade ' . ($gradeLabels[$device->condition_grade] ?? strtoupper(str_replace('_', ' ', $device->condition_grade ?? 'A+'))) }}</span>
                        </div>
                        <div class="p-4 flex justify-between bg-surface-soft/50 font-medium">
                            <span class="text-muted">Battery Health</span>
                            <span class="text-ink font-semibold">{{ $device->battery_health ? $device->battery_health . '%' : '100% Factory' }}</span>
                        </div>
                        <div class="p-4 flex justify-between font-medium">
                            <span class="text-muted">Warranty Coverage</span>
                            <span class="text-ink font-semibold">{{ $device->type === 'new' ? '1-Year Official Brand' : '30-Day Testing Replacement' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ════ SIMILAR IN-STOCK DEVICES (2 PER ROW) ════ -->
        @if($relatedDevices->isNotEmpty())
        <div class="mt-16 border-t border-hairline-soft pt-12 space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-[22px] font-bold text-ink">Similar In-Stock Devices</h2>
                    <p class="text-[13px] text-muted">Other smartphones available at our Bandra showroom right now.</p>
                </div>
                <a href="{{ route('public.store') }}" class="text-[13px] font-semibold text-ink hover:underline">
                    View Entire Catalog →
                </a>
            </div>

            <!-- Responsive Grid: 2 on mobile, 4 on desktop -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                @foreach($relatedDevices as $rd)
                    <a href="{{ route('public.product.show', $rd->id) }}" class="group flex flex-col cursor-pointer">
                        <div class="relative aspect-square w-full rounded-[12px] overflow-hidden bg-surface-soft border border-hairline-soft mb-3 flex items-center justify-center p-6 text-center group-hover:scale-[1.02] transition-transform">
                            <div class="w-16 h-16 rounded-2xl bg-white shadow-airbnb-tier flex items-center justify-center text-ink">
                                <svg class="w-8 h-8 stroke-current fill-none stroke-[1.5]" viewBox="0 0 24 24">
                                    <rect x="5" y="2" width="14" height="20" rx="3"></rect>
                                    <line x1="12" y1="18" x2="12.01" y2="18"></line>
                                </svg>
                            </div>

                            <div class="absolute top-2.5 left-2.5 bg-canvas text-ink text-[10px] font-semibold px-2 py-0.5 rounded-full shadow-sm">
                                {{ $rd->type === 'new' ? 'Sealed' : 'Pre-Owned' }}
                            </div>
                        </div>

                        <div class="space-y-1">
                            <div class="text-[11px] uppercase tracking-wider text-muted font-bold">{{ $rd->brand }}</div>
                            <h3 class="text-[14px] sm:text-[15px] font-bold text-ink truncate group-hover:text-rausch transition-colors">
                                {{ $rd->model }}
                            </h3>
                            <p class="text-[12px] text-muted truncate">
                                {{ $rd->storage ?? '128GB' }} · {{ $rd->color ?? 'Clean' }}
                            </p>
                            <div class="pt-2 flex items-baseline justify-between">
                                <div class="text-[14px] sm:text-[16px] font-bold text-ink">
                                    ₹{{ number_format($rd->selling_price, 2) }}
                                </div>
                                <span class="text-[12px] font-semibold text-rausch group-hover:underline">
                                    View Details →
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
        @endif

    </div>

    <!-- ════ 2-HOUR HOLD RESERVATION MODAL ════ -->
    <div id="reserve-modal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-canvas w-full max-w-md rounded-[20px] shadow-airbnb p-6 border border-hairline space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-[18px] font-bold text-ink">Hold for 2 Hours</h3>
                <button type="button" onclick="document.getElementById('reserve-modal').classList.add('hidden');" class="text-muted hover:text-ink text-[20px] font-bold">&times;</button>
            </div>
            <p class="text-[13px] text-muted">
                We will reserve <strong>{{ $device->brand }} {{ $device->model }}</strong> (₹{{ number_format($device->selling_price, 2) }}) under your name at our Bandra store counter for up to 2 hours.
            </p>
            <div class="space-y-3 pt-2">
                <div>
                    <label class="block text-[12px] font-semibold text-ink mb-1">Your Full Name</label>
                    <input type="text" id="reserve-name" placeholder="e.g. Rahul Sharma" class="w-full px-3 py-2 border border-hairline rounded-lg text-[13px] focus:outline-none focus:border-ink">
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-ink mb-1">Your Mobile Number</label>
                    <input type="tel" id="reserve-phone" placeholder="e.g. 9876543210" class="w-full px-3 py-2 border border-hairline rounded-lg text-[13px] focus:outline-none focus:border-ink">
                </div>
            </div>
            <div class="pt-2 flex gap-3">
                <button type="button" 
                        onclick="document.getElementById('reserve-modal').classList.add('hidden');" 
                        class="flex-1 py-2.5 rounded-full border border-hairline text-[13px] font-semibold text-muted hover:text-ink">
                    Cancel
                </button>
                <button type="button" 
                        onclick="submitReservation()"
                        class="flex-1 py-2.5 rounded-full bg-ink text-white text-[13px] font-semibold hover:bg-black transition-all">
                    Confirm Hold
                </button>
            </div>
        </div>
    </div>

    <script>
        function submitReservation() {
            const name = document.getElementById('reserve-name').value.trim();
            const phone = document.getElementById('reserve-phone').value.trim();
            if (!name || !phone) {
                alert('Please enter your name and phone number to hold this device.');
                return;
            }
            const text = encodeURIComponent(`Hello Maurya Mobile Team,\n\nI would like to place a 2-hour showroom hold on the *${'{{ $device->brand }} {{ $device->model }}'}* (₹${'{{ number_format($device->selling_price, 2) }}'}) while I visit the store.\n\n👤 Name: ${name}\n📱 Phone: ${phone}\n\nPlease confirm availability and hold status. Thank you!`);
            window.open(`https://wa.me/919876543210?text=${text}`, '_blank');
            document.getElementById('reserve-modal').classList.add('hidden');
        }
    </script>

@endsection
