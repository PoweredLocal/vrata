<?php

namespace App\Routing;

/**
 * Class Action
 * @package App\Routing
 */
class Action implements ActionContract
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @const string
     */
    const DEFAULT_FORMAT = 'json';

    /**
     * Endpoint constructor.
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->config = $options;
    }

    /**
     * @inheritDoc
     */
    public function getUrl()
    {
        return $this->config['path'];
    }

    /**
     * @inheritDoc
     */
    public function setUrl($url)
    {
        $this->config['path'] = $url;

        return $this;
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
    public function setMethod($method)
    {
        $this->config['method'] = $method;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAlias()
    {
        return $this->config['alias'] ?? null;
    }

    /**
     * @param $alias
     * @return $this
     */
    public function setAlias($alias)
    {
        $this->config['alias'] = $alias;

        return $this;
    }

    /**
     * @return string
     */
    public function getService()
    {
        return $this->config['service'];
    }

    /**
     * @param string $service
     * @return $this
     */
    public function setService($service)
    {
        $this->config['service'] = $service;

        return $this;
    }

    /**
     * @return int
     */
    public function getSequence()
    {
        return $this->config['sequence'] ?? 0;
    }

    /**
     * @param $sequence
     * @return $this
     */
    public function setSequence($sequence)
    {
        $this->config['sequence'] = $sequence;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isCritical()
    {
        return $this->config['critical'] ?? false;
    }

    /**
     * @param bool $critical
     * @return $this
     */
    public function setCritical($critical)
    {
        $this->config['critical'] = $critical;

        return $this;
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

    /**
     * @return string
     */
    public function getOutputKey()
    {
        return $this->config['output_key'] ?? null;
    }
}