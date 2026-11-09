<?php

namespace App\Http\Controllers\PetugasEC;

use App\Http\Controllers\Controller;
use App\Models\EmergencyCase;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Http\Requests\UpdateEmergencyCaseRequest;
use Illuminate\Http\RedirectResponse;
use App\Services\Hospital\HospitalReferralEligibilityService;

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
