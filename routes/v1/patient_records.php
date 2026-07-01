<?php

use App\Http\Controllers\PatientRecord\PatientRecordController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:100,1'])->prefix('patient-records')->controller(PatientRecordController::class)->group(function () {
    Route::post('/', 'store')
        ->middleware(['checkaccess:role:doctor', 'checkaccess:permission:access records']);
    Route::put('/{id}', 'update')
        ->middleware(['checkaccess:role:doctor', 'checkaccess:permission:access records']);
    Route::delete('/{id}', 'destroy')
        ->middleware(['checkaccess:role:doctor', 'checkaccess:permission:access records']);
    Route::get('/{id}', 'show')
        ->middleware(['checkaccess:role:doctor,patient,secretary']);
    Route::get('/', 'index')
        ->middleware(['checkaccess:role:doctor,owner', 'checkaccess:permission:access records']);
    Route::get('/patient/{patientId}/history', 'history')
        ->middleware(['checkaccess:role:doctor,patient']);
    Route::get('/patient/{patientId}/doctor/{doctorId}', 'getByDoctor')
        ->middleware(['checkaccess:role:doctor', 'checkaccess:permission:access records']);
    Route::post('/rooms/search', 'getByRoom')
        ->middleware(['checkaccess:role:secretary']);
    Route::get('/doctor/{doctorId}/all', 'getAllByDoctor')
        ->middleware(['checkaccess:role:doctor', 'checkaccess:permission:access records']);
});
