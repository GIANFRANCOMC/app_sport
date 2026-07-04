<?php

declare(strict_types=1);

use App\Http\Controllers\System\Organizations\{BranchController};
use Illuminate\Support\Facades\Route;

$entity = "branches";

Route::get('/series/audit', [BranchController::class, 'seriesAudit'])->name("$entity.series.audit");
Route::get('/series/audit/export', [BranchController::class, 'exportSeriesAudit'])->name("$entity.series.audit.export");
Route::get('/{id}/public-attendance-link', [BranchController::class, 'publicAttendanceLink'])->name("$entity.public-attendance-link");

Route::get("",            [BranchController::class, "index"])->name("$entity.index");
Route::get("/initParams", [BranchController::class, "initParams"])->name("$entity.initParams");
Route::get("/list",       [BranchController::class, "list"])->name("$entity.list");
Route::post("",           [BranchController::class, "store"])->name("$entity.store");
Route::patch("/{id}",     [BranchController::class, "update"])->name("$entity.update");
