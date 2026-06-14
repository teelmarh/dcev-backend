<?php

use App\Http\Controllers\Api\V1\Admin\AdminOfficerController;
use App\Http\Controllers\Api\V1\Admin\AdminOfficerPermissionController;
use App\Http\Controllers\Api\V1\Admin\AdminPermissionController;
use App\Http\Controllers\Api\V1\Admin\AdminUserGroupController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:superadmin'])->prefix('v1/admin')->group(function () {

    // Permissions catalogue
    Route::get('/permissions', [AdminPermissionController::class, 'index']);

    // Officer management (CRUD)
    Route::apiResource('officers', AdminOfficerController::class)->only(['index', 'store', 'show', 'update', 'destroy']);

    // Per-officer direct permissions
    Route::get('officers/{officer}/permissions',           [AdminOfficerPermissionController::class, 'index']);
    Route::post('officers/{officer}/permissions',          [AdminOfficerPermissionController::class, 'store']);
    Route::delete('officers/{officer}/permissions/{permission}', [AdminOfficerPermissionController::class, 'destroy']);

    // User group management (CRUD)
    Route::apiResource('groups', AdminUserGroupController::class)->only(['index', 'store', 'show', 'update', 'destroy']);

    // Group sub-resources
    Route::put('groups/{group}/permissions',     [AdminUserGroupController::class, 'syncPermissions']);
    Route::post('groups/{group}/users',          [AdminUserGroupController::class, 'addUser']);
    Route::delete('groups/{group}/users/{user}', [AdminUserGroupController::class, 'removeUser']);
});
