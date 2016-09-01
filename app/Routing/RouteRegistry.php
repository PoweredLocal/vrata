<?php

namespace App\Routing;

use Illuminate\Support\Facades\Storage;

/**
 * Class RouteRegistry
 * @package App\Routing
 */
class RouteRegistry
{
    /**
     * @var array
     */
    protected $routes = [];

    /**
     * @param RouteContract $route
     */
    public function addRoute(RouteContract $route)
    {
        $this->routes[] = $route;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->routes);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getRoutes()
    {
        return collect($this->routes);
    }

    /**
     * @param string $id
     * @return RouteContract
     */
    public function getRoute($id)
    {
        return collect($this->routes)->first(function ($route) use ($id) {
            return $route->getId() == $id;
        });
    }

    /**
     * @param string $filename
     * @return RouteRegistry
     */
    public static function initFromFile($filename = null)
    {
        $registry = new self;
        $filename = $filename ?: 'routes.json';

        if (! Storage::exists($filename)) return $registry;
        $routes = json_decode(Storage::get($filename), true);
        if ($routes === null) return $registry;

        collect($routes)->each(function ($routeDetails) use ($registry) {
            $route = new Route([
                'id' => $routeDetails['id'],
                'method' => $routeDetails['method'],
                'path' => $routeDetails['path']
            ]);

            $route->addEndpoint(new Endpoint([
                'method' => $routeDetails['method'],
                'url' => $routeDetails['endpoint']
            ]));

            $registry->addRoute($route);
        });

        return $registry;
    }
}