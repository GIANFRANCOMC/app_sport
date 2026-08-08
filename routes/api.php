<?php

use App\Http\Controllers\Guest\BiometricEventController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
// return $request->user();
// });

Route::prefix("{company_slug}")
    ->middleware(["company.exists", "throttle:biometric-events"])
    ->group(function () {
        Route::post("/biometric/events", [BiometricEventController::class, "store"])
            ->name("guest.biometric.events.store");
    });
