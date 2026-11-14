
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'EmergencyChain - Petugas EC')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-rose-50 text-slate-800">

    {{-- Layout utama --}}
    <div class="min-h-screen lg:grid lg:grid-cols-[256px_minmax(0,1fr)]">

        {{-- Sidebar Desktop --}}
        <div class="hidden bg-white lg:block">
            @include('components.petugas-ec.sidebar')
        </div>

        {{-- Area konten --}}
        <div class="min-w-0">

            {{-- Header --}}
            <header
                class="flex h-16 items-center justify-between
                       border-b border-slate-100 bg-white
                       px-4 sm:px-6 lg:h-20 lg:justify-end"
            >

                {{-- Logo mobile --}}
                <a
                    href="{{ route('petugas.ec.dashboard') }}"
                    class="text-lg font-bold tracking-tight lg:hidden"
                >
                    Emergency<span class="text-red-500">Chain</span>
                </a>

                {{-- Profil Petugas --}}
                <a
                    href="{{ route('profile.show') }}"
                    class="inline-flex items-center gap-3 rounded-xl
                        px-3 py-2 transition hover:bg-rose-50"
                    title="Lihat Profil"
                >
                    <div class="text-right">
                        <p class="text-sm font-semibold text-slate-700">
                            {{ auth()->user()->nama }}
                        </p>
                        <p class="text-xs text-slate-400">
                            {{ str_replace('_', ' ', ucfirst(auth()->user()->role)) }}
                        </p>
                    </div>

                    <div class="flex h-10 w-10 items-center justify-center
                                rounded-full bg-rose-100 font-bold text-red-500">
                        {{ mb_strtoupper(mb_substr(auth()->user()->nama, 0, 1)) }}
                    </div>
                </a>

            </header>

            {{-- Navigasi mobile --}}
            <nav class="flex gap-2 border-b border-slate-100 bg-white
                        px-4 py-3 lg:hidden">

                <a
                    href="{{ route('petugas.ec.dashboard') }}"
                    class="rounded-lg px-4 py-2 text-sm font-semibold transition
                        {{ request()->routeIs('petugas.ec.dashboard')
                            ? 'bg-red-100 text-red-500'
                            : 'text-slate-500 hover:bg-rose-50' }}"
                >
                    Dashboard
                </a>

                <a
                    href="{{ route('petugas.ec.cases.index') }}"
                    class="rounded-lg px-4 py-2 text-sm font-semibold transition
                        {{ request()->routeIs('petugas.ec.cases.*')
                            ? 'bg-red-100 text-red-500'
                            : 'text-slate-500 hover:bg-rose-50' }}"
                >
                    Emergency Case
                </a>

            </nav>

            {{-- Konten halaman --}}
            <main class="px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                @yield('content')
            </main>

        </div>

    </div>
    @include('components.logout-modal')
</body>
</html>
