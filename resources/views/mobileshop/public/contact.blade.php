@extends('mobileshop.public.layout')

@section('title', 'Visit Bandra Showroom & Contact Desk — Maurya Mobile Mumbai')
@section('meta_description', 'Visit Maurya Mobile showroom on Linking Road, Bandra West, Mumbai. Get store directions, contact numbers, WhatsApp chat, and submit smartphone inquiries.')

@section('subnav_title', 'Showroom & Contact')

@section('content')

    <!-- ════ 1. EDITORIAL HEADER (Parchment Canvas) ════ -->
    <div class="bg-apple-parchment border-b border-apple-hairline py-16 sm:py-24 text-center">
        <div class="max-w-[768px] mx-auto px-4 space-y-3">
            <span class="apple-caption-strong text-apple-primary uppercase tracking-widest text-[12px]">
                Showroom Bandra West
            </span>
            <h1 class="apple-hero-display text-apple-ink">
                Visit our showroom or connect with our desk.
            </h1>
            <p class="apple-lead text-apple-muted-48 max-w-xl mx-auto">
                Have questions about live smartphone availability, trade-in estimates, or express repairs? We're here to help.
            </p>
        </div>
    </div>

    <!-- ════ 2. MAIN GRID CONTAINER (Store Utility Cards, 18px Radius, Zero Chrome Shadow) ════ -->
    <div class="max-w-[1024px] mx-auto px-4 py-16">
        
        <!-- Flash Alert Message -->
        @if(session('success'))
            <div class="mb-10 p-5 rounded-[18px] bg-emerald-50 border border-emerald-200 text-emerald-900 flex items-center gap-3 text-[15px] font-medium">
                <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
            
            <!-- LEFT COLUMN (Span 5): Store Info, Hours & Direct Contact -->
            <div id="location" class="lg:col-span-5 space-y-6">
                <div>
                    <h2 class="apple-display-md text-apple-ink">Showroom Details</h2>
                    <p class="apple-body text-apple-muted-48 mt-1">Conveniently located on Linking Road, Bandra West.</p>
                </div>

                <!-- Showroom Address Card -->
                <div class="apple-utility-card space-y-2">
                    <span class="apple-caption text-apple-primary uppercase font-semibold">Physical Location</span>
                    <h3 class="apple-body-strong text-apple-ink text-[17px]">Maurya Mobile Flagship Store</h3>
                    <p class="apple-body text-apple-muted-80 leading-relaxed text-[15px]">
                        Shop #14, Ground Floor, Linking Road,<br>
                        Opposite Bandra West Station Road,<br>
                        Mumbai, Maharashtra 400050
                    </p>
                    <div class="pt-2">
                        <a href="https://maps.google.com/?q=Linking+Road+Bandra+West+Mumbai" target="_blank" class="apple-text-link text-[14px]">
                            Open in Google Maps <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                </div>

                <!-- Operating Hours Card -->
                <div class="apple-utility-card space-y-2">
                    <span class="apple-caption text-apple-primary uppercase font-semibold">Store Timings</span>
                    <h3 class="apple-body-strong text-apple-ink text-[17px]">Monday through Sunday</h3>
                    <div class="space-y-1 apple-body text-apple-muted-80 text-[15px]">
                        <div class="flex justify-between">
                            <span>Showroom Doors:</span>
                            <strong class="text-apple-ink">10:00 AM – 9:30 PM</strong>
                        </div>
                        <div class="flex justify-between">
                            <span>Service Lab Desk:</span>
                            <strong class="text-apple-ink">10:30 AM – 8:30 PM</strong>
                        </div>
                    </div>
                    <p class="apple-fine-print text-apple-muted-48 pt-2 border-t border-apple-hairline">
                        Open all 7 days including public holidays.
                    </p>
                </div>

                <!-- Direct Numbers Card -->
                <div class="apple-utility-card space-y-3">
                    <span class="apple-caption text-apple-primary uppercase font-semibold">Direct Communication</span>
                    <div class="space-y-2">
                        <a href="tel:9876543210" class="flex items-center justify-between p-3 rounded-[12px] bg-apple-parchment hover:bg-apple-hairline/60 transition-colors text-decoration-none">
                            <div class="flex items-center gap-3">
                                <i data-lucide="phone" class="w-4 h-4 text-apple-primary"></i>
                                <span class="apple-body text-apple-ink text-[15px]">Retail Sales Counter</span>
                            </div>
                            <span class="apple-body-strong text-apple-primary text-[15px]">+91 98765 43210</span>
                        </a>

                        <a href="https://wa.me/919876543210" target="_blank" class="flex items-center justify-between p-3 rounded-[12px] bg-apple-parchment hover:bg-apple-hairline/60 transition-colors text-decoration-none">
                            <div class="flex items-center gap-3">
                                <i data-lucide="message-circle" class="w-4 h-4 text-emerald-600"></i>
                                <span class="apple-body text-apple-ink text-[15px]">WhatsApp Chat</span>
                            </div>
                            <span class="apple-body-strong text-emerald-600 text-[15px]">Chat Now</span>
                        </a>

                        <a href="tel:9876543211" class="flex items-center justify-between p-3 rounded-[12px] bg-apple-parchment hover:bg-apple-hairline/60 transition-colors text-decoration-none">
                            <div class="flex items-center gap-3">
                                <i data-lucide="wrench" class="w-4 h-4 text-apple-primary"></i>
                                <span class="apple-body text-apple-ink text-[15px]">Repair Lab Helpdesk</span>
                            </div>
                            <span class="apple-body-strong text-apple-primary text-[15px]">+91 98765 43211</span>
                        </a>
                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN (Span 7): Clean Apple Customer Inquiry Form -->
            <div id="inquiry" class="lg:col-span-7 apple-utility-card !p-8 sm:!p-12 space-y-6">
                <div>
                    <h2 class="apple-display-md text-apple-ink">Send an Inquiry</h2>
                    <p class="apple-body text-apple-muted-48 mt-1">Our showroom manager will contact you promptly.</p>
                </div>

                <form action="{{ route('public.contact.submit') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="apple-caption-strong text-apple-ink block mb-1.5">Full Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Rahul Sharma" 
                               value="{{ old('name') }}"
                               class="apple-search-input w-full text-[15px]">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="apple-caption-strong text-apple-ink block mb-1.5">Phone Number *</label>
                            <input type="tel" name="phone" required placeholder="e.g. 98765 43210" 
                                   value="{{ old('phone') }}"
                                   class="apple-search-input w-full text-[15px]">
                        </div>
                        <div>
                            <label class="apple-caption-strong text-apple-ink block mb-1.5">Email Address</label>
                            <input type="email" name="email" placeholder="e.g. rahul@example.com" 
                                   value="{{ old('email') }}"
                                   class="apple-search-input w-full text-[15px]">
                        </div>
                    </div>

                    <div>
                        <label class="apple-caption-strong text-apple-ink block mb-1.5">Inquiry Subject</label>
                        <select name="subject" class="apple-search-input w-full text-[15px] cursor-pointer bg-white">
                            <option value="phone_purchase">Brand New Phone Purchase</option>
                            <option value="pre_owned">Certified Pre-Owned Device</option>
                            <option value="repair_quote">Express Repair / Diagnostic Quote</option>
                            <option value="trade_in">Device Trade-in / Sell My Phone</option>
                            <option value="emi_inquiry">0% EMI Financing Eligibility</option>
                            <option value="other">Other Inquiry</option>
                        </select>
                    </div>

                    <div>
                        <label class="apple-caption-strong text-apple-ink block mb-1.5">Message or Model Details *</label>
                        <textarea name="message" rows="4" required 
                                  placeholder="Describe the smartphone model, storage preference, or fault symptoms..."
                                  class="w-full rounded-[18px] border border-black/10 p-4 text-[15px] text-apple-ink outline-none focus:border-apple-primary transition-colors resize-none">{{ old('message') }}</textarea>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="apple-btn-primary w-full py-3 text-[16px]">
                            Submit Inquiry to Showroom Desk
                        </button>
                    </div>

                    <p class="apple-fine-print text-apple-muted-48 text-center pt-2">
                        By submitting this form, you consent to receive a direct call or SMS regarding your request from our Bandra West store desk.
                    </p>
                </form>
            </div>

        </div>

    </div>

@endsection
