<?php

use App\Http\Controllers\Patient\PatientController;
use App\Http\Controllers\Patient\PatientDoctorSearchController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:100,1'])->prefix('/patients')->controller(PatientController::class)->group(function () {
    Route::get('/', 'index')
        ->middleware(['checkaccess:role:owner', 'checkaccess:permission:manage patients']);
    Route::get('/trashed', 'indexTrashed')
        ->middleware(['checkaccess:role:owner']);
    Route::get('/{patientId}', 'show')
        ->middleware(['checkaccess:role:owner,secretary,doctor', 'checkaccess:permission:manage patients', 'resourceAccess:patient_self:patientId']);
    Route::get('/{patientId}/medical-history', 'medicalHistory')
        ->middleware(['checkaccess:role:owner,secretary,doctor,patient', 'resourceAccess:patient_self:patientId']);
    Route::post('/update', 'update')
        ->middleware(['checkaccess:role:patient', 'resourceAccess:patient_self:patient_id']);
    Route::delete('/delete', 'destroy')
        ->middleware(['checkaccess:role:owner']);
    Route::get('/restore/patient', 'restore')
        ->middleware(['checkaccess:role:owner']);
});
Route::middleware(['auth:sanctum', 'throttle:100,1'])->prefix('/patients')->controller(PatientDoctorSearchController::class)->group(function () {
        Route::get('/search/doctors', 'search')
            ->middleware(['checkaccess:role:patient']);
});