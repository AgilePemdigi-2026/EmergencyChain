
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

                <div class="flex items-center justify-between gap-3
                            border-b border-slate-100 px-5 py-4">
                    <h2 class="text-sm font-semibold text-slate-700">
                        Informasi Kejadian
                    </h2>

                    <button
                        type="button"
                        id="edit-case-button"
                        class="rounded-lg border border-blue-200 px-4 py-2
                            text-xs font-semibold text-blue-600
                            hover:bg-blue-50"
                    >
                        Edit Informasi
                    </button>
                </div>

                <div class="p-5">

                    {{-- Pesan setelah update berhasil --}}
                    @if (session('success'))
                        <div class="mb-5 rounded-lg bg-emerald-50 p-3
                                    text-sm text-emerald-700" role="status">
                            {{ session('success') }}
                        </div>
                    @endif

                    {{-- Pesan validasi backend --}}
                    @if ($errors->any())
                        <div class="mb-5 rounded-lg bg-red-50 p-3
                                    text-sm text-red-700" role="alert">
                            <p class="font-semibold">
                                Data Emergency Case gagal disimpan
                            </p>
                            <ul class="mt-2 list-disc pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Mode Lihat --}}
                    <div
                        id="case-view-mode"
                        class="{{ $errors->any() ? 'hidden' : '' }}"
                    >
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

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
                                <p class="mt-1 text-sm text-slate-700">
                                    {{ $case->kebutuhan }}
                                </p>
                            </div>

                        </div>
                    </div>

                    {{-- Mode Edit --}}
                    <form
                        id="case-edit-form"
                        action="{{ route('petugas.ec.cases.update', ['caseId' => $case->case_id]) }}"
                        method="POST"
                        novalidate
                        class="{{ $errors->any() ? '' : 'hidden' }} space-y-5"
                    >
                        @csrf
                        @method('PATCH')

                        <div>
                            <label
                                for="edit-kondisi"
                                class="mb-2 block text-sm font-medium text-slate-700"
                            >
                                Kondisi Kejadian
                                <span class="text-red-500">*</span>
                            </label>

                            <textarea
                                id="edit-kondisi"
                                name="kondisi"
                                rows="4"
                                maxlength="500"
                                required
                                class="w-full rounded-lg border border-slate-200
                                    px-3 py-3 text-sm focus:border-blue-400
                                    focus:outline-none"
                            >{{ old('kondisi', $case->kondisi) }}</textarea>
                        </div>

                        <div>
                            <label
                                for="edit-kebutuhan"
                                class="mb-2 block text-sm font-medium text-slate-700"
                            >
                                Kebutuhan Penanganan
                                <span class="text-red-500">*</span>
                            </label>

                            <select
                                id="edit-kebutuhan"
                                name="kebutuhan"
                                required
                                class="w-full rounded-lg border border-slate-200
                                    bg-white px-3 py-3 text-sm
                                    focus:border-blue-400 focus:outline-none"
                            >
                                <option value="">Pilih kebutuhan</option>

                                @foreach ([
                                    'Butuh Penanganan Cepat',
                                    'Butuh Penanganan Segera'
                                ] as $pilihan)
                                    <option
                                        value="{{ $pilihan }}"
                                        @selected(old('kebutuhan', $case->kebutuhan) === $pilihan)
                                    >
                                        {{ $pilihan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex flex-wrap justify-end gap-3">
                            <button
                                type="button"
                                id="cancel-case-edit"
                                class="rounded-lg border border-slate-200
                                    px-5 py-3 text-sm font-semibold
                                    text-slate-600 hover:bg-slate-50"
                            >
                                Batal
                            </button>

                            <button
                                type="submit"
                                class="rounded-lg bg-red-500 px-5 py-3
                                    text-sm font-semibold text-white
                                    hover:bg-red-600"
                            >
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>

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

{{-- Interaksi Edit Informasi --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const editButton = document.getElementById('edit-case-button');
    const cancelButton = document.getElementById('cancel-case-edit');
    const viewMode = document.getElementById('case-view-mode');
    const editForm = document.getElementById('case-edit-form');

    if (!editButton || !cancelButton || !viewMode || !editForm) {
        return;
    }

    function setEditMode(editing) {
        viewMode.classList.toggle('hidden', editing);
        editForm.classList.toggle('hidden', !editing);
        editButton.classList.toggle('hidden', editing);
    }

    editButton.addEventListener('click', () => {
        setEditMode(true);
    });

    cancelButton.addEventListener('click', () => {
        // Memulihkan nilai yang terakhir tersimpan.
        editForm.reset();
        setEditMode(false);
    });
});
</script>

@endsection
