<?php

use App\Http\Controllers\System\{TrackingAttendanceController};
use Illuminate\Support\Facades\Route;

$entity = "tracking_attendances";

Route::get('',               [TrackingAttendanceController::class, 'index'])->name("$entity.index");
Route::get('/initParams',    [TrackingAttendanceController::class, 'initParams'])->name("$entity.initParams");
Route::get('/list',          [TrackingAttendanceController::class, 'list'])->name("$entity.list");
Route::get('/create',        [TrackingAttendanceController::class, 'create'])->name("$entity.create");
Route::post('',              [TrackingAttendanceController::class, 'store'])->name("$entity.store");
Route::get('/{id}/edit',     [TrackingAttendanceController::class, 'edit'])->name("$entity.edit");
Route::get('/{id}',          [TrackingAttendanceController::class, 'show'])->name("$entity.show");
Route::patch('/{id}',        [TrackingAttendanceController::class, 'update'])->name("$entity.update");
Route::patch('/cancel/{id}', [TrackingAttendanceController::class, 'cancel'])->name("$entity.cancel");


Route::post('qrCamera',              [TrackingAttendanceController::class, 'qrCamera'])->name("$entity.qrCamera");
Route::post('qrScanner',             [TrackingAttendanceController::class, 'qrScanner'])->name("$entity.qrScanner");
