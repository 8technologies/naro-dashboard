<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Tile Provider
    |--------------------------------------------------------------------------
    | Supported: "openstreetmap", "mapbox"
    */
    'default' => env('MAPS_DEFAULT_PROVIDER', 'openstreetmap'),

    /*
    |--------------------------------------------------------------------------
    | Tile Provider Configurations
    |--------------------------------------------------------------------------
    */
    'providers' => [

        'openstreetmap' => [
            'tile_url'    => env(
                'MAPS_OSM_TILE_URL',
                'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'
            ),
            'attribution' => '© OpenStreetMap contributors',
        ],

        'mapbox' => [
            'tile_url'    => env(
                'MAPS_MAPBOX_TILE_URL',
                'https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token={access_token}'
            ),
            'access_token' => env('MAPS_MAPBOX_ACCESS_TOKEN'),
            'attribution'  => '© Mapbox © OpenStreetMap contributors',
            'default_style_id' => env('MAPS_MAPBOX_STYLE_ID', 'mapbox/streets-v11'),
        ],

    ],

];
