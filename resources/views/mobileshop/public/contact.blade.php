@extends('mobileshop.public.layout')

@section('title', 'Contact & Store Location — MobiTrack Mumbai')
@section('meta_description', 'Visit MobiTrack showroom on Linking Road, Bandra West, Mumbai. Get store directions, contact numbers, WhatsApp chat, and submit smartphone inquiries.')

@section('content')

    <!-- Header Banner -->
    <div class="bg-gradient-to-b from-slate-950 to-slate-900 text-white py-14 px-4 text-center">
        <div class="max-w-3xl mx-auto space-y-3">
            <span class="px-3 py-1 rounded-full bg-brand-500/20 text-brand-300 border border-brand-500/30 text-xs font-bold uppercase tracking-wider">
                We're Here For You
            </span>
            <h1 class="font-display font-black text-3xl sm:text-4xl text-white">Visit Our Store or Get In Touch</h1>
            <p class="text-slate-400 text-sm max-w-xl mx-auto">Have a question about phone availability, buyback estimates, or repair timelines? Reach out directly to our store desk.</p>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        
        <!-- Flash Alert -->
        @if(session('success'))
            <div class="mb-8 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-3 text-sm font-semibold">
                <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
            <!-- Left: Contact Details & Store Hours (5 Cols) -->
            <div class="lg:col-span-5 space-y-8">
                <div>
                    <h2 class="font-display font-black text-2xl text-slate-900">Showroom Details</h2>
                    <p class="text-slate-500 text-sm mt-1">Conveniently located in the heart of Bandra's shopping district.</p>
                </div>

                <!-- Info Cards -->
                <div class="space-y-4">
                    <!-- Address -->
                    <div class="flex items-start gap-4 p-5 rounded-3xl bg-white border border-slate-200 shadow-xs">
                        <div class="w-11 h-11 rounded-2xl bg-brand-100 text-brand-700 flex items-center justify-center shrink-0">
                            <i data-lucide="map-pin" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <strong class="font-bold text-sm text-slate-900 block">Showroom Address</strong>
                            <p class="text-xs text-slate-600 leading-relaxed mt-1">
                                Shop #14, Linking Road, Near Bandra Station West,<br>
                                Mumbai, Maharashtra 400050
                            </p>
                        </div>
                    </div>

                    <!-- Phone & WhatsApp -->
                    <div class="flex items-start gap-4 p-5 rounded-3xl bg-white border border-slate-200 shadow-xs">
                        <div class="w-11 h-11 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                            <i data-lucide="phone-call" class="w-5 h-5"></i>
                        </div>
                        <div class="space-y-1">
                            <strong class="font-bold text-sm text-slate-900 block">Phone & WhatsApp</strong>
                            <p class="text-xs text-slate-600">Store Direct: <a href="tel:9876543210" class="font-bold text-slate-900 hover:text-brand-600">+91 98765 43210</a></p>
                            <p class="text-xs text-slate-600">Service Lab: <a href="tel:9876543211" class="font-bold text-slate-900 hover:text-brand-600">+91 98765 43211</a></p>
                            <div class="pt-1">
                                <a href="https://wa.me/919876543210" target="_blank" class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600 hover:text-emerald-700">
                                    <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                    <span>Chat Live on WhatsApp →</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Store Timings -->
                    <div class="flex items-start gap-4 p-5 rounded-3xl bg-white border border-slate-200 shadow-xs">
                        <div class="w-11 h-11 rounded-2xl bg-amber-100 text-amber-800 flex items-center justify-center shrink-0">
                            <i data-lucide="clock" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <strong class="font-bold text-sm text-slate-900 block">Operating Hours</strong>
                            <p class="text-xs text-slate-600 mt-1"><strong>Monday – Sunday:</strong> 10:00 AM – 9:30 PM</p>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-emerald-50 text-emerald-700 text-[10px] font-bold mt-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span>Open 7 Days a Week</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Google Map Placeholder Card -->
                <div class="rounded-3xl border border-slate-200 overflow-hidden shadow-xs bg-slate-100 p-6 text-center space-y-3">
                    <div class="w-10 h-10 rounded-2xl bg-slate-200 text-slate-600 flex items-center justify-center mx-auto">
                        <i data-lucide="navigation" class="w-5 h-5"></i>
                    </div>
                    <h4 class="font-bold text-sm text-slate-900">Directions & Navigation</h4>
                    <p class="text-xs text-slate-500 max-w-xs mx-auto">Located 5 minutes walk from Bandra Suburban Railway Station. Valet & street parking available.</p>
                    <a href="https://maps.google.com/?q=Linking+Road+Bandra+West+Mumbai" target="_blank" class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-900 hover:bg-brand-700 text-white rounded-xl text-xs font-bold transition-all">
                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                        <span>Open in Google Maps</span>
                    </a>
                </div>
            </div>

            <!-- Right: Interactive Inquiry Form (7 Cols) -->
            <div class="lg:col-span-7 bg-white rounded-3xl p-8 sm:p-10 border border-slate-200 shadow-sm space-y-6">
                <div>
                    <h2 class="font-display font-black text-2xl text-slate-900">Send an Inquiry or Request Callback</h2>
                    <p class="text-slate-500 text-sm mt-1">Fill out the details below and our sales counter will reply within 30 minutes during working hours.</p>
                </div>

                <form action="{{ route('public.contact.submit') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Your Full Name *</label>
                            <input type="text" name="name" required placeholder="e.g. Rahul Sharma"
                                   class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:border-brand-500 focus:bg-white transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Phone Number *</label>
                            <input type="tel" name="phone" required placeholder="e.g. 9876543210"
                                   class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:border-brand-500 focus:bg-white transition-all">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Email Address</label>
                            <input type="email" name="email" placeholder="rahul@example.com"
                                   class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:border-brand-500 focus:bg-white transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Department / Inquiry Type</label>
                            <select name="subject" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:border-brand-500 focus:bg-white transition-all">
                                <option value="phone_purchase">Brand New Phone Purchase</option>
                                <option value="secondhand">Certified Pre-Owned Inquiry</option>
                                <option value="repair">Repair Diagnosis / Price Quote</option>
                                <option value="buyback">Sell My Phone / Buyback Valuation</option>
                                <option value="accessories">Back Covers & Accessories</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Message / Device Details *</label>
                        <textarea name="message" rows="4" required placeholder="Tell us which brand/model you are looking for or describe your device fault..."
                                  class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:border-brand-500 focus:bg-white transition-all"></textarea>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-brand-600 to-purple-600 hover:from-brand-500 hover:to-purple-500 text-white font-extrabold text-sm rounded-xl shadow-md shadow-brand-500/25 transition-all flex items-center justify-center gap-2">
                            <i data-lucide="send" class="w-4 h-4"></i>
                            <span>Send Message to Store Desk</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

@endsection
