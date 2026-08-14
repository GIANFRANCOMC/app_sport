<?php

use App\Http\Controllers\System\Organizations\{BusinessProfileController};
use Illuminate\Support\Facades\{Route};

$entity = "business_profile";

Route::get("", [BusinessProfileController::class, "index"])->name("$entity.index");
Route::get("/initParams", [BusinessProfileController::class, "initParams"])->name("$entity.initParams");
Route::post("/apply", [BusinessProfileController::class, "apply"])->name("$entity.apply");
Route::patch("/modules", [BusinessProfileController::class, "updateModules"])->name("$entity.modules.update");
