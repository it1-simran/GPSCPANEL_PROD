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

    // MES (production-mis-backend) — Purchase Order integration.
    // Set MES_API_URL + MES_API_TOKEN in .env to enable. Leave MES_API_URL
    // empty and PO submission reports "not configured" instead of failing.
    'mes' => [
        'url' => env('MES_API_URL', ''),
        'token' => env('MES_API_TOKEN', ''),
        'verify_ssl' => env('MES_VERIFY_SSL', true),
    ],

];
