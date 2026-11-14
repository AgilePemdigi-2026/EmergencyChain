<?php

use App\Http\Controllers\PetugasEC\EmergencyCaseController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PetugasEC\ResourceMatchingController;
use App\Http\Controllers\PetugasEC\DashboardController;
use App\Http\Controllers\PetugasEC\AssignmentController;

Route::middleware('auth')->group(function () {

    Route::get(
        '/petugas-ec/dashboard',
        [DashboardController::class, 'index']
    )->name('petugas.ec.dashboard');

    Route::get(
        '/petugas-ec/emergency-cases',
        [EmergencyCaseController::class, 'index']
    )->name('petugas.ec.cases.index');

    Route::get(
        '/petugas-ec/emergency-cases/{caseId}',
        [EmergencyCaseController::class, 'show']
    )->whereNumber('caseId')
     ->name('petugas.ec.cases.show');

    Route::patch(
        '/petugas-ec/emergency-cases/{caseId}',
        [EmergencyCaseController::class, 'update']
    )->whereNumber('caseId')
    ->name('petugas.ec.cases.update');

    Route::post(
        '/petugas-ec/emergency-cases/{caseId}/hospital-verification',
        [EmergencyCaseController::class, 'verifyHospitalReferral']
    )->whereNumber('caseId')
    ->name('petugas.ec.cases.hospital-verification');

    Route::get(
        '/petugas-ec/emergency-cases/{caseId}/resource-matching',
        [ResourceMatchingController::class, 'show']
    )->whereNumber('caseId')
    ->name('petugas.ec.cases.resource-matching');

    Route::get(
        '/petugas-ec/emergency-cases/{caseId}/hospital-matching',
        [EmergencyCaseController::class, 'hospitalMatchingPlaceholder']
    )->whereNumber('caseId')
    ->name('petugas.ec.cases.hospital-matching');

    Route::get(
        '/petugas-ec/assignments',
        [AssignmentController::class, 'index']
    )->name('petugas.ec.assignments.index');

});
