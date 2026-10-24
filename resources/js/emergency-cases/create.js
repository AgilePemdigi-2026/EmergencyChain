
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('#emergency-report-form');

    if (!form) return;

    const jenis = document.querySelector('#jenis_kejadian');
    const kebutuhan = document.querySelector('#tingkat_kebutuhan');
    const kondisi = document.querySelector('#kondisi_kejadian');
    const alamat = document.querySelector('#alamat');

    const latitude = document.querySelector('#latitude');
    const longitude = document.querySelector('#longitude');

    const summaryJenis = document.querySelector('#summary-jenis');
    const summaryKebutuhan = document.querySelector('#summary-kebutuhan');
    const characterCount = document.querySelector('#character-count');
    const message = document.querySelector('#report-form-message');
    const submitButton = document.querySelector('#submit-report');

    function updateSummary() {
        summaryJenis.textContent = jenis.value || 'Belum dipilih';
        summaryKebutuhan.textContent =
            kebutuhan.value || 'Belum dipilih';

        characterCount.textContent = `${kondisi.value.length}/500`;
    }

    function showMessage(text) {
        message.textContent = text;
        message.className = 'mt-3 text-sm text-red-600';
    }

    function validateReport() {
        const requiredFields = [
            { field: jenis, label: 'Jenis Kejadian' },
            { field: kebutuhan, label: 'Tingkat Kebutuhan' },
            { field: kondisi, label: 'Kondisi Kejadian' },
            { field: alamat, label: 'Alamat Kejadian' },
        ];

        for (const item of requiredFields) {
            if (!item.field.value.trim()) {
                showMessage(`${item.label} wajib diisi.`);
                item.field.focus();
                return false;
            }
        }

        if (alamat.value.length > 100) {
            showMessage('Alamat maksimal 100 karakter.');
            alamat.focus();
            return false;
        }

        const lat = Number(latitude.value);
        const lng = Number(longitude.value);

        if (
            latitude.value.trim() === '' ||
            longitude.value.trim() === '' ||
            !Number.isFinite(lat) ||
            !Number.isFinite(lng) ||
            lat < -90 || lat > 90 ||
            lng < -180 || lng > 180
        ) {
            showMessage(
                'Tentukan lokasi kejadian melalui GPS atau pilih titik pada peta.'
            );

            document.querySelector('#location-map')?.scrollIntoView({
                behavior: 'smooth',
                block: 'center',
            });

            return false;
        }

        return true;
    }

    jenis.addEventListener('change', updateSummary);
    kebutuhan.addEventListener('change', updateSummary);
    kondisi.addEventListener('input', updateSummary);

    form.addEventListener('submit', (event) => {
        if (!validateReport()) {
            event.preventDefault();
            return;
        }

        // Submit valid diteruskan ke backend Laravel.
        submitButton.disabled = true;
        submitButton.textContent = 'Mengirim Laporan...';
    });

    form.addEventListener('reset', () => {
        requestAnimationFrame(() => {
            updateSummary();
            message.textContent = '';
            submitButton.disabled = false;
            submitButton.textContent = 'Kirim Laporan';
        });
    });

    updateSummary();
});
