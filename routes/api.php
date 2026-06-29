<?php

use App\Http\Controllers\FcmTokenController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PatientRecordController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\SecretaryController;
use App\Http\Controllers\DoctorScheduleController;
use App\Http\Controllers\DoctorSpecialtyController;
use App\Http\Controllers\VerificationController;
use App\Models\User;
use App\Services\ApiResponse;
use App\Services\ModelFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\DiseaseController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\userController;
use App\Http\Controllers\AppointmentTypeController;
use App\Http\Controllers\Api\AnalyticsController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('/clinic-system')->group(function () {
    Route::post('/devices/register-token', [FcmTokenController::class, 'registerDeviceToken'])->middleware('auth:sanctum');

    Route::controller(AuthController::class)->group(function () {
        Route::post('/login', 'login');
        Route::post('/register', 'register');
        Route::post('/forgot-password', 'forgotPassword');
        Route::post('/reset-password-with-code', 'resetWithCode');

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/signout', 'signOut');
            Route::post('/reset-password', 'resetPassword');
            Route::post('/refresh-token', 'refreshToken');
        });
    });

    Route::controller(VerificationController::class)->group(function () {
        Route::post('/verify-code', 'verifyCode');
        Route::post('/resend-code', 'resendVerificationCode');
    });

    Route::prefix('/clinic')->group(function () {

        Route::prefix('/specialty')->controller(DoctorSpecialtyController::class)->group(function () {
            Route::middleware('auth:sanctum')->group(function () {
                Route::post('/add', 'attachSpecialties');
                Route::delete('/delete/{specialId}', 'detachSpecialty');
                Route::post('/changePrimary/{specialtyId}', 'changePrimary');
                Route::get('showPrimary/{doctorId}', 'showPrimary');
                Route::get('getAll', 'showDoctorSpecialties');
            });
            // no auth
            Route::get('index', 'index');
            // store
            // delete
        });
        Route::prefix('/schedule')->controller(DoctorScheduleController::class)->group(function () {
            Route::middleware('auth:sanctum')->group(function () {
                Route::post('/add', 'store');
                Route::put('/edit', 'update');
                Route::delete('/delete/{dayOfWeek}/{doctorId}', 'destroy');
            });
            // no auth
            Route::get('/get-weekly/{doctorId}', 'getWeeklySchedule');
            Route::get('/work-hour/{doctorId}', 'getWorkHourByDate');
        });

        // no auth
        Route::prefix('medicines')->controller(MedicineController::class)->group(function () {
            Route::get('search', 'searchMedicine');
            Route::post('store', 'store');
        });
        Route::prefix('diseases')->controller(DiseaseController::class)->group(function () {
            Route::get('search', 'searchDisease');
            Route::post('store', 'store');
        });

        // auth
        Route::middleware('auth:sanctum')->prefix('/clinic')->group(function () {
            Route::controller(ClinicController::class)->group(function () {
                Route::get('/info', 'clinicInfo');
                Route::post('/update/{clinicId}', 'updateClinic');

                Route::post('doctor/register', 'createDoctor');
                Route::post('secretary/register', 'createSecretary');
            });

            Route::prefix('/rooms')->controller(RoomController::class)->group(function () {
                Route::get('/{clinicId}', 'index');
                Route::get('/{clinicId}/info', 'indexWithInfo');
                Route::get('/{roomId}/details', 'get');
                Route::get('/userRooms/get', 'userRooms');
                Route::post('/', 'create');
                Route::post('/{roomId}', 'update');
                Route::delete('/{roomId}', 'destroy');
                Route::post('/sync/doctorRoom', 'addDoctorToRoom');
                Route::post('/sync/secRooms', 'addSecToRoom');
                Route::delete('/detach/doctorRoom', 'delDoctorFromRoom');
                Route::delete('/detach/secRooms', 'delSecFromRoom');
            });

            Route::prefix('/secretaries')->controller(SecretaryController::class)->group(function () {
                Route::get('/{id}', 'info');
                Route::post('/update', 'update');
            });

            Route::prefix('/patients')->controller(PatientController::class)->group(function () {
                Route::get('/', 'index');
                Route::get('/trashed', 'indexTrashed');
                Route::get('/{patientId}/show', 'show');
                Route::get('/{patientId}/medical-history', 'medicalHistory');
                Route::post('/update', 'update');
                Route::delete('/delete', 'destroy');
                Route::get('/restore', 'restore');
            });

            Route::prefix('/users')->controller(userController::class)->group(function () {
                Route::get('/info', 'info');
            });

            Route::prefix('/doctors')->controller(DoctorController::class)->group(function () {
                Route::post('/update', 'update');
                Route::get('/{id}/info', 'info');
                Route::get('filter', 'index');
                Route::delete('/{id}/leave', 'destroy');
                Route::post('/{id}/restore', 'restore')->withTrashed();
                Route::delete('/{id}/force', 'forceDelete')->withTrashed();
            });

            Route::prefix('/appointments')->controller(AppointmentController::class)->group(function () {
                Route::post('/book', 'book');
                Route::post('/{id}/reschedule', 'reschedule');
                Route::post('/{id}/cancel', 'cancel');
                Route::post('/{id}/complete', 'complete');
                Route::post('/{id}/confirmed', 'markConfirmed');
                Route::get('/{id}', 'show');
                Route::get('/patient/{patientId}', 'patientAppointments');
                Route::get('/doctor/{doctorId}', 'doctorAppointments');
                Route::get('/clinic/{clinicId}', 'clinicAppointments');
                Route::get('/room/appo', 'roomAppointments');
                Route::get('/doctor/{doctorId}/schedule', 'doctorSchedule');
                Route::get('/clinic/{clinicId}/schedule', 'clinicSchedule');
                Route::get('/get/available-slots', 'availableSlots');
            });

            Route::prefix('patient-records')->controller(PatientRecordController::class)->group(function () {
                Route::post('/', 'store');
                Route::put('/{id}', 'update');
                Route::delete('/{id}', 'destroy');
                Route::get('/show/{id}', 'show');
                Route::get('/filtered', 'index');
                Route::get('/patient/{patientId}/history', 'history');
                Route::get('/patient/{patientId}/doctor/{doctorId}', 'getByDoctor');
                Route::post('/rooms/search', 'getByRoom');
                Route::get('/doctor/{doctorId}/all', 'getAllByDoctor');
            });
        });
    });
});

