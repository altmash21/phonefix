<?php

namespace App\Abstracts\View\Components\Documents\Concerns;

use App\Models\Document\Document;

trait HasFormItemsAndTemplates
{
    protected function getHideItems($type, $hideItems, $hideItemName, $hideItemDescription)
    {
        if (! empty($hideItems)) {
            return $hideItems;
        }

        $hide = $this->getHideFromConfig($type, 'items');

        if ($hide) {
            return $hide;
        }

        $hideItems = ($this->getHideItemName($type, $hideItemName) & $this->getHideItemDescription($type, $hideItemDescription)) ? true  : false;

        return $hideItems;
    }

    protected function getHideItemName($type, $hideItemName): bool
    {
        if (! empty($hideItemName)) {
            return $hideItemName;
        }

        $hide = $this->getHideFromConfig($type, 'name');

        if ($hide) {
            return $hide;
        }

        return false;
    }

    protected function getHideSettingItemName($type, $hideSettingItemName): bool
    {
        if (! empty($hideSettingItemName)) {
            return $hideSettingItemName;
        }

        $hideItemName = setting($this->getDocumentSettingKey($type, 'item_name'), false);

        // if you use setting translation
        if ($hideItemName === 'hide') {
            return true;
        }

        return false;
    }

    protected function getTextItemName($type, $textItemName)
    {
        if (! empty($textItemName)) {
            return $textItemName;
        }

        // if you use setting translation
        if (setting($this->getDocumentSettingKey($type, 'item_name'), 'items') === 'custom') {
            if (empty($textItemName = setting($this->getDocumentSettingKey($type, 'item_name_input')))) {
                $textItemName = 'general.items';
            }

            return $textItemName;
        }

        if (setting($this->getDocumentSettingKey($type, 'item_name')) !== null
            && (trans(setting($this->getDocumentSettingKey($type, 'item_name'))) != setting($this->getDocumentSettingKey($type, 'item_name')))
        ) {
            return setting($this->getDocumentSettingKey($type, 'item_name'));
        }

        $translation = $this->getTextFromConfig($type, 'items');

        if (! empty($translation)) {
            return $translation;
        }

        return 'general.items';
    }

    protected function getHideItemDescription($type, $hideItemDescription): bool
    {
        if (! empty($hideItemDescription)) {
            return $hideItemDescription;
        }

        $hide = $this->getHideFromConfig($type, 'description');

        if ($hide) {
            return $hide;
        }

        return false;
    }

    protected function getHideSettingItemDescription($type, $hideSettingItemDescription): bool
    {
        if (! empty($hideSettingItemDescription)) {
            return $hideSettingItemDescription;
        }

        // if you use setting translation
        if (setting($this->getDocumentSettingKey($type, 'hide_item_description'), false)) {
            return true;
        }

        return false;
    }

    protected function getTextItemDescription($type, $textItemDescription)
    {
        if (! empty($textItemDescription)) {
            return $textItemDescription;
        }

        $translation = $this->getTextFromConfig($type, 'description');

        if (! empty($translation)) {
            return $translation;
        }

        return 'general.description';
    }

    protected function getHideItemQuantity($type, $hideItemQuantity): bool
    {
        if (! empty($hideItemQuantity)) {
            return $hideItemQuantity;
        }

        $hide = $this->getHideFromConfig($type, 'quantity');

        if ($hide) {
            return $hide;
        }

        return false;
    }

    protected function getHideSettingItemQuantity($type, $hideSettingItemQuantity): bool
    {
        if (! empty($hideSettingItemQuantity)) {
            return $hideSettingItemQuantity;
        }

        $hideItemQuantity = setting($this->getDocumentSettingKey($type, 'quantity_name'), false);

        // if you use setting translation
        if ($hideItemQuantity === 'hide') {
            return true;
        }

        return false;
    }

    protected function getTextItemQuantity($type, $textItemQuantity)
    {
        if (! empty($textItemQuantity)) {
            return $textItemQuantity;
        }

        // if you use setting translation
        if (setting($this->getDocumentSettingKey($type, 'quantity_name'), 'quantity') === 'custom') {
            if (empty($textItemQuantity = setting($this->getDocumentSettingKey($type, 'quantity_name_input')))) {
                $textItemQuantity = 'invoices.quantity';
            }

            return $textItemQuantity;
        }

        if (setting($this->getDocumentSettingKey($type, 'quantity_name')) !== null
            && (trans(setting($this->getDocumentSettingKey($type, 'quantity_name'))) != setting($this->getDocumentSettingKey($type, 'quantity_name')))
        ) {
            return setting($this->getDocumentSettingKey($type, 'quantity_name'));
        }

        $translation = $this->getTextFromConfig($type, 'quantity');

        if (! empty($translation)) {
            return $translation;
        }

        return 'invoices.quantity';
    }

    protected function getHideItemPrice($type, $hideItemPrice): bool
    {
        if (! empty($hideItemPrice)) {
            return $hideItemPrice;
        }

        $hide = $this->getHideFromConfig($type, 'price');

        if ($hide) {
            return $hide;
        }

        return false;
    }

    protected function getHideSettingItemPrice($type, $hideSettingItemPrice): bool
    {
        if (! empty($hideSettingItemPrice)) {
            return $hideSettingItemPrice;
        }

        $hideItemPrice = setting($this->getDocumentSettingKey($type, 'price_name'), false);

        // if you use setting translation
        if ($hideItemPrice === 'hide') {
            return true;
        }

        return false;
    }

