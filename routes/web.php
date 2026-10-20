<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view(
    '/pelapor/laporan/buat',
    'pelapor.emergency-report.create'
)->name('pelapor.laporan.create');