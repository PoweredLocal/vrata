<?php

namespace App\Services;

use App\Exceptions\UnableToExecuteRequestException;
use App\Routing\ActionContract;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise;
use Illuminate\Support\Collection;
use App\Http\Request;
use GuzzleHttp\Psr7\Response as PsrResponse;

/**
 * Class RestClient
 * @package App\Services
 */
class RestClient
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var ServiceRegistryContract
     */
    protected $services;

    /**
     * @var array
     */
    protected $guzzleParams = [
        'headers' => []
    ];

    /**
     * RestClient constructor.
     * @param Client $client
     * @param ServiceRegistryContract $services
     * @param Request $request
     */
    public function __construct(Client $client, ServiceRegistryContract $services, Request $request)
    {
        $this->client = $client;
        $this->services = $services;
        $this->injectHeaders($request);
    }

    /**
     * @param Request $request
     */
    private function injectHeaders(Request $request)
    {
        $this->setHeaders(
            [
                'X-User' => $request->user()->id,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]
        );
    }

    /**
     * @param array $headers
     */
    public function setHeaders(array $headers)
    {
        $this->guzzleParams['headers'] = $headers;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->guzzleParams['headers'];
    }

    /**
     * @param string $body
     */
    public function setBody($body)
    {
        $this->guzzleParams['body'] = $body;
    }

    /**
     * @param $url
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function post($url)
    {
        return $this->client->post($url, $this->guzzleParams);
    }

    /**
     * @param $url
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function put($url)
    {
        return $this->client->put($url, $this->guzzleParams);
    }

    /**
     * @param $url
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function delete($url)
    {
        return $this->client->delete($url, $this->guzzleParams);
    }

    /**
     * @param Collection $batch
     * @param $parametersJar
     * @return RestBatchResponse
     */
    public function asyncGet(Collection $batch, $parametersJar)
    {
        $wrapper = new RestBatchResponse();
        $wrapper->setCritical($batch->filter(function($action) { return $action->isCritical(); })->count());

        $promises = $batch->reduce(function($carry, $action) use ($parametersJar) {
            $url = $this->buildUrl($action, $parametersJar);
            $carry[$action->getAlias()] = $this->client->getAsync($url, $this->guzzleParams);
            return $carry;
        }, []);

        $responses = collect(Promise\settle($promises)->wait());

        // Process successful responses
        $responses->filter(function ($response) {
            return $response['state'] == 'fulfilled';
        })->each(function ($response, $alias) use ($wrapper) {
            $wrapper->addSuccessfulAction($alias, $response['value']);
        });

        // Process failures
        $responses->filter(function ($response) {
            return $response['state'] != 'fulfilled';
        })->each(function ($response, $alias) use ($wrapper) {
            $response = $response['reason']->getResponse();
            if ($wrapper->hasCriticalActions()) throw new UnableToExecuteRequestException($response);

            // Do we have an error response from the service?
            if (! $response) $response = new PsrResponse(502, []);
            $wrapper->addFailedAction($alias, $response);
        });

        return $wrapper;
    }

    /**
     * @param ActionContract $action
     * @param array $parametersJar
     * @return PsrResponse
     * @throws UnableToExecuteRequestException
     */
    public function syncRequest(ActionContract $action, $parametersJar)
    {
        try {
            $response = $this->{strtolower($action->getMethod())}(
                $this->buildUrl($action, $parametersJar)
            );
        } catch (ConnectException $e) {
            throw new UnableToExecuteRequestException();
        } catch (RequestException $e) {
            return new PsrResponse((string)$e->getResponse()->getBody(), $e->getResponse()->getStatusCode());
        }

        return $response;
    }

    /**
     * @param string $url
     * @param array $params
     * @return string
     */
    private function injectParams($url, array $params)
    {
        foreach ($params as $key => $value) {
            $url = str_replace("{" . $key . "}", $value, $url);
        }

        return $url;
    }

    /**
     * @param ActionContract $action
     * @param $parametersJar
     * @return string
     */
    private function buildUrl(ActionContract $action, $parametersJar)
    {
        $url = $this->injectParams($action->getUrl(), $parametersJar);
        if ($url[0] != '/') $url = '/' . $url;

        return $this->services->resolveInstance($action->getService()) . $url;
    }
}