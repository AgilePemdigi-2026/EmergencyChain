<?php

namespace Database\Seeders;

use App\Models\Instansi;
use App\Models\Resource;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DamkarResourceSeeder extends Seeder
{
    public function run(): void
    {
        $instansi = Instansi::query()
            ->where(
                'nama_instansi',
                'Dinas Pemadam Kebakaran dan Penyelamatan Kota Surabaya'
            )
            ->firstOrFail();

        if (!$instansi->lokasi_id) {
            throw new \RuntimeException(
                'Lokasi pangkalan Damkar belum tersedia.'
            );
        }

        $units = [
            ['nama' => 'Mobil Water Supply 5000 - Pasar Turi', 'kapasitas' => 2],
            ['nama' => 'Mobil Tangga 30m - Pasar Turi', 'kapasitas' => 4],
            ['nama' => 'Mobil Pumper Rayon 1', 'kapasitas' => 6],
            ['nama' => 'Mobil Water Mist - Pasar Turi', 'kapasitas' => 3],
            ['nama' => 'Mobil Kebakaran 5000 - Pasar Turi', 'kapasitas' => 6],
            ['nama' => 'Mobil Water Supply System 3000 - 01', 'kapasitas' => 2],
            ['nama' => 'Mobil Water Supply System 3000 - 02', 'kapasitas' => 2],
            ['nama' => 'Mobil Kebakaran 10000 - Pasar Turi', 'kapasitas' => 6],
        ];

        DB::transaction(function () use ($instansi, $units) {
            foreach ($units as $unit) {
                Resource::query()->firstOrCreate(
                    [
                        'instansi_id' => $instansi->instansi_id,
                        'nama_resource' => $unit['nama'],
                    ],
                    [
                        'jenis_resource' => 'Pemadam Kebakaran',
                        'kapasitas_personel' => $unit['kapasitas'],
                        'lokasi_id' => $instansi->lokasi_id,
                        'lokasi' => $instansi->alamat,
                        'status_ketersediaan' => Resource::STATUS_TERSEDIA,
                    ]
                );
            }
        });
    }
}
