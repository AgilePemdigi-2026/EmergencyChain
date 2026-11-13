
@extends('layouts.instansi')

@section('title', 'Detail Riwayat Penanganan - EmergencyChain')

@section('content')

@php
    $case = $assignment->emergencyCase;
    $resource = $assignment->resource;
    $history = $assignment->statusPenanganan;

    $statusLabels = [
        'menuju_lokasi' => 'Menuju Lokasi',
        'tiba_di_lokasi' => 'Tiba di Lokasi',
        'dalam_penanganan' => 'Dalam Penanganan',
        'selesai' => 'Penanganan Selesai',
    ];

    $formatTanggal = function ($value) {
        return $value
            ? \Carbon\Carbon::parse($value)->format('d M Y, H:i')
            : '-';
    };

    $selesaiStatus = $history->firstWhere('status', 'selesai');

    $latestNote = $history
        ->filter(fn ($item) => filled($item->keterangan))
        ->last();

    $latitude = $case?->lokasi?->latitude;
    $longitude = $case?->lokasi?->longitude;

    $hasCoordinates = $latitude !== null
        && $longitude !== null
        && is_numeric($latitude)
        && is_numeric($longitude);
@endphp

<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-wrap items-start justify-between gap-4">

        <div>
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-500">
                RIWAYAT PENANGANAN /
                EC-{{ str_pad($assignment->case_id, 4, '0', STR_PAD_LEFT) }}
            </p>

            <h1 class="mt-2 text-3xl font-semibold text-slate-800">
                {{ $case?->jenis_kejadian ?? 'Detail Penanganan' }}
            </h1>

            <p class="mt-2 text-sm text-slate-500">
                Detail Assignment yang telah diselesaikan oleh instansi Anda.
            </p>
        </div>

        <a
            href="{{ route('instansi.history.index') }}"
            class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
        >
            Kembali ke Daftar
        </a>
    </div>

    {{-- BANNER ASSIGNMENT --}}
    <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-rose-100 bg-rose-50/70 p-5">

        <div>
            <p class="text-xs font-semibold text-red-500">
                Assignment Instansi
            </p>

            <p class="mt-1 text-sm font-bold text-slate-800">
                Assignment #{{ $assignment->assignment_id }}
                —
                {{ auth()->user()->instansi?->nama_instansi ?? 'Instansi' }}
            </p>
        </div>

        <span class="rounded-full bg-emerald-100 px-4 py-2 text-xs font-semibold text-emerald-700">
            ● Selesai
        </span>
    </div>

    <div class="grid items-start gap-5 xl:grid-cols-3">

        {{-- KOLOM KIRI --}}
        <div class="space-y-5 xl:col-span-2">

            {{-- INFORMASI EMERGENCY CASE --}}
            <section class="overflow-hidden rounded-2xl border border-rose-100 bg-white shadow-sm">

                <div class="border-b border-slate-100 px-5 py-4">

                    <h2 class="font-semibold text-slate-800">
                        Informasi Emergency Case
                    </h2>

                    <p class="mt-1 text-xs text-slate-400">
                        Data kejadian dan kebutuhan respons.
                    </p>
                </div>

                <div class="space-y-5 p-5">

                    <div class="grid gap-5 sm:grid-cols-2">

                        <div>
                            <p class="text-xs text-slate-400">
                                Emergency Case ID
                            </p>

                            <p class="mt-1 text-sm font-semibold text-slate-800">
                                EC-{{ str_pad($assignment->case_id, 4, '0', STR_PAD_LEFT) }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400">
                                Jenis Kejadian
                            </p>

                            <p class="mt-1 text-sm font-semibold text-slate-800">
                                {{ $case?->jenis_kejadian ?? '-' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400">
                                Lokasi
                            </p>

                            <p class="mt-1 text-sm font-semibold text-slate-800">
                                {{ $case?->lokasi?->alamat ?? 'Lokasi tidak tersedia' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400">
                                Status Emergency Case
                            </p>

                            <p class="mt-1 text-sm font-semibold text-slate-800">
                                {{ $case?->status_laporan ?? '-' }}
                            </p>
                        </div>

                    </div>

                    <div class="rounded-xl bg-slate-50 p-4">

                        <p class="mb-2 text-xs font-semibold text-slate-500">
                            Kondisi Kejadian
                        </p>

                        <p class="whitespace-pre-line break-words text-sm leading-6 text-slate-700">{{ $case?->kondisi ?: 'Tidak ada informasi kondisi kejadian.' }}</p>

                    </div>

                </div>
            </section>

            {{-- INFORMASI ASSIGNMENT --}}
            <section class="overflow-hidden rounded-2xl border border-rose-100 bg-white shadow-sm">

                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">

                    <h2 class="font-semibold text-slate-800">
                        Informasi Assignment
                    </h2>

                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                        Selesai
                    </span>
                </div>

                <div class="grid gap-5 p-5 sm:grid-cols-2">

                    <div>
                        <p class="text-xs text-slate-400">
                            Assignment ID
                        </p>

                        <p class="mt-1 text-sm font-semibold text-slate-800">
                            #{{ $assignment->assignment_id }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-400">
                            Resource Ditugaskan
                        </p>

                        <p class="mt-1 text-sm font-semibold text-slate-800">
                            {{ $resource?->nama_resource ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-400">
                            Jenis Resource
                        </p>

                        <p class="mt-1 text-sm font-semibold text-slate-800">
                            {{ $resource?->jenis_resource ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-400">
                            Kapasitas / Personel
                        </p>

                        <p class="mt-1 text-sm font-semibold text-slate-800">
                            {{ $resource?->kapasitas_personel !== null
                                ? $resource->kapasitas_personel . ' personel'
                                : '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-400">
                            Waktu Assignment
                        </p>

                        <p class="mt-1 text-sm font-semibold text-slate-800">
                            {{ $formatTanggal($assignment->waktu_assign) }} WIB
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-400">
                            Waktu Konfirmasi
                        </p>

                        <p class="mt-1 text-sm font-semibold text-slate-800">
                            {{ $formatTanggal($assignment->waktu_konfirmasi) }}
                        </p>
                    </div>

                    <div class="sm:col-span-2">
                        <p class="text-xs text-slate-400">
                            Lokasi Pangkalan Resource
                        </p>

                        <p class="mt-1 text-sm font-semibold text-slate-800">
                            {{ $resource?->lokasiPangkalan?->alamat
                                ?? $resource?->lokasi
                                ?? '-' }}
                        </p>
                    </div>

                </div>
            </section>

            {{-- CATATAN LAPANGAN --}}
            <section class="overflow-hidden rounded-2xl border border-rose-100 bg-white shadow-sm">

                <div class="border-b border-slate-100 px-5 py-4">

                    <h2 class="font-semibold text-slate-800">
                        Catatan Lapangan
                    </h2>

                    <p class="mt-1 text-xs text-slate-400">
                        Catatan yang dicatat selama proses penanganan.
                    </p>
                </div>

                <div class="p-5">

                    @if($history->isNotEmpty())

                        <div class="space-y-4">

                            @foreach($history as $item)

                                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">

                                    <div class="flex flex-wrap items-center justify-between gap-2">

                                        <p class="text-sm font-semibold text-slate-800">
                                            {{ $statusLabels[$item->status]
                                                ?? ucwords(str_replace('_', ' ', $item->status)) }}
                                        </p>

                                        <p class="text-xs text-slate-400">
                                            {{ $formatTanggal($item->waktu_update) }}
                                        </p>

                                    </div>

                                    <p class="mt-3 whitespace-pre-line break-words text-sm leading-6 text-slate-600">{{ $item->keterangan ?: 'Tidak ada catatan.' }}</p>

                                </div>

                            @endforeach

                        </div>

                    @else

                        <p class="rounded-xl bg-slate-50 p-4 text-sm text-slate-500">
                            Belum ada catatan lapangan yang tercatat.
                        </p>

                    @endif

                </div>
            </section>

        </div>

        {{-- KOLOM KANAN --}}
        <div class="space-y-5">

            {{-- TIMELINE --}}
            <section class="overflow-hidden rounded-2xl border border-rose-100 bg-white shadow-sm">

                <div class="border-b border-slate-100 px-5 py-4">

                    <h2 class="font-semibold text-slate-800">
                        Timeline Penanganan
                    </h2>

                    <p class="mt-1 text-xs text-slate-400">
                        Riwayat perubahan status Assignment.
                    </p>
                </div>

                <div class="p-5">

                    {{-- Assignment Dibuat --}}
                    <div class="relative flex gap-4 pb-7">

                        <div class="relative flex flex-col items-center">

                            <div class="z-10 flex h-9 w-9 items-center justify-center rounded-full bg-emerald-500 font-semibold text-white">
                                ✓
                            </div>

                            @if($history->isNotEmpty())
                                <div class="absolute top-9 h-full w-px bg-slate-200"></div>
                            @endif

                        </div>

                        <div>
                            <p class="text-sm font-semibold text-slate-800">
                                Assignment Dibuat
                            </p>

                            <p class="mt-1 text-xs text-slate-400">
                                {{ $formatTanggal($assignment->waktu_assign) }}
                            </p>
                        </div>
                    </div>

                    {{-- Riwayat Perubahan Status --}}
                    @foreach($history as $item)

                        <div class="relative flex gap-4 {{ !$loop->last ? 'pb-7' : '' }}">

                            <div class="relative flex flex-col items-center">

                                <div class="z-10 flex h-9 w-9 items-center justify-center rounded-full bg-emerald-500 font-semibold text-white">
                                    ✓
                                </div>

                                @if(!$loop->last)
                                    <div class="absolute top-9 h-full w-px bg-slate-200"></div>
                                @endif

                            </div>

                            <div class="min-w-0 flex-1">

                                <p class="text-sm font-semibold text-slate-800">
                                    {{ $statusLabels[$item->status]
                                        ?? ucwords(str_replace('_', ' ', $item->status)) }}
                                </p>

                                <p class="mt-1 text-xs text-slate-400">
                                    {{ $formatTanggal($item->waktu_update) }}
                                </p>

                                @if($item->keterangan)
                                    <p class="mt-2 break-words text-xs leading-5 text-slate-500">
                                        {{ \Illuminate\Support\Str::limit($item->keterangan, 130) }}
                                    </p>
                                @endif

                            </div>
                        </div>

                    @endforeach

                </div>
            </section>

            {{-- WAKTU SELESAI --}}
            <section class="rounded-2xl border border-emerald-100 bg-emerald-50 p-5">

                <div class="flex items-center gap-3">

                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500 text-xl font-bold text-white">
                        ✓
                    </div>

                    <div>
                        <p class="text-xs font-medium text-emerald-700">
                            Waktu Selesai
                        </p>

                        <p class="mt-1 text-sm font-bold text-emerald-800">
                            {{ $formatTanggal($selesaiStatus?->waktu_update) }}
                        </p>

                        <p class="mt-1 text-xs text-emerald-700">
                            Penanganan Assignment selesai
                        </p>
                    </div>

                </div>
            </section>

            {{-- PETA LOKASI --}}
            <section class="overflow-hidden rounded-2xl border border-rose-100 bg-white shadow-sm">

                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="font-semibold text-slate-800">
                        Lokasi Kejadian
                    </h2>
                </div>

                @if($hasCoordinates)

                    <div
                        id="history-location-map"
                        class="h-64 w-full bg-slate-100"
                        data-latitude="{{ $latitude }}"
                        data-longitude="{{ $longitude }}"
                    ></div>

                    <div class="border-t border-slate-100 px-4 py-3">

                        <p class="text-xs font-semibold text-slate-700">
                            Alamat Kejadian
                        </p>

                        <p class="mt-1 text-xs leading-5 text-slate-500">
                            {{ $case?->lokasi?->alamat ?? '-' }}
                        </p>

                    </div>

                @else

                    <div class="flex h-64 items-center justify-center bg-slate-50 p-6 text-center">

                        <div>
                            <p class="text-2xl">📍</p>

                            <p class="mt-2 text-sm font-semibold text-slate-600">
                                Koordinat belum tersedia
                            </p>

                            <p class="mt-1 text-xs text-slate-400">
                                Peta tidak dapat ditampilkan.
                            </p>
                        </div>

                    </div>

                @endif

            </section>

        </div>

    </div>

</div>

{{-- LEAFLET --}}
@if($hasCoordinates)

<link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
    crossorigin=""
>

<script
    src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
    crossorigin=""
></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const element = document.getElementById('history-location-map');

    if (!element || typeof L === 'undefined') {
        return;
    }

    const latitude = Number(element.dataset.latitude);
    const longitude = Number(element.dataset.longitude);

    if (
        !Number.isFinite(latitude) ||
        !Number.isFinite(longitude)
    ) {
        return;
    }

    const map = L.map(element, {
        scrollWheelZoom: false
    }).setView([latitude, longitude], 15);

    L.tileLayer(
        'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
        {
            maxZoom: 19,
            attribution:
                '&copy; OpenStreetMap contributors'
        }
    ).addTo(map);

    L.marker([latitude, longitude])
        .addTo(map)
        .bindPopup('Lokasi Emergency Case');

    setTimeout(function () {
        map.invalidateSize();
    }, 150);
});
</script>

@endif

@endsection
