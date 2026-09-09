@extends('mobileshop.public.layout')

@section('title', 'Service Lab & Live Repair Job Tracker — Maurya Mobile Mumbai')
@section('meta_description', 'Track live hardware diagnostic and micro-soldering status of your smartphone at Maurya Mobile Service Laboratory Mumbai.')

@section('subnav_title', 'Support')
@section('subnav_links')
    <a href="{{ route('public.track_repair') }}" class="text-apple-ink font-semibold">Repair Tracker</a>
    <a href="{{ route('public.about') }}" class="hover:text-apple-ink">Lab Standards</a>
    <a href="{{ route('public.contact') }}" class="hover:text-apple-ink">Counter Desk</a>
@endsection
@section('subnav_cta')
    <a href="tel:9876543211" class="apple-btn-secondary-pill text-[13px] py-1.5 px-3.5">
        Call Desk
    </a>
@endsection

@section('content')

    <!-- ════ 1. EDITORIAL HEADER & TICKET SEARCH ════ -->
    <div class="bg-apple-parchment border-b border-apple-hairline py-16 sm:py-24 text-center">
        <div class="max-w-[768px] mx-auto px-4 space-y-4">
            <span class="apple-caption-strong text-apple-primary uppercase tracking-widest text-[12px]">
                Service Laboratory
            </span>
            <h1 class="apple-hero-display text-apple-ink">
                Track your repair live.
            </h1>
            <p class="apple-lead text-apple-muted-48 max-w-xl mx-auto">
                Enter the Job Sheet ticket number printed on your counter receipt or received via SMS.
            </p>

            <!-- Search Form -->
            <form action="{{ route('public.track_repair') }}" method="GET" class="pt-4 max-w-md mx-auto">
                <div class="relative">
                    <input type="text" name="ticket_number" value="{{ request('ticket_number') }}" required
                           placeholder="e.g. REP-2026-0042 or 0042"
                           class="apple-search-input w-full pl-12 pr-32 text-[15px]">
                    <svg class="w-4 h-4 text-apple-muted-48 absolute left-4 top-1/2 -translate-y-1/2 stroke-current fill-none stroke-[2.2]" viewBox="0 0 24 24">
                        <line x1="4" y1="9" x2="20" y2="9"></line>
                        <line x1="4" y1="15" x2="20" y2="15"></line>
                        <line x1="10" y1="3" x2="8" y2="21"></line>
                        <line x1="16" y1="3" x2="14" y2="21"></line>
                    </svg>
                    <button type="submit" class="apple-btn-primary text-[13px] py-1.5 px-4 absolute right-1.5 top-1/2 -translate-y-1/2">
                        Track Ticket
                    </button>
                </div>
            </form>
            <div class="apple-fine-print text-apple-muted-48 pt-1">
                Need help finding your ticket? Call our service bench at <a href="tel:9876543211" class="apple-text-link">+91 98765 43211</a>.
            </div>
        </div>
    </div>

    <!-- ════ 2. TICKET STATUS RESULTS (Clean Apple Utility Card, 18px Radius, Zero Chrome Shadow) ════ -->
    <div class="max-w-[1024px] mx-auto px-4 py-16">

        @if($ticket)
        <div class="apple-utility-card !p-8 sm:!p-12 space-y-8">
            
            <!-- Ticket Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-apple-hairline">
                <div>
                    <span class="apple-caption text-apple-primary uppercase tracking-wider font-semibold">Live Job Sheet Status</span>
                    <h2 class="apple-display-md text-apple-ink mt-1">
                        {{ $ticket->brand }} {{ $ticket->model }}
                    </h2>
                    <div class="apple-body text-apple-muted-48 mt-0.5">
                        Client: <strong class="text-apple-ink">{{ $ticket->customer_name }}</strong> · Ticket: <code class="text-apple-ink font-semibold">{{ $ticket->ticket_number }}</code>
                    </div>
                </div>

                @php
                    $statusConfig = match($ticket->status) {
                        'delivered'   => ['label' => 'Delivered to Customer', 'color' => 'bg-emerald-50 text-emerald-800 border-emerald-200'],
                        'completed'   => ['label' => 'Ready for Pickup', 'color' => 'bg-emerald-50 text-emerald-800 border-emerald-200'],
                        'in_progress' => ['label' => 'Under Active Service', 'color' => 'bg-blue-50 text-blue-800 border-blue-200'],
                        'cancelled'   => ['label' => 'Service Cancelled', 'color' => 'bg-neutral-100 text-neutral-800 border-neutral-300'],
                        default       => ['label' => 'Checked-In / Queue', 'color' => 'bg-amber-50 text-amber-800 border-amber-200']
                    };
                @endphp
                <div>
                    <span class="apple-caption font-semibold px-4 py-1.5 rounded-full border {{ $statusConfig['color'] }}">
                        ● {{ $statusConfig['label'] }}
                    </span>
                </div>
            </div>

            <!-- Milestone Step Progress Tracker (Apple Minimalist) -->
            @php
                $step = match($ticket->status) {
                    'received'    => 1,
                    'in_progress' => 2,
                    'completed'   => 3,
                    'delivered'   => 4,
                    default       => 1
                };
            @endphp
            <div class="py-6">
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-6 text-left relative">
                    <!-- Step 1 -->
                    <div class="space-y-1.5 {{ $step >= 1 ? 'opacity-100' : 'opacity-40' }}">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-[13px] font-semibold {{ $step >= 1 ? 'bg-apple-primary text-white' : 'bg-apple-hairline text-apple-muted-48' }}">
                            1
                        </div>
                        <div class="apple-body-strong text-apple-ink text-[15px]">Check-In Intake</div>
                        <div class="apple-fine-print text-apple-muted-48">
                            {{ $ticket->received_at ? date('d M, h:i A', strtotime($ticket->received_at)) : 'Initial receipt' }}
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="space-y-1.5 {{ $step >= 2 ? 'opacity-100' : 'opacity-40' }}">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-[13px] font-semibold {{ $step >= 2 ? 'bg-apple-primary text-white' : 'bg-apple-hairline text-apple-muted-48' }}">
                            2
                        </div>
                        <div class="apple-body-strong text-apple-ink text-[15px]">Micro-Soldering</div>
                        <div class="apple-fine-print text-apple-muted-48">
                            Clean bench parts install
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="space-y-1.5 {{ $step >= 3 ? 'opacity-100' : 'opacity-40' }}">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-[13px] font-semibold {{ $step >= 3 ? 'bg-apple-primary text-white' : 'bg-apple-hairline text-apple-muted-48' }}">
                            3
                        </div>
                        <div class="apple-body-strong text-apple-ink text-[15px]">Quality Inspection</div>
                        <div class="apple-fine-print text-apple-muted-48">
                            {{ $ticket->completed_at ? date('d M, h:i A', strtotime($ticket->completed_at)) : '24-point hardware check' }}
                        </div>
                    </div>

                    <!-- Step 4 -->
                    <div class="space-y-1.5 {{ $step >= 4 ? 'opacity-100' : 'opacity-40' }}">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-[13px] font-semibold {{ $step >= 4 ? 'bg-apple-primary text-white' : 'bg-apple-hairline text-apple-muted-48' }}">
                            4
                        </div>
                        <div class="apple-body-strong text-apple-ink text-[15px]">Delivered</div>
                        <div class="apple-fine-print text-apple-muted-48">
                            {{ $ticket->delivered_at ? date('d M, h:i A', strtotime($ticket->delivered_at)) : 'Customer handover' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reported Faults & Billing Summary -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-6 border-t border-apple-hairline">
                <div>
                    <h3 class="apple-caption-strong text-apple-ink mb-2">Reported Device Faults</h3>
                    <div class="bg-apple-parchment rounded-[12px] p-4 text-[14px] text-apple-ink leading-relaxed">
                        {{ $ticket->reported_faults ?: 'Diagnostic assessment scheduled for display and power circuitry.' }}
                    </div>
                </div>

                <div>
                    <h3 class="apple-caption-strong text-apple-ink mb-2">Billing & Balance Overview</h3>
                    <div class="bg-apple-parchment rounded-[12px] p-4 space-y-2 text-[14px]">
                        <div class="flex justify-between text-apple-muted-48">
                            <span>Service Estimate:</span>
                            <span class="text-apple-ink font-medium">₹{{ number_format($ticket->estimated_cost ?? $ticket->total_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-apple-muted-48">
                            <span>Advance Deposit Paid:</span>
                            <span class="text-emerald-700 font-medium">₹{{ number_format($ticket->advance_paid, 2) }}</span>
                        </div>
                        <div class="flex justify-between apple-body-strong pt-2 border-t border-apple-hairline text-apple-ink">
                            <span>Balance Payable on Pickup:</span>
                            <span class="text-apple-primary">₹{{ number_format($ticket->balance_due, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        @elseif(request('ticket_number'))
        <div class="apple-utility-card !p-12 text-center space-y-4">
            <h3 class="apple-display-md text-apple-ink">Ticket number not found.</h3>
            <p class="apple-body text-apple-muted-48 max-w-md mx-auto">
                We could not locate a job sheet matching "<strong class="text-apple-ink">{{ request('ticket_number') }}</strong>". Please confirm the number or call our counter desk.
            </p>
            <div class="pt-2">
                <a href="tel:9876543211" class="apple-btn-primary">
                    Call Service Desk (+91 98765 43211)
                </a>
            </div>
        </div>
        @endif

        <!-- 3 Pillars of Maurya Service Lab -->
        <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-8 text-left">
            <div class="apple-utility-card space-y-2">
                <h3 class="apple-body-strong text-apple-ink">Zero Data Wipe Policy</h3>
                <p class="apple-caption text-apple-muted-48">
                    Hardware technicians operate under clean isolation. Personal media, documents, and banking apps are preserved without factory reset.
                </p>
            </div>
            <div class="apple-utility-card space-y-2">
                <h3 class="apple-body-strong text-apple-ink">Authentic OEM Assemblies</h3>
                <p class="apple-caption text-apple-muted-48">
                    Displays, batteries, charging ports, and camera modules sourced directly from verified authorized distributor channels.
                </p>
            </div>
            <div class="apple-utility-card space-y-2">
                <h3 class="apple-body-strong text-apple-ink">90-Day Service Guarantee</h3>
                <p class="apple-caption text-apple-muted-48">
                    Every hardware repair is warrantied for 90 days. If the replaced component exhibits anomalies, it is replaced free of charge.
                </p>
            </div>
        </div>

    </div>

@endsection
