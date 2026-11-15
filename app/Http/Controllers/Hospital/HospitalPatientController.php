<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Models\InformasiKorban;
use App\Models\HospitalTreatmentHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class HospitalPatientController extends Controller
{
    /**
     * Daftar pasien yang diterima oleh rumah sakit yang login.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'rumah_sakit' || !$user->rumah_sakit_id) {
            abort(403, 'Akses hanya untuk rumah sakit.');
        }

        $patients = InformasiKorban::with([
            'emergencyCase.lokasi',
            'latestTreatmentHistory',
        ])
            ->where('rumah_sakit_id', $user->rumah_sakit_id)
            ->where('status_penerimaan', 'diterima')
            ->latest('waktu_rujukan')
            ->paginate(10);

        return view('hospital.patients.index', compact('patients'));
    }

    /**
     * Detail pasien.
     */
    public function show(Request $request, int $informasiId)
    {
        $user = $request->user();

        if ($user->role !== 'rumah_sakit' || !$user->rumah_sakit_id) {
            abort(403, 'Akses hanya untuk rumah sakit.');
        }

        $patients = InformasiKorban::with([
    'emergencyCase.lokasi',
    'latestTreatmentHistory',
])
    ->where('rumah_sakit_id', $user->rumah_sakit_id)
    ->where('status_penerimaan', 'diterima')
    ->whereHas('latestTreatmentHistory', function ($query) {
        $query->where('status', 'Dalam Penanganan');
    })
    ->latest('waktu_rujukan')
    ->paginate(10);
        return view('hospital.patients.show', compact('patient'));
    }

    /**
     * Memperbarui status penanganan pasien dan menyimpan riwayatnya.
     */
    public function updateStatus(Request $request, int $informasiId)
    {
        $user = $request->user();

        if ($user->role !== 'rumah_sakit' || !$user->rumah_sakit_id) {
            abort(403, 'Akses hanya untuk rumah sakit.');
        }

        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in([
                    'Dalam Penanganan',
                    'Pasien Sudah Ditangani',
                ]),
            ],
            'keterangan' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($user, $informasiId, $validated) {
            $patient = InformasiKorban::where(
                'rumah_sakit_id',
                $user->rumah_sakit_id
            )
                ->where('status_penerimaan', 'diterima')
                ->lockForUpdate()
                ->findOrFail($informasiId);

            $latestHistory = $patient->latestTreatmentHistory;

            if ($latestHistory && $latestHistory->status === $validated['status']) {
                return;
            }

            $patient->treatmentHistories()->create([
                'status' => $validated['status'],
                'keterangan' => $validated['keterangan'] ?? null,
                'diubah_oleh' => $user->pengguna_id,
            ]);
        });

        return redirect()
            ->route('hospital.patients.show', $informasiId)
            ->with('success', 'Status penanganan pasien berhasil diperbarui.');
    }

    /**
 * Menampilkan riwayat pasien yang sudah selesai ditangani.
 */
public function history(Request $request)
{
    $user = $request->user();

    if ($user->role !== 'rumah_sakit' || !$user->rumah_sakit_id) {
        abort(403, 'Akses hanya untuk rumah sakit.');
    }

    $histories = HospitalTreatmentHistory::with([
    'informasiKorban.emergencyCase',
    'petugas',
])
    ->where('status', 'Pasien Sudah Ditangani')
    ->whereHas('informasiKorban', function ($query) use ($user) {
        $query->where('rumah_sakit_id', $user->rumah_sakit_id)
            ->where('status_penerimaan', 'diterima');
    })
    ->whereIn('id', function ($query) {
        $query->selectRaw('MAX(id)')
            ->from('hospital_treatment_histories')
            ->groupBy('informasi_id');
    })
    ->latest('created_at')
    ->paginate(10);
    return view('hospital.patients.history', compact('histories'));
}

}
