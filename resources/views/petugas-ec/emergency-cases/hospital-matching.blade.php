
@extends('layouts.petugas-ec')

@section('title', 'Hospital Matching - EmergencyChain')

@section('content')
@php
    $status = $referral?->status;

    $statusStyles = [
        'mencari' => 'bg-blue-100 text-blue-700',
        'diterima' => 'bg-emerald-100 text-emerald-700',
        'gagal' => 'bg-red-100 text-red-700',
    ];

    $requestStyles = [
        'menunggu' => 'bg-amber-100 text-amber-700',
        'diterima' => 'bg-emerald-100 text-emerald-700',
        'ditolak' => 'bg-red-100 text-red-700',
        'timeout' => 'bg-slate-100 text-slate-600',
    ];

    $statusLabel = match ($status) {
        'mencari' => 'Sedang Mencari Rumah Sakit',
        'diterima' => 'Rujukan Diterima',
        'gagal' => 'Rujukan Gagal',
        default => $eligibility === 'perlu_verifikasi'
            ? 'Perlu Verifikasi'
            : 'Belum Dimulai',
    };
@endphp

<div class="mx-auto max-w-7xl space-y-6">

    {{-- Heading --}}
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-red-500">
                EMERGENCY CASE / {{ $case->case_id }}
            </p>
            <h1 class="mt-2 text-2xl font-semibold text-slate-700 sm:text-3xl">
                Hospital Matching
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Pemantauan proses pencarian Rumah Sakit untuk penanganan darurat.
            </p>
        </div>

        <a href="{{ route('petugas.ec.cases.show', ['caseId' => $case->case_id]) }}"
           class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50">
            Kembali ke Detail Kasus
        </a>
    </div>

    {{-- Informasi Emergency Case --}}
    <section class="rounded-xl border border-slate-100 bg-white p-5 shadow-sm">
        <h2 class="mb-5 text-sm font-semibold text-slate-700">
            Informasi Emergency Case
        </h2>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <p class="text-xs text-slate-400">Jenis Kejadian</p>
                <p class="mt-1 text-sm font-semibold text-slate-700">
                    {{ $case->jenis_kejadian }}
                </p>
            </div>

            <div>
                <p class="text-xs text-slate-400">Status Penanganan</p>
                <p class="mt-1 text-sm font-semibold text-slate-700">
                    {{ $case->status_laporan }}
                </p>
            </div>

            <div>
                <p class="text-xs text-slate-400">Waktu Laporan</p>
                <p class="mt-1 text-sm font-semibold text-slate-700">
                    {{ \Carbon\Carbon::parse($case->waktu_laporan)->format('d/m/Y H:i') }}
                </p>
            </div>

            <div>
                <p class="text-xs text-slate-400">Lokasi Kejadian</p>
                <p class="mt-1 text-sm text-slate-700">
                    {{ $case->lokasi?->alamat ?? 'Tidak tersedia' }}
                </p>
            </div>

            <div>
                <p class="text-xs text-slate-400">Kondisi Kejadian</p>
                <p class="mt-1 whitespace-pre-wrap break-words text-sm text-slate-700">{{ $case->kondisi ?? '-' }}</p>
            </div>

            <div>
                <p class="text-xs text-slate-400">Kebutuhan Penanganan</p>
                <p class="mt-1 text-sm text-slate-700">
                    {{ $case->kebutuhan ?? '-' }}
                </p>
            </div>
        </div>
    </section>

    {{-- Status Hospital Matching --}}
    <section class="rounded-xl border border-slate-100 bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-sm font-semibold text-slate-700">
                Status Hospital Matching
            </h2>

            <span class="rounded-full px-3 py-1 text-xs font-semibold
                         {{ $statusStyles[$status] ?? 'bg-amber-100 text-amber-700' }}">
                {{ $statusLabel }}
            </span>
        </div>

        @if (!$referral)
            <div class="mt-5 rounded-lg bg-amber-50 p-4">
                <p class="text-sm font-semibold text-amber-700">
                    Belum Ada Proses Rujukan
                </p>
                <p class="mt-2 text-sm leading-relaxed text-amber-700">
                    @if ($eligibility === 'perlu_verifikasi')
                        Kebutuhan rujukan belum dapat dipastikan dari informasi
                        laporan. Diperlukan verifikasi Petugas EC.
                    @else
                        Kebutuhan Rumah Sakit terindikasi, tetapi proses
                        rujukan belum tercatat. Periksa proses otomatis
                        dan log aplikasi.
                    @endif
                </p>
            </div>
        @elseif ($status === 'mencari')
            <div class="mt-5 rounded-lg bg-blue-50 p-4">
                <p class="text-sm font-semibold text-blue-700">
                    Pencarian Rumah Sakit Sedang Berlangsung
                </p>
                <p class="mt-2 text-sm text-blue-700">
                    Sistem sedang menunggu respons dari Rumah Sakit yang
                    dipilih. Jika permintaan ditolak atau timeout,
                    sistem akan mencoba kandidat berikutnya.
                </p>
            </div>
        @elseif ($status === 'diterima')
            <div class="mt-5 rounded-lg bg-emerald-50 p-4">
                <p class="text-sm font-semibold text-emerald-700">
                    Rumah Sakit Berhasil Ditemukan
                </p>
                <p class="mt-2 text-sm text-emerald-700">
                    Permintaan rujukan telah diterima oleh Rumah Sakit.
                </p>
            </div>
        @elseif ($status === 'gagal')
            <div class="mt-5 rounded-lg bg-red-50 p-4">
                <p class="text-sm font-semibold text-red-700">
                    Proses Rujukan Gagal
                </p>
                <p class="mt-2 text-sm text-red-700">
                    {{ $referral->failure_reason ?? 'Tidak ada Rumah Sakit yang dapat menerima rujukan.' }}
                </p>
            </div>
        @endif
    </section>

    {{-- Rumah Sakit Tujuan --}}
    <section class="rounded-xl border border-slate-100 bg-white p-5 shadow-sm">
        <h2 class="mb-4 text-sm font-semibold text-slate-700">
            Rumah Sakit Tujuan
        </h2>

        @if ($referral?->acceptedHospital)
            <div class="rounded-lg border border-emerald-100 bg-emerald-50 p-4">
                <p class="text-base font-semibold text-emerald-700">
                    {{ $referral->acceptedHospital->nama_rs }}
                </p>
                <p class="mt-2 text-sm text-slate-600">
                    {{ $referral->acceptedHospital->alamat ?? 'Alamat tidak tersedia' }}
                </p>
                <p class="mt-2 text-xs font-medium text-emerald-700">
                    Status: Rujukan Diterima
                </p>
            </div>
        @else
            <p class="text-sm text-slate-500">
                Belum ada Rumah Sakit tujuan yang menerima rujukan.
            </p>
        @endif
    </section>

    {{-- Riwayat Permintaan --}}
    <section class="overflow-hidden rounded-xl border border-slate-100 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="text-sm font-semibold text-slate-700">
                Riwayat Permintaan Rumah Sakit
            </h2>
            <p class="mt-1 text-xs text-slate-400">
                Daftar Rumah Sakit yang sudah menerima permintaan dari sistem.
            </p>
        </div>

        @if ($requests->isEmpty())
            <div class="p-5 text-sm text-slate-500">
                Belum ada permintaan Rumah Sakit yang tercatat.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Urutan</th>
                            <th class="px-5 py-3">Rumah Sakit</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Waktu Permintaan</th>
                            <th class="px-5 py-3">Batas Respons</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($requests as $hospitalRequest)
                            <tr>
                                <td class="px-5 py-4 text-slate-600">
                                    {{ $hospitalRequest->attempt_order }}
                                </td>
                                <td class="px-5 py-4 font-semibold text-slate-700">
                                    {{ $hospitalRequest->rumahSakit?->nama_rs ?? 'Tidak tersedia' }}
                                </td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold
                                        {{ $requestStyles[$hospitalRequest->status] ?? 'bg-slate-100 text-slate-600' }}">
                                        {{ ucfirst($hospitalRequest->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-slate-600">
                                    {{ $hospitalRequest->requested_at?->format('d/m/Y H:i:s') ?? '-' }}
                                </td>
                                <td class="px-5 py-4 text-slate-600">
                                    {{ $hospitalRequest->expires_at?->format('d/m/Y H:i:s') ?? '-' }}
                                </td>
                            </tr>

                            @if ($hospitalRequest->rejection_reason)
                                <tr>
                                    <td colspan="5" class="bg-red-50 px-5 py-3 text-xs text-red-700">
                                        Alasan penolakan:
                                        {{ $hospitalRequest->rejection_reason }}
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <div class="flex justify-end">
        <a href="{{ route('petugas.ec.cases.hospital-matching', ['caseId' => $case->case_id]) }}"
           class="rounded-lg border border-red-200 bg-white px-5 py-3 text-sm font-semibold text-red-500 hover:bg-red-50">
            Perbarui Status
        </a>
    </div>
</div>

{{-- Polling sederhana selama referral masih diproses --}}
@if ($status === 'mencari')
    <script>
        setTimeout(() => {
            window.location.reload();
        }, 10000);
    </script>
@endif
@endsection
