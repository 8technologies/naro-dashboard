<div class="card mb-3" style="">


    <div class="d-flex justify-content-between p-2 pt-3 px-4 border-bottom ">
        <h4 class="fs-22 fw-800 mb-0">Recent Pest Reports Map</h4>
        <a href="{{ admin_url('pests-map') }}" class="btn btn-link text-primary fw-600 text-decoration-underline">
            View on map <i class="bi bi-arrow-right"></i>
        </a>
    </div>
    <div class="card-body p-4">
        <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
        <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.css" />
        <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.Default.css" />

        <style>
            #recentPestsMap {
                height: 400px;
                margin-bottom: 1rem;
            }

            .report-popup h3 {
                color: #b03a2e;
            }
        </style>

        <div id="recentPestsMap"></div>

        <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
        <script src="https://unpkg.com/leaflet.markercluster/dist/leaflet.markercluster.js"></script>
        <script>
            (function() {
                const map = L.map('recentPestsMap').setView([1.9339, 32.4053], 7);
                L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                    attribution: '&copy; OSM &copy; CARTO',
                    maxZoom: 18
                }).addTo(map);
                const markers = L.markerClusterGroup();
                const data = @json($markers);

                data.forEach(({
                    lat,
                    long,
                    popup
                }) => {
                    const m = L.marker([lat, long]);
                    m.bindPopup(popup, {
                        minWidth: 180
                    });
                    markers.addLayer(m);
                });

                map.addLayer(markers);
            })();
        </script>
    </div>
</div>
