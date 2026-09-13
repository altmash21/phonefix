<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mobile Shop & POS Store Configuration
    |--------------------------------------------------------------------------
    |
    | Store identity, contact, address, and payment information used across
    | WhatsApp receipt notifications, PDF exports, and POS printouts.
    |
    */

    'store_name'     => env('STORE_NAME'),
    'store_phone'    => env('STORE_PHONE'),
    'store_whatsapp' => env('STORE_WHATSAPP'),
    'store_address'  => env('STORE_ADDRESS'),
    'store_city'     => env('STORE_CITY'),
    'store_state'    => env('STORE_STATE'),
    'store_pin'      => env('STORE_PIN'),
    'store_gstin'    => env('STORE_GSTIN'),
    'store_upi_id'   => env('STORE_UPI_ID'),
    'store_landline' => env('STORE_LANDLINE'),

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Dispatch Webhook
    |--------------------------------------------------------------------------
    |
    | Endpoint and sender phone for background receipt dispatch.
    |
    */

    'whatsapp' => [
        'webhook' => env('WA_WEBHOOK'),
        'phone'   => env('WA_PHONE'),
    ],

];
