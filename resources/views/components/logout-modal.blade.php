
<!-- MODAL KONFIRMASI LOGOUT -->
<dialog
    id="logout-confirm-modal"
    aria-labelledby="logout-modal-title"
    aria-describedby="logout-modal-description"
    class="m-auto w-[calc(100%-2rem)] max-w-sm
           rounded-xl border border-slate-200
           bg-white p-4 shadow-xl
           backdrop:bg-slate-900/50
           sm:p-6"
>
    <!-- Icon Logout -->
    <div class="flex flex-col items-center text-center">

        <div class="flex h-16 w-16 items-center
                    justify-center rounded-full bg-red-50">

            <svg
                class="h-8 w-8 text-red-500"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                stroke-linecap="round"
                stroke-linejoin="round"
                aria-hidden="true"
            >
                <path d="M10 17l5-5-5-5"/>
                <path d="M15 12H3"/>
                <path d="M12 3h6a3 3 0 0 1 3 3v12a3 3 0 0 1-3 3h-6"/>
            </svg>

        </div>

        <!-- Judul -->
        <h2
            id="logout-modal-title"
            class="mt-5 text-xl font-semibold text-slate-800"
        >
            Yakin ingin keluar?
        </h2>

        <!-- Deskripsi -->
        <p
            id="logout-modal-description"
            class="mt-3 text-sm leading-6 text-slate-500"
        >
            Anda akan keluar dari akun EmergencyChain.
            Pastikan semua aktivitas telah selesai.
        </p>

    </div>

    <!-- Tombol Konfirmasi -->
    <div class="mt-6 grid grid-cols-2 gap-3">

        <!-- Batal -->
        <button
            type="button"
            data-logout-close
            class="rounded-xl border border-slate-200
                   bg-white px-4 py-3 text-sm
                   font-medium text-slate-800
                   transition hover:bg-slate-50
                   focus-visible:outline-2
                   focus-visible:outline-offset-2
                   focus-visible:outline-red-500"
        >
            Batal
        </button>

        <!-- Konfirmasi Logout -->
        <a href="{{ route('login') }}"
            class="flex w-full items-center justify-center
                rounded-xl bg-red-500 px-4 py-3
                text-sm font-semibold text-white
                transition hover:bg-red-600">
            
                Ya, Keluar
        </a>

    </div>
</dialog>

@once
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('logout-confirm-modal');

    if (!modal) return;

    // Tombol Logout dari sidebar atau navbar
    document.addEventListener('click', function (event) {
        const trigger = event.target.closest('[data-logout-open]');

        if (trigger) {
            event.preventDefault();

            if (!modal.open) {
                modal.showModal();
            }
        }
    });

    // Tombol Batal
    modal.querySelector('[data-logout-close]')
        .addEventListener('click', function () {
            modal.close();
        });

    // Klik area gelap di luar popup
    modal.addEventListener('click', function (event) {
        if (event.target !== modal) return;

        const rect = modal.getBoundingClientRect();

        const inside =
            event.clientX >= rect.left &&
            event.clientX <= rect.right &&
            event.clientY >= rect.top &&
            event.clientY <= rect.bottom;

        if (!inside) {
            modal.close();
        }
    });
});
</script>
@endonce
