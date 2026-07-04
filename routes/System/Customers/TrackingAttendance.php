<?php

use App\Http\Controllers\System\Customers\{TrackingAttendanceController};
use Illuminate\Support\Facades\Route;

$entity = "tracking_attendances";

Route::get('',               [TrackingAttendanceController::class, 'index'])->name("$entity.index");
Route::get('/initParams',    [TrackingAttendanceController::class, 'initParams'])->name("$entity.initParams");
Route::get('/list',          [TrackingAttendanceController::class, 'list'])->name("$entity.list");
Route::get('/export',        [TrackingAttendanceController::class, 'export'])->name("$entity.export");
Route::post('',              [TrackingAttendanceController::class, 'store'])->name("$entity.store");
Route::patch('/{id}',        [TrackingAttendanceController::class, 'update'])->name("$entity.update");
Route::patch('/cancel/{id}', [TrackingAttendanceController::class, 'cancel'])->name("$entity.cancel");
Route::post('/{id}/corrections', [TrackingAttendanceController::class, 'requestCorrection'])->name("$entity.corrections.store");
Route::patch('/corrections/{id}/review', [TrackingAttendanceController::class, 'reviewCorrection'])->name("$entity.corrections.review");


Route::post('qrCamera',              [TrackingAttendanceController::class, 'qrCamera'])->name("$entity.qrCamera");
Route::post('qrScanner',             [TrackingAttendanceController::class, 'qrScanner'])->name("$entity.qrScanner");
