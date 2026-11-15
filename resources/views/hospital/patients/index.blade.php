@extends('layouts.hospital')

@section('title', 'Daftar Pasien - EmergencyChain')

@section('content')
<div class="mx-auto max-w-7xl">

    {{-- Header --}}
    <div class="mb-8">
        <p class="text-sm font-bold uppercase tracking-wider text-red-500">
            Hospital Management
        </p>
        <h1 class="mt-2 text-3xl font-bold text-slate-800">
            Daftar Pasien
        </h1>
        <p class="mt-2 text-sm text-slate-500">
            Kelola pasien yang telah diterima melalui sistem rujukan EmergencyChain.
        </p>
    </div>

    {{-- Notifikasi --}}
    @if(session('success'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    {{-- Statistik --}}
    <div class="mb-6 rounded-2xl border border-rose-100 bg-white p-6 shadow-sm">
        <p class="text-sm font-medium text-slate-500">
            Total Pasien Terdaftar
        </p>
        <p class="mt-2 text-3xl font-bold text-slate-800">
            {{ $patients->total() }}
        </p>
    </div>

    {{-- Daftar Pasien --}}
    <div class="overflow-hidden rounded-2xl border border-rose-100 bg-white shadow-sm">

        <div class="border-b border-slate-100 px-6 py-5">
            <h2 class="font-bold text-slate-800">
                Pasien yang Diterima
            </h2>
            <p class="mt-1 text-sm text-slate-500">
                Daftar pasien berdasarkan rujukan yang diterima rumah sakit.
            </p>
        </div>

        @if($patients->isEmpty())
            <div class="px-6 py-16 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-rose-50 text-2xl">
                    <span>+</span>
                </div>
                <h3 class="font-semibold text-slate-700">
                    Belum Ada Pasien
                </h3>
                <p class="mt-2 text-sm text-slate-500">
                    Pasien akan muncul di sini setelah rumah sakit menerima permintaan rujukan.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[750px] text-left text-sm">
                    <thead class="bg-rose-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-6 py-4">ID Pasien</th>
                            <th class="px-6 py-4">Emergency Case</th>
                            <th class="px-6 py-4">Jenis Kejadian</th>
                            <th class="px-6 py-4">Waktu Rujukan</th>
                            <th class="px-6 py-4">Status Penanganan</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @foreach($patients as $patient)
                            @php
                                $status = $patient->latestTreatmentHistory?->status
                                    ?? 'Belum ada status';
                            @endphp

                            <tr class="transition hover:bg-rose-50/40">
                                <td class="px-6 py-4 font-semibold text-slate-700">
                                    #{{ $patient->informasi_id }}
                                </td>

                                <td class="px-6 py-4 text-slate-600">
                                    #{{ $patient->case_id }}
                                </td>

                                <td class="px-6 py-4 text-slate-600">
                                    {{ $patient->emergencyCase?->jenis_kejadian ?? '-' }}
                                </td>

                                <td class="px-6 py-4 text-slate-600">
                                    {{ $patient->waktu_rujukan
                                        ? \Carbon\Carbon::parse($patient->waktu_rujukan)->format('d M Y, H:i')
                                        : '-' }}
                                </td>

                                <td class="px-6 py-4">
                                    @if($status === 'Dalam Penanganan')
                                        <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
                                            Dalam Penanganan
                                        </span>
                                    @elseif($status === 'Pasien Sudah Ditangani')
                                        <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                            Pasien Sudah Ditangani
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                            {{ $status }}
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <a
                                        href="{{ route('hospital.patients.show', $patient->informasi_id) }}"
                                        class="inline-flex rounded-lg bg-red-500 px-4 py-2 text-xs font-semibold text-white transition hover:bg-red-600"
                                    >
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="border-t border-slate-100 px-6 py-4">
                {{ $patients->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
