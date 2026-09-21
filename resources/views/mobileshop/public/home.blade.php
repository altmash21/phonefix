@extends('mobileshop.public.layout')

@section('title', store_name() . ' — Express Phone Repair & Genuine Accessories')
@section('meta_description', 'Certified express phone repairs, level-4 micro-soldering, fast GaN chargers, 11D tempered glass, and OEM batteries in ' . store_city() . '. 90-day warranty & zero data wipe.')

@section('content')

    <!-- ════ 1. HERO SECTION (Spacious, Uncluttered, Airy) ════ -->
    <section class="relative bg-white pt-16 pb-20 sm:pt-24 sm:pb-28 border-b border-[#e5e7eb]">
        <div class="max-w-[1200px] mx-auto px-6 sm:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-14 lg:gap-12 items-center">
                
                <!-- LEFT (7 cols): Editorial & Value Proposition -->
                <div class="lg:col-span-7 space-y-7 text-left">
                    
                    <!-- Clean Category Pill (No 45-min claim) -->
                    <div class="inline-flex items-center gap-2.5 px-3.5 py-1.5 rounded-full bg-[#f8f9fa] text-[#111111] text-xs font-semibold border border-[#e5e7eb]">
                        <span class="w-2 h-2 rounded-full bg-[#10b981] animate-pulse"></span>
                        <span>Certified Phone Repair Lab & Genuine Accessories</span>
                    </div>

                    <!-- Cal Sans Display Headline -->
                    <h1 class="cal-display-xl text-[#111111] max-w-2xl leading-[1.08]">
                        The better way to repair and equip your phone.
                    </h1>

                    <!-- Lead text with comfortable line-height -->
                    <p class="text-[#4b5563] text-base sm:text-lg max-w-xl font-normal leading-relaxed">
                        Professional display replacements, battery renewals, and chip-level micro soldering in {{ store_city() }}. Cleanroom ESD benches, zero data wipe, and a transparent 90-day warranty.
                    </p>
                    
                    <!-- Call To Action Row -->
                    <div class="pt-2 flex items-center gap-4 flex-wrap">
                        <a href="{{ route('public.store') }}" class="cal-btn-primary h-11 px-6 text-[14px]">
                            <span>Browse Accessories</span>
                            <svg class="w-4 h-4 stroke-current fill-none stroke-2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"></polyline></svg>
                        </a>
                        <a href="#repairs" class="cal-btn-secondary h-11 px-6 text-[14px]">
                            <span>View Repair Services</span>
                        </a>
                    </div>

                    <!-- Clean Trust Metrics -->
                    <div class="pt-6 border-t border-[#f3f4f6] flex items-center gap-8 text-xs text-[#6b7280] flex-wrap">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#10b981] stroke-current fill-none stroke-2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            <span class="font-medium text-[#111111]">90-Day Warranty</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#10b981] stroke-current fill-none stroke-2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            <span class="font-medium text-[#111111]">Zero Data Loss Policy</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#10b981] stroke-current fill-none stroke-2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            <span class="font-medium text-[#111111]">Express Diagnostic Bench</span>
                        </div>
                    </div>

                </div>

                <!-- RIGHT (5 cols): Streamlined, Clean Repair Ticket Lookup Card -->
                <div class="lg:col-span-5">
                    <div class="bg-[#fafafa] p-8 sm:p-9 border border-[#e5e7eb] rounded-2xl shadow-xs space-y-6">
                        
                        <div class="flex items-center justify-between pb-4 border-b border-[#e5e7eb]">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#10b981] animate-pulse"></span>
                                <span class="text-xs font-semibold text-[#111111]">Service Bench Status</span>
                            </div>
                            <span class="text-[11px] font-medium text-[#6b7280] bg-white px-2.5 py-1 rounded-full border border-[#e5e7eb]">
                                {{ $activeRepairsCount }} Active in Lab
                            </span>
                        </div>

                        <div class="space-y-2 text-left">
                            <h3 class="text-base font-semibold text-[#111111] tracking-tight">Track Your Repair</h3>
                            <p class="text-xs text-[#6b7280] leading-relaxed">
                                Enter your Job Sheet Ticket Number from your physical receipt to check live diagnostic status.
                            </p>
                        </div>

                        <!-- Ticket Lookup Form -->
                        <form action="{{ route('public.track_repair') }}" method="GET" class="space-y-3">
                            <div class="relative">
                                <input type="text" name="ticket_number" placeholder="e.g. REP-2026-0042" 
                                       class="w-full pl-10 pr-4 text-sm bg-white font-mono text-[#111111] placeholder:text-[#9ca3af] border border-[#d1d5db] rounded-lg h-11 focus:border-[#111111] focus:ring-1 focus:ring-[#111111] outline-none transition-all">
                                <svg class="w-4 h-4 text-[#6b7280] absolute left-3.5 top-1/2 -translate-y-1/2 stroke-current fill-none stroke-2" viewBox="0 0 24 24">
                                    <line x1="4" y1="9" x2="20" y2="9"></line>
                                    <line x1="4" y1="15" x2="20" y2="15"></line>
                                    <line x1="10" y1="3" x2="8" y2="21"></line>
                                    <line x1="16" y1="3" x2="14" y2="21"></line>
                                </svg>
                            </div>
                            <button type="submit" class="cal-btn-primary w-full h-11 justify-center rounded-lg">
                                <span>Track Ticket Status</span>
                                <svg class="w-4 h-4 stroke-current fill-none stroke-2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"></polyline></svg>
                            </button>
                        </form>

                        <div class="pt-4 border-t border-[#e5e7eb] flex items-center justify-between text-xs text-[#6b7280]">
                            <span>Need assistance?</span>
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', store_whatsapp()) }}?text={{ urlencode('Hi ' . store_name() . ', I have a question regarding my repair') }}" target="_blank" class="text-[#111111] font-semibold underline hover:text-[#3b82f6]">
                                WhatsApp Desk &rarr;
                            </a>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ════ 2. THREE CORE PILLARS (Clean, Spacious Cards) ════ -->
    <section class="py-20 sm:py-28 bg-white border-b border-[#e5e7eb]">
        <div class="max-w-[1200px] mx-auto px-6 sm:px-8">
            
            <div class="max-w-2xl mb-14 text-left space-y-3">
                <span class="text-xs font-semibold uppercase tracking-widest text-[#6b7280]">Cleanroom Bench Standards</span>
                <h2 class="cal-display-lg text-[#111111]">
                    Built for precision, safety, and absolute trust.
                </h2>
                <p class="text-[#4b5563] text-base leading-relaxed">
                    Every repair and accessory follows verified benchmarks. Transparent counter pricing with zero hidden steps.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <!-- Pillar 1: Rapid Turnaround (No 45-min claim) -->
                <div class="cal-card p-8 flex flex-col justify-between rounded-xl border border-[#e5e7eb] bg-[#fafafa]">
                    <div class="space-y-4">
                        <div class="w-10 h-10 rounded-lg bg-white border border-[#e5e7eb] flex items-center justify-center text-[#111111]">
                            <svg class="w-5 h-5 stroke-current fill-none stroke-2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-[#111111] tracking-tight">Rapid Turnaround Repairs</h3>
                        <p class="text-[#4b5563] text-sm leading-relaxed">
                            Screen replacements, charging ports, and battery renewals are executed promptly at our bench while you wait.
                        </p>
                    </div>

                    <div class="mt-8 pt-4 border-t border-[#e5e7eb] flex items-center justify-between text-xs text-[#6b7280]">
                        <span>Diagnostic SLA:</span>
                        <span class="font-semibold text-[#111111]">Immediate Counter Intake</span>
                    </div>
                </div>

                <!-- Pillar 2: Zero Data Loss Protocol -->
                <div class="cal-card p-8 flex flex-col justify-between rounded-xl border border-[#e5e7eb] bg-[#fafafa]">
                    <div class="space-y-4">
                        <div class="w-10 h-10 rounded-lg bg-white border border-[#e5e7eb] flex items-center justify-center text-[#111111]">
                            <svg class="w-5 h-5 stroke-current fill-none stroke-2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-[#111111] tracking-tight">Zero Data Loss Protocol</h3>
                        <p class="text-[#4b5563] text-sm leading-relaxed">
                            We never wipe your device storage. Your personal photos, WhatsApp chats, and banking apps remain secure and untouched.
                        </p>
                    </div>

                    <div class="mt-8 pt-4 border-t border-[#e5e7eb] flex items-center justify-between text-xs text-[#6b7280]">
                        <span>Device Reset:</span>
                        <span class="font-semibold text-[#111111]">Never Required</span>
                    </div>
                </div>

                <!-- Pillar 3: 90-Day Hardware Coverage -->
                <div class="cal-card p-8 flex flex-col justify-between rounded-xl border border-[#e5e7eb] bg-[#fafafa]">
                    <div class="space-y-4">
                        <div class="w-10 h-10 rounded-lg bg-white border border-[#e5e7eb] flex items-center justify-center text-[#111111]">
                            <svg class="w-5 h-5 stroke-current fill-none stroke-2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-[#111111] tracking-tight">Unconditional 90-Day Coverage</h3>
                        <p class="text-[#4b5563] text-sm leading-relaxed">
                            Every display panel, battery cell, and micro-soldered IC comes with an instant counter replacement warranty.
                        </p>
                    </div>

                    <div class="mt-8 pt-4 border-t border-[#e5e7eb] flex items-center justify-between text-xs text-[#6b7280]">
                        <span>Warranty Resolution:</span>
                        <span class="font-semibold text-[#111111]">Instant Counter Exchange</span>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- ════ 3. REPAIR SERVICES MATRIX (Spacious, Clean) ════ -->
    <section id="repairs" class="py-20 sm:py-28 bg-white border-b border-[#e5e7eb]">
        <div class="max-w-[1200px] mx-auto px-6 sm:px-8">
            
            <div class="flex flex-col sm:flex-row items-start sm:items-end justify-between gap-6 mb-14">
                <div class="space-y-3">
                    <span class="text-xs font-semibold uppercase tracking-widest text-[#6b7280]">Transparent Bench Pricing</span>
                    <h2 class="cal-display-lg text-[#111111]">
                        Express Repair Services.
                    </h2>
                    <p class="text-[#4b5563] text-base">
                        Cleanroom ESD benches for iPhone, Samsung, OnePlus, Vivo, Oppo, and Xiaomi.
                    </p>
                </div>
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', store_whatsapp()) }}?text={{ urlencode('Hi ' . store_name() . ', I would like to get a repair quote for my phone') }}" target="_blank" class="cal-btn-secondary h-11 px-5 shrink-0">
                    <span>Ask For Custom Quote</span>
                    <svg class="w-4 h-4 stroke-current fill-none stroke-2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </a>
            </div>

            <!-- Repair Pricing Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                
                <!-- 1. Screen Replacement -->
                <div class="cal-product-card p-7 flex flex-col justify-between rounded-xl border border-[#e5e7eb]">
                    <div>
                        <div class="flex items-center justify-between text-xs text-[#6b7280] mb-3">
                            <span class="font-semibold uppercase tracking-wider text-[11px] text-[#111111]">Display</span>
                            <span class="text-xs font-medium text-[#10b981]">Express Service</span>
                        </div>
                        <h3 class="text-xl font-semibold text-[#111111]">Screen & OLED Panel</h3>
                        <p class="text-[#4b5563] text-xs mt-2 leading-relaxed">
                            Cracked glass, blank display, green lines, or touch lag. Premium OLED replacements with TrueTone and calibrated touch response.
                        </p>
                    </div>

                    <div class="mt-8 pt-4 border-t border-[#f3f4f6] flex items-center justify-between">
                        <div>
                            <span class="text-[11px] text-[#6b7280] block">Starting from</span>
                            <span class="text-xl font-bold text-[#111111]">₹1,299</span>
                        </div>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', store_whatsapp()) }}?text={{ urlencode('Hi ' . store_name() . ', I need Screen Replacement estimate for my phone') }}" target="_blank" class="cal-btn-primary text-xs h-9 px-4 rounded-lg">
                            Book Slot
                        </a>
                    </div>
                </div>

                <!-- 2. Battery Health Renewal -->
                <div class="cal-product-card p-7 flex flex-col justify-between rounded-xl border border-[#e5e7eb]">
                    <div>
                        <div class="flex items-center justify-between text-xs text-[#6b7280] mb-3">
                            <span class="font-semibold uppercase tracking-wider text-[11px] text-[#111111]">Power Cell</span>
                            <span class="text-xs font-medium text-[#10b981]">Fast Swap</span>
                        </div>
                        <h3 class="text-xl font-semibold text-[#111111]">Battery Health Renewal</h3>
                        <p class="text-[#4b5563] text-xs mt-2 leading-relaxed">
                            Sudden drops, swelling back cover, or fast drain. High-capacity grade-A cells with full-day backup and zero error popups.
                        </p>
                    </div>

                    <div class="mt-8 pt-4 border-t border-[#f3f4f6] flex items-center justify-between">
                        <div>
                            <span class="text-[11px] text-[#6b7280] block">Starting from</span>
                            <span class="text-xl font-bold text-[#111111]">₹899</span>
                        </div>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', store_whatsapp()) }}?text={{ urlencode('Hi ' . store_name() . ', I need a Battery Replacement for my phone') }}" target="_blank" class="cal-btn-primary text-xs h-9 px-4 rounded-lg">
                            Book Slot
                        </a>
                    </div>
                </div>

                <!-- 3. Motherboard Micro-Soldering -->
                <div class="bg-[#101010] text-white rounded-xl p-7 flex flex-col justify-between shadow-sm relative overflow-hidden">
                    <div>
                        <div class="flex items-center justify-between text-xs text-[#a1a1aa] mb-3">
                            <span class="font-bold uppercase tracking-wider text-[11px] text-[#3b82f6]">Featured Specialty</span>
                            <span class="bg-[#1a1a1a] px-2.5 py-0.5 rounded text-white font-mono text-[11px]">Level 4 Lab</span>
                        </div>
                        <h3 class="text-xl font-semibold text-white">Motherboard Micro-Soldering</h3>
                        <p class="text-[#a1a1aa] text-xs mt-2 leading-relaxed">
                            Dead device recovery, short-circuit diagnostics, Audio IC, network drops, and Power IC reballing under stereomicroscopes.
                        </p>
                    </div>

                    <div class="mt-8 pt-4 border-t border-[#262626] flex items-center justify-between">
                        <div>
                            <span class="text-[11px] text-[#a1a1aa] block">Starting from</span>
                            <span class="text-xl font-bold text-white">₹1,499</span>
                        </div>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', store_whatsapp()) }}?text={{ urlencode('Hi ' . store_name() . ', my phone is dead/not turning on. Need Motherboard Micro-Soldering diagnosis') }}" target="_blank" class="bg-white text-[#111111] hover:bg-[#f3f4f6] font-semibold text-xs h-9 px-4 rounded-lg flex items-center transition-colors">
                            Book Lab Slot
                        </a>
                    </div>
                </div>

                <!-- 4. Charging Port & Mic Flex -->
                <div class="cal-product-card p-7 flex flex-col justify-between rounded-xl border border-[#e5e7eb]">
                    <div>
                        <div class="flex items-center justify-between text-xs text-[#6b7280] mb-3">
                            <span class="font-semibold uppercase tracking-wider text-[11px] text-[#111111]">Connector</span>
                            <span class="text-xs font-medium text-[#10b981]">Quick Fix</span>
                        </div>
                        <h3 class="text-xl font-semibold text-[#111111]">Charging Port & Mic Flex</h3>
                        <p class="text-[#4b5563] text-xs mt-2 leading-relaxed">
                            Loose Type-C or Lightning pins, cable disconnecting, slow charging, or caller unable to hear voice.
                        </p>
                    </div>

                    <div class="mt-8 pt-4 border-t border-[#f3f4f6] flex items-center justify-between">
                        <div>
                            <span class="text-[11px] text-[#6b7280] block">Starting from</span>
                            <span class="text-xl font-bold text-[#111111]">₹499</span>
                        </div>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', store_whatsapp()) }}?text={{ urlencode('Hi ' . store_name() . ', my phone charging port is loose/not charging') }}" target="_blank" class="cal-btn-primary text-xs h-9 px-4 rounded-lg">
                            Fix Port
                        </a>
                    </div>
                </div>

                <!-- 5. Laser Back Glass -->
                <div class="cal-product-card p-7 flex flex-col justify-between rounded-xl border border-[#e5e7eb]">
                    <div>
                        <div class="flex items-center justify-between text-xs text-[#6b7280] mb-3">
                            <span class="font-semibold uppercase tracking-wider text-[11px] text-[#111111]">Chassis</span>
                            <span class="text-xs font-medium text-[#10b981]">Clean Laser</span>
                        </div>
                        <h3 class="text-xl font-semibold text-[#111111]">Laser Back Glass Repair</h3>
                        <p class="text-[#4b5563] text-xs mt-2 leading-relaxed">
                            Shattered rear glass replaced cleanly using non-invasive cold laser ablation without opening internal chassis seals.
                        </p>
                    </div>

                    <div class="mt-8 pt-4 border-t border-[#f3f4f6] flex items-center justify-between">
                        <div>
                            <span class="text-[11px] text-[#6b7280] block">Starting from</span>
                            <span class="text-xl font-bold text-[#111111]">₹1,199</span>
                        </div>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', store_whatsapp()) }}?text={{ urlencode('Hi ' . store_name() . ', I need Back Glass Replacement for my phone') }}" target="_blank" class="cal-btn-primary text-xs h-9 px-4 rounded-lg">
                            Laser Fix
                        </a>
                    </div>
                </div>

                <!-- 6. Water Damage Recovery -->
                <div class="cal-product-card p-7 flex flex-col justify-between rounded-xl border border-[#e5e7eb]">
                    <div>
                        <div class="flex items-center justify-between text-xs text-[#6b7280] mb-3">
                            <span class="font-semibold uppercase tracking-wider text-[11px] text-[#111111]">Emergency</span>
                            <span class="text-xs font-medium text-[#ef4444]">High Priority</span>
                        </div>
                        <h3 class="text-xl font-semibold text-[#111111]">Water & Liquid Recovery</h3>
                        <p class="text-[#4b5563] text-xs mt-2 leading-relaxed">
                            Immediate battery isolation, ultrasonic chemical cleaning, and thermal imaging to prevent corrosion damage.
                        </p>
                    </div>

                    <div class="mt-8 pt-4 border-t border-[#f3f4f6] flex items-center justify-between">
                        <div>
                            <span class="text-[11px] text-[#6b7280] block">Starting from</span>
                            <span class="text-xl font-bold text-[#111111]">₹999</span>
                        </div>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', store_whatsapp()) }}?text={{ urlencode('Hi ' . store_name() . ', my phone fell in water! Need emergency assistance') }}" target="_blank" class="cal-btn-primary text-xs h-9 px-4 rounded-lg">
                            Emergency Fix
                        </a>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- ════ 4. CURATED ACCESSORIES SHOWCASE ════ -->
    @if($featuredAccessories->count() > 0)
    <section class="py-20 sm:py-28 bg-white border-b border-[#e5e7eb]">
        <div class="max-w-[1200px] mx-auto px-6 sm:px-8">
            
            <div class="flex flex-col sm:flex-row items-start sm:items-end justify-between gap-6 mb-12">
                <div class="space-y-3">
                    <span class="text-xs font-semibold uppercase tracking-widest text-[#6b7280]">Tested Spares & Accessories</span>
                    <h2 class="cal-display-lg text-[#111111]">
                        Curated Mobile Inventory.
                    </h2>
                    <p class="text-[#4b5563] text-base">
                        Fast GaN chargers, durable cases, 11D tempered glass, and OEM batteries in {{ store_city() }}.
                    </p>
                </div>
                <a href="{{ route('public.store') }}" class="cal-btn-primary h-11 px-5 shrink-0">
                    <span>Explore Full Catalog</span>
                    <svg class="w-4 h-4 stroke-current fill-none stroke-2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </a>
            </div>

            <!-- Featured Accessories Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-7">
                @foreach($featuredAccessories as $item)
                <div class="cal-product-card p-6 flex flex-col justify-between group rounded-xl border border-[#e5e7eb] hover:border-[#111111] transition-all">
                    <div>
                        <div class="flex items-center justify-between text-xs mb-3">
                            <span class="font-medium text-[11px] text-[#6b7280] bg-[#f8f9fa] px-2.5 py-0.5 rounded">
                                {{ $item->brand ?? 'OEM Original' }}
                            </span>
                            @if($item->stock_qty > 0)
                                <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-[#10b981]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#10b981]"></span>
                                    <span>In Stock</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-[#ef4444]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#ef4444]"></span>
                                    <span>Out of Stock</span>
                                </span>
                            @endif
                        </div>

                        <h3 class="font-semibold text-[#111111] text-base group-hover:underline line-clamp-2 mt-2 leading-snug">
                            <a href="{{ route('public.product.show', $item->id) }}" class="text-[#111111]">
                                {{ $item->name }}
                            </a>
                        </h3>

                        @if(!empty($item->compatible_model))
                        <div class="text-xs text-[#6b7280] mt-2 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-[#9ca3af] stroke-current fill-none stroke-2" viewBox="0 0 24 24"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect></svg>
                            <span class="truncate">Fits: {{ $item->compatible_model }}</span>
                        </div>
                        @endif
                    </div>

                    <div class="mt-6 pt-4 border-t border-[#f3f4f6] flex items-center justify-between">
                        <div>
                            <span class="text-[10px] text-[#6b7280] block font-semibold uppercase">Price</span>
                            <span class="font-bold text-[#111111] text-lg">₹{{ number_format($item->selling_price, 0) }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('public.product.show', $item->id) }}" class="cal-btn-secondary text-xs h-8 px-3 rounded-md">
                                View
                            </a>
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', store_whatsapp()) }}?text={{ urlencode('Hi ' . store_name() . ', I would like to purchase ' . $item->name . ' (₹' . $item->selling_price . ')') }}" target="_blank" class="cal-btn-primary text-xs h-8 px-3 rounded-md" title="Order via WhatsApp">
                                Buy
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

        </div>
    </section>
    @endif

    <!-- ════ 5. PRE-FOOTER ASSISTANCE CTA (Airy, Centered) ════ -->
    <section class="py-20 sm:py-28 bg-[#fafafa]">
        <div class="max-w-[840px] mx-auto px-6 text-center space-y-6">
            <span class="text-xs font-semibold uppercase tracking-widest text-[#6b7280]">Prompt Counter Support</span>
            <h2 class="cal-display-md text-[#111111]">
                Need immediate device diagnostic or spare parts in {{ store_city() }}?
            </h2>
            <p class="text-[#4b5563] text-base leading-relaxed max-w-lg mx-auto">
                Visit our service lab or message our technicians directly on WhatsApp for immediate pricing and parts availability.
            </p>
            <div class="pt-4 flex items-center justify-center gap-4 flex-wrap">
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', store_whatsapp()) }}?text={{ urlencode('Hi ' . store_name() . ', I would like to inquire about phone repair/accessories') }}" target="_blank" class="cal-btn-primary h-11 px-6 text-sm rounded-lg">
                    <span>Message Service Desk</span>
                    <svg class="w-4 h-4 stroke-current fill-none stroke-2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </a>
                <a href="{{ route('public.track_repair') }}" class="cal-btn-secondary h-11 px-6 text-sm rounded-lg">
                    <span>Track Ongoing Ticket</span>
                </a>
            </div>
        </div>
    </section>

@endsection
