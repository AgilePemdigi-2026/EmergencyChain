<?php

use Illuminate\Support\Facades\Route;

// Halaman Login
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

// Halaman Register
Route::get('/register', function () {
    return view('auth.register');
})->name('register');

// Preview UI Logout (sementara)
Route::get('/preview-logout', function () {
    return view('auth.preview-logout');
})->name('preview.logout');