<?php

return [

    'local' => [
        'api'        => env('API_LOCAL', 'http://127.0.0.1:8000'),
        'ssl'        => env('API_SSL_LOCAL', false),
    ],

    'hosting' => [
        'api'        => env('API_HOSTING', 'https://roomioflex.io:8000'),
        'ssl'        => env('API_SSL_HOSTING', true),
    ],

];
