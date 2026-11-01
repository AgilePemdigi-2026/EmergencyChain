<?php

use App\Http\Controllers\Instansi\AssignmentController;
use App\Http\Controllers\Instansi\ResourceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:instansi'])
    ->prefix('instansi')
    ->name('instansi.')
    ->group(function () {

        //Dashboard Assignment
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

        // Kelola Unit

        Route::get(
            '/units',
            [ResourceController::class, 'index']
        )->name('units.index');

        Route::post(
            '/units',
            [ResourceController::class, 'store']
        )->name('units.store');

        Route::put(
            '/units/{resourceId}',
            [ResourceController::class, 'update']
        )
            ->whereNumber('resourceId')
            ->name('units.update');

        Route::patch(
            '/units/{resourceId}/availability',
            [ResourceController::class, 'updateAvailability']
        )
            ->whereNumber('resourceId')
            ->name('units.availability.update');

        Route::delete(
            '/units/{resourceId}',
            [ResourceController::class, 'destroy']
        )
            ->whereNumber('resourceId')
            ->name('units.destroy');
    });