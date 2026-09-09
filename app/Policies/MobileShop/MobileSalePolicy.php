<?php

namespace App\Policies\MobileShop;

use App\Models\Auth\User;

class MobileSalePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->can('read-admin-panel') && ($user->isOwner() || $user->hasRole('admin') || $user->hasRole('owner'))) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        if ($this->before($user, 'viewAny')) {
            return true;
        }

        return $user->can('read-mobileshop-sales')
            || $user->can('create-mobileshop-sales')
            || $user->can('read-mobileshop-pos');
    }

    public function create(User $user): bool
    {
        if ($this->before($user, 'create')) {
            return true;
        }

        return $user->can('create-mobileshop-sales')
            || $user->can('create-sale-phones')
            || $user->can('create-mobileshop-pos')
            || $user->can('create-sale-mobiles');
    }

    public function void(User $user): bool
    {
        if ($this->before($user, 'void')) {
            return true;
        }

        return $user->can('void-mobileshop-sales');
    }

    public function printInvoice(User $user): bool
    {
        if ($this->before($user, 'printInvoice')) {
            return true;
        }

        return $user->can('read-mobileshop-sales')
            || $user->can('create-mobileshop-sales')
            || $user->can('read-mobileshop-pos');
    }
}