    protected function getTextItemPrice($type, $textItemPrice)
    {
        if (! empty($textItemPrice)) {
            return $textItemPrice;
        }

        // if you use setting translation
        if (setting($this->getDocumentSettingKey($type, 'price_name'), 'price') === 'custom') {
            if (empty($textItemPrice = setting($this->getDocumentSettingKey($type, 'price_name_input')))) {
                $textItemPrice = 'invoices.price';
            }

            return $textItemPrice;
        }

        if (setting($this->getDocumentSettingKey($type, 'price_name')) !== null
            && (trans(setting($this->getDocumentSettingKey($type, 'price_name'))) != setting($this->getDocumentSettingKey($type, 'price_name')))
        ) {
            return setting($this->getDocumentSettingKey($type, 'price_name'));
        }

        $translation = $this->getTextFromConfig($type, 'price');

        if (! empty($translation)) {
            return $translation;
        }

        return 'invoices.price';
    }

    protected function getHideItemAmount($type, $hideItemAmount): bool
    {
        if (! empty($hideItemAmount)) {
            return $hideItemAmount;
        }

        $hide = $this->getHideFromConfig($type, 'amount');

        if ($hide) {
            return $hide;
        }

        return false;
    }

    protected function getHideSettingItemAmount($type, $hideSettingItemAmount): bool
    {
        if (! empty($hideSettingItemAmount)) {
            return $hideSettingItemAmount;
        }

        // if you use setting translation
        if (setting($this->getDocumentSettingKey($type, 'hide_amount'), false)) {
            return true;
        }

        return false;
    }

    protected function getTextItemAmount($type, $textItemAmount)
    {
        if (!empty($textItemAmount)) {
            return $textItemAmount;
        }

        $translation = $this->getTextFromConfig($type, 'amount');

        if (!empty($translation)) {
            return $translation;
        }

        return 'general.amount';
    }

    protected function getHideDiscount($type, $hideDiscount)
    {
        if (! empty($hideDiscount)) {
            return $hideDiscount;
        }

        // if you use setting translation
        if ($hideDiscount = setting($this->getDocumentSettingKey($type, 'hide_discount'), false)) {
            return $hideDiscount;
        }

        $hide = $this->getHideFromConfig($type, 'discount');

        if ($hide) {
            return $hide;
        }

        // @todo what return value invoice or always false??
        return setting('invoice.hide_discount', $hideDiscount);
    }

    protected function getSearchCharLimit($type, $searchCharLimit)
    {
        if (! empty($searchCharLimit)) {
            return $searchCharLimit;
        }

        // if you use setting translation
        if ($settingCharLimit = setting($this->getDocumentSettingKey($type, 'item_search_chart_limit'), false)) {
            return $settingCharLimit;
        }

        $hide = $this->getHideFromConfig($type, 'item_search_char_limit');

        if ($hide) {
            return $hide;
        }

        // @todo what return value invoice or always false??
        return setting('invoice.item_search_char_limit', $searchCharLimit);
    }

    protected function getNotesValue($notes)
    {
        if (! empty($notes)) {
            return $notes;
        }

        if (! empty($this->document)) {
            return $this->document->notes;
        }

        return setting($this->getDocumentSettingKey($this->type, 'notes'));
    }

    protected function getTextSectionAdvancedTitle($type, $textSectionAdvancedTitle)
    {
        if (! empty($textSectionAdvancedTitle)) {
            return $textSectionAdvancedTitle;
        }

        return $this->getTextSectionTitle($type, 'advanced', 'documents.advanced');
    }

    protected function getTextSectionAdvancedDescription($type, $textSectionAdvancedDescription)
    {
        if (! empty($textSectionAdvancedDescription)) {
            return $textSectionAdvancedDescription;
        }

        return $this->getTextSectionDescription($type, 'advanced', 'documents.form_description.advanced');
    }

    protected function getTitleValue($type, $title)
    {
        if (! empty($title)) {
            return $title;
        }

        if (! empty($this->document) && $this->document->title !== '') {
            return $this->document->title;
        }

        return setting($this->getDocumentSettingKey($type, 'title'));
    }

    protected function getSubheadingValue($type, $subheading)
    {
        if (! empty($subheading)) {
            return $subheading;
        }

        if (! empty($this->document) && $this->document->title !== '') {
            return $this->document->subheading;
        }

        return setting($this->getDocumentSettingKey($type, 'subheading'));
    }

    protected function getFooterValue($footer)
    {
        if (! empty($footer)) {
            return $footer;
        }

        if (! empty($this->document)) {
            return $this->document->footer;
        }

        return setting($this->getDocumentSettingKey($this->type, 'footer'));
    }

    protected function getTypeCategory($type, $typeCategory)
    {
        if (!empty($typeCategory)) {
            return $typeCategory;
        }

        if ($category_type = config('type.' . static::OBJECT_TYPE . '.' . $type . '.category_type')) {
            return $category_type;
        }

        // set default type
        $type = Document::INVOICE_TYPE;

        return config('type.' . static::OBJECT_TYPE .'.' . $type . '.category_type');
    }

    protected function getCategoryId($type, $categoryId)
    {
        if (!empty($categoryId)) {
            return $categoryId;
        }

        if (! empty($this->document) && ! empty($this->document->category_id)) {
            return $this->document->category_id;
        }

        return setting('default.' . $this->typeCategory . '_category');
    }

    protected function getTemplates($type, $templates)
    {
        if (! empty($templates)) {
            return $templates;
        }

        $templates = $this->getDocumentTemplates($type);

        return $templates;
    }   

    protected function getDocumentTemplate($type, $documentTemplate)
    {
        if (! empty($documentTemplate)) {
            return $documentTemplate;
        }

        if (! empty($this->document) && ! empty($this->document->template)) {
            return $this->document->template;
        }

        if ($template = config('type.document.' . $type . '.template', false)) {
            return $template;
        }

        $documentTemplate = setting($this->getDocumentSettingKey($type, 'template'), 'default');

        return $documentTemplate;
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
