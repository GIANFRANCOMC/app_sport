<?php

declare(strict_types=1);

use App\Http\Controllers\System\Organizations\{CompanyController};
use Illuminate\Support\Facades\Route;

$entity = "companies";

Route::get("",            [CompanyController::class, "index"])->name("$entity.index");
Route::get("/initParams", [CompanyController::class, "initParams"])->name("$entity.initParams");
Route::get("/list",       [CompanyController::class, "list"])->name("$entity.list");
Route::get("/create",     [CompanyController::class, "create"])->name("$entity.create");
Route::post("",           [CompanyController::class, "store"])->name("$entity.store");
Route::get("/{id}/edit",  [CompanyController::class, "edit"])->name("$entity.edit");
Route::get("/{id}",       [CompanyController::class, "show"])->name("$entity.show");
Route::patch("/{id}",     [CompanyController::class, "update"])->name("$entity.update");
Route::delete("/{id}",    [CompanyController::class, "destroy"])->name("{$entity}.destroy");
