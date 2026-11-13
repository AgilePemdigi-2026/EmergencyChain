
@extends('layouts.instansi')

@section('title', 'Riwayat Penanganan - EmergencyChain')

@section('content')

<div class="space-y-7">

    {{-- HEADER --}}
    <div>
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-500">
            RIWAYAT PENANGANAN
        </p>

        <h1 class="mt-2 text-3xl font-semibold text-slate-800">
            Arsip Penanganan
            {{ auth()->user()->instansi?->nama_instansi ?? 'Instansi' }}
        </h1>

        <p class="mt-2 text-sm text-slate-500">
            Riwayat Assignment yang telah selesai ditangani oleh instansi Anda.
        </p>
    </div>

    {{-- SUMMARY CARDS --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        {{-- Total Penanganan --}}
        <div class="rounded-2xl border border-rose-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between gap-3">

                <div>
                    <p class="text-xs font-medium text-slate-500">
                        Total Penanganan Selesai
                    </p>

                    <p class="mt-2 text-3xl font-bold text-slate-800">
                        {{ $summary['total'] }}
                    </p>

                    <p class="mt-1 text-xs text-slate-400">
                        Seluruh periode
                    </p>
                </div>

                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-xl text-blue-600">
                    ▣
                </div>
            </div>
        </div>

        {{-- Total Kasus --}}
        <div class="rounded-2xl border border-rose-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between gap-3">

                <div>
                    <p class="text-xs font-medium text-slate-500">
                        Emergency Case Ditangani
                    </p>

                    <p class="mt-2 text-3xl font-bold text-slate-800">
                        {{ $summary['kasus'] }}
                    </p>

                    <p class="mt-1 text-xs text-slate-400">
                        Kasus unik yang memiliki Assignment selesai
                    </p>
                </div>

                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 text-xl text-amber-600">
                    ◈
                </div>
            </div>
        </div>

        {{-- Unit Terlibat --}}
        <div class="rounded-2xl border border-rose-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between gap-3">

                <div>
                    <p class="text-xs font-medium text-slate-500">
                        Unit Terlibat
                    </p>

                    <p class="mt-2 text-3xl font-bold text-slate-800">
                        {{ $summary['unit'] }}
                    </p>

                    <p class="mt-1 text-xs text-slate-400">
                        Unit unik dalam penanganan selesai
                    </p>
                </div>

                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-xl text-emerald-600">
                    ✓
                </div>
            </div>
        </div>

        {{-- Bulan Ini --}}
        <div class="rounded-2xl border border-rose-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between gap-3">

                <div>
                    <p class="text-xs font-medium text-slate-500">
                        Selesai Bulan Ini
                    </p>

                    <p class="mt-2 text-3xl font-bold text-slate-800">
                        {{ $summary['bulan_ini'] }}
                    </p>

                    <p class="mt-1 text-xs text-slate-400">
                        {{ now()->translatedFormat('F Y') }}
                    </p>
                </div>

                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-rose-50 text-xl text-red-500">
                    ◷
                </div>
            </div>
        </div>

    </div>

    {{-- FILTER --}}
    <form
        method="GET"
        action="{{ route('instansi.history.index') }}"
        class="grid gap-3 md:grid-cols-[minmax(0,1fr)_220px_auto]"
    >

        <input
            type="search"
            name="search"
            value="{{ request('search') }}"
            placeholder="Cari Case ID, kejadian, atau lokasi..."
            class="w-full rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm text-slate-700 outline-none transition focus:border-red-400 focus:ring-2 focus:ring-red-100"
        >

        <input
            type="month"
            name="bulan"
            value="{{ request('bulan') }}"
            aria-label="Filter bulan penyelesaian"
            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none focus:border-red-400"
        >

        <div class="flex gap-2">
            <button
                type="submit"
                class="rounded-xl bg-red-500 px-5 py-3 text-sm font-semibold text-white transition hover:bg-red-600"
            >
                Filter
            </button>

            <a
                href="{{ route('instansi.history.index') }}"
                class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50"
            >
                Reset
            </a>
        </div>
    </form>

    {{-- DAFTAR RIWAYAT --}}
    <section class="overflow-hidden rounded-2xl border border-rose-100 bg-white shadow-sm">

        <div class="border-b border-slate-100 px-5 py-4">

            <h2 class="font-semibold text-slate-800">
                Kasus yang Telah Ditangani Instansi
            </h2>

            <p class="mt-1 text-xs text-slate-400">
                Menampilkan {{ $assignments->count() }}
                dari {{ $assignments->total() }} riwayat yang sesuai filter
            </p>

        </div>

        <div class="overflow-x-auto">

            <table class="w-full min-w-[960px] text-left">

                <thead class="bg-rose-50/50 text-[11px] font-semibold uppercase text-slate-400">
                    <tr>
                        <th class="px-5 py-4">Case ID</th>
                        <th class="px-5 py-4">Jenis Kejadian</th>
                        <th class="px-5 py-4">Lokasi</th>
                        <th class="px-5 py-4">Tanggal / Waktu</th>
                        <th class="px-5 py-4">Status Penanganan</th>
                        <th class="px-5 py-4">Waktu Selesai</th>
                        <th class="px-5 py-4 text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">

                    @forelse($assignments as $assignment)

                        @php
                            $case = $assignment->emergencyCase;
                            $resource = $assignment->resource;

                            $selesaiStatus = $assignment
                                ->statusPenanganan
                                ->first();

                            $formatTanggal = function ($value) {
                                return $value
                                    ? \Carbon\Carbon::parse($value)
                                        ->format('d M Y, H:i')
                                    : '-';
                            };
                        @endphp

                        <tr class="transition hover:bg-rose-50/30">

                            {{-- Case ID --}}
                            <td class="px-5 py-4">
                                <p class="text-xs font-bold text-blue-600">
                                    EC-{{ str_pad($assignment->case_id, 4, '0', STR_PAD_LEFT) }}
                                </p>

                                <p class="mt-1 text-[11px] text-slate-400">
                                    Assignment #{{ $assignment->assignment_id }}
                                </p>
                            </td>

                            {{-- Kejadian --}}
                            <td class="px-5 py-4">
                                <p class="text-sm font-semibold text-slate-800">
                                    {{ $case?->jenis_kejadian ?? '-' }}
                                </p>

                                <p class="mt-1 text-xs text-slate-400">
                                    {{ $resource?->nama_resource ?? '-' }}
                                </p>
                            </td>

                            {{-- Lokasi --}}
                            <td class="px-5 py-4">
                                <p class="max-w-[220px] text-sm text-slate-600">
                                    {{ $case?->lokasi?->alamat ?? 'Lokasi tidak tersedia' }}
                                </p>
                            </td>

                            {{-- Waktu Assignment --}}
                            <td class="px-5 py-4">
                                <p class="text-xs text-slate-600">
                                    {{ $formatTanggal($assignment->waktu_assign) }}
                                </p>
                            </td>

                            {{-- Status --}}
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center gap-2 whitespace-nowrap rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    Selesai
                                </span>
                            </td>

                            {{-- Waktu Selesai --}}
                            <td class="px-5 py-4">
                                <p class="text-xs text-slate-600">
                                    {{ $formatTanggal($selesaiStatus?->waktu_update) }}
                                </p>
                            </td>

                            {{-- Detail --}}
                            <td class="px-5 py-4 text-center">
                                <a
                                    href="{{ route('instansi.history.show', $assignment->assignment_id) }}"
                                    class="inline-flex items-center gap-2 rounded-lg border border-red-100 bg-red-50 px-4 py-2 text-xs font-semibold text-red-500 transition hover:bg-red-100"
                                >
                                    Detail
                                    <span aria-hidden="true">›</span>
                                </a>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">

                                <p class="text-base font-semibold text-slate-700">
                                    Belum Ada Riwayat Penanganan
                                </p>

                                <p class="mt-2 text-sm text-slate-400">
                                    Belum ada Assignment selesai yang sesuai dengan filter.
                                </p>

                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>
        </div>

        {{-- PAGINATION --}}
        @if($assignments->hasPages())
            <div class="border-t border-slate-100 px-5 py-4">
                {{ $assignments->links() }}
            </div>
        @endif

    </section>

</div>

@endsection
