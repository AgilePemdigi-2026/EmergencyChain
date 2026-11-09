<?php

namespace App\Http\Controllers\Pelapor;

use App\Http\Controllers\Controller;
use App\Models\EmergencyCase;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportStatusController extends Controller
{
    public function index(Request $request): View
    {
        $penggunaId = $request->user()->getAuthIdentifier();

        $search = trim((string) $request->query('search', ''));
        $status = trim((string) $request->query('status', ''));

        // Selalu batasi data berdasarkan Pelapor yang login.
        $query = EmergencyCase::query()
            ->with('lokasi')
            ->where('pengguna_id', $penggunaId);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                // Mendukung pencarian ID seperti EC-2026-0007.
                if (preg_match('/^EC-\d{4}-(\d+)$/i', $search, $matches)) {
                    $q->where('case_id', (int) $matches[1]);
                } elseif (ctype_digit($search)) {
                    $q->where('case_id', (int) $search);
                } else {
                    // ILIKE digunakan karena project memakai PostgreSQL.
                    $q->where('jenis_kejadian', 'ILIKE', "%{$search}%");
                }
            });
        }

        if ($status !== '') {
            $query->where('status_laporan', $status);
        }

        // Ambil pilihan status dari data milik Pelapor sendiri.
        $statuses = EmergencyCase::query()
            ->where('pengguna_id', $penggunaId)
            ->whereNotNull('status_laporan')
            ->distinct()
            ->orderBy('status_laporan')
            ->pluck('status_laporan');

        $cases = $query
            ->orderByDesc('waktu_laporan')
            ->orderByDesc('case_id')
            ->paginate(10)
            ->withQueryString();

        return view('pelapor.emergency-report.index', compact(
            'cases',
            'statuses',
            'search',
            'status'
        ));
    }
    public function show(Request $request, int $caseId): View
    {
        $case = EmergencyCase::query()
            ->with(['lokasi', 'hospitalReferral.acceptedHospital'])
            ->where('pengguna_id', $request->user()->getAuthIdentifier())
            ->where('case_id', $caseId)
            ->firstOrFail();

        return view('pelapor.emergency-report.show', compact('case'));
    }

}
