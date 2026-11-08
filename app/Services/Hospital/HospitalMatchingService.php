<?php

namespace App\Services\Hospital;

use App\Models\EmergencyCase;
use App\Models\RumahSakit;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class HospitalMatchingService
{
    /**
     * Cari RS aktif dengan kapasitas tersedia,
     * urutkan berdasarkan jarak garis lurus.
     */
    public function findCandidates(EmergencyCase $case): Collection
    {
        $case->loadMissing('lokasi');

        $lokasi = $case->lokasi;

        if (
            !$lokasi ||
            !$this->validCoordinates(
                $lokasi->latitude,
                $lokasi->longitude
            )
        ) {
            throw new InvalidArgumentException(
                'Koordinat lokasi Emergency Case tidak valid.'
            );
        }

        $latitude = (float) $lokasi->latitude;
        $longitude = (float) $lokasi->longitude;

        return RumahSakit::query()
            ->where('status', 'aktif')
            ->where('kapasitas_tersedia', '>', 0)
            ->get()
            ->filter(function (RumahSakit $rs) {
                return $this->validCoordinates(
                    $rs->latitude,
                    $rs->longitude
                );
            })
            ->map(function (RumahSakit $rs) use (
                $latitude,
                $longitude
            ) {
                return [
                    'rumah_sakit_id' => $rs->rumah_sakit_id,
                    'nama_rs' => $rs->nama_rs,
                    'alamat' => $rs->alamat,
                    'latitude' => (float) $rs->latitude,
                    'longitude' => (float) $rs->longitude,
                    'kapasitas_tersedia' => $rs->kapasitas_tersedia,
                    'distance_km' => round(
                        $this->haversine(
                            $latitude,
                            $longitude,
                            (float) $rs->latitude,
                            (float) $rs->longitude
                        ),
                        3
                    ),
                ];
            })
            ->sortBy([
                ['distance_km', 'asc'],
                ['rumah_sakit_id', 'asc'],
            ])
            ->values();
    }

    private function validCoordinates(
        mixed $latitude,
        mixed $longitude
    ): bool {
        if (
            !is_numeric($latitude) ||
            !is_numeric($longitude)
        ) {
            return false;
        }

        $lat = (float) $latitude;
        $lng = (float) $longitude;

        return is_finite($lat)
            && is_finite($lng)
            && $lat >= -90
            && $lat <= 90
            && $lng >= -180
            && $lng <= 180;
    }

    /**
     * Jarak garis lurus permukaan bumi (kilometer).
     * Bukan estimasi jarak atau waktu perjalanan jalan raya.
     */
    private function haversine(
        float $lat1,
        float $lng1,
        float $lat2,
        float $lng2
    ): float {
        $earthRadiusKm = 6371.0;

        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLng = deg2rad($lng2 - $lng1);

        $a = sin($deltaLat / 2) ** 2
            + cos(deg2rad($lat1))
            * cos(deg2rad($lat2))
            * sin($deltaLng / 2) ** 2;

        $a = min(1.0, max(0.0, $a));

        return 2 * $earthRadiusKm
            * atan2(sqrt($a), sqrt(1 - $a));
    }
}
