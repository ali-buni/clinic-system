<?php

use App\Http\Controllers\Invoice\InvoiceController;
use App\Http\Controllers\Invoice\PaymentController as InvoicePaymentController;
use App\Http\Controllers\Invoice\PaymentMethodController;
use App\Http\Controllers\Invoice\WebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('invoices')->controller(InvoiceController::class)->group(function () {
    Route::get('patient/{patient}', 'patientInvoices');
    Route::get('doctor/{doctor}', 'doctorInvoices');
    Route::post('rooms', 'roomsInvoices');

    Route::get('/', 'index');
    Route::post('/', 'store');
    Route::get('{invoice}', 'show');
    Route::put('{invoice}', 'update');
    Route::delete('{invoice}', 'destroy');
});
Route::prefix('payments')->controller(InvoicePaymentController::class)->group(function () {
    Route::get('/', 'index');
    Route::get('/{paymentId}', 'show');
    Route::post('/', 'store');
    Route::delete('/{paymentId}', 'destroy');
});
Route::middleware('auth:sanctum')->controller(PaymentMethodController::class)->group(function () {
    Route::get('payment-methods', 'index');
    Route::post('payment-methods', 'store');
    Route::delete('payment-methods/{paymentMethodId}', 'destroy');
    Route::patch('payment-methods/{paymentMethodId}/stop', 'stop');
});

Route::post('stripe/webhook', [WebhookController::class, 'handle']);
