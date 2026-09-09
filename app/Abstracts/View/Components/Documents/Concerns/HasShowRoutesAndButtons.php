<?php

namespace App\Abstracts\View\Components\Documents\Concerns;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

trait HasShowRoutesAndButtons
{
    protected function getTextCreate($type, $textCreate)
    {
        if (! empty($textCreate)) {
            return $textCreate;
        }

        return trans('general.new') . ' ' . ucfirst($type);
    }

    protected function getHideButtonStatuses($type, $hideButtonStatuses)
    {
        if (! empty($hideButtonStatuses)) {
            return $hideButtonStatuses;
        }

        $hideButtonStatuses = ['paid', 'cancelled'];

        if ($button_statuses = config('type.' . static::OBJECT_TYPE . '.' . $type . '.button_statuses')) {
            $hideButtonStatuses = $button_statuses;
        }

        return $hideButtonStatuses;
    }

    protected function getPrintRoute($type, $printRoute)
    {
        if (! empty($printRoute)) {
            return $printRoute;
        }

        return $this->getRouteFromConfig($type, 'print', $this->document->id);
    }

    protected function getShareRoute($type, $shareRoute)
    {
        if (! empty($shareRoute)) {
            return $shareRoute;
        }

        return $this->getRouteFromConfig($type, 'share', $this->document->id, true);
    }

    protected function getSignedUrl($type, $signedUrl)
    {
        if (! empty($signedUrl)) {
            return $signedUrl;
        }

        $route = config('type.' . static::OBJECT_TYPE . '.' . $type . '.route.signed');

        if (empty($route)) {
            return $signedUrl;
        }

        $company_id = (! empty($this->document) && ! empty($this->document->company_id)) ? $this->document->company_id : company_id();

        return URL::signedRoute($route, [$company_id, $this->document->id]);
    }

    protected function getEmailRoute($type, $emailRoute)
    {
        if (! empty($emailRoute)) {
            return $emailRoute;
        }

        return $this->getRouteFromConfig($type, 'email', $this->document->id, true);
    }

    protected function getTextEmail($type, $textEmail)
    {
        if (! empty($textEmail)) {
            return $textEmail;
        }

        return $this->getTextFromConfig($type, 'send', 'send');
    }

    protected function getPdfRoute($type, $pdfRoute)
    {
        if (! empty($pdfRoute)) {
            return $pdfRoute;
        }

        return $this->getRouteFromConfig($type, 'pdf', $this->document->id);
    }

    protected function getCancelledRoute($type, $cancelledRoute)
    {
        if (! empty($cancelledRoute)) {
            return $cancelledRoute;
        }

        return $this->getRouteFromConfig($type, 'cancelled', $this->document->id);
    }

    protected function getRestoreRoute($type, $restoreRoute)
    {
        if (! empty($restoreRoute)) {
            return $restoreRoute;
        }

        return $this->getRouteFromConfig($type, 'restore', $this->document->id);
    }

    protected function getPermissionCustomize($type, $permissionCustomize)
    {
        if (! empty($permissionCustomize)) {
            return $permissionCustomize;
        }

        return $this->getPermissionFromConfig($type, 'customize');
    }

    protected function getCustomizeRoute($type, $customizeRoute)
    {
        if (! empty($customizeRoute)) {
            return $customizeRoute;
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

            return $this->getRouteFromConfig($type, 'customize', ['template' => $template], true);
        }

        return $this->getRouteFromConfig($type, 'customize', ['template' => setting($this->getDocumentSettingKey($type, 'template'), 'default')], true);
    }

    protected function getEndRoute($type, $endRoute)
    {
        if (! empty($endRoute)) {
            return $endRoute;
        }

        return $this->getRouteFromConfig($type, 'end', $this->document->id);
    }

    protected function getAccordionActive($type, $accordionActive)
    {
        if (! empty($accordionActive)) {
            return $accordionActive;
        }

        if ($this->document->status == 'cancelled') {
            return 'cancelled';
        }

        return config('type.' . static::OBJECT_TYPE . '.' . $type . '.accordion_active', false);
    }

    protected function getTextRecurringType($type, $textRecurringType)
    {
        if (! empty($textRecurringType)) {
            return $textRecurringType;
        }

        $type = Str::replace('-recurring', '', $type);

        return $this->getTextFromConfig($type, 'title', $type);
    }

    protected function getTextStatusMessage($type, $textStatusMessage)
    {
        if (! empty($textStatusMessage)) {
            return $textStatusMessage;
        }

        $alias = config('type.' . static::OBJECT_TYPE . '.' . $type . '.alias');
        $prefix = config('type.' . static::OBJECT_TYPE . '.' . $type . '.route.prefix');

        return ! empty($alias) ? $alias . '::' . $prefix . '.messages.status.' : $prefix . '.messages.status.';
    }

    protected function getTextMarkSent($type, $textMarkSent)
    {
        if (! empty($textMarkSent)) {
            return $textMarkSent;
        }

        $text = $this->getTextFromConfig($type, 'mark_sent', 'mark_sent');

        if (! empty($text)) {
            return $text;
        }

        $alias = config('type.' . static::OBJECT_TYPE . '.' . $type . '.alias');
        $prefix = config('type.' . static::OBJECT_TYPE . '.' . $type . '.route.prefix');

        $text = ! empty($alias) ? $alias . '::' . $prefix . '.mark_sent' : $prefix . '.mark_sent';

        if (trans($text) != $text) {
            return $text;
        }

        return 'general.mark_sent';
    }

    protected function getMarkSentRoute($type, $markSentRoute)
    {
        if (! empty($markSentRoute)) {
            return $markSentRoute;
        }

        return $this->getRouteFromConfig($type, 'sent', $this->document->id);
    }

    protected function getTextMarkReceived($type, $textMarkReceived)
    {
        if (! empty($textMarkReceived)) {
            return $textMarkReceived;
        }

        $text = $this->getTextFromConfig($type, 'mark_received', 'mark_received');

        if (! empty($text)) {
            return $text;
        }

        $alias = config('type.' . static::OBJECT_TYPE . '.' . $type . '.alias');
        $prefix = config('type.' . static::OBJECT_TYPE . '.' . $type . '.route.prefix');

        $text = ! empty($alias) ? $alias . '::' . $prefix . '.mark_received' : $prefix . '.mark_received';

        if (trans($text) != $text) {
            return $text;
        }

        return 'general.mark_received';
    }

    protected function getMarkReceivedRoute($type, $markReceivedRoute)
    {
        if (! empty($markReceivedRoute)) {
            return $markReceivedRoute;
        }

        return $this->getRouteFromConfig($type, 'received', $this->document->id);
    }

    protected function getTransactionEmailRoute($type, $transactionEmailRoute)
    {
        if (! empty($transactionEmailRoute)) {
            return $transactionEmailRoute;
        }

        return config('type.' . static::OBJECT_TYPE . '.' . $type . '.transaction.email_route', false);
    }

    protected function getTransactionEmailTemplate($type, $transactionEmailTemplate)
    {
        if (! empty($transactionEmailTemplate)) {
            return $transactionEmailTemplate;
        }

        return config('type.' . static::OBJECT_TYPE . '.' . $type . '.transaction.email_template', false);
    }

    protected function getHideRestore($hideRestore)
    {
        if (! empty($hideRestore)) {
            return $hideRestore;
        }

        $hideRestore = true;

        if ($this->document->status == 'cancelled') {
            $hideRestore = false;
        }

        return $hideRestore;
    }
}
