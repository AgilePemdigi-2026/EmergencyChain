<?php

namespace App\Http\Controllers\PetugasEC;

use App\Http\Controllers\Controller;
use App\Models\EmergencyCase;
use App\Services\Matching\ResourceMatchingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResourceMatchingController extends Controller
{
    /**
     * Menampilkan hasil Resource Matching suatu Emergency Case.
     *
     * Halaman ini hanya membaca hasil Assignment yang tersimpan.
     * Tidak menjalankan matching ulang.
     */
    public function show(
        Request $request,
        int $caseId,
        ResourceMatchingService $matchingService
    ): View {
        abort_unless(
            $request->user()?->role === 'petugas_ec',
            403
        );

        // Ambil Emergency Case beserta lokasi dan pelapor.
        $case = EmergencyCase::with([
            'pengguna',
            'lokasi',
        ])->findOrFail($caseId);

        // Ambil Assignment yang sudah dibuat sistem.
        $assignments = $case->assignments()
            ->with([
                'resource.instansi',
                'resource.lokasiPangkalan',
                'statusPenanganan' => function ($query) {
                    $query->orderByDesc('status_id');
                },
            ])
            ->orderBy('waktu_assign')
            ->get();

        // Identifikasi kebutuhan Resource berdasarkan kejadian.
        $requiredTypes = $matchingService
            ->getRequiredResourceTypes($case->jenis_kejadian);

        // Identifikasi jenis Resource yang sudah memiliki Assignment.
        $assignedTypes = $assignments
            ->map(fn ($assignment) =>
                $assignment->resource?->jenis_resource
            )
            ->filter()
            ->unique()
            ->values()
            ->all();

        // Jenis Resource yang belum berhasil ditugaskan.
        $missingTypes = $requiredTypes === null
            ? []
            : array_values(
                array_diff($requiredTypes, $assignedTypes)
            );

        // Hitung jarak dari lokasi kejadian ke pangkalan Resource.
        // Jarak ini merupakan jarak garis lurus.
        $assignments = $assignments->map(
            function ($assignment) use ($case) {

                $lokasiKejadian = $case->lokasi;
                $lokasiResource = $assignment->resource
                    ?->lokasiPangkalan;

                $distance = null;

                if (
                    $lokasiKejadian &&
                    $lokasiResource &&
                    is_numeric($lokasiKejadian->latitude) &&
                    is_numeric($lokasiKejadian->longitude) &&
                    is_numeric($lokasiResource->latitude) &&
                    is_numeric($lokasiResource->longitude)
                ) {
                    $distance = round(
                        $this->calculateDistance(
                            (float) $lokasiKejadian->latitude,
                            (float) $lokasiKejadian->longitude,
                            (float) $lokasiResource->latitude,
                            (float) $lokasiResource->longitude
                        ),
                        2
                    );
                }

                $assignment->setAttribute(
                    'jarak_km',
                    $distance
                );

                return $assignment;
            }
        );

        // Tentukan kondisi hasil matching.
        $matchingState = match (true) {
            $requiredTypes === null => 'needs_verification',
            empty($missingTypes) => 'complete',
            $assignments->isNotEmpty() => 'partial',
            default => 'waiting',
        };

        $matchingLabels = [
            'complete' => 'Matching Lengkap',
            'partial' => 'Sebagian Resource Ditugaskan',
            'waiting' => 'Menunggu Resource',
            'needs_verification' => 'Perlu Verifikasi',
        ];

        $matchingStatusLabel = $matchingLabels[$matchingState];

        // Tentukan kondisi setiap tahapan proses.
        $searchState = match (true) {
            $requiredTypes === null => 'needs_verification',
            empty($missingTypes) => 'complete',
            $assignments->isNotEmpty() => 'partial',
            default => 'waiting',
        };

        $assignmentState = match (true) {
            $assignments->isEmpty() => 'waiting',
            !empty($missingTypes) => 'partial',
            default => 'complete',
        };

        $processSteps = [
            [
                'title' => 'Identifikasi Kebutuhan',
                'description' => $requiredTypes === null
                    ? 'Jenis kejadian belum memiliki pemetaan kebutuhan Resource.'
                    : 'Sistem mengidentifikasi kebutuhan: '
                        . implode(', ', $requiredTypes) . '.',
                'state' => $requiredTypes === null
                    ? 'needs_verification'
                    : 'complete',
            ],
            [
                'title' => 'Pencarian Resource Terdekat',
                'description' => 'Sistem mencari Resource dengan status tersedia dan koordinat pangkalan.',
                'state' => $searchState,
            ],
            [
                'title' => 'Pembuatan Assignment',
                'description' => $assignments->isNotEmpty()
                    ? $assignments->count()
                        . ' Assignment telah tercatat dalam sistem.'
                    : 'Belum terdapat Assignment yang berhasil dibuat.',
                'state' => $assignmentState,
            ],
        ];

        return view(
            'petugas-ec.resource-matching.show',
            compact(
                'case',
                'assignments',
                'requiredTypes',
                'missingTypes',
                'matchingState',
                'matchingStatusLabel',
                'processSteps'
            )
        );
    }

    /**
     * Menghitung jarak menggunakan rumus Haversine.
     * Hasil dalam kilometer.
     */
    private function calculateDistance(
        float $lat1,
        float $lon1,
        float $lat2,
        float $lon2
    ): float {
        $earthRadius = 6371;

        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLon = deg2rad($lon2 - $lon1);

        $a = sin($deltaLat / 2) ** 2
            + cos(deg2rad($lat1))
            * cos(deg2rad($lat2))
            * sin($deltaLon / 2) ** 2;

        $c = 2 * atan2(
            sqrt($a),
            sqrt(1 - $a)
        );

        return $earthRadius * $c;
    }
}
