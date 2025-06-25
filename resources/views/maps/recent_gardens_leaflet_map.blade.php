{{-- resources/views/maps/recent_gardens_leaflet_map.blade.php --}}

{{-- Leaflet & MarkerCluster CSS --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.Default.css" />

<style>
  
    /* Map container */
    #recentGardensMap {
        height: 420px;
        border-radius: 12px;
        margin: 0.5rem;
    }

    /* Popup styling reused from your final version */
    .report-popup {
        font-family: system-ui, sans-serif;
        font-size: 15px;
    }

    .report-popup h3 {
        color: #b03a2e;
        font-size: 18px;
        margin-bottom: 8px;
    }

    .report-popup p {
        margin: 4px 0;
    }

    .report-popup .popup-image img {
        max-width: 100%;
        border-radius: 6px;
        margin: 10px 0;
    }

    .report-popup .popup-actions {
        text-align: center;
        margin-top: 8px;
    }

    .popup-link {
        display: inline-block;
        padding: 6px 12px;
        background: #b03a2e;
        color: #fff !important;
        border-radius: 6px;
        text-decoration: none;
    }

    .popup-link:hover {
        background: #922b21;
    }
</style>

<div class="card mb-3" style="">
    <div class="d-flex justify-content-between p-2 pt-3 px-4 border-bottom ">
        <h4 class="fs-22 fw-800 mb-0">Recent Registered Gardens</h4>
        <a href="{{ admin_url('gardens-map') }}" class="btn btn-link text-primary fw-600 text-decoration-underline">
            View Gardens on map <i class="bi bi-arrow-right"></i>
        </a>
    </div>
    <div class="card-body p-0">
        <div id="recentGardensMap"></div>
    </div>
</div>

{{-- Leaflet & MarkerCluster JS --}}
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster/dist/leaflet.markercluster.js"></script>

<script>
    (function() {
        // Initialize map
        const map = L.map('recentGardensMap').setView([1.9339, 32.4053], 7);

        L.tileLayer(
            'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; OpenStreetMap &copy; CARTO',
                maxZoom: 18
            }
        ).addTo(map);

        // Marker cluster
        const markers = L.markerClusterGroup();
        const data = @json($gardens);

        data.forEach(({
            lat,
            long,
            popup
        }) => {
            const m = L.marker([lat, long]);
            m.bindPopup(popup, {
                minWidth: 200
            });
            markers.addLayer(m);
        });

        map.addLayer(markers);
    })();
</script>
