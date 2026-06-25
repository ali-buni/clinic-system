<?php

use App\Http\Controllers\Api\GoogleAuthController;
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
use App\Http\Controllers\UserPhoneController;
use App\Http\Controllers\ScheduleOverrideController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware(['auth:sanctum', 'checkaccess:role:patient,doctor,secretary,owner']);

Route::prefix('auth')->controller(GoogleAuthController::class)->group(function () {
    Route::get('google', 'redirectToGoogle');
    Route::get('google/callback', 'handleGoogleCallback');
});

Route::prefix('/clinic-system')->group(function () {
    Route::post('/devices/register-token', [FcmTokenController::class, 'registerDeviceToken'])
        ->middleware(['auth:sanctum', 'checkaccess:role:patient,doctor,secretary,owner']);

    Route::controller(AuthController::class)->group(function () {
        Route::post('/login', 'login');
        Route::post('/register', 'register');
        Route::post('/forgot-password', 'forgotPassword');
        Route::post('/reset-password-with-code', 'resetWithCode');

        Route::middleware(['auth:sanctum', 'checkaccess:role:patient,doctor,secretary,owner'])->group(function () {
            Route::post('/signout', 'signOut');
            Route::post('/reset-password', 'resetPassword');
            Route::post('/refresh-token', 'refreshToken');
        });
    });

    Route::controller(VerificationController::class)->group(function () {
        Route::post('/verify-code', 'verifyCode');
        Route::post('/resend-code', 'resendVerificationCode');
    });

    Route::middleware(['auth:sanctum', 'checkaccess:role:patient,doctor,secretary,owner'])
        ->prefix('/phone')->controller(UserPhoneController::class)->group(function () {
            Route::post('/update', 'updatePhone');
            Route::post('/verify-update', 'verifyPhoneUpdate');
        });

    Route::prefix('/clinic')->group(function () {

        Route::prefix('/specialty')->controller(DoctorSpecialtyController::class)->group(function () {
            Route::middleware(['auth:sanctum', 'checkaccess:role:owner,doctor'])->group(function () {
                Route::post('/add', 'attachSpecialties');
                Route::delete('/delete/{specialId}', 'detachSpecialty');
                Route::post('/changePrimary/{specialtyId}', 'changePrimary');
                Route::get('showPrimary/{doctorId}', 'showPrimary');
                Route::get('getAll', 'showDoctorSpecialties');
            });
            Route::get('index', 'index');
        });

        Route::prefix('/schedule')->group(function () {
            Route::controller(DoctorScheduleController::class)->group(function () {
                Route::middleware([
                    'auth:sanctum',
                    'checkaccess:role:owner,doctor',
                    'checkaccess:permission:manage schedules',
                ])->group(function () {
                    Route::post('/add', 'store');
                    Route::put('/edit', 'update');
                    Route::delete('/delete/{dayOfWeek}/{doctorId}', 'destroy');
                });
                Route::get('/get-weekly/{doctorId}', 'getWeeklySchedule');
                Route::get('/work-hour/{doctorId}', 'getWorkHourByDate');
            });

            Route::prefix('/override')->controller(ScheduleOverrideController::class)->group(function () {
                Route::middleware([
                    'auth:sanctum',
                    'checkaccess:role:doctor,secretary,owner',
                    'checkaccess:permission:manage overrides',
                ])->group(function () {
                    Route::post('/add', 'store');
                    Route::put('/{id}/edit', 'update');
                    Route::delete('/{id}/delete', 'destroy');
                });
                Route::middleware([
                    'auth:sanctum',
                    'checkaccess:role:doctor,secretary,owner',
                    'checkaccess:permission:view overrides'
                ])->group(function () {
                    Route::get('/{id}', 'show');
                    Route::get('/', 'index');
                    Route::get('/date/single', 'getByDate');
                    Route::get('/date/range', 'getByDateRange');
                });
            });
        });

        Route::prefix('medicines')->controller(MedicineController::class)->group(function () {
            Route::get('search', 'searchMedicine');
            Route::post('store', 'store')
                ->middleware(['auth:sanctum', 'checkaccess:role:doctor,secretary,owner', 'checkaccess:permission:manage m/d']);
        });

        Route::prefix('diseases')->controller(DiseaseController::class)->group(function () {
            Route::get('search', 'searchDisease');
            Route::post('store', 'store')
                ->middleware(['auth:sanctum', 'checkaccess:role:doctor,secretary,owner', 'checkaccess:permission:manage m/d']);
        });

        Route::middleware('auth:sanctum')->prefix('/clinic')->group(function () {

            Route::controller(ClinicController::class)->group(function () {
                Route::get('/info', 'clinicInfo')
                    ->middleware('checkaccess:role:doctor,secretary,owner,patient');
                Route::post('/update/{clinicId}', 'updateClinic')
                    ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin']);
                Route::post('doctor/register', 'createDoctor')
                    ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin']);
                Route::post('secretary/register', 'createSecretary')
                    ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin']);
            });

            Route::prefix('/rooms')->controller(RoomController::class)->group(function () {
                Route::get('/userRooms/get', 'userRooms')
                    ->middleware('checkaccess:role:patient,doctor,secretary,owner');
                Route::get('/{clinicId}', 'index')
                    ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin']);
                Route::get('/{clinicId}/info', 'indexWithInfo')
                    ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin']);
                Route::get('/{roomId}/details', 'get')
                    ->middleware(['checkaccess:role:owner,secretary,doctor']);
                Route::post('/', 'create')
                    ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin']);
                Route::post('/{roomId}', 'update')
                    ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin']);
                Route::delete('/{roomId}', 'destroy')
                    ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin']);
                Route::post('/sync/doctorRoom', 'addDoctorToRoom')
                    ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin']);
                Route::post('/sync/secRooms', 'addSecToRoom')
                    ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin']);
                Route::delete('/detach/doctorRoom', 'delDoctorFromRoom')
                    ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin']);
                Route::delete('/detach/secRooms', 'delSecFromRoom')
                    ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin']);
            });

            Route::prefix('/secretaries')->controller(SecretaryController::class)->group(function () {
                Route::get('/{id}', 'info')
                    ->middleware(['checkaccess:role:owner,secretary,doctor']);
                Route::post('/update', 'update')
                    ->middleware(['checkaccess:role:secretary']);
            });

            Route::prefix('/patients')->controller(PatientController::class)->group(function () {
                Route::get('/', 'index')
                    ->middleware(['checkaccess:role:owner,secretary', 'checkaccess:permission:manage patients']);
                Route::get('/trashed', 'indexTrashed');
                // TODO: admin
                // ->middleware(['checkaccess:role:owner,secretary', 'checkaccess:permission:manage patients']);
                Route::get('/{patientId}/show', 'show')
                    ->middleware(['checkaccess:role:owner,secretary,doctor', 'checkaccess:permission:manage patients']);
                Route::get('/{patientId}/medical-history', 'medicalHistory')
                    ->middleware(['checkaccess:role:owner,secretary,doctor,patient']);
                Route::post('/update', 'update')
                    ->middleware(['checkaccess:role:patient']);
                Route::delete('/delete', 'destroy');
                // TODO: admin
                // ->middleware(['checkaccess:role:owner,secretary', 'checkaccess:permission:manage patients']);
                Route::get('/restore', 'restore');
                // ->middleware(['checkaccess:role:owner,secretary', 'checkaccess:permission:manage patients']);
            });

            Route::prefix('/users')->controller(userController::class)->group(function () {
                Route::get('/info', 'info')
                    ->middleware('checkaccess:role:patient,doctor,secretary,owner');
                Route::post('/update-image', 'updateImage')
                    ->middleware('checkaccess:role:patient,doctor,secretary,owner');
                Route::get('/image-url', 'getImageUrl')
                    ->middleware('checkaccess:role:patient,doctor,secretary,owner');
            });

            Route::prefix('/doctors')->controller(DoctorController::class)->group(function () {
                Route::post('/update', 'update')
                    ->middleware(['checkaccess:role:doctor']);
                Route::get('/{id}/info', 'info')
                    ->middleware(['checkaccess:role:owner,secretary,doctor']);
                Route::get('filter', 'index')
                    ->middleware(['checkaccess:role:owner,secretary']);
                Route::delete('/{id}/leave', 'destroy')
                    ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin']);
                Route::post('/{id}/restore', 'restore')->withTrashed()
                    ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin']);
                Route::delete('/{id}/force', 'forceDelete')->withTrashed()
                    ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin']);
            });

            Route::prefix('/appointments')->controller(AppointmentController::class)->group(function () {
                Route::post('/book', 'book')
                    ->middleware(['checkaccess:role:doctor,secretary,patient',]);
                Route::post('/{id}/reschedule', 'reschedule')
                    ->middleware(['checkaccess:role:doctor,secretary,patient']);
                Route::post('/{id}/cancel', 'cancel')
                    ->middleware(['checkaccess:role:doctor,secretary,patient']);
                Route::post('/{id}/complete', 'complete')
                    ->middleware(['checkaccess:role:doctor', 'checkaccess:permission:manage appointments']);
                Route::post('/{id}/confirmed', 'markConfirmed')
                    ->middleware(['checkaccess:role:doctor,secretary', 'checkaccess:permission:manage appointments']);
                Route::get('/{id}', 'show')
                    ->middleware(['checkaccess:role:patient,doctor,secretary,owner', 'checkaccess:permission:view appointments']);
                Route::get('/patient/{patientId}', 'patientAppointments')
                    ->middleware(['checkaccess:role:doctor,secretary,owner,patient', 'checkaccess:permission:view appointments']);
                Route::get('/doctor/{doctorId}', 'doctorAppointments')
                    ->middleware(['checkaccess:role:doctor,secretary,owner', 'checkaccess:permission:view appointments']);
                Route::get('/clinic/{clinicId}', 'clinicAppointments')
                    ->middleware(['checkaccess:role:doctor,secretary,owner', 'checkaccess:permission:view appointments']);
                Route::get('/room/appo', 'roomAppointments')
                    ->middleware(['checkaccess:role:doctor,secretary,owner', 'checkaccess:permission:view appointments']);
                Route::get('/doctor/{doctorId}/schedule', 'doctorSchedule')
                    ->middleware(['checkaccess:role:doctor,secretary,owner', 'checkaccess:permission:view schedules']);
                Route::get('/clinic/{clinicId}/schedule', 'clinicSchedule')
                    ->middleware(['checkaccess:role:secretary,owner', 'checkaccess:permission:view schedules']);
                Route::get('/get/available-slots', 'availableSlots')
                    ->middleware('checkaccess:role:patient,doctor,secretary,owner');
            });

            Route::prefix('patient-records')->controller(PatientRecordController::class)->group(function () {
                Route::post('/', 'store')
                    ->middleware(['checkaccess:role:doctor', 'checkaccess:permission:access records']);
                Route::put('/{id}', 'update')
                    ->middleware(['checkaccess:role:doctor', 'checkaccess:permission:access records']);
                Route::delete('/{id}', 'destroy')
                    ->middleware(['checkaccess:role:doctor', 'checkaccess:permission:access records']);
                Route::get('/show/{id}', 'show')
                    ->middleware(['checkaccess:role:doctor,patient,secretary']);
                Route::get('/filtered', 'index')
                    ->middleware(['checkaccess:role:doctor,owner', 'checkaccess:permission:access records']);
                Route::get('/patient/{patientId}/history', 'history')
                    ->middleware(['checkaccess:role:doctor,patient']);
                Route::get('/patient/{patientId}/doctor/{doctorId}', 'getByDoctor')
                    ->middleware(['checkaccess:role:doctor', 'checkaccess:permission:access records']);
                Route::post('/rooms/search', 'getByRoom')
                    ->middleware(['checkaccess:role:doctor,secretary']);
                Route::get('/doctor/{doctorId}/all', 'getAllByDoctor')
                    ->middleware(['checkaccess:role:doctor', 'checkaccess:permission:access records']);
            });
        });
    });
});

// TODO: add clinic_id to types
Route::prefix('/clinic-system')->group(function () {
    Route::prefix('appointment-types')->controller(AppointmentTypeController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'add')
            ->middleware(['auth:sanctum', 'checkaccess:role:owner', 'checkaccess:permission:admin']);
        Route::delete('/{id}', 'delete')
            ->middleware(['auth:sanctum', 'checkaccess:role:owner', 'checkaccess:permission:admin']);
    });
});

// TODO: testing
Route::get('/filter', function (Request $request) {
    return ApiResponse::pagination(ModelFilter::filter(new User(), $request->all()));
});
