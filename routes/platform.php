<?php

use App\Http\Controllers\Platform\PlatformAuthController;
use App\Http\Controllers\Platform\TenantController;
use Illuminate\Support\Facades\Route;

Route::get("/login", [PlatformAuthController::class, "create"])->name("platform.login");
Route::post("/login", [PlatformAuthController::class, "store"])->middleware("throttle:platform-login")->name("platform.login.store");

Route::middleware("platform.auth")->group(function (): void {
    Route::get("/", fn () => redirect()->route("platform.tenants.index"))->name("platform.home");
    Route::post("/logout", [PlatformAuthController::class, "destroy"])->name("platform.logout");

    Route::get("/tenants", [TenantController::class, "index"])->name("platform.tenants.index");
    Route::post("/tenants", [TenantController::class, "store"])->middleware("throttle:2,10")->name("platform.tenants.store");
    Route::get("/tenants/{tenant}", [TenantController::class, "show"])->name("platform.tenants.show");
    Route::patch("/tenants/{tenant}/status", [TenantController::class, "status"])->middleware("throttle:20,1")->name("platform.tenants.status");
    Route::put("/tenants/{tenant}/modules", [TenantController::class, "modules"])->middleware("throttle:20,1")->name("platform.tenants.modules");
    Route::post("/tenants/{tenant}/announcements", [TenantController::class, "announcement"])->middleware("throttle:20,1")->name("platform.tenants.announcements.store");
    Route::patch("/tenants/{tenant}/announcements/{announcement}", [TenantController::class, "announcementStatus"])
        ->middleware("throttle:20,1")
        ->name("platform.tenants.announcements.status");
});

Route::fallback(fn () => abort(404));
