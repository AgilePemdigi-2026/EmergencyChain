
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'EmergencyChain')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-rose-50 text-slate-800">

    <div class="min-h-screen lg:flex">

        {{-- Sidebar Desktop --}}
        <div class="hidden lg:block">
            @include('components.pelapor.sidebar')
        </div>

        {{-- Overlay Mobile --}}
        <div
            id="sidebar-overlay"
            class="fixed inset-0 z-40 hidden bg-black/40 lg:hidden"
            aria-hidden="true"
        ></div>

        {{-- Sidebar Mobile --}}
        <div
            id="mobile-sidebar"
            class="fixed inset-y-0 left-0 z-50 w-[min(18rem,85vw)]
                   -translate-x-full bg-white shadow-xl
                   transition-transform duration-300 lg:hidden"
            role="dialog"
            aria-modal="true"
            aria-label="Menu navigasi"
            aria-hidden="true"
            inert
        >
            <div class="absolute right-3 top-3 z-10">
                <button
                    id="close-sidebar"
                    type="button"
                    aria-label="Tutup menu"
                    class="rounded-lg p-2 text-slate-600
                           hover:bg-slate-100
                           focus-visible:outline-2
                           focus-visible:outline-offset-2
                           focus-visible:outline-red-500"
                >
                    <span aria-hidden="true" class="text-2xl">&times;</span>
                </button>
            </div>

            @include('components.pelapor.sidebar')
        </div>

        {{-- Konten Utama --}}
        <div class="min-w-0 flex-1">

            {{-- Header --}}
            <header
                class="flex h-16 items-center justify-between
                       border-b border-slate-100 bg-white
                       px-4 sm:px-6 lg:h-20 lg:justify-end"
            >

                {{-- Mobile: Logo dan Menu --}}
                <div class="flex items-center gap-3 lg:hidden">
                    <button
                        id="open-sidebar"
                        type="button"
                        aria-label="Buka menu navigasi"
                        aria-controls="mobile-sidebar"
                        aria-expanded="false"
                        class="rounded-lg border border-slate-200
                               p-2 text-slate-700 hover:bg-slate-50
                               focus-visible:outline-2
                               focus-visible:outline-offset-2
                               focus-visible:outline-red-500"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="22"
                            height="22"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                d="M4 6h16M4 12h16M4 18h16"
                            />
                        </svg>
                    </button>

                    <div>
                        <p class="text-lg font-bold tracking-tight">
                            Emergency<span class="text-red-500">Chain</span>
                        </p>
                        <p class="text-[9px] font-semibold tracking-widest text-slate-400">
                            INTEGRATED RESPONSE SYSTEM
                        </p>
                    </div>
                </div>

                {{-- Profil Pelapor --}}
                <div class="flex items-center gap-3">
                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-medium text-slate-700">
                            Pelapor
                        </p>
                        <p class="text-xs text-slate-400">
                            Akun Pelapor
                        </p>
                    </div>

                    <div
                        class="flex h-10 w-10 shrink-0 items-center
                               justify-center rounded-full
                               bg-rose-100 text-sm font-bold text-red-500"
                        aria-label="Profil Pelapor"
                    >
                        PL
                    </div>
                </div>

            </header>

            {{-- Konten Halaman --}}
            <main class="px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                @yield('content')
            </main>

        </div>
    </div>

    {{-- Kontrol navigasi mobile --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.getElementById('mobile-sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const openButton = document.getElementById('open-sidebar');
            const closeButton = document.getElementById('close-sidebar');

            if (!sidebar || !overlay || !openButton || !closeButton) {
                return;
            }

            let previousFocus = null;

            function openSidebar() {
                previousFocus = document.activeElement;

                sidebar.inert = false;
                sidebar.setAttribute('aria-hidden', 'false');

                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');

                openButton.setAttribute('aria-expanded', 'true');
                document.body.classList.add('overflow-hidden');

                closeButton.focus();
            }

            function closeSidebar() {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');

                openButton.setAttribute('aria-expanded', 'false');
                document.body.classList.remove('overflow-hidden');

                if (previousFocus instanceof HTMLElement) {
                    previousFocus.focus();
                }

                sidebar.inert = true;
                sidebar.setAttribute('aria-hidden', 'true');
            }

            openButton.addEventListener('click', openSidebar);
            closeButton.addEventListener('click', closeSidebar);
            overlay.addEventListener('click', closeSidebar);

            document.addEventListener('keydown', (event) => {
                if (sidebar.inert) return;

                if (event.key === 'Escape') {
                    closeSidebar();
                    return;
                }

                if (event.key === 'Tab') {
                    const focusable = Array.from(
                        sidebar.querySelectorAll(
                            'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])'
                        )
                    ).filter((element) => !element.closest('[hidden]'));

                    if (!focusable.length) return;

                    const first = focusable[0];
                    const last = focusable[focusable.length - 1];

                    if (event.shiftKey && document.activeElement === first) {
                        event.preventDefault();
                        last.focus();
                    } else if (!event.shiftKey && document.activeElement === last) {
                        event.preventDefault();
                        first.focus();
                    }
                }
            });

            window.addEventListener('resize', () => {
                if (window.innerWidth >= 1024 && !sidebar.inert) {
                    closeSidebar();
                }
            });
        });
    </script>
    {{-- Modal Konfirmasi Logout --}}
    @include('components.logout-modal')
</body>
</html>
