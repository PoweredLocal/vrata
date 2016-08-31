<?php

namespace App\Console\Commands;

use App\Exceptions\DataFormatException;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

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
     * Execute the console command.
     *
     * @return mixed
     * @throws DataFormatException
     */
    public function handle()
    {
        foreach ($this->config['services'] as $serviceId => $settings) {
            $this->info('** Parsing ' . $serviceId);

            $docRoot = $settings['doc_point'] ?? $this->config['defaults']['doc_point'];
            $url = $settings['url'] ?? 'http://' . $serviceId . '.' . $this->config['defaults']['domain'];
            $response = $this->client->request('GET', $url . $docRoot);

            $data = json_decode((string) $response->getBody(), true);
            if ($data === null) throw new DataFormatException('Unable to get JSON response from ' . $serviceId);
            if (! isset($data['apis'])) throw new DataFormatException($serviceId . ' doesn\'t contain API data');

            foreach ($data['apis'] as $api) {
                $pathElements = explode('.', $api['path']);
                $api['path'] = reset($pathElements);
                $this->line('Processing API endpoint: ' . $url . $api['path']);

                $response = $this->client->request('GET', $url . $docRoot . $api['path']);
                $apiData = json_decode((string) $response->getBody(), true);
                if ($apiData === null) throw new DataFormatException('Unable to get JSON response from ' . $serviceId);
                //print_r($apiData);
            }
        }

        $this->info('Finished!');
    }
}