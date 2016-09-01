<?php

namespace App\Routing;

/**
 * Class Route
 * @package App\Routing
 */
class Route implements RouteContract
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var array
     */
    protected $endpoints = [];

    /**
     * Route constructor.
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->id = $options['id'];
        $this->method = $options['method'];
        $this->path = $options['path'];
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->id;
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
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @inheritDoc
     */
    public function isAggregate()
    {
        return count($this->endpoints) > 1;
    }

    /**
     * @inheritDoc
     */
    public function addEndpoint(EndpointContract $endpoint)
    {
        $this->endpoints[] = $endpoint;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getEndpoints()
    {
        return collect($this->endpoints);
    }
}