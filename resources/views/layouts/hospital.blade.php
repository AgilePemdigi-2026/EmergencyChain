
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'EmergencyChain - Rumah Sakit')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body class="min-h-screen bg-rose-50 text-slate-800">

    <div class="min-h-screen lg:grid lg:grid-cols-[256px_minmax(0,1fr)]">

        {{-- Sidebar desktop --}}
        <div class="hidden bg-white lg:block">
            @include('components.hospital.sidebar')
        </div>

        {{-- Area konten utama --}}
        <div class="min-w-0">

            {{-- Header --}}
            <header
                class="flex h-16 items-center justify-between
                       border-b border-slate-100 bg-white
                       px-4 sm:px-6 lg:h-20 lg:justify-end"
            >

                {{-- Brand mobile --}}
                <a
                    href="{{ route('hospital.referrals.index') }}"
                    class="text-lg font-bold tracking-tight lg:hidden"
                >
                    Emergency<span class="text-red-500">Chain</span>
                </a>

                {{-- Profil Rumah Sakit --}}
                <div class="flex items-center gap-3">
                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-semibold text-slate-700">
                            {{ auth()->user()->rumahSakit?->nama_rs ?? 'Rumah Sakit' }}
                        </p>

                        <p class="text-xs text-slate-400">
                            Rumah Sakit
                        </p>
                    </div>

                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-rose-100 text-xs font-bold text-red-500">
                        RS
                    </div>
                </div>

            </header>

            {{-- Navigasi mobile --}}
            <nav class="flex items-center justify-between border-b border-slate-100 bg-white px-4 py-3 lg:hidden">
                <a
                    href="{{ route('hospital.referrals.index') }}"
                    class="rounded-lg bg-red-100 px-4 py-2 text-sm font-semibold text-red-500"
                >
                    Dashboard RS
                </a>

                <button
                    type="button"
                    data-logout-open
                    class="rounded-lg px-4 py-2 text-sm font-medium text-slate-500"
                >
                    Logout
                </button>
            </nav>

            {{-- Konten halaman --}}
            <main class="px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                @yield('content')
            </main>

        </div>
    </div>

    {{-- Modal konfirmasi logout yang sama dengan Instansi --}}
    @include('components.logout-modal')

</body>
</html>
