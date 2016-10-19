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

    /**
     * @return string
     */
    public function getAlias();

    /**
     * @param string $alias
     * @return $this
     */
    public function setAlias($alias);

    /**
     * @return int
     */
    public function getSequence();

    /**
     * @param int $sequence
     * @return $this
     */
    public function setSequence($sequence);
}