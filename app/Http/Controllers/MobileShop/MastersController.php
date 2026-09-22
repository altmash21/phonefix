<?php

namespace App\Http\Controllers\MobileShop;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Auth\User;
use App\Models\Auth\Role;

class MastersController extends BaseMobileShopController
{
    /**
     * Masters Hub (Categories, Financiers, Suppliers & Role Settings)
     */
    public function masters()
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $canAccess = $user->hasRole('store-admin')
            || $user->hasRole('admin')
            || $user->hasRole('owner')
            || $this->isOwner()
            || $user->can('read-mobileshop-masters')
            || $user->can('read-admin-panel');

        if (!$canAccess) {
            return redirect()->route('mobileshop.dashboard')
                ->with('error', 'Store Admin or Owner privileges required to access Store Masters & Settings.');
        }

        $companyId = $this->getCompanyId();

        $categories = collect();
        try {
            $categories = DB::table('ms_part_categories')->where('company_id', $companyId)->orderBy('name', 'asc')->get();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Could not fetch categories in Masters: ' . $e->getMessage());
        }

        $suppliers = collect();
        try {
            $prefix = DB::getTablePrefix();
            $suppliers  = DB::table('ms_suppliers')
                ->leftJoin('ms_supplier_credit_wallets', function($join) use ($companyId) {
                    $join->on('ms_suppliers.id', '=', 'ms_supplier_credit_wallets.supplier_id')
                         ->where('ms_supplier_credit_wallets.company_id', '=', $companyId);
                })
                ->select(
                    'ms_suppliers.id as supplier_id',
                    'ms_suppliers.name as supplier_name',
                    'ms_suppliers.phone as supplier_phone',
                    'ms_suppliers.gstin as supplier_gstin',
                    DB::raw("COALESCE({$prefix}ms_supplier_credit_wallets.credit_balance, 0) as credit_balance")
                )
                ->where('ms_suppliers.company_id', $companyId)
                ->orderBy('ms_suppliers.name', 'asc')
                ->get();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Could not fetch suppliers in Masters: ' . $e->getMessage());
        }

        $staffUsers = collect();
        try {
            $staffUsers = User::with('roles')->whereHas('companies', function($q) use ($companyId) {
                $q->where('companies.id', $companyId);
            })->get();
        } catch (\Throwable $e) {
            $staffUsers = User::with('roles')->get();
        }

        $roles = collect();
        try {
            $roles = Role::whereIn('name', [
                'store-admin',
                'accessories-staff',
                'repair-technician',
            ])->get();
        } catch (\Throwable $e) {
            //
        }

        $loginSessions = collect();
        try {
            $loginSessions = DB::table('ms_login_sessions')
                ->join('users', 'ms_login_sessions.user_id', '=', 'users.id')
                ->where('ms_login_sessions.company_id', $companyId)
                ->select('ms_login_sessions.*', 'users.name as user_name', 'users.email as user_email')
                ->orderByDesc('ms_login_sessions.last_active_at')
                ->limit(50)
                ->get()
                ->map(function ($s) {
                    $lastActive = $s->last_active_at ? Carbon::parse($s->last_active_at) : null;
                    $s->is_online = $s->is_active && $lastActive && $lastActive->diffInMinutes(now()) <= 15;
                    $s->last_online_diff = $lastActive ? $lastActive->diffForHumans() : 'Never';
                    return $s;
                });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Could not fetch login sessions in Masters: ' . $e->getMessage());
        }

        $activeInvites = collect();
        try {
            $activeInvites = DB::table('ms_employee_invites')
                ->where('company_id', $companyId)
                ->orderByDesc('id')
                ->get()
                ->map(function ($inv) {
                    $roleModel = Role::where('name', $inv->role_name)->first();
                    $inv->role_label = $roleModel?->display_name ?? $inv->role_name;
                    $inv->notes = $inv->recipient_name;
                    $expiresAt = $inv->expires_at ? Carbon::parse($inv->expires_at) : null;
                    $inv->expires_diff = $expiresAt ? $expiresAt->diffForHumans() : 'No expiry';
                    return $inv;
                });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Could not fetch active invites in Masters: ' . $e->getMessage());
        }

