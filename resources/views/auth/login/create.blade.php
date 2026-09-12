<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login — Maurya Mobile ERP</title>
    <meta name="description" content="Secure terminal access for Maurya Mobile Mobile Shop ERP staff and store administration.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <base href="{{ config('app.url') . '/' }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Outfit:wght@600;700;800;900&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Tailwind CSS CDN -->
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
        .gradient-panel {
            background: linear-gradient(145deg, #021E1D 0%, #042F2E 60%, #0F766E 100%);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.12);
        }
        .btn-teal {
            background: #0F766E;
        }
        .btn-teal:hover {
            background: #115E59;
        }
    </style>
</head>
<body class="bg-slate-950 font-sans text-slate-800 antialiased min-h-screen flex items-center justify-center p-4 sm:p-6 lg:p-8">

    <!-- Main Container (Split Screen On Desktop) -->
    <div class="relative z-10 w-full max-w-5xl rounded-2xl overflow-hidden shadow-2xl border border-slate-800 bg-white grid grid-cols-1 lg:grid-cols-12 min-h-[600px]">

        <!-- ════ LEFT PANEL: BRANDING & ROLE STATIONS (5 Cols) ════ -->
        <div class="lg:col-span-5 gradient-panel text-white p-8 sm:p-10 flex flex-col justify-between relative overflow-hidden">
            <div class="space-y-6 relative z-10">
                <!-- Top Brand Header -->
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white text-brand-700 flex items-center justify-center shadow-lg shadow-black/20 font-bold">
                        <i data-lucide="smartphone" class="w-5 h-5 text-brand-700"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="font-display font-bold text-2xl text-white tracking-tight">Maurya Mobile</span>
                        </div>
                        <span class="text-[10px] text-teal-300 font-semibold uppercase tracking-wider">Enterprise Retail ERP</span>
                    </div>
                </div>

                <div>
                    <h2 class="font-display font-semibold text-lg text-white leading-snug">
                        Multi-Counter Staff Terminal & Management Portal
                    </h2>
                    <p class="text-xs text-slate-300 mt-2 leading-relaxed">
                        Role-based access terminal with isolated counter inventories, sequential GST billing, and live diagnostic tracking.
                    </p>
                </div>

                <!-- 6 Counter Terminals Overview -->
                <div class="space-y-2 pt-2">
                    <span class="text-[10px] uppercase font-semibold text-teal-300 tracking-wider block">Authorized Stations:</span>
                    <div class="grid grid-cols-2 gap-2 text-[11px]">
                        <div class="glass-card px-2.5 py-2 rounded-lg flex items-center gap-2">
                            <i data-lucide="shield-check" class="w-3.5 h-3.5 text-teal-300"></i>
                            <span class="font-medium text-white">Store Admin</span>
                        </div>
                        <div class="glass-card px-2.5 py-2 rounded-lg flex items-center gap-2">
                            <i data-lucide="smartphone" class="w-3.5 h-3.5 text-teal-300"></i>
                            <span class="font-medium text-white">New Phones POS</span>
                        </div>
                        <div class="glass-card px-2.5 py-2 rounded-lg flex items-center gap-2">
                            <i data-lucide="repeat" class="w-3.5 h-3.5 text-teal-300"></i>
                            <span class="font-medium text-white">Buyback Desk</span>
                        </div>
                        <div class="glass-card px-2.5 py-2 rounded-lg flex items-center gap-2">
                            <i data-lucide="headphones" class="w-3.5 h-3.5 text-teal-300"></i>
                            <span class="font-medium text-white">Accessories</span>
                        </div>
                        <div class="glass-card px-2.5 py-2 rounded-lg flex items-center gap-2">
                            <i data-lucide="shield" class="w-3.5 h-3.5 text-teal-300"></i>
                            <span class="font-medium text-white">Cover & Glass</span>
                        </div>
                        <div class="glass-card px-2.5 py-2 rounded-lg flex items-center gap-2">
                            <i data-lucide="wrench" class="w-3.5 h-3.5 text-teal-300"></i>
                            <span class="font-medium text-white">Service Tech</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Back link -->
            <div class="pt-6 border-t border-white/10 relative z-10 flex items-center justify-between text-xs text-slate-300">
                <a href="{{ route('public.landing') }}" class="hover:text-white font-medium inline-flex items-center gap-1.5 transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Back to Storefront</span>
                </a>
                <span class="text-slate-400">v3.2 ERP</span>
            </div>
        </div>

        <!-- ════ RIGHT PANEL: AUTH FORMS (7 Cols) ════ -->
        <div class="lg:col-span-7 p-6 sm:p-10 flex flex-col justify-center bg-white">
            <div class="max-w-md w-full mx-auto space-y-5">

                <!-- Tab Switcher: Sign In vs Register with Invite Code -->
                <div class="flex items-center p-1 bg-slate-100 rounded-xl border border-slate-200">
                    <button type="button" id="tabSignInBtn" onclick="switchAuthTab('signin')"
                            class="flex-1 py-2 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1.5 bg-white text-slate-900 shadow-sm">
                        <i data-lucide="log-in" class="w-3.5 h-3.5 text-brand-600"></i>
                        <span>Staff Sign In</span>
                    </button>
                    <button type="button" id="tabRegisterBtn" onclick="switchAuthTab('register')"
                            class="flex-1 py-2 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1.5 text-slate-500 hover:text-slate-900">
                        <i data-lucide="ticket" class="w-3.5 h-3.5 text-brand-600"></i>
                        <span>Register with Code</span>
                    </button>
                </div>

                <!-- ══════════════════════════════════════════════════
                     PANEL 1: STAFF SIGN IN
                     ══════════════════════════════════════════════════ -->
                <div id="panelSignIn" class="space-y-5">
                    <!-- Header -->
                    <div>
                        <h1 class="font-display font-black text-2xl text-slate-900">Sign In to Terminal</h1>
                        <p class="text-xs text-slate-500 mt-1">Select your staff station or enter your registered store email.</p>
                    </div>

                    <!-- Alert Messages -->
                    @if (session('success'))
                        <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
                            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif

                    <div id="login-error-alert" class="hidden p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center gap-2">
                        <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 shrink-0"></i>
                        <span id="login-error-text">Invalid login credentials.</span>
                    </div>

                    <div id="login-success-alert" class="hidden p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
                        <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                        <span id="login-success-text">Authorized! Redirecting to station...</span>
                    </div>

                    @php
                        $showDemoStations = !app()->isProduction() && \Illuminate\Support\Facades\DB::table('users')->where('email', 'sales@mobitrack.local')->exists();
                    @endphp

                    @if ($showDemoStations)
                    <!-- 1-Click Quick Station Fillers -->
                    <div class="space-y-2">
                        <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Quick Station Selection:</label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            <button type="button" onclick="fillCreds('altmash', 'Password@12', 'Super Admin (Altmash)')"
                                    class="quick-btn p-2.5 rounded-xl text-left border border-slate-200 hover:border-brand-600 hover:bg-brand-50/50 transition-all text-xs">
                                <span class="block font-semibold text-slate-900 text-[11px]">Super Admin</span>
                                <span class="text-[10px] text-slate-400 font-mono">altmash</span>
                            </button>

                            <button type="button" onclick="fillCreds('sales@mobitrack.local', 'password', 'New Phones POS')"
                                    class="quick-btn p-2.5 rounded-xl text-left border border-slate-200 hover:border-brand-600 hover:bg-brand-50/50 transition-all text-xs">
                                <span class="block font-semibold text-slate-900 text-[11px]">New Phones POS</span>
                                <span class="text-[10px] text-slate-400 font-mono">sales@</span>
                            </button>

                            <button type="button" onclick="fillCreds('buyback@mobitrack.local', 'password', 'Buyback Specialist')"
                                    class="quick-btn p-2.5 rounded-xl text-left border border-slate-200 hover:border-brand-600 hover:bg-brand-50/50 transition-all text-xs">
                                <span class="block font-semibold text-slate-900 text-[11px]">Buyback Desk</span>
                                <span class="text-[10px] text-slate-400 font-mono">buyback@</span>
                            </button>

                            <button type="button" onclick="fillCreds('accessories@mobitrack.local', 'password', 'Accessories Staff')"
                                    class="quick-btn p-2.5 rounded-xl text-left border border-slate-200 hover:border-brand-600 hover:bg-brand-50/50 transition-all text-xs">
                                <span class="block font-semibold text-slate-900 text-[11px]">Accessories</span>
                                <span class="text-[10px] text-slate-400 font-mono">accessories@</span>
                            </button>

                            <button type="button" onclick="fillCreds('cover@mobitrack.local', 'password', 'Cover Staff')"
                                    class="quick-btn p-2.5 rounded-xl text-left border border-slate-200 hover:border-brand-600 hover:bg-brand-50/50 transition-all text-xs">
                                <span class="block font-semibold text-slate-900 text-[11px]">Cover & Glass</span>
                                <span class="text-[10px] text-slate-400 font-mono">cover@</span>
                            </button>

                            <button type="button" onclick="fillCreds('tech@mobitrack.local', 'password', 'Service Tech')"
                                    class="quick-btn p-2.5 rounded-xl text-left border border-slate-200 hover:border-brand-600 hover:bg-brand-50/50 transition-all text-xs">
                                <span class="block font-semibold text-slate-900 text-[11px]">Service Tech</span>
                                <span class="text-[10px] text-slate-400 font-mono">tech@</span>
                            </button>
                        </div>
                    </div>
                    @endif

                    <!-- Login Form -->
                    <form id="loginForm" method="POST" action="{{ route('login.store') }}" class="space-y-4 pt-1">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Staff ID / Email Address</label>
                            <div class="relative">
                                <input type="text" id="emailInput" name="email" value="{{ old('email', 'altmash') }}" required
                                       placeholder="altmash or name@mobitrack.local"
                                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 focus:outline-none focus:border-brand-600 focus:bg-white transition-all">
                                <i data-lucide="user" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-xs font-semibold text-slate-700">Terminal Access Password</label>
                                <a href="{{ route('mobileshop.password.forgot') }}" class="text-[11px] font-semibold text-brand-600 hover:text-brand-700">Forgot?</a>
                            </div>
                            <div class="relative">
                                <input type="password" id="passwordInput" name="password" value="Password@12" required
                                       placeholder="••••••••"
                                       class="w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 focus:outline-none focus:border-brand-600 focus:bg-white transition-all">
                                <i data-lucide="lock" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                                <button type="button" onclick="togglePassword('passwordInput', 'pwdToggleIcon')" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                    <i data-lucide="eye" id="pwdToggleIcon" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Remember Terminal Station -->
                        <div class="flex items-center justify-between text-xs">
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" name="remember" value="1" checked class="w-4 h-4 rounded text-brand-600 focus:ring-brand-600 border-slate-300">
                                <span class="font-medium text-slate-600">Remember this station</span>
                            </label>
                            <span id="selectedBadge" class="text-[10px] font-semibold px-2.5 py-0.5 rounded-full bg-brand-50 text-brand-700">Store Admin</span>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" id="submitBtn"
                                class="w-full py-3 rounded-xl btn-teal text-white font-semibold text-xs shadow-md shadow-brand-700/20 flex items-center justify-center gap-2 transition-colors">
                            <i data-lucide="log-in" class="w-4 h-4"></i>
                            <span id="btnText">Unlock & Enter Terminal</span>
                        </button>
                    </form>

                    <!-- Switch to Register tab prompt -->
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                        <span class="text-slate-500">New team member?</span>
                        <button type="button" onclick="switchAuthTab('register')" class="font-bold text-brand-600 hover:text-brand-700 underline underline-offset-2">
                            Have an Admin Invite Code? Sign Up &rarr;
                        </button>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════
                     PANEL 2: REGISTER WITH INVITE CODE
                     ══════════════════════════════════════════════════ -->
                <div id="panelRegister" class="hidden space-y-5">
                    <!-- Header -->
                    <div>
                        <h1 class="font-display font-black text-2xl text-slate-900">Create Staff Account</h1>
                        <p class="text-xs text-slate-500 mt-1">Enter the code given by your Store Admin, then set your own email &amp; password.</p>
                    </div>

                    <!-- Alert Messages -->
                    <div id="reg-error-alert" class="hidden p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center gap-2">
                        <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 shrink-0"></i>
                        <span id="reg-error-text">Registration error.</span>
                    </div>

                    <div id="reg-success-alert" class="hidden p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
                        <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                        <span id="reg-success-text">Account created &amp; authorized! Launching terminal...</span>
                    </div>

                    <!-- Verified Station Banner -->
                    <div id="regStationBadge" class="hidden p-3 rounded-xl bg-teal-50 border border-teal-200 text-teal-900 text-xs flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i data-lucide="shield-check" class="w-4 h-4 text-teal-600 shrink-0"></i>
                            <div>
                                <span class="font-bold text-teal-950 block" id="regStationLabel">Station Name</span>
                                <span class="text-[10px] text-teal-700" id="regStationExpiry">Valid code</span>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-teal-200 text-teal-900">Designated Role</span>
                    </div>

                    <!-- Registration Form -->
                    <form id="registerForm" method="POST" action="{{ route('mobileshop.register.store') }}" class="space-y-3.5">
                        @csrf

                        <!-- Admin Invite Token -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-xs font-semibold text-slate-700">
                                    Admin Invite Code <span class="text-rose-500">*</span>
                                </label>
                                <span id="tokenCheckStatus" class="text-[10px] font-semibold text-slate-400">Issued by Store Admin</span>
                            </div>
                            <div class="relative">
                                <input type="text" id="regTokenInput" name="token" required
                                       placeholder="e.g. EMP-A1B2C3"
                                       style="text-transform: uppercase;"
                                       oninput="handleTokenInput(this.value)"
                                       onblur="verifyTokenAjax(this.value)"
                                       class="w-full pl-10 pr-24 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-bold tracking-wider text-brand-900 uppercase focus:outline-none focus:border-brand-600 focus:bg-white transition-all">
                                <i data-lucide="ticket" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                                <button type="button" onclick="verifyTokenAjax(document.getElementById('regTokenInput').value)"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 px-2.5 py-1 text-[10px] font-bold bg-slate-200 hover:bg-brand-600 hover:text-white text-slate-700 rounded-lg transition-colors">
                                    Verify Code
                                </button>
                            </div>
                        </div>

                        <!-- Employee Name -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                Your Full Name <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="text" id="regNameInput" name="name" required
                                       placeholder="e.g. Rahul Sharma"
                                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 focus:outline-none focus:border-brand-600 focus:bg-white transition-all">
                                <i data-lucide="user" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                            </div>
                        </div>

                        <!-- Employee Own Email -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                Your Login Email Address <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="email" id="regEmailInput" name="email" required
                                       placeholder="e.g. rahul@store.com"
                                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 focus:outline-none focus:border-brand-600 focus:bg-white transition-all">
                                <i data-lucide="mail" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                            </div>
                        </div>

                        <!-- Employee Own Password -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">
                                    Password <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="password" id="regPasswordInput" name="password" required minlength="6"
                                           placeholder="Min 6 chars"
                                           class="w-full pl-9 pr-9 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 focus:outline-none focus:border-brand-600 focus:bg-white transition-all">
                                    <i data-lucide="lock" class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                                    <button type="button" onclick="togglePassword('regPasswordInput', 'eyeReg1')" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                        <i data-lucide="eye" id="eyeReg1" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">
                                    Confirm Password <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="password" id="regPasswordConfirmInput" name="password_confirmation" required minlength="6"
                                           placeholder="Re-enter"
                                           class="w-full pl-9 pr-9 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 focus:outline-none focus:border-brand-600 focus:bg-white transition-all">
                                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                                    <button type="button" onclick="togglePassword('regPasswordConfirmInput', 'eyeReg2')" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                        <i data-lucide="eye" id="eyeReg2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Registration -->
                        <button type="submit" id="regSubmitBtn"
                                class="w-full py-3 rounded-xl btn-teal text-white font-semibold text-xs shadow-md shadow-brand-700/20 flex items-center justify-center gap-2 transition-colors mt-2">
                            <i data-lucide="user-check" class="w-4 h-4"></i>
                            <span id="regBtnText">Create Account &amp; Unlock Terminal</span>
                        </button>
                    </form>

                    <!-- Back to Login -->
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                        <span class="text-slate-500">Already have an account?</span>
                        <button type="button" onclick="switchAuthTab('signin')" class="font-bold text-brand-600 hover:text-brand-700 underline underline-offset-2">
                            &larr; Switch to Sign In
                        </button>
                    </div>
                </div>

                <!-- Help note -->
                <div class="pt-1 text-center text-xs text-slate-400">
                    Need store access assistance? Contact Store Administrator at <strong class="text-slate-600">+91 98765 43210</strong>
                </div>

            </div>
        </div>

    </div>

    <!-- Interactive Script -->
    <script>
        lucide.createIcons();

        // ══════════════════════════════════════════════════════════════
        // TAB SWITCHING: SIGN IN vs REGISTER WITH INVITE CODE
        // ══════════════════════════════════════════════════════════════
        function switchAuthTab(tab) {
            const panelSignIn = document.getElementById('panelSignIn');
            const panelRegister = document.getElementById('panelRegister');
            const tabSignInBtn = document.getElementById('tabSignInBtn');
            const tabRegisterBtn = document.getElementById('tabRegisterBtn');

            if (tab === 'register') {
                panelSignIn.classList.add('hidden');
                panelRegister.classList.remove('hidden');

                tabSignInBtn.className = 'flex-1 py-2 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1.5 text-slate-500 hover:text-slate-900';
                tabRegisterBtn.className = 'flex-1 py-2 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1.5 bg-white text-slate-900 shadow-sm';

                // Focus on token input
                setTimeout(() => {
                    const tokenInput = document.getElementById('regTokenInput');
                    if (tokenInput && !tokenInput.value) tokenInput.focus();
                }, 100);
            } else {
                panelRegister.classList.add('hidden');
                panelSignIn.classList.remove('hidden');

                tabRegisterBtn.className = 'flex-1 py-2 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1.5 text-slate-500 hover:text-slate-900';
                tabSignInBtn.className = 'flex-1 py-2 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1.5 bg-white text-slate-900 shadow-sm';
            }
            if (window.lucide) lucide.createIcons();
        }

        // 1-Click Station Credential Setter
        function fillCreds(email, password, label) {
            document.getElementById('emailInput').value = email;
            document.getElementById('passwordInput').value = password;
            document.getElementById('selectedBadge').innerText = label;
            document.getElementById('login-error-alert').classList.add('hidden');
        }

        // Show / Hide Password Toggle
        function togglePassword(inputId, iconId) {
            const pwd = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (!pwd || !icon) return;
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.setAttribute('data-lucide', 'eye-off');
            } else {
                pwd.type = 'password';
                icon.setAttribute('data-lucide', 'eye');
            }
            if (window.lucide) lucide.createIcons();
        }

        // ══════════════════════════════════════════════════════════════
        // LIVE TOKEN VERIFIER VIA AJAX
        // ══════════════════════════════════════════════════════════════
        let tokenCheckTimeout = null;
        function handleTokenInput(val) {
            clearTimeout(tokenCheckTimeout);
            const clean = val.trim().toUpperCase();
            if (clean.length >= 6) {
                tokenCheckTimeout = setTimeout(() => verifyTokenAjax(clean), 500);
            }
        }

        async function verifyTokenAjax(tokenStr) {
            tokenStr = (tokenStr || '').trim().toUpperCase();
            const statusSpan = document.getElementById('tokenCheckStatus');
            const badge = document.getElementById('regStationBadge');
            const label = document.getElementById('regStationLabel');
            const expiry = document.getElementById('regStationExpiry');
            const nameInput = document.getElementById('regNameInput');

            if (!tokenStr) {
                statusSpan.innerHTML = 'Issued by Store Admin';
                statusSpan.className = 'text-[10px] font-semibold text-slate-400';
                badge.classList.add('hidden');
                return;
            }

            statusSpan.innerHTML = '<span class="inline-flex items-center gap-1 text-slate-500">Checking...</span>';

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    || '{{ csrf_token() }}';

                const res = await fetch("{{ route('mobileshop.register.verify') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ token: tokenStr })
                });

                const data = await res.json();

                if (res.ok && data.valid) {
                    statusSpan.innerHTML = '<span class="text-emerald-600 font-bold">✓ Code Valid</span>';
                    label.innerText = data.station_label;
                    expiry.innerText = `Role pre-assigned by Admin • Expires ${data.expires_at}`;
                    badge.classList.remove('hidden');

                    if (data.recipient_name && (!nameInput.value || nameInput.value.trim() === '')) {
                        nameInput.value = data.recipient_name;
                    }
                } else {
                    statusSpan.innerHTML = `<span class="text-rose-500 font-bold">${data.message || 'Invalid code'}</span>`;
                    badge.classList.add('hidden');
                }
            } catch (err) {
                statusSpan.innerHTML = '<span class="text-slate-400">Offline check</span>';
            }
            if (window.lucide) lucide.createIcons();
        }

        // ══════════════════════════════════════════════════════════════
        // LOGIN FORM SUBMISSION (AJAX)
        // ══════════════════════════════════════════════════════════════
        const form = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const errAlert = document.getElementById('login-error-alert');
        const errText = document.getElementById('login-error-text');
        const succAlert = document.getElementById('login-success-alert');
        const succText = document.getElementById('login-success-text');

        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            errAlert.classList.add('hidden');
            succAlert.classList.add('hidden');

            submitBtn.disabled = true;
            btnText.innerText = 'Authenticating Terminal...';

            const formData = new FormData(form);

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    || document.querySelector('input[name="_token"]')?.value
                    || '{{ csrf_token() }}';

                const response = await fetch("{{ route('login.store') }}", {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: formData
                });

                if (response.status === 419) {
                    errAlert.classList.remove('hidden');
                    errText.innerText = 'Security session expired. Reloading terminal...';
                    setTimeout(() => { window.location.reload(); }, 600);
                    return;
                }

                const data = await response.json();

                if (response.ok && data.success) {
                    succAlert.classList.remove('hidden');
                    succText.innerText = data.message || 'Access granted! Launching dashboard...';
                    setTimeout(() => {
                        window.location.href = data.redirect || "{{ route('public.landing') }}";
                    }, 500);
                } else {
                    errAlert.classList.remove('hidden');
                    errText.innerText = data.message || 'Invalid email or password. Please verify credentials.';
                    submitBtn.disabled = false;
                    btnText.innerText = 'Unlock & Enter Terminal';
                }
            } catch (err) {
                form.submit();
            }
        });

        // ══════════════════════════════════════════════════════════════
        // REGISTRATION FORM SUBMISSION (AJAX)
        // ══════════════════════════════════════════════════════════════
        const regForm = document.getElementById('registerForm');
        const regSubmitBtn = document.getElementById('regSubmitBtn');
        const regBtnText = document.getElementById('regBtnText');
        const regErrAlert = document.getElementById('reg-error-alert');
        const regErrText = document.getElementById('reg-error-text');
        const regSuccAlert = document.getElementById('reg-success-alert');
        const regSuccText = document.getElementById('reg-success-text');

        regForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            regErrAlert.classList.add('hidden');
            regSuccAlert.classList.add('hidden');

            const pwd = document.getElementById('regPasswordInput').value;
            const pwdConf = document.getElementById('regPasswordConfirmInput').value;

            if (pwd !== pwdConf) {
                regErrAlert.classList.remove('hidden');
                regErrText.innerText = 'Passwords do not match. Please re-enter identical passwords.';
                return;
            }

            regSubmitBtn.disabled = true;
            regBtnText.innerText = 'Activating Account...';

            const formData = new FormData(regForm);

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    || document.querySelector('input[name="_token"]')?.value
                    || '{{ csrf_token() }}';

                const response = await fetch("{{ route('mobileshop.register.store') }}", {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: formData
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    regSuccAlert.classList.remove('hidden');
                    regSuccText.innerText = data.message || 'Account activated successfully! Launching station...';
                    setTimeout(() => {
                        window.location.href = data.redirect || "{{ route('mobileshop.dashboard') }}";
                    }, 700);
                } else {
                    regErrAlert.classList.remove('hidden');
                    regErrText.innerText = data.message || 'Registration failed. Please verify your invite code and inputs.';
                    regSubmitBtn.disabled = false;
                    regBtnText.innerText = 'Create Account & Unlock Terminal';
                }
            } catch (err) {
                regForm.submit();
            }
        });

        // ══════════════════════════════════════════════════════════════
        // AUTO-OPEN REGISTER TAB IF URL CONTAINS TOKEN OR TAB=REGISTER
        // ══════════════════════════════════════════════════════════════
        document.addEventListener('DOMContentLoaded', function() {
            const params = new URLSearchParams(window.location.search);
            const tokenParam = params.get('token');
            const tabParam = params.get('tab');

            if (tokenParam || tabParam === 'register') {
                switchAuthTab('register');
                if (tokenParam) {
                    const tokenInput = document.getElementById('regTokenInput');
                    if (tokenInput) {
                        tokenInput.value = tokenParam.toUpperCase();
                        verifyTokenAjax(tokenParam);
                    }
                }
            }
            if (window.lucide) lucide.createIcons();
        });
    </script>
</body>
</html>
