@extends('mobileshop.public.layout')

@section('title', 'About Us — Maurya Mobile Marketplace & Service Lab')
@section('meta_description', 'Learn about Maurya Mobile, Mumbai’s trusted mobile retail showroom and certified smartphone repair center on Linking Road, Bandra West.')

@section('content')

    <!-- ════ 1. EDITORIAL HERO BANNER ════ -->
    <div class="border-b border-hairline-soft bg-canvas py-16 px-4 sm:px-6 lg:px-8 text-center">
        <div class="max-w-3xl mx-auto space-y-4">
            <span class="inline-block px-3 py-1 bg-surface-soft border border-hairline rounded-full text-[11px] font-bold uppercase tracking-wider text-ink">
                Our Commitment & Standard
            </span>
            <h1 class="text-[28px] sm:text-[36px] font-bold text-ink tracking-tight leading-tight">
                Trust, Quality & Transparency in Every Smartphone.
            </h1>
            <p class="text-[16px] text-muted max-w-2xl mx-auto leading-relaxed">
                Founded to eliminate ambiguity in consumer mobile purchasing and pre-owned trading through absolute diagnostic verification, genuine parts, and certified warranties.
            </p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 space-y-20">

        <!-- ════ 2. WHO WE ARE & MISSION ════ -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2 text-ink font-semibold text-[13px] uppercase tracking-wider">
                    <span class="w-2 h-2 rounded-full bg-rausch"></span>
                    <span>Retail Excellence Since 2018</span>
                </div>
                <h2 class="text-[26px] sm:text-[32px] font-bold text-ink leading-tight">
                    Transforming how Mumbai discovers, trades and repairs personal technology.
                </h2>
                <p class="text-[15px] text-body leading-relaxed">
                    Maurya Mobile is an authorized retailer and multi-brand smartphone hub located on Linking Road, Bandra West. We bridge the gap between expensive brand-new devices and risky street markets by offering <strong>100% genuine sealed smartphones</strong> alongside <strong>laboratory-tested certified pre-owned devices</strong>.
                </p>
                <p class="text-[15px] text-body leading-relaxed">
                    Our in-house micro-soldering laboratory is staffed by certified technicians capable of diagnosing and reviving complex board-level faults in under 45 minutes, saving thousands of customer devices from being discarded prematurely.
                </p>

                <!-- 3 Key Stat Columns -->
                <div class="grid grid-cols-3 gap-6 pt-6 border-t border-hairline">
                    <div>
                        <div class="text-[28px] font-bold text-ink">15,000+</div>
                        <div class="text-[13px] text-muted mt-0.5 font-medium">Happy Customers</div>
                    </div>
                    <div>
                        <div class="text-[28px] font-bold text-ink">99.2%</div>
                        <div class="text-[13px] text-muted mt-0.5 font-medium">Repair Success</div>
                    </div>
                    <div>
                        <div class="text-[28px] font-bold text-ink">4.92 ★</div>
                        <div class="text-[13px] text-muted mt-0.5 font-medium">Google Rating</div>
                    </div>
                </div>
            </div>

            <!-- Right: Clean Host-Card Style Feature Box -->
            <div class="lg:col-span-5 bg-surface-soft rounded-[14px] p-8 border border-hairline shadow-airbnb-tier space-y-6">
                <div>
                    <span class="text-[11px] font-bold text-rausch uppercase tracking-wider">The Maurya Mobile Standard</span>
                    <h3 class="text-[20px] font-bold text-ink mt-1">Why Customers Choose Us Every Single Day</h3>
                </div>

                <div class="space-y-4 text-[14px] text-body">
                    <div class="flex items-start gap-3 p-4 bg-canvas rounded-[14px] border border-hairline-soft">
                        <div class="w-8 h-8 rounded-full bg-surface-strong text-ink flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4 stroke-current fill-none stroke-[2]" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        </div>
                        <div>
                            <strong class="text-ink block font-semibold">Zero Grey-Market Stock</strong>
                            <span class="text-[13px] text-muted">Every sealed phone comes with valid tax invoices and active manufacturer warranty.</span>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-4 bg-canvas rounded-[14px] border border-hairline-soft">
                        <div class="w-8 h-8 rounded-full bg-surface-strong text-ink flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4 stroke-current fill-none stroke-[2]" viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="2" ry="2"></rect><rect x="9" y="9" width="6" height="6"></rect><line x1="9" y1="1" x2="9" y2="4"></line><line x1="15" y1="1" x2="15" y2="4"></line><line x1="9" y1="20" x2="9" y2="23"></line><line x1="15" y1="20" x2="15" y2="23"></line><line x1="20" y1="9" x2="23" y2="9"></line><line x1="20" y1="14" x2="23" y2="14"></line><line x1="1" y1="9" x2="4" y2="9"></line><line x1="1" y1="14" x2="4" y2="14"></line></svg>
                        </div>
                        <div>
                            <strong class="text-ink block font-semibold">Level 4 Certified Lab</strong>
                            <span class="text-[13px] text-muted">Chip-level micro-soldering, authentic replacement screens, and water damage recovery.</span>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-4 bg-canvas rounded-[14px] border border-hairline-soft">
                        <div class="w-8 h-8 rounded-full bg-surface-strong text-ink flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4 stroke-current fill-none stroke-[2]" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                        </div>
                        <div>
                            <strong class="text-ink block font-semibold">Fair Device Buyback</strong>
                            <span class="text-[13px] text-muted">Algorithmic device valuation with instant cash payment or exchange store credits.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ════ 3. THE 50-POINT DIAGNOSTIC CHECKLIST ════ -->
        <div class="bg-canvas rounded-[14px] p-8 sm:p-12 border border-hairline shadow-airbnb-tier space-y-10">
            <div class="text-center max-w-2xl mx-auto space-y-2">
                <span class="inline-block px-3 py-1 bg-surface-soft border border-hairline rounded-full text-[11px] font-bold uppercase tracking-wider text-ink">
                    Quality Assurance
                </span>
                <h2 class="text-[26px] font-bold text-ink">Our 50-Point Diagnostic Checklist</h2>
                <p class="text-[15px] text-muted">Every certified pre-owned smartphone sold at Maurya Mobile must pass all 50 benchmarks before hitting our display shelves.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 text-[13px] text-body">
                <!-- Group 1 -->
                <div class="p-6 rounded-[14px] bg-surface-soft border border-hairline-soft space-y-3">
                    <div class="w-8 h-8 rounded-full bg-canvas text-ink font-bold flex items-center justify-center border border-hairline shadow-sm">1</div>
                    <h4 class="font-semibold text-[15px] text-ink">Display & Touch</h4>
                    <ul class="space-y-2 text-muted">
                        <li>· 100% Touch responsiveness grid</li>
                        <li>· TrueTone & color balance</li>
                        <li>· Dead pixel & backlight bleed test</li>
                        <li>· Original glass scratch grading</li>
                    </ul>
                </div>

                <!-- Group 2 -->
                <div class="p-6 rounded-[14px] bg-surface-soft border border-hairline-soft space-y-3">
                    <div class="w-8 h-8 rounded-full bg-canvas text-ink font-bold flex items-center justify-center border border-hairline shadow-sm">2</div>
                    <h4 class="font-semibold text-[15px] text-ink">Battery & Thermal</h4>
                    <ul class="space-y-2 text-muted">
                        <li>· Genuine OEM battery verification</li>
                        <li>· Minimum 80%+ health certified</li>
                        <li>· Thermal regulation during charging</li>
                        <li>· Fast charge protocol testing</li>
                    </ul>
                </div>

                <!-- Group 3 -->
                <div class="p-6 rounded-[14px] bg-surface-soft border border-hairline-soft space-y-3">
                    <div class="w-8 h-8 rounded-full bg-canvas text-ink font-bold flex items-center justify-center border border-hairline shadow-sm">3</div>
                    <h4 class="font-semibold text-[15px] text-ink">Cameras & Sensors</h4>
                    <ul class="space-y-2 text-muted">
                        <li>· OIS & auto-focus alignment</li>
                        <li>· 4K video recording stability</li>
                        <li>· FaceID / TouchID biometric scan</li>
                        <li>· Gyroscope, proximity & light sensors</li>
                    </ul>
                </div>

                <!-- Group 4 -->
                <div class="p-6 rounded-[14px] bg-surface-soft border border-hairline-soft space-y-3">
                    <div class="w-8 h-8 rounded-full bg-canvas text-ink font-bold flex items-center justify-center border border-hairline shadow-sm">4</div>
                    <h4 class="font-semibold text-[15px] text-ink">Legality & Network</h4>
                    <ul class="space-y-2 text-muted">
                        <li>· National IMEI police clearance check</li>
                        <li>· Dual-SIM 5G/4G Volte signal testing</li>
                        <li>· Wi-Fi 6 & Bluetooth pairing</li>
                        <li>· Microphones & stereo speakers</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- ════ 4. VISIT SHOWROOM CTA ════ -->
        <div class="rounded-[20px] bg-surface-soft border border-hairline p-8 sm:p-12 text-center space-y-5">
            <h3 class="text-[24px] font-bold text-ink">Experience the Difference in Person</h3>
            <p class="text-[15px] text-muted max-w-xl mx-auto leading-relaxed">
                Walk into our Bandra West showroom to test-drive any smartphone, get a free 10-minute diagnostic report on your current device, or pick up curated protective accessories.
            </p>
            <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
                <a href="{{ route('public.store') }}" 
                   class="px-6 py-3.5 bg-rausch hover:bg-rausch-active text-white font-medium text-[14px] rounded-sm transition-airbnb shadow-sm">
                    Explore Store Stock
                </a>
                <a href="{{ route('public.contact') }}" 
                   class="px-6 py-3.5 bg-canvas border border-ink hover:bg-surface-soft text-ink font-medium text-[14px] rounded-sm transition-airbnb">
                    Showroom Directions & Hours
                </a>
            </div>
        </div>

    </div>

@endsection
