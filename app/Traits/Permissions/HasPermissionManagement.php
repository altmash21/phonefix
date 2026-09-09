<?php

namespace App\Traits\Permissions;

use App\Models\Auth\Permission;
use App\Models\Auth\Role;
use Illuminate\Routing\Route;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait HasPermissionManagement
{
    public function createRole($name, $display_name = null, $description = null)
    {
        $alias = !empty($this->alias) ? $this->alias : $name;

        if (empty($display_name)) {
            $display_name = $this->findTranslation([
                'auth.roles.' . Str::replace('-', '_', $name) . '.name',
                $alias . '::permissions.roles.' . Str::replace('-', '_', $name) . '.name',
                $alias . '::auth.roles.' . Str::replace('-', '_', $name) . '.name',
            ]);

            if (empty($display_name)) {
                $display_name = Str::title(Str::replace('-', ' ', $name));
            }
        }

        if (empty($description)) {
            $description = $this->findTranslation([
                'auth.roles.' . Str::replace('-', '_', $name) . '.description',
                $alias . '::permissions.roles.' . Str::replace('-', '_', $name) . '.description',
                $alias . '::auth.roles.' . Str::replace('-', '_', $name) . '.description',
            ]);

            if (empty($description)) {
                $description = $display_name;
            }
        }

        return role_model_class()::firstOrCreate([
            'name' => $name,
        ], [
            'display_name' => $display_name,
            'description' => $description,
        ]);
    }

    public function createPermission($name, $display_name = null, $description = null)
    {
        $display_name = $display_name ?? $this->getPermissionDisplayName($name);

        return Permission::firstOrCreate([
            'name' => $name,
        ], [
            'display_name' => $display_name,
            'description' => $description ?? $display_name,
        ]);
    }

    public function attachPermission($role, $permission)
    {
        if (is_string($permission)) {
            $permission = $this->createPermission($permission);
        }

        if ($role->hasPermission($permission->name)) {
            return;
        }

        $role->attachPermission($permission);
    }

    public function detachPermission($role, $permission, $delete = true)
    {
        if (is_string($role)) {
            $role = role_model_class()::where('name', $role)->first();
        }

        if (empty($role)) {
            return;
        }

        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->first();
        }

        if (empty($permission)) {
            return;
        }

        if ($role->hasPermission($permission->name)) {
            $role->detachPermission($permission);
        }

        if ($delete === false) {
            return;
        }

        $permission->delete();
    }


    public function assignPermissionsToController()
    {
        // No need to check for permission in console
        if (app()->runningInConsole()) {
            return;
        }

        $table = request_is_api() ? request()->segment(2) : '';

        // Find the proper controller for common API endpoints
        if (in_array($table, ['contacts', 'documents'])) {
            $controller = '';

            // Look for type in search variable like api/contacts?search=type:customer
            $type = $this->getSearchStringValue('type');

            if (! empty($type)) {
                $alias = config('type.' . Str::singular($table) . '.' . $type . '.alias');
                $group = config('type.' . Str::singular($table) . '.' . $type . '.group');
                $prefix = config('type.' . Str::singular($table) . '.' . $type . '.permission.prefix');

                // if use module set module alias
                if (! empty($alias)) {
                    $controller .= $alias . '-';
                }

                // if controller in folder it must
                if (! empty($group)) {
                    $controller .= $group . '-';
                }

                $controller .= $prefix;
            }
        } else {
            $route = app(Route::class);

            // Get the controller array
            $arr = array_reverse(explode('\\', explode('@', $route->getAction()['uses'])[0]));

            $controller = '';

            // Add module
            if (isset($arr[3]) && isset($arr[4])) {
                if (strtolower($arr[4]) == 'modules') {
                    $controller .= Str::kebab($arr[3]) . '-';
                } elseif (isset($arr[5]) && (strtolower($arr[5]) == 'modules')) {
                    $controller .= Str::kebab($arr[4]) . '-';
                }
            }

            // Add folder
            if (! in_array(strtolower($arr[1]), ['api', 'controllers'])) {
                $controller .= Str::kebab($arr[1]) . '-';
            }

            // Add file
            $controller .= Str::kebab($arr[0]);

            // Skip ACL
            $skip = ['portal-dashboard'];
            if (in_array($controller, $skip)) {
                return;
            }

            // App\Http\Controllers\FooBar                  -->> foo-bar
            // App\Http\Controllers\FooBar\Main             -->> foo-bar-main
            // Modules\Blog\Http\Controllers\Posts          -->> blog-posts
            // Modules\Blog\Http\Controllers\Portal\Posts   -->> blog-portal-posts
        }

        // Add CRUD permission check
        $this->middleware('permission:create-' . $controller)->only('create', 'store', 'duplicate', 'import');
        $this->middleware('permission:read-' . $controller)->only('index', 'show', 'edit', 'export');
        $this->middleware('permission:update-' . $controller)->only('update', 'enable', 'disable', 'markSent', 'markCancelled', 'markReceived', 'markApproved', 'markRefused', 'restoreInvoice', 'restoreBill', 'end');
        $this->middleware('permission:delete-' . $controller)->only('destroy');
    }

    public function canAccessMenuItem($title, $permissions)
    {
        $permissions = Arr::wrap($permissions);

        $item = new \stdClass();
        $item->title = $title;
        $item->permissions = $permissions;

        event(new \App\Events\Menu\ItemAuthorizing($item));

        return user()->canAny($item->permissions);
    }
}
