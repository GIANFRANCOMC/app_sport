<?php

use App\Http\Controllers\System\Devices\Biometric\BiometricDeviceController;
use Illuminate\Support\Facades\Route;

$entity = "guest.biometric_devices";

// Public endpoint for receiving events from ZKTeco devices
Route::post('/receiveEvent', [BiometricDeviceController::class, 'receiveEvent'])
     ->name("$entity.receiveEvent");

