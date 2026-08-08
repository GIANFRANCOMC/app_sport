<?php

declare(strict_types=1);

use App\Http\Controllers\System\Organizations\{CompanyController};
use Illuminate\Support\Facades\{Route};

$entity = "companies";

Route::get("", [CompanyController::class, "index"])->name("$entity.index");
Route::get("/initParams", [CompanyController::class, "initParams"])->name("$entity.initParams");
Route::patch("/{id}", [CompanyController::class, "update"])->name("$entity.update");
