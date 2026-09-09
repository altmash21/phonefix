<?php

namespace App\Traits\Import;

use App\Events\Common\ImportViewCreating;
use App\Events\Common\ImportViewCreated;
use Akaunting\Module\Module;

trait HasImportViewConfig
{
    public function getImportView($group, $type, $route = null)
    {
        // Get the view path
        $view = $this->getImportViewPath($group, $type);

        // Get import blade variables
        $path = $this->getImportPath($group, $type);
        $title_type = $this->getImportTitleType($group, $type);
        $sample_file = $this->getImportSampleFile($group, $type);
        $form_params = $this->getImportFormParams($group, $type, $path, $route);
        $document_link = $this->getImportDocumentLink($group, $type);

        // Create the import view
        $import = new \stdClass();
        $import->view = $view;
        $import->data = compact('group', 'type', 'route', 'path', 'title_type', 'sample_file', 'form_params', 'document_link');

        event(new ImportViewCreating($import));

        event(new ImportViewCreated($import));

        return [
            $import->view,
            $import->data
        ];
    }


    protected function getImportPath($group, $type)
    {
        $path = config('import.' . $group . '.' . $type . '.path');

        if (! empty($path)) {
            return str_replace('company_id', company_id(), $path);
        }

        return company_id() . '/' . $group . '/' . $type;
    }

    protected function getImportTitleType($group, $type)
    {
        $title_type = config('import.' . $group . '.' . $type . '.title_type');

        if (! empty($title_type)) {
            return $this->findTranslation($title_type);
        }

        $module = module($group);

        $title_type = trans_choice('general.' . str_replace('-', '_', $type), 2);

        if ($module instanceof Module) {
            $title_type = trans_choice($group . '::general.' . str_replace('-', '_', $type), 2);
        }

        return $title_type;
    }

    protected function getImportSampleFile($group, $type)
    {
        $sample_file = config('import.' . $group . '.' . $type . '.sample_file');

        if (! empty($sample_file)) {
            return url($sample_file);
        }

        $module = module($group);

        $sample_file = url('public/files/import/' . $type . '.xlsx');

        if ($module instanceof Module) {
            $sample_file = url('modules/' . $module->getStudlyName() . '/Resources/assets/' . $type . '.xlsx');
        }

        return $sample_file;
    }

    protected function getImportFormParams($group, $type, $path = null, $route = null)
    {
        $form_params = config('import.' . $group . '.' . $type . '.form_params');

        if (! empty($form_params)) {
            return $form_params;
        }

        $form_params = [
            'id' => 'import',
            '@submit.prevent' => 'onSubmit',
            '@keydown' => 'form.errors.clear($event.target.name)',
            'files' => true,
            'role' => 'form',
            'class' => 'form-loading-button',
            'novalidate' => true,
            'route' => '',
            'url' => '',
        ];

        if (! empty($route)) {
            $form_params['route'] = $route;
        } else {
            $form_params['url'] = $path . '/import';
        }

        return $form_params;
    }

    protected function getImportDocumentLink($group, $type)
    {
        $document_link = config('import.' . $group . '.' . $type . '.document_link');

        if (! empty($document_link)) {
            return $document_link;
        }

        $document_link = 'https://akaunting.com/hc/docs/import-export/';

        return $document_link;
    }

    protected function getImportViewPath($group, $type)
    {
        $view = config('import.' . $group . '.' . $type . '.view');

        if (! empty($view)) {
            return $view;
        }

        $view = 'common.import.create';

        return $view;
    }
}
