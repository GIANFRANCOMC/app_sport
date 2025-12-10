<?php

use App\Http\Controllers\System\{SaleController};
use Illuminate\Support\Facades\Route;

$entity = "sales";

Route::get('',               [SaleController::class, 'index'])->name("$entity.index");
Route::get('/initParams',    [SaleController::class, 'initParams'])->name("$entity.initParams");
Route::get('/list',          [SaleController::class, 'list'])->name("$entity.list");
Route::get('/create',        [SaleController::class, 'create'])->name("$entity.create");
Route::post('',              [SaleController::class, 'store'])->name("$entity.store");
Route::get('/{id}/edit',     [SaleController::class, 'edit'])->name("$entity.edit");
Route::get('/{id}',          [SaleController::class, 'show'])->name("$entity.show");
Route::patch('/{id}',        [SaleController::class, 'update'])->name("$entity.update");
Route::patch('/cancel/{id}', [SaleController::class, 'cancel'])->name("$entity.cancel");

