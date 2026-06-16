<?php

use App\Http\Controllers\Api\V1\Officer\OfficerMetricsController;
use App\Http\Controllers\Api\V1\Officer\OfficerCardsController;
use App\Http\Controllers\Api\V1\Admin\AdminOfficerController;
use App\Http\Controllers\Api\V1\Admin\AdminOfficerPermissionController;
use App\Http\Controllers\Api\V1\Admin\AdminPermissionController;
use App\Http\Controllers\Api\V1\Admin\AdminRegionalOfficeController;
use App\Http\Controllers\Api\V1\Admin\AdminUserGroupController;
use App\Http\Controllers\Api\V1\Officer\OfficerApplicationController;
use App\Http\Controllers\Api\V1\Officer\OfficerBiometricController;
use App\Http\Controllers\Api\V1\Officer\OfficerDashboardController;
use App\Http\Controllers\Api\V1\Officer\OfficerEnrollmentController;
use App\Http\Controllers\Api\V1\Officer\OfficerDeliveryController;
use App\Http\Controllers\Api\V1\Officer\OfficerHandledApplicationsController;
use App\Http\Controllers\Api\V1\Officer\OfficerRegionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Officer Routes — accessible by officers and superadmins
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'role:officer,superadmin'])->prefix('v1/officer')->group(function () {
    // Dashboard
    Route::get('/metrics',                [OfficerMetricsController::class, 'index']);
    Route::get('/applications',           [OfficerDashboardController::class, 'applications']);
    Route::get('/applications/show',      [OfficerDashboardController::class, 'showApplication']);
    Route::get('/appointments/today',     [OfficerDashboardController::class, 'todayAppointments']);
    Route::get('/appointments',           [OfficerDashboardController::class, 'appointments']);
    Route::patch('/appointments/mark-attended', [OfficerDashboardController::class, 'markAttended']);
    Route::get('/stats',                  [OfficerDashboardController::class, 'stats']);

    // Application queue / claim / process workflow
    Route::get('/queue',                          [OfficerApplicationController::class, 'queue']);
    Route::post('/applications/claim',            [OfficerApplicationController::class, 'claim']);
    Route::post('/applications/unclaim',          [OfficerApplicationController::class, 'unclaim']);
    Route::post('/applications/process',          [OfficerApplicationController::class, 'process']);

    // Enrollment + verification workflow
    Route::post('/applications/enroll',               [OfficerEnrollmentController::class, 'enroll']);
    Route::post('/enrollment/verify',                 [OfficerEnrollmentController::class, 'verify']);
    Route::post('/enrollment/complete-verification',  [OfficerEnrollmentController::class, 'completeVerification']);
    Route::post('/enrollment/escalate',               [OfficerEnrollmentController::class, 'escalate']);
    Route::get('/enrollment/show',                    [OfficerEnrollmentController::class, 'show']);
    Route::get('/audit',                              [OfficerEnrollmentController::class, 'auditLog']);

    // Applications handled by officer (permission: view_handled_applications)
    Route::get('/handled-applications',   [OfficerHandledApplicationsController::class, 'index']);

    // Region oversight (permission: oversee_regions)
    Route::get('/regions',                [OfficerRegionController::class, 'index']);
    Route::get('/regions/appointments',   [OfficerRegionController::class, 'appointments']);

    // Delivery / dispatch management (permission: manage_delivery)
    Route::get('/delivery/dispatch',      [OfficerDeliveryController::class, 'dispatch']);
    Route::get('/delivery/show',          [OfficerDeliveryController::class, 'show']);

    // Biometric capture (permission: capture_biometrics)
    Route::get('/biometrics/show',             [OfficerBiometricController::class, 'show']);
    Route::post('/biometrics/photo',           [OfficerBiometricController::class, 'photo']);
    Route::post('/biometrics/fingerprint',     [OfficerBiometricController::class, 'fingerprint']);
    Route::post('/biometrics/signature',       [OfficerBiometricController::class, 'signature']);
    Route::post('/biometrics/complete',        [OfficerBiometricController::class, 'complete']);

    // Cards / print management (permission: print_management)
    Route::get('/cards',         [OfficerCardsController::class, 'index']);
    Route::post('/cards/notify', [OfficerCardsController::class, 'notify']);
});

/*
|--------------------------------------------------------------------------
| Admin Routes — superadmin only
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'role:superadmin'])->prefix('v1/admin')->group(function () {

    // Permissions catalogue
    Route::get('/permissions', [AdminPermissionController::class, 'index']);

    // Officer management
    Route::get('/officers',       [AdminOfficerController::class, 'index']);
    Route::post('/officers',      [AdminOfficerController::class, 'store']);
    Route::get('/officers/show',  [AdminOfficerController::class, 'show']);
    Route::patch('/officers',     [AdminOfficerController::class, 'update']);
    Route::delete('/officers',    [AdminOfficerController::class, 'destroy']);

    // Officer direct permissions
    Route::get('/officers/permissions',    [AdminOfficerPermissionController::class, 'index']);
    Route::post('/officers/permissions',   [AdminOfficerPermissionController::class, 'store']);
    Route::delete('/officers/permissions', [AdminOfficerPermissionController::class, 'destroy']);

    // User group management
    Route::get('/groups',      [AdminUserGroupController::class, 'index']);
    Route::post('/groups',     [AdminUserGroupController::class, 'store']);
    Route::get('/groups/show', [AdminUserGroupController::class, 'show']);
    Route::patch('/groups',    [AdminUserGroupController::class, 'update']);
    Route::delete('/groups',   [AdminUserGroupController::class, 'destroy']);

    // Group sub-resources
    Route::put('/groups/permissions',    [AdminUserGroupController::class, 'syncPermissions']);
    Route::post('/groups/users',         [AdminUserGroupController::class, 'addUser']);
    Route::delete('/groups/users',       [AdminUserGroupController::class, 'removeUser']);

    // Regional office management
    Route::get('/regions',               [AdminRegionalOfficeController::class, 'index']);
    Route::post('/regions',              [AdminRegionalOfficeController::class, 'store']);
    Route::get('/regions/show',          [AdminRegionalOfficeController::class, 'show']);
    Route::patch('/regions',             [AdminRegionalOfficeController::class, 'update']);
    Route::delete('/regions',            [AdminRegionalOfficeController::class, 'destroy']);
    Route::patch('/regions/capacity',    [AdminRegionalOfficeController::class, 'updateCapacity']);
});
