
@extends('layouts.auth')

@section('title', 'Register - EmergencyChain')

@section('content')

<div class="w-full rounded-xl border border-rose-100 bg-white p-4 shadow-xl shadow-rose-100/40 sm:p-6">

    <!-- Header Register -->
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-slate-800 sm:text-3xl">
            Daftar Akun EmergencyChain
        </h2>

        <p class="mt-3 text-sm leading-6 text-slate-500">
            Buat akun untuk mengakses layanan koordinasi
            darurat EmergencyChain.
        </p>
    </div>

    <!-- Form Register -->
    <form action="{{ route('register.store') }}"
          method="POST"
          class="space-y-6">

        @csrf

        <!-- Pesan Error -->
        @if ($errors->any())
            <div class="rounded-xl border border-red-200
                        bg-red-50 px-4 py-3 text-sm text-red-600"
                 role="alert">
                Periksa kembali data pendaftaran Anda.
            </div>
        @endif

        <!-- Grid Input -->
        <div class="grid grid-cols-1 gap-x-4 gap-y-5 sm:grid-cols-2">

            <!-- Nama Lengkap -->
            <div>
                <label for="nama"
                       class="mb-2 block text-sm font-medium text-slate-800">
                    Nama Lengkap
                    <span class="text-red-500">*</span>
                </label>

                <input
                    type="text"
                    id="nama"
                    name="nama"
                    value="{{ old('nama') }}"
                    placeholder="Masukkan nama lengkap"
                    autocomplete="name"
                    maxlength="100"
                    required
                    class="w-full rounded-xl border
                           @error('nama') border-red-500 @else border-slate-200 @enderror
                           bg-white px-4 py-3 text-sm text-slate-800
                           outline-none transition
                           placeholder:text-slate-400
                           focus:border-red-500
                           focus:ring-2 focus:ring-red-100"
                >

                @error('nama')
                    <p class="mt-2 text-xs text-red-500">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email"
                       class="mb-2 block text-sm font-medium text-slate-800">
                    Email
                    <span class="text-red-500">*</span>
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="Masukkan email"
                    autocomplete="email"
                    maxlength="100"
                    required
                    class="w-full rounded-xl border
                           @error('email') border-red-500 @else border-slate-200 @enderror
                           bg-white px-4 py-3 text-sm text-slate-800
                           outline-none transition
                           placeholder:text-slate-400
                           focus:border-red-500
                           focus:ring-2 focus:ring-red-100"
                >

                @error('email')
                    <p class="mt-2 text-xs text-red-500">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Nomor Telepon -->
            <div>
                <label for="no_telp"
                       class="mb-2 block text-sm font-medium text-slate-800">
                    Nomor Telepon
                    <span class="text-red-500">*</span>
                </label>

                <input
                    type="tel"
                    id="no_telp"
                    name="no_telp"
                    value="{{ old('no_telp') }}"
                    placeholder="Contoh: 081234567890"
                    autocomplete="tel"
                    inputmode="tel"
                    maxlength="20"
                    required
                    class="w-full rounded-xl border
                           @error('no_telp') border-red-500 @else border-slate-200 @enderror
                           bg-white px-4 py-3 text-sm text-slate-800
                           outline-none transition
                           placeholder:text-slate-400
                           focus:border-red-500
                           focus:ring-2 focus:ring-red-100"
                >

                @error('no_telp')
                    <p class="mt-2 text-xs text-red-500">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password"
                    class="mb-2 block text-sm font-medium text-slate-800">
                    Password
                    <span class="text-red-500">*</span>
                </label>

                <x-password-input
                    id="password"
                    name="password"
                    placeholder="Masukkan password"
                    autocomplete="new-password"
                    minlength="8"
                    required
                />

                <p class="mt-2 text-xs text-slate-400">
                    Minimal 8 karakter, huruf besar, huruf kecil,
                    angka, dan simbol.
                </p>

                @error('password')
                    <p class="mt-2 text-xs text-red-500">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Konfirmasi Password -->
            <div>
                <label for="password_confirmation"
                    class="mb-2 block text-sm font-medium text-slate-800">
                    Konfirmasi Password
                    <span class="text-red-500">*</span>
                </label>

                <x-password-input
                    id="password_confirmation"
                    name="password_confirmation"
                    placeholder="Masukkan ulang password"
                    autocomplete="new-password"
                    minlength="8"
                    required
                />
            </div>

        <!-- Persetujuan -->
        <div class="flex items-start gap-3">
            <input
                type="checkbox"
                id="agreement"
                name="agreement"
                value="1"
                {{ old('agreement') ? 'checked' : '' }}
                required
                class="mt-1 h-4 w-4 shrink-0 accent-red-500"
            >

            <label for="agreement"
                   class="text-xs leading-5 text-slate-500">
                Saya menyetujui penggunaan data untuk layanan
                koordinasi darurat EmergencyChain.
            </label>
        </div>

        <!-- Tombol Daftar -->
        <button
            type="submit"
            class="w-full rounded-xl bg-red-500
                   px-4 py-3 text-sm font-semibold
                   text-white shadow-md shadow-red-100
                   transition hover:bg-red-600
                   focus:outline-none
                   focus:ring-2 focus:ring-red-200"
        >
            Daftar
        </button>

    </form>

    <!-- Link Login -->
    <p class="mt-6 text-center text-sm text-slate-500">
        Sudah punya akun?

        <a href="{{ route('login') }}"
           class="ml-1 font-semibold text-red-500
                  hover:text-red-600">
            Masuk
        </a>
    </p>

</div>

@endsection
