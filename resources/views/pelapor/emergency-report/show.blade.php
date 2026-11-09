
@extends('layouts.pelapor')

@section('title', 'Detail Laporan - EmergencyChain')

@section('content')
@php
    $waktu = $case->waktu_laporan
        ? \Illuminate\Support\Carbon::parse($case->waktu_laporan)
        : null;

    $nomorKasus = sprintf(
        'EC-%s-%04d',
        $waktu?->format('Y') ?? '0000',
        $case->case_id
    );

    $status = $case->status_laporan ?? '-';

    $statusClass = match (mb_strtolower($status)) {
        'selesai' => 'bg-emerald-50 text-emerald-700',
        'menunggu' => 'bg-amber-50 text-amber-700',
        'diproses', 'dalam proses' => 'bg-blue-50 text-blue-700',
        default => 'bg-slate-100 text-slate-600',
    };
@endphp

<div class="mx-auto max-w-6xl">

    <div class="mb-6">
        <a href="{{ route('pelapor.laporan.index') }}"
           class="inline-flex items-center gap-2 text-sm
                  font-medium text-red-500 hover:text-red-600">
            ← Kembali ke Status Laporan
        </a>

        <p class="mt-6 text-[11px] font-bold tracking-widest text-blue-500">
            PELAPOR / DETAIL LAPORAN
        </p>

        <div class="mt-2 flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-semibold tracking-tight sm:text-3xl">
                Detail Emergency Case
            </h1>

            <span class="rounded-full px-4 py-2 text-xs font-semibold
                         {{ $statusClass }}">
                {{ $status }}
            </span>
        </div>

        <p class="mt-2 text-sm text-slate-500">
            {{ $nomorKasus }}
        </p>
    </div>

    <section class="rounded-xl border border-slate-100 bg-white
                    p-5 shadow-sm sm:p-6">

        <h2 class="text-base font-semibold">
            Informasi Laporan
        </h2>

        <div class="mt-6 grid gap-6 sm:grid-cols-2">

            <div>
                <p class="text-xs text-slate-400">Case ID</p>
                <p class="mt-1 font-semibold">{{ $nomorKasus }}</p>
            </div>

            <div>
                <p class="text-xs text-slate-400">Jenis Kejadian</p>
                <p class="mt-1 font-medium">
                    {{ $case->jenis_kejadian }}
                </p>
            </div>

            <div>
                <p class="text-xs text-slate-400">Waktu Laporan</p>
                <p class="mt-1 font-medium">
                    {{ $waktu?->format('d/m/Y H:i') ?? '-' }}
                </p>
            </div>

            <div>
                <p class="text-xs text-slate-400">Status Laporan</p>
                <p class="mt-1 font-medium">{{ $status }}</p>
            </div>

            <div class="sm:col-span-2">
                <p class="text-xs text-slate-400">Lokasi Kejadian</p>
                <p class="mt-1 font-medium">
                    {{ $case->lokasi?->alamat ?? '-' }}
                </p>
            </div>

            <div class="sm:col-span-2">
                <p class="text-xs text-slate-400">Kondisi Kejadian</p>
                <p class="mt-1 whitespace-pre-line font-medium">
                    {{ $case->kondisi ?? '-' }}
                </p>
            </div>

            <div class="sm:col-span-2">
                <p class="text-xs text-slate-400">Kebutuhan Penanganan</p>
                <p class="mt-1 font-medium">
                    {{ $case->kebutuhan ?? '-' }}
                </p>
            </div>
        </div>
    </section>

    <section class="mt-5 rounded-xl border border-slate-100
                    bg-white p-5 shadow-sm sm:p-6">

        <h2 class="text-base font-semibold">
            Informasi Rujukan Rumah Sakit
        </h2>

        @if (!$case->hospitalReferral)
            <p class="mt-4 text-sm text-slate-500">
                Belum ada informasi rujukan Rumah Sakit.
            </p>
        @else
            @php
                $referral = $case->hospitalReferral;

                $referralLabel = match ($referral->status) {
                    'mencari' => 'Sedang Diproses',
                    'diterima' => 'Diterima',
                    'gagal' => 'Gagal',
                    default => $referral->status,
                };
            @endphp

            <div class="mt-4 space-y-3 text-sm">
                <p>
                    <span class="text-slate-400">Status Rujukan:</span>
                    <span class="ml-2 font-semibold">
                        {{ $referralLabel }}
                    </span>
                </p>

                @if ($referral->status === 'diterima')
                    <p>
                        <span class="text-slate-400">Rumah Sakit Tujuan:</span>
                        <span class="ml-2 font-semibold">
                            {{ $referral->acceptedHospital?->nama_rs ?? '-' }}
                        </span>
                    </p>
                @elseif ($referral->status === 'gagal')
                    <p class="text-red-600">
                        {{ $referral->failure_reason
                            ?? 'Proses rujukan belum berhasil.' }}
                    </p>
                @endif
            </div>
        @endif
    </section>

</div>
@endsection
