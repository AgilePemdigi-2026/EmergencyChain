
<aside class="flex min-h-screen w-full flex-col border-r border-slate-200 bg-white">

    {{-- Brand EmergencyChain --}}
    <div class="px-6 pb-10 pt-8">
        <h1 class="whitespace-nowrap text-2xl font-bold tracking-tight text-slate-700">
            Emergency<span class="text-red-500">Chain</span>
        </h1>
        <p class="mt-1 text-[10px] font-semibold tracking-[0.12em] text-slate-400">
            INTEGRATED RESPONSE SYSTEM
        </p>
    </div>

    {{-- Navigasi --}}
    <nav class="flex-1 px-4">

        <p class="mb-5 px-3 text-xs font-semibold tracking-widest text-slate-400">
            MENU UTAMA
        </p>

        {{-- Dashboard Assignment --}}
        <div class="mb-2 flex items-center gap-3 rounded-xl bg-red-100 px-4 py-3 text-sm font-semibold text-red-500">
            <svg class="h-5 w-5" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="1.8"
                 aria-hidden="true">
                <rect x="3" y="3" width="7" height="7" rx="1"/>
                <rect x="14" y="3" width="7" height="7" rx="1"/>
                <rect x="3" y="14" width="7" height="7" rx="1"/>
                <rect x="14" y="14" width="7" height="7" rx="1"/>
            </svg>
            Dashboard Assignment
        </div>

        {{-- Status Emergency --}}
        <div class="mb-2 flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-500">
            <svg class="h-5 w-5" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="1.8"
                 aria-hidden="true">
                <path d="M12 3v18M3 12h18"/>
                <circle cx="12" cy="12" r="9"/>
            </svg>
            Status Emergency
        </div>

        {{-- Kelola Unit --}}
        <div class="mb-2 flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-500">
            <svg class="h-5 w-5" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="1.8"
                 aria-hidden="true">
                <rect x="3" y="7" width="18" height="13" rx="2"/>
                <path d="M7 7V5a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v2"/>
            </svg>
            Kelola Unit
        </div>

    </nav>

    {{-- Logout --}}
    <div class="mt-auto border-t border-slate-200 p-4">
        <button
            type="button"
            data-logout-open
            class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-500 transition hover:bg-rose-50 hover:text-red-500"
        >
            <svg class="h-5 w-5" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="1.8"
                 aria-hidden="true">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <path d="m16 17 5-5-5-5M21 12H9"/>
            </svg>

            Logout
        </button>
    </div>

</aside>
