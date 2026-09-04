@extends('mobileshop.layout')

@section('title', 'Masters & Store Configuration — MobiTrack ERP')
@section('page-title', 'Store Masters & Master Configuration')

@section('page-actions')
    <a href="{{ route('mobileshop.dashboard') }}" class="btn btn-outline btn-sm">
        <i data-lucide="arrow-left" style="width:14px;height:14px;"></i> Back to Dashboard
    </a>
@endsection

@section('content')

    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap:24px;">

        <!-- 1. Categories Master -->
        <div class="card" style="margin:0;">
            <div class="card-header" style="background:#F8FAFC; border-bottom:1px solid #E2E8F0;">
                <div>
                    <div class="card-title">Parts & Accessories Categories ({{ count($categories) }})</div>
                    <div class="card-subtitle">Display, Front Glass, Pin, IC, Covers, Batteries</div>
                </div>
                <a href="{{ route('mobileshop.stock') }}" class="btn btn-primary btn-sm">View in Stock</a>
            </div>
            <div class="card-body" style="padding:14px;">
                <div id="categoriesList" style="display:flex; flex-direction:column; gap:8px;">
                    @forelse($categories as $cat)
                    <div class="cat-item" style="display:flex; justify-content:space-between; align-items:center; padding:8px 12px; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px;">
                        <div>
                            <div style="font-weight:700; color:#0F172A; font-size:13px;">{{ $cat->name }}</div>
                            <div style="font-size:11px; color:#64748B;">Slug: <code>{{ $cat->slug }}</code></div>
                        </div>
                        @if($cat->is_gift_eligible)
                            <span class="badge badge-green" style="font-size:10px;">🎁 Free Gift Eligible</span>
                        @endif
                    </div>
                    @empty
                    <div style="text-align:center; padding:16px; color:#94A3B8;">No categories found.</div>
                    @endforelse
                </div>
                <div id="categoriesPagination"></div>
            </div>
        </div>

        <!-- 2. EMI Financiers Master -->
        <div class="card" style="margin:0;">
            <div class="card-header" style="background:#F8FAFC; border-bottom:1px solid #E2E8F0;">
                <div>
                    <div class="card-title">Financiers & EMI Providers ({{ count($financiers) }})</div>
                    <div class="card-subtitle">Bajaj Finserv, TVS Credit, HDB, Home Credit</div>
                </div>
            </div>
            <div class="card-body" style="padding:14px;">
                <div id="financiersList" style="display:flex; flex-direction:column; gap:8px;">
                    @forelse($financiers as $emi)
                    <div class="emi-item" style="display:flex; justify-content:space-between; align-items:center; padding:8px 12px; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px;">
                        <div>
                            <div style="font-weight:700; color:#0F172A; font-size:13px;">{{ $emi->name }}</div>
                            <div style="font-size:11px; color:#64748B;">Code: {{ $emi->code }}</div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-weight:800; font-size:12px; color:var(--brand-700);">Payout Balance: ₹{{ number_format($emi->advance_balance, 2) }}</div>
                        </div>
                    </div>
                    @empty
                    <div style="text-align:center; padding:16px; color:#94A3B8;">No EMI providers registered.</div>
                    @endforelse
                </div>
                <div id="financiersPagination"></div>
            </div>
        </div>

        <!-- 3. Suppliers & Distributers Master -->
        <div class="card" style="margin:0;">
            <div class="card-header" style="background:#F8FAFC; border-bottom:1px solid #E2E8F0;">
                <div>
                    <div class="card-title">Suppliers & Credit Wallets ({{ count($suppliers) }})</div>
                    <div class="card-subtitle">Wholesale vendors & distributors</div>
                </div>
                <a href="{{ route('mobileshop.purchase_orders') }}" class="btn btn-outline btn-sm">POs</a>
            </div>
            <div class="card-body" style="padding:14px;">
                <div id="suppliersList" style="display:flex; flex-direction:column; gap:8px;">
                    @forelse($suppliers as $sup)
                    <div class="sup-item" style="display:flex; justify-content:space-between; align-items:center; padding:8px 12px; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px;">
                        <div>
                            <div style="font-weight:700; color:#0F172A; font-size:13px;">{{ $sup->supplier_name }}</div>
                            <div style="font-size:11px; color:#64748B;">Advance Wallet: ₹{{ number_format($sup->credit_balance, 2) }}</div>
                        </div>
                    </div>
                    @empty
                    <div style="text-align:center; padding:16px; color:#94A3B8;">No suppliers found.</div>
                    @endforelse
                </div>
                <div id="suppliersPagination"></div>
            </div>
        </div>

        <!-- 4. Staff Accounts & RBAC Roles -->
        <div class="card" style="margin:0;">
            <div class="card-header" style="background:#F8FAFC; border-bottom:1px solid #E2E8F0;">
                <div>
                    <div class="card-title">Staff Counter Users & Roles ({{ count($staffUsers) }})</div>
                    <div class="card-subtitle">Isolated counter terminals</div>
                </div>
            </div>
            <div class="card-body" style="padding:14px;">
                <div id="staffUsersList" style="display:flex; flex-direction:column; gap:8px;">
                    @forelse($staffUsers as $usr)
                    <div class="usr-item" style="display:flex; justify-content:space-between; align-items:center; padding:8px 12px; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div style="width:28px; height:28px; border-radius:50%; background:var(--brand-100); color:var(--brand-700); font-weight:800; font-size:11px; display:flex; align-items:center; justify-content:center;">
                                {{ strtoupper(substr($usr->name, 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-weight:700; color:#0F172A; font-size:12px;">{{ $usr->name }}</div>
                                <div style="font-size:10px; color:#64748B;">{{ $usr->email }}</div>
                            </div>
                        </div>
                        <span class="badge badge-purple" style="font-size:10px;">
                            {{ $usr->roles->first()?->name ?? 'Staff' }}
                        </span>
                    </div>
                    @empty
                    <div style="text-align:center; padding:16px; color:#94A3B8;">No users found.</div>
                    @endforelse
                </div>
                <div id="staffUsersPagination"></div>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.setupMobiTablePagination) {
            window.setupMobiTablePagination({
                cardsContainerId: 'categoriesList',
                cardSelector: '.cat-item',
                paginationContainerId: 'categoriesPagination',
                pageSize: 10,
                itemName: 'categories'
            });
            window.setupMobiTablePagination({
                cardsContainerId: 'financiersList',
                cardSelector: '.emi-item',
                paginationContainerId: 'financiersPagination',
                pageSize: 10,
                itemName: 'providers'
            });
            window.setupMobiTablePagination({
                cardsContainerId: 'suppliersList',
                cardSelector: '.sup-item',
                paginationContainerId: 'suppliersPagination',
                pageSize: 10,
                itemName: 'suppliers'
            });
            window.setupMobiTablePagination({
                cardsContainerId: 'staffUsersList',
                cardSelector: '.usr-item',
                paginationContainerId: 'staffUsersPagination',
                pageSize: 10,
                itemName: 'staff users'
            });
        }
        if (window.lucide) window.lucide.createIcons();
    });
</script>
@endpush
