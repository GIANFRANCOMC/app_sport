<?php

use App\Http\Controllers\Guest\{HomeController};
use Illuminate\Support\Facades\{Route};

$entity = "guest.home";

Route::get("", [HomeController::class, "index"])->name("$entity.index");
Route::get("/initParams", [HomeController::class, "initParams"])->name("$entity.initParams");
Route::get("/initData", [HomeController::class, "initData"])->name("$entity.initData");
