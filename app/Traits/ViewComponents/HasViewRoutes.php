<?php

namespace App\Traits\ViewComponents;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait HasViewRoutes
{
    protected function getIndexRoute($type, $indexRoute)
    {
        if (! empty($indexRoute)) {
            return $indexRoute;
        }

        $route = $this->getRouteFromConfig($type, 'index');

        if (!empty($route)) {
            return $route;
        }

        return static::DEFAULT_PLURAL_TYPE . '.index';
    }

    protected function getShowRoute($type, $showRoute)
    {
        if (! empty($showRoute)) {
            return $showRoute;
        }

        $route = $this->getRouteFromConfig($type, 'show', 1);

        if (!empty($route)) {
            return $route;
        }

        return static::DEFAULT_PLURAL_TYPE . '.show';
    }

    protected function getCreateRoute($type, $createRoute)
    {
        if (! empty($createRoute)) {
            return $createRoute;
        }

        $route = $this->getRouteFromConfig($type, 'create');

        if (! empty($route)) {
            return $route;
        }

        return static::DEFAULT_PLURAL_TYPE . '.create';
    }

    protected function getEditRoute($type, $editRoute)
    {
        if (! empty($editRoute)) {
            return $editRoute;
        }

        $route = $this->getRouteFromConfig($type, 'edit', 1);

        if (! empty($route)) {
            return $route;
        }

        return static::DEFAULT_PLURAL_TYPE . '.edit';
    }

    protected function getDuplicateRoute($type, $duplicateRoute)
    {
        if (! empty($duplicateRoute)) {
            return $duplicateRoute;
        }

        $route = $this->getRouteFromConfig($type, 'duplicate', 1);

        if (! empty($route)) {
            return $route;
        }

        return static::DEFAULT_PLURAL_TYPE . '.duplicate';
    }

    protected function getDeleteRoute($type, $deleteRoute)
    {
        if (! empty($deleteRoute)) {
            return $deleteRoute;
        }

        $route = $this->getRouteFromConfig($type, 'destroy', 1);

        if (! empty($route)) {
            return $route;
        }

        return static::DEFAULT_PLURAL_TYPE . '.destroy';
    }

    protected function getCancelRoute($type, $cancelRoute)
    {
        if (! empty($cancelRoute)) {
            return $cancelRoute;
        }

        $route = $this->getRouteFromConfig($type, 'index');

        if (! empty($route)) {
            return $route;
        }

        return static::DEFAULT_PLURAL_TYPE . '.index';
    }

    protected function getImportRoute($importRoute)
    {
        if (! empty($importRoute)) {
            return $importRoute;
        }

        $route = 'import.create';

        return $route;
    }

    protected function getImportRouteParameters($type, $importRouteParameters)
    {
        if (! empty($importRouteParameters)) {
            return $importRouteParameters;
        }

        $alias = config('type.' . static::OBJECT_TYPE . '.' . $type . '.alias');
        $group = config('type.' . static::OBJECT_TYPE . '.' . $type . '.group');
        $prefix = config('type.' . static::OBJECT_TYPE . '.' . $type . '.route.prefix');

        if (empty($group) && ! empty($alias)){
            $group = $alias;
        } else if (empty($group) && empty($alias)) {
            $group = 'sales';
        }

        $importRouteParameters = [
            'group' => $group,
            'type' => $prefix,
        ];

        return $importRouteParameters;
    }

    protected function getExportRoute($type, $exportRoute)
    {
        if (! empty($exportRoute)) {
            return $exportRoute;
        }

        $route = $this->getRouteFromConfig($type, 'export');

        if (! empty($route)) {
            return $route;
        }

        return static::DEFAULT_PLURAL_TYPE . '.export';
    }


    protected function getFormRoute($type, $formRoute, $model = false)
    {
        if (! empty($formRoute)) {
            return $formRoute;
        }

        $prefix = 'store';
        $parameters = [];

        if (! empty($model)) {
            $prefix = 'update';
            $parameters = [$model->id];
        }

        $route = $this->getRouteFromConfig($type, $prefix, $parameters);

        return (! empty($model)) ? [$route, $model->id] : $route;
    }

    protected function getFormMethod($type, $formMethod, $model = false)
    {
        if (! empty($formMethod)) {
            return $formMethod;
        }

        $method = 'POST';

        if (! empty($model)) {
            $method = 'PATCH';
        }

        return $method;
    }

    protected function getAlias($type, $alias)
    {
        if (!empty($alias)) {
            return $alias;
        }

        if ($alias = config('type.' . static::OBJECT_TYPE . '.' . $type . '.alias')) {
            return $alias;
        }

        return 'core';
    }

    protected function getScriptFolder($type, $folder)
    {
        if (!empty($folder)) {
            return $folder;
        }

        if ($folder = config('type.' . static::OBJECT_TYPE . '.' . $type . '.script.folder')) {
            return $folder;
        }

        return '';
    }

    protected function getScriptFile($type, $file)
    {
        if (!empty($file)) {
            return $file;
        }

        if ($file = config('type.' . static::OBJECT_TYPE . '.' . $type . '.script.file')) {
            return $file;
        }

        return '';
    }
}
