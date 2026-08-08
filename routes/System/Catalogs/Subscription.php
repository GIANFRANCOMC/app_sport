<?php

declare(strict_types=1);

use App\Http\Controllers\System\Catalogs\{SubscriptionController};
use Illuminate\Support\Facades\{Route};

$entity = "subscriptions";

Route::get("", [SubscriptionController::class, "index"])->name("$entity.index");
Route::get("/initParams", [SubscriptionController::class, "initParams"])->name("$entity.initParams");
Route::get("/list", [SubscriptionController::class, "list"])->name("$entity.list");
Route::post("", [SubscriptionController::class, "store"])->name("$entity.store");
Route::patch("/{id}", [SubscriptionController::class, "update"])->name("$entity.update");
