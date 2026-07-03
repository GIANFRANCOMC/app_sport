<?php

use App\Http\Controllers\Guest\{TrackingAttendanceController};
use Illuminate\Support\Facades\Route;

$entity = "guest.tracking_attendances";

Route::get('',            [TrackingAttendanceController::class, 'index'])->name("$entity.index");
Route::get('/access/{branch}', [TrackingAttendanceController::class, 'signedIndex'])
    ->middleware(['signed', 'throttle:guest-status'])
    ->name("$entity.signed");
Route::get('/initParams', [TrackingAttendanceController::class, 'initParams'])->name("$entity.initParams");
Route::get('/list',       [TrackingAttendanceController::class, 'list'])->name("$entity.list");
// Route::get('/create',     [TrackingAttendanceController::class, 'create'])->name("$entity.create");
// Route::post('',           [TrackingAttendanceController::class, 'store'])->name("$entity.store");
// Route::get('/{id}/edit',  [TrackingAttendanceController::class, 'edit'])->name("$entity.edit");
// Route::get('/{id}',       [TrackingAttendanceController::class, 'show'])->name("$entity.show");
// Route::patch('/{id}',     [TrackingAttendanceController::class, 'update'])->name("$entity.update");

Route::post('qrCamera', [TrackingAttendanceController::class, 'qrCamera'])
    ->middleware('throttle:guest-attendance')
    ->name("$entity.qrCamera");
