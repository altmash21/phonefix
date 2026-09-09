<?php

namespace App\Providers;

use App\Policies\MobileShop\MobileSalePolicy;
use App\Policies\MobileShop\MobileStockPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as Provider;
use Illuminate\Support\Facades\Gate;

class Auth extends Provider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('sale.create', [MobileSalePolicy::class, 'create']);
        Gate::define('sale.viewAny', [MobileSalePolicy::class, 'viewAny']);
        Gate::define('sale.void', [MobileSalePolicy::class, 'void']);
        Gate::define('sale.printInvoice', [MobileSalePolicy::class, 'printInvoice']);

        Gate::define('stock.viewAny', [MobileStockPolicy::class, 'viewAny']);
        Gate::define('stock.create', [MobileStockPolicy::class, 'create']);
        Gate::define('stock.update', [MobileStockPolicy::class, 'update']);
        Gate::define('stock.delete', [MobileStockPolicy::class, 'delete']);
    }
}
