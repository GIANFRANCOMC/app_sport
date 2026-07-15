<?php

use App\Http\Controllers\System\Finance\MiscExpenseController;
use Illuminate\Support\Facades\Route;

$entity = "misc_expenses";

Route::get("", [MiscExpenseController::class, "index"])->name("$entity.index");
Route::get("/list", [MiscExpenseController::class, "list"])->name("$entity.list");
Route::post("", [MiscExpenseController::class, "store"])->name("$entity.store");
Route::patch("/{id}/cancel", [MiscExpenseController::class, "cancel"])->name("$entity.cancel");
