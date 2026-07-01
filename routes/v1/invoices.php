<?php

use App\Http\Controllers\Invoice\PaymentMethodController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('invoices')->name('invoices.')->controller(InvoiceController::class)->group(function () {
    Route::get('patient/{patient}', 'patientInvoices')->name('patient');
    Route::get('doctor/{doctor}', 'doctorInvoices')->name('doctor');
    Route::post('rooms', 'roomsInvoices')->name('rooms');

    Route::get('/', 'index')->name('index');
    Route::post('/', 'store')->name('store');
    Route::get('{invoice}', 'show')->name('show');
    Route::put('{invoice}', 'update')->name('update');
    Route::delete('{invoice}', 'destroy')->name('destroy');

    Route::post('{invoice}/payments', [PaymentController::class, 'store'])->name('payments.store');
});

Route::apiResource('payment-methods', PaymentMethodController::class)->only(['index', 'store', 'destroy']);

Route::delete('payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');

Route::post('stripe/webhook', [WebhookController::class, 'handle'])->name('stripe.webhook');
