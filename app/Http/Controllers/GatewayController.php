<?php

namespace App\Http\Controllers;

use App\Exceptions\DataFormatException;
use App\Exceptions\NotImplementedException;
use App\Services\RestClient;
use App\Http\Request;
use Illuminate\Http\Response;

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
            ->sort();
    }

    /**
     * @param Request $request
     * @param RestClient $client
     * @return Response
     */
    public function get(Request $request, RestClient $client)
    {
        $client->setHeaders(['X-User' => $request->user()->id]);
        $parametersJar = $request->getRouteParams();

        $output = $this->actions->reduce(function($carry, $batch) use (&$parametersJar, $client) {
            $responses = $client->asyncGet($batch, $parametersJar);
            $parametersJar = array_merge($parametersJar, $responses->exportParameters());

            return array_merge($carry, $responses->getResponses()->toArray());
        }, []);

        return new Response(json_encode($this->rearrangeKeys($output)), 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @param array $output
     * @return array
     */
    private function rearrangeKeys(array $output)
    {
        return collect(array_keys($output))->reduce(function($carry, $alias) use ($output) {
            $key = $this->config['actions'][$alias]['output_key'] ?? $alias;

            if (! $key) {
                return array_merge($carry, $output);
            }

            array_set($carry, $key, $output[$alias]);

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
        return $this->simpleRequest('delete', $request, $client);
    }

    /**
     * @param Request $request
     * @param RestClient $client
     * @return Response
     */
    public function post(Request $request, RestClient $client)
    {
        return $this->simpleRequest('post', $request, $client);
    }

    /**
     * @param Request $request
     * @param RestClient $client
     * @return Response
     */
    public function put(Request $request, RestClient $client)
    {
        return $this->simpleRequest('put', $request, $client);
    }

    /**
     * @param $verb
     * @param Request $request
     * @param RestClient $client
     * @return Response
     * @throws NotImplementedException
     */
    private function simpleRequest($verb, Request $request, RestClient $client)
    {
        if ($request->getRoute()->isAggregate()) throw new NotImplementedException('Aggregate ' . strtoupper($verb) . 's are not implemented yet');

        $client->setHeaders(['X-User' => $request->user()->id]);
        $client->setBody($request->getContent());

        $response = $client->{$verb}($this->actions->first()->getUrl());

        return new Response((string)$response->getBody(), $response->getStatusCode());
    }
}
