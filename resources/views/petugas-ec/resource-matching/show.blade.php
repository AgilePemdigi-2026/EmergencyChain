@extends('layouts.petugas-ec')

@section('title', 'Resource Matching - EmergencyChain')

@section('content')

@php
    $caseStatusStyles = [
        'Menunggu' => 'bg-amber-100 text-amber-700',
        'Menunggu Assignment' => 'bg-orange-100 text-orange-700',
        'Perlu Verifikasi' => 'bg-red-100 text-red-600',
        'Menuju Lokasi' => 'bg-blue-100 text-blue-700',
        'Dalam Penanganan' => 'bg-amber-100 text-amber-700',
        'Selesai' => 'bg-emerald-100 text-emerald-700',
    ];

    $caseStatusStyle = $caseStatusStyles[$case->status_laporan]
        ?? 'bg-slate-100 text-slate-600';

    $matchingStyles = [
        'complete' => 'bg-emerald-100 text-emerald-700',
        'partial' => 'bg-amber-100 text-amber-700',
        'waiting' => 'bg-slate-100 text-slate-600',
        'needs_verification' => 'bg-red-100 text-red-600',
    ];

    $matchingStyle = $matchingStyles[$matchingState];

    $stepStyles = [
        'complete' => [
            'badge' => 'bg-emerald-100 text-emerald-700',
            'circle' => 'bg-emerald-500 text-white',
            'label' => 'Selesai',
        ],
        'partial' => [
            'badge' => 'bg-amber-100 text-amber-700',
            'circle' => 'bg-amber-500 text-white',
            'label' => 'Sebagian',
        ],
        'waiting' => [
            'badge' => 'bg-slate-100 text-slate-600',
            'circle' => 'bg-slate-200 text-slate-600',
            'label' => 'Menunggu',
        ],
        'needs_verification' => [
            'badge' => 'bg-red-100 text-red-600',
            'circle' => 'bg-red-500 text-white',
            'label' => 'Perlu Verifikasi',
        ],
    ];

    $waktuLaporan = \Carbon\Carbon::parse($case->waktu_laporan);
@endphp

