<?php

namespace App\Services\Notification;

use App\Models\Pengguna;
use App\Notifications\EmergencyChainNotification;

class NotificationService
{
    /**
     * Mengirim notifikasi kepada satu pengguna.
     */
    public function sendToUser(
        ?Pengguna $user,
        string $title,
        string $message,
        string $url,
        string $category
    ): void {
        if (!$user) {
            return;
        }

        $user->notify(
            new EmergencyChainNotification(
                $title,
                $message,
                $url,
                $category
            )
        );
    }

    /**
     * Mengirim notifikasi kepada seluruh pengguna
     * dengan role tertentu.
     */
    public function sendToRole(
        string $role,
        string $title,
        string $message,
        string $url,
        string $category
    ): void {
        Pengguna::query()
            ->where('role', $role)
            ->each(function (Pengguna $user) use (
                $title,
                $message,
                $url,
                $category
            ) {
                $this->sendToUser(
                    $user,
                    $title,
                    $message,
                    $url,
                    $category
                );
            });
    }

    /**
     * Mengirim notifikasi kepada pengguna
     * yang terhubung dengan Instansi tertentu.
     */
    public function sendToInstansi(
        int $instansiId,
        string $title,
        string $message,
        string $url,
        string $category
    ): void {
        Pengguna::query()
            ->where('role', 'instansi')
            ->where('instansi_id', $instansiId)
            ->each(function (Pengguna $user) use (
                $title,
                $message,
                $url,
                $category
            ) {
                $this->sendToUser(
                    $user,
                    $title,
                    $message,
                    $url,
                    $category
                );
            });
    }

    /**
     * Mengirim notifikasi kepada pengguna
     * Rumah Sakit tertentu.
     */
    public function sendToRumahSakit(
        int $rumahSakitId,
        string $title,
        string $message,
        string $url,
        string $category
    ): void {
        Pengguna::query()
            ->where('role', 'rumah_sakit')
            ->where('rumah_sakit_id', $rumahSakitId)
            ->each(function (Pengguna $user) use (
                $title,
                $message,
                $url,
                $category
            ) {
                $this->sendToUser(
                    $user,
                    $title,
                    $message,
                    $url,
                    $category
                );
            });
    }

    /**
     * Mengirim notifikasi kepada Pelapor.
     */
    public function sendToPelapor(
        int $penggunaId,
        string $title,
        string $message,
        string $url,
        string $category
    ): void {
        $user = Pengguna::query()
            ->where('pengguna_id', $penggunaId)
            ->where('role', 'pelapor')
            ->first();

        $this->sendToUser(
            $user,
            $title,
            $message,
            $url,
            $category
        );
    }
}
