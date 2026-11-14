<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {

    // Seluruh aktor dapat melihat profil.
    Route::get(
        '/profile',
        [ProfileController::class, 'show']
    )->name('profile.show');

    // Hanya Pelapor yang dapat memperbarui profil.
    Route::patch(
        '/profile',
        [ProfileController::class, 'update']
    )->name('profile.update');

    // Hanya Pelapor yang dapat mengubah password.
    Route::put(
        '/profile/password',
        [ProfileController::class, 'updatePassword']
    )->name('profile.password.update');

});
