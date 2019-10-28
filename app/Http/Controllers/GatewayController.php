<?php

namespace App\Http\Controllers;

use App\Exceptions\DataFormatException;
use App\Exceptions\NotImplementedException;
use App\Presenters\PresenterContract;
use App\Services\RestClient;
use App\Http\Request;
use Illuminate\Http\Response;
use Laravel\Lumen\Routing\Controller;

class GatewayController extends Controller
{
    /**
     * @var array ActionContract
     */
    protected $actions;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var PresenterContract
     */
    protected $presenter;

    /**
     * GatewayController constructor.
     * @param Request $request
     * @throws DataFormatException
     * @throws NotImplementedException
     */
    public function __construct(Request $request)
    {
        if (empty($request->getRoute())) throw new DataFormatException('Unable to find original URI pattern');

        $this->config = $request
            ->getRoute()
            ->getConfig();

        $this->actions = $request
            ->getRoute()
            ->getActions()
            ->groupBy(function ($action) {
                return $action->getSequence();
            })
            ->sortBy(function ($batch, $key) {
                return intval($key);
            });

        $this->presenter = $request
            ->getRoute()
            ->getPresenter();
    }

    /**
     * @param Request $request
     * @param RestClient $client
     * @return Response
     */
    public function get(Request $request, RestClient $client)
    {
        if (! $request->getRoute()->isAggregate()) return $this->simpleRequest($request, $client);
        
        return $this->aggregateRequest($request, $client);
    }

    /**
     * @param array $output
     * @return array
     */
    private function rearrangeKeys(array $output)
    {
        return collect(array_keys($output))->reduce(function($carry, $alias) use ($output) {
            $key = $this->config['actions'][$alias]['output_key'] ?? $alias;

            if ($key === false) return $carry;

            $data = isset($this->config['actions'][$alias]['input_key']) ? $output[$alias][$this->config['actions'][$alias]['input_key']] : $output[$alias];

            if (empty($key)) {
                return array_merge($carry, $data);
            }

            if (is_string($key)) {
                array_set($carry, $key, $data);
            }

            if (is_array($key)) {
                collect($key)->each(function($outputKey, $property) use (&$data, &$carry, $key) {
                    if ($property == '*') {
                        array_set($carry, $outputKey, $data);
                        return;
                    }

                    if (isset($data[$property])) {
                        array_set($carry, $outputKey, $data[$property]);
                        unset($data[$property]);
                    }
                });
            }

            return $carry;
        }, []);
    }

    /**
     * @param Request $request
     * @param RestClient $client
     * @return Response
     */
    public function delete(Request $request, RestClient $client)
    {
        if (! $request->getRoute()->isAggregate()) return $this->simpleRequest($request, $client);
        
        return $this->aggregateRequest($request, $client);
    }

    /**
     * @param Request $request
     * @param RestClient $client
     * @return Response
     */
    public function post(Request $request, RestClient $client)
    {
        if (! $request->getRoute()->isAggregate()) return $this->simpleRequest($request, $client);
        
        return $this->aggregateRequest($request, $client);
    }

    /**
     * @param Request $request
     * @param RestClient $client
     * @return Response
     */
    public function put(Request $request, RestClient $client)
    {
        if (! $request->getRoute()->isAggregate()) return $this->simpleRequest($request, $client);
        
        return $this->aggregateRequest($request, $client);
    }

    /**
     * @param Request $request
     * @param RestClient $client
     * @return Response
     * @throws NotImplementedException
     */
    private function simpleRequest(Request $request, RestClient $client)
    {
        if ($request->getRoute()->isAggregate()) throw new NotImplementedException('Aggregate ' . strtoupper($request->method()) . 's are not implemented yet');

        $client->setBody($request->getContent());
  
        if (count($request->allFiles()) !== 0) {
            $client->setFiles($request->allFiles());
        }

        $parametersJar = array_merge($request->getRouteParams(), ['query_string' => $request->getQueryString()]);

        $response = $client->syncRequest($this->actions->first()->first(), $parametersJar);

        return $this->presenter->format((string)$response->getBody(), $response->getStatusCode());
    }

    /**
     * @param Request $request
     * @param RestClient $client
     * @return Response
     */
    private function aggregateRequest(Request $request, RestClient $client) {

        // Aggregate request
        $parametersJar = array_merge($request->getRouteParams(), ['query_string' => $request->getQueryString()]);
    
        // Initial Body
        if ($request->getContent() != "") {
            $parametersJar = array_merge($parametersJar, $client->setAggregateOriginBody($request->getContent()));
        }

        $output = $this->actions->reduce(function($carry, $batch) use (&$parametersJar, $client) {
            $responses = $client->asyncRequest($batch, $parametersJar);
           
            $parametersJar = array_merge($parametersJar, $responses->exportParameters());

            return array_merge($carry, $responses->getResponses()->toArray());
        }, []);

        return $this->presenter->format($this->rearrangeKeys($output), 200);
    }
}
