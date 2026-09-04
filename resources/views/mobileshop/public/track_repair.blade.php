@extends('mobileshop.public.layout')

@section('title', 'Live Repair Status Tracker — MobiTrack Service Lab')
@section('meta_description', 'Track the real-time service status of your smartphone under repair at MobiTrack. View live diagnostic stages, parts replacement, and ready-for-pickup alerts.')

@section('content')

    <!-- Header Banner -->
    <div class="bg-gradient-to-b from-slate-950 via-slate-900 to-slate-950 text-white py-14 px-4 text-center">
        <div class="max-w-2xl mx-auto space-y-3">
            <span class="px-3.5 py-1.5 rounded-full bg-sky-500/20 text-sky-400 border border-sky-500/30 text-xs font-bold uppercase tracking-wider">
                MobiTrack Service Tracker
            </span>
            <h1 class="font-display font-black text-3xl sm:text-4xl text-white">Track Your Device Repair Live</h1>
            <p class="text-slate-400 text-sm">Enter your Repair Job Sheet Ticket Number (printed on your counter receipt or sent via SMS) to view real-time laboratory updates.</p>
        </div>
    </div>

    <!-- Tracker Container -->
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-12 space-y-8">

        <!-- Search Form Card -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm space-y-4">
            <form action="{{ route('public.track_repair') }}" method="GET" class="space-y-3">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Repair Job Sheet Ticket Number</label>
                <div class="flex flex-col sm:flex-row gap-2.5">
                    <div class="relative flex-1">
                        <input type="text" name="ticket_number" value="{{ request('ticket_number') }}" required 
                               placeholder="e.g. REP-2026-0042 or 0042"
                               class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:outline-none focus:border-sky-500 focus:bg-white transition-all">
                        <i data-lucide="hash" class="w-5 h-5 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    </div>
                    <button type="submit" class="px-8 py-3.5 bg-sky-600 hover:bg-sky-700 text-white font-extrabold text-xs rounded-2xl shadow-md shadow-sky-600/20 flex items-center justify-center gap-2 transition-all">
                        <i data-lucide="search" class="w-4 h-4"></i>
                        <span>Search Job Sheet</span>
                    </button>
                </div>
            </form>
            <p class="text-[11px] text-slate-400">Can't find your ticket number? Call our repair desk at <a href="tel:9876543211" class="text-sky-600 font-bold hover:underline">+91 98765 43211</a>.</p>
        </div>

        <!-- Result Display Section -->
        @if(request()->has('ticket_number'))
            @if($ticket)
                <div class="bg-white rounded-3xl border border-slate-200 shadow-md p-6 sm:p-8 space-y-8">
                    
                    <!-- Ticket Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-100">
                        <div>
                            <span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Verified Repair Sheet</span>
                            <h2 class="font-display font-black text-2xl text-slate-900 mt-0.5">{{ $ticket->ticket_number }}</h2>
                            <p class="text-sm font-semibold text-slate-600">{{ $ticket->brand }} {{ $ticket->model }}</p>
                        </div>
                        <div>
                            <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-wider
                                {{ $ticket->status === 'received' ? 'bg-amber-100 text-amber-800' : '' }}
                                {{ in_array($ticket->status, ['in_diagnosis', 'waiting_for_parts', 'in_repair']) ? 'bg-sky-100 text-sky-800' : '' }}
                                {{ $ticket->status === 'ready' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                {{ $ticket->status === 'delivered' ? 'bg-slate-100 text-slate-700' : '' }}
                            ">
                                <span class="w-2 h-2 rounded-full 
                                    {{ $ticket->status === 'ready' ? 'bg-emerald-500' : 'bg-current' }} animate-pulse"></span>
                                <span>{{ str_replace('_', ' ', $ticket->status) }}</span>
                            </span>
                        </div>
                    </div>

                    <!-- 4-Stage Visual Progress Stepper -->
                    <div class="space-y-3">
                        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Service Lab Progression</h4>
                        <div class="grid grid-cols-4 gap-2 text-center text-xs font-bold">
                            <!-- Stage 1: Received -->
                            <div class="p-3 rounded-2xl {{ in_array($ticket->status, ['received', 'in_diagnosis', 'waiting_for_parts', 'in_repair', 'ready', 'delivered']) ? 'bg-sky-600 text-white shadow-xs' : 'bg-slate-100 text-slate-400' }}">
                                <div class="text-[10px] opacity-80 uppercase">Stage 1</div>
                                <div class="mt-0.5">Intake Received</div>
                            </div>

                            <!-- Stage 2: Diagnosis -->
                            <div class="p-3 rounded-2xl {{ in_array($ticket->status, ['in_diagnosis', 'waiting_for_parts', 'in_repair', 'ready', 'delivered']) ? 'bg-sky-600 text-white shadow-xs' : 'bg-slate-100 text-slate-400' }}">
                                <div class="text-[10px] opacity-80 uppercase">Stage 2</div>
                                <div class="mt-0.5">In Repair</div>
                            </div>

                            <!-- Stage 3: Ready -->
                            <div class="p-3 rounded-2xl {{ in_array($ticket->status, ['ready', 'delivered']) ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 text-slate-400' }}">
                                <div class="text-[10px] opacity-80 uppercase">Stage 3</div>
                                <div class="mt-0.5">Tested & Ready</div>
                            </div>

                            <!-- Stage 4: Delivered -->
                            <div class="p-3 rounded-2xl {{ $ticket->status === 'delivered' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-400' }}">
                                <div class="text-[10px] opacity-80 uppercase">Stage 4</div>
                                <div class="mt-0.5">Delivered</div>
                            </div>
                        </div>
                    </div>

                    <!-- Ticket Details Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-5 rounded-2xl bg-slate-50 border border-slate-100 text-xs">
                        <div>
                            <span class="text-slate-400 block font-medium">Customer Name</span>
                            <span class="font-bold text-slate-900 text-sm">{{ $ticket->customer_name }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block font-medium">Reported Faults</span>
                            <span class="font-bold text-slate-900 text-sm">{{ $ticket->reported_faults ?: 'Diagnostic Inspection' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block font-medium">Date Received</span>
                            <span class="font-bold text-slate-700">{{ \Carbon\Carbon::parse($ticket->received_at)->format('d M Y, h:i A') }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block font-medium">Estimated / Final Total</span>
                            <span class="font-bold text-slate-900 text-sm">₹{{ number_format($ticket->total_amount ?? $ticket->estimated_cost ?? 0, 2) }}</span>
                        </div>
                    </div>

                    <!-- Payment Status -->
                    <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-100 border border-slate-200/80">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-500">Advance Paid: ₹{{ number_format($ticket->advance_paid ?? 0, 2) }}</span>
                            <div class="text-lg font-black text-slate-900">
                                Balance Due on Pickup: <span class="text-rose-600">₹{{ number_format($ticket->balance_due ?? 0, 2) }}</span>
                            </div>
                        </div>
                        @if($ticket->status === 'ready')
                            <div class="text-right">
                                <span class="px-3 py-1.5 rounded-xl bg-emerald-600 text-white font-bold text-xs shadow-xs inline-flex items-center gap-1">
                                    <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                    <span>Ready for Pickup!</span>
                                </span>
                            </div>
                        @endif
                    </div>

                </div>
            @else
                <div class="bg-white rounded-3xl border border-rose-200 p-8 text-center space-y-3 shadow-xs">
                    <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 mx-auto flex items-center justify-center">
                        <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                    </div>
                    <h3 class="font-display font-bold text-lg text-slate-900">No Job Sheet Found</h3>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto">We could not find any active repair record matching ticket number <strong>"{{ request('ticket_number') }}"</strong>. Please verify the number on your bill or call our service desk.</p>
                </div>
            @endif
        @endif

    </div>

@endsection
