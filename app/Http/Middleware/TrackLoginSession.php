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
        if (!Auth::check()) {
            return $next($request);
        }

        // Throttle DB updates: only touch session table once every 2 minutes per user session
        $lastTracked = session('ms_login_session_tracked', 0);
        if (time() - $lastTracked < 120) {
            return $next($request);
        }

        try {
            $user = Auth::user();
            $sessionId = session()->getId();
            $hashedToken = hash('sha256', $sessionId);

            $companyId = company_id() ?? session('company_id') ?? 1;

            $existing = DB::table('ms_login_sessions')
                ->where('session_token', $hashedToken)
                ->where('is_active', true)
                ->first();

            if ($existing) {
                // Update last active timestamp
                DB::table('ms_login_sessions')
                    ->where('id', $existing->id)
                    ->update(['last_active_at' => now()]);
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

            session(['ms_login_session_tracked' => time()]);

            // Auto-expire stale sessions older than 24 hours (1-in-100 lottery to prevent every-request update overhead)
            if (mt_rand(1, 100) === 1) {
                DB::table('ms_login_sessions')
                    ->where('is_active', true)
                    ->where('last_active_at', '<', now()->subHours(24))
                    ->update([
                        'is_active'     => false,
                        'logged_out_at' => now(),
                    ]);
            }
        } catch (\Throwable $e) {
            // Table doesn't exist yet or DB issue, fail silently without slowing or breaking request
        }

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
