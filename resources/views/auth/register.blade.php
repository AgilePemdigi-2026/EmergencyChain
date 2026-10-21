
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
    <form class="space-y-6">

        <!-- Grid Input -->
        <div class="grid grid-cols-1 gap-x-4 gap-y-5 sm:grid-cols-2">

            <!-- Nama Lengkap -->
            <div>
                <label for="name"
                       class="mb-2 block text-sm font-medium text-slate-800">
                    Nama Lengkap
                    <span class="text-red-500">*</span>
                </label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    placeholder="Masukkan nama lengkap"
                    autocomplete="name"
                    required
                    class="w-full rounded-xl border border-slate-200
                           bg-white px-4 py-3 text-sm text-slate-800
                           outline-none transition
                           placeholder:text-slate-400
                           focus:border-red-500
                           focus:ring-2 focus:ring-red-100"
                >
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
                    placeholder="Masukkan email"
                    autocomplete="email"
                    required
                    class="w-full rounded-xl border border-slate-200
                           bg-white px-4 py-3 text-sm text-slate-800
                           outline-none transition
                           placeholder:text-slate-400
                           focus:border-red-500
                           focus:ring-2 focus:ring-red-100"
                >
            </div>

            <!-- Password -->
            <div>
                <label for="password"
                       class="mb-2 block text-sm font-medium text-slate-800">
                    Password
                    <span class="text-red-500">*</span>
                </label>

                <div class="relative">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Masukkan password"
                        autocomplete="new-password"
                        minlength="8"
                        required
                        class="w-full rounded-xl border border-slate-200
                               bg-white py-3 pl-4 pr-12
                               text-sm text-slate-800
                               outline-none transition
                               placeholder:text-slate-400
                               focus:border-red-500
                               focus:ring-2 focus:ring-red-100"
                    >

                    <!-- Show / Hide Password -->
                    <button
                        type="button"
                        id="togglePassword"
                        aria-label="Tampilkan password"
                        aria-pressed="false"
                        class="absolute inset-y-0 right-0 flex
                               items-center px-4 text-slate-400
                               hover:text-red-500"
                    >
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>

                <p class="mt-2 text-xs text-slate-400">
                    Minimal 8 karakter, huruf besar, kecil, dan angka.
                </p>
            </div>

            <!-- Nomor Telepon -->
            <div>
                <label for="phone"
                       class="mb-2 block text-sm font-medium text-slate-800">
                    Nomor Telepon
                    <span class="text-red-500">*</span>
                </label>

                <input
                    type="tel"
                    id="phone"
                    name="phone"
                    placeholder="Contoh: 081234567890"
                    autocomplete="tel"
                    inputmode="tel"
                    required
                    class="w-full rounded-xl border border-slate-200
                           bg-white px-4 py-3 text-sm text-slate-800
                           outline-none transition
                           placeholder:text-slate-400
                           focus:border-red-500
                           focus:ring-2 focus:ring-red-100"
                >
            </div>

        </div>

        <!-- Persetujuan -->
        <div class="flex items-start gap-3">
            <input
                type="checkbox"
                id="agreement"
                name="agreement"
                required
                class="mt-1 h-4 w-4 shrink-0
                       accent-red-500"
            >

            <label for="agreement"
                   class="text-xs leading-5 text-slate-500">
                Saya menyetujui penggunaan data untuk layanan
                koordinasi darurat EmergencyChain.
            </label>
        </div>

        <!-- Tombol Daftar -->
        <button
            type="button"
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

<!-- JavaScript: Toggle Password -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const passwordInput = document.getElementById('password');
        const toggleButton = document.getElementById('togglePassword');

        if (!passwordInput || !toggleButton) return;

        toggleButton.addEventListener('click', function () {
            const isHidden = passwordInput.type === 'password';

            passwordInput.type = isHidden ? 'text' : 'password';
            toggleButton.setAttribute(
                'aria-label',
                isHidden ? 'Sembunyikan password' : 'Tampilkan password'
            );
            toggleButton.setAttribute(
                'aria-pressed',
                String(isHidden)
            );
        });
    });
</script>

@endsection
