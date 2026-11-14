
@extends('layouts.petugas-ec')

@section('title', 'Dashboard Command Center - EmergencyChain')

@section('content')

@php
    $dashboardStats = [
        [
            'label' => 'Total Emergency Case',
            'value' => $totalCases,
            'description' => 'Seluruh laporan',
            'icon' => 'EC',
            'color' => 'bg-blue-50 text-blue-600',
        ],
        [
            'label' => 'Kasus Aktif',
            'value' => $activeCasesCount,
            'description' => 'Belum selesai ditangani',
            'icon' => 'AC',
            'color' => 'bg-rose-50 text-rose-600',
        ],
        [
            'label' => 'Perlu Verifikasi',
            'value' => $verificationCount,
            'description' => 'Menunggu verifikasi',
            'icon' => 'VR',
            'color' => 'bg-violet-50 text-violet-600',
        ],
        [
            'label' => 'Menunggu Assignment',
            'value' => $waitingAssignmentCount,
            'description' => 'Menunggu unit tersedia',
            'icon' => 'AS',
            'color' => 'bg-amber-50 text-amber-600',
        ],
        [
            'label' => 'Dalam Penanganan',
            'value' => $handlingCount,
            'description' => 'Sedang ditangani',
            'icon' => 'PN',
            'color' => 'bg-red-50 text-red-600',
        ],
        [
            'label' => 'Selesai',
            'value' => $completedCount,
            'description' => 'Penanganan selesai',
            'icon' => 'OK',
            'color' => 'bg-emerald-50 text-emerald-600',
        ],
    ];

    $statusColors = [
        'Perlu Verifikasi' => 'bg-violet-100 text-violet-700',
        'Menunggu Assignment' => 'bg-amber-100 text-amber-700',
        'Menunggu' => 'bg-slate-100 text-slate-700',
        'Menuju Lokasi' => 'bg-blue-100 text-blue-700',
        'Tiba di Lokasi' => 'bg-indigo-100 text-indigo-700',
        'Dalam Penanganan' => 'bg-red-100 text-red-700',
        'Selesai' => 'bg-emerald-100 text-emerald-700',
    ];
@endphp

