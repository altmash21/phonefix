@extends('mobileshop.public.layout')

@section('title', 'Explore Live Inventory — MobiTrack Store')
@section('meta_description', 'Browse our current in-stock catalog of sealed brand new smartphones, 50-point certified pre-owned devices, original phone covers, and fast chargers.')

@section('content')

    <!-- Header Banner -->
    <div class="bg-gradient-to-b from-slate-950 to-slate-900 text-white py-12 px-4 text-center">
        <div class="max-w-3xl mx-auto space-y-3">
            <span class="px-3 py-1 rounded-full bg-brand-500/20 text-brand-300 border border-brand-500/30 text-xs font-bold uppercase tracking-wider">
                Live Storefront & Catalog
            </span>
            <h1 class="font-display font-black text-3xl sm:text-4xl text-white">Explore Current Stock & Deals</h1>
            <p class="text-slate-400 text-sm max-w-xl mx-auto">Updated in real-time with our physical showroom inventory in Bandra West, Mumbai.</p>
        </div>
    </div>

    <!-- Store Catalog Container -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">

        <!-- Search & Filter Controls -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
            <!-- Category Tabs -->
            @php $currentTab = request('tab', 'all'); @endphp
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('public.store', ['tab' => 'all', 'q' => request('q')]) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $currentTab === 'all' ? 'bg-brand-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    All Items ({{ ($newPhones->count() + $secondHandPhones->count() + $accessories->count()) }})
                </a>
                <a href="{{ route('public.store', ['tab' => 'new', 'q' => request('q')]) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $currentTab === 'new' ? 'bg-brand-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    📱 Brand New ({{ $newPhones->count() }})
                </a>
                <a href="{{ route('public.store', ['tab' => 'second_hand', 'q' => request('q')]) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $currentTab === 'second_hand' ? 'bg-amber-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    🔄 Certified Pre-Owned ({{ $secondHandPhones->count() }})
                </a>
                <a href="{{ route('public.store', ['tab' => 'covers', 'q' => request('q')]) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $currentTab === 'covers' ? 'bg-purple-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    🖼️ Covers & Tempered
                </a>
                <a href="{{ route('public.store', ['tab' => 'accessories', 'q' => request('q')]) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $currentTab === 'accessories' ? 'bg-sky-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    ⚡ Accessories & Parts
                </a>
            </div>

            <!-- Search Form -->
            <form action="{{ route('public.store') }}" method="GET" class="relative min-w-[260px]">
                <input type="hidden" name="tab" value="{{ $currentTab }}">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search brand or model..."
                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:border-brand-500 focus:bg-white transition-all">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
            </form>
        </div>

        <!-- ════ 1. BRAND NEW PHONES SECTION ════ -->
        @if(in_array($currentTab, ['all', 'new']))
        <section class="space-y-6">
            <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                <div class="flex items-center gap-2.5">
                    <span class="p-2 rounded-xl bg-brand-100 text-brand-700"><i data-lucide="smartphone" class="w-5 h-5"></i></span>
                    <h2 class="font-display font-extrabold text-xl text-slate-900">Brand New Sealed Smartphones</h2>
                </div>
                <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full">
                    {{ $newPhones->count() }} In Stock
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($newPhones as $np)
                    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs hover:shadow-xl hover:-translate-y-1 transition-all flex flex-col justify-between group">
                        <div class="space-y-3">
                            <div class="flex justify-between items-start">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-brand-100 text-brand-800">
                                    100% Sealed
                                </span>
                                <span class="text-xs text-slate-400 font-mono font-bold">{{ $np->brand }}</span>
                            </div>

                            <h3 class="font-display font-extrabold text-lg text-slate-900 group-hover:text-brand-600 transition-colors">
                                {{ $np->brand }} {{ $np->model }}
                            </h3>

                            <div class="flex flex-wrap gap-1.5 text-xs text-slate-500">
                                <span class="px-2.5 py-0.5 rounded-md bg-slate-100 font-semibold">{{ $np->storage ?? '128GB' }}</span>
                                <span class="px-2.5 py-0.5 rounded-md bg-slate-100 font-semibold">{{ $np->ram ?? '8GB' }} RAM</span>
                                <span class="px-2.5 py-0.5 rounded-md bg-slate-100 font-semibold">{{ $np->color ?? 'Standard' }}</span>
                            </div>

                            <div class="pt-2 text-xs space-y-1 text-slate-600">
                                <p class="flex items-center gap-1.5 text-emerald-700 font-medium">
                                    <i data-lucide="check-circle" class="w-3.5 h-3.5 text-emerald-600"></i>
                                    <span>Manufacturer 1-Year Warranty Included</span>
                                </p>
                                <p class="flex items-center gap-1.5 text-slate-500">
                                    <i data-lucide="gift" class="w-3.5 h-3.5 text-brand-600"></i>
                                    <span>Free 9D Tempered Glass + Protective Case</span>
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                            <div>
                                <span class="text-[10px] text-slate-400 uppercase font-bold">Store Price</span>
                                <p class="text-2xl font-black text-slate-900">₹{{ number_format($np->selling_price, 2) }}</p>
                            </div>
                            <a href="https://wa.me/919876543210?text={{ urlencode('Hello, I want to purchase brand new ' . $np->brand . ' ' . $np->model . ' for ₹' . number_format($np->selling_price, 2)) }}" target="_blank" class="px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow-md shadow-brand-600/20 flex items-center gap-1.5 transition-all">
                                <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                <span>Buy via WhatsApp</span>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 py-10 text-center bg-white rounded-3xl border border-slate-200 text-slate-500">
                        <p class="font-bold">No new phone models matched your search.</p>
                    </div>
                @endforelse
            </div>
        </section>
        @endif

        <!-- ════ 2. CERTIFIED PRE-OWNED SECTION ════ -->
        @if(in_array($currentTab, ['all', 'second_hand']))
        <section class="space-y-6">
            <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                <div class="flex items-center gap-2.5">
                    <span class="p-2 rounded-xl bg-amber-100 text-amber-800"><i data-lucide="refresh-cw" class="w-5 h-5"></i></span>
                    <h2 class="font-display font-extrabold text-xl text-slate-900">Certified Pre-Owned & Inspected</h2>
                </div>
                <span class="text-xs font-bold text-amber-800 bg-amber-50 px-3 py-1 rounded-full">
                    {{ $secondHandPhones->count() }} Available
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($secondHandPhones as $sp)
                    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs hover:shadow-xl hover:-translate-y-1 transition-all flex flex-col justify-between group">
                        <div class="space-y-3">
                            <div class="flex justify-between items-start">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-100 text-amber-900">
                                    Grade {{ strtoupper($sp->condition_grade ?? 'A') }}
                                </span>
                                @if($sp->battery_health)
                                    <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md">
                                        🔋 {{ $sp->battery_health }}% Battery
                                    </span>
                                @endif
                            </div>

                            <h3 class="font-display font-extrabold text-lg text-slate-900 group-hover:text-amber-600 transition-colors">
                                {{ $sp->brand }} {{ $sp->model }}
                            </h3>

                            <div class="flex flex-wrap gap-1.5 text-xs text-slate-500">
                                <span class="px-2.5 py-0.5 rounded-md bg-slate-100 font-semibold">{{ $sp->storage ?? '128GB' }}</span>
                                <span class="px-2.5 py-0.5 rounded-md bg-slate-100 font-semibold">{{ $sp->color ?? 'Graphite' }}</span>
                            </div>

                            @if($sp->checklist_notes)
                                <p class="text-xs text-slate-500 bg-slate-50 p-2.5 rounded-xl border border-slate-100 line-clamp-2">
                                    "{{ $sp->checklist_notes }}"
                                </p>
                            @endif

                            <div class="text-xs text-slate-500 space-y-1">
                                <p class="flex items-center gap-1.5 text-emerald-700 font-medium">
                                    <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-600"></i>
                                    <span>30-Day Testing & Store Replacement Warranty</span>
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                            <div>
                                <span class="text-[10px] text-slate-400 uppercase font-bold">Deal Price</span>
                                <p class="text-2xl font-black text-slate-900">₹{{ number_format($sp->selling_price, 2) }}</p>
                            </div>
                            <a href="https://wa.me/919876543210?text={{ urlencode('Hello, I want to reserve certified pre-owned ' . $sp->brand . ' ' . $sp->model . ' for ₹' . number_format($sp->selling_price, 2)) }}" target="_blank" class="px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow-md shadow-amber-600/20 flex items-center gap-1.5 transition-all">
                                <i data-lucide="tag" class="w-3.5 h-3.5"></i>
                                <span>Reserve Device</span>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 py-10 text-center bg-white rounded-3xl border border-slate-200 text-slate-500">
                        <p class="font-bold">No certified pre-owned models matched your search.</p>
                    </div>
                @endforelse
            </div>
        </section>
        @endif

        <!-- ════ 3. ACCESSORIES & COVERS SECTION ════ -->
        @if(in_array($currentTab, ['all', 'accessories', 'covers']) && count($accessories) > 0)
        <section class="space-y-6">
            <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                <div class="flex items-center gap-2.5">
                    <span class="p-2 rounded-xl bg-purple-100 text-purple-700"><i data-lucide="package" class="w-5 h-5"></i></span>
                    <h2 class="font-display font-extrabold text-xl text-slate-900">
                        {{ $currentTab === 'covers' ? 'Back Covers & 9D Tempered Glass' : 'Original Accessories & Parts' }}
                    </h2>
                </div>
                <span class="text-xs font-bold text-purple-700 bg-purple-50 px-3 py-1 rounded-full">
                    {{ count($accessories) }} Items
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($accessories as $acc)
                    <div class="bg-white rounded-3xl border border-slate-200 p-5 shadow-xs hover:shadow-lg transition-all flex flex-col justify-between">
                        <div class="space-y-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 uppercase">
                                {{ ucfirst(str_replace('_', ' ', $acc->category ?? 'General')) }}
                            </span>
                            <h4 class="font-bold text-sm text-slate-900 line-clamp-2">{{ $acc->name }}</h4>
                            <p class="text-xs text-slate-500">In Stock: {{ $acc->stock_qty }} units</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                            <span class="font-black text-lg text-slate-900">₹{{ number_format($acc->selling_price, 2) }}</span>
                            <a href="https://wa.me/919876543210?text={{ urlencode('Hi, do you have ' . $acc->name . ' available in store?') }}" target="_blank" class="px-3 py-1.5 bg-slate-900 hover:bg-brand-600 text-white rounded-lg text-xs font-bold transition-colors">
                                Inquire
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
        @endif

    </div>

@endsection
