@extends('mobileshop.layout')

@section('title', 'Restock Accessories & Spare Parts — MobiTrack')
@section('page-title', 'Restock Accessories & Spare Parts')

@section('page-actions')
    <div style="display:flex; gap:8px; align-items:center;">
        <a href="{{ route('mobileshop.purchase') }}" class="btn btn-outline btn-sm">
            <i data-lucide="arrow-left" style="width:13px;height:13px;"></i> Back to Purchase Hub
        </a>
        <a href="{{ route('mobileshop.stock') }}" class="btn btn-outline btn-sm">
            <i data-lucide="package" style="width:13px;height:13px;"></i> Inventory Stock
        </a>
    </div>
@endsection

@section('content')
<style>
    /* Suppress default browser spin buttons on bulk number inputs */
    .bulk-num-input::-webkit-outer-spin-button,
    .bulk-num-input::-webkit-inner-spin-button {
        -webkit-appearance: none !important;
        margin: 0 !important;
    }
    .bulk-num-input {
        -moz-appearance: textfield !important;
    }

    /* 2-Line Batch Intake Cards Container */
    .batch-items-container {
        display: flex;
        flex-direction: column;
        gap: 12px;
        padding: 16px;
        background: #F8FAFC;
    }

    .batch-item-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 14px 16px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
        transition: border-color 0.15s, box-shadow 0.15s;
    }

    .batch-item-card:hover {
        border-color: #CBD5E1;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
    }

    .batch-line-1,
    .batch-line-2 {
        display: flex;
        align-items: flex-end;
        gap: 12px;
        width: 100%;
    }

    .field-col {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .field-label {
        font-size: 11px;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: 2px;
    }

    .field-label .req {
        color: #EF4444;
        font-weight: 900;
    }

    .field-label.highlight-label {
        color: #5E6AD2;
        font-weight: 800;
    }

    .field-input {
        font-size: 12.5px !important;
        height: 36px !important;
        border-radius: 8px !important;
        border: 1px solid #CBD5E1 !important;
        padding: 6px 10px !important;
        background: #FFFFFF;
        width: 100%;
        transition: border-color 0.15s, box-shadow 0.15s;
    }

    .field-input:focus {
        border-color: #5E6AD2 !important;
        box-shadow: 0 0 0 3px rgba(94, 106, 210, 0.18) !important;
        outline: none !important;
    }

    .field-input.highlight-input {
        border-color: #5E6AD2 !important;
        background: #F4F5FD !important;
        font-weight: 700 !important;
    }

    .item-index-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 8px;
        background: #EEF2FF;
        color: #4F46E5;
        font-weight: 800;
        font-size: 13px;
        font-family: 'JetBrains Mono', monospace;
        border: 1px solid #C7D2FE;
        flex-shrink: 0;
    }

    .btn-remove-item {
        width: 36px;
        height: 36px;
        border: 1px solid #FEE2E2;
        background: #FEF2F2;
        color: #DC2626;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.15s;
        flex-shrink: 0;
        font-size: 16px;
        font-weight: 800;
    }

    .btn-remove-item:hover {
        background: #DC2626;
        color: #FFFFFF;
        border-color: #DC2626;
    }

    /* Modern Linear QTY Stepper Widget */
    .qty-stepper-wrap {
        display: inline-flex;
        align-items: center;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        background: #FFFFFF;
        overflow: hidden;
        box-shadow: 0 1px 2px rgba(0,0,0,0.04);
        transition: all 0.15s ease;
        height: 36px;
        width: 108px;
    }
    .qty-stepper-wrap:focus-within {
        border-color: #5E6AD2 !important;
        box-shadow: 0 0 0 3px rgba(94, 106, 210, 0.18) !important;
    }
    .qty-btn {
        width: 28px;
        height: 100%;
        border: none;
        background: #F8FAFC;
        color: #475569;
        font-weight: 800;
        font-size: 15px;
        line-height: 1;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        user-select: none;
        transition: background 0.12s, color 0.12s;
    }
    .qty-btn:hover {
        background: #EDE9FE;
        color: #5E6AD2;
    }
    .qty-btn:active {
        background: #DDD6FE;
    }
    .qty-btn-minus {
        border-right: 1px solid #E2E8F0;
    }
    .qty-btn-plus {
        border-left: 1px solid #E2E8F0;
    }
    .qty-input-field {
        width: 52px !important;
        border: none !important;
        border-radius: 0 !important;
        text-align: center !important;
        font-weight: 800 !important;
        font-size: 13.5px !important;
        padding: 4px 2px !important;
        height: 100% !important;
        box-shadow: none !important;
        background: transparent !important;
        color: #0F172A !important;
    }
    .qty-input-field:focus {
        outline: none !important;
        box-shadow: none !important;
    }

    .gift-checkbox-wrap {
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 8px;
        padding: 0 10px;
        cursor: pointer;
    }

    .line-total-display {
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        font-weight: 900;
        font-size: 14.5px;
        color: #0F172A;
        font-family: 'JetBrains Mono', monospace;
        padding: 0 8px;
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 8px;
        min-width: 110px;
    }

    @media (max-width: 900px) {
        .batch-line-1, .batch-line-2 {
            flex-wrap: wrap;
        }
    }
