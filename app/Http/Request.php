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
}