@extends('mobileshop.layout')

@section('title', 'Brand New Mobiles POS — MobiTrack')
@section('page-title', 'Brand New Mobiles POS Terminal')

@section('page-actions')
    <a href="{{ route('mobileshop.dashboard') }}" class="btn btn-outline btn-sm">
        <i data-lucide="arrow-left" style="width:14px;height:14px;"></i> Back to Dashboard
    </a>
@endsection

@push('styles')
<style>
    .pos-grid {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 24px;
        align-items: start;
    }
    @media (max-width: 960px) {
        .pos-grid { grid-template-columns: 1fr; }
    }
    .spec-pill-box {
        background: var(--lama-purple-light);
        border: 1px solid var(--lama-purple);
        border-radius: 12px;
        padding: 14px 18px;
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
    }
    @media (max-width: 600px) {
        .spec-pill-box { grid-template-columns: 1fr 1fr; }
    }
</style>
@endpush

@section('content')
<form action="{{ route('mobileshop.pos.sale') }}" method="POST" id="posForm" onsubmit="this.querySelector('button[type=submit]').disabled=true; this.querySelector('button[type=submit]').innerText='Processing Sale...'; if(typeof MT !== 'undefined'){ MT.enqueue('create', 'ms_mobile_sales', {source:'posForm', action:'processSale'}); return false; }">
    @csrf
    <input type="hidden" name="idempotency_key" value="{{ \Illuminate\Support\Str::uuid() }}">
    <div class="pos-grid">

        <!-- LEFT COLUMN: Customer & Device & Gifts -->
        <div style="display: flex; flex-direction: column; gap: 20px;">

            <!-- 0. AI Fast-Fill Card (EMI Slip / Delivery Challan / Invoice Scan) -->
            <div class="card" style="background: linear-gradient(135deg, #FAF5FF 0%, #F5F3FF 100%); border: 1.5px dashed #A855F7;">
                <div class="card-body" style="padding: 16px 20px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                        <div style="display: flex; align-items: center; gap: 14px;">
                            <div style="width: 44px; height: 44px; border-radius: 12px; background: #7E22CE; color: #fff; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(126, 34, 206, 0.25); flex-shrink: 0;">
                                <i data-lucide="sparkles" style="width: 22px; height: 22px;"></i>
                            </div>
                            <div>
                                <div style="font-weight: 800; font-size: 14px; color: #581C87; display: flex; align-items: center; gap: 6px;">
                                    <span>AI Auto-Fill from EMI Slip / Bill</span>
                                    <span class="badge badge-purple" style="font-size: 10px; background: #E9D5FF; color: #6B21A8;">Gemini 1.5 Flash</span>
                                </div>
                                <div style="font-size: 11.5px; color: #6B21A8; margin-top: 2px;">
                                    Upload/Capture Bajaj, TVS, HDB challan or invoice to auto-fill Customer, Device IMEI, Loan # & Down Payment!
                                </div>
                            </div>
                        </div>

                        <div style="display: flex; align-items: center; gap: 8px;">
                            <input type="file" id="emiBillFileInput" accept="image/*,.pdf" capture="environment" style="display: none;" onchange="handleEmiBillUpload(this)">
                            <button type="button" onclick="document.getElementById('emiBillFileInput').click()" id="btnScanEmiBill" class="btn btn-primary btn-sm" style="background: #7E22CE; border-color: #7E22CE; font-weight: 700; padding: 8px 16px; box-shadow: 0 2px 6px rgba(126, 34, 206, 0.3);">
                                <i data-lucide="camera" style="width: 15px; height: 15px;"></i>
                                <span id="btnScanText">Scan / Upload EMI Bill</span>
                            </button>
                        </div>
                    </div>

                    <!-- Scanning Progress Bar / Feedback -->
                    <div id="emiScanStatus" style="display: none; margin-top: 14px; padding: 12px 14px; background: #FFFFFF; border-radius: 10px; border: 1px solid #E9D5FF;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                            <span style="font-size: 12px; font-weight: 700; color: #7E22CE;" id="emiScanStatusMsg">
                                🔄 Reading document with Gemini AI...
                            </span>
                            <span style="font-size: 11px; font-weight: 700; color: #9333EA;" id="emiScanPercent">Scanning</span>
                        </div>
                        <div style="width: 100%; height: 6px; background: #F3E8FF; border-radius: 99px; overflow: hidden;">
                            <div id="emiScanProgressBar" style="width: 60%; height: 100%; background: linear-gradient(90deg, #9333EA, #C084FC); transition: width 0.3s ease;"></div>
                        </div>
                    </div>

                    <!-- Toast / Banner on Success -->
                    <div id="emiScanSuccessBanner" style="display: none; margin-top: 12px; padding: 10px 14px; background: #F0FDF4; border: 1px solid #86EFAC; border-radius: 8px; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 700; color: #166534;">
                            <i data-lucide="check-circle-2" style="width: 16px; height: 16px; color: #16A34A;"></i>
                            <span id="emiScanSuccessText">Extracted: Customer & IMEI matched in stock!</span>
                        </div>
                        <button type="button" onclick="this.parentElement.style.display='none'" style="background: none; border: none; font-size: 12px; cursor: pointer; color: #166534;">✕</button>
                    </div>
                </div>
            </div>

            <!-- 1. Customer Details -->
            <div class="card">
                <div class="card-header">
                    <div>
                        <div class="card-title">Customer Details & Khata Lookup</div>
                        <div class="card-subtitle">Autofill details by mobile number</div>
                    </div>
                    <span class="badge badge-purple">Instant Lookup</span>
                </div>
                <div class="card-body">
                    <div class="form-row" style="margin-bottom: 16px;">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label required">Customer Mobile Number</label>
                            <input type="text" name="customer_phone" id="customerPhone" list="customerList" placeholder="e.g. 9876543210" required class="form-control" style="font-weight: 700;">
                            <datalist id="customerList">
                                @foreach($customers as $c)
                                    <option value="{{ $c->phone }}" data-name="{{ $c->name }}" data-gstin="{{ $c->gstin }}" data-address="{{ $c->address }}" data-balance="{{ $c->udhari_balance }}">
                                        {{ $c->name }} (Pending Khata: ₹{{ number_format($c->udhari_balance, 2) }})
                                    </option>
                                @endforeach
                            </datalist>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label required">Customer Full Name</label>
                            <input type="text" name="customer_name" id="customerName" placeholder="e.g. Rahul Sharma" required class="form-control" style="font-weight: 700;">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Customer GSTIN (Optional B2B)</label>
                            <input type="text" name="customer_gstin" id="customerGstin" placeholder="e.g. 27AAAAA0000A1Z5" class="form-control">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Customer Address / City</label>
                            <input type="text" name="customer_address" id="customerAddress" placeholder="e.g. Bandra West, Mumbai" class="form-control">
                        </div>
                    </div>

                    <!-- Khata Alert Banner -->
                    <div id="khataAlertBanner" style="display:none; margin-top: 16px; padding: 14px 16px; background: var(--lama-yellow-light); border: 1px solid var(--lama-yellow); border-radius: 12px; align-items: center; justify-content: space-between;">
                        <div style="display:flex; align-items:center; gap: 10px;">
                            <i data-lucide="alert-circle" style="width:18px;height:18px;color:var(--lama-yellow-dark);"></i>
                            <div>
                                <span style="font-size:12px; font-weight:700; color:var(--lama-yellow-dark);">Existing Customer with Outstanding Khata / Udhari Balance!</span>
                                <div style="font-size:11px; color:var(--text-secondary);">Previous unpaid dues will be printed on receipt summary.</div>
                            </div>
                        </div>
                        <span id="khataAmountText" class="badge badge-orange" style="font-size:13px; padding: 4px 12px;">₹0.00</span>
                    </div>
                </div>
            </div>

            <!-- 2. Device Selection -->
            <div class="card">
                <div class="card-header">
                    <div>
                        <div class="card-title">Select Brand New Mobile from Stock</div>
                        <div class="card-subtitle">Showing {{ count($newPhones) }} boxed units available in store</div>
                    </div>
                    <span class="badge badge-green">Boxed / Sealed Stock</span>
                </div>
                <div class="card-body">
                    @if(count($newPhones) === 0)
                        <div style="padding: 28px 16px; text-align:center; background:#FFFBEB; border:1px solid #FDE68A; border-radius:12px; display:flex; flex-direction:column; align-items:center; gap:10px;">
                            <div style="width:48px; height:48px; border-radius:50%; background:#FEF3C7; display:flex; align-items:center; justify-content:center; color:#D97706;">
                                <i data-lucide="smartphone" style="width:24px;height:24px;"></i>
                            </div>
                            <div style="font-weight:800; font-size:15px; color:#92400E;">No Brand New Phones in Stock</div>
                            <div style="font-size:12px; color:#B45309; max-width:320px;">Please intake new phone inventory with IMEI numbers to generate POS invoices.</div>
                            <a href="{{ route('mobileshop.new_mobiles') }}" class="btn btn-primary btn-sm" style="background:#D97706; border-color:#D97706; margin-top:4px;">
                                <i data-lucide="plus" style="width:13px;height:13px;"></i> Add New Phone Stock
                            </a>
                        </div>
                    @else
                        <div class="form-group">
                            <label class="form-label required">Pick Available New Phone by Model / IMEI</label>
                            <select name="device_id" id="deviceSelect" required class="form-control" style="font-weight: 700;">
                                <option value="">-- Select Brand New Phone --</option>
                                @foreach($newPhones as $np)
                                    <option value="{{ $np->id }}" data-price="{{ $np->selling_price }}" data-brand="{{ $np->brand }}" data-model="{{ $np->model }}" data-color="{{ $np->color }}" data-storage="{{ $np->storage }}" data-imei="{{ $np->imei_1 }}">
                                        {{ $np->brand }} {{ $np->model }} ({{ $np->storage }} • {{ $np->color }}) — IMEI: {{ $np->imei_1 }} — ₹{{ number_format($np->selling_price, 2) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Live Selected Phone Spec Card -->
                        <div id="deviceSpecCard" class="spec-pill-box" style="display:none; margin-top: 14px;">
                            <div>
                                <span style="font-size:10px; color:var(--text-secondary); text-transform:uppercase; font-weight:700;">Model</span>
                                <div style="font-size:13px; font-weight:800; color:#0F172A;" id="specModel">-</div>
                            </div>
                            <div>
                                <span style="font-size:10px; color:var(--text-secondary); text-transform:uppercase; font-weight:700;">Variant / Color</span>
                                <div style="font-size:13px; font-weight:700; color:#0F172A;" id="specVariant">-</div>
                            </div>
                            <div>
                                <span style="font-size:10px; color:var(--text-secondary); text-transform:uppercase; font-weight:700;">IMEI Number</span>
                                <div style="font-size:12px; font-family:monospace; font-weight:700; color:var(--brand-700);" id="specImei">-</div>
                            </div>
                            <div>
                                <span style="font-size:10px; color:var(--text-secondary); text-transform:uppercase; font-weight:700;">Standard Price</span>
                                <div style="font-size:15px; font-weight:800; color:var(--brand-700);" id="specPrice">₹0.00</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 3. Free Gift Bundles -->
            <div class="card">
                <div class="card-header">
                    <div>
                        <div class="card-title">Promotional Free Gifts & Bundles</div>
                        <div class="card-subtitle">Deducts stock automatically at ₹0 charge</div>
                    </div>
                    <span class="badge badge-green">0% Cost</span>
                </div>
                <div class="card-body">
                    @if(count($gifts ?? []) > 0)
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px;">
                            @foreach($gifts as $g)
                                <label style="display:flex; align-items:center; justify-content:space-between; padding: 12px 14px; border: 1px solid var(--border-color); border-radius:10px; cursor:pointer; background:#fff; transition:all 0.15s;">
                                    <div style="display:flex; align-items:center; gap: 10px;">
                                        <input type="checkbox" name="gift_ids[]" value="{{ $g->id }}" style="accent-color: var(--brand-600); width:16px; height:16px;">
                                        <div>
                                            <div style="font-size:12px; font-weight:700; color:#0F172A;">{{ $g->name }}</div>
                                            <div style="font-size:10px; color:var(--text-secondary); display:flex; gap:6px; align-items:center; margin-top:2px;">
                                                <span class="badge badge-purple" style="font-size:9px; padding:1px 6px;">{{ str_replace('_', ' ', $g->category ?? 'Gift') }}</span>
                                                <span>Stock: {{ $g->stock_qty }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="badge badge-green" style="font-size:10px;">FREE</span>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <div style="padding: 16px; text-align:center; background:#F8FAFC; border:1px dashed #CBD5E1; border-radius:10px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <i data-lucide="gift" style="width:18px;height:18px;color:#64748B;"></i>
                                <span style="font-size:12px; color:#64748B;">No free gift items currently eligible in inventory.</span>
                            </div>
                            <a href="{{ route('mobileshop.purchase') }}" class="btn btn-outline btn-sm">
                                <i data-lucide="plus" style="width:12px;height:12px;"></i> Add Gifts in Catalog
                            </a>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        <!-- RIGHT COLUMN: Billing & Checkout -->
        <div style="position: sticky; top: calc(var(--topbar-height) + 20px);">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Payment & Checkout</div>
                    <span class="badge badge-purple">18% GST INCL</span>
                </div>
                <div class="card-body" style="display:flex; flex-direction:column; gap: 16px;">

                    <!-- Bill Type -->
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label required">Bill Type</label>
                        <select name="bill_type" id="billType" required class="form-control" style="font-weight:700;">
                            <option value="gst">📜 Formal GST Tax Invoice (18% incl.)</option>
                            <option value="non_gst">📄 Estimate / Retail Bill (0% Tax)</option>
                        </select>
                    </div>

                    <!-- Final Agreed Selling Price -->
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label required">Final Sale Price (₹)</label>
                        <input type="number" step="0.01" name="sale_price" id="salePrice" required placeholder="0.00" class="form-control" style="font-size:18px; font-weight:800; color:#0F172A;">
                    </div>

                    <!-- Payment Mode -->
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label required">Payment Method</label>
                        <select name="payment_mode" id="paymentMode" required class="form-control" style="font-weight:700;">
                            <option value="cash">💵 Cash Payment</option>
                            <option value="upi">📱 UPI / QR Code</option>
                            <option value="card">💳 Debit / Credit Card</option>
                            <option value="emi">🏦 EMI / Finance Company</option>
                            <option value="credit_udhari">📒 Full Udhari (Khata Credit)</option>
                            <option value="split">⚖️ Split (Partial Cash + Udhari)</option>
                        </select>
                    </div>

                    <!-- EMI Fields (Conditional) -->
                    <div id="emiFieldsSection" style="display:none; padding: 14px; background: var(--lama-purple-light); border: 1px solid var(--lama-purple); border-radius: 12px; flex-direction:column; gap: 10px;">
                        <div style="font-size:11px; font-weight:700; color:var(--brand-700); text-transform:uppercase;">Finance Provider Details</div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" style="font-size:11px;">Provider</label>
                            <select name="emi_provider_id" id="emiProviderId" class="form-control" style="font-size:12px;">
                                @foreach($emiProviders as $ep)
                                    <option value="{{ $ep->id }}">{{ $ep->name }} (Pool: ₹{{ number_format($ep->advance_balance, 2) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                            <div>
                                <label class="form-label" style="font-size:11px;">Loan Ref #</label>
                                <input type="text" name="emi_loan_no" placeholder="BJF-1234" class="form-control" style="font-size:12px; padding:6px 10px;">
                            </div>
                            <div>
                                <label class="form-label" style="font-size:11px;">Down Payment (₹)</label>
                                <input type="number" step="0.01" name="emi_downpayment" id="emiDownpayment" placeholder="0.00" class="form-control" style="font-size:12px; padding:6px 10px;">
                            </div>
                        </div>
                    </div>

                    <!-- Amount Paid Right Now -->
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label required">Amount Paid Right Now (₹)</label>
                        <input type="number" step="0.01" name="amount_paid" id="amountPaid" required placeholder="0.00" class="form-control" style="font-size:16px; font-weight:800; color:var(--lama-green-dark);">
                    </div>

                    <!-- Summary Box -->
                    <div style="background: var(--bg-page); border: 1px solid var(--border-color); border-radius: 12px; padding: 14px 16px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;font-size:13px;padding:4px 0;color:var(--text-secondary);">
                            <span>Sale Total (Incl. GST)</span>
                            <span style="font-weight:700;color:var(--text-primary);" id="summaryTotal">₹0.00</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;font-size:13px;padding:4px 0;color:var(--text-secondary);">
                            <span>Paid Now</span>
                            <span style="font-weight:700; color:var(--lama-green-dark);" id="summaryPaid">₹0.00</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;font-size:14px;padding:8px 0 0;margin-top:6px;border-top:1px solid var(--border-color);font-weight:800;">
                            <span>Added to Khata:</span>
                            <span style="color:var(--lama-rose-dark);" id="summaryUdhari">₹0.00</span>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 14px; font-size: 14px; font-weight: 800;">
                        <i data-lucide="printer" style="width:18px;height:18px;"></i>
                        Confirm Sale & Print Invoice
                    </button>
                </div>
            </div>
        </div>

    </div>
</form>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const customerPhone = document.getElementById('customerPhone');
        const customerName = document.getElementById('customerName');
        const customerGstin = document.getElementById('customerGstin');
        const customerAddress = document.getElementById('customerAddress');
        const khataAlert = document.getElementById('khataAlertBanner');
        const khataText = document.getElementById('khataAmountText');

        const deviceSelect = document.getElementById('deviceSelect');
        const specCard = document.getElementById('deviceSpecCard');
        const specModel = document.getElementById('specModel');
        const specVariant = document.getElementById('specVariant');
        const specImei = document.getElementById('specImei');
        const specPrice = document.getElementById('specPrice');

        const salePrice = document.getElementById('salePrice');
        const amountPaid = document.getElementById('amountPaid');
        const paymentMode = document.getElementById('paymentMode');
        const emiFields = document.getElementById('emiFieldsSection');

        const summaryTotal = document.getElementById('summaryTotal');
        const summaryPaid = document.getElementById('summaryPaid');
        const summaryUdhari = document.getElementById('summaryUdhari');

        // Customer Datalist Selection
        customerPhone.addEventListener('input', function() {
            const val = this.value.trim();
            const option = document.querySelector(`#customerList option[value="${val}"]`);
            if (option) {
                customerName.value = option.dataset.name || '';
                customerGstin.value = option.dataset.gstin || '';
                customerAddress.value = option.dataset.address || '';
                const bal = parseFloat(option.dataset.balance) || 0;
                if (bal > 0) {
                    khataAlert.style.display = 'flex';
                    khataText.textContent = '₹' + bal.toFixed(2);
                } else {
                    khataAlert.style.display = 'none';
                }
            } else {
                khataAlert.style.display = 'none';
            }
        });

        // Device Selection
        deviceSelect.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            if (opt.value) {
                specCard.style.display = 'grid';
                specModel.textContent = (opt.dataset.brand || '') + ' ' + (opt.dataset.model || '');
                specVariant.textContent = (opt.dataset.storage || '') + ' • ' + (opt.dataset.color || '');
                specImei.textContent = opt.dataset.imei || '';
                const p = parseFloat(opt.dataset.price) || 0;
                specPrice.textContent = '₹' + p.toFixed(2);
                salePrice.value = p.toFixed(2);
                amountPaid.value = p.toFixed(2);
                updateSummary();
            } else {
                specCard.style.display = 'none';
                salePrice.value = '';
                amountPaid.value = '';
                updateSummary();
            }
        });

        // Payment Mode Toggle
        paymentMode.addEventListener('change', function() {
            if (this.value === 'emi') {
                emiFields.style.display = 'flex';
            } else {
                emiFields.style.display = 'none';
            }

            if (this.value === 'credit_udhari') {
                amountPaid.value = '0.00';
            } else if (this.value === 'cash' || this.value === 'upi' || this.value === 'card') {
                amountPaid.value = salePrice.value;
            }
            updateSummary();
        });

        function updateSummary() {
            const tot = parseFloat(salePrice.value) || 0;
            const pd = parseFloat(amountPaid.value) || 0;
            const udh = Math.max(0, tot - pd);

            summaryTotal.textContent = '₹' + tot.toFixed(2);
            summaryPaid.textContent = '₹' + pd.toFixed(2);
            summaryUdhari.textContent = '₹' + udh.toFixed(2);
        }

        salePrice.addEventListener('input', updateSummary);
        amountPaid.addEventListener('input', updateSummary);
    });

    window.handleEmiBillUpload = function(input) {
        const file = input.files && input.files[0];
        if (!file) return;

        const btn = document.getElementById('btnScanEmiBill');
        const btnText = document.getElementById('btnScanText');
        const statusBox = document.getElementById('emiScanStatus');
        const statusMsg = document.getElementById('emiScanStatusMsg');
        const progressBar = document.getElementById('emiScanProgressBar');
        const successBanner = document.getElementById('emiScanSuccessBanner');
        const successText = document.getElementById('emiScanSuccessText');

        statusBox.style.display = 'block';
        successBanner.style.display = 'none';
        btn.disabled = true;
        btnText.textContent = 'Scanning...';
        statusMsg.textContent = 'Uploading document image...';
        progressBar.style.width = '30%';

        const formData = new FormData();
        formData.append('bill_image', file);
        formData.append('_token', '{{ csrf_token() }}');

        setTimeout(() => {
            statusMsg.textContent = 'Analyzing with Google Gemini AI OCR...';
            progressBar.style.width = '70%';
        }, 600);

        fetch('{{ route('mobileshop.pos.scan_emi_bill') }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(({ status, body }) => {
            btn.disabled = false;
            btnText.textContent = 'Scan / Upload EMI Bill';
            input.value = '';

            if (status !== 200 || !body.success) {
                statusBox.style.display = 'none';
                alert('⚠️ OCR Scan Notice: ' + (body.message || 'Could not parse document.'));
                return;
            }

            progressBar.style.width = '100%';
            statusMsg.textContent = 'Done! Auto-filling POS fields...';

            setTimeout(() => {
                statusBox.style.display = 'none';
            }, 800);

            const d = body.data || {};
            let matchedFields = [];

            // 1. Customer Phone & Name
            if (d.customer_phone) {
                const cleanP = String(d.customer_phone).replace(/[^0-9]/g, '');
                if (cleanP.length >= 10) {
                    const phoneInput = document.getElementById('customerPhone');
                    phoneInput.value = cleanP.slice(-10);
                    phoneInput.dispatchEvent(new Event('input'));
                    matchedFields.push('Phone (' + cleanP.slice(-10) + ')');
                }
            }

            if (d.customer_name) {
                document.getElementById('customerName').value = d.customer_name;
                matchedFields.push('Customer (' + d.customer_name + ')');
            }

            if (d.customer_address) {
                document.getElementById('customerAddress').value = d.customer_address;
            }

            if (d.customer_gstin) {
                document.getElementById('customerGstin').value = d.customer_gstin;
            }

            // 2. Device Selection (by Matched Device or IMEI)
            const deviceSelect = document.getElementById('deviceSelect');
            let deviceMatched = false;
            if (body.matched_device && body.matched_device.id) {
                deviceSelect.value = body.matched_device.id;
                deviceSelect.dispatchEvent(new Event('change'));
                deviceMatched = true;
                matchedFields.push('Device (' + (body.matched_device.brand || '') + ' ' + (body.matched_device.model || '') + ')');
            } else if (d.imei) {
                const targetImei = String(d.imei).replace(/[^0-9]/g, '');
                for (let i = 0; i < deviceSelect.options.length; i++) {
                    const opt = deviceSelect.options[i];
                    if (opt.dataset.imei && opt.dataset.imei.replace(/[^0-9]/g, '').includes(targetImei)) {
                        deviceSelect.selectedIndex = i;
                        deviceSelect.dispatchEvent(new Event('change'));
                        deviceMatched = true;
                        matchedFields.push('IMEI Matched (' + targetImei + ')');
                        break;
                    }
                }
            }

            // 3. Payment Mode & EMI Fields
            const paymentMode = document.getElementById('paymentMode');
            paymentMode.value = 'emi';
            paymentMode.dispatchEvent(new Event('change'));

            if (body.matched_provider_id) {
                const emiProv = document.getElementById('emiProviderId');
                if (emiProv) {
                    emiProv.value = body.matched_provider_id;
                    matchedFields.push('Financier Matched');
                }
            }

            if (d.emi_loan_no) {
                const loanInput = document.querySelector('input[name="emi_loan_no"]');
                if (loanInput) {
                    loanInput.value = d.emi_loan_no;
                    matchedFields.push('Loan #' + d.emi_loan_no);
                }
            }

            if (d.emi_downpayment && parseFloat(d.emi_downpayment) > 0) {
                const dp = parseFloat(d.emi_downpayment);
                const dpInput = document.getElementById('emiDownpayment');
                if (dpInput) dpInput.value = dp.toFixed(2);
                document.getElementById('amountPaid').value = dp.toFixed(2);
                document.getElementById('amountPaid').dispatchEvent(new Event('input'));
                matchedFields.push('Down Payment (₹' + dp.toFixed(2) + ')');
            }

            if (d.sale_price && parseFloat(d.sale_price) > 0) {
                const sp = parseFloat(d.sale_price);
                document.getElementById('salePrice').value = sp.toFixed(2);
                document.getElementById('salePrice').dispatchEvent(new Event('input'));
            }

            // Show Success Notification Banner
            successBanner.style.display = 'flex';
            successText.textContent = 'Auto-Filled: ' + (matchedFields.join(' • ') || 'Details extracted');
            if (!deviceMatched && (d.brand || d.model || d.imei)) {
                successText.textContent += ' ⚠️ Note: Device (' + (d.brand || '') + ' ' + (d.model || '') + ' ' + (d.imei ? 'IMEI: ' + d.imei : '') + ') was not matched in stock. Please verify device.';
            }

            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        })
        .catch(err => {
            btn.disabled = false;
            btnText.textContent = 'Scan / Upload EMI Bill';
            statusBox.style.display = 'none';
            input.value = '';
            alert('⚠️ Network Error: ' + err.message);
        });
    };
</script>
@endpush
