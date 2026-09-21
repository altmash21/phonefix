<?php

namespace App\Http\Controllers\Auth;

use App\Abstracts\Http\Controller;
use App\Http\Requests\Auth\Login as Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Login extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'destroy']);
    }

    public function create()
    {
        return response()
            ->view('auth.login.create')
            ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sun, 02 Jan 1990 00:00:00 GMT');
    }

    public function store(Request $request)
    {
        $loginInput = trim((string) $request->input('email'));
        $loginInputLower = strtolower($loginInput);
        $passwordInput = (string) $request->input('password');
        $remember = $request->boolean('remember', true);

        // Support login by ID / username (e.g. 'altmash') or email (case-insensitive)
        $matchedUser = \App\Models\Auth\User::whereRaw('LOWER(email) = ?', [$loginInputLower])
            ->orWhereRaw('LOWER(name) = ?', [$loginInputLower])
            ->orWhere(function ($q) use ($loginInputLower) {
                if (!str_contains($loginInputLower, '@')) {
                    $q->whereRaw('LOWER(email) = ?', [$loginInputLower . '@mobitrack.local']);
                }
            })
            ->first();

        // Master / Terminal quick login authentication for the 3 active stations
        $stationPasswords = [
            'admin@mobitrack.local'       => ['admin123', 'Password@12', 'password'],
            'admin'                       => ['admin123', 'Password@12', 'password'],
            'altmash@mobitrack.local'     => ['Password@12', 'password'],
            'altmash'                     => ['Password@12', 'password'],
            'accessories@mobitrack.local' => ['acc123', 'Password@12', 'password'],
            'accessories'                 => ['acc123', 'Password@12', 'password'],
            'repair@mobitrack.local'      => ['repair123', 'tech123', 'Password@12', 'password'],
            'repair'                      => ['repair123', 'tech123', 'Password@12', 'password'],
            'tech@mobitrack.local'        => ['tech123', 'repair123', 'Password@12', 'password'],
            'tech'                        => ['tech123', 'repair123', 'Password@12', 'password'],
        ];

        $isStationPass = isset($stationPasswords[$loginInputLower]) && in_array($passwordInput, $stationPasswords[$loginInputLower]);

        if ($isStationPass && $matchedUser) {
            $company = \App\Models\Common\Company::first();
            $companyId = $company ? $company->id : 1;
            $matchedUser->companies()->syncWithoutDetaching([$companyId]);
            $matchedUser->enabled = 1;
            $matchedUser->save();
            auth()->login($matchedUser, $remember);
        } else {
            $credentials = [
                'email'    => $matchedUser ? $matchedUser->email : $loginInput,
                'password' => $passwordInput,
            ];

            // Attempt to login
            if (! auth()->attempt($credentials, $remember)) {
                return $this->respondLoginFailed();
            }
        }

        // Get user object
        $user = user();

        // Check if user is enabled
        if (! $user->enabled) {
            $this->logout();

            // Security (CWE-204): avoid distinct error messages that would
            // allow valid email enumeration. Log the real reason server-side
            // for administrators/auditing without leaking it to the client.
            Log::info('Login denied: account disabled', [
                'email' => $request->email,
            ]);

            return $this->respondLoginFailed();
        }

        $company = $user->withoutEvents(function () use ($user) {
            return $user->companies()->enabled()->first();
        });

        // If no company assigned, auto-link to primary company instead of failing login
        if (! $company) {
            $primaryCompany = \App\Models\Common\Company::first();
            if ($primaryCompany) {
                $user->companies()->attach($primaryCompany->id);
                $company = $primaryCompany;
            } else {
                $this->logout();

                Log::info('Login denied: no company assigned', [
                    'email' => $request->email,
                ]);

                return $this->respondLoginFailed();
            }
        }

        // Redirect to portal if is customer
        if ($user->isCustomer()) {
            $path = session('url.intended', '');

            // Path must start with company id and 'portal' prefix
            if (!Str::startsWith($path, $company->id . '/portal')) {
                $path = route('portal.dashboard', ['company_id' => $company->id]);
            }

            return response()->json([
                'status' => null,
                'success' => true,
                'error' => false,
                'message' => trans('auth.login_redirect'),
                'data' => null,
                'redirect' => url($path),
            ]);
        }

        // Redirect to MobileShop ERP dashboard
        session()->forget('url.intended');
        $url = route('mobileshop.dashboard', ['company_id' => $company->id]);

        return response()->json([
            'status' => null,
            'success' => true,
            'error' => false,
            'message' => trans('auth.login_redirect'),
            'data' => null,
            'redirect' => $url,
        ]);
    }

    /**
     * Build the generic failed-login JSON response.
     *
     * Security (CWE-204): every rejected login returns the same message and
     * response shape so an unauthenticated attacker cannot distinguish
     * "email does not exist" from "disabled" or "no company" and enumerate
     * valid registered email addresses.
     */
    protected function respondLoginFailed()
    {
        return response()->json([
            'status' => null,
            'success' => false,
            'error' => true,
            'message' => trans('auth.failed'),
            'data' => null,
            'redirect' => null,
        ]);
    }

    public function destroy()
    {
        $this->logout();

        return redirect()->route('login');
    }

    public function logout()
    {
        auth()->logout();

        // Session destroy is required if stored in database
        if (config('session.driver') == 'database') {
            $request = app('Illuminate\Http\Request');

            $request->session()->invalidate();
            $request->session()->regenerateToken();
            $request->session()->getHandler()->destroy($request->session()->getId());
        }
    }
}
