<?php

namespace App\Listeners\Menu;

use App\Events\Menu\NewwCreated as Event;
use App\Traits\Permissions;

class ShowInNeww
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

        // New Mobile Sale / POS
        if ($this->canAccessMenuItem('New Sale', 'create-sale-phones')) {
            $menu->route('mobileshop.sales', 'New Sale', [], 10, ['icon' => 'point_of_sale']);
        }

        // New Purchase Entry
        if ($this->canAccessMenuItem('New Purchase', 'create-purchase-phones')) {
            $menu->route('mobileshop.purchase', 'New Purchase Entry', [], 20, ['icon' => 'add_shopping_cart']);
        }

        // New Stock / Accessory
        if ($this->canAccessMenuItem('Add Stock', 'manage-stock-phones')) {
            $menu->route('mobileshop.stock', 'Add Stock', [], 30, ['icon' => 'add_box']);
        }

        // New Repair Job Sheet
        if ($this->canAccessMenuItem('New Repair', 'manage-stock-repairs')) {
            $menu->route('mobileshop.repairs', 'New Repair Job', [], 40, ['icon' => 'build']);
        }
    }
}
