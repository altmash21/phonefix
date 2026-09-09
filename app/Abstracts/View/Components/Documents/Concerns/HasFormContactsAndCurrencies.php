<?php

namespace App\Abstracts\View\Components\Documents\Concerns;

use App\Models\Common\Contact;
use App\Models\Setting\Currency;

trait HasFormContactsAndCurrencies
{
    protected function getCurrencies($currencies)
    {
        if (!empty($currencies)) {
            return $currencies;
        }

        return Currency::enabled()->pluck('name', 'code');
    }

    protected function getCurrency($document, $currency, $currency_code)
    {
        if (! empty($currency)) {
            return $currency;
        }

        if (! empty($currency_code)) {
            $currency = Currency::where('code', $currency_code)->first();
        }

        if (empty($currency) && ! empty($document)) {
            $currency = Currency::where('code', $document->currency_code)->first();
        }

        if (empty($currency)) {
            $currency = Currency::where('code', default_currency())->first();
        }

        return $currency;
    }

    protected function getTextSectionCompaniesTitle($type, $textSectionCompaniesTitle)
    {
        if (! empty($textSectionCompaniesTitle)) {
            return $textSectionCompaniesTitle;
        }

        return $this->getTextSectionTitle($type, 'companies', 'general.companies');
    }

    protected function getTextSectionCompaniesDescription($type, $textSectionCompaniesDescription)
    {
        if (! empty($textSectionCompaniesDescription)) {
            return $textSectionCompaniesDescription;
        }

        return $this->getTextSectionDescription($type, 'companies', 'documents.form_description.companies');
    }

    protected function getTextSectionMainTitle($type, $textSectionMainTitle)
    {
        if (! empty($textSectionMainTitle)) {
            return $textSectionMainTitle;
        }

        return $this->getTextSectionTitle($type, 'main', 'documents.billing');
    }

    protected function getTextSectionMainDescription($type, $textSectionMainDescription)
    {
        if (! empty($textSectionMainDescription)) {
            return $textSectionMainDescription;
        }

        return $this->getTextSectionDescription($type, 'billing', 'documents.form_description.billing');
    }

    protected function getTypeContact($type, $typeContact)
    {
        if (! empty($typeContact)) {
            return $typeContact;
        }

        return config('type.' . static::OBJECT_TYPE . '.' . $type . '.contact_type', 'customer');
    }

    protected function getTextContact($type, $textContact)
    {
        if (! empty($textContact)) {
            return $textContact;
        }

        $contact_type = config('type.' . static::OBJECT_TYPE . '.' . $type . '.contact_type');

        $default_key = config('type.contact.' . $contact_type . '.translation.prefix');

        $translation = $this->getTextFromConfig($type, 'contact', $default_key);

        if (! empty($translation)) {
            return $translation;
        }

        return 'general.customers';
    }

    protected function getContact($document, $contact, $contact_id)
    {
        if (! empty($contact)) {
            return $contact;
        }

        if (! empty($contact_id)) {
            $contact = Contact::find($contact_id);
        }

        if (empty($contact) && ! empty($document)) {
            $contact = $document->contact;
        }

        return $contact;
    }

    protected function getContacts($type, $contacts)
    {
        if (! empty($contacts)) {
            return $contacts;
        }

        $contact_type = config('type.' . static::OBJECT_TYPE . '.' . $type . '.contact_type', 'customer');

        return Contact::type($contact_type)->enabled()->orderBy('name')->take(setting('default.select_limit'))->pluck('name', 'id');
    }

    protected function getSearchContactRoute($type, $searchContactRoute)
    {
        if (! empty($searchContactRoute)) {
            return $searchContactRoute;
        }

        $contact_type = config('type.' . static::OBJECT_TYPE . '.' . $type . '.contact_type', 'customer');

        return config('type.contact.' . $contact_type . '.route.search');
    }

    protected function getCreateContactRoute($type, $createContactRoute)
    {
        if (! empty($createContactRoute)) {
            return $createContactRoute;
        }

        $contact_type = config('type.' . static::OBJECT_TYPE . '.' . $type . '.contact_type', 'customer');

        return config('type.contact.' . $contact_type . '.route.create');
    }

    protected function getTextAddContact($type, $textAddContact)
    {
        if (! empty($textAddContact)) {
            return $textAddContact;
        }

        $contact_type = config('type.' . static::OBJECT_TYPE . '.' . $type . '.contact_type', 'customer');

        $default_key = config('type.contact.' . $contact_type . '.translation.prefix');

        $translation = $this->getTextFromConfig($type, 'add_contact', $default_key);

        if (! empty($translation)) {
            return [
                'general.form.add_new',
                $translation,
            ];
        }

        return [
            'general.form.add_new',
            'general.customers',
        ];
    }

    protected function getTextCreateNewContact($type, $textCreateNewContact)
    {
        if (! empty($textCreateNewContact)) {
            return $textCreateNewContact;
        }

        $contact_type = config('type.' . static::OBJECT_TYPE . '.' . $type . '.contact_type', 'customer');

        $default_key = config('type.contact.' . $contact_type . '.translation.prefix');

        $translation = $this->getTextFromConfig($type, 'create_new_contact', $default_key);

        if (! empty($translation)) {
            return [
                'general.title.new',
                $translation,
            ];
        }

        return 'contacts.create_customer';
    }

    protected function getTextEditContact($type, $textEditContact)
    {
        if (! empty($textEditContact)) {
            return $textEditContact;
        }

        $contact_type = config('type.' . static::OBJECT_TYPE . '.' . $type . '.contact_type', 'customer');

        $default_key = config('type.contact.' . $contact_type . '.translation.prefix');

        $translation = $this->getTextFromConfig($type, 'edit_contact', $default_key);

        if (! empty($translation)) {
            return [
                'general.title.edit',
                $translation,
            ];
        }

        return 'contacts.edit_customer';
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

    protected function getTextChooseDifferentContact($type, $textChooseDifferentContact)
    {
        if (! empty($textChooseDifferentContact)) {
            return $textChooseDifferentContact;
        }

        $contact_type = config('type.' . static::OBJECT_TYPE . '.' . $type . '.contact_type', 'customer');

        $default_key = config('type.contact.' . $contact_type . '.translation.prefix');

        $translation = $this->getTextFromConfig($type, 'choose_different_contact', $default_key);

        if (!empty($translation)) {
            return [
                'general.form.choose_different',
                $translation,
            ];
        }

        return [
            'general.form.choose_different',
            'general.customers',
        ];
    }
}
