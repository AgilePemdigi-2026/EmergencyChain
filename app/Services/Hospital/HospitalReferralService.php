<?php

namespace App\Services\Hospital;

use App\Models\EmergencyCase;
use App\Models\HospitalReferral;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\HospitalReferralRequest;
use App\Models\Pengguna;
use App\Jobs\ProcessHospitalReferralTimeout;
use App\Models\InformasiKorban;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Facades\Log;
use Throwable;

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

                $this->notifyReferralFailed($referral);
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

// Kirim notifikasi kepada RS pertama.
$this->notifyHospitalNewRequest(
    $request,
    (int) $caseId
);


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

    // Buat data pasien dari rujukan yang diterima.
    $informasiKorban = InformasiKorban::firstOrCreate(
        [
            'case_id' => $referral->case_id,
            'rumah_sakit_id' => $user->rumah_sakit_id,
        ],
        [
            'kondisi_korban' =>
                'Belum diperiksa oleh petugas rumah sakit',
            'waktu_rujukan' => now(),
            'status_penerimaan' => 'diterima',
        ]
    );

    // Buat riwayat awal jika belum tersedia.
    if (!$informasiKorban->treatmentHistories()->exists()) {
        $informasiKorban->treatmentHistories()->create([
            'status' => 'Dalam Penanganan',
            'keterangan' => 'Rujukan diterima oleh rumah sakit.',
            'diubah_oleh' => $user->pengguna_id,
        ]);
    }
} else {
    // Jika ditolak, cari kandidat berikutnya.
    $this->sendNextRequest($referral);
}

$this->notifyReferralResponse(
    $referral,
    $request,
    $decision
);

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

        $this->notifyReferralFailed($referral);
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
    ]);

$this->scheduleTimeout($request);

// Beri tahu RS berikutnya tentang permintaan baru.
$this->notifyHospitalNewRequest(
    $request,
    (int) $referral->case_id
);
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
 * Beri tahu RS setelah permintaan rujukan
 * berhasil disimpan ke database.
 */
private function notifyHospitalNewRequest(
    HospitalReferralRequest $request,
    int $caseId
): void {
    $hospitalId = (int) $request->rumah_sakit_id;
    $requestId = (int) $request->id;

    DB::afterCommit(function () use (
        $hospitalId,
        $requestId,
        $caseId
    ) {
        app(NotificationService::class)->sendToRumahSakit(
            $hospitalId,
            'Permintaan Rujukan Baru',
            "Terdapat permintaan rujukan baru untuk Emergency Case #{$caseId}. Silakan periksa dan berikan respons.",
            route('hospital.referrals.show', [
                'requestId' => $requestId,
                ], false),
            'hospital_referral'
        );
    });
}


/**
 * Notifikasi respons penerimaan atau penolakan rujukan.
 */
private function notifyReferralResponse(
    HospitalReferral $referral,
    HospitalReferralRequest $request,
    string $decision
): void {
    $caseId = (int) $referral->case_id;
    $requestId = (int) $request->id;
    $hospitalId = (int) $request->rumah_sakit_id;
    $referralStatus = $referral->status;

    $penggunaId = (int) EmergencyCase::query()
        ->where('case_id', $caseId)
        ->value('pengguna_id');

    DB::afterCommit(function () use (
        $caseId,
        $requestId,
        $hospitalId,
        $penggunaId,
        $decision,
        $referralStatus
    ) {
        $notifications = app(NotificationService::class);

        $accepted = $decision === 'diterima';

        $title = $accepted
            ? 'Rujukan Rumah Sakit Diterima'
            : 'Rujukan Rumah Sakit Ditolak';

        $message = $accepted
            ? "Rumah Sakit #{$hospitalId} telah menerima rujukan EC-{$caseId}."
            : "Rumah Sakit #{$hospitalId} menolak rujukan EC-{$caseId}. Status proses rujukan: {$referralStatus}.";

        try {
            $notifications->sendToRole(
                'petugas_ec',
                $title,
                $message,
                route('petugas.ec.cases.hospital-matching', [
                    'caseId' => $caseId,
                ]),
                'hospital_referral'
            );
        } catch (Throwable $exception) {
            Log::error('Notifikasi respons RS ke Petugas EC gagal.', [
                'request_id' => $requestId,
                'error' => $exception->getMessage(),
            ]);
        }

        // Pelapor menerima notifikasi jika rujukan diterima
        // atau jika penolakan masih diikuti pencarian RS lain.
        if ($accepted || $referralStatus === 'mencari') {
            $pelaporTitle = $accepted
                ? 'Rujukan Korban Diterima'
                : 'Pencarian Rumah Sakit Dilanjutkan';

            $pelaporMessage = $accepted
                ? "Rujukan korban untuk EC-{$caseId} telah diterima Rumah Sakit."
                : "Rumah Sakit sebelumnya belum dapat menerima rujukan EC-{$caseId}. Sistem sedang mencari Rumah Sakit lain.";

            try {
                $notifications->sendToPelapor(
                    $penggunaId,
                    $pelaporTitle,
                    $pelaporMessage,
                    route('pelapor.laporan.show', [
                        'caseId' => $caseId,
                    ]),
                    'hospital_referral'
                );
            } catch (Throwable $exception) {
                Log::error('Notifikasi respons RS ke Pelapor gagal.', [
                    'request_id' => $requestId,
                    'error' => $exception->getMessage(),
                ]);
            }
        }
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



/**
 * Notifikasi ketika seluruh kandidat Rumah Sakit habis.
 */
private function notifyReferralFailed(
    HospitalReferral $referral
): void {
    $caseId = (int) $referral->case_id;

    $penggunaId = (int) EmergencyCase::query()
        ->where('case_id', $caseId)
        ->value('pengguna_id');

    DB::afterCommit(function () use (
        $caseId,
        $penggunaId
    ) {
        $notifications = app(NotificationService::class);

        try {
            $notifications->sendToRole(
                'petugas_ec',
                'Rujukan Rumah Sakit Gagal',
                "Rujukan EC-{$caseId} belum berhasil karena tidak ada kandidat Rumah Sakit yang dapat menerima.",
                route('petugas.ec.cases.hospital-matching', [
                    'caseId' => $caseId,
                ]),
                'hospital_referral'
            );
        } catch (Throwable $exception) {
            Log::error('Notifikasi kegagalan RS ke Petugas EC gagal.', [
                'case_id' => $caseId,
                'error' => $exception->getMessage(),
            ]);
        }

        try {
            $notifications->sendToPelapor(
                $penggunaId,
                'Rujukan Rumah Sakit Belum Berhasil',
                "Rujukan korban untuk EC-{$caseId} belum mendapatkan Rumah Sakit penerima.",
                route('pelapor.laporan.show', [
                    'caseId' => $caseId,
                ]),
                'hospital_referral'
            );
        } catch (Throwable $exception) {
            Log::error('Notifikasi kegagalan RS ke Pelapor gagal.', [
                'case_id' => $caseId,
                'error' => $exception->getMessage(),
            ]);
        }
    });
}
}
