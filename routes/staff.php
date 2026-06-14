<?php

use App\Http\Controllers\Api\V1\Admin\AdminOfficerController;
use App\Http\Controllers\Api\V1\Admin\AdminOfficerPermissionController;
use App\Http\Controllers\Api\V1\Admin\AdminPermissionController;
use App\Http\Controllers\Api\V1\Admin\AdminUserGroupController;
use App\Http\Controllers\Api\V1\Officer\OfficerDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Officer Routes — accessible by officers and superadmins
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'role:officer,superadmin'])->prefix('v1/officer')->group(function () {
    Route::get('/applications',           [OfficerDashboardController::class, 'applications']);
    Route::get('/applications/show',      [OfficerDashboardController::class, 'showApplication']);
    Route::get('/appointments/today',     [OfficerDashboardController::class, 'todayAppointments']);
    Route::get('/appointments',           [OfficerDashboardController::class, 'appointments']);
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
});
