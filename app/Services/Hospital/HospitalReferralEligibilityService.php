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
        // Gunakan keputusan Petugas EC jika sudah diverifikasi.
        if (
            in_array(
                $case->hospital_referral_verification,
                [
                    self::PERLU_RS,
                    self::TIDAK_PERLU_RS,
                ],
                true
            )
        ) {
            return $case->hospital_referral_verification;
        }

        // Jika belum diverifikasi, gunakan pemeriksaan otomatis.
        $kebutuhan = $this->normalize($case->kebutuhan);
        $kondisi = $this->normalize($case->kondisi);

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
