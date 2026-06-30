<?php

use App\Http\Controllers\User\UserPhoneController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:100,1', 'checkaccess:role:patient,doctor,secretary,owner'])
    ->prefix('/phone')->controller(UserPhoneController::class)->group(function () {
        Route::post('/update', 'updatePhone');
        Route::post('/verify-update', 'verifyPhoneUpdate');
    });
