<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Account Setup — PhoneFix Azamgarh ERP</title>
    <meta name="description" content="Register your staff terminal account with your administrator invite token.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <base href="{{ config('app.url') . '/' }}">

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

    <!-- Main Container -->
    <div class="relative z-10 w-full max-w-5xl rounded-2xl overflow-hidden shadow-2xl border border-slate-800 bg-white grid grid-cols-1 lg:grid-cols-12 min-h-[600px]">

        <!-- LEFT PANEL: BRANDING & TOKEN INSTRUCTIONS (5 Cols) -->
        <div class="lg:col-span-5 gradient-panel text-white p-8 sm:p-10 flex flex-col justify-between relative overflow-hidden">
            <div class="space-y-6 relative z-10">
                <!-- Top Brand Header -->
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white text-brand-700 flex items-center justify-center shadow-lg shadow-black/20 font-bold">
                        <i data-lucide="smartphone" class="w-5 h-5 text-brand-700"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="font-display font-bold text-2xl text-white tracking-tight">PhoneFix Azamgarh</span>
                        </div>
                        <span class="text-[10px] text-teal-300 font-semibold uppercase tracking-wider">Enterprise Retail ERP</span>
                    </div>
                </div>

                <div>
                    <h2 class="font-display font-semibold text-lg text-white leading-snug">
                        Staff Account Activation
                    </h2>
                    <p class="text-xs text-slate-300 mt-2 leading-relaxed">
                        Join your store's terminal team. You need a unique 8-character token issued by your Store Administrator to activate your terminal station.
                    </p>
                </div>

                <!-- Step-by-Step Guidance -->
                <div class="space-y-3 pt-2">
                    <span class="text-[10px] uppercase font-semibold text-teal-300 tracking-wider block">Activation Process:</span>
                    
                    <div class="glass-card p-3 rounded-xl flex items-start gap-3">
                        <div class="w-6 h-6 rounded-full bg-teal-400/20 text-teal-300 flex items-center justify-center text-xs font-bold shrink-0">1</div>
                        <div>
                            <span class="font-semibold text-xs text-white block">Get Token from Admin</span>
                            <span class="text-[11px] text-slate-300">Your Store Admin generates a role-assigned token in Masters &gt; Staff Accounts.</span>
                        </div>
                    </div>

                    <div class="glass-card p-3 rounded-xl flex items-start gap-3">
                        <div class="w-6 h-6 rounded-full bg-teal-400/20 text-teal-300 flex items-center justify-center text-xs font-bold shrink-0">2</div>
                        <div>
                            <span class="font-semibold text-xs text-white block">Enter Your Credentials</span>
                            <span class="text-[11px] text-slate-300">Set your email address and secure password for your terminal station.</span>
                        </div>
                    </div>

                    <div class="glass-card p-3 rounded-xl flex items-start gap-3">
                        <div class="w-6 h-6 rounded-full bg-teal-400/20 text-teal-300 flex items-center justify-center text-xs font-bold shrink-0">3</div>
                        <div>
                            <span class="font-semibold text-xs text-white block">Instant Activation</span>
                            <span class="text-[11px] text-slate-300">Your station is instantly activated with isolated inventory and counter billing rights.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Back link -->
            <div class="pt-6 border-t border-white/10 relative z-10 flex items-center justify-between text-xs text-slate-300">
                <a href="{{ route('login') }}" class="hover:text-white font-medium inline-flex items-center gap-1.5 transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Already have an account? Sign In</span>
                </a>
                <span class="text-slate-400">v3.2 ERP</span>
            </div>
        </div>

        <!-- RIGHT PANEL: REGISTRATION FORM (7 Cols) -->
        <div class="lg:col-span-7 p-8 sm:p-12 flex flex-col justify-center bg-white">
            <div class="max-w-md w-full mx-auto space-y-6">

                <!-- Header -->
                <div>
                    <h1 class="font-display font-black text-2xl text-slate-900">Activate Staff Account</h1>
                    <p class="text-xs text-slate-500 mt-1">Provide your invite token and choose your terminal credentials.</p>
                </div>

                <!-- Alert Messages -->
                <div id="reg-error-alert" class="hidden p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 shrink-0"></i>
                    <span id="reg-error-text">Registration error.</span>
                </div>

                <div id="reg-success-alert" class="hidden p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                    <span id="reg-success-text">Account activated! Redirecting...</span>
                </div>

                @if (!empty($inviteInfo))
                <div class="p-3.5 rounded-2xl bg-teal-50 border border-teal-200 text-teal-900 text-xs flex items-center gap-3">
                    <i data-lucide="shield-check" class="w-5 h-5 text-teal-600 shrink-0"></i>
                    <div>
                        <div class="font-semibold text-teal-900">Station: {{ $inviteInfo['station_label'] }}</div>
                        <div class="text-[11px] text-teal-700">Valid token &bull; Expires {{ $inviteInfo['expires_at'] }}</div>
                    </div>
                </div>
                @endif

                <!-- Registration Form -->
                <form id="registerForm" method="POST" action="{{ route('mobileshop.register.store') }}" class="space-y-4">
                    @csrf

                    <!-- Token Input -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Admin Invite Token <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text" id="tokenInput" name="token" value="{{ old('token', $token ?? request('token')) }}" required
                                   placeholder="e.g. EMP-A1B2C3D4"
                                   style="text-transform: uppercase;"
                                   class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-bold tracking-wider text-brand-800 uppercase focus:outline-none focus:border-brand-600 focus:bg-white transition-all">
                            <i data-lucide="key" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                        </div>
                        <span class="text-[10px] text-slate-400 mt-1 block">Ask your Store Administrator if you don't have a token.</span>
                    </div>

                    <!-- Full Name -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Your Full Name <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text" id="nameInput" name="name" value="{{ old('name', $inviteInfo['recipient_name'] ?? '') }}" required
                                   placeholder="e.g. Rahul Sharma"
                                   class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 focus:outline-none focus:border-brand-600 focus:bg-white transition-all">
                            <i data-lucide="user" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                        </div>
                    </div>

                    <!-- Email Address -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Work Email Address <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="email" id="emailInput" name="email" value="{{ old('email') }}" required
                                   placeholder="e.g. rahul@mobitrack.local"
                                   class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 focus:outline-none focus:border-brand-600 focus:bg-white transition-all">
                            <i data-lucide="mail" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Password <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" id="passwordInput" name="password" required minlength="6"
                                   placeholder="Minimum 6 characters"
                                   class="w-full pl-10 pr-11 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 focus:outline-none focus:border-brand-600 focus:bg-white transition-all">
                            <i data-lucide="lock" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                            <button type="button" onclick="togglePassword('passwordInput', 'eyeIcon1')" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                <i data-lucide="eye" id="eyeIcon1" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Password Confirmation -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Confirm Password <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" id="passwordConfirmInput" name="password_confirmation" required minlength="6"
                                   placeholder="Re-enter password"
                                   class="w-full pl-10 pr-11 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 focus:outline-none focus:border-brand-600 focus:bg-white transition-all">
                            <i data-lucide="shield-check" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                            <button type="button" onclick="togglePassword('passwordConfirmInput', 'eyeIcon2')" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                <i data-lucide="eye" id="eyeIcon2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" id="submitBtn"
                            class="w-full py-3 rounded-xl btn-teal text-white font-semibold text-xs shadow-md shadow-brand-700/20 flex items-center justify-center gap-2 transition-colors mt-2">
                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                        <span id="btnText">Activate Account & Enter Terminal</span>
                    </button>
                </form>

                <!-- Already have account link -->
                <div class="pt-2 text-center text-xs text-slate-500">
                    Already an authorized team member? 
                    <a href="{{ route('login') }}" class="font-bold text-brand-600 hover:text-brand-700 underline underline-offset-2 ml-1">Sign In here</a>
                </div>

            </div>
        </div>

    </div>

    <!-- Interactive Script -->
    <script>
        lucide.createIcons();

        function togglePassword(inputId, iconId) {
            const pwd = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.setAttribute('data-lucide', 'eye-off');
            } else {
                pwd.type = 'password';
                icon.setAttribute('data-lucide', 'eye');
            }
            lucide.createIcons();
        }

        const form = document.getElementById('registerForm');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const errAlert = document.getElementById('reg-error-alert');
        const errText = document.getElementById('reg-error-text');
        const succAlert = document.getElementById('reg-success-alert');
        const succText = document.getElementById('reg-success-text');

        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            errAlert.classList.add('hidden');
            succAlert.classList.add('hidden');

            const pwd = document.getElementById('passwordInput').value;
            const pwdConf = document.getElementById('passwordConfirmInput').value;
            if (pwd !== pwdConf) {
                errAlert.classList.remove('hidden');
                errText.innerText = 'Passwords do not match. Please re-enter.';
                return;
            }

            submitBtn.disabled = true;
            btnText.innerText = 'Validating Token & Registering...';

            const formData = new FormData(form);

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    || document.querySelector('input[name="_token"]')?.value;

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
                    succAlert.classList.remove('hidden');
                    succText.innerText = data.message || 'Account activated successfully! Redirecting...';
                    setTimeout(() => {
                        window.location.href = data.redirect || "{{ route('public.landing') }}";
                    }, 800);
                } else {
                    errAlert.classList.remove('hidden');
                    errText.innerText = data.message || 'Registration failed. Please check your token and inputs.';
                    submitBtn.disabled = false;
                    btnText.innerText = 'Activate Account & Enter Terminal';
                }
            } catch (err) {
                form.submit();
            }
        });
    </script>
</body>
</html>
