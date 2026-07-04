<?php

use App\Http\Controllers\System\Sales\{SaleController};
use Illuminate\Support\Facades\Route;

$entity = "sales";

Route::get('',               [SaleController::class, 'index'])->name("$entity.index");
Route::get('/initParams',    [SaleController::class, 'initParams'])->name("$entity.initParams");
Route::get('/list',          [SaleController::class, 'list'])->name("$entity.list");
Route::get('/create',        [SaleController::class, 'create'])->name("$entity.create");
Route::get('/pos',           [SaleController::class, 'pos'])->name("$entity.pos");
Route::post('',              [SaleController::class, 'store'])->name("$entity.store");
Route::patch('/cancel/{id}', [SaleController::class, 'cancel'])->name("$entity.cancel");
