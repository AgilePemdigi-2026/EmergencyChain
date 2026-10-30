<?php

use Illuminate\Support\Facades\Route;

// Halaman utama
Route::get('/', function () {
    return redirect('/login');
});

// Route berdasarkan modul
require __DIR__.'/auth.php';
require __DIR__.'/pelapor.php';
require __DIR__.'/petugas-ec.php';
require __DIR__.'/instansi.php';


