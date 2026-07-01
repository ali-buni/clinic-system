<?php

use App\Http\Controllers\Doctor\DoctorSpecialtyController;
use Illuminate\Support\Facades\Route;

Route::prefix('/specialties')->controller(DoctorSpecialtyController::class)->group(function () {
    Route::middleware(['auth:sanctum', 'throttle:100,1', 'checkaccess:role:doctor'])->group(function () {
        Route::post('/', 'attachSpecialties');
        Route::delete('/{specialId}', 'detachSpecialty');
        Route::post('/{specialtyId}/primary', 'changePrimary');
        Route::get('/doctor/{doctorId}/primary', 'showPrimary');
        Route::get('/doctor/{doctorId}', 'showDoctorSpecialties');
    });
    Route::get('/', 'index');
});
