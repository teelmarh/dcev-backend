<?php

use App\Http\Controllers\Api\V1\Users\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\V1\Users\Auth\LoginController;
use App\Http\Controllers\Api\V1\Users\Auth\RegisterController;
use App\Http\Controllers\Api\V1\Users\Auth\ResetPasswordController;
use App\Http\Controllers\Api\V1\Users\Auth\SendTokenController;
use App\Http\Controllers\Api\V1\Users\Auth\VerifyEmailController;
use App\Http\Controllers\Api\V1\Users\NinVerificationController;
use App\Http\Controllers\Api\V1\Profile\ProfileController;
use App\Http\Controllers\Api\V1\Empic\EmpicHumanController;
use App\Http\Controllers\Api\V1\Empic\EmpicAddressController;
use App\Http\Controllers\Api\V1\System\HealthController;
use App\Http\Controllers\Api\V1\Licences\LicenceController;
use App\Http\Controllers\Api\V1\Licences\Fcl\FclPilotController;
use App\Http\Controllers\Api\V1\Licences\Fcl\FclCabinCrewController;
use App\Http\Controllers\Api\V1\Licences\Fcl\FclFlightDispatchController;
use App\Http\Controllers\Api\V1\Licences\Ans\AnsAtcController;
use App\Http\Controllers\Api\V1\Licences\Ans\AnsAtsepController;
use App\Http\Controllers\Api\V1\Licences\Ans\AnsAsoController;
use App\Http\Controllers\Api\V1\Licences\Amel\AmelAmeController;
use App\Http\Controllers\Api\V1\Licences\DeliveryDetailController;
use App\Http\Controllers\Api\V1\Transactions\TransactionController;
use App\Http\Controllers\Api\V1\Transactions\WebhookController;
use App\Http\Controllers\Api\V1\Appointments\AppointmentController;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->group(function () {
    Route::apiResource('/health', HealthController::class)->only('index');

    Route::post('/webhooks/{gateway}', [WebhookController::class, 'handle'])->where('gateway', 'remita|paystack');

    Route::apiResource('/register', RegisterController::class)->only('store');
    Route::apiResource('/login', LoginController::class)->only('store');
    Route::apiResource('/send-token', SendTokenController::class)->only('store');
    Route::apiResource('/verify-email', VerifyEmailController::class)->only('store');
    Route::apiResource('/forgot-password', ForgotPasswordController::class)->only('store');
    Route::apiResource('/reset-password', ResetPasswordController::class)->only('store');

    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('/nin/verify', NinVerificationController::class)->only('store');
        Route::apiResource('/profile', ProfileController::class)->only(['index', 'store']);
        Route::post('/empic/human', [EmpicHumanController::class, 'store']);
        Route::post('/empic/address', [EmpicAddressController::class, 'store']);

        Route::prefix('transactions')->group(function () {
            Route::post('/enrollment/initiate', [TransactionController::class, 'initiateEnrollment']);
            Route::post('/enrollment/verify',   [TransactionController::class, 'verifyEnrollment']);
            Route::post('/delivery/initiate',   [TransactionController::class, 'initiateDelivery']);
            Route::post('/delivery/verify',     [TransactionController::class, 'verifyDelivery']);
        });

        Route::apiResource('licences', LicenceController::class)->only(['index']);

        Route::prefix('licences')->group(function () {
            Route::get('/show',      [LicenceController::class, 'show']);
            Route::post('/delivery', [DeliveryDetailController::class, 'store']);
            Route::get('/delivery',  [DeliveryDetailController::class, 'show']);

            Route::post('/fcl/pilot',      [FclPilotController::class, 'store']);
            Route::post('/fcl/cabin-crew', [FclCabinCrewController::class, 'store']);
            Route::post('/fcl/dispatch',   [FclFlightDispatchController::class, 'store']);
            Route::post('/ans/atc',        [AnsAtcController::class, 'store']);
            Route::post('/ans/atsep',      [AnsAtsepController::class, 'store']);
            Route::post('/ans/aso',        [AnsAsoController::class, 'store']);
            Route::post('/amel/ame',       [AmelAmeController::class, 'store']);
        });

        Route::apiResource('appointments', AppointmentController::class)->only(['index', 'store']);

        Route::prefix('appointments')->group(function () {
            Route::get('/show',    [AppointmentController::class, 'show']);
            Route::get('/offices', [AppointmentController::class, 'offices']);
            Route::get('/licence', [AppointmentController::class, 'licenceAppointment']);
            Route::get('/availability/{office}/{date}', [AppointmentController::class, 'availability']);
            Route::patch('/reschedule', [AppointmentController::class, 'reschedule']);
            Route::patch('/cancel',     [AppointmentController::class, 'cancel']);
        });
    });
});

