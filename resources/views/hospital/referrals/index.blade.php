
@extends('layouts.hospital')

@section('title', 'Dashboard Rumah Sakit - EmergencyChain')

@section('content')
<div class="space-y-7">

    {{-- JUDUL DASHBOARD --}}
    <div>
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-500">
            {{ auth()->user()->rumahSakit?->nama_rs ?? 'Rumah Sakit' }}
        </p>

        <h1 class="mt-2 text-3xl font-semibold text-slate-800">
            Dashboard Rumah Sakit
        </h1>

        <p class="mt-2 text-sm text-slate-500">
            Pantau dan konfirmasi permintaan rujukan pasien dari EmergencyChain.
        </p>
    </div>

    {{-- NOTIFIKASI --}}
    @if(session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- KARTU STATISTIK --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <div class="flex items-center gap-4 rounded-2xl border border-rose-100 bg-white p-5 shadow-sm">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-500">
                <svg class="h-6 w-6" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="1.8"
                     stroke-linecap="round" stroke-linejoin="round">
                    <rect x="4" y="4" width="16" height="16" rx="3"/>
                    <path d="M12 8v8M8 12h8"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500">Incoming Cases</p>
                <p class="mt-1 text-3xl font-bold text-slate-800">
                    {{ $stats['incoming'] }}
                </p>
                <p class="text-xs text-slate-400">Total permintaan masuk</p>
            </div>
        </div>

        <div class="flex items-center gap-4 rounded-2xl border border-rose-100 bg-white p-5 shadow-sm">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                <svg class="h-6 w-6" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="1.8"
                     stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="M12 7v5l3 2"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500">Menunggu Penerimaan</p>
                <p class="mt-1 text-3xl font-bold text-slate-800">
                    {{ $stats['waiting'] }}
                </p>
                <p class="text-xs text-slate-400">Perlu konfirmasi</p>
            </div>
        </div>

        <div class="flex items-center gap-4 rounded-2xl border border-rose-100 bg-white p-5 shadow-sm">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                <svg class="h-6 w-6" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="1.8"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="m5 12 4 4L19 6"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500">Diterima Hari Ini</p>
                <p class="mt-1 text-3xl font-bold text-slate-800">
                    {{ $stats['accepted_today'] }}
                </p>
                <p class="text-xs text-slate-400">Rujukan diterima</p>
            </div>
        </div>

        <div class="flex items-center gap-4 rounded-2xl border border-rose-100 bg-white p-5 shadow-sm">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-500">
                <svg class="h-6 w-6" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="1.8"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 6l12 12M18 6 6 18"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500">Ditolak</p>
                <p class="mt-1 text-3xl font-bold text-slate-800">
                    {{ $stats['rejected'] }}
                </p>
                <p class="text-xs text-slate-400">Total penolakan</p>
            </div>
        </div>

    </div>

    {{-- DAFTAR PERMINTAAN --}}
    <section class="overflow-hidden rounded-2xl border border-rose-100 bg-white shadow-sm">

        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
            <div>
                <h2 class="font-semibold text-slate-800">
                    Incoming Emergency Cases
                </h2>
                <p class="mt-1 text-xs text-slate-400">
                    Permintaan aktif ditampilkan terlebih dahulu, diikuti riwayat.
                </p>
            </div>

            <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-600">
                {{ $stats['waiting'] }} Menunggu
            </span>
        </div>

        <div class="grid items-stretch gap-4 p-4 md:grid-cols-2">

            @forelse($requests as $referralRequest)

                @php
                    $caseId = $referralRequest->referral?->case_id;
                    $case = $casesById->get($caseId);

                    $isActive =
                        $referralRequest->status === 'menunggu'
                        && $referralRequest->referral?->status === 'mencari'
                        && $referralRequest->expires_at->isFuture();

                    $isExpired =
                        $referralRequest->status === 'menunggu'
                        && !$isActive;

                    $statusText = match (true) {
                        $isActive => 'Menunggu Konfirmasi',
                        $isExpired => 'Lewat Batas Waktu',
                        $referralRequest->status === 'diterima' => 'Diterima',
                        $referralRequest->status === 'ditolak' => 'Ditolak',
                        $referralRequest->status === 'timeout' => 'Timeout',
                        default => ucfirst($referralRequest->status),
                    };

                    $statusClass = match (true) {
                        $isActive => 'bg-amber-50 text-amber-700',
                        $referralRequest->status === 'diterima' => 'bg-emerald-50 text-emerald-700',
                        $isExpired => 'bg-slate-100 text-slate-600',
                        $referralRequest->status === 'ditolak' => 'bg-red-50 text-red-600',
                        $referralRequest->status === 'timeout' => 'bg-slate-100 text-slate-600',
                        default => 'bg-slate-100 text-slate-600',
                    };
                @endphp

                <article class="flex h-full flex-col rounded-xl border border-slate-200 p-4 transition hover:border-rose-200 hover:shadow-sm">

                    {{-- STATUS --}}
                    <div class="flex flex-wrap items-center justify-between gap-2">

                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                            {{ $statusText }}
                        </span>

                        <span class="text-xs text-slate-400">
                            Percobaan ke-{{ $referralRequest->attempt_order }}
                        </span>
                    </div>

                    {{-- NOMOR CASE --}}
                    <p class="mt-4 text-xs font-bold text-blue-500">
                        EC-{{ str_pad($caseId ?? 0, 4, '0', STR_PAD_LEFT) }}
                    </p>

                    {{-- JENIS KEJADIAN --}}
                    <h3 class="mt-2 text-base font-semibold text-slate-800">
                        {{ $case?->jenis_kejadian ?? 'Jenis kejadian belum tersedia' }}
                    </h3>

                    {{-- IDENTITAS PERMINTAAN --}}
                    <p class="mt-1 text-xs text-slate-400">
                        Permintaan Rujukan #{{ $referralRequest->id }}
                    </p>

                    {{-- LOKASI --}}
                    <div class="mt-3 flex items-start gap-2 text-sm text-slate-500">
                        <svg class="mt-0.5 h-4 w-4 shrink-0"
                             viewBox="0 0 24 24"
                             fill="none" stroke="currentColor"
                             stroke-width="1.8"
                             stroke-linecap="round"
                             stroke-linejoin="round">
                            <path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/>
                            <circle cx="12" cy="10" r="2.5"/>
                        </svg>

                        <span>
                            {{ $case?->lokasi?->alamat ?? 'Lokasi belum tersedia' }}
                        </span>
                    </div>

                    {{-- KEBUTUHAN PASIEN --}}
                    <div class="mt-4 rounded-lg bg-rose-50 p-3">
                        <p class="text-xs text-slate-400">
                            Kebutuhan Pasien
                        </p>

                        <p class="mt-1 text-sm font-semibold text-slate-700">
                            {{ $case?->kebutuhan ?: 'Belum ada keterangan kebutuhan' }}
                        </p>
                    </div>

                    {{-- WAKTU --}}
                    <div class="mt-4 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500">
                        <span>
                            Dikirim:
                            {{ $referralRequest->requested_at->format('d M, H:i') }}
                        </span>

                        <span class="{{ $isActive ? 'font-semibold text-red-500' : '' }}">
                            Batas:
                            {{ $referralRequest->expires_at->format('H:i:s') }}
                        </span>
                    </div>

                    {{-- TOMBOL --}}
                    <div class="mt-auto flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">

                        <a
                            href="{{ route('hospital.referrals.show', $referralRequest->id) }}"
                            class="inline-flex items-center justify-center rounded-lg bg-slate-100 px-4 py-2.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-200"
                        >
                            Lihat Detail
                        </a>

                        @if($isActive)
                            <div class="flex items-center gap-2">

                                <form
                                    method="POST"
                                    action="{{ route('hospital.referrals.respond', $referralRequest->id) }}"
                                >
                                    @csrf
                                    <input type="hidden" name="decision" value="ditolak">

                                    <button
                                        type="submit"
                                        class="rounded-lg border border-red-100 bg-red-50 px-4 py-2.5 text-xs font-semibold text-red-600 transition hover:bg-red-100"
                                    >
                                        Tolak
                                    </button>
                                </form>

                                <form
                                    method="POST"
                                    action="{{ route('hospital.referrals.respond', $referralRequest->id) }}"
                                >
                                    @csrf
                                    <input type="hidden" name="decision" value="diterima">

                                    <button
                                        type="submit"
                                        class="rounded-lg bg-red-500 px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-red-600"
                                    >
                                        Terima
                                    </button>
                                </form>

                            </div>
                        @else
                            <span class="text-xs text-slate-400">
                                Tidak memerlukan respons
                            </span>
                        @endif

                    </div>
                </article>

            @empty
                <div class="col-span-full py-12 text-center">
                    <p class="text-sm font-medium text-slate-600">
                        Belum ada permintaan rujukan.
                    </p>
                    <p class="mt-2 text-xs text-slate-400">
                        Permintaan dari sistem Hospital Matching akan muncul di sini.
                    </p>
                </div>
            @endforelse

        </div>

        <div class="px-5 pb-5">
            {{ $requests->links() }}
        </div>

    </section>
</div>
@endsection
