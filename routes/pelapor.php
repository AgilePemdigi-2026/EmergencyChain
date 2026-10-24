<?php

use App\Http\Controllers\Pelapor\EmergencyCaseController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get(
        '/pelapor/laporan/buat',
        [EmergencyCaseController::class, 'create']
    )->name('pelapor.laporan.create');

    Route::post(
        '/pelapor/laporan/buat',
        [EmergencyCaseController::class, 'store']
    )->name('pelapor.laporan.store');
});
