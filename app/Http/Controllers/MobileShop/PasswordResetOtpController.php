<?php

namespace App\Http\Controllers\MobileShop;

use App\Models\Auth\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class PasswordResetOtpController extends Controller
{
    /**
     * Show Forgot Password View (Step 1: Enter email)
     */
    public function showForgot()
    {
        if (auth()->check()) {
            return redirect()->route('mobileshop.dashboard');
        }

        return view('auth.forgot_otp');
    }

    /**
     * Dispatch 6-digit OTP code to the user's email address
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:191',
        ], [
            'email.required' => 'Please enter your registered email address.',
            'email.email'    => 'Please provide a valid email format.',
        ]);

        $email = strtolower(trim($request->email));
        $user = User::where('email', $email)->first();

        if (!$user) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['email' => 'We could not find an active account with that email address.']);
        }

        // Rate limit: max 3 requests per 5 minutes per email
        $throttleKey = 'pwd_otp_req:' . $email;
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return redirect()->back()
                ->withInput()
                ->withErrors(['email' => "Too many OTP requests. Please wait {$seconds} seconds before requesting a new code."]);
        }
        RateLimiter::hit($throttleKey, 300);

        // Generate 6-digit OTP
        $otp = sprintf('%06d', mt_rand(100000, 999999));

        DB::table('ms_password_reset_otps')->insert([
            'email'       => $email,
            'otp_code'    => $otp,
            'expires_at'  => Carbon::now()->addMinutes(15),
            'created_at'  => Carbon::now(),
        ]);

        // Dispatch via Laravel Mailer
        try {
            Mail::raw("Hello {$user->name},\n\nYour 6-digit verification code to reset your MobiTrack ERP password is:\n\n  {$otp}\n\nThis authorization code is valid for 15 minutes. If you did not request a password reset, please inform your Store Administrator immediately.", function ($message) use ($user) {
                $message->to($user->email)->subject("MobiTrack Password Reset Code: {$user->name}");
            });
        } catch (\Throwable $e) {
            Log::warning("Could not dispatch password reset OTP email to {$email}: " . $e->getMessage());
        }

        // Mask email for display
        $parts = explode('@', $email);
        $maskedEmail = substr($parts[0], 0, min(3, strlen($parts[0]))) . '***@' . ($parts[1] ?? '');

        $redirect = redirect()->route('mobileshop.password.reset', ['email' => $email])
            ->with('success', "A 6-digit security code has been sent to {$maskedEmail}. Check your inbox.");

        // For local development convenience
        if (!app()->isProduction()) {
            $redirect->with('debug_otp', $otp);
        }

        return $redirect;
    }

    /**
     * Show Password Reset View (Step 2: Enter OTP & New Password)
     */
    public function showReset(Request $request)
    {
        if (auth()->check()) {
            return redirect()->route('mobileshop.dashboard');
        }

        $email = strtolower(trim((string) $request->query('email', '')));

        return view('auth.forgot_otp', [
            'step'  => 'verify',
            'email' => $email,
        ]);
    }

    /**
     * Verify OTP and update the user's password
     */
    public function processReset(Request $request)
    {
        $request->validate([
            'email'                 => 'required|email|max:191',
            'otp_code'              => 'required|string|size:6',
            'password'              => 'required|string|min:6|confirmed',
        ], [
            'otp_code.required'     => 'Please enter the 6-digit verification code.',
            'otp_code.size'         => 'Verification code must be exactly 6 digits.',
            'password.min'          => 'New password must be at least 6 characters.',
            'password.confirmed'    => 'Password confirmation does not match.',
        ]);

        $email = strtolower(trim($request->email));
        $code  = trim($request->otp_code);

        // Verification rate limiting: max 5 attempts per 5 minutes
        $verifyThrottleKey = 'pwd_otp_ver:' . $email;
        if (RateLimiter::tooManyAttempts($verifyThrottleKey, 5)) {
            return redirect()->back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['otp_code' => 'Too many failed verification attempts. Please request a new code.']);
        }

        $otpRecord = DB::table('ms_password_reset_otps')
            ->where('email', $email)
            ->where('otp_code', $code)
            ->where('expires_at', '>', Carbon::now())
            ->whereNull('verified_at')
            ->latest('id')
            ->first();

        if (!$otpRecord) {
            RateLimiter::hit($verifyThrottleKey, 300);
            return redirect()->back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['otp_code' => 'The verification code is incorrect or has expired. Please check your email or request a new code.']);
        }

        // Reset user password (User::setPasswordAttribute handles bcrypt automatically)
        $user = User::where('email', $email)->first();
        if (!$user) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'User account not found.'], 404);
            }
            return redirect()->route('mobileshop.password.forgot')
                ->withErrors(['email' => 'User account not found.']);
        }

        $user->password = $request->password;
        $user->save();

        // Invalidate OTP
        DB::table('ms_password_reset_otps')->where('id', $otpRecord->id)->update([
            'verified_at' => Carbon::now(),
        ]);

        RateLimiter::clear($verifyThrottleKey);

        $successMsg = "Password successfully updated! You can now log into your terminal.";

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'  => true,
                'redirect' => route('login'),
                'message'  => $successMsg,
            ]);
        }

        return redirect()->route('login')
            ->with('success', $successMsg);
    }
}
