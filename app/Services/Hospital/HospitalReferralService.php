<?php

namespace App\Services\Hospital;

use App\Models\EmergencyCase;
use App\Models\HospitalReferral;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\HospitalReferralRequest;
use App\Models\Pengguna;
use App\Jobs\ProcessHospitalReferralTimeout;

class HospitalReferralService
{
    public const RESPONSE_TIMEOUT_SECONDS = 100;

    public function __construct(
        private HospitalMatchingService $matchingService,
        private HospitalReferralEligibilityService $eligibilityService
    ) {}

    /**
     * Mulai proses rujukan untuk satu Emergency Case.
     *
     * Aman dipanggil berulang: proses yang sudah ada
     * tidak akan dibuat ulang.
     */
    public function start(int $caseId): HospitalReferral
    {
        return DB::transaction(function () use ($caseId) {

            // Kunci Emergency Case agar dua proses bersamaan
            // tidak membuat referral ganda.
            $case = EmergencyCase::query()
                ->where('case_id', $caseId)
                ->lockForUpdate()
                ->firstOrFail();

            // Jika referral sudah pernah dibuat,
            // gunakan proses yang sama.
            $existingReferral = HospitalReferral::query()
                ->where('case_id', $caseId)
                ->first();

            if ($existingReferral) {
                return $existingReferral->load('requests');
            }

            // Periksa kebutuhan rujukan.
            $eligibility = $this->eligibilityService->check($case);

            if (
                $eligibility !==
                HospitalReferralEligibilityService::PERLU_RS
            ) {
                throw ValidationException::withMessages([
                    'referral' => 'Kebutuhan rujukan belum terkonfirmasi. '
                        . 'Diperlukan verifikasi Petugas EC.',
                ]);
            }

            // Cari kandidat Rumah Sakit.
            $candidates = $this->matchingService
                ->findCandidates($case);

            // Buat proses rujukan.
            $referral = HospitalReferral::create([
                'case_id' => $caseId,
                'status' => 'mencari',
                'accepted_hospital_id' => null,
                'failure_reason' => null,
            ]);

            // Tidak ada RS yang memenuhi kriteria.
            if ($candidates->isEmpty()) {
                $referral->update([
                    'status' => 'gagal',
                    'failure_reason' => 'Tidak ada Rumah Sakit '
                        . 'dengan kapasitas dan koordinat valid.',
                ]);

                return $referral;
            }

            
            // Kandidat pertama = RS terdekat.
            $firstHospital = $candidates->first();

            $requestedAt = now();

            $request = $referral->requests()->create([
                'rumah_sakit_id' => $firstHospital['rumah_sakit_id'],
                'attempt_order' => 1,
                'status' => 'menunggu',
                'requested_at' => $requestedAt,
                'expires_at' => $requestedAt->copy()->addSeconds(
                    self::RESPONSE_TIMEOUT_SECONDS
                ),
                'responded_at' => null,
                'rejection_reason' => null,
            ]);

            // Jadwalkan timeout permintaan pertama.
            $this->scheduleTimeout($request);


            return $referral->load('requests');
        });
    }

    
/**
 * Rumah Sakit menerima atau menolak permintaan.
 */
public function respond(
    int $requestId,
    Pengguna $user,
    string $decision,
    ?string $reason = null
): HospitalReferral {
    if (!in_array($decision, ['diterima', 'ditolak'], true)) {
        throw ValidationException::withMessages([
            'status' => 'Respons Rumah Sakit tidak valid.',
        ]);
    }

    if ($user->role !== 'rumah_sakit' || !$user->rumah_sakit_id) {
        abort(403, 'Akun tidak memiliki akses Rumah Sakit.');
    }

    return DB::transaction(function () use (
        $requestId,
        $user,
        $decision,
        $reason
    ) {
        // Temukan induk referral terlebih dahulu.
        $requestData = HospitalReferralRequest::query()
            ->findOrFail($requestId);

        // Kunci referral agar tidak ada dua respons bersamaan.
        $referral = HospitalReferral::query()
            ->whereKey($requestData->hospital_referral_id)
            ->lockForUpdate()
            ->firstOrFail();

        $request = HospitalReferralRequest::query()
            ->whereKey($requestId)
            ->lockForUpdate()
            ->firstOrFail();

        // Validasi kepemilikan RS di backend.
        if ($request->rumah_sakit_id != $user->rumah_sakit_id) {
            abort(403, 'Permintaan bukan milik Rumah Sakit Anda.');
        }

        if (
            $referral->status !== 'mencari' ||
            $request->status !== 'menunggu'
        ) {
            throw ValidationException::withMessages([
                'referral' => 'Permintaan sudah tidak aktif.',
            ]);
        }

        if ($request->expires_at->isPast()) {
            throw ValidationException::withMessages([
                'referral' => 'Batas waktu respons telah habis.',
            ]);
        }

        $request->update([
            'status' => $decision,
            'responded_at' => now(),
            'rejection_reason' => $decision === 'ditolak'
                ? $reason
                : null,
        ]);

        if ($decision === 'diterima') {
            $referral->update([
                'status' => 'diterima',
                'accepted_hospital_id' => $user->rumah_sakit_id,
                'failure_reason' => null,
            ]);
        } else {
            // Jika ditolak, cari kandidat berikutnya.
            $this->sendNextRequest($referral);
        }

        return $referral->fresh(['requests', 'acceptedHospital']);
    });
}

/**
 * Buat permintaan untuk RS berikutnya yang belum dicoba.
 *
 * Dipanggil saat penolakan, dan nantinya juga oleh Job timeout.
 * Method ini mengasumsikan referral sudah dikunci oleh pemanggil.
 */
private function sendNextRequest(HospitalReferral $referral): void
{
    $case = EmergencyCase::query()
        ->with('lokasi')
        ->findOrFail($referral->case_id);

    $candidates = $this->matchingService
        ->findCandidates($case);

    $triedHospitalIds = $referral->requests()
        ->pluck('rumah_sakit_id')
        ->all();

    $nextHospital = $candidates->first(
        fn ($candidate) => !in_array(
            $candidate['rumah_sakit_id'],
            $triedHospitalIds
        )
    );

    if (!$nextHospital) {
        $referral->update([
            'status' => 'gagal',
            'failure_reason' =>
                'Tidak ada kandidat Rumah Sakit berikutnya yang tersedia.',
        ]);

        return;
    }

    $nextOrder = $referral->requests()
        ->max('attempt_order') + 1;

    $requestedAt = now();

    $request = $referral->requests()->create([
        'rumah_sakit_id' => $nextHospital['rumah_sakit_id'],
        'attempt_order' => $nextOrder,
        'status' => 'menunggu',
        'requested_at' => $requestedAt,
        'expires_at' => $requestedAt->copy()->addSeconds(
            self::RESPONSE_TIMEOUT_SECONDS
        ),
        'responded_at' => null,
        'rejection_reason' => null,
    ]);$this->scheduleTimeout($request);
}


/**
 * Proses timeout permintaan RS secara aman.
 */
public function processTimeout(int $requestId): void
{
    DB::transaction(function () use ($requestId) {
        $requestData = HospitalReferralRequest::query()
            ->find($requestId);

        if (!$requestData) {
            return;
        }

        // Selalu kunci referral lebih dulu,
        // konsisten dengan respond().
        $referral = HospitalReferral::query()
            ->whereKey($requestData->hospital_referral_id)
            ->lockForUpdate()
            ->first();

        if (!$referral) {
            return;
        }

        $request = HospitalReferralRequest::query()
            ->whereKey($requestId)
            ->lockForUpdate()
            ->first();

        if (
            !$request ||
            $referral->status !== 'mencari' ||
            $request->status !== 'menunggu'
        ) {
            return;
        }

        // Job yang berjalan terlalu awal tidak
        // boleh langsung menganggap timeout.
        if (now()->lt($request->expires_at)) {
            ProcessHospitalReferralTimeout::dispatch(
                $request->id
            )
                ->delay($request->expires_at)
                ->afterCommit();

            return;
        }

        $request->update([
            'status' => 'timeout',
            'responded_at' => now(),
        ]);

        // Cari RS berikutnya setelah timeout.
        $this->sendNextRequest($referral);
    });
}

/**
 * Jadwalkan timeout 100 detik setelah permintaan dibuat.
 */
private function scheduleTimeout(
    HospitalReferralRequest $request
): void {
    ProcessHospitalReferralTimeout::dispatch(
        $request->id
    )
        ->delay($request->expires_at)
        ->afterCommit();
}

}
