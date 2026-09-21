@extends('mobileshop.public.layout')

@section('title', 'About Our Cleanroom Lab & Values — ' . store_name())
@section('meta_description', 'Learn about ' . store_name() . '’s commitment to precision chip-level phone repairs, genuine accessories, zero data wipe, and 90-day warranty in ' . store_city() . '.')

@section('content')

    <!-- ════ 1. EDITORIAL HERO ════ -->
    <section class="bg-gradient-radial bg-slate-50 text-slate-900 py-16 sm:py-24 border-b border-slate-200 text-center overflow-hidden">
        <div class="max-w-[1140px] mx-auto px-4 space-y-4">
            <span class="text-xs font-bold uppercase tracking-widest text-blue-700 bg-blue-50 px-3.5 py-1 rounded-full border border-blue-200/80 inline-block shadow-xs">
                Level-4 Micro-Lab & Accessories Center
            </span>
            <h1 class="apple-hero-display text-slate-950 max-w-3xl mx-auto">
                Precision Repairs, Genuine Spares & Absolute Data Privacy.
            </h1>
            <p class="apple-lead text-slate-600 text-base max-w-2xl mx-auto pt-2 font-normal">
                Serving {{ store_city() }} since 2018. We rescue smartphones from liquid damage, broken screens, and dead motherboards while supplying tested high-grade chargers, cases, and components.
            </p>
        </div>
    </section>

    <!-- ════ 2. CLEANROOM BENCH STANDARDS ════ -->
    <section id="diagnostics" class="bg-white text-slate-900 py-16 sm:py-24 border-b border-slate-200">
        <div class="max-w-[1140px] mx-auto px-4">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                
                <div class="lg:col-span-7 space-y-6">
                    <span class="text-xs font-bold uppercase tracking-wider text-blue-600">Forensic Bench Rigour</span>
                    <h2 class="apple-display-lg text-slate-950">
                        Every repair is diagnosed under stereomicroscopes with thermal imaging.
                    </h2>
                    <p class="text-slate-600 text-sm leading-relaxed">
                        Rather than replacing entire motherboards or telling customers that their device is beyond repair, our technicians isolate shorted capacitors, reball Power ICs, and restore broken flex circuits at component level.
                    </p>
                    <p class="text-slate-600 text-sm leading-relaxed">
                        You receive an exact diagnosis, transparent cost estimate before work commences, and a 90-day replacement warranty on every replaced part.
                    </p>

                    <!-- 3 Metric Columns -->
                    <div class="grid grid-cols-3 gap-6 pt-6 border-t border-slate-200 text-left">
                        <div>
                            <div class="apple-display-md text-slate-950 font-bold">18,500+</div>
                            <div class="text-xs text-slate-500 mt-1">Repairs Completed</div>
                        </div>
                        <div>
                            <div class="apple-display-md text-slate-950 font-bold">Fast Swap</div>
                            <div class="text-xs text-slate-500 mt-1">Screen & Battery</div>
                        </div>
                        <div>
                            <div class="apple-display-md text-slate-950 font-bold">90-Day</div>
                            <div class="text-xs text-slate-500 mt-1">Hardware Guarantee</div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-5 bg-slate-50 rounded-2xl border border-slate-200 p-8 space-y-4 shadow-xs">
                    <h3 class="text-base font-bold text-slate-950">Bench Inspection Protocol</h3>
                    <ul class="space-y-3.5 text-xs text-slate-700">
                        <li class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5 stroke-current fill-none stroke-2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            <span><strong class="text-slate-900">Zero Data Wipe Protocol:</strong> Your photos, WhatsApp history, and banking credentials remain intact.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5 stroke-current fill-none stroke-2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            <span><strong class="text-slate-900">TrueTone & Biometric Retention:</strong> EEPROM programmer tools to restore TrueTone and Face ID calibration.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5 stroke-current fill-none stroke-2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            <span><strong class="text-slate-900">ESD-Safe Antistatic Workstation:</strong> Grounded mats and temperature-regulated soldering irons protect delicate logic boards.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5 stroke-current fill-none stroke-2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            <span><strong class="text-slate-900">Original Grade Battery Cells:</strong> High capacity cells that don't trigger warning popups or overheating.</span>
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    </section>

    <!-- ════ 3. REPAIR SAVINGS & LONGEVITY ════ -->
    <section id="ewaste" class="bg-slate-950 text-white py-16 sm:py-24 border-b border-slate-800 text-center overflow-hidden">
        <div class="max-w-[1140px] mx-auto px-4">
            
            <div class="max-w-3xl mx-auto space-y-3">
                <span class="text-xs font-bold uppercase tracking-widest text-blue-400">
                    Component Level Longevity
                </span>
                <h2 class="apple-display-lg text-white">
                    We repair what's broken to save your smartphone and budget.
                </h2>
                <p class="apple-lead text-slate-400 max-w-2xl mx-auto text-base">
                    Instead of pushing you into expensive new device purchases, our micro-soldering team saves you up to 75% by resolving short circuits, loose dock connectors, and broken display flex cables.
                </p>
            </div>

            <!-- 3 Columns -->
            <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6 text-left">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-2">
                    <h3 class="font-bold text-white text-base">18,500+ Saved Phones</h3>
                    <p class="text-slate-400 text-xs leading-relaxed">
                        Devices saved from premature disposal and restored to full day-to-day productivity since our {{ store_city() }} workbench opened.
                    </p>
                </div>
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-2">
                    <h3 class="font-bold text-white text-base">Eco Lithium Recycling</h3>
                    <p class="text-slate-400 text-xs leading-relaxed">
                        Exhausted lithium-ion batteries are safely channelled to certified recycling partners for heavy metal and mineral reclamation.
                    </p>
                </div>
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-2">
                    <h3 class="font-bold text-white text-base">Genuine Spare Parts</h3>
                    <p class="text-slate-400 text-xs leading-relaxed">
                        From 120W GaN fast chargers to INCELL/OLED screens, we only stock tested items that pass strict electrical safety benchmarks.
                    </p>
                </div>
            </div>

        </div>
    </section>

    <!-- ════ 4. STORE & WORKBENCH ACCESS ════ -->
    <section class="bg-slate-50 text-slate-900 py-16 sm:py-20 text-center">
        <div class="max-w-[1140px] mx-auto px-4 space-y-10">
            
            <div class="max-w-xl mx-auto space-y-3">
                <h2 class="apple-display-lg text-slate-950">
                    Visit Our {{ store_city() }} Workbench.
                </h2>
                <p class="text-slate-500 text-sm">
                    Bring your smartphone in for a free counter assessment or pick up verified fast chargers and durable phone cases.
                </p>
                <div class="pt-2 flex items-center justify-center gap-3">
                    <a href="{{ route('public.contact') }}" class="apple-btn-primary text-xs py-2 px-5">
                        View Store Map & Hours
                    </a>
                    <a href="{{ route('public.track_repair') }}" class="apple-btn-secondary-pill text-xs py-2 px-5">
                        Track Active Repair
                    </a>
                </div>
            </div>

            <!-- 4 Value Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-left">
                <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs">
                    <div class="text-[10px] text-blue-600 font-bold uppercase tracking-wider">Genuine Spares</div>
                    <h3 class="font-bold text-slate-900 text-sm mt-1">Official GST Invoice</h3>
                    <p class="text-[11px] text-slate-500 mt-1">Included with every accessory, charger, and display replacement.</p>
                </div>
                <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs">
                    <div class="text-[10px] text-blue-600 font-bold uppercase tracking-wider">Pricing</div>
                    <h3 class="font-bold text-slate-900 text-sm mt-1">Upfront Estimates</h3>
                    <p class="text-[11px] text-slate-500 mt-1">No surprise bills. We always confirm pricing before bench service.</p>
                </div>
                <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs">
                    <div class="text-[10px] text-blue-600 font-bold uppercase tracking-wider">Turnaround</div>
                    <h3 class="font-bold text-slate-900 text-sm mt-1">Express Turnaround</h3>
                    <p class="text-[11px] text-slate-500 mt-1">Screens, batteries, and charging ports done while you wait.</p>
                </div>
                <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs">
                    <div class="text-[10px] text-blue-600 font-bold uppercase tracking-wider">Protection</div>
                    <h3 class="font-bold text-slate-900 text-sm mt-1">Free Installation</h3>
                    <p class="text-[11px] text-slate-500 mt-1">11D tempered glass and back covers installed bubble-free on counter.</p>
                </div>
            </div>

        </div>
    </section>

@endsection

