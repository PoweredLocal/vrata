<?php

namespace App\Http\Controllers;

use App\Exceptions\DataFormatException;
use App\Exceptions\NotImplementedException;
use App\Routing\ActionContract;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use App\Http\Request;
use Illuminate\Http\Response;

class GatewayController extends Controller
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var array ActionContract
     */
    protected $actions;

    /**
     * GatewayController constructor.
     * @param Request $request
     * @param Client $client
     * @throws DataFormatException
     * @throws NotImplementedException
     */
    public function __construct(Request $request, Client $client)
    {
        if (empty($request->getRoute())) throw new DataFormatException('Unable to find original URI pattern');

        $this->actions = $request
            ->getRoute()
            ->getActions()
            ->groupBy(function ($action) {
                return $action->getSequence();
            })
            ->sort();

        $this->client = $client;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function get(Request $request)
    {
        $output = $this->actions->reduce(function($carry, $batch) use ($request) {
            $promises = $batch->reduce(function($carry, $action) use ($request) {
                $url = $this->injectParams($action->getUrl(), $request->getRouteParams());

                $carry[$action->getAlias()] = $this->client->getAsync($url, [
                    'headers' => [
                        'X-User' => $request->user()->id
                    ]
                ]);

                return $carry;
            }, []);

            $responses = Promise\settle($promises)->wait();

            foreach ($responses as $key => $response) {
                if ($response['state'] == 'fulfilled') {
                    $decoded = json_decode((string)$response['value']->getBody(), true);
                    if ($decoded !== null) $carry = array_merge_recursive($decoded, $carry);
                } else {
                    // Get the error
                }
            }

            return $carry;
        }, []);

        return new Response(json_encode($output), 200, [
            'Content-Type' => 'application/json'
        ]);
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
     * @param Request $request
     * @throws NotImplementedException
     */
    public function delete(Request $request)
    {
        if ($request->getRoute()->isAggregate()) throw new NotImplementedException('Aggregate DELETEs are not implemented yet');
    }

    /**
     * @param Request $request
     * @throws NotImplementedException
     */
    public function post(Request $request)
    {
        if ($request->getRoute()->isAggregate()) throw new NotImplementedException('Aggregate POSTs are not implemented yet');
    }

    /**
     * @param Request $request
     * @throws NotImplementedException
     */
    public function put(Request $request)
    {
        if ($request->getRoute()->isAggregate()) throw new NotImplementedException('Aggregate PUTs are not implemented yet');
    }
}
