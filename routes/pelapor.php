<?php

use App\Http\Controllers\Pelapor\EmergencyCaseController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Pelapor\ReportStatusController;

Route::middleware(['auth', 'role:pelapor'])->group(function () {
    Route::get(
        '/pelapor/laporan/buat',
        [EmergencyCaseController::class, 'create']
    )->name('pelapor.laporan.create');

    Route::get(
    '/pelapor/laporan/status',
    [ReportStatusController::class, 'index']
    )->name('pelapor.laporan.index');

    Route::get(
    '/pelapor/laporan/{caseId}',
    [ReportStatusController::class, 'show']
    )->whereNumber('caseId')
     ->name('pelapor.laporan.show');

    Route::post(
        '/pelapor/laporan/buat',
        [EmergencyCaseController::class, 'store']
    )->name('pelapor.laporan.store');
});
