<?php

use App\Http\Controllers\System\{SubscriptionController};
use Illuminate\Support\Facades\Route;

$entity = "subscriptions";

Route::get('',            [SubscriptionController::class, 'index'])->name("$entity.index");
Route::get('/initParams', [SubscriptionController::class, 'initParams'])->name("$entity.initParams");
Route::get('/list',       [SubscriptionController::class, 'list'])->name("$entity.list");
Route::get('/create',     [SubscriptionController::class, 'create'])->name("$entity.create");
Route::post('',           [SubscriptionController::class, 'store'])->name("$entity.store");
Route::get('/{id}/edit',  [SubscriptionController::class, 'edit'])->name("$entity.edit");
Route::get('/{id}',       [SubscriptionController::class, 'show'])->name("$entity.show");
Route::patch('/{id}',     [SubscriptionController::class, 'update'])->name("$entity.update");

