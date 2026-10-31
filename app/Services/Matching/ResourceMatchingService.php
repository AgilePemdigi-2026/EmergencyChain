<?php

namespace App\Services\Matching;

use App\Models\Assignment;
use App\Models\EmergencyCase;
use App\Models\Resource;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ResourceMatchingService
{
    /**
     * Menjalankan Resource Matching untuk Emergency Case.
     */
    public function match(EmergencyCase $case): array
    {
        return DB::transaction(function () use ($case) {

            // Ambil ulang Emergency Case beserta lokasi kejadian.
            $case = EmergencyCase::with('lokasi')
                ->lockForUpdate()
                ->findOrFail($case->case_id);

            if (!$case->lokasi) {
                throw new InvalidArgumentException(
                    'Lokasi kejadian tidak ditemukan.'
                );
            }

            // Tentukan jenis Resource berdasarkan kejadian.
            $requiredTypes = $this->getRequiredResourceTypes(
                $case->jenis_kejadian
            );

            // Kejadian yang belum memiliki pemetaan perlu diverifikasi.
            if ($requiredTypes === null) {
                $case->update([
                    'status_laporan' => 'Perlu Verifikasi',
                ]);

                return [
                    'success' => false,
                    'needs_verification' => true,
                    'matched' => [],
                    'missing' => [],
                ];
            }

            $matched = [];
            $missing = [];

            foreach ($requiredTypes as $type) {

                // Cegah Assignment duplikat jika matching dijalankan ulang.
                $existing = Assignment::where('case_id', $case->case_id)
                    ->whereHas('resource', function ($query) use ($type) {
                        $query->where('jenis_resource', $type);
                    })
                    ->with('resource')
                    ->first();

                if ($existing) {
                    $matched[] = [
                        'resource_id' => $existing->resource_id,
                        'jenis_resource' => $type,
                    ];

                    continue;
                }

                // Ambil Resource tersedia yang memiliki koordinat pangkalan.
                $resources = Resource::where(
                    'jenis_resource',
                    $type
                )
                    ->where('status_ketersediaan', 'tersedia')
                    ->whereNotNull('lokasi_id')
                    ->whereHas('lokasiPangkalan', function ($query) {
                        $query->whereNotNull('latitude')
                            ->whereNotNull('longitude');
                    })
                    ->with('lokasiPangkalan')
                    ->lockForUpdate()
                    ->get();

                // Cari Resource dengan jarak terdekat.
                $nearest = $resources
                    ->map(function ($resource) use ($case) {

                        $lokasiResource = $resource->lokasiPangkalan;

                        $distance = $this->calculateDistance(
                            (float) $case->lokasi->latitude,
                            (float) $case->lokasi->longitude,
                            (float) $lokasiResource->latitude,
                            (float) $lokasiResource->longitude
                        );

                        return [
                            'resource' => $resource,
                            'distance' => $distance,
                        ];
                    })
                    ->sortBy('distance')
                    ->first();

                // Jika tidak ditemukan Resource.
                if (!$nearest) {
                    $missing[] = $type;
                    continue;
                }

                $resource = $nearest['resource'];

                // Buat Assignment otomatis.
                Assignment::create([
                    'resource_id' => $resource->resource_id,
                    'case_id' => $case->case_id,
                    'waktu_assign' => now(),
                    'status_assign' => 'diterima',
                    'waktu_konfirmasi' => now(),
                ]);

                // Tandai Resource sudah dialokasikan.
                $resource->update([
                    'status_ketersediaan' => 'sedang_ditugaskan',
                ]);

                $matched[] = [
                    'resource_id' => $resource->resource_id,
                    'jenis_resource' => $type,
                    'jarak_km' => round($nearest['distance'], 2),
                ];
            }

            // Perbarui status laporan berdasarkan hasil matching.
            if (!empty($missing)) {
                $case->update([
                    'status_laporan' => 'Menunggu Assignment',
                ]);
            } elseif ($case->status_laporan === 'Menunggu Assignment') {
                $case->update([
                    'status_laporan' => 'Menunggu',
                ]);
            }

            return [
                'success' => empty($missing),
                'needs_verification' => false,
                'matched' => $matched,
                'missing' => $missing,
            ];
        });
    }

    /**
     * Pemetaan jenis kejadian ke jenis Resource.
     */
    private function getRequiredResourceTypes(
        string $jenisKejadian
    ): ?array {
        return match (mb_strtolower(trim($jenisKejadian))) {

            'kecelakaan',
            'kecelakaan lalu lintas' => [
                'Ambulans',
                'Polisi',
            ],

            'kebakaran' => [
                'Pemadam Kebakaran',
            ],

            'bencana alam' => [
                'Ambulans',
                'Polisi',
                'Pemadam Kebakaran',
            ],

            'darurat medis' => [
                'Ambulans',
            ],

            default => null,
        };
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
