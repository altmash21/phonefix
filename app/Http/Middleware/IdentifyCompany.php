<?php

namespace App\Http\Middleware;

use App\Traits\Companies;
use App\Traits\Modules;
use App\Traits\Users;
use Closure;
use Illuminate\Auth\AuthenticationException;

class IdentifyCompany
{
    use Companies, Modules, Users;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$guards
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next, ...$guards)
    {
        $this->request = $request;

        $company_id = $this->getCompanyId();

        if (empty($company_id)) {
            if (request_is_mcp($this->request) || $this->request->bearerToken()) {
                throw new AuthenticationException('Unauthenticated.', $guards);
            }

            // Fallback for public / guest routes without company in URL
            $company_id = auth()->check() ? ($this->getFirstCompanyOfUser()?->id ?? 1) : 1;
        }

        // Check if user can access company
        if (auth()->check() && $this->request->isNotSigned($company_id) && $this->isNotUserCompany($company_id)) {
            throw new AuthenticationException('Unauthenticated.', $guards);
        }

        // Set company as current
        $company = company($company_id);

        if (empty($company)) {
            abort(404, 'Company not found');
        }

        $company->makeCurrent();

        // Load company-specific modules (event listeners, providers)
        $this->registerModules();

        // Fix file/folder paths
        config(['filesystems.disks.' . config('filesystems.default') . '.url' => url('/' . $company_id)  . '/uploads']);

        // Fix routes
        if ($this->request->isNotApi()) {
            app('url')->defaults(['company_id' => $company_id]);
            $this->request->route()->forgetParameter('company_id');
        }

        return $next($this->request);
    }
}
