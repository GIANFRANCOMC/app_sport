<?php

use App\Http\Controllers\System\{TrackingNotificationController};
use Illuminate\Support\Facades\Route;

$entity = "tracking_notifications";

Route::get('',               [TrackingNotificationController::class, 'index'])->name("$entity.index");
Route::get('/initParams',    [TrackingNotificationController::class, 'initParams'])->name("$entity.initParams");
Route::get('/list',          [TrackingNotificationController::class, 'list'])->name("$entity.list");
Route::get('/create',        [TrackingNotificationController::class, 'create'])->name("$entity.create");
Route::post('',              [TrackingNotificationController::class, 'store'])->name("$entity.store");
Route::get('/{id}/edit',     [TrackingNotificationController::class, 'edit'])->name("$entity.edit");
Route::get('/{id}',          [TrackingNotificationController::class, 'show'])->name("$entity.show");
Route::patch('/{id}',        [TrackingNotificationController::class, 'update'])->name("$entity.update");
Route::patch('/cancel/{id}', [TrackingNotificationController::class, 'cancel'])->name("$entity.cancel");
