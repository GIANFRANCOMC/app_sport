<?php

declare(strict_types=1);

use App\Http\Controllers\System\Finance\{CashMovementController};
use Illuminate\Support\Facades\{Route};

Route::get("", [CashMovementController::class, "index"])->name("cash_movements.index");
Route::get("/list", [CashMovementController::class, "list"])->name("cash_movements.list");
Route::post("", [CashMovementController::class, "store"])->name("cash_movements.store");
Route::get("/export", [CashMovementController::class, "export"])->name("cash_movements.export");
