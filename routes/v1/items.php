<?php

use App\Http\Controllers\Item\ItemController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:100,1'])->prefix('/items')->controller(ItemController::class)->group(function () {
    Route::get('/', 'index')->middleware('checkaccess:role:admin,doctor,owner');
    Route::post('/', 'store')->middleware('checkaccess:role:admin,owner,doctor');
    Route::delete('{item}', 'destroy')->middleware('checkaccess:role:owner,admin');
});
