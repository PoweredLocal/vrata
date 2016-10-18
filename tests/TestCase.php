<?php

use Illuminate\Http\Request;

class TestCase extends Laravel\Lumen\Testing\TestCase
{
    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
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
            return $request;
        });

        $this->app->alias(\App\Http\Request::class, 'request');

        return $this->response = $this->app->prepareResponse(
            $this->app->handle($request)
        );
    }
}
