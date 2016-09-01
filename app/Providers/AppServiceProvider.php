<?php

namespace App\Providers;

use App\Http\Request;
use App\Routing\RouteRegistry;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpFoundation\File\File;
use Illuminate\Support\Facades\Storage;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->singleton(RouteRegistry::class, function() {
            return RouteRegistry::initFromFile('routes.json');
        });

        $this->app->singleton(Request::class, function () {
            return $this->prepareRequest(Request::capture());
        });

        $this->app->alias(Request::class, 'request');

        $this->registerRoutes();
    }

    /**
     * Prepare the given request instance for use with the application.
     *
     * @param   Request $request
     * @return  Request
     */
    protected function prepareRequest(Request $request)
    {
        $request->setUserResolver(function () {
            return $this->app->make('auth')->user();
        })->setRouteResolver(function () {
            return $this->app->currentRoute;
        });

        return $request;
    }

    /**
     * @return void
     */
    protected function registerRoutes()
    {
        $registry = $this->app->make(RouteRegistry::class);

        if ($registry->isEmpty()) {
            Log::info('Not adding any service routes - route file is missing');
            return;
        }

        $registry->getRoutes()->each(function ($route) {
            $method = strtolower($route->getMethod());

            $this->app->{$method}($route->getPath(), [
                'uses' => 'App\Http\Controllers\GatewayController@' . $method,
                'middleware' => [ 'auth', 'helper:' . $route->getId() ]
            ]);
        });
    }
}
