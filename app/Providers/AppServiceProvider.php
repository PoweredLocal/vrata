<?php

namespace App\Providers;

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
        $this->registerRoutes();
    }

    /**
     * @return void
     */
    protected function registerRoutes()
    {
        $config = $this->app['config']->get('gateway');

        if (Storage::exists('routes.json')) {
            $routes = json_decode(Storage::get('routes.json'), true);

            foreach ($routes as $route) {
                $method = strtolower($route['method']);
                $path = parse_url($route['path'], PHP_URL_PATH);
                if (isset($config['global']['prefix'])) $path = $config['global']['prefix'] . $path;

                $this->app->group(['namespace' => 'App\Http\Controllers', 'middleware' => ['auth']], function ($app) use ($path, $method) {
                    $app->{$method}($path, 'GatewayController@' . $method);
                });
            }
        } else {
            Log::info('Not adding any service routes - route file is missing');
        }
    }
}
