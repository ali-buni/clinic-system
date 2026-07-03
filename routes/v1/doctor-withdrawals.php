<?php

use App\Http\Controllers\Doctor\DoctorWithdrawalController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:100,1', 'checkaccess:role:doctor'])->prefix('doctor-withdrawals')->group(function () {
    Route::get('/', [DoctorWithdrawalController::class, 'index']);
    Route::post('/', [DoctorWithdrawalController::class, 'store']);
    Route::get('/balance', [DoctorWithdrawalController::class, 'getBalance']);
    Route::post('/setup-stripe', [DoctorWithdrawalController::class, 'setupStripe']);
});