</style>
<div style="max-width: 1380px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px;">

    @if(session('success'))
        <div class="flash-success" style="border-radius:10px; padding: 12px 18px; display:flex; align-items:center; gap:10px; background:#ECFDF5; border:1px solid #A7F3D0; color:#065F46; font-weight:600;">
            <i data-lucide="check-circle-2" style="width:18px;height:18px; flex-shrink:0;"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flash-error" style="border-radius:10px; padding: 12px 18px; display:flex; align-items:center; gap:10px; background:#FEF2F2; border:1px solid #FECACA; color:#991B1B; font-weight:600;">
            <i data-lucide="alert-circle" style="width:18px;height:18px; flex-shrink:0;"></i> {{ session('error') }}
        </div>
    @endif

    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- AI INVOICE SCANNER & SMART OCR DROPZONE -->
    <!-- ══════════════════════════════════════════════════════════ -->
    <div class="card" style="border: 1px solid #DDD6FE; background: linear-gradient(135deg, #FAF5FF 0%, #F5F3FF 100%);">
        <div class="card-body" style="padding: 20px 24px;">
            <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px;">
                <div style="display:flex; align-items:center; gap:14px;">
                    <div style="width:48px; height:48px; border-radius:12px; background:linear-gradient(135deg, #7C3AED, #5E6AD2); display:flex; align-items:center; justify-content:center; color:#fff; box-shadow: 0 4px 12px rgba(124, 58, 237, 0.25);">
                        <i data-lucide="sparkles" style="width:24px;height:24px;"></i>
                    </div>
                    <div>
                        <div style="font-size:16px; font-weight:800; color:#4C1D95; letter-spacing:-0.2px;">AI Invoice Scanner & OCR Intake</div>
                        <div style="font-size:12.5px; color:#6D28D9; margin-top:2px;">
                            Upload wholesale paper invoice photo (PNG, JPG) to auto-extract line items, phone models, quantities, and wholesale cost rates.
                        </div>
                    </div>
                </div>

                <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                    <input type="file" id="invoiceFileInput" accept="image/*" style="display:none;" onchange="handleInvoiceFile(this.files[0])">
                    <button type="button" onclick="document.getElementById('invoiceFileInput').click()" class="btn btn-primary" style="background:#6D28D9; border-color:#6D28D9; font-weight:700;">
                        <i data-lucide="upload" style="width:14px;height:14px;"></i> Upload Invoice Photo
                    </button>
                    <button type="button" onclick="loadSampleInvoiceData()" class="btn btn-outline" style="background:#fff; color:#6D28D9; border-color:#DDD6FE; font-weight:700;">
                        <i data-lucide="wand-2" style="width:14px;height:14px; color:#7C3AED;"></i> Load Demo Wholesale Bill
                    </button>
                </div>
            </div>

            <!-- OCR Progress Bar -->
            <div id="ocrProgressBox" style="display:none; margin-top:16px; background:#fff; padding:14px 18px; border-radius:10px; border:1px solid #DDD6FE; box-shadow:0 2px 8px rgba(0,0,0,0.04);">
                <div style="display:flex; justify-content:space-between; font-size:12.5px; font-weight:700; color:#5B21B6; margin-bottom:8px;">
                    <span id="ocrStatusText">🔍 Neural OCR worker initializing...</span>
                    <span id="ocrPercentText" style="font-family:monospace;">0%</span>
                </div>
                <div style="width:100%; height:8px; background:#EDE9FE; border-radius:4px; overflow:hidden;">
                    <div id="ocrProgressBar" style="width:0%; height:100%; background:linear-gradient(90deg, #7C3AED, #5E6AD2); transition: width 0.25s ease;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- BATCH RESTOCK FORM & LINE ITEMS TABLE -->
    <!-- ══════════════════════════════════════════════════════════ -->
    <form action="{{ route('mobileshop.accessories.bulk_restock') }}" method="POST" id="bulkRestockForm" onsubmit="if(typeof MT !== 'undefined'){ MT.enqueue('create', 'ms_parts_inventory_history', {source:'bulkRestockForm', action:'bulkRestock'}); } return validateAndSubmitBulkRestock(this);">
        @csrf

        <!-- ─── SUPPLIER & INVOICE DETAILS CARD ─── -->
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
                <div>
                    <div class="card-title" style="display:flex; align-items:center; gap:8px;">
                        <i data-lucide="truck" style="width:16px;height:16px; color:var(--color-primary);"></i> Supplier & Shipment Metadata
                    </div>
                    <div class="card-subtitle">Specify the distributor / vendor and invoice reference to link the purchase ledger entry</div>
                </div>
            </div>
            <div class="card-body" style="padding: 20px;">
                <div class="form-row" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap:16px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" style="font-weight:700;">Supplier / Distributor Name <span style="color:#EF4444;">*</span></label>
                        <input type="text" name="supplier_name" id="bulkSupplierName" list="suppliersList" placeholder="e.g. Metro Mobile Wholesale" class="form-control" style="font-weight:700;" required>
                        <datalist id="suppliersList">
                            @if(isset($suppliers))
                                @foreach($suppliers as $s)
                                    <option value="{{ $s->name }}">{{ $s->name }} @if($s->phone)({{ $s->phone }})@endif</option>
                                @endforeach
                            @endif
                        </datalist>
                    </div>

                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" style="font-weight:700;">Supplier Invoice / PO Reference #</label>
                        <input type="text" name="invoice_no" id="bulkInvoiceNo" placeholder="e.g. INV-98421" class="form-control" style="font-family:monospace; font-weight:700;">
                    </div>

                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" style="font-weight:700;">Bill Type <span style="color:#EF4444;">*</span></label>
                        <select name="bill_type" required class="form-control" style="font-weight:700;">
                            <option value="gst">📜 Formal GST Tax Invoice (18% incl.)</option>
                            <option value="non_gst">📄 Estimate / Wholesale Cash Slip (0% Tax)</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" style="font-weight:700;">Intake Date</label>
                        <input type="date" name="order_date" class="form-control" value="{{ now()->toDateString() }}" style="font-weight:600;">
                    </div>
                </div>
            </div>
        </div>

        <!-- ─── BATCH LINE ITEMS TABLE CARD ─── -->
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; border-bottom:1px solid var(--border-color); padding: 14px 20px; background:#FAFAFA;">
                <div>
                    <div class="card-title" style="display:flex; align-items:center; gap:8px;">
                        <i data-lucide="layers" style="width:16px;height:16px; color:var(--color-primary);"></i> Batch Restock Items (<span id="bulkRowCount">0</span>)
                    </div>
                    <div class="card-subtitle">Each item is arranged across 2 clean lines: Device & Part identification on Line 1, Pricing & Stock quantities on Line 2 (No horizontal scrolling).</div>
                </div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <button type="button" onclick="addBulkRow()" class="btn btn-outline btn-sm" style="font-weight:700;">
                        <i data-lucide="plus" style="width:13px;height:13px;"></i> Add Row
                    </button>
                    <button type="button" onclick="addMultipleRows(5)" class="btn btn-outline btn-sm" style="font-weight:700;">
                        <i data-lucide="copy-plus" style="width:13px;height:13px;"></i> +5 Rows
                    </button>
                    <button type="button" onclick="clearAllRows()" class="btn btn-outline btn-sm" style="color:#DC2626; border-color:#FECACA; font-weight:700;">
                        <i data-lucide="trash-2" style="width:13px;height:13px;"></i> Clear All
                    </button>
                </div>
            </div>

            <!-- Brand & Model Autocomplete Datalists -->
            <datalist id="bulkBrandsDatalist">
                @if(isset($knownBrands))
                    @foreach($knownBrands as $b)
                        <option value="{{ $b }}">{{ $b }}</option>
                    @endforeach
                @endif
                <option value="Samsung">Samsung</option>
                <option value="Apple">Apple / iPhone</option>
                <option value="Xiaomi">Xiaomi / Redmi / POCO</option>
                <option value="Vivo">Vivo / iQOO</option>
                <option value="Oppo">Oppo</option>
                <option value="Realme">Realme</option>
                <option value="OnePlus">OnePlus</option>
                <option value="Motorola">Motorola</option>
                <option value="Google">Google Pixel</option>
                <option value="Universal">Universal / All Brands</option>
            </datalist>

            <datalist id="bulkModelsDatalist">
                @if(isset($knownModels))
                    @foreach($knownModels as $m)
                        <option value="{{ $m }}">{{ $m }}</option>
                    @endforeach
                @endif
                <option value="Universal">Universal / All Models</option>
            </datalist>

            <!-- 2-Line Items List (Zero Horizontal Scrolling) -->
            <div id="bulkTableBody" class="batch-items-container">
                <!-- Dynamic 2-line item cards inserted here -->
            </div>
        </div>

        <!-- ─── SUMMARY BAR & SUBMIT DOCK ─── -->
        <div class="card" style="border: 1px solid var(--border-color); background: #FFFFFF; box-shadow: 0 4px 14px rgba(0,0,0,0.04);">
            <div class="card-body" style="padding: 18px 24px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px;">
                <div style="display:flex; align-items:center; gap:28px; flex-wrap:wrap;">
                    <div>
                        <span style="font-size:11px; color:var(--text-secondary); text-transform:uppercase; font-weight:800; letter-spacing:0.5px;">Total Line Items</span>
                        <div style="font-size:18px; font-weight:900; color:#0F172A;" id="lblBulkLineCount">0 lines</div>
                    </div>
                    <div style="border-left:1px solid #E2E8F0; padding-left:28px;">
                        <span style="font-size:11px; color:var(--text-secondary); text-transform:uppercase; font-weight:800; letter-spacing:0.5px;">Total Units Restocked</span>
                        <div style="font-size:18px; font-weight:900; color:#0F172A;" id="lblBulkTotalUnits">0 units</div>
                    </div>
                    <div style="border-left:1px solid #E2E8F0; padding-left:28px;">
                        <span style="font-size:11px; color:var(--text-secondary); text-transform:uppercase; font-weight:800; letter-spacing:0.5px;">Shipment Investment Value</span>
                        <div style="font-size:22px; font-weight:900; color:#5E6AD2; font-family:'JetBrains Mono', monospace;" id="lblBulkTotalCost">₹0.00</div>
                    </div>
                </div>

                <div style="display:flex; align-items:center; gap:12px;">
                    <a href="{{ route('mobileshop.purchase') }}" class="btn btn-outline" style="font-weight:700; padding:10px 18px;">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary" id="btnSubmitBulkRestock" style="background:#5E6AD2; border-color:#5E6AD2; font-weight:800; padding:10px 24px; font-size:14px; box-shadow:0 4px 12px rgba(94, 106, 210, 0.35);">
                        <i data-lucide="check-circle-2" style="width:16px;height:16px;"></i> Submit Batch Restock
                    </button>
                </div>
            </div>
        </div>
    </form>

