<?php

namespace App\Policies\MobileShop;

use App\Models\Auth\User;

class MobileStockPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->can('read-admin-panel') && ($user->isOwner() || $user->hasRole('admin') || $user->hasRole('owner') || $user->hasRole('store-admin'))) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        if ($this->before($user, 'viewAny')) {
            return true;
        }

        return $user->can('read-mobileshop-stock')
            || $user->can('create-mobileshop-stock')
            || $user->can('manage-stock-phones')
            || $user->hasRole('sales-staff')
            || $user->hasRole('secondhand-staff');
    }

    public function create(User $user): bool
    {
        if ($this->before($user, 'create')) {
            return true;
        }

        return $user->can('create-mobileshop-stock')
            || $user->can('manage-stock-phones')
            || $user->can('create-mobileshop-pos')
            || $user->can('create-mobileshop-secondhand')
            || $user->hasRole('sales-staff')
            || $user->hasRole('secondhand-staff');
    }

    public function update(User $user): bool
    {
        if ($this->before($user, 'update')) {
            return true;
        }

        return $user->can('update-mobileshop-stock')
            || $user->can('create-mobileshop-stock')
            || $user->can('manage-stock-phones');
    }

    public function delete(User $user): bool
    {
        if ($this->before($user, 'delete')) {
            return true;
        }

        return $user->can('delete-mobileshop-stock');
    }
}
