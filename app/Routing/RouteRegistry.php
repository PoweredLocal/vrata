<?php

namespace App\Routing;

use Illuminate\Support\Facades\Storage;
use Laravel\Lumen\Application;
use Webpatser\Uuid\Uuid;

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
     * RouteRegistry constructor.
     */
    public function __construct()
    {
        $this->parseConfigRoutes();
    }

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
     * @param Application $app
     */
    public function bind(Application $app)
    {
        $this->getRoutes()->each(function ($route) use ($app) {
            $method = strtolower($route->getMethod());

            $middleware = [ 'helper:' . $route->getId() ];
            if (! $route->isPublic()) $middleware[] = 'auth';

            $app->{$method}($route->getPath(), [
                'uses' => 'App\Http\Controllers\GatewayController@' . $method,
                'middleware' => $middleware
            ]);
        });
    }

    /**
     * @return $this
     */
    private function parseConfigRoutes()
    {
        $config = config('gateway');
        if (empty($config)) return $this;

        $this->parseRoutes($config['routes']);

        return $this;
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

        // We want to re-parse config routes to allow route overwriting
        return $registry->parseRoutes($routes)->parseConfigRoutes();
    }

    /**
     * @param array $routes
     * @return $this
     */
    private function parseRoutes(array $routes)
    {
        collect($routes)->each(function ($routeDetails) {
            if (! isset($routeDetails['id'])) {
                $routeDetails['id'] = (string)Uuid::generate(4);
            }

            $route = new Route($routeDetails);

            collect($routeDetails['actions'])->each(function ($action, $alias) use ($route) {
                $route->addAction(new Action(array_merge($action, ['alias' => $alias])));
            });

            $this->addRoute($route);
        });

        return $this;
    }
}