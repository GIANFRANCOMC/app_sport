<?php

use App\Http\Controllers\System\Customers\{TrackingNotificationController};
use Illuminate\Support\Facades\Route;

$entity = "tracking_notifications";

Route::get('',               [TrackingNotificationController::class, 'index'])->name("$entity.index");
Route::get('/initParams',    [TrackingNotificationController::class, 'initParams'])->name("$entity.initParams");
Route::get('/list',          [TrackingNotificationController::class, 'list'])->name("$entity.list");
Route::patch('/cancel/{id}', [TrackingNotificationController::class, 'cancel'])->name("$entity.cancel");
