<?php

declare(strict_types=1);

use App\Http\Controllers\System\Organizations\UserAttendanceController;
use Illuminate\Support\Facades\Route;

Route::get("/list", [UserAttendanceController::class, "list"])->name("user_attendances.list");
Route::get("/weekly-summary", [UserAttendanceController::class, "weeklySummary"])->name("user_attendances.weekly");
Route::post("/check-in", [UserAttendanceController::class, "checkIn"])->name("user_attendances.checkin");
Route::patch("/check-out", [UserAttendanceController::class, "checkOut"])->name("user_attendances.checkout");
