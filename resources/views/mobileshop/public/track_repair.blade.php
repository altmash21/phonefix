@extends('mobileshop.public.layout')

@section('title', 'Live Repair Status Tracker — Maurya Mobile Service Lab')
@section('meta_description', 'Track real-time service progression of your smartphone at Maurya Mobile Service Lab. View live diagnostic stages, parts replacement, and ready-for-pickup alerts.')

@section('content')

    <!-- ════ 1. EDITORIAL HEADER ════ -->
    <div class="border-b border-hairline-soft bg-canvas py-12 px-4 sm:px-6 lg:px-8 text-center">
        <div class="max-w-2xl mx-auto space-y-3">
            <span class="inline-block px-3 py-1 bg-surface-soft border border-hairline rounded-full text-[11px] font-bold uppercase tracking-wider text-ink">
                Service Lab Tracker
            </span>
            <h1 class="text-[28px] font-bold text-ink tracking-tight">Track Your Device Repair Live</h1>
            <p class="text-[15px] text-muted leading-relaxed">
                Enter your Job Sheet Ticket Number (printed on your counter receipt or sent via SMS) to view real-time laboratory updates.
            </p>
        </div>
    </div>

    <!-- ════ 2. TRACKER SEARCH CONTAINER ════ -->
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-10 space-y-8">

        <!-- Search Form Card (Airbnb Text Input & Primary Button Spec) -->
        <div class="bg-canvas rounded-[14px] p-6 sm:p-8 border border-hairline shadow-airbnb-tier space-y-4">
            <form action="{{ route('public.track_repair') }}" method="GET" class="space-y-4">
                <div>
                    <label for="ticket_number" class="block text-[12px] font-bold uppercase tracking-wider text-muted mb-1.5">
                        Repair Job Sheet Ticket Number
                    </label>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <div class="relative flex-1">
                            <!-- Airbnb text-input component: 56px height, 8px radius, 1px hairline, focus 2px ink -->
                            <input type="text" id="ticket_number" name="ticket_number" value="{{ request('ticket_number') }}" required 
                                   placeholder="e.g. REP-2026-0042 or 0042"
                                   class="w-full h-14 pl-12 pr-4 bg-canvas border border-hairline rounded-sm text-[15px] text-ink placeholder:text-muted focus:outline-none focus:border-2 focus:border-ink transition-all">
                            <svg class="w-5 h-5 text-muted absolute left-4 top-1/2 -translate-y-1/2 stroke-current fill-none stroke-[2]" viewBox="0 0 24 24">
                                <line x1="4" y1="9" x2="20" y2="9"></line>
                                <line x1="4" y1="15" x2="20" y2="15"></line>
                                <line x1="10" y1="3" x2="8" y2="21"></line>
                                <line x1="16" y1="3" x2="14" y2="21"></line>
                            </svg>
                        </div>

                        <!-- Airbnb button-primary component: 48-56px, Rausch fill, 8px radius -->
                        <button type="submit" 
                                class="h-14 px-8 bg-rausch hover:bg-rausch-active text-white font-medium text-[15px] rounded-sm transition-airbnb flex items-center justify-center gap-2 shrink-0 shadow-sm">
                            <svg class="w-4 h-4 stroke-current fill-none stroke-[2.5]" viewBox="0 0 24 24">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                            <span>Search Job Sheet</span>
                        </button>
                    </div>
                </div>
            </form>
            <p class="text-[13px] text-muted">
                Can't find your ticket number? Call our repair desk directly at <a href="tel:9876543211" class="text-ink font-semibold underline hover:text-rausch">+91 98765 43211</a>.
            </p>
        </div>

        <!-- ════ 3. RESULT DISPLAY CARD ════ -->
        @if(request()->has('ticket_number'))
            @if($ticket)
                <!-- Airbnb Card Surface with Single Shadow Tier -->
                <div class="bg-canvas rounded-[14px] border border-hairline shadow-airbnb-tier p-6 sm:p-8 space-y-8">
                    
                    <!-- Ticket Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-hairline-soft">
                        <div>
                            <span class="text-[11px] font-bold uppercase text-muted tracking-wider">Verified Job Sheet</span>
                            <h2 class="text-[24px] font-bold text-ink mt-0.5">{{ $ticket->ticket_number }}</h2>
                            <p class="text-[14px] text-muted font-medium">{{ $ticket->brand }} {{ $ticket->model }}</p>
                        </div>
                        <div>
                            <!-- Status Lozenge -->
                            <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-[12px] font-semibold tracking-wide uppercase
                                {{ $ticket->status === 'received' ? 'bg-surface-soft text-ink border border-hairline' : '' }}
                                {{ in_array($ticket->status, ['in_diagnosis', 'waiting_for_parts', 'in_repair']) ? 'bg-amber-50 text-amber-900 border border-amber-200' : '' }}
                                {{ $ticket->status === 'ready' ? 'bg-rausch text-white' : '' }}
                                {{ $ticket->status === 'delivered' ? 'bg-ink text-white' : '' }}
                            ">
                                <span class="w-2 h-2 rounded-full 
                                    {{ $ticket->status === 'ready' ? 'bg-white' : 'bg-current' }} animate-pulse"></span>
                                <span>{{ str_replace('_', ' ', $ticket->status) }}</span>
                            </span>
                        </div>
                    </div>

                    <!-- 4-Stage Visual Progress Stepper -->
                    <div class="space-y-3">
                        <div class="text-[12px] font-bold text-muted uppercase tracking-wider">Laboratory Stage Progression</div>
                        <div class="grid grid-cols-4 gap-2 text-center text-[12px] font-semibold">
                            <!-- Stage 1: Received -->
                            <div class="p-3 rounded-sm border {{ in_array($ticket->status, ['received', 'in_diagnosis', 'waiting_for_parts', 'in_repair', 'ready', 'delivered']) ? 'bg-ink text-white border-ink' : 'bg-surface-soft text-muted border-hairline' }}">
                                <div class="text-[10px] opacity-75 uppercase">Stage 1</div>
                                <div class="mt-0.5 truncate">Intake Done</div>
                            </div>

                            <!-- Stage 2: In Diagnosis / Repair -->
                            <div class="p-3 rounded-sm border {{ in_array($ticket->status, ['in_diagnosis', 'waiting_for_parts', 'in_repair', 'ready', 'delivered']) ? 'bg-ink text-white border-ink' : 'bg-surface-soft text-muted border-hairline' }}">
                                <div class="text-[10px] opacity-75 uppercase">Stage 2</div>
                                <div class="mt-0.5 truncate">In Repair</div>
                            </div>

                            <!-- Stage 3: Tested & Ready -->
                            <div class="p-3 rounded-sm border {{ in_array($ticket->status, ['ready', 'delivered']) ? 'bg-rausch text-white border-rausch' : 'bg-surface-soft text-muted border-hairline' }}">
                                <div class="text-[10px] opacity-75 uppercase">Stage 3</div>
                                <div class="mt-0.5 truncate">Ready for Pickup</div>
                            </div>

                            <!-- Stage 4: Delivered -->
                            <div class="p-3 rounded-sm border {{ $ticket->status === 'delivered' ? 'bg-ink text-white border-ink' : 'bg-surface-soft text-muted border-hairline' }}">
                                <div class="text-[10px] opacity-75 uppercase">Stage 4</div>
                                <div class="mt-0.5 truncate">Delivered</div>
                            </div>
                        </div>
                    </div>

                    <!-- Ticket Details Clean Table/Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-5 rounded-[14px] bg-surface-soft border border-hairline text-[14px]">
                        <div>
                            <span class="text-muted block text-[12px]">Customer Name</span>
                            <span class="font-semibold text-ink">{{ $ticket->customer_name }}</span>
                        </div>
                        <div>
                            <span class="text-muted block text-[12px]">Reported Fault / Problem</span>
                            <span class="font-semibold text-ink">{{ $ticket->reported_faults ?: 'Diagnostic Inspection' }}</span>
                        </div>
                        <div>
                            <span class="text-muted block text-[12px]">Intake Timestamp</span>
                            <span class="font-semibold text-ink">{{ \Carbon\Carbon::parse($ticket->received_at)->format('d M Y, h:i A') }}</span>
                        </div>
                        <div>
                            <span class="text-muted block text-[12px]">Service Total Estimate</span>
                            <span class="font-semibold text-ink">₹{{ number_format($ticket->total_amount ?? $ticket->estimated_cost ?? 0, 2) }}</span>
                        </div>
                    </div>

                    <!-- Payment / Balance Due Pill -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 rounded-[14px] bg-canvas border border-hairline">
                        <div>
                            <span class="text-[12px] uppercase font-semibold text-muted">Advance Paid: ₹{{ number_format($ticket->advance_paid ?? 0, 2) }}</span>
                            <div class="text-[18px] font-bold text-ink mt-0.5">
                                Balance Due on Pickup: <span class="text-rausch">₹{{ number_format($ticket->balance_due ?? 0, 2) }}</span>
                            </div>
                        </div>
                        @if($ticket->status === 'ready')
                            <div class="text-right">
                                <a href="https://wa.me/919876543210?text={{ rawurlencode("Hello Maurya Mobile Service Lab,\n\nI am on my way to collect my repaired device (Job Ticket #{$ticket->ticket_number}).\n\nPlease keep the handover receipt ready. Thank you!") }}" 
                                   target="_blank"
                                   class="px-5 py-2.5 bg-rausch hover:bg-rausch-active text-white font-medium text-[13px] rounded-sm transition-airbnb inline-flex items-center gap-1.5 shadow-sm">
                                    <span>Confirm Pickup on WhatsApp</span>
                                </a>
                            </div>
                        @endif
                    </div>

                </div>
            @else
                <div class="bg-canvas rounded-[14px] border border-hairline p-8 text-center space-y-3 shadow-airbnb-tier">
                    <div class="w-10 h-10 rounded-full bg-surface-strong text-ink mx-auto flex items-center justify-center font-bold">
                        !
                    </div>
                    <h3 class="text-[18px] font-bold text-ink">No Job Sheet Record Found</h3>
                    <p class="text-[14px] text-muted max-w-md mx-auto">
                        We could not find any active repair record matching ticket number <strong>"{{ request('ticket_number') }}"</strong>. Please verify the code printed on your counter receipt or call our service desk.
                    </p>
                </div>
            @endif
        @endif

    </div>

@endsection
