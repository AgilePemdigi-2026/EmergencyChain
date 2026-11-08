<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Models\HospitalReferralRequest;
use App\Services\Hospital\HospitalReferralService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HospitalReferralController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless(
            $user && $user->role === 'rumah_sakit'
                && $user->rumah_sakit_id,
            403
        );

        $requests = HospitalReferralRequest::query()
            ->where('rumah_sakit_id', $user->rumah_sakit_id)
            ->orderByDesc('requested_at')
            ->paginate(10);

        return view('hospital.referrals.index', [
            'requests' => $requests,
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
}
