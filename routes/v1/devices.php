<?php

use App\Http\Controllers\Device\FcmTokenController;
use Illuminate\Support\Facades\Route;

Route::post('/devices/register-token', [FcmTokenController::class, 'registerDeviceToken'])
    ->middleware(['auth:sanctum', 'throttle:100,1', 'checkaccess:role:patient,doctor,secretary,owner']);
