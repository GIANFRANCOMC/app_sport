<?php

use App\Http\Controllers\System\{UserController};
use Illuminate\Support\Facades\Route;

$entity = "users";

Route::get('',            [UserController::class, 'index'])->name("$entity.index");
Route::get('/initParams', [UserController::class, 'initParams'])->name("$entity.initParams");
Route::get('/list',       [UserController::class, 'list'])->name("$entity.list");
Route::get('/create',     [UserController::class, 'create'])->name("$entity.create");
Route::post('',           [UserController::class, 'store'])->name("$entity.store");
Route::get('/{id}/edit',  [UserController::class, 'edit'])->name("$entity.edit");
Route::get('/{id}',       [UserController::class, 'show'])->name("$entity.show");
Route::patch('/{id}',     [UserController::class, 'update'])->name("$entity.update");

