<?php

use App\Http\Controllers\PaymentCallbackController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/health', function () {
    return response()->json(['status' => 'ok'], 200);
});

Route::get('/payment-success', [PaymentCallbackController::class, 'success'])->name('payment.success');
Route::get('/payment-failed', [PaymentCallbackController::class, 'failed'])->name('payment.failed');

require __DIR__.'/admin.php';
