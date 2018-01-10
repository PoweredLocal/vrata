<?php

namespace App\Console\Commands;

use App\Exceptions\DataFormatException;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Webpatser\Uuid\Uuid;

class Swagger2ParseServices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gateway:parse2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse Swagger 2 API documentation of services';

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
     * @return array
     */
    protected function getActions()
    {
        $actions = [];
        
        foreach($this->config['services'] as $serviceId => $settings){
            $this->info('** Parsing ' . $serviceId);

            $docRoot = $settings['doc_point'] ?? $this->config['global']['doc_point'];
            $hostname = $settings['hostname'] ?? $serviceId . '.' . $this->config['global']['domain'];
            $url = $settings['url'] ?? 'http://' . $hostname;
            $prefix = $settings['prefix'];

            $response = $this->client->request('GET', $url . $docRoot, ['timeout' => 10.0]);

            $data = json_decode((string) $response->getBody(), true);
            if ($data === null) throw new DataFormatException('Unable to get JSON response from ' . $serviceId);
            if (! isset($data['paths'])) throw new DataFormatException($serviceId . ' doesn\'t contain Swagger 2 API data');
            
            foreach ($data['paths'] as $path => $resourceMethods) {
                foreach ($resourceMethods as $method => $actionDetails) {
                    $actions[] = [
                        'id' => (string)Uuid::generate(4),
                        'method' => $method,
                        'path' => (empty($prefix) ? $this->config['global']['prefix'] : $prefix) . $path,
                        'url' => $url,
                        'service' => $serviceId,
                        'docRoot' => $docRoot,
                        'actions' => [[
                            'method' => $method,
                            'service' => $serviceId,
                            'path' => $data['basePath'] . $path,
                            'critical' => true
                        ]]
                    ];
                }
            }
        };
        
        return $actions;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws DataFormatException
     */
    public function handle()
    {
        $output = $this->getActions();

        if (empty($output)) throw new DataFormatException('Unable to parse the routes');

        $this->info('Dumping route data to JSON file');
        Storage::put('routes.json', json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->info('Finished!');
    }
}