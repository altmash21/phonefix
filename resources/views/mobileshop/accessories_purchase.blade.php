@extends('mobileshop.layout')

@section('title', 'Restock Accessories & Spare Parts — Maurya Mobile')
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
    /* ── Hide browser number spinners ── */
    .bulk-num-input::-webkit-outer-spin-button,
    .bulk-num-input::-webkit-inner-spin-button { -webkit-appearance: none !important; margin: 0 !important; }
    .bulk-num-input { -moz-appearance: textfield !important; }

    /* ── Container Layout ── */
    .restock-container {
        max-width: 1420px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    /* ── High-Contrast Form Inputs ── */
    .restock-input {
        width: 100%;
        color: #111827 !important;
        font-weight: 600 !important;
        background: #ffffff !important;
        border: 1px solid #CBD5E1 !important;
        border-radius: 6px !important;
        padding: 5px 8px !important;
        font-size: 12.5px !important;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
        box-sizing: border-box;
    }
    .restock-input:focus {
        outline: none !important;
        border-color: #6366F1 !important;
        box-shadow: 0 0 0 2.5px rgba(99, 102, 241, 0.15) !important;
        background: #ffffff !important;
    }
    .restock-input.cat-select {
        background-color: #F8FAFC !important;
        color: #3730A3 !important;
        font-weight: 700 !important;
        cursor: pointer;
    }
    .restock-input.num-field {
        font-family: 'JetBrains Mono', monospace !important;
        font-weight: 700 !important;
        color: #111827 !important;
    }
    .restock-input.cost-field {
        text-align: right;
        color: #0F172A !important;
    }
    .restock-input.sell-field {
        text-align: right;
        color: #047857 !important;
    }

    /* ── Ghost Delete Cross Button (Clearly visible, turns red on hover) ── */
    .btn-ghost-delete {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        background: #F1F5F9;
        border: 1px solid #CBD5E1;
        color: #475569;
        cursor: pointer;
        padding: 0;
        transition: all 0.15s ease;
        flex-shrink: 0;
    }
    .btn-ghost-delete:hover {
        background: #FEF2F2 !important;
        border-color: #FECACA !important;
        color: #DC2626 !important;
    }

    /* ── Stepper Component (Responsive) ── */
    .stepper-wrap {
        display: inline-flex;
        align-items: center;
        width: 100%;
        border: 1px solid #CBD5E1;
        border-radius: 6px;
        background: #ffffff;
        overflow: hidden;
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .stepper-wrap:focus-within {
        border-color: #6366F1 !important;
        box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.15);
    }
    .stepper-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        background: #F8FAFC;
        color: #475569;
        font-size: 15px;
        font-weight: 800;
        border: none;
        cursor: pointer;
        user-select: none;
        transition: background 0.1s, color 0.1s;
        padding: 0;
        flex-shrink: 0;
    }
    .stepper-btn:hover {
        background: #EDE9FE;
        color: #4F46E5;
    }
    .stepper-btn-minus { border-right: 1px solid #E2E8F0; }
    .stepper-btn-plus  { border-left:  1px solid #E2E8F0; }
    .stepper-input {
        flex: 1;
        border: none !important;
        border-radius: 0 !important;
        text-align: center !important;
        font-family: 'JetBrains Mono', monospace !important;
        font-weight: 800 !important;
        color: #111827 !important;
        padding: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
        min-width: 0;
    }

    /* ── Quality OG / Normal Inline Pill ── */
    .quality-toggle-pill {
        display: none;
        align-items: center;
        gap: 3px;
        padding: 2px 6px;
        background: #FEF3C7;
        border: 1px solid #FCD34D;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 800;
        color: #92400E;
        cursor: pointer;
        margin-top: 3px;
    }
    .quality-toggle-pill.visible {
        display: inline-flex;
    }

    /* ==========================================================================
       DESKTOP VIEWPORT (>= 880px): True Aligned Tabular Grid
       ========================================================================== */
    @media (min-width: 880px) {
        .batch-grid-row {
            display: grid;
            grid-template-columns: 140px minmax(220px, 2fr) minmax(150px, 1.2fr) 96px 105px 105px 65px 105px 36px;
            gap: 8px;
            align-items: center;
        }

        .batch-table-header {
            background: #F8FAFC;
            border-bottom: 2px solid #E2E8F0;
            padding: 8px 14px;
            font-size: 10px;
            font-weight: 700;
            color: #64748B;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .batch-item-row {
            padding: 7px 14px;
            background: #FFFFFF;
            border-bottom: 1px solid #F1F5F9;
            transition: background 0.12s ease;
        }
        .batch-item-row:hover {
            background: #FBFBFE;
        }
        .batch-item-row:last-child {
            border-bottom: none;
        }

        .restock-input {
            height: 32px !important;
            font-size: 12px !important;
        }
        .stepper-wrap {
            height: 32px;
        }
        .stepper-btn {
            width: 26px;
            height: 100%;
        }
        .stepper-input {
            font-size: 12.5px !important;
        }

        /* Hide mobile card chrome on desktop */
        .mobile-card-top,
        .mobile-card-label,
        .mobile-sticky-footer {
            display: none !important;
        }

        .desktop-total-cell {
            text-align: right;
        }
        .desktop-total-amt {
            font-family: 'JetBrains Mono', monospace;
            font-size: 13px;
            font-weight: 800;
            color: #0F172A;
            line-height: 1.2;
        }
    }

    /* ==========================================================================
       MOBILE & TABLET VIEWPORT (< 880px): Clean Card Transformation & Sticky Dock
       ========================================================================== */
    @media (max-width: 879px) {
        .batch-table-header {
            display: none !important;
        }

        .batch-items-container {
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 10px;
            background: #F8FAFC;
            border-radius: 8px;
        }

        .batch-item-row {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 10px;
            padding: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .mobile-card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            border-bottom: 1px solid #F1F5F9;
            padding-bottom: 8px;
        }

        .mobile-row-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #EEF2FF;
            color: #4F46E5;
            font-weight: 800;
            font-size: 10.5px;
            padding: 3px 8px;
            border-radius: 6px;
            font-family: 'JetBrains Mono', monospace;
        }

        .mobile-card-label {
            display: block;
            font-size: 10px;
            font-weight: 700;
            color: #64748B;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 3px;
        }

        /* 44px touch targets on mobile */
        .restock-input {
            height: 44px !important;
            font-size: 13.5px !important;
            padding: 8px 10px !important;
            border-radius: 7px !important;
        }
        .stepper-wrap {
            height: 44px;
            border-radius: 7px;
        }
        .stepper-btn {
            width: 44px;
            height: 44px;
            font-size: 18px;
        }
        .stepper-input {
            font-size: 14px !important;
        }
        .btn-ghost-delete {
            width: 36px;
            height: 36px;
        }

        /* Mobile metric columns */
        .mobile-metric-grid {
            display: grid;
            grid-template-columns: 1.1fr 1fr 1fr 0.7fr;
            gap: 8px;
        }

        .mobile-card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 8px;
            border-top: 1px dashed #E2E8F0;
        }

        .desktop-total-cell {
            display: none !important;
        }
        .desktop-action-cell {
            display: none !important;
        }

        /* Ensure bottom margin so fixed dock doesn't obscure content */
        .restock-container {
            padding-bottom: 96px;
        }

        /* Mobile Sticky Footer Dock */
        .mobile-sticky-footer {
            display: flex !important;
            position: fixed;
            bottom: var(--bottom-nav-height, 58px);
            left: 0;
            right: 0;
            z-index: 960;
            background: #FFFFFF;
            border-top: 1px solid #E2E8F0;
            box-shadow: 0 -4px 16px rgba(0, 0, 0, 0.08);
            padding: 10px 14px;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .desktop-summary-card {
            display: none !important;
        }
    }
</style>

<div class="restock-container">

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
    <!-- AI INVOICE SCANNER — COMPACT OCR TOOLBAR -->
    <!-- ══════════════════════════════════════════════════════════ -->
    <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap; padding:9px 14px; background:#F5F3FF; border:1px solid #DDD6FE; border-radius:8px;">
        <i data-lucide="sparkles" style="width:15px;height:15px; color:#7C3AED; flex-shrink:0;"></i>
        <span style="font-size:12px; font-weight:700; color:#4C1D95; flex:1; min-width:160px;">AI Invoice OCR &mdash; Scan wholesale vendor paper invoice to auto-fill items</span>
        <input type="file" id="invoiceFileInput" accept="image/*" style="display:none;" onchange="handleInvoiceFile(this.files[0])">
        <button type="button" onclick="document.getElementById('invoiceFileInput').click()" class="btn btn-sm" style="background:#6D28D9; color:#fff; border:none; font-weight:700; padding:5px 12px; border-radius:6px; font-size:11.5px; display:inline-flex; align-items:center; gap:5px;">
            <i data-lucide="camera" style="width:13px;height:13px;"></i> Scan Bill
        </button>
        <button type="button" onclick="loadSampleInvoiceData()" class="btn btn-sm btn-outline" style="font-weight:700; font-size:11.5px; padding:5px 10px; border-radius:6px; color:#6D28D9; border-color:#DDD6FE;">
            Demo Data
        </button>
        <!-- Inline OCR Progress -->
        <div id="ocrProgressBox" style="display:none; width:100%; margin-top:6px;">
            <div style="display:flex; justify-content:space-between; font-size:11px; font-weight:700; color:#5B21B6; margin-bottom:3px;">
                <span id="ocrStatusText">🔍 OCR running...</span>
                <span id="ocrPercentText" style="font-family:monospace;">0%</span>
            </div>
            <div style="width:100%; height:4px; background:#EDE9FE; border-radius:4px; overflow:hidden;">
                <div id="ocrProgressBar" style="width:0%; height:100%; background:linear-gradient(90deg,#7C3AED,#5E6AD2); transition:width 0.25s ease;"></div>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- BATCH RESTOCK FORM -->
    <!-- ══════════════════════════════════════════════════════════ -->
    <form action="{{ route('mobileshop.accessories.bulk_restock') }}" method="POST" id="bulkRestockForm" onsubmit="if(typeof MT !== 'undefined'){ MT.enqueue('create', 'ms_parts_inventory_history', {source:'bulkRestockForm', action:'bulkRestock'}); } return validateAndSubmitBulkRestock(this);">
        @csrf

        <!-- ─── SUPPLIER & SHIPMENT METADATA CARD ─── -->
        <div class="card" style="margin-bottom: 12px; border-radius:8px; border:1px solid #E2E8F0; background:#FFFFFF;">
            <div class="card-header" style="border-bottom: 1px solid #E2E8F0; padding: 7px 12px; background:#FAFAFA;">
                <div class="card-title" style="display:flex; align-items:center; gap:6px; font-size:12px; font-weight:700; color:#1E293B;">
                    <i data-lucide="truck" style="width:14px;height:14px; color:var(--color-primary);"></i> Supplier & Shipment Details
                </div>
            </div>
            <div class="card-body" style="padding: 10px 12px;">
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap:10px;">
                    <div>
                        <label class="mobile-card-label" style="display:block;">Supplier / Distributor <span style="color:#EF4444;">*</span></label>
                        <input type="text" name="supplier_name" id="bulkSupplierName" list="suppliersList" placeholder="e.g. Metro Mobile Wholesale" class="restock-input" required>
                        <datalist id="suppliersList">
                            @if(isset($suppliers))
                                @foreach($suppliers as $s)
                                    <option value="{{ $s->name }}">{{ $s->name }} @if($s->phone)({{ $s->phone }})@endif</option>
                                @endforeach
                            @endif
                        </datalist>
                    </div>

                    <div>
                        <label class="mobile-card-label" style="display:block;">Invoice / PO #</label>
                        <input type="text" name="invoice_no" id="bulkInvoiceNo" placeholder="e.g. INV-98421" class="restock-input num-field">
                    </div>

                    <div>
                        <label class="mobile-card-label" style="display:block;">Bill Type <span style="color:#EF4444;">*</span></label>
                        <select name="bill_type" required class="restock-input">
                            <option value="gst">📜 GST Tax Invoice (18% incl.)</option>
                            <option value="non_gst">📄 Estimate / Cash Slip (0% Tax)</option>
                        </select>
                    </div>

                    <div>
                        <label class="mobile-card-label" style="display:block;">Intake Date</label>
                        <input type="date" name="order_date" class="restock-input" value="{{ now()->toDateString() }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- ─── BATCH ITEMS CARD ─── -->
        <div class="card" style="border-radius:8px; border:1px solid #E2E8F0; background:#FFFFFF; overflow:hidden; margin-bottom:12px;">
            <!-- Header Toolbar -->
            <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px; border-bottom:1px solid #E2E8F0; padding:8px 14px; background:#FAFAFA;">
                <div style="display:flex; align-items:center; gap:6px; font-size:12px; font-weight:700; color:#1E293B;">
                    <i data-lucide="layers" style="width:14px;height:14px; color:var(--color-primary);"></i>
                    Batch Items &mdash; <span id="bulkRowCount" style="color:var(--color-primary); font-weight:800;">0</span> rows
                </div>
                <div style="display:flex; align-items:center; gap:6px;">
                    <button type="button" onclick="addBulkRow()" class="btn btn-primary btn-sm" style="font-weight:700; font-size:11.5px; padding:4px 10px; border-radius:6px;">
                        <i data-lucide="plus" style="width:12px;height:12px;"></i> Add Row
                    </button>
                    <button type="button" onclick="addMultipleRows(5)" class="btn btn-outline btn-sm" style="font-weight:700; font-size:11.5px; padding:4px 9px; border-radius:6px;">
                        +5 Rows
                    </button>
                    <button type="button" onclick="clearAllRows()" class="btn btn-outline btn-sm" style="color:#DC2626; border-color:#FECACA; font-weight:700; font-size:11.5px; padding:4px 9px; border-radius:6px;">
                        <i data-lucide="trash-2" style="width:12px;height:12px;"></i> Clear
                    </button>
                </div>
            </div>

            <!-- 1. DESKTOP ALIGNED TABLE HEADER -->
            <div class="batch-table-header batch-grid-row" id="batchTableHeader">
                <div>Category <span style="color:#EF4444;">*</span></div>
                <div>Item Name & Model <span style="color:#EF4444;">*</span></div>
                <div>Description / Specs</div>
                <div style="text-align:center;">Qty <span style="color:#EF4444;">*</span></div>
                <div style="text-align:right;">Cost ₹ <span style="color:#EF4444;">*</span></div>
                <div style="text-align:right;">Sell ₹ <span style="color:#EF4444;">*</span></div>
                <div style="text-align:center;">Alert</div>
                <div style="text-align:right;">Total</div>
                <div style="text-align:center;">Action</div>
            </div>

            <!-- Autocomplete datalists -->
            <datalist id="bulkBrandsDatalist">
                @if(isset($knownBrands))
                    @foreach($knownBrands as $b)<option value="{{ $b }}">{{ $b }}</option>@endforeach
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
                    @foreach($knownModels as $m)<option value="{{ $m }}">{{ $m }}</option>@endforeach
                @endif
                <option value="Universal">Universal / All Models</option>
            </datalist>

            <!-- 2. BATCH ROWS CONTAINER -->
            <div id="bulkTableBody" class="batch-items-container">
                <!-- Rows injected dynamically by JS -->
            </div>
        </div>

        <!-- ─── DESKTOP SUMMARY STAT STRIP ─── -->
        <div class="card desktop-summary-card" style="border:1px solid #E2E8F0; background:#FFFFFF; border-radius:8px; box-shadow:0 1px 3px rgba(0,0,0,0.03); margin-bottom:20px;">
            <div class="card-body" style="padding:10px 16px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
                <div style="display:flex; align-items:center; gap:24px; flex-wrap:wrap;">
                    <div>
                        <span style="font-size:10px; color:#64748B; text-transform:uppercase; font-weight:700; letter-spacing:0.4px;">Items</span>
                        <div style="font-size:15px; font-weight:800; color:#0F172A;" id="lblBulkLineCount">0 lines</div>
                    </div>
                    <div style="border-left:1px solid #E2E8F0; padding-left:24px;">
                        <span style="font-size:10px; color:#64748B; text-transform:uppercase; font-weight:700; letter-spacing:0.4px;">Units</span>
                        <div style="font-size:15px; font-weight:800; color:#0F172A;" id="lblBulkTotalUnits">0 units</div>
                    </div>
                    <div style="border-left:1px solid #E2E8F0; padding-left:24px;">
                        <span style="font-size:10px; color:#64748B; text-transform:uppercase; font-weight:700; letter-spacing:0.4px;">Total Cost</span>
                        <div style="font-size:18px; font-weight:900; color:#4F46E5; font-family:'JetBrains Mono', monospace;" id="lblBulkTotalCost">₹0.00</div>
                    </div>
                </div>

                <div style="display:flex; align-items:center; gap:8px;">
                    <a href="{{ route('mobileshop.purchase') }}" class="btn btn-outline btn-sm" style="font-weight:700; padding:6px 14px; border-radius:6px; font-size:12px;">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary btn-sm" id="btnSubmitBulkRestock" style="background:#5E6AD2; border-color:#5E6AD2; font-weight:800; padding:7px 18px; font-size:12px; border-radius:6px; box-shadow:0 2px 6px rgba(94, 106, 210, 0.3);">
                        <i data-lucide="check-circle-2" style="width:14px;height:14px;"></i> Purchase Accessories
                    </button>
                </div>
            </div>
        </div>

        <!-- ─── MOBILE STICKY BOTTOM DOCK ─── -->
        <div class="mobile-sticky-footer" id="mobileStickyFooter" style="display:none;">
            <div>
                <div style="font-size:9.5px; font-weight:700; color:#64748B; text-transform:uppercase; letter-spacing:0.3px;" id="mobileStickyCount">0 ITEMS &bull; 0 UNITS</div>
                <div style="font-size:17px; font-weight:900; color:#4F46E5; font-family:'JetBrains Mono', monospace; line-height:1.2;" id="mobileStickyTotal">₹0.00</div>
            </div>
            <button type="submit" class="btn btn-primary" id="btnMobileSubmitRestock" style="height:44px; padding:0 20px; font-size:13.5px; font-weight:800; border-radius:8px; display:inline-flex; align-items:center; gap:6px; box-shadow:0 2px 8px rgba(94, 106, 210, 0.35);">
                <i data-lucide="check-circle-2" style="width:16px;height:16px;"></i> Purchase Accessories
            </button>
        </div>

    </form>

</div>
@endsection

@push('scripts')
<script>
    /* ── HTML Sanitizer ── */
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

    /* ── Local Currency Formatter (₹9,585.00) ── */
    function formatCurrency(num) {
        const val = parseFloat(num) || 0;
        return '₹' + val.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    const rawCatalogParts = {!! json_encode($parts ?? []) !!};
    const catalogParts = Array.isArray(rawCatalogParts) ? rawCatalogParts : Object.values(rawCatalogParts || {});
    const rawCatalogCategories = {!! json_encode($categories ?? []) !!};
    let catalogCategories = Array.isArray(rawCatalogCategories) ? rawCatalogCategories : Object.values(rawCatalogCategories || {});
    if (!catalogCategories || catalogCategories.length === 0) {
        catalogCategories = [
            { slug: 'folder_display', name: 'Display Screen / Folder' },
            { slug: 'front_glass', name: 'Front Glass' },
            { slug: 'charging_port', name: 'Charging Pin / Port' },
            { slug: 'ic_chip', name: 'IC / Motherboard Chip' },
            { slug: 'battery', name: 'Battery' },
            { slug: 'back_panel', name: 'Back Panel / Housing Glass' },
            { slug: 'back_cover', name: 'Back Cover & Cases 🎁' },
            { slug: 'tempered_glass', name: 'Tempered Glass 🎁' },
            { slug: 'charger_cable', name: 'Chargers & Cables 🎁' },
            { slug: 'earphones_audio', name: 'Earphones & Audio 🎁' },
            { slug: 'camera_module', name: 'Camera Module' },
            { slug: 'general_accessory', name: 'General Accessories 🎁' }
        ];
    }

    /* ── Canonicalize category names & aliases to standard DB slugs ── */
    function canonicalizeCategory(cat, name = '') {
        const text = (String(cat || '') + ' ' + String(name || '')).toLowerCase().trim();
        const c = String(cat || '').toLowerCase().trim().replace(/[-\s]/g, '_');

        if (/folder|display|screen|lcd|oled|tft|combo|touch\s*display/.test(text) || c === 'display_folder' || c === 'folder_display') {
            return 'folder_display';
        }
        if (/front\s*glass|touch\s*glass|oca/.test(text) || c === 'front_glass') {
            return 'front_glass';
        }
        if (/pin|charging\s*port|connector|charging\s*jack|cc\s*board|sub\s*board/.test(text) || c === 'charging_pin' || c === 'charging_port') {
            return 'charging_port';
        }
        if (/ic\b|motherboard|power\s*ic|charging\s*ic|wtr|pmic|cpu\s*ic|chip/.test(text) || c === 'ic_motherboard' || c === 'ic_chip') {
            return 'ic_chip';
        }
        if (/battery|cell|mah\b/.test(text) || c === 'battery') {
            return 'battery';
        }
        if (/back\s*panel|housing|housing\s*glass|body/.test(text) || c === 'back_panel') {
            return 'back_panel';
        }
        if (/cover|case|smoke|bumper|pouch|silicone|leather|matte\s*case/.test(text) || c === 'back_cover_case' || c === 'back_cover') {
            return 'back_cover';
        }
        if (/tempered|11d|9d|21d|uv\s*glass|d\+|screen\s*guard|screen\s*protector/.test(text) || c === 'tempered_glass') {
            return 'tempered_glass';
        }
        if (/charger|cable|adapter|type-c|lightning|micro\s*usb|braided|fast\s*charg|power\s*bank|pd\s*cable/.test(text) || c === 'charger' || c === 'cable' || c === 'charger_cable') {
            return 'charger_cable';
        }
        if (/earphone|headphone|audio|buds|airpod|neckband|tws|bluetooth\s*headset|speaker|mic\b|aux|sound/.test(text) || c === 'audio' || c === 'earphones_audio') {
            return 'earphones_audio';
        }
        if (/camera|lens|camera\s*glass|camera\s*module/.test(text) || c === 'camera_module') {
            return 'camera_module';
        }
        return c || 'general_accessory';
    }
    window.canonicalizeCategory = canonicalizeCategory;

    let bulkRowIndex = 0;

    // Auto-detect brand from item name text
    function guessBrand(name) {
        const n = (name || '').toLowerCase();
        if (/iphone|apple/.test(n))                  return 'Apple';
        if (/galaxy|samsung|s\d{2}\b|\ba\d{2}/.test(n)) return 'Samsung';
        if (/redmi|poco|xiaomi|\bnote\s*\d/.test(n)) return 'Xiaomi';
        if (/vivo|iqoo/.test(n))                     return 'Vivo';
        if (/oppo|reno/.test(n))                     return 'Oppo';
        if (/realme|narzo/.test(n))                  return 'Realme';
        if (/oneplus/.test(n))                       return 'OnePlus';
        if (/moto|motorola/.test(n))                 return 'Motorola';
        if (/pixel|google/.test(n))                  return 'Google';
        return 'Universal';
    }

    function addBulkRow(itemData = null) {
        try {
            bulkRowIndex++;
            const idx = bulkRowIndex;
            const container = document.getElementById('bulkTableBody');
            if (!container) return;

            const row = document.createElement('div');
            row.className = 'batch-item-row batch-grid-row';
            row.id = `bulk-row-${idx}`;

            const targetCat = canonicalizeCategory(itemData?.category, itemData?.name);

            let catOptions = '';
            let matchedOption = false;
            const cats = Array.isArray(catalogCategories) ? catalogCategories : Object.values(catalogCategories || {});
            cats.forEach(cat => {
                if (!cat) return;
                const slug = cat.slug || cat.name || '';
                const name = cat.name || cat.slug || '';
                const isSelected = itemData && (
                    itemData.category === slug ||
                    itemData.category === name ||
                    targetCat === slug ||
                    canonicalizeCategory(slug) === targetCat ||
                    canonicalizeCategory(name) === targetCat
                );
                if (isSelected && !matchedOption) {
                    matchedOption = true;
                    catOptions += `<option value="${escapeHtml(slug)}" selected>${escapeHtml(name)}</option>`;
                } else {
                    catOptions += `<option value="${escapeHtml(slug)}">${escapeHtml(name)}</option>`;
                }
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
            const isGiftChecked = (itemData?.is_gift_eligible || targetCat === 'tempered_glass' || targetCat === 'back_cover' || targetCat === 'charger_cable' || targetCat === 'earphones_audio' || targetCat === 'general_accessory') ? 'checked' : '';
            const lineTotal = (qtyVal * parseFloat(costVal));
            const detectedBrand = guessBrand(nameVal || '') || brandVal;
            const isFolder = (targetCat === 'folder_display');

            const matchedCatObj = cats.find(c => (c.slug === targetCat || canonicalizeCategory(c.slug) === targetCat));
            const catDisplayName = matchedCatObj?.name || 'Category';

            row.innerHTML = `
                <!-- Hidden form fields -->
                <input type="hidden" name="items[${idx}][part_id]" id="partId_${idx}" value="${itemData?.part_id || ''}">
                <input type="hidden" name="items[${idx}][brand]"   id="brand_${idx}" value="${escapeHtml(detectedBrand)}">
                <input type="hidden" name="items[${idx}][compatible_model]" id="model_${idx}" value="${modelVal}">
                <input type="hidden" name="items[${idx}][is_gift_eligible]" id="gift_${idx}" value="${isGiftChecked ? '1' : '0'}">
                <input type="hidden" name="items[${idx}][display_type]" id="displayTypeVal_${idx}" value="${folderType}">

                <!-- MOBILE CARD TOP BAR -->
                <div class="mobile-card-top">
                    <div style="display:flex; align-items:center; gap:6px;">
                        <span class="mobile-row-badge">#${idx}</span>
                        <span style="font-size:11px; font-weight:700; color:#64748B;" id="mobileCardCatLabel_${idx}">Category</span>
                    </div>
                    <button type="button" class="btn-ghost-delete" onclick="removeBulkRow(${idx})" title="Remove item">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>

                <!-- 1. CATEGORY -->
                <div>
                    <label class="mobile-card-label">Category</label>
                    <select name="items[${idx}][category]" id="catSelect_${idx}"
                        class="restock-input cat-select"
                        onchange="onBulkCategoryChange(${idx}, this.value)">
                        ${catOptions}
                    </select>
                </div>

                <!-- 2. ITEM NAME & MODEL -->
                <div>
                    <label class="mobile-card-label">Item Name & Model</label>
                    <input type="text" name="items[${idx}][name]" id="name_${idx}"
                        value="${nameVal}" list="partList_${idx}"
                        placeholder="e.g. 9D Glass iPhone 15 Pro / OLED Folder Galaxy A14"
                        required class="restock-input"
                        oninput="onBulkPartNameInput(${idx}, this.value)">
                    <datalist id="partList_${idx}">${partDatalist}</datalist>

                    <!-- OG / Normal folder quality switch pill -->
                    <div class="quality-toggle-pill ${isFolder ? 'visible' : ''}" id="qualityToggle_${idx}" onclick="toggleFolderQuality(${idx})">
                        <span>Quality:</span> <strong id="qualityLabel_${idx}">${folderType} ✨</strong>
                    </div>
                </div>

                <!-- 3. DESCRIPTION / SPECS -->
                <div>
                    <label class="mobile-card-label">Description / Specs</label>
                    <input type="text" name="items[${idx}][description]" id="desc_${idx}"
                        value="${descVal}" placeholder="e.g. Matte, 120Hz, Black"
                        class="restock-input">
                </div>

                <!-- 4. MOBILE METRIC GRID (Transforms to tabular columns on desktop) -->
                <div class="mobile-metric-grid" style="display:contents;">
                    <!-- Qty Stepper -->
                    <div>
                        <label class="mobile-card-label">Qty</label>
                        <div class="stepper-wrap">
                            <button type="button" tabindex="-1" class="stepper-btn stepper-btn-minus" onclick="adjustBulkQty(${idx}, -1)">−</button>
                            <input type="number" name="items[${idx}][qty]" id="qty_${idx}"
                                value="${qtyVal}" min="1" required
                                class="bulk-num-input stepper-input"
                                oninput="updateBulkRowTotal(${idx})">
                            <button type="button" tabindex="-1" class="stepper-btn stepper-btn-plus" onclick="adjustBulkQty(${idx}, 1)">+</button>
                        </div>
                    </div>

                    <!-- Cost ₹ -->
                    <div>
                        <label class="mobile-card-label">Cost ₹</label>
                        <input type="number" step="0.01" name="items[${idx}][unit_cost]" id="cost_${idx}"
                            value="${costVal}" min="0" required
                            class="bulk-num-input restock-input num-field cost-field"
                            oninput="updateBulkRowTotal(${idx})">
                    </div>

                    <!-- Sell ₹ -->
                    <div>
                        <label class="mobile-card-label">Sell ₹</label>
                        <input type="number" step="0.01" name="items[${idx}][selling_price]" id="price_${idx}"
                            value="${priceVal}" min="0" required
                            class="bulk-num-input restock-input num-field sell-field"
                            oninput="updateBulkRowTotal(${idx})">
                    </div>

                    <!-- Alert -->
                    <div>
                        <label class="mobile-card-label">Alert</label>
                        <input type="number" name="items[${idx}][min_stock_alert]" id="alert_${idx}"
                            value="${alertVal}" min="0" placeholder="3"
                            class="bulk-num-input restock-input num-field" style="text-align:center;"
                            title="Low stock threshold">
                    </div>
                </div>

                <!-- 5. DESKTOP TOTAL CELL -->
                <div class="desktop-total-cell">
                    <div class="desktop-total-amt" id="lineTotal_${idx}">${formatCurrency(lineTotal)}</div>
                </div>

                <!-- 6. DESKTOP GHOST DELETE BUTTON -->
                <div class="desktop-action-cell" style="text-align:center;">
                    <button type="button" class="btn-ghost-delete" onclick="removeBulkRow(${idx})" title="Remove item">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>

                <!-- 7. MOBILE CARD FOOTER -->
                <div class="mobile-card-footer">
                    <span style="font-size:11px; color:#64748B; font-weight:700; text-transform:uppercase; letter-spacing:0.3px;">Line Total</span>
                    <strong style="font-size:14px; font-family:'JetBrains Mono', monospace; font-weight:800; color:#0F172A;" id="mobileLineTotal_${idx}">${formatCurrency(lineTotal)}</strong>
                </div>
            `;

            container.appendChild(row);

            // Ensure select value is set explicitly to targetCat if provided
            const selectEl = document.getElementById(`catSelect_${idx}`);
            if (selectEl && targetCat) {
                for (let i = 0; i < selectEl.options.length; i++) {
                    const optVal = selectEl.options[i].value;
                    const optText = selectEl.options[i].text;
                    if (optVal === targetCat || canonicalizeCategory(optVal) === targetCat || canonicalizeCategory(optText) === targetCat) {
                        selectEl.selectedIndex = i;
                        onBulkCategoryChange(idx, selectEl.value);
                        break;
                    }
                }
            }

            updateBulkSummary();

            try {
                if (window.refreshIcons) window.refreshIcons();
                else if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons();
            } catch (e) { /* noop */ }
        } catch (err) {
            console.error('addBulkRow failed:', err);
        }
    }

    function toggleFolderQuality(idx) {
        const valInput = document.getElementById(`displayTypeVal_${idx}`);
        const labelEl = document.getElementById(`qualityLabel_${idx}`);
        if (!valInput || !labelEl) return;

        if (valInput.value === 'OG') {
            valInput.value = 'Normal';
            labelEl.textContent = 'Normal';
        } else {
            valInput.value = 'OG';
            labelEl.textContent = 'OG ✨';
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

    function onBulkCategoryChange(idx, val) {
        const canonicalVal = canonicalizeCategory(val);

        // Show/hide OG/Normal pill switch only for folder_display
        const qualityToggle = document.getElementById(`qualityToggle_${idx}`);
        if (qualityToggle) {
            if (canonicalVal === 'folder_display' || val === 'display_folder') {
                qualityToggle.classList.add('visible');
            } else {
                qualityToggle.classList.remove('visible');
            }
        }

        // Auto-set gift eligible hidden field based on category
        const giftEl = document.getElementById(`gift_${idx}`);
        if (giftEl) {
            const isGift = (
                canonicalVal === 'tempered_glass' || 
                canonicalVal === 'back_cover' || 
                canonicalVal === 'charger_cable' || 
                canonicalVal === 'earphones_audio' || 
                canonicalVal === 'general_accessory'
            );
            giftEl.value = isGift ? '1' : '0';
        }

        // Update mobile category badge
        const catSelect = document.getElementById(`catSelect_${idx}`);
        const mobileBadge = document.getElementById(`mobileCardCatLabel_${idx}`);
        if (catSelect && mobileBadge && catSelect.selectedOptions[0]) {
            mobileBadge.textContent = catSelect.selectedOptions[0].text;
        }

        // Filter part autocomplete
        const dl = document.getElementById(`partList_${idx}`);
        if (dl) {
            const parts = Array.isArray(catalogParts) ? catalogParts : Object.values(catalogParts || {});
            const filtered = val ? parts.filter(p => p && (p.category === val || canonicalizeCategory(p.category) === canonicalVal)) : parts;
            let opts = '';
            filtered.forEach(p => {
                if (!p) return;
                const pName = p.name || '';
                const pCat = p.category || '';
                const pCost = p.unit_cost || 0;
                const pPrice = p.selling_price || 0;
                const pQty = p.stock_qty || 0;
                opts += `<option value="${escapeHtml(pName)}" data-id="${p.id || ''}" data-category="${escapeHtml(pCat)}" data-brand="${escapeHtml(p.brand || '')}" data-model="${escapeHtml(p.compatible_model || '')}" data-cost="${pCost}" data-price="${pPrice}">${escapeHtml(pName)} (Stock: ${pQty})</option>`;
            });
            dl.innerHTML = opts;
        }
    }

    function onBulkPartNameInput(idx, val) {
        const parts = Array.isArray(catalogParts) ? catalogParts : Object.values(catalogParts || {});
        const match = parts.find(p => p && p.name && p.name.toLowerCase() === val.toLowerCase().trim());
        if (match) {
            document.getElementById(`partId_${idx}`).value = match.id;
            if (match.category) {
                const targetCat = canonicalizeCategory(match.category, match.name);
                const selectEl = document.getElementById(`catSelect_${idx}`);
                if (selectEl) {
                    for (let i = 0; i < selectEl.options.length; i++) {
                        const optVal = selectEl.options[i].value;
                        if (optVal === targetCat || canonicalizeCategory(optVal) === targetCat) {
                            selectEl.selectedIndex = i;
                            break;
                        }
                    }
                }
                onBulkCategoryChange(idx, selectEl?.value || targetCat);
            }
            const detectedB = guessBrand(val);
            if (match.brand) document.getElementById(`brand_${idx}`).value = match.brand;
            else document.getElementById(`brand_${idx}`).value = detectedB;
            if (match.compatible_model) document.getElementById(`model_${idx}`).value = match.compatible_model;
            if (match.display_type) {
                document.getElementById(`displayTypeVal_${idx}`).value = match.display_type;
                const qLbl = document.getElementById(`qualityLabel_${idx}`);
                if (qLbl) qLbl.textContent = match.display_type === 'OG' ? 'OG ✨' : 'Normal';
            }
            if (match.description) document.getElementById(`desc_${idx}`).value = match.description;
            if (match.min_stock_alert !== undefined && match.min_stock_alert !== null) document.getElementById(`alert_${idx}`).value = match.min_stock_alert;
            if (match.unit_cost) document.getElementById(`cost_${idx}`).value = parseFloat(match.unit_cost).toFixed(2);
            if (match.selling_price) document.getElementById(`price_${idx}`).value = parseFloat(match.selling_price).toFixed(2);
            updateBulkRowTotal(idx);
        } else {
            document.getElementById(`partId_${idx}`).value = '';
            const b = guessBrand(val);
            const bEl = document.getElementById(`brand_${idx}`);
            if (bEl && b !== 'Universal') bEl.value = b;

            // Auto-detect category from typed item name
            if (val && val.length >= 2) {
                const autoCat = canonicalizeCategory('', val);
                const selectEl = document.getElementById(`catSelect_${idx}`);
                if (selectEl && autoCat && autoCat !== 'general_accessory') {
                    for (let i = 0; i < selectEl.options.length; i++) {
                        const optVal = selectEl.options[i].value;
                        if (optVal === autoCat || canonicalizeCategory(optVal) === autoCat) {
                            selectEl.selectedIndex = i;
                            onBulkCategoryChange(idx, selectEl.value);
                            break;
                        }
                    }
                }
            }
        }
    }

    function updateBulkRowTotal(idx) {
        const qty = parseInt(document.getElementById(`qty_${idx}`)?.value || 0);
        const cost = parseFloat(document.getElementById(`cost_${idx}`)?.value || 0);
        const sell = parseFloat(document.getElementById(`price_${idx}`)?.value || 0);
        const total = qty * cost;

        const lineTotalEl = document.getElementById(`lineTotal_${idx}`);
        if (lineTotalEl) lineTotalEl.textContent = formatCurrency(total);

        const mobileLineTotalEl = document.getElementById(`mobileLineTotal_${idx}`);
        if (mobileLineTotalEl) mobileLineTotalEl.textContent = formatCurrency(total);

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
        const rows = document.querySelectorAll('#bulkTableBody .batch-item-row');
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

        const formattedCost = formatCurrency(totalCost);

        const rowCountEl = document.getElementById('bulkRowCount');
        if (rowCountEl) rowCountEl.textContent = rows.length;

        const lineCountEl = document.getElementById('lblBulkLineCount');
        if (lineCountEl) lineCountEl.textContent = `${rows.length} items`;

        const totalUnitsEl = document.getElementById('lblBulkTotalUnits');
        if (totalUnitsEl) totalUnitsEl.textContent = `${totalUnits} units`;

        const totalCostEl = document.getElementById('lblBulkTotalCost');
        if (totalCostEl) totalCostEl.textContent = formattedCost;

        // Mobile Sticky Dock elements
        const mobileTotalEl = document.getElementById('mobileStickyTotal');
        if (mobileTotalEl) mobileTotalEl.textContent = formattedCost;

        const mobileCountEl = document.getElementById('mobileStickyCount');
        if (mobileCountEl) mobileCountEl.textContent = `${rows.length} ITEMS • ${totalUnits} UNITS`;
    }

    function validateAndSubmitBulkRestock(form) {
        const rows = document.querySelectorAll('#bulkTableBody .batch-item-row');
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
        const mobBtn = document.getElementById('btnMobileSubmitRestock');
        if (mobBtn) {
            mobBtn.disabled = true;
            mobBtn.innerHTML = '<i data-lucide="loader-2" style="width:16px;height:16px;" class="spin"></i> Processing...';
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

        const fileInput = document.getElementById('invoiceFileInput');
        const progressBox = document.getElementById('ocrProgressBox');
        const progressBar = document.getElementById('ocrProgressBar');
        const statusText = document.getElementById('ocrStatusText');
        const percentText = document.getElementById('ocrPercentText');

        if (file.size > 15 * 1024 * 1024) {
            alert('File size exceeds 15MB. Please upload or snap a clearer, compressed photo.');
            if (fileInput) fileInput.value = '';
            return;
        }

        progressBox.style.display = 'block';
        progressBar.style.width = '15%';
        statusText.textContent = '🚀 Uploading invoice slip...';
        percentText.textContent = '15%';

        // Progressive indicator ticks
        const progressTimer1 = setTimeout(() => {
            progressBar.style.width = '45%';
            statusText.textContent = '🔍 Gemini AI reading handwriting, ditto marks & items...';
            percentText.textContent = '45%';
        }, 1200);

        const progressTimer2 = setTimeout(() => {
            progressBar.style.width = '75%';
            statusText.textContent = '⚙️ Extracting display folders, quantities & wholesale rates...';
            percentText.textContent = '75%';
        }, 3200);

        try {
            const formData = new FormData();
            formData.append('invoice_image', file);

            const response = await fetch("{{ route('mobileshop.purchase.scan_invoice') }}", {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            clearTimeout(progressTimer1);
            clearTimeout(progressTimer2);

            const json = await response.json();
            if (fileInput) fileInput.value = '';

            if (!response.ok || !json.success) {
                progressBox.style.display = 'none';
                const errMsg = json.message || 'Could not scan the invoice image. Please check camera clarity or try again.';
                alert('OCR Scan Notice: ' + errMsg);
                return;
            }

            progressBar.style.width = '100%';
            statusText.textContent = '✅ Extraction complete! Populating restock items...';
            percentText.textContent = '100%';

            setTimeout(() => {
                progressBox.style.display = 'none';
            }, 1200);

            const ocrData = (json.data && json.data.items) ? json.data : json;
            populateOcrDataIntoTable(ocrData);

        } catch (err) {
            clearTimeout(progressTimer1);
            clearTimeout(progressTimer2);
            if (fileInput) fileInput.value = '';
            console.error('OCR Upload Error:', err);
            progressBox.style.display = 'none';
            alert('Network error while processing document: ' + err.message);
        }
    }

    function populateOcrDataIntoTable(ocrData) {
        if (!ocrData) return;

        // 1. Supplier Name auto-population
        if (ocrData.supplier_name) {
            const suppInput = document.getElementById('bulkSupplierName');
            if (suppInput) {
                suppInput.value = ocrData.supplier_name;
            }
        }

        // 2. Invoice / PO / Slip No
        const invNo = ocrData.supplier_invoice_no || ocrData.invoice_number || ocrData.invoice_no;
        if (invNo) {
            const invInput = document.getElementById('bulkInvoiceNo');
            if (invInput) {
                invInput.value = invNo;
            }
        }

        // 3. Intake Date
        if (ocrData.invoice_date) {
            const dateInput = document.querySelector('input[name="order_date"]');
            if (dateInput) {
                dateInput.value = ocrData.invoice_date;
            }
        }

        // 4. Populate Items
        const items = ocrData.items || [];
        if (!items || items.length === 0) {
            alert('AI processed the slip, but could not detect any item lines. Please verify image clarity.');
            return;
        }

        const tbody = document.getElementById('bulkTableBody');
        tbody.innerHTML = '';
        bulkRowIndex = 0;

        items.forEach(it => {
            const rawName = it.name || it.model || 'Item';
            const cat = canonicalizeCategory(it.category, rawName);
            const nameLower = rawName.toLowerCase();

            const isOg = /og\b|\boriginal/i.test(nameLower) || (it.display_type && it.display_type.toUpperCase() === 'OG');
            const displayType = isOg ? 'OG' : 'Normal';

            const qty = parseInt(it.qty) || 1;
            const cost = parseFloat(it.unit_cost) || 0;
            let sellingPrice = parseFloat(it.selling_price) || 0;
            if (sellingPrice <= cost) {
                if (cat === 'folder_display' || cat === 'display_folder') {
                    sellingPrice = cost + 250;
                } else if (cat === 'tempered_glass' || cat === 'back_cover' || cat === 'back_cover_case') {
                    sellingPrice = Math.round(cost * 2.5);
                } else {
                    sellingPrice = Math.round(cost * 1.35);
                }
            }

            const brand = it.brand || guessBrand(rawName);
            const model = it.model || it.compatible_model || '';

            addBulkRow({
                name: rawName,
                category: cat,
                brand: brand,
                compatible_model: model,
                display_type: displayType,
                qty: qty,
                unit_cost: cost,
                selling_price: sellingPrice,
                is_gift_eligible: (cat === 'tempered_glass' || cat === 'back_cover' || cat === 'charger_cable' || cat === 'earphones_audio' || cat === 'general_accessory') ? 1 : 0
            });
        });

        updateBulkSummary();

        const supplierName = ocrData.supplier_name || 'Vendor Slip';
        const totalFormatted = ocrData.bill_total ? '₹' + parseFloat(ocrData.bill_total).toLocaleString('en-IN') : '';
        const flashMsg = `🎉 Extracted ${items.length} items from ${supplierName} ${totalFormatted ? '(' + totalFormatted + ')' : ''}! Review rows and click Process Restock.`;

        const banner = document.createElement('div');
        banner.style.cssText = 'background:#ECFDF5; border:1px solid #10B981; color:#065F46; padding:12px 16px; border-radius:8px; font-weight:700; font-size:13px; display:flex; align-items:center; gap:8px; margin-top:10px;';
        banner.innerHTML = `<span>${flashMsg}</span>`;
        const container = document.querySelector('.restock-container');
        const form = document.getElementById('bulkRestockForm');
        if (container && form) {
            container.insertBefore(banner, form);
            setTimeout(() => banner.remove(), 8000);
        }
    }

    function loadSampleInvoiceData() {
        document.getElementById('bulkSupplierName').value = 'National Mobile Wholesale Hub, Mumbai';
        document.getElementById('bulkInvoiceNo').value = 'INV-2026-8942';

        document.getElementById('bulkTableBody').innerHTML = '';
        bulkRowIndex = 0;

        const sampleItems = [
            { name: '9D Super Clear Tempered Glass (iPhone 14/15)', category: 'tempered_glass', brand: 'Apple', compatible_model: 'iPhone 14 / 15', qty: 50, unit_cost: 22.00, selling_price: 149.00, is_gift_eligible: 1 },
            { name: 'Matte Smoke Anti-Drop Bumper Case (Galaxy S24)', category: 'back_cover', brand: 'Samsung', compatible_model: 'Galaxy S24', qty: 25, unit_cost: 45.00, selling_price: 199.00, is_gift_eligible: 1 },
            { name: 'Original OLED Display Screen Folder (iPhone 14)', category: 'folder_display', brand: 'Apple', compatible_model: 'iPhone 14', display_type: 'OG', qty: 5, unit_cost: 1650.00, selling_price: 2499.00, is_gift_eligible: 0 },
            { name: 'Samsung Galaxy A54 Front Outer Glass with OCA', category: 'front_glass', brand: 'Samsung', compatible_model: 'Galaxy A54', qty: 15, unit_cost: 110.00, selling_price: 399.00, is_gift_eligible: 0 },
            { name: 'Type-C 65W Braided Fast Charging Cable (1.5m)', category: 'charger_cable', brand: 'Universal', compatible_model: 'Universal Type-C', qty: 30, unit_cost: 38.00, selling_price: 199.00, is_gift_eligible: 1 },
            { name: 'Universal Type-C Charging Pin Connector Jack', category: 'charging_port', brand: 'Universal', compatible_model: 'Type-C All Devices', qty: 40, unit_cost: 12.00, selling_price: 99.00, is_gift_eligible: 0 },
            { name: 'High Capacity 5000mAh Battery (Redmi Note 12)', category: 'battery', brand: 'Xiaomi', compatible_model: 'Redmi Note 12 5G', qty: 8, unit_cost: 340.00, selling_price: 799.00, is_gift_eligible: 0 }
        ];

        sampleItems.forEach(item => addBulkRow(item));
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Initialize 3 starter rows if table is empty
        const tbody = document.getElementById('bulkTableBody');
        if (tbody && tbody.querySelectorAll('.batch-item-row').length === 0) {
            addBulkRow();
            addBulkRow();
            addBulkRow();
        }
    });
</script>
@endpush
