<?php

namespace Tests\Feature;

use App\Jobs\ProcessHospitalReferralTimeout;
use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class HospitalReferralHttpTest extends TestCase
{
    use RefreshDatabase;

    private function buatDataTest(): array
    {
        Queue::fake();

        $lokasiId = DB::table('lokasi')->insertGetId([
            'latitude' => -7.26183,
            'longitude' => 112.78729,
            'alamat' => 'Lokasi Test',
        ], 'lokasi_id');

        $pelaporId = DB::table('pengguna')->insertGetId([
            'nama' => 'Pelapor HTTP Test',
            'no_telp' => '081234567890',
            'email' => 'pelapor-http@example.test',
            'role' => 'pelapor',
        ], 'pengguna_id');

        $caseId = DB::table('emergency_case')->insertGetId([
            'lokasi_id' => $lokasiId,
            'pengguna_id' => $pelaporId,
            'jenis_kejadian' => 'Kecelakaan',
            'kondisi' => 'Korban pingsan',
            'kebutuhan' => 'Butuh rumah sakit',
            'waktu_laporan' => now(),
            'status_laporan' => 'Menunggu Assignment',
        ], 'case_id');

        $rs1 = DB::table('rumah_sakit')->insertGetId([
            'nama_rs' => 'RS HTTP Pertama',
            'alamat' => 'Surabaya',
            'latitude' => -7.262,
            'longitude' => 112.787,
            'kapasitas' => 100,
            'kapasitas_tersedia' => 50,
            'status' => 'aktif',
        ], 'rumah_sakit_id');

        $rs2 = DB::table('rumah_sakit')->insertGetId([
            'nama_rs' => 'RS HTTP Kedua',
            'alamat' => 'Surabaya',
            'latitude' => -7.270,
            'longitude' => 112.760,
            'kapasitas' => 100,
            'kapasitas_tersedia' => 50,
            'status' => 'aktif',
        ], 'rumah_sakit_id');

        $userRs1 = Pengguna::create([
            'nama' => 'Petugas RS HTTP Pertama',
            'no_telp' => '081234567891',
            'email' => 'rs1-http@example.test',
            'role' => 'rumah_sakit',
            'rumah_sakit_id' => $rs1,
        ]);

        $userRs2 = Pengguna::create([
            'nama' => 'Petugas RS HTTP Kedua',
            'no_telp' => '081234567892',
            'email' => 'rs2-http@example.test',
            'role' => 'rumah_sakit',
            'rumah_sakit_id' => $rs2,
        ]);

        $userEc = Pengguna::create([
            'nama' => 'Petugas EC HTTP',
            'no_telp' => '081234567893',
            'email' => 'ec-http@example.test',
            'role' => 'petugas_ec',
        ]);

        return compact(
            'caseId',
            'rs1',
            'rs2',
            'userRs1',
            'userRs2',
            'userEc'
        );
    }

    public function test_tamu_dialihkan_ke_login(): void
    {
        $this->get(route('hospital.referrals.index'))
            ->assertRedirect();
    }

    public function test_petugas_ec_dapat_memulai_referral(): void
    {
        $data = $this->buatDataTest();

        $this->actingAs($data['userEc'])
            ->post(route('hospital.referrals.start', $data['caseId']))
            ->assertRedirect();

        $this->assertDatabaseHas('hospital_referrals', [
            'case_id' => $data['caseId'],
            'status' => 'mencari',
        ]);

        $this->assertDatabaseHas('hospital_referral_requests', [
            'rumah_sakit_id' => $data['rs1'],
            'attempt_order' => 1,
            'status' => 'menunggu',
        ]);

        Queue::assertPushed(ProcessHospitalReferralTimeout::class, 1);
    }

    public function test_rs_hanya_melihat_permintaan_miliknya(): void
    {
        $data = $this->buatDataTest();

        $this->actingAs($data['userEc'])
            ->post(route('hospital.referrals.start', $data['caseId']));

        $this->actingAs($data['userRs1'])
            ->get(route('hospital.referrals.index'))
            ->assertOk()
            ->assertSee('Permintaan #1')
            ->assertSee('Terima Pasien');

        $this->actingAs($data['userRs2'])
            ->get(route('hospital.referrals.index'))
            ->assertOk()
            ->assertSee('Belum ada permintaan rujukan.')
            ->assertDontSee('Terima Pasien');
    }

    public function test_rs_dapat_menerima_melalui_http(): void
    {
        $data = $this->buatDataTest();

        $this->actingAs($data['userEc'])
            ->post(route('hospital.referrals.start', $data['caseId']));

        $requestId = DB::table('hospital_referral_requests')->value('id');

        $this->actingAs($data['userRs1'])
            ->post(
                route('hospital.referrals.respond', $requestId),
                ['decision' => 'diterima']
            )
            ->assertRedirect(route('hospital.referrals.index'));

        $this->assertDatabaseHas('hospital_referrals', [
            'case_id' => $data['caseId'],
            'status' => 'diterima',
            'accepted_hospital_id' => $data['rs1'],
        ]);
    }

    public function test_rs_menolak_dan_permintaan_berpindah(): void
    {
        $data = $this->buatDataTest();

        $this->actingAs($data['userEc'])
            ->post(route('hospital.referrals.start', $data['caseId']));

        $requestId = DB::table('hospital_referral_requests')->value('id');

        $this->actingAs($data['userRs1'])
            ->post(
                route('hospital.referrals.respond', $requestId),
                [
                    'decision' => 'ditolak',
                    'reason' => 'Tidak tersedia',
                ]
            )
            ->assertRedirect(route('hospital.referrals.index'));

        $this->assertDatabaseHas('hospital_referral_requests', [
            'rumah_sakit_id' => $data['rs2'],
            'attempt_order' => 2,
            'status' => 'menunggu',
        ]);

        $this->actingAs($data['userRs2'])
            ->get(route('hospital.referrals.index'))
            ->assertOk()
            ->assertSee('Terima Pasien');
    }

    public function test_rs_lain_tidak_bisa_merespons(): void
    {
        $data = $this->buatDataTest();

        $this->actingAs($data['userEc'])
            ->post(route('hospital.referrals.start', $data['caseId']));

        $requestId = DB::table('hospital_referral_requests')->value('id');

        $this->actingAs($data['userRs2'])
            ->post(
                route('hospital.referrals.respond', $requestId),
                ['decision' => 'diterima']
            )
            ->assertForbidden();

        $this->assertDatabaseHas('hospital_referral_requests', [
            'id' => $requestId,
            'status' => 'menunggu',
        ]);
    }
}
