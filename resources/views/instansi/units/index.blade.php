@extends('layouts.instansi')

@section('content')

<div class="min-h-screen bg-rose-50/60 px-8 py-8">

    {{-- Header --}}
    <div class="mb-8 flex items-start justify-between">

        <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-600">
                {{ strtoupper(auth()->user()->instansi->nama_instansi ?? 'INSTANSI PENANGANAN') }}
                / OPERASIONAL
            </p>

            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">
                Kelola Unit
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Kelola unit operasional dan perbarui status ketersediaan resource.
            </p>
        </div>

        <button
            type="button"
            data-modal-open="create-unit-modal"
            class="inline-flex items-center gap-2 rounded-xl bg-red-500 px-6 py-3
                   text-sm font-semibold text-white shadow-lg shadow-red-200
                   transition hover:bg-red-600"
        >
            <span class="text-xl font-light">+</span>
            Tambah Unit
        </button>
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

        {{-- Total --}}
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

        {{-- Tersedia --}}
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

        {{-- Ditugaskan --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase text-amber-600">
                        Sedang Bertugas
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

        {{-- Tidak tersedia --}}
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
            class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm
                   text-slate-700 outline-none transition
                   focus:border-red-400 focus:ring-2 focus:ring-red-100"
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

    {{-- Table --}}
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
                        <th class="px-5 py-4">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">

                    @forelse($resources as $resource)

                        <tr class="transition hover:bg-slate-50/60">

                            {{-- Unit --}}
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

                            <td class="px-5 py-4 text-sm text-slate-600">
                                {{ $resource->jenis_resource }}
                            </td>

                            <td class="px-5 py-4 text-sm font-semibold text-slate-700">
                                {{ $resource->kapasitas_personel ?? '-' }}
                                @if($resource->kapasitas_personel)
                                    personel
                                @endif
                            </td>

                            {{-- Availability --}}
                            <td class="px-5 py-4">

                                @if(
                                    $resource->status_ketersediaan
                                    === \App\Models\Resource::STATUS_DITUGASKAN
                                )

                                    <span class="inline-flex rounded-lg bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-600">
                                        Sedang Ditugaskan
                                    </span>

                                @else

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
                                            class="
                                                rounded-lg border-0 px-3 py-2 text-xs font-semibold
                                                outline-none ring-1
                                                {{ $resource->status_ketersediaan === \App\Models\Resource::STATUS_TERSEDIA
                                                    ? 'bg-emerald-50 text-emerald-700 ring-emerald-100'
                                                    : 'bg-red-50 text-red-500 ring-red-100'
                                                }}
                                            "
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

                            {{-- Actions --}}
                            <td class="px-5 py-4">

                                <div class="flex items-center gap-2">

                                    <button
                                        type="button"
                                        data-edit-unit
                                        data-id="{{ $resource->resource_id }}"
                                        data-name="{{ $resource->nama_resource }}"
                                        data-type="{{ $resource->jenis_resource }}"
                                        data-capacity="{{ $resource->kapasitas_personel }}"
                                        class="rounded-lg border border-slate-200 px-3 py-2 text-xs
                                               font-semibold text-blue-600 transition
                                               hover:bg-blue-50"
                                    >
                                        ✎ Edit
                                    </button>

                                    <button
                                        type="button"
                                        data-delete-unit
                                        data-id="{{ $resource->resource_id }}"
                                        data-name="{{ $resource->nama_resource }}"
                                        class="rounded-lg border border-slate-200 px-3 py-2 text-xs
                                               font-semibold text-red-500 transition
                                               hover:bg-red-50"
                                    >
                                        🗑 Hapus
                                    </button>

                                </div>

                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="5"
                                class="px-6 py-16 text-center"
                            >
                                <p class="font-medium text-slate-500">
                                    Belum terdapat unit.
                                </p>

                                <p class="mt-1 text-sm text-slate-400">
                                    Tambahkan unit operasional untuk mulai mengelola ketersediaan.
                                </p>
                            </td>
                        </tr>

                    @endforelse

                </tbody>
            </table>
        </div>

        @if($resources->hasPages())
            <div class="border-t border-slate-100 px-6 py-4">
                {{ $resources->links() }}
            </div>
        @endif

    </div>
</div>


{{-- ========================================================= --}}
{{-- CREATE MODAL --}}
{{-- ========================================================= --}}

<div
    id="create-unit-modal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/40 p-4"
>
    <div class="w-full max-w-lg rounded-2xl bg-white shadow-2xl">

        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
            <div>
                <h3 class="text-lg font-semibold text-slate-800">
                    Tambah Unit
                </h3>

                <p class="mt-1 text-xs text-slate-400">
                    Tambahkan resource operasional baru.
                </p>
            </div>

            <button
                type="button"
                data-modal-close
                class="text-xl text-slate-400 hover:text-slate-700"
            >
                ×
            </button>
        </div>

        <form
            method="POST"
            action="{{ route('instansi.units.store') }}"
            class="space-y-5 p-6"
        >
            @csrf

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">
                    Nama Unit
                </label>

                <input
                    type="text"
                    name="nama_resource"
                    required
                    maxlength="100"
                    placeholder="Contoh: Damkar Unit 08"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none focus:border-red-400 focus:ring-2 focus:ring-red-100"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">
                    Jenis Unit
                </label>

                <input
                    type="text"
                    name="jenis_resource"
                    required
                    maxlength="50"
                    placeholder="Contoh: Unit Pemadam"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none focus:border-red-400 focus:ring-2 focus:ring-red-100"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">
                    Kapasitas / Personel
                </label>

                <input
                    type="number"
                    name="kapasitas_personel"
                    required
                    min="1"
                    max="999"
                    placeholder="Contoh: 6"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none focus:border-red-400 focus:ring-2 focus:ring-red-100"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">
                    Lokasi / Posko
                </label>

                <input
                    type="text"
                    name="lokasi"
                    required
                    maxlength="255"
                    placeholder="Contoh: Posko Pusat Genteng"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none focus:border-red-400 focus:ring-2 focus:ring-red-100"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">
                    Ketersediaan Awal
                </label>

                <select
                    name="status_ketersediaan"
                    required
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm"
                >
                    <option value="tersedia">Tersedia</option>
                    <option value="tidak_tersedia">Tidak Tersedia</option>
                </select>
            </div>

            <div class="flex justify-end gap-3 pt-2">

                <button
                    type="button"
                    data-modal-close
                    class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600"
                >
                    Batal
                </button>

                <button
                    type="submit"
                    class="rounded-xl bg-red-500 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-red-600"
                >
                    Tambah Unit
                </button>
            </div>
        </form>
    </div>
</div>


{{-- ========================================================= --}}
{{-- EDIT MODAL --}}
{{-- ========================================================= --}}

<div
    id="edit-unit-modal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/40 p-4"
>
    <div class="w-full max-w-lg rounded-2xl bg-white shadow-2xl">

        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">

            <div>
                <h3 class="text-lg font-semibold text-slate-800">
                    Edit Unit
                </h3>

                <p class="mt-1 text-xs text-slate-400">
                    Perbarui informasi unit operasional.
                </p>
            </div>

            <button
                type="button"
                data-modal-close
                class="text-xl text-slate-400 hover:text-slate-700"
            >
                ×
            </button>

        </div>

        <form
            id="edit-unit-form"
            method="POST"
            class="space-y-5 p-6"
        >
            @csrf
            @method('PUT')

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">
                    Nama Unit
                </label>

                <input
                    id="edit-name"
                    name="nama_resource"
                    type="text"
                    required
                    maxlength="100"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">
                    Jenis Unit
                </label>

                <input
                    id="edit-type"
                    name="jenis_resource"
                    type="text"
                    required
                    maxlength="50"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">
                    Kapasitas / Personel
                </label>

                <input
                    id="edit-capacity"
                    name="kapasitas_personel"
                    type="number"
                    required
                    min="1"
                    max="999"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm"
                >
            </div>

            <div class="flex justify-end gap-3 pt-2">

                <button
                    type="button"
                    data-modal-close
                    class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600"
                >
                    Batal
                </button>

                <button
                    type="submit"
                    class="rounded-xl bg-red-500 px-6 py-2.5 text-sm font-semibold text-white"
                >
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>


{{-- ========================================================= --}}
{{-- DELETE MODAL --}}
{{-- ========================================================= --}}

<div
    id="delete-unit-modal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/40 p-4"
>
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">

        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-50 text-xl text-red-500">
            !
        </div>

        <div class="mt-4 text-center">
            <h3 class="text-lg font-semibold text-slate-800">
                Hapus Unit?
            </h3>

            <p class="mt-2 text-sm leading-6 text-slate-500">
                Unit
                <strong id="delete-unit-name"></strong>
                akan dihapus dari daftar operasional.
            </p>
        </div>

        <form
            id="delete-unit-form"
            method="POST"
            class="mt-6 flex justify-center gap-3"
        >
            @csrf
            @method('DELETE')

            <button
                type="button"
                data-modal-close
                class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600"
            >
                Batal
            </button>

            <button
                type="submit"
                class="rounded-xl bg-red-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-600"
            >
                Hapus Unit
            </button>
        </form>

    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const openModal = (id) => {
        const modal = document.getElementById(id);

        if (!modal) return;

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        document.body.classList.add('overflow-hidden');
    };

    const closeModal = (modal) => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');

        document.body.classList.remove('overflow-hidden');
    };


    // Generic modal opener
    document
        .querySelectorAll('[data-modal-open]')
        .forEach(button => {
            button.addEventListener('click', function () {
                openModal(this.dataset.modalOpen);
            });
        });


    // Generic modal closer
    document
        .querySelectorAll('[data-modal-close]')
        .forEach(button => {
            button.addEventListener('click', function () {
                const modal = this.closest('.fixed');

                if (modal) {
                    closeModal(modal);
                }
            });
        });


    // Close when backdrop clicked
    document
        .querySelectorAll('.fixed[id$="-modal"]')
        .forEach(modal => {
            modal.addEventListener('click', function (event) {
                if (event.target === this) {
                    closeModal(this);
                }
            });
        });


    // Edit modal
    document
        .querySelectorAll('[data-edit-unit]')
        .forEach(button => {
            button.addEventListener('click', function () {

                const id = this.dataset.id;

                document.getElementById('edit-name').value =
                    this.dataset.name || '';

                document.getElementById('edit-type').value =
                    this.dataset.type || '';

                document.getElementById('edit-capacity').value =
                    this.dataset.capacity || '';

                document.getElementById('edit-unit-form').action =
                    `{{ url('/instansi/units') }}/${id}`;

                openModal('edit-unit-modal');
            });
        });


    // Delete modal
    document
        .querySelectorAll('[data-delete-unit]')
        .forEach(button => {
            button.addEventListener('click', function () {

                const id = this.dataset.id;
                const name = this.dataset.name;

                document.getElementById('delete-unit-name').textContent =
                    `"${name}"`;

                document.getElementById('delete-unit-form').action =
                    `{{ url('/instansi/units') }}/${id}`;

                openModal('delete-unit-modal');
            });
        });


    // Escape closes modal
    document.addEventListener('keydown', function (event) {

        if (event.key !== 'Escape') {
            return;
        }

        document
            .querySelectorAll('.fixed[id$="-modal"]:not(.hidden)')
            .forEach(closeModal);
    });

});
</script>

@endsection