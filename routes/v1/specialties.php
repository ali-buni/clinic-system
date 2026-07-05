<?php

use App\Http\Controllers\Doctor\DoctorSpecialtyController;
use Illuminate\Support\Facades\Route;

Route::prefix('/specialties')->controller(DoctorSpecialtyController::class)->group(function () {
    Route::middleware(['auth:sanctum', 'throttle:100,1', 'checkaccess:role:doctor'])->group(function () {
        Route::post('/', 'attachSpecialties');
        Route::delete('/{specialId}', 'detachSpecialty');
        Route::post('/{specialtyId}/primary', 'changePrimary');
        Route::get('/doctor/{doctorId}/primary', 'showPrimary')
            ->middleware('resourceAccess:doctor_self:doctorId');
        Route::get('/doctor/{doctorId}', 'showDoctorSpecialties')
            ->middleware('resourceAccess:doctor_self:doctorId');
    });
    Route::get('/', 'index');
});
