<?php

use App\Models\Banking\Transaction;
use App\Models\Common\Contact;
use App\Models\Setting\Category;

/**
 * MobiTrack type configuration.
 * Document model removed — invoice/bill types replaced with string literals.
 */

return [

    // Categories
    'category' => [
        Category::INCOME_TYPE => [
            'alias' => '',
            'group' => Category::INCOME_TYPE,
            'translation' => [
                'prefix' => 'general',
            ],
            'hide' => ['code'],
        ],

        Category::EXPENSE_TYPE => [
            'alias' => '',
            'group' => Category::EXPENSE_TYPE,
            'translation' => [
                'prefix' => 'general',
            ],
            'hide' => ['code'],
        ],

        Category::DIRECT_COST_TYPE => [
            'alias' => '',
            'group' => Category::EXPENSE_TYPE,
            'translation' => [
                'prefix' => 'general',
            ],
            'hide' => ['code'],
        ],

        Category::ITEM_TYPE => [
            'alias' => '',
            'group' => Category::ITEM_TYPE,
            'translation' => [
                'prefix' => 'general',
            ],
            'hide' => ['code'],
        ],

        Category::OTHER_TYPE => [
            'alias' => '',
            'group' => Category::OTHER_TYPE,
            'translation' => [
                'prefix' => 'general',
            ],
            'hide' => ['code'],
        ],
    ],

    // Contacts
    'contact' => [
        Contact::CUSTOMER_TYPE => [
            'alias'                 => '',
            'group'                 => 'sales',
            'route' => [
                'prefix'            => 'customers',
                'parameter'         => 'customer',
            ],
            'permission' => [
                'prefix'            => 'customers',
            ],
            'translation' => [
                'prefix'                        => 'customers',
                'section_general_description'   => 'customers.form_description.general',
                'section_billing_description'   => 'customers.form_description.billing',
                'section_address_description'   => 'customers.form_description.address',
            ],
            'category_type'         => Category::INCOME_TYPE,
            'document_type'         => 'invoice',
            'transaction_type'      => Transaction::INCOME_TYPE,
            'hide'                  => [],
            'class'                 => [],
        ],

        Contact::VENDOR_TYPE => [
            'alias'                 => '',
            'group'                 => 'purchases',
            'route' => [
                'prefix'            => 'vendors',
                'parameter'         => 'vendor',
            ],
            'permission' => [
                'prefix'            => 'vendors',
            ],
            'translation' => [
                'prefix'                        => 'vendors',
                'section_general_description'   => 'vendors.form_description.general',
                'section_billing_description'   => 'vendors.form_description.billing',
                'section_address_description'   => 'vendors.form_description.address',
            ],
            'category_type'         => Category::EXPENSE_TYPE,
            'document_type'         => 'bill',
            'transaction_type'      => Transaction::EXPENSE_TYPE,
            'hide'                  => [],
            'class'                 => [],
        ],
    ],

    // Transactions
    'transaction' => [
        Transaction::INCOME_TYPE => [
            'group'                 => 'banking',
            'route' => [
                'prefix'            => 'transactions',
                'parameter'         => 'transaction',
                'params' => [
                    'income'        => ['search' => 'type:income'],
                    'expense'       => ['search' => 'type:expense'],
                    'all'           => ['list_records' => 'all'],
                ],
            ],
            'permission' => [
                'prefix'            => 'transactions',
            ],
            'translation' => [
                'prefix'                    => 'transactions',
                'related_document_amount'   => 'invoices.invoice_amount',
                'transactions'              => 'general.incomes',
            ],
            'category_type'         => Category::INCOME_TYPE,
            'contact_type'          => Contact::CUSTOMER_TYPE,
            'document_type'         => 'invoice',
            'split_type'            => Transaction::INCOME_SPLIT_TYPE,
            'email_template'        => 'payment_received_customer',
        ],

        Transaction::EXPENSE_TYPE => [
            'group'                 => 'banking',
            'route' => [
                'prefix'            => 'transactions',
                'parameter'         => 'transaction',
                'params' => [
                    'income'        => ['search' => 'type:income'],
                    'expense'       => ['search' => 'type:expense'],
                    'all'           => ['list_records' => 'all'],
                ],
            ],
            'permission' => [
                'prefix'            => 'transactions',
            ],
            'translation' => [
                'prefix'                    => 'transactions',
                'related_document_amount'   => 'bills.bill_amount',
            ],
            'category_type'         => Category::EXPENSE_TYPE,
            'contact_type'          => Contact::VENDOR_TYPE,
            'document_type'         => 'bill',
            'split_type'            => Transaction::EXPENSE_SPLIT_TYPE,
            'email_template'        => 'payment_made_vendor',
        ],
    ],

];
