<?php

use App\Http\Controllers\Secretary\SecretaryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:100,1'])->prefix('/secretaries')->controller(SecretaryController::class)->group(function () {
    Route::get('/{id}', 'info')
        ->middleware(['checkaccess:role:owner,secretary,doctor']);
    Route::post('/update', 'update')
        ->middleware(['checkaccess:role:secretary']);
});
