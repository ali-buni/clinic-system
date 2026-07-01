<?php

use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:100,1'])->prefix('/users')->controller(UserController::class)->group(function () {
    Route::get('/info', 'info')
        ->middleware('checkaccess:role:patient,doctor,secretary,owner');
    Route::post('/update-image', 'updateImage')
        ->middleware('checkaccess:role:patient,doctor,secretary,owner');
    Route::get('/image-url', 'getImageUrl')
        ->middleware('checkaccess:role:patient,doctor,secretary,owner');
});
