<?php

declare(strict_types=1);

use App\Http\Controllers\System\Organizations\BranchController;
use Illuminate\Support\Facades\Route;

/**
 * Branch Routes
 *
 * All routes related to branch management are defined here.
 * Routes are organized following RESTful conventions.
 */

$entity = "branches";

// Index route - Display branches page
Route::get("", [BranchController::class, "index"])->name("{$entity}.index");

// Initialization parameters for frontend
Route::get("/initParams", [BranchController::class, "initParams"])->name("{$entity}.initParams");

// List route - Get paginated list of branches
Route::get("/list", [BranchController::class, "list"])->name("{$entity}.list");

// Create route - Show create form (SPA handled)
Route::get("/create", [BranchController::class, "create"])->name("{$entity}.create");

// Store route - Create new branch
Route::post("", [BranchController::class, "store"])->name("{$entity}.store");

// Show route - Display specific branch
Route::get("/{id}", [BranchController::class, "show"])->name("{$entity}.show");

// Edit route - Show edit form (SPA handled)
Route::get("/{id}/edit", [BranchController::class, "edit"])->name("{$entity}.edit");

// Update route - Update existing branch
Route::patch("/{id}", [BranchController::class, "update"])->name("{$entity}.update");

// Delete route - Delete branch (if implemented)
Route::delete("/{id}", [BranchController::class, "destroy"])->name("{$entity}.destroy");

