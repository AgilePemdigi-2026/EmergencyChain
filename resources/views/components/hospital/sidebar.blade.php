@php
    $dashboardActive = request()->routeIs('hospital.referrals.index');
    $detailActive = request()->routeIs('hospital.referrals.show');
@endphp

<aside class="sticky top-0 flex h-screen w-full flex-col border-r border-slate-200 bg-white">

    {{-- Brand - sama dengan Instansi --}}
    <div class="px-6 pb-10 pt-8">
        <h1 class="whitespace-nowrap text-2xl font-bold tracking-tight text-slate-700">
            Emergency<span class="text-red-500">Chain</span>
        </h1>

        <p class="mt-1 text-[10px] font-semibold tracking-[0.12em] text-slate-400">
            INTEGRATED RESPONSE SYSTEM
        </p>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 px-4">

        <p class="mb-5 px-3 text-xs font-semibold tracking-widest text-slate-400">
            MENU UTAMA
        </p>

        {{-- Dashboard --}}
        <a
            href="{{ route('hospital.referrals.index') }}"
            class="relative mb-2 flex items-center gap-3 rounded-xl px-4 py-3
                   text-sm transition
                   {{ $dashboardActive
                       ? 'bg-red-100 font-semibold text-red-500'
                       : 'font-medium text-slate-500 hover:bg-rose-50 hover:text-red-500' }}"
        >
            @if ($dashboardActive)
                <span class="absolute -left-4 h-8 w-1 rounded-r-full bg-red-500"></span>
            @endif

            <svg class="h-5 w-5 shrink-0"
                 viewBox="0 0 24 24"
                 fill="none"
                 stroke="currentColor"
                 stroke-width="1.8"
                 aria-hidden="true">
                <rect x="3" y="3" width="7" height="7" rx="1"/>
                <rect x="14" y="3" width="7" height="7" rx="1"/>
                <rect x="3" y="14" width="7" height="7" rx="1"/>
                <rect x="14" y="14" width="7" height="7" rx="1"/>
            </svg>

            Dashboard
        </a>

        {{-- Status Emergency --}}
        <a
            href="{{ route('hospital.referrals.index') }}"
            class="relative mb-2 flex items-center gap-3 rounded-xl px-4 py-3
                   text-sm font-medium text-slate-500 transition
                   hover:bg-rose-50 hover:text-red-500"
        >
            <svg class="h-5 w-5 shrink-0"
                 viewBox="0 0 24 24"
                 fill="none"
                 stroke="currentColor"
                 stroke-width="1.8"
                 aria-hidden="true">
                <path d="M3 12h4l2.2-5 4.2 10 2.1-5H21"/>
            </svg>

            Status Emergency
        </a>

        {{-- Riwayat Penanganan --}}
        {{-- Belum ada route khusus riwayat --}}
        <div
            class="mb-2 flex cursor-not-allowed items-center gap-3 rounded-xl
                   px-4 py-3 text-sm font-medium text-slate-400"
            title="Halaman riwayat belum tersedia"
        >
            <svg class="h-5 w-5 shrink-0"
                 viewBox="0 0 24 24"
                 fill="none"
                 stroke="currentColor"
                 stroke-width="1.8"
                 aria-hidden="true">
                <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/>
                <rect x="9" y="3" width="6" height="4" rx="1"/>
                <path d="M9 12h6M9 16h6"/>
            </svg>

            Riwayat Penanganan
        </div>

    </nav>

    {{-- Logout - mekanisme asli tim --}}
    <div class="mt-auto border-t border-slate-200 p-4">
        <button
            type="button"
            data-logout-open
            class="flex w-full items-center gap-3 rounded-xl px-4 py-3
                   text-sm font-medium text-slate-500 transition
                   hover:bg-rose-50 hover:text-red-500"
        >
            <svg class="h-5 w-5 shrink-0"
                 viewBox="0 0 24 24"
                 fill="none"
                 stroke="currentColor"
                 stroke-width="1.8"
                 aria-hidden="true">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <path d="m16 17 5-5-5-5M21 12H9"/>
            </svg>

            Logout
        </button>
    </div>

</aside>
