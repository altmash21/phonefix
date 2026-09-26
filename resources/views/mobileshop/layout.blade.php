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
    <base href="{{ config('app.url') . '/' }}">
    <title>@yield('title', 'PhoneFix Azamgarh — Mobile Shop ERP')</title>

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

    <!-- Google Fonts: Inter & Plus Jakarta Sans (Non-blocking with Font-Display Swap) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700&family=Inter:wght@400;500;600;700&display=swap">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    </noscript>

    <!-- Lucide Icons (Local bundle with CDN fallback) -->
    <script src="{{ asset('public/vendor/lucide/lucide.min.js') }}"></script>
    <script>
        window.refreshIcons = function () {
            if (window.lucide && typeof window.lucide.createIcons === 'function') {
                try { window.lucide.createIcons(); } catch (e) { /* noop */ }
            }
        };

        // Fallback to CDN if local bundle is somehow inaccessible
        if (typeof lucide === 'undefined') {
            (function () {
                var script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/lucide@latest/dist/umd/lucide.min.js';
                script.onload = function () {
                    window.refreshIcons();
                };
                script.onerror = function () {
                    var script2 = document.createElement('script');
                    script2.src = 'https://unpkg.com/lucide@latest/dist/umd/lucide.min.js';
                    script2.onload = function () { window.refreshIcons(); };
                    document.head.appendChild(script2);
                };
                document.head.appendChild(script);
            })();
        } else {
            document.addEventListener('DOMContentLoaded', function () {
                window.refreshIcons();
            });
        }
    </script>

    <!-- MobileShop Pre-Compiled Tailwind CSS (replaces cdn.tailwindcss.com) -->
    <link rel="stylesheet" href="{{ asset('public/css/mobileshop-panel.css') }}?v=2.6.0">
    <link rel="stylesheet" href="{{ asset('public/css/admin-panel.css') }}?v=2.5.0">

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
    <div id="instant-page-progress" style="position:fixed;top:0;left:0;height:2.5px;width:0%;background:linear-gradient(90deg, #5E6AD2, #818cf8);z-index:99999;transition:width 0.25s ease, opacity 0.2s ease;pointer-events:none;opacity:0;"></div>
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
        <button type="button" class="topbar-hamburger no-print" id="mobiHamburgerBtn" onclick="openMobileSidebar()" title="Navigation Menu" aria-label="Open navigation menu">
            <i data-lucide="menu"></i>
        </button>
        <a href="{{ route('mobileshop.dashboard') }}" class="topbar-logo">
            <div class="topbar-logo-icon">
                <i data-lucide="smartphone" style="width:18px;height:18px;"></i>
            </div>
            <div>
                <div class="topbar-logo-text">PhoneFix Azamgarh <span class="topbar-logo-dot"></span></div>
                <div class="topbar-logo-sub topbar-subtitle">Retail & Service Console</div>
            </div>
        </a>

        <div class="topbar-right">
            @if(Auth::check())
                @php
                    $u = Auth::user();
                    $roleLabel = 'Staff';
                    if ($u) {
                        if ($u->hasRole('admin') || $u->hasRole('store-admin')) {
                            $roleLabel = 'Store Admin';
                        } elseif ($u->hasRole('accessories-staff')) {
                            $roleLabel = 'Accessories Staff';
                        } elseif ($u->hasRole('repair-technician')) {
                            $roleLabel = 'Repair Technician';
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
                        @canany(['read-mobileshop-dashboard', 'read-mobileshop-reports', 'read-admin-panel'])
                        <a href="{{ route('mobileshop.expenses.index') }}">
                            <i data-lucide="receipt" style="width:14px;height:14px;"></i> Shop Expenses
                        </a>
                        @endcanany
                        @canany(['read-mobileshop-repairs', 'manage-stock-repairs', 'update-mobileshop-repairs', 'read-admin-panel'])
                        <a href="{{ route('mobileshop.repairs') }}">
                            <i data-lucide="wrench" style="width:14px;height:14px;"></i> Repair Desk
                        </a>
                        @endcanany
                        @if($u && ($u->hasRole('admin') || $u->hasRole('store-admin') || $u->can('read-mobileshop-masters') || $u->can('read-admin-panel')))
                        <a href="{{ route('mobileshop.masters') }}">
                            <i data-lucide="settings" style="width:14px;height:14px;"></i> Master Option
                        </a>
                        @endif
                        <a href="{{ route('logout') }}">
                            <i data-lucide="log-out" style="width:14px;height:14px;"></i> Log Out
                        </a>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="btn btn-primary btn-sm" style="display:inline-flex;align-items:center;gap:6px;">
                    <i data-lucide="log-in" style="width:14px;height:14px;"></i>
                    <span>Login</span>
                </a>
            @endif
        </div>
    </header>

    {{-- ════ MOBILE SIDEBAR OVERLAY + DRAWER ════ --}}
    <div class="mobile-sidebar-overlay no-print" id="mobileSidebarOverlay" onclick="closeMobileSidebar()"></div>
    <div class="mobile-sidebar-drawer no-print" id="mobileSidebarDrawer" role="navigation" aria-label="Mobile navigation">
        <div class="mobile-sidebar-drawer-header">
            <div style="display:flex;align-items:center;gap:8px;">
                <div class="topbar-logo-icon" style="width:28px;height:28px;">
                    <i data-lucide="smartphone" style="width:14px;height:14px;"></i>
                </div>
                <div class="topbar-logo-text" style="font-size:13px;">PhoneFix Azamgarh</div>
            </div>
            <button type="button" class="mobile-sidebar-drawer-close" onclick="closeMobileSidebar()" aria-label="Close menu">×</button>
        </div>
        <nav class="sidebar-nav">
            @canany(['read-mobileshop-dashboard', 'read-admin-panel'])
            <a href="{{ route('mobileshop.dashboard') }}"
               class="nav-link {{ request()->routeIs('mobileshop.dashboard') ? 'active' : '' }}"
               onclick="closeMobileSidebar()">
                <i data-lucide="layout-dashboard"></i> Dashboard
            </a>
            @endcanany

            @canany(['read-mobileshop-purchase', 'create-purchase-accessories', 'create-purchase-covers', 'create-purchase-phones', 'create-purchase-secondhand', 'read-admin-panel'])
            <a href="{{ route('mobileshop.purchase') }}"
               class="nav-link {{ request()->routeIs('mobileshop.purchase*') ? 'active' : '' }}"
               onclick="closeMobileSidebar()">
                <i data-lucide="truck"></i> Purchase
            </a>
            @endcanany

            @canany(['read-mobileshop-sales', 'sell-mobileshop-accessories', 'create-sale-accessories', 'create-sale-covers', 'create-sale-phones', 'read-mobileshop-accessories', 'read-admin-panel'])
            <a href="{{ route('mobileshop.sales') }}"
               class="nav-link {{ (request()->routeIs('mobileshop.sales*') || request()->routeIs('mobileshop.accessories.pos')) ? 'active' : '' }}"
               onclick="closeMobileSidebar()">
                <i data-lucide="shopping-cart"></i> Sales
            </a>
            @endcanany

            @canany(['read-mobileshop-stock', 'manage-stock-accessories', 'manage-stock-covers', 'manage-stock-repairs', 'manage-stock-phones', 'read-admin-panel'])
            <a href="{{ route('mobileshop.stock') }}"
               class="nav-link {{ request()->routeIs('mobileshop.stock*') ? 'active' : '' }}"
               onclick="closeMobileSidebar()">
                <i data-lucide="package"></i> Stock
            </a>
            @endcanany

            @canany(['read-mobileshop-khata', 'read-reports-khata', 'read-mobileshop-sales', 'read-mobileshop-dashboard', 'read-admin-panel'])
            <a href="{{ route('mobileshop.khata') }}"
               class="nav-link {{ request()->routeIs('mobileshop.khata*') ? 'active' : '' }}"
               onclick="closeMobileSidebar()">
                <i data-lucide="book-open"></i> Customer Khata
            </a>
            @endcanany

            @canany(['read-mobileshop-repairs', 'manage-stock-repairs', 'update-mobileshop-repairs', 'read-admin-panel'])
            <a href="{{ route('mobileshop.repairs') }}"
               class="nav-link {{ request()->routeIs('mobileshop.repairs*') ? 'active' : '' }}"
               onclick="closeMobileSidebar()">
                <i data-lucide="wrench"></i> Repair Desk
            </a>
            @endcanany

            @canany(['read-mobileshop-reports', 'read-reports-financial', 'read-reports-khata', 'read-admin-panel'])
            <a href="{{ route('mobileshop.reports') }}"
               class="nav-link {{ request()->routeIs('mobileshop.reports*') ? 'active' : '' }}"
               onclick="closeMobileSidebar()">
                <i data-lucide="bar-chart-3"></i> Report
            </a>
            @endcanany

            @canany(['read-mobileshop-dashboard', 'read-mobileshop-reports', 'read-admin-panel'])
            <a href="{{ route('mobileshop.expenses.index') }}"
               class="nav-link {{ request()->routeIs('mobileshop.expenses*') ? 'active' : '' }}"
               onclick="closeMobileSidebar()">
                <i data-lucide="receipt"></i> Expenses
            </a>
            @endcanany

            @if($u && ($u->hasRole('admin') || $u->hasRole('store-admin') || $u->can('read-mobileshop-masters') || $u->can('read-admin-panel')))
            <a href="{{ route('mobileshop.masters') }}"
               class="nav-link {{ request()->routeIs('mobileshop.masters*') ? 'active' : '' }}"
               onclick="closeMobileSidebar()">
                <i data-lucide="sliders"></i> Master Option
            </a>
            @endif
        </nav>
        <div class="sidebar-footer">
            <div style="display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:8px;background:var(--color-surface-1);">
                <div class="user-avatar" style="width:30px;height:30px;font-size:11px;">
                    {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 2)) }}
                </div>
                <div style="overflow:hidden;flex:1;min-width:0;">
                    <div style="font-size:12px;font-weight:600;color:var(--color-ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ Auth::user()->name ?? 'Staff' }}</div>
                    <div style="font-size:10px;color:var(--color-ink-muted);">{{ $roleLabel }}</div>
                </div>
                <a href="{{ route('logout') }}" title="Logout" style="color:var(--color-ink-muted);text-decoration:none;padding:4px;border-radius:6px;">
                    <i data-lucide="log-out" style="width:15px;height:15px;"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="app-wrapper">
        <aside class="sidebar no-print">
            <nav class="sidebar-nav">
                @canany(['read-mobileshop-dashboard', 'read-admin-panel'])
                <a href="{{ route('mobileshop.dashboard') }}"
                   class="nav-link {{ request()->routeIs('mobileshop.dashboard') ? 'active' : '' }}">
                    <i data-lucide="layout-dashboard"></i>
                    Dashboard
                </a>
                @endcanany

                @canany(['read-mobileshop-purchase', 'create-purchase-accessories', 'create-purchase-covers', 'create-purchase-phones', 'create-purchase-secondhand', 'read-admin-panel'])
                <a href="{{ route('mobileshop.purchase') }}"
                   class="nav-link {{ request()->routeIs('mobileshop.purchase*') ? 'active' : '' }}">
                    <i data-lucide="truck"></i>
                    Purchase
                </a>
                @endcanany

                @canany(['read-mobileshop-sales', 'sell-mobileshop-accessories', 'create-sale-accessories', 'create-sale-covers', 'create-sale-phones', 'read-mobileshop-accessories', 'read-admin-panel'])
                <a href="{{ route('mobileshop.sales') }}"
                   class="nav-link {{ (request()->routeIs('mobileshop.sales*') || request()->routeIs('mobileshop.accessories.pos')) ? 'active' : '' }}">
                    <i data-lucide="shopping-cart"></i>
                    Sales
                </a>
                @endcanany

                @canany(['read-mobileshop-stock', 'manage-stock-accessories', 'manage-stock-covers', 'manage-stock-repairs', 'manage-stock-phones', 'read-admin-panel'])
                <a href="{{ route('mobileshop.stock') }}"
                   class="nav-link {{ request()->routeIs('mobileshop.stock*') ? 'active' : '' }}">
                    <i data-lucide="package"></i>
                    Stock
                </a>
                @endcanany

                @canany(['read-mobileshop-khata', 'read-reports-khata', 'read-mobileshop-sales', 'read-mobileshop-dashboard', 'read-admin-panel'])
                <a href="{{ route('mobileshop.khata') }}"
                   class="nav-link {{ request()->routeIs('mobileshop.khata*') ? 'active' : '' }}">
                    <i data-lucide="book-open"></i>
                    Customer Khata
                </a>
                @endcanany

                @canany(['read-mobileshop-repairs', 'manage-stock-repairs', 'update-mobileshop-repairs', 'read-admin-panel'])
                <a href="{{ route('mobileshop.repairs') }}"
                   class="nav-link {{ request()->routeIs('mobileshop.repairs*') ? 'active' : '' }}">
                    <i data-lucide="wrench"></i>
                    Repair Desk
                </a>
                @endcanany

                @canany(['read-mobileshop-reports', 'read-reports-financial', 'read-reports-khata', 'read-admin-panel'])
                <a href="{{ route('mobileshop.reports') }}"
                   class="nav-link {{ request()->routeIs('mobileshop.reports*') ? 'active' : '' }}">
                    <i data-lucide="bar-chart-3"></i>
                    Report
                </a>
                @endcanany

                @canany(['read-mobileshop-dashboard', 'read-mobileshop-reports', 'read-admin-panel'])
                <a href="{{ route('mobileshop.expenses.index') }}"
                   class="nav-link {{ request()->routeIs('mobileshop.expenses*') ? 'active' : '' }}">
                    <i data-lucide="receipt"></i>
                    Expenses
                </a>
                @endcanany

                @if($u && ($u->hasRole('admin') || $u->hasRole('store-admin') || $u->can('read-mobileshop-masters') || $u->can('read-admin-panel')))
                <a href="{{ route('mobileshop.masters') }}"
                   class="nav-link {{ request()->routeIs('mobileshop.masters*') ? 'active' : '' }}">
                    <i data-lucide="sliders"></i>
                    Master Option
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
                <h1 class="page-title">@yield('page-title', 'PhoneFix Azamgarh')</h1>
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
        </div>
    </nav>

    <script src="{{ asset('public/js/mobileshop/ui-utils.js') }}?v=2.5.0" defer></script>
    @stack('scripts')

    <!-- Interactive Navigation Feedback -->
    <script>
    (function () {
        var progressBar = document.getElementById('instant-page-progress');

        function resetPageTransitions() {
            if (progressBar) {
                progressBar.style.opacity = '0';
                progressBar.style.width = '0%';
            }
        }

        // Always reset loading progress on bfcache restore or history back/forward navigation
        window.addEventListener('pageshow', function () {
            resetPageTransitions();
            if (window.refreshIcons) window.refreshIcons();
            else if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons();
        });

        // Show instant progress bar when clicking internal navigation links
        document.addEventListener('click', function (e) {
            var a = e.target.closest('a');
            if (!a || !a.href || a.target === '_blank' || a.href.includes('#') || a.getAttribute('download') !== null) return;
            try {
                var target = new URL(a.href, window.location.origin);
                if (target.origin === window.location.origin && target.pathname !== window.location.pathname) {
                    if (progressBar) {
                        progressBar.style.opacity = '1';
                        progressBar.style.width = '75%';
                    }
                    setTimeout(resetPageTransitions, 2500);
                }
            } catch (err) {}
        });
    })();
    </script>
</body>
</html>
