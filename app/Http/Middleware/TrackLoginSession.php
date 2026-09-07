<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TrackLoginSession
{
    /**
     * Track active login sessions for device & IP monitoring.
     * Creates a session row on first hit, updates last_active_at on subsequent requests.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check() || !Schema::hasTable('ms_login_sessions')) {
            return $next($request);
        }

        $user = Auth::user();
        $sessionId = session()->getId();
        $hashedToken = hash('sha256', $sessionId);

        $companyId = company_id() ?? session('company_id') ?? ($user->companies()->first()?->id ?? 1);

        $existing = DB::table('ms_login_sessions')
            ->where('session_token', $hashedToken)
            ->where('is_active', true)
            ->first();

        if ($existing) {
            // Update last active timestamp (throttled: max once per 60 seconds)
            if (!$existing->last_active_at || now()->diffInSeconds($existing->last_active_at) >= 60) {
                DB::table('ms_login_sessions')
                    ->where('id', $existing->id)
                    ->update(['last_active_at' => now()]);
            }
        } else {
            // First request in this session — record login
            $userAgent = $request->userAgent() ?? 'Unknown';
            $deviceLabel = $this->parseDeviceLabel($userAgent);

            DB::table('ms_login_sessions')->insert([
                'company_id'     => $companyId,
                'user_id'        => $user->id,
                'ip_address'     => $request->ip(),
                'user_agent'     => $userAgent,
                'device_label'   => $deviceLabel,
                'session_token'  => $hashedToken,
                'logged_in_at'   => now(),
                'last_active_at' => now(),
                'is_active'      => true,
            ]);
        }

        // Auto-expire stale sessions older than 24 hours
        DB::table('ms_login_sessions')
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->where('last_active_at', '<', now()->subHours(24))
            ->update([
                'is_active'     => false,
                'logged_out_at' => now(),
            ]);

        return $next($request);
    }

    /**
     * Parse user agent string into a human-readable device label.
     */
    private function parseDeviceLabel(string $ua): string
    {
        // Detect browser
        $browser = 'Unknown Browser';
        if (preg_match('/Edg\/[\d.]+/', $ua)) {
            $browser = 'Microsoft Edge';
        } elseif (preg_match('/OPR\/[\d.]+|Opera\/[\d.]+/', $ua)) {
            $browser = 'Opera';
        } elseif (preg_match('/Chrome\/[\d.]+/', $ua) && !preg_match('/Edg\//', $ua)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Firefox\/[\d.]+/', $ua)) {
            $browser = 'Firefox';
        } elseif (preg_match('/Safari\/[\d.]+/', $ua) && !preg_match('/Chrome\//', $ua)) {
            $browser = 'Safari';
        } elseif (preg_match('/MSIE|Trident/', $ua)) {
            $browser = 'Internet Explorer';
        }

        // Detect OS
        $os = 'Unknown OS';
        if (preg_match('/Windows NT 10/', $ua)) {
            $os = 'Windows 10/11';
        } elseif (preg_match('/Windows NT/', $ua)) {
            $os = 'Windows';
        } elseif (preg_match('/Macintosh|Mac OS/', $ua)) {
            $os = 'macOS';
        } elseif (preg_match('/Android/', $ua)) {
            $os = 'Android';
        } elseif (preg_match('/iPhone|iPad/', $ua)) {
            $os = 'iOS';
        } elseif (preg_match('/Linux/', $ua)) {
            $os = 'Linux';
        }

        return "{$browser} on {$os}";
    }
}
