<?php

return [
    'services' => [
        'core' => [

        ],

        'login' => [

        ]
    ],

    'global' => [
        'prefix' => 'v1',
        'timeout' => 1000
    ],

    'defaults' => [
        'doc_point' => '/api/doc',
        'domain' => env('DOMAIN', 'local.pwred.com')
    ]
];