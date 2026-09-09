<?php

namespace App\Traits\Import;

use App\Models\Banking\Account;
use App\Models\Banking\Transaction;
use App\Models\Common\Contact;
use App\Models\Common\Item;
use App\Models\Document\Document;
use App\Models\Setting\Category;
use App\Models\Setting\Currency;
use App\Models\Setting\Tax;

trait HasImportEntityResolvers
{
    public function getAccountId($row)
    {
        $id = isset($row['account_id']) ? $row['account_id'] : null;

        if (empty($id) && !empty($row['account_name'])) {
            $id = $this->getAccountIdFromName($row);
        }

        if (empty($id) && !empty($row['account_number'])) {
            $id = $this->getAccountIdFromNumber($row);
        }

        if (empty($id) && !empty($row['currency_code'])) {
            $id = $this->getAccountIdFromCurrency($row);
        }

        return is_null($id) ? $id : (int) $id;
    }

    public function getCategoryId($row, $type = null)
    {
        $id = isset($row['category_id']) ? $row['category_id'] : null;

        $type = !empty($type) ? $type : (!empty($row['type']) ? $row['type'] : Category::INCOME_TYPE);

        if (empty($id) && !empty($row['category_name'])) {
            $id = $this->getCategoryIdFromName($row, $type);
        }

        return is_null($id) ? $id : (int) $id;
    }

    public function getCategoryType($type)
    {
        return array_key_exists($type, config('type.category')) ? $type : Category::OTHER_TYPE;
    }

    public function getContactId($row, $type = null)
    {
        $id = isset($row['contact_id']) ? $row['contact_id'] : null;

        $type = !empty($type) ? $type : (!empty($row['type']) ? (($row['type'] == Transaction::INCOME_TYPE) ? Contact::CUSTOMER_TYPE : Contact::VENDOR_TYPE) : Contact::CUSTOMER_TYPE);

        if (empty($row['contact_id']) && !empty($row['contact_email'])) {
            $id = $this->getContactIdFromEmail($row, $type);
        }

        if (empty($id) && !empty($row['contact_name'])) {
            $id = $this->getContactIdFromName($row, $type);
        }

        return is_null($id) ? $id : (int) $id;
    }

    public function getCurrencyCode($row)
    {
        // Uploaded file may not include a currency_code column — fall back to the
        // company default instead of crashing on an undefined array key.
        if (empty($row['currency_code'])) {
            return default_currency();
        }

        $currency = Currency::where('code', $row['currency_code'])->first();

        if (!empty($currency)) {
            return $currency->code;
        }

        try {
            $data = [
                'company_id'            => company_id(),
                'code'                  => $row['currency_code'],
                'name'                  => isset($row['currency_name']) ? $row['currency_name'] : currency($row['currency_code'])->getName(),
                'rate'                  => isset($row['currency_rate']) ? $row['currency_rate'] : 1,
                'symbol'                => isset($row['currency_symbol']) ? $row['currency_symbol'] : currency($row['currency_code'])->getSymbol(),
                'precision'             => isset($row['currency_precision']) ? $row['currency_precision'] : currency($row['currency_code'])->getPrecision(),
                'decimal_mark'          => isset($row['currency_decimal_mark']) ? $row['currency_decimal_mark'] : currency($row['currency_code'])->getDecimalMark(),
                'thousands_separator'   => isset($row['currency_thousands_separator']) ? $row['currency_thousands_separator'] : currency($row['currency_code'])->getThousandsSeparator(),
                'created_from'          => !empty($row['created_from']) ? $row['created_from'] : $this->getSourcePrefix() . 'import',
                'created_by'            => !empty($row['created_by']) ? $row['created_by'] : user()?->id,
            ];
        } catch (\OutOfBoundsException $e) {
            return default_currency();
        }

        Validator::validate($data, (new CurrencyRequest)->rules());

        $currency = $this->dispatch(new CreateCurrency($data));

        return $currency->code;
    }

    public function getCreatedById($row)
    {
        if (empty($row['created_by'])) {
            return $this->user?->id;
        }

        $user = user_model_class()::where('email', $row['created_by'])->first();

        if (! empty($user)) {
            return $user->id;
        }

        return $this->user->id;
    }

    public function getDocumentId($row)
    {
        $id = isset($row['document_id']) ? $row['document_id'] : null;

        if (empty($id) && !empty($row['document_number'])) {
            $id = Document::number($row['document_number'])->pluck('id')->first();
        }

        if (empty($id) && !empty($row['invoice_number'])) {
            $id = Document::invoice()->number($row['invoice_number'])->pluck('id')->first();
        }

        if (empty($id) && !empty($row['bill_number'])) {
            $id = Document::bill()->number($row['bill_number'])->pluck('id')->first();
        }

        if (empty($id) && !empty($row['invoice_bill_number'])) {
            if ($row['type'] == Transaction::INCOME_TYPE) {
                $id = Document::invoice()->number($row['invoice_bill_number'])->pluck('id')->first();
            } else {
                $id = Document::bill()->number($row['invoice_bill_number'])->pluck('id')->first();
            }
        }

        return is_null($id) ? $id : (int) $id;
    }

    public function getParentId($row)
    {
        $id = isset($row['parent_id']) ? $row['parent_id'] : null;

        if (empty($row['parent_number']) && empty($row['parent_name'])){
            return null;
        }

        if (empty($id) && (!empty($row['document_number']) || !empty($row['invoice_number']) || !empty($row['bill_number']))) {
            $id = Document::number($row['parent_number'])->pluck('id')->first();
        }

        if (empty($id) && isset($row['number'])) {
            $id = Transaction::number($row['parent_number'])->pluck('id')->first();
        }

        if (empty($id) && isset($row['parent_name'])) {
            $id = Category::type($row['type'])->withSubCategory()->where('name', $row['parent_name'])->pluck('id')->first();
        }

        return is_null($id) ? $id : (int) $id;
    }

    public function getPaymentMethod($row)
    {
        Modules::clearPaymentMethodsCache();

        $methods = Modules::getPaymentMethods('all');

        $payment_method = isset($row['payment_method']) ? $row['payment_method'] : null;

        if (array_key_exists($payment_method, $methods)) {
            return $payment_method;
        }

        if (module_is_enabled('offline-payments')) {
            $offline_payment = $this->dispatch(new \Modules\OfflinePayments\Jobs\CreatePaymentMethod([
                'name'          => $payment_method,
                'customer'      => 1,
                'order'         => count($methods) + 1,
                'description'   => '',
            ]));

            $payment_method = $offline_payment['code'];
        }

        return $payment_method;
    }

    public function getItemId($row, $type = null)
    {
        $id = isset($row['item_id']) ? $row['item_id'] : null;

        $type = !empty($type) ? $type : (!empty($row['item_type']) ? $row['item_type'] : 'product');

        if (empty($id) && !empty($row['item_name'])) {
            $id = $this->getItemIdFromName($row, $type);
        }

        return is_null($id) ? $id : (int) $id;
    }

    public function getTaxId($row)
    {
        $id = isset($row['tax_id']) ? $row['tax_id'] : null;

        if (empty($id) && !empty($row['tax_name'])) {
            $id = Tax::name($row['tax_name'])->pluck('id')->first();
        }

        if (empty($id) && !empty($row['tax_rate'])) {
            $id = $this->getTaxIdFromRate($row);
        }

        return is_null($id) ? $id : (int) $id;
    }


}
