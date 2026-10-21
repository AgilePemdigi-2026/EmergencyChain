<?php

use Illuminate\Support\Facades\Route;

// Halaman utama
Route::get('/', function () {
    return redirect('/login');
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
            'id' => 'EC-2026-0011',
        ],
    ];

    abort_unless(isset($cases[$caseId]), 404);

    return view('petugas-ec.emergency-cases.show', [
        'case' => $cases[$caseId],
    ]);
});
