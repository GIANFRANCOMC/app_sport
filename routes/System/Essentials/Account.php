<?php

declare(strict_types=1);

use App\Http\Controllers\System\Essentials\{AccountController};
use Illuminate\Support\Facades\{Route};

Route::get("", [AccountController::class, "index"])->name("account.index");
Route::patch("", [AccountController::class, "update"])->name("account.update");
