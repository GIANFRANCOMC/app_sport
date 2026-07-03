<?php

declare(strict_types=1);

use App\Http\Controllers\System\Organizations\UserAttendanceController;
use Illuminate\Support\Facades\Route;

Route::get("", [UserAttendanceController::class, "index"])->name("user_attendances.index");
Route::get("/initParams", [UserAttendanceController::class, "initParams"])->name("user_attendances.initParams");
Route::get("/list", [UserAttendanceController::class, "list"])->name("user_attendances.list");
Route::get("/weekly-summary", [UserAttendanceController::class, "weeklySummary"])->name("user_attendances.weekly");
Route::post("/check-in", [UserAttendanceController::class, "checkIn"])->name("user_attendances.checkin");
Route::post("/biometric/check-in", [UserAttendanceController::class, "biometricCheckIn"])->name("user_attendances.biometric-checkin");
Route::patch("/check-out", [UserAttendanceController::class, "checkOut"])->name("user_attendances.checkout");