// no auth
Route::get('/filter', function (Request $request) {
    return ApiResponse::pagination(ModelFilter::filter(new User(), $request->all()));
});

Route::prefix('/clinic-system')->group(function () {
    Route::prefix('appointment-types')->controller(AppointmentTypeController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'add');
        Route::delete('/{id}', 'delete');
    });
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
// جميع المسارات محمية بـ auth:sanctum لضمان أن مدير العيادة فقط من يستعلم
Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('clinic-system/analytics')->group(function () {
        // التحليل التشغيلي
        Route::post('/operational', [AnalyticsController::class, 'getOperationalReport']);

        // التحليل المالي
        Route::post('/financial', [AnalyticsController::class, 'getFinancialReport']);

        // تحليل المرضى
        Route::post('/patients', [AnalyticsController::class, 'getPatientReport']);

        // التحليل الطبي
        Route::get('/medical', [AnalyticsController::class, 'getMedicalReport']);

        // التنبؤات
        Route::post('/predictive', [AnalyticsController::class, 'getPredictiveReport']);

        // الذكاء الاصطناعي التوليدي (الاستعلام باللغة الطبيعية)
        Route::post('/nla', [AnalyticsController::class, 'askAnalytics']);

        // مؤشر الصحة العام
        Route::post('/health-score', [AnalyticsController::class, 'getHealthScore']);

        // لوحة المعلومات السريعة
        Route::post('/dashboard', [AnalyticsController::class, 'getDashboard']);

        // حفظ لقطة بيانات
        Route::post('/snapshot', [AnalyticsController::class, 'storeSnapshot']);
    });
});
