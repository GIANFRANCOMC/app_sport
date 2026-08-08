<?php

declare(strict_types=1);

use App\Http\Controllers\System\Devices\{BiometricDeviceController};
use Illuminate\Support\Facades\{Route};

$entity = "biometric_devices";

Route::get("", [BiometricDeviceController::class, "index"])->name("$entity.index");
Route::get("/initParams", [BiometricDeviceController::class, "initParams"])->name("$entity.initParams");
Route::get("/list", [BiometricDeviceController::class, "list"])->name("$entity.list");
Route::post("", [BiometricDeviceController::class, "store"])->name("$entity.store");
Route::patch("/{id}/credentials", [BiometricDeviceController::class, "rotateCredentials"])
    ->name("$entity.credentials.rotate");
Route::get("/{id}/events", [BiometricDeviceController::class, "events"])->name("$entity.events");
Route::patch("/{id}", [BiometricDeviceController::class, "update"])->name("$entity.update");
