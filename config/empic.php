<?php

return [
    'base_url'  => env('EMPIC_BASE_URL'),
    'client_id' => env('EMPIC_CLIENT_ID'),
    'username'  => env('EMPIC_USERNAME'),
    'password'  => env('EMPIC_PASSWORD'),
    'timeout'   => (int) env('EMPIC_TIMEOUT', 10),
];
