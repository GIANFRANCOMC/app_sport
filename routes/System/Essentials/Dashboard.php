<?php

use App\Http\Controllers\System\{DashboardController};
use Illuminate\Support\Facades\Route;

$entity = "dashboard";

Route::get('',            [DashboardController::class, 'index'])->name("$entity.index");
Route::get('/initParams', [DashboardController::class, 'initParams'])->name("$entity.initParams");
Route::get('/initData',   [DashboardController::class, 'initData'])->name("$entity.initData");
