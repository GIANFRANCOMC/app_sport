<?php

declare(strict_types=1);

use App\Http\Controllers\System\Operations\ServiceOperationController;
use Illuminate\Support\Facades\Route;

Route::get("/restaurant", [ServiceOperationController::class, "index"])->name("restaurant_pos.index");
Route::get("/services", [ServiceOperationController::class, "index"])->name("service_sessions.index");
Route::get("/initParams", [ServiceOperationController::class, "initParams"])->name("service_operations.initParams");
Route::get("/stations", [ServiceOperationController::class, "stations"])->name("service_operations.stations");
Route::post("/stations", [ServiceOperationController::class, "storeStation"])->name("service_operations.stations.store");
Route::get("/sessions", [ServiceOperationController::class, "sessions"])->name("service_operations.sessions");
Route::post("/sessions", [ServiceOperationController::class, "openSession"])->name("service_operations.sessions.store");
Route::get("/sessions/{id}", [ServiceOperationController::class, "show"])->name("service_operations.sessions.show");
Route::post("/sessions/{id}/items", [ServiceOperationController::class, "addItem"])->name("service_operations.items.store");
Route::patch("/sessions/{id}/start", [ServiceOperationController::class, "startSession"])->name("service_operations.sessions.start");
Route::patch("/sessions/{id}/complete", [ServiceOperationController::class, "completeSession"])->name("service_operations.sessions.complete");
Route::patch("/items/{id}/start", [ServiceOperationController::class, "startItem"])->name("service_operations.items.start");
Route::patch("/items/{id}/complete", [ServiceOperationController::class, "completeItem"])->name("service_operations.items.complete");
