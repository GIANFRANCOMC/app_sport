<?php

use App\Http\Controllers\System\Finance\AccountsReceivableController;
use Illuminate\Support\Facades\Route;

$entity = "accounts_receivable";

Route::get("", [AccountsReceivableController::class, "index"])->name("$entity.index");
Route::get("/list", [AccountsReceivableController::class, "list"])->name("$entity.list");
Route::get("/{id}", [AccountsReceivableController::class, "show"])->whereNumber("id")->name("$entity.show");
