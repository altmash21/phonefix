<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>@yield('title', store_name() . ' — Mobile Accessories & Express Repair Lab')</title>
    <meta name="description" content="@yield('meta_description', 'Official mobile accessories, fast chargers, tempered glass, OEM batteries, and certified express phone repair in ' . store_city() . '.')">
    <base href="{{ config('app.url') . '/' }}">

    <!-- Google Fonts: Inter & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    <!-- Cal Sans Font-Face (with Inter geometric fallback) -->
    <style>
        @font-face {
            font-family: 'Cal Sans';
            src: url('https://cdn.jsdelivr.net/npm/cal-sans@1.0.1/fonts/web/calsans-semibold.woff2') format('woff2');
            font-weight: 600;
            font-style: normal;
            font-display: swap;
        }

        :root {
            /* Cal.com Design System Tokens */
            --color-primary: #111111;
            --color-primary-active: #242424;
            --color-primary-disabled: #e5e7eb;
            --color-ink: #111111;
            --color-body: #374151;
            --color-muted: #6b7280;
            --color-muted-soft: #898989;
            --color-hairline: #e5e7eb;
            --color-hairline-soft: #f3f4f6;
            --color-canvas: #ffffff;
            --color-surface-soft: #f8f9fa;
            --color-surface-card: #f5f5f5;
            --color-surface-strong: #e5e7eb;
            --color-surface-dark: #101010;
            --color-surface-dark-elevated: #1a1a1a;
            --color-on-primary: #ffffff;
            --color-on-dark: #ffffff;
            --color-on-dark-soft: #a1a1aa;
            --color-brand-accent: #3b82f6;
            --color-badge-orange: #fb923c;
            --color-badge-pink: #ec4899;
            --color-badge-violet: #8b5cf6;
            --color-badge-emerald: #34d399;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: var(--color-body);
            background-color: var(--color-canvas);
            font-size: 16px;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            letter-spacing: 0;
        }

        /* ── Cal.com Display Typography ── */
        .font-cal {
            font-family: 'Cal Sans', 'Inter', -apple-system, sans-serif;
            font-weight: 600;
        }

        .cal-display-xl {
            font-family: 'Cal Sans', 'Inter', -apple-system, sans-serif;
            font-size: clamp(38px, 5.5vw, 64px);
            font-weight: 600;
            line-height: 1.05;
            letter-spacing: -2px;
            color: var(--color-ink);
        }

        .cal-display-lg {
            font-family: 'Cal Sans', 'Inter', -apple-system, sans-serif;
            font-size: clamp(28px, 4vw, 48px);
            font-weight: 600;
            line-height: 1.1;
            letter-spacing: -1.5px;
            color: var(--color-ink);
        }

        .cal-display-md {
            font-family: 'Cal Sans', 'Inter', -apple-system, sans-serif;
            font-size: clamp(22px, 3vw, 36px);
            font-weight: 600;
            line-height: 1.15;
            letter-spacing: -1px;
            color: var(--color-ink);
        }

        .cal-display-sm {
            font-family: 'Cal Sans', 'Inter', -apple-system, sans-serif;
            font-size: clamp(20px, 2.5vw, 28px);
            font-weight: 600;
            line-height: 1.2;
            letter-spacing: -0.5px;
            color: var(--color-ink);
        }

        /* ── Cal.com Buttons ── */
        .cal-btn-primary {
            background-color: #111111;
            color: #ffffff;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            font-weight: 600;
            line-height: 1;
            letter-spacing: 0;
            border-radius: 8px;
            padding: 12px 20px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            border: 1px solid #111111;
            transition: background-color 0.15s ease, transform 0.1s ease;
            box-sizing: border-box;
            cursor: pointer;
        }
        .cal-btn-primary:active {
            background-color: #242424;
            transform: scale(0.99);
        }

        .cal-btn-secondary {
            background-color: #ffffff;
            color: #111111;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            font-weight: 600;
            line-height: 1;
            letter-spacing: 0;
            border-radius: 8px;
            padding: 12px 20px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            border: 1px solid #e5e7eb;
            transition: background-color 0.15s ease, border-color 0.15s ease;
            box-sizing: border-box;
            cursor: pointer;
        }
        .cal-btn-secondary:hover {
            background-color: #f8f9fa;
            border-color: #d1d5db;
        }
        .cal-btn-secondary:active {
            background-color: #f3f4f6;
        }

        /* ── Cal.com Nav Pill Group ── */
        .cal-nav-pill-group {
            background-color: #f8f9fa;
            border-radius: 9999px;
            padding: 6px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            border: 1px solid #f3f4f6;
        }

        .cal-category-tab {
            background: transparent;
            color: #6b7280;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            font-weight: 500;
            padding: 8px 14px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.15s ease;
            white-space: nowrap;
        }
        .cal-category-tab:hover {
            color: #111111;
        }
        .cal-category-tab.active,
        .cal-category-tab-active {
            background-color: #ffffff;
            color: #111111;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            font-weight: 600;
        }

        /* ── Cal.com Cards ── */
        .cal-card {
            background-color: #f5f5f5;
            border-radius: 12px;
            padding: 32px;
            box-sizing: border-box;
        }

        .cal-product-card {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
            transition: box-shadow 0.2s ease, transform 0.2s ease;
        }
        .cal-product-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
            transform: translateY(-2px);
        }

        .cal-hero-mockup-card {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        /* ── Cal.com Badge Pills ── */
        .cal-badge-pill {
            background-color: #f5f5f5;
            color: #111111;
            font-family: 'Inter', sans-serif;
            font-size: 13px;
            font-weight: 500;
            border-radius: 9999px;
            padding: 4px 12px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .cal-avatar-circle {
            width: 36px;
            height: 36px;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 600;
            flex-shrink: 0;
        }

        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>

    <!-- Tailwind CSS with Cal.com Tokens -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
                        display: ['Cal Sans', 'Inter', '-apple-system', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace']
                    },
                    colors: {
                        primary: '#111111',
                        'primary-active': '#242424',
                        canvas: '#ffffff',
                        'surface-card': '#f5f5f5',
                        'surface-soft': '#f8f9fa',
                        'surface-strong': '#e5e7eb',
                        'surface-dark': '#101010',
                        'surface-dark-elevated': '#1a1a1a',
                        hairline: '#e5e7eb',
                        'hairline-soft': '#f3f4f6',
                        ink: '#111111',
                        body: '#374151',
                        muted: '#6b7280',
                        'muted-soft': '#898989',
                        'on-dark': '#ffffff',
                        'on-dark-soft': '#a1a1aa',
                        'brand-accent': '#3b82f6',
                        'badge-orange': '#fb923c',
                        'badge-pink': '#ec4899',
                        'badge-violet': '#8b5cf6',
                        'badge-emerald': '#34d399'
                    },
                    borderRadius: {
                        'xs': '4px',
                        'sm': '6px',
                        'md': '8px',
                        'lg': '12px',
                        'xl': '16px',
                        'pill': '9999px',
                        'full': '9999px'
                    },
                    spacing: {
                        'section': '96px'
                    }
                }
            }
        }
    </script>

    <!-- Lucide Icons -->
    <script src="{{ asset('public/vendor/lucide/lucide.min.js') }}"></script>
    <script>
        if (typeof lucide === 'undefined') {
            document.write('<script src="https://unpkg.com/lucide@latest"><\/script>');
        }
    </script>
    @stack('styles')
