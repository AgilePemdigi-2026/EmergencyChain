
@extends('layouts.auth')

@section('title', 'Login - EmergencyChain')

@section('content')

    <!-- Header -->
    <div class="mb-8">

        <span class="inline-flex rounded-full
                     bg-rose-50 px-3 py-1
                     text-xs font-medium text-red-500">
            AKSES AMAN
        </span>

        <h2 class="mt-5 text-2xl font-semibold
                   text-slate-800 sm:text-3xl">
            Selamat Datang
        </h2>

        <p class="mt-3 text-sm text-slate-500">
            Masuk ke akun EmergencyChain untuk melanjutkan.
        </p>

    </div>

    <!-- Form Login -->
    <form action="{{ route('login.store') }}"
          method="POST"
          class="space-y-5">

        @csrf

        <!-- Pesan Error Login -->
        @if ($errors->any())
            <div class="rounded-xl border border-red-200
                        bg-red-50 px-4 py-3
                        text-sm text-red-600"
                 role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- Email -->
        <div>
            <label for="email"
                   class="mb-2 block text-sm
                          font-medium text-slate-800">
                Email
            </label>

            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="Masukkan email"
                autocomplete="username"
                required
                autofocus
                class="w-full rounded-xl border
                       @error('email') border-red-500 @else border-slate-200 @enderror
                       bg-white px-4 py-3
                       text-sm text-slate-800
                       outline-none transition
                       placeholder:text-slate-400
                       focus:border-red-500
                       focus:ring-2 focus:ring-red-100"
            >
        </div>

        <!-- Password -->
        <div>
            <label for="password"
                   class="mb-2 block text-sm
                          font-medium text-slate-800">
                Password
            </label>

            <input
                type="password"
                id="password"
                name="password"
                placeholder="Masukkan password"
                autocomplete="current-password"
                required
                class="w-full rounded-xl border
                       @error('password') border-red-500 @else border-slate-200 @enderror
                       bg-white px-4 py-3
                       text-sm text-slate-800
                       outline-none transition
                       placeholder:text-slate-400
                       focus:border-red-500
                       focus:ring-2 focus:ring-red-100"
            >
        </div>

        <!-- Remember Me dan Lupa Password -->
        <div class="flex flex-wrap items-center
                    justify-between gap-3">

            <label class="flex items-center gap-2
                          text-sm text-slate-500">

                <input
                    type="checkbox"
                    name="remember"
                    value="1"
                    {{ old('remember') ? 'checked' : '' }}
                    class="h-4 w-4 rounded
                           border-slate-200
                           accent-red-500"
                >

                Ingat saya
            </label>

            <!-- Tampilan sementara, fitur lupa password belum tersedia -->
            <span class="text-sm font-medium
                         text-red-500">
                Lupa password?
            </span>

        </div>

        <!-- Tombol Login -->
        <button
            type="submit"
            class="w-full rounded-xl bg-red-500
                   px-4 py-3 text-sm font-semibold
                   text-white transition
                   hover:bg-red-600
                   focus:outline-none
                   focus:ring-2 focus:ring-red-200">
            Login
        </button>

    </form>

    <!-- Link Register -->
    <p class="mt-8 text-center text-sm text-slate-500">
        Belum punya akun?

        <a href="{{ route('register') }}"
           class="font-semibold text-red-500 hover:text-red-600">
            Daftar
        </a>
    </p>

@endsection
