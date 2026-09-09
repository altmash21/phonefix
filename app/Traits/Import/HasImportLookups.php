<?php

namespace App\Traits\Import;

use App\Http\Requests\Banking\Account as AccountRequest;
use App\Http\Requests\Common\Contact as ContactRequest;
use App\Http\Requests\Common\Item as ItemRequest;
use App\Http\Requests\Setting\Category as CategoryRequest;
use App\Http\Requests\Setting\Currency as CurrencyRequest;
use App\Http\Requests\Setting\Tax as TaxRequest;
use App\Jobs\Banking\CreateAccount;
use App\Jobs\Common\CreateContact;
use App\Jobs\Common\CreateItem;
use App\Jobs\Setting\CreateCategory;
use App\Jobs\Setting\CreateCurrency;
use App\Jobs\Setting\CreateTax;
use App\Models\Banking\Account;
use App\Models\Common\Contact;
use App\Models\Common\Item;
use App\Models\Setting\Category;
use App\Models\Setting\Tax;
use Illuminate\Support\Facades\Validator;

trait HasImportLookups
{
    public function getAccountIdFromCurrency($row)
    {
        $account_id = Account::where('currency_code', $row['currency_code'])->pluck('id')->first();

        if (!empty($account_id)) {
            return $account_id;
        }

        $data = [
            'company_id'        => company_id(),
            'type'              => !empty($row['account_type']) ? $row['account_type'] : 'bank',
            'currency_code'     => $row['currency_code'],
            'name'              => !empty($row['account_name']) ? $row['account_name'] : $row['currency_code'],
            'number'            => !empty($row['account_number']) ? $row['account_number'] : (string) rand(1, 10000),
            'opening_balance'   => !empty($row['opening_balance']) ? $row['opening_balance'] : 0,
            'enabled'           => 1,
            'created_from'      => !empty($row['created_from']) ? $row['created_from'] : $this->getSourcePrefix() . 'import',
            'created_by'        => !empty($row['created_by']) ? $row['created_by'] : user()?->id,
        ];

        Validator::validate($data, (new AccountRequest)->rules());

        $account = $this->dispatch(new CreateAccount($data));

        return $account->id;
    }

    public function getAccountIdFromName($row)
    {
        $account_id = Account::where('name', $row['account_name'])->pluck('id')->first();

        if (!empty($account_id)) {
            return $account_id;
        }

        $data = [
            'company_id'        => company_id(),
            'type'              => !empty($row['account_type']) ? $row['account_type'] : 'bank',
            'name'              => $row['account_name'],
            'number'            => !empty($row['account_number']) ? $row['account_number'] : (string) rand(1, 10000),
            'currency_code'     => !empty($row['currency_code']) ? $row['currency_code'] : default_currency(),
            'opening_balance'   => !empty($row['opening_balance']) ? $row['opening_balance'] : 0,
            'enabled'           => 1,
            'created_from'      => !empty($row['created_from']) ? $row['created_from'] : $this->getSourcePrefix() . 'import',
            'created_by'        => !empty($row['created_by']) ? $row['created_by'] : user()?->id,
        ];

        Validator::validate($data, (new AccountRequest)->rules());

        $account = $this->dispatch(new CreateAccount($data));

        return $account->id;
    }

    public function getAccountIdFromNumber($row)
    {
        $account_id = Account::where('account_number', $row['account_number'])->pluck('id')->first();

        if (!empty($account_id)) {
            return $account_id;
        }

        $data = [
            'company_id'        => company_id(),
            'type'              => !empty($row['account_type']) ? $row['account_type'] : 'bank',
            'number'            => $row['account_number'],
            'name'              => !empty($row['account_name']) ? $row['account_name'] : $row['account_number'],
            'currency_code'     => !empty($row['currency_code']) ? $row['currency_code'] : default_currency(),
            'opening_balance'   => !empty($row['opening_balance']) ? $row['opening_balance'] : 0,
            'enabled'           => 1,
            'created_from'      => !empty($row['created_from']) ? $row['created_from'] : $this->getSourcePrefix() . 'import',
            'created_by'        => !empty($row['created_by']) ? $row['created_by'] : user()?->id,
        ];

        Validator::validate($data, (new AccountRequest)->rules());

        $account = $this->dispatch(new CreateAccount($data));

        return $account->id;
    }

