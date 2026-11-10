<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

use App\Models\Pengguna;
use App\Models\Instansi;
use App\Models\RumahSakit;
use App\Models\Resource;
use App\Models\Lokasi;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {

        // ==========================================
        // 1. DATA LOKASI PANGKALAN INSTANSI
        // ==========================================

        $lokasiPolisi = Lokasi::firstOrCreate(
            [
                'alamat' => 'Jl. Sikatan No. 1, Krembangan Selatan, Surabaya, Jawa Timur 60175',
            ],
            [
                'latitude' => -7.259900,
                'longitude' => 112.750800,
            ]
        );

        $lokasiDamkar = Lokasi::firstOrCreate(
            [
                'alamat' => 'Jl. Pasar Turi No. 21, Bubutan, Surabaya, Jawa Timur 60174',
            ],
            [
                'latitude' => -7.2451166,
                'longitude' => 112.7364212,
            ]
        );

        $lokasiAmbulansRs = Lokasi::firstOrCreate(
            [
                'alamat' => 'Jl. Mayjen Prof. Dr. Moestopo No. 6-8, Airlangga, Gubeng, Surabaya, Jawa Timur 60286',
            ],
            [
                'latitude' => -7.2681934182,
                'longitude' => 112.7580586771,
            ]
        );

        $lokasiAmbulansPmi = Lokasi::firstOrCreate(
            [
                'alamat' => 'Jl. Sumatera No. 71, Gubeng, Surabaya, Jawa Timur 60281',
            ],
            [
                'latitude' => -7.2713,
                'longitude' => 112.7479,
            ]
        );


        // ==========================================
        // 2. DATA MASTER INSTANSI
        // ==========================================

        $polisi = Instansi::updateOrCreate(
            [
                'nama_instansi' => 'Polrestabes Surabaya',
            ],
            [
                'jenis_instansi' => 'Kepolisian',
                'alamat' => 'Jl. Sikatan No. 1, Krembangan Selatan, Surabaya, Jawa Timur 60175',
                'no_telp' => '0313526011',
                'lokasi_id' => $lokasiPolisi->lokasi_id,
            ]
        );

        $damkar = Instansi::updateOrCreate(
            [
                'nama_instansi' => 'Dinas Pemadam Kebakaran dan Penyelamatan Kota Surabaya',
            ],
            [
                'jenis_instansi' => 'Pemadam Kebakaran',
                'alamat' => 'Jl. Pasar Turi No. 21, Bubutan, Surabaya, Jawa Timur 60174',
                'no_telp' => '0313533843',
                'lokasi_id' => $lokasiDamkar->lokasi_id,
            ]
        );

        $ambulansRs = Instansi::updateOrCreate(
            [
                'nama_instansi' => 'Unit Ambulans RSUD Dr. Soetomo',
            ],
            [
                'jenis_instansi' => 'Ambulans Rumah Sakit',
                'alamat' => 'Jl. Mayjen Prof. Dr. Moestopo No. 6-8, Airlangga, Gubeng, Surabaya, Jawa Timur 60286',
                'no_telp' => '0315501001',
                'lokasi_id' => $lokasiAmbulansRs->lokasi_id,
            ]
        );

        $ambulansIndependen = Instansi::updateOrCreate(
            [
                'nama_instansi' => 'PMI Kota Surabaya',
            ],
            [
                'jenis_instansi' => 'Ambulans Independen',
                'alamat' => 'Jl. Sumatera No. 71, Gubeng, Surabaya, Jawa Timur 60281',
                'no_telp' => '03199443899',
                'lokasi_id' => $lokasiAmbulansPmi->lokasi_id,
            ]
        );


        // ==========================================
        // 2. DATA MASTER RUMAH SAKIT
        // ==========================================

        $rs = RumahSakit::firstOrCreate(
            [
                'nama_rs' => 'RSUD Dr. Soetomo',
            ],
            [
                'alamat' => 'Jl. Mayjen Prof. Dr. Moestopo No. 6-8, Airlangga, Gubeng, Surabaya, Jawa Timur 60286',
                'latitude' => -7.2681934182,
                'longitude' => 112.7580586771,
                'kapasitas' => 100,
                'kapasitas_tersedia' => 80,
                'status' => 'aktif',
            ]
        );

        // RS Universitas Airlangga (Simulasi)
        $rsUnair = RumahSakit::firstOrCreate(
            [
                'nama_rs' => 'RS Universitas Airlangga',
            ],
            [
                'alamat' => 'Jl. Dharmahusada Permai, Mulyorejo, Surabaya, Jawa Timur 60115',
                'latitude' => -7.26970931,
                'longitude' => 112.78482274,
                'kapasitas' => 100,
                'kapasitas_tersedia' => 80,
                'status' => 'aktif',
            ]
        );


        // ==========================================
        // 4. DATA RESOURCE MILIK INSTANSI
        // ==========================================

        // Resource Polisi
        $resourcePolisi = Resource::firstOrCreate(
            [
                'instansi_id' => $polisi->instansi_id,
                'nama_resource' => 'Unit Polisi Demo',
            ],
            [
                'lokasi_id' => $lokasiPolisi->lokasi_id,
                'jenis_resource' => 'Polisi',
                'lokasi' => 'Surabaya',
                'status_ketersediaan' => 'tersedia',
            ]
        );

        // Hubungkan lokasi jika Resource lama belum memiliki lokasi_id.
        if (!$resourcePolisi->lokasi_id) {
            $resourcePolisi->update([
                'lokasi_id' => $lokasiPolisi->lokasi_id,
            ]);
        }


        // Resource Damkar
        $resourceDamkar = Resource::firstOrCreate(
            [
                'instansi_id' => $damkar->instansi_id,
                'nama_resource' => 'Unit Damkar Demo',
            ],
            [
                'lokasi_id' => $lokasiDamkar->lokasi_id,
                'jenis_resource' => 'Pemadam Kebakaran',
                'lokasi' => 'Surabaya',
                'status_ketersediaan' => 'tersedia',
            ]
        );

        // Hubungkan lokasi jika Resource lama belum memiliki lokasi_id.
        if (!$resourceDamkar->lokasi_id) {
            $resourceDamkar->update([
                'lokasi_id' => $lokasiDamkar->lokasi_id,
            ]);
        }


        // Resource Ambulans RSUD Dr. Soetomo
        $resourceAmbulansRs = Resource::firstOrCreate(
            [
                'instansi_id' => $ambulansRs->instansi_id,
                'nama_resource' => 'Ambulans RSUD Dr. Soetomo 01 (Simulasi)',
            ],
            [
                'lokasi_id' => $lokasiAmbulansRs->lokasi_id,
                'jenis_resource' => 'Ambulans',
                'lokasi' => 'Surabaya',
                'status_ketersediaan' => 'tersedia',
            ]
        );

        if (!$resourceAmbulansRs->lokasi_id) {
            $resourceAmbulansRs->update([
                'lokasi_id' => $lokasiAmbulansRs->lokasi_id,
            ]);
        }


        // Resource Ambulans PMI Kota Surabaya
        $resourceAmbulansPmi = Resource::firstOrCreate(
            [
                'instansi_id' => $ambulansIndependen->instansi_id,
                'nama_resource' => 'Ambulans PMI Kota Surabaya 01 (Simulasi)',
            ],
            [
                'lokasi_id' => $lokasiAmbulansPmi->lokasi_id,
                'jenis_resource' => 'Ambulans',
                'lokasi' => 'Surabaya',
                'status_ketersediaan' => 'tersedia',
            ]
        );

        if (!$resourceAmbulansPmi->lokasi_id) {
            $resourceAmbulansPmi->update([
                'lokasi_id' => $lokasiAmbulansPmi->lokasi_id,
            ]);
        }


        // ==========================================
        // 5. DATA AKUN PENGGUNA
        // ==========================================

        $accounts = [

            // Akun Petugas Emergency Case
            [
                'nama' => 'Petugas Emergency Case',
                'no_telp' => '081200000010',
                'email' => 'petugas@emergencychain.test',
                'role' => 'petugas_ec',
            ],

            // Akun Instansi Polisi
            [
                'nama' => 'Petugas Polisi Demo',
                'no_telp' => '081200000011',
                'email' => 'polisi@emergencychain.test',
                'role' => 'instansi',
                'instansi_id' => $polisi->instansi_id,
            ],

            // Akun Rumah Sakit
            [
                'nama' => 'Petugas Rumah Sakit Demo',
                'no_telp' => '081200000012',
                'email' => 'rs@emergencychain.test',
                'role' => 'rumah_sakit',
                'rumah_sakit_id' => $rs->rumah_sakit_id,
            ],

            // Akun RS Universitas Airlangga
            [
                'nama' => 'Petugas RS Universitas Airlangga',
                'no_telp' => '081200000016',
                'email' => 'rs.unair@emergencychain.test',
                'role' => 'rumah_sakit',
                'rumah_sakit_id' => $rsUnair->rumah_sakit_id,
            ],

            // Akun Pelapor
            [
                'nama' => 'Pelapor Demo',
                'no_telp' => '081200000013',
                'email' => 'pelapor@emergencychain.test',
                'role' => 'pelapor',
            ],

            // Akun Instansi Ambulans RS
            [
                'nama' => 'Petugas Ambulans RS Demo',
                'no_telp' => '081200000014',
                'email' => 'ambulans.rs@emergencychain.test',
                'role' => 'instansi',
                'instansi_id' => $ambulansRs->instansi_id,
            ],

            // Akun Instansi PMI
            [
                'nama' => 'Petugas Ambulans Independen',
                'no_telp' => '081200000015',
                'email' => 'ambulans.independen@emergencychain.test',
                'role' => 'instansi',
                'instansi_id' => $ambulansIndependen->instansi_id,
            ],
        ];

        foreach ($accounts as $account) {
            Pengguna::firstOrCreate(
                [
                    'email' => $account['email'],
                ],
                [
                    ...$account,
                    'password' => Hash::make('Demo12345!'),
                ]
            );
        }
    }
}
