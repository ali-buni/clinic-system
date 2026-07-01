<?php

use App\Http\Controllers\Auth\VerificationController;
use Illuminate\Support\Facades\Route;

Route::controller(VerificationController::class)->group(function () {
    Route::post('/verify-code', 'verifyCode')->middleware('throttle:10,1');
    Route::post('/resend-code', 'resendVerificationCode')->middleware('throttle:10,1');
});
