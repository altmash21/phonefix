@extends('mobileshop.layout')

@section('title', 'New Mobiles Stock — Maurya Mobile')
@section('page-title', 'Brand New Mobiles Inventory')

@section('page-actions')
    <div style="display:flex; gap:10px; align-items:center;">
        @if(auth()->user()->can('create-mobileshop-pos') || auth()->user()->can('read-mobileshop-new') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin') || auth()->user()->hasRole('sales-staff'))
        <a href="{{ route('mobileshop.purchase.create') }}" class="btn btn-outline btn-sm">
            <i data-lucide="plus" style="width:14px;height:14px;"></i> Purchase New Phone
        </a>
        @endif
        <a href="{{ route('mobileshop.pos') }}" class="btn btn-primary btn-sm">
            <i data-lucide="shopping-cart" style="width:14px;height:14px;"></i> Sell New Phone
        </a>
    </div>
@endsection

@section('content')

@php
    $mobilesCollection = collect($mobiles ?? []);
    $inStockCount = $mobilesCollection->where('status', 'in_stock')->count();
    $soldCount = $mobilesCollection->where('status', '!=', 'in_stock')->count();
    $totalStockValuation = $mobilesCollection->where('status', 'in_stock')->sum('selling_price');
    $uniqueBrands = $mobilesCollection->pluck('brand')->filter()->unique()->values();
@endphp

<!-- Mobile Horizontal Stat Strip -->
<div class="mobile-stat-strip">
    <div class="stat-strip-item" onclick="applyMobileFilter('in_stock')">
        <span class="stat-label">In Stock</span>
        <span class="stat-val" style="color:#16A34A;">{{ $inStockCount }} Units</span>
    </div>
    <div class="stat-divider"></div>
    <div class="stat-strip-item" onclick="applyMobileFilter('sold')">
        <span class="stat-label">Sold Out</span>
        <span class="stat-val" style="color:#DC2626;">{{ $soldCount }}</span>
    </div>
    <div class="stat-divider"></div>
    <div class="stat-strip-item" onclick="applyMobileFilter('all')">
        <span class="stat-label">Holding Value</span>
        <span class="stat-val" style="color:#2563EB;">₹{{ number_format($totalStockValuation, 0) }}</span>
    </div>
</div>

<div class="card">
    <!-- Header & Search Controls -->
    <div class="card-header" style="border-bottom:1px solid var(--card-border); padding:16px 20px;">
        <div style="flex:1;">
            <div class="card-title">Brand New Inventory (IMEI Tracked)</div>
            <div class="card-subtitle">
                Total Units In Stock: <strong style="color:var(--brand-700);">{{ $inStockCount }}</strong> • Holding Value: <strong style="color:#16A34A;">₹{{ number_format($totalStockValuation, 2) }}</strong>
            </div>
        </div>
        <div style="display:flex; align-items:center; gap:10px; width:100%; max-width:340px;">
            <div class="search-bar" style="width:100%;">
                <i data-lucide="search" style="width:15px;height:15px;"></i>
                <input type="text" id="searchInput" placeholder="Search brand, model, IMEI..." oninput="onMobileSearch(this.value)">
            </div>
        </div>
    </div>

    <!-- Filter Bar Rail -->
    <div class="pills-scroll-rail" style="padding:10px 16px; border-bottom:1px solid var(--card-border); display:flex; align-items:center; gap:8px; overflow-x:auto;">
        <button type="button" class="filter-pill-btn active" id="pill-mobile-all" onclick="applyMobileFilter('all', this)">
            <i data-lucide="smartphone" style="width:13px;height:13px;"></i> All Units ({{ $mobilesCollection->count() }})
        </button>
        <button type="button" class="filter-pill-btn" id="pill-mobile-stock" onclick="applyMobileFilter('in_stock', this)">
            <i data-lucide="check-circle" style="width:13px;height:13px;"></i> In Stock ({{ $inStockCount }})
        </button>
        <button type="button" class="filter-pill-btn" id="pill-mobile-sold" onclick="applyMobileFilter('sold', this)">
            <i data-lucide="x-circle" style="width:13px;height:13px;"></i> Sold ({{ $soldCount }})
        </button>

        <div style="margin-left:auto; display:flex; align-items:center; gap:6px; flex-shrink:0;">
            <span style="font-size:11px; font-weight:700; color:var(--text-secondary);">Brand:</span>
            <select id="mobileBrandSelect" class="form-control" style="padding:4px 8px; font-size:12px; font-weight:700; width:auto; border-radius:8px;" onchange="applyMobileBrandFilter(this.value)">
                <option value="all">-- All Brands --</option>
                @foreach($uniqueBrands as $b)
                    <option value="{{ strtolower($b) }}">{{ $b }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Desktop Data Table View -->
    <div class="data-table-wrap" id="newMobilesDesktopTableWrap">
        <table class="data-table" id="inventoryTable">
            <thead>
                <tr>
                    <th style="width:50px; text-align:center;">Photo</th>
                    <th>Brand & Model</th>
                    <th>IMEI / Serial</th>
                    <th>RAM & Storage</th>
                    <th>Color</th>
                    <th style="text-align:right;">Purchase Cost</th>
                    <th style="text-align:right;">Selling Price</th>
                    <th style="text-align:center;">Status</th>
                    <th style="text-align:center;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mobiles ?? [] as $i => $mobile)
                @php
                    $photo = $mobile->photo_path ?? $mobile->box_photo_path ?? null;
                @endphp
                <tr class="mobile-row" data-status="{{ $mobile->status ?? 'in_stock' }}" data-brand="{{ strtolower($mobile->brand ?? '') }}" data-search="{{ strtolower(($mobile->brand ?? '') . ' ' . ($mobile->model ?? '') . ' ' . ($mobile->imei_1 ?? '')) }}">
                    <td style="text-align:center;">
                        @if($photo)
                        <img src="{{ asset($photo) }}" alt="{{ $mobile->model ?? 'Phone' }}" style="width:36px; height:36px; object-fit:cover; border-radius:8px; border:1px solid #E2E8F0; cursor:pointer;" onclick="previewImage('{{ asset($photo) }}', '{{ $mobile->brand ?? '' }} {{ $mobile->model ?? '' }}')">
                        @else
                        <div style="width:36px; height:36px; border-radius:8px; background:#F1F5F9; display:inline-flex; align-items:center; justify-content:center; color:#94A3B8;">
                            <i data-lucide="smartphone" style="width:18px;height:18px;"></i>
                        </div>
                        @endif
                    </td>
                    <td>
                        <div style="font-weight:800;color:#0F172A;font-size:13px;">{{ $mobile->brand ?? '' }} {{ $mobile->model ?? $mobile->name ?? '—' }}</div>
                    </td>
                    <td style="font-family:monospace;font-size:12px;color:var(--brand-700);font-weight:700;">{{ $mobile->imei_1 ?? $mobile->sku ?? '—' }}</td>
                    <td style="color:var(--text-secondary);font-size:12px;">{{ $mobile->ram_rom ?? $mobile->storage ?? $mobile->description ?? '—' }}</td>
                    <td><span class="badge badge-gray" style="font-size:11px;">{{ $mobile->color ?? 'Standard' }}</span></td>
                    <td style="color:var(--text-secondary); text-align:right; font-size:12px;">₹{{ number_format($mobile->purchase_cost ?? 0, 2) }}</td>
                    <td style="font-weight:800; color:#0F172A; text-align:right; font-size:13px;">₹{{ number_format($mobile->selling_price ?? $mobile->sale_price ?? 0, 2) }}</td>
                    <td style="text-align:center;">
                        <span class="badge {{ ($mobile->status ?? 'in_stock') === 'in_stock' ? 'badge-green' : 'badge-red' }}">
                            {{ strtoupper(str_replace('_', ' ', $mobile->status ?? 'in_stock')) }}
                        </span>
                    </td>
                    <td style="text-align:center;">
                        @if(($mobile->status ?? 'in_stock') === 'in_stock')
                        <a href="{{ route('mobileshop.pos') }}" class="btn-icon" title="Sell in POS" style="background:#EFF6FF; color:#2563EB;">
                            <i data-lucide="shopping-cart"></i>
                        </a>
                        @else
                        <span style="font-size:11px; color:#94A3B8; font-weight:700;">SOLD</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align:center; padding:56px 20px;">
                        <div style="max-width:380px; margin:0 auto; display:flex; flex-direction:column; align-items:center; gap:12px;">
                            <div style="width:56px; height:56px; border-radius:50%; background:var(--lama-purple-light); display:flex; align-items:center; justify-content:center; color:var(--brand-700);">
                                <i data-lucide="smartphone" style="width:28px;height:28px;"></i>
                            </div>
                            <div style="font-weight:800; font-size:16px; color:#0F172A;">No Brand New Phones in Stock</div>
                            <div style="font-size:13px; color:var(--text-secondary); line-height:1.5;">Add your first boxed smartphone with photo and IMEI serial numbers to start selling on the POS terminal.</div>
                            <a href="{{ route('mobileshop.purchase.create') }}" class="btn btn-primary" style="margin-top:6px;">
                                <i data-lucide="plus" style="width:15px;height:15px;"></i> Purchase New Phone
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Zero-Depth Flat Cards List -->
    <div id="newMobilesMobileCards" style="display:none; padding:10px 12px; flex-direction:column; gap:8px;">
        @forelse($mobiles ?? [] as $mobile)
        @php
            $photo = $mobile->photo_path ?? $mobile->box_photo_path ?? null;
        @endphp
        <div class="app-flat-row new-mobile-card" data-status="{{ $mobile->status ?? 'in_stock' }}" data-brand="{{ strtolower($mobile->brand ?? '') }}" data-search="{{ strtolower(($mobile->brand ?? '') . ' ' . ($mobile->model ?? '') . ' ' . ($mobile->imei_1 ?? '')) }}">
            <div style="display:flex; align-items:center; gap:10px; width:100%;">
                @if($photo)
                <img src="{{ asset($photo) }}" alt="{{ $mobile->model ?? 'Phone' }}" style="width:44px; height:44px; object-fit:cover; border-radius:10px; border:1px solid #E2E8F0; flex-shrink:0; cursor:pointer;" onclick="previewImage('{{ asset($photo) }}', '{{ $mobile->brand ?? '' }} {{ $mobile->model ?? '' }}')">
                @else
                <div style="width:44px; height:44px; border-radius:10px; background:#F1F5F9; display:flex; align-items:center; justify-content:center; color:#94A3B8; flex-shrink:0;">
                    <i data-lucide="smartphone" style="width:22px;height:22px;"></i>
                </div>
                @endif
                <div style="flex:1; min-width:0;">
                    <div style="font-weight:800; font-size:13px; color:#0F172A; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                        {{ $mobile->brand ?? '' }} {{ $mobile->model ?? $mobile->name ?? '—' }}
                    </div>
                    <div style="font-size:11px; color:#64748B; margin-top:2px;">
                        IMEI: <span style="font-family:monospace; font-weight:700; color:var(--brand-700);">{{ $mobile->imei_1 ?? '—' }}</span>
                        @if($mobile->storage) • {{ $mobile->storage }} @endif
                    </div>
                </div>
                <div style="text-align:right; flex-shrink:0;">
                    <div style="font-weight:900; font-size:14px; color:#0F172A;">₹{{ number_format($mobile->selling_price ?? 0, 0) }}</div>
                    <span class="badge {{ ($mobile->status ?? 'in_stock') === 'in_stock' ? 'badge-green' : 'badge-red' }}" style="font-size:10px; padding:1px 6px;">
                        {{ strtoupper(str_replace('_', ' ', $mobile->status ?? 'in_stock')) }}
                    </span>
                </div>
            </div>
            @if(($mobile->status ?? 'in_stock') === 'in_stock')
            <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:6px; padding-top:6px; border-top:1px solid #F1F5F9; width:100%;">
                <a href="{{ route('mobileshop.pos') }}" class="btn btn-primary btn-sm" style="font-size:11px; padding:4px 10px; height:auto;">
                    <i data-lucide="shopping-cart" style="width:12px;height:12px;"></i> Sell in POS
                </a>
            </div>
            @endif
        </div>
        @empty
        <div style="text-align:center; padding:30px 16px; color:#94A3B8; font-size:12px;">No new phones found.</div>
        @endforelse
    </div>
    <div id="newMobilesPagination"></div>
</div>

<!-- Mobile Floating Action Button -->
<div class="mobile-fab-container">
    <a href="{{ route('mobileshop.purchase.create') }}" class="btn-app-fab" title="Purchase New Phone" style="text-decoration:none;">
        <i data-lucide="plus" style="width:20px;height:20px;"></i>
        <span>Purchase New Phone</span>
    </a>
</div>


<!-- Lightbox Modal for Photo Preview -->
<div id="imageLightboxModal" style="display:none; position:fixed; inset:0; z-index:300; background:rgba(15,23,42,0.85); backdrop-filter:blur(4px); align-items:center; justify-content:center; padding:16px;" onclick="closeImageLightbox()">
    <div style="max-width:90vw; max-height:85vh; position:relative; text-align:center;" onclick="event.stopPropagation()">
        <button onclick="closeImageLightbox()" style="position:absolute; top:-12px; right:-12px; background:#fff; border:none; border-radius:50%; width:32px; height:32px; font-weight:900; cursor:pointer; box-shadow:0 4px 10px rgba(0,0,0,0.3);">✕</button>
        <img id="lightboxImg" src="" alt="Device Photo" style="max-width:100%; max-height:75vh; border-radius:12px; box-shadow:0 20px 25px -5px rgba(0,0,0,0.5);">
        <div id="lightboxCaption" style="color:#fff; font-weight:800; font-size:14px; margin-top:10px;"></div>
    </div>
</div>

@endsection

@push('scripts')
<style>
@media (max-width: 768px) {
    #newMobilesDesktopTableWrap { display: none !important; }
    #newMobilesMobileCards { display: flex !important; }
}
</style>
<script>
let currentMobileStatus = 'all';
let currentMobileBrand = 'all';
let currentMobileSearch = '';

function applyMobileFilter(status, btnElement) {
    currentMobileStatus = status;

    document.querySelectorAll('.filter-pill-btn').forEach(p => p.classList.remove('active'));
    if (btnElement) {
        btnElement.classList.add('active');
    } else {
        const map = { 'all': 'pill-mobile-all', 'in_stock': 'pill-mobile-stock', 'sold': 'pill-mobile-sold' };
        if (map[status]) document.getElementById(map[status])?.classList.add('active');
    }

    renderFilteredMobiles();
}

function applyMobileBrandFilter(brand) {
    currentMobileBrand = brand.toLowerCase();
    renderFilteredMobiles();
}

function onMobileSearch(val) {
    currentMobileSearch = val.toLowerCase().trim();
    renderFilteredMobiles();
}

function renderFilteredMobiles() {
    // Filter Desktop Rows
    document.querySelectorAll('#inventoryTable tbody tr.mobile-row').forEach(row => {
        const status = row.dataset.status || 'in_stock';
        const brand = row.dataset.brand || '';
        const searchTarget = (row.dataset.search || row.textContent).toLowerCase();

        let matchStatus = (currentMobileStatus === 'all') || (currentMobileStatus === 'in_stock' && status === 'in_stock') || (currentMobileStatus === 'sold' && status !== 'in_stock');
        let matchBrand = (currentMobileBrand === 'all') || brand.includes(currentMobileBrand);
        let matchSearch = !currentMobileSearch || searchTarget.includes(currentMobileSearch);

        row.dataset.mobiHidden = (matchStatus && matchBrand && matchSearch) ? '0' : '1';
    });

    // Filter Mobile Cards
    document.querySelectorAll('#newMobilesMobileCards .new-mobile-card').forEach(card => {
        const status = card.dataset.status || 'in_stock';
        const brand = card.dataset.brand || '';
        const searchTarget = (card.dataset.search || card.textContent).toLowerCase();

        let matchStatus = (currentMobileStatus === 'all') || (currentMobileStatus === 'in_stock' && status === 'in_stock') || (currentMobileStatus === 'sold' && status !== 'in_stock');
        let matchBrand = (currentMobileBrand === 'all') || brand.includes(currentMobileBrand);
        let matchSearch = !currentMobileSearch || searchTarget.includes(currentMobileSearch);

        card.dataset.mobiHidden = (matchStatus && matchBrand && matchSearch) ? '0' : '1';
    });

    if (window.newMobilesPager) {
        window.newMobilesPager.refresh();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    if (window.setupMobiTablePagination) {
        window.newMobilesPager = window.setupMobiTablePagination({
            tableId: 'inventoryTable',
            cardsContainerId: 'newMobilesMobileCards',
            paginationContainerId: 'newMobilesPagination',
            rowSelector: 'tbody tr.mobile-row',
            cardSelector: '.new-mobile-card',
            pageSize: 25,
            itemName: 'phones'
        });
    }
    renderFilteredMobiles();
    if (window.lucide) window.lucide.createIcons();
});

function previewSelectedPhoto(input, imgId, boxId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.getElementById(imgId);
            const box = document.getElementById(boxId);
            if (img && box) {
                img.src = e.target.result;
                box.style.display = 'block';
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function previewImage(url, caption) {
    const modal = document.getElementById('imageLightboxModal');
    const img = document.getElementById('lightboxImg');
    const cap = document.getElementById('lightboxCaption');
    if (modal && img) {
        img.src = url;
        if (cap) cap.textContent = caption || '';
        modal.style.display = 'flex';
    }
}

function closeImageLightbox() {
    const modal = document.getElementById('imageLightboxModal');
    if (modal) modal.style.display = 'none';
}


</script>
@endpush
