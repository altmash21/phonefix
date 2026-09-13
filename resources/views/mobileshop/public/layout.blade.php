<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>@yield('title', 'Maurya Mobile — Official Smartphones, Certified Pre-Owned & Express Repairs')</title>
    <meta name="description" content="@yield('meta_description', 'Discover official sealed flagship smartphones, 50-point certified pre-owned devices, and professional 45-minute express repair laboratory in Mumbai.')">
    <base href="{{ config('app.url') . '/' }}">

    <!-- Preconnect Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- Inter as the pristine SF Pro open substitute for non-Apple devices -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

    <!-- Local Compiled Stylesheet -->
    <link rel="stylesheet" href="{{ asset('public/css/mobileshop-panel.css') }}">

    <!-- Lucide Icons (Local bundle with unpkg fallback) -->
    <script src="{{ asset('public/vendor/lucide/lucide.min.js') }}"></script>
    <script>
        if (typeof lucide === 'undefined') {
            document.write('<script src="https://unpkg.com/lucide@latest"><\/script>');
        }
    </script>

    <!-- Tailwind CSS with Apple Design Tokens -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: [
                            'SF Pro Text',
                            'SF Pro Display',
                            '-apple-system',
                            'BlinkMacSystemFont',
                            'Inter',
                            'system-ui',
                            'sans-serif'
                        ],
                        display: [
                            'SF Pro Display',
                            '-apple-system',
                            'BlinkMacSystemFont',
                            'Inter',
                            'system-ui',
                            'sans-serif'
                        ]
                    },
                    colors: {
                        apple: {
                            primary: '#0066cc',
                            'primary-focus': '#0071e3',
                            'primary-dark': '#2997ff',
                            ink: '#1d1d1f',
                            body: '#1d1d1f',
                            'body-dark': '#ffffff',
                            'body-muted': '#cccccc',
                            'muted-80': '#333333',
                            'muted-48': '#7a7a7a',
                            'divider-soft': '#f0f0f0',
                            hairline: '#e0e0e0',
                            canvas: '#ffffff',
                            parchment: '#f5f5f7',
                            pearl: '#fafafc',
                            'tile-1': '#272729',
                            'tile-2': '#2a2a2c',
                            'tile-3': '#252527',
                            black: '#000000',
                            'chip-translucent': 'rgba(210, 210, 215, 0.64)'
                        }
                    },
                    borderRadius: {
                        'apple-xs': '5px',
                        'apple-sm': '8px',
                        'apple-md': '11px',
                        'apple-lg': '18px',
                        'apple-pill': '9999px'
                    }
                }
            }
        }
    </script>

    <style>
        :root {
            --color-primary: #0066cc;
            --color-primary-focus: #0071e3;
            --color-primary-on-dark: #2997ff;
            --color-ink: #1d1d1f;
            --color-canvas: #ffffff;
            --color-canvas-parchment: #f5f5f7;
            --color-surface-pearl: #fafafc;
            --color-surface-tile-1: #272729;
            --color-surface-tile-2: #2a2a2c;
            --color-surface-tile-3: #252527;
            --color-surface-black: #000000;
            --color-hairline: #e0e0e0;
            --color-divider-soft: #f0f0f0;
        }

        body {
            font-family: 'SF Pro Text', -apple-system, BlinkMacSystemFont, 'Inter', system-ui, sans-serif;
            color: #1d1d1f;
            background-color: #ffffff;
            font-size: 17px;
            line-height: 1.47;
            letter-spacing: -0.374px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* ── Typography Ladder ── */
        .apple-hero-display {
            font-family: 'SF Pro Display', -apple-system, BlinkMacSystemFont, 'Inter', system-ui, sans-serif;
            font-size: clamp(34px, 5.5vw, 56px);
            font-weight: 600;
            line-height: 1.07;
            letter-spacing: -0.28px;
        }

        .apple-display-lg {
            font-family: 'SF Pro Display', -apple-system, BlinkMacSystemFont, 'Inter', system-ui, sans-serif;
            font-size: clamp(28px, 4vw, 40px);
            font-weight: 600;
            line-height: 1.10;
            letter-spacing: 0px;
        }

        .apple-display-md {
            font-family: 'SF Pro Text', -apple-system, BlinkMacSystemFont, 'Inter', system-ui, sans-serif;
            font-size: clamp(24px, 3.2vw, 34px);
            font-weight: 600;
            line-height: 1.47;
            letter-spacing: -0.374px;
        }

        .apple-lead {
            font-family: 'SF Pro Display', -apple-system, BlinkMacSystemFont, 'Inter', system-ui, sans-serif;
            font-size: clamp(20px, 2.8vw, 28px);
            font-weight: 400;
            line-height: 1.14;
            letter-spacing: 0.196px;
        }

        .apple-lead-airy {
            font-family: 'SF Pro Text', -apple-system, BlinkMacSystemFont, 'Inter', system-ui, sans-serif;
            font-size: clamp(18px, 2.4vw, 24px);
            font-weight: 300;
            line-height: 1.5;
            letter-spacing: 0px;
        }

        .apple-tagline {
            font-family: 'SF Pro Display', -apple-system, BlinkMacSystemFont, 'Inter', system-ui, sans-serif;
            font-size: 21px;
            font-weight: 600;
            line-height: 1.19;
            letter-spacing: 0.231px;
        }

        .apple-body-strong {
            font-size: 17px;
            font-weight: 600;
            line-height: 1.24;
            letter-spacing: -0.374px;
        }

        .apple-body {
            font-size: 17px;
            font-weight: 400;
            line-height: 1.47;
            letter-spacing: -0.374px;
        }

        .apple-dense-link {
            font-size: 17px;
            font-weight: 400;
            line-height: 2.41;
            letter-spacing: 0px;
        }

        .apple-caption {
            font-size: 14px;
            font-weight: 400;
            line-height: 1.43;
            letter-spacing: -0.224px;
        }

        .apple-caption-strong {
            font-size: 14px;
            font-weight: 600;
            line-height: 1.29;
            letter-spacing: -0.224px;
        }

        .apple-fine-print {
            font-size: 12px;
            font-weight: 400;
            line-height: 1.0;
            letter-spacing: -0.12px;
        }

        /* ── Elevation: The EXACT ONE Apple Product Shadow ── */
        .apple-product-shadow {
            box-shadow: rgba(0, 0, 0, 0.22) 3px 5px 30px 0;
        }

        /* ── Interactive Button Grammars ── */
        .apple-btn-primary {
            background-color: #0066cc;
            color: #ffffff;
            font-size: 17px;
            font-weight: 400;
            border-radius: 9999px;
            padding: 11px 22px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
            transition: opacity 0.15s ease, transform 0.15s ease;
            white-space: nowrap;
        }
        .apple-btn-primary:hover {
            opacity: 0.94;
        }
        .apple-btn-primary:active {
            transform: scale(0.95);
        }
        .apple-btn-primary:focus-visible {
            outline: 2px solid #0071e3;
            outline-offset: 2px;
        }

        .apple-btn-secondary-pill {
            background-color: transparent;
            color: #0066cc;
            font-size: 17px;
            font-weight: 400;
            border: 1px solid #0066cc;
            border-radius: 9999px;
            padding: 11px 22px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
            transition: background-color 0.15s ease, color 0.15s ease, transform 0.15s ease;
            white-space: nowrap;
        }
        .apple-btn-secondary-pill:hover {
            background-color: #0066cc;
            color: #ffffff;
        }
        .apple-btn-secondary-pill:active {
            transform: scale(0.95);
        }

        .apple-btn-dark-utility {
            background-color: #1d1d1f;
            color: #ffffff;
            font-size: 14px;
            font-weight: 400;
            letter-spacing: -0.224px;
            border-radius: 8px;
            padding: 8px 15px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: background-color 0.15s ease, transform 0.15s ease;
        }
        .apple-btn-dark-utility:hover {
            background-color: #333333;
        }
        .apple-btn-dark-utility:active {
            transform: scale(0.95);
        }

        .apple-text-link {
            color: #0066cc;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: color 0.15s ease;
        }
        .apple-text-link:hover {
            text-decoration: underline;
        }

        .apple-text-link-on-dark {
            color: #2997ff;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: color 0.15s ease;
        }
        .apple-text-link-on-dark:hover {
            text-decoration: underline;
        }

        /* ── Frosted Blur Filter ── */
        .apple-frosted {
            background-color: rgba(245, 245, 247, 0.8);
            backdrop-filter: saturate(180%) blur(20px);
            -webkit-backdrop-filter: saturate(180%) blur(20px);
        }

        .apple-frosted-dark {
            background-color: rgba(39, 39, 41, 0.8);
            backdrop-filter: saturate(180%) blur(20px);
            -webkit-backdrop-filter: saturate(180%) blur(20px);
        }

        /* ── Store Utility Card (rounded.lg 18px, 1px hairline, zero shadow) ── */
        .apple-utility-card {
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 18px;
            padding: 24px;
            transition: border-color 0.2s ease;
        }
        .apple-utility-card:hover {
            border-color: #bbbbbc;
        }

        /* ── Pill Input ── */
        .apple-search-input {
            background-color: #ffffff;
            color: #1d1d1f;
            border: 1px solid rgba(0, 0, 0, 0.12);
            border-radius: 9999px;
            padding: 12px 20px;
            height: 44px;
            font-size: 17px;
            line-height: 1.47;
            letter-spacing: -0.374px;
            outline: none;
            transition: border-color 0.2s ease;
        }
        .apple-search-input:focus {
            border-color: #0071e3;
        }

        /* Hide scrollbars */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
    @stack('styles')
</head>
<body class="bg-apple-canvas text-apple-ink antialiased flex flex-col min-h-screen">

    <!-- ════ 1. GLOBAL NAVIGATION (44px, True Black #000000) ════ -->
    <nav class="bg-apple-black text-apple-body-dark h-[44px] sticky top-0 z-50 select-none border-b border-white/10">
        <div class="max-w-[1024px] mx-auto px-4 h-full flex items-center justify-between text-[12px] font-normal tracking-[-0.12px]">
            
            <!-- Brand Icon (Apple-inspired tech glyph) -->
            <a href="{{ route('public.landing') }}" class="text-white hover:opacity-75 transition-opacity flex items-center gap-1.5" title="Maurya Mobile — Home">
                <svg viewBox="0 0 24 24" class="w-4 h-4 fill-current">
                    <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.37c.62-.75 1.04-1.8 0.92-2.85-.9.04-1.99.6-2.64 1.35-.57.65-1.07 1.71-0.93 2.73 1 .08 2.03-.48 2.65-1.23z"/>
                </svg>
                <span class="font-semibold tracking-normal text-[13px]">Maurya Mobile</span>
            </a>

            <!-- Right Utility Icons -->
            <div class="flex items-center gap-4 text-white/80">
                <!-- Search Icon -->
                <a href="{{ route('public.store') }}" title="Search Phones" class="hover:text-white transition-colors">
                    <svg class="w-3.5 h-3.5 stroke-current fill-none stroke-[2.2]" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </a>

                <!-- Track Repair Status Icon -->
                <a href="{{ route('public.track_repair') }}" title="Track Repair Status" class="hover:text-white transition-colors flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 stroke-current fill-none stroke-[2]" viewBox="0 0 24 24">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                </a>

                <!-- Staff Login Utility -->
                <a href="{{ route('login') }}" class="apple-btn-dark-utility text-[11px] py-1 px-2.5">
                    Staff Portal
                </a>

                <!-- Mobile Hamburger Toggle -->
                <button id="apple-mobile-btn" class="sm:hidden text-white/80 hover:text-white p-1" aria-label="Toggle menu">
                    <svg class="w-4 h-4 stroke-current fill-none stroke-[2]" viewBox="0 0 24 24">
                        <line x1="4" y1="7" x2="20" y2="7"></line>
                        <line x1="4" y1="17" x2="20" y2="17"></line>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Drawer Tray -->
        <div id="apple-mobile-menu" class="hidden sm:hidden bg-apple-black/95 backdrop-blur-xl border-t border-white/10 px-6 py-6 space-y-4 text-[16px] font-normal text-white/90">
            <a href="{{ route('public.landing') }}" class="block hover:text-white">Home</a>
            <a href="{{ route('public.store', ['tab' => 'new']) }}" class="block hover:text-white">New Phones</a>
            <a href="{{ route('public.store', ['tab' => 'second_hand']) }}" class="block hover:text-white">Second Hand</a>
            <a href="{{ route('public.store') }}" class="block hover:text-white">Shop</a>
            <a href="{{ route('public.track_repair') }}" class="block hover:text-white">Repair</a>
            <div class="pt-4 border-t border-white/15">
                <a href="{{ route('login') }}" class="apple-btn-dark-utility w-full justify-center py-2">Staff Portal Login</a>
            </div>
        </div>
    </nav>

    <!-- ════ 2. FROSTED GLASS SUB-NAVIGATION (52px, Sticky below 44px Global Nav) ════ -->
    <div class="apple-frosted border-b border-black/[0.08] sticky top-[44px] z-40">
        <div class="max-w-[1024px] mx-auto px-4 h-[52px] flex items-center justify-between">
            <!-- Left: Product / Section Title -->
            <a href="{{ route('public.landing') }}" class="apple-tagline text-apple-ink font-semibold hover:opacity-80 transition-opacity truncate">
                @yield('subnav_title', 'Maurya Mobile')
            </a>

            <!-- Right: The 4 Primary Nav Links (new phones, second hand, shop, repair) -->
            <div class="flex items-center gap-6 sm:gap-8">
                <div class="flex items-center gap-4 sm:gap-7 text-[13px] sm:text-[14px] text-apple-ink/75 font-medium">
                    @hasSection('subnav_links')
                        @yield('subnav_links')
                    @else
                        <a href="{{ route('public.store', ['tab' => 'new']) }}" class="hover:text-apple-ink transition-colors {{ request('tab') === 'new' ? 'text-apple-ink font-bold' : '' }}">New Phones</a>
                        <a href="{{ route('public.store', ['tab' => 'second_hand']) }}" class="hover:text-apple-ink transition-colors {{ request('tab') === 'second_hand' ? 'text-apple-ink font-bold' : '' }}">Second Hand</a>
                        <a href="{{ route('public.store') }}" class="hover:text-apple-ink transition-colors {{ request()->routeIs('public.store') && !request('tab') ? 'text-apple-ink font-bold' : '' }}">Shop</a>
                        <a href="{{ route('public.track_repair') }}" class="hover:text-apple-ink transition-colors {{ request()->routeIs('public.track_repair') ? 'text-apple-ink font-bold' : '' }}">Repair</a>
                    @endif
                </div>

                @hasSection('subnav_cta')
                    @yield('subnav_cta')
                @endif
            </div>
        </div>
    </div>

    <!-- ════ 3. MAIN PRODUCT CANVAS (Zero border, Alternating light/dark tiles) ════ -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- ════ 4. APPLE FOOTER (Parchment #f5f5f7, Dense 2.41 leading, Fine-print 12px) ════ -->
    <footer class="bg-apple-parchment text-apple-muted-80 border-t border-apple-hairline mt-auto">
        <div class="max-w-[1024px] mx-auto px-4 py-16">
            
            <!-- Fine-print Disclaimers (12px / 400) -->
            <div class="apple-fine-print text-apple-muted-48 pb-8 border-b border-apple-hairline space-y-2.5">
                <p>1. Certified Pre-Owned devices undergo an intensive 50-point hardware diagnostic test and battery health validation before retail release.</p>
                <p>2. Trade-in values will vary based on condition, year, and configuration of your device. In-person instant evaluation at our {{ store_city() }} showroom.</p>
                <p>3. Express Repair services subject to parts in-stock availability. Free diagnostics offered for all devices.</p>
            </div>

            <!-- 5 Dense Link Columns -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-8 py-10 border-b border-apple-hairline">
                <!-- Column 1: Shop & Explore -->
                <div>
                    <h3 class="apple-caption-strong text-apple-ink mb-2">Shop & Explore</h3>
                    <ul class="apple-dense-link text-apple-muted-80">
                        <li><a href="{{ route('public.store') }}" class="hover:text-apple-primary">All Phones</a></li>
                        <li><a href="{{ route('public.store', ['tab' => 'new']) }}" class="hover:text-apple-primary">New Phones</a></li>
                        <li><a href="{{ route('public.store', ['tab' => 'second_hand']) }}" class="hover:text-apple-primary">Second Hand Phones</a></li>
                        <li><a href="{{ route('public.contact') }}" class="hover:text-apple-primary">Sell / Exchange Phone</a></li>
                        <li><a href="{{ route('public.store') }}" class="hover:text-apple-primary">0% EMI Available</a></li>
                    </ul>
                </div>

                <!-- Column 2: Service & Support -->
                <div>
                    <h3 class="apple-caption-strong text-apple-ink mb-2">Repair & Support</h3>
                    <ul class="apple-dense-link text-apple-muted-80">
                        <li><a href="{{ route('public.track_repair') }}" class="hover:text-apple-primary">Track Repair Live</a></li>
                        <li><a href="{{ route('public.track_repair') }}" class="hover:text-apple-primary">Check Repair Status</a></li>
                        <li><a href="{{ route('public.contact') }}" class="hover:text-apple-primary">Screen Replacement</a></li>
                        <li><a href="{{ route('public.contact') }}" class="hover:text-apple-primary">Battery Replacement</a></li>
                        <li><a href="{{ route('public.contact') }}" class="hover:text-apple-primary">Water Damage Repair</a></li>
                    </ul>
                </div>

                <!-- Column 3: Trust & Policies -->
                <div>
                    <h3 class="apple-caption-strong text-apple-ink mb-2">Trust & Legal</h3>
                    <ul class="apple-dense-link text-apple-muted-80">
                        <li><a href="{{ route('public.about') }}" class="hover:text-apple-primary">About Our Store</a></li>
                        <li><a href="{{ route('public.privacy') }}" class="hover:text-apple-primary">Privacy Policy</a></li>
                        <li><a href="{{ route('public.terms') }}" class="hover:text-apple-primary">Terms of Service</a></li>
                        <li><a href="{{ route('public.warranty') }}" class="hover:text-apple-primary">Warranty Information</a></li>
                        <li><a href="{{ route('public.refunds') }}" class="hover:text-apple-primary">Returns & Refunds</a></li>
                        <li><a href="{{ route('public.shipping') }}" class="hover:text-apple-primary">Shipping & Delivery</a></li>
                    </ul>
                </div>

                <!-- Column 4: Store Location -->
                <div>
                    <h3 class="apple-caption-strong text-apple-ink mb-2">Store Location</h3>
                    <ul class="apple-dense-link text-apple-muted-80">
                        <li><a href="{{ route('public.contact') }}" class="hover:text-apple-primary">{{ store_address_short() }}</a></li>
                        <li><a href="{{ route('public.contact') }}" class="hover:text-apple-primary">Store Hours & Map</a></li>
                        <li><a href="{{ route('public.contact') }}" class="hover:text-apple-primary">Contact Support</a></li>
                        <li><a href="tel:{{ preg_replace('/[^0-9]/', '', store_phone()) }}" class="hover:text-apple-primary">{{ store_phone() }}</a></li>
                    </ul>
                </div>

                <!-- Column 5: Staff & Ops -->
                <div>
                    <h3 class="apple-caption-strong text-apple-ink mb-2">Staff Portal</h3>
                    <ul class="apple-dense-link text-apple-muted-80">
                        <li><a href="{{ route('login') }}" class="hover:text-apple-primary font-semibold">Staff Login</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-apple-primary">Sales Register</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-apple-primary">Service Desk</a></li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Legal Band -->
            <div class="pt-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 apple-fine-print text-apple-muted-48">
                <div>
                    Copyright © {{ date('Y') }} {{ store_name() }}. All rights reserved. {{ store_address() }}.
                </div>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
                    <a href="{{ route('public.privacy') }}" class="hover:text-apple-ink">Privacy Policy</a>
                    <span>|</span>
                    <a href="{{ route('public.terms') }}" class="hover:text-apple-ink">Terms of Sale</a>
                    <span>|</span>
                    <a href="{{ route('public.refunds') }}" class="hover:text-apple-ink">Returns & Refunds</a>
                    <span>|</span>
                    <a href="{{ route('public.warranty') }}" class="hover:text-apple-ink">Warranty Policy</a>
                    <span>|</span>
                    <a href="{{ route('public.shipping') }}" class="hover:text-apple-ink">Shipping & Delivery</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Mobile Drawer Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) {
                window.lucide.createIcons();
            }
            const btn = document.getElementById('apple-mobile-btn');
            const menu = document.getElementById('apple-mobile-menu');
            if (btn && menu) {
                btn.addEventListener('click', () => {
                    menu.classList.toggle('hidden');
                });
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
