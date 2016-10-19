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
    protected $alias;

    /**
     * @var int
     */
    protected $sequence;

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
        $this->sequence = $options['sequence'] ?? 0;
        $this->alias = $options['alias'] ?? null;
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
}