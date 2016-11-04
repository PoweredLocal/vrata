<?php

return (static function() {
    $configTemplate = [
        // List of microservices behind the gateway
        'services' => [
            'core' => [],
            'login' => []
        ],

        // Array of extra (eg. aggregated) routes
        'routes' => [
            [
                'aggregate' => true,
                'method' => 'GET',
                'path' => '/v1/devices/{mac}/details',
                'actions' => [
                    'device' => [
                        'service' => 'core',
                        'method' => 'GET',
                        'path' => 'devices/{mac}',
                        'sequence' => 0,
                        'critical' => true
                    ],
                    'ping' => [
                        'service' => 'core',
                        'output_key' => false,
                        'method' => 'POST',
                        'path' => 'devices/{mac}/ping',
                        'sequence' => 0,
                        'critical' => false
                    ],
                    'settings' => [
                        'service' => 'core',
                        'output_key' => 'network.settings',
                        'method' => 'GET',
                        'path' => 'networks/{device%network_id}',
                        'sequence' => 1,
                        'critical' => false
                    ],
                    'clients' => [
                        'service' => 'login',
                        'output_key' => 'network.clients',
                        'method' => 'GET',
                        'path' => 'visitors/{device%network_id}',
                        'sequence' => 1,
                        'critical' => false
                    ]
                ]
            ]
        ],

        // Global parameters
        'global' => [
            'prefix' => '/v1',
            'timeout' => 5.0,
            'doc_point' => '/api/doc',
            'domain' => 'local.pwred.com'
        ],
    ];

    $sections = ['services', 'routes', 'global'];

    foreach ($sections as $section) {
        $config = env('GATEWAY_' . strtoupper($section), false);
        ${$section} = $config ? json_decode($config, true) : $configTemplate[$section];
        if (${$section} === null) throw new \Exception('Unable to decode GATEWAY_' . strtoupper($section) . ' variable');
    }

    return compact($sections);
})();
