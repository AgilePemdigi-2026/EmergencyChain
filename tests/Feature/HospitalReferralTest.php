<?php

namespace Tests\Feature;

use App\Models\EmergencyCase;
use App\Models\HospitalReferral;
use App\Models\HospitalReferralRequest;
use App\Services\Hospital\HospitalMatchingService;
use App\Services\Hospital\HospitalReferralEligibilityService;
use App\Services\Hospital\HospitalReferralService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use App\Jobs\ProcessHospitalReferralTimeout;
use Illuminate\Support\Facades\DB;


class HospitalReferralTest extends TestCase
{
    use RefreshDatabase;

    public function test_eligibility_mendeteksi_kebutuhan_rs(): void
    {
        $case = new EmergencyCase([
            'jenis_kejadian' => 'Kecelakaan',
            'kondisi' => 'Korban luka berat',
            'kebutuhan' => 'Butuh rumah sakit',
        ]);

        $service = new HospitalReferralEligibilityService();

        $this->assertSame('perlu_rs', $service->check($case));
    }

    public function test_kondisi_ambigu_perlu_verifikasi(): void
    {
        $case = new EmergencyCase([
            'jenis_kejadian' => 'Kebakaran',
            'kondisi' => 'Kondisi korban belum diketahui',
            'kebutuhan' => 'Bantuan petugas',
        ]);

        $service = new HospitalReferralEligibilityService();

        $this->assertSame(
            'perlu_verifikasi',
            $service->check($case)
        );
    }

    
public function test_referral_tidak_membuat_duplikasi(): void
{
    Queue::fake();

    // Buat lokasi pengujian.
    $lokasiId = DB::table('lokasi')->insertGetId([
        'latitude' => -7.26183,
        'longitude' => 112.78729,
        'alamat' => 'Lokasi Test',
    ], 'lokasi_id');

    // Buat Pelapor.
    $penggunaId = DB::table('pengguna')->insertGetId([
        'nama' => 'Pelapor Test',
        'no_telp' => '081234567890',
        'email' => 'pelapor-hospital-test@example.test',
        'role' => 'pelapor',
    ], 'pengguna_id');

    // Buat Emergency Case yang membutuhkan RS.
    $caseId = DB::table('emergency_case')->insertGetId([
        'lokasi_id' => $lokasiId,
        'pengguna_id' => $penggunaId,
        'jenis_kejadian' => 'Kecelakaan',
        'kondisi' => 'Korban luka berat',
        'kebutuhan' => 'Butuh rumah sakit',
        'waktu_laporan' => now(),
        'status_laporan' => 'Menunggu Assignment',
    ], 'case_id');

    // Buat Rumah Sakit kandidat.
    DB::table('rumah_sakit')->insert([
        'nama_rs' => 'RS Test',
        'alamat' => 'Surabaya',
        'latitude' => -7.26819,
        'longitude' => 112.75805,
        'kapasitas' => 100,
        'kapasitas_tersedia' => 50,
        'status' => 'aktif',
    ]);

    // Panggil service dua kali.
    $service = app(HospitalReferralService::class);

    $first = $service->start($caseId);
    $second = $service->start($caseId);

    // Tidak boleh membuat referral kedua.
    $this->assertSame($first->id, $second->id);

    $this->assertDatabaseCount('hospital_referrals', 1);
    $this->assertDatabaseCount('hospital_referral_requests', 1);

    $this->assertDatabaseHas('hospital_referral_requests', [
        'hospital_referral_id' => $first->id,
        'attempt_order' => 1,
        'status' => 'menunggu',
    ]);

    // Hanya satu Job timeout yang dijadwalkan.
    Queue::assertPushed(
        ProcessHospitalReferralTimeout::class,
        1
    );
}


private function buatDataRujukanTest(): array
{
    Queue::fake();

    $lokasiId = DB::table('lokasi')->insertGetId([
        'latitude' => -7.26183,
        'longitude' => 112.78729,
        'alamat' => 'Lokasi Test',
    ], 'lokasi_id');

    $pelaporId = DB::table('pengguna')->insertGetId([
        'nama' => 'Pelapor Test',
        'no_telp' => '081234567890',
        'email' => 'pelapor-referral@example.test',
        'role' => 'pelapor',
    ], 'pengguna_id');

    $caseId = DB::table('emergency_case')->insertGetId([
        'lokasi_id' => $lokasiId,
        'pengguna_id' => $pelaporId,
        'jenis_kejadian' => 'Kecelakaan',
        'kondisi' => 'Korban luka berat',
        'kebutuhan' => 'Butuh rumah sakit',
        'waktu_laporan' => now(),
        'status_laporan' => 'Menunggu Assignment',
    ], 'case_id');

    $rs1 = DB::table('rumah_sakit')->insertGetId([
        'nama_rs' => 'RS Terdekat Test',
        'alamat' => 'Surabaya',
        'latitude' => -7.262,
        'longitude' => 112.787,
        'kapasitas' => 100,
        'kapasitas_tersedia' => 50,
        'status' => 'aktif',
    ], 'rumah_sakit_id');

    $rs2 = DB::table('rumah_sakit')->insertGetId([
        'nama_rs' => 'RS Kedua Test',
        'alamat' => 'Surabaya',
        'latitude' => -7.270,
        'longitude' => 112.760,
        'kapasitas' => 100,
        'kapasitas_tersedia' => 50,
        'status' => 'aktif',
    ], 'rumah_sakit_id');

    $penggunaRs1 = \App\Models\Pengguna::create([
        'nama' => 'Petugas RS Test',
        'no_telp' => '081234567891',
        'email' => 'rs-referral@example.test',
        'role' => 'rumah_sakit',
        'rumah_sakit_id' => $rs1,
    ]);

    $service = app(HospitalReferralService::class);
    $referral = $service->start($caseId);
    $request = $referral->requests()->firstOrFail();

    return compact(
        'service',
        'referral',
        'request',
        'rs1',
        'rs2',
        'penggunaRs1'
    );
}

public function test_rs_menerima_permintaan(): void
{
    $data = $this->buatDataRujukanTest();

    $hasil = $data['service']->respond(
        $data['request']->id,
        $data['penggunaRs1'],
        'diterima'
    );

    $this->assertSame('diterima', $hasil->status);
    $this->assertEquals(
        $data['rs1'],
        $hasil->accepted_hospital_id
    );

    $this->assertDatabaseHas('hospital_referral_requests', [
        'id' => $data['request']->id,
        'status' => 'diterima',
    ]);

    $this->assertDatabaseCount('hospital_referral_requests', 1);
}

public function test_rs_menolak_lanjut_ke_rs_kedua(): void
{
    $data = $this->buatDataRujukanTest();

    $hasil = $data['service']->respond(
        $data['request']->id,
        $data['penggunaRs1'],
        'ditolak',
        'Tidak dapat menerima pasien'
    );

    $this->assertSame('mencari', $hasil->status);

    $this->assertDatabaseHas('hospital_referral_requests', [
        'id' => $data['request']->id,
        'status' => 'ditolak',
    ]);

    $this->assertDatabaseHas('hospital_referral_requests', [
        'hospital_referral_id' => $hasil->id,
        'rumah_sakit_id' => $data['rs2'],
        'attempt_order' => 2,
        'status' => 'menunggu',
    ]);

    $this->assertDatabaseCount('hospital_referral_requests', 2);

    Queue::assertPushed(
        \App\Jobs\ProcessHospitalReferralTimeout::class,
        2
    );
}


public function test_timeout_100_detik_lanjut_ke_rs_kedua(): void
{
    $data = $this->buatDataRujukanTest();

    $request = $data['request'];

    // Simulasikan waktu maju 101 detik.
    $this->travel(101)->seconds();

    try {
        $data['service']->processTimeout($request->id);

        // Permintaan pertama sudah timeout.
        $this->assertDatabaseHas('hospital_referral_requests', [
            'id' => $request->id,
            'status' => 'timeout',
        ]);

        // Sistem otomatis mengirim permintaan ke RS kedua.
        $this->assertDatabaseHas('hospital_referral_requests', [
            'hospital_referral_id' => $data['referral']->id,
            'rumah_sakit_id' => $data['rs2'],
            'attempt_order' => 2,
            'status' => 'menunggu',
        ]);

        $this->assertDatabaseCount(
            'hospital_referral_requests',
            2
        );

        // Ada Job timeout untuk masing-masing permintaan.
        Queue::assertPushed(
            \App\Jobs\ProcessHospitalReferralTimeout::class,
            2
        );
    } finally {
        $this->travelBack();
    }
}


public function test_timeout_ganda_tidak_membuat_request_duplikat(): void
{
    $data = $this->buatDataRujukanTest();

    $this->travel(101)->seconds();

    try {
        $data['service']->processTimeout($data['request']->id);
        $data['service']->processTimeout($data['request']->id);

        $this->assertDatabaseCount('hospital_referral_requests', 2);

        $this->assertDatabaseHas('hospital_referral_requests', [
            'id' => $data['request']->id,
            'status' => 'timeout',
        ]);

        Queue::assertPushed(
            \App\Jobs\ProcessHospitalReferralTimeout::class,
            2
        );
    } finally {
        $this->travelBack();
    }
}

public function test_semua_rs_menolak_referral_gagal(): void
{
    $data = $this->buatDataRujukanTest();

    // RS pertama menolak.
    $data['service']->respond(
        $data['request']->id,
        $data['penggunaRs1'],
        'ditolak',
        'Tidak tersedia'
    );

    // Buat akun petugas RS kedua.
    $penggunaRs2 = \App\Models\Pengguna::create([
        'nama' => 'Petugas RS Kedua',
        'no_telp' => '081234567892',
        'email' => 'rs-kedua@example.test',
        'role' => 'rumah_sakit',
        'rumah_sakit_id' => $data['rs2'],
    ]);

    $requestKedua = \App\Models\HospitalReferralRequest::query()
        ->where('hospital_referral_id', $data['referral']->id)
        ->where('attempt_order', 2)
        ->firstOrFail();

    // RS kedua juga menolak.
    $hasil = $data['service']->respond(
        $requestKedua->id,
        $penggunaRs2,
        'ditolak',
        'Tidak tersedia'
    );

    $this->assertSame('gagal', $hasil->status);
    $this->assertNotNull($hasil->failure_reason);
    $this->assertNull($hasil->accepted_hospital_id);

    $this->assertDatabaseCount('hospital_referral_requests', 2);
}

public function test_rs_lain_tidak_boleh_merespons_request(): void
{
    $data = $this->buatDataRujukanTest();

    $penggunaRsLain = \App\Models\Pengguna::create([
        'nama' => 'Petugas RS Lain',
        'no_telp' => '081234567893',
        'email' => 'rs-lain@example.test',
        'role' => 'rumah_sakit',
        'rumah_sakit_id' => $data['rs2'],
    ]);

    $this->expectException(
        \Symfony\Component\HttpKernel\Exception\HttpException::class
    );

    
try {
    $data['service']->respond(
        $data['request']->id,
        $penggunaRsLain,
        'diterima'
    );

    $this->fail('Seharusnya RS lain tidak boleh merespons.');
} catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
    $this->assertSame(403, $e->getStatusCode());
}

$this->assertDatabaseHas('hospital_referral_requests', [
    'id' => $data['request']->id,
    'status' => 'menunggu',
]);

$this->assertDatabaseHas('hospital_referrals', [
    'id' => $data['referral']->id,
    'status' => 'mencari',
    'accepted_hospital_id' => null,
]);


    $data['service']->respond(
        $data['request']->id,
        $penggunaRsLain,
        'diterima'
    );
}



}
