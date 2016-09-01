<?php

namespace App\Routing;

/**
 * Class Endpoint
 * @package App\Routing
 */
class Endpoint implements EndpointContract
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $method;

    /**
     * Endpoint constructor.
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->url = $options['url'];
        $this->method = $options['method'];
    }

    /**
     * @inheritDoc
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @inheritDoc
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @inheritDoc
     */
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }
}