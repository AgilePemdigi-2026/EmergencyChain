
@extends('layouts.petugas-ec')

@section('title', 'Emergency Case - EmergencyChain')

@section('content')

@php
    // Data simulasi untuk pengembangan UI.
    // Akan diganti dengan data Eloquent setelah backend tersedia.
    

    $priorityColors = [
        'Kritis' => 'bg-red-100 text-red-600',
        'Tinggi' => 'bg-amber-100 text-amber-700',
        'Sedang' => 'bg-blue-100 text-blue-600',
    ];

    $statusColors = [
        'Menunggu' => 'bg-amber-100 text-amber-700',
        'Dalam Penanganan' => 'bg-red-100 text-red-600',
        'Selesai' => 'bg-emerald-100 text-emerald-700',
    ];
@endphp

<div class="mx-auto max-w-7xl">

    {{-- Heading --}}
    <div class="mb-5">
        <p class="text-[11px] font-bold tracking-widest text-blue-500">
            OPERASIONAL
        </p>

        <h1 class="mt-1 text-2xl font-semibold sm:text-3xl">
            Emergency Case
        </h1>
    </div>

    {{-- Pencarian dan Filter --}}
    <div class="mb-5 flex flex-col gap-3
                sm:flex-row sm:flex-wrap sm:items-center">

        <input
            id="case-search"
            type="search"
            placeholder="Cari Case ID, lokasi, atau kejadian..."
            aria-label="Cari Emergency Case"
            class="min-w-0 flex-1 rounded-lg border
                   border-slate-200 bg-white px-4 py-3
                   text-sm focus:border-red-400 focus:outline-none"
        >

        <select
            id="status-filter"
            aria-label="Filter status"
            class="rounded-lg border border-slate-200
                   bg-white px-3 py-3 text-sm"
        >
            <option value="">Semua Status</option>
            <option value="Menunggu">Menunggu</option>
            <option value="Dalam Penanganan">Dalam Penanganan</option>
            <option value="Selesai">Selesai</option>
        </select>

        <button
            id="apply-case-filter"
            type="button"
            class="rounded-lg border border-red-300
                   bg-white px-5 py-3 text-sm
                   font-semibold text-red-500
                   hover:bg-red-50"
        >
            Filter
        </button>

    </div>

    {{-- Tabel Emergency Case --}}
    <div class="overflow-hidden rounded-xl border
                border-slate-100 bg-white shadow-sm">

        <div class="w-full overflow-x-auto">
            <table class="w-full min-w-[750px] text-left text-xs">

                <thead class="bg-rose-50 text-[10px]
                            uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-4">Case ID</th>
                        <th class="px-4 py-4">Jenis Kejadian</th>
                        <th class="px-4 py-4">Lokasi</th>
                        <th class="px-4 py-4">Waktu</th>
                        <th class="px-4 py-4">Status</th>
                        <th class="px-4 py-4">Resource</th>
                        <th class="px-4 py-4">Aksi</th>
                    </tr>
                </thead>

                <tbody
                    id="case-table-body"
                    class="divide-y divide-slate-100"
                >
                    @forelse ($cases as $case)
                        @php
                            $statusClass = match ($case->status_laporan) {
                                'Menunggu' => 'bg-amber-100 text-amber-700',
                                'Dalam Penanganan' => 'bg-red-100 text-red-600',
                                'Selesai' => 'bg-emerald-100 text-emerald-700',
                                default => 'bg-slate-100 text-slate-600',
                            };
                        @endphp

                        <tr
                            class="case-row hover:bg-slate-50"
                            data-search="{{ strtolower(
                                $case->case_id . ' ' .
                                $case->jenis_kejadian . ' ' .
                                ($case->lokasi?->alamat ?? '')
                            ) }}"
                            data-status="{{ $case->status_laporan }}"
                        >
                            {{-- Case ID --}}
                            <td class="whitespace-nowrap px-4 py-4 font-semibold">
                                <a
                                    href="{{ route('petugas.ec.cases.show', ['caseId' => $case->case_id]) }}"
                                    class="text-blue-500 hover:underline"
                                >
                                    {{ $case->case_id }}
                                </a>
                            </td>

                            {{-- Jenis Kejadian --}}
                            <td class="px-4 py-4">
                                {{ $case->jenis_kejadian }}
                            </td>

                            {{-- Lokasi --}}
                            <td class="px-4 py-4">
                                {{ $case->lokasi?->alamat ?? 'Tidak tersedia' }}
                            </td>

                            {{-- Waktu Laporan --}}
                            <td class="whitespace-nowrap px-4 py-4">
                                {{ \Carbon\Carbon::parse($case->waktu_laporan)->format('d/m/Y H:i') }}
                            </td>

                            {{-- Status --}}
                            <td class="px-4 py-4">
                                <span class="inline-block whitespace-nowrap
                                            rounded-full px-3 py-1
                                            text-[11px] font-semibold
                                            {{ $statusClass }}">
                                    ● {{ $case->status_laporan }}
                                </span>
                            </td>

                            {{-- Resource: belum ada Assignment --}}
                            <td class="whitespace-nowrap px-4 py-4 text-slate-500">
                                Belum ditugaskan
                            </td>

                            {{-- Aksi Detail --}}
                            <td class="px-4 py-4">
                                <a
                                    href="{{ route('petugas.ec.cases.show', ['caseId' => $case->case_id]) }}"
                                    aria-label="Lihat detail Emergency Case {{ $case->case_id }}"
                                    class="inline-flex h-8 w-8 items-center
                                        justify-center rounded-lg
                                        text-blue-500 hover:bg-blue-50"
                                >
                                    <span aria-hidden="true">›</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="7"
                                class="px-4 py-10 text-center
                                    text-sm text-slate-500"
                            >
                                Belum ada Emergency Case.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Hasil pencarian kosong --}}
        <div
            id="no-case-results"
            class="hidden px-6 py-10 text-center
                text-sm text-slate-500"
            role="status"
        >
            Tidak ada Emergency Case yang sesuai dengan pencarian.
        </div>
    </div>


        <div id="no-case-results"
             class="hidden px-6 py-10 text-center text-sm text-slate-500"
             role="status">
            Tidak ada Emergency Case yang sesuai dengan pencarian.
        </div>
    </div>

    <p id="case-result-count"
       class="mt-4 text-center text-xs text-slate-500"
       aria-live="polite"
    >
        Menampilkan {{ count($cases) }} laporan
    </p>

</div>

{{-- Pencarian dan filter Emergency Case --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const search = document.getElementById('case-search');
    const status = document.getElementById('status-filter');
    const button = document.getElementById('apply-case-filter');

    const rows = [...document.querySelectorAll('.case-row')];
    const noResults = document.getElementById('no-case-results');
    const resultCount = document.getElementById('case-result-count');

    if (!search || !status || !button || !noResults || !resultCount) {
        return;
    }

    function applyFilters() {
        const keyword = search.value.trim().toLocaleLowerCase('id-ID');
        let visibleCount = 0;

        rows.forEach((row) => {
            const matchesSearch =
                row.dataset.search.includes(keyword);

            const matchesStatus =
                !status.value ||
                row.dataset.status === status.value;

            const visible = matchesSearch && matchesStatus;

            row.classList.toggle('hidden', !visible);

            if (visible) visibleCount++;
        });

        noResults.classList.toggle('hidden', visibleCount !== 0);

        resultCount.textContent =
            `Menampilkan ${visibleCount} dari ${rows.length} laporan`;
    }

    button.addEventListener('click', applyFilters);
    search.addEventListener('input', applyFilters);
    
});
</script>

@endsection
