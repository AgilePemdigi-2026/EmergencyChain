
@php
    $layout = match ($user->role) {
        'pelapor' => 'layouts.pelapor',
        'petugas_ec' => 'layouts.petugas-ec',
        'instansi' => 'layouts.instansi',
        'rumah_sakit' => 'layouts.hospital',
        default => abort(403),
    };

    $initial = mb_strtoupper(
        mb_substr($user->nama, 0, 1)
    );
@endphp

@extends($layout)

@section('title', 'Profil Pengguna - EmergencyChain')

@section('content')

<div class="mx-auto max-w-5xl space-y-6">

    {{-- Header Profil --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div>
            <p class="text-xs font-bold tracking-widest text-red-500">
                EMERGENCYCHAIN
            </p>

            <h1 class="mt-1 text-2xl font-bold text-slate-800">
                Profil Pengguna
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Informasi akun yang terdaftar di EmergencyChain.
            </p>
        </div>

        {{-- Tombol Kembali --}}
        <a
            href="{{ $backUrl }}"
            class="inline-flex min-h-12 items-center justify-center
                rounded-xl border border-slate-200 bg-white
                px-7 py-3 text-sm font-medium text-slate-700
                transition hover:border-red-200 hover:bg-rose-50
                hover:text-red-500"
        >
            Kembali
        </a>

    </div>

    {{-- Notifikasi berhasil --}}
    @if (session('success'))
        <div
            role="status"
            class="rounded-xl border border-emerald-200 bg-emerald-50
                   px-5 py-4 text-sm text-emerald-700"
        >
            {{ session('success') }}
        </div>
    @endif

    {{-- Identitas akun --}}
    <section class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">

        <div class="flex flex-wrap items-center gap-4">

            <div
                class="flex h-16 w-16 items-center justify-center
                       rounded-full bg-rose-100 text-2xl font-bold
                       text-red-500"
            >
                {{ $initial }}
            </div>

            <div>
                <h2 class="text-xl font-bold text-slate-800">
                    {{ $user->nama }}
                </h2>

                <span
                    class="mt-2 inline-flex rounded-full bg-rose-50
                           px-3 py-1 text-xs font-semibold text-red-500"
                >
                    {{ $roleLabel }}
                </span>
            </div>

        </div>

        <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2">

            <div>
                <p class="text-xs font-medium text-slate-400">
                    Nama Lengkap
                </p>
                <p class="mt-2 font-semibold text-slate-800">
                    {{ $user->nama }}
                </p>
            </div>

            <div>
                <p class="text-xs font-medium text-slate-400">
                    Aktor
                </p>
                <p class="mt-2 font-semibold text-slate-800">
                    {{ $roleLabel }}
                </p>
            </div>

            <div>
                <p class="text-xs font-medium text-slate-400">
                    Email
                </p>
                <p class="mt-2 break-all font-semibold text-slate-800">
                    {{ $user->email }}
                </p>
            </div>

            <div>
                <p class="text-xs font-medium text-slate-400">
                    Nomor Telepon
                </p>
                <p class="mt-2 font-semibold text-slate-800">
                    {{ $user->no_telp ?: 'Belum tersedia' }}
                </p>
            </div>

            @if ($user->role === 'instansi' && $user->instansi)
                <div class="sm:col-span-2">
                    <p class="text-xs font-medium text-slate-400">
                        Instansi Terkait
                    </p>

                    <p class="mt-2 font-semibold text-slate-800">
                        {{ $user->instansi->nama_instansi }}
                    </p>
                </div>
            @endif

            @if ($user->role === 'rumah_sakit' && $user->rumahSakit)
                <div class="sm:col-span-2">
                    <p class="text-xs font-medium text-slate-400">
                        Rumah Sakit Terkait
                    </p>

                    <p class="mt-2 font-semibold text-slate-800">
                        {{ $user->rumahSakit->nama_rs }}
                    </p>
                </div>
            @endif

        </div>

    </section>

    @if ($canEdit)

        {{-- Form Edit Profil Pelapor --}}
        <section class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">

            <h2 class="text-lg font-bold text-slate-800">
                Edit Informasi Profil
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Perbarui nama, email, dan nomor telepon.
            </p>

            <form
                action="{{ route('profile.update') }}"
                method="POST"
                class="mt-6 space-y-5"
            >
                @csrf
                @method('PATCH')

                <div>
                    <label
                        for="nama"
                        class="mb-2 block text-sm font-medium text-slate-700"
                    >
                        Nama Lengkap
                    </label>

                    <input
                        id="nama"
                        name="nama"
                        type="text"
                        maxlength="100"
                        required
                        value="{{ old('nama', $user->nama) }}"
                        class="w-full rounded-xl border border-slate-200
                               px-4 py-3 text-sm outline-none
                               focus:border-red-400"
                    >

                    @error('nama')
                        <p class="mt-1 text-xs text-red-500">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label
                        for="email"
                        class="mb-2 block text-sm font-medium text-slate-700"
                    >
                        Email
                    </label>

                    <input
                        id="email"
                        name="email"
                        type="email"
                        maxlength="255"
                        required
                        value="{{ old('email', $user->email) }}"
                        class="w-full rounded-xl border border-slate-200
                               px-4 py-3 text-sm outline-none
                               focus:border-red-400"
                    >

                    @error('email')
                        <p class="mt-1 text-xs text-red-500">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label
                        for="no_telp"
                        class="mb-2 block text-sm font-medium text-slate-700"
                    >
                        Nomor Telepon
                    </label>

                    <input
                        id="no_telp"
                        name="no_telp"
                        type="tel"
                        maxlength="20"
                        required
                        value="{{ old('no_telp', $user->no_telp) }}"
                        class="w-full rounded-xl border border-slate-200
                               px-4 py-3 text-sm outline-none
                               focus:border-red-400"
                    >

                    @error('no_telp')
                        <p class="mt-1 text-xs text-red-500">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="flex justify-end">
                    <button
                        type="submit"
                        class="rounded-xl bg-red-500 px-6 py-3
                               text-sm font-semibold text-white
                               hover:bg-red-600"
                    >
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </section>

        {{-- Form Ubah Password --}}
        <section class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">

            <h2 class="text-lg font-bold text-slate-800">
                Ubah Password
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Gunakan password yang kuat untuk keamanan akun.
            </p>

            <form
                action="{{ route('profile.password.update') }}"
                method="POST"
                class="mt-6 space-y-5"
            >
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password"
                           class="mb-2 block text-sm font-medium text-slate-700">
                        Password Saat Ini
                    </label>

                    <input
                        id="current_password"
                        name="current_password"
                        type="password"
                        autocomplete="current-password"
                        required
                        class="w-full rounded-xl border border-slate-200
                               px-4 py-3 text-sm outline-none
                               focus:border-red-400"
                    >

                    @error('current_password')
                        <p class="mt-1 text-xs text-red-500">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label for="password"
                           class="mb-2 block text-sm font-medium text-slate-700">
                        Password Baru
                    </label>

                    <input
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="new-password"
                        required
                        class="w-full rounded-xl border border-slate-200
                               px-4 py-3 text-sm outline-none
                               focus:border-red-400"
                    >

                    @error('password')
                        <p class="mt-1 text-xs text-red-500">
                            {{ $message }}
                        </p>
                    @enderror

                    <p class="mt-2 text-xs text-slate-400">
                        Minimal 8 karakter, kombinasi huruf besar,
                        huruf kecil, angka, dan simbol.
                    </p>
                </div>

                <div>
                    <label for="password_confirmation"
                           class="mb-2 block text-sm font-medium text-slate-700">
                        Konfirmasi Password Baru
                    </label>

                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        required
                        class="w-full rounded-xl border border-slate-200
                               px-4 py-3 text-sm outline-none
                               focus:border-red-400"
                    >
                </div>

                <div class="flex justify-end">
                    <button
                        type="submit"
                        class="rounded-xl bg-red-500 px-6 py-3
                               text-sm font-semibold text-white
                               hover:bg-red-600"
                    >
                        Ubah Password
                    </button>
                </div>

            </form>
        </section>

    @else

        <div class="rounded-xl border border-slate-200 bg-white px-5 py-4">
            <p class="text-sm text-slate-500">
                Informasi profil akun ini hanya dapat dilihat.
                Perubahan data akun dikelola oleh administrator sistem.
            </p>
        </div>

    @endif

</div>

@endsection
