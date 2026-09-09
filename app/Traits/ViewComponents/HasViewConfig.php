<?php

namespace App\Traits\ViewComponents;

use App\Models\Setting\Category;
use Illuminate\Support\Str;

trait HasViewConfig
{
    public function setParentData()
    {
        $excludes = [
            'componentName',
            'attributes',
            'except',
        ];

        foreach ($this as $key => $value) {
            if (in_array($key, $excludes)) {
                continue;
            }

            $this->{$key} = $this->getParentData($key, $value);
        }
    }

    public function getTextFromConfig($type, $config_key, $default_key = '', $trans_type = 'trans')
    {
        $translation = '';

        // if set config translation config_key
        if ($translation = config('type.' . static::OBJECT_TYPE . '.' . $type . '.translation.' . $config_key)) {
            return $translation;
        }

        $alias = config('type.' . static::OBJECT_TYPE . '.' . $type . '.alias');
        $prefix = config('type.' . static::OBJECT_TYPE . '.' . $type . '.translation.prefix');

        if (! empty($alias)) {
            $alias .= '::';
        }

        // This magic trans key..
        $translations = [
            'general' => $alias . 'general.' . $default_key,
            'prefix' => $alias . $prefix . '.' . $default_key,
            'config_general' => $alias . 'general.' . $config_key,
            'config_prefix' => $alias . $prefix . '.' . $config_key,
        ];

        switch ($trans_type) {
            case 'trans':
                foreach ($translations as $trans) {
                    if (is_string(trans($trans)) && trans($trans) !== $trans) {
                        return $trans;
                    }
                }

                break;
            case 'trans_choice':
                foreach ($translations as $trans_choice) {
                    if (is_string(trans($trans_choice)) && trans_choice($trans_choice, 1) !== $trans_choice) {
                        return $trans_choice;
                    }
                }

                break;
        }

        return $translation;
    }

    public function getRouteFromConfig($type, $config_key, $config_parameters = [], $modal = false)
    {
        $route = '';

        // if set config translation config_key
        if ($route = config('type.' . static::OBJECT_TYPE . '.' . $type . '.route.' . $config_key)) {
            return $route;
        }

        $alias = config('type.' . static::OBJECT_TYPE . '.' . $type . '.alias');
        $prefix = config('type.' . static::OBJECT_TYPE . '.' . $type . '.route.prefix');

        // if use module set module alias
        if (! empty($alias)) {
            $route .= $alias . '.';
        }

        if ($modal == true) {
            $route .= 'modals.';
        }

        if (! empty($prefix)) {
            $route .= $prefix . '.';
        }

        $route .= $config_key;

        try {
            route($route, $config_parameters);
        } catch (\Exception $e) {
            try {
                $route = Str::plural($type, 2) . '.' . $config_key;

                route($route, $config_parameters);
            } catch (\Exception $e) {
                $route = '';
            }
        }

        return $route;
    }

    public function getRouteParamsFromConfig($type, $config_key)
    {
        $params = config('type.' . static::OBJECT_TYPE . '.' . $type . '.route.params.' . $config_key, []);

        return $params;
    }

    public function getPermissionFromConfig($type, $config_key)
    {
        $permission = '';

        // if set config translation config_key
        if ($permission = config('type.' . static::OBJECT_TYPE . '.' . $type . '.permission.' . $config_key)) {
            return $permission;
        }

        $alias = config('type.' . static::OBJECT_TYPE . '.' . $type . '.alias');
        $group = config('type.' . static::OBJECT_TYPE . '.' . $type . '.group');
        $prefix = config('type.' . static::OBJECT_TYPE . '.' . $type . '.permission.prefix');

        $permission = $config_key . '-';

        // if use module set module alias
        if (! empty($alias)) {
            $permission .= $alias . '-';
        }

        // if controller in folder it must
        if (! empty($group)) {
            $permission .= $group . '-';
        }

        $permission .= $prefix;

        return $permission;
    }

    public function getHideFromConfig($type, $config_key)
    {
        $hide = false;

        $hides = config('type.' . static::OBJECT_TYPE . '.' . $type . '.hide');

        if (! empty($hides) && (in_array($config_key, $hides))) {
            $hide = true;
        }

        return $hide;
    }

    public function getClassFromConfig($type, $config_key)
    {
        $class_key = 'type.' . $type . '.class.' . $config_key;

        return config($class_key, '');
    }

    public function getCategoryFromConfig($type)
    {
        $category_type = '';

        // if set config translation config_key
        if ($category_type = config('type.' . static::OBJECT_TYPE . '.' . $type . '.category_type')) {
            return $category_type;
        }

        switch ($type) {
            case 'bill':
            case 'expense':
            case 'purchase':
                $category_type = Category::EXPENSE_TYPE;
                break;
            case 'item':
                $category_type = Category::ITEM_TYPE;
                break;
            case 'other':
                $category_type = Category::OTHER_TYPE;
                break;
            case 'transfer':
                $category_type = 'transfer';
                break;
            default:
                $category_type = Category::INCOME_TYPE;
                break;
        }

        return $category_type;
    }

    public function getScriptFromConfig($type, $config_key)
    {
        $script_key = config('type.' . static::OBJECT_TYPE . '.' . $type . '.script.' . $config_key, '');

        return $script_key;
    }

    public function getTabActiveFromSetting($type)
    {
        $tabs = setting('favorites.tab.' . user()->id, []);

        if (empty($tabs)) {
            return false;
        }

        $tabs = json_decode($tabs, true);

        return $tabs[$type] ?? false;
    }

    protected function getTextPage($type, $textPage)
    {
        if (! empty($textPage)) {
            return $textPage;
        }

        $config_route_prefix = config('type.' . static::OBJECT_TYPE . '.' . $type . '.route.prefix', static::DEFAULT_PLURAL_TYPE);

        $page = str_replace('-', '_', $config_route_prefix);

        $translation = $this->getTextFromConfig($type, 'page', $page);

        if (! empty($translation)) {
            return $translation;
        }

        return 'general.' . $page;
    }

    protected function getGroup($type, $group)
    {
        if (! empty($group)) {
            return $group;
        }

        return config('type.' . static::OBJECT_TYPE . '.' . $type . '.group', static::DEFAULT_PLURAL_TYPE);
    }

    protected function getPage($type, $page)
    {
        if (! empty($page)) {
            return $page;
        }

        return Str::plural($type);
    }

    protected function getPermissionCreate($type, $permissionCreate)
    {
        if (! empty($permissionCreate)) {
            return $permissionCreate;
        }

        $permissionCreate = $this->getPermissionFromConfig($type, 'create');

        return $permissionCreate;
    }

    protected function getPermissionUpdate($type, $permissionUpdate)
    {
        if (! empty($permissionUpdate)) {
            return $permissionUpdate;
        }

        $permissionUpdate = $this->getPermissionFromConfig($type, 'update');

        return $permissionUpdate;
    }

    protected function getPermissionDelete($type, $permissionDelete)
    {
        if (! empty($permissionDelete)) {
            return $permissionDelete;
        }

        $permissionDelete = $this->getPermissionFromConfig($type, 'delete');

        return $permissionDelete;
    }

}
