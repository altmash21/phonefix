<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as Provider;

class Event extends Provider
{
    /**
     * The event listener mappings for the application.
     * MobiTrack: Stripped to auth, menu, module, banking & settings only.
     *
     * @var array
     */
    protected $listen = [
        // Auth
        \Illuminate\Auth\Events\Login::class => [
            \App\Listeners\Auth\Login::class,
        ],
        \Illuminate\Auth\Events\Logout::class => [
            \App\Listeners\Auth\Logout::class,
        ],
        \Illuminate\Routing\Events\PreparingResponse::class => [
            \App\Listeners\Common\PreparingResponse::class,
        ],
        \Illuminate\Console\Events\CommandStarting::class => [
            \App\Listeners\Common\SkipScheduleInReadOnlyMode::class,
        ],
        \App\Events\Auth\LandingPageShowing::class => [
            \App\Listeners\Auth\AddLandingPages::class,
        ],

        // Menu
        \App\Events\Menu\NotificationsCreated::class => [
            \App\Listeners\Menu\ShowInNotifications::class,
        ],
        \App\Events\Menu\AdminCreated::class => [
            \App\Listeners\Menu\ShowInAdmin::class,
        ],
        \App\Events\Menu\ProfileCreated::class => [
            \App\Listeners\Menu\ShowInProfile::class,
        ],
        \App\Events\Menu\SettingsCreated::class => [
            \App\Listeners\Menu\ShowInSettings::class,
        ],
        \App\Events\Menu\NewwCreated::class => [
            \App\Listeners\Menu\ShowInNeww::class,
        ],

        // Module lifecycle
        \App\Events\Module\Installed::class => [
            \App\Listeners\Module\FinishInstallation::class,
        ],
        \App\Events\Module\Uninstalled::class => [
            \App\Listeners\Module\FinishUninstallation::class,
        ],

        // Banking (transaction numbering still used by MobileShopController)
        \App\Events\Banking\TransactionCreated::class => [
            \App\Listeners\Banking\IncreaseNextTransactionNumber::class,
        ],

        // Settings
        \App\Events\Setting\CategoryDeleted::class => [
            \App\Listeners\Setting\DeleteCategoryDeletedSubCategories::class,
        ],
    ];

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        \App\Listeners\Common\ClearPlansCache::class,
        \App\Listeners\Module\ClearCache::class,
    ];
}
