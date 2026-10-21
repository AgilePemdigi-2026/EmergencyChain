
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'EmergencyChain')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-rose-50 font-sans antialiased">

    <main class="min-h-screen lg:grid lg:grid-cols-2">

        <!-- BAGIAN KIRI: BRANDING EMERGENCYCHAIN -->
        <section
            class="flex flex-col justify-center
                   bg-rose-50 px-6 py-10
                   sm:px-10 lg:min-h-screen lg:px-14 xl:px-16"
        >

            <div class="mx-auto w-full max-w-xl">

                <!-- Logo -->
                <h1
                    class="text-3xl font-bold tracking-tight
                           text-slate-800 sm:text-4xl
                           xl:text-5xl"
                >
                    Emergency<span class="text-red-500">Chain</span>
                </h1>

                <!-- Tagline -->
                <h2
                    class="mt-10 text-2xl font-semibold
                           leading-tight tracking-tight
                           text-slate-800
                           sm:text-3xl xl:text-4xl"
                >
                    Respons lebih cepat.
                    <span class="block">
                        Koordinasi lebih tepat.
                    </span>
                </h2>

                <!-- Deskripsi -->
                <p
                    class="mt-5 max-w-md text-sm
                           leading-7 text-slate-500"
                >
                    Menghubungkan pelapor, petugas,
                    instansi penanganan, dan rumah sakit
                    dalam satu rantai respons darurat.
                </p>

                <!-- TAHAPAN EMERGENCYCHAIN -->
                <div class="mt-10 w-full max-w-lg">

                    <div class="flex items-start">

                        <!-- LAPORAN -->
                        <div class="flex shrink-0 flex-col items-center">

                            <div
                                class="flex h-14 w-14 items-center
                                       justify-center rounded-2xl
                                       border border-rose-100
                                       bg-white shadow-sm
                                       sm:h-16 sm:w-16"
                            >
                                <svg
                                    class="h-6 w-6 text-red-500 sm:h-7 sm:w-7"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M10.29 3.86 1.82 18a2 2
                                           0 0 0 1.71 3h16.94a2 2
                                           0 0 0 1.71-3L13.71
                                           3.86a2 2 0 0 0-3.42 0z"
                                    />
                                    <path d="M12 9v4" />
                                    <path d="M12 17h.01" />
                                </svg>
                            </div>

                            <p class="mt-3 text-xs font-medium text-slate-500 sm:text-sm">
                                Laporan
                            </p>

                        </div>

                        <!-- GARIS PENGHUBUNG 1 -->
                        <div
                            class="mt-7 h-px min-w-3 flex-1
                                   bg-rose-200 sm:mt-8"
                            aria-hidden="true"
                        ></div>

                        <!-- KOORDINASI -->
                        <div class="flex shrink-0 flex-col items-center">

                            <div
                                class="flex h-14 w-14 items-center
                                       justify-center rounded-2xl
                                       border border-rose-100
                                       bg-white shadow-sm
                                       sm:h-16 sm:w-16"
                            >
                                <svg
                                    class="h-6 w-6 text-red-500 sm:h-7 sm:w-7"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <path d="M3 21h18" />
                                    <path d="M5 21V8l7-4 7 4v13" />
                                    <path d="M9 21v-6h6v6" />
                                    <path d="M9 10h.01" />
                                    <path d="M15 10h.01" />
                                </svg>
                            </div>

                            <p class="mt-3 text-xs font-medium text-slate-500 sm:text-sm">
                                Koordinasi
                            </p>

                        </div>

                        <!-- GARIS PENGHUBUNG 2 -->
                        <div
                            class="mt-7 h-px min-w-3 flex-1
                                   bg-rose-200 sm:mt-8"
                            aria-hidden="true"
                        ></div>

                        <!-- PENANGANAN -->
                        <div class="flex shrink-0 flex-col items-center">

                            <div
                                class="flex h-14 w-14 items-center
                                       justify-center rounded-2xl
                                       border border-rose-100
                                       bg-white shadow-sm
                                       sm:h-16 sm:w-16"
                            >
                                <svg
                                    class="h-6 w-6 text-red-500 sm:h-7 sm:w-7"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M2 12h4l3-7 4 14 3-7h6"
                                    />
                                </svg>
                            </div>

                            <p class="mt-3 text-xs font-medium text-slate-500 sm:text-sm">
                                Penanganan
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </section>

        <!-- BAGIAN KANAN: FORM LOGIN / REGISTER -->
        <section
            class="flex min-h-screen items-center
                   justify-center bg-white
                   px-4 py-12
                   sm:px-8 lg:px-10"
        >

            <div
                class="w-full max-w-lg rounded-xl
                       bg-white p-4 sm:p-6"
            >

                @yield('content')

            </div>

        </section>

    </main>

</body>
</html>
