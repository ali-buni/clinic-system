<?php

use App\Http\Controllers\PatientRecord\PatientRecordController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:100,1'])->prefix('patient-records')->controller(PatientRecordController::class)->group(function () {
    Route::post('/', 'store')
        ->middleware(['checkaccess:role:doctor', 'checkaccess:permission:access records', 'resourceAccess:doctor_self:doctor_id']);
    Route::put('/{id}', 'update')
        ->middleware(['checkaccess:role:doctor', 'checkaccess:permission:access records', 'resourceAccess:owns:Patient_record:id']);
    Route::delete('/{id}', 'destroy')
        ->middleware(['checkaccess:role:doctor', 'checkaccess:permission:access records', 'resourceAccess:owns:Patient_record:id']);
    Route::get('/{id}', 'show')
        ->middleware(['checkaccess:role:doctor,patient,secretary', 'resourceAccess:owns:Patient_record:id']);
    Route::get('/', 'index')
        ->middleware(['checkaccess:role:doctor,owner', 'checkaccess:permission:access records']);
    Route::get('/patient/{patientId}/history', 'history')
        ->middleware(['checkaccess:role:doctor,patient', 'resourceAccess:patient_self:patientId']);
    Route::get('/patient/{patientId}/doctor/{doctorId}', 'getByDoctor')
        ->middleware(['checkaccess:role:doctor', 'checkaccess:permission:access records', 'resourceAccess:doctor_self:doctorId']);
    Route::post('/rooms/search', 'getByRoom')
        ->middleware(['checkaccess:role:secretary', 'resourceAccess:secretary_rooms:room_ids']);
    Route::get('/doctor/{doctorId}/all', 'getAllByDoctor')
        ->middleware(['checkaccess:role:doctor', 'checkaccess:permission:access records', 'resourceAccess:doctor_self:doctorId']);
});
