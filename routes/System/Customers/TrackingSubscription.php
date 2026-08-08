<?php

use App\Http\Controllers\System\Customers\{TrackingSubscriptionController};
use Illuminate\Support\Facades\{Route};

$entity = "tracking_subscriptions";

Route::get("", [TrackingSubscriptionController::class, "index"])->name("$entity.index");
Route::get("/initParams", [TrackingSubscriptionController::class, "initParams"])->name("$entity.initParams");
Route::get("/list", [TrackingSubscriptionController::class, "list"])->name("$entity.list");
Route::post("/manual", [TrackingSubscriptionController::class, "storeManual"])->name("$entity.manual");
Route::patch("/cancel/{id}", [TrackingSubscriptionController::class, "cancel"])->name("$entity.cancel");
Route::post("/{id}/renew", [TrackingSubscriptionController::class, "renew"])->name("$entity.renew");
