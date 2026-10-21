
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('#emergency-report-form');

    if (!form) return;

    const jenis = document.querySelector('#jenis_kejadian');
    const kebutuhan = document.querySelector('#tingkat_kebutuhan');
    const kondisi = document.querySelector('#kondisi_kejadian');
    const patokan = document.querySelector('#patokan_lokasi');

    const latitude = document.querySelector('#latitude');
    const longitude = document.querySelector('#longitude');

    const summaryJenis = document.querySelector('#summary-jenis');
    const summaryKebutuhan = document.querySelector('#summary-kebutuhan');

    const characterCount = document.querySelector('#character-count');
    const patokanCount = document.querySelector('#patokan-count');
    const message = document.querySelector('#report-form-message');

    function updateSummary() {
        summaryJenis.textContent = jenis.value || 'Belum dipilih';
        summaryKebutuhan.textContent =
            kebutuhan.value || 'Belum dipilih';

        characterCount.textContent = `${kondisi.value.length}/500`;

        if (patokan && patokanCount) {
            patokanCount.textContent = `${patokan.value.length}/300`;
        }
    }

    function showMessage(text, isValid = false) {
        message.textContent = text;
        message.className = isValid
            ? 'mt-3 text-sm text-emerald-700'
            : 'mt-3 text-sm text-red-600';
    }

    function validateReport() {
        const requiredFields = [
            { field: jenis, label: 'Jenis Kejadian' },
            { field: kebutuhan, label: 'Tingkat Kebutuhan' },
            { field: kondisi, label: 'Kondisi Kejadian' },
        ];

        for (const item of requiredFields) {
            if (!item.field.value.trim()) {
                showMessage(`${item.label} wajib diisi.`);
                item.field.focus();
                return false;
            }
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
    patokan?.addEventListener('input', updateSummary);

    form.addEventListener('submit', (event) => {
        event.preventDefault();

        if (!validateReport()) return;

        showMessage(
            'Data laporan sudah lengkap dan siap diintegrasikan dengan backend.',
            true
        );
    });

    form.addEventListener('reset', () => {
        requestAnimationFrame(() => {
            updateSummary();
            message.textContent = '';
        });
    });

    updateSummary();
});
