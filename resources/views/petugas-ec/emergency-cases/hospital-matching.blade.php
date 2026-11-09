
@extends('layouts.petugas-ec')

@section('title', 'Hospital Matching - EmergencyChain')

@section('content')
@php
    $status = $referral?->status;

    $statusStyles = [
        'mencari' => 'bg-blue-50 text-blue-700 ring-blue-200',
        'diterima' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'gagal' => 'bg-red-50 text-red-700 ring-red-200',
    ];

    $requestStyles = [
        'menunggu' => 'bg-amber-50 text-amber-700',
        'diterima' => 'bg-emerald-50 text-emerald-700',
        'ditolak' => 'bg-red-50 text-red-700',
        'timeout' => 'bg-slate-100 text-slate-600',
    ];

    $statusLabel = match ($status) {
        'mencari' => 'Sedang Mencari',
        'diterima' => 'Rujukan Diterima',
        'gagal' => 'Rujukan Gagal',
        default => match ($eligibility) {
            'perlu_verifikasi' => 'Perlu Verifikasi',
            'tidak_perlu_rs' => 'Tidak Memerlukan Rujukan',
            'perlu_rs' => 'Belum Dimulai',
            default => 'Belum Dimulai',
        },
    };

    $badgeStyle = $statusStyles[$status] ??
        match ($eligibility) {
            'tidak_perlu_rs' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'perlu_verifikasi' => 'bg-amber-50 text-amber-700 ring-amber-200',
            default => 'bg-slate-100 text-slate-600 ring-slate-200',
        };
@endphp

<div class="mx-auto max-w-7xl space-y-6">

    {{-- Header --}}
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-red-500">
                EMERGENCY CASE / {{ $case->case_id }}
            </p>

            <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-800">
                Hospital Matching
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Pemantauan proses pencarian dan rujukan Rumah Sakit.
            </p>
        </div>

        <a href="{{ route('petugas.ec.cases.show', ['caseId' => $case->case_id]) }}"
           class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm transition hover:bg-slate-50">
            <span>&larr;</span>
            Kembali ke Detail Kasus
        </a>
    </div>


    {{-- Informasi Emergency Case --}}
    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

        <div class="border-b border-slate-100 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">
                Informasi Emergency Case
            </h2>
            <p class="mt-1 text-xs text-slate-400">
                Informasi kejadian yang menjadi dasar proses rujukan.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-x-8 gap-y-6 p-6 sm:grid-cols-2 lg:grid-cols-3">

            <div>
                <p class="text-xs font-medium text-slate-400">Jenis Kejadian</p>
                <p class="mt-1.5 text-sm font-semibold text-slate-700">
                    {{ $case->jenis_kejadian }}
                </p>
            </div>

            <div>
                <p class="text-xs font-medium text-slate-400">Status Penanganan</p>
                <p class="mt-1.5 text-sm font-semibold text-slate-700">
                    {{ $case->status_laporan }}
                </p>
            </div>

            <div>
                <p class="text-xs font-medium text-slate-400">Waktu Laporan</p>
                <p class="mt-1.5 text-sm font-semibold text-slate-700">
                    {{ \Carbon\Carbon::parse($case->waktu_laporan)->format('d/m/Y H:i') }}
                </p>
            </div>

            <div>
                <p class="text-xs font-medium text-slate-400">Lokasi Kejadian</p>
                <p class="mt-1.5 text-sm font-semibold text-slate-700">
                    {{ $case->lokasi?->alamat ?? 'Tidak tersedia' }}
                </p>
            </div>

<div class="text-left">
    <p class="text-left text-xs font-medium text-slate-400">
        Kondisi Kejadian
    </p>
    <p class="mt-1.5 text-left whitespace-pre-wrap break-words text-sm text-slate-700">
        {{ $case->kondisi ?? '-' }}
    </p>
