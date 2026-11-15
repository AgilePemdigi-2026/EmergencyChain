<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Hospital\HospitalReferralController;
use App\Http\Controllers\Hospital\HospitalPatientController;

// Halaman utama
Route::get('/', function () {
    return redirect('/login');
});


Route::middleware('auth')->group(function () {
    Route::get(
        '/hospital/referrals',
        [HospitalReferralController::class, 'index']
    )->name('hospital.referrals.index');

    Route::get(
    '/hospital/patients/history',
    [HospitalPatientController::class, 'history']
)->name('hospital.patients.history');

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

        // Hospital Patients
    Route::get(
        '/hospital/patients',
        [HospitalPatientController::class, 'index']
    )->name('hospital.patients.index');

    Route::get(
        '/hospital/patients/{informasiId}',
        [HospitalPatientController::class, 'show']
    )->whereNumber('informasiId')
     ->name('hospital.patients.show');

    Route::put(
        '/hospital/patients/{informasiId}/status',
        [HospitalPatientController::class, 'updateStatus']
    )->whereNumber('informasiId')
     ->name('hospital.patients.update-status');

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


