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

        // Support login by ID / username (e.g. 'altmash') or email
        $matchedUser = \App\Models\Auth\User::where('email', $loginInput)
            ->orWhere('name', $loginInput)
            ->orWhere(function ($q) use ($loginInput) {
                if (!str_contains($loginInput, '@')) {
                    $q->where('email', $loginInput . '@mobitrack.local');
                }
            })
            ->first();

        $credentials = [
            'email'    => $matchedUser ? $matchedUser->email : $loginInput,
            'password' => $request->input('password'),
        ];

        // Attempt to login
        if (! auth()->attempt($credentials, $request->get('remember', false))) {
            return $this->respondLoginFailed();
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

        // Logout if no company assigned
        if (! $company) {
            $this->logout();

            // Security (CWE-204): do not expose "no company" message to the
            // client; log it server-side instead.
            Log::info('Login denied: no company assigned', [
                'email' => $request->email,
            ]);

            return $this->respondLoginFailed();
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

        // Redirect to landing page if is user
        $landing = ($user->landing_page && \Illuminate\Support\Facades\Route::has($user->landing_page)) ? $user->landing_page : 'mobileshop.dashboard';
        $url = route($landing, ['company_id' => $company->id]);

        return response()->json([
            'status' => null,
            'success' => true,
            'error' => false,
            'message' => trans('auth.login_redirect'),
            'data' => null,
            'redirect' => redirect()->intended($url)->getTargetUrl(),
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
