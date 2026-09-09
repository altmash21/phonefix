<?php

namespace App\Abstracts\View\Components\Documents\Concerns;

use Illuminate\Support\Str;

trait HasShowDocumentLabels
{
    protected function getTextDocumentTitle($type, $textDocumentTitle)
    {
        if (! empty($textDocumentTitle)) {
            return $textDocumentTitle;
        }

        if (! empty($this->document) && $this->document->title !== '') {
            return $this->document->title;
        }

        $key = $this->getDocumentSettingKey($type, 'title');

        if (! empty(setting($key))) {
            return setting($key);
        }

        $translation = $this->getTextFromConfig($type, 'document_title', Str::plural($type));

        if (! empty($translation)) {
            return trans_choice($translation, 1);
        }

        return setting('invoice.title');
    }

    protected function getTextDocumentSubheading($type, $textDocumentSubheading)
    {
        if (! empty($textDocumentSubheading)) {
            return $textDocumentSubheading;
        }

        if (! empty($this->document) && $this->document->subheading !== '') {
            return $this->document->subheading;
        }

        $key = $this->getDocumentSettingKey($type, 'subheading');

        if (!empty(setting($key))) {
            return setting($key);
        }

        $translation = $this->getTextFromConfig($type, 'document_subheading', 'subheading');

        if (! empty($translation)) {
            return trans($translation);
        }

        return false;
    }

    protected function getTextDocumentNumber($type, $textDocumentNumber)
    {
        if (! empty($textDocumentNumber)) {
            return $textDocumentNumber;
        }

        switch ($type) {
            case 'bill':
            case 'expense':
            case 'purchase':
                $default_key = 'bill_number';
                break;
            default:
                $default_key = 'invoice_number';
                break;
        }

        $translation = $this->getTextFromConfig($type, 'document_number', $default_key);

        if (! empty($translation)) {
            return $translation;
        }

        return 'general.numbers';
    }

    protected function getTextOrderNumber($type, $textOrderNumber)
    {
        if (! empty($textOrderNumber)) {
            return $textOrderNumber;
        }

        $translation = $this->getTextFromConfig($type, 'order_number');

        if (! empty($translation)) {
            return $translation;
        }

        return 'invoices.order_number';
    }

    protected function getTextContactInfo($type, $textContactInfo)
    {
        if (! empty($textContactInfo)) {
            return $textContactInfo;
        }

        switch ($type) {
            case 'bill':
            case 'expense':
            case 'purchase':
                $default_key = 'bill_from';
                break;
            default:
                $default_key = 'bill_to';
                break;
        }

        $translation = $this->getTextFromConfig($type, 'contact_info', $default_key);

        if (! empty($translation)) {
            return $translation;
        }

        return 'invoices.bill_to';
    }

    protected function getTextIssuedAt($type, $textIssuedAt)
    {
        if (! empty($textIssuedAt)) {
            return $textIssuedAt;
        }

        switch ($type) {
            case 'bill':
            case 'expense':
            case 'purchase':
                $default_key = 'bill_date';
                break;
            default:
                $default_key = 'invoice_date';
                break;
        }

        $translation = $this->getTextFromConfig($type, 'issued_at', $default_key);

        if (! empty($translation)) {
            return $translation;
        }

        return 'invoices.invoice_date';
    }

    protected function getTextDueAt($type, $textDueAt)
    {
        if (! empty($textDueAt)) {
            return $textDueAt;
        }

        $translation = $this->getTextFromConfig($type, 'due_at', 'due_date');

        if (! empty($translation)) {
            return $translation;
        }

        return 'invoices.due_date';
    }

    protected function getTextItems($type, $textItems)
    {
        if (! empty($textItems)) {
            return $textItems;
        }

        // if you use settting translation
        if (setting($this->getDocumentSettingKey($type, 'item_name'), 'items') == 'custom') {
            if (empty($textItems = setting($this->getDocumentSettingKey($type, 'item_name_input')))) {
                $textItems = 'general.items';
            }

            return $textItems;
        }

        $translation = $this->getTextFromConfig($type, 'items');

        if (! empty($translation)) {
            return $translation;
        }

        return 'general.items';
    }

