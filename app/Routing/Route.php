<?php

namespace App\Routing;

/**
 * Class Route
 * @package App\Routing
 */
class Route implements RouteContract
{
    /**
     * @var array
     */
    protected $actions = [];

    /**
     * @const string
     */
    const DEFAULT_FORMAT = 'json';

    /**
     * @var array
     */
    protected $config;

    /**
     * Route constructor.
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->config = $options;
    }

    /**
     * @return string
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->config['id'];
    }

    /**
     * @inheritDoc
     */
    public function getMethod()
    {
        return $this->config['method'];
    }

    /**
     * @inheritDoc
     */
    public function getPath()
    {
        return $this->config['path'];
    }

    /**
     * @inheritDoc
     */
    public function isAggregate()
    {
        return count($this->actions) > 1;
    }

    /**
     * @inheritDoc
     */
    public function addAction(ActionContract $action)
    {
        $this->actions[] = $action;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getActions()
    {
        return collect($this->actions);
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->config['format'] ?? self::DEFAULT_FORMAT;
    }

    /**
     * @param string $format
     * @return $this
     */
    public function setFormat($format)
    {
        $this->config['format'] = $format;

        return $this;
    }
}