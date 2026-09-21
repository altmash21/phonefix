@extends('mobileshop.layout')

@section('title', 'Masters & Store Configuration — Maurya Mobile ERP')
@section('page-title', 'Store Masters & Master Configuration')

@section('page-actions')
    <a href="{{ route('mobileshop.dashboard') }}" class="btn btn-outline btn-sm">
        <i data-lucide="arrow-left" style="width:14px;height:14px;"></i> Back to Dashboard
    </a>
@endsection

@section('content')

    @if(session('success'))
    <div style="background:#ECFDF5; border:1px solid #A7F3D0; color:#065F46; padding:12px 16px; border-radius:8px; margin-bottom:20px; font-weight:600; font-size:13px; display:flex; align-items:center; gap:8px;">
        <i data-lucide="check-circle-2" style="width:18px;height:18px;color:#10B981;"></i>
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div style="background:#FEF2F2; border:1px solid #FECACA; color:#991B1B; padding:12px 16px; border-radius:8px; margin-bottom:20px; font-weight:600; font-size:13px; display:flex; align-items:center; gap:8px;">
        <i data-lucide="alert-circle" style="width:18px;height:18px;color:#EF4444;"></i>
        {{ session('error') }}
    </div>
    @endif

    <!-- Category Quick-Nav Toolbar & Controls -->
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:20px; background:#fff; padding:12px 18px; border-radius:12px; border:1px solid #E2E8F0; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
        <!-- Quick Jump Pills -->
        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
            <span style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase; letter-spacing:0.5px; margin-right:4px;">Jump to:</span>
            <button type="button" onclick="focusMasterCategory('sec-staff')" class="btn btn-outline btn-xs" style="border-radius:20px; font-weight:600; font-size:11.5px; padding:4px 10px; display:inline-flex; align-items:center; gap:5px;">
                <i data-lucide="users" style="width:13px;height:13px; color:#0F766E;"></i> Staff &amp; Access ({{ count($staffUsers) }})
            </button>
            <button type="button" onclick="focusMasterCategory('sec-categories')" class="btn btn-outline btn-xs" style="border-radius:20px; font-weight:600; font-size:11.5px; padding:4px 10px; display:inline-flex; align-items:center; gap:5px;">
                <i data-lucide="package" style="width:13px;height:13px; color:#D97706;"></i> Categories ({{ count($categories) }})
            </button>
            <button type="button" onclick="focusMasterCategory('sec-financiers')" class="btn btn-outline btn-xs" style="border-radius:20px; font-weight:600; font-size:11.5px; padding:4px 10px; display:inline-flex; align-items:center; gap:5px;">
                <i data-lucide="landmark" style="width:13px;height:13px; color:#2563EB;"></i> EMI Providers ({{ count($financiers) }})
            </button>
            <button type="button" onclick="focusMasterCategory('sec-suppliers')" class="btn btn-outline btn-xs" style="border-radius:20px; font-weight:600; font-size:11.5px; padding:4px 10px; display:inline-flex; align-items:center; gap:5px;">
                <i data-lucide="truck" style="width:13px;height:13px; color:#7C3AED;"></i> Suppliers ({{ count($suppliers) }})
            </button>
            @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin'))
            <button type="button" onclick="focusMasterCategory('sec-security')" class="btn btn-outline btn-xs" style="border-radius:20px; font-weight:600; font-size:11.5px; padding:4px 10px; display:inline-flex; align-items:center; gap:5px;">
                <i data-lucide="shield-check" style="width:13px;height:13px; color:#DC2626;"></i> Sessions ({{ count($loginSessions) }})
            </button>
            @endif
        </div>

        <!-- Master Expand / Collapse All & Quick Issue Action -->
        <div style="display:flex; align-items:center; gap:10px;">
            <button type="button" id="toggleAllAccordionsBtn" onclick="toggleAllAccordions()" data-expanded="1" class="btn btn-outline btn-xs" style="font-size:11.5px; font-weight:600; display:inline-flex; align-items:center; gap:5px;">
                <i data-lucide="chevrons-up" id="toggleAllIcon" style="width:13px;height:13px;"></i>
                <span id="toggleAllText">Collapse All</span>
            </button>
            <button type="button" onclick="openGenerateInviteModal()" class="btn btn-primary btn-xs" style="background:#0F766E; border-color:#0F766E; font-size:11.5px; font-weight:700; display:inline-flex; align-items:center; gap:5px;">
                <i data-lucide="ticket" style="width:13px;height:13px;"></i> Issue Invite Token
            </button>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════════
         CATEGORY 1: STAFF ACCOUNTS, COUNTER ROLES & INVITE TOKENS (COLLAPSIBLE)
         ══════════════════════════════════════════════════════════════════════ -->
    <div class="card master-category-card" id="sec-staff" style="margin:0 0 20px 0; border:1px solid #E2E8F0; border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
        <!-- Collapsible Header -->
        <div class="card-header master-category-header" onclick="toggleMasterAccordion('sec-staff')" style="background:#F8FAFC; border-bottom:1px solid #E2E8F0; padding:14px 20px; cursor:pointer; display:flex; justify-content:space-between; align-items:center; user-select:none;">
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="width:36px; height:36px; border-radius:10px; background:#F0FDFA; color:#0F766E; display:flex; align-items:center; justify-content:center; border:1px solid #CCFBF1;">
                    <i data-lucide="users" style="width:18px;height:18px;"></i>
                </div>
                <div>
                    <div style="font-weight:800; color:#0F172A; font-size:14px; display:flex; align-items:center; gap:8px;">
                        Staff Accounts, Terminal Roles &amp; Invite Access
                        <span class="badge badge-purple" style="font-size:10.5px; padding:2px 8px;">{{ count($staffUsers) }} accounts</span>
                        <span class="badge badge-blue" style="font-size:10.5px; padding:2px 8px;">{{ count($activeInvites) }} tokens</span>
                    </div>
                    <div style="font-size:11.5px; color:#64748B; margin-top:2px;">
                        Pre-assign designated counter roles, issue 8-character invite codes for self-registration, and manage credentials.
                    </div>
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:10px;">
                <button type="button" class="btn btn-primary btn-xs" onclick="event.stopPropagation(); openGenerateInviteModal();" style="background:#0F766E; border-color:#0F766E; display:inline-flex; align-items:center; gap:5px; font-weight:700;">
                    <i data-lucide="user-plus" style="width:13px;height:13px;"></i> Generate Invite Token
                </button>
                <div style="width:28px; height:28px; border-radius:6px; background:#E2E8F0; display:flex; align-items:center; justify-content:center; color:#475569;">
                    <i data-lucide="chevron-up" id="sec-staff-chevron" style="width:16px;height:16px;"></i>
                </div>
            </div>
        </div>

        <!-- Collapsible Body -->
        <div id="sec-staff-body" style="display:block; padding:18px 20px; background:#fff;">
            
            <!-- Workflow Explainer Guide Banner -->
            <div style="background:#F0FDFA; border:1px solid #99F6E4; border-radius:10px; padding:12px 16px; margin-bottom:18px; display:flex; align-items:flex-start; gap:12px;">
                <div style="width:28px; height:28px; border-radius:8px; background:#14B8A6; color:#fff; display:flex; align-items:center; justify-content:center; shrink-0; margin-top:1px;">
                    <i data-lucide="lightbulb" style="width:15px;height:15px;"></i>
                </div>
                <div style="flex:1;">
                    <div style="font-weight:700; color:#0F766E; font-size:12.5px;">How Employee Self-Registration Works:</div>
                    <div style="font-size:11.5px; color:#115E59; margin-top:4px; line-height:1.5;">
                        <strong>1. Issue Code:</strong> Store Admin chooses the employee's designation/station and generates an 8-character token (e.g. <code>EMP-A1B2C3</code>).<br>
                        <strong>2. Give Code to User:</strong> Hand or send the code to your employee.<br>
                        <strong>3. Register on Login Page:</strong> Employee opens the <strong>Login Page</strong>, clicks <strong>"Register with Code"</strong>, enters the token, and sets their own work email and password.<br>
                        <strong>4. Instant Access:</strong> The account is immediately created, assigned to the selected designation, and logged into their terminal.
                    </div>
                </div>
            </div>

            <!-- Two Column Layout: Staff List & Invite Tokens List -->
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap:24px;">
                
                <!-- Left: Staff Users List -->
                <div>
                    <div style="font-size:12px; font-weight:700; color:#334155; margin-bottom:10px; display:flex; align-items:center; justify-content:space-between;">
                        <span>Active Counter Terminals &amp; Roles</span>
                        <span class="badge badge-gray" style="font-size:10px;">{{ count($staffUsers) }} accounts</span>
                    </div>
                    <div id="staffUsersList" style="display:flex; flex-direction:column; gap:8px;">
                        @forelse($staffUsers as $usr)
                        @php
                            $isSuperAdmin = $usr->hasRole('admin');
                            $userRoleName = $usr->roles->first()?->name ?? 'Staff';
                            $userRoleDisplay = $usr->roles->first()?->display_name ?? $userRoleName;
                            $isActive = (bool) ($usr->enabled ?? true);
                        @endphp
                        <div class="usr-item" style="display:flex; justify-content:space-between; align-items:center; padding:10px 12px; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; opacity: {{ $isActive ? '1' : '0.65' }};">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <div style="width:34px; height:34px; border-radius:50%; background:var(--brand-100); color:var(--brand-700); font-weight:800; font-size:13px; display:flex; align-items:center; justify-content:center;">
                                    {{ strtoupper(substr($usr->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div style="display:flex; align-items:center; gap:6px;">
                                        <span style="font-weight:700; color:#0F172A; font-size:13px;">{{ $usr->name }}</span>
                                        @if(!$isActive)
                                            <span class="badge badge-red" style="font-size:9px; padding:1px 5px;">Deactivated</span>
                                        @endif
                                    </div>
                                    <div style="font-size:11px; color:#64748B;">{{ $usr->email }}</div>
                                </div>
                            </div>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <span class="badge {{ $isSuperAdmin ? 'badge-gray' : 'badge-purple' }}" style="font-size:10px;">
                                    {{ $isSuperAdmin ? 'Super Admin' : $userRoleDisplay }}
                                </span>
                                @if(!$isSuperAdmin || auth()->user()->hasRole('admin'))
                                <button type="button" class="btn btn-outline btn-xs" style="padding:4px 8px; font-size:11px;"
                                    title="Edit Credentials"
                                    onclick="openEditUserCredentialsModal({{ $usr->id }}, '{{ addslashes($usr->name) }}', '{{ addslashes($usr->email) }}', '{{ $userRoleName }}')">
                                    <i data-lucide="key" style="width:12px;height:12px;"></i>
                                </button>
                                @if(!$isSuperAdmin && $usr->id !== auth()->id())
                                <button type="button" class="btn btn-xs {{ $isActive ? 'btn-outline-danger' : 'btn-outline-success' }}" style="padding:4px 8px; font-size:11px;"
                                    title="{{ $isActive ? 'Deactivate Staff Account' : 'Activate Staff Account' }}"
                                    onclick="toggleStaffStatus({{ $usr->id }}, this)">
                                    <i data-lucide="{{ $isActive ? 'user-x' : 'user-check' }}" style="width:12px;height:12px;"></i>
                                </button>
                                @endif
                                @endif
                            </div>
                        </div>
                        @empty
                        <div style="text-align:center; padding:16px; color:#94A3B8;">No users found.</div>
                        @endforelse
                    </div>
                    <div id="staffUsersPagination"></div>
                </div>

                <!-- Right: Employee Invite Tokens -->
                <div>
                    <div style="font-size:12px; font-weight:700; color:#334155; margin-bottom:10px; display:flex; align-items:center; justify-content:space-between;">
                        <span>Active &amp; Pending Invite Tokens</span>
                        <span class="badge badge-blue" style="font-size:10px;">{{ count($activeInvites) }} tokens</span>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:8px; max-height:360px; overflow-y:auto;">
                        @forelse($activeInvites as $inv)
                        <div class="inv-item" id="invite-row-{{ $inv->id }}" style="display:flex; justify-content:space-between; align-items:center; padding:10px 12px; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px;">
                            <div>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <code style="font-family:monospace; font-size:12px; font-weight:700; color:var(--brand-700); background:var(--brand-50); padding:2px 6px; border-radius:4px; border:1px solid var(--brand-200);">{{ $inv->token }}</code>
                                    @if($inv->status === 'active' || $inv->status === 'pending')
                                        <span class="badge badge-green" style="font-size:9.5px; padding:1px 6px;">Active &bull; {{ $inv->expires_diff }}</span>
                                    @elseif($inv->status === 'used' || $inv->status === 'claimed')
                                        <span class="badge badge-gray" style="font-size:9.5px; padding:1px 6px;">Claimed</span>
                                    @else
                                        <span class="badge badge-red" style="font-size:9.5px; padding:1px 6px;">Revoked</span>
                                    @endif
                                </div>
                                <div style="font-size:11px; color:#64748B; margin-top:3px;">
                                    Role: <strong style="color:#0F172A;">{{ $inv->role_label }}</strong> 
                                    @if($inv->notes) &bull; <span style="font-style:italic;">"{{ $inv->notes }}"</span> @endif
                                </div>
                            </div>
                            <div style="display:flex; align-items:center; gap:6px;">
                                @if($inv->status === 'active' || $inv->status === 'pending')
                                <button type="button" class="btn btn-outline btn-xs" style="padding:4px 8px; font-size:11px;"
                                    title="Copy Invite Code Only"
                                    onclick="copyRawToken('{{ $inv->token }}', this)">
                                    <i data-lucide="key" style="width:12px;height:12px;"></i> Code
                                </button>
                                <button type="button" class="btn btn-outline btn-xs" style="padding:4px 8px; font-size:11px;"
                                    title="Copy Full Login Registration Link"
                                    onclick="copyInviteLink('{{ $inv->token }}', this)">
                                    <i data-lucide="link" style="width:12px;height:12px;"></i> Link
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-xs" style="padding:4px 8px; font-size:11px; color:#DC2626; border-color:#FECACA;"
                                    title="Revoke Token"
                                    onclick="revokeInviteToken({{ $inv->id }}, this)">
                                    <i data-lucide="x" style="width:12px;height:12px;"></i>
                                </button>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div style="text-align:center; padding:24px; color:#94A3B8; background:#F8FAFC; border:1px dashed #CBD5E1; border-radius:8px;">
                            <i data-lucide="key" style="width:24px;height:24px; margin:0 auto 6px; display:block; opacity:0.5;"></i>
                            No invite tokens generated yet.<br>Click "Generate Invite Token" above to issue a code.
                        </div>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════════
         CATEGORY 2: PARTS & ACCESSORIES CATEGORIES (COLLAPSIBLE)
         ══════════════════════════════════════════════════════════════════════ -->
    <div class="card master-category-card" id="sec-categories" style="margin:0 0 20px 0; border:1px solid #E2E8F0; border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
        <!-- Collapsible Header -->
        <div class="card-header master-category-header" onclick="toggleMasterAccordion('sec-categories')" style="background:#F8FAFC; border-bottom:1px solid #E2E8F0; padding:14px 20px; cursor:pointer; display:flex; justify-content:space-between; align-items:center; user-select:none;">
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="width:36px; height:36px; border-radius:10px; background:#FFFBEB; color:#D97706; display:flex; align-items:center; justify-content:center; border:1px solid #FEF3C7;">
                    <i data-lucide="package" style="width:18px;height:18px;"></i>
                </div>
                <div>
                    <div style="font-weight:800; color:#0F172A; font-size:14px; display:flex; align-items:center; gap:8px;">
                        Parts &amp; Accessories Categories
                        <span class="badge badge-amber" style="font-size:10.5px; padding:2px 8px; background:#FEF3C7; color:#B45309;">{{ count($categories) }} categories</span>
                    </div>
                    <div style="font-size:11.5px; color:#64748B; margin-top:2px;">
                        Inventory categorization for spare parts, back covers, tempered glasses, and free gift eligibility.
                    </div>
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:10px;">
                <a href="{{ route('mobileshop.stock') }}" onclick="event.stopPropagation();" class="btn btn-outline btn-xs" style="font-weight:600; font-size:11.5px;">
                    <i data-lucide="boxes" style="width:13px;height:13px;"></i> View in Stock
                </a>
                <div style="width:28px; height:28px; border-radius:6px; background:#E2E8F0; display:flex; align-items:center; justify-content:center; color:#475569;">
                    <i data-lucide="chevron-up" id="sec-categories-chevron" style="width:16px;height:16px;"></i>
                </div>
            </div>
        </div>

        <!-- Collapsible Body -->
        <div id="sec-categories-body" style="display:block; padding:18px 20px; background:#fff;">
            <div id="categoriesList" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:10px;">
                @forelse($categories as $cat)
                <div class="cat-item" style="display:flex; justify-content:space-between; align-items:center; padding:10px 14px; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px;">
                    <div>
                        <div style="font-weight:700; color:#0F172A; font-size:13px;">{{ $cat->name }}</div>
                        <div style="font-size:11px; color:#64748B;">Slug: <code>{{ $cat->slug }}</code></div>
                    </div>
                    @if($cat->is_gift_eligible)
                        <span class="badge badge-green" style="font-size:10px;">🎁 Free Gift Eligible</span>
                    @endif
                </div>
                @empty
                <div style="text-align:center; padding:16px; color:#94A3B8; grid-column:1 / -1;">No categories found.</div>
                @endforelse
            </div>
            <div id="categoriesPagination"></div>
        </div>
    </div>



    <!-- ══════════════════════════════════════════════════════════════════════
         CATEGORY 4: SUPPLIERS & PREPAID CREDIT WALLETS (COLLAPSIBLE)
         ══════════════════════════════════════════════════════════════════════ -->
    <div class="card master-category-card" id="sec-suppliers" style="margin:0 0 20px 0; border:1px solid #E2E8F0; border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
        <!-- Collapsible Header -->
        <div class="card-header master-category-header" onclick="toggleMasterAccordion('sec-suppliers')" style="background:#F8FAFC; border-bottom:1px solid #E2E8F0; padding:14px 20px; cursor:pointer; display:flex; justify-content:space-between; align-items:center; user-select:none;">
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="width:36px; height:36px; border-radius:10px; background:#F5F3FF; color:#7C3AED; display:flex; align-items:center; justify-content:center; border:1px solid #EDE9FE;">
                    <i data-lucide="truck" style="width:18px;height:18px;"></i>
                </div>
                <div>
                    <div style="font-weight:800; color:#0F172A; font-size:14px; display:flex; align-items:center; gap:8px;">
                        Suppliers &amp; Advance Credit Wallets
                        <span class="badge badge-purple" style="font-size:10.5px; padding:2px 8px;">{{ count($suppliers) }} suppliers</span>
                    </div>
                    <div style="font-size:11.5px; color:#64748B; margin-top:2px;">
                        Device wholesalers, spare parts vendors, GSTINs, and prepaid advance wallet balances.
                    </div>
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:10px;">
                <a href="{{ route('mobileshop.purchase') }}" onclick="event.stopPropagation();" class="btn btn-outline btn-xs" style="font-weight:600; font-size:11.5px;">
                    <i data-lucide="shopping-cart" style="width:13px;height:13px;"></i> POs &amp; Ledger
                </a>
                <div style="width:28px; height:28px; border-radius:6px; background:#E2E8F0; display:flex; align-items:center; justify-content:center; color:#475569;">
                    <i data-lucide="chevron-up" id="sec-suppliers-chevron" style="width:16px;height:16px;"></i>
                </div>
            </div>
        </div>

        <!-- Collapsible Body -->
        <div id="sec-suppliers-body" style="display:block; padding:18px 20px; background:#fff;">
            <div id="suppliersList" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:10px;">
                @forelse($suppliers as $sup)
                <div class="sup-item" style="display:flex; justify-content:space-between; align-items:center; padding:10px 14px; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px;">
                    <div>
                        <div style="font-weight:700; color:#0F172A; font-size:13px;">{{ $sup->supplier_name }}</div>
                        <div style="font-size:11px; color:#64748B;">
                            Phone: {{ $sup->supplier_phone ?: '—' }} | GSTIN: {{ $sup->supplier_gstin ?: '—' }}
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <div style="text-align:right;">
                            <div style="font-size:11px; font-weight:700; color:#16A34A;">Wallet: ₹{{ number_format($sup->credit_balance, 2) }}</div>
                        </div>
                        <button type="button" class="btn btn-outline btn-xs" style="padding:4px 8px; font-size:11px;"
                            onclick="openEditSupplierMasterModal({{ $sup->supplier_id }}, '{{ addslashes($sup->supplier_name) }}', '{{ addslashes($sup->supplier_phone ?? '') }}', '{{ addslashes($sup->supplier_gstin ?? '') }}', {{ (float) ($sup->credit_balance ?? 0) }})">
                            <i data-lucide="edit-3" style="width:12px;height:12px;"></i> Edit
                        </button>
                    </div>
                </div>
                @empty
                <div style="text-align:center; padding:16px; color:#94A3B8; grid-column:1 / -1;">No suppliers found.</div>
                @endforelse
            </div>
            <div id="suppliersPagination"></div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════════
         CATEGORY 5: ACTIVE DEVICE LOGIN SESSIONS & SECURITY (COLLAPSIBLE)
         ══════════════════════════════════════════════════════════════════════ -->
    @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin'))
    <div class="card master-category-card" id="sec-security" style="margin:0 0 20px 0; border:1px solid #E2E8F0; border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
        <!-- Collapsible Header -->
        <div class="card-header master-category-header" onclick="toggleMasterAccordion('sec-security')" style="background:#F8FAFC; border-bottom:1px solid #E2E8F0; padding:14px 20px; cursor:pointer; display:flex; justify-content:space-between; align-items:center; user-select:none;">
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="width:36px; height:36px; border-radius:10px; background:#FEF2F2; color:#DC2626; display:flex; align-items:center; justify-content:center; border:1px solid #FEE2E2;">
                    <i data-lucide="shield-check" style="width:18px;height:18px;"></i>
                </div>
                <div>
                    <div style="font-weight:800; color:#0F172A; font-size:14px; display:flex; align-items:center; gap:8px;">
                        Device Login Sessions &amp; Security History
                        <span class="badge badge-gray" style="font-size:10.5px; padding:2px 8px;">{{ count($loginSessions) }} total</span>
                        <span class="badge badge-purple" style="font-size:10px; font-weight:700;">Admin Protected</span>
                    </div>
                    <div style="font-size:11.5px; color:#64748B; margin-top:2px;">
                        Active hardware terminals, browser fingerprints, IP tracking, and instant remote session termination.
                    </div>
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:10px;">
                <div style="width:28px; height:28px; border-radius:6px; background:#E2E8F0; display:flex; align-items:center; justify-content:center; color:#475569;">
                    <i data-lucide="chevron-up" id="sec-security-chevron" style="width:16px;height:16px;"></i>
                </div>
            </div>
        </div>

        <!-- Collapsible Body -->
        <div id="sec-security-body" style="display:block; padding:18px 20px; background:#fff;">
            <div id="loginSessionsList" style="display:flex; flex-direction:column; gap:8px;">
                @forelse($loginSessions as $sess)
                <div class="session-item" id="session-row-{{ $sess->id }}" style="display:flex; justify-content:space-between; align-items:center; padding:10px 14px; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px;">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <div style="width:36px; height:36px; border-radius:8px; background:#E2E8F0; display:flex; align-items:center; justify-content:center; color:#334155;">
                            @if(stripos($sess->device_label, 'Mobile') !== false || stripos($sess->device_label, 'Phone') !== false || stripos($sess->device_label, 'Android') !== false || stripos($sess->device_label, 'iPhone') !== false)
                                <i data-lucide="smartphone" style="width:18px;height:18px;"></i>
                            @else
                                <i data-lucide="laptop" style="width:18px;height:18px;"></i>
                            @endif
                        </div>
                        <div>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <span style="font-weight:700; color:#0F172A; font-size:13px;">{{ $sess->device_label ?: 'Web Browser Terminal' }}</span>
                                @if(!empty($sess->is_online))
                                    <span class="badge session-status-badge" style="background:#DCFCE7; color:#15803D; font-size:10px; font-weight:700; display:flex; align-items:center; gap:4px;">
                                        <span style="display:inline-block; width:6px; height:6px; border-radius:50%; background:#22C55E;"></span> Online Now
                                    </span>
                                @elseif(!empty($sess->is_active))
                                    <span class="badge badge-blue session-status-badge" style="font-size:10px;">Active Session</span>
                                @else
                                    <span class="badge badge-gray session-status-badge" style="font-size:10px;">Logged Out</span>
                                @endif
                            </div>
                            <div style="font-size:11px; color:#64748B; margin-top:2px;">
                                User: <strong>{{ $sess->user_name ?? 'User' }}</strong> ({{ $sess->user_email ?? '—' }}) &bull; IP: <code>{{ $sess->ip_address ?? '—' }}</code>
                            </div>
                            <div style="font-size:10.5px; color:#94A3B8; margin-top:2px;">
                                Last Online: <strong>{{ $sess->last_online_diff ?? 'Never' }}</strong> ({{ !empty($sess->last_active_at) ? \Carbon\Carbon::parse($sess->last_active_at)->format('d M Y, h:i A') : 'N/A' }})
                            </div>
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; gap:10px;">
                        @if($sess->is_active)
                        <button type="button" class="btn btn-outline-danger btn-xs" style="color:#DC2626; border-color:#FECACA; background:#FEF2F2; padding:5px 12px; font-size:11px; font-weight:600; cursor:pointer;"
                            onclick="terminateSession({{ $sess->id }}, this)">
                            <i data-lucide="log-out" style="width:12px;height:12px;"></i> Disconnect
                        </button>
                        @endif
                    </div>
                </div>
                @empty
                <div style="text-align:center; padding:20px; color:#94A3B8;">No login session history recorded yet.</div>
                @endforelse
            </div>
            <div id="loginSessionsPagination"></div>
        </div>
    </div>
    @endif

    <!-- ══════════════════════════════════════════════════════════════════════
         MODAL: EDIT USER CREDENTIALS (NAME, EMAIL, PASSWORD, ROLE)
         ══════════════════════════════════════════════════════════════════════ -->
    <div id="editUserModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.55); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(3px);">
        <div class="card" style="width:100%; max-width:440px; margin:20px; border-radius:12px; overflow:hidden; box-shadow:0 20px 40px rgba(0,0,0,0.2);">
            <div class="card-header" style="background:#5E6AD2; color:#fff; padding:16px 20px;">
                <div>
                    <div class="card-title" style="color:#fff; font-size:16px;"><i data-lucide="user-check" style="width:18px;height:18px; vertical-align:-3px;"></i> Edit User Credentials</div>
                    <div class="card-subtitle" style="color:#E0E7FF; font-size:11px;">Update name, login email, password, or counter role</div>
                </div>
                <button type="button" onclick="closeEditUserModal()" style="background:none; border:none; color:#fff; cursor:pointer; font-size:20px;">&times;</button>
            </div>
            <form id="editUserForm" method="POST" action="">
                @csrf
                <div class="card-body" style="padding:20px; display:flex; flex-direction:column; gap:14px;">
                    <div>
                        <label class="form-label" style="font-weight:700; font-size:12px;">Full Name *</label>
                        <input type="text" name="name" id="editUserName" class="form-control" required style="width:100%; font-size:13px;">
                    </div>
                    <div>
                        <label class="form-label" style="font-weight:700; font-size:12px;">Login Email *</label>
                        <input type="email" name="email" id="editUserEmail" class="form-control" required style="width:100%; font-size:13px;">
                    </div>
                    <div>
                        <label class="form-label" style="font-weight:700; font-size:12px;">Counter Role / Terminal</label>
                        <select name="role" id="editUserRole" class="form-control" style="width:100%; font-size:13px;">
                            <option value="store-admin">👑 Store Owner / Administrator (store-admin)</option>
                            <option value="accessories-staff">⚡ Accessories &amp; Parts Staff (accessories-staff)</option>
                            <option value="repair-technician">🔧 Service Desk &amp; Repair Technician (repair-technician)</option>
                        </select>
                    </div>
                    <div style="border-top:1px dashed #CBD5E1; padding-top:12px;">
                        <label class="form-label" style="font-weight:700; font-size:12px; color:#5E6AD2;">
                            New Password <span style="font-weight:400; color:#64748B;">(Leave blank to keep unchanged)</span>
                        </label>
                        <div style="position:relative;">
                            <input type="password" name="password" id="editUserPassword" class="form-control" placeholder="Min. 6 characters" minlength="6" style="width:100%; font-size:13px; padding-right:38px;">
                            <button type="button" onclick="togglePasswordVisibility('editUserPassword')" style="position:absolute; right:8px; top:50%; transform:translateY(-50%); background:none; border:none; color:#64748B; cursor:pointer; padding:4px;">
                                <i data-lucide="eye" style="width:15px;height:15px;"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-footer" style="background:#F8FAFC; border-top:1px solid #E2E8F0; padding:12px 20px; display:flex; justify-content:flex-end; gap:8px;">
                    <button type="button" class="btn btn-outline btn-sm" onclick="closeEditUserModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm" style="background:#5E6AD2;">Save Credentials</button>
                </div>
            </form>
        </div>
    </div>



    <!-- ══════════════════════════════════════════════════════════════════════
         MODAL: EDIT SUPPLIER (SUPPLIER DETAILS & PREPAID WALLET)
         ══════════════════════════════════════════════════════════════════════ -->
    <div id="editSupplierMasterModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.55); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(3px);">
        <div class="card" style="width:100%; max-width:440px; margin:20px; border-radius:12px; overflow:hidden; box-shadow:0 20px 40px rgba(0,0,0,0.2);">
            <div class="card-header" style="background:#5E6AD2; color:#fff; padding:16px 20px;">
                <div>
                    <div class="card-title" style="color:#fff; font-size:16px;"><i data-lucide="truck" style="width:18px;height:18px; vertical-align:-3px;"></i> Update Supplier Ledger</div>
                    <div class="card-subtitle" style="color:#E0E7FF; font-size:11px;">Update vendor details and prepaid credit balance</div>
                </div>
                <button type="button" onclick="closeEditSupplierMasterModal()" style="background:none; border:none; color:#fff; cursor:pointer; font-size:20px;">&times;</button>
            </div>
            <form method="POST" action="{{ route('mobileshop.supplier.update') }}">
                @csrf
                <input type="hidden" name="supplier_id" id="editSupId">
                <div class="card-body" style="padding:20px; display:flex; flex-direction:column; gap:12px;">
                    <div>
                        <label class="form-label" style="font-weight:700; font-size:12px;">Supplier / Distributor Name *</label>
                        <input type="text" name="name" id="editSupName" class="form-control" required style="width:100%;">
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        <div>
                            <label class="form-label" style="font-weight:700; font-size:12px;">Phone</label>
                            <input type="text" name="phone" id="editSupPhone" class="form-control">
                        </div>
                        <div>
                            <label class="form-label" style="font-weight:700; font-size:12px;">GSTIN</label>
                            <input type="text" name="gstin" id="editSupGstin" class="form-control">
                        </div>
                    </div>
                    <div style="background:#F0FDF4; border:1px solid #BBF7D0; border-radius:8px; padding:12px;">
                        <label class="form-label" style="font-weight:700; font-size:12px; color:#166534;">Prepaid Advance Wallet (₹)</label>
                        <input type="number" step="0.01" min="0" name="credit_balance" id="editSupBalance" class="form-control" style="font-weight:800; font-size:16px; color:#15803D;">
                        <div style="font-size:10.5px; color:#166534; margin-top:4px;">Sets or adjusts supplier advance wallet balance.</div>
                    </div>
                    <div>
                        <label class="form-label" style="font-weight:700; font-size:12px;">Adjustment Note</label>
                        <input type="text" name="adjustment_notes" class="form-control" placeholder="e.g. Opening balance reconciliation">
                    </div>
                </div>
                <div class="card-footer" style="background:#F8FAFC; border-top:1px solid #E2E8F0; padding:12px 20px; display:flex; justify-content:flex-end; gap:8px;">
                    <button type="button" class="btn btn-outline btn-sm" onclick="closeEditSupplierMasterModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm" style="background:#5E6AD2;">Update Supplier</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════════
         MODAL: GENERATE EMPLOYEE INVITE TOKEN
         ══════════════════════════════════════════════════════════════════════ -->
    <div id="generateInviteModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.55); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(3px);">
        <div class="card" style="width:100%; max-width:480px; margin:20px; border-radius:12px; overflow:hidden; box-shadow:0 20px 40px rgba(0,0,0,0.2);">
            <div class="card-header" style="background:#0F766E; color:#fff; padding:16px 20px;">
                <div>
                    <div class="card-title" style="color:#fff; font-size:16px;"><i data-lucide="ticket" style="width:18px;height:18px; vertical-align:-3px;"></i> Issue Employee Invite Token</div>
                    <div class="card-subtitle" style="color:#CCFBF1; font-size:11px;">Create a single-use token pre-assigned to a counter station</div>
                </div>
                <button type="button" onclick="closeGenerateInviteModal()" style="background:none; border:none; color:#fff; font-size:20px; cursor:pointer; line-height:1;">&times;</button>
            </div>
            <div class="card-body" style="padding:20px;">
                <div id="inviteResultBanner" style="display:none; margin-bottom:16px; padding:14px; background:#F0FDFA; border:1px solid #99F6E4; border-radius:10px;">
                    <div style="font-size:11px; font-weight:700; color:#0F766E; text-transform:uppercase;">Invite Token Generated Successfully:</div>
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-top:8px; gap:8px;">
                        <code id="newGeneratedTokenDisplay" style="font-family:monospace; font-size:20px; font-weight:900; color:#0F766E; letter-spacing:1px; background:#fff; padding:4px 10px; border-radius:6px; border:1px solid #99F6E4;"></code>
                        <div style="display:flex; gap:6px;">
                            <button type="button" class="btn btn-primary btn-xs" id="copyGeneratedTokenBtn" onclick="copyNewTokenText()">Copy Code</button>
                            <button type="button" class="btn btn-outline btn-xs" id="copyGeneratedLinkBtn" onclick="copyNewTokenLink()">Copy Link</button>
                        </div>
                    </div>
                    <div style="font-size:11.5px; color:#115E59; margin-top:10px; line-height:1.4;">
                        <strong>Employee Instructions:</strong> Give this code to the employee. They can enter it on the <a href="{{ route('login') }}?tab=register" target="_blank" style="text-decoration:underline; font-weight:bold; color:#0F766E;">Login Page ("Register with Code")</a> and choose their own password!
                    </div>
                </div>

                <form id="generateInviteForm" onsubmit="submitGenerateInvite(event)">
                    <div class="form-group" style="margin-bottom:14px;">
                        <label style="font-size:12px; font-weight:700; color:#334155; margin-bottom:4px; display:block;">Counter Station &amp; Role <span style="color:#EF4444;">*</span></label>
                        <select id="inviteRoleSelect" required style="width:100%; padding:9px 12px; border:1px solid #CBD5E1; border-radius:6px; font-size:13px; background:#fff;">
                            <option value="store-admin">Store Administrator / Manager (store-admin)</option>
                            <option value="accessories-staff">Phone Accessories &amp; Spare Parts (accessories-staff)</option>
                            <option value="repair-technician">Diagnostics &amp; Repair Technician (repair-technician)</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom:14px;">
                        <label style="font-size:12px; font-weight:700; color:#334155; margin-bottom:4px; display:block;">Employee Reference / Note (Optional)</label>
                        <input type="text" id="inviteNotesInput" placeholder="e.g. Rahul Sharma — North Counter" style="width:100%; padding:9px 12px; border:1px solid #CBD5E1; border-radius:6px; font-size:13px;">
                    </div>

                    <div class="form-group" style="margin-bottom:18px;">
                        <label style="font-size:12px; font-weight:700; color:#334155; margin-bottom:4px; display:block;">Token Expiration</label>
                        <select id="inviteExpiryDays" style="width:100%; padding:9px 12px; border:1px solid #CBD5E1; border-radius:6px; font-size:13px; background:#fff;">
                            <option value="7" selected>7 Days (Standard)</option>
                            <option value="1">24 Hours (Urgent)</option>
                            <option value="14">14 Days</option>
                            <option value="30">30 Days</option>
                        </select>
                    </div>

                    <div style="display:flex; justify-content:flex-end; gap:8px;">
                        <button type="button" class="btn btn-outline" onclick="closeGenerateInviteModal()">Close</button>
                        <button type="submit" id="generateInviteSubmitBtn" class="btn btn-primary" style="background:#0F766E; border-color:#0F766E;">
                            <i data-lucide="plus" style="width:14px;height:14px;"></i> Issue Token
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    // ══════════════════════════════════════════════════════════════════════
    // EMPLOYEE INVITE TOKEN & ACCOUNT MANAGEMENT FUNCTIONS
    // ══════════════════════════════════════════════════════════════════════
    function openGenerateInviteModal() {
        document.getElementById('inviteResultBanner').style.display = 'none';
        document.getElementById('inviteNotesInput').value = '';
        document.getElementById('generateInviteSubmitBtn').disabled = false;
        document.getElementById('generateInviteModal').style.display = 'flex';
        if (window.lucide) window.lucide.createIcons();
    }

    function closeGenerateInviteModal() {
        document.getElementById('generateInviteModal').style.display = 'none';
    }

    function submitGenerateInvite(e) {
        e.preventDefault();
        var submitBtn = document.getElementById('generateInviteSubmitBtn');
        submitBtn.disabled = true;

        var role = document.getElementById('inviteRoleSelect').value;
        var notes = document.getElementById('inviteNotesInput').value;
        var expiryDays = document.getElementById('inviteExpiryDays').value;

        fetch("{{ route('mobileshop.accounts.invite.generate') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                role: role,
                notes: notes,
                expires_days: expiryDays
            })
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                var banner = document.getElementById('inviteResultBanner');
                document.getElementById('newGeneratedTokenDisplay').innerText = data.token;
                banner.style.display = 'block';
                submitBtn.disabled = false;
                setTimeout(function() {
                    window.location.reload();
                }, 2200);
            } else {
                alert(data.message || 'Could not generate invite token.');
                submitBtn.disabled = false;
            }
        })
        .catch(function(err) {
            console.error(err);
            alert('Failed to connect to server.');
            submitBtn.disabled = false;
        });
    }

    // ══════════════════════════════════════════════════════════════════════
    // COLLAPSIBLE CATEGORIES ACCORDION CONTROLLERS
    // ══════════════════════════════════════════════════════════════════════
    function toggleMasterAccordion(id) {
        var content = document.getElementById(id + '-body');
        var chevron = document.getElementById(id + '-chevron');
        if (!content) return;

        if (content.style.display === 'none') {
            content.style.display = 'block';
            if (chevron) chevron.setAttribute('data-lucide', 'chevron-up');
        } else {
            content.style.display = 'none';
            if (chevron) chevron.setAttribute('data-lucide', 'chevron-down');
        }
        if (window.lucide) window.lucide.createIcons();
    }

    function toggleAllAccordions() {
        var btn = document.getElementById('toggleAllAccordionsBtn');
        var isExpanded = btn && btn.getAttribute('data-expanded') === '1';
        var newExpanded = !isExpanded;

        var categories = ['sec-staff', 'sec-categories', 'sec-suppliers', 'sec-security'];
        categories.forEach(function(catId) {
            var body = document.getElementById(catId + '-body');
            var chevron = document.getElementById(catId + '-chevron');
            if (body) {
                body.style.display = newExpanded ? 'block' : 'none';
            }
            if (chevron) {
                chevron.setAttribute('data-lucide', newExpanded ? 'chevron-up' : 'chevron-down');
            }
        });

        if (btn) {
            btn.setAttribute('data-expanded', newExpanded ? '1' : '0');
            var icon = document.getElementById('toggleAllIcon');
            var text = document.getElementById('toggleAllText');
            if (icon) icon.setAttribute('data-lucide', newExpanded ? 'chevrons-up' : 'chevrons-down');
            if (text) text.innerText = newExpanded ? 'Collapse All' : 'Expand All';
        }
        if (window.lucide) window.lucide.createIcons();
    }

    function focusMasterCategory(id) {
        var target = document.getElementById(id);
        var body = document.getElementById(id + '-body');
        var chevron = document.getElementById(id + '-chevron');

        if (body && body.style.display === 'none') {
            body.style.display = 'block';
            if (chevron) chevron.setAttribute('data-lucide', 'chevron-up');
            if (window.lucide) window.lucide.createIcons();
        }

        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            target.style.transition = 'outline 0.3s ease';
            target.style.outline = '2px solid #0F766E';
            setTimeout(function() { target.style.outline = 'none'; }, 1500);
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    // TOKEN COPY UTILITIES
    // ══════════════════════════════════════════════════════════════════════
    function copyRawToken(token, btn) {
        navigator.clipboard.writeText(token).then(function() {
            var origHtml = btn.innerHTML;
            btn.innerHTML = '<i data-lucide="check" style="width:12px;height:12px;"></i> Copied!';
            if (window.lucide) window.lucide.createIcons();
            setTimeout(function() {
                btn.innerHTML = origHtml;
                if (window.lucide) window.lucide.createIcons();
            }, 1500);
        }).catch(function() {
            prompt('Copy Invite Code:', token);
        });
    }

    function copyInviteLink(token, btn) {
        var registerUrl = "{{ url('/auth/login') }}?tab=register&token=" + encodeURIComponent(token);
        navigator.clipboard.writeText(registerUrl).then(function() {
            var origHtml = btn.innerHTML;
            btn.innerHTML = '<i data-lucide="check" style="width:12px;height:12px;"></i> Copied!';
            if (window.lucide) window.lucide.createIcons();
            setTimeout(function() {
                btn.innerHTML = origHtml;
                if (window.lucide) window.lucide.createIcons();
            }, 1500);
        }).catch(function() {
            prompt('Copy registration link:', registerUrl);
        });
    }

    function copyNewTokenText() {
        var token = document.getElementById('newGeneratedTokenDisplay').innerText;
        navigator.clipboard.writeText(token).then(function() {
            var btn = document.getElementById('copyGeneratedTokenBtn');
            btn.innerText = 'Copied!';
            setTimeout(function() { btn.innerText = 'Copy Code'; }, 1500);
        });
    }

    function copyNewTokenLink() {
        var token = document.getElementById('newGeneratedTokenDisplay').innerText;
        var registerUrl = "{{ url('/auth/login') }}?tab=register&token=" + encodeURIComponent(token);
        navigator.clipboard.writeText(registerUrl).then(function() {
            var btn = document.getElementById('copyGeneratedLinkBtn');
            btn.innerText = 'Link Copied!';
            setTimeout(function() { btn.innerText = 'Copy Link'; }, 1500);
        }).catch(function() {
            prompt('Copy registration link:', registerUrl);
        });
    }

    function revokeInviteToken(id, btn) {
        if (!confirm('Are you sure you want to revoke this invite token? It will become permanently invalid.')) {
            return;
        }

        btn.disabled = true;
        var url = "{{ route('mobileshop.accounts.invite.revoke', ['id' => '__ID__']) }}".replace('__ID__', id);

        fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                var row = document.getElementById('invite-row-' + id);
                if (row) {
                    row.style.opacity = '0.5';
                    row.innerHTML = '<span style="color:#DC2626; font-size:12px; font-weight:bold;">Token Revoked</span>';
                }
            } else {
                alert(data.message || 'Could not revoke token.');
                btn.disabled = false;
            }
        })
        .catch(function(err) {
            console.error(err);
            alert('Failed to connect to server.');
            btn.disabled = false;
        });
    }

    function toggleStaffStatus(id, btn) {
        if (!confirm('Toggle active status for this staff member?')) {
            return;
        }

        btn.disabled = true;
        var url = "{{ route('mobileshop.accounts.toggle_status', ['id' => '__ID__']) }}".replace('__ID__', id);

        fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Could not update staff status.');
                btn.disabled = false;
            }
        })
        .catch(function(err) {
            console.error(err);
            alert('Failed to connect to server.');
            btn.disabled = false;
        });
    }
    function openEditUserCredentialsModal(id, name, email, role) {
        var form = document.getElementById('editUserForm');
        var updateUrl = "{{ route('mobileshop.masters.user.update', ['id' => '__ID__']) }}".replace('__ID__', id);
        form.action = updateUrl;
        document.getElementById('editUserName').value = name;
        document.getElementById('editUserEmail').value = email;
        document.getElementById('editUserPassword').value = '';
        var roleSelect = document.getElementById('editUserRole');
        if (roleSelect && role) {
            roleSelect.value = role;
        }
        document.getElementById('editUserModal').style.display = 'flex';
        if (window.lucide) window.lucide.createIcons();
    }
    function closeEditUserModal() {
        document.getElementById('editUserModal').style.display = 'none';
    }



    function terminateSession(sessionId, btn) {
        if (!confirm('Are you sure you want to terminate this active device session? The user will be logged out on that device.')) {
            return;
        }
        btn.disabled = true;
        btn.innerHTML = '<i data-lucide="loader-2" class="spin" style="width:12px;height:12px;"></i> Disconnecting...';
        if (window.lucide) window.lucide.createIcons();

        var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        var terminateUrl = "{{ route('mobileshop.sessions.terminate', ['id' => '__ID__']) }}".replace('__ID__', sessionId);

        fetch(terminateUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                var row = document.getElementById('session-row-' + sessionId);
                if (row) {
                    var badge = row.querySelector('.session-status-badge');
                    if (badge) {
                        badge.className = 'badge badge-gray session-status-badge';
                        badge.textContent = 'Terminated';
                    }
                    btn.remove();
                }
                alert('Session terminated successfully.');
            } else {
                alert(data.message || 'Failed to terminate session.');
                btn.disabled = false;
                btn.textContent = 'Disconnect';
            }
        })
        .catch(function(err) {
            console.error(err);
            alert('Failed to connect to server.');
            btn.disabled = false;
            btn.textContent = 'Disconnect';
        });
    }

    function openEditSupplierMasterModal(id, name, phone, gstin, balance) {
        document.getElementById('editSupId').value = id;
        document.getElementById('editSupName').value = name;
        document.getElementById('editSupPhone').value = phone || '';
        document.getElementById('editSupGstin').value = gstin || '';
        document.getElementById('editSupBalance').value = balance || 0;
        document.getElementById('editSupplierMasterModal').style.display = 'flex';
        if (window.lucide) window.lucide.createIcons();
    }
    function closeEditSupplierMasterModal() {
        document.getElementById('editSupplierMasterModal').style.display = 'none';
    }

    function togglePasswordVisibility(fieldId) {
        var input = document.getElementById(fieldId);
        if (input) {
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    }

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
            window.setupMobiTablePagination({
                cardsContainerId: 'loginSessionsList',
                cardSelector: '.session-item',
                paginationContainerId: 'loginSessionsPagination',
                pageSize: 10,
                itemName: 'sessions'
            });
        }
        if (window.lucide) window.lucide.createIcons();
    });
</script>
@endpush
