
@extends('layouts.petugas-ec')

@section('title', 'Assignment Management - EmergencyChain')

@section('content')

@php
    $stats = [
        [
            'label' => 'Total Assignment',
            'value' => $totalAssignments,
            'color' => 'text-slate-800',
        ],
        [
            'label' => 'Aktif',
            'value' => $activeAssignments,
            'color' => 'text-blue-600',
        ],
        [
            'label' => 'Selesai',
            'value' => $completedAssignments,
            'color' => 'text-emerald-600',
        ],
    ];

    $statusLabels = [
        'diterima' => 'Diterima',
        'selesai' => 'Selesai',
        'ditolak' => 'Ditolak',
        'dibatalkan' => 'Dibatalkan',
    ];

    $progressLabels = [
        'menuju_lokasi' => 'Menuju Lokasi',
        'tiba_di_lokasi' => 'Tiba di Lokasi',
        'dalam_penanganan' => 'Dalam Penanganan',
        'selesai' => 'Selesai',
    ];
@endphp

<div class="mx-auto max-w-7xl space-y-6">

    {{-- Heading --}}
    <div>
        <p class="text-xs font-bold tracking-widest text-red-500">
            EMERGENCYCHAIN
        </p>

        <h1 class="mt-1 text-2xl font-bold text-slate-800">
            Assignment Management
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Monitoring penugasan resource pada seluruh Emergency Case.
        </p>
    </div>

    {{-- Statistik --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        @foreach ($stats as $stat)
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">
                    {{ $stat['label'] }}
                </p>

                <p class="mt-3 text-3xl font-bold {{ $stat['color'] }}">
                    {{ $stat['value'] }}
                </p>
            </div>
        @endforeach

    </div>

    {{-- Daftar Assignment --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        <div class="border-b border-slate-100 p-5">

            <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center">

                <div>
                    <h2 class="text-lg font-bold text-slate-800">
                        Daftar Assignment
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Seluruh penugasan resource dari proses matching.
                    </p>
                </div>

                {{-- Pencarian dan filter --}}
                <form method="GET"
                      action="{{ route('petugas.ec.assignments.index') }}"
                      class="flex flex-col gap-2 sm:flex-row">

                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Cari Assignment / Case ID"
                        class="rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-red-400 focus:outline-none"
                    >

                    <select
                        name="status"
                        class="rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-red-400 focus:outline-none"
                    >
                        <option value="">Semua Status</option>

                        @foreach ($statusLabels as $key => $label)
                            <option value="{{ $key }}"
                                @selected($status === $key)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>

                    <button
                        type="submit"
                        class="rounded-xl bg-red-500 px-5 py-2 text-sm font-semibold text-white hover:bg-red-600"
                    >
                        Filter
                    </button>

                    <a
                        href="{{ route('petugas.ec.assignments.index') }}"
                        class="rounded-xl border border-slate-200 px-4 py-2 text-center text-sm font-medium text-slate-600 hover:bg-slate-50"
                    >
                        Reset
                    </a>

                </form>
            </div>
        </div>

        {{-- Tabel --}}
        <div class="overflow-x-auto">

            <table class="w-full text-left text-sm">

                <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-4">Assignment</th>
                        <th class="px-5 py-4">Emergency Case</th>
                        <th class="px-5 py-4">Resource</th>
                        <th class="px-5 py-4">Lokasi Kejadian</th>
                        <th class="px-5 py-4">Waktu Assignment</th>
                        <th class="px-5 py-4">Status Assignment</th>
                        <th class="px-5 py-4">Progres Terakhir</th>
                        <th class="px-5 py-4">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">

                    @forelse ($assignments as $assignment)

                        @php
                            $case = $assignment->emergencyCase;
                            $location = $case?->lokasi;
                            $latestProgress = $assignment->statusPenanganan->last();

                            $statusColor = match ($assignment->status_assign) {
                                'diterima' => 'bg-blue-50 text-blue-700',
                                'selesai' => 'bg-emerald-50 text-emerald-700',
                                'ditolak' => 'bg-red-50 text-red-700',
                                'dibatalkan' => 'bg-slate-100 text-slate-600',
                                default => 'bg-slate-100 text-slate-600',
                            };
                        @endphp

                        <tr class="transition hover:bg-slate-50">

                            <td class="whitespace-nowrap px-5 py-4 font-semibold text-slate-800">
                                #{{ $assignment->assignment_id }}
                            </td>

                            <td class="px-5 py-4">
                                <p class="font-semibold text-slate-800">
                                    EC-{{ $assignment->case_id }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $case?->jenis_kejadian ?? '-' }}
                                </p>
                            </td>

                            <td class="px-5 py-4">
                                <span class="font-medium text-slate-700">
                                    {{ $assignment->resource?->jenis_resource ?? '-' }}
                                </span>
                            </td>

                            <td class="min-w-48 px-5 py-4 text-slate-600">
                                {{ $location?->alamat ?? 'Lokasi tidak tersedia' }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-slate-600">
                                {{ $assignment->waktu_assign
                                    ? \Carbon\Carbon::parse($assignment->waktu_assign)->format('d M Y, H:i')
                                    : '-' }}
                            </td>

                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusColor }}">
                                    {{ $statusLabels[$assignment->status_assign] ?? $assignment->status_assign }}
                                </span>
                            </td>

                            <td class="px-5 py-4">
                                @if ($latestProgress)
                                    <p class="font-medium text-slate-700">
                                        {{ $progressLabels[$latestProgress->status] ?? $latestProgress->status }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-400">
                                        {{ \Carbon\Carbon::parse($latestProgress->waktu_update)->format('d M Y, H:i') }}
                                    </p>
                                @else
                                    <span class="text-xs text-slate-400">
                                        Belum ada progres
                                    </span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-5 py-4">
                                <a
                                    href="{{ route('petugas.ec.cases.resource-matching', ['caseId' => $assignment->case_id]) }}"
                                    class="font-semibold text-red-500 hover:text-red-700"
                                >
                                    Detail
                                </a>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center">
                                <p class="font-semibold text-slate-700">
                                    Belum ada Assignment
                                </p>

                                <p class="mt-1 text-sm text-slate-400">
                                    Data penugasan akan muncul setelah Resource Matching dijalankan.
                                </p>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>
        </div>

        {{-- Pagination --}}
        <div class="border-t border-slate-100 p-5">
            {{ $assignments->links() }}
        </div>

    </div>

</div>

@endsection
