<?php

use App\Http\Controllers\Guest\{TrackingAttendanceController};
use Illuminate\Support\Facades\{Route};

$entity = "guest.tracking_attendances";

Route::get("", [TrackingAttendanceController::class, "index"])->name("$entity.index");
Route::get("/access/{branch}", [TrackingAttendanceController::class, "signedIndex"])
    ->middleware(["signed", "throttle:guest-status"])
    ->name("$entity.signed");
Route::get("/initParams", [TrackingAttendanceController::class, "initParams"])->name("$entity.initParams");

Route::post("qrCamera", [TrackingAttendanceController::class, "qrCamera"])
    ->middleware(["public.attendance.access", "throttle:guest-attendance"])
    ->name("$entity.qrCamera");
