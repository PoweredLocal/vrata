<?php

namespace App\Console\Commands;

use App\Exceptions\DataFormatException;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Webpatser\Uuid\Uuid;

class ParseServices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gateway:parse';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse API documentation of services';

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var array
     */
    protected $config;

    /**
     * ParseServices constructor.
     * @param Client $client
     * @param array $config
     */
    public function __construct(Client $client, $config = null)
    {
        parent::__construct();
        $this->client = $client;
        $this->config = $config ?: app('config')->get('gateway');
    }

    /**
     * @return Collection
     */
    private function getRoot()
    {
        return collect($this->config['services'])->map(function($settings, $serviceId) {
            $this->info('** Parsing ' . $serviceId);

            $docRoot = $settings['doc_point'] ?? $this->config['global']['doc_point'];
            $hostname = $settings['hostname'] ?? $serviceId . '.' . $this->config['global']['domain'];
            $url = $settings['url'] ?? 'http://' . $hostname;

            $response = $this->client->request('GET', $url . $docRoot, ['timeout' => 10.0]);

            $data = json_decode((string) $response->getBody(), true);
            if ($data === null) throw new DataFormatException('Unable to get JSON response from ' . $serviceId);

            if (isset($data['swaggerVersion']) && isset($data['apis']) && preg_match('/^1\./', $data['swaggerVersion']))
                return $this->injectSettings($this->parseSwaggerResources($data['apis']), $url, $docRoot, $serviceId);

            if (isset($data['swagger']) && preg_match('/^2\./', $data['swagger']) && isset($data['paths']))
                return $this->injectSettings($this->parseSwaggerTwo($data), $url, $docRoot, $serviceId);

            throw new DataFormatException($serviceId . ' doesn\'t contain API data');
        })->flatten(1);
    }

    /**
     * @param Collection $data
     * @param string $url
     * @param string $docRoot
     * @param string $serviceId
     * @return Collection
     */
    protected function injectSettings(Collection $data, $url, $docRoot, $serviceId)
    {
        return $data->map(function($input) use ($url, $serviceId, $docRoot) {
            return array_merge($input, [
                'url' => $url,
                'service' => $serviceId,
                'docRoot' => $docRoot,
            ]);
        });
    }

    /**
     * Parse an array of Swagger V1 resources (root level)
     *
     * @param array $resources
     * @return Collection
     */
    protected function parseSwaggerResources(array $resources)
    {
        return collect($resources)->map(function ($api) {
            return $api;
        });
    }

    /**
     * Parse a Swagger V2 output
     *
     * @param array $swagger
     * @return Collection
     */
    protected function parseSwaggerTwo(array $swagger)
    {
        $basePath = array_key_exists('basePath', $swagger) ? $swagger['basePath'] : '/';
        return collect($swagger['paths'])->map(function($data, $path) use ($basePath) {
            return [
                'path' => str_replace('//', '/', $basePath.$path),
                'description' => isset($data['description']) ? $data['description'] : '',
                'swagger2-data' => $data
            ];
        });
    }

    /**
     * @param Collection $resources
     * @return array
     */
    private function getPaths(Collection $resources)
    {
        return $resources->reduce(function($carry, $resource) {
            $pathElements = explode('.', $resource['path']);
            $resource['path'] = reset($pathElements);
            $this->line('Processing API action: ' . $resource['url'] . $resource['path']);

            if (! isset($resource['swagger2-data'])) {
                $response = $this->client->request('GET', $resource['url'] . $resource['docRoot'] . $resource['path'], ['timeout' => 10.0]);
                $data = json_decode((string) $response->getBody(), true);
                if ($data === null) throw new DataFormatException('Unable to get JSON response from ' . $resource['serviceId']);

                // Inject service details
                $apis = collect($data['apis'])->map(function ($api) use ($resource) {
                    return array_merge($resource, $api);
                });

                return array_merge($carry, $apis->toArray());
            }

            return array_merge($carry, [array_merge($resource, [
                'operations' =>
                    collect($resource['swagger2-data'])->map(function($data, $method) {
                        return ['method' => strtoupper($method)];
                    })->toArray(),
                'swagger2-data' => null
            ])]);
        }, []);
    }

    /**
     * @param Collection $paths
     * @return array
     */
    private function getActions(Collection $paths)
    {
        return collect($paths)->reduce(function($carry, $route) {
            $pathElements = explode('.', $route['path']);
            $route['path'] = reset($pathElements);

            foreach ($route['operations'] as $realOperation) {
                $carry[] = [
                    'id' => (string)Uuid::generate(4),
                    'method' => $realOperation['method'],
                    'path' => $this->config['global']['prefix'] . $route['path'],
                    'actions' => [[
                        'method' => $realOperation['method'],
                        'service' => $route['service'],
                        'path' => $route['path'],
                        'critical' => true
                    ]]
                ];
            }

            return $carry;
        }, []);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws DataFormatException
     */
    public function handle()
    {
        $output = $this->getActions(
            collect($this->getPaths(
                $this->getRoot()
            ))
        );

        if (empty($output)) throw new DataFormatException('Unable to parse the routes');

        $this->info('Dumping route data to JSON file');
        Storage::put('routes.json', json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->info('Finished!');
    }
}
