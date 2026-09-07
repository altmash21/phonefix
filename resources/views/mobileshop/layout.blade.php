<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', 'MobiTrack — Mobile Shop ERP')</title>

    <!-- Google Fonts: Inter & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Lucide Icons (with offline fallback) -->
    <script>
        window.__lucideLoaded = false;
        window.__lucideFallback = function () {
            // If unpkg fails, inject a minimal set of SVG icons used across the app
            if (window.__lucideLoaded) return;
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
    <script src="https://unpkg.com/lucide@latest" onerror="window.__lucideLoaded = true;"></script>

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            /* ── Design Tokens: Deep Teal B2B SaaS Console ── */
            --color-primary: #0F766E;
            --color-primary-gradient: linear-gradient(90deg, #134E4A 0%, #0F766E 100%);
            --color-primary-hover: #115E59;
            --color-primary-light: #F0FDFA;

            /* Semantic Roles */
            --color-success: #16A34A; /* Inbound, paid, completed, in-stock, approved */
            --color-warning: #EA580C; /* Caution, pending, due, buyback, expiring */
            --color-info: #0F766E;    /* Same as primary — no separate info color */
            --color-danger: #DC2626;  /* Negative, voided, cancelled, debts */

            /* Neutrals */
            --color-bg: #F8FAFC;
            --color-surface: #FFFFFF;
            --color-border: #E2E8F0;
            --color-card-border: #F1F5F9;
            --color-text-primary: #0F172A;
            --color-text-secondary: #64748B;
            --color-text-muted: #94A3B8;

            /* ── Pastel accent families (lama-* tokens used by store-selector, pastel stat cards, flash messages) ── */
            --lama-sky-light: #E0F2FE;
            --lama-sky: #7DD3FC;
            --lama-sky-dark: #0369A1;
            --lama-purple-light: #F3E8FF;
            --lama-purple: #C084FC;
            --lama-purple-dark: #7E22CE;
            --lama-yellow-light: #FEF9C3;
            --lama-yellow: #FACC15;
            --lama-yellow-dark: #A16207;
            --lama-green-light: #DCFCE7;
            --lama-green: #4ADE80;
            --lama-green-dark: #15803D;
            --lama-rose-light: #FFE4E6;
            --lama-rose: #FB7185;
            --lama-rose-dark: #BE123C;

            /* Radius & Spacing */
            --radius-card: 10px;
            --radius-pill: 9999px;
            --radius-button: 8px;
            --spacing-unit: 8px;

            /* Shell Dimensions */
            --sidebar-width: 240px;
            --topbar-height: 56px;
            --bottom-nav-height: 60px;

            /* Backward compatibility aliases */
            --brand-600: var(--color-primary);
            --brand-700: var(--color-primary-hover);
            --brand-50: var(--color-primary-light);
            --brand-100: #CCFBF1;
            --brand-200: #99F6E4;
            --brand-500: #14B8A6;
            --bg-page: var(--color-bg);
            --text-primary: var(--color-text-primary);
            --text-secondary: var(--color-text-secondary);
            --text-muted: var(--color-text-muted);
            --border-color: var(--color-border);
            --card-border: var(--color-card-border);
            --radius-btn: var(--radius-button);
        }

        body {
            font-family: 'Inter', 'Plus Jakarta Sans', sans-serif;
            background: var(--color-bg);
            color: var(--color-text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            -webkit-font-smoothing: antialiased;
        }

        /* ─── TOP NAVBAR ─── */
        .topbar {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: var(--topbar-height);
            background: #ffffff;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            padding: 0 20px;
            z-index: 100;
            gap: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }

        .topbar-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            flex-shrink: 0;
        }
        .topbar-logo-icon {
            width: 38px; height: 38px;
            background: var(--brand-50);
            border: 1px solid var(--brand-100);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            color: var(--brand-600);
            transition: transform 0.2s;
        }
        .topbar-logo:hover .topbar-logo-icon {
            transform: scale(1.05);
        }
        .topbar-logo-text {
            font-size: 17px;
            font-weight: 800;
            color: #0F172A;
            letter-spacing: -0.4px;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .topbar-logo-dot {
            width: 7px; height: 7px;
            border-radius: 50%;
            background: var(--brand-600);
            display: inline-block;
        }
        .topbar-logo-sub {
            font-size: 10px;
            font-weight: 600;
            color: var(--text-secondary);
            letter-spacing: 0.4px;
        }

        .topbar-divider {
            width: 1px; height: 26px;
            background: var(--border-color);
            flex-shrink: 0;
        }

        .topbar-right {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .store-selector {
            display: flex;
            align-items: center;
            gap: 6px;
            background: var(--lama-sky-light);
            border: 1px solid var(--lama-sky);
            border-radius: 20px;
            padding: 6px 12px;
            color: var(--lama-sky-dark);
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.15s;
            text-decoration: none;
        }
        .store-selector:hover { background: #E0F2FE; }

        .user-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 4px 12px 4px 4px;
            cursor: pointer;
            transition: all 0.15s;
            position: relative;
        }
        .user-badge:hover { 
            background: var(--bg-page);
            border-color: #CBD5E1; 
        }
        .user-avatar {
            width: 32px; height: 32px;
            background: var(--color-primary-light);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: var(--color-primary);
            font-size: 12px;
            font-weight: 700;
        }
        .user-name {
            font-size: 13px;
            font-weight: 700;
            color: var(--text-primary);
        }
        .user-chevron {
            color: var(--text-muted);
            width: 14px; height: 14px;
        }

        /* User dropdown */
        .user-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            background: #fff;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            min-width: 180px;
            display: none;
            z-index: 200;
            overflow: hidden;
        }
        .user-dropdown.open { display: block; animation: fadeIn 0.15s ease-out; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .user-dropdown a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary);
            text-decoration: none;
            transition: background 0.15s;
        }
        .user-dropdown a:hover { background: var(--color-primary-light); color: var(--color-primary); }
        .user-dropdown a:last-child { color: #DC2626; border-top: 1px solid var(--card-border); }
        .user-dropdown a:last-child:hover { background: var(--lama-rose-light); }

        /* ─── LAYOUT WRAPPER ─── */
        .app-wrapper {
            display: flex;
            min-height: 100vh;
            padding-top: var(--topbar-height);
        }

        /* ─── LEFT SIDEBAR ─── */
        .sidebar {
            width: var(--sidebar-width);
            background: #ffffff;
            border-right: 1px solid var(--border-color);
            position: fixed;
            top: var(--topbar-height);
            bottom: 0;
            left: 0;
            overflow-y: auto;
            z-index: 50;
            display: flex;
            flex-direction: column;
        }

        .sidebar-nav {
            padding: 16px 12px;
            flex: 1;
        }

        .nav-section-label {
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted);
            letter-spacing: 0.6px;
            text-transform: uppercase;
            padding: 10px 12px 6px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: var(--radius-button);
            font-size: 13px;
            font-weight: 600;
            color: var(--color-text-secondary);
            text-decoration: none;
            transition: all 0.15s ease;
            margin-bottom: 4px;
            border: 1px solid transparent;
        }
        .nav-link:hover {
            background: var(--color-primary-light);
            color: var(--color-primary);
            border-color: var(--brand-200);
        }
        .nav-link:focus-visible {
            outline: 2px solid var(--color-primary);
            outline-offset: 2px;
        }
        .nav-link.active {
            background: var(--color-primary);
            color: #FFFFFF !important;
            font-weight: 700;
            border-color: var(--color-primary);
            box-shadow: 0 2px 8px rgba(15, 118, 110, 0.2);
        }
        .nav-link svg { flex-shrink: 0; width: 18px; height: 18px; color: var(--color-text-muted); transition: color 0.15s; }
        .nav-link:hover svg { color: var(--color-primary); }
        .nav-link.active svg { color: #FFFFFF !important; }

        .sidebar-context-card {
            margin: 12px 12px 6px;
            padding: 12px 14px;
            border-radius: 12px;
            background: var(--color-bg);
            border: 1px solid var(--color-border);
        }

        .sidebar-footer {
            padding: 12px;
            border-top: 1px solid var(--color-card-border);
        }

        /* ─── CONTENT AREA ─── */
        .content-area {
            margin-left: var(--sidebar-width);
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
        }

        /* ─── PAGE HEADER ─── */
        .page-header {
            background: #ffffff;
            border-bottom: 1px solid var(--color-border);
            padding: 10px 24px;
            display: flex;
            align-items: center;
            gap: 14px;
            position: sticky;
            top: var(--topbar-height);
            z-index: 40;
        }

        .page-back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px; height: 30px;
            border-radius: 8px;
            background: var(--color-bg);
            border: 1px solid var(--color-border);
            color: var(--color-text-secondary);
            text-decoration: none;
            transition: all 0.15s;
        }
        .page-back:hover { background: var(--color-primary-light); color: var(--color-primary); border-color: var(--brand-200); }

        .page-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--color-text-primary);
            letter-spacing: -0.2px;
        }

        .page-header-right {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* ─── MAIN CONTENT ─── */
        .main-content {
            padding: 14px 24px 32px;
            flex: 1;
        }

        /* ─── CARDS & KPI CARDS (Left Accent Border Stripe Pattern) ─── */
        .card {
            background: var(--color-surface);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-card);
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            transition: box-shadow 0.2s, transform 0.2s;
        }
        .card-header {
            padding: 16px 22px;
            border-bottom: 1px solid var(--color-card-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
        }
        .card-title {
            font-size: 15px;
            font-weight: 800;
            color: var(--color-text-primary);
            letter-spacing: -0.2px;
        }
        .card-subtitle {
            font-size: 12px;
            color: var(--color-text-secondary);
            font-weight: 500;
            margin-top: 2px;
        }
        .card-body { padding: 14px 24px; }

        /* KPI Card Pattern — Restrained B2B Neutral Style */
        .kpi-card, .stat-card {
            background: var(--color-surface);
            border: 1px solid var(--color-border);
            border-left: 3px solid var(--color-border);
            border-radius: var(--radius-card);
            padding: 18px 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            position: relative;
            transition: transform 0.15s, box-shadow 0.15s, border-color 0.15s;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .kpi-card:hover, .stat-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.04);
            border-left-color: var(--color-primary);
        }
        .kpi-card.kpi-success, .stat-card.stat-success,
        .kpi-card.kpi-warning, .stat-card.stat-warning,
        .kpi-card.kpi-info,    .stat-card.stat-info,
        .kpi-card.kpi-danger,  .stat-card.stat-danger,
        .kpi-card.kpi-primary, .stat-card.stat-primary,
        .kpi-card.kpi-green,   .kpi-card.kpi-orange,
        .kpi-card.kpi-blue,    .kpi-card.kpi-purple {
            border-left-color: var(--color-border);
        }
        .kpi-card.kpi-success { border-left-color: var(--color-success); }
        .kpi-card.kpi-warning { border-left-color: var(--color-warning); }
        .kpi-card.kpi-danger, .kpi-card.kpi-red { border-left-color: var(--color-danger); }
        .kpi-card.kpi-info, .kpi-card.kpi-primary { border-left-color: var(--color-primary); }
        .kpi-card.kpi-green { border-left-color: var(--color-success); }
        .kpi-card.kpi-orange { border-left-color: var(--color-warning); }
        .kpi-card.kpi-blue { border-left-color: var(--color-primary); }
        .kpi-card.kpi-purple { border-left-color: #7E22CE; }

        .kpi-card:hover, .stat-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.04);
        }
        .kpi-card:not(.kpi-success):not(.kpi-warning):not(.kpi-danger):not(.kpi-red):not(.kpi-info):not(.kpi-primary):not(.kpi-green):not(.kpi-orange):not(.kpi-blue):not(.kpi-purple):not(.kpi-green):hover,
        .stat-card:hover {
            border-left-color: var(--color-primary);
        }
        .kpi-card.kpi-green:hover,   .kpi-card.kpi-orange:hover,
        .kpi-card.kpi-blue:hover,    .kpi-card.kpi-purple:hover {
            border-left-color: var(--color-primary);
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
            margin-bottom: 24px;
        }

        .stat-card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .stat-card-tag {
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: var(--radius-pill);
            background: rgba(255,255,255,0.7);
        }
        .stat-card-label {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: var(--color-text-secondary);
        }
        .stat-card-value {
            font-size: 26px;
            font-weight: 900;
            line-height: 1.1;
            letter-spacing: -0.5px;
            color: var(--color-text-primary);
        }
        .stat-card-meta {
            font-size: 12px;
            color: var(--color-text-secondary);
            font-weight: 500;
            margin-top: 4px;
        }

        /* Circular Action Buttons Pattern */
        .action-cluster {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .action-btn-circle {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: 1px solid var(--color-border);
            background: var(--color-surface);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--color-text-secondary);
            transition: all 0.15s ease;
            text-decoration: none;
            cursor: pointer;
        }
        .action-btn-circle:hover {
            border-color: var(--color-primary);
            color: var(--color-primary);
            background: var(--color-primary-light);
            transform: scale(1.05);
        }

        /* Form Accordion Pattern */
        .accordion-section {
            border: 1px solid var(--color-border);
            border-radius: var(--radius-card);
            overflow: hidden;
            margin-bottom: 16px;
            background: var(--color-surface);
        }
        .accordion-header {
            background: #F8FAFC;
            padding: 14px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            font-weight: 700;
            font-size: 13px;
            color: var(--color-text-primary);
            border-bottom: 1px solid var(--color-border);
            user-select: none;
        }
        .accordion-body {
            padding: 20px;
        }

        /* Pastel Card Themes */
        .stat-pastel-purple {
            background: var(--lama-purple-light);
            border: 1px solid var(--lama-purple);
        }
        .stat-pastel-purple .stat-card-value { color: var(--lama-purple-dark); }
        .stat-pastel-purple .stat-card-tag { color: var(--lama-purple-dark); }

        .stat-pastel-yellow {
            background: var(--lama-yellow-light);
            border: 1px solid var(--lama-yellow);
        }
        .stat-pastel-yellow .stat-card-value { color: var(--lama-yellow-dark); }
        .stat-pastel-yellow .stat-card-tag { color: var(--lama-yellow-dark); }

        .stat-pastel-sky {
            background: var(--lama-sky-light);
            border: 1px solid var(--lama-sky);
        }
        .stat-pastel-sky .stat-card-value { color: var(--lama-sky-dark); }
        .stat-pastel-sky .stat-card-tag { color: var(--lama-sky-dark); }

        .stat-pastel-green {
            background: var(--lama-green-light);
            border: 1px solid var(--lama-green);
        }
        .stat-pastel-green .stat-card-value { color: var(--lama-green-dark); }
        .stat-pastel-green .stat-card-tag { color: var(--lama-green-dark); }

        .stat-pastel-rose {
            background: var(--lama-rose-light);
            border: 1px solid var(--lama-rose);
        }
        .stat-pastel-rose .stat-card-value { color: var(--lama-rose-dark); }
        .stat-pastel-rose .stat-card-tag { color: var(--lama-rose-dark); }

        /* ─── DATA TABLE ─── */
        .data-table-wrap {
            overflow-x: auto;
            border-radius: 0 0 var(--radius-card) var(--radius-card);
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .data-table thead tr {
            background: #F8FAFC;
        }
        .data-table thead th {
            position: sticky;
            top: 0;
            z-index: 1;
            background: #F8FAFC;
            padding: 12px 18px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            color: var(--text-secondary);
            letter-spacing: 0.4px;
            text-transform: uppercase;
            white-space: nowrap;
            border-bottom: 1px solid var(--card-border);
        }
        .data-table tbody tr {
            border-bottom: 1px solid var(--card-border);
            transition: background 0.12s;
        }
        .data-table tbody tr:last-child {
            border-bottom: none;
        }
        .data-table tbody tr:hover { background: var(--color-primary-light); }
        .data-table tbody td {
            padding: 14px 18px;
            color: var(--text-primary);
            vertical-align: middle;
        }

        /* ─── MOBITRACK GLOBAL PAGINATION STYLES ─── */
        .mobi-pagination-wrap {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            padding: 12px 16px;
            background: #FFFFFF;
            border-top: 1px solid var(--border-color, #E2E8F0);
            border-radius: 0 0 10px 10px;
        }
        .mobi-pagination-left {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .mobi-page-info {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary, #64748B);
        }
        .mobi-page-info strong {
            color: var(--text-primary, #0F172A);
            font-weight: 800;
        }
        .mobi-page-size-picker {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary, #64748B);
        }
        .mobi-page-size-select {
            padding: 3px 8px;
            font-size: 12px;
            font-weight: 700;
            color: #0F172A;
            background: #F8FAFC;
            border: 1px solid #CBD5E1;
            border-radius: 6px;
            outline: none;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .mobi-page-size-select:focus {
            border-color: var(--brand-500, #14B8A6);
            box-shadow: 0 0 0 2px rgba(20, 184, 166, 0.2);
        }
        .mobi-pagination-right {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .mobi-pagination-nav {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .mobi-page-nav-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            min-width: 32px;
            height: 32px;
            padding: 0 10px;
            font-size: 12px;
            font-weight: 700;
            color: #334155;
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.15s ease;
            user-select: none;
        }
        .mobi-page-nav-btn:hover:not(:disabled) {
            background: #F1F5F9;
            color: #0F172A;
            border-color: #CBD5E1;
        }
        .mobi-page-nav-btn:disabled {
            opacity: 0.45;
            cursor: not-allowed;
            background: #F8FAFC;
        }
        .mobi-page-numbers {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .mobi-page-num-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 32px;
            height: 32px;
            padding: 0 6px;
            font-size: 12px;
            font-weight: 700;
            color: #334155;
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.15s ease;
            user-select: none;
        }
        .mobi-page-num-btn:hover:not(.active) {
            background: #F1F5F9;
            color: #0F172A;
            border-color: #CBD5E1;
        }
        .mobi-page-num-btn.active {
            background: var(--brand-700, #0F766E);
            color: #FFFFFF;
            border-color: var(--brand-700, #0F766E);
            font-weight: 800;
        }
        .mobi-page-ellipsis {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 24px;
            height: 32px;
            font-size: 12px;
            font-weight: 700;
            color: #94A3B8;
        }
        @media (max-width: 640px) {
            .mobi-pagination-wrap {
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
                padding: 10px 12px;
            }
            .mobi-pagination-left {
                justify-content: space-between;
            }
            .mobi-pagination-right {
                justify-content: center;
            }
        }

        /* ─── SEARCH & FILTER BAR ─── */
        .search-bar {
            display: flex;
            align-items: center;
            gap: 8px;
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 8px 14px;
            background: #fff;
            transition: border-color 0.15s;
        }
        .search-bar:focus-within {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 2px var(--brand-100);
        }
        .search-bar input {
            border: none;
            outline: none;
            font-size: 13px;
            color: var(--text-primary);
            width: 220px;
            font-family: inherit;
        }
        .search-bar input:focus-visible {
            outline: none;
        }
        .search-bar input::-webkit-search-decoration,
        .search-bar input::-webkit-search-cancel-button,
        .search-bar input::-webkit-search-results-button,
        .search-bar input::-webkit-search-results-decoration {
            -webkit-appearance: none;
        }
        .search-bar svg { color: var(--text-muted); }

        /* ─── BUTTONS ─── */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 9px 16px;
            border-radius: var(--radius-btn);
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.15s;
            border: none;
            text-decoration: none;
            white-space: nowrap;
        }
        .btn-primary {
            background: var(--color-primary);
            color: #fff;
            box-shadow: 0 1px 3px rgba(15, 118, 110, 0.15);
        }
        .btn-primary:hover:not(:disabled) { background: var(--color-primary-hover); transform: translateY(-1px); }
        .btn-primary:disabled {
            background: #94A3B8;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        .btn-primary.btn-loading {
            position: relative;
            color: transparent;
        }
        .btn-primary.btn-loading::after {
            content: '';
            position: absolute;
            width: 14px;
            height: 14px;
            border: 2px solid rgba(255,255,255,0.4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: btn-spinner 0.6s linear infinite;
        }
        @keyframes btn-spinner {
            to { transform: rotate(360deg); }
        }
        .btn-outline:disabled {
            background: #F1F5F9;
            color: #94A3B8;
            cursor: not-allowed;
            border-color: #E2E8F0;
        }
        .btn-outline {
            background: #fff;
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }
        .btn-outline:hover { background: var(--bg-page); border-color: #CBD5E1; }
        .btn-sm { padding: 6px 12px; font-size: 12px; border-radius: 8px; }
        .btn-icon {
            width: 32px; height: 32px;
            padding: 0;
            display: inline-flex; align-items: center; justify-content: center;
            border-radius: 50%;
            border: 1px solid var(--border-color);
            background: #fff;
            cursor: pointer;
            color: var(--text-secondary);
            transition: all 0.15s;
        }

        /* ─── FILTER BAR & PILLS ─── */
        .filter-bar {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            padding: 12px 20px;
            background: #F8FAFC;
            border-bottom: 1px solid var(--card-border);
        }
        .filter-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            color: var(--text-secondary);
            background: #ffffff;
            border: 1px solid var(--border-color);
            cursor: pointer;
            transition: all 0.15s ease;
            user-select: none;
            text-decoration: none;
        }
        .filter-pill:hover {
            border-color: var(--brand-200);
            color: var(--color-primary);
            background: var(--color-primary-light);
        }
        .filter-pill:focus-visible {
            outline: 2px solid var(--color-primary);
            outline-offset: 2px;
        }
            border: 1px solid var(--border-color);
            cursor: pointer;
            transition: all 0.15s ease;
            user-select: none;
            text-decoration: none;
        }
        .filter-pill:hover {
            border-color: var(--brand-200);
            color: var(--color-primary);
            background: var(--color-primary-light);
        }
        .filter-pill.active {
            background: var(--color-primary);
            color: #ffffff;
            border-color: var(--color-primary);
            box-shadow: 0 2px 6px rgba(15, 118, 110, 0.2);
        }
        .filter-pill.active .pill-count {
            background: rgba(255, 255, 255, 0.25);
            color: #ffffff;
        }
        .filter-pill-danger {
            border-color: #FECDD3;
            color: #BE123C;
            background: #FFF1F2;
        }
        .filter-pill-danger:hover {
            background: #FFE4E6;
            border-color: #FDA4AF;
            color: #9F1239;
        }
        .filter-pill-danger.active {
            background: #E11D48;
            color: #ffffff;
            border-color: #E11D48;
            box-shadow: 0 2px 6px rgba(225, 29, 72, 0.25);
        }
        .filter-pill-warning {
            border-color: #FEF08A;
            color: #854D0E;
            background: #FEFCE8;
        }
        .filter-pill-warning.active {
            background: #CA8A04;
            color: #ffffff;
            border-color: #CA8A04;
        }
        .filter-pill-success {
            border-color: #BBF7D0;
            color: #15803D;
            background: #F0FDF4;
        }
        .filter-pill-success.active {
            background: #16A34A;
            color: #ffffff;
            border-color: #16A34A;
        }
        .pill-count {
            font-size: 10px;
            font-weight: 800;
            padding: 2px 6px;
            border-radius: 10px;
            background: var(--bg-page);
            color: var(--text-primary);
        }
        .btn-icon:hover { background: var(--color-primary-light); color: var(--color-primary); border-color: var(--brand-200); }
        .btn-icon svg { width: 14px; height: 14px; }

        /* ─── STATUS PILL BADGES ─── */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.2px;
        }
        .badge-green { background: #DCFCE7; color: #15803D; border: 1px solid #BBF7D0; }
        .badge-orange { background: #FEF3C7; color: #B45309; border: 1px solid #FDE68A; }
        .badge-red { background: #FEE2E2; color: #B91C1C; border: 1px solid #FECACA; }
        .badge-purple { background: var(--color-primary-light); color: var(--color-primary-hover); border: 1px solid var(--brand-200); }
        .badge-blue { background: #F0FDFA; color: #0F766E; border: 1px solid #CCFBF1; }
        .badge-gray { background: #F1F5F9; color: var(--text-secondary); border: 1px solid var(--border-color); }

        /* ─── FORM ELEMENTS ─── */
        .form-group { margin-bottom: 18px; }
        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 6px;
        }
        .form-label.required::after {
            content: ' *';
            color: #EF4444;
        }
        .form-control {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            font-size: 13px;
            color: var(--text-primary);
            font-family: inherit;
            transition: all 0.15s;
            outline: none;
            background: #fff;
        }
        .form-control:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px var(--brand-100);
        }
        .form-control::placeholder { color: var(--text-muted); }
        select.form-control { appearance: none; cursor: pointer; }

        /* Form validation visual feedback */
        .form-control.is-invalid {
            border-color: var(--color-danger);
            background-color: #FFF5F5;
        }
        .form-control.is-invalid:focus {
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.15);
            border-color: var(--color-danger);
        }
        .form-control.is-valid {
            border-color: var(--color-success);
            background-color: #F0FDF4;
        }
        .form-control.is-valid:focus {
            box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.15);
            border-color: var(--color-success);
        }
        .invalid-feedback {
            color: var(--color-danger);
            font-size: 12px;
            font-weight: 600;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .invalid-feedback::before {
            content: '⚠';
            font-size: 11px;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
        }

        .page-view-select {
            padding: 7px 30px 7px 12px;
            border: 1px solid var(--border-color);
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            color: var(--text-primary);
            appearance: none;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%2364748B' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E") right 10px center no-repeat #fff;
            cursor: pointer;
            font-family: inherit;
        }

        /* ─── FLASH MESSAGES ─── */
        .flash-success, .flash-error {
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 13px;
            font-weight: 700;
        }
        .flash-success { background: var(--lama-green-light); color: var(--lama-green-dark); border-bottom: 1px solid var(--lama-green); }
        .flash-error { background: var(--lama-rose-light); color: var(--lama-rose-dark); border-bottom: 1px solid var(--lama-rose); }

        /* ─── RESPONSIVE & MOBILE BOTTOM NAVBAR ─── */
        .mobile-bottom-nav {
            display: none;
            position: fixed;
            bottom: 0; left: 0; right: 0;
            height: var(--bottom-nav-height);
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-top: 1px solid var(--border-color);
            z-index: 1000;
            padding: 4px 8px;
            box-shadow: 0 -4px 16px rgba(0,0,0,0.04);
        }

        .mobile-nav-items {
            display: flex;
            align-items: center;
            justify-content: space-around;
            width: 100%;
            height: 100%;
        }

        .mobile-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 3px;
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.15s;
            flex: 1;
            max-width: 75px;
            text-align: center;
        }
        .mobile-nav-item span {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 60px;
        }
        .mobile-nav-item.active {
            color: var(--color-primary);
            background: var(--color-primary-light);
            font-weight: 700;
        }
        .mobile-nav-item svg {
            width: 20px; height: 20px;
            color: var(--text-secondary);
            transition: transform 0.15s;
        }
        .mobile-nav-item.active svg {
            color: var(--color-primary);
            transform: translateY(-1px);
        }

        @media (max-width: 900px) {
            :root {
                --topbar-height: 48px;
                --bottom-nav-height: 52px;
            }
            .sidebar { display: none; }
            .content-area { margin-left: 0; width: 100%; }
            .page-header {
                display: flex !important;
                padding: 8px 12px !important;
                gap: 8px !important;
                flex-wrap: wrap !important;
                align-items: center !important;
                position: static !important;
                background: #ffffff !important;
                border-bottom: 1px solid var(--color-border) !important;
            }
            .page-header.no-actions:not(:has(.page-back)):not(:has(.page-title)) {
                display: none !important;
            }
            .page-title {
                font-size: 14px !important;
                font-weight: 700 !important;
            }
            .page-header-right {
                margin-left: auto;
                display: flex;
                flex-wrap: wrap;
                gap: 6px;
                align-items: center;
            }
            .main-content { padding: 8px 10px calc(var(--bottom-nav-height) + 14px) !important; }
            .mobile-bottom-nav { display: flex; }
            .topbar-subtitle { display: none; }
            .search-bar input { width: 140px; }
        }

        @media (max-width: 380px) {
            .mobile-nav-item span { display: none; }
            .mobile-nav-item { max-width: 44px !important; padding: 6px 2px !important; }
        }

        @media (max-width: 767px) {
            :root {
                --topbar-height: 48px;
                --bottom-nav-height: 50px;
            }
            .page-header {
                display: flex !important;
                padding: 8px 10px !important;
                gap: 8px !important;
                flex-wrap: wrap !important;
                align-items: center !important;
                position: static !important;
                background: #ffffff !important;
                border-bottom: 1px solid var(--color-border) !important;
            }
            .page-header.no-actions:not(:has(.page-back)):not(:has(.page-title)) {
                display: none !important;
            }
            .page-title {
                font-size: 13px !important;
            }
            .page-header-right {
                width: 100%;
                display: flex;
                flex-wrap: wrap;
                gap: 6px;
                align-items: center;
            }
            .page-header-right .btn {
                font-size: 11px !important;
                padding: 5px 10px !important;
            }
            .mobile-bottom-nav {
                height: 50px !important;
                padding: 2px 6px !important;
            }
            .mobile-nav-item {
                font-size: 10px !important;
                gap: 2px !important;
                padding: 4px 6px !important;
            }
            .mobile-nav-item svg {
                width: 18px !important;
                height: 18px !important;
            }
            .main-content {
                padding: 6px 8px calc(var(--bottom-nav-height) + 12px) !important;
            }
        }

        @media (max-width: 600px) {
            :root {
                --topbar-height: 46px;
                --bottom-nav-height: 48px;
            }
            .topbar { padding: 0 8px; gap: 6px; }
            .topbar-store-name { display: none; }
            .topbar-store-pill { padding: 3px 6px !important; font-size: 11px !important; }
            .user-name { display: none; }
            .user-badge { padding: 2px 4px !important; gap: 3px; }
            .page-header {
                display: flex !important;
                padding: 6px 8px !important;
                gap: 6px !important;
                flex-wrap: wrap !important;
                position: static !important;
                background: #ffffff !important;
                border-bottom: 1px solid var(--color-border) !important;
            }
            .page-header.no-actions:not(:has(.page-back)):not(:has(.page-title)) {
                display: none !important;
            }
            .page-title {
                font-size: 12px !important;
            }
            .page-header-right {
                width: 100%;
                display: flex;
                flex-wrap: wrap;
                gap: 5px;
            }
            .page-header-right .btn {
                font-size: 11px !important;
                padding: 5px 8px !important;
            }
            .main-content { padding: 4px 6px calc(var(--bottom-nav-height) + 10px) !important; }
        }

        .kpi-grid, .kpi-row {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .kpi-grid .kpi-card, .kpi-row .kpi-card {
            margin: 0;
            background: #fff;
            border: 1px solid var(--color-border);
            border-left: 3px solid var(--color-border);
            border-radius: 10px;
            padding: 14px 16px;
            transition: border-color 0.15s, transform 0.15s;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .kpi-grid .kpi-card:hover, .kpi-row .kpi-card:hover {
            border-left-color: var(--color-primary);
            transform: translateY(-1px);
        }
        .kpi-grid .kpi-label, .kpi-row .kpi-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--color-text-secondary);
            letter-spacing: 0.2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .kpi-grid .kpi-num, .kpi-row .kpi-num {
            font-size: 22px;
            font-weight: 700;
            color: var(--color-text-primary);
            margin-top: 4px;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .kpi-grid .kpi-sub, .kpi-row .kpi-sub {
            font-size: 11px;
            color: var(--color-text-muted);
            margin-top: 3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        @media (max-width: 600px) {
            .stat-grid { grid-template-columns: 1fr !important; }
            .kpi-grid, .kpi-row {
                display: grid !important;
                grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
                gap: 4px !important;
                margin-bottom: 8px !important;
            }
            .kpi-grid .kpi-card, .kpi-row .kpi-card {
                padding: 6px 4px !important;
                min-height: unset !important;
                border-radius: 6px !important;
                border-left-width: 2px !important;
                margin: 0 !important;
                text-align: center !important;
            }
            .kpi-grid .kpi-label, .kpi-row .kpi-label {
                font-size: 8.5px !important;
                font-weight: 600 !important;
                color: var(--color-text-secondary) !important;
                margin: 0 !important;
                line-height: 1.15 !important;
                white-space: nowrap !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
                text-transform: none !important;
            }
            .kpi-grid .kpi-num, .kpi-row .kpi-num {
                font-size: 11px !important;
                font-weight: 700 !important;
                margin-top: 2px !important;
                margin-bottom: 1px !important;
                line-height: 1.1 !important;
                white-space: nowrap !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
                letter-spacing: -0.3px !important;
            }
            .kpi-grid .kpi-sub, .kpi-row .kpi-sub {
                font-size: 7.5px !important;
                margin: 0 !important;
                line-height: 1.1 !important;
                color: #94A3B8 !important;
                white-space: nowrap !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
            }
            .card { overflow: hidden; max-width: 100%; }
            .card-header { padding: 12px 14px; flex-wrap: wrap; gap: 8px; }
            .card-body { padding: 14px; }
            .data-table th, .data-table td { padding: 9px 10px; font-size: 12px; }
            .filter-bar { padding: 8px 12px; gap: 6px; }
            .filter-pill { font-size: 11px; padding: 5px 10px; }
        }

        /* ══════════════════════════════════════════════════════════ */
        /* UNIVERSAL PRINT ENGINE (PDF & Paper Output Optimization)   */
        /* ══════════════════════════════════════════════════════════ */
        .printable-invoice-container {
            width: 100%;
            max-width: 100%;
            margin: 0;
            background: #ffffff;
            border: 1px solid var(--color-border);
            border-radius: var(--radius-card);
            padding: 24px 28px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            color: #0F172A;
        }
        .bill-table {
            width: 100%;
            border-collapse: collapse;
        }
        .bill-table th, .bill-table td {
            border: 1px solid #E2E8F0;
            padding: 9px 12px;
            font-size: 12px;
        }
        .bill-table th {
            background-color: #F8FAFC;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.3px;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 8mm 10mm 8mm 10mm;
            }
            *, *::before, *::after {
                box-shadow: none !important;
                text-shadow: none !important;
            }
            html, body {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
                color: #0f172a !important;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
                font-size: 11.5px !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .app-container, .content-area, .main-content {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                float: none !important;
                border: none !important;
                background: #ffffff !important;
            }
            .topbar, .sidebar, .page-header, .page-actions, .mobile-bottom-nav,
            .flash-success, .flash-error, .btn, button, .no-print, nav, aside,
            .notification-bell, .user-badge, .mobile-quick-actions, .app-layout, .main-wrapper,
            .statement-page-wrapper, .invoice-page-wrapper {
                display: none !important;
            }
            .card {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
                background: transparent !important;
                max-width: 100% !important;
            }
            .printable-bill-wrapper, .printable-invoice-container {
                border: 1.5px solid #000000 !important;
                box-shadow: none !important;
                padding: 10mm 12mm !important;
                margin: 0 auto !important;
                width: 100% !important;
                max-width: 210mm !important;
                border-radius: 0 !important;
            }
            .bill-table th, .bill-table td {
                border: 1px solid #000000 !important;
                color: #000000 !important;
                padding: 6px 8px !important;
            }
            .bill-table th {
                background-color: #f1f5f9 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            #viewThermal { display: none !important; }
            body.thermal-mode { width: 80mm !important; }
            body.thermal-mode .printable-invoice-container { display: none !important; }
            body.thermal-mode #viewThermal { display: block !important; width: 100% !important; margin: 0 !important; padding: 0 !important; border: none !important; box-shadow: none !important; }
            table { page-break-inside: auto !important; }
            tr { page-break-inside: avoid !important; page-break-after: auto !important; }
            thead { display: table-header-group !important; }
            tfoot { display: table-footer-group !important; }
        }

        /* ══════════════════════════════════════════════════════════ */
        /* SHARED MOBILE-FIRST COMPONENTS (Stat Strip, FAB, Flat Rows)*/
        /* ══════════════════════════════════════════════════════════ */

        /* 1. Mobile Stat Strip */
        .mobile-stat-strip {
            display: none;
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 10px;
            padding: 8px 6px;
            margin-bottom: 10px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.02);
            flex-direction: row;
            justify-content: space-around;
            align-items: center;
        }
        .stat-strip-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1px;
            flex: 1;
            text-align: center;
        }
        .stat-strip-item .stat-label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #94A3B8;
        }
        .stat-strip-item .stat-val {
            font-size: 14px;
            font-weight: 800;
            color: #0F172A;
            letter-spacing: -0.3px;
            line-height: 1.2;
        }
        .stat-divider {
            width: 1px;
            height: 24px;
            background: #E2E8F0;
            flex-shrink: 0;
        }

        /* 2. Horizontal Filter Rails */
        .pills-scroll-rail, .date-pills-scroll-rail, .purchase-pills-rail {
            display: flex;
            align-items: center;
            gap: 6px;
            overflow-x: auto;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            -ms-overflow-style: none;
            padding: 2px 0;
        }
        .pills-scroll-rail::-webkit-scrollbar,
        .date-pills-scroll-rail::-webkit-scrollbar,
        .purchase-pills-rail::-webkit-scrollbar {
            display: none;
        }
        .sales-date-pill, .purchase-pill, .filter-pill-btn {
            padding: 6px 12px !important;
            font-size: 11.5px !important;
            font-weight: 700 !important;
            border-radius: 8px !important;
            border: 1px solid transparent !important;
            background: transparent;
            color: #475569;
            cursor: pointer;
            min-height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s ease;
            flex-shrink: 0;
        }
        .sales-date-pill:hover, .purchase-pill:hover, .filter-pill-btn:hover {
            background: #E2E8F0;
            color: #0F172A;
        }
        .sales-date-pill.active, .purchase-pill.active, .filter-pill-btn.active {
            background: #0F766E !important;
            color: #FFFFFF !important;
            font-weight: 800 !important;
            box-shadow: 0 2px 4px rgba(15,118,110,0.25);
        }

        /* 3. Mobile Flat Rows (Zero Depth, No Card-in-Card) */
        .mobile-sales-cards, .mobile-purchase-cards {
            display: none;
        }
        .sales-flat-row, .purchase-flat-row, .app-flat-row {
            padding: 10px 12px;
            border-bottom: 1px solid #F1F5F9;
            display: flex;
            flex-direction: column;
            gap: 3px;
            background: #FFFFFF;
            transition: background 0.1s ease;
        }
        .sales-flat-row:last-child, .purchase-flat-row:last-child, .app-flat-row:last-child {
            border-bottom: none;
        }
        .sales-flat-row:active, .purchase-flat-row:active, .app-flat-row:active {
            background: #F8FAFC;
        }
        .row-line1 {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .row-line1 .inv-num {
            font-size: 12px;
            font-weight: 700;
            font-family: monospace;
            color: #0F766E;
            text-decoration: none;
            flex-shrink: 0;
        }
        .row-line1 .pay-badge {
            font-size: 9.5px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 1px 6px;
            border-radius: 4px;
            letter-spacing: 0.2px;
        }
        .row-line1 .row-amount {
            margin-left: auto;
            font-size: 14.5px;
            font-weight: 800;
            color: #0F172A;
            letter-spacing: -0.2px;
        }
        .row-line2 {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }
        .row-line2 .cust-name {
            font-size: 12.5px;
            font-weight: 600;
            color: #0F172A;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .row-line2 .cust-phone {
            font-size: 11px;
            color: #64748B;
            font-family: monospace;
            font-weight: 500;
            flex-shrink: 0;
        }
        .row-line3 {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-top: 1px;
        }
        .row-line3 .items-summary {
            font-size: 11px;
            color: #64748B;
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .row-line3 .row-actions {
            display: flex;
            align-items: center;
            gap: 5px;
            flex-shrink: 0;
        }
        .compact-action-btn, .mobile-action-icon-btn {
            width: 32px;
            height: 32px;
            border-radius: 7px;
            border: 1px solid #CBD5E1;
            background: #F8FAFC;
            color: #475569;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            text-decoration: none;
            transition: transform 0.1s, opacity 0.1s;
        }
        .compact-action-btn:active, .mobile-action-icon-btn:active {
            transform: scale(0.93);
            opacity: 0.85;
        }
        .mobile-whatsapp-btn {
            background: #F0FDF4;
            color: #16A34A;
            border: 1px solid #BBF7D0;
        }
        .mobile-whatsapp-btn:active {
            background: #DCFCE7;
        }
        .mobile-print-btn {
            background: #F8FAFC;
            color: #0F172A;
            border: 1px solid #CBD5E1;
        }
        .mobile-print-btn:active {
            background: #E2E8F0;
        }
        .mobile-return-btn {
            background: #FFF1F2;
            color: #DC2626;
            border: 1px solid #FECDD3;
        }
        .mobile-return-btn:active {
            background: #FEE2E2;
        }

        /* 4. Floating Action Button (FAB) */
        .mobile-fab-container {
            display: none;
            position: fixed;
            bottom: 68px;
            right: 14px;
            z-index: 995;
        }
        .btn-app-fab, .btn-sales-fab, .btn-purchase-fab {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0F766E, #059669);
            color: #FFFFFF;
            border: none;
            box-shadow: 0 6px 20px rgba(15, 118, 110, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-app-fab:active, .btn-sales-fab:active, .btn-purchase-fab:active {
            transform: scale(0.92);
        }
        .btn-app-fab svg, .btn-sales-fab svg, .btn-purchase-fab svg {
            width: 22px;
            height: 22px;
        }
        .fab-dropup-menu {
            position: absolute;
            bottom: 58px;
            right: 0;
            width: 215px;
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            box-shadow: 0 14px 32px rgba(15, 23, 42, 0.18);
            overflow: hidden;
            padding: 5px;
            display: flex;
            flex-direction: column;
            gap: 3px;
            animation: fabSlideUp 0.18s ease-out;
        }
        @keyframes fabSlideUp {
            from { opacity: 0; transform: translateY(10px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        .fab-menu-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            min-height: 40px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            border: none;
            background: transparent;
            cursor: pointer;
            text-align: left;
            width: 100%;
            transition: background 0.15s;
        }
        .fab-menu-item:hover, .fab-menu-item:active {
            background: #F1F5F9;
        }

        /* 5. Embedded Search Filter Controls */
        .mobile-filter-btn {
            display: none;
            height: 30px;
            width: 32px;
            padding: 0;
            border-radius: 6px;
            background: #FFFFFF;
            border: 1px solid #CBD5E1;
            color: #0F766E;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            position: relative;
            transition: all 0.15s ease;
        }
        .mobile-filter-btn:active {
            background: #F1F5F9;
            transform: scale(0.95);
        }
        .app-search-wrapper, .sales-search-wrapper, .purchase-search-wrapper {
            width: 280px;
        }
        .app-search-box, .sales-search-box, .purchase-search-box {
            width: 100%;
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

    <header class="topbar">
        <a href="{{ route('mobileshop.dashboard') }}" class="topbar-logo" style="text-decoration:none; display:flex; align-items:center; gap:10px;">
            <div style="width:34px; height:34px; border-radius:8px; background:var(--color-primary); color:#fff; display:flex; align-items:center; justify-content:center;">
                <i data-lucide="smartphone" style="width:18px;height:18px;"></i>
            </div>
            <div>
                <div style="font-size:16px; font-weight:700; color:#0F172A; letter-spacing:-0.3px;">MobiTrack</div>
                <div style="font-size:10px; font-weight:500; color:var(--text-secondary); letter-spacing:0.2px;" class="topbar-subtitle">Retail & Service Console</div>
            </div>
        </a>

        <div class="topbar-right" style="margin-left:auto; display:flex; align-items:center; gap:10px;">
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

            <!-- Store selector -->
            <div class="topbar-store-pill" style="background:var(--color-bg); border:1px solid var(--color-border); border-radius:6px; padding:5px 12px; color:var(--text-secondary); font-size:12px; font-weight:600; display:flex; align-items:center; gap:6px; cursor:pointer;">
                <i data-lucide="building-2" style="width:13px;height:13px;"></i>
                <span class="topbar-store-name">Main Mobile Store</span>
                <i data-lucide="chevron-down" style="width:12px;height:12px; opacity:0.6;"></i>
            </div>

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
        <aside class="sidebar">
            <nav class="sidebar-nav">
                    {{-- ════ UNIFIED SIDEBAR — Same 5 items for all roles, @can gated ════ --}}
                @can('read-mobileshop-dashboard')
                <div class="nav-section-label">Overview</div>
                <a href="{{ route('mobileshop.dashboard') }}"
                   class="nav-link {{ request()->routeIs('mobileshop.dashboard') ? 'active' : '' }}">
                    <i data-lucide="layout-dashboard"></i>
                    Dashboard
                </a>
                @endcan

                @can('read-mobileshop-purchase')
                <div class="nav-section-label" style="margin-top:14px;">Purchase</div>
                <a href="{{ route('mobileshop.purchase') }}"
                   class="nav-link {{ request()->routeIs('mobileshop.purchase*') ? 'active' : '' }}">
                    <i data-lucide="truck"></i>
                    Purchase
                </a>
                @endcan

                @can('read-mobileshop-sales')
                <div class="nav-section-label" style="margin-top:14px;">Sales</div>
                <a href="{{ route('mobileshop.sales') }}"
                   class="nav-link {{ request()->routeIs('mobileshop.sales*') ? 'active' : '' }}">
                    <i data-lucide="trending-up"></i>
                    Sales
                </a>
                @endcan

                @can('read-mobileshop-stock')
                <div class="nav-section-label" style="margin-top:14px;">Inventory</div>
                <a href="{{ route('mobileshop.stock') }}"
                   class="nav-link {{ request()->routeIs('mobileshop.stock*') ? 'active' : '' }}">
                    <i data-lucide="package"></i>
                    Stock
                </a>
                @endcan

                <div class="nav-section-label" style="margin-top:14px;">Credit & Khata</div>
                <a href="{{ route('mobileshop.khata') }}"
                   class="nav-link {{ request()->routeIs('mobileshop.khata*') ? 'active' : '' }}">
                    <i data-lucide="book-open"></i>
                    Customer Khata
                </a>

                @canany(['read-mobileshop-reports', 'read-reports-financial', 'read-reports-khata'])
                <div class="nav-section-label" style="margin-top:14px;">Analytics</div>
                <a href="{{ route('mobileshop.reports') }}"
                   class="nav-link {{ request()->routeIs('mobileshop.reports*') ? 'active' : '' }}">
                    <i data-lucide="bar-chart-3"></i>
                    Reports
                </a>
                @endcanany

                @if($u && ($u->hasRole('admin') || $u->hasRole('store-admin')))
                <div class="nav-section-label" style="margin-top:14px;">Administration</div>
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
            <div class="page-header {{ empty(trim($__env->yieldContent('page-actions'))) ? 'no-actions' : '' }}">
                @hasSection('back-url')
                    <a href="@yield('back-url')" class="page-back">
                        <i data-lucide="arrow-left" style="width:16px;height:16px;"></i>
                    </a>
                @endif
                <h1 class="page-title">@yield('page-title', 'MobiTrack')</h1>
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
    <nav class="mobile-bottom-nav">
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

    <script src="{{ asset('js/mobileshop/ui-utils.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
