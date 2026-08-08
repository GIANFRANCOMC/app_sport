<?php

use App\Http\Controllers\System\Customers\{TrackingCustomerController};
use Illuminate\Support\Facades\{Route};

$entity = "tracking_customers";

Route::get("", [TrackingCustomerController::class, "index"])->name("$entity.index");
Route::get("/initParams", [TrackingCustomerController::class, "initParams"])->name("$entity.initParams");
Route::get("/getTracking/{id}", [TrackingCustomerController::class, "getTracking"])->name("$entity.getTracking");