        return view('mobileshop.masters', [
            'categories'    => $categories,
            'suppliers'     => $suppliers,
            'staffUsers'    => $staffUsers,
            'roles'         => $roles,
            'loginSessions' => $loginSessions,
            'activeInvites' => $activeInvites,
            'financiers'    => collect(),
        ]);
    }

    /**
     * Generate an Employee Invite Token
     */
    public function generateInviteToken(Request $request)
    {
        $currentUser = auth()->user();
        abort_unless($currentUser && (
            $currentUser->hasRole('store-admin') ||
            $currentUser->hasRole('admin') ||
            $currentUser->hasRole('owner') ||
            $currentUser->can('read-mobileshop-masters')
        ), 403, 'Admin privileges required to generate employee invite tokens.');

        $roleName = $request->input('role_name') ?? $request->input('role');
        $recipientNotes = $request->input('recipient_name') ?? $request->input('notes');
        $expiresDays = (int) ($request->input('expires_days') ?? 7);
        if ($expiresDays < 1 || $expiresDays > 90) {
            $expiresDays = 7;
        }

        if (empty($roleName)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Role name is required.'], 422);
            }
            return redirect()->back()->with('error', 'Role is required.');
        }

        $companyId = $this->getCompanyId();

        // Generate unique 8-character token (e.g. EMP-7X9K2P)
        do {
            $token = 'EMP-' . strtoupper(Str::random(6));
        } while (DB::table('ms_employee_invites')->where('token', $token)->exists());

        DB::table('ms_employee_invites')->insert([
            'company_id'     => $companyId,
            'token'          => $token,
            'role_name'      => $roleName,
            'recipient_name' => trim($recipientNotes ?? ''),
            'created_by'     => $currentUser->id,
            'expires_at'     => now()->addDays($expiresDays),
            'status'         => 'active',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        $registerUrl = url('auth/employee-register?token=' . $token);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'      => true,
                'token'        => $token,
                'register_url' => $registerUrl,
                'message'      => "Invite Token '{$token}' generated successfully!",
            ]);
        }

        return redirect()->route('mobileshop.masters', ['company_id' => $companyId, 'tab' => 'staff'])
            ->with('success', "Invite Token '{$token}' generated successfully! Share this code or registration link with the employee: {$registerUrl}");
    }

    /**
     * Revoke an unconsumed invite token
     */
    public function revokeInviteToken(Request $request, $id)
    {
        $currentUser = auth()->user();
        abort_unless($currentUser && (
            $currentUser->hasRole('store-admin') ||
            $currentUser->hasRole('admin') ||
            $currentUser->hasRole('owner')
        ), 403, 'Admin privileges required to revoke invite tokens.');

        $companyId = $this->getCompanyId();

        DB::table('ms_employee_invites')
            ->where('id', $id)
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->update([
                'status'     => 'revoked',
                'updated_at' => now(),
            ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Invite token has been revoked.',
            ]);
        }

        return redirect()->route('mobileshop.masters', ['company_id' => $companyId, 'tab' => 'staff'])
            ->with('success', 'Invite token has been revoked.');
    }

    /**
     * Toggle active/disabled status of a staff member
     */
    public function toggleStaffStatus(Request $request, $id)
    {
        $currentUser = auth()->user();
        abort_unless($currentUser && (
            $currentUser->hasRole('store-admin') ||
            $currentUser->hasRole('admin') ||
            $currentUser->hasRole('owner')
        ), 403, 'Admin privileges required to toggle employee access.');

        if ((int) $id === (int) $currentUser->id) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'You cannot disable your own active account.'], 422);
            }
            return redirect()->back()->with('error', 'You cannot disable your own active account.');
        }

        $targetUser = User::findOrFail($id);
        if ($targetUser->hasRole('admin') && !$currentUser->hasRole('admin')) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Store Admin cannot modify the Super Admin account.'], 403);
            }
            return redirect()->back()->with('error', 'Store Admin cannot modify the Super Admin account.');
        }

        $targetUser->enabled = !$targetUser->enabled;
        $targetUser->save();

        $statusText = $targetUser->enabled ? 'enabled' : 'disabled';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'enabled' => (bool) $targetUser->enabled,
                'message' => "Employee '{$targetUser->name}' has been {$statusText}.",
            ]);
        }

        return redirect()->route('mobileshop.masters', ['company_id' => $this->getCompanyId(), 'tab' => 'staff'])
            ->with('success', "Employee '{$targetUser->name}' has been {$statusText}.");
    }

    /**
     * Update user credentials from Masters (Store Admin can edit any non-superadmin user)
     */
    public function updateUserCredentials(Request $request, $id)
    {
        $currentUser = auth()->user();
        abort_unless($currentUser && (
            $currentUser->hasRole('store-admin') ||
            $currentUser->hasRole('admin') ||
            $currentUser->hasRole('owner') ||
            $currentUser->can('read-mobileshop-masters')
        ), 403, 'Admin access required to modify user credentials.');

        $currentUser = auth()->user();
        $targetUser = User::findOrFail($id);

        // Security restriction: store-admin cannot modify super-admin (role 'admin')
        $targetIsSuperAdmin = $targetUser->hasRole('admin');
        if ($targetIsSuperAdmin && !$currentUser->hasRole('admin')) {
            return redirect()->back()->with('error', 'Store Admin cannot modify credentials of the Super Admin account.');
        }

        $request->validate([
            'name' => 'required|string|max:191',
            'email' => 'required|email|max:191|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6',
            'role' => 'nullable|string|exists:roles,name',
        ]);

        $targetUser->name = trim($request->name);
        $targetUser->email = trim($request->email);

        if ($request->filled('password')) {
            $targetUser->password = $request->password;
        }

        $targetUser->save();

        // Update role if provided and target is not super admin
        if ($request->filled('role') && !$targetIsSuperAdmin) {
            $role = Role::where('name', $request->role)->first();
            if ($role) {
                $targetUser->syncRoles([$role]);
            }
        }

        return redirect()->route('mobileshop.masters', ['company_id' => $this->getCompanyId()])
            ->with('success', "Credentials for user '{$targetUser->name}' updated successfully." . ($request->filled('password') ? " Password has been changed." : ""));
    }

    /**
     * Request OTP for destructive action authorization (sent to store owner's email)
     */
    public function requestOtp(Request $request)
    {
        abort_unless(auth()->check(), 401);

        $request->validate([
            'action'         => 'required|string|max:50',
            'item_reference' => 'nullable|string|max:100',
        ]);

        $companyId     = $this->getCompanyId();
        $action        = $request->action;
        $itemReference = $request->item_reference ?? 'general';

        if ($this->isOwner()) {
            return response()->json([
                'success'  => true,
                'is_owner' => true,
                'message'  => 'User has owner privileges. Direct authorization granted.',
            ]);
        }

        $result = $this->generateOtp($companyId, $action, $itemReference, auth()->id());

        if (empty($result['sent'])) {
            return response()->json([
                'success'      => false,
                'is_owner'     => false,
                'message'      => $result['error'] ?? 'Too many attempts. Please wait before requesting another OTP.',
                'available_in' => $result['available_in'] ?? 300,
            ], 429);
        }

        return response()->json([
            'success'      => true,
            'is_owner'     => false,
            'target_email' => $result['target_email'],
            'message'      => "A 6-digit authorization OTP has been dispatched to store owner ({$result['target_email']}).",
        ]);
    }

    /**
     * Verify OTP code endpoint
     */
    public function verifyOtpEndpoint(Request $request)
    {
        abort_unless(auth()->check(), 401);

        $request->validate([
            'action'         => 'required|string|max:50',
            'item_reference' => 'nullable|string|max:100',
            'otp_code'       => 'required|string|max:10',
        ]);

        $companyId     = $this->getCompanyId();
        $action        = $request->action;
        $itemReference = $request->item_reference ?? 'general';
        $code          = $request->otp_code;

        if ($this->verifyOtp($companyId, $action, $itemReference, $code)) {
            return response()->json(['success' => true, 'message' => 'OTP verified successfully.']);
        }

        return response()->json(['success' => false, 'message' => 'Invalid or expired OTP code.'], 422);
    }

    /**
     * Get active login sessions (Admin-only)
     */
    public function getLoginSessions(Request $request)
    {
        abort_unless($this->isOwner(), 403, 'Only Store Owner / Admin can view login session history.');

        $companyId = $this->getCompanyId();
        $sessions = collect();

        try {
            $sessions = DB::table('ms_login_sessions')
                ->join('users', 'ms_login_sessions.user_id', '=', 'users.id')
                ->where('ms_login_sessions.company_id', $companyId)
                ->select(
                    'ms_login_sessions.*',
                    'users.name as user_name',
                    'users.email as user_email'
                )
                ->orderByDesc('ms_login_sessions.last_active_at')
                ->limit(100)
                ->get()
                ->map(function ($s) {
                    $lastActive = $s->last_active_at ? Carbon::parse($s->last_active_at) : null;
                    $isOnline = $s->is_active && $lastActive && $lastActive->diffInMinutes(now()) <= 15;
                    $s->is_online = $isOnline;
                    $s->last_online_diff = $lastActive ? $lastActive->diffForHumans() : 'Never';
                    return $s;
                });
        } catch (\Throwable $e) {}

        return response()->json(['success' => true, 'sessions' => $sessions]);
    }

    /**
     * Terminate / Kick an active login session (Admin-only)
     */
    public function terminateLoginSession(Request $request, $id)
    {
        abort_unless($this->isOwner(), 403, 'Only Store Owner / Admin can terminate login sessions.');

        $companyId = $this->getCompanyId();

        try {
            DB::table('ms_login_sessions')
                ->where('company_id', $companyId)
                ->where('id', $id)
                ->update([
                    'is_active' => false,
                    'logged_out_at' => Carbon::now(),
                ]);
        } catch (\Throwable $e) {}

        return response()->json(['success' => true, 'message' => 'Device session terminated successfully.']);
    }
}
