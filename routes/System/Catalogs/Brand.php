<?php

declare(strict_types=1);

use App\Http\Controllers\System\Catalogs\BrandController;
use Illuminate\Support\Facades\Route;

$entity = "brands";

Route::get("", [BrandController::class, "index"])->name("$entity.index");
Route::get("/initParams", [BrandController::class, "initParams"])->name("$entity.initParams");
Route::get("/list", [BrandController::class, "list"])->name("$entity.list");
Route::get("/create", [BrandController::class, "create"])->name("$entity.create");
Route::post("", [BrandController::class, "store"])->name("$entity.store");
Route::get("/{id}/edit", [BrandController::class, "edit"])->name("$entity.edit");
Route::get("/{id}", [BrandController::class, "show"])->name("$entity.show");
Route::patch("/{id}", [BrandController::class, "update"])->name("$entity.update");
Route::delete("/{id}", [BrandController::class, "destroy"])->name("$entity.destroy");
