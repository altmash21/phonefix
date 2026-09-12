<?php

namespace App\Services\MobileShop\Common;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class MobileShopOtpService
{
    /**
     * Resolve store owner's email address for security OTP notifications.
     */
    public static function getOwnerEmail(int $companyId): string
    {
        try {
            $owner = DB::table('users')
                ->join('user_companies', 'users.id', '=', 'user_companies.user_id')
                ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
                ->join('roles', 'user_roles.role_id', '=', 'roles.id')
                ->where('user_companies.company_id', $companyId)
                ->whereIn('roles.name', ['store-admin', 'admin'])
                ->select('users.email')
                ->first();

            if ($owner && !empty($owner->email)) {
                return $owner->email;
            }
        } catch (\Throwable $e) {
            Log::warning("Could not query owner email via roles: " . $e->getMessage());
        }

        if (auth()->check() && (auth()->user()->hasRole('store-admin') || auth()->user()->hasRole('admin'))) {
            return auth()->user()->email;
        }

        try {
            $anyAdmin = DB::table('users')
                ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
                ->join('roles', 'user_roles.role_id', '=', 'roles.id')
                ->whereIn('roles.name', ['store-admin', 'admin'])
                ->select('users.email')
                ->first();

            if ($anyAdmin && !empty($anyAdmin->email)) {
                return $anyAdmin->email;
            }
        } catch (\Throwable $e) {}

        $company = DB::table('companies')->where('id', $companyId)->first();
        return $company?->email ?? config('mail.from.address', 'admin@mobileshop.local');
    }

    /**
     * Generate and dispatch a 6-digit OTP to the store owner's email.
     * Rate-limited to 3 requests per 5 minutes per user/IP.
     * Token is hashed using bcrypt before database persistence.
     */
    public static function generateOtp(int $companyId, string $action, string $itemReference, ?int $userId): array
    {
        $throttleKey = 'otp_gen:' . ($userId ?? auth()->id() ?? request()->ip()) . ':' . $action;

        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return [
                'sent'         => false,
                'error'        => "Too many OTP requests. Please wait {$seconds} seconds before requesting a new code.",
                'available_in' => $seconds,
            ];
        }

        RateLimiter::hit($throttleKey, 300);

        $otp = sprintf('%06d', mt_rand(100000, 999999));
        $ownerEmail = self::getOwnerEmail($companyId);

        DB::table('ms_otp_tokens')->insert([
            'company_id'     => $companyId,
            'requested_by'   => $userId ?? (auth()->check() ? auth()->id() : 1),
            'action'         => $action,
            'item_reference' => $itemReference,
            'otp_code'       => Hash::make($otp),
            'expires_at'     => Carbon::now()->addMinutes(5),
            'created_at'     => Carbon::now(),
        ]);

        $readableAction = ucwords(str_replace('_', ' ', $action));
        $requesterName  = auth()->check() ? auth()->user()->name : "User #{$userId}";

        try {
            Mail::raw("Security Notice: Restricted Action '{$readableAction}' requested on item reference '{$itemReference}' by {$requesterName}.\n\nYour 6-digit Authorization Code is: {$otp}\n\nThis OTP expires in 5 minutes. If you did not authorize this action, do not disclose this code.", function ($message) use ($ownerEmail, $readableAction) {
                $message->to($ownerEmail)->subject("Security Authorization OTP: {$readableAction}");
            });
        } catch (\Throwable $e) {
            Log::warning("Failed to dispatch OTP email to {$ownerEmail}: " . $e->getMessage());
        }

        $maskedEmail = $ownerEmail;
        if (str_contains($ownerEmail, '@')) {
            $parts = explode('@', $ownerEmail);
            $maskedEmail = substr($parts[0], 0, 3) . '***@' . $parts[1];
        }

        return [
            'sent'         => true,
            'target_email' => $maskedEmail,
            'expires_in'   => 300,
        ];
    }

    /**
     * Verify OTP token for a specific action and item reference.
     * Enforces rate limiting (max 5 verification attempts per 5 minutes).
     */
    public static function verifyOtp(int $companyId, string $action, string $itemReference, ?string $code, bool $isOwner): bool
    {
        if ($isOwner) {
            return true;
        }

        if (empty($code)) {
            return false;
        }

        $throttleKey = 'otp_verify:' . (auth()->id() ?? request()->ip()) . ':' . $action . ':' . $itemReference;

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            Log::warning("OTP verification locked out for key: {$throttleKey}");
            return false;
        }

        $submittedCode = trim($code);

        $tokens = DB::table('ms_otp_tokens')
            ->where('company_id', $companyId)
            ->where('action', $action)
            ->where('item_reference', $itemReference)
            ->where('expires_at', '>', Carbon::now())
            ->whereNull('verified_at')
            ->latest('id')
            ->limit(5)
            ->get();

        foreach ($tokens as $token) {
            if (Hash::check($submittedCode, $token->otp_code) || hash_equals($token->otp_code, $submittedCode)) {
                DB::table('ms_otp_tokens')->where('id', $token->id)->update([
                    'verified_at' => Carbon::now(),
                ]);
                RateLimiter::clear($throttleKey);
                return true;
            }
        }

        RateLimiter::hit($throttleKey, 300);
        return false;
    }
}
