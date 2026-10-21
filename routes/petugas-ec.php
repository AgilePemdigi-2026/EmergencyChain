<?php

use Illuminate\Support\Facades\Route;

Route::view(
    '/petugas-ec/emergency-cases',
    'petugas-ec.emergency-cases.index'
)->name('petugas.ec.cases.index');