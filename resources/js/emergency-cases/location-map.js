
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

document.addEventListener('DOMContentLoaded', () => {
    const mapElement = document.getElementById('location-map');
    const form = document.getElementById('emergency-report-form');
    const gpsButton = document.getElementById('use-current-location');
    const statusElement = document.getElementById('location-status');
    const summaryElement = document.getElementById('summary-location');
    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');
    const coordinatesElement = document.getElementById('location-coordinates');

    // Script hanya aktif pada halaman pembuatan laporan.
    if (
        !mapElement || !form || !gpsButton ||
        !statusElement || !summaryElement ||
        !latitudeInput || !longitudeInput ||
        !coordinatesElement
    ) {
        return;
    }

    // Koordinat awal hanya untuk pusat tampilan peta.
    // Bukan koordinat kejadian atau lokasi pengguna.
    const defaultCenter = [-7.2575, 112.7521];

    const map = L.map(mapElement).setView(defaultCenter, 12);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    let marker = null;
    let locationSource = null;
    let gpsRequestId = 0;

    function updateLocation(lat, lng, source) {
        const latitude = Number(lat);
        const longitude = Number(lng);

        if (
            !Number.isFinite(latitude) ||
            !Number.isFinite(longitude) ||
            latitude < -90 || latitude > 90 ||
            longitude < -180 || longitude > 180
        ) {
            showError('Koordinat lokasi tidak valid.');
            return;
        }

        latitudeInput.value = latitude.toFixed(7);
        longitudeInput.value = longitude.toFixed(7);

        locationSource = source;

        if (marker) {
            marker.setLatLng([latitude, longitude]);
        } else {
            marker = L.marker([latitude, longitude]).addTo(map);
        }

        marker.bindPopup('Titik lokasi kejadian').openPopup();
        map.setView([latitude, longitude], 16);

        const message = source === 'gps'
            ? 'Lokasi berhasil terdeteksi melalui GPS'
            : 'Lokasi berhasil dipilih secara manual';

        statusElement.textContent = message;
        summaryElement.textContent =
            source === 'gps' ? 'GPS terdeteksi' : 'Lokasi manual dipilih';

        summaryElement.className =
            'mt-1 text-sm font-medium text-emerald-600';

        coordinatesElement.textContent =
            `${latitude.toFixed(7)}, ${longitude.toFixed(7)}`;
    }

    function showError(message) {
        statusElement.textContent = message;

        if (!locationSource) {
            summaryElement.textContent = 'Belum ditentukan';
            summaryElement.className =
                'mt-1 text-sm font-medium text-amber-600';
        }
    }

    // Pengguna dapat memilih lokasi dengan mengklik peta.
    map.on('click', (event) => {
        // Abaikan respons GPS lama jika pengguna sudah memilih manual.
        gpsRequestId++;

        updateLocation(
            event.latlng.lat,
            event.latlng.lng,
            'manual'
        );
    });

    gpsButton.disabled = false;
    gpsButton.classList.remove(
        'cursor-not-allowed',
        'opacity-60'
    );

    gpsButton.addEventListener('click', () => {
        if (!navigator.geolocation) {
            showError(
                'Browser tidak mendukung GPS. Pilih titik pada peta.'
            );
            return;
        }

        const requestId = ++gpsRequestId;

        statusElement.textContent = 'Sedang mencari lokasi...';
        gpsButton.disabled = true;
        gpsButton.textContent = 'Mencari Lokasi...';

        navigator.geolocation.getCurrentPosition(
            (position) => {
                if (requestId !== gpsRequestId) return;

                updateLocation(
                    position.coords.latitude,
                    position.coords.longitude,
                    'gps'
                );

                gpsButton.disabled = false;
                gpsButton.textContent = 'Gunakan Lokasi Saya';
            },
            (error) => {
                if (requestId !== gpsRequestId) return;

                let message = 'Lokasi gagal ditemukan.';

                if (error.code === 1) {
                    message = 'Izin lokasi ditolak. Pilih titik pada peta.';
                } else if (error.code === 2) {
                    message = 'Lokasi tidak tersedia. Pilih titik pada peta.';
                } else if (error.code === 3) {
                    message = 'Pencarian lokasi terlalu lama. Pilih titik pada peta.';
                }

                showError(message);

                gpsButton.disabled = false;
                gpsButton.textContent = 'Gunakan Lokasi Saya';
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0,
            }
        );
    });

    // Tombol Batal menghapus lokasi beserta marker.
    form.addEventListener('reset', () => {
        gpsRequestId++;

        latitudeInput.value = '';
        longitudeInput.value = '';
        locationSource = null;

        if (marker) {
            map.removeLayer(marker);
            marker = null;
        }

        map.setView(defaultCenter, 12);

        statusElement.textContent = 'Lokasi belum ditentukan';
        summaryElement.textContent = 'Belum ditentukan';
        summaryElement.className =
            'mt-1 text-sm font-medium text-amber-600';

        coordinatesElement.textContent =
            'Klik pada peta untuk menentukan lokasi.';

        gpsButton.disabled = false;
        gpsButton.textContent = 'Gunakan Lokasi Saya';
    });

    // Memastikan ukuran peta dihitung dengan benar.
    requestAnimationFrame(() => map.invalidateSize());
});
