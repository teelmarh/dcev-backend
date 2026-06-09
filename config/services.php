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

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'paystack' => [
        'secret_key'   => env('PAYSTACK_SECRET_KEY'),
        'public_key'   => env('PAYSTACK_PUBLIC_KEY'),
    ],

    'remita' => [
        'merchant_id'    => env('REMITA_MERCHANT_ID'),
        'service_type_id'=> env('REMITA_SERVICE_TYPE_ID'),
        'api_key'        => env('REMITA_API_KEY'),
        'sandbox'        => (bool) env('REMITA_SANDBOX', true),
    ],

    'dcev' => [
        'enrollment_fee' => (int) env('DCEV_ENROLLMENT_FEE', 15000),
        'delivery_fee'   => (int) env('DCEV_DELIVERY_FEE', 5000),
    ],

];
