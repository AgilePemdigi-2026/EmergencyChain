
<aside
    class="flex h-full w-full flex-col
           border-r border-slate-200 bg-white
           lg:min-h-screen lg:w-64"
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
    <nav class="flex-1 px-4">
        <p class="mb-4 px-3 text-xs font-semibold
                  tracking-widest text-slate-400">
            MENU UTAMA
        </p>

        <a
            href="{{ route('pelapor.laporan.create') }}"
            aria-current="page"
            class="mb-2 flex items-center gap-3
                   rounded-xl bg-red-100 px-4 py-3
                   text-sm font-semibold text-red-500
                   transition hover:bg-red-200"
        >
            <span aria-hidden="true" class="text-lg leading-none">+</span>
            Buat Laporan Emergency
        </a>

        {{-- Fitur Sprint 2 --}}
        <div
            class="flex items-center gap-3 rounded-xl
                   px-4 py-3 text-sm text-slate-400"
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
                <path d="M3 12h4l3-7 4 14 3-7h4" />
            </svg>

            <span>Status Laporan (Sprint 2)</span>
        </div>
    </nav>


    
{{-- Logout --}}
    <div class="mt-auto border-t border-slate-200 p-4">
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
