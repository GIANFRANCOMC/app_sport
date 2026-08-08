<?php

use App\Http\Controllers\System\Customers\{CustomerController};
use Illuminate\Support\Facades\Route;

$entity = "customers";

Route::get("", [CustomerController::class, "index"])->name("$entity.index");
Route::get("/initParams", [CustomerController::class, "initParams"])->name("$entity.initParams");
Route::get("/list", [CustomerController::class, "list"])->name("$entity.list");
Route::post("", [CustomerController::class, "store"])->name("$entity.store");
Route::patch("/{id}", [CustomerController::class, "update"])->name("$entity.update");
Route::get("/getSubscriptions/{id}", [CustomerController::class, "getSubscriptions"])->name("$entity.getSubscriptions");
Route::post("/registerBiometricFingerprint/{id}", [CustomerController::class, "registerBiometricFingerprint"])->name("$entity.registerBiometricFingerprint");
