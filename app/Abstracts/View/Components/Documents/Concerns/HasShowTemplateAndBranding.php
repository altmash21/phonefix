<?php

namespace App\Abstracts\View\Components\Documents\Concerns;

use App\Models\Common\Media;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Exception\NotReadableException;
use Image;

trait HasShowTemplateAndBranding
{
    protected function getDocumentTemplate($type, $documentTemplate)
    {
        if (! empty($documentTemplate)) {
            return $documentTemplate;
        }

        if (! empty($this->document) && ! empty($this->document->template)) {
            $document_templates = $this->getDocumentTemplates($type);

            $template = 'default';

            foreach ($document_templates as $template) {
                if ($template['id'] != $this->document->template) {
                    continue;
                }

                $template =  $template['template'];
                break;
            }

            return $template;
        }

        if ($template = config('type.' . static::OBJECT_TYPE . '.' . $type . '.template', false)) {
            return $template;
        }

        $documentTemplate = setting($this->getDocumentSettingKey($type, 'template'), 'default');

        return $documentTemplate;
    }

    protected function getLogo($logo)
    {
        if (! empty($logo)) {
            return $logo;
        }

        static $content;

        if (! empty($content)) {
            return $content;
        }

        $media_id = (! empty($this->document->contact->logo) && ! empty($this->document->contact->logo->id)) ? $this->document->contact->logo->id : setting('company.logo');

        $media = Media::find($media_id);

        if (! empty($media)) {
            $path = $media->getDiskPath();

            if (! $media->fileExists()) {
                return $logo;
            }
        } else {
            $path = base_path('public/img/company.png');
        }

        try {
            $image = Image::cache(function($image) use ($media, $path) {
                $width = setting('invoice.logo_size_width');
                $height = setting('invoice.logo_size_height');

                if ($media) {
                    $image->make($media->contents())->resize($width, $height)->encode();
                } else {
                    $image->make($path)->resize($width, $height)->encode();
                }
            });
        } catch (NotReadableException | \Exception $e) {
            Log::info('Company ID: ' . company_id() . ' components/documentshow.php exception.');
            Log::info($e->getMessage());

            $path = base_path('public/img/company.png');

            $image = Image::cache(function($image) use ($path) {
                $width = setting('invoice.logo_size_width');
                $height = setting('invoice.logo_size_height');

                $image->make($path)->resize($width, $height)->encode();
            });
        }

        if (empty($image)) {
            return $logo;
        }

        $extension = File::extension($path);

        $content = 'data:image/' . $extension . ';base64,' . base64_encode($image);

        return $content;
    }

    protected function getBackgroundColor($type, $backgroundColor)
    {
        if (! empty($backgroundColor)) {
            return $backgroundColor;
        }

        if (! empty($this->document) && $this->document->color !== '') {
            return $this->getHexCodeOfTailwindClass($this->document->color);
        }

        // checking setting color
        $key = $this->getDocumentSettingKey($type, 'color');

        if (! empty(setting($key))) {
            $backgroundColor = setting($key);
        }

        // checking config color
        if (empty($backgroundColor) && $background_color = config('type.document.' . $type . '.color', false)) {
            $backgroundColor = $background_color;
        }

        // set default color
        if (empty($backgroundColor)) {
            $backgroundColor = '#55588b';
        }

        return $this->getHexCodeOfTailwindClass($backgroundColor);
    }
}
