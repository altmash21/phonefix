<?php

namespace App\Http\Controllers\MobileShop;

use App\Models\Auth\Role;
use App\Models\Auth\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class StaffOnboardingController extends Controller
{
    /**
     * Resolve database role name with alias normalization
     */
    public static function resolveRoleName(string $roleName): string
    {
        return match ($roleName) {
            'staff-phones', 'phones'                => 'sales-staff',
            'staff-secondhand', 'secondhand'        => 'secondhand-staff',
            'staff-accessories', 'accessories'      => 'accessories-staff',
            'staff-covers', 'covers'                => 'cover-staff',
            'staff-repairs', 'repairs'              => 'repair-technician',
            default                                 => $roleName,
        };
    }

    /**
     * Map role names to human-readable station names
     */
    public static function getStationLabel(string $roleName): string
    {
        $normalized = self::resolveRoleName($roleName);

        return match ($normalized) {
            'sales-staff'          => '📱 Brand New Mobiles POS',
            'secondhand-staff'     => '🔄 Pre-Owned & Buyback Evaluation',
            'accessories-staff'    => '⚡ Accessories & Spare Parts Counter',
            'cover-staff'          => '🖼️ Back Cover & Tempered Glass',
            'repair-technician'    => '🔧 Service Desk & Repair Lab',
            'store-admin', 'admin' => '👑 Store Administrator / Manager',
            default                => ucwords(str_replace(['-', '_'], ' ', $roleName)),
        };
    }

    /**
     * Show registration form with invite token
     */
    public function showRegister(Request $request)
    {
        if (auth()->check()) {
            return redirect()->route('mobileshop.dashboard');
        }

        $tokenStr = strtoupper(trim((string) $request->query('token', '')));
        $inviteInfo = null;

        if (!empty($tokenStr)) {
            $invite = DB::table('ms_employee_invites')
                ->where('token', $tokenStr)
                ->where('status', 'active')
                ->where('expires_at', '>', now())
                ->first();

            if ($invite) {
                $inviteInfo = [
                    'token'          => $invite->token,
                    'role_name'      => $invite->role_name,
                    'station_label'  => self::getStationLabel($invite->role_name),
                    'recipient_name' => $invite->recipient_name,
                    'expires_at'     => Carbon::parse($invite->expires_at)->diffForHumans(),
                ];
            }
        }

        return view('auth.register_invite', [
            'token'      => $tokenStr,
            'inviteInfo' => $inviteInfo,
        ]);
    }

    /**
     * Process employee registration using an active invite token
     */
    public function processRegister(Request $request)
    {
        if (auth()->check()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success'  => true,
                    'redirect' => route('mobileshop.dashboard'),
                ]);
            }
            return redirect()->route('mobileshop.dashboard');
        }

        $validator = Validator::make($request->all(), [
            'token'                 => 'required|string|max:32',
            'name'                  => 'required|string|max:191',
            'email'                 => 'required|email|max:191|unique:users,email',
            'password'              => 'required|string|min:6|confirmed',
        ], [
            'token.required'        => 'An employee onboarding token is required to register.',
            'email.unique'          => 'This email address is already registered. Please log in or use forgot password.',
            'password.min'          => 'Password must be at least 6 characters.',
            'password.confirmed'    => 'Password confirmation does not match.',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors'  => $validator->errors(),
                ], 422);
            }
            return redirect()->back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors($validator);
        }

        $submittedToken = strtoupper(trim((string) $request->token));

        $invite = DB::table('ms_employee_invites')
            ->where('token', $submittedToken)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->first();

        if (!$invite) {
            $msg = 'This invite token is invalid, expired, or has already been used. Please ask your Store Admin for a new token.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['token' => $msg]);
        }

        // Resolve assigned role with alias fallback
        $resolvedRoleName = self::resolveRoleName($invite->role_name);
        $role = Role::where('name', $resolvedRoleName)->first();
        if (!$role) {
            $role = Role::where('name', $invite->role_name)->first()
                ?? Role::where('name', 'accessories-staff')->first()
                ?? Role::first();
        }

        $companyId = (int) ($invite->company_id ?? 1);

        $user = DB::transaction(function () use ($request, $invite, $role, $companyId) {
            // Note: User model has setPasswordAttribute which calls bcrypt() automatically.
            // Pass plain text password to avoid double-hashing!
            $newUser = User::create([
                'name'         => trim($request->name),
                'email'        => strtolower(trim($request->email)),
                'password'     => $request->password,
                'landing_page' => 'mobileshop.dashboard',
                'locale'       => 'en-GB',
                'enabled'      => 1,
            ]);

            // Link to store company
            if (!$newUser->companies()->where('company_id', $companyId)->exists()) {
                $newUser->companies()->attach($companyId);
            }

            // Sync designated counter role
            if ($role) {
                $newUser->syncRoles([$role->id]);
            }

            // Mark token as consumed
            DB::table('ms_employee_invites')->where('id', $invite->id)->update([
                'used_by'    => $newUser->id,
                'used_at'    => now(),
                'status'     => 'used',
                'updated_at' => now(),
            ]);

            return $newUser;
        });

        // Log employee in
        auth()->login($user);
        session(['company_id' => $companyId]);

        $station = self::getStationLabel($invite->role_name);
        $welcomeMsg = "Welcome to the team, {$user->name}! Your account has been activated for the {$station} station.";

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'  => true,
                'redirect' => route('mobileshop.dashboard', ['company_id' => $companyId]),
                'message'  => $welcomeMsg,
            ]);
        }

        return redirect()->route('mobileshop.dashboard', ['company_id' => $companyId])
            ->with('success', $welcomeMsg);
    }
}
