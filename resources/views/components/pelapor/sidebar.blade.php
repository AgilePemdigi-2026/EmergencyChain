
<aside
    class="sticky top-0 flex h-screen w-full flex-col
           border-r border-slate-200 bg-white
           lg:w-64 lg:shrink-0"
>
    {{-- Logo --}}
    <div class="px-6 pb-7 pt-8">
        <h1 class="text-2xl font-bold tracking-tight">
            Emergency<span class="text-red-500">Chain</span>
        </h1>

        <p class="mt-1 text-[10px] font-semibold
                  tracking-[0.15em] text-slate-400">
            INTEGRATED RESPONSE SYSTEM
        </p>
    </div>
    {{-- Navigasi --}}
    <nav class="min-h-0 flex-1 space-y-1 overflow-y-auto px-4 pb-4">
        <p class="mb-4 px-3 text-xs font-semibold tracking-widest text-slate-400">
            MENU UTAMA
        </p>

        <a
            href="{{ route('pelapor.laporan.create') }}"
            @if (request()->routeIs('pelapor.laporan.create'))
                aria-current="page"
            @endif
            class="flex items-center gap-3 rounded-xl px-4 py-3
                text-sm font-semibold transition
                {{ request()->routeIs('pelapor.laporan.create')
                        ? 'bg-red-100 text-red-500'
                        : 'text-slate-500 hover:bg-red-50 hover:text-red-500' }}"
        >
            <span aria-hidden="true" class="text-lg leading-none">+</span>
            Buat Laporan Emergency
        </a>

        <a
            href="{{ route('pelapor.laporan.index') }}"
            @if (request()->routeIs('pelapor.laporan.index'))
                aria-current="page"
            @endif
            class="flex items-center gap-3 rounded-xl px-4 py-3
                text-sm font-semibold transition
                {{ request()->routeIs('pelapor.laporan.index')
                        ? 'bg-red-100 text-red-500'
                        : 'text-slate-500 hover:bg-red-50 hover:text-red-500' }}"
        >
            <svg xmlns="http://www.w3.org/2000/svg"
                width="18" height="18" viewBox="0 0 24 24"
                fill="none" stroke="currentColor"
                stroke-width="1.7" stroke-linecap="round"
                stroke-linejoin="round" aria-hidden="true">
                <path d="M3 12h4l3-7 4 14 3-7h4" />
            </svg>

            Status Laporan
        </a>
    </nav>
    
{{-- Logout --}}
    <div class="mt-auto shrink-0 border-t border-slate-200 bg-white p-4">
        <button
            type="button"
            data-logout-open
            class="flex w-full items-center gap-3
                rounded-xl px-4 py-3
                text-left text-sm font-medium
                text-slate-500 transition
                hover:bg-red-50 hover:text-red-500
                focus-visible:outline-2
                focus-visible:outline-offset-2
                focus-visible:outline-red-500"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                width="18"
                height="18"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.7"
                stroke-linecap="round"
                stroke-linejoin="round"
                aria-hidden="true"
            >
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                <path d="m16 17 5-5-5-5" />
                <path d="M21 12H9" />
            </svg>
            Logout
        </button>
    </div>
</aside>
