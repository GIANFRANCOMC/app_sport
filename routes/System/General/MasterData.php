<?php

use App\Http\Controllers\System\General\MasterDataController;
use Illuminate\Support\Facades\Route;

Route::get('/{resource}', [MasterDataController::class, 'list'])->name('master_data.list');
Route::post('/{resource}', [MasterDataController::class, 'store'])->name('master_data.store');
Route::patch('/{resource}/{id}', [MasterDataController::class, 'update'])->name('master_data.update');
