
@extends('layouts.instansi')

@section('title', 'Detail Penanganan - EmergencyChain')

@section('content')
@php
    // Data mock sementara, nanti diganti data dari backend.
    $case = [
        'id' => 'EC-2026-0009',
        'judul' => 'Penanganan Kebakaran Bangunan',
        'jenis' => 'Kebakaran Bangunan',
        'lokasi' => 'Jl. Diponegoro, Surabaya',
        'prioritas' => 'Tinggi',
        'resource' => 'Damkar Unit 01',
        'personel' => 6,
        'jumlah_unit' => 1,
        'status' => 'Diterima',
        'deskripsi' => 'Kebakaran di lantai dua bangunan ruko. Asap tebal terlihat, penghuni sudah dievakuasi.',
    ];

    $tahapan = [
        'Assignment Diterima',
        'Menuju Lokasi',
        'Tiba di Lokasi',
        'Dalam Penanganan',
        'Penanganan Selesai',
    ];
@endphp

<div class="space-y-7">

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-sm font-bold uppercase tracking-wider text-blue-500">
                Status Emergency / {{ $case['id'] }}
            </p>

            <h1 class="mt-2 text-2xl font-semibold text-slate-800">
                {{ $case['judul'] }}
            </h1>
        </div>

        <a
            href="{{ route('preview.instansi.assignments.index') }}"
            class="rounded-xl border border-slate-200 bg-white px-6 py-3 font-semibold text-slate-700 hover:bg-slate-50"
        >
            Kembali
        </a>
    </div>

    <div class="grid gap-5 xl:grid-cols-3">

        {{-- Bagian kiri --}}
        <div class="space-y-5 xl:col-span-2">

            {{-- Progres respons --}}
            <section class="rounded-2xl border border-rose-100 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="font-semibold text-slate-800">
                        Progres Respons
                    </h2>
                </div>

                <div class="overflow-x-auto p-5">
                    <div class="flex min-w-[570px] items-start justify-between gap-2">
                        @foreach ($tahapan as $index => $tahap)
                            <div class="flex flex-1 flex-col items-center text-center">
                                <div
                                    class="flex h-9 w-9 items-center justify-center rounded-full border-2 text-sm font-bold
                                    {{ $index === 0
                                        ? 'border-green-500 bg-green-500 text-white'
                                        : ($index === 1
                                            ? 'border-red-500 bg-white text-red-500'
                                            : 'border-slate-200 bg-white text-slate-400') }}"
                                >
                                    {{ $index === 0 ? '✓' : $index + 1 }}
                                </div>

                                <p class="mt-3 text-xs font-semibold text-slate-700">
                                    {{ $tahap }}
                                </p>

                                <p class="mt-1 text-xs text-slate-400">
                                    {{ $index === 0 ? '10.10 WIB' : ($index === 1 ? 'Tahap berikutnya' : 'Menunggu') }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- Informasi kasus --}}
            <section class="rounded-2xl border border-rose-100 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="font-semibold text-slate-800">
                        Informasi Emergency Case
                    </h2>
                </div>

                <div class="space-y-5 p-5">

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <p class="text-xs text-slate-400">Case ID</p>
                            <p class="mt-1 font-semibold text-slate-800">
                                {{ $case['id'] }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400">Prioritas</p>
                            <span class="mt-1 inline-block rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
                                {{ $case['prioritas'] }}
                            </span>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400">Lokasi</p>
                            <p class="mt-1 font-semibold text-slate-800">
                                {{ $case['lokasi'] }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400">Unit Ditugaskan</p>
                            <p class="mt-1 font-semibold text-slate-800">
                                {{ $case['resource'] }}
                            </p>
                            <p class="text-xs text-slate-500">
                                {{ $case['personel'] }} personel
                            </p>
                        </div>
                    </div>

                    {{-- Jumlah unit diberangkatkan --}}
                    <div class="rounded-xl border border-rose-100 bg-rose-50/50 p-4">
                        <label
                            for="jumlah_unit"
                            class="block text-sm font-semibold text-slate-700"
                        >
                            Jumlah Unit Diberangkatkan
                        </label>

                        <p class="mt-1 text-xs text-slate-500">
                            Masukkan jumlah unit yang benar-benar diberangkatkan.
                        </p>

                        <div class="mt-3 flex flex-wrap items-center gap-3">
                            <input
                                id="jumlah_unit"
                                type="number"
                                min="1"
                                step="1"
                                value="{{ $case['jumlah_unit'] }}"
                                class="w-28 rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-800 focus:border-red-400 focus:outline-none"
                            >

                            <button
                                id="simpan-jumlah-unit"
                                type="button"
                                class="rounded-lg bg-red-500 px-5 py-2 font-semibold text-white hover:bg-red-600"
                            >
                                Simpan Jumlah
                            </button>
                        </div>

                        <p id="pesan-jumlah-unit" role="status" class="mt-2 text-xs text-slate-500">
                            Simulasi frontend — belum tersimpan ke database.
                        </p>
                    </div>

                    {{-- Deskripsi --}}
                    <div class="rounded-xl bg-rose-50 p-4">
                        <p class="text-sm text-slate-600">
                            {{ $case['deskripsi'] }}
                        </p>
                    </div>

                </div>
            </section>
        </div>

        {{-- Bagian kanan --}}
        <div class="space-y-5">

            {{-- Peta sementara --}}
            <section class="overflow-hidden rounded-2xl border border-rose-100 bg-white shadow-sm">
                <div class="relative flex h-64 items-center justify-center bg-slate-100">
                    <div
                        class="absolute inset-0 opacity-60"
                        style="background-image: linear-gradient(35deg, transparent 45%, white 46%, white 52%, transparent 53%), linear-gradient(125deg, transparent 45%, white 46%, white 52%, transparent 53%); background-size: 130px 130px;"
                    ></div>

                    <div class="relative z-10 rounded-xl bg-white px-4 py-3 text-center shadow-md">
                        <p class="text-2xl">📍</p>
                        <p class="mt-1 text-xs font-semibold text-slate-700">
                            Lokasi Kejadian
                        </p>
                        <p class="text-xs text-slate-500">
                            Jl. Diponegoro
                        </p>
                    </div>
                </div>

                <p class="px-4 py-3 text-xs text-slate-400">
                    Peta sementara — akan mengikuti map halaman Pelapor.
                </p>
            </section>

            {{-- Form update status --}}
            <section class="rounded-2xl border border-rose-100 bg-white p-5 shadow-sm">
                <h2 class="font-semibold text-slate-800">
                    Perbarui Status Penanganan
                </h2>

                <div class="mt-5 space-y-4">
                    <div>
                        <label
                            for="status_berikutnya"
                            class="block text-sm font-semibold text-slate-700"
                        >
                            Status berikutnya
                        </label>

                        <select
                            id="status_berikutnya"
                            class="mt-2 w-full rounded-lg border border-slate-200 bg-white px-3 py-3 text-sm text-slate-700"
                        >
                            <option value="Menuju Lokasi">Menuju Lokasi</option>
                            <option value="Tiba di Lokasi">Tiba di Lokasi</option>
                            <option value="Dalam Penanganan">Dalam Penanganan</option>
                            <option value="Selesai">Selesai</option>
                        </select>
                    </div>

                    <div>
                        <label
                            for="catatan_lapangan"
                            class="block text-sm font-semibold text-slate-700"
                        >
                            Catatan Lapangan
                        </label>

                        <textarea
                            id="catatan_lapangan"
                            rows="4"
                            placeholder="Tambahkan catatan kondisi terbaru..."
                            class="mt-2 w-full resize-none rounded-lg border border-slate-200 px-3 py-3 text-sm text-slate-700"
                        ></textarea>
                    </div>

                    <button
                        type="button"
                        id="perbarui-status"
                        class="w-full rounded-lg bg-red-500 px-4 py-3 font-semibold text-white hover:bg-red-600"
                    >
                        Perbarui Status
                    </button>

                    <p id="pesan-status" role="status" class="text-xs text-slate-500">
                        Simulasi frontend — status belum disimpan.
                    </p>
                </div>
            </section>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputJumlah = document.getElementById('jumlah_unit');
    const tombolJumlah = document.getElementById('simpan-jumlah-unit');
    const pesanJumlah = document.getElementById('pesan-jumlah-unit');

    tombolJumlah.addEventListener('click', function () {
        const nilai = inputJumlah.value.trim();
        const jumlah = Number(nilai);

        if (nilai === '' || !Number.isSafeInteger(jumlah) || jumlah < 1) {
            pesanJumlah.textContent = 'Jumlah unit harus bilangan bulat minimal 1.';
            pesanJumlah.className = 'mt-2 text-xs text-red-600';
            return;
        }

        pesanJumlah.textContent =
            `Simulasi: ${jumlah} unit diberangkatkan. Belum tersimpan ke database.`;
        pesanJumlah.className = 'mt-2 text-xs text-green-600';
    });

    const tombolStatus = document.getElementById('perbarui-status');
    const pilihanStatus = document.getElementById('status_berikutnya');
    const catatan = document.getElementById('catatan_lapangan');
    const pesanStatus = document.getElementById('pesan-status');

    tombolStatus.addEventListener('click', function () {
        if (catatan.value.trim() === '') {
            pesanStatus.textContent = 'Isi catatan lapangan terlebih dahulu.';
            pesanStatus.className = 'text-xs text-red-600';
            return;
        }

        pesanStatus.textContent =
            `Simulasi status: ${pilihanStatus.value}. Belum tersimpan ke database.`;
        pesanStatus.className = 'text-xs text-green-600';
    });
});
</script>
@endsection
