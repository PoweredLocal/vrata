<?php

namespace App\Routing;

/**
 * Interface EndpointContract
 * @package App\Routing
 */
interface EndpointContract
{
    /**
     * @return string
     */
    public function getUrl();

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl($url);

    /**
     * @return string
     */
    public function getMethod();

    /**
     * @param string $method
     * @return $this
     */
    public function setMethod($method);
}