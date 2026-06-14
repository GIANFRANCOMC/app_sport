<?php

use App\Http\Controllers\System\Warehouses\{StockManagementController};
use Illuminate\Support\Facades\Route;

$entity = "stocks_management";

Route::get('',            [StockManagementController::class, 'index'])->name("$entity.index");
Route::get('/initParams', [StockManagementController::class, 'initParams'])->name("$entity.initParams");
Route::get('/list',       [StockManagementController::class, 'list'])->name("$entity.list");
Route::get('/movements',  [StockManagementController::class, 'movements'])->name("$entity.movements");
Route::post('/movements', [StockManagementController::class, 'storeMovement'])->name("$entity.movements.store");
Route::post('/transfers', [StockManagementController::class, 'storeTransfer'])->name("$entity.transfers.store");
Route::get('/create',     [StockManagementController::class, 'create'])->name("$entity.create");
Route::post('',           [StockManagementController::class, 'store'])->name("$entity.store");
Route::get('/{id}/edit',  [StockManagementController::class, 'edit'])->name("$entity.edit");
Route::get('/{id}',       [StockManagementController::class, 'show'])->name("$entity.show");
Route::patch('/{id}',     [StockManagementController::class, 'update'])->name("$entity.update");
