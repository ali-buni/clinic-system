<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {
    Route::post('/login', 'login')->middleware('throttle:5,1');
    Route::post('/register', 'register')->middleware('throttle:5,1');
    Route::post('/forgot-password', 'forgotPassword')->middleware('throttle:5,1');
    Route::post('/reset-password-with-code', 'resetWithCode');

    Route::middleware(['auth:sanctum', 'throttle:100,1', 'checkaccess:role:patient,doctor,secretary,owner'])->group(function () {
        Route::post('/signout', 'signOut');
        Route::post('/reset-password', 'resetPassword');
        Route::post('/refresh-token', 'refreshToken');
    });
});
