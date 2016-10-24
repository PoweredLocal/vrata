<?php

use Illuminate\Http\Request;
use App\Services\ServiceRegistryContract;
use App\Services\DNSRegistry;

class TestCase extends Laravel\Lumen\Testing\TestCase
{
    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make('config')->set('filesystems.disks.local', [
            'driver' => 'local',
            'root'   => base_path('tests/tmp'),
        ]);

        $app->bind(ServiceRegistryContract::class, DNSRegistry::class);

        return $app;
    }

    /**
     * Send the given request through the application.
     *
     * This method allows you to fully customize the entire Request object.
     *
     * @param  Request  $request
     * @return $this
     */
    public function handle(Request $request)
    {
        $this->currentUri = $request->fullUrl();

        $this->response = $this->app->prepareResponse($this->app->handle($request));

        return $this;
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
        })->setTrustedProxies([
            '10.7.0.0/16'
        ]);

        return $request;
    }

    /**
     * Call the given URI and return the Response.
     *
     * @param  string  $method
     * @param  string  $uri
     * @param  array   $parameters
     * @param  array   $cookies
     * @param  array   $files
     * @param  array   $server
     * @param  string  $content
     * @return \Illuminate\Http\Response
     */
    public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        $this->currentUri = $this->prepareUrlForRequest($uri);

        $request = \App\Http\Request::create(
            $this->currentUri, $method, $parameters,
            $cookies, $files, $server, $content
        );

        $this->app->singleton(\App\Http\Request::class, function () use ($request) {
            return $this->prepareRequest($request);
        });

        $this->app->alias(\App\Http\Request::class, 'request');

        return $this->response = $this->app->prepareResponse(
            $this->app->handle($request)
        );
    }
}
