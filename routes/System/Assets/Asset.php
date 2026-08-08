<?php

declare(strict_types=1);

use App\Http\Controllers\System\Assets\{AssetCategoryController};
use App\Http\Controllers\System\Assets\{AssetController};
use Illuminate\Support\Facades\{Route};

$entity = "assets";

Route::get("/categories/list", [AssetCategoryController::class, "list"])->name("assets.categories.list");
Route::post("/categories", [AssetCategoryController::class, "store"])->name("assets.categories.store");
Route::patch("/categories/{id}", [AssetCategoryController::class, "update"])->name("assets.categories.update");

Route::get("", [AssetController::class, "index"])->name("$entity.index");
Route::get("/initParams", [AssetController::class, "initParams"])->name("$entity.initParams");
Route::get("/list", [AssetController::class, "list"])->name("$entity.list");
Route::post("", [AssetController::class, "store"])->name("$entity.store");
Route::patch("/{id}", [AssetController::class, "update"])->name("$entity.update");
