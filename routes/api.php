<?php

use App\Http\Controllers\Api\GoogleAuthController;
use App\Models\User;
use App\Services\ApiResponse;
use App\Services\ModelFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware(['auth:sanctum', 'throttle:100,1', 'checkaccess:role:patient,doctor,secretary,owner']);

Route::prefix('/auth')->controller(GoogleAuthController::class)->group(function () {
    Route::get('google', 'redirectToGoogle');
    Route::get('google/callback', 'handleGoogleCallback');
});

Route::prefix('/v1/clinic-system')->group(function () {
    require __DIR__ . '/v1/auth.php';
    require __DIR__ . '/v1/verification.php';
    require __DIR__ . '/v1/phone.php';
    require __DIR__ . '/v1/devices.php';
    require __DIR__ . '/v1/appointment_types.php';
    require __DIR__ . '/v1/analytics.php';
    require __DIR__ . '/v1/clinic.php';
    require __DIR__ . '/v1/specialties.php';
    require __DIR__ . '/v1/schedules.php';
    require __DIR__ . '/v1/schedule_overrides.php';
    require __DIR__ . '/v1/medicines.php';
    require __DIR__ . '/v1/diseases.php';
    require __DIR__ . '/v1/rooms.php';
    require __DIR__ . '/v1/secretaries.php';
    require __DIR__ . '/v1/patients.php';
    require __DIR__ . '/v1/users.php';
    require __DIR__ . '/v1/doctors.php';
    require __DIR__ . '/v1/appointments.php';
    require __DIR__ . '/v1/patient_records.php';
    require __DIR__ . '/v1/ai.php';
});

Route::get('/filter', function (Request $request) {
    return ApiResponse::pagination(ModelFilter::filter(new User(), $request->all()));
});

Route::prefix('/clinic-system')->group(function () {
    Route::prefix('invoices')->controller(InvoiceController::class)->group(function () {
        Route::post('/create', 'createInvoice');
        Route::put('/update', 'updateInvoice');
        Route::delete('/delete', 'deleteInvoice');
        Route::get('/patient/{Patient_id}', 'getPatientInvoices');
        Route::post('/rooms', 'getRoomsInvoices');
        Route::get('/doctor/{doctorId}', 'getDoctorInvoices');
        Route::get('/details/{invoice_id}', 'getInvoiceWithPayments');
        Route::get('/filtered', 'getAllInvoicesFiltered');
    });
    Route::get('/payment_method',[PaymentMethodController::class,'getPaymentMethods']);
    Route::post('/payments/process', [PaymentController::class, 'processPayment']);        // دفع الفاتورة وتسجيل مبلغ (جزئي/كلي)[cite: 1]
    Route::delete('/payments/{payment_id}/cancel', [PaymentController::class, 'cancelPayment']);
    Route::post('/stripe/webhook', [WebhookController::class, 'handleStripeWebhook']);
});



// Route::post('/send-notification', function (Request $request) {

//     $receiver = User::findOrFail($request->receiver_id);
//     if (!$receiver->fcm_token) {
//         return response()->json(['error' => 'هذا المستخدم لا يملك توكن فايربيس مسجل'], 422);
//     }

//     $receiver->notify(new MobileNotification(
//         'مرحباً بك!',
//         'تم تفعيل حسابك بنجاح على تطبيق الجوال.',
//         ['screen' => 'profile', 'badge' => '1']
//     ));

//     return response()->json(['message' => 'تم إرسال الإشعار إلى الفايربيس الخاص بالمتلقي بنجاح!']);
// });
