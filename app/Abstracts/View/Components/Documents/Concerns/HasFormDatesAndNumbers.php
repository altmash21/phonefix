<?php

namespace App\Abstracts\View\Components\Documents\Concerns;

use App\Models\Common\Contact;
use App\Models\Document\Document;
use App\Interfaces\Utility\DocumentNumber;
use App\Utilities\Date;

trait HasFormDatesAndNumbers
{
    protected function getTextIssuedAt($type, $textIssuedAt)
    {
        if (!empty($textIssuedAt)) {
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

        if (!empty($translation)) {
            return $translation;
        }

        return 'invoices.invoice_date';
    }

    protected function getIssuedAt($type, $document, $issuedAt)
    {
        if (!empty($issuedAt)) {
            return $issuedAt;
        }

        if ($document) {
            return $document->issued_at;
        }

        $issued_at = $type . '_at';

        if (request()->has($issued_at)) {
            $issuedAt = request()->get($issued_at);
        } else {
            $issuedAt = request()->get('invoice_at', Date::now()->toDateString());
        }

        return $issuedAt;
    }

    protected function getTextDueAt($type, $textDueAt)
    {
        if (!empty($textDueAt)) {
            return $textDueAt;
        }

        $translation = $this->getTextFromConfig($type, 'due_at', 'due_date');

        if (!empty($translation)) {
            return $translation;
        }

        return 'invoices.due_date';
    }

    protected function getDueAt($type, $document, $dueAt)
    {
        if (!empty($dueAt)) {
            return $dueAt;
        }

        if ($document) {
            return $document->due_at;
        }

        $issued_at = $type . '_at';

        if (request()->has($issued_at)) {
            $issuedAt = request()->get($issued_at);
        } else {
            $issuedAt = Date::now()->toDateString();
        }

        $addDays = setting($this->getDocumentSettingKey($type, 'payment_terms'), 0) ?: 0;

        $dueAt = Date::parse($issuedAt)->addDays($addDays)->toDateString();

        return $dueAt;
    }

    protected function getPeriodDueAt($type, $periodDueAt)
    {
        if (! empty($periodDueAt)) {
            return $periodDueAt;
        }

        return setting($type. '.payment_terms', 0);
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

        return 'invoices.invoice_number';
    }

    protected function getDocumentNumber($type, $document, $documentNumber)
    {
        if (! empty($documentNumber)) {
            return $documentNumber;
        }

        if ($document) {
            return $document->document_number;
        }

        $contact = ($this->contact instanceof Contact) ? $this->contact : null;

        $utility = app(DocumentNumber::class);

        $document_number = $utility->getNextNumber($type, $contact);

        if (empty($document_number)) {
            $document_number = $utility->getNextNumber(Document::INVOICE_TYPE, $contact);
        }

        return $document_number;
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

    protected function getOrderNumber($type, $document, $orderNumber)
    {
        if (! empty($orderNumber)) {
            return $orderNumber;
        }

        if ($document) {
            return $document->order_number;
        }

        $order_number = null;
    }

}
