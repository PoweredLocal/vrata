<?php

namespace App\Http\Controllers;

use App\Exceptions\DataFormatException;
use App\Exceptions\NotImplementedExceptions;
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
     * @throws DataFormatException
     * @throws NotImplementedExceptions
     */
    public function __construct(Request $request)
    {
        if (empty($request->getRoute())) throw new DataFormatException('Unable to find original URI pattern');
        if ($request->getRoute()->isAggregate()) throw new NotImplementedExceptions('Aggregate routes are not supported yet');

        $this->endpoint = $request->getRoute()->getEndpoints()->first();
        $this->client = new Client([
            'timeout'  => Config::get('gateway.global.timeout'),
        ]);
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
        } catch (RequestException $e) {
            $response = $e->getResponse();
        }

        return new Response((string)$response->getBody(), $response->getStatusCode());
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
        } catch (RequestException $e) {
            $response = $e->getResponse();
        }

        return new Response((string)$response->getBody(), $response->getStatusCode());
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
