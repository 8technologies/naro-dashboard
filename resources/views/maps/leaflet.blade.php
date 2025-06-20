@props([
    'centerPoint' => ['lat' => 0.3476, 'long' => 32.5825], // Default to Kampala
    'zoomLevel' => 13,
    'markers' => [],
])

{{-- A div for the map to render in --}}
<div id="map" style="height: 60vh; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);"></div>

@push('styles')
    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
@endpush

@push('scripts')
    {{-- Leaflet JavaScript --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize the map
            const map = L.map('map').setView([{{ $centerPoint['lat'] }}, {{ $centerPoint['long'] }}],
                {{ $zoomLevel }});

            // Add a professional-looking tile layer from OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Safely parse the PHP markers array into a JavaScript array
            const markers = @json($markers);

            if (markers && markers.length > 0) {
                // THE FIX IS HERE: Loop through each marker data object
                markers.forEach(markerData => {
                    // Check if marker has valid coordinates
                    if (markerData.lat && markerData.long) {
                        const marker = L.marker([markerData.lat, markerData.long]).addTo(map);

                        // **THIS IS THE CRITICAL LINE**
                        // If a 'popup' key exists in our data, bind its content to the marker.
                        if (markerData.popup) {
                            marker.bindPopup(markerData.popup);
                        }
                    }
                });
            }
        });
    </script>
@endpush
