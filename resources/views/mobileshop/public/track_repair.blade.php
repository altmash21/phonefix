@extends('mobileshop.public.layout')

@section('title', 'Live Express Repair Job Sheet Tracker — ' . store_name())
@section('meta_description', 'Check the real-time bench diagnostic and micro-soldering status of your smartphone at ' . store_name() . ' Express Repair Lab.')

@section('content')

    <!-- ════ 1. HEADER & TICKET SEARCH (White Canvas, Cal Sans Headline) ════ -->
    <div class="bg-white border-b border-[#e5e7eb] py-14 sm:py-20 text-center">
        <div class="max-w-[768px] mx-auto px-4 sm:px-6 space-y-4">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#f5f5f5] text-[#111111] text-xs font-medium border border-[#e5e7eb]">
                <span class="w-2 h-2 rounded-full bg-[#10b981] animate-pulse"></span>
                <span>Cleanroom Service Laboratory Status</span>
            </div>
            
            <h1 class="cal-display-xl text-[#111111]">
                Live Repair Job Tracker.
            </h1>
            
            <p class="text-[#374151] text-base max-w-xl mx-auto font-normal">
                Enter your Job Sheet Ticket Number from your physical counter receipt to track diagnostic stages in real time.
            </p>

            <!-- Ticket Search Form (40px, 8px radius) -->
            <form action="{{ route('public.track_repair') }}" method="GET" class="pt-4 max-w-md mx-auto">
                <div class="relative flex items-center">
                    <input type="text" name="ticket_number" value="{{ request('ticket_number') }}" required
                           placeholder="e.g. REP-2026-0042"
                           class="w-full pl-10 pr-28 text-sm bg-white text-[#111111] placeholder:text-[#898989] border border-[#e5e7eb] rounded-[8px] h-[40px] focus:border-[#111111] focus:ring-1 focus:ring-[#111111] outline-none font-mono font-medium transition-all">
                    <svg class="w-4 h-4 text-[#6b7280] absolute left-3.5 top-1/2 -translate-y-1/2 stroke-current fill-none stroke-2" viewBox="0 0 24 24">
                        <line x1="4" y1="9" x2="20" y2="9"></line>
                        <line x1="4" y1="15" x2="20" y2="15"></line>
                        <line x1="10" y1="3" x2="8" y2="21"></line>
                        <line x1="16" y1="3" x2="14" y2="21"></line>
                    </svg>
                    <button type="submit" class="cal-btn-primary absolute right-1 top-1 h-[32px] px-3.5 text-xs">
                        <span>Track</span>
                        <svg class="w-3.5 h-3.5 stroke-current fill-none stroke-2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </button>
                </div>
            </form>

            <!-- Sample Demo Tickets -->
            @if(isset($sampleTickets) && count($sampleTickets) > 0)
            <div class="pt-2 flex items-center justify-center gap-2 text-xs text-[#6b7280] flex-wrap">
                <span>Quick demo jobs:</span>
                @foreach($sampleTickets as $st)
                <a href="{{ route('public.track_repair', ['ticket_number' => $st->ticket_number]) }}" 
                   class="font-mono text-[#111111] underline hover:text-[#3b82f6] px-1.5 py-0.5 rounded transition-colors">
                    {{ $st->ticket_number }} ({{ $st->brand }})
                </a>
                @endforeach
            </div>
            @endif

            <div class="text-xs text-[#6b7280] pt-1">
                Need counter assistance? WhatsApp our service desk at <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', store_whatsapp()) }}" class="text-[#111111] font-semibold underline hover:text-[#3b82f6]">{{ store_whatsapp() }}</a>.
            </div>
        </div>
    </div>

    <!-- ════ 2. TICKET STATUS RESULTS (Cal.com Product Card Chrome) ════ -->
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 py-12 sm:py-16">

        @if($ticket)
        <div class="cal-product-card bg-white p-6 sm:p-10 space-y-8">
            
            <!-- Ticket Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-[#e5e7eb]">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-[#6b7280] block">Verified Bench Record</span>
                    <h2 class="cal-display-md text-[#111111] mt-1">
                        {{ $ticket->brand }} {{ $ticket->model }}
                    </h2>
                    <div class="text-xs text-[#6b7280] mt-1.5 space-x-2">
                        <span>Customer: <strong class="text-[#111111] font-semibold">{{ $ticket->customer_name }}</strong></span>
                        <span>·</span>
                        <span>Job Sheet: <code class="text-[#111111] font-mono font-bold">{{ $ticket->ticket_number }}</code></span>
                        <span>·</span>
                        <span>Received: {{ $ticket->received_at ? date('d M Y, h:i A', strtotime($ticket->received_at)) : 'Today' }}</span>
                    </div>
                </div>

                @php
                    $statusConfig = match($ticket->status) {
                        'received', 'pending'     => ['label' => 'Intake Logged · Waiting Diagnostics', 'badge' => 'bg-[#f5f5f5] text-[#111111] border-[#e5e7eb]'],
                        'diagnosing'              => ['label' => 'Diagnostic Assessment in Progress', 'badge' => 'bg-[#eff6ff] text-[#1d4ed8] border-[#bfdbfe]'],
                        'in_progress', 'repairing'=> ['label' => 'Active Bench Micro-Soldering', 'badge' => 'bg-[#fefce8] text-[#854d0e] border-[#fef08a]'],
                        'parts_awaited'           => ['label' => 'OEM Parts In Transit to Bench', 'badge' => 'bg-[#fff7ed] text-[#c2410c] border-[#fed7aa]'],
                        'testing', 'qc'           => ['label' => '21-Point Stress & QC Testing', 'badge' => 'bg-[#ecfeff] text-[#0e7490] border-[#a5f3fc]'],
                        'ready_for_pickup'        => ['label' => 'Ready for Pickup at Counter', 'badge' => 'bg-[#ecfdf5] text-[#047857] border-[#a7f3d0]'],
                        'completed', 'delivered'  => ['label' => 'Handover Completed', 'badge' => 'bg-[#f5f5f5] text-[#111111] border-[#e5e7eb]'],
                        default                   => ['label' => ucfirst($ticket->status), 'badge' => 'bg-[#f5f5f5] text-[#111111] border-[#e5e7eb]']
                    };
                @endphp
                <div>
                    <span class="text-xs font-semibold px-3 py-1.5 rounded-full border {{ $statusConfig['badge'] }} inline-flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-current animate-pulse"></span>
                        <span>{{ $statusConfig['label'] }}</span>
                    </span>
                </div>
            </div>

            <!-- 5-Step Visual Progress Bar -->
            <div class="py-4">
                <div class="flex items-center justify-between text-xs font-semibold text-[#6b7280] mb-2.5">
                    <span>Diagnostic Progress</span>
                    <span class="text-[#111111] font-mono font-bold">{{ $progressPct }}% Complete</span>
                </div>
                
                <!-- Progress track -->
                <div class="w-full bg-[#f3f4f6] h-2 rounded-full overflow-hidden mb-8 border border-[#e5e7eb]">
                    <div class="bg-[#111111] h-full rounded-full transition-all duration-700" style="width: {{ $progressPct }}%;"></div>
                </div>

                <!-- 5 Step Nodes -->
                <div class="grid grid-cols-5 gap-2 text-center relative">
                    <!-- Step 1 -->
                    <div class="space-y-1.5 {{ $activeStep >= 1 ? 'opacity-100' : 'opacity-40' }}">
                        <div class="w-8 h-8 rounded-full mx-auto flex items-center justify-center text-xs font-semibold {{ $activeStep >= 1 ? 'bg-[#111111] text-white' : 'bg-[#e5e7eb] text-[#6b7280]' }}">
                            1
                        </div>
                        <div class="font-semibold text-[#111111] text-xs">Received</div>
                        <div class="text-[11px] text-[#6b7280] hidden sm:block">Intake & Inspection</div>
                    </div>

                    <!-- Step 2 -->
                    <div class="space-y-1.5 {{ $activeStep >= 2 ? 'opacity-100' : 'opacity-40' }}">
                        <div class="w-8 h-8 rounded-full mx-auto flex items-center justify-center text-xs font-semibold {{ $activeStep >= 2 ? 'bg-[#111111] text-white' : 'bg-[#e5e7eb] text-[#6b7280]' }}">
                            2
                        </div>
                        <div class="font-semibold text-[#111111] text-xs">Diagnostics</div>
                        <div class="text-[11px] text-[#6b7280] hidden sm:block">Circuit Probe</div>
                    </div>

                    <!-- Step 3 -->
                    <div class="space-y-1.5 {{ $activeStep >= 3 ? 'opacity-100' : 'opacity-40' }}">
                        <div class="w-8 h-8 rounded-full mx-auto flex items-center justify-center text-xs font-semibold {{ $activeStep >= 3 ? 'bg-[#111111] text-white' : 'bg-[#e5e7eb] text-[#6b7280]' }}">
                            3
                        </div>
                        <div class="font-semibold text-[#111111] text-xs">Bench Repair</div>
                        <div class="text-[11px] text-[#6b7280] hidden sm:block">Micro-Soldering / Swap</div>
                    </div>

                    <!-- Step 4 -->
                    <div class="space-y-1.5 {{ $activeStep >= 4 ? 'opacity-100' : 'opacity-40' }}">
                        <div class="w-8 h-8 rounded-full mx-auto flex items-center justify-center text-xs font-semibold {{ $activeStep >= 4 ? 'bg-[#111111] text-white' : 'bg-[#e5e7eb] text-[#6b7280]' }}">
                            4
                        </div>
                        <div class="font-semibold text-[#111111] text-xs">QC & Stress</div>
                        <div class="text-[11px] text-[#6b7280] hidden sm:block">Hardware Validation</div>
                    </div>

                    <!-- Step 5 -->
                    <div class="space-y-1.5 {{ $activeStep >= 5 ? 'opacity-100' : 'opacity-40' }}">
                        <div class="w-8 h-8 rounded-full mx-auto flex items-center justify-center text-xs font-semibold {{ $activeStep >= 5 ? 'bg-[#10b981] text-white' : 'bg-[#e5e7eb] text-[#6b7280]' }}">
                            ✓
                        </div>
                        <div class="font-semibold text-[#111111] text-xs">Ready</div>
                        <div class="text-[11px] text-[#6b7280] hidden sm:block">Counter Handover</div>
                    </div>
                </div>
            </div>

            <!-- Faults & Billing Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6 border-t border-[#e5e7eb]">
                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-[#6b7280] mb-2">Reported Device Faults</h3>
                    <div class="bg-[#f5f5f5] rounded-[8px] p-4 text-xs text-[#111111] leading-relaxed border border-[#e5e7eb]">
                        {{ $ticket->reported_faults ?: 'Screen and circuit bench assessment in progress.' }}
                    </div>
                    <div class="mt-3 p-3 rounded-[8px] bg-white border border-[#e5e7eb] text-xs text-[#374151] flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0 text-[#10b981] stroke-current fill-none stroke-2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        <span>Protected by 90-Day Warranty & Zero Data Loss Protocol.</span>
                    </div>
                </div>

                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-[#6b7280] mb-2">Billing & Balance Overview</h3>
                    <div class="bg-[#f5f5f5] rounded-[8px] p-4 space-y-2 text-xs border border-[#e5e7eb]">
                        <div class="flex justify-between text-[#6b7280]">
                            <span>Service & Parts Estimate:</span>
                            <span class="text-[#111111] font-semibold">₹{{ number_format($ticket->estimated_cost ?? $ticket->total_amount, 0) }}</span>
                        </div>
                        <div class="flex justify-between text-[#6b7280]">
                            <span>Advance Deposit Paid:</span>
                            <span class="text-[#10b981] font-semibold">₹{{ number_format($ticket->advance_paid, 0) }}</span>
                        </div>
                        <div class="flex justify-between pt-2 border-t border-[#e5e7eb] text-[#111111] text-sm font-bold">
                            <span>Balance Payable on Pickup:</span>
                            <span class="text-[#111111] text-base font-bold">₹{{ number_format($ticket->balance_due, 0) }}</span>
                        </div>
                    </div>

                    <!-- Direct WhatsApp update button -->
                    <div class="mt-3">
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', store_whatsapp()) }}?text={{ urlencode('Hi ' . store_name() . ', I am inquiring about Job Sheet ' . $ticket->ticket_number . ' (' . $ticket->brand . ' ' . $ticket->model . ')') }}" 
                           target="_blank" 
                           class="cal-btn-secondary w-full text-xs justify-center">
                            Chat with Lead Technician on WhatsApp
                        </a>
                    </div>
                </div>
            </div>

        </div>
        @elseif(request('ticket_number'))
        <div class="cal-card text-center space-y-4 p-12">
            <div class="w-12 h-12 rounded-full bg-white border border-[#e5e7eb] text-[#ef4444] flex items-center justify-center mx-auto">
                <svg class="w-6 h-6 stroke-current fill-none stroke-2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
            </div>
            <h3 class="cal-display-sm text-[#111111]">Job Sheet Not Found</h3>
            <p class="text-[#374151] text-sm max-w-md mx-auto">
                We could not locate an active repair sheet matching "<strong class="text-[#111111]">{{ request('ticket_number') }}</strong>". Please verify the ticket code on your counter receipt.
            </p>
            <div class="pt-2 flex items-center justify-center gap-3">
                <a href="{{ route('public.track_repair', ['ticket_number' => 'REP-2026-0042']) }}" class="cal-btn-secondary text-xs">
                    Load Demo Ticket
                </a>
                <a href="tel:{{ preg_replace('/[^0-9]/', '', store_phone()) }}" class="cal-btn-primary text-xs">
                    Call Counter Desk ({{ store_phone() }})
                </a>
            </div>
        </div>
        @endif

        <!-- ════ 3. TRANSPARENT REPAIR PRICE ESTIMATOR MATRIX (Cal.com Hairline Table) ════ -->
        <div class="mt-16">
            <div class="text-left max-w-xl mb-8">
                <span class="text-xs font-semibold uppercase tracking-widest text-[#6b7280] block mb-1">Standard Rates</span>
                <h3 class="cal-display-md text-[#111111]">Repair Bench Price Schedule</h3>
                <p class="text-xs text-[#6b7280] mt-1">All services include 90-day warranty and zero data loss protocol.</p>
            </div>

            <div class="bg-white border border-[#e5e7eb] rounded-[12px] overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-[#f8f9fa] border-b border-[#e5e7eb] text-[#111111] font-semibold">
                                <th class="p-3.5">Repair Service</th>
                                <th class="p-3.5">Compatible Devices</th>
                                <th class="p-3.5">Turnaround Time</th>
                                <th class="p-3.5">Starting Price</th>
                                <th class="p-3.5 text-right">Inquiry</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#f3f4f6] text-[#374151]">
                            <tr>
                                <td class="p-3.5 font-semibold text-[#111111]">OLED / AMOLED Display Replacement</td>
                                <td class="p-3.5 text-[#6b7280]">iPhone, Samsung, OnePlus, Xiaomi</td>
                                <td class="p-3.5 font-medium text-[#111111]">Express Bench</td>
                                <td class="p-3.5 font-bold text-[#111111] text-sm">₹1,299</td>
                                <td class="p-3.5 text-right">
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', store_whatsapp()) }}?text={{ urlencode('Hi ' . store_name() . ', I need a display replacement quote') }}" target="_blank" class="text-[#111111] font-semibold underline hover:text-[#3b82f6]">Book</a>
                                </td>
                            </tr>
                            <tr>
                                <td class="p-3.5 font-semibold text-[#111111]">Original Battery Renewal</td>
                                <td class="p-3.5 text-[#6b7280]">iPhone, Samsung, Realme, Vivo</td>
                                <td class="p-3.5 font-medium text-[#111111]">~30 Mins</td>
                                <td class="p-3.5 font-bold text-[#111111] text-sm">₹899</td>
                                <td class="p-3.5 text-right">
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', store_whatsapp()) }}?text={{ urlencode('Hi ' . store_name() . ', I need a battery replacement quote') }}" target="_blank" class="text-[#111111] font-semibold underline hover:text-[#3b82f6]">Book</a>
                                </td>
                            </tr>
                            <tr>
                                <td class="p-3.5 font-semibold text-[#111111]">Motherboard Micro-Soldering (IC / Short)</td>
                                <td class="p-3.5 text-[#6b7280]">All Smartphone Brands</td>
                                <td class="p-3.5 font-medium text-[#111111]">2 – 4 Hours</td>
                                <td class="p-3.5 font-bold text-[#111111] text-sm">₹1,499</td>
                                <td class="p-3.5 text-right">
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', store_whatsapp()) }}?text={{ urlencode('Hi ' . store_name() . ', I need motherboard micro-soldering assistance') }}" target="_blank" class="text-[#111111] font-semibold underline hover:text-[#3b82f6]">Book</a>
                                </td>
                            </tr>
                            <tr>
                                <td class="p-3.5 font-semibold text-[#111111]">Charging Port Sub-Board & Mic Replacement</td>
                                <td class="p-3.5 text-[#6b7280]">Type-C & Lightning Devices</td>
                                <td class="p-3.5 font-medium text-[#111111]">~30 Mins</td>
                                <td class="p-3.5 font-bold text-[#111111] text-sm">₹499</td>
                                <td class="p-3.5 text-right">
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', store_whatsapp()) }}?text={{ urlencode('Hi ' . store_name() . ', my phone is not charging properly') }}" target="_blank" class="text-[#111111] font-semibold underline hover:text-[#3b82f6]">Book</a>
                                </td>
                            </tr>
                            <tr>
                                <td class="p-3.5 font-semibold text-[#111111]">Laser Back Glass Replacement</td>
                                <td class="p-3.5 text-[#6b7280]">iPhone 8 through 16 Pro Max</td>
                                <td class="p-3.5 font-medium text-[#111111]">~90 Mins</td>
                                <td class="p-3.5 font-bold text-[#111111] text-sm">₹1,199</td>
                                <td class="p-3.5 text-right">
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', store_whatsapp()) }}?text={{ urlencode('Hi ' . store_name() . ', I need back glass replacement') }}" target="_blank" class="text-[#111111] font-semibold underline hover:text-[#3b82f6]">Book</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

@endsection
