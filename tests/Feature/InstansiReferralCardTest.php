<?php

namespace Tests\Feature;

use App\Models\HospitalReferral;
use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InstansiReferralCardTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{user: Pengguna, caseId: int, assignmentId: int, lokasiId: int} */
    private function createAssignment(): array
    {
        $lokasiId = DB::table('lokasi')->insertGetId([
            'latitude' => -7.265,
            'longitude' => 112.735,
            'alamat' => 'Lokasi Uji Rujukan',
        ], 'lokasi_id');

        $instansiId = DB::table('instansi')->insertGetId([
            'nama_instansi' => 'Instansi Uji Rujukan',
            'jenis_instansi' => 'Pemadam Kebakaran',
            'alamat' => 'Alamat Instansi Uji',
            'no_telp' => '081234567810',
            'lokasi_id' => $lokasiId,
        ], 'instansi_id');

        $user = Pengguna::create([
            'nama' => 'Petugas Instansi Uji',
            'no_telp' => '081234567811',
            'email' => 'instansi-referral-card@example.test',
            'role' => 'instansi',
            'instansi_id' => $instansiId,
        ]);

        $pelapor = Pengguna::create([
            'nama' => 'Pelapor Uji Rujukan',
            'no_telp' => '081234567812',
            'email' => 'pelapor-referral-card@example.test',
            'role' => 'pelapor',
        ]);

        $caseId = DB::table('emergency_case')->insertGetId([
            'lokasi_id' => $lokasiId,
            'pengguna_id' => $pelapor->pengguna_id,
            'jenis_kejadian' => 'Kecelakaan',
            'kondisi' => 'Korban pingsan',
            'kebutuhan' => 'Butuh rumah sakit',
            'waktu_laporan' => now(),
            'status_laporan' => 'Menunggu',
        ], 'case_id');

        $resourceId = DB::table('resource')->insertGetId([
            'instansi_id' => $instansiId,
            'lokasi_id' => $lokasiId,
            'nama_resource' => 'Unit Instansi Uji',
            'jenis_resource' => 'Ambulans',
            'lokasi' => 'Pangkalan Uji',
            'status_ketersediaan' => 'sedang_ditugaskan',
        ], 'resource_id');

        $assignmentId = DB::table('assignment')->insertGetId([
            'resource_id' => $resourceId,
            'case_id' => $caseId,
            'waktu_assign' => now(),
            'status_assign' => 'diterima',
        ], 'assignment_id');

        return compact('user', 'caseId', 'assignmentId', 'lokasiId');
    }

    private function createHospital(): int
    {
        return DB::table('rumah_sakit')->insertGetId([
            'nama_rs' => 'RS Pengujian Rujukan',
            'alamat' => 'Alamat RS Pengujian',
            'latitude' => -7.26,
            'longitude' => 112.74,
            'kapasitas' => 100,
            'kapasitas_tersedia' => 25,
            'status' => 'aktif',
        ], 'rumah_sakit_id');
    }

    public function test_instansi_melihat_pesan_jika_belum_ada_referral(): void
    {
        $data = $this->createAssignment();

        $this->actingAs($data['user'])
            ->get(route('instansi.assignments.show', $data['assignmentId']))
            ->assertOk()
            ->assertSee('Informasi Rujukan Rumah Sakit')
            ->assertSee('Belum ada proses rujukan Rumah Sakit');
    }

    public function test_instansi_melihat_referral_sedang_dicari(): void
    {
        $data = $this->createAssignment();
        HospitalReferral::create([
            'case_id' => $data['caseId'],
            'status' => 'mencari',
        ]);

        $this->actingAs($data['user'])
            ->get(route('instansi.assignments.show', $data['assignmentId']))
            ->assertOk()
            ->assertSee('Sedang Diproses')
            ->assertSee('Proses pencarian Rumah Sakit');
    }

    public function test_instansi_melihat_permintaan_yang_menunggu_respons(): void
    {
        $data = $this->createAssignment();
        $hospitalId = $this->createHospital();
        $referral = HospitalReferral::create([
            'case_id' => $data['caseId'],
            'status' => 'mencari',
        ]);
        $referral->requests()->create([
            'rumah_sakit_id' => $hospitalId,
            'attempt_order' => 1,
            'status' => 'menunggu',
            'requested_at' => now(),
            'expires_at' => now()->addSeconds(100),
        ]);

        $this->actingAs($data['user'])
            ->get(route('instansi.assignments.show', $data['assignmentId']))
            ->assertOk()
            ->assertSee('Menunggu respons Rumah Sakit.');
    }

    public function test_instansi_melihat_status_timeout_permintaan(): void
    {
        $data = $this->createAssignment();
        $hospitalId = $this->createHospital();
        $referral = HospitalReferral::create([
            'case_id' => $data['caseId'],
            'status' => 'mencari',
        ]);
        $referral->requests()->create([
            'rumah_sakit_id' => $hospitalId,
            'attempt_order' => 1,
            'status' => 'timeout',
            'requested_at' => now()->subMinutes(2),
            'expires_at' => now()->subSeconds(20),
            'responded_at' => now(),
        ]);

        $this->actingAs($data['user'])
            ->get(route('instansi.assignments.show', $data['assignmentId']))
            ->assertOk()
            ->assertSee('Permintaan terakhir melewati');
    }

    public function test_instansi_melihat_rs_tujuan_yang_menerima(): void
    {
        $data = $this->createAssignment();
        $hospitalId = $this->createHospital();
        HospitalReferral::create([
            'case_id' => $data['caseId'],
            'status' => 'diterima',
            'accepted_hospital_id' => $hospitalId,
        ]);

        $this->actingAs($data['user'])
            ->get(route('instansi.assignments.show', $data['assignmentId']))
            ->assertOk()
            ->assertSee('Rumah Sakit Tujuan')
            ->assertSee('RS Pengujian Rujukan');
    }

    public function test_instansi_melihat_alasan_kegagalan_referral(): void
    {
        $data = $this->createAssignment();
        HospitalReferral::create([
            'case_id' => $data['caseId'],
            'status' => 'gagal',
            'failure_reason' => 'Tidak ada kandidat Rumah Sakit tersedia.',
        ]);

        $this->actingAs($data['user'])
            ->get(route('instansi.assignments.show', $data['assignmentId']))
            ->assertOk()
            ->assertSee('Rujukan belum berhasil')
            ->assertSee('Tidak ada kandidat Rumah Sakit tersedia.');
    }

    public function test_instansi_tidak_bisa_melihat_assignment_instansi_lain(): void
    {
        $data = $this->createAssignment();
        $instansiLainId = DB::table('instansi')->insertGetId([
            'nama_instansi' => 'Instansi Lain',
            'jenis_instansi' => 'Polisi',
            'alamat' => 'Alamat Instansi Lain',
            'no_telp' => '081234567813',
            'lokasi_id' => $data['lokasiId'],
        ], 'instansi_id');

        $penggunaLain = Pengguna::create([
            'nama' => 'Petugas Instansi Lain',
            'no_telp' => '081234567814',
            'email' => 'instansi-lain-referral@example.test',
            'role' => 'instansi',
            'instansi_id' => $instansiLainId,
        ]);

        $this->actingAs($penggunaLain)
            ->get(route('instansi.assignments.show', $data['assignmentId']))
            ->assertNotFound();
    }
}
