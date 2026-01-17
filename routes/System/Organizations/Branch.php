<?php

declare(strict_types=1);

use App\Http\Controllers\System\Organizations\{BranchController};
use Illuminate\Support\Facades\Route;

$entity = "branches";

Route::get("",            [BranchController::class, "index"])->name("$entity.index");
Route::get("/initParams", [BranchController::class, "initParams"])->name("$entity.initParams");
Route::get("/list",       [BranchController::class, "list"])->name("$entity.list");
Route::get("/create",     [BranchController::class, "create"])->name("$entity.create");
Route::post("",           [BranchController::class, "store"])->name("$entity.store");
Route::get("/{id}/edit",  [BranchController::class, "edit"])->name("$entity.edit");
Route::get("/{id}",       [BranchController::class, "show"])->name("$entity.show");
Route::patch("/{id}",     [BranchController::class, "update"])->name("$entity.update");
Route::delete("/{id}",    [BranchController::class, "destroy"])->name("{$entity}.destroy");
