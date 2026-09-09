<?php

namespace App\Abstracts\View\Components\Transactions\Concerns;

trait HasTransactionShowLabels
{
    protected function getTextButtonAddNew($type, $textButtonAddNew)
    {
        if (! empty($textButtonAddNew)) {
            return $textButtonAddNew;
        }

        $translation = $this->getTextFromConfig($type, 'transactions');

        if (! empty($translation)) {
            return trans('general.title.new', ['type' => trans_choice($translation, 1)]);
        }

        return trans('general.title.new', ['type' => trans_choice('general.' . Str::plural($type), 1)]);
    }

    protected function getTextContentTitle($type, $textContentTitle)
    {
        if (! empty($textContentTitle)) {
            return $textContentTitle;
        }

        switch ($type) {
            case 'bill':
            case 'expense':
            case 'purchase':
                $default_key = 'payment_made';
                break;
            default:
                $default_key = 'receipts';
                break;
        }

        $translation = $this->getTextFromConfig($type, $type . '_made', $default_key);

        if (! empty($translation)) {
            return $translation;
        }

        return 'general.receipts';
    }

    protected function getTextNumber($type, $textNumber)
    {
        if (! empty($textNumber)) {
            return $textNumber;
        }

        return 'general.numbers';
    }

    protected function getTextPaidAt($type, $textPaidAt)
    {
        if (! empty($textPaidAt)) {
            return $textPaidAt;
        }

        $translation = $this->getTextFromConfig($type, 'paid_at', 'date');

        if (! empty($translation)) {
            return $translation;
        }

        return 'general.date';
    }

    protected function getTextAccount($type, $textAccount)
    {
        if (! empty($textAccount)) {
            return $textAccount;
        }

        $translation = $this->getTextFromConfig($type, 'accounts', 'accounts', 'trans_choice');

        if (! empty($translation)) {
            return $translation;
        }

        return 'general.accounts';
    }

    protected function getTextCategory($type, $textCategory)
    {
        if (! empty($textCategory)) {
            return $textCategory;
        }

        $translation = $this->getTextFromConfig($type, 'categories', 'categories', 'trans_choice');

        if (! empty($translation)) {
            return $translation;
        }

        return 'general.categories';
    }

    protected function getTextPaymentMethods($type, $textPaymentMethods)
    {
        if (! empty($textPaymentMethods)) {
            return $textPaymentMethods;
        }

        $translation = $this->getTextFromConfig($type, 'payment_methods', 'payment_methods', 'trans_choice');

        if (! empty($translation)) {
            return $translation;
        }

        return 'general.payment_methods';
    }

    protected function getTextReference($type, $textReference)
    {
        if (! empty($textReference)) {
            return $textReference;
        }

        $translation = $this->getTextFromConfig($type, 'reference', 'reference');

        if (! empty($translation)) {
            return $translation;
        }

        return 'general.reference';
    }

    protected function getTextDescription($type, $textDescription)
    {
        if (! empty($textDescription)) {
            return $textDescription;
        }

        $translation = $this->getTextFromConfig($type, 'description', 'description');

        if (! empty($translation)) {
            return $translation;
        }

        return 'general.description';
    }

    protected function getTextAmount($type, $textAmount)
    {
        if (! empty($textAmount)) {
            return $textAmount;
        }

        $translation = $this->getTextFromConfig($type, 'amount', 'amount');

        if (! empty($translation)) {
            return $translation;
        }

        return 'general.amount';
    }

    protected function getTextPaidBy($type, $textPaidBy)
    {
        if (! empty($textPaidBy)) {
            return $textPaidBy;
        }

        switch ($type) {
            case 'bill':
            case 'expense':
            case 'purchase':
                $default_key = 'paid_to';
                break;
            default:
                $default_key = 'paid_by';
                break;
        }

        $translation = $this->getTextFromConfig($type, 'paid_to_by', $default_key);

        if (! empty($translation)) {
            return $translation;
        }

        return 'transactions.paid_by';
    }

