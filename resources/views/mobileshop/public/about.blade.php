@extends('mobileshop.public.layout')

@section('title', 'About Us — MobiTrack Telecom & Electronics')
@section('meta_description', 'Learn about MobiTrack, Mumbai’s trusted mobile retail showroom and certified smartphone repair center. Discover our 50-point inspection process and genuine parts pledge.')

@section('content')

    <!-- Hero Header -->
    <div class="bg-gradient-to-b from-slate-950 via-brand-950 to-slate-900 text-white py-16 px-4 text-center">
        <div class="max-w-3xl mx-auto space-y-4">
            <span class="px-3.5 py-1.5 rounded-full bg-brand-500/20 text-brand-300 border border-brand-500/30 text-xs font-bold uppercase tracking-wider">
                Our Story & Commitment
            </span>
            <h1 class="font-display font-black text-4xl sm:text-5xl text-white">Your Trusted Partner in Mobile Retail & Repairs</h1>
            <p class="text-slate-300 text-base max-w-2xl mx-auto leading-relaxed">
                Founded with a mission to eliminate fraud in mobile retail and second-hand smartphone trading through absolute transparency, diagnostic verification, and certified warranties.
            </p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 space-y-20">

        <!-- Section 1: Who We Are -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div class="space-y-6">
                <div class="inline-flex items-center gap-2 text-brand-700 font-bold text-xs uppercase tracking-wider">
                    <span class="w-2 h-2 rounded-full bg-brand-600"></span>
                    <span>Retail Excellence Since 2018</span>
                </div>
                <h2 class="font-display font-black text-3xl sm:text-4xl text-slate-900 leading-tight">
                    Transforming How Mumbai Buys, Sells & Repairs Smartphones.
                </h2>
                <p class="text-slate-600 text-sm leading-relaxed">
                    MobiTrack is an authorized retailer and multi-brand smartphone hub located on Linking Road, Bandra West. We bridge the gap between expensive brand-new devices and risky second-hand street markets by offering <strong>100% genuine sealed smartphones</strong> alongside <strong>laboratory-tested certified pre-owned devices</strong>.
                </p>
                <p class="text-slate-600 text-sm leading-relaxed">
                    Our in-house micro-soldering laboratory is staffed by certified technicians capable of diagnosing and reviving complex board-level faults in under 45 minutes, saving thousands of customer devices from being discarded prematurely.
                </p>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-6 pt-4 border-t border-slate-200">
                    <div>
                        <div class="font-display font-black text-3xl text-brand-700">15,000+</div>
                        <div class="text-xs text-slate-500 font-medium mt-0.5">Satisfied Customers</div>
                    </div>
                    <div>
                        <div class="font-display font-black text-3xl text-brand-700">99.2%</div>
                        <div class="text-xs text-slate-500 font-medium mt-0.5">Repair Success Rate</div>
                    </div>
                    <div>
                        <div class="font-display font-black text-3xl text-brand-700">4.9 ★</div>
                        <div class="text-xs text-slate-500 font-medium mt-0.5">Google Rating</div>
                    </div>
                </div>
            </div>

            <!-- Visual Showcase Card -->
            <div class="bg-gradient-to-br from-brand-900 to-indigo-900 rounded-3xl p-8 text-white shadow-2xl space-y-6 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-brand-500/20 rounded-full blur-3xl pointer-events-none"></div>

                <div class="space-y-2">
                    <span class="text-xs font-bold text-brand-300 uppercase tracking-wider">The MobiTrack Standard</span>
                    <h3 class="font-display font-black text-2xl text-white">Why Customers Choose Us Every Single Day</h3>
                </div>

                <div class="space-y-4 text-sm text-slate-200">
                    <div class="flex items-start gap-3 bg-white/10 p-4 rounded-2xl border border-white/10">
                        <i data-lucide="shield-check" class="w-5 h-5 text-emerald-400 shrink-0 mt-0.5"></i>
                        <div>
                            <strong class="text-white block font-bold">Zero Grey-Market Stock</strong>
                            <span class="text-xs text-slate-300">Every brand-new smartphone comes with valid GST tax invoice and active brand warranty.</span>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 bg-white/10 p-4 rounded-2xl border border-white/10">
                        <i data-lucide="cpu" class="w-5 h-5 text-purple-300 shrink-0 mt-0.5"></i>
                        <div>
                            <strong class="text-white block font-bold">Level 4 Certified Lab</strong>
                            <span class="text-xs text-slate-300">Chip-level micro-soldering, authentic replacement displays, and original adhesive seals.</span>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 bg-white/10 p-4 rounded-2xl border border-white/10">
                        <i data-lucide="dollar-sign" class="w-5 h-5 text-amber-300 shrink-0 mt-0.5"></i>
                        <div>
                            <strong class="text-white block font-bold">Transparent Buyback Pricing</strong>
                            <span class="text-xs text-slate-300">Algorithmic device valuation with instant cash payment or trade-in store credit.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: The 50-Point Pre-Owned Checklist -->
        <div class="bg-white rounded-3xl p-8 sm:p-12 border border-slate-200 shadow-xs space-y-10">
            <div class="text-center max-w-2xl mx-auto space-y-3">
                <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-bold uppercase tracking-wider">
                    Quality Assurance
                </span>
                <h2 class="font-display font-black text-3xl text-slate-900">Our Rigorous 50-Point Diagnostic Checklist</h2>
                <p class="text-slate-500 text-sm">Every pre-owned smartphone sold at MobiTrack must pass all 50 technical benchmarks before reaching the display counter.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 text-xs text-slate-600">
                <!-- Category 1 -->
                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 space-y-3">
                    <div class="w-8 h-8 rounded-xl bg-brand-100 text-brand-700 flex items-center justify-center font-bold">1</div>
                    <h4 class="font-bold text-sm text-slate-900">Display & Touch</h4>
                    <ul class="space-y-1.5 text-slate-500">
                        <li>✓ 100% Touch responsiveness grid</li>
                        <li>✓ TrueTone & color calibration</li>
                        <li>✓ Dead pixel & backlight bleed test</li>
                        <li>✓ Original glass scratch grading</li>
                    </ul>
                </div>

                <!-- Category 2 -->
                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 space-y-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">2</div>
                    <h4 class="font-bold text-sm text-slate-900">Battery & Power</h4>
                    <ul class="space-y-1.5 text-slate-500">
                        <li>✓ Genuine OEM battery verification</li>
                        <li>✓ Minimum 80%+ health certified</li>
                        <li>✓ Thermal regulation during charging</li>
                        <li>✓ Fast charge protocol testing</li>
                    </ul>
                </div>

                <!-- Category 3 -->
                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 space-y-3">
                    <div class="w-8 h-8 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center font-bold">3</div>
                    <h4 class="font-bold text-sm text-slate-900">Cameras & Sensors</h4>
                    <ul class="space-y-1.5 text-slate-500">
                        <li>✓ OIS & auto-focus alignment</li>
                        <li>✓ 4K video recording stability</li>
                        <li>✓ FaceID / TouchID biometric scan</li>
                        <li>✓ Gyroscope, proximity & light sensors</li>
                    </ul>
                </div>

                <!-- Category 4 -->
                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 space-y-3">
                    <div class="w-8 h-8 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center font-bold">4</div>
                    <h4 class="font-bold text-sm text-slate-900">Connectivity & Legality</h4>
                    <ul class="space-y-1.5 text-slate-500">
                        <li>✓ National IMEI police blacklisting check</li>
                        <li>✓ Dual-SIM 5G/4G Volte signal testing</li>
                        <li>✓ Wi-Fi 6 & Bluetooth pairing</li>
                        <li>✓ Microphones & stereo speakers</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Section 3: Visit Our Showroom CTA -->
        <div class="rounded-3xl bg-gradient-to-r from-brand-800 to-purple-900 text-white p-8 sm:p-12 text-center space-y-6">
            <h3 class="font-display font-black text-2xl sm:text-3xl">Experience the Difference in Person</h3>
            <p class="text-slate-200 text-sm max-w-xl mx-auto">Walk into our Bandra West showroom to test-drive any smartphone, get a free 10-minute diagnostic report on your current device, or pick up curated protective accessories.</p>
            <div class="flex flex-wrap items-center justify-center gap-4 pt-2">
                <a href="{{ route('public.store') }}" class="px-6 py-3.5 bg-white text-brand-900 font-bold text-xs rounded-xl shadow-lg hover:bg-slate-100 transition-colors">
                    Explore Store Stock
                </a>
                <a href="{{ route('public.contact') }}" class="px-6 py-3.5 bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs rounded-xl border border-brand-400/40 transition-colors">
                    Store Location & Timings
                </a>
            </div>
        </div>

    </div>

@endsection
