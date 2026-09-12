@extends('mobileshop.public.layout')

@section('title', 'Shipping, Showroom Pickup & Delivery — Maurya Mobile Mumbai')
@section('meta_description', 'Learn about 2-hour in-store showroom pickup in Bandra West, same-day Mumbai express courier, and insured pan-India delivery at Maurya Mobile.')

@section('subnav_title', 'Delivery Guide')
@section('subnav_links')
    <a href="#pickup" class="hover:text-apple-ink">Showroom Pickup</a>
    <a href="#mumbai" class="hover:text-apple-ink">Mumbai Same-Day</a>
    <a href="#pan-india" class="hover:text-apple-ink">Pan-India Courier</a>
    <a href="#transit-insurance" class="hover:text-apple-ink">Transit Insurance</a>
@endsection
@section('subnav_cta')
    <a href="{{ route('public.contact') }}" class="apple-btn-primary text-[13px] py-1.5 px-3.5">
        Store Directions
    </a>
@endsection

@section('content')

    <!-- ════ 1. HERO BANNER ════ -->
    <section class="bg-apple-parchment text-apple-ink py-16 sm:py-24 border-b border-apple-hairline text-center overflow-hidden">
        <div class="max-w-[1024px] mx-auto px-4 space-y-4">
            <span class="apple-caption-strong text-apple-primary uppercase tracking-widest text-[12px]">
                Safe, Prompt & Insured Handover
            </span>
            <h1 class="apple-hero-display text-apple-ink max-w-3xl mx-auto">
                Showroom Pickup & Delivery Policy.
            </h1>
            <p class="apple-lead-airy text-apple-muted-80 max-w-2xl mx-auto pt-2">
                Whether collecting in person from our Bandra West flagship showroom or requesting insured dispatch across India, your smartphone arrives safely and on time.
            </p>
            <div class="pt-2 flex items-center justify-center gap-4 text-xs text-apple-muted-48">
                <span>Tamper-Evident Security Seals</span>
                <span>&bull;</span>
                <span>100% Transit Insured</span>
            </div>
        </div>
    </section>

    <!-- ════ 2. CONTENT SECTIONS ════ -->
    <div class="max-w-[920px] mx-auto px-4 py-16 sm:py-20 space-y-16 text-apple-ink">

        <!-- SECTION 1: IN-STORE SHOWROOM PICKUP -->
        <section id="pickup" class="space-y-4 scroll-mt-24">
            <span class="apple-caption-strong text-apple-primary uppercase tracking-wider text-[11px]">Option 01</span>
            <h2 class="apple-display-md text-apple-ink">1. Express Showroom Pickup (Bandra West)</h2>
            <p class="apple-body text-apple-muted-80">
                The fastest way to experience your new smartphone. Orders reserved online or by phone are pre-inspected and staged for collection at our flagship retail counter within <strong>2 hours</strong>:
            </p>
            <div class="p-6 rounded-2xl bg-apple-parchment border border-apple-hairline space-y-3 text-xs">
                <div class="flex items-center justify-between font-mono text-apple-ink border-b border-apple-hairline pb-2">
                    <span>Pickup Location:</span>
                    <strong>Maurya Mobile, Linking Road, Bandra West, Mumbai 400050</strong>
                </div>
                <div class="flex items-center justify-between font-mono text-apple-ink border-b border-apple-hairline pb-2">
                    <span>Counter Timings:</span>
                    <strong>Monday – Saturday, 10:30 AM to 8:30 PM (Sunday open till 6:00 PM)</strong>
                </div>
                <div class="flex items-center justify-between font-mono text-apple-ink">
                    <span>Verification Required:</span>
                    <strong>Original Order Confirmation SMS or WhatsApp message + Valid Photo ID</strong>
                </div>
            </div>
        </section>

        <hr class="border-apple-hairline">

        <!-- SECTION 2: SAME-DAY MUMBAI DELIVERY -->
        <section id="mumbai" class="space-y-4 scroll-mt-24">
            <span class="apple-caption-strong text-apple-primary uppercase tracking-wider text-[11px]">Option 02</span>
            <h2 class="apple-display-md text-apple-ink">2. Same-Day Hyperlocal Express (Mumbai MMR)</h2>
            <p class="apple-body text-apple-muted-80">
                For customers residing within Greater Mumbai, Navi Mumbai, and Thane:
            </p>
            <ul class="space-y-3 apple-body text-apple-muted-80 list-disc list-inside">
                <li><strong class="text-apple-ink">Cut-off Window:</strong> Confirmed orders placed before <strong>4:00 PM</strong> are dispatched via dedicated, insured store delivery associates for same-evening doorstep handover.</li>
                <li><strong class="text-apple-ink">Open-Box Verification:</strong> Delivery personnel permit you to verify outer tamper-evident security tape, serial numbers, and physical condition before completing the delivery OTP confirmation.</li>
            </ul>
        </section>

        <hr class="border-apple-hairline">

        <!-- SECTION 3: PAN-INDIA EXPRESS COURIER -->
        <section id="pan-india" class="space-y-4 scroll-mt-24">
            <span class="apple-caption-strong text-apple-primary uppercase tracking-wider text-[11px]">Option 03</span>
            <h2 class="apple-display-md text-apple-ink">3. Pan-India Insured Express Courier</h2>
            <p class="apple-body text-apple-muted-80">
                For shipments across India outside the Mumbai metropolitan zone:
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 text-xs">
                <div class="p-5 rounded-2xl bg-apple-parchment border border-apple-hairline space-y-1.5">
                    <strong class="text-apple-ink block text-sm">Carrier Partners</strong>
                    <span class="text-apple-muted-80">All long-distance dispatches are handled by Tier-1 priority express couriers (BlueDart Air Express / DTDC Platinum).</span>
                </div>
                <div class="p-5 rounded-2xl bg-apple-parchment border border-apple-hairline space-y-1.5">
                    <strong class="text-apple-ink block text-sm">Transit Timelines</strong>
                    <span class="text-apple-muted-80">Metro cities: 24 to 48 hours. Non-metro tier-2/3 destinations: 3 to 4 business days with real-time SMS tracking updates.</span>
                </div>
            </div>
        </section>

        <hr class="border-apple-hairline">

        <!-- SECTION 4: TRANSIT INSURANCE & DAMAGE -->
        <section id="transit-insurance" class="space-y-4 scroll-mt-24">
            <span class="apple-caption-strong text-apple-primary uppercase tracking-wider text-[11px]">Insurance Protection</span>
            <h2 class="apple-display-md text-apple-ink">4. 100% Transit Insurance & Unboxing Protocol</h2>
            <p class="apple-body text-apple-muted-80">
                Every dispatched smartphone parcel is insured for 100% of its invoice valuation against transit loss, pilferage, or vehicle accidental damage.
            </p>
            <div class="p-5 rounded-2xl bg-slate-950 text-white border border-slate-800 space-y-2 text-xs">
                <strong class="text-emerald-400 font-bold block text-sm flex items-center gap-2">
                    <i data-lucide="video" class="w-4 h-4"></i> Unboxing Video Advisory
                </strong>
                <p class="text-slate-300 leading-relaxed">
                    To ensure rapid insurance settlement in the extremely rare event of external carrier mishandling, we advise customers to capture a brief, continuous smartphone video while opening the outer courier packaging and checking the inner retail box security seals.
                </p>
            </div>
        </section>

    </div>

@endsection
