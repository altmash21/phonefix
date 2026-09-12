@extends('mobileshop.public.layout')

@section('title', $device->brand . ' ' . $device->model . ' — Buy at Maurya Mobile Mumbai')
@section('meta_description', 'Buy authentic ' . $device->brand . ' ' . $device->model . ' (' . ($device->storage ?? '128GB') . ', ' . ($device->color ?? 'Original') . ') with GST invoice and showroom warranty in Mumbai.')

@section('subnav_title', $device->brand . ' ' . $device->model)
@section('subnav_links')
    <a href="#overview" class="hover:text-apple-ink">Overview</a>
    <a href="#specs" class="hover:text-apple-ink">Specs</a>
    <a href="#emi" class="hover:text-apple-ink">EMI Financing</a>
@endsection
@section('subnav_cta')
    <a href="tel:9876543210" class="apple-btn-primary text-[13px] py-1.5 px-4">
        Buy · ₹{{ number_format($device->selling_price, 0) }}
    </a>
@endsection

@section('content')

    <!-- ════ 1. BREADCRUMBS BAR (Parchment, 14px / 400) ════ -->
    <div class="bg-apple-parchment border-b border-apple-hairline py-3 px-4">
        <div class="max-w-[1024px] mx-auto flex items-center gap-2 apple-caption text-apple-muted-48">
            <a href="{{ route('public.landing') }}" class="hover:text-apple-ink">Home</a>
            <span>/</span>
            <a href="{{ route('public.store') }}" class="hover:text-apple-ink">Store</a>
            <span>/</span>
            <a href="{{ route('public.store', ['q' => $device->brand]) }}" class="hover:text-apple-ink">{{ $device->brand }}</a>
            <span>/</span>
            <span class="text-apple-ink font-semibold truncate">{{ $device->model }}</span>
        </div>
    </div>

    <!-- ════ 2. PRODUCT BUY-FLOW CONFIGURATOR (Apple iPhone Buy Page Grid) ════ -->
    <div id="overview" class="max-w-[1024px] mx-auto px-4 py-12 md:py-20">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-start">
            
            <!-- LEFT COLUMN (Span 6): Product Visual Pedestal & Surface Shadow -->
            <div class="lg:col-span-6 lg:sticky lg:top-28 space-y-6 text-center">
                <div class="bg-apple-parchment rounded-[18px] border border-apple-hairline p-8 sm:p-14 relative flex items-center justify-center">
                    
                    <!-- Condition Badge (Apple pill) -->
                    <div class="absolute top-4 left-4">
                        @if($device->type === 'new')
                            <span class="apple-caption font-semibold text-emerald-800 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
                                ● 100% Factory Sealed
                            </span>
                        @else
                            @php
                                $gradeLabels = [
                                    'like_new_A_plus' => 'Grade A+ Like New',
                                    'good_A'          => 'Grade A Good',
                                    'fair_B'          => 'Grade B Fair',
                                    'brand_new'       => 'Brand New'
                                ];
                                $gradeBadge = $gradeLabels[$device->condition_grade] ?? 'Certified Pre-Owned';
                            @endphp
                            <span class="apple-caption font-semibold text-blue-800 bg-blue-50 px-3 py-1 rounded-full border border-blue-200">
                                {{ $gradeBadge }}
                            </span>
                        @endif
                    </div>

                    @php
                        $devPhoto = $device->photo_path ?? $device->box_photo_path ?? null;
                        $devBoxPhoto = $device->box_photo_path ?? null;
                    @endphp
                    <!-- Single Signature Product Shadow Resting on Surface -->
                    <img id="mainProductPhoto" 
                         src="{{ $devPhoto ? asset($devPhoto) : asset('img/hero-smartphones.jpg') }}" 
                         alt="{{ $device->brand }} {{ $device->model }}" 
                         class="max-h-[380px] w-auto object-contain apple-product-shadow rounded-xl transition-transform duration-500 hover:scale-[1.02]">
                </div>

                @if($devPhoto && $devBoxPhoto && $devPhoto !== $devBoxPhoto)
                <div class="flex items-center justify-center gap-3">
                    <button type="button" onclick="document.getElementById('mainProductPhoto').src='{{ asset($devPhoto) }}'" class="p-1 rounded-lg border-2 border-apple-ink transition-all">
                        <img src="{{ asset($devPhoto) }}" alt="Device" class="w-12 h-12 object-cover rounded-md">
                    </button>
                    <button type="button" onclick="document.getElementById('mainProductPhoto').src='{{ asset($devBoxPhoto) }}'" class="p-1 rounded-lg border border-apple-hairline hover:border-apple-ink transition-all">
                        <img src="{{ asset($devBoxPhoto) }}" alt="Box / Bill" class="w-12 h-12 object-cover rounded-md">
                    </button>
                </div>
                @endif

                <div class="apple-caption text-apple-muted-48">
                    Model: {{ $device->brand }} {{ $device->model }} · Color: {{ $device->color ?? 'Titanium' }} · Storage: {{ $device->storage ?? '128GB' }}
                </div>
            </div>

            <!-- RIGHT COLUMN (Span 6): Specifications, Pricing, Configurator Chips & Actions -->
            <div class="lg:col-span-6 space-y-8 text-left">
                
                <!-- Product Headline & Pricing -->
                <div class="space-y-2 border-b border-apple-hairline pb-6">
                    <span class="apple-caption-strong text-apple-primary uppercase tracking-widest text-[12px]">
                        {{ $device->type === 'new' ? 'Official Flagship' : '50-Point Certified Pre-Owned' }}
                    </span>
                    <h1 class="apple-display-lg text-apple-ink">
                        {{ $device->brand }} {{ $device->model }}
                    </h1>
                    <div class="pt-2">
                        <span class="apple-hero-display text-apple-ink text-[34px] sm:text-[40px]">
                            ₹{{ number_format($device->selling_price, 0) }}
                        </span>
                        <div class="apple-body text-apple-muted-48 mt-1">
                            Inclusive of all taxes · Or ₹{{ number_format($emiPlans[0]['monthly'], 0) }}/mo. for 3 months with 0% EMI.
                        </div>
                    </div>
                </div>

                <!-- Configurator Chip Selection (Pill Grammar) -->
                <div class="space-y-4">
                    <div class="apple-body-strong text-apple-ink">Storage Capacity</div>
                    <div class="grid grid-cols-3 gap-3">
                        <div class="apple-search-input flex flex-col items-center justify-center text-center !h-auto py-3 border-2 !border-apple-primary-focus bg-white cursor-pointer select-none">
                            <span class="apple-body-strong text-apple-ink">{{ $device->storage ?? '128GB' }}</span>
                            <span class="apple-fine-print text-apple-muted-48">Included</span>
                        </div>
                        <div class="apple-search-input flex flex-col items-center justify-center text-center !h-auto py-3 border border-apple-hairline bg-apple-parchment/60 opacity-60 select-none">
                            <span class="apple-body-strong text-apple-ink">256GB</span>
                            <span class="apple-fine-print text-apple-muted-48">+₹10,000</span>
                        </div>
                        <div class="apple-search-input flex flex-col items-center justify-center text-center !h-auto py-3 border border-apple-hairline bg-apple-parchment/60 opacity-60 select-none">
                            <span class="apple-body-strong text-apple-ink">512GB</span>
                            <span class="apple-fine-print text-apple-muted-48">+₹22,000</span>
                        </div>
                    </div>
                </div>

                <!-- Verification & Trust Indicators -->
                <div class="space-y-3 bg-apple-parchment rounded-[18px] border border-apple-hairline p-6 text-[14px]">
                    <div class="apple-caption-strong text-apple-ink mb-2">Showroom Guarantee & Verification</div>
                    <div class="flex items-center gap-3 text-apple-ink">
                        <i data-lucide="check-circle" class="w-4 h-4 text-apple-primary shrink-0"></i>
                        <span><strong>100% Genuine Invoice:</strong> Issued with official store GST bill for full manufacturer claim.</span>
                    </div>
                    @if($device->type !== 'new')
                    <div class="flex items-center gap-3 text-apple-ink">
                        <i data-lucide="battery-charging" class="w-4 h-4 text-apple-primary shrink-0"></i>
                        <span><strong>Battery Health:</strong> Validated at {{ $device->battery_health ?? '90' }}%+ health capacity.</span>
                    </div>
                    <div class="flex items-center gap-3 text-apple-ink">
                        <i data-lucide="shield-check" class="w-4 h-4 text-apple-primary shrink-0"></i>
                        <span><strong>30-Day Warranty:</strong> Comprehensive counter replacement warranty against hardware faults.</span>
                    </div>
                    @endif
                    <div class="flex items-center gap-3 text-apple-ink">
                        <i data-lucide="map-pin" class="w-4 h-4 text-apple-primary shrink-0"></i>
                        <span><strong>Express Store Pickup:</strong> Ready today at Linking Road, Bandra West showroom.</span>
                    </div>
                </div>

                <!-- Primary Purchase Action Buttons -->
                <div class="space-y-3 pt-2">
                    <a href="https://wa.me/919876543210?text=Hi%20Maurya%20Mobile,%20I%20want%20to%20buy/reserve%20{{ urlencode($device->brand . ' ' . $device->model) }}%20listed%20for%20₹{{ $device->selling_price }}" 
                       target="_blank" 
                       class="apple-btn-primary w-full py-3.5 text-[17px] font-medium shadow-sm">
                        <i data-lucide="message-circle" class="w-5 h-5"></i>
                        Reserve on WhatsApp / Store Pickup
                    </a>
                    <a href="tel:9876543210" class="apple-btn-secondary-pill w-full py-3 text-[16px]">
                        <i data-lucide="phone" class="w-4 h-4"></i> Call Showroom Desk (+91 98765 43210)
                    </a>
                </div>

                <!-- EMI Calculation Table -->
                <div id="emi" class="border-t border-apple-hairline pt-8 space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="apple-tagline text-apple-ink">Instant 0% EMI Financing</h2>
                        <span class="apple-caption text-apple-muted-48">No cost options available</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($emiPlans as $plan)
                        <div class="apple-utility-card !p-4 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between">
                                    <span class="apple-caption-strong text-apple-ink">{{ $plan['bank'] }}</span>
                                    <span class="apple-caption text-apple-primary font-semibold">{{ $plan['months'] }} Months</span>
                                </div>
                                <div class="apple-body-strong text-apple-ink text-[18px] mt-2">
                                    ₹{{ number_format($plan['monthly'], 0) }} <span class="apple-caption text-apple-muted-48 font-normal">/ month</span>
                                </div>
                            </div>
                            <div class="apple-fine-print text-apple-muted-48 mt-3 pt-2 border-t border-apple-hairline">
                                Zero Down Payment · Instant counter approval via Aadhaar + PAN
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

            </div>

        </div>
    </div>

    <!-- ════ 3. FLOATING STICKY BAR (iPhone 17 Pro Buy Page Spec: 64px Height, Frosted Parchment Blur) ════ -->
    <div class="fixed bottom-0 inset-x-0 h-[64px] apple-frosted border-t border-black/[0.08] z-50 px-4 flex items-center justify-between shadow-sm">
        <div class="max-w-[1024px] mx-auto w-full flex items-center justify-between">
            <div class="flex items-center gap-3 truncate">
                <span class="apple-body-strong text-apple-ink truncate hidden sm:inline">
                    {{ $device->brand }} {{ $device->model }}
                </span>
                <span class="apple-body-strong text-apple-ink text-[18px]">
                    ₹{{ number_format($device->selling_price, 0) }}
                </span>
            </div>

            <div class="flex items-center gap-3">
                <a href="https://wa.me/919876543210?text=Hi%20Maurya%20Mobile,%20I%20want%20to%20reserve%20{{ urlencode($device->brand . ' ' . $device->model) }}" 
                   target="_blank"
                   class="apple-btn-primary text-[14px] py-2 px-5">
                    Reserve Device
                </a>
            </div>
        </div>
    </div>

    <!-- ════ 4. RELATED IN-STOCK DEVICES (Store Utility Cards) ════ -->
    @if(isset($relatedDevices) && $relatedDevices->count() > 0)
    <div class="bg-apple-parchment border-t border-apple-hairline py-16">
        <div class="max-w-[1024px] mx-auto px-4">
            <div class="mb-8">
                <h2 class="apple-display-md text-apple-ink">You may also like.</h2>
                <p class="apple-body text-apple-muted-48">Other devices in stock today at our Bandra West showroom.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($relatedDevices as $rel)
                <a href="{{ route('public.product.show', $rel->id) }}" class="apple-utility-card block text-decoration-none group">
                    <div class="w-full aspect-square bg-white rounded-[8px] p-4 flex items-center justify-center relative overflow-hidden mb-3">
                        <img src="{{ asset('img/hero-smartphones.jpg') }}" 
                             alt="{{ $rel->brand }} {{ $rel->model }}" 
                             class="max-h-[120px] object-contain apple-product-shadow transition-transform duration-300 group-hover:scale-105">
                    </div>
                    <div class="text-[11px] text-apple-muted-48 uppercase font-semibold">{{ $rel->brand }}</div>
                    <div class="apple-body-strong text-apple-ink truncate mt-0.5 group-hover:text-apple-primary transition-colors">
                        {{ $rel->brand }} {{ $rel->model }}
                    </div>
                    <div class="apple-body-strong text-apple-ink text-[16px] mt-2">
                        ₹{{ number_format($rel->selling_price, 0) }}
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif

@endsection
