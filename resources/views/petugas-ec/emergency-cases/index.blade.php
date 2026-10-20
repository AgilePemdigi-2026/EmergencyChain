
@extends('layouts.petugas-ec')

@section('title', 'Emergency Case - EmergencyChain')

@section('content')

@php
    // Data simulasi untuk pengembangan UI.
    // Akan diganti dengan data Eloquent setelah backend tersedia.
    $cases = [
        [
            'id' => 'EC-2026-0011',
            'jenis' => 'Kecelakaan Lalu Lintas',
            'lokasi' => 'Jl. Kartini Timur',
            'waktu' => 'Hari ini, 11.55',
            'prioritas' => 'Kritis',
            'status' => 'Dalam Penanganan',
            'resource' => 'Ambulans 07',
        ],
        [
            'id' => 'EC-2026-0010',
            'jenis' => 'Kecelakaan Lalu Lintas',
            'lokasi' => 'Jl. Ahmad Yani',
            'waktu' => 'Hari ini, 11.40',
            'prioritas' => 'Tinggi',
            'status' => 'Menunggu',
            'resource' => '-',
        ],
        [
            'id' => 'EC-2026-0009',
            'jenis' => 'Kebakaran',
            'lokasi' => 'Jl. Diponegoro',
            'waktu' => 'Hari ini, 10.10',
            'prioritas' => 'Tinggi',
            'status' => 'Dalam Penanganan',
            'resource' => 'Damkar 02',
        ],
        [
            'id' => 'EC-2026-0008',
            'jenis' => 'Kondisi Medis Darurat',
            'lokasi' => 'Jl. Raya Darmo',
            'waktu' => 'Hari ini, 09.53',
            'prioritas' => 'Tinggi',
            'status' => 'Dalam Penanganan',
            'resource' => 'Ambulans 05',
        ],
        [
            'id' => 'EC-2026-0007',
            'jenis' => 'Kecelakaan Lalu Lintas',
            'lokasi' => 'Jl. Keninting',
            'waktu' => 'Hari ini, 09.23',
            'prioritas' => 'Sedang',
            'status' => 'Selesai',
            'resource' => 'Ambulans 03',
        ],
        [
            'id' => 'EC-2026-0006',
            'jenis' => 'Kondisi Medis Darurat',
            'lokasi' => 'Jl. Gebang',
            'waktu' => 'Hari ini, 09.20',
            'prioritas' => 'Kritis',
            'status' => 'Selesai',
            'resource' => 'Ambulans 01',
        ],
        [
            'id' => 'EC-2026-0005',
            'jenis' => 'Pohon Tumbang',
            'lokasi' => 'Jl. Ngagel',
            'waktu' => 'Hari ini, 08.01',
            'prioritas' => 'Sedang',
            'status' => 'Selesai',
            'resource' => 'BPBD 01',
        ],
        [
            'id' => 'EC-2026-0004',
            'jenis' => 'Kecelakaan Lalu Lintas',
            'lokasi' => 'Jl. Mulyosari',
            'waktu' => 'Hari ini, 07.56',
            'prioritas' => 'Tinggi',
            'status' => 'Selesai',
            'resource' => 'Ambulans 07',
        ],
        [
            'id' => 'EC-2026-0003',
            'jenis' => 'Kecelakaan Lalu Lintas',
            'lokasi' => 'Jl. Kenjeran',
            'waktu' => 'Hari ini, 06.36',
            'prioritas' => 'Sedang',
            'status' => 'Selesai',
            'resource' => 'Ambulans 03',
        ],
    ];

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
            id="priority-filter"
            aria-label="Filter prioritas"
            class="rounded-lg border border-slate-200
                   bg-white px-3 py-3 text-sm"
        >
            <option value="">Semua Prioritas</option>
            <option value="Kritis">Kritis</option>
            <option value="Tinggi">Tinggi</option>
            <option value="Sedang">Sedang</option>
        </select>

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
            <table class="w-full min-w-[850px] text-left text-xs">

                <thead class="bg-rose-50 text-[10px]
                              uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-4">Case ID</th>
                        <th class="px-4 py-4">Jenis Kejadian</th>
                        <th class="px-4 py-4">Lokasi</th>
                        <th class="px-4 py-4">Waktu</th>
                        <th class="px-4 py-4">Prioritas</th>
                        <th class="px-4 py-4">Status</th>
                        <th class="px-4 py-4">Resource</th>
                        <th class="px-4 py-4"></th>
                    </tr>
                </thead>

                <tbody id="case-table-body" class="divide-y divide-slate-100">
                    @foreach ($cases as $case)
                        <tr
                            class="case-row hover:bg-slate-50"
                            data-search="{{ strtolower($case['id'] . ' ' . $case['jenis'] . ' ' . $case['lokasi']) }}"
                            data-priority="{{ $case['prioritas'] }}"
                            data-status="{{ $case['status'] }}"
                        >   
                            <td class="whitespace-nowrap px-4 py-4 font-semibold">
                                <a
                                    href="{{ route('petugas.ec.cases.show', ['caseId' => $case['id']]) }}"
                                    class="text-blue-500 hover:underline"
                                >
                                    {{ $case['id'] }}
                                </a>
                            </td>

                            <td class="px-4 py-4">
                                {{ $case['jenis'] }}
                            </td>

                            <td class="px-4 py-4">
                                {{ $case['lokasi'] }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4">
                                {{ $case['waktu'] }}
                            </td>

                            <td class="px-4 py-4">
                                <span class="inline-block whitespace-nowrap rounded-full
                                             px-3 py-1 text-[11px] font-semibold
                                             {{ $priorityColors[$case['prioritas']] }}">
                                    ● {{ $case['prioritas'] }}
                                </span>
                            </td>

                            <td class="px-4 py-4">
                                <span class="inline-block whitespace-nowrap rounded-full
                                             px-3 py-1 text-[11px] font-semibold
                                             {{ $statusColors[$case['status']] }}">
                                    ● {{ $case['status'] }}
                                </span>
                            </td>

                            <td class="whitespace-nowrap px-4 py-4">
                                {{ $case['resource'] }}
                            </td>

                            <td class="px-4 py-4">
                                <a
                                    href="{{ route('petugas.ec.cases.show', ['caseId' => $case['id']]) }}"
                                    aria-label="Lihat detail {{ $case['id'] }}"
                                    class="inline-flex h-8 w-8 items-center
                                        justify-center rounded-lg text-blue-500
                                        hover:bg-blue-50"
                                >
                                    <span aria-hidden="true">›</span>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div id="no-case-results"
             class="hidden px-6 py-10 text-center text-sm text-slate-500"
             role="status">
            Tidak ada Emergency Case yang sesuai dengan pencarian.
        </div>
    </div>

    <p id="case-result-count"
       class="mt-4 text-center text-xs text-slate-500"
       aria-live="polite">
        Menampilkan {{ count($cases) }} laporan contoh
    </p>

    <p class="mt-2 text-center text-xs text-slate-400">
        Data simulasi frontend. Belum terhubung ke database.
    </p>

</div>

{{-- Filter frontend sementara --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const search = document.getElementById('case-search');
    const priority = document.getElementById('priority-filter');
    const status = document.getElementById('status-filter');
    const applyButton = document.getElementById('apply-case-filter');
    const rows = [...document.querySelectorAll('.case-row')];
    const noResults = document.getElementById('no-case-results');
    const resultCount = document.getElementById('case-result-count');

    if (!search || !priority || !status || !applyButton) return;

    function applyFilters() {
        const keyword = search.value.trim().toLocaleLowerCase('id-ID');
        let visibleCount = 0;

        rows.forEach((row) => {
            const matchesSearch =
                row.dataset.search.includes(keyword);

            const matchesPriority =
                !priority.value ||
                row.dataset.priority === priority.value;

            const matchesStatus =
                !status.value ||
                row.dataset.status === status.value;

            const visible =
                matchesSearch && matchesPriority && matchesStatus;

            row.classList.toggle('hidden', !visible);

            if (visible) visibleCount++;
        });

        noResults.classList.toggle('hidden', visibleCount !== 0);
        resultCount.textContent =
            `Menampilkan ${visibleCount} dari ${rows.length} laporan contoh`;
    }

    applyButton.addEventListener('click', applyFilters);
    search.addEventListener('input', applyFilters);
    priority.addEventListener('change', applyFilters);
    status.addEventListener('change', applyFilters);
});
</script>
@endsection
