
@extends('layouts.instansi')

@section('title', 'Dashboard Penanganan - EmergencyChain')

@section('content')
@php
    // Data sementara untuk preview frontend
    $assignments = [
        [
            'id' => 'ASG-001',
            'case_id' => 'EC-2026-0009',
            'jenis_kejadian' => 'Kebakaran Bangunan',
            'lokasi' => 'Jl. Diponegoro, Surabaya',
            'prioritas' => 'Tinggi',
            'waktu' => 'Hari ini, 10:10',
            'resource' => 'Unit Pemadam & Rescue',
            'status' => 'Diterima',
        ],
    ];

    $assignmentAktif = count($assignments);
    $menungguAssignment = 0;
    $selesaiHariIni = 0;
@endphp

<div class="space-y-7">

    {{-- Judul dashboard --}}
    <div>
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-500">
            Damkar Surabaya
        </p>

        <h1 class="mt-2 text-3xl font-semibold text-slate-800">
            Dashboard Penanganan
        </h1>
    </div>

    {{-- Ringkasan --}}
    <div class="grid gap-4 md:grid-cols-3">

        <div class="rounded-2xl border border-rose-100 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">
                Assignment Aktif
            </p>
            <p class="mt-2 text-3xl font-bold text-slate-800">
                {{ $assignmentAktif }}
            </p>
            <p class="mt-1 text-xs text-slate-400">
                Sedang ditangani
            </p>
        </div>

        <div class="rounded-2xl border border-rose-100 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">
                Menunggu Assignment
            </p>
            <p class="mt-2 text-3xl font-bold text-slate-800">
                {{ $menungguAssignment }}
            </p>
            <p class="mt-1 text-xs text-slate-400">
                Menunggu resource tersedia
            </p>
        </div>

        <div class="rounded-2xl border border-rose-100 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">
                Selesai Hari Ini
            </p>
            <p class="mt-2 text-3xl font-bold text-slate-800">
                {{ $selesaiHariIni }}
            </p>
            <p class="mt-1 text-xs text-slate-400">
                Penanganan selesai
            </p>
        </div>

    </div>

    {{-- Area utama --}}
    <div class="grid gap-5 xl:grid-cols-3">

        {{-- Daftar assignment --}}
        <section class="rounded-2xl border border-rose-100 bg-white shadow-sm xl:col-span-2">

            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="font-semibold text-slate-800">
                    Assignment Membutuhkan Tindakan
                </h2>
                <p class="mt-1 text-xs text-slate-400">
                    Penugasan aktif yang perlu ditangani
                </p>
            </div>

            <div class="grid gap-4 p-4 sm:grid-cols-2">

                @forelse ($assignments as $assignment)

                    <article class="rounded-xl border border-slate-200 border-t-4 border-t-red-500 p-4">

                        <div class="flex items-center justify-between gap-2">

                            <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
                                {{ $assignment['prioritas'] }}
                            </span>

                            <span class="text-xs text-slate-400">
                                {{ $assignment['waktu'] }}
                            </span>

                        </div>

                        <h3 class="mt-3 text-lg font-bold text-slate-800">
                            {{ $assignment['case_id'] }}
                        </h3>

                        <p class="mt-2 text-sm font-medium text-slate-700">
                            {{ $assignment['jenis_kejadian'] }}
                        </p>

                        <p class="mt-1 text-sm text-slate-500">
                            {{ $assignment['lokasi'] }}
                        </p>

                        <div class="mt-4">
                            <p class="text-xs text-slate-400">
                                Respons Dibutuhkan
                            </p>

                            <p class="mt-1 text-sm font-semibold text-slate-700">
                                {{ $assignment['resource'] }}
                            </p>
                        </div>

                        <div class="mt-4">
                            <p class="text-xs text-slate-400">Status</p>

                            <span class="mt-1 inline-block rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                {{ $assignment['status'] }}
                            </span>
                        </div>

                        <a
                            href="{{ route('preview.instansi.assignments.show') }}"
                            class="mt-5 block rounded-lg border border-red-400 px-4 py-3 text-center text-sm font-semibold text-red-500 transition hover:bg-red-50"
                        >
                            Buka Penanganan
                        </a>

                    </article>

                @empty

                    <p class="col-span-full py-10 text-center text-sm text-slate-500">
                        Belum ada assignment aktif.
                    </p>

                @endforelse

            </div>

        </section>

        {{-- Peta mock --}}
        <section class="overflow-hidden rounded-2xl border border-rose-100 bg-white shadow-sm">

            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="font-semibold text-slate-800">
                    Peta Penugasan
                </h2>
                <p class="mt-1 text-xs text-slate-400">
                    Area operasional aktif
                </p>
            </div>

            <div class="relative flex h-64 items-center justify-center overflow-hidden bg-slate-100">

                {{-- Ilustrasi peta sementara --}}
                <div class="absolute inset-0 opacity-60"
                     style="background-image: linear-gradient(35deg, transparent 45%, white 46%, white 52%, transparent 53%), linear-gradient(125deg, transparent 45%, white 46%, white 52%, transparent 53%); background-size: 130px 130px;">
                </div>

                <div class="relative z-10 rounded-xl border border-red-100 bg-white px-4 py-3 text-center shadow-md">
                    <div class="text-2xl">📍</div>
                    <p class="mt-1 text-xs font-semibold text-slate-700">
                        Lokasi Kejadian
                    </p>
                    <p class="mt-1 text-xs text-slate-500">
                        Jl. Diponegoro
                    </p>
                </div>

            </div>

            <div class="flex flex-wrap gap-4 px-5 py-4 text-xs text-slate-500">
                <span>🟢 4 unit tersedia</span>
                <span>🟠 3 unit bertugas</span>
            </div>

            <p class="px-5 pb-4 text-xs text-slate-400">
                Ilustrasi peta dan jumlah unit masih data mock.
            </p>

        </section>

    </div>

</div>
@endsection
