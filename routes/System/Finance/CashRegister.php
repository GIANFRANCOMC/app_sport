<?php

use App\Http\Controllers\System\Finance\CashRegisterController;
use Illuminate\Support\Facades\Route;

$entity = "cash_registers";

Route::get("", [CashRegisterController::class, "index"])->name("$entity.index");
Route::get("/page/registers", [CashRegisterController::class, "index"])->name("$entity.registers.index");
Route::get("/page/sessions", [CashRegisterController::class, "index"])->name("$entity.sessions.index");
Route::get("/page/movements", [CashRegisterController::class, "index"])->name("$entity.movements.index");
Route::get("/page/summary", [CashRegisterController::class, "index"])->name("$entity.summary.index");
Route::get("/initParams", [CashRegisterController::class, "initParams"])->name("$entity.initParams");
Route::get("/list", [CashRegisterController::class, "list"])->name("$entity.list");
Route::get("/export", [CashRegisterController::class, "export"])->name("$entity.export");
Route::get("/sessions", [CashRegisterController::class, "sessions"])->name("$entity.sessions");
Route::get("/movements", [CashRegisterController::class, "movements"])->name("$entity.movements");
Route::get("/summary", [CashRegisterController::class, "summary"])->name("$entity.summary");
Route::post("", [CashRegisterController::class, "store"])->name("$entity.store");
Route::post("/open", [CashRegisterController::class, "open"])->name("$entity.open");
Route::post("/close", [CashRegisterController::class, "close"])->name("$entity.close");
Route::post("/movement", [CashRegisterController::class, "movement"])->name("$entity.movement");
