<?php

use App\Http\Controllers\System\{HomeController};
use Illuminate\Support\Facades\Route;

$entity = "home";

Route::get('',            [HomeController::class, 'index'])->name("$entity.index");
Route::get('/initParams', [HomeController::class, 'initParams'])->name("$entity.initParams");
Route::patch('/{id}',     [HomeController::class, 'update'])->name("$entity.update");
