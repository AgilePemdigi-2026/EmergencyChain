
<aside class="flex h-full min-h-screen w-full flex-col border-r
              border-slate-200 bg-white">

    {{-- Brand --}}
    <div class="px-6 pb-10 pt-8">
        <h1 class="whitespace-nowrap text-2xl font-bold tracking-tight
                   text-slate-700">
            Emergency<span class="text-red-500">Chain</span>
        </h1>
        <p class="mt-1 text-[10px] font-semibold tracking-[0.12em]
                  text-slate-400">
            INTEGRATED RESPONSE SYSTEM
        </p>
    </div>

    {{-- Navigasi --}}
    <nav class="flex-1 px-4">
        <p class="mb-5 px-3 text-xs font-semibold
                  tracking-widest text-slate-400">
            MENU UTAMA
        </p>

        {{-- Dashboard --}}
        <div class="mb-2 flex items-center gap-3 rounded-xl
                    px-4 py-3 text-sm font-medium text-slate-500">
            <svg class="h-5 w-5" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor"
                 stroke-width="1.8" aria-hidden="true">
                <rect x="3" y="3" width="7" height="7" rx="1"/>
                <rect x="14" y="3" width="7" height="7" rx="1"/>
                <rect x="3" y="14" width="7" height="7" rx="1"/>
                <rect x="14" y="14" width="7" height="7" rx="1"/>
            </svg>
            Dashboard
        </div>

        {{-- Emergency Case aktif --}}
        <div class="relative my-2">
            <span class="absolute -left-4 top-1/2 h-8 w-1
                         -translate-y-1/2 rounded-r bg-red-500">
            </span>

            <a href="{{ route('petugas.ec.cases.index') }}"
               aria-current="page"
               class="flex items-center gap-3 rounded-xl
                      bg-red-100 px-4 py-3
                      text-sm font-semibold text-red-500">

                <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor"
                     stroke-width="1.8" aria-hidden="true">
                    <rect x="3" y="5" width="18" height="16" rx="3"/>
                    <path d="M9 5V3h6v2M12 9v8M8 13h8"/>
                </svg>

                Emergency Case
            </a>
        </div>

        {{-- Menu fitur lainnya --}}
        <div class="my-2 flex items-center gap-3 rounded-xl px-4 py-3
                    text-sm font-medium text-slate-500">
            <svg class="h-5 w-5" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor"
                 stroke-width="1.8" aria-hidden="true">
                <path d="M3 20h18M5 20V9l7-5 7 5v11M9 20v-7h6v7"/>
            </svg>
            Resource Matching
        </div>

        <div class="my-2 flex items-center gap-3 rounded-xl px-4 py-3
                    text-sm font-medium text-slate-500">
            <svg class="h-5 w-5" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor"
                 stroke-width="1.8" aria-hidden="true">
                <path d="M5 21V3h14v18M3 21h18"/>
                <path d="M12 6v7M8.5 9.5h7"/>
            </svg>
            Hospital Matching
        </div>

        <div class="my-2 flex items-center gap-3 rounded-xl px-4 py-3
                    text-sm font-medium text-slate-500">
            <svg class="h-5 w-5" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor"
                 stroke-width="1.8" aria-hidden="true">
                <rect x="5" y="4" width="14" height="17" rx="2"/>
                <path d="M9 4V2h6v2M9 10h6M9 14h6"/>
            </svg>
            Assignment
        </div>
    </nav>


    {{-- Logout Petugas EC --}}
    <div class="mt-auto border-t border-slate-200 p-4">
        <button
            type="button"
            data-logout-open
            class="flex w-full items-center gap-3 rounded-xl
                px-4 py-3 text-sm font-medium text-slate-500
                transition hover:bg-rose-50 hover:text-red-500"
        >
            <svg
                class="h-5 w-5"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                aria-hidden="true"
            >
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <path d="m16 17 5-5-5-5M21 12H9"/>
            </svg>

            <span>Logout</span>
        </button>
    </div>


</aside>
