{{-- We don't need the component anymore. This file handles everything. --}}

{{-- 1. Link to Leaflet's CSS and the new Marker Cluster CSS --}}


{{-- Custom styles to make the popup look nicer and bigger --}}
<style>
    #gardenMap {
        height: 80vh;
        width: 100%;
        border-radius: 15px;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        border: 1px solid #ddd;
    }

    /* Style the popup content */
    .leaflet-popup-content {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
        font-size: 16px;
        /* <-- Bigger and clearer text */
        line-height: 1.6;
        margin: 15px 20px !important;
        text-align: center;
    }

    .leaflet-popup-content strong {
        font-size: 18px;
        font-weight: 600;
        color: #003300;
        /* Matching your app's primary color */
    }

    .leaflet-popup-content-wrapper {
        border-radius: 12px !important;
    }

    /* Style the link to look like a button */
    .popup-link {
        display: inline-block;
        margin-top: 10px;
        padding: 8px 16px;
        background-color: #003300;
        color: white !important;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 500;
        transition: background-color 0.3s ease;
    }

    .popup-link:hover {
        background-color: #056098;
    }
</style>

{{-- 2. The div element for the map --}}
<div id="gardenMap"></div>


{{-- 4. The JavaScript logic with all improvements --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize the map when the DOM is fully loaded
        initMap();
    });

    $(document).on('pjax:complete', function() {
        initMap();
    });

    function initMap() {
        const centerLat = 1.933934;
        const centerLong = 32.405282;
        const zoomLevel = 7;

        const map = L.map('gardenMap').setView([centerLat, centerLong], zoomLevel);

        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>',
            maxZoom: 19
        }).addTo(map);

        const gardenMarkersData = @json($gardens);

        // Professional Touch: Initialize a Marker Cluster Group
        const markers = L.markerClusterGroup();

        // Professional Touch: Define a custom icon for the markers
        const gardenIcon = L.icon({
            iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png', // Default icon, can be replaced
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
            shadowSize: [41, 41]
        });


        if (gardenMarkersData && gardenMarkersData.length > 0) {
            gardenMarkersData.forEach(function(garden) {
                if (garden.lat && garden.long) {

                    const marker = L.marker([garden.lat, garden.long], {
                        icon: gardenIcon
                    });

                    if (garden.popup) {
                        marker.bindPopup(garden.popup, {
                            minWidth: 200 // Set a minimum width for the popup
                        });
                    }

                    // Add the marker to the cluster group instead of directly to the map
                    markers.addLayer(marker);
                }
            });

            // Add the entire cluster group to the map
            map.addLayer(markers);

        } else {
            console.log('No gardens with valid coordinates to display.');
        }
    }
</script>
