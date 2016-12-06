<?php

namespace App\Routing;
use App\Presenters\JSONPresenter;
use App\Presenters\PresenterContract;

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
     * @var PresenterContract
     */
    protected $presenter;

    /**
     * Route constructor.
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->config = $options;
        $this->presenter = new JSONPresenter();
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
     * @inheritdoc
     */
    public function isPublic()
    {
        return $this->config['public'] ?? false;
    }

    /**
     * @inheritDoc
     */
    public function getActions()
    {
        return collect($this->actions);
    }

    /**
     * @return PresenterContract
     */
    public function getPresenter()
    {
        return $this->presenter;
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