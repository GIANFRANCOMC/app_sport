<?php

use App\Http\Controllers\System\{StockManagementController};
use Illuminate\Support\Facades\Route;

$entity = "stocks_management";

Route::get('',            [StockManagementController::class, 'index'])->name("$entity.index");
Route::get('/initParams', [StockManagementController::class, 'initParams'])->name("$entity.initParams");
Route::get('/list',       [StockManagementController::class, 'list'])->name("$entity.list");
Route::get('/create',     [StockManagementController::class, 'create'])->name("$entity.create");
Route::post('',           [StockManagementController::class, 'store'])->name("$entity.store");
Route::get('/{id}/edit',  [StockManagementController::class, 'edit'])->name("$entity.edit");
Route::get('/{id}',       [StockManagementController::class, 'show'])->name("$entity.show");
Route::patch('/{id}',     [StockManagementController::class, 'update'])->name("$entity.update");
