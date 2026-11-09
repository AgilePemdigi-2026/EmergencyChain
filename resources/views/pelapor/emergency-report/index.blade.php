
@extends('layouts.pelapor')

@section('title', 'Status Laporan - EmergencyChain')

@section('content')
<div class="mx-auto max-w-6xl">

    {{-- Header --}}
    <div class="mb-6 lg:mb-8">
        <p class="text-[11px] font-bold tracking-widest text-blue-500">
            PELAPOR / STATUS LAPORAN
        </p>

        <h1 class="mt-2 text-2xl font-semibold tracking-tight sm:text-3xl">
            Status Laporan Emergency Case
        </h1>

        <p class="mt-2 text-sm leading-relaxed text-slate-500">
            Pantau seluruh laporan emergency yang telah Anda kirimkan.
        </p>
    </div>

    {{-- Konten --}}
    <section class="overflow-hidden rounded-xl border border-slate-100
                    bg-white shadow-sm">

        {{-- Judul tabel --}}
        <div class="border-b border-slate-100 px-5 py-5 sm:px-6">
            <h2 class="text-base font-semibold text-slate-800">
                Daftar Laporan Saya
            </h2>

            <p class="mt-1 text-xs text-slate-400">
                Data laporan berdasarkan akun Pelapor yang sedang login.
            </p>
        </div>

        {{-- Search dan Filter --}}
        <form method="GET"
              action="{{ route('pelapor.laporan.index') }}"
              class="flex flex-col gap-3 border-b border-slate-100
                     p-4 sm:flex-row sm:items-center sm:p-6">

            <div class="min-w-0 flex-1">
                <label for="search" class="sr-only">
                    Cari laporan
                </label>

                <input
                    id="search"
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Cari Case ID atau jenis kejadian..."
                    class="w-full rounded-lg border border-slate-200
                           bg-white px-4 py-3 text-sm text-slate-700
                           placeholder:text-slate-400
                           focus:border-red-400 focus:outline-none"
                >
            </div>

            <div class="w-full sm:w-48">
                <label for="status" class="sr-only">
                    Filter status laporan
                </label>

                <select
                    id="status"
                    name="status"
                    class="w-full rounded-lg border border-slate-200
                           bg-white px-4 py-3 text-sm text-slate-700
                           focus:border-red-400 focus:outline-none"
                >
                    <option value="">Semua Status</option>

                    @foreach ($statuses as $item)
                        <option value="{{ $item }}"
                            @selected($status === $item)>
                            {{ $item }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button
                type="submit"
                class="rounded-lg bg-red-500 px-5 py-3
                       text-sm font-semibold text-white
                       transition hover:bg-red-600"
            >
                Cari
            </button>

            @if ($search !== '' || $status !== '')
                <a href="{{ route('pelapor.laporan.index') }}"
                   class="rounded-lg px-3 py-3 text-center
                          text-sm font-medium text-slate-500
                          hover:bg-slate-50">
                    Reset
                </a>
            @endif
        </form>

        {{-- Tabel --}}
        <div class="w-full overflow-x-auto">
            <table class="w-full min-w-[720px] text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold
                              uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-6 py-4">Case ID</th>
                        <th class="px-6 py-4">Jenis Kejadian</th>
                        <th class="px-6 py-4">Lokasi</th>
                        <th class="px-6 py-4">Waktu Laporan</th>
                        <th class="px-6 py-4">Status</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse ($cases as $case)
                        @php
                            $waktu = $case->waktu_laporan
                                ? \Illuminate\Support\Carbon::parse(
                                    $case->waktu_laporan
                                )
                                : null;

                            $nomorKasus = sprintf(
                                'EC-%s-%04d',
                                $waktu?->format('Y') ?? '0000',
                                $case->case_id
                            );

                            $statusLaporan = $case->status_laporan;

                            $statusClass = match (
                                mb_strtolower($statusLaporan ?? '')
                            ) {
                                'selesai' =>
                                    'bg-emerald-50 text-emerald-700',
                                'menunggu' =>
                                    'bg-amber-50 text-amber-700',
                                'diproses', 'dalam proses' =>
                                    'bg-blue-50 text-blue-700',
                                default =>
                                    'bg-slate-100 text-slate-600',
                            };
                        @endphp

                        <tr class="transition hover:bg-rose-50/40">
                            <td class="px-6 py-4">
                                <a
                                    href="{{ route('pelapor.laporan.show', $case->case_id) }}"
                                    class="font-semibold text-blue-500
                                        hover:text-blue-700 hover:underline"
                                >
                                    {{ $nomorKasus }}
                                </a>
                            </td>

                            <td class="px-6 py-4 text-slate-700">
                                {{ $case->jenis_kejadian }}
                            </td>

                            <td class="max-w-[220px] px-6 py-4
                                       text-slate-500">
                                <span class="block truncate"
                                      title="{{ $case->lokasi?->alamat ?? '' }}">
                                    {{ $case->lokasi?->alamat ?? '-' }}
                                </span>
                            </td>

                            <td class="whitespace-nowrap px-6 py-4
                                       text-slate-500">
                                {{ $waktu?->format('d/m/Y H:i') ?? '-' }}
                            </td>

                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full
                                             px-3 py-1 text-xs font-semibold
                                             {{ $statusClass }}">
                                    {{ $statusLaporan ?? '-' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <p class="font-semibold text-slate-700">
                                    Belum ada laporan ditemukan
                                </p>

                                <p class="mt-2 text-sm text-slate-400">
                                    Laporan yang sesuai akan muncul di sini.
                                </p>

                                @if ($search === '' && $status === '')
                                    <a
                                        href="{{ route('pelapor.laporan.create') }}"
                                        class="mt-4 inline-block text-sm
                                               font-semibold text-red-500
                                               hover:text-red-600"
                                    >
                                        Buat Laporan Emergency
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($cases->hasPages())
            <div class="border-t border-slate-100 px-5 py-4 sm:px-6">
                {{ $cases->links() }}
            </div>
        @endif
    </section>

</div>
@endsection
