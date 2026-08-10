<?php

use App\Http\Controllers\Secretary\SecretaryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:100,1'])->prefix('/secretaries')->controller(SecretaryController::class)->group(function () {
    Route::get('filter', 'index')
        ->middleware(['checkaccess:role:owner,secretary']);
    Route::get('/{id}', 'info')
        ->middleware(['checkaccess:role:owner,secretary,doctor', 'resourceAccess:owns:Secretary:id']);
    Route::post('/update', 'update')
        ->middleware(['checkaccess:role:secretary']);
    Route::delete('/{id}/leave', 'destroy')
        ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin', 'resourceAccess:owns:Secretary:id']);
    Route::post('/{id}/restore', 'restore')->withTrashed()
        ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin', 'resourceAccess:owns:Secretary:id']);
});
