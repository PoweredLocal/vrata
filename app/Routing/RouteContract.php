<?php

namespace App\Routing;
use Illuminate\Support\Collection;

/**
 * Interface RouteContract
 * @package App\Routing
 */
interface RouteContract
{
    /**
     * @return string
     */
    public function getId();

    /**
     * @return string
     */
    public function getMethod();

    /**
     * @return string
     */
    public function getPath();

    /**
     * @return bool
     */
    public function isAggregate();

    /**
     * @return Collection
     */
    public function getEndpoints();

    /**
     * @param EndpointContract $endpoint
     * @return $this
     */
    public function addEndpoint(EndpointContract $endpoint);
}