<?php

declare(strict_types=1);

use App\Http\Controllers\System\Catalogs\{ServiceController};
use Illuminate\Support\Facades\Route;

$entity = "services";

Route::get("",            [ServiceController::class, "index"])->name("$entity.index");
Route::get("/initParams", [ServiceController::class, "initParams"])->name("$entity.initParams");
Route::get("/list",       [ServiceController::class, "list"])->name("$entity.list");
Route::post("",           [ServiceController::class, "store"])->name("$entity.store");
Route::patch("/{id}",     [ServiceController::class, "update"])->name("$entity.update");
