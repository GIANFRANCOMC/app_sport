<?php

use App\Http\Controllers\System\Organizations\{BookComplaintController};
use Illuminate\Support\Facades\Route;

$entity = "book_complaints";

Route::get('',            [BookComplaintController::class, 'index'])->name("$entity.index");
Route::get('/initParams', [BookComplaintController::class, 'initParams'])->name("$entity.initParams");
Route::get('/list',       [BookComplaintController::class, 'list'])->name("$entity.list");
Route::get('/create',     [BookComplaintController::class, 'create'])->name("$entity.create");
Route::post('',           [BookComplaintController::class, 'store'])->name("$entity.store");
Route::get('/{id}/edit',  [BookComplaintController::class, 'edit'])->name("$entity.edit");
Route::get('/{id}',       [BookComplaintController::class, 'show'])->name("$entity.show");
Route::patch('/{id}',     [BookComplaintController::class, 'update'])->name("$entity.update");