    protected function getTextRelatedTransansaction($type, $textRelatedTransansaction)
    {
        if (! empty($textRelatedTransansaction)) {
            return $textRelatedTransansaction;
        }

        switch ($type) {
            case 'bill':
            case 'expense':
            case 'purchase':
                $default_key = 'related_bill';
                break;
            default:
                $default_key = 'related_invoice';
                break;
        }

        $translation = $this->getTextFromConfig($type, 'related_type', $default_key);

        if (! empty($translation)) {
            return $translation;
        }

        return 'transactions.related_invoice';
    }

    protected function getTextRelatedDocumentNumber($type, $textRelatedDocumentNumber)
    {
        if (! empty($textRelatedDocumentNumber)) {
            return $textRelatedDocumentNumber;
        }

        $translation = $this->getTextFromConfig($type, 'related_document_number', 'numbers');

        if (! empty($translation)) {
            return $translation;
        }

        return 'general.numbers';
    }

    protected function getTextRelatedContact($type, $textRelatedContact)
    {
        if (! empty($textRelatedContact)) {
            return $textRelatedContact;
        }

        $default_key = Str::plural(config('type.transaction.' . $type . '.contact_type'), 2);

        $translation = $this->getTextFromConfig($type, 'related_contact', $default_key, 'trans_choice');

        if (! empty($translation)) {
            return $translation;
        }

        return 'general.customers';
    }

    protected function getTextRelatedDocumentDate($type, $textRelatedDocumentDate)
    {
        if (! empty($textRelatedDocumentDate)) {
            return $textRelatedDocumentDate;
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

        $translation = $this->getTextFromConfig($type, 'related_document_date', $default_key);

        if (! empty($translation)) {
            return $translation;
        }

        return 'invoices.invoice_date';
    }

    protected function getTextRelatedDocumentAmount($type, $textRelatedDocumentAmount)
    {
        if (! empty($textRelatedDocumentAmount)) {
            return $textRelatedDocumentAmount;
        }

        switch ($type) {
            case 'bill':
            case 'expense':
            case 'purchase':
                $default_key = 'bill_amount';
                break;
            default:
                $default_key = 'invoice_amount';
                break;
        }

        $translation = $this->getTextFromConfig($type, 'related_document_amount', $default_key);

        if (! empty($translation)) {
            return $translation;
        }

        return 'general.amount';
    }

    protected function getTextRelatedAmount($type, $textRelatedAmount)
    {
        if (! empty($textRelatedAmount)) {
            return $textRelatedAmount;
        }

        $translation = $this->getTextFromConfig($type, 'related_amount', 'amount');

        if (! empty($translation)) {
            return $translation;
        }

        return 'general.amount';
    }

    protected function routeDocumentShow($type, $routeDocumentShow)
    {
        if (! empty($routeDocumentShow)) {
            return $routeDocumentShow;
        }

        if (! $this->transaction->document) {
            return $routeDocumentShow;
        }

        //example route parameter.
        $parameter = 1;

        $route = $this->getRouteFromConfig($this->transaction->document->type, 'show', $parameter);

        if (! empty($route)) {
            return $route;
        }

        return 'invoices.show';
    }

    protected function routeTransactionShow($type, $routeTransactionShow)
    {
        if (! empty($routeTransactionShow)) {
            return $routeTransactionShow;
        }

        //example route parameter.
        $parameter = 1;

        $route = $this->getRouteFromConfig($type, 'show', $parameter);

        if (! empty($route)) {
            return $route;
        }

        return 'transactions.show';
    }

    protected function getTextRecurringType($type, $textRecurringType)
    {
        if (! empty($textRecurringType)) {
            return $textRecurringType;
        }

        $translation = config('type.transaction.' . $type . '.translation.transactions');

        if (! empty($translation)) {
            return $translation;
        }

        return 'general.incomes';
    }
}
