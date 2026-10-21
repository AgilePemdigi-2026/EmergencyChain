
@extends('layouts.petugas-ec')

@section('title', 'Detail Emergency Case - EmergencyChain')

@section('content')
@php
    $priorityColors = [
        'Kritis' => 'bg-red-100 text-red-600',
        'Tinggi' => 'bg-amber-100 text-amber-700',
        'Sedang' => 'bg-blue-100 text-blue-600',
    ];

    $statusColors = [
        'Menunggu' => 'bg-amber-100 text-amber-700',
        'Dalam Penanganan' => 'bg-red-100 text-red-600',
        'Selesai' => 'bg-emerald-100 text-emerald-700',
    ];

    $prioritasStyle = $priorityColors[$case['prioritas']] ?? 'bg-slate-100 text-slate-600';
    $statusStyle = $statusColors[$case['status']] ?? 'bg-slate-100 text-slate-600';
@endphp

<div class="mx-auto max-w-7xl">

    {{-- Heading sesuai desain 4.3 --}}
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-start">

        <div class="min-w-0">
            <p class="text-xs font-bold uppercase tracking-[0.13em] text-blue-500">
                EMERGENCY CASE / {{ $case['id'] }}
            </p>

            <div class="mt-3 flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-medium tracking-tight text-slate-700 sm:text-3xl">
                    {{ $case['jenis'] }}
                </h1>

                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $prioritasStyle }}">
                    ● {{ $case['prioritas'] }}
                </span>

                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusStyle }}">
                    ● {{ $case['status'] }}
                </span>
            </div>
        </div>

        <a
            href="{{ route('petugas.ec.cases.index') }}"
            class="inline-flex shrink-0 items-center justify-center
                   self-start rounded-xl border border-slate-200
                   bg-white px-6 py-3 text-sm font-semibold
                   text-slate-700 hover:bg-slate-50"
        >
            Kembali
        </a>
    </div>

    {{-- Empat informasi ringkas --}}
    <section class="mb-6 grid grid-cols-2 overflow-hidden
                    rounded-xl border border-red-100 bg-rose-50
                    lg:grid-cols-4">

        <div class="bg-rose-50 px-4 py-4 sm:px-6 lg:border-r lg:border-red-100">
            <p class="text-xs text-slate-400">Waktu sejak laporan</p>
            <p class="mt-1 text-sm font-semibold text-slate-700">
                {{ $case['waktu_sejak'] ?? 'Belum tersedia' }}
            </p>
        </div>

        <div class="bg-rose-50 px-4 py-4 sm:px-6 border-l border-red-100 lg:border-l-0 lg:border-r lg:border-red-100">
            <p class="text-xs text-slate-400">Waktu Laporan</p>
            <p class="mt-1 text-sm font-semibold text-slate-700">
                {{ $case['waktu_laporan'] ?? $case['waktu'] }}
            </p>
        </div>

        <div class="bg-rose-50 px-4 py-4 sm:px-6 border-t border-red-100 lg:border-t-0 lg:border-r lg:border-red-100">
            <p class="text-xs text-slate-400">Pelapor</p>
            <p class="mt-1 text-sm font-semibold text-slate-700">
                {{ $case['pelapor'] ?? 'Belum tersedia' }}
            </p>
        </div>

        <div class="bg-rose-50 px-4 py-4 sm:px-6 border-l border-t border-red-100 lg:border-l-0 lg:border-t-0">
            <p class="text-xs text-slate-400">Lokasi</p>
            <p class="mt-1 text-sm font-semibold text-slate-700">
                {{ $case['lokasi'] }}
            </p>
        </div>

    </section>

    {{-- Area konten --}}
    <div class="grid grid-cols-1 items-start gap-4
                xl:grid-cols-[minmax(0,1fr)_300px]">

        {{-- Kolom kiri --}}
        <div class="min-w-0 space-y-4">

            {{-- Informasi Kejadian --}}
            <section class="overflow-hidden rounded-xl border
                            border-slate-100 bg-white shadow-sm">

                <div class="flex flex-wrap items-center justify-between
                            gap-3 border-b border-slate-100 px-5 py-4">
                    <h2 class="text-sm font-semibold text-slate-700">
                        Informasi Kejadian
                    </h2>

                    {{-- Menunggu fitur koreksi sesuai skenario --}}
                    <span class="text-xs font-semibold text-blue-500">
                        Kelola Emergency Case
                    </span>
                </div>

                <div class="p-5">
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <p class="text-xs text-slate-400">
                                Kondisi Kejadian
                            </p>
                            <p class="mt-1 text-sm font-medium text-slate-700">
                                {{ $case['kondisi'] }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400">
                                Kebutuhan Penanganan
                            </p>
                            <p class="mt-1 text-sm font-medium text-slate-700">
                                {{ $case['kebutuhan'] }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400">
                                Jumlah Korban
                            </p>
                            <p class="mt-1 text-sm font-medium text-slate-700">
                                {{ $case['jumlah_korban'] ?? 'Belum tersedia' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400">
                                Current Status
                            </p>
                            <span class="mt-1 inline-flex rounded-full
                                         px-3 py-1 text-xs font-semibold
                                         {{ $statusStyle }}">
                                ● {{ $case['status'] }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-5 rounded-lg bg-rose-50 p-4">
                        <p class="text-xs leading-relaxed text-slate-500">
                            {{ $case['tambahan'] }}
                        </p>
                    </div>
                </div>
            </section>

            {{-- Lokasi Kejadian --}}
            <section class="overflow-hidden rounded-xl border
                            border-slate-100 bg-white shadow-sm">

                <div class="flex flex-wrap items-center justify-between
                            gap-2 px-5 py-4">
                    <h2 class="text-sm font-semibold text-slate-700">
                        Lokasi Kejadian
                    </h2>

                    <p class="text-xs text-slate-400">
                        @if(isset($case['latitude'], $case['longitude']))
                            {{ $case['latitude'] }}, {{ $case['longitude'] }}
                        @else
                            Koordinat belum tersedia
                        @endif
                    </p>
                </div>

                <div
                    id="detail-location-map"
                    class="relative z-0 h-64 w-full bg-slate-100 sm:h-80"
                    aria-label="Peta lokasi kejadian"
                    @if(isset($case['latitude'], $case['longitude']))
                        data-latitude="{{ $case['latitude'] }}"
                        data-longitude="{{ $case['longitude'] }}"
                    @endif
                ></div>

                <div class="border-t border-slate-100 px-5 py-3">
                    <div class="flex items-start gap-2 text-xs text-slate-500">
                        <svg class="h-4 w-4 shrink-0 text-red-500"
                             viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="1.8"
                             aria-hidden="true">
                            <path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/>
                            <circle cx="12" cy="10" r="2.5"/>
                        </svg>
                        <div>
                            <p class="font-semibold text-slate-700">
                                {{ $case['lokasi'] }}
                            </p>
                            <p class="mt-1">
                                Patokan: {{ $case['patokan'] }}
                            </p>
                        </div>
                    </div>
                </div>

            </section>

        </div>

        {{-- Kolom kanan --}}
        <aside class="space-y-4">

            {{-- Aksi Penanganan --}}
            <section class="rounded-xl border border-slate-100
                            bg-white p-4 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold text-slate-700">
                    Aksi Penanganan
                </h2>

                <div class="space-y-2">

                    <button
                        type="button"
                        disabled
                        class="flex w-full cursor-not-allowed items-center
                               justify-center gap-2 rounded-lg bg-red-500
                               px-4 py-3 text-xs font-semibold text-white
                               opacity-70"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor"
                             stroke-width="1.8" aria-hidden="true">
                            <path d="M3 20h18M5 20v-9l7-5 7 5v9"/>
                            <path d="M9 20v-6h6v6"/>
                        </svg>
                        Resource Matching
                    </button>

                    <button
                        type="button"
                        disabled
                        class="flex w-full cursor-not-allowed items-center
                               justify-center gap-2 rounded-lg border
                               border-red-300 bg-white px-4 py-3
                               text-xs font-semibold text-red-500 opacity-70"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor"
                             stroke-width="1.8" aria-hidden="true">
                            <path d="M5 21V3h14v18M3 21h18"/>
                            <path d="M12 6v7M8.5 9.5h7"/>
                        </svg>
                        Hospital Matching
                    </button>

                    <button
                        type="button"
                        disabled
                        class="flex w-full cursor-not-allowed items-center
                               justify-center gap-2 rounded-lg
                               bg-rose-50 px-4 py-3 text-xs font-semibold
                               text-slate-500 opacity-70"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor"
                             stroke-width="1.8" aria-hidden="true">
                            <path d="M2 12h5l3-6 4 12 3-6h5"/>
                        </svg>
                        Perbarui Status
                    </button>
                </div>
            </section>

            {{-- Resource Ditugaskan --}}
            <section class="rounded-xl border border-slate-100
                            bg-white p-4 shadow-sm">

                <h2 class="mb-4 text-sm font-semibold text-slate-700">
                    Resource Ditugaskan
                </h2>

                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0
                                items-center justify-center
                                rounded-lg bg-red-100 text-red-500">
                        <svg class="h-5 w-5" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor"
                             stroke-width="1.8" aria-hidden="true">
                            <path d="M2 12h4l3-6 5 12 3-6h5"/>
                        </svg>
                    </div>

                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-slate-700">
                            {{ $case['resource'] }}
                        </p>
                        <p class="mt-1 text-xs text-slate-400">
                            {{ $case['resource_detail'] ?? 'Informasi resource belum tersedia' }}
                        </p>
                    </div>
                </div>

                <h3 class="mb-3 mt-5 text-sm font-medium text-slate-600">
                    Rumah Sakit Tujuan
                </h3>

                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0
                                items-center justify-center
                                rounded-lg bg-red-100 text-red-500">
                        <svg class="h-5 w-5" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor"
                             stroke-width="1.8" aria-hidden="true">
                            <path d="M5 21V3h14v18M3 21h18"/>
                            <path d="M12 6v7M8.5 9.5h7"/>
                        </svg>
                    </div>

                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-slate-700">
                            {{ $case['rumah_sakit'] ?? 'Belum ditentukan' }}
                        </p>
                        <p class="mt-1 text-xs text-slate-400">
                            {{ $case['rumah_sakit_detail'] ?? 'Belum tersedia' }}
                        </p>
                    </div>
                </div>

            </section>

        </aside>
    </div>

    <p class="mt-5 text-center text-xs text-slate-400">
        Data simulasi frontend. Belum terhubung ke database.
    </p>
</div>
@endsection
