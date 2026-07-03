<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\ClinicController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PaymentUrlController;
use App\Http\Controllers\Admin\StructuredLogController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [DashboardController::class, 'loginForm'])->name('login');
    Route::post('login', [DashboardController::class, 'login'])->name('login.post');

    Route::post('logout', [DashboardController::class, 'logout'])->name('logout')->middleware('auth');

    Route::middleware(['auth', 'role:admin'])->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('clinics', ClinicController::class);
        Route::post('clinics/{clinic}/restore', [ClinicController::class, 'restore'])->name('clinics.restore');
        Route::get('clinics/{clinic}/send-payment', [PaymentUrlController::class, 'sendPaymentForm'])->name('clinics.send-payment');
        Route::post('clinics/{clinic}/send-payment', [PaymentUrlController::class, 'sendPayment'])->name('clinics.send-payment.store');

        Route::resource('users', UserController::class);
        Route::post('users/{user}/restore', [UserController::class, 'restore'])->name('users.restore');

        Route::get('payment-urls', [PaymentUrlController::class, 'index'])->name('payment-urls.index');
        Route::get('payment-urls/{payment}', [PaymentUrlController::class, 'show'])->name('payment-urls.show');
        Route::post('payment-urls/{payment}/resend', [PaymentUrlController::class, 'resend'])->name('payment-urls.resend');

        Route::get('logs', [ActivityLogController::class, 'index'])->name('logs.index');
        Route::get('logs/{id}', [ActivityLogController::class, 'show'])->name('logs.show');

        Route::get('structured-logs', [StructuredLogController::class, 'index'])->name('structured-logs.index');
        Route::get('structured-logs/{date}', [StructuredLogController::class, 'show'])->name('structured-logs.show');
        Route::get('structured-logs/{date}/download', [StructuredLogController::class, 'download'])->name('structured-logs.download');
    });
});
