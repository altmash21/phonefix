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

    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap:24px;">

        <!-- 1. Categories Master -->
        <div class="card" style="margin:0;">
            <div class="card-header" style="background:#F8FAFC; border-bottom:1px solid #E2E8F0;">
                <div>
                    <div class="card-title">Parts & Accessories Categories ({{ count($categories) }})</div>
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
                </div>
                <a href="{{ route('mobileshop.emi.ledger') }}" class="btn btn-outline btn-sm">EMI Ledger</a>
            </div>
            <div class="card-body" style="padding:14px;">
                <div id="financiersList" style="display:flex; flex-direction:column; gap:8px;">
                    @forelse($financiers as $emi)
                    <div class="emi-item" style="display:flex; justify-content:space-between; align-items:center; padding:8px 12px; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px;">
                        <div>
                            <div style="font-weight:700; color:#0F172A; font-size:13px;">{{ $emi->name }}</div>
                            <div style="font-size:11px; color:#64748B;">
                                Code: {{ $emi->code ?: '—' }} | Contact: {{ $emi->contact_person ?: '—' }} ({{ $emi->phone ?: '—' }})
                            </div>
                            <div style="font-size:10.5px; color:#475569; margin-top:3px; display:flex; gap:6px; flex-wrap:wrap;">
                                @if(!empty($emi->processing_fee_flat) && $emi->processing_fee_flat > 0)
                                    <span class="badge badge-blue" style="font-size:9.5px; padding:1px 6px;">Fee: ₹{{ number_format($emi->processing_fee_flat, 2) }}</span>
                                @endif
                                @if(!empty($emi->processing_fee_pct) && $emi->processing_fee_pct > 0)
                                    <span class="badge badge-purple" style="font-size:9.5px; padding:1px 6px;">Fee: {{ $emi->processing_fee_pct }}%</span>
                                @endif
                                @if(!empty($emi->default_tenure_months))
                                    <span class="badge badge-gray" style="font-size:9.5px; padding:1px 6px;">{{ $emi->default_tenure_months }} Mo Tenure</span>
                                @endif
                            </div>
                        </div>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <div style="text-align:right;">
                                <div style="font-weight:800; font-size:12px; color:var(--brand-700);">Pool: ₹{{ number_format($emi->advance_balance, 2) }}</div>
                            </div>
                            <button type="button" class="btn btn-outline btn-xs" style="padding:3px 8px; font-size:11px;"
                                onclick="openEditEmiMasterModal({{ $emi->id }}, '{{ addslashes($emi->name) }}', '{{ addslashes($emi->code ?? '') }}', '{{ addslashes($emi->contact_person ?? '') }}', '{{ addslashes($emi->phone ?? '') }}', {{ (float) ($emi->advance_balance ?? 0) }}, {{ (float) ($emi->processing_fee_flat ?? 0) }}, {{ (float) ($emi->processing_fee_pct ?? 0) }}, {{ (int) ($emi->default_tenure_months ?? 0) }}, {{ (float) ($emi->interest_rate_pct ?? 0) }}, '{{ addslashes($emi->notes ?? '') }}')">
                                <i data-lucide="edit-3" style="width:12px;height:12px;"></i> Edit
                            </button>
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
                </div>
                <a href="{{ route('mobileshop.purchase') }}" class="btn btn-outline btn-sm">POs & Ledger</a>
            </div>
            <div class="card-body" style="padding:14px;">
                <div id="suppliersList" style="display:flex; flex-direction:column; gap:8px;">
                    @forelse($suppliers as $sup)
                    <div class="sup-item" style="display:flex; justify-content:space-between; align-items:center; padding:8px 12px; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px;">
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
                            <button type="button" class="btn btn-outline btn-xs" style="padding:3px 8px; font-size:11px;"
                                onclick="openEditSupplierMasterModal({{ $sup->supplier_id }}, '{{ addslashes($sup->supplier_name) }}', '{{ addslashes($sup->supplier_phone ?? '') }}', '{{ addslashes($sup->supplier_gstin ?? '') }}', {{ (float) ($sup->credit_balance ?? 0) }})">
                                <i data-lucide="edit-3" style="width:12px;height:12px;"></i> Edit
                            </button>
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
                </div>
            </div>
            <div class="card-body" style="padding:14px;">
                <div id="staffUsersList" style="display:flex; flex-direction:column; gap:8px;">
                    @forelse($staffUsers as $usr)
                    @php
                        $isSuperAdmin = $usr->hasRole('admin');
                        $userRoleName = $usr->roles->first()?->name ?? 'Staff';
                        $userRoleDisplay = $usr->roles->first()?->display_name ?? $userRoleName;
                    @endphp
                    <div class="usr-item" style="display:flex; justify-content:space-between; align-items:center; padding:8px 12px; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div style="width:30px; height:30px; border-radius:50%; background:var(--brand-100); color:var(--brand-700); font-weight:800; font-size:12px; display:flex; align-items:center; justify-content:center;">
                                {{ strtoupper(substr($usr->name, 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-weight:700; color:#0F172A; font-size:12.5px;">{{ $usr->name }}</div>
                                <div style="font-size:10.5px; color:#64748B;">{{ $usr->email }}</div>
                            </div>
                        </div>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span class="badge {{ $isSuperAdmin ? 'badge-gray' : 'badge-purple' }}" style="font-size:10px;">
                                {{ $isSuperAdmin ? 'Super Admin (Protected)' : $userRoleDisplay }}
                            </span>
                            @if(!$isSuperAdmin || auth()->user()->hasRole('admin'))
                            <button type="button" class="btn btn-outline btn-xs" style="padding:3px 8px; font-size:11px;"
                                onclick="openEditUserCredentialsModal({{ $usr->id }}, '{{ addslashes($usr->name) }}', '{{ addslashes($usr->email) }}', '{{ $userRoleName }}')">
                                <i data-lucide="key" style="width:12px;height:12px;"></i> Credentials
                            </button>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div style="text-align:center; padding:16px; color:#94A3B8;">No users found.</div>
                    @endforelse
                </div>
                <div id="staffUsersPagination"></div>
            </div>
        </div>

        <!-- 5. Active Login Sessions & Devices (Store Owner / Admin Only) -->
        @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin'))
        <div class="card" style="margin:0; grid-column: 1 / -1;">
            <div class="card-header" style="background:#F8FAFC; border-bottom:1px solid #E2E8F0; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <div class="card-title" style="display:flex; align-items:center; gap:8px;">
                        <i data-lucide="shield-check" style="width:18px;height:18px; color:var(--brand-600);"></i>
                        Device Login Sessions & Security History ({{ count($loginSessions) }})
                    </div>
                </div>
                <span class="badge badge-purple" style="font-size:11px; font-weight:700;">Admin Protected</span>
            </div>
            <div class="card-body" style="padding:14px;">
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
                                    @if($sess->is_online)
                                        <span class="badge session-status-badge" style="background:#DCFCE7; color:#15803D; font-size:10px; font-weight:700; display:flex; align-items:center; gap:4px;">
                                            <span style="display:inline-block; width:6px; height:6px; border-radius:50%; background:#22C55E;"></span> Online Now
                                        </span>
                                    @elseif($sess->is_active)
                                        <span class="badge badge-blue session-status-badge" style="font-size:10px;">Active Session</span>
                                    @else
                                        <span class="badge badge-gray session-status-badge" style="font-size:10px;">Logged Out</span>
                                    @endif
                                </div>
                                <div style="font-size:11px; color:#64748B; margin-top:2px;">
                                    User: <strong>{{ $sess->user_name }}</strong> ({{ $sess->user_email }}) &bull; IP: <code>{{ $sess->ip_address }}</code>
                                </div>
                                <div style="font-size:10.5px; color:#94A3B8; margin-top:2px;">
                                    Last Online: <strong>{{ $sess->last_online_diff }}</strong> ({{ $sess->last_active_at ? \Carbon\Carbon::parse($sess->last_active_at)->format('d M Y, h:i A') : 'N/A' }})
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

    </div>

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
                            @foreach($roles as $r)
                                <option value="{{ $r->name }}">{{ $r->display_name ?: $r->name }} ({{ $r->name }})</option>
                            @endforeach
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
         MODAL: EDIT EMI PROVIDER (FINANCIER DETAILS & ADVANCE POOL)
         ══════════════════════════════════════════════════════════════════════ -->
    <div id="editEmiMasterModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.55); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(3px);">
        <div class="card" style="width:100%; max-width:440px; margin:20px; border-radius:12px; overflow:hidden; box-shadow:0 20px 40px rgba(0,0,0,0.2);">
            <div class="card-header" style="background:#2563EB; color:#fff; padding:16px 20px;">
                <div>
                    <div class="card-title" style="color:#fff; font-size:16px;"><i data-lucide="building-2" style="width:18px;height:18px; vertical-align:-3px;"></i> Update EMI Financier</div>
                    <div class="card-subtitle" style="color:#DBEAFE; font-size:11px;">Update company details and advance ledger pool balance</div>
                </div>
                <button type="button" onclick="closeEditEmiMasterModal()" style="background:none; border:none; color:#fff; cursor:pointer; font-size:20px;">&times;</button>
            </div>
            <form method="POST" action="{{ route('mobileshop.emi.provider.update') }}">
                @csrf
                <input type="hidden" name="emi_provider_id" id="editEmiId">
                <div class="card-body" style="padding:20px; display:flex; flex-direction:column; gap:12px;">
                    <div>
                        <label class="form-label" style="font-weight:700; font-size:12px;">Company Name *</label>
                        <input type="text" name="name" id="editEmiName" class="form-control" required style="width:100%;">
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        <div>
                            <label class="form-label" style="font-weight:700; font-size:12px;">Short Code</label>
                            <input type="text" name="code" id="editEmiCode" class="form-control" placeholder="BAJAJ, TVS">
                        </div>
                        <div>
                            <label class="form-label" style="font-weight:700; font-size:12px;">Phone</label>
                            <input type="text" name="phone" id="editEmiPhone" class="form-control">
                        </div>
                    </div>
                    <div>
                        <label class="form-label" style="font-weight:700; font-size:12px;">Contact Person</label>
                        <input type="text" name="contact_person" id="editEmiContact" class="form-control">
                    </div>
                    <div style="background:#F0FDF4; border:1px solid #BBF7D0; border-radius:8px; padding:12px;">
                        <label class="form-label" style="font-weight:700; font-size:12px; color:#166534;">Advance Balance Pool (₹)</label>
                        <input type="number" step="0.01" min="0" name="advance_balance" id="editEmiBalance" class="form-control" style="font-weight:800; font-size:16px; color:#15803D;">
                        <div style="font-size:10.5px; color:#166534; margin-top:4px;">Direct balance adjustment records an audit entry in the EMI ledger log.</div>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        <div>
                            <label class="form-label" style="font-weight:700; font-size:12px;">Processing Fee (Flat ₹)</label>
                            <input type="number" step="0.01" min="0" name="processing_fee_flat" id="editEmiFlatFee" class="form-control" placeholder="0.00">
                        </div>
                        <div>
                            <label class="form-label" style="font-weight:700; font-size:12px;">Processing Fee (%)</label>
                            <input type="number" step="0.01" min="0" max="100" name="processing_fee_pct" id="editEmiPctFee" class="form-control" placeholder="0.00">
                        </div>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        <div>
                            <label class="form-label" style="font-weight:700; font-size:12px;">Default Tenure (Months)</label>
                            <input type="number" min="1" max="60" name="default_tenure_months" id="editEmiTenure" class="form-control" placeholder="e.g. 12">
                        </div>
                        <div>
                            <label class="form-label" style="font-weight:700; font-size:12px;">Interest Rate (%)</label>
                            <input type="number" step="0.01" min="0" max="100" name="interest_rate_pct" id="editEmiInterest" class="form-control" placeholder="0.00">
                        </div>
                    </div>
                    <div>
                        <label class="form-label" style="font-weight:700; font-size:12px;">Notes / T&C</label>
                        <textarea name="notes" id="editEmiNotes" class="form-control" rows="2" placeholder="Fee deduction policy, terms..."></textarea>
                    </div>
                    <div>
                        <label class="form-label" style="font-weight:700; font-size:12px;">Adjustment Reason / Note</label>
                        <input type="text" name="adjustment_notes" class="form-control" placeholder="e.g. Reconciliation adjustment, settlement variance">
                    </div>
                </div>
                <div class="card-footer" style="background:#F8FAFC; border-top:1px solid #E2E8F0; padding:12px 20px; display:flex; justify-content:flex-end; gap:8px;">
                    <button type="button" class="btn btn-outline btn-sm" onclick="closeEditEmiMasterModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm" style="background:#2563EB;">Update Financier</button>
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

@endsection

@push('scripts')
<script>
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

    function openEditEmiMasterModal(id, name, code, contact, phone, balance, flatFee, pctFee, tenure, interest, notes) {
        document.getElementById('editEmiId').value = id;
        document.getElementById('editEmiName').value = name;
        document.getElementById('editEmiCode').value = code || '';
        document.getElementById('editEmiContact').value = contact || '';
        document.getElementById('editEmiPhone').value = phone || '';
        document.getElementById('editEmiBalance').value = balance || 0;
        document.getElementById('editEmiFlatFee').value = flatFee || '';
        document.getElementById('editEmiPctFee').value = pctFee || '';
        document.getElementById('editEmiTenure').value = tenure || '';
        document.getElementById('editEmiInterest').value = interest || '';
        document.getElementById('editEmiNotes').value = notes || '';
        document.getElementById('editEmiMasterModal').style.display = 'flex';
        if (window.lucide) window.lucide.createIcons();
    }
    function closeEditEmiMasterModal() {
        document.getElementById('editEmiMasterModal').style.display = 'none';
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
