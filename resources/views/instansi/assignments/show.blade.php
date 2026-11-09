@extends('layouts.instansi')

@section('title', 'Status Emergency - EmergencyChain')

@section('content')

@php
    // Data utama
    $case = $assignment->emergencyCase;
    $resource = $assignment->resource;
    $history = $assignment->statusPenanganan;

    $latestStatusValue =
        $latestStatus?->status;


    //Label status
    $statusLabels = [
        'menuju_lokasi' =>
            'Menuju Lokasi',

        'tiba_di_lokasi' =>
            'Tiba di Lokasi',

        'dalam_penanganan' =>
            'Dalam Penanganan',

        'selesai' =>
            'Selesai',
    ];

    // Progress
    $stages = [
        [
            'key' => 'assignment_diterima',
            'label' => 'Assignment Diterima',
        ],
        [
            'key' => 'menuju_lokasi',
            'label' => 'Menuju Lokasi',
        ],
        [
            'key' => 'tiba_di_lokasi',
            'label' => 'Tiba di Lokasi',
        ],
        [
            'key' => 'dalam_penanganan',
            'label' => 'Dalam Penanganan',
        ],
        [
            'key' => 'selesai',
            'label' => 'Penanganan Selesai',
        ],
    ];


    $completedThrough = match (
        $latestStatusValue
    ) {
        'menuju_lokasi' => 1,
        'tiba_di_lokasi' => 2,
        'dalam_penanganan' => 3,
        'selesai' => 4,
        default => 0,
    };

    $activeStage =
        $latestStatusValue === 'selesai'
            ? null
            : $completedThrough + 1;

    $nextStatus = match (
        $latestStatusValue
    ) {
        null => [
            'value' => 'menuju_lokasi',
            'label' => 'Menuju Lokasi',
        ],

        'menuju_lokasi' => [
            'value' => 'tiba_di_lokasi',
            'label' => 'Tiba di Lokasi',
        ],

        'tiba_di_lokasi' => [
            'value' => 'dalam_penanganan',
            'label' => 'Dalam Penanganan',
        ],

        'dalam_penanganan' => [
            'value' => 'selesai',
            'label' => 'Selesai',
        ],

        default => null,
    };


    $caseCode =
        'EC-' .
        now()->format('Y') .
        '-' .
        str_pad(
            $case->case_id,
            4,
            '0',
            STR_PAD_LEFT
        );


    $statusTime = function (
        string $status
    ) use ($history) {

        $item = $history->firstWhere(
            'status',
            $status
        );

        if (!$item) {
            return null;
        }

        return $item->waktu_update;
    };


    $formatWib = function ($time, string $format = 'H:i') {
    if (!$time) {
        return null;
    }

    return \Carbon\Carbon::parse($time)
        ->format($format);
    };
@endphp


