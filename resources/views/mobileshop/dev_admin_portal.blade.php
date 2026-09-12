<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MobiTrack — Developer Control Center & Super Admin Portal</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome & Tailwind CDN for standalone console reliability -->
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
        .mono-num {
            font-family: 'Fira Code', monospace;
        }
        .glow-card {
            background: linear-gradient(145deg, rgba(30, 41, 59, 0.45) 0%, rgba(15, 23, 42, 0.85) 100%);
            border: 1px solid rgba(51, 65, 85, 0.45);
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(12px);
        }
        .glow-card:hover {
            border-color: rgba(99, 102, 241, 0.4);
        }
        .glow-danger {
            background: linear-gradient(145deg, rgba(127, 29, 29, 0.15) 0%, rgba(69, 10, 10, 0.3) 100%);
            border: 1px solid rgba(239, 68, 68, 0.35);
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #090d16;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 9999px;
        }
        .pulse-dot {
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }
    </style>
</head>
<body class="min-h-full flex flex-col antialiased selection:bg-indigo-500 selection:text-white">

    <!-- Top Navigation Bar -->
    <header class="sticky top-0 z-40 bg-slate-900/90 border-b border-slate-800/80 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white shadow-lg shadow-indigo-500/20">
                    <i class="fa-solid fa-terminal text-lg"></i>
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="font-bold text-white text-base tracking-tight">MobiTrack <span class="text-indigo-400 font-mono text-xs uppercase px-2 py-0.5 rounded bg-indigo-950/80 border border-indigo-800/60 ml-1">Dev Admin</span></span>
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-950/80 text-emerald-400 border border-emerald-800/60">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 pulse-dot"></span> Online
                        </span>
                    </div>
                    <p class="text-xs text-slate-400 font-mono">Route: <span class="text-slate-300">/home/ad</span> &bull; Host: <span class="text-slate-300">{{ $telemetry['server']['db_host'] ?? '127.0.0.1' }}</span></p>
                </div>
            </div>

            <!-- Header Quick Actions -->
            <div class="flex items-center space-x-3">
                <a href="{{ url($companyId . '/mobileshop/dashboard') }}" class="hidden sm:inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-300 bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700/60 transition shadow-sm">
                    <i class="fa-solid fa-store text-indigo-400"></i> Store POS
                </a>
                <a href="{{ url($companyId . '/mobileshop/masters') }}" class="hidden sm:inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-300 bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700/60 transition shadow-sm">
                    <i class="fa-solid fa-layer-group text-purple-400"></i> Masters Hub
                </a>
                <a href="{{ route('public.store') }}" target="_blank" class="hidden md:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-slate-400 hover:text-slate-200 transition">
                    <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i> Public Site
                </a>
                
                <div class="h-6 w-px bg-slate-800 hidden sm:block"></div>

                <div class="flex items-center space-x-2 text-xs text-slate-400">
                    <span class="px-2.5 py-1 rounded-lg bg-slate-800/70 border border-slate-700/50 font-mono text-slate-300">
                        <i class="fa-solid fa-user-shield text-indigo-400 mr-1.5"></i>{{ auth()->user()->name ?? 'Altmash' }}
                    </span>
                    <a href="{{ route('dev.portal.lock') }}" title="Lock Developer Terminal Immediately" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-rose-300 bg-rose-950/70 hover:bg-rose-900/80 border border-rose-800/60 transition shadow-sm">
                        <i class="fa-solid fa-lock text-rose-400"></i> Lock Console
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Flash Notifications -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        @if(session('success'))
            <div id="flash-banner" class="p-4 rounded-xl bg-emerald-950/70 border border-emerald-700/60 text-emerald-200 flex items-start justify-between shadow-lg">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-circle-check text-emerald-400 text-lg"></i>
                    <div>
                        <h4 class="font-bold text-sm text-emerald-100">Operation Succeeded</h4>
                        <p class="text-xs text-emerald-300 mt-0.5">{{ session('success') }}</p>
                    </div>
                </div>
                <button onclick="document.getElementById('flash-banner').remove()" class="text-emerald-400 hover:text-emerald-200"><i class="fa-solid fa-xmark"></i></button>
            </div>
        @endif

        @if(session('error'))
            <div id="flash-banner-err" class="p-4 rounded-xl bg-rose-950/70 border border-rose-700/60 text-rose-200 flex items-start justify-between shadow-lg">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-triangle-exclamation text-rose-400 text-lg"></i>
                    <div>
                        <h4 class="font-bold text-sm text-rose-100">Operation Notice</h4>
                        <p class="text-xs text-rose-300 mt-0.5">{{ session('error') }}</p>
                    </div>
                </div>
                <button onclick="document.getElementById('flash-banner-err').remove()" class="text-rose-400 hover:text-rose-200"><i class="fa-solid fa-xmark"></i></button>
            </div>
        @endif

        <!-- Banner / Hero Summary -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-indigo-950/40 to-slate-900 border border-slate-800 p-6 sm:p-8 shadow-2xl">
            <div class="absolute -right-12 -top-12 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                            Developer Maintenance Engine
                        </span>
                        <span class="text-xs text-slate-400 font-mono">PHP {{ $telemetry['server']['php_version'] }} &bull; MySQL {{ $telemetry['server']['mysql_version'] }}</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">System Telemetry & Database Management</h1>
                    <p class="text-slate-400 text-sm mt-1 max-w-2xl">
                        Central developer hub to monitor system health, generate full database SQL snapshots, configure daily automated backups, and perform clean factory resets for client handover.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    <button type="button" onclick="openBackupModal()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold transition shadow-lg shadow-indigo-600/30">
                        <i class="fa-solid fa-download"></i> Create Backup Now
                    </button>
                    <a href="#danger-zone" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-rose-950/80 hover:bg-rose-900/90 text-rose-300 hover:text-rose-100 border border-rose-800/60 text-xs font-bold transition shadow-lg shadow-rose-950/50">
                        <i class="fa-solid fa-broom text-rose-400"></i> Client Handover Reset
                    </a>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════ -->
        <!-- SECTION 1: SYSTEM & DATABASE TELEMETRY METRICS             -->
        <!-- ══════════════════════════════════════════════════════════ -->
        <div>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-chart-line text-indigo-400"></i> Live Telemetry & Footprint
                </h2>
                <span class="text-xs text-slate-400">Refreshed: {{ $telemetry['generated_at'] }}</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- DB Storage Metric -->
                <div class="glow-card rounded-xl p-5 relative overflow-hidden">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Database Size</span>
                        <div class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center">
                            <i class="fa-solid fa-database"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-2xl font-extrabold text-white mono-num">{{ $telemetry['database']['database_size_mb'] }} <span class="text-sm font-semibold text-slate-400">MB</span></span>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-400">
                        <span>Tables: <strong class="text-slate-200 mono-num">{{ $telemetry['database']['total_tables'] }}</strong></span>
                        <span>MobiTrack: <strong class="text-indigo-300 mono-num">{{ $telemetry['database']['mobileshop_tables'] }}</strong></span>
                    </div>
                </div>

                <!-- Commercial Transactions Metric -->
                <div class="glow-card rounded-xl p-5 relative overflow-hidden">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Sales & Turnover</span>
                        <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                            <i class="fa-solid fa-receipt"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-2xl font-extrabold text-white mono-num">₹{{ number_format($telemetry['commercial']['total_sales_turnover'], 2) }}</span>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-400">
                        <span>Total Invoices: <strong class="text-slate-200 mono-num">{{ $telemetry['commercial']['invoices_count'] }}</strong></span>
                        <span>Khata Due: <strong class="text-amber-400 mono-num">₹{{ number_format($telemetry['commercial']['total_khata_receivables'], 0) }}</strong></span>
                    </div>
                </div>

                <!-- Hardware & Devices Metric -->
                <div class="glow-card rounded-xl p-5 relative overflow-hidden">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Inventory Catalog</span>
                        <div class="w-8 h-8 rounded-lg bg-sky-500/10 text-sky-400 flex items-center justify-center">
                            <i class="fa-solid fa-mobile-screen-button"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-2xl font-extrabold text-white mono-num">{{ $telemetry['inventory']['new_phones_stock'] + $telemetry['inventory']['secondhand_stock'] + $telemetry['inventory']['accessories_stock'] }} <span class="text-sm font-semibold text-slate-400">Units</span></span>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-400">
                        <span>Phones: <strong class="text-slate-200 mono-num">{{ $telemetry['inventory']['new_phones_stock'] }} New / {{ $telemetry['inventory']['secondhand_stock'] }} Used</strong></span>
                        <span>Repairs: <strong class="text-sky-300 mono-num">{{ $telemetry['inventory']['repair_orders'] }}</strong></span>
                    </div>
                </div>

                <!-- Server Environment Metric -->
                <div class="glow-card rounded-xl p-5 relative overflow-hidden">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Server Resources</span>
                        <div class="w-8 h-8 rounded-lg bg-purple-500/10 text-purple-400 flex items-center justify-center">
                            <i class="fa-solid fa-server"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-2xl font-extrabold text-white mono-num">{{ $telemetry['server']['disk_free_gb'] }} <span class="text-sm font-semibold text-slate-400">GB Free</span></span>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-400">
                        <span>Memory Limit: <strong class="text-slate-200 mono-num">{{ $telemetry['server']['memory_limit'] }}</strong></span>
                        <span>Max Exec: <strong class="text-slate-200 mono-num">{{ $telemetry['server']['max_execution_time'] }}s</strong></span>
                    </div>
                </div>
            </div>

            <!-- Expandable Table Module Breakdown -->
            <div class="mt-4 glow-card rounded-xl overflow-hidden">
                <button type="button" onclick="toggleTableDetails()" class="w-full px-5 py-3 text-left flex items-center justify-between bg-slate-900/60 hover:bg-slate-800/50 transition">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-300 flex items-center gap-2">
                        <i class="fa-solid fa-table-cells text-indigo-400"></i> Module Table Inventory (33 MobiTrack Tables)
                    </span>
                    <div class="flex items-center gap-2 text-xs text-slate-400">
                        <span id="table-details-hint">Click to inspect table row counts</span>
                        <i id="table-details-chevron" class="fa-solid fa-chevron-down text-slate-400 transition transform duration-200"></i>
                    </div>
                </button>

                <div id="table-details-content" class="hidden p-5 border-t border-slate-800 bg-slate-950/60">
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                        @foreach($telemetry['tables'] as $tableName => $rowCount)
                            <div class="p-3 rounded-lg bg-slate-900/80 border border-slate-800 flex flex-col justify-between">
                                <span class="text-[11px] font-mono text-slate-400 truncate" title="{{ $tableName }}">{{ str_replace(['9nj_ms_', 'ms_'], '', $tableName) }}</span>
                                <div class="mt-2 flex items-baseline justify-between">
                                    <span class="text-base font-bold text-slate-200 mono-num">{{ number_format($rowCount) }}</span>
                                    <span class="text-[10px] uppercase text-slate-500">rows</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════ -->
        <!-- SECTION 2: DATABASE BACKUP MANAGEMENT & DAILY SCHEDULE     -->
        <!-- ══════════════════════════════════════════════════════════ -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left 2 Cols: Backups Archive & Immediate Backup -->
            <div class="lg:col-span-2 space-y-6">
                <div class="glow-card rounded-2xl p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-5 border-b border-slate-800">
                        <div>
                            <h3 class="text-base font-bold text-white flex items-center gap-2">
                                <i class="fa-solid fa-box-archive text-indigo-400"></i> Database Snapshot Archive
                            </h3>
                            <p class="text-xs text-slate-400 mt-1">Full SQL dumps generated with CREATE TABLE, DROP TABLE, and table records.</p>
                        </div>
                        <button type="button" onclick="openBackupModal()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold transition shadow-md shadow-indigo-600/30">
                            <i class="fa-solid fa-plus"></i> New Snapshot
                        </button>
                    </div>

                    <!-- Backup List Table -->
                    <div class="mt-4 overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left text-xs">
                            <thead class="text-slate-400 uppercase bg-slate-950/60 border-b border-slate-800">
                                <tr>
                                    <th class="px-4 py-3 font-semibold">Backup File</th>
                                    <th class="px-4 py-3 font-semibold">Size</th>
                                    <th class="px-4 py-3 font-semibold">Generated</th>
                                    <th class="px-4 py-3 font-semibold">Age</th>
                                    <th class="px-4 py-3 font-semibold text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/60">
                                @forelse($backups as $b)
                                    <tr class="hover:bg-slate-800/30 transition">
                                        <td class="px-4 py-3.5">
                                            <div class="flex items-center gap-2">
                                                <i class="fa-solid fa-file-lines text-indigo-400 text-sm"></i>
                                                <div>
                                                    <span class="font-mono font-medium text-slate-200">{{ $b['filename'] }}</span>
                                                    @if(str_contains($b['filename'], 'PRE-RESET'))
                                                        <span class="ml-2 px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-950/80 text-amber-300 border border-amber-800/60">Safety Snapshot</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5 mono-num text-slate-300">{{ $b['size_formatted'] }}</td>
                                        <td class="px-4 py-3.5 text-slate-400">{{ $b['created_at'] }}</td>
                                        <td class="px-4 py-3.5 text-slate-400">{{ $b['age'] }}</td>
                                        <td class="px-4 py-3.5 text-right space-x-1.5 whitespace-nowrap">
                                            <a href="{{ route('dev.portal.backup.download', ['filename' => $b['filename']]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-300 border border-emerald-500/30 font-semibold transition" title="Download SQL Dump">
                                                <i class="fa-solid fa-cloud-arrow-down"></i> Download
                                            </a>
                                            <button type="button" onclick="confirmDeleteBackup('{{ $b['filename'] }}')" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-rose-600/10 hover:bg-rose-600/20 text-rose-400 border border-rose-500/20 transition" title="Delete File">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                                            <i class="fa-solid fa-folder-open text-2xl mb-2 block"></i>
                                            No SQL dumps found in archive. Click "New Snapshot" to generate your first backup.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Col: Daily Automated Backup Preferences -->
            <div class="space-y-6">
                <div class="glow-card rounded-2xl p-6">
                    <h3 class="text-base font-bold text-white flex items-center gap-2 pb-4 border-b border-slate-800">
                        <i class="fa-solid fa-clock-rotate-left text-purple-400"></i> Automated Daily Backup
                    </h3>

                    <form action="{{ route('dev.portal.settings') }}" method="POST" class="mt-4 space-y-4">
                        @csrf
                        
                        <!-- Toggle Switch -->
                        <div class="flex items-center justify-between p-3.5 rounded-xl bg-slate-900/80 border border-slate-800">
                            <div>
                                <label for="enabled" class="text-xs font-bold text-slate-200 block">Enable Daily Backup</label>
                                <span class="text-[11px] text-slate-400">Scheduled daily execution via cron/scheduler</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="enabled" id="enabled" value="1" {{ !empty($settings['enabled']) ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                            </label>
                        </div>

                        <!-- Schedule Time -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Scheduled Daily Run Time</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                                    <i class="fa-regular fa-clock"></i>
                                </span>
                                <input type="time" name="daily_time" value="{{ $settings['daily_time'] ?? '02:00' }}" class="w-full pl-9 pr-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                            </div>
                            <span class="text-[10px] text-slate-400 mt-1 block">Recommended: Low-traffic off-hours (e.g. 02:00 AM)</span>
                        </div>

                        <!-- Retention Days -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Retention Window (Days)</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                                    <i class="fa-solid fa-calendar-days"></i>
                                </span>
                                <input type="number" name="retention_days" min="1" max="365" value="{{ $settings['retention_days'] ?? 14 }}" class="w-full pl-9 pr-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                            </div>
                            <span class="text-[10px] text-slate-400 mt-1 block">Backups older than this will be automatically pruned.</span>
                        </div>

                        <!-- Last Run Status -->
                        <div class="p-3 rounded-xl bg-slate-950/80 border border-slate-800/80 text-xs space-y-1">
                            <div class="flex justify-between text-slate-400">
                                <span>Last Run:</span>
                                <span class="text-slate-200 font-mono">{{ $settings['last_backup_at'] ?? 'Never' }}</span>
                            </div>
                            <div class="flex justify-between text-slate-400">
                                <span>Last Status:</span>
                                <span class="font-semibold {{ ($settings['last_backup_status'] ?? '') === 'success' ? 'text-emerald-400' : 'text-slate-400' }}">
                                    {{ ucfirst($settings['last_backup_status'] ?? 'N/A') }}
                                </span>
                            </div>
                        </div>

                        <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-100 text-xs font-bold transition border border-slate-700/60 shadow">
                            <i class="fa-solid fa-floppy-disk mr-1.5"></i> Save Schedule Settings
                        </button>
                    </form>

                    <!-- CLI Command Info -->
                    <div class="mt-4 p-3 rounded-xl bg-slate-950 border border-slate-800 font-mono text-[11px] text-slate-400">
                        <span class="text-indigo-400 block font-semibold mb-1">Artisan Command:</span>
                        <div class="flex items-center justify-between bg-slate-900 px-2.5 py-1.5 rounded border border-slate-800">
                            <span class="text-slate-300">php artisan mobileshop:backup-db</span>
                            <button onclick="navigator.clipboard.writeText('php artisan mobileshop:backup-db')" title="Copy Command" class="text-slate-500 hover:text-slate-300"><i class="fa-regular fa-copy"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════ -->
        <!-- SECTION 3: DANGER ZONE — CLIENT HANDOVER DATABASE RESET   -->
        <!-- ══════════════════════════════════════════════════════════ -->
        <div id="danger-zone" class="glow-danger rounded-2xl p-6 sm:p-8 space-y-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-rose-900/60 pb-5">
                <div>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-extrabold uppercase tracking-wider bg-rose-500/20 text-rose-300 border border-rose-500/40">
                        Irreversible Action &bull; Handover Ready
                    </span>
                    <h3 class="text-xl font-black text-rose-100 tracking-tight mt-2 flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation text-rose-500"></i> Client Delivery & Database Clean Reset
                    </h3>
                    <p class="text-xs text-rose-200/80 mt-1 max-w-3xl">
                        Designed specifically for client handover. Clears all test invoices, purchases, IMEI stock entries, customer khata ledger lines, repair jobs, and login tokens, restoring sequence numbers back to <span class="font-mono font-bold text-white">#0001</span>.
                    </p>
                </div>
                <div class="shrink-0">
                    <button type="button" onclick="openResetModal()" class="px-5 py-3 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold tracking-wide uppercase transition shadow-xl shadow-rose-600/40 flex items-center gap-2">
                        <i class="fa-solid fa-broom"></i> Reset Database For Handover
                    </button>
                </div>
            </div>

            <!-- Comparison of What is Cleared vs Preserved -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
                <!-- Cleared -->
                <div class="p-4 rounded-xl bg-rose-950/40 border border-rose-800/40 space-y-3">
                    <h4 class="font-bold text-rose-200 uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-circle-xmark text-rose-400"></i> Wiped Clean For Delivery
                    </h4>
                    <ul class="space-y-1.5 text-rose-300/90 list-disc list-inside">
                        <li>All customer sales invoices & credit notes</li>
                        <li>All vendor purchase orders & goods receipt notes</li>
                        <li>All phone IMEI items (new & second-hand inventory)</li>
                        <li>All customer khata debit/credit ledger transactions</li>
                        <li>Customer EMI payment installments & down-payments</li>
                        <li>Repair service desk work orders & diagnosis logs</li>
                        <li>Customer phone advance balance pools reset to ₹0</li>
                        <li>Supplier ledger balances & advance pools reset to ₹0</li>
                        <li>All invoice & GRN serial counters reset to start at #0001</li>
                        <li>Test invite registration tokens & OTP reset requests</li>
                    </ul>
                </div>

                <!-- Preserved -->
                <div class="p-4 rounded-xl bg-emerald-950/40 border border-emerald-800/40 space-y-3">
                    <h4 class="font-bold text-emerald-200 uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-emerald-400"></i> Intact & Preserved
                    </h4>
                    <ul class="space-y-1.5 text-emerald-300/90 list-disc list-inside">
                        <li>Super Admin and Store Administrator accounts</li>
                        <li>Staff roles, designations, and permissions matrix</li>
                        <li>Master catalog definitions (brands, categories, accessories masters)</li>
                        <li>Company profile, tax rates, GSTIN configuration & settings</li>
                        <li>Store branding, logo assets, and custom theme presets</li>
                        <li><strong class="text-white">Pre-Reset Safety Snapshot:</strong> An emergency full SQL backup is generated automatically before tables are purged!</li>
                    </ul>
                </div>
            </div>
        </div>
    </main>

    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- MODAL 1: CREATE BACKUP NOW                                 -->
    <!-- ══════════════════════════════════════════════════════════ -->
    <div id="backupModal" class="fixed inset-0 z-50 hidden bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 space-y-5 shadow-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-database text-indigo-400"></i> Generate Database Snapshot
                </h3>
                <button type="button" onclick="closeBackupModal()" class="text-slate-400 hover:text-slate-200"><i class="fa-solid fa-xmark"></i></button>
            </div>
            
            <form id="createBackupForm" action="{{ route('dev.portal.backup') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Backup Identifier Tag</label>
                    <input type="text" name="tag" id="backupTag" placeholder="e.g. pre-launch, demo-milestone, v1.0" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    <span class="text-[10px] text-slate-400 mt-1 block">Optional label attached to the exported .sql filename.</span>
                </div>

                <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 text-[11px] text-slate-400 space-y-1">
                    <p><i class="fa-solid fa-info-circle text-indigo-400 mr-1"></i> Includes all 79 tables, schemas, indexes, and current records.</p>
                    <p><i class="fa-solid fa-bolt text-amber-400 mr-1"></i> Dumper engine: Native mysqldump (with PDO fallback).</p>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-2">
                    <button type="button" onclick="closeBackupModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:text-slate-200 bg-slate-800/60">Cancel</button>
                    <button type="submit" id="backupSubmitBtn" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-md shadow-indigo-600/30 flex items-center gap-2">
                        <span id="backupSpinner" class="hidden"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                        <span id="backupBtnText">Generate Backup</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- MODAL 2: CONFIRM CLIENT HANDOVER RESET                     -->
    <!-- ══════════════════════════════════════════════════════════ -->
    <div id="resetModal" class="fixed inset-0 z-50 hidden bg-black/85 backdrop-blur-md flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-rose-700/60 rounded-2xl max-w-lg w-full p-6 space-y-5 shadow-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-rose-900/60">
                <h3 class="text-base font-black text-rose-300 flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation text-rose-500"></i> Confirm Client Handover Reset
                </h3>
                <button type="button" onclick="closeResetModal()" class="text-slate-400 hover:text-slate-200"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form id="resetDbForm" action="{{ route('dev.portal.reset') }}" method="POST" class="space-y-4">
                @csrf
                
                <div class="p-4 rounded-xl bg-rose-950/40 border border-rose-800/40 text-xs text-rose-200 space-y-2">
                    <p class="font-bold text-rose-100">⚠️ WARNING: This will purge all transaction data across 23 tables!</p>
                    <p class="text-rose-300/80">To proceed, you must type <strong class="font-mono text-white bg-rose-900/60 px-1.5 py-0.5 rounded">RESET</strong> into the confirmation box below.</p>
                </div>

                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-xs text-slate-300 cursor-pointer">
                        <input type="checkbox" name="reset_sequences" value="1" checked class="rounded bg-slate-950 border-slate-700 text-indigo-600 focus:ring-0">
                        <span>Reset all invoice & GRN counters back to start (<span class="font-mono text-indigo-300">#0001</span>)</span>
                    </label>
                    <label class="flex items-center gap-2 text-xs text-slate-300 cursor-pointer">
                        <input type="checkbox" name="clean_media" value="1" checked class="rounded bg-slate-950 border-slate-700 text-indigo-600 focus:ring-0">
                        <span>Purge temporary uploaded receipt/test attachment files</span>
                    </label>
                    <label class="flex items-center gap-2 text-xs text-slate-300 cursor-pointer">
                        <input type="checkbox" name="preserve_staff" value="1" checked class="rounded bg-slate-950 border-slate-700 text-indigo-600 focus:ring-0">
                        <span>Preserve Super Admin & registered staff credentials</span>
                    </label>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-200 mb-1">Type "RESET" to confirm:</label>
                    <input type="text" name="confirm_text" id="confirmText" required autocomplete="off" placeholder="RESET" class="w-full px-3 py-2.5 bg-slate-950 border border-rose-800/60 rounded-xl text-xs font-mono font-bold text-rose-200 focus:ring-2 focus:ring-rose-500 outline-none">
                </div>

                <div class="flex items-center justify-end space-x-3 pt-2">
                    <button type="button" onclick="closeResetModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:text-slate-200 bg-slate-800/60">Cancel</button>
                    <button type="submit" id="resetSubmitBtn" class="px-5 py-2.5 rounded-xl text-xs font-extrabold text-white bg-rose-600 hover:bg-rose-500 shadow-lg shadow-rose-600/40 flex items-center gap-2">
                        <span id="resetSpinner" class="hidden"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                        <span id="resetBtnText">Execute Handover Reset</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Hidden Form for Backup Deletion -->
    <form id="deleteBackupForm" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>

    <!-- Footer -->
    <footer class="mt-auto border-t border-slate-900 bg-slate-950 py-6 text-center text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-3">
            <span>MobiTrack Multi-Store Management Engine &bull; Super Admin Console</span>
            <div class="flex items-center space-x-4">
                <a href="{{ url($companyId . '/mobileshop/dashboard') }}" class="hover:text-slate-300">POS Dashboard</a>
                <a href="{{ url($companyId . '/mobileshop/masters') }}" class="hover:text-slate-300">Masters Hub</a>
                <span class="text-slate-600">|</span>
                <span class="font-mono text-slate-400">v2.4.0-release</span>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        function toggleTableDetails() {
            const content = document.getElementById('table-details-content');
            const chevron = document.getElementById('table-details-chevron');
            const hint = document.getElementById('table-details-hint');
            
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                chevron.classList.add('rotate-180');
                hint.textContent = 'Click to collapse table inventory';
            } else {
                content.classList.add('hidden');
                chevron.classList.remove('rotate-180');
                hint.textContent = 'Click to inspect table row counts';
            }
        }

        function openBackupModal() {
            document.getElementById('backupModal').classList.remove('hidden');
            document.getElementById('backupTag').focus();
        }

        function closeBackupModal() {
            document.getElementById('backupModal').classList.add('hidden');
        }

        function openResetModal() {
            document.getElementById('resetModal').classList.remove('hidden');
            document.getElementById('confirmText').value = '';
            document.getElementById('confirmText').focus();
        }

        function closeResetModal() {
            document.getElementById('resetModal').classList.add('hidden');
        }

        // Form submit loading states
        document.getElementById('createBackupForm').addEventListener('submit', function() {
            document.getElementById('backupSpinner').classList.remove('hidden');
            document.getElementById('backupBtnText').textContent = 'Creating Dump...';
            document.getElementById('backupSubmitBtn').disabled = true;
        });

        document.getElementById('resetDbForm').addEventListener('submit', function(e) {
            const val = document.getElementById('confirmText').value.trim();
            if (val !== 'RESET') {
                e.preventDefault();
                alert("Please type 'RESET' in all capital letters to confirm this action.");
                return;
            }
            if (!confirm("FINAL CONFIRMATION:\n\nAre you ABSOLUTELY sure you want to purge all demo sales, purchases, and repair records?\n\nAn automated safety backup will be created first.")) {
                e.preventDefault();
                return;
            }
            document.getElementById('resetSpinner').classList.remove('hidden');
            document.getElementById('resetBtnText').textContent = 'Purging & Resetting...';
            document.getElementById('resetSubmitBtn').disabled = true;
        });

        function confirmDeleteBackup(filename) {
            if (confirm(`Are you sure you want to permanently delete the backup:\n\n${filename}?`)) {
                const form = document.getElementById('deleteBackupForm');
                form.action = `{{ url('home/ad/backup') }}/${filename}`;
                form.submit();
            }
        }
    </script>
</body>
</html>
