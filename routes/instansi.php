<?php

use App\Http\Controllers\Instansi\AssignmentController;
use App\Http\Controllers\Instansi\ResourceController;
use App\Http\Controllers\Instansi\HistoryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:instansi'])
    ->prefix('instansi')
    ->name('instansi.')
    ->group(function () {

        // DASHBOARD & STATUS ASSIGNMENT
        Route::get(
            '/assignments',
            [AssignmentController::class, 'index']
        )->name('assignments.index');

        Route::get(
            '/assignments/{assignmentId}',
            [AssignmentController::class, 'show']
        )
            ->whereNumber('assignmentId')
            ->name('assignments.show');

        Route::patch(
            '/assignments/{assignmentId}/status',
            [AssignmentController::class, 'updateStatus']
        )
            ->whereNumber('assignmentId')
            ->name('assignments.status.update');

        // RIWAYAT PENANGANAN INSTANSI
        Route::get(
            '/history',
            [HistoryController::class, 'index']
        )->name('history.index');

        Route::get(
            '/history/{assignmentId}',
            [HistoryController::class, 'show']
        )
            ->whereNumber('assignmentId')
            ->name('history.show');

        // KELOLA KETERSEDIAAN UNIT
        Route::get(
            '/units',
            [ResourceController::class, 'index']
        )->name('units.index');

        Route::patch(
            '/units/{resourceId}/availability',
            [ResourceController::class, 'updateAvailability']
        )
            ->whereNumber('resourceId')
            ->name('units.availability.update');
    });
