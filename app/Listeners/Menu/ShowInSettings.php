<?php

namespace App\Listeners\Menu;

use App\Events\Menu\SettingsCreated as Event;
use App\Traits\Permissions;

class ShowInSettings
{
    use Permissions;

    /**
     * Handle the event.
     *
     * @param  $event
     * @return void
     */
    public function handle(Event $event)
    {
        $menu = $event->menu;

        $title = trim(trans_choice('general.companies', 1));
        if ($this->canAccessMenuItem($title, 'read-settings-company')) {
            $menu->route('settings.company.edit', $title, [], 10, ['icon' => 'business', 'search_keywords' => trans('settings.company.search_keywords')]);
        }

        $title = trim(trans_choice('general.localisations', 1));
        if ($this->canAccessMenuItem($title, 'read-settings-localisation')) {
            $menu->route('settings.localisation.edit', $title, [], 20, ['icon' => 'flag', 'search_keywords' => trans('settings.localisation.search_keywords')]);
        }

        // MobiTrack Masters / Store Profile
        if ($this->canAccessMenuItem('Store Masters', 'read-mobileshop-masters')) {
            $menu->route('mobileshop.masters', 'Store Masters', [], 30, ['icon' => 'store']);
        }
    }
}
