<?php

namespace App\Abstracts\View\Components\Transactions\Concerns;

use App\Models\Common\Media;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Intervention\Image\Exception\NotReadableException;

trait HasTransactionShowRoutes
{
    protected function getTransactionTemplate($type, $transactionTemplate)
    {
        if (! empty($transactionTemplate)) {
            return $transactionTemplate;
        }

        if ($template = config('type.transaction.' . $type . '.template', false)) {
            return $template;
        }

        $transactionTemplate = setting($this->getTransactionSettingKey($type, 'template')) ?: 'default';

        return $transactionTemplate;
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

        $media_id = (!empty($this->transaction->contact->logo) && !empty($this->transaction->contact->logo->id)) ? $this->transaction->contact->logo->id : setting('company.logo');

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
            Log::info('Company ID: ' . company_id() . ' components/transactionshow.php exception.');
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

    protected function getRouteButtonAddNew($type, $routeButtonAddNew)
    {
        if (! empty($routeButtonAddNew)) {
            return $routeButtonAddNew;
        }

        //example route parameter.
        $parameter = 1;

        $route = $this->getRouteFromConfig($type, 'create', $parameter);

        if (! empty($route)) {
            return $route;
        }

        return 'transactions.create';
    }

    protected function getRouteButtonEdit($type, $routeButtonEdit)
    {
        if (! empty($routeButtonEdit)) {
            return $routeButtonEdit;
        }

        //example route parameter.
        $parameter = 1;

        $route = $this->getRouteFromConfig($type, 'edit', $parameter);

        if (! empty($route)) {
            return $route;
        }

        return 'transactions.edit';
    }

    protected function getRouteButtonDuplicate($type, $routeButtonDuplicate)
    {
        if (! empty($routeButtonDuplicate)) {
            return $routeButtonDuplicate;
        }

        //example route parameter.
        $parameter = 1;

        $route = $this->getRouteFromConfig($type, 'duplicate', $parameter);

        if (! empty($route)) {
            return $route;
        }

        return 'transactions.duplicate';
    }

    protected function getRouteButtonPrint($type, $routeButtonPrint)
    {
        if (! empty($routeButtonPrint)) {
            return $routeButtonPrint;
        }

        //example route parameter.
        $parameter = 1;

        $route = $this->getRouteFromConfig($type, 'print', $parameter);

        if (! empty($route)) {
            return $route;
        }

        return 'transactions.print';
    }

    protected function getShareRoute($type, $shareRoute)
    {
        if (! empty($shareRoute)) {
            return $shareRoute;
        }

        //example route parameter.
        $parameter = 1;

        $route = $this->getRouteFromConfig($type, 'share', $parameter);

        if (! empty($route)) {
            return $route;
        }

        return 'modals.transactions.share.create';
    }

    protected function getSignedUrl($type, $signedUrl)
    {
        if (! empty($signedUrl)) {
            return $signedUrl;
        }

        $page = config('type.transaction.' . $type . '.route.prefix');
        $alias = config('type.transaction.' . $type . '.alias');

        $route = '';

        if (! empty($alias)) {
            $route .= $alias . '.';
        }

        $route .= 'signed.' . $page . '.show';

        try {
            route($route, [$this->transaction->id, 'company_id' => company_id()]);

            $signedUrl = URL::signedRoute($route, [$this->transaction->id]);
        } catch (\Exception $e) {
            $signedUrl = URL::signedRoute('signed.payments.show', [$this->transaction->id]);
        }

        return $signedUrl;
    }

    protected function getRouteButtonEmail($type, $routeButtonEmail)
    {
        if (! empty($routeButtonEmail)) {
            return $routeButtonEmail;
        }

        //example route parameter.
        $parameter = 1;

        $route = $this->getRouteFromConfig($type, 'emails.create', $parameter, true);

        if (! empty($route)) {
            return $route;
        }

        return 'modals.transactions.emails.create';
    }

    protected function getRouteButtonPdf($type, $routeButtonPdf)
    {
        if (! empty($routeButtonPdf)) {
            return $routeButtonPdf;
        }

        //example route parameter.
        $parameter = 1;

        $route = $this->getRouteFromConfig($type, 'pdf', $parameter);

        if (! empty($route)) {
            return $route;
        }

        return 'transactions.pdf';
    }

    protected function getRouteButtonEnd($type, $routeButtonEnd)
    {
        if (! empty($routeButtonEnd)) {
            return $routeButtonEnd;
        }

        //example route parameter.
        $parameter = 1;

        $route = $this->getRouteFromConfig($type, 'end', $parameter);

        if (! empty($route)) {
            return $route;
        }

        return 'recurring-transactions.end';
    }

    protected function getRouteButtonDelete($type, $routeButtonDelete)
    {
        if (! empty($routeButtonDelete)) {
            return $routeButtonDelete;
        }

        //example route parameter.
        $parameter = 1;

        $route = $this->getRouteFromConfig($type, 'destroy', $parameter);

        if (! empty($route)) {
            return $route;
        }

        return 'transactions.destroy';
    }

    protected function getRouteContactShow($type, $routeContactShow)
    {
        if (! empty($routeContactShow)) {
            return $routeContactShow;
        }

        //example route parameter.
        $parameter = 1;

        $route = Str::plural(config('type.transaction.' . $type . '.contact_type'), 2) . '.show';

        try {
            route($route, $parameter);
        } catch (\Exception $e) {
            try {
                $route = Str::plural($type, 2) . '.' . $config_key;

                route($route, $parameter);
            } catch (\Exception $e) {
                $route = '';
            }
        }

        if (! empty($route)) {
            return $route;
        }

        return 'customers.show';
    }

}
