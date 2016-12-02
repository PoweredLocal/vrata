<?php

namespace App\Services;

/**
 * Class DNSRegistry
 * @package App\Services
 */
class DNSRegistry implements ServiceRegistryContract
{
    /**
     * @param string $serviceId
     * @return string
     */
    public function resolveInstance($serviceId)
    {
        $config = config('gateway');

        // If service doesn't have a specific URL, simply append global domain to service name
        $hostname = $config['services'][$serviceId]['hostname'] ?? $serviceId . '.' . $config['global']['domain'];

        return 'http://' .  $hostname;
    }
}