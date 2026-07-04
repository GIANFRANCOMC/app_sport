<?php

use App\Http\Controllers\System\Organizations\{BookComplaintController};
use Illuminate\Support\Facades\Route;

$entity = "book_complaints";

Route::get('',            [BookComplaintController::class, 'index'])->name("$entity.index");
Route::get('/initParams', [BookComplaintController::class, 'initParams'])->name("$entity.initParams");
Route::get('/list',       [BookComplaintController::class, 'list'])->name("$entity.list");
Route::patch('/{id}',     [BookComplaintController::class, 'update'])->name("$entity.update");
