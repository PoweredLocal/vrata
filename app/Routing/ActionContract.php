<?php

namespace App\Routing;

/**
 * Interface ActionContract
 * @package App\Routing
 */
interface ActionContract
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

    /**
     * @return string
     */
    public function getFormat();

    /**
     * @return string
     */
    public function getService();

    /**
     * @return bool
     */
    public function isCritical();

    /**
     * @return string
     */
    public function getOutputKey();
}