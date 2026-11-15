<?php

use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')
    ->prefix('notifications')
    ->name('notifications.')
    ->group(function () {

        Route::get(
            '/',
            [NotificationController::class, 'index']
        )->name('index');

        Route::patch(
            '/read-all',
            [NotificationController::class, 'markAllAsRead']
        )->name('read-all');

        Route::patch(
            '/{notificationId}/read',
            [NotificationController::class, 'markAsRead']
        )->name('read');

    });
