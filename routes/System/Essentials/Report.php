<?php

use App\Http\Controllers\System\{ReportController};
use Illuminate\Support\Facades\Route;

$entity = "reports";

Route::get('',            [ReportController::class, 'index'])->name("$entity.index");
Route::get('/initParams', [ReportController::class, 'initParams'])->name("$entity.initParams");
Route::get('/sale',       [ReportController::class, 'sale'])->name("$entity.sale");

Route::get('/customers',  [ReportController::class, 'customers'])->name("$entity.customers");
Route::get('/items',      [ReportController::class, 'items'])->name("$entity.items");
Route::get('/branches',   [ReportController::class, 'branches'])->name("$entity.branches");
Route::get('/sales',      [ReportController::class, 'sales'])->name("$entity.sales");
Route::get('/users',      [ReportController::class, 'users'])->name("$entity.users");
