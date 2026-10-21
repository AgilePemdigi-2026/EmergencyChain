<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Pengguna;
use App\Models\Instansi;
use App\Models\RumahSakit;
use App\Models\Resource;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Data master instansi
        $polisi = Instansi::firstOrCreate(
            ['nama_instansi' => 'Polisi Demo'],
            [
                'jenis_instansi' => 'Kepolisian',
                'alamat' => 'Surabaya',
                'no_telp' => '081200000001',
            ]
        );

        $damkar = Instansi::firstOrCreate(
            ['nama_instansi' => 'Damkar Demo'],
            [
                'jenis_instansi' => 'Pemadam Kebakaran',
                'alamat' => 'Surabaya',
                'no_telp' => '081200000002',
            ]
        );

        // 2. Data master rumah sakit
        $rs = RumahSakit::firstOrCreate(
            ['nama_rs' => 'Rumah Sakit Demo'],
            [
                'alamat' => 'Surabaya',
                'latitude' => -7.2575,
                'longitude' => 112.7521,
                'kapasitas' => 100,
                'kapasitas_tersedia' => 80,
                'status' => 'aktif',
            ]
        );

        // 3. Data resource milik instansi
        Resource::firstOrCreate(
            [
                'instansi_id' => $polisi->instansi_id,
                'nama_resource' => 'Unit Polisi Demo',
            ],
            [
                'jenis_resource' => 'Personel',
                'lokasi' => 'Surabaya',
                'status_ketersediaan' => 'tersedia',
            ]
        );

        Resource::firstOrCreate(
            [
                'instansi_id' => $damkar->instansi_id,
                'nama_resource' => 'Unit Damkar Demo',
            ],
            [
                'jenis_resource' => 'Kendaraan',
                'lokasi' => 'Surabaya',
                'status_ketersediaan' => 'tersedia',
            ]
        );

        // 4. Akun demo
        $accounts = [
            [
                'nama' => 'Petugas Emergency Case',
                'no_telp' => '081200000010',
                'email' => 'petugas@emergencychain.test',
                'role' => 'petugas_ec',
            ],
            [
                'nama' => 'Petugas Polisi Demo',
                'no_telp' => '081200000011',
                'email' => 'polisi@emergencychain.test',
                'role' => 'instansi',
                'instansi_id' => $polisi->instansi_id,
            ],
            [
                'nama' => 'Petugas Rumah Sakit Demo',
                'no_telp' => '081200000012',
                'email' => 'rs@emergencychain.test',
                'role' => 'rumah_sakit',
                'rumah_sakit_id' => $rs->rumah_sakit_id,
            ],
            [
                'nama' => 'Pelapor Demo',
                'no_telp' => '081200000013',
                'email' => 'pelapor@emergencychain.test',
                'role' => 'pelapor',
            ],
        ];

        foreach ($accounts as $account) {
            Pengguna::firstOrCreate(
                ['email' => $account['email']],
                [
                    ...$account,
                    'password' => Hash::make('Demo12345!'),
                ]
            );
        }
    }
}
