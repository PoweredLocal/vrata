<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use App\Console\Commands\ParseServices;
use GuzzleHttp\Middleware;
use Illuminate\Support\Facades\Storage;

/**
 * Class ParseServicesTest
 */
class ParseServicesTest extends TestCase
{
    protected $mockConfig = [
        'services' => [
            'satu' => [],
            'dua' => []
        ],
        'global' => [
            'prefix' => '/v1',
            'timeout' => 5.0,
            'doc_point' => '/api/doc',
            'domain' => 'local'
        ],
    ];

    /**
     * @var array
     */
    protected $mockResources = [
        [
            'apiVersion' => 'v1',
            'swaggerVersion' => '1.2',
            'apis' => [
                [ 'path' => '/devices', 'description' => 'Operations on devices' ],
                [ 'path' => '/balloons', 'description' => 'Operations on balloons' ]
            ]
        ],
        [
            'apiVersion' => 'v1',
            'swaggerVersion' => '1.2',
            'apis' => [
                [ 'path' => '/jokes', 'description' => 'Operations on jokes' ],
            ]
        ],
    ];

    /**
     * @var array
     */
    protected $mockEndpoints = [
        [
            'apiVersion' => 'v1',
            'swaggerVersion' => '1.2',
            'resourcePath' => '/devices',
            'apis' => [
                ['path' => '/devices', 'operations' => [['method' => 'GET']]]
            ]
        ],
        [
            'apiVersion' => 'v1',
            'swaggerVersion' => '1.2',
            'resourcePath' => '/balloons',
            'apis' => [
                ['path' => '/balloons/{id}', 'operations' => [['method' => 'GET']]]
            ]
        ],
        [
        'apiVersion' => 'v1',
        'swaggerVersion' => '1.2',
        'resourcePath' => '/jokes',
        'apis' => [
            ['path' => '/jokes', 'operations' => [['method' => 'GET']]]
        ]
    ]
    ];

    /**
     * @var array
     */
    protected $expectedRoutes = [
        [
            'method' => 'GET',
            'path' => '/v1/devices',
            'actions' => [
                [
                    'method' => 'GET',
                    'service' => 'satu',
                    'path' => '/devices',
                    'critical' => true
                ]
            ]
        ],
        [
            'method' => 'GET',
            'path' => '/v1/balloons/{id}',
            'actions' => [
                [
                    'method' => 'GET',
                    'service' => 'satu',
                    'path' => '/balloons/{id}',
                    'critical' => true
                ]
            ]
        ],
        [
            'method' => 'GET',
            'path' => '/v1/jokes',
            'actions' => [
                [
                    'method' => 'GET',
                    'service' => 'dua',
                    'path' => '/jokes',
                    'critical' => true
                ]
            ]
        ],
    ];

    /**
     * @test
     * @expectedException \App\Exceptions\DataFormatException
     */
    public function empty_service_array_throws_an_exception()
    {
        $mock = new MockHandler([
            new \GuzzleHttp\Psr7\Response(200, [], 'test')
        ]);

        $handler = HandlerStack::create($mock);

        $client = new Client(['handler' => $handler]);
        $command = new ParseServices($client, []);
        $command->setLaravel($this->app);
        $this->assertEquals(ParseServices::class, get_class($command));
        $command->run(new \Symfony\Component\Console\Input\ArgvInput(), new \Symfony\Component\Console\Output\ConsoleOutput());
    }

    /**
     * @test
     * @expectedException \App\Exceptions\DataFormatException
     */
    public function requests_are_made_to_services_but_wrong_output_throws_exception()
    {
        $container = [];
        $history = Middleware::history($container);

        $mock = new MockHandler([
            new \GuzzleHttp\Psr7\Response(200, [], json_encode('test')),
            new \GuzzleHttp\Psr7\Response(200, [], json_encode('test')),
        ]);

        $handler = HandlerStack::create($mock);
        $handler->push($history);

        $client = new Client(['handler' => $handler]);
        $command = new ParseServices($client, $this->mockConfig);
        $command->setLaravel($this->app);
        $this->assertEquals(ParseServices::class, get_class($command));
        $command->run(new \Symfony\Component\Console\Input\ArgvInput(), new \Symfony\Component\Console\Output\NullOutput());

        $this->assertEquals(2, count($container));
    }

    /**
     * @test
     * @covers \App\Console\Commands\ParseServices::getPaths
     * @covers \App\Console\Commands\ParseServices::getResources
     * @covers \App\Console\Commands\ParseServices::getActions
     */
    public function requests_are_made_to_services()
    {
        $container = [];
        $history = Middleware::history($container);

        $mock = new MockHandler([
            new \GuzzleHttp\Psr7\Response(200, [], json_encode($this->mockResources[0])),
            new \GuzzleHttp\Psr7\Response(200, [], json_encode($this->mockResources[1])),
            new \GuzzleHttp\Psr7\Response(200, [], json_encode($this->mockEndpoints[0])),
            new \GuzzleHttp\Psr7\Response(200, [], json_encode($this->mockEndpoints[1])),
            new \GuzzleHttp\Psr7\Response(200, [], json_encode($this->mockEndpoints[2])),
        ]);

        $handler = HandlerStack::create($mock);
        $handler->push($history);

        $client = new Client(['handler' => $handler]);
        $command = new ParseServices($client, $this->mockConfig);
        $command->setLaravel($this->app);
        $this->assertEquals(ParseServices::class, get_class($command));
        $command->run(new \Symfony\Component\Console\Input\ArgvInput(), new \Symfony\Component\Console\Output\NullOutput());

        $this->assertEquals(5, count($container));

        $routes = Storage::get('routes.json');
        $routes = json_decode($routes, true);
        $this->assertTrue($routes !== null);

        // Remove unique IDs
        $routes = collect($routes)->map(function($route) {
            return array_diff_key($route, ['id' => '']);
        })->toArray();

        $this->assertEquals($this->expectedRoutes, $routes);
    }
}