    public function getCategoryIdFromName($row, $type)
    {
        $category_id = Category::type($type)->withSubCategory()->where('name', $row['category_name'])->pluck('id')->first();

        if (!empty($category_id)) {
            return $category_id;
        }

        $data = [
            'company_id'        => company_id(),
            'name'              => $row['category_name'],
            'type'              => $type,
            'color'             => !empty($row['category_color']) ? $row['category_color'] : '#' . dechex(rand(0x000000, 0xFFFFFF)),
            'enabled'           => 1,
            'created_from'      => !empty($row['created_from']) ? $row['created_from'] : $this->getSourcePrefix() . 'import',
            'created_by'        => !empty($row['created_by']) ? $row['created_by'] : user()?->id,
        ];

        Validator::validate($data, (new CategoryRequest)->rules());

        $category = $this->dispatch(new CreateCategory($data));

        return $category->id;
    }

    public function getContactIdFromEmail($row, $type)
    {
        $contact_id = Contact::type($type)->where('email', $row['contact_email'])->pluck('id')->first();

        if (!empty($contact_id)) {
            return $contact_id;
        }

        $data = [
            'company_id'        => company_id(),
            'email'             => $row['contact_email'],
            'type'              => $type,
            'name'              => !empty($row['contact_name']) ? $row['contact_name'] : $row['contact_email'],
            'currency_code'     => !empty($row['contact_currency']) ? $row['contact_currency'] : default_currency(),
            'enabled'           => 1,
            'created_from'      => !empty($row['created_from']) ? $row['created_from'] : $this->getSourcePrefix() . 'import',
            'created_by'        => !empty($row['created_by']) ? $row['created_by'] : user()?->id,
        ];

        Validator::validate($data, (new ContactRequest)->rules());

        $contact = $this->dispatch(new CreateContact($data));

        return $contact->id;
    }

    public function getContactIdFromName($row, $type)
    {
        $contact_id = Contact::type($type)->where('name', $row['contact_name'])->pluck('id')->first();

        if (!empty($contact_id)) {
            return $contact_id;
        }

        $data = [
            'company_id'        => company_id(),
            'name'              => $row['contact_name'],
            'type'              => $type,
            'email'             => !empty($row['contact_email']) ? $row['contact_email'] : null,
            'currency_code'     => !empty($row['contact_currency']) ? $row['contact_currency'] : default_currency(),
            'enabled'           => 1,
            'created_from'      => !empty($row['created_from']) ? $row['created_from'] : $this->getSourcePrefix() . 'import',
            'created_by'        => !empty($row['created_by']) ? $row['created_by'] : user()?->id,
        ];

        Validator::validate($data, (new ContactRequest)->rules());

        $contact = $this->dispatch(new CreateContact($data));

        return $contact->id;
    }

    public function getItemIdFromName($row, $type = null)
    {
        $type = !empty($type) ? $type : (!empty($row['item_type']) ? $row['item_type'] : 'product');

        $item_id = Item::type($type)->where('name', $row['item_name'])->pluck('id')->first();

        if (!empty($item_id)) {
            return $item_id;
        }

        $data = [
            'company_id'        => company_id(),
            'type'              => $type,
            'name'              => $row['item_name'],
            'description'       => !empty($row['item_description']) ? $row['item_description'] : null,
            'sale_price'        => !empty($row['sale_price']) ? $row['sale_price'] : (!empty($row['price']) ? $row['price'] : 0),
            'purchase_price'    => !empty($row['purchase_price']) ? $row['purchase_price'] : (!empty($row['price']) ? $row['price'] : 0),
            'enabled'           => 1,
            'created_from'      => !empty($row['created_from']) ? $row['created_from'] : $this->getSourcePrefix() . 'import',
            'created_by'        => !empty($row['created_by']) ? $row['created_by'] : user()?->id,
        ];

        Validator::validate($data, (new ItemRequest())->rules());

        $item = $this->dispatch(new CreateItem($data));

        return $item->id;
    }

    public function getTaxIdFromRate($row, $type = 'normal')
    {
        $tax_id = Tax::type($type)->where('rate', $row['tax_rate'])->pluck('id')->first();

        if (!empty($tax_id)) {
            return $tax_id;
        }

        $data = [
            'company_id'        => company_id(),
            'rate'              => $row['tax_rate'],
            'type'              => $type,
            'name'              => !empty($row['tax_name']) ? $row['tax_name'] : (string) $row['tax_rate'],
            'enabled'           => 1,
            'created_from'      => !empty($row['created_from']) ? $row['created_from'] : $this->getSourcePrefix() . 'import',
            'created_by'        => !empty($row['created_by']) ? $row['created_by'] : user()?->id,
        ];

        Validator::validate($data, (new TaxRequest())->rules());

        $tax = $this->dispatch(new CreateTax($data));

        return $tax->id;
    }


}
