
@extends('layouts.petugas-ec')

@section('title', 'Detail Emergency Case - EmergencyChain')

@section('content')
@php
    $statusColors = [
        'Menunggu' => 'bg-amber-100 text-amber-700',
        'Dalam Penanganan' => 'bg-red-100 text-red-600',
        'Selesai' => 'bg-emerald-100 text-emerald-700',
    ];

    $statusStyle = $statusColors[$case->status_laporan]
        ?? 'bg-slate-100 text-slate-600';

    $waktuLaporan = \Carbon\Carbon::parse($case->waktu_laporan);

    $lokasi = $case->lokasi;
    $pelapor = $case->pengguna;
@endphp

<div class="mx-auto max-w-7xl">

    {{-- Heading --}}
    <div class="mb-6 flex flex-col justify-between gap-4
                sm:flex-row sm:items-start">

        <div class="min-w-0">
            <p class="text-xs font-bold uppercase tracking-widest
                      text-blue-500">
                EMERGENCY CASE / {{ $case->case_id }}
            </p>

            <div class="mt-3 flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-semibold text-slate-700
                           sm:text-3xl">
                    {{ $case->jenis_kejadian }}
                </h1>

                <span class="rounded-full px-3 py-1 text-xs
                             font-semibold {{ $statusStyle }}">
                    ● {{ $case->status_laporan }}
                </span>
            </div>
        </div>

        <a
            href="{{ route('petugas.ec.cases.index') }}"
            class="inline-flex items-center justify-center rounded-xl
                   border border-slate-200 bg-white px-6 py-3
                   text-sm font-semibold text-slate-700
                   hover:bg-slate-50"
        >
            Kembali
        </a>
    </div>

    {{-- Informasi Ringkas --}}
    <section class="mb-6 grid grid-cols-2 overflow-hidden
                    rounded-xl border border-red-100 bg-rose-50
                    lg:grid-cols-4">

        <div class="border-b border-red-100 px-4 py-4
                    lg:border-b-0 lg:border-r">
            <p class="text-xs text-slate-400">
                Waktu Sejak Laporan
            </p>
            <p class="mt-1 text-sm font-semibold text-slate-700">
                {{ $waktuLaporan->diffForHumans() }}
            </p>
        </div>

        <div class="border-b border-l border-red-100
                    px-4 py-4 lg:border-b-0 lg:border-l-0
                    lg:border-r">
            <p class="text-xs text-slate-400">
                Waktu Laporan
            </p>
            <p class="mt-1 text-sm font-semibold text-slate-700">
                {{ $waktuLaporan->format('d/m/Y H:i') }}
            </p>
        </div>

        <div class="px-4 py-4 lg:border-r lg:border-red-100">
            <p class="text-xs text-slate-400">
                Pelapor
            </p>
            <p class="mt-1 text-sm font-semibold text-slate-700">
                {{ $pelapor?->nama ?? 'Tidak tersedia' }}
            </p>
        </div>

        <div class="border-l border-red-100 px-4 py-4
                    lg:border-l-0">
            <p class="text-xs text-slate-400">
                Lokasi
            </p>
            <p class="mt-1 break-words text-sm font-semibold
                      text-slate-700">
                {{ $lokasi?->alamat ?? 'Tidak tersedia' }}
            </p>
        </div>
    </section>

    <div class="grid grid-cols-1 items-start gap-4
                xl:grid-cols-[minmax(0,1fr)_300px]">

        {{-- Kolom Kiri --}}
        <div class="min-w-0 space-y-4">

            {{-- Informasi Kejadian --}}
            <section class="overflow-hidden rounded-xl border
                            border-slate-100 bg-white shadow-sm">

                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="text-sm font-semibold text-slate-700">
                        Informasi Kejadian
                    </h2>
                </div>

                <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2">

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
                            Status Laporan
                        </p>
                        <span class="mt-1 inline-flex rounded-full
                                     px-3 py-1 text-xs font-semibold
                                     {{ $statusStyle }}">
                            ● {{ $case->status_laporan }}
                        </span>
                    </div>

                    <div>
                        <p class="text-xs text-slate-400">
                            Kondisi Kejadian
                        </p>
                        <p class="mt-1 whitespace-pre-wrap break-words
                                  text-sm text-slate-700">{{ $case->kondisi }}</p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-400">
                            Kebutuhan Penanganan
                        </p>
                        <p class="mt-1 whitespace-pre-wrap break-words
                                  text-sm text-slate-700">{{ $case->kebutuhan }}</p>
                    </div>

                </div>
            </section>

            {{-- Lokasi Kejadian --}}
            <section class="overflow-hidden rounded-xl border
                            border-slate-100 bg-white shadow-sm">

                <div class="flex flex-wrap items-center
                            justify-between gap-2 px-5 py-4">

                    <h2 class="text-sm font-semibold text-slate-700">
                        Lokasi Kejadian
                    </h2>

                    <p class="text-xs text-slate-400">
                        @if ($lokasi)
                            {{ $lokasi->latitude }},
                            {{ $lokasi->longitude }}
                        @else
                            Koordinat belum tersedia
                        @endif
                    </p>
                </div>

                {{-- Peta Leaflet --}}
                <div
                    id="detail-location-map"
                    class="relative z-0 h-64 w-full bg-slate-100
                           sm:h-80"
                    aria-label="Peta lokasi kejadian"
                    @if ($lokasi)
                        data-latitude="{{ $lokasi->latitude }}"
                        data-longitude="{{ $lokasi->longitude }}"
                    @endif
                ></div>

                <div class="border-t border-slate-100 px-5 py-4">
                    <p class="text-xs text-slate-400">
                        Alamat Kejadian
                    </p>
                    <p class="mt-1 text-sm font-semibold
                              text-slate-700">
                        {{ $lokasi?->alamat ?? 'Tidak tersedia' }}
                    </p>
                </div>
            </section>
        </div>

        {{-- Kolom Kanan --}}
        <aside class="space-y-4">

            {{-- Pengelolaan Emergency Case --}}
            <section class="rounded-xl border border-slate-100
                            bg-white p-5 shadow-sm">

                <h2 class="text-sm font-semibold text-slate-700">
                    Kelola Emergency Case
                </h2>

                <p class="mt-2 text-xs leading-relaxed text-slate-500">
                    Petugas EC dapat memeriksa informasi laporan.
                    Koreksi kondisi dan kebutuhan penanganan
                    akan tersedia pada tahap integrasi Update.
                </p>
            </section>

            {{-- Resource --}}
            <section class="rounded-xl border border-slate-100
                            bg-white p-5 shadow-sm">

                <h2 class="text-sm font-semibold text-slate-700">
                    Resource Ditugaskan
                </h2>

                <p class="mt-3 text-sm font-medium text-slate-500">
                    Belum ditugaskan
                </p>

                <p class="mt-2 text-xs text-slate-400">
                    Informasi resource akan tersedia setelah
                    proses Assignment diintegrasikan.
                </p>
            </section>

            {{-- Rumah Sakit --}}
            <section class="rounded-xl border border-slate-100
                            bg-white p-5 shadow-sm">

                <h2 class="text-sm font-semibold text-slate-700">
                    Rumah Sakit Tujuan
                </h2>

                <p class="mt-3 text-sm font-medium text-slate-500">
                    Belum ditentukan
                </p>

                <p class="mt-2 text-xs text-slate-400">
                    Informasi rumah sakit akan tersedia
                    setelah Hospital Matching diintegrasikan.
                </p>
            </section>
        </aside>

    </div>
</div>
@endsection
