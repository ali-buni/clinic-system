<?php

use App\Http\Controllers\Medicine\MedicineController;
use Illuminate\Support\Facades\Route;

Route::prefix('medicines')->controller(MedicineController::class)->group(function () {
    Route::get('search', 'searchMedicine')
        ->middleware(['auth:sanctum', 'throttle:100,1']);
    Route::post('/', 'store')
        ->middleware(['auth:sanctum', 'throttle:100,1', 'checkaccess:role:doctor,secretary,owner']);
});
