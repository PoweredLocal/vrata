<?php

namespace App\Http\Controllers;

use App\Exceptions\DataFormatException;
use App\Exceptions\NotImplementedException;
use App\Routing\EndpointContract;
use GuzzleHttp\Client;
use App\Http\Request;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Response;

class GatewayController extends Controller
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var EndpointContract
     */
    protected $endpoint;

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

        $this->endpoint = $request->getRoute()->getEndpoints()->first();
        $this->client = $client;
    }

    /**
     * @param null $id
     * @param Request $request
     * @return array
     */
    public function get($id = null, Request $request)
    {
        return $id ? $this->show($id, $request) : $this->index($request);
    }

    /**
     * @param $id
     * @param Request $request
     * @return Response
     */
    public function show($id, Request $request)
    {
        try {
            $response = $this->client->get(str_replace('{id}', $id, $this->endpoint->getUrl()), [
                'headers' => [
                    'X-User' => $request->user()->id
                ]
            ]);

            $status = $response->getStatusCode();
            $response = (string) $response->getBody();
        } catch (RequestException $e) {
            $status = 500;
            $response = $e->getResponse() ?? get_class($e);
        }

        return new Response($response, $status, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        try {
            $response = $this->client->get($this->endpoint->getUrl(), [
                'headers' => [
                    'X-User' => $request->user()->id
                ]
            ]);

            $status = $response->getStatusCode();
            $response = (string) $response->getBody();
        } catch (RequestException $e) {
            $status = 500;
            $response = $e->getResponse() ?? get_class($e);
        }

        return new Response($response, $status, [
            'Content-Type' => 'application/json'
        ]);
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
