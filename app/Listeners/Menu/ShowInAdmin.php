<?php

namespace App\Listeners\Menu;

use App\Traits\Permissions;
use App\Events\Menu\AdminCreated as Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class ShowInAdmin
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

        $attr = ['icon' => ''];

        // MobiTrack ERP Dashboard
        if ($this->canAccessMenuItem('Dashboard', 'read-mobileshop-dashboard')) {
            $inactive = ('mobileshop.dashboard' != Route::currentRouteName()) ? true : false;
            $menu->route('mobileshop.dashboard', 'MobiTrack Dashboard', [], 10, ['icon' => 'speed', 'inactive' => $inactive]);
        }

        // Sales Hub
        if ($this->canAccessMenuItem('Sales', 'read-mobileshop-sales')) {
            $menu->route('mobileshop.sales', 'Sales Hub', [], 20, ['icon' => 'payments']);
        }

        // Purchase Hub
        if ($this->canAccessMenuItem('Purchase', 'read-mobileshop-purchase')) {
            $menu->route('mobileshop.purchase', 'Purchase Hub', [], 30, ['icon' => 'shopping_cart']);
        }

        // Stock Hub
        if ($this->canAccessMenuItem('Stock', 'read-mobileshop-stock')) {
            $menu->route('mobileshop.stock', 'Stock & Inventory', [], 40, ['icon' => 'inventory_2']);
        }

        // Reports
        if ($this->canAccessMenuItem('Reports', 'read-mobileshop-reports')) {
            $menu->route('mobileshop.reports', 'Reports & Khata', [], 50, ['icon' => 'donut_small']);
        }

        // Masters (Admin Only)
        if ($this->canAccessMenuItem('Masters', 'read-mobileshop-masters')) {
            $menu->route('mobileshop.masters', 'Masters & Settings', [], 60, ['icon' => 'tune']);
        }
    }
}
