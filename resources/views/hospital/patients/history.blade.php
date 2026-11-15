@extends('layouts.hospital')

@section('title', 'Riwayat Penanganan - EmergencyChain')

@section('content')
<div class="mx-auto max-w-7xl">

    {{-- Header --}}
    <div class="mb-8">
        <p class="text-sm font-bold uppercase tracking-wider text-red-500">
            Hospital Management
        </p>
        <h1 class="mt-2 text-3xl font-bold text-slate-800">
            Riwayat Penanganan
        </h1>
        <p class="mt-2 text-sm text-slate-500">
            Catatan seluruh perubahan status penanganan pasien di rumah sakit.
        </p>
    </div>

    {{-- Statistik --}}
    <div class="mb-6 rounded-2xl border border-rose-100 bg-white p-6 shadow-sm">
        <p class="text-sm font-medium text-slate-500">
            Total Riwayat Penanganan
        </p>
        <p class="mt-2 text-3xl font-bold text-slate-800">
            {{ $histories->total() }}
        </p>
    </div>

    {{-- Daftar Riwayat --}}
    <div class="overflow-hidden rounded-2xl border border-rose-100 bg-white shadow-sm">

        <div class="border-b border-slate-100 px-6 py-5">
            <h2 class="font-bold text-slate-800">
                Aktivitas Penanganan Pasien
            </h2>
            <p class="mt-1 text-sm text-slate-500">
                Riwayat diurutkan berdasarkan waktu perubahan terbaru.
            </p>
        </div>

        @if($histories->isEmpty())
            <div class="px-6 py-16 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-rose-50 text-2xl">
                    <span>+</span>
                </div>

                <h3 class="font-semibold text-slate-700">
                    Belum Ada Riwayat Penanganan
                </h3>

                <p class="mt-2 text-sm text-slate-500">
                    Riwayat akan muncul setelah terdapat perubahan status penanganan pasien.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-left text-sm">

                    <thead class="bg-rose-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-6 py-4">Waktu</th>
                            <th class="px-6 py-4">ID Pasien</th>
                            <th class="px-6 py-4">Emergency Case</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Keterangan</th>
                            <th class="px-6 py-4">Petugas</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @foreach($histories as $history)
                            @php
                                $patient = $history->informasiKorban;
                                $case = $patient?->emergencyCase;
                            @endphp

                            <tr class="transition hover:bg-rose-50/40">

                                <td class="whitespace-nowrap px-6 py-4 text-slate-600">
                                    {{ $history->created_at?->format('d M Y, H:i') ?? '-' }}
                                </td>

                                <td class="px-6 py-4 font-semibold text-slate-700">
                                    #{{ $patient?->informasi_id ?? '-' }}
                                </td>

                                <td class="px-6 py-4">
                                    <p class="font-semibold text-slate-700">
                                        #{{ $patient?->case_id ?? '-' }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $case?->jenis_kejadian ?? '-' }}
                                    </p>
                                </td>

                                <td class="px-6 py-4">
                                    @if($history->status === 'Dalam Penanganan')
                                        <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
                                            Dalam Penanganan
                                        </span>
                                    @elseif($history->status === 'Pasien Sudah Ditangani')
                                        <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                            Pasien Sudah Ditangani
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                            {{ $history->status }}
                                        </span>
                                    @endif
                                </td>

                                <td class="max-w-xs px-6 py-4 text-slate-600">
                                    {{ $history->keterangan ?: 'Tidak ada keterangan.' }}
                                </td>

                                <td class="px-6 py-4 text-slate-600">
                                    {{ $history->petugas?->nama ?? 'Pengguna tidak tersedia' }}
                                </td>

                                <td class="px-6 py-4 text-center">
                                    @if($patient)
                                        <a
                                            href="{{ route('hospital.patients.show', $patient->informasi_id) }}"
                                            class="inline-flex rounded-lg bg-red-500 px-4 py-2 text-xs font-semibold text-white transition hover:bg-red-600"
                                        >
                                            Detail
                                        </a>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="border-t border-slate-100 px-6 py-4">
                {{ $histories->links() }}
            </div>
        @endif

    </div>
</div>
@endsection
