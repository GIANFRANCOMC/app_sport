<?php

use App\Http\Controllers\System\Purchases\{SupplierController};
use Illuminate\Support\Facades\{Route};

$entity = "suppliers";

Route::get("", [SupplierController::class, "index"])->name("$entity.index");
Route::get("/list", [SupplierController::class, "list"])->name("$entity.list");
Route::post("", [SupplierController::class, "store"])->name("$entity.store");
Route::patch("/{id}", [SupplierController::class, "update"])->name("$entity.update");
