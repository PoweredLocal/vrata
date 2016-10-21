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
     * GatewayController constructor.
     * @param Request $request
     * @throws DataFormatException
     * @throws NotImplementedException
     */
    public function __construct(Request $request)
    {
        if (empty($request->getRoute())) throw new DataFormatException('Unable to find original URI pattern');

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

            return array_merge($carry, $responses->getResponses()->flatten(1)->toArray());
        }, []);

        return new Response(json_encode($output), 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @param Request $request
     * @param RestClient $client
     * @return Response
     */
    public function delete(Request $request, RestClient $client)
    {
        return $this->singleCommand('delete', $request, $client);
    }

    /**
     * @param Request $request
     * @param RestClient $client
     * @return Response
     */
    public function post(Request $request, RestClient $client)
    {
        return $this->singleCommand('post', $request, $client);
    }

    /**
     * @param Request $request
     * @param RestClient $client
     * @return Response
     */
    public function put(Request $request, RestClient $client)
    {
        return $this->singleCommand('put', $request, $client);
    }

    /**
     * @param $verb
     * @param Request $request
     * @param RestClient $client
     * @return Response
     * @throws NotImplementedException
     */
    private function singleCommand($verb, Request $request, RestClient $client)
    {
        if ($request->getRoute()->isAggregate()) throw new NotImplementedException('Aggregate ' . strtoupper($verb) . 's are not implemented yet');

        $client->setBody($request->getContent());
        $response = $client->{$verb}($this->actions->first()->getUrl());

        return new Response((string)$response->getBody(), $response->getStatusCode());
    }
}
