<?php

namespace App\Http\Middleware;

use Closure;

class AddXHeader
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // Check if we should add header
        if (method_exists($response, 'header')) {
            $response->header('X-MobiTrack', 'Mobile Shop ERP');
            $response->header('X-Content-Type-Options', 'nosniff');
            $response->header('X-Frame-Options', 'SAMEORIGIN');
            $response->header('Referrer-Policy', 'strict-origin-when-cross-origin');
            $response->header('X-XSS-Protection', '0');
            $response->header('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

            // Prevent caching of HTML/session pages across reverse proxies, CDNs, and browser bfcache
            if (! $request->is('*.css', '*.js', '*.png', '*.jpg', '*.jpeg', '*.gif', '*.svg', '*.woff', '*.woff2', '*.ico')) {
                $response->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate');
                $response->header('Pragma', 'no-cache');
                $response->header('Expires', 'Sun, 02 Jan 1990 00:00:00 GMT');
            }
        }

        return $response;
    }
}
