<?php

use App\Http\Controllers\System\Purchases\PurchaseController;
use Illuminate\Support\Facades\Route;

$entity = "purchases";

Route::get("", [PurchaseController::class, "index"])->name("$entity.index");
Route::get("/initParams", [PurchaseController::class, "initParams"])->name("$entity.initParams");
Route::get("/list", [PurchaseController::class, "list"])->name("$entity.list");
Route::get("/export", [PurchaseController::class, "export"])->name("$entity.export");
Route::post("", [PurchaseController::class, "store"])->name("$entity.store");
Route::get("/{id}", [PurchaseController::class, "show"])->name("$entity.show");
Route::post("/{id}/receive", [PurchaseController::class, "receive"])->name("$entity.receive");
Route::patch("/{id}/cancel", [PurchaseController::class, "cancel"])->name("$entity.cancel");
