<?php

namespace App\Http\Controllers;

use App\Exceptions\DataFormatException;
use App\Exceptions\NotImplementedException;
use App\Routing\EndpointContract;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use App\Http\Request;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

class GatewayController extends Controller
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var array EndpointContract
     */
    protected $endpoints;

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

        $this->endpoints = $request
            ->getRoute()
            ->getEndpoints()
            ->groupBy(function ($endpoint) {
                return $endpoint->getSequence();
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
        $output = [];

        $this->endpoints->each(function($batch, $sequence) use ($request, $output) {
            $promises = $batch->reduce(function($carry, $endpoint) use ($request) {
                $url = $this->injectParams($endpoint->getUrl(), $request->getRouteParams());

                $carry[$endpoint->getAlias()] = $this->client->getAsync($url, [
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
                    if ($decoded !== null) $output = array_merge_recursive($output, $decoded);
                }
            }
        });

        // When all is done, generate final output based on response array
/*        try {
            $status = $response->getStatusCode();
            $response = (string) $response->getBody();
        } catch (RequestException $e) {
            $status = 500;
            $response = $e->getResponse() ?? get_class($e);
        }*/

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
     * @param $id
     * @param Request $request
     */
    public function delete($id, Request $request)
    {

    }

    /**
     * @param Request $request
     */
    public function post(Request $request)
    {

    }

    /**
     * @param $id
     * @param Request $request
     */
    public function put($id, Request $request)
    {

    }
}
