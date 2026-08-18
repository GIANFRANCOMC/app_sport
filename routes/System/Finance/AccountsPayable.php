<?php

use App\Http\Controllers\System\Finance\{AccountsPayableController};
use Illuminate\Support\Facades\{Route};

$entity = "accounts_payable";

Route::get("", [AccountsPayableController::class, "index"])->name("$entity.index");
Route::get("/list", [AccountsPayableController::class, "list"])->name("$entity.list");
Route::get("/{id}", [AccountsPayableController::class, "show"])->whereNumber("id")->name("$entity.show");
