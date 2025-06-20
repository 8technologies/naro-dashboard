{{-- We are using a center point in Uganda and passing the gardens data --}}
<x-maps-leaflet :centerPoint="['lat' => 1.933934, 'long' => 32.405282]" :zoomLevel="7" :markers="$gardens" />