<?php

use App\Http\Controllers\Doctor\DoctorController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:100,1'])->prefix('/doctors')->controller(DoctorController::class)->group(function () {
    Route::post('/update', 'update')
        ->middleware(['checkaccess:role:doctor']);
    Route::get('/{id}/info', 'info')
        ->middleware(['checkaccess:role:owner,secretary,doctor', 'resourceAccess:doctor_self:id']);
    Route::get('filter', 'index')
        ->middleware(['checkaccess:role:owner,secretary']);
    Route::delete('/{id}/leave', 'destroy')
        ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin', 'resourceAccess:owns:Doctor:id']);
    Route::post('/{id}/restore', 'restore')->withTrashed()
        ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin', 'resourceAccess:owns:Doctor:id']);
    Route::delete('/{id}/force', 'forceDelete')->withTrashed()
        ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin', 'resourceAccess:owns:Doctor:id']);
});
