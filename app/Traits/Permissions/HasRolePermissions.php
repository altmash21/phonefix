<?php

namespace App\Traits\Permissions;

use App\Models\Auth\Role;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait HasRolePermissions
{
    public function getActionsMap()
    {
        return [
            'c' => 'create',
            'r' => 'read',
            'u' => 'update',
            'd' => 'delete',
        ];
    }

    public function attachPermissionsByRoleNames($roles)
    {
        foreach ($roles as $role_name => $permissions) {
            $role = $this->createRole($role_name);

            foreach ($permissions as $id => $permission) {
                $this->attachPermissionsByAction($role, $id, $permission);
            }
        }
    }

    public function attachPermissionsToAdminRoles($permissions)
    {
        $this->applyPermissionsToRoles($this->getDefaultAdminRoles(), 'attach', $permissions);
    }

    public function attachPermissionsToPortalRoles($permissions)
    {
        $this->applyPermissionsToRoles($this->getDefaultPortalRoles(), 'attach', $permissions);
    }

    public function attachPermissionsToAllRoles($permissions, $require = 'read-admin-panel')
    {
        $this->applyPermissionsToRoles($this->getRoles($require), 'attach', $permissions);
    }

    public function detachPermissionsByRoleNames($roles)
    {
        foreach ($roles as $role_name => $permissions) {
            foreach ($permissions as $permission_name) {
                $this->detachPermission($role_name, $permission_name);
            }
        }
    }

    public function detachPermissionsFromAdminRoles($permissions)
    {
        $this->applyPermissionsToRoles($this->getDefaultAdminRoles(), 'detach', $permissions);
    }

    public function detachPermissionsFromPortalRoles($permissions)
    {
        $this->applyPermissionsToRoles($this->getDefaultPortalRoles(), 'detach', $permissions);
    }

    public function detachPermissionsFromAllRoles($permissions, $require = 'read-admin-panel')
    {
        $this->applyPermissionsToRoles($this->getRoles($require), 'detach', $permissions, $require);
    }

    public function applyPermissionsToRoles($roles, $apply, $permissions)
    {
        $roles->each(function ($role) use ($apply, $permissions) {
            $f1 = $apply . 'PermissionsByAction';
            $f2 = $apply . 'Permission';

            foreach ($permissions as $id => $permission) {
                if ($this->isActionList($permission)) {
                    $this->$f1($role, $id, $permission);

                    continue;
                }

                $this->$f2($role, $permission);
            }
        });
    }

    public function updatePermissionNames($permissions)
    {
        $actions = $this->getActionsMap();

        foreach ($permissions as $old => $new) {
            foreach ($actions as $action) {
                $old_name = $action . '-' . $old;

                $permission = Permission::where('name', $old_name)->first();

                if (empty($permission)) {
                    continue;
                }

                $new_name = $action . '-' . $new;
                $new_display_name = $this->getPermissionDisplayName($new_name);

                $permission->update([
                    'name' => $new_name,
                    'display_name' => $new_display_name,
                ]);
            }
        }
    }


    public function isActionList($permission)
    {
        if (!is_string($permission)) {
            return false;
        }

        // c || c,r,u,d
        return (Str::length($permission) == 1) || Str::contains($permission, ',');
    }

    public function attachPermissionsByAction($role, $page, $action_list)
    {
        $this->applyPermissionsByAction('attach', $role, $page, $action_list);
    }

    public function detachPermissionsByAction($role, $page, $action_list)
    {
        $this->applyPermissionsByAction('detach', $role, $page, $action_list);
    }

    public function applyPermissionsByAction($apply, $role, $page, $action_list)
    {
        $function = $apply . 'Permission';

        $actions_map = collect($this->getActionsMap());

        $actions = explode(',', $action_list);

        foreach ($actions as $short_action) {
            $action = $actions_map->get($short_action);

            $name = $action . '-' . $page;

            $this->$function($role, $name);
        }
    }

    public function getPermissionDisplayName($name)
    {
        if (!empty($this->alias)) {
            $name = str_replace($this->alias, '{Module Placeholder}', $name);
        }

        $name = Str::title(str_replace('-', ' ', $name));

        if (!empty($this->alias)) {
            $name = str_replace('{Module Placeholder}', module($this->alias)->getName(), $name);
        }

        return $name;
    }

    public function getRoles($require = 'read-admin-panel')
    {
        return role_model_class()::all()->filter(function ($role) use ($require) {
            return $require ? $role->hasPermission($require) : true;
        });
    }

    public function getDefaultAdminRoles($custom = null)
    {
        $roles = role_model_class()::whereIn('name', $custom ?? ['admin', 'manager'])->get();

        if ($roles->isNotEmpty()) {
            return $roles;
        }

        return $this->getRoles('read-admin-panel');
    }

    public function getDefaultPortalRoles($custom = null)
    {
        $roles = role_model_class()::whereIn('name', $custom ?? ['customer'])->get();

        if ($roles->isNotEmpty()) {
            return $roles;
        }

        return $this->getRoles('read-client-portal');
    }

    /**
     * Assign permissions middleware to default controller methods.
     *
     * @return void
     */

}