<div class="mx-auto max-w-7xl space-y-6">

    {{-- Heading --}}
    <div class="flex flex-col justify-between gap-2 sm:flex-row sm:items-center">
        <div>
            <p class="text-xs font-bold tracking-widest text-red-500">
                EMERGENCYCHAIN
            </p>

            <h1 class="mt-1 text-2xl font-bold text-slate-800 sm:text-3xl">
                Command Center
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Pusat pemantauan dan koordinasi penanganan Emergency Case.
            </p>
        </div>

    </div>

    {{-- KPI Dashboard --}}
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">

        @foreach ($dashboardStats as $stat)
            <div class="rounded-xl border border-slate-100 bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="text-[11px] font-medium text-slate-500">
                            {{ $stat['label'] }}
                        </p>

                        <p class="mt-2 text-2xl font-bold tabular-nums text-slate-800">
                            {{ $stat['value'] }}
                        </p>
                    </div>

                    <div class="flex h-9 w-9 items-center justify-center rounded-xl
                                {{ $stat['color'] }} text-[11px] font-bold">
                        {{ $stat['icon'] }}
                    </div>
                </div>

                <p class="mt-2 text-[11px] text-slate-400">
                    {{ $stat['description'] }}
                </p>
            </div>
        @endforeach

    </div>

    {{-- Peta Emergency Case --}}
    <section class="overflow-hidden rounded-xl border border-slate-100 bg-white shadow-sm">

        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="font-semibold text-slate-800">
                Peta Emergency Case Aktif
            </h2>

            <p class="mt-1 text-xs text-slate-500">
                Sebaran lokasi kejadian yang masih dalam proses penanganan.
            </p>
        </div>

        @if ($mapCases->isNotEmpty())
            <div id="case-map" class="z-0 h-[350px] w-full sm:h-[420px]"></div>
        @else
            <div class="flex h-64 items-center justify-center px-5 text-center">
                <div>
                    <p class="font-medium text-slate-600">
                        Belum ada lokasi Emergency Case yang dapat ditampilkan.
                    </p>

                    <p class="mt-1 text-xs text-slate-400">
                        Pastikan kasus aktif memiliki koordinat lokasi.
                    </p>
                </div>
            </div>
        @endif

    </section>

    {{-- Emergency Case Aktif --}}
    <section class="overflow-hidden rounded-xl border border-slate-100 bg-white shadow-sm">

        <div class="flex flex-col gap-2 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-slate-800">
                    Emergency Case Aktif
                </h2>

                <p class="mt-1 text-xs text-slate-500">
                    Seluruh kasus yang belum berstatus Selesai.
                </p>
            </div>

            <span class="w-fit rounded-full bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-600">
                {{ $activeCasesCount }} Kasus Aktif
            </span>
        </div>

        {{-- Search dan Filter --}}
        <div class="flex flex-col gap-3 border-b border-slate-100 p-4 sm:flex-row sm:flex-wrap">

            <input
                id="case-search"
                type="search"
                placeholder="Cari Case ID, lokasi, atau kejadian..."
                aria-label="Cari Emergency Case"
                class="min-w-0 flex-1 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm focus:border-red-400 focus:outline-none"
            >

            <select
                id="status-filter"
                aria-label="Filter status"
                class="rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm"
            >
                <option value="">Semua Status</option>
                <option value="Perlu Verifikasi">Perlu Verifikasi</option>
                <option value="Menunggu Assignment">Menunggu Assignment</option>
                <option value="Menunggu">Menunggu</option>
                <option value="Menuju Lokasi">Menuju Lokasi</option>
                <option value="Tiba di Lokasi">Tiba di Lokasi</option>
                <option value="Dalam Penanganan">Dalam Penanganan</option>
            </select>

        </div>

        {{-- Tabel Emergency Case --}}
        <div class="w-full overflow-x-auto">
            <table class="w-full min-w-[850px] text-left text-sm">

                <thead class="bg-rose-50 text-[11px] uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-4">Case ID</th>
                        <th class="px-5 py-4">Jenis Kejadian</th>
                        <th class="px-5 py-4">Lokasi</th>
                        <th class="px-5 py-4">Waktu</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4">Resource Ditugaskan</th>
                        <th class="px-5 py-4 text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody id="case-table-body" class="divide-y divide-slate-100">

                    @forelse ($cases as $case)

                        @php
                            $statusClass = $statusColors[$case->status_laporan]
                                ?? 'bg-slate-100 text-slate-600';

                            $resourceTypes = $case->assignments
                                ->pluck('resource.jenis_resource')
                                ->filter()
                                ->unique()
                                ->values();
                        @endphp

                        <tr
                            class="case-row transition hover:bg-slate-50"
                            data-search="{{ strtolower($case->case_id . ' ' . $case->jenis_kejadian . ' ' . ($case->lokasi?->alamat ?? '') . ' ' . $resourceTypes->implode(' ')) }}"
                            data-status="{{ $case->status_laporan }}"
                        >

                            <td class="whitespace-nowrap px-5 py-4 font-semibold">
                                <a
                                    href="{{ route('petugas.ec.cases.show', ['caseId' => $case->case_id]) }}"
                                    class="text-blue-600 hover:underline"
                                >
                                    #{{ $case->case_id }}
                                </a>
                            </td>

                            <td class="px-5 py-4 font-medium text-slate-700">
                                {{ $case->jenis_kejadian }}
                            </td>

                            <td class="max-w-xs px-5 py-4 text-slate-600">
                                {{ $case->lokasi?->alamat ?? 'Tidak tersedia' }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-slate-600">
                                {{ \Carbon\Carbon::parse($case->waktu_laporan)->format('d/m/Y H:i') }}
                            </td>

                            <td class="px-5 py-4">
                                <span class="inline-block whitespace-nowrap rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                                    {{ $case->status_laporan }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-slate-600">
                                @if ($resourceTypes->isNotEmpty())
                                    {{ $resourceTypes->implode(', ') }}
                                @else
                                    <span class="text-slate-400">
                                        Belum ditugaskan
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-center">
                                <a
                                    href="{{ route('petugas.ec.cases.show', ['caseId' => $case->case_id]) }}"
                                    aria-label="Lihat detail kasus {{ $case->case_id }}"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-blue-600 hover:bg-blue-50"
                                >
                                    &rsaquo;
                                </a>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center">
                                <p class="font-medium text-slate-600">
                                    Tidak ada Emergency Case aktif.
                                </p>

                                <p class="mt-1 text-xs text-slate-400">
                                    Semua kasus telah selesai ditangani atau belum ada laporan.
                                </p>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>
        </div>

        {{-- Tidak ditemukan --}}
        <div
            id="no-case-results"
            class="hidden px-5 py-10 text-center text-sm text-slate-500"
            role="status"
        >
            Tidak ada Emergency Case yang sesuai dengan pencarian.
        </div>

        <div class="border-t border-slate-100 px-5 py-3">
            <p id="case-result-count" class="text-xs text-slate-500" aria-live="polite">
                Menampilkan {{ $cases->count() }} dari {{ $activeCasesCount }} kasus aktif.
            </p>
        </div>

    </section>

</div>

{{-- Leaflet Map --}}
@if ($mapCases->isNotEmpty())
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    >

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const caseData = @json($mapCases);

            const map = L.map('case-map');

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            const markers = [];

            caseData.forEach(function (item) {
                const marker = L.marker([
                    item.latitude,
                    item.longitude
                ]).addTo(map);

                const popup = document.createElement('div');

                const title = document.createElement('strong');
                title.textContent = 'Emergency Case #' + item.case_id;

                const type = document.createElement('p');
                type.textContent = item.jenis_kejadian;

                const status = document.createElement('p');
                status.textContent = 'Status: ' + item.status;

                const link = document.createElement('a');
                link.href = item.url;
                link.textContent = 'Lihat Detail';
                link.className = 'text-blue-600 font-semibold';

                popup.append(title, type, status, link);

                marker.bindPopup(popup);

                markers.push(marker.getLatLng());
            });

            if (markers.length > 0) {
                map.fitBounds(L.latLngBounds(markers), {
                    padding: [35, 35],
                    maxZoom: 15
                });
            }
        });
    </script>
@endif

{{-- Search dan Filter --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const search = document.getElementById('case-search');
    const status = document.getElementById('status-filter');

    const rows = Array.from(document.querySelectorAll('.case-row'));
    const noResults = document.getElementById('no-case-results');
    const resultCount = document.getElementById('case-result-count');

    if (!search || !status || !noResults || !resultCount) {
        return;
    }

    function applyFilters() {
        const keyword = search.value.trim().toLowerCase();
        let visibleCount = 0;

        rows.forEach(function (row) {
            const matchesSearch = row.dataset.search.includes(keyword);

            const matchesStatus =
                !status.value || row.dataset.status === status.value;

            const visible = matchesSearch && matchesStatus;

            row.classList.toggle('hidden', !visible);

            if (visible) {
                visibleCount++;
            }
        });

        noResults.classList.toggle(
            'hidden',
            visibleCount !== 0 || rows.length === 0
        );

        resultCount.textContent =
            `Menampilkan ${visibleCount} dari ${rows.length} kasus aktif.`;
    }

    search.addEventListener('input', applyFilters);
    status.addEventListener('change', applyFilters);
});
</script>

@endsection
