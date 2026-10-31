
@extends('layouts.instansi')

@section('title', 'Dashboard Penanganan - EmergencyChain')

@section('content')
@php
    // Data $assignments dikirim oleh AssignmentController.
    $assignmentAktif = $assignments->getCollection()
        ->whereNotIn('status_assign', ['selesai', 'ditolak', 'dibatalkan'])
        ->count();

    $selesaiPadaHalamanIni = $assignments->getCollection()
        ->where('status_assign', 'selesai')
        ->count();
@endphp

<div class="space-y-7">

    {{-- Judul Dashboard --}}
    <div>
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-500">
            {{ auth()->user()->instansi?->nama_instansi ?? 'Instansi Penanganan' }}
        </p>

        <h1 class="mt-2 text-3xl font-semibold text-slate-800">
            Dashboard Penanganan
        </h1>

        <p class="mt-2 text-sm text-slate-500">
            Daftar penugasan yang ditujukan kepada instansi Anda.
        </p>
    </div>

    {{-- Pesan dari backend --}}
    @if (session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- Ringkasan Dashboard --}}
    <div class="grid gap-4 md:grid-cols-3">

        <div class="rounded-2xl border border-rose-100 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">
                Assignment Aktif
            </p>
            <p class="mt-2 text-3xl font-bold text-slate-800">
                {{ $assignmentAktif }}
            </p>
            <p class="mt-1 text-xs text-slate-400">
                Pada halaman ini
            </p>
        </div>

        <div class="rounded-2xl border border-rose-100 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">
                Total Assignment
            </p>
            <p class="mt-2 text-3xl font-bold text-slate-800">
                {{ $assignments->total() }}
            </p>
            <p class="mt-1 text-xs text-slate-400">
                Seluruh data instansi Anda
            </p>
        </div>

        <div class="rounded-2xl border border-rose-100 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">
                Penanganan Selesai
            </p>
            <p class="mt-2 text-3xl font-bold text-slate-800">
                {{ $selesaiPadaHalamanIni }}
            </p>
            <p class="mt-1 text-xs text-slate-400">
                Pada halaman ini
            </p>
        </div>

    </div>

    {{-- Area Utama --}}
    <div class="grid gap-5 xl:grid-cols-3">

        {{-- Daftar Assignment --}}
        <section class="rounded-2xl border border-rose-100 bg-white shadow-sm xl:col-span-2">

            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="font-semibold text-slate-800">
                    Assignment Membutuhkan Tindakan
                </h2>
                <p class="mt-1 text-xs text-slate-400">
                    Informasi penugasan dari sistem Resource Matching
                </p>
            </div>

            <div class="grid gap-4 p-4 sm:grid-cols-2">

                @forelse ($assignments as $assignment)

                    @php
                        $latestStatus = $assignment->statusPenanganan
                            ->sortByDesc('waktu_update')
                            ->first();

                        $isSelesai = in_array(
                            $assignment->status_assign,
                            ['selesai', 'ditolak', 'dibatalkan'],
                            true
                        );
                    @endphp

                    <article class="rounded-xl border border-slate-200 border-t-4 border-t-red-500 p-4">

                        <div class="flex items-center justify-between gap-2">

                            <span class="rounded-full bg-rose-50 px-3 py-1 text-xs font-semibold text-red-600">
                                Assignment #{{ $assignment->assignment_id }}
                            </span>

                            <span class="text-xs text-slate-400">
                                {{ $assignment->waktu_assign }}
                            </span>

                        </div>

                        <h3 class="mt-3 text-lg font-bold text-slate-800">
                            Emergency Case #{{ $assignment->case_id }}
                        </h3>

                        <p class="mt-2 text-sm font-medium text-slate-700">
                            {{ $assignment->emergencyCase?->jenis_kejadian ?? 'Jenis kejadian tidak tersedia' }}
                        </p>

                        <p class="mt-1 text-sm text-slate-500">
                            {{ $assignment->emergencyCase?->lokasi?->alamat ?? 'Lokasi belum tersedia' }}
                        </p>

                        <div class="mt-4">
                            <p class="text-xs text-slate-400">
                                Resource Ditugaskan
                            </p>
                            <p class="mt-1 text-sm font-semibold text-slate-700">
                                {{ $assignment->resource?->nama_resource ?? 'Resource tidak tersedia' }}
                            </p>
                        </div>

                        <div class="mt-4">
                            <p class="text-xs text-slate-400">
                                Status Assignment
                            </p>
                            <span class="mt-1 inline-block rounded-full px-3 py-1 text-xs font-semibold
                                {{ $isSelesai
                                    ? 'bg-slate-100 text-slate-600'
                                    : 'bg-emerald-50 text-emerald-700' }}">
                                {{ ucwords(str_replace('_', ' ', $assignment->status_assign)) }}
                            </span>
                        </div>

                        <div class="mt-3">
                            <p class="text-xs text-slate-400">
                                Status Penanganan
                            </p>
                            <p class="mt-1 text-sm font-medium text-slate-700">
                                {{ $latestStatus
                                    ? ucwords(str_replace('_', ' ', $latestStatus->status))
                                    : 'Belum ada pembaruan' }}
                            </p>
                        </div>

                        {{-- Route detail resmi belum tersedia --}}
                        <div class="mt-5 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-center text-sm font-medium text-slate-500">
                            Detail Penanganan — segera tersedia
                        </div>

                    </article>

                @empty
                    <p class="col-span-full py-10 text-center text-sm text-slate-500">
                        Belum ada Assignment untuk instansi Anda.
                    </p>
                @endforelse

            </div>

            <div class="px-5 pb-5">
                {{ $assignments->links() }}
            </div>

        </section>

        {{-- Peta sementara --}}
        <section class="overflow-hidden rounded-2xl border border-rose-100 bg-white shadow-sm">

            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="font-semibold text-slate-800">
                    Peta Penugasan
                </h2>
                <p class="mt-1 text-xs text-slate-400">
                    Area operasional instansi
                </p>
            </div>

            <div class="relative flex h-64 items-center justify-center overflow-hidden bg-slate-100">

                <div
                    class="absolute inset-0 opacity-60"
                    style="background-image: linear-gradient(35deg, transparent 45%, white 46%, white 52%, transparent 53%), linear-gradient(125deg, transparent 45%, white 46%, white 52%, transparent 53%); background-size: 130px 130px;"
                ></div>

                <div class="relative z-10 rounded-xl bg-white px-4 py-3 text-center shadow-md">
                    <p class="text-2xl">📍</p>
                    <p class="mt-1 text-xs font-semibold text-slate-700">
                        Peta Penugasan
                    </p>
                    <p class="text-xs text-slate-500">
                        Integrasi peta menyusul
                    </p>
                </div>

            </div>

            <p class="px-5 py-4 text-xs text-slate-400">
                Peta masih ilustrasi. Lokasi kejadian di kartu sudah berasal dari database.
            </p>

        </section>

    </div>
</div>
@endsection
