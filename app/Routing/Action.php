<?php

namespace App\Routing;

/**
 * Class Action
 * @package App\Routing
 */
class Action implements ActionContract
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $alias;

    /**
     * @var string
     */
    protected $format;

    /**
     * @var int
     */
    protected $sequence;

    /**
     * @var bool
     */
    protected $critical;

    /**
     * @var string
     */
    protected $method;

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
        $this->url = $options['url'];
        $this->method = $options['method'];
        $this->sequence = $options['sequence'] ?? 0;
        $this->alias = $options['alias'] ?? null;
        $this->critical = $options['critical'] ?? false;
        $this->format = $options['format'] ?? self::DEFAULT_FORMAT;
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

    /**
     * @return string|null
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @param $alias
     * @return $this
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * @return int
     */
    public function getSequence()
    {
        return $this->sequence;
    }

    /**
     * @param $sequence
     * @return $this
     */
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isCritical()
    {
        return $this->critical;
    }

    /**
     * @param bool $critical
     * @return $this
     */
    public function setCritical($critical)
    {
        $this->critical = $critical;

        return $this;
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