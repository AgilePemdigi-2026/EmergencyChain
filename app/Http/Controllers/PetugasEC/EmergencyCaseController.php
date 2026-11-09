<?php

namespace App\Http\Controllers\PetugasEC;

use App\Http\Controllers\Controller;
use App\Models\EmergencyCase;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Http\Requests\UpdateEmergencyCaseRequest;
use Illuminate\Http\RedirectResponse;
use App\Services\Hospital\HospitalReferralEligibilityService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Services\Hospital\HospitalReferralService;

class EmergencyCaseController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->role === 'petugas_ec', 403);

        $cases = EmergencyCase::with(['pengguna', 'lokasi'])
            ->orderByDesc('waktu_laporan')
            ->get();

        return view('petugas-ec.emergency-cases.index', [
            'cases' => $cases,
        ]);
    }

    public function show(Request $request, int $caseId): View
    {
        abort_unless($request->user()?->role === 'petugas_ec', 403);

        $case = EmergencyCase::with([
            'pengguna',
            'lokasi',
            'assignments.resource',
            'hospitalReferral.acceptedHospital',
            'hospitalReferral.requests.rumahSakit',
        ])->findOrFail($caseId);

        $eligibility = app(
            HospitalReferralEligibilityService::class
        )->check($case);

        $referral = $case->hospitalReferral;

        $requests = $referral
            ? $referral->requests->sortBy('attempt_order')
            : collect();

        return view('petugas-ec.emergency-cases.show', [
            'case' => $case,
            'referral' => $referral,
            'requests' => $requests,
            'eligibility' => $eligibility,
        ]);
    }

    public function hospitalMatchingPlaceholder(
        Request $request,
        int $caseId
    ): View {
        abort_unless($request->user()?->role === 'petugas_ec', 403);

        $case = EmergencyCase::with([
            'pengguna',
            'lokasi',
            'hospitalReferral.acceptedHospital',
            'hospitalReferral.requests.rumahSakit',
        ])->findOrFail($caseId);

        $eligibility = app(
            HospitalReferralEligibilityService::class
        )->check($case);

        $referral = $case->hospitalReferral;

        $requests = $referral
            ? $referral->requests->sortBy('attempt_order')
            : collect();

        return view(
            'petugas-ec.emergency-cases.hospital-matching',
            compact('case', 'referral', 'requests', 'eligibility')
        );
    }

    public function verifyHospitalReferral(
        Request $request,
        int $caseId
    ): RedirectResponse {
        abort_unless($request->user()?->role === 'petugas_ec', 403);

        $validated = $request->validate([
            'decision' => [
                'required',
                'in:perlu_rs,tidak_perlu_rs',
            ],
        ]);

        $decision = $validated['decision'];

        $case = DB::transaction(function () use (
            $caseId,
            $decision,
            $request
        ) {
            $case = EmergencyCase::query()
                ->where('case_id', $caseId)
                ->lockForUpdate()
                ->firstOrFail();

            // Verifikasi hanya berlaku untuk kasus yang ambigu.
            $eligibility = app(
                HospitalReferralEligibilityService::class
            )->check($case);

            if (
                $eligibility !==
                HospitalReferralEligibilityService::PERLU_VERIFIKASI
            ) {
                throw ValidationException::withMessages([
                    'decision' => 'Kasus tidak sedang menunggu verifikasi.',
                ]);
            }

            // Pastikan referral belum pernah dibuat.
            if ($case->hospitalReferral()->exists()) {
                throw ValidationException::withMessages([
                    'decision' => 'Hospital Referral sudah pernah dibuat.',
                ]);
            }

            $case->update([
                'hospital_referral_verification' => $decision,
                'hospital_referral_verified_by' =>
                    $request->user()->getAuthIdentifier(),
                'hospital_referral_verified_at' => now(),
            ]);

            return $case;
        });

        // Jalankan Hospital Matching hanya jika membutuhkan RS.
        if (
            $decision ===
            HospitalReferralEligibilityService::PERLU_RS
        ) {
            app(HospitalReferralService::class)
                ->start($case->case_id);

            return redirect()
                ->route('petugas.ec.cases.hospital-matching', [
                    'caseId' => $case->case_id,
                ])
                ->with(
                    'success',
                    'Verifikasi berhasil. Hospital Matching telah dijalankan.'
                );
        }

        return redirect()
            ->route('petugas.ec.cases.hospital-matching', [
                'caseId' => $case->case_id,
            ])
            ->with(
                'success',
                'Kasus telah diverifikasi dan tidak memerlukan rujukan Rumah Sakit.'
            );
    }


    public function update(
        UpdateEmergencyCaseRequest $request,
        int $caseId
    ): RedirectResponse {
        $case = EmergencyCase::findOrFail($caseId);

        $validated = $request->validated();

        $case->update([
            'kondisi' => $validated['kondisi'],
            'kebutuhan' => $validated['kebutuhan'],
        ]);

        return redirect()
            ->route('petugas.ec.cases.show', [
                'caseId' => $case->case_id,
            ])
            ->with(
                'success',
                'Emergency Case berhasil diperbarui'
            );
    }
}
