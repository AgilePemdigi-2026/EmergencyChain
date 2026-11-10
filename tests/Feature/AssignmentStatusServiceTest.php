<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\EmergencyCase;
use App\Models\Pengguna;
use App\Models\Resource;
use App\Models\StatusPenanganan;
use App\Services\Assignment\AssignmentStatusService;
use App\Services\Matching\ResourceMatchingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mockery;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class AssignmentStatusServiceTest extends TestCase
{
    use RefreshDatabase;

    private function createScenario(
        string $caseStatus = 'Menunggu'
    ): array {
        $lokasiId = DB::table('lokasi')->insertGetId([
            'latitude' => -7.265,
            'longitude' => 112.735,
            'alamat' => 'Lokasi Pengujian',
        ], 'lokasi_id');

        $instansiId = DB::table('instansi')->insertGetId([
            'nama_instansi' => 'Instansi Pengujian',
            'jenis_instansi' => 'Ambulans',
            'alamat' => 'Alamat Instansi',
            'no_telp' => '081234567890',
            'lokasi_id' => $lokasiId,
        ], 'instansi_id');

        $user = Pengguna::create([
            'nama' => 'Petugas Instansi',
            'no_telp' => '081234567891',
            'email' => 'petugas@test.example',
            'role' => 'instansi',
            'instansi_id' => $instansiId,
        ]);

        $pelapor = Pengguna::create([
            'nama' => 'Pelapor Pengujian',
            'no_telp' => '081234567892',
            'email' => 'pelapor@test.example',
            'role' => 'pelapor',
        ]);

        $caseId = DB::table('emergency_case')->insertGetId([
            'lokasi_id' => $lokasiId,
            'pengguna_id' => $pelapor->pengguna_id,
            'jenis_kejadian' => 'Kecelakaan',
            'kondisi' => 'Korban mengalami luka',
            'kebutuhan' => 'Ambulans',
            'waktu_laporan' => now(),
            'status_laporan' => $caseStatus,
        ], 'case_id');

        $resourceId = DB::table('resource')->insertGetId([
            'instansi_id' => $instansiId,
            'lokasi_id' => $lokasiId,
            'nama_resource' => 'Ambulans Pengujian',
            'jenis_resource' => 'Ambulans',
            'kapasitas_personel' => 4,
            'lokasi' => 'Pangkalan Pengujian',
            'status_ketersediaan' => 'sedang_ditugaskan',
        ], 'resource_id');

        $assignmentId = DB::table('assignment')->insertGetId([
            'resource_id' => $resourceId,
            'case_id' => $caseId,
            'waktu_assign' => now(),
            'status_assign' => 'diterima',
        ], 'assignment_id');

        return compact(
            'user',
            'pelapor',
            'lokasiId',
            'instansiId',
            'caseId',
            'resourceId',
            'assignmentId'
        );
    }

    private function createService(
        ?ResourceMatchingService $matching = null
    ): AssignmentStatusService {
        $matching ??= Mockery::mock(ResourceMatchingService::class);

        return new AssignmentStatusService($matching);
    }

    private function updateAssignment(
        array $data,
        string $status,
        ?AssignmentStatusService $service = null
    ): array {
        $service ??= $this->createService();

        return $service->updateStatus(
            $data['assignmentId'],
            $data['user'],
            $status,
            'Catatan pengujian ' . $status
        );
    }

    private function createAnotherCase(array $data): int
    {
        return DB::table('emergency_case')->insertGetId([
            'lokasi_id' => $data['lokasiId'],
            'pengguna_id' => $data['pelapor']->pengguna_id,
            'jenis_kejadian' => 'Kebakaran',
            'kondisi' => 'Terjadi kebakaran',
            'kebutuhan' => 'Pemadam Kebakaran',
            'waktu_laporan' => now(),
            'status_laporan' => 'Menunggu',
        ], 'case_id');
    }

    private function createAnotherResource(array $data): int
    {
        return DB::table('resource')->insertGetId([
            'instansi_id' => $data['instansiId'],
            'lokasi_id' => $data['lokasiId'],
            'nama_resource' => 'Resource Tambahan',
            'jenis_resource' => 'Ambulans',
            'kapasitas_personel' => 4,
            'lokasi' => 'Pangkalan Pengujian',
            'status_ketersediaan' => 'sedang_ditugaskan',
        ], 'resource_id');
    }

    /**
     * 1. Menguji seluruh urutan lifecycle Assignment.
     */
    public function test_assignment_status_lifecycle_updates_emergency_case(): void
    {
        $data = $this->createScenario();

        $matching = Mockery::mock(ResourceMatchingService::class);

        $matching->shouldReceive('rematchWaitingCases')
            ->once()
            ->andReturn([]);

        $service = $this->createService($matching);

        $statuses = [
            'menuju_lokasi' => 'Menuju Lokasi',
            'tiba_di_lokasi' => 'Tiba di Lokasi',
            'dalam_penanganan' => 'Dalam Penanganan',
            'selesai' => 'Selesai',
        ];

        foreach ($statuses as $status => $expectedCaseStatus) {
            $this->updateAssignment(
                $data,
                $status,
                $service
            );

            $this->assertDatabaseHas('status_penanganan', [
                'assignment_id' => $data['assignmentId'],
                'status' => $status,
            ]);

            $this->assertDatabaseHas('emergency_case', [
                'case_id' => $data['caseId'],
                'status_laporan' => $expectedCaseStatus,
            ]);
        }

        $this->assertDatabaseCount('status_penanganan', 4);

        $this->assertDatabaseHas('assignment', [
            'assignment_id' => $data['assignmentId'],
            'status_assign' => 'selesai',
        ]);

        $this->assertDatabaseHas('resource', [
            'resource_id' => $data['resourceId'],
            'status_ketersediaan' => 'tersedia',
        ]);
    }

    /**
     * 2. Resource tidak dilepas jika masih memiliki
     * Assignment aktif lainnya.
     */
    public function test_resource_is_not_released_while_other_assignment_is_active(): void
    {
        $data = $this->createScenario();

        $anotherCaseId = $this->createAnotherCase($data);

        DB::table('assignment')->insert([
            'resource_id' => $data['resourceId'],
            'case_id' => $anotherCaseId,
            'waktu_assign' => now(),
            'status_assign' => 'diterima',
        ]);

        $matching = Mockery::mock(ResourceMatchingService::class);

        $matching->shouldNotReceive('rematchWaitingCases');

        $service = $this->createService($matching);

        $this->updateAssignment(
            $data,
            'selesai',
            $service
        );

        $this->assertDatabaseHas('resource', [
            'resource_id' => $data['resourceId'],
            'status_ketersediaan' => 'sedang_ditugaskan',
        ]);
    }

    /**
     * 3. Resource dilepas dan matching ulang dijalankan
     * jika tidak memiliki Assignment aktif lainnya.
     */
    public function test_resource_is_released_and_rematching_is_triggered(): void
    {
        $data = $this->createScenario();

        $matching = Mockery::mock(ResourceMatchingService::class);

        $matching->shouldReceive('rematchWaitingCases')
            ->once()
            ->andReturn([]);

        $service = $this->createService($matching);

        $result = $this->updateAssignment(
            $data,
            'selesai',
            $service
        );

        $this->assertTrue($result['resource_released']);

        $this->assertDatabaseHas('resource', [
            'resource_id' => $data['resourceId'],
            'status_ketersediaan' => 'tersedia',
        ]);
    }

    /**
     * 4. EC tidak disinkronisasi ketika masih
     * Menunggu Assignment.
     */
    public function test_emergency_case_waiting_assignment_is_not_synchronized(): void
    {
        $data = $this->createScenario('Menunggu Assignment');

        $matching = Mockery::mock(ResourceMatchingService::class);

        $matching->shouldNotReceive('rematchWaitingCases');

        $service = $this->createService($matching);

        $this->updateAssignment(
            $data,
            'menuju_lokasi',
            $service
        );

        $this->assertDatabaseHas('emergency_case', [
            'case_id' => $data['caseId'],
            'status_laporan' => 'Menunggu Assignment',
        ]);

        $this->assertDatabaseHas('status_penanganan', [
            'assignment_id' => $data['assignmentId'],
            'status' => 'menuju_lokasi',
        ]);
    }

    /**
     * 5. EC tidak disinkronisasi ketika masih
     * Perlu Verifikasi.
     */
    public function test_emergency_case_needing_verification_is_not_synchronized(): void
    {
        $data = $this->createScenario('Perlu Verifikasi');

        $matching = Mockery::mock(ResourceMatchingService::class);

        $matching->shouldNotReceive('rematchWaitingCases');

        $service = $this->createService($matching);

        $this->updateAssignment(
            $data,
            'menuju_lokasi',
            $service
        );

        $this->assertDatabaseHas('emergency_case', [
            'case_id' => $data['caseId'],
            'status_laporan' => 'Perlu Verifikasi',
        ]);
    }

    /**
     * 6. EC selesai hanya setelah seluruh Assignment
     * pada kasus tersebut selesai.
     */
    public function test_emergency_case_finishes_after_all_assignments_are_completed(): void
    {
        $data = $this->createScenario('Dalam Penanganan');

        $anotherResourceId = $this->createAnotherResource($data);

        $anotherAssignmentId = DB::table('assignment')->insertGetId([
            'resource_id' => $anotherResourceId,
            'case_id' => $data['caseId'],
            'waktu_assign' => now(),
            'status_assign' => 'diterima',
        ], 'assignment_id');

        $matching = Mockery::mock(ResourceMatchingService::class);

        $matching->shouldReceive('rematchWaitingCases')
            ->twice()
            ->andReturn([]);

        $service = $this->createService($matching);

        $this->updateAssignment(
            $data,
            'selesai',
            $service
        );

        $this->assertDatabaseMissing('emergency_case', [
            'case_id' => $data['caseId'],
            'status_laporan' => 'Selesai',
        ]);

        $service->updateStatus(
            $anotherAssignmentId,
            $data['user'],
            'selesai',
            'Penanganan selesai'
        );

        $this->assertDatabaseHas('emergency_case', [
            'case_id' => $data['caseId'],
            'status_laporan' => 'Selesai',
        ]);
    }

    /**
     * 7. Assignment yang sudah selesai tidak dapat
     * diperbarui kembali.
     */
    public function test_completed_assignment_cannot_be_updated_again(): void
    {
        $data = $this->createScenario();

        DB::table('assignment')
            ->where('assignment_id', $data['assignmentId'])
            ->update([
                'status_assign' => 'selesai',
            ]);

        $service = $this->createService();

        try {
            $this->updateAssignment(
                $data,
                'menuju_lokasi',
                $service
            );

            $this->fail('Assignment selesai seharusnya tidak dapat diperbarui.');

        } catch (HttpException $exception) {
            $this->assertSame(422, $exception->getStatusCode());
        }
    }

    /**
     * 8. Instansi tidak dapat memperbarui Assignment
     * milik Instansi lain.
     */
    public function test_instansi_cannot_update_another_instancies_assignment(): void
    {
        $data = $this->createScenario();

        $anotherLocationId = DB::table('lokasi')->insertGetId([
            'latitude' => -7.270,
            'longitude' => 112.740,
            'alamat' => 'Lokasi Instansi Lain',
        ], 'lokasi_id');

        $anotherInstansiId = DB::table('instansi')->insertGetId([
            'nama_instansi' => 'Instansi Lain',
            'jenis_instansi' => 'Polisi',
            'alamat' => 'Alamat Instansi Lain',
            'no_telp' => '081234567899',
            'lokasi_id' => $anotherLocationId,
        ], 'instansi_id');

        $anotherUser = Pengguna::create([
            'nama' => 'Petugas Instansi Lain',
            'no_telp' => '081234567898',
            'email' => 'petugas-lain@test.example',
            'role' => 'instansi',
            'instansi_id' => $anotherInstansiId,
        ]);

        $service = $this->createService();

        try {
            $service->updateStatus(
                $data['assignmentId'],
                $anotherUser,
                'menuju_lokasi',
                'Pengujian akses'
            );

            $this->fail('Instansi lain seharusnya tidak dapat mengakses Assignment.');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            $this->assertTrue(true);
        }
    }

    /**
     * 9. Perubahan status non-selesai tidak menjalankan
     * matching ulang.
     */
    public function test_non_completed_status_does_not_trigger_rematching(): void
    {
        $data = $this->createScenario();

        $matching = Mockery::mock(ResourceMatchingService::class);

        $matching->shouldNotReceive('rematchWaitingCases');

        $service = $this->createService($matching);

        $this->updateAssignment(
            $data,
            'menuju_lokasi',
            $service
        );

        $this->assertDatabaseHas('resource', [
            'resource_id' => $data['resourceId'],
            'status_ketersediaan' => 'sedang_ditugaskan',
        ]);
    }
}
