<?php

namespace App\Traits\ViewComponents;

use Akaunting\Module\Module;
use App\Events\Common\BulkActionsAdding;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait HasViewBulkAndUi
{
    protected function getSearchStringModel($type, $searchStringModel)
    {
        if (! empty($searchStringModel)) {
            return $searchStringModel;
        }

        $search_string_model = config('type.' . static::OBJECT_TYPE . '.' . $type . '.search_string_model');

        if (! empty($search_string_model)) {
            return $search_string_model;
        }

        if ($group = config('type.' . static::OBJECT_TYPE . '.' . $type . '.group')) {
            $group = Str::studly(Str::singular($group)) . '\\';
        }

        $prefix = Str::studly(Str::singular(config('type.' . static::OBJECT_TYPE . '.' . $type . '.route.prefix')));

        if ($alias = config('type.' . static::OBJECT_TYPE . '.' . $type . '.alias')) {
            $searchStringModel = 'Modules\\' . Str::studly($alias) .'\Models\\' . $group . $prefix;
        } else {
            $searchStringModel = 'App\Models\\' . $group . $prefix;
        }

        return $searchStringModel;
    }

    protected function getBulkActionClass($type, $bulkActionClass)
    {
        if (! empty($bulkActionClass)) {
            return $bulkActionClass;
        }

        $bulk_actions = config('type.' . static::OBJECT_TYPE . '.' . $type . '.bulk_actions');

        if (! empty($bulk_actions)) {
            return $bulk_actions;
        }

        $file_name = '';

        if ($group = config('type.' . static::OBJECT_TYPE . '.' . $type . '.group')) {
            $file_name .= Str::studly($group) . '\\';
        }

        if ($prefix = config('type.' . static::OBJECT_TYPE . '.' . $type . '.route.prefix')) {
            $file_name .= Str::studly($prefix);
        }

        if ($alias = config('type.' . static::OBJECT_TYPE . '.' . $type . '.alias')) {
            $module = module($alias);

            if (! $module instanceof Module) {
                $b = new \stdClass();
                $b->actions = [];

                event(new BulkActionsAdding($b));

                return $b->actions;
            }

            $bulkActionClass = 'Modules\\' . $module->getStudlyName() . '\BulkActions\\' . $file_name;
        } else {
            $bulkActionClass = 'App\BulkActions\\' .  $file_name;
        }

        return $bulkActionClass;
    }

    protected function getBulkActionRouteParameters($type, $bulkActionRouteParameters)
    {
        if (! empty($bulkActionRouteParameters)) {
            return $bulkActionRouteParameters;
        }

        $group = config('type.' . static::OBJECT_TYPE . '.' . $type . '.group');

        if (! empty(config('type.' . static::OBJECT_TYPE . '.' . $type . '.alias'))) {
            $group = config('type.' . static::OBJECT_TYPE . '.' . $type . '.alias');
        }

        $bulkActionRouteParameters = [
            'group' => $group,
            'type' => config('type.' . static::OBJECT_TYPE . '.' . $type . '.route.prefix')
        ];

        return $bulkActionRouteParameters;
    }

    protected function getClassBulkAction($type, $classBulkAction)
    {
        if (! empty($classBulkAction)) {
            return $classBulkAction;
        }

        $class = $this->getClassFromConfig($type, 'bulk_action');

        if (! empty($class)) {
            return $class;
        }

        return 'ltr:pr-6 rtl:pl-6 hidden sm:table-cell';
    }

    protected function getImageEmptyPage($type, $imageEmptyPage)
    {
        if (! empty($imageEmptyPage)) {
            return $imageEmptyPage;
        }

        $image_empty_page = config('type.' . static::OBJECT_TYPE . '.' . $type . '.image_empty_page');

        if (! empty($image_empty_page)) {
            return $image_empty_page;
        }

        $page = str_replace('-', '_', config('type.' . static::OBJECT_TYPE . '.' . $type . '.route.prefix', 'invoices'));
        $image_path = 'public/img/empty_pages/' . $page . '.png';

        if ($alias = config('type.' . static::OBJECT_TYPE . '.' . $type . '.alias')) {
            $image_path = 'modules/' . Str::studly($alias) . '/Resources/assets/img/empty_pages/' . $page . '.png';
        }

        return $image_path;
    }

    protected function getTextEmptyPage($type, $textEmptyPage)
    {
        if (! empty($textEmptyPage)) {
            return $textEmptyPage;
        }

        $page = str_replace('-', '_', config('type.' . static::OBJECT_TYPE . '.' . $type . '.route.prefix', 'invoices'));

        $translation = $this->getTextFromConfig($type, 'empty_page', 'empty.' . $page);

        if (! empty($translation)) {
            return $translation;
        }

        return 'general.empty.' . $page;
    }

    protected function getTextSectionTitle($type, $key, $default_key = '')
    {
        $translation = $this->getTextFromConfig($type, 'section_'. $key . '_title', $key);

        if (! empty($translation)) {
            return $translation;
        }

        if ($default_key) {
            return $default_key;
        }

        return 'general.' . $key;
    }

    protected function getTextSectionDescription($type, $key, $default_key = '')
    {
        $translation = $this->getTextFromConfig($type, 'section_'. $key . '_description', 'form_description.' . $key);

        if (! empty($translation)) {
            return $translation;
        }

        if ($default_key) {
            return $default_key;
        }

        return 'general.form_description.' . $key;
    }

    protected function getUrlDocsPath($type, $urlDocsPath)
    {
        if (! empty($urlDocsPath)) {
            return $urlDocsPath;
        }

        $docs_path = config('type.' . static::OBJECT_TYPE . '.' . $type . '.docs_path');

        if (! empty($docs_path)) {
            return $docs_path;
        }

        switch ($type) {
            case 'bill':
            case 'expense':
            case 'purchase':
                $docsPath = 'purchases/bills';
                break;
            case 'vendor':
                $docsPath = 'purchases/vendors';
                break;
            case 'customer':
                $docsPath = 'sales/customers';
                break;
            case 'transaction':
                $docsPath = 'banking/transactions';
                break;
            default:
                $docsPath = 'sales/invoices';
                break;
        }

        return 'https://akaunting.com/docs/user-manual/' . $docsPath;
    }

    public function getSuggestionModule()
    {
        return !empty($this->suggestions) ? Arr::random($this->suggestions) : false;
    }

    public function getSuggestionModules()
    {
        if ((! $user = user()) || $user->cannot('read-modules-home')) {
            return [];
        }

        if (! $path = Route::current()->uri()) {
            return [];
        }

        $path = str_replace('{company_id}/', '', $path);

        if (! $suggestions = $this->getSuggestions($path)) {
            return [];
        }

        $modules = [];

        foreach ($suggestions->modules as $s_module) {
            if ($this->moduleIsEnabled($s_module->alias)) {
                continue;
            }

            $s_module->action_url = company_id() . '/' . $s_module->action_url;

            $modules[] = $s_module;
        }

        if (empty($modules)) {
            return [];
        }

        return $modules;
    }

}
