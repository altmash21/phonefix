<?php

namespace App\Abstracts\View\Components\Transactions\Concerns;

trait HasTransactionIndexColumns
{
    protected function getClassPaidAtAndNumber($type, $classPaidAtAndNumber)
    {
        if (! empty($classPaidAtAndNumber)) {
            return $classPaidAtAndNumber;
        }

        $class = $this->getClassFromConfig($type, 'paid_at_and_number');

        if (! empty($class)) {
            return $class;
        }

        return 'w-4/12 sm:w-3/12';
    }

    protected function getTextPaidAt($type, $textPaidAt)
    {
        if (! empty($textPaidAt)) {
            return $textPaidAt;
        }

        $translation = $this->getTextFromConfig($type, 'paid_at', 'paid_date');

        if (! empty($translation)) {
            return $translation;
        }

        return 'general.date';
    }

    protected function getTextNumber($type, $textNumber)
    {
        if (! empty($textNumber)) {
            return $textNumber;
        }

        $translation = $this->getTextFromConfig($type, 'number', 'number');

        if (! empty($translation)) {
            return $translation;
        }

        return 'general.numbers';
    }

    protected function getClassStartedAndEndedAt($type, $classStartedAtAndEndedAt)
    {
        if (! empty($classStartedAtAndEndedAt)) {
            return $classStartedAtAndEndedAt;
        }

        $class = $this->getClassFromConfig($type, 'started_at_and_end_at');

        if (! empty($class)) {
            return $class;
        }

        return '"w-4/12 sm:w-3/12';
    }

    protected function getTextStartedAt($type, $textStartedAt)
    {
        if (! empty($textStartedAt)) {
            return $textStartedAt;
        }

        $translation = $this->getTextFromConfig($type, 'started_at', 'started_date');

        if (! empty($translation)) {
            return $translation;
        }

        return 'general.start_date';
    }

    protected function getTextEndedAt($type, $textEndedAt)
    {
        if (! empty($textEndedAt)) {
            return $textEndedAt;
        }

        $translation = $this->getTextFromConfig($type, 'ended_at', 'ended_date');

        if (! empty($translation)) {
            return $translation;
        }

        return 'recurring.last_issued';
    }

    protected function getClassTypeAndCategory($type, $classTypeAndCategory)
    {
        if (! empty($classTypeAndCategory)) {
            return $classTypeAndCategory;
        }

        $class = $this->getClassFromConfig($type, 'type_and_category');

        if (! empty($class)) {
            return $class;
        }

        return 'w-2/12';
    }

    protected function getTextType($type, $textType)
    {
        if (! empty($textType)) {
            return $textType;
        }

        $translation = $this->getTextFromConfig($type, 'type', 'type');

        if (! empty($translation)) {
            return $translation;
        }

        return 'general.types';
    }

    protected function getTextCategory($type, $textCategory)
    {
        if (! empty($textCategory)) {
            return $textCategory;
        }

        $translation = $this->getTextFromConfig($type, 'category', 'category');

        if (! empty($translation)) {
            return $translation;
        }

        return 'general.categories';
    }

    protected function getClassStatus($type, $classStatus)
    {
        if (! empty($classStatus)) {
            return $classStatus;
        }

        $class = $this->getClassFromConfig($type, 'status');

        if (! empty($class)) {
            return $class;
        }

        return 'w-4/12 sm:w-3/12';
    }

    protected function getClassFrequencyAndDuration($type, $classFrequencyAndDuration)
    {
        if (! empty($classFrequencyAndDuration)) {
            return $classFrequencyAndDuration;
        }

        $class = $this->getClassFromConfig($type, 'frequency_and_duration');

        if (! empty($class)) {
            return $class;
        }

        return 'w-2/12';
    }

    protected function getClassAccount($type, $classAccount)
    {
        if (! empty($classAccount)) {
            return $classAccount;
        }

        $class = $this->getClassFromConfig($type, 'account');

        if (! empty($class)) {
            return $class;
        }

        return 'w-4/12 sm:w-3/12';
    }

    protected function getTextAccount($type, $textAccount)
    {
        if (! empty($textAccount)) {
            return $textAccount;
        }

        $translation = $this->getTextFromConfig($type, 'account', 'account');

        if (! empty($translation)) {
            return $translation;
        }

        return 'general.accounts';
    }

    protected function getClassContactAndDocument($type, $classContactAndDocument)
    {
        if (! empty($classContactAndDocument)) {
            return $classContactAndDocument;
        }

        $class = $this->getClassFromConfig($type, 'contact_and_document');

        if (! empty($class)) {
            return $class;
        }

        return 'w-2/12';
    }

    protected function getTextContact($type, $textContact)
    {
        if (! empty($textContact)) {
            return $textContact;
        }

        $translation = $this->getTextFromConfig($type, 'contact', 'contact');

        if (! empty($translation)) {
            return $translation;
        }

        return 'general.contacts';
    }

    protected function getTextDocument($type, $textDocument)
    {
        if (! empty($textDocument)) {
            return $textDocument;
        }

        $translation = $this->getTextFromConfig($type, 'document', 'document');

        if (! empty($translation)) {
            return $translation;
        }

        return 'general.documents';
    }

    protected function getClassAmount($type, $classAmount)
    {
        if (! empty($classAmount)) {
            return $classAmount;
        }

        $class = $this->getClassFromConfig($type, 'amount');

        if (! empty($class)) {
            return $class;
        }

        return 'w-4/12 sm:w-2/12';
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
}
