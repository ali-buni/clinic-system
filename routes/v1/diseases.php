<?php

use App\Http\Controllers\Disease\DiseaseController;
use Illuminate\Support\Facades\Route;

Route::prefix('diseases')->controller(DiseaseController::class)->group(function () {
    Route::get('search', 'searchDisease');
    Route::post('/', 'store')
        ->middleware(['auth:sanctum', 'throttle:100,1', 'checkaccess:role:doctor,owner', 'checkaccess:permission:manage m/d']);
});
