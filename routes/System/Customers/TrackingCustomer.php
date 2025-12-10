<?php

use App\Http\Controllers\System\{TrackingCustomerController};
use Illuminate\Support\Facades\Route;

$entity = "tracking_customers";

Route::get('',               [TrackingCustomerController::class, 'index'])->name("$entity.index");
Route::get('/initParams',    [TrackingCustomerController::class, 'initParams'])->name("$entity.initParams");
// Route::get('/list',          [TrackingCustomerController::class, 'list'])->name("$entity.list");
// Route::get('/create',        [TrackingCustomerController::class, 'create'])->name("$entity.create");
// Route::post('',              [TrackingCustomerController::class, 'store'])->name("$entity.store");
// Route::get('/{id}/edit',     [TrackingCustomerController::class, 'edit'])->name("$entity.edit");
// Route::get('/{id}',          [TrackingCustomerController::class, 'show'])->name("$entity.show");
// Route::patch('/{id}',        [TrackingCustomerController::class, 'update'])->name("$entity.update");
// Route::patch('/cancel/{id}', [TrackingCustomerController::class, 'cancel'])->name("$entity.cancel");

Route::get('/getTracking/{id}', [TrackingCustomerController::class, 'getTracking'])->name("$entity.getTracking");
