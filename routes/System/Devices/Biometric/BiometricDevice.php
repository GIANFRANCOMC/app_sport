<?php

declare(strict_types=1);

use App\Http\Controllers\System\Devices\Biometric\{BiometricDeviceController};
use Illuminate\Support\Facades\Route;

$entity = "biometric_devices";

Route::get('',            [BiometricDeviceController::class, 'index'])->name("$entity.index");
Route::get('/initParams', [BiometricDeviceController::class, 'initParams'])->name("$entity.initParams");
Route::get('/list',       [BiometricDeviceController::class, 'list'])->name("$entity.list");
Route::get('/create',     [BiometricDeviceController::class, 'create'])->name("$entity.create");
Route::post('',           [BiometricDeviceController::class, 'store'])->name("$entity.store");
Route::get('/{id}/edit',  [BiometricDeviceController::class, 'edit'])->name("$entity.edit");
Route::get('/{id}',       [BiometricDeviceController::class, 'show'])->name("$entity.show");
Route::patch('/{id}',     [BiometricDeviceController::class, 'update'])->name("$entity.update");
Route::get('/devices',    [BiometricDeviceController::class, 'getDevices'])->name("$entity.getDevices");

