<?php

use App\Http\Controllers\Api\V1\Users\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\V1\Users\Auth\LoginController;
use App\Http\Controllers\Api\V1\Users\Auth\RegisterController;
use App\Http\Controllers\Api\V1\Users\Auth\ResetPasswordController;
use App\Http\Controllers\Api\V1\Users\Auth\SendTokenController;
use App\Http\Controllers\Api\V1\Users\Auth\VerifyEmailController;
use App\Http\Controllers\Api\V1\Users\NinVerificationController;
use App\Http\Controllers\Api\V1\System\HealthController;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->group(function () {
    Route::apiResource('/health', HealthController::class)->only('index');

    Route::apiResource('/register', RegisterController::class)->only('store');
    Route::apiResource('/login', LoginController::class)->only('store');
    Route::apiResource('/send-token', SendTokenController::class)->only('store');
    Route::apiResource('/verify-email', VerifyEmailController::class)->only('store');
    Route::apiResource('/forgot-password', ForgotPasswordController::class)->only('store');
    Route::apiResource('/reset-password', ResetPasswordController::class)->only('store');

    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('/nin/verify', NinVerificationController::class)->only('store');
    });
});

