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
    protected $actions = [];

    /**
     * @var string
     */
    protected $format;

    /**
     * @const string
     */
    const DEFAULT_FORMAT = 'json';

    /**
     * Route constructor.
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->id = $options['id'];
        $this->method = $options['method'];
        $this->path = $options['path'];
        $this->format = $options['format'] ?? self::DEFAULT_FORMAT;
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
        return $this->format;
    }

    /**
     * @param string $format
     * @return $this
     */
    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
    }
}