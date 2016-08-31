<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GatewayController extends Controller
{
    /**
     * @param null $id
     * @param Request $request
     * @return array
     */
    public function get($id = null, Request $request)
    {
        return [
            'code' => 200,
            'id' => $id,
            'request' => $request->getRequestUri()
        ];
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
