<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider as Provider;
use Laravel\Sanctum\Sanctum;

class App extends Provider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (config('app.installed') && config('app.debug')) {
            $this->app->register(\Barryvdh\Debugbar\ServiceProvider::class);
        }

        if (! env_is_production()) {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }

        Sanctum::ignoreMigrations();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Resolve public directory or public_html for DomPDF and asset resolution
        if (!is_dir(base_path('public'))) {
            @mkdir(base_path('public'), 0755, true);
        }

        if (!is_dir(public_path())) {
            if (is_dir(base_path('../public_html'))) {
                $this->app->usePublicPath(realpath(base_path('../public_html')));
            } elseif (is_dir(base_path('public_html'))) {
                $this->app->usePublicPath(realpath(base_path('public_html')));
            }
        }

        // Guarantee DomPDF always has a valid, existing directory
        $resolvedPublic = is_dir(public_path()) ? public_path() : (realpath(base_path('public')) ?: base_path());
        config(['dompdf.public_path' => $resolvedPublic]);

        // Laravel db fix
        Schema::defaultStringLength(191);

        Paginator::useBootstrap();

        Model::preventLazyLoading(config('app.eager_load'));

        Model::handleLazyLoadingViolationUsing(function ($model, $relation) {
            if (config('logging.default') == 'sentry') {
                \Sentry\Laravel\Integration::lazyLoadingViolationReporter();
            } else {
                $class = get_class($model);

                report("Attempted to lazy load [{$relation}] on model [{$class}].");
            }
        });
    }
}
