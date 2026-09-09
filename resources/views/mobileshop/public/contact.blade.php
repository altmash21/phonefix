@extends('mobileshop.public.layout')

@section('title', 'Contact & Showroom Location — Maurya Mobile Mumbai')
@section('meta_description', 'Visit Maurya Mobile showroom on Linking Road, Bandra West, Mumbai. Get store directions, contact numbers, WhatsApp chat, and submit smartphone inquiries.')

@section('content')

    <!-- ════ 1. EDITORIAL HEADER ════ -->
    <div class="border-b border-hairline-soft bg-canvas py-14 px-4 sm:px-6 lg:px-8 text-center">
        <div class="max-w-3xl mx-auto space-y-3">
            <span class="inline-block px-3 py-1 bg-surface-soft border border-hairline rounded-full text-[11px] font-bold uppercase tracking-wider text-ink">
                We're Here For You
            </span>
            <h1 class="text-[28px] sm:text-[36px] font-bold text-ink tracking-tight">Visit Our Showroom or Get In Touch</h1>
            <p class="text-[15px] text-muted max-w-xl mx-auto leading-relaxed">
                Have a question about phone availability, buyback estimates, or repair turnaround? Reach out directly to our store desk.
            </p>
        </div>
    </div>

    <!-- ════ 2. MAIN GRID CONTAINER ════ -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        
        <!-- Flash Alert -->
        @if(session('success'))
            <div class="mb-8 p-4 rounded-[14px] bg-surface-soft border border-hairline text-ink flex items-center gap-3 text-[14px] font-medium shadow-airbnb-tier">
                <span class="w-2 h-2 rounded-full bg-rausch"></span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
            
            <!-- Left: Contact Details & Store Hours (5 Cols) -->
            <div class="lg:col-span-5 space-y-6">
                <div>
                    <h2 class="text-[22px] font-bold text-ink">Showroom Details</h2>
                    <p class="text-[14px] text-muted mt-1">Conveniently located in the heart of Bandra's shopping district.</p>
                </div>

                <!-- Info Cards -->
                <div class="space-y-4">
                    <!-- Address Card -->
                    <div class="p-5 rounded-[14px] bg-canvas border border-hairline shadow-airbnb-tier flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full bg-surface-strong text-ink flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 stroke-current fill-none stroke-[1.8]" viewBox="0 0 24 24">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                        </div>
                        <div>
                            <strong class="font-semibold text-[15px] text-ink block">Physical Showroom</strong>
                            <p class="text-[13px] text-muted leading-relaxed mt-1">
                                Shop #14, Linking Road, Near Bandra Station West,<br>
                                Mumbai, Maharashtra 400050
                            </p>
                        </div>
                    </div>

                    <!-- Phone & WhatsApp Card -->
                    <div class="p-5 rounded-[14px] bg-canvas border border-hairline shadow-airbnb-tier flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full bg-surface-strong text-ink flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 stroke-current fill-none stroke-[1.8]" viewBox="0 0 24 24">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                            </svg>
                        </div>
                        <div class="space-y-1">
                            <strong class="font-semibold text-[15px] text-ink block">Direct Desk & Lab</strong>
                            <p class="text-[13px] text-muted">Counter Desk: <a href="tel:9876543210" class="font-semibold text-ink hover:text-rausch">+91 98765 43210</a></p>
                            <p class="text-[13px] text-muted">Service Lab: <a href="tel:9876543211" class="font-semibold text-ink hover:text-rausch">+91 98765 43211</a></p>
                            <div class="pt-1">
                                <a href="https://wa.me/919876543210" target="_blank" class="inline-flex items-center gap-1.5 text-[13px] font-semibold text-rausch hover:underline">
                                    <span>Chat live on WhatsApp →</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Timings Card -->
                    <div class="p-5 rounded-[14px] bg-canvas border border-hairline shadow-airbnb-tier flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full bg-surface-strong text-ink flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 stroke-current fill-none stroke-[1.8]" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                        </div>
                        <div>
                            <strong class="font-semibold text-[15px] text-ink block">Operating Hours</strong>
                            <p class="text-[13px] text-muted mt-0.5"><strong>Monday – Sunday:</strong> 10:00 AM – 9:30 PM</p>
                            <span class="inline-block text-[11px] font-bold text-ink mt-1 bg-surface-soft px-2 py-0.5 rounded-full border border-hairline">
                                Open All 7 Days
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Directions Card -->
                <div class="rounded-[14px] border border-hairline bg-surface-soft p-6 text-center space-y-3">
                    <h4 class="font-semibold text-[15px] text-ink">Showroom Directions</h4>
                    <p class="text-[13px] text-muted max-w-xs mx-auto">Located 5 minutes walk from Bandra Suburban Railway Station West. Valet and street parking available.</p>
                    <a href="https://maps.google.com/?q=Linking+Road+Bandra+West+Mumbai" target="_blank" 
                       class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-canvas border border-hairline hover:border-ink rounded-sm text-[13px] font-medium text-ink transition-airbnb shadow-airbnb-tier">
                        <span>Open in Google Maps</span>
                    </a>
                </div>
            </div>

            <!-- Right: Airbnb-Styled Form Card (7 Cols) -->
            <div class="lg:col-span-7 bg-canvas rounded-[14px] p-8 sm:p-10 border border-hairline shadow-airbnb-tier space-y-6">
                <div>
                    <h2 class="text-[22px] font-bold text-ink">Send an Inquiry or Request Callback</h2>
                    <p class="text-[14px] text-muted mt-1">Our sales and service counter replies within 30 minutes during store hours.</p>
                </div>

                <form action="{{ route('public.contact.submit') }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Full Name Input -->
                        <div>
                            <label class="block text-[12px] font-bold text-muted uppercase tracking-wider mb-1.5">Full Name *</label>
                            <input type="text" name="name" required placeholder="e.g. Rahul Sharma"
                                   class="w-full h-14 px-4 bg-canvas border border-hairline rounded-sm text-[14px] text-ink placeholder:text-muted focus:outline-none focus:border-2 focus:border-ink transition-all">
                        </div>

                        <!-- Phone Number Input -->
                        <div>
                            <label class="block text-[12px] font-bold text-muted uppercase tracking-wider mb-1.5">Phone Number *</label>
                            <input type="tel" name="phone" required placeholder="e.g. 9876543210"
                                   class="w-full h-14 px-4 bg-canvas border border-hairline rounded-sm text-[14px] text-ink placeholder:text-muted focus:outline-none focus:border-2 focus:border-ink transition-all">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Email Input -->
                        <div>
                            <label class="block text-[12px] font-bold text-muted uppercase tracking-wider mb-1.5">Email Address</label>
                            <input type="email" name="email" placeholder="rahul@example.com"
                                   class="w-full h-14 px-4 bg-canvas border border-hairline rounded-sm text-[14px] text-ink placeholder:text-muted focus:outline-none focus:border-2 focus:border-ink transition-all">
                        </div>

                        <!-- Inquiry Department -->
                        <div>
                            <label class="block text-[12px] font-bold text-muted uppercase tracking-wider mb-1.5">Inquiry Type</label>
                            <select name="subject" 
                                    class="w-full h-14 px-4 bg-canvas border border-hairline rounded-sm text-[14px] text-ink focus:outline-none focus:border-2 focus:border-ink transition-all cursor-pointer">
                                <option value="phone_purchase">Brand New Smartphone Purchase</option>
                                <option value="secondhand">Certified Pre-Owned Inquiry</option>
                                <option value="repair">Repair Diagnosis / Price Quote</option>
                                <option value="buyback">Sell My Phone / Buyback Valuation</option>
                                <option value="emi">0% EMI Financing Scheme</option>
                            </select>
                        </div>
                    </div>

                    <!-- Message Textarea -->
                    <div>
                        <label class="block text-[12px] font-bold text-muted uppercase tracking-wider mb-1.5">Message / Device Details *</label>
                        <textarea name="message" rows="4" required placeholder="Tell us which brand/model you are looking for or describe your device fault..."
                                  class="w-full p-4 bg-canvas border border-hairline rounded-sm text-[14px] text-ink placeholder:text-muted focus:outline-none focus:border-2 focus:border-ink transition-all"></textarea>
                    </div>

                    <!-- Airbnb Primary Button (48px height, 8px radius, Rausch color) -->
                    <div class="pt-2">
                        <button type="submit" 
                                class="w-full h-12 bg-rausch hover:bg-rausch-active text-white font-medium text-[15px] rounded-sm transition-airbnb flex items-center justify-center gap-2 shadow-sm">
                            <span>Send Message to Store Desk</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

@endsection
