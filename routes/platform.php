<?php

use App\Http\Controllers\Platform\{PlatformAuthController, PlatformShellController, TenantController};
use Illuminate\Support\Facades\{Route};

Route::get("/login", [PlatformAuthController::class, "create"])->name("platform.login");
Route::post("/login", [PlatformAuthController::class, "store"])->middleware("throttle:platform-login")->name("platform.login.store");

Route::middleware("platform.auth")->group(function(): void {

    Route::get("/", fn() => redirect()->route("platform.tenants.index"))->name("platform.home");
    Route::post("/logout", [PlatformAuthController::class, "destroy"])->name("platform.logout");

    Route::get("/tenants", PlatformShellController::class)->name("platform.tenants.index");
    Route::get("/tenants/{tenant}", PlatformShellController::class)->name("platform.tenants.show");

    Route::prefix("/api/tenants")->name("platform.api.tenants.")->group(function(): void {

        Route::get("/", [TenantController::class, "index"])->name("index");
        Route::post("/", [TenantController::class, "store"])->middleware("throttle:platform-provision")->name("store");
        Route::get("/{tenant}", [TenantController::class, "show"])->name("show");
        Route::patch("/{tenant}/status", [TenantController::class, "status"])->middleware("throttle:platform-write")->name("status");
        Route::put("/{tenant}/modules", [TenantController::class, "modules"])->middleware("throttle:platform-write")->name("modules");
        Route::post("/{tenant}/announcements", [TenantController::class, "announcement"])->middleware("throttle:platform-write")->name("announcements.store");
        Route::patch("/{tenant}/announcements/{announcement}", [TenantController::class, "announcementStatus"])
            ->middleware("throttle:platform-write")
            ->name("announcements.status");

    });

});

Route::fallback(fn() => abort(404));
