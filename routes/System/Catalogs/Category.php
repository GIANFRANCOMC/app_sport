<?php

use App\Http\Controllers\System\{CategoryController};
use Illuminate\Support\Facades\Route;

$entity = "categories";

Route::get('',            [CategoryController::class, 'index'])->name("$entity.index");
Route::get('/initParams', [CategoryController::class, 'initParams'])->name("$entity.initParams");
Route::get('/list',       [CategoryController::class, 'list'])->name("$entity.list");
Route::get('/create',     [CategoryController::class, 'create'])->name("$entity.create");
Route::post('',           [CategoryController::class, 'store'])->name("$entity.store");
Route::get('/{id}/edit',  [CategoryController::class, 'edit'])->name("$entity.edit");
Route::get('/{id}',       [CategoryController::class, 'show'])->name("$entity.show");
Route::patch('/{id}',     [CategoryController::class, 'update'])->name("$entity.update");
