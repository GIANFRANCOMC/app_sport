<?php

declare(strict_types=1);

use App\Http\Controllers\System\Finance\{CashSessionController};
use Illuminate\Support\Facades\{Route};

Route::get("", [CashSessionController::class, "index"])->name("cash_sessions.index");
Route::get("/list", [CashSessionController::class, "list"])->name("cash_sessions.list");
Route::post("/open", [CashSessionController::class, "open"])->name("cash_sessions.open");
Route::post("/close", [CashSessionController::class, "close"])->name("cash_sessions.close");
