<?php

use App\Http\Controllers\Patient\PatientController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:100,1'])->prefix('/patients')->controller(PatientController::class)->group(function () {
    Route::get('/', 'index')
        ->middleware(['checkaccess:role:owner,secretary', 'checkaccess:permission:manage patients']);
    Route::get('/trashed', 'indexTrashed');
    Route::get('/{patientId}', 'show')
        ->middleware(['checkaccess:role:owner,secretary,doctor', 'checkaccess:permission:manage patients']);
    Route::get('/{patientId}/medical-history', 'medicalHistory')
        ->middleware(['checkaccess:role:owner,secretary,doctor,patient']);
    Route::post('/update', 'update')
        ->middleware(['checkaccess:role:patient']);
    Route::delete('/delete', 'destroy');
    Route::get('/restore', 'restore');
});
