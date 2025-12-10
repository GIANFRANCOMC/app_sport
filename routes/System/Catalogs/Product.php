<?php

use App\Http\Controllers\System\{ProductController};
use Illuminate\Support\Facades\Route;

$entity = "products";

Route::get('',            [ProductController::class, 'index'])->name("$entity.index");
Route::get('/initParams', [ProductController::class, 'initParams'])->name("$entity.initParams");
Route::get('/list',       [ProductController::class, 'list'])->name("$entity.list");
Route::get('/create',     [ProductController::class, 'create'])->name("$entity.create");
Route::post('',           [ProductController::class, 'store'])->name("$entity.store");
Route::get('/{id}/edit',  [ProductController::class, 'edit'])->name("$entity.edit");
Route::get('/{id}',       [ProductController::class, 'show'])->name("$entity.show");
Route::patch('/{id}',     [ProductController::class, 'update'])->name("$entity.update");

