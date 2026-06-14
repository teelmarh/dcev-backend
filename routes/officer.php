<?php

use App\Http\Controllers\Api\V1\Officer\OfficerDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:officer,superadmin'])->prefix('v1/officer')->group(function () {
    Route::get('/applications',           [OfficerDashboardController::class, 'applications']);
    Route::get('/applications/{licence}', [OfficerDashboardController::class, 'showApplication']);
    Route::get('/appointments/today',     [OfficerDashboardController::class, 'todayAppointments']);
    Route::get('/appointments',           [OfficerDashboardController::class, 'appointments']);
});
