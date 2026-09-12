@extends('mobileshop.public.layout')

@section('title', 'Privacy & Data Zero-Wipe Policy — Maurya Mobile Mumbai')
@section('meta_description', 'Read Maurya Mobile’s Privacy Policy and our DoD-standard cryptographic Data Zero-Wipe guarantee for second-hand trade-ins and repair service devices.')

@section('subnav_title', 'Legal & Trust')
@section('subnav_links')
    <a href="#collection" class="hover:text-apple-ink">Information We Collect</a>
    <a href="#zerowipe" class="hover:text-apple-ink">Device Data Zero-Wipe</a>
    <a href="#repairs" class="hover:text-apple-ink">Repair Privacy</a>
    <a href="#sharing" class="hover:text-apple-ink">Data Security & GST</a>
    <a href="#rights" class="hover:text-apple-ink">Your Rights</a>
@endsection
@section('subnav_cta')
    <a href="{{ route('public.contact') }}" class="apple-btn-primary text-[13px] py-1.5 px-3.5">
        Grievance Contact
    </a>
@endsection

@section('content')

    <!-- ════ 1. HERO BANNER (Apple Parchment Canvas) ════ -->
    <section class="bg-apple-parchment text-apple-ink py-16 sm:py-24 border-b border-apple-hairline text-center overflow-hidden">
        <div class="max-w-[1024px] mx-auto px-4 space-y-4">
            <span class="apple-caption-strong text-apple-primary uppercase tracking-widest text-[12px]">
                Customer Confidentiality & Device Integrity
            </span>
            <h1 class="apple-hero-display text-apple-ink max-w-3xl mx-auto">
                Privacy & Data Zero-Wipe Guarantee.
            </h1>
            <p class="apple-lead-airy text-apple-muted-80 max-w-2xl mx-auto pt-2">
                At Maurya Mobile, we hold customer privacy to the highest standard. Whether you buy a smartphone, trade in a pre-owned device, or leave your phone for a lab repair, your personal data remains strictly your own.
            </p>
            <div class="pt-2 flex items-center justify-center gap-4 text-xs text-apple-muted-48">
                <span>Effective Date: 1 January {{ date('Y') }}</span>
                <span>&bull;</span>
                <span>DPDP Act (India) Compliant</span>
            </div>
        </div>
    </section>

    <!-- ════ 2. CONTENT SECTIONS CONTAINER ════ -->
    <div class="max-w-[920px] mx-auto px-4 py-16 sm:py-20 space-y-16 text-apple-ink">

        <!-- SECTION 1: INFORMATION WE COLLECT -->
        <section id="collection" class="space-y-4 scroll-mt-24">
            <span class="apple-caption-strong text-apple-primary uppercase tracking-wider text-[11px]">Section 01</span>
            <h2 class="apple-display-md text-apple-ink">1. Information We Collect</h2>
            <p class="apple-body text-apple-muted-80">
                When you interact with Maurya Mobile in-store at our Bandra West showroom or online via our digital portal, we only collect information essential for transaction execution, legal compliance, and customer warranty support:
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div class="p-5 rounded-2xl bg-apple-parchment border border-apple-hairline space-y-2">
                    <h3 class="apple-caption-strong text-apple-ink text-sm">Retail Sales & GST Invoicing</h3>
                    <p class="apple-caption text-apple-muted-80 text-xs">
                        Customer full name, billing address, mobile telephone number, email, and GSTIN (for business tax invoice claims under Indian CGST/SGST laws).
                    </p>
                </div>
                <div class="p-5 rounded-2xl bg-apple-parchment border border-apple-hairline space-y-2">
                    <h3 class="apple-caption-strong text-apple-ink text-sm">Hardware & IMEI Registry</h3>
                    <p class="apple-caption text-apple-muted-80 text-xs">
                        Device serial numbers, dual-IMEI identifiers, brand, model, and purchase timestamp to validate genuine OEM warranties and prevent stolen-device trafficking.
                    </p>
                </div>
                <div class="p-5 rounded-2xl bg-apple-parchment border border-apple-hairline space-y-2">
                    <h3 class="apple-caption-strong text-apple-ink text-sm">Service & Repair Job Sheets</h3>
                    <p class="apple-caption text-apple-muted-80 text-xs">
                        Reported handset hardware symptoms, passcodes/pattern lock temporary waivers for diagnostic testing, OTP-verified intake timestamps, and technician bench notes.
                    </p>
                </div>
                <div class="p-5 rounded-2xl bg-apple-parchment border border-apple-hairline space-y-2">
                    <h3 class="apple-caption-strong text-apple-ink text-sm">Second-Hand Buyback Identity</h3>
                    <p class="apple-caption text-apple-muted-80 text-xs">
                        Valid Government photo ID (Aadhaar, Voter ID, or Passport copy) and written ownership declaration as mandated by Mumbai Police electronic asset verification regulations.
                    </p>
                </div>
            </div>
        </section>

        <hr class="border-apple-hairline">

        <!-- SECTION 2: DEVICE ZERO-WIPE GUARANTEE (CRITICAL PROMISE) -->
        <section id="zerowipe" class="space-y-6 scroll-mt-24">
            <div class="p-8 rounded-3xl bg-slate-950 text-white border border-slate-800 relative overflow-hidden shadow-xl">
                <div class="absolute -right-16 -top-16 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
                
                <div class="relative z-10 space-y-4">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-950/80 border border-emerald-700/60 text-emerald-400 text-xs font-bold uppercase tracking-wider">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Industry Gold Standard
                    </div>
                    
                    <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                        2. The Maurya Device Zero-Wipe Guarantee
                    </h2>
                    
                    <p class="text-sm text-slate-300 leading-relaxed max-w-2xl">
                        Selling or exchanging your pre-owned smartphone shouldn't risk your digital identity. Every mobile device purchased or taken in trade by Maurya Mobile undergoes a strict, multi-stage cryptographic erasure before entering our display showcase.
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-4 border-t border-slate-800 text-xs">
                        <div class="space-y-1">
                            <span class="text-emerald-400 font-bold block text-sm">DoD 5220.22-M Standard</span>
                            <span class="text-slate-400">Multi-pass cryptographic overwrite obliterates photos, WhatsApp logs, banking credentials, and private documents.</span>
                        </div>
                        <div class="space-y-1">
                            <span class="text-emerald-400 font-bold block text-sm">iCloud & FRP Disassociation</span>
                            <span class="text-slate-400">Apple Activation Lock and Google Factory Reset Protection are cleanly decoupled under customer supervision.</span>
                        </div>
                        <div class="space-y-1">
                            <span class="text-emerald-400 font-bold block text-sm">Non-Recoverable Guarantee</span>
                            <span class="text-slate-400">Deep NAND flash zero-filling prevents any forensic recovery tool from extracting residual data blocks.</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <hr class="border-apple-hairline">

        <!-- SECTION 3: REPAIR WORKBENCH & DATA INTEGRITY -->
        <section id="repairs" class="space-y-4 scroll-mt-24">
            <span class="apple-caption-strong text-apple-primary uppercase tracking-wider text-[11px]">Section 03</span>
            <h2 class="apple-display-md text-apple-ink">3. Repair Service Bench Data Confidentiality</h2>
            <p class="apple-body text-apple-muted-80">
                When you submit a device for hardware restoration (screen renewal, battery swap, charging flex repair, or camera replacement), we maintain strict protocol boundaries:
            </p>
            <ul class="space-y-3 apple-body text-apple-muted-80 list-disc list-inside">
                <li><strong class="text-apple-ink">Zero Data Access Mandate:</strong> Our technicians are legally and contractually prohibited from browsing user photo galleries, social applications, message threads, or local files.</li>
                <li><strong class="text-apple-ink">Maintenance Mode Encouraged:</strong> On compatible devices (Samsung Maintenance Mode / iOS Diagnostics Mode), we strongly advise activating diagnostic sandboxing prior to handoff.</li>
                <li><strong class="text-apple-ink">Hardware Only Testing:</strong> Post-repair verification is confined strictly to hardware diagnostics (digitizer grid test, microphone recording test loop, charging ammeter rate verification).</li>
                <li><strong class="text-apple-ink">Customer Backup Advisory:</strong> While hardware service rarely affects flash storage, customers are always advised to maintain an iCloud or Google Drive backup prior to lab handoff.</li>
            </ul>
        </section>

        <hr class="border-apple-hairline">

        <!-- SECTION 4: DATA SECURITY, GST & DISCLOSURE -->
        <section id="sharing" class="space-y-4 scroll-mt-24">
            <span class="apple-caption-strong text-apple-primary uppercase tracking-wider text-[11px]">Section 04</span>
            <h2 class="apple-display-md text-apple-ink">4. How We Safeguard & Share Information</h2>
            <p class="apple-body text-apple-muted-80">
                Maurya Mobile is a retail electronics company — we never monetize, sell, lease, or distribute our customer database to third-party telemarketers, insurance aggregators, or loan brokers.
            </p>
            <p class="apple-body text-apple-muted-80">
                We only transmit customer information under the following legitimate regulatory situations:
            </p>
            <div class="space-y-3 pt-2 text-xs">
                <div class="p-4 rounded-xl bg-apple-parchment border border-apple-hairline flex items-start gap-3">
                    <span class="text-apple-primary text-base font-bold">&bull;</span>
                    <div>
                        <strong class="text-apple-ink block text-sm">Goods & Services Tax (GSTN) Network</strong>
                        <span class="text-apple-muted-80">Invoice numbers, HSN/SAC codes, and tax breakdowns are uploaded to the Indian GST e-invoicing portal as legally mandated.</span>
                    </div>
                </div>
                <div class="p-4 rounded-xl bg-apple-parchment border border-apple-hairline flex items-start gap-3">
                    <span class="text-apple-primary text-base font-bold">&bull;</span>
                    <div>
                        <strong class="text-apple-ink block text-sm">Financing & Paperless EMI Partners</strong>
                        <span class="text-apple-muted-80">If you explicitly choose 0% EMI financing via Bajaj Finserv, IDFC First Bank, or HDFC Consumer Finance, your application data is transmitted directly into their secure encrypted lending APIs.</span>
                    </div>
                </div>
                <div class="p-4 rounded-xl bg-apple-parchment border border-apple-hairline flex items-start gap-3">
                    <span class="text-apple-primary text-base font-bold">&bull;</span>
                    <div>
                        <strong class="text-apple-ink block text-sm">Statutory Law Enforcement Compliance</strong>
                        <span class="text-apple-muted-80">IMEI logs and buyback customer identification records will be produced if officially demanded under a formal subpoena or warrant by Mumbai Police Cyber Cell.</span>
                    </div>
                </div>
            </div>
        </section>

        <hr class="border-apple-hairline">

        <!-- SECTION 5: YOUR RIGHTS & CONTACT -->
        <section id="rights" class="space-y-6 scroll-mt-24">
            <span class="apple-caption-strong text-apple-primary uppercase tracking-wider text-[11px]">Section 05</span>
            <h2 class="apple-display-md text-apple-ink">5. Customer Rights & Grievance Redressal</h2>
            <p class="apple-body text-apple-muted-80">
                In compliance with the Digital Personal Data Protection Act, 2023, you have the right to request access to your recorded transaction history, update incorrect contact details, or request deletion of non-statutory marketing contacts.
            </p>

            <div class="p-6 rounded-2xl bg-apple-parchment border border-apple-hairline space-y-3 text-xs">
                <h3 class="apple-caption-strong text-apple-ink text-sm">Grievance Officer & Data Controller</h3>
                <p class="text-apple-muted-80">
                    For any questions, data modification requests, or privacy inquiries, contact our designated store compliance officer:
                </p>
                <div class="space-y-1 font-mono text-apple-ink pt-1">
                    <div><strong>Officer:</strong> Altmash (Store Administration Head)</div>
                    <div><strong>Showroom:</strong> Maurya Mobile, Linking Road, Bandra West, Mumbai 400050</div>
                    <div><strong>Email:</strong> <a href="mailto:privacy@mobitrack.local" class="text-apple-primary hover:underline">privacy@mobitrack.local</a></div>
                    <div><strong>Helpline:</strong> +91 98765 43210 (Mon–Sat, 10:30 AM to 8:30 PM)</div>
                </div>
            </div>
        </section>

    </div>

@endsection
