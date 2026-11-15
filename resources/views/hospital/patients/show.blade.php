@extends('layouts.hospital')

@section('title', 'Detail Pasien - EmergencyChain')

@section('content')
@php
    $latestHistory = $patient->treatmentHistories->sortByDesc('created_at')->first();

    $currentStatus = $latestHistory?->status ?? 'Belum ada status';

    $statusClass = match ($currentStatus) {
        'Dalam Penanganan' => 'bg-amber-100 text-amber-700',
        'Pasien Sudah Ditangani' => 'bg-green-100 text-green-700',
        default => 'bg-slate-100 text-slate-600',
    };
@endphp

<div class="mx-auto max-w-5xl">

    {{-- Kembali --}}
    <a href="{{ route('hospital.patients.index') }}"
       class="font-semibold text-red-500 hover:text-red-600">
        ← Kembali ke Daftar Pasien
    </a>

    {{-- Header --}}
    <div class="mb-8 mt-6">
        <p class="text-sm font-bold uppercase tracking-wider text-red-500">
            Patient Management
        </p>

        <h1 class="mt-2 text-3xl font-bold text-slate-800">
            Detail Pasien #{{ $patient->informasi_id }}
        </h1>

        <p class="mt-2 text-sm text-slate-500">
            Emergency Case #{{ $patient->case_id }}
        </p>

        <div class="mt-4">
            <span class="inline-flex rounded-full px-4 py-2 text-sm font-semibold {{ $statusClass }}">
                {{ $currentStatus }}
            </span>
        </div>
    </div>

    {{-- Notifikasi --}}
    @if(session('success'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- Informasi Pasien --}}
    <div class="mb-6 rounded-2xl border border-rose-100 bg-white p-6 shadow-sm">
        <h2 class="mb-5 text-lg font-bold text-slate-800">
            Informasi Pasien
        </h2>

        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <p class="text-sm text-slate-500">ID Informasi Pasien</p>
                <p class="mt-1 font-semibold text-slate-800">
                    #{{ $patient->informasi_id }}
                </p>
            </div>

            <div>
                <p class="text-sm text-slate-500">Status Penerimaan</p>
                <p class="mt-1 font-semibold text-green-700">
                    {{ ucfirst($patient->status_penerimaan) }}
                </p>
            </div>

            <div>
                <p class="text-sm text-slate-500">Waktu Rujukan</p>
                <p class="mt-1 font-semibold text-slate-800">
                    {{ $patient->waktu_rujukan
                        ? \Carbon\Carbon::parse($patient->waktu_rujukan)->format('d M Y, H:i')
                        : '-' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-slate-500">Kondisi Korban</p>
                <p class="mt-1 font-semibold text-slate-800">
                    {{ $patient->kondisi_korban ?: 'Belum tersedia' }}
                </p>
            </div>
        </div>
    </div>

    {{-- Informasi Emergency Case --}}
    <div class="mb-6 rounded-2xl border border-rose-100 bg-white p-6 shadow-sm">
        <h2 class="mb-5 text-lg font-bold text-slate-800">
            Informasi Emergency Case
        </h2>

        @if($patient->emergencyCase)
            <div class="grid gap-6 sm:grid-cols-2">

                <div>
                    <p class="text-sm text-slate-500">Jenis Kejadian</p>
                    <p class="mt-1 font-semibold text-slate-800">
                        {{ $patient->emergencyCase->jenis_kejadian }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-slate-500">Waktu Laporan</p>
                    <p class="mt-1 font-semibold text-slate-800">
                        {{ $patient->emergencyCase->waktu_laporan
                            ? \Carbon\Carbon::parse($patient->emergencyCase->waktu_laporan)->format('d M Y, H:i')
                            : '-' }}
                    </p>
                </div>

                <div class="sm:col-span-2">
                    <p class="text-sm text-slate-500">Deskripsi Kondisi Kejadian</p>
                    <p class="mt-1 leading-relaxed text-slate-700">
                        {{ $patient->emergencyCase->kondisi ?: '-' }}
                    </p>
                </div>

                <div class="sm:col-span-2">
                    <p class="text-sm text-slate-500">Kebutuhan Penanganan</p>
                    <p class="mt-1 leading-relaxed text-slate-700">
                        {{ $patient->emergencyCase->kebutuhan ?: '-' }}
                    </p>
                </div>

                <div class="sm:col-span-2">
                    <p class="text-sm text-slate-500">Lokasi Kejadian</p>
                    <p class="mt-1 font-semibold text-slate-800">
                        {{ $patient->emergencyCase->lokasi?->alamat ?? 'Lokasi belum tersedia' }}
                    </p>
                </div>

            </div>
        @else
            <p class="text-sm text-slate-500">
                Informasi Emergency Case tidak ditemukan.
            </p>
        @endif
    </div>

    {{-- Update Status --}}
    <div class="mb-6 rounded-2xl border border-rose-100 bg-white p-6 shadow-sm">
        <h2 class="mb-2 text-lg font-bold text-slate-800">
            Pembaruan Status Penanganan
        </h2>

        <p class="mb-5 text-sm text-slate-500">
            Perbarui status sesuai perkembangan penanganan pasien.
        </p>

        <form method="POST"
              action="{{ route('hospital.patients.update-status', $patient->informasi_id) }}">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="status" class="mb-2 block text-sm font-semibold text-slate-700">
                    Status Penanganan
                </label>

                <select
                    name="status"
                    id="status"
                    required
                    class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-red-400 focus:outline-none focus:ring-2 focus:ring-red-100"
                >
                    <option value="Dalam Penanganan"
                        {{ $currentStatus === 'Dalam Penanganan' ? 'selected' : '' }}>
                        Dalam Penanganan
                    </option>

                    <option value="Pasien Sudah Ditangani"
                        {{ $currentStatus === 'Pasien Sudah Ditangani' ? 'selected' : '' }}>
                        Pasien Sudah Ditangani
                    </option>
                </select>
            </div>

            <div class="mb-5">
                <label for="keterangan" class="mb-2 block text-sm font-semibold text-slate-700">
                    Keterangan Penanganan (Opsional)
                </label>

                <textarea
                    name="keterangan"
                    id="keterangan"
                    rows="3"
                    maxlength="2000"
                    placeholder="Tuliskan keterangan perkembangan penanganan pasien..."
                    class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-red-400 focus:outline-none focus:ring-2 focus:ring-red-100"
                >{{ old('keterangan') }}</textarea>
            </div>

            <button
                type="submit"
                class="rounded-xl bg-red-500 px-6 py-3 text-sm font-bold text-white transition hover:bg-red-600"
            >
                Simpan Perubahan Status
            </button>
        </form>
    </div>

    {{-- Riwayat Penanganan --}}
    <div class="rounded-2xl border border-rose-100 bg-white p-6 shadow-sm">
        <h2 class="mb-2 text-lg font-bold text-slate-800">
            Riwayat Penanganan
        </h2>

        <p class="mb-6 text-sm text-slate-500">
            Catatan perubahan status penanganan pasien.
        </p>

        @if($patient->treatmentHistories->isEmpty())
            <div class="rounded-xl bg-slate-50 p-6 text-center">
                <p class="text-sm text-slate-500">
                    Belum ada riwayat penanganan.
                </p>
            </div>
        @else
            <div class="relative space-y-6 border-l-2 border-rose-100 pl-6">
                @foreach($patient->treatmentHistories->sortByDesc('created_at') as $history)
                    <div class="relative">
                        <span class="absolute -left-[31px] top-1 h-4 w-4 rounded-full border-2 border-white bg-red-400 ring-2 ring-rose-100"></span>

                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="font-semibold text-slate-800">
                                {{ $history->status }}
                            </span>

                            <span class="text-xs text-slate-400">
                                {{ $history->created_at?->format('d M Y, H:i') ?? '-' }}
                            </span>
                        </div>

                        <p class="mt-2 text-sm leading-relaxed text-slate-600">
                            {{ $history->keterangan ?: 'Tidak ada keterangan.' }}
                        </p>

                        <p class="mt-2 text-xs text-slate-400">
                            Diubah oleh:
                            {{ $history->petugas?->nama ?? 'Pengguna tidak tersedia' }}
                        </p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
