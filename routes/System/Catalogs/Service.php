<?php

use App\Http\Controllers\System\{ServiceController};
use Illuminate\Support\Facades\Route;

$entity = "services";

Route::get('',            [ServiceController::class, 'index'])->name("$entity.index");
Route::get('/initParams', [ServiceController::class, 'initParams'])->name("$entity.initParams");
Route::get('/list',       [ServiceController::class, 'list'])->name("$entity.list");
Route::get('/create',     [ServiceController::class, 'create'])->name("$entity.create");
Route::post('',           [ServiceController::class, 'store'])->name("$entity.store");
Route::get('/{id}/edit',  [ServiceController::class, 'edit'])->name("$entity.edit");
Route::get('/{id}',       [ServiceController::class, 'show'])->name("$entity.show");
Route::patch('/{id}',     [ServiceController::class, 'update'])->name("$entity.update");

