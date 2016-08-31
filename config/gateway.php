<?php

return [
    'services' => [
        'core' => [

        ],

        'login' => [

        ]
    ],

    'global' => [
        'prefix' => 'v1'
    ],

    'defaults' => [
        'doc_point' => '/api/doc',
        'domain' => env('DOMAIN', 'local.pwred.com')
    ]
];