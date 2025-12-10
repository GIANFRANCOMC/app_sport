<?php

use App\Http\Controllers\System\{AssetController};
use Illuminate\Support\Facades\Route;

$entity = "assets";

Route::get('',            [AssetController::class, 'index'])->name("$entity.index");
Route::get('/initParams', [AssetController::class, 'initParams'])->name("$entity.initParams");
Route::get('/list',       [AssetController::class, 'list'])->name("$entity.list");
Route::get('/create',     [AssetController::class, 'create'])->name("$entity.create");
Route::post('',           [AssetController::class, 'store'])->name("$entity.store");
Route::get('/{id}/edit',  [AssetController::class, 'edit'])->name("$entity.edit");
Route::get('/{id}',       [AssetController::class, 'show'])->name("$entity.show");
Route::patch('/{id}',     [AssetController::class, 'update'])->name("$entity.update");