</div>

            <div>
                <p class="text-xs font-medium text-slate-400">Kebutuhan Penanganan</p>
                <p class="mt-1.5 text-sm font-semibold text-slate-700">
                    {{ $case->kebutuhan ?? '-' }}
                </p>
            </div>

        </div>
    </section>


    {{-- Status Hospital Matching --}}
    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

        <div class="flex flex-col justify-between gap-3 border-b border-slate-100 px-6 py-4 sm:flex-row sm:items-center">

            <div>
                <h2 class="text-sm font-bold text-slate-800">
                    Status Hospital Matching
                </h2>
                <p class="mt-1 text-xs text-slate-400">
                    Status terkini proses verifikasi dan rujukan.
                </p>
            </div>

            <span class="inline-flex w-fit items-center rounded-full px-3 py-1.5 text-xs font-semibold ring-1 ring-inset {{ $badgeStyle }}">
                {{ $statusLabel }}
            </span>

        </div>


        <div class="p-6">

            @if (!$referral)

                {{-- Membutuhkan Verifikasi --}}
                @if ($eligibility === 'perlu_verifikasi')

                    <div class="flex gap-4 rounded-lg border border-amber-200 bg-amber-50/60 p-5">

                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-100 text-lg text-amber-700">
                            !
                        </div>

                        <div class="min-w-0 flex-1">
                            <h3 class="text-sm font-bold text-amber-900">
                                Verifikasi Petugas EC Diperlukan
                            </h3>

                            <p class="mt-1.5 text-sm leading-relaxed text-amber-800">
                                Kebutuhan rujukan Rumah Sakit belum dapat dipastikan
                                berdasarkan informasi laporan. Silakan tinjau
                                kondisi dan kebutuhan korban sebelum menentukan keputusan.
                            </p>

                            <div class="mt-5 flex flex-col gap-3 sm:flex-row">

                                <form
                                    action="{{ route('petugas.ec.cases.hospital-verification', [
                                        'caseId' => $case->case_id
                                    ]) }}"
                                    method="POST"
                                    class="flex-1"
                                >
                                    @csrf
                                    <input type="hidden" name="decision" value="perlu_rs">

                                    <button
                                        type="submit"
                                        class="w-full rounded-lg bg-red-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-600"
                                    >
                                        Verifikasi & Cari Rumah Sakit
                                    </button>
                                </form>

                                <form
                                    action="{{ route('petugas.ec.cases.hospital-verification', [
                                        'caseId' => $case->case_id
                                    ]) }}"
                                    method="POST"
                                    class="flex-1"
                                >
                                    @csrf
                                    <input type="hidden" name="decision" value="tidak_perlu_rs">

                                    <button
                                        type="submit"
                                        class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
                                    >
                                        Tidak Memerlukan Rujukan
                                    </button>
                                </form>

                            </div>
                        </div>
                    </div>


                {{-- Tidak Membutuhkan Rujukan --}}
                @elseif ($eligibility === 'tidak_perlu_rs')

                    <div class="flex gap-4">

                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                            <span class="text-lg font-bold">&#10003;</span>
                        </div>

                        <div>
                            <h3 class="text-sm font-bold text-slate-800">
                                Verifikasi Selesai
                            </h3>

                            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">
                                Kasus telah ditinjau dan diverifikasi oleh Petugas EC.
                                Berdasarkan hasil verifikasi, kasus dinyatakan tidak
                                memerlukan rujukan ke Rumah Sakit sehingga proses
                                Hospital Matching tidak dijalankan.
                            </p>
                        </div>

                    </div>


                {{-- Belum Dimulai --}}
                @else

                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <h3 class="text-sm font-semibold text-slate-700">
                            Proses Rujukan Belum Dimulai
                        </h3>

                        <p class="mt-1.5 text-sm text-slate-500">
                            Kebutuhan Rumah Sakit terindikasi, tetapi proses
                            rujukan belum tercatat. Periksa proses otomatis
                            dan log aplikasi.
                        </p>
                    </div>

                @endif


            {{-- Sedang Mencari --}}
            @elseif ($status === 'mencari')

                <div class="flex gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-50 text-blue-600">
                        <span class="text-lg">&#8635;</span>
                    </div>

                    <div>
                        <h3 class="text-sm font-bold text-slate-800">
                            Pencarian Rumah Sakit Sedang Berlangsung
                        </h3>

                        <p class="mt-1.5 text-sm leading-relaxed text-slate-500">
                            Sistem sedang menunggu respons dari Rumah Sakit yang
                            dipilih. Jika permintaan ditolak atau mengalami timeout,
                            sistem akan mencoba kandidat berikutnya secara otomatis.
                        </p>
                    </div>
                </div>


            {{-- Diterima --}}
            @elseif ($status === 'diterima')

                <div class="flex gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                        <span class="text-lg font-bold">&#10003;</span>
                    </div>

                    <div>
                        <h3 class="text-sm font-bold text-slate-800">
                            Rumah Sakit Berhasil Ditemukan
                        </h3>

                        <p class="mt-1.5 text-sm leading-relaxed text-slate-500">
                            Permintaan rujukan telah diterima oleh Rumah Sakit.
                            Informasi Rumah Sakit tujuan tersedia pada bagian di bawah.
                        </p>
                    </div>
                </div>


            {{-- Gagal --}}
            @elseif ($status === 'gagal')

                <div class="flex gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                        <span class="text-lg font-bold">!</span>
                    </div>

                    <div>
                        <h3 class="text-sm font-bold text-slate-800">
                            Proses Rujukan Gagal
                        </h3>

                        <p class="mt-1.5 text-sm leading-relaxed text-slate-500">
                            {{ $referral->failure_reason ?? 'Tidak ada Rumah Sakit yang dapat menerima rujukan.' }}
                        </p>
                    </div>
                </div>

            @endif

        </div>
    </section>


    {{-- Rumah Sakit Tujuan --}}
    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

        <div class="border-b border-slate-100 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">
                Rumah Sakit Tujuan
            </h2>

            <p class="mt-1 text-xs text-slate-400">
                Informasi Rumah Sakit yang menerima rujukan.
            </p>
        </div>

        <div class="p-6">

            @if ($referral?->acceptedHospital)

                <div class="flex items-start gap-4 rounded-lg border border-emerald-200 bg-emerald-50/50 p-5">

                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">
                        <span class="text-lg">&#10003;</span>
                    </div>

                    <div class="min-w-0">
                        <h3 class="text-base font-bold text-slate-800">
                            {{ $referral->acceptedHospital->nama_rs }}
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            {{ $referral->acceptedHospital->alamat ?? 'Alamat tidak tersedia' }}
                        </p>

                        <span class="mt-3 inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                            Rujukan Diterima
                        </span>
                    </div>

                </div>

            @else

                <div class="flex flex-col items-center justify-center rounded-lg border border-dashed border-slate-200 bg-slate-50/70 px-5 py-9 text-center">

                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                        <span class="text-xl">&#43;</span>
                    </div>

                    <h3 class="mt-3 text-sm font-semibold text-slate-700">
                        Belum Ada Rumah Sakit Tujuan
                    </h3>

                    <p class="mt-1 max-w-md text-sm leading-relaxed text-slate-500">
                        @if ($eligibility === 'tidak_perlu_rs')
                            Kasus tidak memerlukan rujukan berdasarkan hasil verifikasi Petugas EC.
                        @elseif ($status === 'mencari')
                            Sistem sedang mencari Rumah Sakit yang sesuai dengan kebutuhan kasus.
                        @else
                            Belum ada Rumah Sakit yang menerima permintaan rujukan.
                        @endif
                    </p>

                </div>

            @endif

        </div>
    </section>


    {{-- Riwayat Permintaan Rumah Sakit --}}
    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

        <div class="border-b border-slate-100 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">
                Riwayat Permintaan Rumah Sakit
            </h2>

            <p class="mt-1 text-xs text-slate-400">
                Riwayat Rumah Sakit yang telah menerima permintaan rujukan dari sistem.
            </p>
        </div>

        @if ($requests->isEmpty())

            <div class="flex flex-col items-center justify-center px-5 py-9 text-center">

                <div class="flex h-11 w-11 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                    <span class="text-xl">&#9776;</span>
                </div>

                <h3 class="mt-3 text-sm font-semibold text-slate-700">
                    Belum Ada Riwayat Permintaan
                </h3>

                <p class="mt-1 max-w-lg text-sm leading-relaxed text-slate-500">
                    @if ($eligibility === 'tidak_perlu_rs')
                        Tidak ada permintaan rujukan yang dibuat karena kasus telah diverifikasi tidak memerlukan rujukan Rumah Sakit.
                    @else
                        Belum ada permintaan Rumah Sakit yang tercatat dalam sistem.
                    @endif
                </p>

            </div>

        @else

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">

                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-6 py-3">Urutan</th>
                            <th class="px-6 py-3">Rumah Sakit</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Waktu Permintaan</th>
                            <th class="px-6 py-3">Batas Respons</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">

                        @foreach ($requests as $hospitalRequest)

                            <tr class="transition hover:bg-slate-50">

                                <td class="whitespace-nowrap px-6 py-4 text-slate-600">
                                    {{ $hospitalRequest->attempt_order }}
                                </td>

                                <td class="px-6 py-4 font-semibold text-slate-700">
                                    {{ $hospitalRequest->rumahSakit?->nama_rs ?? 'Tidak tersedia' }}
                                </td>

                                <td class="whitespace-nowrap px-6 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $requestStyles[$hospitalRequest->status] ?? 'bg-slate-100 text-slate-600' }}">
                                        {{ ucfirst($hospitalRequest->status) }}
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-6 py-4 text-slate-500">
                                    {{ $hospitalRequest->requested_at?->format('d/m/Y H:i:s') ?? '-' }}
                                </td>

                                <td class="whitespace-nowrap px-6 py-4 text-slate-500">
                                    {{ $hospitalRequest->expires_at?->format('d/m/Y H:i:s') ?? '-' }}
                                </td>

                            </tr>

                            @if ($hospitalRequest->rejection_reason)

                                <tr>
                                    <td colspan="5" class="bg-red-50 px-6 py-3 text-xs text-red-700">
                                        <span class="font-semibold">Alasan penolakan:</span>
                                        {{ $hospitalRequest->rejection_reason }}
                                    </td>
                                </tr>

                            @endif

                        @endforeach

                    </tbody>
                </table>
            </div>

        @endif

    </section>


    {{-- Footer Action --}}
    <div class="flex justify-end">

        <a href="{{ route('petugas.ec.cases.hospital-matching', ['caseId' => $case->case_id]) }}"
           class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm transition hover:bg-slate-50">

            <span>&#8635;</span>
            Perbarui Status

        </a>

    </div>

</div>


{{-- Polling selama proses pencarian berlangsung --}}
@if ($status === 'mencari')
    <script>
        setTimeout(() => {
            window.location.reload();
        }, 10000);
    </script>
@endif

@endsection
