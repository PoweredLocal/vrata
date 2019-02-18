<?php

namespace App\Services;
use App\Presenters\JSONPresenter;
use GuzzleHttp\Psr7\Response;

/**
 * Class RestResponse
 * @package App\Services
 */
class RestBatchResponse
{
    /**
     * @var array
     */
    protected $responses = [];

    /**
     * @var array
     */
    protected $codes = [];

    /**
     * @var int
     */
    protected $failures = 0;

    /**
     * @var bool
     */
    protected $hasCritical = false;

    /**
     * @param string $alias
     * @param Response $response
     */
    public function addSuccessfulAction($alias, Response $response)
    {
        $this->addAction($alias, (string)$response->getBody(), $response->getStatusCode());
    }

    /**
     * @param string $alias
     * @param Response $response
     */
    public function addFailedAction($alias, Response $response)
    {
        $this->addAction($alias, (string)$response->getBody(), $response->getStatusCode());
        $this->failures++;
    }

    /**
     * @param string $alias
     * @param $content
     * @param $code
     */
    private function addAction($alias, $content, $code)
    {
        $this->responses[$alias] = $content;
        $this->codes[$alias] = $code;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getResponses()
    {
        return collect($this->responses)->map(function ($response) {
            return JSONPresenter::safeDecode($response);
        });
    }

    /**
     * @return array
     */
    public function exportParameters()
    {
        return collect(array_keys($this->responses))->reduce(function ($carry, $alias) {
            $output = [];
            $decoded = json_decode($this->responses[$alias], true);
            if ($decoded === null) return $carry;

            foreach ($decoded as $key => $value) {
                $output[$alias . '%' . $key] = $value;
            }

            return array_merge($carry, $output);
        }, []);
    }

    /**
     * @return bool
     */
    public function hasFailedRequests()
    {
        return $this->failures > 0;
    }

    /**
     * @param bool $critical
     * @return $this
     */
    public function setCritical($critical)
    {
        $this->hasCritical = $critical;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasCriticalActions()
    {
        return $this->hasCritical;
    }
}