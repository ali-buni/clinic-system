<?php

use App\Http\Controllers\Invoice\InvoiceController;
use App\Http\Controllers\Invoice\PaymentController as InvoicePaymentController;
use App\Http\Controllers\Invoice\PaymentMethodController;
use App\Http\Controllers\Invoice\WebhookController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:100,1'])->group(function () {
    Route::prefix('invoices')->controller(InvoiceController::class)->group(function () {
        Route::get('patient/{patientId}', 'patientInvoices')
            ->middleware(['checkaccess:role:owner,doctor,secretary,patient', 'resourceAccess:patient_self:patientId']);
        Route::get('doctor/{doctorId}', 'doctorInvoices')
            ->middleware(['checkaccess:role:owner,doctor,secretary', 'resourceAccess:doctor_self:doctorId']);
        Route::post('rooms', 'roomsInvoices')
            ->middleware(['checkaccess:role:owner,secretary', 'resourceAccess:secretary_rooms:room_ids']);

        Route::get('/', 'index')
            ->middleware(['checkaccess:role:owner']);
        Route::post('/', 'store')
            ->middleware(['checkaccess:role:secretary,doctor', 'resourceAccess:owns:Appointment:appointment_id']);
        Route::get('{invoiceId}', 'show')
            ->middleware(['checkaccess:role:owner,doctor,secretary,patient', 'resourceAccess:owns:Invoice:invoiceId']);
        Route::put('{invoiceId}', 'update')
            ->middleware(['checkaccess:role:secretary,doctor', 'resourceAccess:owns:Invoice:invoiceId']);
        Route::delete('{invoiceId}', 'destroy')
            ->middleware(['checkaccess:role:owner', 'resourceAccess:owns:Invoice:invoiceId']);
    });
    Route::prefix('payments')->controller(InvoicePaymentController::class)->group(function () {
        Route::get('/', 'index')
            ->middleware(['checkaccess:role:owner']);
        Route::get('/{paymentId}', 'show')
            ->middleware(['checkaccess:role:owner', 'resourceAccess:owner_clinic:Payment:paymentId']);
        Route::post('/', 'store')
            ->middleware(['checkaccess:role:secretary,patient', 'resourceAccess:owns:Invoice:invoice_id']);
        Route::delete('/{paymentId}', 'destroy')
            ->middleware(['checkaccess:role:owner', 'resourceAccess:owner_clinic:Payment:paymentId']);
        Route::post('/refund', 'refund')
            ->middleware(['checkaccess:role:doctor']);
    });
});
Route::middleware(['auth:sanctum', 'throttle:100,1'])->controller(PaymentMethodController::class)->group(function () {
    Route::get('payment-methods', 'index');
    Route::post('payment-methods', 'store')->middleware('checkaccess:role:admin');
    Route::delete('payment-methods/{paymentMethodId}', 'destroy')->middleware('checkaccess:role:admin');
    Route::patch('payment-methods/{paymentMethodId}/stop', 'stop')->middleware('checkaccess:role:admin');
});
