<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Models\HospitalReferralRequest;
use App\Services\Hospital\HospitalReferralService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\EmergencyCase;

class HospitalReferralController extends Controller
{public function index(Request $request): View{
    $user = $request->user();

    abort_unless(
        $user && $user->role === 'rumah_sakit'
            && $user->rumah_sakit_id,
        403
    );

    $query = HospitalReferralRequest::query()
        ->where('rumah_sakit_id', $user->rumah_sakit_id);

    $stats = [
        'incoming' => (clone $query)->count(),

        'waiting' => (clone $query)
            ->where('status', 'menunggu')
            ->where('expires_at', '>', now())
            ->count(),

        'accepted_today' => (clone $query)
            ->where('status', 'diterima')
            ->whereDate('responded_at', today())
            ->count(),

        'rejected' => (clone $query)
            ->where('status', 'ditolak')
            ->count(),
    ];

    $requests = (clone $query)
        ->with('referral')
        ->orderByDesc('requested_at')
        ->paginate(10);

    return view('hospital.referrals.index', [
        'requests' => $requests,
        'stats' => $stats,
    ]);
}


    public function start(
        int $caseId,
        Request $request,
        HospitalReferralService $service
    ): RedirectResponse {
        $user = $request->user();

        // Sementara hanya Petugas EC yang boleh memulai.
        abort_unless(
            $user && $user->role === 'petugas_ec',
            403
        );

        $service->start($caseId);

        return back()->with(
            'success',
            'Proses rujukan Rumah Sakit berhasil dimulai.'
        );
    }

    public function respond(
        Request $request,
        int $requestId,
        HospitalReferralService $service
    ): RedirectResponse {
        $user = $request->user();

        abort_unless(
            $user && $user->role === 'rumah_sakit',
            403
        );

        $validated = $request->validate([
            'decision' => 'required|in:diterima,ditolak',
            'reason' => 'nullable|string|max:1000',
        ]);

        $service->respond(
            $requestId,
            $user,
            $validated['decision'],
            $validated['reason'] ?? null
        );

        return redirect()
            ->route('hospital.referrals.index')
            ->with(
                'success',
                'Respons rujukan berhasil disimpan.'
            );
    }
    public function show(Request $request, int $requestId): View
{
    $user = $request->user();

    abort_unless(
        $user && $user->role === 'rumah_sakit'
            && $user->rumah_sakit_id,
        403
    );

    $referralRequest = HospitalReferralRequest::query()
        ->with('referral')
        ->where('rumah_sakit_id', $user->rumah_sakit_id)
        ->findOrFail($requestId);

    $case = EmergencyCase::query()
        ->with('lokasi')
        ->where('case_id', $referralRequest->referral->case_id)
        ->firstOrFail();

    return view('hospital.referrals.show', [
        'referralRequest' => $referralRequest,
        'case' => $case,
    ]);
}
}
