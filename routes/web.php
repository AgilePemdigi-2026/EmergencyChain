<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route khusus Authentication
require __DIR__.'/auth.php';