<div class="space-y-7">


    <div class="flex flex-wrap items-start justify-between gap-4">

        <div>

            <p class="text-sm font-bold uppercase tracking-wider text-blue-500">
                STATUS EMERGENCY /
                {{ $caseCode }}
            </p>

            <h1 class="mt-2 text-2xl font-semibold text-slate-800">
                Penanganan
                {{ $case->jenis_kejadian }}
            </h1>

        </div>


        <a
            href="{{ route(
                'instansi.assignments.index'
            ) }}"
            class="rounded-xl border border-slate-200
                   bg-white px-6 py-3 font-semibold
                   text-slate-700 transition
                   hover:bg-slate-50"
        >
            Kembali
        </a>

    </div>


    @if(session('success'))

        <div
            class="rounded-xl border border-emerald-200
                   bg-emerald-50 px-5 py-4
                   text-sm text-emerald-700"
        >
            {{ session('success') }}
        </div>

    @endif


    @if($errors->any())

        <div
            class="rounded-xl border border-red-200
                   bg-red-50 px-5 py-4"
        >

            <p class="text-sm font-semibold text-red-600">
                Status belum dapat diperbarui.
            </p>

            <ul
                class="mt-2 list-disc space-y-1
                       pl-5 text-sm text-red-500"
            >
                @foreach($errors->all() as $error)

                    <li>
                        {{ $error }}
                    </li>

                @endforeach
            </ul>

        </div>

    @endif


    <div class="grid gap-5 xl:grid-cols-3">

        {{-- ================================================== --}}
        {{-- BAGIAN KIRI --}}
        {{-- ================================================== --}}

        <div class="space-y-5 xl:col-span-2">

            <section
                class="overflow-hidden rounded-2xl
                       border border-rose-100
                       bg-white shadow-sm"
            >

                <div
                    class="flex items-center justify-between
                           border-b border-slate-100
                           px-5 py-4"
                >

                    <h2 class="font-semibold text-slate-800">
                        Progres Respons
                    </h2>


                    @if(
                        $assignment->status_assign
                        === 'selesai'
                    )

                        <span
                            class="rounded-full bg-emerald-50
                                   px-3 py-1 text-xs
                                   font-semibold text-emerald-600"
                        >
                            ✓ Selesai
                        </span>

                    @else

                        <span
                            class="rounded-full bg-blue-50
                                   px-3 py-1 text-xs
                                   font-semibold text-blue-600"
                        >
                            ● Aktif
                        </span>

                    @endif

                </div>


                <div class="overflow-x-auto p-6">

                    <div
                        class="flex min-w-[650px]
                               items-start"
                    >

                        @foreach(
                            $stages as $index => $stage
                        )

                            @php

                                $completed =
                                    $index
                                    <= $completedThrough;

                                $active =
                                    $activeStage !== null
                                    && $index
                                        === $activeStage;

                                $time = null;

                                if ($index === 0) {

                                    $time =
                                        $assignment
                                            ->waktu_konfirmasi
                                        ?? $assignment
                                            ->waktu_assign;

                                } else {

                                    $time =
                                        $statusTime(
                                            $stage['key']
                                        );
                                }

                            @endphp


                            <div
                                class="relative flex flex-1
                                       flex-col items-center
                                       text-center"
                            >

                                {{-- Connection line --}}
                                @if(!$loop->last)

                                    <div
                                        class="absolute left-1/2
                                               top-[18px] h-[2px]
                                               w-full
                                               {{ $index < $completedThrough
                                                   ? 'bg-emerald-400'
                                                   : 'bg-slate-200'
                                               }}"
                                    ></div>

                                @endif


                                {{-- Circle --}}
                                <div
                                    class="
                                        relative z-10 flex h-9 w-9
                                        items-center justify-center
                                        rounded-full border-2
                                        text-sm font-bold

                                        @if($completed)
                                            border-emerald-500
                                            bg-emerald-500
                                            text-white

                                        @elseif($active)
                                            border-red-500
                                            bg-white
                                            text-red-500
                                            ring-4 ring-red-50

                                        @else
                                            border-slate-200
                                            bg-white
                                            text-slate-400
                                        @endif
                                    "
                                >

                                    @if($completed)

                                        ✓

                                    @else

                                        {{ $index + 1 }}

                                    @endif

                                </div>


                                {{-- Label --}}
                                <p
                                    class="mt-3 text-xs
                                           font-semibold
                                           text-slate-700"
                                >
                                    {{ $stage['label'] }}
                                </p>


                                {{-- Time / state --}}
                                <p
                                    class="mt-1 text-[11px]
                                           text-slate-400"
                                >

                                    @if($time)

                                        {{ $formatWib(
                                            $time,
                                            'H:i'
                                        ) }}
                                        WIB

                                    @elseif($active)

                                        Tahap berikutnya

                                    @else

                                        Menunggu

                                    @endif

                                </p>

                            </div>

                        @endforeach

                    </div>

                </div>

            </section>


            <section
                class="overflow-hidden rounded-2xl
                       border border-rose-100
                       bg-white shadow-sm"
            >

                <div
                    class="border-b border-slate-100
                           px-5 py-4"
                >

                    <h2 class="font-semibold text-slate-800">
                        Informasi Emergency Case
                    </h2>

                </div>


                <div class="space-y-5 p-5">

                    <div
                        class="grid gap-5
                               sm:grid-cols-2"
                    >

                        {{-- Case ID --}}
                        <div>

                            <p class="text-xs text-slate-400">
                                Case ID
                            </p>

                            <p
                                class="mt-1 font-semibold
                                       text-slate-800"
                            >
                                {{ $caseCode }}
                            </p>

                        </div>


                        {{-- Status Case --}}
                        <div>

                            <p class="text-xs text-slate-400">
                                Status Laporan
                            </p>

                            <span
                                class="mt-1 inline-flex
                                       rounded-full
                                       bg-amber-50 px-3 py-1
                                       text-xs font-semibold
                                       text-amber-700"
                            >
                                {{ $case->status_laporan }}
                            </span>

                        </div>


                        {{-- Jenis kejadian --}}
                        <div>

                            <p class="text-xs text-slate-400">
                                Jenis Kejadian
                            </p>

                            <p
                                class="mt-1 font-semibold
                                       text-slate-800"
                            >
                                {{ $case->jenis_kejadian }}
                            </p>

                        </div>

                        {{-- Kondisi kejadian --}}
                        <div>

                            <p class="text-xs text-slate-400">
                                Kondisi Kejadian
                            </p>

                            <p
                                class="mt-1 font-semibold
                                    text-slate-800"
                            >
                                {{ $case->kondisi ?? 'Tidak ada informasi kondisi kejadian.' }}
                            </p>

                        </div>
                        {{-- Lokasi --}}
                        <div>

                            <p class="text-xs text-slate-400">
                                Lokasi
                            </p>

                            <p
                                class="mt-1 font-semibold
                                       text-slate-800"
                            >
                                {{
                                    $case->lokasi?->alamat
                                    ?? 'Lokasi tidak tersedia'
                                }}
                            </p>

                        </div>


                        {{-- Resource --}}
                        <div>

                            <p class="text-xs text-slate-400">
                                Unit Ditugaskan
                            </p>

                            <p
                                class="mt-1 font-semibold
                                       text-slate-800"
                            >
                                {{
                                    $resource->nama_resource
                                    ?? 'Resource tidak tersedia'
                                }}
                            </p>

                            <p
                                class="mt-1 text-xs
                                       text-slate-500"
                            >
                                {{
                                    $resource->jenis_resource
                                    ?? '-'
                                }}
                            </p>

                        </div>


                        {{-- Personel --}}
                        <div>

                            <p class="text-xs text-slate-400">
                                Kapasitas / Personel
                            </p>

                            <p
                                class="mt-1 font-semibold
                                       text-slate-800"
                            >

                                @if(
                                    $resource
                                        ->kapasitas_personel
                                )

                                    {{
                                        $resource
                                            ->kapasitas_personel
                                    }}
                                    personel

                                @else

                                    -

                                @endif

                            </p>

                        </div>

                    </div>


                    {{-- Assignment info --}}
                    <div
                        class="rounded-xl border
                               border-rose-100
                               bg-rose-50/60 p-4"
                    >

                        <div
                            class="grid gap-4
                                   sm:grid-cols-2"
                        >

                            <div>

                                <p
                                    class="text-xs
                                           text-slate-400"
                                >
                                    Assignment
                                </p>

                                <p
                                    class="mt-1 text-sm
                                           font-semibold
                                           text-slate-700"
                                >
                                    Assignment
                                    #{{ $assignment->assignment_id }}
                                </p>

                            </div>


                            <div>

                                <p
                                    class="text-xs
                                           text-slate-400"
                                >
                                    Status Assignment
                                </p>

                                <p
                                    class="mt-1 text-sm
                                           font-semibold
                                           text-slate-700"
                                >
                                    {{
                                        ucwords(
                                            str_replace(
                                                '_',
                                                ' ',
                                                $assignment
                                                    ->status_assign
                                            )
                                        )
                                    }}
                                </p>

                            </div>

                        </div>

                    </div>

                </div>

            </section>

            <section
                class="overflow-hidden rounded-2xl
                       border border-rose-100
                       bg-white shadow-sm"
            >

                <div
                    class="border-b border-slate-100
                           px-5 py-4"
                >

                    <h2 class="font-semibold text-slate-800">
                        Riwayat Status Penanganan
                    </h2>

                    <p
                        class="mt-1 text-xs
                               text-slate-400"
                    >
                        Riwayat perubahan status dan
                        catatan lapangan.
                    </p>

                </div>


                <div class="p-5">

                    {{-- Assignment diterima --}}
                    <div
                        class="relative flex gap-4
                               pb-6"
                    >

                        <div
                            class="relative flex
                                   flex-col items-center"
                        >

                            <div
                                class="z-10 flex h-9 w-9
                                       items-center
                                       justify-center
                                       rounded-full
                                       bg-emerald-500
                                       text-sm font-bold
                                       text-white"
                            >
                                ✓
                            </div>


                            @if($history->isNotEmpty())

                                <div
                                    class="absolute top-9
                                           h-full w-px
                                           bg-slate-200"
                                ></div>

                            @endif

                        </div>


                        <div>

                            <p
                                class="text-sm font-semibold
                                       text-slate-800"
                            >
                                Assignment Diterima
                            </p>

                            <p
                                class="mt-1 text-xs
                                       text-slate-400"
                            >
                                {{
                                    $formatWib(
                                        $assignment
                                            ->waktu_konfirmasi
                                        ?? $assignment
                                            ->waktu_assign,
                                        'd M Y, H:i'
                                    )
                                }}
                                WIB
                            </p>

                            <p
                                class="mt-2 text-sm
                                       text-slate-500"
                            >
                                {{
                                    $resource
                                        ->nama_resource
                                }}
                                menerima Assignment
                                untuk menangani
                                {{ $case->jenis_kejadian }}.
                            </p>

                        </div>

                    </div>


                    @forelse(
                        $history as $item
                    )

                        <div
                            class="relative flex gap-4
                                   pb-6"
                        >

                            <div
                                class="relative flex
                                       flex-col
                                       items-center"
                            >

                                <div
                                    class="
                                        z-10 flex h-9 w-9
                                        items-center
                                        justify-center
                                        rounded-full

                                        {{
                                            $item->status
                                                === 'selesai'
                                            ? 'bg-emerald-500 text-white'
                                            : 'bg-red-50 text-red-500'
                                        }}
                                    "
                                >

                                    @if(
                                        $item->status
                                        === 'selesai'
                                    )
                                        ✓
                                    @else
                                        •
                                    @endif

                                </div>


                                @if(!$loop->last)

                                    <div
                                        class="absolute top-9
                                               h-full w-px
                                               bg-slate-200"
                                    ></div>

                                @endif

                            </div>


                            <div class="min-w-0">

                                <p
                                    class="text-sm
                                           font-semibold
                                           text-slate-800"
                                >
                                    {{
                                        $statusLabels[
                                            $item->status
                                        ]
                                        ?? $item->status
                                    }}
                                </p>


                                <p
                                    class="mt-1 text-xs
                                           text-slate-400"
                                >
                                    {{
                                        $formatWib(
                                            $item
                                                ->waktu_update,
                                            'd M Y, H:i'
                                        )
                                    }}
                                    WIB
                                </p>


                                <div
                                    class="mt-2 rounded-xl
                                           bg-slate-50
                                           px-4 py-3"
                                >

                                    <p
                                        class="text-sm leading-6
                                               text-slate-600"
                                    >
                                        {{
                                            $item
                                                ->keterangan
                                        }}
                                    </p>

                                </div>

                            </div>

                        </div>

                    @empty

                        <div
                            class="rounded-xl bg-slate-50
                                   px-4 py-4
                                   text-sm text-slate-500"
                        >
                            Belum ada pembaruan
                            status penanganan.
                        </div>

                    @endforelse

                </div>

            </section>

        </div>


        <div class="space-y-5">



                <link
                    rel="stylesheet"
                    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
                    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
                    crossorigin=""
                >

                <section
                    class="overflow-hidden rounded-2xl
                        border border-rose-100
                        bg-white shadow-sm"
                >
                    <div class="border-b border-slate-100 px-5 py-4">
                        <h2 class="font-semibold text-slate-800">
                            Lokasi Kejadian
                        </h2>

                        <p class="mt-1 text-xs text-slate-400">
                            Lokasi berdasarkan laporan Emergency Case.
                        </p>
                    </div>

                    @if(
                        $case->lokasi
                        && $case->lokasi->latitude !== null
                        && $case->lokasi->longitude !== null
                    )

                        <div
                            id="emergency-map"
                            class="h-64 w-full"
                            data-latitude="{{ $case->lokasi->latitude }}"
                            data-longitude="{{ $case->lokasi->longitude }}"
                        ></div>

                        <div class="border-t border-slate-100 px-4 py-3">
                            <div class="flex items-start gap-2">
                                <span class="mt-0.5">📍</span>

                                <div>
                                    <p class="text-xs font-semibold text-slate-700">
                                        Lokasi Kejadian
                                    </p>

                                    <p class="mt-1 text-xs leading-5 text-slate-500">
                                        {{ $case->lokasi->alamat ?? 'Alamat tidak tersedia' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                    @else

                        <div
                            class="flex h-64 items-center justify-center
                                bg-slate-50 px-6 text-center"
                        >
                            <div>
                                <p class="text-2xl">📍</p>

                                <p class="mt-2 text-sm font-semibold text-slate-600">
                                    Koordinat lokasi tidak tersedia
                                </p>

                                <p class="mt-1 text-xs text-slate-400">
                                    Peta tidak dapat ditampilkan untuk Emergency Case ini.
                                </p>
                            </div>
                        </div>

                    @endif
                </section>


                @if(
                    $case->lokasi
                    && $case->lokasi->latitude !== null
                    && $case->lokasi->longitude !== null
                )

                    <script
                        src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
                        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
                        crossorigin=""
                    ></script>

                    <script>
                        document.addEventListener('DOMContentLoaded', function () {

                            const mapElement =
                                document.getElementById('emergency-map');

                            if (!mapElement || typeof L === 'undefined') {
                                return;
                            }

                            const latitude =
                                parseFloat(mapElement.dataset.latitude);

                            const longitude =
                                parseFloat(mapElement.dataset.longitude);

                            if (
                                Number.isNaN(latitude)
                                || Number.isNaN(longitude)
                            ) {
                                return;
                            }

                            const map = L.map(
                                'emergency-map',
                                {
                                    scrollWheelZoom: false,
                                }
                            ).setView(
                                [latitude, longitude],
                                16
                            );

                            L.tileLayer(
                                'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                                {
                                    maxZoom: 19,
                                    attribution:
                                        '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                                }
                            ).addTo(map);

                            const marker = L.marker(
                                [latitude, longitude]
                            ).addTo(map);

                            const alamat =
    @js($case->lokasi->alamat ?? 'Alamat tidak tersedia');

                            marker
                                .bindPopup(
                                    `
                                        <div style="min-width: 180px;">
                                            <strong>Lokasi Kejadian</strong>
                                            <br>
                                            ${alamat}
                                        </div>
                                    `
                                )
                                .openPopup();

                            setTimeout(function () {
                                map.invalidateSize();
                            }, 100);
                        });
                    </script>

                @endif

            <section
                class="rounded-2xl border
                       border-rose-100
                       bg-white p-5
                       shadow-sm"
            >

                <h2
                    class="font-semibold
                           text-slate-800"
                >
                    Perbarui Status Penanganan
                </h2>


                @if(
                    $assignment->status_assign
                    === 'selesai'
                )

                    <div
                        class="mt-5 rounded-xl
                               border
                               border-emerald-200
                               bg-emerald-50
                               p-4"
                    >

                        <p
                            class="font-semibold
                                   text-emerald-700"
                        >
                            Penanganan Selesai
                        </p>

                        <p
                            class="mt-1 text-sm
                                   text-emerald-600"
                        >
                            Assignment ini sudah selesai
                            dan tidak dapat diperbarui lagi.
                        </p>

                    </div>


                @elseif($nextStatus)

                    <form
                        method="POST"
                        action="{{
                            route(
                                'instansi.assignments.status.update',
                                $assignment->assignment_id
                            )
                        }}"
                        class="mt-5 space-y-4"
                    >

                        @csrf
                        @method('PATCH')
                       
                        <div>
                            <p class="block text-sm font-semibold text-slate-700">
                                Status Berikutnya
                            </p>

                            <div class="mt-2 w-full rounded-lg border border-slate-200
                                        bg-slate-50 px-3 py-3 text-sm font-semibold
                                        text-slate-700">
                                {{ $nextStatus['label'] }}
                            </div>

                            <input
                                type="hidden"
                                name="status"
                                value="{{ $nextStatus['value'] }}"
                            >
                        </div>



                        <div>

                            <label
                                for="keterangan"
                                class="block text-sm
                                       font-semibold
                                       text-slate-700"
                            >
                                Catatan Lapangan
                            </label>


                            <textarea
                                id="keterangan"
                                name="keterangan"
                                rows="4"
                                required
                                maxlength="5000"
                                placeholder="Tambahkan catatan kondisi terbaru..."
                                class="mt-2 w-full
                                       resize-none rounded-lg
                                       border border-slate-200
                                       px-3 py-3 text-sm
                                       text-slate-700
                                       outline-none
                                       focus:border-red-400
                                       focus:ring-2
                                       focus:ring-red-100"
                            >{{ old('keterangan') }}</textarea>


                            <p
                                class="mt-1 text-xs
                                       text-slate-400"
                            >
                                Catatan akan tersimpan
                                ke riwayat penanganan.
                            </p>

                        </div>


                        <button
                            type="submit"
                            class="flex w-full
                                   items-center
                                   justify-center gap-2
                                   rounded-lg
                                   bg-red-500
                                   px-4 py-3
                                   font-semibold
                                   text-white
                                   transition
                                   hover:bg-red-600"
                        >

                            <svg
                                class="h-4 w-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    d="M3 12h4l2-5 4 10 2-5h6"
                                />
                            </svg>

                            Perbarui Status

                        </button>

                    </form>


                @else

                    <div
                        class="mt-5 rounded-xl
                               bg-slate-50 p-4
                               text-sm text-slate-500"
                    >
                        Tidak ada status berikutnya.
                    </div>

                @endif

            </section>

        </div>

    </div>

</div>

@endsection