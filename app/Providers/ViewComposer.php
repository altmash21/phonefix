<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider as Provider;
use Illuminate\Support\Facades\View;

class ViewComposer extends Provider
{
    /**
     * Register bindings in the container.
     * MobiTrack: removed document type and portal composers.
     */
    public function boot()
    {
        View::composer(
            ['components.layouts.admin.notifications'],
            'App\Http\ViewComposers\ReadOnlyNotification'
        );

        View::composer(
            ['components.layouts.admin.header'],
            'App\Http\ViewComposers\PlanLimits'
        );
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        //
    }
}
