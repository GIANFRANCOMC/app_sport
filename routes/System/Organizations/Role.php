<?php

declare(strict_types=1);

use App\Http\Controllers\System\Organizations\{RoleController};
use Illuminate\Support\Facades\{Route};

$entity = "roles";

Route::get("", [RoleController::class, "index"])->name("$entity.index");
Route::get("/initParams", [RoleController::class, "initParams"])->name("$entity.initParams");
Route::get("/list", [RoleController::class, "list"])->name("$entity.list");
Route::post("", [RoleController::class, "store"])->name("$entity.store");
Route::get("/{id}", [RoleController::class, "show"])->name("$entity.show");
Route::post("/{id}/duplicate", [RoleController::class, "duplicate"])->name("$entity.duplicate");
Route::patch("/{id}", [RoleController::class, "update"])->name("$entity.update");
