<?php

use Laravel\Lumen\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

class AuthTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function user_can_get_a_token_based_on_password()
    {
        $user = \App\User::create([
            'email' => 'taylor@laravel.com',
            'password' => 'my-password'
        ]);

        DB::insert('insert into oauth_clients (user_id, name, secret, password_client, revoked, personal_access_client, redirect) values (?, ?, ?, ?, ?, ?, ?)', [$user->id, 'Test', '', 1, 0, 0, '']);

        $this->post('/oauth/token', [
            'grant_type' => 'password',
            'client_id' => $this->app['db.connection']->getPdo()->lastInsertId(),
            'username' => 'taylor@laravel.com',
            'password' => 'my-password',
            'scope' => '*',
        ]);

        $token = json_decode($this->response->getContent(), true);
        $this->assertEquals(true, $token !== null);
        $this->assertEquals('Bearer', $token['token_type']);
    }

    /**
     * @test
     */
    public function invalid_user_wont_get_a_token()
    {
        $user = \App\User::create([
            'email' => 'taylor@laravel.com',
            'password' => 'my-password'
        ]);

        DB::insert('insert into oauth_clients (user_id, name, secret, password_client, revoked, personal_access_client, redirect) values (?, ?, ?, ?, ?, ?, ?)', [$user->id, 'Test', '', 1, 0, 0, '']);

        $response = $this->post('/oauth/token', [
            'grant_type' => 'password',
            'client_id' => $this->app['db.connection']->getPdo()->lastInsertId(),
            'username' => 'taylor@laravel.com',
            'password' => 'my-pas23sword',
            'scope' => '*',
        ]);

        $token = json_decode($this->response->getContent(), true);
        $this->assertEquals(401, $this->response->getStatusCode());
        $this->assertEquals(true, ! empty($token['error']));
    }

    /**
     * @test
     */
    public function protected_route_doesnt_work_without_token()
    {
        $this->get('/');
        $this->assertEquals(401, $this->response->getStatusCode());
        $this->assertEquals('Unauthorized.', $this->response->getContent());
    }

    /**
     * @test
     */
    public function protected_route_works_with_valid_token()
    {
        $user = \App\User::create([
            'email' => 'taylor@laravel.com',
            'password' => 'my-password'
        ]);

        DB::insert('insert into oauth_clients (user_id, name, secret, password_client, revoked, personal_access_client, redirect) values (?, ?, ?, ?, ?, ?, ?)', [$user->id, 'Test', '', 1, 0, 0, '']);

        $response = $this->post('/oauth/token', [
            'grant_type' => 'password',
            'client_id' => $this->app['db.connection']->getPdo()->lastInsertId(),
            'username' => 'taylor@laravel.com',
            'password' => 'my-password',
            'scope' => '*',
        ]);

        $token = json_decode($this->response->getContent(), true);

        $this->get('/', [
            'Authorization' => 'Bearer ' . $token['access_token']
        ]);

        $this->assertEquals(200, $this->response->getStatusCode());
    }

    /**
     * @test
     */
    public function user_can_see_details_about_his_account()
    {
        $user = \App\User::create([
            'email' => 'taylor@laravel.com',
            'password' => 'my-password'
        ]);

        DB::insert('insert into oauth_clients (user_id, name, secret, password_client, revoked, personal_access_client, redirect) values (?, ?, ?, ?, ?, ?, ?)', [$user->id, 'Test', '', 1, 0, 0, '']);

        $response = $this->post('/oauth/token', [
            'grant_type' => 'password',
            'client_id' => $this->app['db.connection']->getPdo()->lastInsertId(),
            'username' => 'taylor@laravel.com',
            'password' => 'my-password',
            'scope' => '*',
        ]);

        $token = json_decode($this->response->getContent(), true);

        $this->get('/me', [
            'Authorization' => 'Bearer ' . $token['access_token']
        ]);

        $decoded = json_decode($this->response->getContent(), true);
        $this->assertEquals(200, $this->response->getStatusCode());
        $this->assertEquals('taylor@laravel.com', $decoded['email']);
    }

    /**
     * @test
     */
    public function client_id_may_have_custom_ttl()
    {
        $user = \App\User::create([
            'email' => 'taylor@laravel.com',
            'password' => 'my-password'
        ]);

        DB::insert('insert into oauth_clients (user_id, name, secret, password_client, revoked, personal_access_client, redirect) values (?, ?, ?, ?, ?, ?, ?)', [$user->id, 'Test', '', 1, 0, 0, '']);

        $clientId = $this->app['db.connection']->getPdo()->lastInsertId();

        \Dusterio\LumenPassport\LumenPassport::tokensExpireIn(\Carbon\Carbon::now()->addDays(3), $clientId);

        $response = $this->post('/oauth/token', [
            'grant_type' => 'password',
            'client_id' => $clientId,
            'username' => 'taylor@laravel.com',
            'password' => 'my-password',
            'scope' => '*',
        ]);

        $token = json_decode($this->response->getContent(), true);

        $this->get('/', [
            'Authorization' => 'Bearer ' . $token['access_token']
        ]);

        $this->assertEquals(200, $this->response->getStatusCode());
        $this->assertTrue(intval($token['expires_in']) <= 86400 * 3);
    }
}
