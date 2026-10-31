<?php

use App\Http\Controllers\Instansi\AssignmentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:instansi'])
    ->prefix('instansi')
    ->name('instansi.')
    ->group(function () {

        Route::get(
            '/assignments',
            [AssignmentController::class, 'index']
        )->name('assignments.index');

        Route::patch(
            '/assignments/{assignmentId}/status',
            [AssignmentController::class, 'updateStatus']
        )
            ->whereNumber('assignmentId')
            ->name('assignments.status.update');
    });
