<?php

namespace App\Services\Assignment;

use App\Models\Assignment;
use App\Models\EmergencyCase;
use App\Models\Pengguna;
use App\Models\Resource;
use App\Models\StatusPenanganan;
use App\Services\Matching\ResourceMatchingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class AssignmentStatusService
{
    public const ALLOWED_STATUSES = [
        'menuju_lokasi',
        'dalam_penanganan',
        'selesai',
    ];

    public function __construct(
        private ResourceMatchingService $matchingService
    ) {}

    /**
     * Memperbarui status Assignment milik Instansi.
     */
    public function updateStatus(
        int $assignmentId,
        Pengguna $user,
        string $status,
        string $keterangan
    ): array {
        $result = DB::transaction(function () use (
            $assignmentId,
            $user,
            $status,
            $keterangan
        ) {

            // 1. Pastikan Assignment milik Instansi login.
            $assignment = Assignment::query()
                ->where('assignment_id', $assignmentId)
                ->whereHas('resource', function ($query) use ($user) {
                    $query->where('instansi_id', $user->instansi_id);
                })
                ->lockForUpdate()
                ->firstOrFail();

            // Assignment selesai tidak boleh diperbarui lagi.
            if (in_array(
                $assignment->status_assign,
                ['selesai', 'ditolak', 'dibatalkan'],
                true
            )) {
                abort(422, 'Assignment sudah tidak aktif.');
            }

            // 2. Kunci Resource.
            $resource = Resource::query()
                ->where('resource_id', $assignment->resource_id)
                ->lockForUpdate()
                ->firstOrFail();

            // 3. Simpan riwayat penanganan.
            StatusPenanganan::create([
                'assignment_id' => $assignment->assignment_id,
                'case_id' => $assignment->case_id,
                'status' => $status,
                'waktu_update' => now(),
                'keterangan' => $keterangan,
            ]);

            $resourceReleased = false;

            // 4. Jika penanganan selesai.
            if ($status === 'selesai') {

                $assignment->update([
                    'status_assign' => 'selesai',
                ]);

                // Periksa apakah Resource masih digunakan
                // oleh Assignment aktif lainnya.
                $hasOtherActiveAssignment = Assignment::query()
                    ->where('resource_id', $resource->resource_id)
                    ->where(
                        'assignment_id',
                        '!=',
                        $assignment->assignment_id
                    )
                    ->whereNotIn('status_assign', [
                        'selesai',
                        'ditolak',
                        'dibatalkan',
                    ])
                    ->exists();

                // Resource hanya dilepas jika tidak ada
                // Assignment aktif lainnya.
                if (!$hasOtherActiveAssignment) {
                    $resource->update([
                        'status_ketersediaan' => 'tersedia',
                    ]);

                    $resourceReleased = true;
                }
            }

            // 5. Sinkronisasi status Emergency Case.
            $this->syncEmergencyCaseStatus(
                $assignment->case_id
            );

            return [
                'resource_released' => $resourceReleased,
            ];
        });

        // 6. Matching ulang setelah transaksi selesai.
        if ($result['resource_released']) {
            $result['rematching'] =
                $this->rematchWaitingCases();
        }

        return $result;
    }

    /**
     * Menyesuaikan status Emergency Case
     * berdasarkan seluruh Assignment.
     */
    private function syncEmergencyCaseStatus(
        int $caseId
    ): void {

        $case = EmergencyCase::query()
            ->where('case_id', $caseId)
            ->lockForUpdate()
            ->firstOrFail();

        // Jangan mengubah Case yang masih membutuhkan
        // Resource atau masih perlu verifikasi.
        if (in_array(
            $case->status_laporan,
            ['Menunggu Assignment', 'Perlu Verifikasi'],
            true
        )) {
            return;
        }

        $assignments = Assignment::query()
            ->where('case_id', $caseId)
            ->get();

        if ($assignments->isEmpty()) {
            return;
        }

        // Case selesai hanya jika seluruh Assignment selesai.
        $allCompleted = $assignments->every(
            fn ($assignment) =>
                $assignment->status_assign === 'selesai'
        );

        if ($allCompleted) {
            $case->update([
                'status_laporan' => 'Selesai',
            ]);

            return;
        }

        // Ambil status penanganan terakhir setiap Assignment.
        $latestStatuses = StatusPenanganan::query()
            ->whereIn(
                'assignment_id',
                $assignments->pluck('assignment_id')
            )
            ->orderByDesc('waktu_update')
            ->orderByDesc('status_id')
            ->get()
            ->unique('assignment_id')
            ->pluck('status');

        // Jika ada unit yang sedang melakukan penanganan.
        if ($latestStatuses->contains('dalam_penanganan')) {
            $newStatus = 'Dalam Penanganan';
        }

        // Jika ada unit yang sedang menuju lokasi.
        elseif ($latestStatuses->contains('menuju_lokasi')) {
            $newStatus = 'Menuju Lokasi';
        }

        // Belum ada unit yang mulai bertugas.
        else {
            $newStatus = 'Menunggu';
        }

        $case->update([
            'status_laporan' => $newStatus,
        ]);
    }

    /**
     * Mencoba kembali Emergency Case yang menunggu Assignment.
     */
    private function rematchWaitingCases(): array
    {
        $waitingCases = EmergencyCase::query()
            ->where('status_laporan', 'Menunggu Assignment')
            ->orderBy('waktu_laporan')
            ->orderBy('case_id')
            ->get();

        $results = [];

        foreach ($waitingCases as $case) {
            try {
                $results[$case->case_id] =
                    $this->matchingService->match($case);

            } catch (Throwable $exception) {

                Log::error(
                    'Resource matching ulang gagal.',
                    [
                        'case_id' => $case->case_id,
                        'error' => $exception->getMessage(),
                    ]
                );

                $results[$case->case_id] = [
                    'success' => false,
                ];
            }
        }

        return $results;
    }
}
