<?php

namespace App\Services\Hospital;

use App\Models\EmergencyCase;

class HospitalReferralEligibilityService
{
    public const PERLU_RS = 'perlu_rs';
    public const TIDAK_PERLU_RS = 'tidak_perlu_rs';
    public const PERLU_VERIFIKASI = 'perlu_verifikasi';

    public function check(EmergencyCase $case): string
    {
        $kebutuhan = $this->normalize($case->kebutuhan);
        $kondisi = $this->normalize($case->kondisi);

        // Informasi yang bertentangan atau tidak pasti
        // tidak boleh diputuskan otomatis.
        $teks = trim($kebutuhan . ' ' . $kondisi);

        if ($teks === '') {
            return self::PERLU_VERIFIKASI;
        }

        $indikasiMedis = [
            'butuh rumah sakit',
            'butuh rs',
            'perlu rumah sakit',
            'perlu dirujuk',
            'membutuhkan rujukan',
            'butuh ambulans',
            'butuh ambulance',
            'membutuhkan ambulans',
            'luka berat',
            'luka bakar',
            'tidak sadarkan diri',
            'pingsan',
            'pendarahan hebat',
            'sesak napas',
        ];

        // Indikasi negatif dari laporan teks bebas
        // tetap perlu ditinjau jika ada kemungkinan konflik.
        $penyangkalan = [
            'tidak butuh rumah sakit',
            'tidak perlu rumah sakit',
            'tidak butuh rs',
            'tidak perlu dirujuk',
            'tidak membutuhkan rujukan',
            'tidak ada korban',
            'tidak ada yang terluka',
            'tidak luka berat',
            'tidak pingsan',
        ];

        foreach ($penyangkalan as $frasa) {
            if (str_contains($teks, $frasa)) {
                return self::PERLU_VERIFIKASI;
            }
        }

        foreach ($indikasiMedis as $frasa) {
            if (str_contains($teks, $frasa)) {
                return self::PERLU_RS;
            }
        }

        // Tidak menemukan kata medis bukan berarti
        // korban pasti tidak membutuhkan Rumah Sakit.
        return self::PERLU_VERIFIKASI;
    }

    private function normalize(?string $value): string
    {
        $value = mb_strtolower($value ?? '');

        return trim(
            preg_replace('/\s+/u', ' ', $value)
        );
    }
}
