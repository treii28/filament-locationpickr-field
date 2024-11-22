<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Plugin Options
    |--------------------------------------------------------------------------
    */
    'key' => env('GMAP_API', ''),

    'default_location' => [
        'lat' => 45.158,
        'lng' => -84.245,
    ],

    'default_zoom' => 12.8,

    'default_draggable' => true,

    'default_clickable' => true,

    'default_height' => '500px',

    'my_location_button' => 'CCR',
];
