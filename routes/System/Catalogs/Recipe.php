<?php

declare(strict_types=1);

use App\Http\Controllers\System\Catalogs\{RecipeController};
use Illuminate\Support\Facades\{Route};

$entity = "recipes";

Route::get("", [RecipeController::class, "index"])->name("$entity.index");
Route::get("/initParams", [RecipeController::class, "initParams"])->name("$entity.initParams");
Route::get("/list", [RecipeController::class, "list"])->name("$entity.list");
Route::get("/waste-records", [RecipeController::class, "wasteRecords"])->name("$entity.waste-records");
Route::post("", [RecipeController::class, "store"])->name("$entity.store");
Route::get("/{id}/theoretical-cost", [RecipeController::class, "theoreticalCost"])->name("$entity.theoretical-cost");
Route::post("/{id}/waste-records", [RecipeController::class, "storeWaste"])->name("$entity.waste-records.store");
Route::get("/{id}", [RecipeController::class, "show"])->name("$entity.show");
Route::patch("/{id}", [RecipeController::class, "update"])->name("$entity.update");
Route::delete("/{id}", [RecipeController::class, "destroy"])->name("$entity.destroy");