</head>
<body class="bg-white text-body antialiased flex flex-col min-h-screen">

    <!-- ════ TOP NAVIGATION ════ -->
    <nav class="bg-white/90 backdrop-blur-md border-b border-[#e5e7eb] sticky top-0 z-50 select-none">
        <div class="w-full px-6 sm:px-10 lg:px-12 h-16 sm:h-[68px] flex items-center justify-between">
            
            <!-- Left: Brand Logo & Wordmark (Flush to left end) -->
            <a href="{{ route('public.landing') }}" class="flex items-center gap-3 text-[#111111] no-underline group shrink-0" title="PhoneFix Azamgarh — Express Phone Repair & Genuine Accessories">
                <div class="w-9 h-9 rounded-lg bg-[#111111] text-white flex items-center justify-center shadow-xs group-hover:bg-[#242424] transition-colors">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                        <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                    </svg>
                </div>
                <div class="flex flex-col">
                    <span class="font-cal text-[18px] tracking-tight text-[#111111] leading-none font-semibold">PhoneFix <span class="text-[#6b7280] font-normal">Azamgarh</span></span>
                    <span class="text-[10px] uppercase font-semibold text-[#898989] tracking-wider mt-1">Express Lab & Accessories</span>
                </div>
            </a>

            <!-- Center: Spacious Navigation Links with Cal.com interactive hover pills -->
            <div class="hidden md:flex items-center gap-1.5 text-[14px] font-medium text-[#6b7280]">
                <a href="{{ route('public.landing') }}" class="px-3.5 py-1.5 rounded-md transition-colors {{ request()->routeIs('public.landing') ? 'text-[#111111] font-semibold bg-[#f8f9fa]' : 'hover:text-[#111111] hover:bg-[#f8f9fa]' }}">Home</a>
                <a href="{{ route('public.store') }}" class="px-3.5 py-1.5 rounded-md transition-colors {{ request()->routeIs('public.store') ? 'text-[#111111] font-semibold bg-[#f8f9fa]' : 'hover:text-[#111111] hover:bg-[#f8f9fa]' }}">Accessories</a>
                <a href="{{ route('public.landing') }}#repairs" class="px-3.5 py-1.5 rounded-md hover:text-[#111111] hover:bg-[#f8f9fa] transition-colors">Express Repairs</a>
                <a href="{{ route('public.about') }}" class="px-3.5 py-1.5 rounded-md transition-colors {{ request()->routeIs('public.about') ? 'text-[#111111] font-semibold bg-[#f8f9fa]' : 'hover:text-[#111111] hover:bg-[#f8f9fa]' }}">About</a>
                <a href="{{ route('public.contact') }}" class="px-3.5 py-1.5 rounded-md transition-colors {{ request()->routeIs('public.contact') ? 'text-[#111111] font-semibold bg-[#f8f9fa]' : 'hover:text-[#111111] hover:bg-[#f8f9fa]' }}">Contact</a>
            </div>

            <!-- Right: Login & Track Repair Buttons (Flush to right end) -->
            <div class="flex items-center gap-2.5 shrink-0">
                @auth
                    <a href="{{ route('mobileshop.dashboard', ['company_id' => auth()->user()?->company_id ?? session('company_id') ?? 1]) }}" class="cal-btn-secondary text-[13px] font-semibold h-10 px-3.5 sm:px-4 rounded-lg inline-flex items-center gap-2 border border-[#e5e7eb] text-[#111111] hover:bg-[#f8f9fa] transition-colors" title="Console Dashboard">
                        <svg class="w-4 h-4 stroke-current fill-none stroke-2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                        <span>Dashboard</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="cal-btn-secondary text-[13px] font-semibold h-10 px-3.5 sm:px-4 rounded-lg inline-flex items-center gap-2 border border-[#e5e7eb] text-[#111111] hover:bg-[#f8f9fa] transition-colors" title="Login">
                        <svg class="w-4 h-4 stroke-current fill-none stroke-2" viewBox="0 0 24 24">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                            <polyline points="10 17 15 12 10 7"></polyline>
                            <line x1="15" y1="12" x2="3" y2="12"></line>
                        </svg>
                        <span>Login</span>
                    </a>
                @endauth

                <a href="{{ route('public.track_repair') }}" class="cal-btn-primary text-[13px] font-semibold h-10 px-4 sm:px-5 rounded-lg inline-flex items-center gap-2 shadow-xs hover:shadow transition-all group">
                    <span class="w-2 h-2 rounded-full bg-badge-emerald animate-pulse"></span>
                    <span>Track Repair</span>
                    <svg class="w-3.5 h-3.5 stroke-current fill-none stroke-2 group-hover:translate-x-0.5 transition-transform" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </a>

                <!-- Mobile Hamburger Toggle -->
                <button type="button" id="cal-mobile-btn" class="md:hidden p-2 text-[#111111] hover:bg-[#f8f9fa] rounded-lg transition-colors" aria-label="Toggle menu">
                    <svg class="w-5 h-5 stroke-current fill-none stroke-2" viewBox="0 0 24 24">
                        <line x1="4" y1="7" x2="20" y2="7"></line>
                        <line x1="4" y1="17" x2="20" y2="17"></line>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Navigation Menu Tray -->
        <div id="cal-mobile-menu" class="hidden md:hidden bg-white border-b border-[#e5e7eb] px-6 py-5 space-y-2 text-[15px] font-medium text-[#111111] shadow-lg">
            <a href="{{ route('public.landing') }}" class="block px-3 py-2 rounded-md hover:bg-[#f8f9fa] transition-colors {{ request()->routeIs('public.landing') ? 'font-semibold bg-[#f8f9fa]' : '' }}">Home</a>
            <a href="{{ route('public.store') }}" class="block px-3 py-2 rounded-md hover:bg-[#f8f9fa] transition-colors {{ request()->routeIs('public.store') ? 'font-semibold bg-[#f8f9fa]' : '' }}">Accessories Catalog</a>
            <a href="{{ route('public.landing') }}#repairs" class="block px-3 py-2 rounded-md hover:bg-[#f8f9fa] transition-colors">Express Repairs</a>
            <a href="{{ route('public.about') }}" class="block px-3 py-2 rounded-md hover:bg-[#f8f9fa] transition-colors {{ request()->routeIs('public.about') ? 'font-semibold bg-[#f8f9fa]' : '' }}">About Lab</a>
            <a href="{{ route('public.contact') }}" class="block px-3 py-2 rounded-md hover:bg-[#f8f9fa] transition-colors {{ request()->routeIs('public.contact') ? 'font-semibold bg-[#f8f9fa]' : '' }}">Contact Desk</a>
            <div class="pt-3 border-t border-[#f3f4f6] flex flex-col gap-2">
                @auth
                    <a href="{{ route('mobileshop.dashboard', ['company_id' => auth()->user()?->company_id ?? session('company_id') ?? 1]) }}" class="cal-btn-secondary w-full text-center flex items-center justify-center gap-2 h-10 rounded-lg border border-[#e5e7eb] text-[#111111] hover:bg-[#f8f9fa]">
                        <svg class="w-4 h-4 stroke-current fill-none stroke-2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                        <span>Console Dashboard</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="cal-btn-secondary w-full text-center flex items-center justify-center gap-2 h-10 rounded-lg border border-[#e5e7eb] text-[#111111] hover:bg-[#f8f9fa]">
                        <svg class="w-4 h-4 stroke-current fill-none stroke-2" viewBox="0 0 24 24">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                            <polyline points="10 17 15 12 10 7"></polyline>
                            <line x1="15" y1="12" x2="3" y2="12"></line>
                        </svg>
                        <span>Login</span>
                    </a>
                @endauth
                <a href="{{ route('public.track_repair') }}" class="cal-btn-primary w-full text-center flex items-center justify-center gap-2 h-10 rounded-lg">
                    <span class="w-2 h-2 rounded-full bg-badge-emerald"></span>
                    <span>Track Repair</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- ════ MAIN CONTENT CANVAS ════ -->
    <main class="flex-grow bg-canvas">
        @yield('content')
    </main>

    <!-- ════ FLOATING WHATSAPP BUTTON ════ -->
    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', store_whatsapp()) }}?text={{ urlencode('Hi ' . store_name() . ', I would like to inquire about accessories / repair service.') }}" target="_blank" class="fixed bottom-6 right-6 z-40 bg-surface-dark hover:bg-surface-dark-elevated text-white rounded-full p-3.5 shadow-xl hover:shadow-2xl transition-all flex items-center justify-center border border-white/10 group" title="Chat on WhatsApp">
        <span class="absolute -top-1 -right-1 w-3.5 h-3.5 bg-badge-emerald rounded-full border-2 border-surface-dark animate-pulse"></span>
        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.572-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
        </svg>
    </a>

    <!-- ════ 6. CAL.COM DEEP NAVY/BLACK FOOTER (#101010) ════ -->
    <footer class="bg-surface-dark text-on-dark-soft border-t border-white/10 mt-auto pt-16 pb-12">
        <div class="max-w-[1200px] mx-auto px-4">
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-10 pb-12 border-b border-white/10">
                <!-- Column 1: Products -->
                <div>
                    <h3 class="text-[13px] font-semibold text-white uppercase tracking-wider mb-4">Accessories</h3>
                    <ul class="space-y-2.5 text-xs text-on-dark-soft">
                        <li><a href="{{ route('public.store') }}" class="hover:text-white transition-colors">All Products</a></li>
                        <li><a href="{{ route('public.store', ['category' => 'charger']) }}" class="hover:text-white transition-colors">Fast Chargers</a></li>
                        <li><a href="{{ route('public.store', ['category' => 'cable']) }}" class="hover:text-white transition-colors">Cables & Connectors</a></li>
                        <li><a href="{{ route('public.store', ['category' => 'tempered_glass']) }}" class="hover:text-white transition-colors">Screen Protectors</a></li>
                        <li><a href="{{ route('public.store', ['category' => 'back_cover']) }}" class="hover:text-white transition-colors">Shockproof Cases</a></li>
                    </ul>
                </div>

                <!-- Column 2: Solutions / Services -->
                <div>
                    <h3 class="text-[13px] font-semibold text-white uppercase tracking-wider mb-4">Repair Lab</h3>
                    <ul class="space-y-2.5 text-xs text-on-dark-soft">
                        <li><a href="{{ route('public.track_repair') }}" class="text-white font-medium hover:underline flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-badge-emerald"></span> Live Repair Tracker
                        </a></li>
                        <li><a href="{{ route('public.landing') }}#repairs" class="hover:text-white transition-colors">Display Assembly</a></li>
                        <li><a href="{{ route('public.landing') }}#repairs" class="hover:text-white transition-colors">Battery Replacement</a></li>
                        <li><a href="{{ route('public.landing') }}#repairs" class="hover:text-white transition-colors">Charging Port Fix</a></li>
                        <li><a href="{{ route('public.landing') }}#repairs" class="hover:text-white transition-colors">Level-4 Micro-Soldering</a></li>
                    </ul>
                </div>

                <!-- Column 3: Company / Guarantees -->
                <div>
                    <h3 class="text-[13px] font-semibold text-white uppercase tracking-wider mb-4">Company</h3>
                    <ul class="space-y-2.5 text-xs text-on-dark-soft">
                        <li><a href="{{ route('public.about') }}" class="hover:text-white transition-colors">About Our Lab</a></li>
                        <li><a href="{{ route('public.privacy') }}" class="hover:text-white transition-colors">Privacy & Zero-Wipe Protocol</a></li>
                        <li><a href="{{ route('public.terms') }}" class="hover:text-white transition-colors">Terms of Service</a></li>
                        <li><a href="{{ route('public.warranty') }}" class="hover:text-white transition-colors">90-Day Guarantee</a></li>
                        <li><a href="{{ route('public.refunds') }}" class="hover:text-white transition-colors">Return Policy</a></li>
                    </ul>
                </div>

                <!-- Column 4: Location & Desk -->
                <div>
                    <h3 class="text-[13px] font-semibold text-white uppercase tracking-wider mb-4">Store Location</h3>
                    <ul class="space-y-2.5 text-xs text-on-dark-soft">
                        <li class="text-white font-medium">{{ store_name() }}</li>
                        <li>{{ store_address() }}</li>
                        <li><a href="tel:{{ preg_replace('/[^0-9]/', '', store_phone()) }}" class="text-white hover:underline">{{ store_phone() }}</a></li>
                        <li class="pt-1">
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded bg-white/10 text-white font-mono text-[11px]">
                                Mon–Sun: 9:30 AM – 9:00 PM
                            </span>
                        </li>
                        <li class="pt-3">
                            <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-white/10 hover:bg-white/20 text-white text-xs font-semibold transition-colors">
                                Staff Login &rarr;
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Row -->
            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-muted-soft">
                <div class="flex items-center gap-2">
                    <span class="font-cal text-white text-sm font-semibold tracking-tight">{{ store_name() }}</span>
                    <span>·</span>
                    <span>© {{ date('Y') }} {{ store_name() }}. All rights reserved. {{ store_city() }}.</span>
                </div>
                <div class="flex items-center gap-4 text-xs">
                    <a href="{{ route('public.privacy') }}" class="hover:text-white transition-colors">Privacy</a>
                    <span>·</span>
                    <a href="{{ route('public.terms') }}" class="hover:text-white transition-colors">Terms</a>
                    <span>·</span>
                    <a href="{{ route('public.warranty') }}" class="hover:text-white transition-colors">Warranty</a>
                    <span>·</span>
                    <a href="{{ route('public.shipping') }}" class="hover:text-white transition-colors">Shipping</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Global Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) window.lucide.createIcons();
            const btn = document.getElementById('cal-mobile-btn');
            const menu = document.getElementById('cal-mobile-menu');
            if (btn && menu) {
                btn.addEventListener('click', () => menu.classList.toggle('hidden'));
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
