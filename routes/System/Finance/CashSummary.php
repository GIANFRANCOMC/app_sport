<?php

declare(strict_types=1);

use App\Http\Controllers\System\Finance\{CashSummaryController};
use Illuminate\Support\Facades\{Route};

Route::get("", [CashSummaryController::class, "index"])->name("cash_summary.index");
Route::get("/data", [CashSummaryController::class, "data"])->name("cash_summary.data");
