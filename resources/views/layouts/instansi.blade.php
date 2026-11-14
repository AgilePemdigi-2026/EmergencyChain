
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'EmergencyChain - Instansi')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-rose-50 text-slate-800">

    <div class="min-h-screen lg:grid lg:grid-cols-[256px_minmax(0,1fr)]">

        {{-- Sidebar desktop --}}
        <div class="hidden bg-white lg:block">
            @include('components.instansi.sidebar')
        </div>

        {{-- Area konten utama --}}
        <div class="min-w-0">

            {{-- Header --}}
            <header class="flex h-16 items-center justify-between border-b border-slate-100 bg-white px-4 sm:px-6 lg:h-20 lg:justify-end">

                {{-- Brand mobile --}}
                <span class="text-lg font-bold tracking-tight lg:hidden">
                    Emergency<span class="text-red-500">Chain</span>
                </span>

                {{-- Profil Instansi --}}
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

            {{-- Konten halaman --}}
            <main class="px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                @yield('content')
            </main>

        </div>
    </div>

    @include('components.logout-modal')
</body>
</html>
