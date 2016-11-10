<?php

namespace App\Http\Controllers;

use App\Exceptions\DataFormatException;
use App\Exceptions\NotImplementedException;
use App\Presenters\PresenterContract;
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
            ->sort();

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
        $parametersJar = $request->getRouteParams();

        $output = $this->actions->reduce(function($carry, $batch) use (&$parametersJar, $client) {
            $responses = $client->asyncRequest($batch, $parametersJar);
            $parametersJar = array_merge($parametersJar, $responses->exportParameters());

            return array_merge($carry, $responses->getResponses()->toArray());
        }, []);

        return $this->presenter->format($this->rearrangeKeys($output), 200);
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

            if (empty($key)) {
                return array_merge($carry, $output[$alias]);
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
        return $this->simpleRequest($request, $client);
    }

    /**
     * @param Request $request
     * @param RestClient $client
     * @return Response
     */
    public function post(Request $request, RestClient $client)
    {
        return $this->simpleRequest($request, $client);
    }

    /**
     * @param Request $request
     * @param RestClient $client
     * @return Response
     */
    public function put(Request $request, RestClient $client)
    {
        return $this->simpleRequest($request, $client);
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

        $response = $client->syncRequest($this->actions->first()->first(), $request->getRouteParams());

        return $this->presenter->format((string)$response->getBody(), $response->getStatusCode());
    }
}
