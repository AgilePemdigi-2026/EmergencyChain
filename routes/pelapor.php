<?php

use Illuminate\Support\Facades\Route;

Route::view(
    '/pelapor/laporan/buat',
    'pelapor.emergency-report.create'
)->name('pelapor.laporan.create');