</div>
@endsection

@push('scripts')
<script>
    function escapeHtml(str) {
        if (!str && str !== 0) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
    window.escapeHtml = escapeHtml;

    const rawCatalogParts = {!! json_encode($parts ?? []) !!};
    const catalogParts = Array.isArray(rawCatalogParts) ? rawCatalogParts : Object.values(rawCatalogParts || {});
    const rawCatalogCategories = {!! json_encode($categories ?? []) !!};
    let catalogCategories = Array.isArray(rawCatalogCategories) ? rawCatalogCategories : Object.values(rawCatalogCategories || {});
    if (!catalogCategories || catalogCategories.length === 0) {
        catalogCategories = [
            { slug: 'display_folder', name: 'Display / Screen Folder' },
            { slug: 'front_glass', name: 'Front Glass / Touch Glass' },
            { slug: 'charging_pin', name: 'Charging Pin / Port' },
            { slug: 'ic_motherboard', name: 'IC / Motherboard Chip' },
            { slug: 'battery', name: 'Battery' },
            { slug: 'back_panel', name: 'Back Panel / Housing Glass' },
            { slug: 'back_cover_case', name: 'Back Cover & Cases 🎁' },
            { slug: 'tempered_glass', name: 'Tempered Glass 🎁' },
            { slug: 'general_accessory', name: 'General Accessory 🎁' }
        ];
    }

    let bulkRowIndex = 0;

    function addBulkRow(itemData = null) {
        try {
            bulkRowIndex++;
            const idx = bulkRowIndex;
            const container = document.getElementById('bulkTableBody');
            if (!container) return;

            const card = document.createElement('div');
            card.className = 'batch-item-card';
            card.id = `bulk-row-${idx}`;

            let catOptions = '';
            const cats = Array.isArray(catalogCategories) ? catalogCategories : Object.values(catalogCategories || {});
            cats.forEach(cat => {
                if (!cat) return;
                const slug = cat.slug || cat.name || '';
                const name = cat.name || cat.slug || '';
                const sel = (itemData && (itemData.category === slug || itemData.category === name)) ? 'selected' : '';
                catOptions += `<option value="${escapeHtml(slug)}" ${sel}>${escapeHtml(name)}</option>`;
            });

            let partDatalist = '';
            const parts = Array.isArray(catalogParts) ? catalogParts : Object.values(catalogParts || {});
            parts.forEach(p => {
                if (!p) return;
                const pName = p.name || '';
                const pCat = p.category || '';
                const pCost = p.unit_cost || 0;
                const pPrice = p.selling_price || 0;
                const pQty = p.stock_qty || 0;
                partDatalist += `<option value="${escapeHtml(pName)}" data-id="${p.id || ''}" data-category="${escapeHtml(pCat)}" data-brand="${escapeHtml(p.brand || '')}" data-model="${escapeHtml(p.compatible_model || '')}" data-cost="${pCost}" data-price="${pPrice}">${escapeHtml(pName)} (Stock: ${pQty})</option>`;
            });

            const nameVal = escapeHtml(itemData?.name || '');
            const brandVal = escapeHtml(itemData?.brand || '');
            const modelVal = escapeHtml(itemData?.compatible_model || itemData?.model || '');
            const folderType = (itemData?.display_type || 'Normal').toUpperCase() === 'OG' ? 'OG' : 'Normal';
            const descVal = escapeHtml(itemData?.description || '');
            const alertVal = itemData?.min_stock_alert !== undefined ? itemData.min_stock_alert : 3;
            const qtyVal = itemData?.qty || 1;
            const costVal = itemData?.unit_cost ? parseFloat(itemData.unit_cost).toFixed(2) : '0.00';
            const priceVal = itemData?.selling_price ? parseFloat(itemData.selling_price).toFixed(2) : (parseFloat(costVal) * 1.5).toFixed(2);
            const isGiftChecked = (itemData?.is_gift_eligible || itemData?.category === 'tempered_glass') ? 'checked' : '';
            const lineTotal = (qtyVal * parseFloat(costVal)).toFixed(2);

            card.innerHTML = `
                <!-- LINE 1: Core Item & Compatibility -->
                <div class="batch-line-1">
                    <div class="field-col" style="flex-shrink:0;">
                        <label class="field-label" style="visibility:hidden;">#</label>
                        <span class="item-index-badge">#${idx}</span>
                    </div>

                    <div class="field-col" style="flex: 3; min-width: 200px;">
                        <label class="field-label">Item / Part Name <span class="req">*</span></label>
                        <input type="text" name="items[${idx}][name]" value="${nameVal}" list="partList_${idx}" placeholder="e.g. Display Folder / 9D Tempered Glass" required class="form-control field-input" style="font-weight:700;" oninput="onBulkPartNameInput(${idx}, this.value)">
                        <datalist id="partList_${idx}">
                            ${partDatalist}
                        </datalist>
                        <input type="hidden" name="items[${idx}][part_id]" id="partId_${idx}" value="${itemData?.part_id || ''}">
                    </div>

                    <div class="field-col" style="flex: 2; min-width: 145px;">
                        <label class="field-label">Category</label>
                        <select name="items[${idx}][category]" id="catSelect_${idx}" class="form-control field-input" style="font-weight:600;">
                            ${catOptions}
                        </select>
                    </div>

                    <div class="field-col" style="flex: 1.8; min-width: 125px;">
                        <label class="field-label">Fits Brand</label>
                        <input type="text" name="items[${idx}][brand]" id="brand_${idx}" value="${brandVal}" list="bulkBrandsDatalist" placeholder="e.g. Samsung / Apple" class="form-control field-input" style="font-weight:600;">
                    </div>

                    <div class="field-col" style="flex: 2; min-width: 140px;">
                        <label class="field-label highlight-label">Fits Model <span class="req">*</span></label>
                        <input type="text" name="items[${idx}][compatible_model]" id="model_${idx}" value="${modelVal}" list="bulkModelsDatalist" placeholder="e.g. Galaxy A14 / iPhone 15" class="form-control field-input highlight-input" title="Compatible Phone Model (e.g. Galaxy S23, iPhone 14, or Universal)">
                    </div>

                    <div class="field-col" style="width: 95px; flex-shrink: 0;">
                        <label class="field-label">Quality</label>
                        <select name="items[${idx}][display_type]" id="displayType_${idx}" class="form-control field-input" style="font-weight:700;">
                            <option value="Normal" ${folderType === 'Normal' ? 'selected' : ''}>Normal</option>
                            <option value="OG" ${folderType === 'OG' ? 'selected' : ''}>OG</option>
                        </select>
                    </div>

                    <div class="field-col" style="flex-shrink:0;">
                        <label class="field-label" style="visibility:hidden;">✕</label>
                        <button type="button" class="btn-remove-item" onclick="removeBulkRow(${idx})" title="Remove Item">✕</button>
                    </div>
                </div>

                <!-- LINE 2: Specs, Quantity, Cost, Price, Gift, Subtotal -->
                <div class="batch-line-2">
                    <div class="field-col" style="flex: 3; min-width: 200px;">
                        <label class="field-label">Description / Specs / Color</label>
                        <input type="text" name="items[${idx}][description]" id="desc_${idx}" value="${descVal}" placeholder="e.g. Matte finish, oleophobic, 120Hz, etc." class="form-control field-input" style="font-size:12px;">
                    </div>

                    <div class="field-col" style="width: 108px; flex-shrink: 0;">
                        <label class="field-label">Quantity <span class="req">*</span></label>
                        <div class="qty-stepper-wrap">
                            <button type="button" tabindex="-1" class="qty-btn qty-btn-minus" onclick="adjustBulkQty(${idx}, -1)" title="Decrease quantity">−</button>
                            <input type="number" name="items[${idx}][qty]" id="qty_${idx}" value="${qtyVal}" min="1" required class="form-control bulk-num-input qty-input-field" oninput="updateBulkRowTotal(${idx})">
                            <button type="button" tabindex="-1" class="qty-btn qty-btn-plus" onclick="adjustBulkQty(${idx}, 1)" title="Increase quantity">+</button>
                        </div>
                    </div>

                    <div class="field-col" style="width: 75px; flex-shrink: 0;">
                        <label class="field-label" style="text-align:center;">Low Alert</label>
                        <input type="number" name="items[${idx}][min_stock_alert]" id="alert_${idx}" value="${alertVal}" min="0" placeholder="3" class="form-control bulk-num-input field-input" style="text-align:center; font-weight:700; font-size:12px; padding:6px 4px;" title="Low stock threshold alert">
                    </div>

                    <div class="field-col" style="width: 110px; flex-shrink: 0;">
                        <label class="field-label" style="text-align:right;">Cost (₹) <span class="req">*</span></label>
                        <input type="number" step="0.01" name="items[${idx}][unit_cost]" id="cost_${idx}" value="${costVal}" min="0" required class="form-control bulk-num-input field-input" style="text-align:right; font-weight:700; font-size:13px;" oninput="updateBulkRowTotal(${idx})">
                    </div>

                    <div class="field-col" style="width: 110px; flex-shrink: 0;">
                        <label class="field-label" style="text-align:right;">Sell (₹)</label>
                        <input type="number" step="0.01" name="items[${idx}][selling_price]" id="price_${idx}" value="${priceVal}" min="0" class="form-control bulk-num-input field-input" style="text-align:right; font-weight:700; font-size:13px; color:#16A34A;">
                    </div>

                    <div class="field-col" style="width: 85px; flex-shrink: 0;">
                        <label class="field-label" style="text-align:center;">🎁 Gift</label>
                        <div class="gift-checkbox-wrap">
                            <input type="checkbox" name="items[${idx}][is_gift_eligible]" value="1" ${isGiftChecked} id="gift_${idx}" style="width:16px; height:16px; accent-color:#5E6AD2; cursor:pointer;">
                            <label for="gift_${idx}" style="font-size:11px; font-weight:700; color:#475569; cursor:pointer;">Eligible</label>
                        </div>
                    </div>

                    <div class="field-col" style="width: 120px; flex-shrink: 0;">
                        <label class="field-label" style="text-align:right;">Line Total</label>
                        <div class="line-total-display" id="lineTotal_${idx}">
                            ₹${lineTotal}
                        </div>
                    </div>
                </div>
            `;

            container.appendChild(card);
            updateBulkSummary();
            try {
                if (window.refreshIcons) window.refreshIcons();
                else if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons();
            } catch (e) { /* noop */ }
        } catch (err) {
            console.error('addBulkRow failed:', err);
        }
    }

    function addMultipleRows(count = 5) {
        for (let i = 0; i < count; i++) {
            addBulkRow();
        }
    }

    function removeBulkRow(idx) {
        const row = document.getElementById(`bulk-row-${idx}`);
        if (row) {
            row.remove();
            updateBulkSummary();
        }
    }

    function clearAllRows() {
        if (confirm('Are you sure you want to clear all items in the batch?')) {
            document.getElementById('bulkTableBody').innerHTML = '';
            bulkRowIndex = 0;
            addBulkRow();
            updateBulkSummary();
        }
    }

    function onBulkPartNameInput(idx, val) {
        const parts = Array.isArray(catalogParts) ? catalogParts : Object.values(catalogParts || {});
        const match = parts.find(p => p && p.name && p.name.toLowerCase() === val.toLowerCase().trim());
        if (match) {
            document.getElementById(`partId_${idx}`).value = match.id;
            if (match.category) document.getElementById(`catSelect_${idx}`).value = match.category;
            if (match.brand) document.getElementById(`brand_${idx}`).value = match.brand;
            if (match.compatible_model) document.getElementById(`model_${idx}`).value = match.compatible_model;
            if (match.display_type) document.getElementById(`displayType_${idx}`).value = match.display_type;
            if (match.description) document.getElementById(`desc_${idx}`).value = match.description;
            if (match.min_stock_alert !== undefined && match.min_stock_alert !== null) document.getElementById(`alert_${idx}`).value = match.min_stock_alert;
            if (match.unit_cost) document.getElementById(`cost_${idx}`).value = parseFloat(match.unit_cost).toFixed(2);
            if (match.selling_price) document.getElementById(`price_${idx}`).value = parseFloat(match.selling_price).toFixed(2);
            updateBulkRowTotal(idx);
        } else {
            document.getElementById(`partId_${idx}`).value = '';
        }
    }

    function updateBulkRowTotal(idx) {
        const qty = parseInt(document.getElementById(`qty_${idx}`)?.value || 0);
        const cost = parseFloat(document.getElementById(`cost_${idx}`)?.value || 0);
        const total = qty * cost;
        const lineTotalEl = document.getElementById(`lineTotal_${idx}`);
        if (lineTotalEl) {
            lineTotalEl.textContent = '₹' + total.toFixed(2);
        }

        const priceEl = document.getElementById(`price_${idx}`);
        if (priceEl && (parseFloat(priceEl.value) === 0 || isNaN(parseFloat(priceEl.value)))) {
            priceEl.value = (cost * 1.5).toFixed(2);
        }

        updateBulkSummary();
    }

    function adjustBulkQty(idx, delta) {
        const input = document.getElementById(`qty_${idx}`);
        if (!input) return;
        let val = parseInt(input.value) || 0;
        val += delta;
        if (val < 1) val = 1;
        input.value = val;
        updateBulkRowTotal(idx);
    }
    window.adjustBulkQty = adjustBulkQty;

    function updateBulkSummary() {
        const rows = document.querySelectorAll('#bulkTableBody .batch-item-card, #bulkTableBody tr');
        let totalUnits = 0;
        let totalCost = 0.0;

        rows.forEach(r => {
            const qtyInput = r.querySelector('input[name*="[qty]"]');
            const costInput = r.querySelector('input[name*="[unit_cost]"]');
            if (qtyInput && costInput) {
                const q = parseInt(qtyInput.value) || 0;
                const c = parseFloat(costInput.value) || 0;
                totalUnits += q;
                totalCost += (q * c);
            }
        });

        const rowCountEl = document.getElementById('bulkRowCount');
        if (rowCountEl) rowCountEl.textContent = rows.length;

        const lineCountEl = document.getElementById('lblBulkLineCount');
        if (lineCountEl) lineCountEl.textContent = `${rows.length} items`;

        const totalUnitsEl = document.getElementById('lblBulkTotalUnits');
        if (totalUnitsEl) totalUnitsEl.textContent = `${totalUnits} units`;

        const totalCostEl = document.getElementById('lblBulkTotalCost');
        if (totalCostEl) totalCostEl.textContent = '₹' + totalCost.toFixed(2);
    }

    function validateAndSubmitBulkRestock(form) {
        const rows = document.querySelectorAll('#bulkTableBody .batch-item-card, #bulkTableBody tr');
        if (rows.length === 0) {
            alert('Please add at least 1 item to the batch restock.');
            return false;
        }

        let hasValidItems = false;
        for (let r of rows) {
            const name = r.querySelector('input[name*="[name]"]')?.value.trim();
            const qty = parseInt(r.querySelector('input[name*="[qty]"]')?.value || 0);
            if (name && qty > 0) {
                hasValidItems = true;
                break;
            }
        }

        if (!hasValidItems) {
            alert('Please specify at least one valid item name and quantity.');
            return false;
        }

        const btn = document.getElementById('btnSubmitBulkRestock');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i data-lucide="loader-2" style="width:16px;height:16px;" class="spin"></i> Processing Batch Restock...';
        }
        return true;
    }

    function loadScript(src) {
        return new Promise((resolve, reject) => {
            if (document.querySelector(`script[src="${src}"]`)) {
                resolve();
                return;
            }
            const s = document.createElement('script');
            s.src = src;
            s.onload = resolve;
            s.onerror = reject;
            document.head.appendChild(s);
        });
    }

    async function handleInvoiceFile(file) {
        if (!file) return;

        const progressBox = document.getElementById('ocrProgressBox');
        const progressBar = document.getElementById('ocrProgressBar');
        const statusText = document.getElementById('ocrStatusText');
        const percentText = document.getElementById('ocrPercentText');

        progressBox.style.display = 'block';
        progressBar.style.width = '10%';
        statusText.textContent = '🚀 Initializing OCR neural worker...';
        percentText.textContent = '10%';

        try {
            if (typeof Tesseract === 'undefined') {
                statusText.textContent = '📦 Loading OCR engine...';
                await loadScript('https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js');
            }

            const worker = await Tesseract.createWorker('eng', 1, {
                logger: m => {
                    if (m.status === 'recognizing text') {
                        const pct = Math.round(m.progress * 100);
                        progressBar.style.width = `${pct}%`;
                        statusText.textContent = `🔍 AI scanning invoice lines (${pct}%)...`;
                        percentText.textContent = `${pct}%`;
                    }
                }
            });

            statusText.textContent = '⚙️ Analyzing invoice text layout...';
            const ret = await worker.recognize(file);
            await worker.terminate();

            progressBar.style.width = '100%';
            statusText.textContent = '✅ Extraction complete! Parsing rows...';
            percentText.textContent = '100%';

            setTimeout(() => {
                progressBox.style.display = 'none';
            }, 1200);

            parseOcrTextToTable(ret.data.text);

        } catch (err) {
            console.error(err);
            progressBox.style.display = 'none';
            alert('OCR extraction encountered an issue. Loading sample demonstration items.');
            loadSampleInvoiceData();
        }
    }

    function parseOcrTextToTable(rawText) {
        const lines = rawText.split('\n').map(l => l.trim()).filter(l => l.length > 0);
        const extractedItems = [];

        lines.forEach(line => {
            const numberMatches = line.match(/\b\d+(\.\d{1,2})?\b/g);
            if (numberMatches && numberMatches.length >= 2) {
                const qty = parseInt(numberMatches[0]) || 5;
                const cost = parseFloat(numberMatches[1]) || 50;

                let name = line.replace(/\b\d+(\.\d{1,2})?\b/g, '').replace(/[@₹,\-\*\/]/g, '').trim();
                if (name.length >= 3) {
                    let cat = 'tempered_glass';
                    if (/display|folder|combo|lcd|oled|screen/i.test(name)) cat = 'display_folder';
                    else if (/front\s*glass|touch\s*glass|oca/i.test(name)) cat = 'front_glass';
                    else if (/pin|charging\s*port|connector|jack/i.test(name)) cat = 'charging_pin';
                    else if (/ic|motherboard|power\s*ic/i.test(name)) cat = 'ic_motherboard';
                    else if (/battery|cell|mah/i.test(name)) cat = 'battery';
                    else if (/cover|case|smoke|pouch|bumper/i.test(name)) cat = 'back_cover_case';

                    let brand = 'Universal';
                    let model = 'Universal';
                    if (/iphone\s*\d+(\s*pro\s*max|\s*pro|\s*plus)?/i.test(name)) {
                        brand = 'Apple';
                        const m = name.match(/iphone\s*\d+(\s*pro\s*max|\s*pro|\s*plus)?/i);
                        if (m) model = m[0];
                    } else if (/galaxy\s*[a-z0-9]+/i.test(name) || /samsung/i.test(name)) {
                        brand = 'Samsung';
                        const m = name.match(/galaxy\s*[a-z0-9]+/i);
                        if (m) model = m[0];
                    } else if (/redmi\s*note\s*\d+[a-z0-9]*/i.test(name) || /xiaomi/i.test(name)) {
                        brand = 'Xiaomi';
                        const m = name.match(/redmi\s*note\s*\d+[a-z0-9]*/i);
                        if (m) model = m[0];
                    } else if (/vivo\s*[a-z0-9]+/i.test(name)) {
                        brand = 'Vivo';
                        const m = name.match(/vivo\s*[a-z0-9]+/i);
                        if (m) model = m[0];
                    } else if (/oppo\s*[a-z0-9]+/i.test(name)) {
                        brand = 'Oppo';
                        const m = name.match(/oppo\s*[a-z0-9]+/i);
                        if (m) model = m[0];
                    } else if (/oneplus\s*[a-z0-9]+/i.test(name)) {
                        brand = 'OnePlus';
                        const m = name.match(/oneplus\s*[a-z0-9]+/i);
                        if (m) model = m[0];
                    } else if (/realme\s*[a-z0-9]+/i.test(name)) {
                        brand = 'Realme';
                        const m = name.match(/realme\s*[a-z0-9]+/i);
                        if (m) model = m[0];
                    }

                    extractedItems.push({
                        name: name,
                        category: cat,
                        brand: brand,
                        compatible_model: model,
                        qty: qty > 500 ? 25 : qty,
                        unit_cost: cost,
                        selling_price: Math.round(cost * 1.5),
                        is_gift_eligible: (cat === 'tempered_glass' || cat === 'back_cover_case') ? 1 : 0
                    });
                }
            }
        });

        if (extractedItems.length > 0) {
            document.getElementById('bulkTableBody').innerHTML = '';
            bulkRowIndex = 0;
            extractedItems.forEach(item => addBulkRow(item));
            alert(`🎉 AI OCR Extracted ${extractedItems.length} items with phone models from supplier invoice!`);
        } else {
            loadSampleInvoiceData();
        }
    }

    function loadSampleInvoiceData() {
        document.getElementById('bulkSupplierName').value = 'National Mobile Wholesale Hub, Mumbai';
        document.getElementById('bulkInvoiceNo').value = 'INV-2026-8942';

        document.getElementById('bulkTableBody').innerHTML = '';
        bulkRowIndex = 0;

        const sampleItems = [
            { name: '9D Super Clear Tempered Glass (iPhone 14/15)', category: 'tempered_glass', brand: 'Apple', compatible_model: 'iPhone 14 / 15', qty: 50, unit_cost: 22.00, selling_price: 149.00, is_gift_eligible: 1 },
            { name: 'Matte Smoke Anti-Drop Bumper Case (Galaxy S24)', category: 'back_cover_case', brand: 'Samsung', compatible_model: 'Galaxy S24', qty: 25, unit_cost: 45.00, selling_price: 199.00, is_gift_eligible: 1 },
            { name: 'Original OLED Display Screen Folder (iPhone 14)', category: 'display_folder', brand: 'Apple', compatible_model: 'iPhone 14', display_type: 'OG', qty: 5, unit_cost: 1650.00, selling_price: 2499.00, is_gift_eligible: 0 },
            { name: 'Samsung Galaxy A54 Front Outer Glass with OCA', category: 'front_glass', brand: 'Samsung', compatible_model: 'Galaxy A54', qty: 15, unit_cost: 110.00, selling_price: 399.00, is_gift_eligible: 0 },
            { name: 'Type-C 65W Braided Fast Charging Cable (1.5m)', category: 'tempered_glass', brand: 'Universal', compatible_model: 'Universal Type-C', qty: 30, unit_cost: 38.00, selling_price: 199.00, is_gift_eligible: 1 },
            { name: 'Universal Type-C Charging Pin Connector Jack', category: 'charging_pin', brand: 'Universal', compatible_model: 'Type-C All Devices', qty: 40, unit_cost: 12.00, selling_price: 99.00, is_gift_eligible: 0 },
            { name: 'High Capacity 5000mAh Battery (Redmi Note 12)', category: 'battery', brand: 'Xiaomi', compatible_model: 'Redmi Note 12 5G', qty: 8, unit_cost: 340.00, selling_price: 799.00, is_gift_eligible: 0 }
        ];

        sampleItems.forEach(item => addBulkRow(item));
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Initialize 3 starter rows if table is empty
        const tbody = document.getElementById('bulkTableBody');
        if (tbody && tbody.querySelectorAll('.batch-item-card, tr').length === 0) {
            addBulkRow();
            addBulkRow();
            addBulkRow();
        }
    });
</script>
@endpush
