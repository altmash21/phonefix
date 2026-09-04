@extends('mobileshop.public.layout')

@section('title', 'MobiTrack — Premium Smartphones, Certified Pre-Owned & Express Repairs')
@section('meta_description', 'Discover official brand new smartphones, 50-point certified pre-owned devices, original mobile accessories, and 45-minute express repair services in Mumbai.')

@section('content')

    <!-- ════ HERO SECTION (Calm Neutral B2B Aesthetic) ════ -->
    <section class="bg-slate-900 text-white py-14 lg:py-20 px-4 sm:px-6 lg:px-8 border-b border-slate-800">
        <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
            <!-- Left: Hero Text -->
            <div class="lg:col-span-7 space-y-5 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-md bg-slate-800 border border-slate-700 text-brand-300 text-xs font-semibold uppercase tracking-wider">
                    <span class="w-2 h-2 rounded-full bg-brand-500"></span>
                    <span>Mumbai's Certified Mobile Store & Service Lab</span>
                </div>

                <h1 class="font-display font-bold text-3xl sm:text-4xl lg:text-5xl tracking-tight leading-tight text-white">
                    Smartphones You Love. <br>
                    Prices & Trust You Deserve.
                </h1>

                <p class="text-slate-400 text-base sm:text-lg max-w-2xl mx-auto lg:mx-0 leading-relaxed font-normal">
                    Get genuine factory-sealed brand new phones with manufacturer warranty, or save up to 45% on 50-point certified pre-owned devices inspected by master technicians.
                </p>

                <!-- Trust Metrics Under Hero -->
                <div class="pt-4 border-t border-slate-800 grid grid-cols-3 gap-4 max-w-lg mx-auto lg:mx-0 text-left">
                    <div>
                        <div class="font-display font-bold text-2xl text-white">100%</div>
                        <div class="text-xs text-slate-400 font-medium">Genuine Warranty</div>
                    </div>
                    <div>
                        <div class="font-display font-bold text-2xl text-brand-400">50-Point</div>
                        <div class="text-xs text-slate-400 font-medium">Pre-Owned Check</div>
                    </div>
                    <div>
                        <div class="font-display font-bold text-2xl text-emerald-400">45 Min</div>
                        <div class="text-xs text-slate-400 font-medium">Express Repair</div>
                    </div>
                </div>
            </div>

            <!-- Right: Live Counter Feed Card -->
            <div class="lg:col-span-5">
                <div class="mx-auto max-w-md bg-slate-800/80 border border-slate-700/80 rounded-2xl p-5 shadow-xl space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-700 pb-3">
                        <span class="text-xs font-semibold text-slate-300">Live Store Feed</span>
                        <span class="text-[11px] font-medium text-emerald-400 bg-emerald-950/60 border border-emerald-800/60 px-2 py-0.5 rounded">Counters Open</span>
                    </div>

                    <!-- Deal 1: Brand New Mobiles -->
                    <a href="{{ route('public.store') }}?tab=new" class="block bg-slate-900/70 hover:bg-slate-900 rounded-xl p-3.5 border border-slate-700/60 transition-colors text-decoration-none">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-brand-900/60 text-brand-400 flex items-center justify-center">
                                    <i data-lucide="smartphone" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <div class="text-xs font-semibold text-slate-300">Brand New Phones</div>
                                    <div class="text-sm font-bold text-white">{{ $newCount ?? 12 }} Sealed Models In Stock</div>
                                </div>
                            </div>
                            <span class="text-xs font-semibold text-brand-400">View Stock →</span>
                        </div>
                    </a>

                    <!-- Deal 2: Certified Pre-Owned -->
                    <a href="{{ route('public.store') }}?tab=second_hand" class="block bg-slate-900/70 hover:bg-slate-900 rounded-xl p-3.5 border border-slate-700/60 transition-colors text-decoration-none">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-amber-950/60 text-amber-400 flex items-center justify-center">
                                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <div class="text-xs font-semibold text-slate-300">Certified Pre-Owned</div>
                                    <div class="text-sm font-bold text-white">{{ $secondHandCount ?? 8 }} Tested Devices Ready</div>
                                </div>
                            </div>
                            <span class="text-xs font-semibold text-amber-400">View Stock →</span>
                        </div>
                    </a>

                    <!-- Deal 3: Service Lab -->
                    <a href="{{ route('public.track_repair') }}" class="block bg-slate-900/70 hover:bg-slate-900 rounded-xl p-3.5 border border-slate-700/60 transition-colors text-decoration-none">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-sky-950/60 text-sky-400 flex items-center justify-center">
                                    <i data-lucide="wrench" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <div class="text-xs font-semibold text-slate-300">Service Desk</div>
                                    <div class="text-sm font-bold text-white">Express Screen & Battery Repair</div>
                                </div>
                            </div>
                            <span class="text-xs font-semibold text-sky-400">Track Repair →</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ════ 4 VALUE PILLARS ════ -->
    <section class="py-14 bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Pillar 1 -->
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-brand-100 text-brand-700 flex items-center justify-center shrink-0">
                        <i data-lucide="shield-check" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="font-display font-bold text-base text-slate-900">100% Genuine Guarantee</h3>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">Official sealed devices directly with manufacturer warranty and IMEI serial bill.</p>
                    </div>
                </div>

                <!-- Pillar 2 -->
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-800 flex items-center justify-center shrink-0">
                        <i data-lucide="check-circle-2" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="font-display font-bold text-base text-slate-900">50-Point Certified Lab</h3>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">Rigorous hardware, display, camera, and battery health inspection on all pre-owned stock.</p>
                    </div>
                </div>

                <!-- Pillar 3 -->
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-sky-100 text-sky-700 flex items-center justify-center shrink-0">
                        <i data-lucide="clock-3" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="font-display font-bold text-base text-slate-900">45-Min Fast Repair</h3>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">Original displays and high-capacity battery replacements done on the spot while you wait.</p>
                    </div>
                </div>

                <!-- Pillar 4 -->
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                        <i data-lucide="badge-percent" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="font-display font-bold text-base text-slate-900">0% EMI & Instant Buyback</h3>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">Bajaj Finserv, HDB & Credit card financing options. Trade in old phone for top cash.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ════ LIVE FEATURED INVENTORY (NEW PHONES) ════ -->
    <section class="py-16 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <span class="text-xs font-extrabold text-brand-700 uppercase tracking-wider">Sealed With Warranty</span>
                    <h2 class="font-display font-black text-3xl text-slate-900 mt-1">Trending Brand New Smartphones</h2>
                    <p class="text-sm text-slate-500 mt-1">Direct from official distributors with free tempered glass and case bundled.</p>
                </div>
                <a href="{{ route('public.store') }}?tab=new" class="inline-flex items-center gap-1.5 text-xs font-bold text-brand-700 hover:text-brand-800 bg-white border border-slate-200 px-4 py-2 rounded-xl shadow-xs hover:shadow transition-all">
                    <span>View All New Phones</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <!-- New Phone Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($featuredNew as $phone)
                    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs hover:shadow-xl hover:-translate-y-1 transition-all flex flex-col justify-between group">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-brand-100 text-brand-800">
                                    Brand New Sealed
                                </span>
                                <span class="text-xs font-bold text-slate-400 font-mono">{{ $phone->brand }}</span>
                            </div>

                            <div>
                                <h3 class="font-display font-extrabold text-xl text-slate-900 group-hover:text-brand-600 transition-colors">
                                    {{ $phone->brand }} {{ $phone->model }}
                                </h3>
                                <div class="flex flex-wrap gap-2 text-xs text-slate-500 mt-2">
                                    <span class="px-2.5 py-1 rounded-lg bg-slate-100 font-semibold">{{ $phone->storage ?? '128GB' }}</span>
                                    <span class="px-2.5 py-1 rounded-lg bg-slate-100 font-semibold">{{ $phone->ram ?? '8GB' }} RAM</span>
                                    <span class="px-2.5 py-1 rounded-lg bg-slate-100 font-semibold">{{ $phone->color ?? 'Black' }}</span>
                                </div>
                            </div>

                            <div class="p-3 bg-brand-50/60 rounded-2xl text-xs space-y-1 text-brand-900">
                                <p class="flex items-center gap-1.5 font-medium">
                                    <i data-lucide="gift" class="w-3.5 h-3.5 text-brand-600"></i>
                                    <span>Free 9D Tempered Glass + Protective Case</span>
                                </p>
                                <p class="flex items-center gap-1.5 font-medium">
                                    <i data-lucide="credit-card" class="w-3.5 h-3.5 text-brand-600"></i>
                                    <span>0% EMI Available via Store Desk</span>
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                            <div>
                                <span class="text-[10px] text-slate-400 font-bold uppercase">Store Price</span>
                                <div class="text-2xl font-black text-slate-900">₹{{ number_format($phone->selling_price, 2) }}</div>
                            </div>
                            <a href="https://wa.me/919876543210?text={{ urlencode('Hi MobiTrack, I want to inquire about ' . $phone->brand . ' ' . $phone->model . ' listed on your website.') }}" target="_blank" class="px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-md shadow-brand-600/20 flex items-center gap-1.5 transition-all">
                                <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                <span>Inquire / Buy</span>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 bg-white rounded-3xl p-8 text-center text-slate-500 border border-slate-200">
                        <p class="font-bold text-slate-700">New phone stock arriving this morning!</p>
                        <p class="text-xs mt-1">Please check our explore store page or call us directly.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- ════ CERTIFIED PRE-OWNED HIGHLIGHTS ════ -->
    <section class="py-16 bg-white border-t border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <span class="text-xs font-extrabold text-amber-700 uppercase tracking-wider">Certified Pre-Owned</span>
                    <h2 class="font-display font-black text-3xl text-slate-900 mt-1">Inspected Second-Hand Smartphones</h2>
                    <p class="text-sm text-slate-500 mt-1">Save big on Grade A & A+ certified smartphones with battery health guarantee and store warranty.</p>
                </div>
                <a href="{{ route('public.store') }}?tab=second_hand" class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-800 bg-amber-50 hover:bg-amber-100 border border-amber-200 px-4 py-2 rounded-xl transition-all">
                    <span>View All Pre-Owned Deals</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <!-- Second Hand Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($featuredSecondHand as $phone)
                    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs hover:shadow-xl hover:-translate-y-1 transition-all flex flex-col justify-between group">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-100 text-amber-900">
                                    Grade {{ strtoupper($phone->condition_grade ?? 'A') }}
                                </span>
                                @if($phone->battery_health)
                                    <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-lg">
                                        🔋 {{ $phone->battery_health }}% Battery Health
                                    </span>
                                @endif
                            </div>

                            <div>
                                <h3 class="font-display font-extrabold text-xl text-slate-900 group-hover:text-amber-600 transition-colors">
                                    {{ $phone->brand }} {{ $phone->model }}
                                </h3>
                                <div class="flex flex-wrap gap-2 text-xs text-slate-500 mt-2">
                                    <span class="px-2.5 py-1 rounded-lg bg-slate-100 font-semibold">{{ $phone->storage ?? '128GB' }}</span>
                                    <span class="px-2.5 py-1 rounded-lg bg-slate-100 font-semibold">{{ $phone->color ?? 'Graphite' }}</span>
                                </div>
                            </div>

                            @if($phone->checklist_notes)
                                <p class="text-xs text-slate-600 bg-slate-50 p-3 rounded-2xl border border-slate-100 line-clamp-2">
                                    "{{ $phone->checklist_notes }}"
                                </p>
                            @endif

                            <div class="p-3 bg-amber-50/60 rounded-2xl text-xs space-y-1 text-amber-950 font-medium">
                                <p>✓ 50-Point Hardware & Screen Diagnostic Passed</p>
                                <p>✓ 30-Day In-Store Replacement Warranty</p>
                            </div>
                        </div>

                        <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                            <div>
                                <span class="text-[10px] text-slate-400 font-bold uppercase">Deal Price</span>
                                <div class="text-2xl font-black text-slate-900">₹{{ number_format($phone->selling_price, 2) }}</div>
                            </div>
                            <a href="https://wa.me/919876543210?text={{ urlencode('Hi MobiTrack, I want to reserve pre-owned ' . $phone->brand . ' ' . $phone->model . ' for ₹' . number_format($phone->selling_price, 2)) }}" target="_blank" class="px-4 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-md shadow-amber-600/20 flex items-center gap-1.5 transition-all">
                                <i data-lucide="tag" class="w-3.5 h-3.5"></i>
                                <span>Reserve Device</span>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 bg-white rounded-3xl p-8 text-center text-slate-500 border border-slate-200">
                        <p class="font-bold text-slate-700">Pre-owned devices currently undergoing inspection!</p>
                        <p class="text-xs mt-1">Visit our store to check walk-in buyback inventory.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- ════ EXPRESS REPAIR SERVICE SECTION ════ -->
    <section class="py-20 bg-slate-950 text-white relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="max-w-2xl mx-auto text-center space-y-4 mb-16">
                <span class="px-3.5 py-1.5 rounded-full bg-sky-500/20 text-sky-400 text-xs font-extrabold uppercase tracking-wider border border-sky-500/30">
                    MobiTrack Service Lab
                </span>
                <h2 class="font-display font-black text-3xl sm:text-4xl">Fast, Precision Smartphone Repairs</h2>
                <p class="text-sm text-slate-400">Broken screen? Draining battery? Water damage? Our chip-level technicians fix over 95% of issues on the exact same day.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Service 1 -->
                <div class="bg-white/5 border border-white/10 rounded-3xl p-6 hover:bg-white/10 transition-all space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-sky-500/20 text-sky-400 flex items-center justify-center">
                        <i data-lucide="smartphone" class="w-6 h-6"></i>
                    </div>
                    <h3 class="font-display font-bold text-lg text-white">Original Display Glass</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">OLED & Super AMOLED screen replacements with authentic color reproduction and touch responsiveness.</p>
                    <span class="text-xs font-bold text-sky-400 block pt-1">Time: ~35 Mins</span>
                </div>

                <!-- Service 2 -->
                <div class="bg-white/5 border border-white/10 rounded-3xl p-6 hover:bg-white/10 transition-all space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center">
                        <i data-lucide="battery-charging" class="w-6 h-6"></i>
                    </div>
                    <h3 class="font-display font-bold text-lg text-white">Battery Health Boost</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">Certified high-capacity battery replacements restoring full day battery life with zero warning popups.</p>
                    <span class="text-xs font-bold text-emerald-400 block pt-1">Time: ~25 Mins</span>
                </div>

                <!-- Service 3 -->
                <div class="bg-white/5 border border-white/10 rounded-3xl p-6 hover:bg-white/10 transition-all space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-purple-500/20 text-purple-400 flex items-center justify-center">
                        <i data-lucide="cpu" class="w-6 h-6"></i>
                    </div>
                    <h3 class="font-display font-bold text-lg text-white">Motherboard IC Repair</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">Micro-soldering, audio IC, power management, charging port flex and network receiver repairs.</p>
                    <span class="text-xs font-bold text-purple-400 block pt-1">Time: Same Day</span>
                </div>

                <!-- Service 4 -->
                <div class="bg-white/5 border border-white/10 rounded-3xl p-6 hover:bg-white/10 transition-all space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-amber-500/20 text-amber-400 flex items-center justify-center">
                        <i data-lucide="droplet" class="w-6 h-6"></i>
                    </div>
                    <h3 class="font-display font-bold text-lg text-white">Water Damage Revival</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">Ultrasonic board cleansing and short-circuit diagnosis to revive phones exposed to moisture or liquid.</p>
                    <span class="text-xs font-bold text-amber-400 block pt-1">Time: 24-48 Hours</span>
                </div>
            </div>

            <div class="mt-12 text-center">
                <a href="{{ route('public.track_repair') }}" class="inline-flex items-center gap-2 px-8 py-3.5 rounded-2xl bg-sky-500 hover:bg-sky-600 text-white font-extrabold text-sm shadow-lg shadow-sky-500/20 transition-all">
                    <i data-lucide="search" class="w-4 h-4"></i>
                    <span>Already Left a Phone? Track Repair Status Live</span>
                </a>
            </div>
        </div>
    </section>

    <!-- ════ CTA VISIT / CONTACT (Calm Neutral) ════ -->
    <section class="py-16 bg-slate-900 border-t border-slate-800 text-white text-center">
        <div class="max-w-4xl mx-auto px-4 space-y-5">
            <h2 class="font-display font-bold text-2xl sm:text-3xl text-white">Ready to Upgrade or Fix Your Phone?</h2>
            <p class="text-slate-400 text-sm sm:text-base max-w-xl mx-auto leading-relaxed">Visit our showroom on Linking Road, Bandra West. Open all 7 days with live device demos, instant trade-ins, and free diagnosis.</p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
                <a href="{{ route('public.store') }}" class="px-6 py-3 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm transition-colors">
                    Explore Store Catalog
                </a>
                <a href="{{ route('public.contact') }}" class="px-6 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-sm border border-slate-700 transition-colors">
                    Store Location & Directions
                </a>
            </div>
        </div>
    </section>

@endsection
