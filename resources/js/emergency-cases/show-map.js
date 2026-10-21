
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

document.addEventListener('DOMContentLoaded', () => {
    const element = document.getElementById('detail-location-map');
    if (!element) return;

    const latitudeText = element.dataset.latitude;
    const longitudeText = element.dataset.longitude;

    const latitude = Number(latitudeText);
    const longitude = Number(longitudeText);

    const hasLocation =
        latitudeText !== undefined &&
        longitudeText !== undefined &&
        latitudeText.trim() !== '' &&
        longitudeText.trim() !== '' &&
        Number.isFinite(latitude) &&
        Number.isFinite(longitude) &&
        latitude >= -90 && latitude <= 90 &&
        longitude >= -180 && longitude <= 180;

    const center = hasLocation
        ? [latitude, longitude]
        : [-7.2575, 112.7521];

    const map = L.map(element, {
        scrollWheelZoom: false,
    }).setView(center, hasLocation ? 16 : 12);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    if (hasLocation) {
        L.circleMarker(center, {
            radius: 9,
            color: '#ffffff',
            weight: 3,
            fillColor: '#ef4444',
            fillOpacity: 1,
        })
        .addTo(map)
        .bindPopup('Lokasi kejadian');
    }

    requestAnimationFrame(() => map.invalidateSize());
});
