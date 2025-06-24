{{-- resources/views/maps/service_providers_leaflet_map.blade.php --}}

<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.Default.css"/>

<style>
  #spMap {
    height: 80vh;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    margin-bottom: 20px;
  }
  .sp-popup {
    font-family: system-ui, sans-serif;
    font-size: 15px;
  }
  .sp-popup h3 {
    color: #2a7f62;
    font-size: 18px;
    margin-bottom: 8px;
  }
  .sp-popup p {
    margin: 4px 0;
  }
  .sp-popup .popup-image img {
    max-width: 100%;
    border-radius: 6px;
    margin: 10px 0;
  }
  .sp-popup .popup-actions {
    text-align: center;
    margin-top: 8px;
  }
  .popup-link {
    display: inline-block;
    padding: 6px 12px;
    background: #2a7f62;
    color: #fff !important;
    border-radius: 6px;
    text-decoration: none;
  }
  .popup-link:hover {
    background: #1e5c44;
  }
</style>

<div id="spMap"></div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster/dist/leaflet.markercluster.js"></script>

<script>
  (function() {
    const map = L.map('spMap').setView([1.9339, 32.4053], 7);

    L.tileLayer(
      'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png',
      { attribution: '&copy; OSM &copy; CARTO', maxZoom: 18 }
    ).addTo(map);

    const markers = L.markerClusterGroup();
    const data    = @json($markers);

    data.forEach(({lat, long, popup}) => {
      const m = L.marker([lat, long]);
      m.bindPopup(popup, { minWidth: 200 });
      markers.addLayer(m);
    });

    map.addLayer(markers);
  })();
</script>