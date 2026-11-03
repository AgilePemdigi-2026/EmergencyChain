import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

document.addEventListener('DOMContentLoaded', () => {
    const element = document.getElementById(
        'assignment-locations-map'
    );

    if (!element) return;

    let locations = [];

    try {
        locations = JSON.parse(
            element.dataset.locations || '[]'
        );
    } catch (error) {
        console.error('Data lokasi tidak valid:', error);
    }

    const map = L.map(element, {
        scrollWheelZoom: false,
    }).setView([-7.2575, 112.7521], 12);

    L.tileLayer(
        'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
        {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors',
        }
    ).addTo(map);

    const markers = [];

    locations.forEach((location) => {
        const latitude = Number(location.latitude);
        const longitude = Number(location.longitude);

        if (
            !Number.isFinite(latitude) ||
            !Number.isFinite(longitude) ||
            latitude < -90 ||
            latitude > 90 ||
            longitude < -180 ||
            longitude > 180
        ) {
            return;
        }

        const marker = L.circleMarker(
            [latitude, longitude],
            {
                radius: 9,
                color: '#ffffff',
                weight: 3,
                fillColor: '#ef4444',
                fillOpacity: 1,
            }
        ).addTo(map);

        const popup = document.createElement('div');

        const title = document.createElement('strong');
        title.textContent =
            `Assignment #${location.assignment_id}`;

        const caseInfo = document.createElement('p');
        caseInfo.textContent =
            `Emergency Case #${location.case_id}`;

        const incident = document.createElement('p');
        incident.textContent =
            location.jenis_kejadian || '-';

        const address = document.createElement('p');
        address.textContent =
            location.alamat || 'Alamat tidak tersedia';

        popup.append(
            title,
            caseInfo,
            incident,
            address
        );

        marker.bindPopup(popup);

        markers.push(marker);
    });

    if (markers.length === 1) {
        map.setView(
            markers[0].getLatLng(),
            15
        );
    } else if (markers.length > 1) {
        const group = L.featureGroup(markers);

        map.fitBounds(
            group.getBounds(),
            {
                padding: [30, 30],
                maxZoom: 15,
            }
        );
    }

    requestAnimationFrame(() => {
        map.invalidateSize();
    });
});