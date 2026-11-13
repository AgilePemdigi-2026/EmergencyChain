<?php

namespace App\Http\Controllers\Instansi;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Resource;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HistoryController extends Controller
{
    /**
     * Query Assignment selesai milik Instansi login.
     */
    private function completedQuery(Request $request): Builder
    {
        $instansiId = $request->user()->instansi_id;

        return Assignment::query()
            ->where('status_assign', 'selesai')
            ->whereHas('resource', function ($query) use ($instansiId) {
                $query->where('instansi_id', $instansiId);
            });
    }

    /**
     * Daftar Riwayat Penanganan Instansi.
     */
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'bulan' => ['nullable', 'date_format:Y-m'],
        ]);

        $baseQuery = $this->completedQuery($request);

        // Ringkasan seluruh Assignment selesai.
        $totalSelesai = (clone $baseQuery)->count();

        $totalKasus = (clone $baseQuery)
            ->distinct()
            ->count('case_id');

        $totalUnit = (clone $baseQuery)
            ->distinct()
            ->count('resource_id');

        $bulanIni = now()->format('Y-m');

        $awalBulanIni = Carbon::createFromFormat(
            '!Y-m',
            $bulanIni
        )->startOfMonth();

        $akhirBulanIni = $awalBulanIni->copy()->addMonth();

        $selesaiBulanIni = (clone $baseQuery)
            ->whereHas('statusPenanganan', function ($query) use (
                $awalBulanIni,
                $akhirBulanIni
            ) {
                $query->where('status', 'selesai')
                    ->where('waktu_update', '>=', $awalBulanIni)
                    ->where('waktu_update', '<', $akhirBulanIni);
            })
            ->count();

        $summary = [
            'total' => $totalSelesai,
            'kasus' => $totalKasus,
            'unit' => $totalUnit,
            'bulan_ini' => $selesaiBulanIni,
        ];

        // Query daftar riwayat.
        $query = clone $baseQuery;

        // Filter Case ID, jenis kejadian, dan lokasi.
        if (!empty($validated['search'])) {
            $search = trim($validated['search']);

            $query->where(function ($builder) use ($search) {
                $builder->whereRaw(
                    'CAST(case_id AS TEXT) ILIKE ?',
                    ["%{$search}%"]
                )->orWhereHas('emergencyCase', function ($caseQuery) use ($search) {
                    $caseQuery
                        ->where('jenis_kejadian', 'ilike', "%{$search}%")
                        ->orWhereHas('lokasi', function ($lokasiQuery) use ($search) {
                            $lokasiQuery->where(
                                'alamat',
                                'ilike',
                                "%{$search}%"
                            );
                        });
                });
            });
        }

        // Filter bulan berdasarkan waktu selesai.
        if (!empty($validated['bulan'])) {
            $awalBulan = Carbon::createFromFormat(
                '!Y-m',
                $validated['bulan']
            )->startOfMonth();

            $akhirBulan = $awalBulan->copy()->addMonth();

            $query->whereHas('statusPenanganan', function ($builder) use (
                $awalBulan,
                $akhirBulan
            ) {
                $builder->where('status', 'selesai')
                    ->where('waktu_update', '>=', $awalBulan)
                    ->where('waktu_update', '<', $akhirBulan);
            });
        }

        $assignments = $query
            ->with([
                'resource',
                'emergencyCase.lokasi',
                'statusPenanganan' => function ($builder) {
                    $builder->where('status', 'selesai')
                        ->orderByDesc('waktu_update')
                        ->orderByDesc('status_id');
                },
            ])
            ->orderByDesc('waktu_assign')
            ->orderByDesc('assignment_id')
            ->paginate(10)
            ->withQueryString();

        return view('instansi.history.index', compact(
            'assignments',
            'summary'
        ));
    }

    /**
     * Detail Riwayat Assignment selesai.
     */
    public function show(
        Request $request,
        int $assignmentId
    ): View {
        $assignment = $this->completedQuery($request)
            ->with([
                'resource.lokasiPangkalan',
                'emergencyCase.lokasi',
                'statusPenanganan' => function ($builder) {
                    $builder->orderBy('waktu_update')
                        ->orderBy('status_id');
                },
            ])
            ->where('assignment_id', $assignmentId)
            ->firstOrFail();

        return view(
            'instansi.history.show',
            compact('assignment')
        );
    }
}
