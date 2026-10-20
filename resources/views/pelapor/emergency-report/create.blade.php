
@extends('layouts.pelapor')

@section('title', 'Buat Laporan Emergency - EmergencyChain')

@section('content')
<div class="mx-auto max-w-6xl">

    {{-- Judul --}}
    <div class="mb-6 lg:mb-8">
        <p class="text-[11px] font-bold tracking-widest text-blue-500">
            PELAPOR / LAPORAN BARU
        </p>

        <h1 class="mt-2 text-2xl font-semibold
                   tracking-tight sm:text-3xl">
            Buat Laporan Emergency
        </h1>
    </div>

    <div
        class="grid grid-cols-1 items-start gap-5
               lg:grid-cols-[minmax(0,1fr)_250px]"
    >

        {{-- Form --}}
        <form
            id="emergency-report-form"
            class="min-w-0 space-y-5"
            novalidate
        >
            {{-- Informasi Kejadian --}}
            <section
                class="rounded-xl border border-slate-100
                       bg-white p-4 shadow-sm sm:p-6"
            >
                <div class="mb-6 flex items-start gap-3">
                    <span
                        class="flex h-7 w-7 shrink-0 items-center
                               justify-center rounded-lg
                               bg-red-500 text-sm font-bold text-white"
                    >
                        1
                    </span>

                    <div class="min-w-0">
                        <h2 class="font-semibold">
                            Informasi Kejadian
                        </h2>
                        <p class="mt-1 text-xs leading-relaxed text-slate-400">
                            Berikan detail kejadian agar petugas dapat
                            menentukan respons yang tepat.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div class="min-w-0">
                        <label
                            for="jenis_kejadian"
                            class="mb-2 block text-sm font-medium"
                        >
                            Jenis Kejadian
                            <span class="text-red-500">*</span>
                        </label>

                        <select
                            id="jenis_kejadian"
                            name="jenis_kejadian"
                            required
                            class="w-full min-w-0 rounded-lg
                                   border border-slate-200 bg-white
                                   px-3 py-3 text-sm
                                   focus:border-red-400
                                   focus:outline-none"
                        >
                            <option value="">Pilih jenis kejadian</option>
                            <option value="Kecelakaan Lalu Lintas">
                                Kecelakaan Lalu Lintas
                            </option>
                            <option value="Kebakaran">Kebakaran</option>
                            <option value="Bencana Alam">Bencana Alam</option>
                            <option value="Darurat Medis">Darurat Medis</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div class="min-w-0">
                        <label
                            for="tingkat_kebutuhan"
                            class="mb-2 block text-sm font-medium"
                        >
                            Tingkat Kebutuhan
                            <span class="text-red-500">*</span>
                        </label>

                        <select
                            id="tingkat_kebutuhan"
                            name="tingkat_kebutuhan"
                            required
                            class="w-full min-w-0 rounded-lg
                                   border border-slate-200 bg-white
                                   px-3 py-3 text-sm
                                   focus:border-red-400
                                   focus:outline-none"
                        >
                            <option value="">Pilih tingkat kebutuhan</option>
                            <option value="Butuh Penanganan Cepat">
                                Butuh Penanganan Cepat
                            </option>
                            <option value="Butuh Penanganan Segera">
                                Butuh Penanganan Segera
                            </option>
                        </select>
                    </div>
                </div>

                <div class="mt-5">
                    <div class="mb-2 flex justify-between gap-2">
                        <label
                            for="kondisi_kejadian"
                            class="text-sm font-medium"
                        >
                            Kondisi Kejadian
                            <span class="text-red-500">*</span>
                        </label>
                    </div>

                    <textarea
                        id="kondisi_kejadian"
                        name="kondisi_kejadian"
                        rows="4"
                        maxlength="500"
                        required
                        placeholder="Jelaskan kondisi kejadian..."
                        class="w-full rounded-lg border
                               border-slate-200 px-3 py-3 text-sm
                               focus:border-red-400 focus:outline-none"
                    ></textarea>

                    <p
                        id="character-count"
                        class="mt-1 text-right text-xs text-slate-400"
                    >
                        0/500
                    </p>
                </div>

                <div class="mt-5">
                    <label
                        for="informasi_tambahan"
                        class="mb-2 block text-sm font-medium"
                    >
                        Informasi Tambahan (opsional)
                    </label>

                    <textarea
                        id="informasi_tambahan"
                        name="informasi_tambahan"
                        rows="3"
                        placeholder="Tambahkan informasi lain yang dapat membantu petugas..."
                        class="w-full rounded-lg border
                               border-slate-200 px-3 py-3 text-sm
                               focus:border-red-400 focus:outline-none"
                    ></textarea>
                </div>
            </section>

            {{-- Lokasi --}}
            <section
                class="rounded-xl border border-slate-100
                       bg-white p-4 shadow-sm sm:p-6"
            >
                <div class="mb-5 flex items-start gap-3">
                    <span
                        class="flex h-7 w-7 shrink-0 items-center
                               justify-center rounded-lg
                               bg-red-500 text-sm font-bold text-white"
                    >
                        2
                    </span>

                    <div class="min-w-0">
                        <h2 class="font-semibold">Lokasi Kejadian</h2>
                        <p class="mt-1 text-xs leading-relaxed text-slate-400">
                            Pastikan titik lokasi sesuai dengan
                            tempat kejadian.
                        </p>
                    </div>
                </div>

                
                {{-- Peta interaktif Leaflet --}}
                <div
                    id="location-map"
                    class="relative z-0 h-56 w-full overflow-hidden
                        rounded-lg bg-slate-100 sm:h-72"
                    aria-label="Peta lokasi kejadian"
                ></div>

                {{-- Koordinat yang nantinya dikirim ke backend --}}
                <input type="hidden" id="latitude" name="latitude">
                <input type="hidden" id="longitude" name="longitude">

                {{-- Informasi koordinat untuk pengguna --}}
                <div class="mt-3 rounded-lg bg-slate-50 px-4 py-3">
                    <p class="text-xs font-medium text-slate-600">
                        Koordinat Lokasi
                    </p>

                    <p
                        id="location-coordinates"
                        class="mt-1 break-words text-xs text-slate-500"
                        aria-live="polite"
                    >
                        Klik pada peta untuk menentukan lokasi.
                    </p>

                    <p class="mt-2 text-xs leading-relaxed text-slate-400">
                        Pilih titik langsung pada peta jika GPS tidak tersedia
                        atau lokasi yang terdeteksi kurang tepat.
                    </p>
                </div>

                {{-- Patokan Lokasi --}}
                <div class="mt-4">
                    <label
                        for="patokan_lokasi"
                        class="mb-2 block text-sm font-medium text-slate-700"
                    >
                        Patokan Lokasi
                        <span class="font-normal text-slate-400">(opsional)</span>
                    </label>

                    <textarea
                        id="patokan_lokasi"
                        name="patokan_lokasi"
                        rows="3"
                        maxlength="300"
                        placeholder="Contoh: Depan gerbang kampus, dekat halte bus, atau masuk gang sebelah minimarket."
                        class="w-full resize-y rounded-lg border border-slate-200
                            bg-white px-3 py-3 text-sm text-slate-700
                            placeholder:text-slate-400 focus:border-red-400
                            focus:outline-none focus:ring-2 focus:ring-red-100"
                        aria-describedby="patokan-help"
                    ></textarea>

                    <div class="mt-1 flex items-start justify-between gap-3">
                        <p id="patokan-help" class="text-xs leading-relaxed text-slate-400">
                            Tambahkan petunjuk agar petugas lebih mudah menemukan lokasi kejadian.
                        </p>

                        <span
                            id="patokan-count"
                            class="shrink-0 text-xs text-slate-400"
                        >
                            0/300
                        </span>
                    </div>
                </div>



                {{-- Informasi Lokasi --}}
                <div
                    class="mt-4 flex flex-col gap-4
                           rounded-lg bg-emerald-50 p-4
                           sm:flex-row sm:items-center
                           sm:justify-between"
                >
                    <div class="min-w-0">
                        <p
                            id="location-status"
                            class="text-sm font-semibold text-slate-700"
                            role="status"
                            aria-live="polite"
                        >
                            Lokasi belum ditentukan
                        </p>

                        <p class="mt-1 text-xs leading-relaxed text-slate-500">
                            Gunakan GPS atau pilih lokasi secara manual.
                        </p>
                    </div>

                    <button
                        type="button"
                        id="use-current-location"
                        disabled
                        class="w-full shrink-0 cursor-not-allowed
                               rounded-lg border border-red-200
                               bg-white px-4 py-3 text-sm
                               font-medium text-red-500 opacity-60
                               sm:w-auto"
                    >
                        Gunakan Lokasi Saya
                    </button>
                </div>
            </section>
        </form>

        {{-- Ringkasan --}}
        <aside
            class="min-w-0 rounded-xl border border-slate-100
                   bg-white p-5 shadow-sm
                   sm:p-6 lg:sticky lg:top-5"
        >
            <h2 class="mb-5 font-semibold">Ringkasan Laporan</h2>

            <div class="space-y-4">
                <div>
                    <p class="text-xs text-slate-400">Jenis Kejadian</p>
                    <p
                        id="summary-jenis"
                        class="mt-1 text-sm font-medium break-words"
                    >
                        Belum dipilih
                    </p>
                </div>

                <div>
                    <p class="text-xs text-slate-400">Tingkat Kebutuhan</p>
                    <p
                        id="summary-kebutuhan"
                        class="mt-1 text-sm font-medium break-words"
                    >
                        Belum dipilih
                    </p>
                </div>

                <div>
                    <p class="text-xs text-slate-400">Lokasi GPS</p>
                    <p
                        id="summary-location"
                        class="mt-1 text-sm font-medium text-amber-600"
                    >
                        Belum ditentukan
                    </p>
                </div>
            </div>

            <p class="my-5 text-xs leading-relaxed text-slate-400">
                Setelah fitur penyimpanan diaktifkan,
                laporan yang berhasil dikirim akan diteruskan
                untuk proses penanganan.
            </p>


            {{-- Tombol untuk memeriksa validasi laporan --}}
            <button
                id="submit-report"
                type="submit"
                form="emergency-report-form"
                class="w-full rounded-lg bg-red-500 px-4 py-3
                    text-sm font-semibold text-white
                    transition hover:bg-red-600"
            >
                Periksa Laporan
            </button>

            {{-- Tempat menampilkan pesan validasi --}}
            <p
                id="report-form-message"
                class="mt-3 text-sm"
                role="status"
                aria-live="polite"
            ></p>            

            <p class="mt-2 text-center text-xs text-slate-400">
                Fitur ini masih dalam tahap validasi.
                Laporan belum dikirim ke sistem.
            </p>

            <button
                type="reset"
                form="emergency-report-form"
                class="mt-3 w-full rounded-lg
                       bg-rose-50 px-4 py-3
                       text-sm font-semibold text-slate-600
                       transition hover:bg-rose-100"
            >
                Batal
            </button>

            <p class="mt-3 text-center text-xs text-slate-400">
                Penyimpanan laporan belum diaktifkan.
            </p>
        </aside>
    </div>
</div>
@endsection
