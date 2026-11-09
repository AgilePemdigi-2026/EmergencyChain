<?php

namespace App\Http\Controllers\Pelapor;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmergencyCaseRequest;
use App\Models\EmergencyCase;
use App\Models\Lokasi;
use App\Services\Matching\ResourceMatchingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Services\Hospital\HospitalReferralEligibilityService;
use App\Services\Hospital\HospitalReferralService;
use Illuminate\Support\Facades\Log;

class EmergencyCaseController extends Controller
{
    public function create(): View
    {
        return view('pelapor.emergency-report.create');
    }

    public function store(
        StoreEmergencyCaseRequest $request
    ): RedirectResponse {
        $data = $request->validated();

        $case = DB::transaction(function () use ($data, $request) {
            $lokasi = Lokasi::create([
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'alamat' => $data['alamat'],
            ]);

            $case = EmergencyCase::create([
                'lokasi_id' => $lokasi->lokasi_id,
                'pengguna_id' => $request->user()->getAuthIdentifier(),
                'jenis_kejadian' => $data['jenis_kejadian'],
                'kondisi' => $data['kondisi_kejadian'],
                'kebutuhan' => $data['tingkat_kebutuhan'],
                'waktu_laporan' => now(),
                'status_laporan' => 'Menunggu',
            ]);

            // Resource Matching tetap berjalan otomatis.
            app(ResourceMatchingService::class)->match($case);

            return $case;
        });

        // Hospital Matching dijalankan setelah laporan tersimpan.
        try {
            $eligibility = app(
                HospitalReferralEligibilityService::class
            )->check($case);

            if (
                $eligibility ===
                HospitalReferralEligibilityService::PERLU_RS
            ) {
                app(HospitalReferralService::class)
                    ->start($case->case_id);
            }
        } catch (\Throwable $exception) {
            Log::error('Hospital Matching otomatis gagal', [
                'case_id' => $case->case_id,
                'error' => $exception->getMessage(),
            ]);
        }

        return redirect()
            ->route('pelapor.laporan.create')
            ->with(
                'success',
                'Laporan Emergency Case berhasil dibuat'
            );
    }
    
}
