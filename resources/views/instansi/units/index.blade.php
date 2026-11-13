
@extends('layouts.instansi')

@section('content')

<div class="min-h-screen bg-rose-50/60 px-8 py-8">

    {{-- Header --}}
    <div class="mb-8">
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-600">
            {{ strtoupper(auth()->user()->instansi->nama_instansi ?? 'INSTANSI PENANGANAN') }}
            / OPERASIONAL
        </p>

        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">
            Kelola Unit
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Pantau unit operasional dan perbarui status ketersediaan resource instansi.
        </p>
    </div>

    {{-- Success / Error --}}
    @if(session('success'))
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-600">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-5 py-4">
            <p class="mb-2 text-sm font-semibold text-red-600">
                Data belum dapat disimpan.
            </p>

            <ul class="list-disc space-y-1 pl-5 text-sm text-red-500">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Summary --}}
    <div class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

        {{-- Total Unit --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase text-slate-500">
                        Seluruh Unit
                    </p>

                    <p class="mt-1 text-3xl font-semibold text-slate-900">
                        {{ $summary['total'] }}
                    </p>

                    <p class="mt-1 text-xs text-slate-400">
                        Terdaftar di instansi
                    </p>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-500">
                    <svg class="h-5 w-5" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M3 7h11v9H3z"/>
                        <path d="M14 10h4l3 3v3h-7z"/>
                        <circle cx="7" cy="18" r="2"/>
                        <circle cx="17" cy="18" r="2"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Unit Tersedia --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase text-emerald-600">
                        Unit Tersedia
                    </p>

                    <p class="mt-1 text-3xl font-semibold text-slate-900">
                        {{ $summary['tersedia'] }}
                    </p>

                    <p class="mt-1 text-xs text-slate-400">
                        Siap menerima penugasan
                    </p>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                    ✓
                </div>
            </div>
        </div>

        {{-- Sedang Ditugaskan --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase text-amber-600">
                        Sedang Ditugaskan
                    </p>

                    <p class="mt-1 text-3xl font-semibold text-slate-900">
                        {{ $summary['ditugaskan'] }}
                    </p>

                    <p class="mt-1 text-xs text-slate-400">
                        Menjalankan penugasan
                    </p>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                    ◷
                </div>
            </div>
        </div>

        {{-- Tidak Tersedia --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase text-slate-500">
                        Tidak Tersedia
                    </p>

                    <p class="mt-1 text-3xl font-semibold text-slate-900">
                        {{ $summary['tidak_tersedia'] }}
                    </p>

                    <p class="mt-1 text-xs text-slate-400">
                        Tidak dapat ditugaskan
                    </p>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-rose-50 text-red-500">
                    !
                </div>
            </div>
        </div>

    </div>

    {{-- Filter --}}
    <form
        method="GET"
        action="{{ route('instansi.units.index') }}"
        class="mb-6 grid grid-cols-1 gap-3 lg:grid-cols-[1fr_230px_230px]"
    >
        <input
            type="search"
            name="search"
            value="{{ request('search') }}"
            placeholder="Cari berdasarkan nama unit..."
            class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm text-slate-700 outline-none transition focus:border-red-400 focus:ring-2 focus:ring-red-100"
        >

        <select
            name="jenis"
            onchange="this.form.submit()"
            class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700"
        >
            <option value="">Semua Jenis</option>

            @foreach($jenisOptions as $jenis)
                <option
                    value="{{ $jenis }}"
                    @selected(request('jenis') === $jenis)
                >
                    {{ $jenis }}
                </option>
            @endforeach
        </select>

        <select
            name="status"
            onchange="this.form.submit()"
            class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700"
        >
            <option value="">Semua Status</option>

            @foreach($statusOptions as $value => $label)
                <option
                    value="{{ $value }}"
                    @selected(request('status') === $value)
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </form>

    {{-- Daftar Unit --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">

        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5">
            <div>
                <h2 class="font-semibold text-slate-800">
                    Daftar Unit Operasional
                </h2>

                <p class="mt-1 text-xs text-slate-400">
                    {{ $resources->total() }} unit ditemukan
                </p>
            </div>

            <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600">
                ● Data Instansi
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">

                <thead class="bg-rose-50/40 text-[11px] font-semibold uppercase text-slate-400">
                    <tr>
                        <th class="px-6 py-4">Nama Unit</th>
                        <th class="px-5 py-4">Jenis Unit</th>
                        <th class="px-5 py-4">Kapasitas / Personel</th>
                        <th class="px-5 py-4">Ketersediaan</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">

                    @forelse($resources as $resource)

                        <tr class="transition hover:bg-slate-50/60">

                            {{-- Nama Unit --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">

                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-500">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24"
                                             fill="none" stroke="currentColor" stroke-width="1.8">
                                            <path d="M3 7h11v9H3z"/>
                                            <path d="M14 10h4l3 3v3h-7z"/>
                                            <circle cx="7" cy="18" r="2"/>
                                            <circle cx="17" cy="18" r="2"/>
                                        </svg>
                                    </div>

                                    <div>
                                        <p class="text-sm font-semibold text-slate-800">
                                            {{ $resource->nama_resource }}
                                        </p>

                                        <p class="mt-0.5 text-xs text-slate-400">
                                            {{ $resource->lokasi }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            {{-- Jenis Unit --}}
                            <td class="px-5 py-4 text-sm text-slate-600">
                                {{ $resource->jenis_resource }}
                            </td>

                            {{-- Kapasitas --}}
                            <td class="px-5 py-4 text-sm font-semibold text-slate-700">
                                {{ $resource->kapasitas_personel ?? '-' }}

                                @if($resource->kapasitas_personel)
                                    personel
                                @endif
                            </td>

                            {{-- Status Ketersediaan --}}
                            <td class="px-5 py-4">

                                @if(
                                    $resource->status_ketersediaan
                                    === \App\Models\Resource::STATUS_DITUGASKAN
                                )

                                    {{-- Unit sedang ditugaskan tidak dapat diubah --}}
                                    <span class="inline-flex rounded-lg bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-600">
                                        Sedang Ditugaskan
                                    </span>

                                @else

                                    {{-- Hanya boleh mengubah ketersediaan --}}
                                    <form
                                        method="POST"
                                        action="{{ route(
                                            'instansi.units.availability.update',
                                            $resource->resource_id
                                        ) }}"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <select
                                            name="status_ketersediaan"
                                            onchange="this.form.submit()"
                                            aria-label="Status ketersediaan {{ $resource->nama_resource }}"
                                            class="rounded-lg border-0 px-3 py-2 text-xs font-semibold outline-none ring-1
                                                {{ $resource->status_ketersediaan === \App\Models\Resource::STATUS_TERSEDIA
                                                    ? 'bg-emerald-50 text-emerald-700 ring-emerald-100'
                                                    : 'bg-red-50 text-red-500 ring-red-100'
                                                }}"
                                        >

                                            <option
                                                value="{{ \App\Models\Resource::STATUS_TERSEDIA }}"
                                                @selected(
                                                    $resource->status_ketersediaan
                                                    === \App\Models\Resource::STATUS_TERSEDIA
                                                )
                                            >
                                                Tersedia
                                            </option>

                                            <option
                                                value="{{ \App\Models\Resource::STATUS_TIDAK_TERSEDIA }}"
                                                @selected(
                                                    $resource->status_ketersediaan
                                                    === \App\Models\Resource::STATUS_TIDAK_TERSEDIA
                                                )
                                            >
                                                Tidak Tersedia
                                            </option>

                                        </select>
                                    </form>

                                @endif

                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="4" class="px-6 py-16 text-center">
                                <p class="font-medium text-slate-500">
                                    Belum terdapat unit.
                                </p>

                                <p class="mt-1 text-sm text-slate-400">
                                    Belum ada unit operasional yang terdaftar pada instansi ini.
                                </p>
                            </td>
                        </tr>

                    @endforelse

                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($resources->hasPages())
            <div class="border-t border-slate-100 px-6 py-4">
                {{ $resources->links() }}
            </div>
        @endif

    </div>

</div>

@endsection