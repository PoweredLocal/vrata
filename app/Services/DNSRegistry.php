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
        $url = $config['services'][$serviceId]['url'] ?? 'http://' . $serviceId . '.' . $config['global']['domain'];

        return $url;
    }
}