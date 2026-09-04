<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MobiTrack — Premium Smartphones, Certified Pre-Owned & Express Repairs')</title>
    <meta name="description" content="@yield('meta_description', 'Official retailer of brand new smartphones, 50-point certified pre-owned devices, original accessories, and professional smartphone repair lab in Mumbai.')">

    <!-- Google Fonts (Inter & Outfit) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Outfit:wght@500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Tailwind CSS (CDN for zero-build shared hosting compatibility) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#F0FDFA',
                            100: '#CCFBF1',
                            200: '#99F6E4',
                            300: '#5EEAD4',
                            400: '#2DD4BF',
                            500: '#14B8A6',
                            600: '#0F766E',
                            700: '#115E59',
                            800: '#134E4A',
                            900: '#042F2E',
                            950: '#021E1D',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .font-display { font-family: 'Outfit', sans-serif; }
        .glass-header {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
        .teal-accent {
            background: #0F766E;
        }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-800 antialiased selection:bg-brand-600 selection:text-white flex flex-col min-h-screen">

    <!-- Top Announcement Bar (Neutral Slate) -->
    <div class="bg-slate-900 text-slate-300 text-xs py-2 px-4 text-center font-medium flex items-center justify-center gap-2 border-b border-slate-800">
        <span class="inline-flex items-center gap-1 bg-brand-500/20 text-brand-300 px-2 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider">Store Offer</span>
        <span>Free 9D Tempered Glass & Protective Case on all smartphone purchases this week</span>
        <a href="{{ route('public.store') }}" class="underline font-semibold hover:text-white transition-colors ml-1 hidden sm:inline">Shop Deals →</a>
    </div>

    <!-- Main Navigation Header -->
    <header class="glass-header border-b border-slate-200 sticky top-0 z-40 shadow-xs transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <!-- Brand Logo -->
            <a href="{{ route('public.landing') }}" class="flex items-center gap-3 group text-decoration-none">
                <div class="w-9 h-9 rounded-lg bg-brand-600 flex items-center justify-center text-white font-bold text-lg transition-transform">
                    <i data-lucide="smartphone" class="w-5 h-5"></i>
                </div>
                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="font-display font-bold text-xl text-slate-900 tracking-tight">MobiTrack</span>
                    </div>
                    <span class="text-[10px] text-slate-500 font-semibold tracking-wide uppercase">Retail Store & Repair Lab</span>
                </div>
            </a>

            <!-- Desktop Navigation Links -->
            <nav class="hidden md:flex items-center gap-1 font-semibold text-sm text-slate-600">
                <a href="{{ route('public.landing') }}" class="px-3 py-1.5 rounded-lg transition-colors {{ request()->routeIs('public.landing') ? 'text-brand-700 font-bold bg-brand-50' : 'hover:text-brand-600 hover:bg-slate-100/70' }}">
                    Home
                </a>
                <a href="{{ route('public.store') }}" class="px-3 py-1.5 rounded-lg transition-colors {{ request()->routeIs('public.store') ? 'text-brand-700 font-bold bg-brand-50' : 'hover:text-brand-600 hover:bg-slate-100/70' }}">
                    Explore Shop
                </a>
                <a href="{{ route('public.track_repair') }}" class="px-3 py-1.5 rounded-lg transition-colors {{ request()->routeIs('public.track_repair') ? 'text-brand-700 font-bold bg-brand-50' : 'hover:text-brand-600 hover:bg-slate-100/70' }}">
                    <span class="flex items-center gap-1.5">
                        <i data-lucide="wrench" class="w-4 h-4 text-slate-500"></i>
                        <span>Track Repair</span>
                    </span>
                </a>
                <a href="{{ route('public.about') }}" class="px-3 py-1.5 rounded-lg transition-colors {{ request()->routeIs('public.about') ? 'text-brand-700 font-bold bg-brand-50' : 'hover:text-brand-600 hover:bg-slate-100/70' }}">
                    About Us
                </a>
                <a href="{{ route('public.contact') }}" class="px-3 py-1.5 rounded-lg transition-colors {{ request()->routeIs('public.contact') ? 'text-brand-700 font-bold bg-brand-50' : 'hover:text-brand-600 hover:bg-slate-100/70' }}">
                    Contact & Visit
                </a>
            </nav>

            <!-- Action Buttons -->
            <div class="flex items-center gap-3">
                <a href="tel:9876543210" class="hidden lg:flex items-center gap-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-lg transition-colors">
                    <i data-lucide="phone-call" class="w-3.5 h-3.5 text-emerald-600"></i>
                    <span>+91 98765 43210</span>
                </a>

                <!-- Staff Login CTA Button -->
                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs transition-colors">
                    <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                    <span>Staff Login</span>
                </a>

                <!-- Mobile Menu Button -->
                <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Navigation Drawer -->
        <div id="mobile-menu" class="hidden md:hidden border-t border-slate-200 bg-white px-4 pt-3 pb-6 space-y-2 shadow-xl">
            <a href="{{ route('public.landing') }}" class="block px-3 py-2.5 rounded-xl text-sm font-bold {{ request()->routeIs('public.landing') ? 'bg-brand-50 text-brand-700' : 'text-slate-700 hover:bg-slate-50' }}">
                Home
            </a>
            <a href="{{ route('public.store') }}" class="block px-3 py-2.5 rounded-xl text-sm font-bold {{ request()->routeIs('public.store') ? 'bg-brand-50 text-brand-700' : 'text-slate-700 hover:bg-slate-50' }}">
                Explore Shop
            </a>
            <a href="{{ route('public.track_repair') }}" class="block px-3 py-2.5 rounded-xl text-sm font-bold {{ request()->routeIs('public.track_repair') ? 'bg-brand-50 text-brand-700' : 'text-slate-700 hover:bg-slate-50' }}">
                Track Repair
            </a>
            <a href="{{ route('public.about') }}" class="block px-3 py-2.5 rounded-xl text-sm font-bold {{ request()->routeIs('public.about') ? 'bg-brand-50 text-brand-700' : 'text-slate-700 hover:bg-slate-50' }}">
                About Us
            </a>
            <a href="{{ route('public.contact') }}" class="block px-3 py-2.5 rounded-xl text-sm font-bold {{ request()->routeIs('public.contact') ? 'bg-brand-50 text-brand-700' : 'text-slate-700 hover:bg-slate-50' }}">
                Contact & Visit
            </a>
            <div class="pt-2 border-t border-slate-100 flex flex-col gap-2">
                <a href="tel:9876543210" class="flex items-center justify-center gap-2 py-2.5 rounded-xl bg-slate-100 text-xs font-bold text-slate-800">
                    <i data-lucide="phone-call" class="w-4 h-4 text-emerald-600"></i>
                    <span>Call Store Desk (+91 98765 43210)</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Page Body -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Comprehensive Public Footer -->
    <footer class="bg-slate-950 text-slate-300 border-t border-slate-800 pt-16 pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10 pb-12 border-b border-slate-800">
                <!-- Column 1: Store Bio -->
                <div class="lg:col-span-2 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-brand-600 flex items-center justify-center text-white font-bold text-lg shadow-md">
                            <i data-lucide="smartphone" class="w-5 h-5"></i>
                        </div>
                        <span class="font-display font-bold text-2xl text-white tracking-tight">MobiTrack</span>
                    </div>
                    <p class="text-sm text-slate-400 leading-relaxed max-w-sm">
                        Mumbai's trusted smartphone destination. Official sealed brand new mobiles, 50-point certified pre-owned devices with warranty, premium accessories, and fast chip-level repairs.
                    </p>
                    <div class="flex items-center gap-3 pt-2">
                        <div class="flex items-center gap-1 text-amber-400 text-sm">
                            <i data-lucide="star" class="w-4 h-4 fill-amber-400"></i>
                            <i data-lucide="star" class="w-4 h-4 fill-amber-400"></i>
                            <i data-lucide="star" class="w-4 h-4 fill-amber-400"></i>
                            <i data-lucide="star" class="w-4 h-4 fill-amber-400"></i>
                            <i data-lucide="star" class="w-4 h-4 fill-amber-400"></i>
                        </div>
                        <span class="text-xs font-bold text-white">4.9 / 5.0 (1,850+ Google Reviews)</span>
                    </div>
                </div>

                <!-- Column 2: Quick Links -->
                <div class="space-y-3">
                    <h4 class="text-sm font-bold text-white uppercase tracking-wider font-display">Explore Store</h4>
                    <ul class="space-y-2 text-sm text-slate-400">
                        <li><a href="{{ route('public.store') }}?tab=new" class="hover:text-white transition-colors">Brand New Mobiles</a></li>
                        <li><a href="{{ route('public.store') }}?tab=second_hand" class="hover:text-white transition-colors">Certified Pre-Owned</a></li>
                        <li><a href="{{ route('public.store') }}?tab=covers" class="hover:text-white transition-colors">Back Covers & Glass</a></li>
                        <li><a href="{{ route('public.store') }}?tab=accessories" class="hover:text-white transition-colors">Chargers & Parts</a></li>
                        <li><a href="{{ route('public.track_repair') }}" class="hover:text-white transition-colors">Live Repair Tracker</a></li>
                    </ul>
                </div>

                <!-- Column 3: Services & Warranty -->
                <div class="space-y-3">
                    <h4 class="text-sm font-bold text-white uppercase tracking-wider font-display">Services & Trust</h4>
                    <ul class="space-y-2 text-sm text-slate-400">
                        <li><span class="text-emerald-400 font-medium">✓ 100% Genuine Seal</span></li>
                        <li><span class="text-emerald-400 font-medium">✓ 50-Point Inspection</span></li>
                        <li><span class="text-emerald-400 font-medium">✓ Express Screen & Battery</span></li>
                        <li><span class="text-emerald-400 font-medium">✓ Instant Buyback Cash</span></li>
                        <li><span class="text-emerald-400 font-medium">✓ 0% EMI Financing</span></li>
                    </ul>
                </div>

                <!-- Column 4: Visit Store -->
                <div class="space-y-3">
                    <h4 class="text-sm font-bold text-white uppercase tracking-wider font-display">Store Location</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Shop #14, Linking Road, Near Bandra Station West, Mumbai, Maharashtra 400050
                    </p>
                    <div class="text-xs text-slate-400 space-y-1 pt-1">
                        <p><strong class="text-slate-300">Timings:</strong> 10:00 AM – 9:30 PM</p>
                        <p><strong class="text-slate-300">Open:</strong> All 7 Days Open</p>
                        <p><strong class="text-slate-300">Phone:</strong> +91 98765 43210</p>
                    </div>
                    <div class="pt-2">
                        <a href="https://wa.me/919876543210" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-colors">
                            <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                            <span>Chat on WhatsApp</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Bottom Bar -->
            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-4">
                <p>© {{ date('Y') }} MobiTrack Telecom & Electronics. All rights reserved.</p>
                <div class="flex items-center gap-6">
                    <a href="{{ route('public.about') }}" class="hover:text-slate-400">About</a>
                    <a href="{{ route('public.contact') }}" class="hover:text-slate-400">Contact</a>
                    <a href="{{ route('public.track_repair') }}" class="hover:text-slate-400">Track Repair</a>
                    <a href="{{ route('login') }}" class="hover:text-slate-400 font-bold text-slate-400">Staff Portal</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Mobile Drawer Toggle Script -->
    <script>
        lucide.createIcons();
        const mobileBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        if (mobileBtn && mobileMenu) {
            mobileBtn.addEventListener('click', () => {
                mobileMenu.classList.toggle('hidden');
            });
        }
    </script>
    @stack('scripts')
</body>
</html>
