<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Terminal Password — Maurya Mobile ERP</title>
    <meta name="description" content="Reset your staff terminal account password securely via email OTP verification.">
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

        <!-- LEFT PANEL: BRANDING & OTP SECURITY (5 Cols) -->
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
                        Self-Service Password Recovery
                    </h2>
                    <p class="text-xs text-slate-300 mt-2 leading-relaxed">
                        To protect your counter's cash drawer, transactions, and inventory integrity, password resets require 2-step email OTP authorization.
                    </p>
                </div>

                <!-- OTP Security Highlights -->
                <div class="space-y-3 pt-2">
                    <span class="text-[10px] uppercase font-semibold text-teal-300 tracking-wider block">Security Safeguards:</span>

                    <div class="glass-card p-3 rounded-xl flex items-start gap-3">
                        <i data-lucide="mail-check" class="w-5 h-5 text-teal-300 shrink-0 mt-0.5"></i>
                        <div>
                            <span class="font-semibold text-xs text-white block">Email Dispatch</span>
                            <span class="text-[11px] text-slate-300">A one-time 6-digit numeric OTP is sent directly to your registered store address.</span>
                        </div>
                    </div>

                    <div class="glass-card p-3 rounded-xl flex items-start gap-3">
                        <i data-lucide="clock" class="w-5 h-5 text-teal-300 shrink-0 mt-0.5"></i>
                        <div>
                            <span class="font-semibold text-xs text-white block">15-Minute Expiry</span>
                            <span class="text-[11px] text-slate-300">Each OTP expires automatically after 15 minutes and can only be used once.</span>
                        </div>
                    </div>

                    <div class="glass-card p-3 rounded-xl flex items-start gap-3">
                        <i data-lucide="shield-alert" class="w-5 h-5 text-teal-300 shrink-0 mt-0.5"></i>
                        <div>
                            <span class="font-semibold text-xs text-white block">Anti-Brute Force Protection</span>
                            <span class="text-[11px] text-slate-300">Verification is strictly rate-limited to 5 attempts to prevent unauthorized access.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Back link -->
            <div class="pt-6 border-t border-white/10 relative z-10 flex items-center justify-between text-xs text-slate-300">
                <a href="{{ route('login') }}" class="hover:text-white font-medium inline-flex items-center gap-1.5 transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Back to Terminal Sign In</span>
                </a>
                <span class="text-slate-400">v3.2 ERP</span>
            </div>
        </div>

        <!-- RIGHT PANEL: RESET FORM (7 Cols) -->
        <div class="lg:col-span-7 p-8 sm:p-12 flex flex-col justify-center bg-white">
            <div class="max-w-md w-full mx-auto space-y-6">

                @php
                    $isVerifyStep = (isset($step) && $step === 'verify') || old('otp_code') || $errors->has('otp_code') || $errors->has('password');
                @endphp

                <!-- Header -->
                <div>
                    <h1 class="font-display font-black text-2xl text-slate-900">
                        {{ $isVerifyStep ? 'Verify OTP & Set New Password' : 'Reset Password' }}
                    </h1>
                    <p class="text-xs text-slate-500 mt-1">
                        {{ $isVerifyStep 
                            ? 'Enter the 6-digit verification code sent to your email and pick a new password.' 
                            : 'Enter your registered email address to receive an authorization code.' }}
                    </p>
                </div>

                <!-- Notifications & Errors -->
                @if (session('success'))
                    <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
                        <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if (session('debug_otp'))
                    <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i data-lucide="info" class="w-4 h-4 text-amber-600"></i>
                            <span>Local Dev Code: <strong class="font-mono text-sm tracking-wider">{{ session('debug_otp') }}</strong></span>
                        </div>
                        <button type="button" onclick="document.getElementById('otpInput').value='{{ session('debug_otp') }}'" class="text-[11px] font-bold text-amber-700 underline">Fill</button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold space-y-1">
                        @foreach ($errors->all() as $error)
                            <div class="flex items-center gap-2">
                                <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 shrink-0"></i>
                                <span>{{ $error }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if (!$isVerifyStep)
                    <!-- ══ STEP 1: REQUEST OTP FORM ══ -->
                    <form method="POST" action="{{ route('mobileshop.password.send_otp') }}" class="space-y-4">
                        @csrf

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                Your Registered Email <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                                       placeholder="e.g. rahul@mobitrack.local"
                                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 focus:outline-none focus:border-brand-600 focus:bg-white transition-all">
                                <i data-lucide="mail" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                            </div>
                        </div>

                        <button type="submit"
                                class="w-full py-3 rounded-xl btn-teal text-white font-semibold text-xs shadow-md shadow-brand-700/20 flex items-center justify-center gap-2 transition-colors">
                            <i data-lucide="send" class="w-4 h-4"></i>
                            <span>Send 6-Digit OTP</span>
                        </button>
                    </form>

                    <div class="text-center pt-2">
                        <span class="text-xs text-slate-500">Already have an OTP code?</span>
                        <a href="{{ route('mobileshop.password.reset') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700 ml-1 underline">Enter code here</a>
                    </div>
                @else
                    <!-- ══ STEP 2: VERIFY OTP & SET PASSWORD FORM ══ -->
                    <form method="POST" action="{{ route('mobileshop.password.process_reset') }}" class="space-y-4">
                        @csrf

                        <!-- Email Field -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                Registered Email <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="email" name="email" value="{{ old('email', $email ?? '') }}" required
                                       placeholder="e.g. rahul@mobitrack.local"
                                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 focus:outline-none focus:border-brand-600 focus:bg-white transition-all">
                                <i data-lucide="mail" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                            </div>
                        </div>

                        <!-- 6-Digit OTP -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-xs font-semibold text-slate-700">
                                    6-Digit Verification Code <span class="text-rose-500">*</span>
                                </label>
                                <a href="{{ route('mobileshop.password.forgot') }}" class="text-[11px] font-semibold text-brand-600 hover:text-brand-700">Resend Code</a>
                            </div>
                            <div class="relative">
                                <input type="text" id="otpInput" name="otp_code" value="{{ old('otp_code') }}" required maxlength="6"
                                       placeholder="123456"
                                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono font-bold tracking-widest text-slate-900 focus:outline-none focus:border-brand-600 focus:bg-white transition-all">
                                <i data-lucide="key" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                            </div>
                        </div>

                        <!-- New Password -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                New Password <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" id="newPwd" name="password" required minlength="6"
                                       placeholder="Minimum 6 characters"
                                       class="w-full pl-10 pr-11 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 focus:outline-none focus:border-brand-600 focus:bg-white transition-all">
                                <i data-lucide="lock" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                                <button type="button" onclick="togglePwd('newPwd', 'eye1')" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                    <i data-lucide="eye" id="eye1" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Confirm New Password -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                Confirm New Password <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" id="newPwdConf" name="password_confirmation" required minlength="6"
                                       placeholder="Re-enter new password"
                                       class="w-full pl-10 pr-11 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 focus:outline-none focus:border-brand-600 focus:bg-white transition-all">
                                <i data-lucide="shield-check" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                                <button type="button" onclick="togglePwd('newPwdConf', 'eye2')" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                    <i data-lucide="eye" id="eye2" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit"
                                class="w-full py-3 rounded-xl btn-teal text-white font-semibold text-xs shadow-md shadow-brand-700/20 flex items-center justify-center gap-2 transition-colors mt-2">
                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                            <span>Update Password & Enter Terminal</span>
                        </button>
                    </form>
                @endif

                <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <a href="{{ route('login') }}" class="font-semibold text-slate-600 hover:text-slate-900 inline-flex items-center gap-1">
                        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                        <span>Return to Sign In</span>
                    </a>
                    <a href="{{ route('mobileshop.register') }}" class="font-semibold text-brand-600 hover:text-brand-700">
                        Got an Invite Token?
                    </a>
                </div>

            </div>
        </div>

    </div>

    <!-- Script -->
    <script>
        lucide.createIcons();

        function togglePwd(inputId, iconId) {
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
    </script>
</body>
</html>
