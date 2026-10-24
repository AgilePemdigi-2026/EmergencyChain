<?php

namespace App\Http\Controllers\PetugasEC;

use App\Http\Controllers\Controller;
use App\Models\EmergencyCase;
use Illuminate\Http\Request;
use Illuminate\View\View;

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

        $case = EmergencyCase::with(['pengguna', 'lokasi'])
            ->findOrFail($caseId);

        return view('petugas-ec.emergency-cases.show', [
            'case' => $case,
        ]);
    }
}
