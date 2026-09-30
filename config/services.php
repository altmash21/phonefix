<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'sendgrid' => [
        'api_key' => env('SENDGRID_API_KEY'),
    ],

    'gemini' => [
        'key'            => env('GEMINI_API_KEY', ''),
        'model'          => env('GEMINI_MODEL', 'gemini-3.6-flash'),
        'fallback_model' => env('GEMINI_FALLBACK_MODEL', 'gemini-3.8-flash'),
    ],

    'groq' => [
        'key'            => env('GROQ_API_KEY', ''),
        'model'          => env('GROQ_MODEL', 'llama-3.2-11b-vision-preview'),
        'fallback_model' => env('GROQ_FALLBACK_MODEL', 'llama-3.2-90b-vision-preview'),
    ],

    'openai' => [
        'key'   => env('OPENAI_API_KEY', ''),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
    ],

    'ocr' => [
        'provider_order' => env('OCR_PROVIDER_ORDER', 'gemini,groq,openai'),
    ],

    'whatsapp' => [
        'webhook' => env('WA_WEBHOOK'),
        'phone'   => env('WA_PHONE'),
    ],
];
