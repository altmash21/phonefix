<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#5E6AD2">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Maurya Mobile — Mobile Shop ERP')</title>

    <script>
        window.mobiShopRoutes = {
            otpRequest: "{{ route('mobileshop.otp.request') }}",
            otpVerify: "{{ route('mobileshop.otp.verify') }}",
            partsSearch: "{{ route('mobileshop.parts.search') }}"
        };
    </script>

    <!-- Global Date Helpers & Presets (Guaranteed early initialization) -->
    <script>
        (function () {
            function pad2(n) { return (n < 10 ? '0' : '') + n; }
            function toIso(d) {
                return d.getFullYear() + '-' + pad2(d.getMonth() + 1) + '-' + pad2(d.getDate());
            }

            window.formatDate = function (d) {
                if (!d) return '';
                return toIso(d instanceof Date ? d : new Date(d));
            };

            window.getDateRangePreset = function (preset) {
                var now = new Date();
                var y = now.getFullYear();
                var m = now.getMonth();
                var d = now.getDate();
                var todayStr = toIso(now);

                switch (String(preset || '').toLowerCase()) {
                    case 'today':
                        return { from: todayStr, to: todayStr };
                    case 'yesterday':
                        var yest = new Date(y, m, d - 1);
                        var yestStr = toIso(yest);
                        return { from: yestStr, to: yestStr };
                    case 'week':
                    case '7days':
                    case '7_days':
                    case '7-days':
                        var weekAgo = new Date(y, m, d - 6);
                        return { from: toIso(weekAgo), to: todayStr };
                    case 'month':
                    case 'this_month':
                    case 'this-month':
                        var startOfMonth = new Date(y, m, 1);
                        var endOfMonth = new Date(y, m + 1, 0);
                        return { from: toIso(startOfMonth), to: toIso(endOfMonth) };
                    case 'all':
                    default:
                        return { from: '', to: '' };
                }
            };
        })();
    </script>

    <!-- Google Fonts: Inter, Plus Jakarta Sans & JetBrains Mono (Linear Software Craft) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Lucide Icons (with offline fallback) -->
    <script>
        window.__lucideLoaded = false;
        window.__lucideFallback = function () {
            // If the CDN lucide script is unavailable, render icons from the
            // inline fallback set. Runs only when real lucide never loaded.
            if (window.lucide && typeof window.lucide.createIcons === 'function' && window.__lucideLoaded) return;
            window.lucide = window.lucide || {};
            window.lucide.createIcons = function (options) {
                var selector = options && options.selector ? options.selector : '[data-lucide]';
                var nodes = document.querySelectorAll(selector);
                nodes.forEach(function (node) {
                    var name = node.getAttribute('data-lucide');
                    var svg = getFallbackSvg(name);
                    if (svg) node.innerHTML = svg;
                });
            };
        };
        window.getFallbackSvg = function (name) {
            var svgs = {
                'layout-dashboard': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"></rect><rect x="14" y="3" width="7" height="5"></rect><rect x="14" y="12" width="7" height="9"></rect><rect x="3" y="12" width="7" height="5"></rect></svg>',
                'plus': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>',
                'chevron-down': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>',
                'smartphone': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>',
                'zap': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>',
                'repeat': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"></polyline><path d="M3 11V9a4 4 0 0 1 4-4h14"></path><polyline points="7 23 3 19 7 15"></polyline><path d="M21 13v2a4 4 0 0 1-4 4H3"></path></svg>',
                'truck': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>',
                'trending-up': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>',
                'package': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16.5 9.4 7.55 4.24"></path><path d="M7.55 19.76 16.5 14.6"></path><path d="M22 11.5a7 7 0 1 1-14 0 7 7 0 0 1 14 0z"></path></svg>',
                'book-open': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>',
                'bar-chart-3': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>',
                'settings': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"></path></svg>',
                'sliders': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line><line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line><line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line><line x1="1" y1="1" x2="23" y2="23"></line></svg>',
                'log-out': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>',
                'alert-circle': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>',
                'check-circle-2': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>',
                'arrow-left': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>',
                'shopping-cart': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>',
                'wrench': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>',
                'file-check-2': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path><path d="M14 2v4a2 2 0 0 0 2 2h4"></path><path d="M9 15H7"></path><path d="M13 15h2"></path><path d="M9 11H7"></path><path d="M13 11h2"></path><path d="M15 15h2"></path><path d="M15 11h2"></path><path d="M9 19H7"></path><path d="M13 19h6"></path></svg>',
                'alert-triangle': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>',
                'user': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>',
                'calendar': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>',
                'building-2': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 22V4h12v18"></path><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"></path><path d="M18 9h2a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2h-2"></path><path d="M6 12h4"></path></svg>',
                'headphones': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 18h1.5a2.5 2.5 0 0 0 2.5-2.5v-1a2.5 2.5 0 0 1 2.5-2.5h3A2.5 2.5 0 0 0 13 6.5V4A2.5 2.5 0 0 1 15.5 6.5h3a2.5 2.5 0 0 0 2.5-2.5v1A2.5 2.5 0 0 1 21.5 12v1.5a2.5 2.5 0 0 0 2.5 2.5H22a2.5 2.5 0 0 0 2.5 2.5v1a2.5 2.5 0 0 0 2.5 2.5h3"></path><path d="M10.5 13a2.5 2.5 0 0 0-5 0"></path></svg>'
            };
            return svgs[name] || null;
        };
        window.__lucideFallback();
    </script>
    <script>
        // Robust CDN loading with automatic fallback (works with or without internet)
        (function () {
            var script = document.createElement('script');
            script.src = 'https://unpkg.com/lucide@latest';
            script.onload = function () { window.__lucideLoaded = true; window.refreshIcons && window.refreshIcons(); };
            script.onerror = function () { window.__lucideLoaded = false; };
            document.head.appendChild(script);
        })();
    </script>    <!-- Tailwind CSS with Linear Light Design System Tokens -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            corePlugins: {
                preflight: false,
            },
            theme: {
                extend: {
                    colors: {
                        canvas: "#ffffff",
                        surface: {
                            1: "#f7f8fa",
                            2: "#f1f2f4",
                            3: "#e9ebee",
                            4: "#e2e5e9",
                        },
                        ink: {
                            DEFAULT: "#111113",
                            muted: "#4f535b",
                            subtle: "#737780",
                            tertiary: "#9a9ea6",
                        },
                        primary: {
                            DEFAULT: "#5e6ad2",
                            hover: "#4f5bc2",
                            focus: "#5e69d1",
                            tint: "#f0f2ff",
                        },
                        hairline: {
                            DEFAULT: "#e2e4e8",
                            strong: "#cfd3d9",
                            tertiary: "#bcc1c8",
                        },
                        success: "#27a644",
                    },
                    fontFamily: {
                        display: ["Inter", "SF Pro Display", "-apple-system", "system-ui", "Segoe UI", "Roboto", "sans-serif"],
                        text: ["Inter", "SF Pro Display", "-apple-system", "system-ui", "Segoe UI", "Roboto", "sans-serif"],
                        mono: ["JetBrains Mono", "ui-monospace", "SF Mono", "Menlo", "monospace"],
                    },
                    screens: {
                        mobile: "480px",
                        tablet: "768px",
                        desktop: "1024px",
                        wide: "1280px",
                        "desktop-xl": "1440px",
                    },
                    borderRadius: {
                        xs: "4px",
                        sm: "6px",
                        md: "8px",
                        lg: "12px",
                        xl: "16px",
                        pill: "9999px",
                    }
                }
            }
        }
    </script>

    <!-- Maurya Mobile Admin Panel Design System (Linear Light System) -->
    <link rel="stylesheet" href="{{ url('/css/admin-panel.css') }}?v={{ time() }}">

    <!-- Dedicated Print Media Engine: Eliminates UI chrome, sidebars, headers, and buttons on Print/PDF -->
    <style>
        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm 12mm 10mm 12mm;
            }

            *, *::before, *::after {
                box-shadow: none !important;
                text-shadow: none !important;
            }

            html, body {
                background: #ffffff !important;
                color: #111827 !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                height: auto !important;
                min-height: 0 !important;
                overflow: visible !important;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* Hide all app chrome, sidebars, topbars, buttons, debug bars, and mobile nav */
            .topbar,
            header.topbar,
            .sidebar,
            aside.sidebar,
            .sidebar-nav,
            .sidebar-footer,
            .page-header,
            .page-header-right,
            .page-back,
            .mobile-bottom-nav,
            .mobile-fab-container,
            .btn-sales-fab,
            .btn-purchase-fab,
            .flash-success,
            .flash-error,
            #flash-msg,
            .phpdebugbar,
            .phpdebugbar-openhandler,
            .phpdebugbar-mini,
            #phpdebugbar,
            .reports-filter-card,
            .filter-bar,
            .filter-pill,
            .pagination-bar,
            .pagination-wrapper,
            button,
            .btn,
            .btn-primary,
            .btn-outline,
            .btn-icon,
            .no-print,
            #new-action-menu,
            #user-dropdown,
            .user-dropdown,
            .modal,
            [id*="Modal"],
            [id*="Drawer"],
            .fab-dropup-menu {
                display: none !important;
                visibility: hidden !important;
                height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            /* Unconstrain body, app wrapper, and containers */
            .app-wrapper {
                display: block !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                background: transparent !important;
                width: 100% !important;
                max-width: 100% !important;
            }

            .content-area {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                background: transparent !important;
                min-height: 0 !important;
            }

            .page-body {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }

            .invoice-page-wrapper,
            .statement-page-wrapper {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                display: block !important;
                background: transparent !important;
            }

            .printable-invoice-container {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                border: none !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                background: #ffffff !important;
            }

            table {
                width: 100% !important;
                border-collapse: collapse !important;
                page-break-inside: auto !important;
            }

            tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            thead {
                display: table-header-group !important;
            }

            tfoot {
                display: table-footer-group !important;
            }

            /* Thermal POS Receipt Mode (80mm) */
            body.thermal-mode {
                width: 76mm !important;
                max-width: 76mm !important;
                margin: 0 auto !important;
                padding: 0 !important;
                font-family: 'Courier New', Courier, monospace !important;
            }

            body.thermal-mode #viewA4 {
                display: none !important;
            }

            body.thermal-mode #viewThermal {
                display: block !important;
                width: 100% !important;
                max-width: 76mm !important;
                margin: 0 auto !important;
                padding: 4mm 2mm !important;
                border: none !important;
                box-shadow: none !important;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>window.Laravel = { csrfToken: "{{ csrf_token() }}" };</script>
    <script>
    (function(){
        var MT = window.MT || (window.MT = {});
        MT.queue = [];
        MT.online = navigator.onLine;
        MT.syncKey = function(){ return Math.random().toString(36).slice(2,10) + Date.now().toString(36); };
        window.addEventListener("offline", function(){ MT.online = false; });
        window.addEventListener("online", function(){ MT.online = true; MT.flush(); });
        MT.enqueue = function(action, model, payload){
            MT.queue.push({ action: action, model: model, payload: JSON.stringify(payload), sync_key: MT.syncKey() });
            try { localStorage.setItem("mt_queue", JSON.stringify(MT.queue)); } catch(e) {}
        };
        MT.flush = function(){
            if (!MT.online || !MT.queue.length) return;
            var batch = MT.queue.slice(0, 20);
            fetch("{{ url('api/sync/upload') }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": document.querySelector("meta[name=csrf-token]").content, "Accept": "application/json" },
                body: JSON.stringify({ operations: batch })
            }).then(function(r){ return r.json(); })
            .then(function(res){
                if (res.success) {
                    var uploaded = new Set(res.data.filter(function(d){ return d.status === "ok"; }).map(function(d){ return d.sync_key; }));
                    MT.queue = MT.queue.filter(function(q){ return !uploaded.has(q.sync_key); });
                    try { localStorage.setItem("mt_queue", JSON.stringify(MT.queue)); } catch(e) {}
                    if (!MT.queue.length) MT.queue = [];
                    MT.flush();
                }
            }).catch(function(){ /* retry on next online */ });
        };
        MT.syncStatus = function(cb){
            fetch("{{ url('api/sync/status') }}", { headers: { "Accept": "application/json" } }).then(function(r){ return r.json(); }).then(cb);
        };
        if (navigator.onLine) setTimeout(MT.flush, 500);
    })();
    </script>

    <!-- Flash messages -->
    @if(session('success'))
        <div class="flash-success" id="flash-msg">
            <i data-lucide="check-circle-2" style="width:18px;height:18px;"></i>
            {{ session('success') }}
            <button onclick="document.getElementById('flash-msg').remove()" style="margin-left:auto;background:none;border:none;cursor:pointer;color:currentColor;font-weight:bold;">✕</button>
        </div>
        <script>setTimeout(() => { const el = document.getElementById('flash-msg'); if (el) { el.style.transition = 'opacity 0.3s'; el.style.opacity = '0'; setTimeout(() => el.remove(), 300); } }, 5000);</script>
    @endif
    @if(session('error'))
        <div class="flash-error" id="flash-msg">
            <i data-lucide="alert-circle" style="width:18px;height:18px;"></i>
            {{ session('error') }}
            <button onclick="document.getElementById('flash-msg').remove()" style="margin-left:auto;background:none;border:none;cursor:pointer;color:currentColor;font-weight:bold;">✕</button>
        </div>
        <script>setTimeout(() => { const el = document.getElementById('flash-msg'); if (el) { el.style.transition = 'opacity 0.3s'; el.style.opacity = '0'; setTimeout(() => el.remove(), 300); } }, 8000);</script>
    @endif

    <header class="topbar no-print">
        <a href="{{ route('mobileshop.dashboard') }}" class="topbar-logo">
            <div class="topbar-logo-icon">
                <i data-lucide="smartphone" style="width:18px;height:18px;"></i>
            </div>
            <div>
                <div class="topbar-logo-text">Maurya Mobile <span class="topbar-logo-dot"></span></div>
                <div class="topbar-logo-sub topbar-subtitle">Retail & Service Console</div>
            </div>
        </a>

        <div class="topbar-right">
            @php
                $u = Auth::user();
                $roleLabel = 'Staff';
                if ($u) {
                    if ($u->hasRole('admin') || $u->hasRole('store-admin')) {
                        $roleLabel = 'Store Admin';
                    } elseif ($u->hasRole('sales-staff')) {
                        $roleLabel = 'New Phones POS';
                    } elseif ($u->hasRole('secondhand-staff')) {
                        $roleLabel = 'Buyback Specialist';
                    } elseif ($u->hasRole('accessories-staff')) {
                        $roleLabel = 'Accessories Staff';
                    } elseif ($u->hasRole('cover-staff')) {
                        $roleLabel = 'Cover & Tempered';
                    } elseif ($u->hasRole('repair-technician')) {
                        $roleLabel = 'Service Technician';
                    }
                }
            @endphp


            <!-- User Badge -->
            <div class="user-badge" id="user-badge-btn">
                <div class="user-avatar">
                    {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                </div>
                <span class="user-name">{{ Auth::user()->name ?? 'Admin' }}</span>
                <i data-lucide="chevron-down" class="user-chevron"></i>

                <div class="user-dropdown" id="user-dropdown">
                    <a href="{{ route('profile.edit', ['company_id' => session('company_id', 1), 'user' => Auth::id()]) }}">
                        <i data-lucide="user" style="width:14px;height:14px;"></i> Profile
                    </a>
                    @canany(['read-mobileshop-reports','read-reports-khata','read-reports-financial'])
                    <a href="{{ route('mobileshop.reports') }}">
                        <i data-lucide="bar-chart-3" style="width:14px;height:14px;"></i> Reports & Analytics
                    </a>
                    @endcanany
                    @can('read-mobileshop-repairs')
                    <a href="{{ route('mobileshop.repairs') }}">
                        <i data-lucide="wrench" style="width:14px;height:14px;"></i> Repairs Desk
                    </a>
                    @endcan
                    @if($u && ($u->hasRole('admin') || $u->hasRole('store-admin')))
                    <a href="{{ route('mobileshop.masters') }}">
                        <i data-lucide="settings" style="width:14px;height:14px;"></i> Masters & Settings
                    </a>
                    @endif
                    <a href="{{ route('logout') }}">
                        <i data-lucide="log-out" style="width:14px;height:14px;"></i> Log Out
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="app-wrapper">
        <aside class="sidebar no-print">
            <nav class="sidebar-nav">
                    {{-- ════ UNIFIED SIDEBAR — Same 5 items for all roles, @can gated ════ --}}
                @can('read-mobileshop-dashboard')
                <a href="{{ route('mobileshop.dashboard') }}"
                   class="nav-link {{ request()->routeIs('mobileshop.dashboard') ? 'active' : '' }}">
                    <i data-lucide="layout-dashboard"></i>
                    Dashboard
                </a>
                @endcan

                @can('read-mobileshop-purchase')
                <a href="{{ route('mobileshop.purchase') }}"
                   class="nav-link {{ request()->routeIs('mobileshop.purchase*') ? 'active' : '' }}">
                    <i data-lucide="truck"></i>
                    Purchase
                </a>
                @endcan

                @can('read-mobileshop-sales')
                <a href="{{ route('mobileshop.sales') }}"
                   class="nav-link {{ request()->routeIs('mobileshop.sales*') ? 'active' : '' }}">
                    <i data-lucide="trending-up"></i>
                    Sales
                </a>
                @endcan

                @can('read-mobileshop-stock')
                <a href="{{ route('mobileshop.stock') }}"
                   class="nav-link {{ request()->routeIs('mobileshop.stock*') ? 'active' : '' }}">
                    <i data-lucide="package"></i>
                    Stock
                </a>
                @endcan

                <a href="{{ route('mobileshop.khata') }}"
                   class="nav-link {{ request()->routeIs('mobileshop.khata*') ? 'active' : '' }}">
                    <i data-lucide="book-open"></i>
                    Customer Khata
                </a>

                @if($u && ($u->hasRole('admin') || $u->hasRole('store-admin') || $u->hasRole('sales-staff')))
                <a href="{{ route('mobileshop.emi.ledger') }}"
                   class="nav-link {{ request()->routeIs('mobileshop.emi*') ? 'active' : '' }}">
                    <i data-lucide="building-2"></i>
                    EMI Ledger
                </a>
                @endif

                @can('read-mobileshop-repairs')
                <a href="{{ route('mobileshop.repairs') }}"
                   class="nav-link {{ request()->routeIs('mobileshop.repairs*') ? 'active' : '' }}">
                    <i data-lucide="wrench"></i>
                    Repairs Desk
                </a>
                @endcan

                @canany(['read-mobileshop-reports', 'read-reports-financial', 'read-reports-khata'])
                <a href="{{ route('mobileshop.reports') }}"
                   class="nav-link {{ request()->routeIs('mobileshop.reports*') ? 'active' : '' }}">
                    <i data-lucide="bar-chart-3"></i>
                    Reports
                </a>
                @endcanany

                @if($u && ($u->hasRole('admin') || $u->hasRole('store-admin')))
                <a href="{{ route('mobileshop.masters') }}"
                   class="nav-link {{ request()->routeIs('mobileshop.masters*') ? 'active' : '' }}">
                    <i data-lucide="sliders"></i>
                    Masters
                </a>
                @endif
            </nav>

            <!-- Sidebar footer -->
            <div class="sidebar-footer">
                <div style="display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:8px;background:var(--color-bg);">
                    <div class="user-avatar" style="width:30px;height:30px;font-size:11px;">
                        {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 2)) }}
                    </div>
                    <div style="overflow:hidden;flex:1;min-width:0;">
                        <div style="font-size:12px;font-weight:600;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ Auth::user()->name ?? 'Staff' }}</div>
                        <div style="font-size:10px;color:var(--text-secondary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $roleLabel }}</div>
                    </div>
                    <a href="{{ route('logout') }}" title="Logout" style="color:var(--text-muted);text-decoration:none;padding:4px;border-radius:6px;transition:color 0.15s;" onmouseover="this.style.color='#DC2626'" onmouseout="this.style.color='var(--text-muted)'">
                        <i data-lucide="log-out" style="width:15px;height:15px;"></i>
                    </a>
                </div>
            </div>
        </aside>

        <!-- ════ CONTENT AREA ════ -->
        <div class="content-area">
            <!-- Page Header -->
            <div class="page-header no-print {{ empty(trim($__env->yieldContent('page-actions'))) ? 'no-actions' : '' }}">
                @hasSection('back-url')
                    <a href="@yield('back-url')" class="page-back">
                        <i data-lucide="arrow-left" style="width:16px;height:16px;"></i>
                    </a>
                @endif
                <h1 class="page-title">@yield('page-title', 'Maurya Mobile')</h1>
                <div class="page-header-right">
                    @yield('page-actions')
                </div>
            </div>

            <main class="main-content">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- ════ MOBILE BOTTOM NAV ════ -->
    <nav class="mobile-bottom-nav no-print">
        <div class="mobile-nav-items">
            @can('read-mobileshop-dashboard')
            <a href="{{ route('mobileshop.dashboard') }}" class="mobile-nav-item {{ request()->routeIs('mobileshop.dashboard') ? 'active' : '' }}">
                <i data-lucide="layout-dashboard"></i>
                <span>Home</span>
            </a>
            @endcan

            @can('read-mobileshop-purchase')
            <a href="{{ route('mobileshop.purchase') }}" class="mobile-nav-item {{ request()->routeIs('mobileshop.purchase*') ? 'active' : '' }}">
                <i data-lucide="truck"></i>
                <span>Purchase</span>
            </a>
            @endcan

            @can('read-mobileshop-sales')
            <a href="{{ route('mobileshop.sales') }}" class="mobile-nav-item {{ request()->routeIs('mobileshop.sales*') ? 'active' : '' }}">
                <i data-lucide="shopping-cart"></i>
                <span>Sales</span>
            </a>
            @endcan

            @can('read-mobileshop-stock')
            <a href="{{ route('mobileshop.stock') }}" class="mobile-nav-item {{ request()->routeIs('mobileshop.stock*') ? 'active' : '' }}">
                <i data-lucide="package"></i>
                <span>Stock</span>
            </a>
            @endcan

            <a href="{{ route('mobileshop.khata') }}" class="mobile-nav-item {{ request()->routeIs('mobileshop.khata*') ? 'active' : '' }}">
                <i data-lucide="book-open"></i>
                <span>Khata</span>
            </a>

            @can('read-mobileshop-repairs')
            <a href="{{ route('mobileshop.repairs') }}" class="mobile-nav-item {{ request()->routeIs('mobileshop.repairs*') ? 'active' : '' }}">
                <i data-lucide="wrench"></i>
                <span>Repairs</span>
            </a>
            @endcan
        </div>
    </nav>

    <script src="{{ asset('js/mobileshop/ui-utils.js') }}"></script>
    @stack('scripts')
</body>
</html>
