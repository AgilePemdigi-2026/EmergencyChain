<?php

namespace App\Http\Controllers\PetugasEC;

use App\Http\Controllers\Controller;
use App\Models\EmergencyCase;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(
            $request->user()?->role === 'petugas_ec',
            403
        );

        // Mengambil Emergency Case yang belum selesai.
        $cases = EmergencyCase::with([
            'lokasi',
            'assignments.resource',
        ])
            ->where('status_laporan', '!=', 'Selesai')
            ->orderByDesc('waktu_laporan')
            ->get();

        // KPI Dashboard berdasarkan database.
        $totalCases = EmergencyCase::count();

        $activeCasesCount = EmergencyCase::where(
            'status_laporan',
            '!=',
            'Selesai'
        )->count();

        $verificationCount = EmergencyCase::where(
            'status_laporan',
            'Perlu Verifikasi'
        )->count();

        $waitingAssignmentCount = EmergencyCase::where(
            'status_laporan',
            'Menunggu Assignment'
        )->count();

        $handlingCount = EmergencyCase::where(
            'status_laporan',
            'Dalam Penanganan'
        )->count();

        $completedCount = EmergencyCase::where(
            'status_laporan',
            'Selesai'
        )->count();

        // Menyiapkan koordinat Emergency Case untuk peta.
        $mapCases = $cases
            ->filter(function ($case) {
                return is_numeric($case->lokasi?->latitude)
                    && is_numeric($case->lokasi?->longitude);
            })
            ->map(function ($case) {
                return [
                    'case_id' => $case->case_id,
                    'jenis_kejadian' => $case->jenis_kejadian,
                    'status' => $case->status_laporan,
                    'latitude' => (float) $case->lokasi->latitude,
                    'longitude' => (float) $case->lokasi->longitude,
                    'url' => route('petugas.ec.cases.show', [
                        'caseId' => $case->case_id,
                    ]),
                ];
            })
            ->values();

        return view('petugas-ec.dashboard.index', [
            'cases' => $cases,
            'totalCases' => $totalCases,
            'activeCasesCount' => $activeCasesCount,
            'verificationCount' => $verificationCount,
            'waitingAssignmentCount' => $waitingAssignmentCount,
            'handlingCount' => $handlingCount,
            'completedCount' => $completedCount,
            'mapCases' => $mapCases,
        ]);
    }
}
