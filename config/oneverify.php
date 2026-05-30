<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OneVERIFY API Configuration
    |--------------------------------------------------------------------------
    |
    | Credentials and endpoint config for the OneVERIFY identity verification
    | API (Fontanella). The Bearer token obtained on login is cached for
    | ONEVERIFY_TOKEN_TTL seconds and refreshed automatically on expiry.
    |
    */

    'base_url'  => env('ONEVERIFY_BASE_URL', 'https://sandbox.fontanella.app/api/v1'),
    'api_key'   => env('ONEVERIFY_API_KEY'),
    'user_id'   => env('ONEVERIFY_USER_ID'),
    'email'     => env('ONEVERIFY_EMAIL'),
    'password'  => env('ONEVERIFY_PASSWORD'),
    'token_ttl' => (int) env('ONEVERIFY_TOKEN_TTL', 3600), // seconds

];
