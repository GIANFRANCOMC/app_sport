<?php

use App\Http\Controllers\System\Finance\{CashRegisterController};
use Illuminate\Support\Facades\{Route};

$entity = "cash_registers";

Route::get("", [CashRegisterController::class, "index"])->name("$entity.index");
Route::get("/initParams", [CashRegisterController::class, "initParams"])->name("$entity.initParams");
Route::get("/list", [CashRegisterController::class, "list"])->name("$entity.list");
Route::post("", [CashRegisterController::class, "store"])->name("$entity.store");
