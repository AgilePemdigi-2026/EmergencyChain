<?php

namespace App\Http\Controllers\Pelapor;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmergencyCaseRequest;
use App\Models\EmergencyCase;
use App\Models\Lokasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

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

        DB::transaction(function () use ($data, $request) {
            $lokasi = Lokasi::create([
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'alamat' => $data['alamat'],
            ]);

            EmergencyCase::create([
                'lokasi_id' => $lokasi->lokasi_id,
                'pengguna_id' => $request->user()->getAuthIdentifier(),
                'jenis_kejadian' => $data['jenis_kejadian'],
                'kondisi' => $data['kondisi_kejadian'],
                'kebutuhan' => $data['tingkat_kebutuhan'],
                'waktu_laporan' => now(),
                'status_laporan' => 'Menunggu',
            ]);
        });

        return redirect()
            ->route('pelapor.laporan.create')
            ->with(
                'success',
                'Laporan Emergency Case berhasil dibuat'
            );
    }
}
