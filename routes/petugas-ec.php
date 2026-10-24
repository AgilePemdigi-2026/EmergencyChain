
<?php

use App\Http\Controllers\PetugasEC\EmergencyCaseController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {

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

});
