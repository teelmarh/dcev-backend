<?php

use App\Classes\CustomMailerManager;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/mail-preview', function () {
    $cmm = new CustomMailerManager();

    return view('components.dcev-mailer.notifications.email', [
        'cmm'                  => $cmm,
        'level'                => 'default',
        'greeting'             => 'Hello, John Doe!',
        'introLines'           => [
            'Your email address has been verified successfully.',
            'You can now access all features of your DCEV account.',
        ],
        'actionText'           => 'Go to Dashboard',
        'actionUrl'            => 'https://dcev.ncaa.gov.ng/dashboard',
        'displayableActionUrl' => 'https://dcev.ncaa.gov.ng/dashboard',
        'outroLines'           => [
            'If you did not create this account, no further action is required.',
        ],
        'salutation'           => null,
    ]);
});
