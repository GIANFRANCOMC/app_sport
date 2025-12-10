<?php

use App\Http\Controllers\System\{TrackingSubscriptionController};
use Illuminate\Support\Facades\Route;

$entity = "tracking_subscriptions";

Route::get('',               [TrackingSubscriptionController::class, 'index'])->name("$entity.index");
Route::get('/initParams',    [TrackingSubscriptionController::class, 'initParams'])->name("$entity.initParams");
Route::get('/list',          [TrackingSubscriptionController::class, 'list'])->name("$entity.list");
Route::get('/create',        [TrackingSubscriptionController::class, 'create'])->name("$entity.create");
Route::post('',              [TrackingSubscriptionController::class, 'store'])->name("$entity.store");
Route::get('/{id}/edit',     [TrackingSubscriptionController::class, 'edit'])->name("$entity.edit");
Route::get('/{id}',          [TrackingSubscriptionController::class, 'show'])->name("$entity.show");
Route::patch('/{id}',        [TrackingSubscriptionController::class, 'update'])->name("$entity.update");
Route::patch('/cancel/{id}', [TrackingSubscriptionController::class, 'cancel'])->name("$entity.cancel");

