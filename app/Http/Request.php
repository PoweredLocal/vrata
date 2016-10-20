<?php

namespace App\Http;

use App\Routing\RouteContract;

/**
 * Class Request
 * @package App\Http
 */
class Request extends \Illuminate\Http\Request
{
    /**
     * @var RouteContract
     */
    protected $currentRoute;

    /**
     * @param RouteContract $route
     * @return $this
     */
    public function attachRoute(RouteContract $route)
    {
        $this->currentRoute = $route;

        return $this;
    }

    /**
     * @return RouteContract
     */
    public function getRoute()
    {
        return $this->currentRoute;
    }

    /**
     * Get the route handling the request.
     *
     * @param string|null $param
     *
     * @return \Illuminate\Routing\Route|object|string
     */
    public function route($param = null)
    {
        $route = call_user_func($this->getRouteResolver());

        if (is_null($route) || is_null($param)) {
            return $route;
        } else {
            return $route[2][$param];
        }
    }

    /**
     * @return array
     */
    public function getRouteParams()
    {
        $route = call_user_func($this->getRouteResolver());

        return $route ? $route[2] : [];
    }
}