    protected function getTextQuantity($type, $textQuantity)
    {
        if (! empty($textQuantity)) {
            return $textQuantity;
        }

        // if you use settting translation
        if (setting($this->getDocumentSettingKey($type, 'quantity_name'), 'quantity') === 'custom') {
            if (empty($textQuantity = setting($this->getDocumentSettingKey($type, 'quantity_name_input')))) {
                $textQuantity = 'invoices.quantity';
            }

            return $textQuantity;
        }

        $translation = $this->getTextFromConfig($type, 'quantity');

        if (! empty($translation)) {
            return $translation;
        }

        return 'invoices.quantity';
    }

    protected function getTextPrice($type, $textPrice)
    {
        if (!empty($textPrice)) {
            return $textPrice;
        }

        // if you use settting translation
        if (setting($this->getDocumentSettingKey($type, 'price_name'), 'price') === 'custom') {
            if (empty($textPrice = setting($this->getDocumentSettingKey($type, 'price_name_input')))) {
                $textPrice = 'invoices.price';
            }

            return $textPrice;
        }

        $translation = $this->getTextFromConfig($type, 'price');

        if (! empty($translation)) {
            return $translation;
        }

        return 'invoices.price';
    }

    protected function getTextAmount($type, $textAmount)
    {
        if (! empty($textAmount)) {
            return $textAmount;
        }

        $translation = $this->getTextFromConfig($type, 'amount');

        if (! empty($translation)) {
            return $translation;
        }

        return 'general.amount';
    }

    protected function getHideItems($type, $hideItems, $hideName, $hideDescription)
    {
        if (! empty($hideItems)) {
            return $hideItems;
        }

        $hide = $this->getHideFromConfig($type, 'items');

        if ($hide) {
            return $hide;
        }

        $hideItems = ($this->getHideName($type, $hideName) & $this->getHideDescription($type, $hideDescription)) ? true  : false;

        return $hideItems;
    }

    protected function getHideName($type, $hideName)
    {
        if (! empty($hideName)) {
            return $hideName;
        }

        $hideName = setting($this->getDocumentSettingKey($type, 'item_name'), false);

        // if you use settting translation
        if ($hideName === 'hide') {
            return true;
        }

        $hide = $this->getHideFromConfig($type, 'name');

        if ($hide) {
            return $hide;
        }

        return false;
    }

    protected function getHideDescription($type, $hideDescription)
    {
        if (! empty($hideDescription)) {
            return $hideDescription;
        }

        // if you use settting translation
        if (setting($this->getDocumentSettingKey($type, 'hide_item_description'), false)) {
            return true;
        }

        $hide = $this->getHideFromConfig($type, 'description');

        if ($hide) {
            return $hide;
        }

        return false;
    }

    protected function getHideQuantity($type, $hideQuantity)
    {
        if (! empty($hideQuantity)) {
            return $hideQuantity;
        }

        $hideQuantity = setting($this->getDocumentSettingKey($type, 'quantity_name'), false);

        // if you use settting translation
        if ($hideQuantity === 'hide') {
            return true;
        }

        $hide = $this->getHideFromConfig($type, 'quantity');

        if ($hide) {
            return $hide;
        }

        return false;
    }

    protected function getHidePrice($type, $hidePrice)
    {
        if (! empty($hidePrice)) {
            return $hidePrice;
        }

        $hidePrice = setting($this->getDocumentSettingKey($type, 'price_name'), false);

        // if you use settting translation
        if ($hidePrice === 'hide') {
            return true;
        }

        $hide = $this->getHideFromConfig($type, 'price');

        if ($hide) {
            return $hide;
        }

        return false;
    }

    protected function getHideDiscount($type, $hideDiscount)
    {
        if (! empty($hideDiscount)) {
            return $hideDiscount;
        }

        // if you use settting translation
        if ($hideDiscount = setting($this->getDocumentSettingKey($type, 'hide_discount'), false)) {
            return $hideDiscount;
        }

        $hide = $this->getHideFromConfig($type, 'discount');

        if ($hide) {
            return $hide;
        }

        return setting('invoice.hide_discount', $hideDiscount);
    }

    protected function getHideAmount($type, $hideAmount)
    {
        if (! empty($hideAmount)) {
            return $hideAmount;
        }

        // if you use settting translation
        if (setting($this->getDocumentSettingKey($type, 'hide_amount'), false)) {
            return true;
        }

        $hide = $this->getHideFromConfig($type, 'amount');

        if ($hide) {
            return $hide;
        }

        return false;
    }
}
