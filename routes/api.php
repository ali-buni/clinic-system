<?php

use App\Http\Controllers\Api\GoogleAuthController;
use App\Http\Controllers\Invoice\WebhookController;
use App\Models\User;
use App\Notifications\MobileNotification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
        'app' => config('app.name'),
        'env' => config('app.env'),
    ]);
});

Route::post('stripe/webhook', [WebhookController::class, 'handle']);

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
    require __DIR__ . '/v1/items.php';
    require __DIR__ . '/v1/rooms.php';
    require __DIR__ . '/v1/secretaries.php';
    require __DIR__ . '/v1/patients.php';
    require __DIR__ . '/v1/users.php';
    require __DIR__ . '/v1/doctors.php';
    require __DIR__ . '/v1/appointments.php';
    require __DIR__ . '/v1/patient_records.php';
    require __DIR__ . '/v1/ai.php';
    require __DIR__ . '/v1/invoices.php';
    require __DIR__ . '/v1/doctor-withdrawals.php';
    require __DIR__ . '/v1/location.php';
});

Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Endpoint not found.',
    ], 404);
});

Route::post('/send-notification', function (Request $request, NotificationService $notificationService) {
    $validated = $request->validate([
        'receiver_id' => 'required|integer|exists:users,id',
        'title' => 'sometimes|string|max:255',
        'body' => 'sometimes|string|max:1000',
        'data' => 'sometimes|array',
    ]);

    $receiver = User::findOrFail($validated['receiver_id']);

    if ($notificationService->sendToUser(
        $receiver->id,
        $validated['title'] ?? 'Welcome!',
        $validated['body'] ?? 'Your account has been successfully activated on the mobile app.',
        $validated['data'] ?? ['screen' => 'profile', 'badge' => '1']
    )) {
        return response()->json(['message' => 'The notification was sent successfully to the recipient device.']);
    }

    return response()->json(['error' => 'This user does not have a registered Firebase token.'], 422);
})->middleware('auth:sanctum');
