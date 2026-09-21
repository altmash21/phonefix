@extends('mobileshop.public.layout')

@section('title', 'Contact Service Desk & Location — ' . store_name())
@section('meta_description', 'Contact ' . store_name() . ' repair laboratory and mobile accessories counter in ' . store_city() . '. Get store directions, contact numbers, and WhatsApp service desk.')

@section('content')

    <!-- ════ 1. HEADER (White Canvas, Cal Sans Display) ════ -->
    <div class="bg-white border-b border-[#e5e7eb] py-14 sm:py-20 text-center">
        <div class="max-w-[768px] mx-auto px-4 sm:px-6 space-y-4">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#f5f5f5] text-[#111111] text-xs font-medium border border-[#e5e7eb]">
                <span class="w-2 h-2 rounded-full bg-[#10b981]"></span>
                <span>{{ store_name() }} &bull; {{ store_city() }}</span>
            </div>
            
            <h1 class="cal-display-xl text-[#111111]">
                Visit our workbench or reach out.
            </h1>
            
            <p class="text-[#374151] text-base max-w-xl mx-auto font-normal">
                Have questions regarding display or battery repair turnaround, live accessory availability, or custom IC micro-soldering? We are ready to assist.
            </p>
        </div>
    </div>

    <!-- ════ 2. MAIN GRID CONTAINER ════ -->
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 py-12 sm:py-16">
        
        <!-- Flash Alert Message -->
        @if(session('success'))
            <div class="mb-8 p-4 rounded-[8px] bg-[#f5f5f5] border border-[#e5e7eb] text-[#111111] flex items-center gap-3 text-sm font-semibold">
                <svg class="w-5 h-5 text-[#10b981] shrink-0 stroke-current fill-none stroke-2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
            
            <!-- LEFT COLUMN: Store Info, Hours & Direct Contact -->
            <div id="location" class="lg:col-span-5 space-y-6">
                <div>
                    <h2 class="cal-display-md text-[#111111]">Service Desk Details</h2>
                    <p class="text-[#6b7280] text-xs mt-1">Located centrally in {{ store_city() }}.</p>
                </div>

                <!-- Showroom Address Card -->
                <div class="cal-product-card space-y-2.5">
                    <span class="text-[11px] font-semibold text-[#6b7280] uppercase tracking-wider block">Physical Counter</span>
                    <h3 class="font-semibold text-[#111111] text-base">{{ store_name() }}</h3>
                    <p class="text-xs text-[#374151] leading-relaxed">
                        {{ store_address() }}
                    </p>
                    <div class="pt-2">
                        <a href="https://maps.google.com/?q={{ urlencode(store_address()) }}" target="_blank" class="text-[#111111] hover:text-[#3b82f6] text-xs font-semibold underline inline-flex items-center gap-1">
                            <span>Open in Google Maps</span>
                            <svg class="w-3.5 h-3.5 stroke-current fill-none stroke-2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"></polyline></svg>
                        </a>
                    </div>
                </div>

                <!-- Operating Hours Card -->
                <div class="cal-product-card space-y-2.5">
                    <span class="text-[11px] font-semibold text-[#6b7280] uppercase tracking-wider block">Store & Lab Timings</span>
                    <h3 class="font-semibold text-[#111111] text-base">Monday through Sunday</h3>
                    <div class="space-y-2 text-xs text-[#374151]">
                        <div class="flex justify-between">
                            <span class="text-[#6b7280]">Accessories Counter:</span>
                            <strong class="text-[#111111] font-semibold">10:00 AM – 9:30 PM</strong>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[#6b7280]">Cleanroom Repair Bench:</span>
                            <strong class="text-[#111111] font-semibold">10:30 AM – 8:30 PM</strong>
                        </div>
                    </div>
                    <p class="text-[#898989] pt-2 border-t border-[#f3f4f6] text-[11px]">
                        Open all 7 days for emergency phone repairs and walk-in accessory purchases.
                    </p>
                </div>

                <!-- Direct Numbers Card -->
                <div class="cal-product-card space-y-3">
                    <span class="text-[11px] font-semibold text-[#6b7280] uppercase tracking-wider block">Direct Communication</span>
                    <div class="space-y-2 text-xs">
                        <a href="tel:{{ preg_replace('/[^0-9]/', '', store_phone()) }}" class="flex items-center justify-between p-3 rounded-[8px] bg-[#f8f9fa] hover:bg-[#f3f4f6] border border-[#e5e7eb] transition-colors">
                            <div class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 text-[#111111] stroke-current fill-none stroke-2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                <span class="font-medium text-[#111111]">Counter Phone</span>
                            </div>
                            <span class="font-semibold text-[#111111]">{{ store_phone() }}</span>
                        </a>

                        @php
                            $waNum = preg_replace('/[^0-9]/', '', store_whatsapp());
                            if (strlen($waNum) === 10) $waNum = '91' . $waNum;
                            $waInquiryMsg = "Hi " . store_name() . ", I have an inquiry regarding mobile accessories / express repair services.";
                        @endphp
                        <a href="https://wa.me/{{ $waNum }}?text={{ rawurlencode($waInquiryMsg) }}" target="_blank" class="flex items-center justify-between p-3 rounded-[8px] bg-[#f8f9fa] hover:bg-[#f3f4f6] border border-[#e5e7eb] transition-colors">
                            <div class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 text-[#10b981] fill-current" viewBox="0 0 24 24"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                                <span class="font-medium text-[#111111]">WhatsApp Tech Desk</span>
                            </div>
                            <span class="font-semibold text-[#10b981]">Chat Now</span>
                        </a>
                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN: Customer Inquiry Form -->
            <div id="inquiry" class="lg:col-span-7 cal-product-card p-6 sm:p-8 space-y-6">
                <div>
                    <h2 class="cal-display-md text-[#111111]">Send a Service Inquiry</h2>
                    <p class="text-[#6b7280] text-xs mt-1">Our technician will respond via WhatsApp or direct phone call.</p>
                </div>

                <form action="{{ route('public.contact.submit') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="text-xs font-semibold text-[#111111] block mb-1">Full Name *</label>
                        <input type="text" name="name" required placeholder="Rahul Sharma" 
                               value="{{ old('name') }}"
                               class="w-full text-sm bg-white border border-[#e5e7eb] rounded-[8px] h-[40px] px-3 focus:border-[#111111] focus:ring-1 focus:ring-[#111111] outline-none">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-semibold text-[#111111] block mb-1">Phone Number (WhatsApp) *</label>
                            <input type="tel" name="phone" required placeholder="98765 43210" 
                                   value="{{ old('phone') }}"
                                   class="w-full text-sm bg-white border border-[#e5e7eb] rounded-[8px] h-[40px] px-3 focus:border-[#111111] focus:ring-1 focus:ring-[#111111] outline-none">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-[#111111] block mb-1">Email Address (Optional)</label>
                            <input type="email" name="email" placeholder="rahul@example.com" 
                                   value="{{ old('email') }}"
                                   class="w-full text-sm bg-white border border-[#e5e7eb] rounded-[8px] h-[40px] px-3 focus:border-[#111111] focus:ring-1 focus:ring-[#111111] outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-[#111111] block mb-1">Service or Component Needed</label>
                        <select name="subject" class="w-full text-sm cursor-pointer bg-white border border-[#e5e7eb] rounded-[8px] h-[40px] px-3 focus:border-[#111111] focus:ring-1 focus:ring-[#111111] outline-none">
                            <option value="screen_replacement">Screen / OLED Display Replacement</option>
                            <option value="battery_replacement">Battery Health Renewal</option>
                            <option value="motherboard_repair">Motherboard Micro-Soldering (Dead / Short Circuit)</option>
                            <option value="charging_port">Charging Port / Mic Flex Repair</option>
                            <option value="back_glass">Laser Back Glass Replacement</option>
                            <option value="fast_charger">Fast Charger (GaN 65W/120W) / Cable</option>
                            <option value="covers_glass">Tough Cases & 11D Tempered Glass</option>
                            <option value="other">Other Accessory or Parts Inquiry</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-[#111111] block mb-1">Phone Model & Problem Symptoms *</label>
                        <textarea name="message" rows="4" required 
                                  placeholder="Mention your exact smartphone model (e.g. iPhone 13, OnePlus 11R) and what symptoms or spare parts you need..."
                                  class="w-full rounded-[8px] border border-[#e5e7eb] p-3 text-sm text-[#111111] outline-none focus:border-[#111111] focus:ring-1 focus:ring-[#111111] resize-none">{{ old('message') }}</textarea>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="cal-btn-primary w-full justify-center h-[44px]">
                            Submit Inquiry to Technician
                        </button>
                    </div>

                    <p class="text-xs text-[#898989] text-center pt-1">
                        By submitting this inquiry, you will receive a direct reply from our {{ store_name() }} workbench team. Zero spam.
                    </p>
                </form>
            </div>

        </div>

    </div>

@endsection
