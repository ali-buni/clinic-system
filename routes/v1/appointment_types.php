<?php

use App\Http\Controllers\Appointment\AppointmentTypeController;
use Illuminate\Support\Facades\Route;

Route::prefix('appointment-types')->controller(AppointmentTypeController::class)->group(function () {
    Route::get('/', 'index');
    Route::post('/', 'add')
        ->middleware(['auth:sanctum', 'throttle:100,1', 'checkaccess:role:owner', 'checkaccess:permission:admin']);
    Route::delete('/{id}', 'delete')
        ->middleware(['auth:sanctum', 'throttle:100,1', 'checkaccess:role:owner', 'checkaccess:permission:admin']);
});
