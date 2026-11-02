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
        'tiba_di_lokasi',
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

            // Pastikan Assignment benar-benar milik Instansi login.
            $assignment = Assignment::query()
                ->where('assignment_id', $assignmentId)
                ->whereHas('resource', function ($query) use ($user) {
                    $query->where(
                        'instansi_id',
                        $user->instansi_id
                    );
                })
                ->lockForUpdate()
                ->firstOrFail();

            // Assignment yang sudah selesai/tidak aktif tidak boleh diubah.
            if (in_array(
                $assignment->status_assign,
                ['selesai', 'ditolak', 'dibatalkan'],
                true
            )) {
                abort(422, 'Assignment sudah tidak aktif.');
            }

            // Kunci Resource.
            $resource = Resource::query()
                ->where(
                    'resource_id',
                    $assignment->resource_id
                )
                ->lockForUpdate()
                ->firstOrFail();

            // Simpan riwayat status + catatan + waktu.
            StatusPenanganan::create([
                'assignment_id' => $assignment->assignment_id,
                'case_id' => $assignment->case_id,
                'status' => $status,
                'waktu_update' => now(),
                'keterangan' => $keterangan,
            ]);

            $resourceReleased = false;

            // Jika penanganan selesai.
            if ($status === 'selesai') {

                $assignment->update([
                    'status_assign' => 'selesai',
                ]);

                $hasOtherActiveAssignment = Assignment::query()
                    ->where(
                        'resource_id',
                        $resource->resource_id
                    )
                    ->where(
                        'assignment_id',
                        '!=',
                        $assignment->assignment_id
                    )
                    ->whereNotIn(
                        'status_assign',
                        [
                            'selesai',
                            'ditolak',
                            'dibatalkan',
                        ]
                    )
                    ->exists();

                if (!$hasOtherActiveAssignment) {

                    $resource->update([
                        'status_ketersediaan' => 'tersedia',
                    ]);

                    $resourceReleased = true;
                }
            }

            $this->syncEmergencyCaseStatus(
                $assignment->case_id
            );

            return [
                'resource_released' => $resourceReleased,
            ];
        });

        // Jalankan matching ulang jika resource kembali tersedia.
        if ($result['resource_released']) {
            $result['rematching'] =
                $this->rematchWaitingCases();
        }

        return $result;
    }

    /**
     * Sinkronisasi status Emergency Case berdasarkan
     * perkembangan seluruh Assignment pada Case.
     */
    private function syncEmergencyCaseStatus(
        int $caseId
    ): void {
        $case = EmergencyCase::query()
            ->where('case_id', $caseId)
            ->lockForUpdate()
            ->firstOrFail();

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

        if (
            $latestStatuses->contains(
                'dalam_penanganan'
            )
        ) {
            $newStatus = 'Dalam Penanganan';

        } elseif (
            $latestStatuses->contains(
                'tiba_di_lokasi'
            )
        ) {
            $newStatus = 'Tiba di Lokasi';

        } elseif (
            $latestStatuses->contains(
                'menuju_lokasi'
            )
        ) {
            $newStatus = 'Menuju Lokasi';

        } else {
            $newStatus = 'Menunggu';
        }

        $case->update([
            'status_laporan' => $newStatus,
        ]);
    }

    /**
     * Mencoba matching ulang Case yang masih
     * menunggu Assignment.
     */
    private function rematchWaitingCases(): array
    {
        $waitingCases = EmergencyCase::query()
            ->where(
                'status_laporan',
                'Menunggu Assignment'
            )
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
                        'error' =>
                            $exception->getMessage(),
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