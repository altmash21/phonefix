<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Developer Console Access — MobiTrack</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome & Tailwind -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"Fira Code"', 'monospace'],
                    },
                    colors: {
                        slate: {
                            850: '#0f172a',
                            900: '#090d16',
                            950: '#05070c',
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #05070c;
            color: #f1f5f9;
        }
        .glow-card {
            background: linear-gradient(145deg, rgba(30, 41, 59, 0.45) 0%, rgba(15, 23, 42, 0.85) 100%);
            border: 1px solid rgba(51, 65, 85, 0.45);
            box-shadow: 0 20px 50px -10px rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(16px);
        }
    </style>
</head>
<body class="min-h-full flex items-center justify-center p-4 antialiased selection:bg-indigo-500 selection:text-white">

    <div class="max-w-md w-full space-y-6">
        <!-- Brand / Terminal Tag -->
        <div class="text-center space-y-2">
            <div class="inline-flex items-center justify-center h-14 w-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow-xl shadow-indigo-500/30 mb-2">
                <i class="fa-solid fa-terminal text-2xl"></i>
            </div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Developer Control Center</h1>
            <p class="text-xs text-slate-400 font-mono">Terminal Route: <span class="text-indigo-400">/home/ad</span></p>
        </div>

        @php
            $displayError = $error ?? session('error');
            $displayWarning = $warning ?? session('warning');
            $displayInfo = $info ?? session('info');
        @endphp

        @if($displayError)
            <div class="p-4 rounded-xl bg-rose-950/70 border border-rose-700/60 text-rose-200 text-xs flex items-center gap-3">
                <i class="fa-solid fa-circle-exclamation text-rose-400 text-base shrink-0"></i>
                <span>{{ $displayError }}</span>
            </div>
        @endif

        @if($displayWarning)
            <div class="p-4 rounded-xl bg-amber-950/70 border border-amber-700/60 text-amber-200 text-xs flex items-center gap-3">
                <i class="fa-solid fa-shield-halved text-amber-400 text-base shrink-0"></i>
                <span>{{ $displayWarning }}</span>
            </div>
        @endif

        @if($displayInfo)
            <div class="p-4 rounded-xl bg-indigo-950/70 border border-indigo-700/60 text-indigo-200 text-xs flex items-center gap-3">
                <i class="fa-solid fa-lock text-indigo-400 text-base shrink-0"></i>
                <span>{{ $displayInfo }}</span>
            </div>
        @endif

        <!-- Card Container -->
        <div class="glow-card rounded-2xl p-6 sm:p-8 space-y-6">
            <div class="border-b border-slate-800 pb-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-bold text-white flex items-center gap-2">
                        <i class="fa-solid fa-lock text-indigo-400"></i> Terminal Access Gate
                    </h2>
                    <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-rose-950/80 border border-rose-800/60 text-rose-300 font-bold uppercase">
                        Restricted: Altmash Only
                    </span>
                </div>
                <p class="text-xs text-slate-400 mt-1.5">This Developer Console is strictly restricted to the master administrator. Store Admin accounts cannot access this area.</p>
            </div>

            <form action="{{ route('dev.portal.login') }}" method="POST" class="space-y-4">
                @csrf

                <!-- ID / Username -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Master Developer ID</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i class="fa-solid fa-user-shield"></i>
                        </span>
                        <input type="text" name="id" value="{{ old('id', 'altmash') }}" required autofocus
                               placeholder="altmash"
                               class="w-full pl-10 pr-3 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs font-mono text-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-xs font-semibold text-slate-300">Terminal Password</label>
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i class="fa-solid fa-key"></i>
                        </span>
                        <input type="password" id="devPassword" name="password" value="" required autocomplete="current-password"
                               placeholder="Enter Password@12"
                               class="w-full pl-10 pr-10 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs font-mono text-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                        <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-500 hover:text-slate-300">
                            <i id="eyeIcon" class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold tracking-wide uppercase transition shadow-lg shadow-indigo-600/30 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-unlock-keyhole"></i> Unlock Developer Console
                    </button>
                </div>
            </form>

            <div class="pt-2 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-400">
                <a href="{{ route('login') }}" class="hover:text-slate-200 transition">
                    <i class="fa-solid fa-arrow-left mr-1"></i> Staff POS Login
                </a>
                <span class="font-mono text-[11px] text-slate-500">MobiTrack v2.4</span>
            </div>
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            const pwd = document.getElementById('devPassword');
            const icon = document.getElementById('eyeIcon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.className = 'fa-solid fa-eye-slash';
            } else {
                pwd.type = 'password';
                icon.className = 'fa-solid fa-eye';
            }
        }
    </script>
</body>
</html>
