<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login — Maurya Mobile ERP</title>
    <meta name="description" content="Secure terminal access for Maurya Mobile Mobile Shop ERP staff and store administration.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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

        <!-- ════ RIGHT PANEL: LOGIN FORM (7 Cols) ════ -->
        <div class="lg:col-span-7 p-8 sm:p-12 flex flex-col justify-center bg-white">
            <div class="max-w-md w-full mx-auto space-y-6">

                <!-- Header -->
                <div>
                    <h1 class="font-display font-black text-2xl text-slate-900">Sign In to Terminal</h1>
                    <p class="text-xs text-slate-500 mt-1">Select your staff station or enter your registered store email.</p>
                </div>

                <!-- Alert Messages -->
                <div id="login-error-alert" class="hidden p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 shrink-0"></i>
                    <span id="login-error-text">Invalid login credentials.</span>
                </div>

                <div id="login-success-alert" class="hidden p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                    <span id="login-success-text">Authorized! Redirecting to station...</span>
                </div>

                <!-- 1-Click Quick Station Fillers -->
                <div class="space-y-2">
                    <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Quick Station Selection:</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        <button type="button" onclick="fillCreds('admin@mauryamobile.local', 'password', 'Store Admin')"
                                class="quick-btn p-2.5 rounded-xl text-left border border-slate-200 hover:border-brand-600 hover:bg-brand-50/50 transition-all text-xs">
                            <span class="block font-semibold text-slate-900 text-[11px]">Store Admin</span>
                            <span class="text-[10px] text-slate-400 font-mono">admin@</span>
                        </button>

                        <button type="button" onclick="fillCreds('sales@mauryamobile.local', 'password', 'New Phones POS')"
                                class="quick-btn p-2.5 rounded-xl text-left border border-slate-200 hover:border-brand-600 hover:bg-brand-50/50 transition-all text-xs">
                            <span class="block font-semibold text-slate-900 text-[11px]">New Phones POS</span>
                            <span class="text-[10px] text-slate-400 font-mono">sales@</span>
                        </button>

                        <button type="button" onclick="fillCreds('buyback@mauryamobile.local', 'password', 'Buyback Specialist')"
                                class="quick-btn p-2.5 rounded-xl text-left border border-slate-200 hover:border-brand-600 hover:bg-brand-50/50 transition-all text-xs">
                            <span class="block font-semibold text-slate-900 text-[11px]">Buyback Desk</span>
                            <span class="text-[10px] text-slate-400 font-mono">buyback@</span>
                        </button>

                        <button type="button" onclick="fillCreds('accessories@mauryamobile.local', 'password', 'Accessories Staff')"
                                class="quick-btn p-2.5 rounded-xl text-left border border-slate-200 hover:border-brand-600 hover:bg-brand-50/50 transition-all text-xs">
                            <span class="block font-semibold text-slate-900 text-[11px]">Accessories</span>
                            <span class="text-[10px] text-slate-400 font-mono">accessories@</span>
                        </button>

                        <button type="button" onclick="fillCreds('cover@mauryamobile.local', 'password', 'Cover Staff')"
                                class="quick-btn p-2.5 rounded-xl text-left border border-slate-200 hover:border-brand-600 hover:bg-brand-50/50 transition-all text-xs">
                            <span class="block font-semibold text-slate-900 text-[11px]">Cover & Glass</span>
                            <span class="text-[10px] text-slate-400 font-mono">cover@</span>
                        </button>

                        <button type="button" onclick="fillCreds('tech@mauryamobile.local', 'password', 'Service Tech')"
                                class="quick-btn p-2.5 rounded-xl text-left border border-slate-200 hover:border-brand-600 hover:bg-brand-50/50 transition-all text-xs">
                            <span class="block font-semibold text-slate-900 text-[11px]">Service Tech</span>
                            <span class="text-[10px] text-slate-400 font-mono">tech@</span>
                        </button>
                    </div>
                </div>

                <!-- Login Form -->
                <form id="loginForm" method="POST" action="{{ route('login.store') }}" class="space-y-4 pt-1">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Staff Email Address</label>
                        <div class="relative">
                            <input type="email" id="emailInput" name="email" value="{{ old('email', 'admin@mauryamobile.local') }}" required
                                   placeholder="name@mauryamobile.local"
                                   class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 focus:outline-none focus:border-brand-600 focus:bg-white transition-all">
                            <i data-lucide="mail" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-xs font-semibold text-slate-700">Terminal Access Password</label>
                            <a href="{{ route('forgot') }}" class="text-[11px] font-semibold text-brand-600 hover:text-brand-700">Forgot?</a>
                        </div>
                        <div class="relative">
                            <input type="password" id="passwordInput" name="password" value="password" required
                                   placeholder="••••••••"
                                   class="w-full pl-10 pr-11 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 focus:outline-none focus:border-brand-600 focus:bg-white transition-all">
                            <i data-lucide="lock" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                            <button type="button" onclick="togglePassword()" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                <i data-lucide="eye" id="eyeIcon" class="w-4 h-4"></i>
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

                <!-- Help note -->
                <div class="pt-2 text-center text-xs text-slate-400">
                    Need technical terminal assistance? Call Store Admin at <strong class="text-slate-600">+91 98765 43210</strong>
                </div>

            </div>
        </div>

    </div>

    <!-- Interactive Script -->
    <script>
        lucide.createIcons();

        // 1-Click Station Credential Setter
        function fillCreds(email, password, label) {
            document.getElementById('emailInput').value = email;
            document.getElementById('passwordInput').value = password;
            document.getElementById('selectedBadge').innerText = label;
            document.getElementById('login-error-alert').classList.add('hidden');
        }

        // Show / Hide Password Toggle
        function togglePassword() {
            const pwd = document.getElementById('passwordInput');
            const icon = document.getElementById('eyeIcon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.setAttribute('data-lucide', 'eye-off');
            } else {
                pwd.type = 'password';
                icon.setAttribute('data-lucide', 'eye');
            }
            lucide.createIcons();
        }

        // AJAX Form Submit with Smooth Redirect
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
                    errText.innerText = 'Security session expired or token refreshed. Reloading terminal...';
                    setTimeout(() => {
                        window.location.reload();
                    }, 600);
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
                    errText.innerText = data.message || 'Invalid email or password. Please verify your credentials.';
                    submitBtn.disabled = false;
                    btnText.innerText = 'Unlock & Enter Terminal';
                }
            } catch (err) {
                // Fallback to standard form submission if fetch is interrupted
                form.submit();
            }
        });
    </script>
</body>
</html>
