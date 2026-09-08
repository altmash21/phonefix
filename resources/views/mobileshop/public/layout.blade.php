<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MobiTrack — Smartphones, Certified Pre-Owned & Express Repairs')</title>
    <meta name="description" content="@yield('meta_description', 'MobiTrack is Mumbai\'s premier smartphone marketplace for brand new sealed devices, 50-point certified pre-owned phones, and professional express repairs.')">

    <!-- Google Fonts (Inter - matching Airbnb Cereal VF metrics) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Tailwind CSS with Airbnb Design System Tokens -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'Circular', '-apple-system', 'BlinkMacSystemFont', 'system-ui', 'Roboto', 'sans-serif'],
                    },
                    colors: {
                        rausch: {
                            DEFAULT: '#ff385c',
                            active: '#e00b41',
                            disabled: '#ffd1da',
                            tint: '#fff1f3'
                        },
                        ink: '#222222',
                        body: '#3f3f3f',
                        muted: {
                            DEFAULT: '#6a6a6a',
                            soft: '#929292'
                        },
                        hairline: {
                            DEFAULT: '#dddddd',
                            soft: '#ebebeb'
                        },
                        'border-strong': '#c1c1c1',
                        canvas: '#ffffff',
                        surface: {
                            soft: '#f7f7f7',
                            strong: '#f2f2f2',
                            card: '#ffffff'
                        },
                        luxe: '#460479',
                        plus: '#92174d'
                    },
                    borderRadius: {
                        'sm': '8px',
                        'md': '14px',
                        'lg': '20px',
                        'xl': '32px'
                    },
                    boxShadow: {
                        'airbnb': 'rgba(0, 0, 0, 0.02) 0 0 0 1px, rgba(0, 0, 0, 0.04) 0 2px 6px 0, rgba(0, 0, 0, 0.1) 0 4px 8px 0',
                        'airbnb-hover': 'rgba(0, 0, 0, 0.02) 0 0 0 1px, rgba(0, 0, 0, 0.06) 0 4px 12px 0, rgba(0, 0, 0, 0.12) 0 8px 18px 0',
                        'airbnb-dropdown': 'rgba(0, 0, 0, 0.1) 0 10px 30px 0, rgba(0, 0, 0, 0.05) 0 1px 3px 0'
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Inter', -apple-system, system-ui, Roboto, sans-serif;
            color: #222222;
            background-color: #ffffff;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Airbnb single shadow tier */
        .shadow-airbnb-tier {
            box-shadow: rgba(0, 0, 0, 0.02) 0 0 0 1px, rgba(0, 0, 0, 0.04) 0 2px 6px 0, rgba(0, 0, 0, 0.1) 0 4px 8px 0;
        }
        .shadow-airbnb-hover:hover {
            box-shadow: rgba(0, 0, 0, 0.02) 0 0 0 1px, rgba(0, 0, 0, 0.06) 0 6px 16px 0, rgba(0, 0, 0, 0.12) 0 8px 20px 0;
        }

        /* Clean transitions */
        .transition-airbnb {
            transition: all 0.2s cubic-bezier(0.2, 0, 0, 1);
        }

        /* Hide scrollbars for clean category strips */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
    @stack('styles')
</head>
<body class="bg-canvas text-ink antialiased flex flex-col min-h-screen">

    <!-- Top Notice Band (Clean Minimalist White/Surface-Soft) -->
    <div class="bg-surface-soft border-b border-hairline-soft text-ink text-[13px] py-2 px-4 text-center font-normal">
        <div class="max-w-7xl mx-auto flex items-center justify-center gap-2">
            <span class="inline-block w-1.5 h-1.5 rounded-full bg-rausch"></span>
            <span>Complimentary 9D Tempered Glass & Protective Case on all smartphone purchases this week.</span>
            <a href="{{ route('public.store') }}" class="underline font-medium hover:text-rausch transition-airbnb ml-1">Explore Deals</a>
        </div>
    </div>

    <!-- ════ 80px AIRBNB TOP NAVIGATION ════ -->
    <header class="bg-canvas border-b border-hairline-soft sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            
            <!-- Left: Brand Wordmark with Rausch Voltage -->
            <a href="{{ route('public.landing') }}" class="flex items-center gap-2 text-decoration-none group">
                <div class="w-9 h-9 rounded-full bg-rausch flex items-center justify-center text-white transition-transform group-hover:scale-105">
                    <!-- Custom Airbnb Belo inspired / Phone fusion icon -->
                    <svg viewBox="0 0 24 24" class="w-5 h-5 fill-current" stroke="currentColor" stroke-width="0.5">
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                    </svg>
                </div>
                <div class="flex flex-col">
                    <span class="text-[20px] font-bold text-rausch tracking-tight leading-none">mobitrack</span>
                    <span class="text-[10px] text-muted font-medium tracking-wide">marketplace</span>
                </div>
            </a>

            <!-- Center: Three-Product Top Nav (Homes / Experiences / Services Pattern) -->
            <nav class="hidden md:flex items-center gap-6 lg:gap-8 h-full">
                <!-- Tab 1: Smartphones (Homes equivalent) -->
                <a href="{{ route('public.store', ['tab' => 'new']) }}" 
                   class="relative h-full flex flex-col items-center justify-center gap-1 group text-decoration-none transition-airbnb {{ request()->routeIs('public.landing') || (request()->routeIs('public.store') && request('tab') === 'new') ? 'text-ink font-semibold' : 'text-muted hover:text-ink font-medium' }}">
                    <div class="flex items-center gap-2">
                        <svg class="w-6 h-6 stroke-current fill-none stroke-[1.8]" viewBox="0 0 24 24">
                            <rect x="5" y="2" width="14" height="20" rx="3" ry="3"></rect>
                            <line x1="12" y1="18" x2="12.01" y2="18"></line>
                        </svg>
                        <span class="text-[15px]">Smartphones</span>
                    </div>
                    @if(request()->routeIs('public.landing') || (request()->routeIs('public.store') && request('tab') === 'new'))
                        <div class="absolute bottom-0 inset-x-0 h-[2px] bg-ink"></div>
                    @else
                        <div class="absolute bottom-0 inset-x-0 h-[2px] bg-transparent group-hover:bg-hairline transition-all"></div>
                    @endif
                </a>

                <!-- Tab 2: Certified Pre-Owned (Experiences equivalent + NEW badge) -->
                <a href="{{ route('public.store', ['tab' => 'second_hand']) }}" 
                   class="relative h-full flex flex-col items-center justify-center gap-1 group text-decoration-none transition-airbnb {{ request()->routeIs('public.store') && request('tab') === 'second_hand' ? 'text-ink font-semibold' : 'text-muted hover:text-ink font-medium' }}">
                    <div class="flex items-center gap-2">
                        <div class="relative">
                            <svg class="w-6 h-6 stroke-current fill-none stroke-[1.8]" viewBox="0 0 24 24">
                                <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"></path>
                            </svg>
                            <!-- NEW Badge -->
                            <span class="absolute -top-1.5 -right-3 px-1.5 py-[1px] bg-rausch text-white rounded-full text-[8px] font-bold tracking-[0.32px] uppercase leading-tight">NEW</span>
                        </div>
                        <span class="text-[15px]">Pre-Owned</span>
                    </div>
                    @if(request()->routeIs('public.store') && request('tab') === 'second_hand')
                        <div class="absolute bottom-0 inset-x-0 h-[2px] bg-ink"></div>
                    @else
                        <div class="absolute bottom-0 inset-x-0 h-[2px] bg-transparent group-hover:bg-hairline transition-all"></div>
                    @endif
                </a>

                <!-- Tab 3: Repair Lab & Services (Services equivalent + NEW badge) -->
                <a href="{{ route('public.track_repair') }}" 
                   class="relative h-full flex flex-col items-center justify-center gap-1 group text-decoration-none transition-airbnb {{ request()->routeIs('public.track_repair') ? 'text-ink font-semibold' : 'text-muted hover:text-ink font-medium' }}">
                    <div class="flex items-center gap-2">
                        <div class="relative">
                            <svg class="w-6 h-6 stroke-current fill-none stroke-[1.8]" viewBox="0 0 24 24">
                                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                            </svg>
                            <span class="absolute -top-1.5 -right-3 px-1.5 py-[1px] bg-rausch text-white rounded-full text-[8px] font-bold tracking-[0.32px] uppercase leading-tight">NEW</span>
                        </div>
                        <span class="text-[15px]">Repair Lab</span>
                    </div>
                    @if(request()->routeIs('public.track_repair'))
                        <div class="absolute bottom-0 inset-x-0 h-[2px] bg-ink"></div>
                    @else
                        <div class="absolute bottom-0 inset-x-0 h-[2px] bg-transparent group-hover:bg-hairline transition-all"></div>
                    @endif
                </a>
            </nav>

            <!-- Right: Utilities & User Pill Menu -->
            <div class="flex items-center gap-2">
                <!-- Host Link equivalent -->
                <a href="{{ route('public.contact') }}" class="hidden sm:inline-block px-3.5 py-2.5 rounded-full text-sm font-medium text-ink hover:bg-surface-soft transition-airbnb">
                    Sell Your Phone
                </a>

                <!-- Globe / Region Selector -->
                <a href="{{ route('public.about') }}" title="About & Store Info" class="p-2.5 rounded-full text-ink hover:bg-surface-soft transition-airbnb">
                    <svg class="w-4 h-4 stroke-current fill-none stroke-[1.8]" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="2" y1="12" x2="22" y2="12"></line>
                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                    </svg>
                </a>

                <!-- User / Staff Login Pill (Airbnb Signature Component) -->
                <a href="{{ route('login') }}" 
                   class="flex items-center gap-3 pl-3.5 pr-2 py-1.5 border border-hairline rounded-full hover:shadow-airbnb-tier transition-airbnb bg-canvas">
                    <svg class="w-4 h-4 stroke-current fill-none stroke-[2]" viewBox="0 0 24 24">
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                    <div class="w-7 h-7 rounded-full bg-muted text-white flex items-center justify-center">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                    </div>
                </a>

                <!-- Mobile Menu Button -->
                <button id="mobile-menu-btn" class="md:hidden p-2.5 rounded-full text-ink hover:bg-surface-soft transition-airbnb ml-1">
                    <svg class="w-5 h-5 stroke-current fill-none stroke-[2]" viewBox="0 0 24 24">
                        <line x1="4" y1="6" x2="20" y2="6"></line>
                        <line x1="4" y1="12" x2="20" y2="12"></line>
                        <line x1="4" y1="18" x2="20" y2="18"></line>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Drawer Menu -->
        <div id="mobile-menu" class="hidden md:hidden border-t border-hairline-soft bg-canvas px-4 py-4 space-y-1 shadow-airbnb-dropdown">
            <a href="{{ route('public.landing') }}" class="block px-4 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('public.landing') ? 'bg-surface-soft text-ink font-semibold' : 'text-body hover:bg-surface-soft' }}">
                Home
            </a>
            <a href="{{ route('public.store', ['tab' => 'new']) }}" class="block px-4 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('public.store') && request('tab') !== 'second_hand' ? 'bg-surface-soft text-ink font-semibold' : 'text-body hover:bg-surface-soft' }}">
                Smartphones
            </a>
            <a href="{{ route('public.store', ['tab' => 'second_hand']) }}" class="block px-4 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('public.store') && request('tab') === 'second_hand' ? 'bg-surface-soft text-ink font-semibold' : 'text-body hover:bg-surface-soft' }}">
                Pre-Owned Devices <span class="ml-1.5 px-1.5 py-0.5 bg-rausch text-white rounded-full text-[8px] font-bold">NEW</span>
            </a>
            <a href="{{ route('public.track_repair') }}" class="block px-4 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('public.track_repair') ? 'bg-surface-soft text-ink font-semibold' : 'text-body hover:bg-surface-soft' }}">
                Track Repair <span class="ml-1.5 px-1.5 py-0.5 bg-rausch text-white rounded-full text-[8px] font-bold">NEW</span>
            </a>
            <a href="{{ route('public.about') }}" class="block px-4 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('public.about') ? 'bg-surface-soft text-ink font-semibold' : 'text-body hover:bg-surface-soft' }}">
                About Us
            </a>
            <a href="{{ route('public.contact') }}" class="block px-4 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('public.contact') ? 'bg-surface-soft text-ink font-semibold' : 'text-body hover:bg-surface-soft' }}">
                Contact & Showroom
            </a>
            <div class="pt-3 border-t border-hairline-soft mt-2">
                <a href="{{ route('login') }}" class="block w-full text-center py-2.5 rounded-sm bg-rausch text-white text-sm font-medium">
                    Staff Portal Login
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content Area (Clean White Canvas) -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- ════ AIRBNB FOOTER-LIGHT ════ -->
    <footer class="bg-surface-soft border-t border-hairline-soft text-ink mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            
            <!-- 4 Editorial Link Columns -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 pb-12 border-b border-hairline">
                <!-- Column 1: Support -->
                <div class="space-y-3">
                    <h4 class="text-sm font-semibold text-ink">Support & Desk</h4>
                    <ul class="space-y-2.5 text-sm text-muted">
                        <li><a href="{{ route('public.track_repair') }}" class="hover:text-ink hover:underline transition-airbnb">Live Repair Tracker</a></li>
                        <li><a href="{{ route('public.contact') }}" class="hover:text-ink hover:underline transition-airbnb">Diagnostic Help Desk</a></li>
                        <li><a href="https://wa.me/919876543210" target="_blank" class="hover:text-ink hover:underline transition-airbnb">WhatsApp Support</a></li>
                        <li><a href="tel:9876543210" class="hover:text-ink hover:underline transition-airbnb">+91 98765 43210</a></li>
                    </ul>
                </div>

                <!-- Column 2: Marketplace -->
                <div class="space-y-3">
                    <h4 class="text-sm font-semibold text-ink">Marketplace</h4>
                    <ul class="space-y-2.5 text-sm text-muted">
                        <li><a href="{{ route('public.store', ['tab' => 'new']) }}" class="hover:text-ink hover:underline transition-airbnb">Brand New Smartphones</a></li>
                        <li><a href="{{ route('public.store', ['tab' => 'second_hand']) }}" class="hover:text-ink hover:underline transition-airbnb">Certified Pre-Owned</a></li>
                        <li><a href="{{ route('public.track_repair') }}" class="hover:text-ink hover:underline transition-airbnb">Express Repair Desk</a></li>
                        <li><a href="{{ route('public.contact') }}" class="hover:text-ink hover:underline transition-airbnb">0% EMI Financing</a></li>
                    </ul>
                </div>

                <!-- Column 3: Trust & Lab -->
                <div class="space-y-3">
                    <h4 class="text-sm font-semibold text-ink">Trust & Quality</h4>
                    <ul class="space-y-2.5 text-sm text-muted">
                        <li><a href="{{ route('public.about') }}" class="hover:text-ink hover:underline transition-airbnb">50-Point Inspection</a></li>
                        <li><a href="{{ route('public.about') }}" class="hover:text-ink hover:underline transition-airbnb">100% Genuine Seal</a></li>
                        <li><a href="{{ route('public.about') }}" class="hover:text-ink hover:underline transition-airbnb">30-Day Store Warranty</a></li>
                        <li><a href="{{ route('public.contact') }}" class="hover:text-ink hover:underline transition-airbnb">Instant Buyback</a></li>
                    </ul>
                </div>

                <!-- Column 4: MobiTrack -->
                <div class="space-y-3">
                    <h4 class="text-sm font-semibold text-ink">MobiTrack</h4>
                    <ul class="space-y-2.5 text-sm text-muted">
                        <li><a href="{{ route('public.about') }}" class="hover:text-ink hover:underline transition-airbnb">About Showroom</a></li>
                        <li><a href="{{ route('public.contact') }}" class="hover:text-ink hover:underline transition-airbnb">Visit Linking Road Store</a></li>
                        <li><a href="{{ route('public.contact') }}" class="hover:text-ink hover:underline transition-airbnb">Business Inquiries</a></li>
                        <li><a href="{{ route('login') }}" class="text-ink font-medium hover:underline">Staff Portal Login</a></li>
                    </ul>
                </div>
            </div>

            <!-- Legal Band -->
            <div class="pt-8 flex flex-col md:flex-row items-center justify-between gap-4 text-[13px] text-muted">
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                    <span>© {{ date('Y') }} MobiTrack, Inc.</span>
                    <span>·</span>
                    <a href="{{ route('public.about') }}" class="hover:text-ink hover:underline">Privacy</a>
                    <span>·</span>
                    <a href="{{ route('public.about') }}" class="hover:text-ink hover:underline">Terms</a>
                    <span>·</span>
                    <a href="{{ route('public.store') }}" class="hover:text-ink hover:underline">Sitemap</a>
                    <span>·</span>
                    <a href="{{ route('public.contact') }}" class="hover:text-ink hover:underline">Company details</a>
                </div>

                <div class="flex items-center gap-6">
                    <div class="flex items-center gap-2 font-semibold text-ink">
                        <svg class="w-4 h-4 stroke-current fill-none stroke-[2]" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="2" y1="12" x2="22" y2="12"></line>
                            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                        </svg>
                        <span>English (IN)</span>
                    </div>
                    <div class="font-semibold text-ink">
                        <span>₹ INR</span>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Mobile Drawer Script -->
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
