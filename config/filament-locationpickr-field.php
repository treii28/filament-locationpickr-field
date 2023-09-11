<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Plugin Options
    |--------------------------------------------------------------------------
    */
    'key' => env('GMAP_API', ''),

    'default_location' => [
        'lat' => 41.32836109345274,
        'lng' => 19.818383186960773,
    ],

    'default_zoom' => 12,

    'my_location_button' => 'My location'
];
