<?php

namespace App\Providers;

use App\User;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        /*$this->app['auth']->viaRequest('api', function ($request) {
            if ($request->input('api_token')) {
                return User::where('api_token', $request->input('api_token'))->first();
            }
        }); */

        $this->app->singleton(Connection::class, function() {
            return $this->app['db.connection'];
        });

        $this->registerRoutes();
    }

    /**
     * Register routes for transient tokens, clients, and personal access tokens.
     *
     * @return void
     */
    public function registerRoutes()
    {
        $this->forAccessTokens();
        $this->forTransientTokens();
        $this->forClients();
        $this->forPersonalAccessTokens();
    }

    /**
     * Register the routes for retrieving and issuing access tokens.
     *
     * @return void
     */
    public function forAccessTokens()
    {
        $this->app->post('/oauth/token', [
            'uses' => '\Laravel\Passport\Http\Controllers\AccessTokenController@issueToken'
        ]);

        $this->app->group(['middleware' => ['auth']], function () {
            $this->app->get('/oauth/tokens', [
                'uses' => '\Laravel\Passport\Http\Controllers\AuthorizedAccessTokenController@forUser',
            ]);

            $this->app->delete('/oauth/tokens/{token_id}', [
                'uses' => '\Laravel\Passport\Http\Controllers\AuthorizedAccessTokenController@destroy',
            ]);
        });
    }

    /**
     * Register the routes needed for refreshing transient tokens.
     *
     * @return void
     */
    public function forTransientTokens()
    {
        $this->app->post('/oauth/token/refresh', [
            'middleware' => ['auth'],
            'uses' => '\Laravel\Passport\Http\Controllers\TransientTokenController@refresh',
        ]);
    }

    /**
     * Register the routes needed for managing clients.
     *
     * @return void
     */
    public function forClients()
    {
        $this->app->group(['middleware' => ['auth']], function () {
            $this->app->get('/oauth/clients', [
                'uses' => '\Laravel\Passport\Http\Controllers\ClientController@forUser',
            ]);

            $this->app->post('/oauth/clients', [
                'uses' => '\Laravel\Passport\Http\Controllers\ClientController@store',
            ]);

            $this->app->put('/oauth/clients/{client_id}', [
                'uses' => '\Laravel\Passport\Http\Controllers\ClientController@update',
            ]);

            $this->app->delete('/oauth/clients/{client_id}', [
                'uses' => '\Laravel\Passport\Http\Controllers\ClientController@destroy',
            ]);
        });
    }

    /**
     * Register the routes needed for managing personal access tokens.
     *
     * @return void
     */
    public function forPersonalAccessTokens()
    {
        $this->app->group(['middleware' => ['auth']], function () {
            $this->app->get('/oauth/scopes', [
                'uses' => '\Laravel\Passport\Http\Controllers\ScopeController@all',
            ]);

            $this->app->get('/oauth/personal-access-tokens', [
                'uses' => '\Laravel\Passport\Http\Controllers\PersonalAccessTokenController@forUser',
            ]);

            $this->app->post('/oauth/personal-access-tokens', [
                'uses' => '\Laravel\Passport\Http\Controllers\PersonalAccessTokenController@store',
            ]);

            $this->app->delete('/oauth/personal-access-tokens/{token_id}', [
                'uses' => '\Laravel\Passport\Http\Controllers\PersonalAccessTokenController@destroy',
            ]);
        });
    }
}
