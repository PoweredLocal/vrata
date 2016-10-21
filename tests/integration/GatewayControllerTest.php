<?php

use App\Routing\RouteRegistry;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;

class RoutingTest extends TestCase {
    use \Laravel\Lumen\Testing\DatabaseTransactions;

    /**
     * @var array
     */
    protected $history = [];

    protected $mockRoutes = ['gateway' => [
        'services' => [
            'service1' => [],
            'service2' => []
        ],

        'routes' => [
            [
                'aggregate' => true,
                'method' => 'GET',
                'path' => '/somewhere/{page}/details',
                'source' => [
                    'basic' => [
                        'service' => 'service1',
                        'method' => 'GET',
                        'path' => '/pages/{page}',
                        'sequence' => 0
                    ],
                    'settings' => [
                        'service' => 'service1',
                        'json_key' => 'details.settings',
                        'method' => 'GET',
                        'path' => '/posts/{basic_post_id}',
                        'sequence' => 1,
                        'critical' => false
                    ],
                    'clients' => [
                        'service' => 'service2',
                        'json_key' => 'details.data',
                        'method' => 'GET',
                        'path' => '/data/{basic_post_id}',
                        'sequence' => 1,
                        'critical' => false
                    ]
                ]
            ]
        ],

        'global' => [
            'prefix' => '/v1',
            'timeout' => 1.0
        ],

        'defaults' => [
            'doc_point' => '/api/doc',
            'domain' => 'localhost'
        ]
    ]];

    /**
     * @test
     */
    public function config_routes_are_parsed_correctly()
    {
        config($this->mockRoutes);
        $registry = new RouteRegistry;

        $this->assertFalse($registry->isEmpty());
        $route = $registry->getRoutes()->first();
        $this->assertEquals('/v1/somewhere/{page}/details', $route->getPath());
        $this->assertEquals(3, $route->getActions()->count());
    }

    /**
     * @test
     */
    public function aggregate_route_works()
    {
        config($this->mockRoutes);

        $this->app->singleton(RouteRegistry::class, function() {
            return new RouteRegistry;
        });

        $this->app->make(RouteRegistry::class)->bind(app());

        $response1 = ['id' => 5123123, 'title' => 'Some title', 'post_id' => 5];
        $response2 = ['book_id' => 5];
        $response3 = ['more_data' => 'something'];

        $this->mockGuzzle([
            new Response(200, [], json_encode($response1)),
            new Response(200, [], json_encode($response2)),
            new Response(200, [], json_encode($response3))
        ]);

        $this->get('/v1/somewhere/super-page/details', [
            'Authorization' => 'Bearer ' . $this->getUser()
        ]);

        $this->assertEquals(200, $this->response->getStatusCode());
        $this->assertEquals(3, count($this->history));
        $output = json_decode($this->response->getContent(), true);
        $this->assertFalse($output === null);
        $this->assertTrue(! array_diff($output, $response1 + $response2 + $response3) && ! array_diff($response1 + $response2 + $response3, $output));
    }

    /**
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        // Erase request history
        $this->history = [];
    }

    /**
     * @param array $responses
     * @return void
     */
    private function mockGuzzle(array $responses)
    {
        $history = Middleware::history($this->history);
        $mock = new MockHandler($responses);

        $stack = HandlerStack::create($mock);
        $stack->push($history);

        $this->app->singleton(Client::class, function() use ($stack) {
            return new Client([
                'handler' => $stack
            ]);
        });
    }

    /**
     * @return string
     */
    private function getUser()
    {
        $user = \App\User::create([
            'email' => 'taylor@laravel.com',
            'login' => 'dasdasd',
            'password' => 'my-password'
        ]);

        DB::insert('insert into oauth_clients (user_id, name, password_client) values (?, ?, ?)', [$user->id, 'Test', 1]);

        $this->post('/oauth/token', [
            'grant_type' => 'password',
            'client_id' => $this->app['db.connection']->getPdo()->lastInsertId(),
            'username' => 'taylor@laravel.com',
            'password' => 'my-password',
            'scope' => '*',
        ]);

        $token = json_decode($this->response->getContent(), true);

        return $token['access_token'];
    }
}