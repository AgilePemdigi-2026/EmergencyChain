
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
<<<<<<< HEAD
    return redirect('/login');
});

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'register'])
        ->name('register.store');

    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');
});
=======
    return view('welcome');
});

// Route berdasarkan modul
require __DIR__.'/auth.php';
require __DIR__.'/pelapor.php';
require __DIR__.'/petugas-ec.php';


// Preview detail Emergency Case.
// Data contoh sementara sampai backend tersedia.
Route::get('/petugas-ec/emergency-cases/{caseId}', function (string $caseId) {
    $cases = [
        'EC-2026-0011' => [
            'jenis' => 'Kecelakaan Lalu Lintas',
            'lokasi' => 'Jl. Kartini Timur',
            'waktu' => 'Hari ini, 11.55',
            'prioritas' => 'Kritis',
            'status' => 'Dalam Penanganan',
            'resource' => 'Ambulans 07',
        ],
        'EC-2026-0010' => [
            'jenis' => 'Kecelakaan Lalu Lintas',
            'lokasi' => 'Jl. Ahmad Yani',
            'waktu' => 'Hari ini, 11.40',
            'prioritas' => 'Tinggi',
            'status' => 'Menunggu',
            'resource' => '-',
        ],
        'EC-2026-0009' => [
            'jenis' => 'Kebakaran',
            'lokasi' => 'Jl. Diponegoro',
            'waktu' => 'Hari ini, 10.10',
            'prioritas' => 'Tinggi',
            'status' => 'Dalam Penanganan',
            'resource' => 'Damkar 02',
        ],
        'EC-2026-0008' => [
            'jenis' => 'Kondisi Medis Darurat',
            'lokasi' => 'Jl. Raya Darmo',
            'waktu' => 'Hari ini, 09.53',
            'prioritas' => 'Tinggi',
            'status' => 'Dalam Penanganan',
            'resource' => 'Ambulans 05',
        ],
        'EC-2026-0007' => [
            'jenis' => 'Kecelakaan Lalu Lintas',
            'lokasi' => 'Jl. Keninting',
            'waktu' => 'Hari ini, 09.23',
            'prioritas' => 'Sedang',
            'status' => 'Selesai',
            'resource' => 'Ambulans 03',
        ],
        'EC-2026-0006' => [
            'jenis' => 'Kondisi Medis Darurat',
            'lokasi' => 'Jl. Gebang',
            'waktu' => 'Hari ini, 09.20',
            'prioritas' => 'Kritis',
            'status' => 'Selesai',
            'resource' => 'Ambulans 01',
        ],
        'EC-2026-0005' => [
            'jenis' => 'Pohon Tumbang',
            'lokasi' => 'Jl. Ngagel',
            'waktu' => 'Hari ini, 08.01',
            'prioritas' => 'Sedang',
            'status' => 'Selesai',
            'resource' => 'BPBD 01',
        ],
        'EC-2026-0004' => [
            'jenis' => 'Kecelakaan Lalu Lintas',
            'lokasi' => 'Jl. Mulyosari',
            'waktu' => 'Hari ini, 07.56',
            'prioritas' => 'Tinggi',
            'status' => 'Selesai',
            'resource' => 'Ambulans 07',
        ],
        'EC-2026-0003' => [
            'jenis' => 'Kecelakaan Lalu Lintas',
            'lokasi' => 'Jl. Kenjeran',
            'waktu' => 'Hari ini, 06.36',
            'prioritas' => 'Sedang',
            'status' => 'Selesai',
            'resource' => 'Ambulans 03',
        ],
    ];

    abort_unless(isset($cases[$caseId]), 404);


    $case = array_merge([
        'id' => $caseId,
        'waktu_sejak' => 'Belum tersedia',
        'waktu_laporan' => $cases[$caseId]['waktu'],
        'pelapor' => 'Belum tersedia',
        'kebutuhan' => 'Belum tersedia pada data simulasi',
        'kondisi' => 'Detail kondisi kejadian belum tersedia.',
        'jumlah_korban' => 'Belum tersedia',
        'tambahan' => 'Tidak ada informasi tambahan.',
        'patokan' => 'Belum tersedia',
        'resource_detail' => 'Informasi penugasan belum tersedia',
        'rumah_sakit' => 'Belum ditentukan',
        'rumah_sakit_detail' => 'Belum tersedia',
    ], $cases[$caseId]);

    // Data contoh khusus untuk menyesuaikan tampilan desain 4.3.
    // Bukan data kejadian nyata.
    if ($caseId === 'EC-2026-0010') {
        $case = array_merge($case, [
            'waktu_sejak' => '00:15:18 (contoh)',
            'waktu_laporan' => '00:15:18 (contoh)',
            'pelapor' => 'A.A. Sadya Reksa',
            'kebutuhan' => 'Ambulans & penanganan lalu lintas',
            'kondisi' => 'Tabrakan mobil dan sepeda motor',
            'jumlah_korban' => '1 korban, sadar',
            'tambahan' => 'Korban mengalami luka di bagian kaki dan membutuhkan pemeriksaan medis segera. Lalu lintas di lokasi cukup padat.',
            'resource' => 'Ambulans 01 — PSC 119',
            'resource_detail' => 'Penugasan simulasi',
            'rumah_sakit' => 'RSUP Fatmawati',
            'rumah_sakit_detail' => 'Tujuan simulasi',
        ]);
    }


    return view('petugas-ec.emergency-cases.show', compact('case'));
})->name('petugas.ec.cases.show');
>>>>>>> origin/main
