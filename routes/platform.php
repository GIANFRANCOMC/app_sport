<?php

use App\Http\Controllers\Platform\{PlatformAuthController, TenantController};
use Illuminate\Support\Facades\Route;

Route::get('/login', [PlatformAuthController::class, 'create'])->name('platform.login');
Route::post('/login', [PlatformAuthController::class, 'store'])->middleware('throttle:6,1')->name('platform.login.store');

Route::middleware('platform.auth')->group(function(): void {
    Route::get('/', fn() => redirect()->route('platform.tenants.index'))->name('platform.home');
    Route::post('/logout', [PlatformAuthController::class, 'destroy'])->name('platform.logout');

    Route::get('/clientes', [TenantController::class, 'index'])->name('platform.tenants.index');
    Route::post('/clientes', [TenantController::class, 'store'])->name('platform.tenants.store');
    Route::get('/clientes/{tenant}', [TenantController::class, 'show'])->name('platform.tenants.show');
    Route::patch('/clientes/{tenant}/estado', [TenantController::class, 'status'])->name('platform.tenants.status');
    Route::put('/clientes/{tenant}/modulos', [TenantController::class, 'modules'])->name('platform.tenants.modules');
    Route::post('/clientes/{tenant}/avisos', [TenantController::class, 'announcement'])->name('platform.tenants.announcements.store');
    Route::patch('/clientes/{tenant}/avisos/{announcement}', [TenantController::class, 'announcementStatus'])
        ->name('platform.tenants.announcements.status');
});

Route::fallback(fn() => abort(404));
