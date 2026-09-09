<?php

namespace App\Traits\Permissions;

use App\Utilities\Reports;
use App\Utilities\Widgets;
use Illuminate\Support\Str;

trait HasModulePermissions
{
    public function attachDefaultModulePermissions($module, $require = null)
    {
        $this->attachModuleReportPermissions($module, $require);

        $this->attachModuleWidgetPermissions($module, $require);

        $this->attachModuleSettingPermissions($module, $require);
    }

    public function attachModuleReportPermissions($module, $require = null)
    {
        if (is_string($module)) {
            $module = module($module);
        }

        if (empty($module->get('reports'))) {
            return;
        }

        $permissions = [];

        foreach ($module->get('reports') as $class) {
            if (!class_exists($class)) {
                continue;
            }

            $permissions[] = $this->createModuleReportPermission($module, $class);
        }

        $require
                ? $this->attachPermissionsToAllRoles($permissions, $require)
                : $this->attachPermissionsToAdminRoles($permissions);
    }

    public function attachModuleWidgetPermissions($module, $require = null)
    {
        if (is_string($module)) {
            $module = module($module);
        }

        if (empty($module->get('widgets'))) {
            return;
        }

        $permissions = [];

        foreach ($module->get('widgets') as $class) {
            if (!class_exists($class)) {
                continue;
            }

            $permissions[] = $this->createModuleWidgetPermission($module, $class);
        }

        $require
                ? $this->attachPermissionsToAllRoles($permissions, $require)
                : $this->attachPermissionsToAdminRoles($permissions);
    }

    public function attachModuleSettingPermissions($module, $require = null)
    {
        if (is_string($module)) {
            $module = module($module);
        }

        if (empty($module->get('settings'))) {
            return;
        }

        $permissions = [];

        $permissions[] = $this->createModuleSettingPermission($module, 'read');
        $permissions[] = $this->createModuleSettingPermission($module, 'update');

        $require
                ? $this->attachPermissionsToAllRoles($permissions, $require)
                : $this->attachPermissionsToAdminRoles($permissions);
    }

    public function createModuleReportPermission($module, $class)
    {
        if (is_string($module)) {
            $module = module($module);
        }

        if (!class_exists($class)) {
            return;
        }

        $name = Reports::getPermission($class);
        $display_name = 'Read ' . $module->getName() . ' Reports ' . Reports::getDefaultName($class);

        return $this->createPermission($name, $display_name);
    }

    public function createModuleWidgetPermission($module, $class)
    {
        if (is_string($module)) {
            $module = module($module);
        }

        if (!class_exists($class)) {
            return;
        }

        $name = Widgets::getPermission($class);
        $display_name = 'Read ' . $module->getName() . ' Widgets ' . Widgets::getDefaultName($class);

        return $this->createPermission($name, $display_name);
    }

    public function createModuleSettingPermission($module, $action)
    {
        return $this->createModuleControllerPermission($module, $action, 'settings');
    }

    public function createModuleControllerPermission($module, $action, $controller)
    {
        if (is_string($module)) {
            $module = module($module);
        }

        $name = $action . '-' . $module->getAlias() . '-' . $controller;
        $display_name = Str::title($action) . ' ' . $module->getName() . ' ' . Str::title($controller);

        return $this->createPermission($name, $display_name);
    }


}
