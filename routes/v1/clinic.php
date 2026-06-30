<?php

use App\Http\Controllers\Clinic\ClinicController;
use Illuminate\Support\Facades\Route;

Route::get('/info', [ClinicController::class, 'clinicInfo']);

Route::middleware(['auth:sanctum', 'throttle:100,1'])->group(function () {
    Route::post('/update/{clinicId}', [ClinicController::class, 'updateClinic'])
        ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin']);
    Route::post('/doctors/register', [ClinicController::class, 'createDoctor'])
        ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin']);
    Route::post('/secretaries/register', [ClinicController::class, 'createSecretary'])
        ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin']);
});