<div class="mx-auto max-w-7xl">

    {{-- Heading --}}
    <div class="mb-6 flex flex-col justify-between gap-4
                sm:flex-row sm:items-start">

        <div class="min-w-0">
            <p class="text-xs font-bold uppercase tracking-widest
                      text-blue-500">
                EMERGENCY CASE / {{ $case->case_id }} / RESOURCE MATCHING
            </p>

            <div class="mt-3 flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-semibold text-slate-700 sm:text-3xl">
                    Resource Matching
                </h1>

                <span class="rounded-full px-3 py-1 text-xs
                             font-semibold {{ $matchingStyle }}">
                    {{ $matchingStatusLabel }}
                </span>
            </div>

            <p class="mt-2 text-sm text-slate-500">
                Hasil pencocokan Resource otomatis untuk Emergency Case.
            </p>
        </div>

        <a
            href="{{ route('petugas.ec.cases.show', [
                'caseId' => $case->case_id
            ]) }}"
            class="inline-flex items-center justify-center rounded-xl
                   border border-slate-200 bg-white px-6 py-3
                   text-sm font-semibold text-slate-700
                   hover:bg-slate-50"
        >
            Kembali ke Detail
        </a>

    </div>

    {{-- Informasi Ringkas --}}
    <section class="mb-6 grid grid-cols-2 overflow-hidden
                    rounded-xl border border-red-100 bg-rose-50
                    lg:grid-cols-4">

        <div class="border-b border-red-100 px-4 py-4
                    lg:border-b-0 lg:border-r">

            <p class="text-xs text-slate-400">
                ID Emergency Case
            </p>

            <p class="mt-1 text-sm font-semibold text-slate-700">
                #{{ $case->case_id }}
            </p>
        </div>

        <div class="border-b border-l border-red-100
                    px-4 py-4 lg:border-b-0 lg:border-l-0
                    lg:border-r">

            <p class="text-xs text-slate-400">
                Jenis Kejadian
            </p>

            <p class="mt-1 text-sm font-semibold text-slate-700">
                {{ $case->jenis_kejadian }}
            </p>
        </div>

        <div class="px-4 py-4 lg:border-r lg:border-red-100">

            <p class="text-xs text-slate-400">
                Waktu Laporan
            </p>

            <p class="mt-1 text-sm font-semibold text-slate-700">
                {{ $waktuLaporan->format('d/m/Y H:i') }}
            </p>
        </div>

        <div class="border-l border-red-100 px-4 py-4
                    lg:border-l-0">

            <p class="text-xs text-slate-400">
                Status Emergency Case
            </p>

            <span class="mt-1 inline-flex rounded-full px-3 py-1
                         text-xs font-semibold {{ $caseStatusStyle }}">
                {{ $case->status_laporan }}
            </span>
        </div>

    </section>

    <div class="grid grid-cols-1 items-start gap-4
                xl:grid-cols-[minmax(0,1fr)_340px]">

        {{-- Kolom Kiri --}}
        <div class="min-w-0 space-y-4">

            {{-- Informasi Kasus --}}
            <section class="overflow-hidden rounded-xl border
                            border-slate-100 bg-white shadow-sm">

                <div class="border-b border-slate-100 px-5 py-4">

                    <h2 class="text-sm font-semibold text-slate-700">
                        Informasi Kasus
                    </h2>

                </div>

                <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2">

                    <div>
                        <p class="text-xs text-slate-400">
                            Nama Pelapor
                        </p>

                        <p class="mt-1 text-sm font-medium text-slate-700">
                            {{ $case->pengguna?->nama ?? 'Tidak tersedia' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-400">
                            Jenis Kejadian
                        </p>

                        <p class="mt-1 text-sm font-medium text-slate-700">
                            {{ $case->jenis_kejadian }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-400">
                            Kondisi Kejadian
                        </p>

                        <p class="mt-1 whitespace-pre-wrap break-words
                                  text-sm text-slate-700">
                            {{ $case->kondisi }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-400">
                            Kebutuhan Penanganan
                        </p>

                        <p class="mt-1 text-sm font-medium text-slate-700">
                            {{ $case->kebutuhan }}
                        </p>
                    </div>

                    <div class="sm:col-span-2">

                        <p class="text-xs text-slate-400">
                            Lokasi Kejadian
                        </p>

                        <p class="mt-1 text-sm font-medium text-slate-700">
                            {{ $case->lokasi?->alamat ?? 'Tidak tersedia' }}
                        </p>

                        @if ($case->lokasi)
                            <p class="mt-1 text-xs text-slate-400">
                                Koordinat:
                                {{ $case->lokasi->latitude }},
                                {{ $case->lokasi->longitude }}
                            </p>
                        @endif

                    </div>

                </div>

            </section>

            {{-- Resource yang Dibutuhkan --}}
            <section class="overflow-hidden rounded-xl border
                            border-slate-100 bg-white shadow-sm">

                <div class="border-b border-slate-100 px-5 py-4">

                    <h2 class="text-sm font-semibold text-slate-700">
                        Resource yang Dibutuhkan
                    </h2>

                    <p class="mt-1 text-xs text-slate-400">
                        Kebutuhan ditentukan berdasarkan jenis kejadian.
                    </p>

                </div>

                <div class="p-5">

                    @if ($requiredTypes === null)

                        <div class="rounded-lg border border-red-100
                                    bg-red-50 p-4">

                            <p class="text-sm font-semibold text-red-700">
                                Jenis kejadian memerlukan verifikasi.
                            </p>

                            <p class="mt-1 text-xs leading-relaxed
                                      text-red-600">
                                Sistem belum memiliki pemetaan kebutuhan
                                Resource untuk jenis kejadian ini.
                            </p>

                        </div>

                    @else

                        <div class="flex flex-wrap gap-2">

                            @foreach ($requiredTypes as $type)

                                @php
                                    $isAssigned = in_array(
                                        $type,
                                        $assignments
                                            ->pluck('resource.jenis_resource')
                                            ->filter()
                                            ->all()
                                    );
                                @endphp

                                <span class="inline-flex items-center
                                             gap-2 rounded-lg border
                                             px-3 py-2 text-xs font-semibold
                                             {{ $isAssigned
                                                ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                                : 'border-amber-200 bg-amber-50 text-amber-700' }}">

                                    {{ $type }}

                                    <span>
                                        {{ $isAssigned
                                            ? '✓'
                                            : 'Menunggu' }}
                                    </span>

                                </span>

                            @endforeach

                        </div>

                    @endif

                </div>

            </section>

            {{-- Resource Ditugaskan --}}
            <section class="overflow-hidden rounded-xl border
                            border-slate-100 bg-white shadow-sm">

                <div class="flex flex-wrap items-center
                            justify-between gap-3
                            border-b border-slate-100 px-5 py-4">

                    <div>
                        <h2 class="text-sm font-semibold text-slate-700">
                            Resource Ditugaskan
                        </h2>

                        <p class="mt-1 text-xs text-slate-400">
                            Hasil Assignment yang tersimpan dalam sistem.
                        </p>
                    </div>

                    <span class="rounded-full bg-blue-50 px-3 py-1
                                 text-xs font-semibold text-blue-600">
                        {{ $assignments->count() }} Unit
                    </span>

                </div>

                <div class="space-y-4 p-5">

                    @forelse ($assignments as $assignment)

                        @php
                            $resource = $assignment->resource;
                            $latestStatus = $assignment
                                ->statusPenanganan
                                ->first();

                            $statusValue = $latestStatus?->status
                                ?? $assignment->status_assign;

                            $statusLabels = [
                                'diterima' => 'Diterima',
                                'menuju_lokasi' => 'Menuju Lokasi',
                                'dalam_penanganan' => 'Dalam Penanganan',
                                'selesai' => 'Selesai',
                                'ditolak' => 'Ditolak',
                                'dibatalkan' => 'Dibatalkan',
                            ];

                            $statusStyles = [
                                'diterima' => 'bg-blue-100 text-blue-700',
                                'menuju_lokasi' => 'bg-blue-100 text-blue-700',
                                'dalam_penanganan' => 'bg-amber-100 text-amber-700',
                                'selesai' => 'bg-emerald-100 text-emerald-700',
                                'ditolak' => 'bg-red-100 text-red-700',
                                'dibatalkan' => 'bg-slate-100 text-slate-600',
                            ];

                            $statusLabel = $statusLabels[$statusValue]
                                ?? ucfirst(str_replace('_', ' ', $statusValue));

                            $statusClass = $statusStyles[$statusValue]
                                ?? 'bg-slate-100 text-slate-600';
                        @endphp

                        <div class="rounded-xl border border-slate-100
                                    bg-white p-4">

                            <div class="flex flex-col justify-between
                                        gap-3 sm:flex-row sm:items-start">

                                <div class="min-w-0">

                                    <p class="text-xs font-semibold
                                              uppercase tracking-wide
                                              text-blue-500">
                                        {{ $resource?->jenis_resource
                                            ?? 'Resource' }}
                                    </p>

                                    <h3 class="mt-1 text-base font-semibold
                                               text-slate-700">
                                        {{ $resource?->nama_resource
                                            ?? 'Resource tidak tersedia' }}
                                    </h3>

                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $resource?->instansi?->nama_instansi
                                            ?? 'Instansi tidak tersedia' }}
                                    </p>

                                </div>

                                <span class="inline-flex w-fit shrink-0
                                             rounded-full px-3 py-1
                                             text-xs font-semibold
                                             {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>

                            </div>

                            <div class="mt-4 grid grid-cols-1 gap-3
                                        border-t border-slate-100 pt-4
                                        sm:grid-cols-2">

                                <div>
                                    <p class="text-xs text-slate-400">
                                        Jarak dari Lokasi Kejadian
                                    </p>

                                    <p class="mt-1 text-sm font-semibold
                                              text-slate-700">

                                        @if ($assignment->jarak_km !== null)

                                            {{ number_format(
                                                $assignment->jarak_km,
                                                2,
                                                ',',
                                                '.'
                                            ) }} km

                                        @else

                                            Tidak tersedia

                                        @endif

                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-slate-400">
                                        Waktu Assignment
                                    </p>

                                    <p class="mt-1 text-sm font-semibold
                                              text-slate-700">
                                        {{ \Carbon\Carbon::parse(
                                            $assignment->waktu_assign
                                        )->format('d/m/Y H:i') }}
                                    </p>
                                </div>

                                <div class="sm:col-span-2">

                                    <p class="text-xs text-slate-400">
                                        Lokasi Pangkalan
                                    </p>

                                    <p class="mt-1 text-sm text-slate-700">
                                        {{ $resource?->lokasiPangkalan?->alamat
                                            ?? 'Tidak tersedia' }}
                                    </p>

                                </div>

                            </div>

                            @if ($latestStatus)

                                <div class="mt-4 rounded-lg bg-slate-50 p-3">

                                    <p class="text-xs font-semibold
                                              text-slate-600">
                                        Informasi Status Terakhir
                                    </p>

                                    <p class="mt-1 text-xs leading-relaxed
                                              text-slate-500">
                                        {{ $latestStatus->keterangan }}
                                    </p>

                                    <p class="mt-2 text-[11px] text-slate-400">
                                        Diperbarui:
                                        {{ \Carbon\Carbon::parse(
                                            $latestStatus->waktu_update
                                        )->format('d/m/Y H:i') }}
                                    </p>

                                </div>

                            @endif

                        </div>

                    @empty

                        <div class="rounded-xl border border-dashed
                                    border-slate-200 bg-slate-50
                                    px-5 py-8 text-center">

                            <p class="text-sm font-semibold text-slate-600">
                                Belum ada Resource yang ditugaskan.
                            </p>

                            <p class="mt-2 text-xs leading-relaxed
                                      text-slate-400">
                                Assignment akan ditampilkan setelah
                                sistem berhasil menemukan Resource tersedia.
                            </p>

                        </div>

                    @endforelse

                    {{-- Resource yang belum tersedia --}}
                    @if (!empty($missingTypes))

                        <div class="rounded-xl border border-amber-200
                                    bg-amber-50 p-4">

                            <p class="text-sm font-semibold text-amber-800">
                                Resource Belum Tersedia
                            </p>

                            <p class="mt-1 text-xs text-amber-700">
                                Sistem belum menemukan Resource tersedia
                                untuk kebutuhan berikut:
                            </p>

                            <ul class="mt-2 list-disc space-y-1 pl-5
                                       text-xs font-medium text-amber-800">

                                @foreach ($missingTypes as $type)
                                    <li>{{ $type }}</li>
                                @endforeach

                            </ul>

                        </div>

                    @elseif ($requiredTypes !== null && $assignments->isNotEmpty())

                        <div class="rounded-lg border border-emerald-100
                                    bg-emerald-50 p-3">

                            <p class="text-sm font-semibold text-emerald-700">
                                Seluruh kebutuhan Resource telah terpenuhi.
                            </p>

                        </div>

                    @endif

                </div>

            </section>

        </div>

        {{-- Kolom Kanan --}}
        <aside class="space-y-4">

            {{-- Proses Resource Matching --}}
            <section class="rounded-xl border border-slate-100
                            bg-white p-5 shadow-sm">

                <h2 class="text-sm font-semibold text-slate-700">
                    Proses Resource Matching
                </h2>

                <p class="mt-2 text-xs leading-relaxed text-slate-400">
                    Tahapan sistem dalam menentukan dan menugaskan Resource.
                </p>

                <div class="mt-6 space-y-6">

                    @foreach ($processSteps as $index => $step)

                        @php
                            $stepStyle = $stepStyles[$step['state']];
                        @endphp

                        <div class="relative flex gap-3">

                            @if (!$loop->last)
                                <div class="absolute left-4 top-9
                                            h-full w-px bg-slate-200">
                                </div>
                            @endif

                            <div class="relative z-10 flex h-8 w-8
                                        shrink-0 items-center justify-center
                                        rounded-full text-xs font-bold
                                        {{ $stepStyle['circle'] }}">
                                {{ $index + 1 }}
                            </div>

                            <div class="min-w-0 flex-1">

                                <div class="flex flex-wrap
                                            items-center justify-between gap-2">

                                    <h3 class="text-sm font-semibold text-slate-700">
                                        {{ $step['title'] }}
                                    </h3>

                                    <span class="rounded-full px-2 py-1
                                                text-[10px] font-semibold
                                                {{ $stepStyle['badge'] }}">
                                        {{ $stepStyle['label'] }}
                                    </span>

                                </div>

                                <p class="mt-2 text-xs leading-relaxed
                                        text-slate-500">
                                    {{ $step['description'] }}
                                </p>

                            </div>

                        </div>

                    @endforeach

                </div>

            </section>

        </aside>


    </div>

</div>

@endsection
