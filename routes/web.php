<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Hospital\HospitalReferralController;

// Halaman utama
Route::get('/', function () {
    return redirect('/login');
});


Route::middleware('auth')->group(function () {
    Route::get(
        '/hospital/referrals',
        [HospitalReferralController::class, 'index']
    )->name('hospital.referrals.index');

    Route::post(
        '/hospital/referrals/{requestId}/respond',
        [HospitalReferralController::class, 'respond']
    )->whereNumber('requestId')
     ->name('hospital.referrals.respond');

    Route::post(
        '/hospital/referrals/start/{caseId}',
        [HospitalReferralController::class, 'start']
    )->whereNumber('caseId')
     ->name('hospital.referrals.start');

    Route::get(
    '/hospital/referrals/{requestId}',
    [HospitalReferralController::class, 'show']
)->whereNumber('requestId')
 ->name('hospital.referrals.show');
});


// Route berdasarkan modul
require __DIR__.'/auth.php';
require __DIR__.'/pelapor.php';
require __DIR__.'/petugas-ec.php';
require __DIR__.'/instansi.php';
require __DIR__.'/profile.php';


