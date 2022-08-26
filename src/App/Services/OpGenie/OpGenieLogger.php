<?php

declare(strict_types=1);

namespace App\Services\OpGenie;

use GuzzleHttp\Client;

/**
 * Class OpGenieLogger
 * @package App\Services\OpGenie
 */
class OpGenieLogger
{
    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    public function __construct()
    {
        $this->client = new Client(['base_uri' => 'https://api.eu.opsgenie.com']);
    }

    public function sendToOPGenie($json)
    {
        $response = $this->client->request('POST', '/v1/incidents/create', [
            'headers' => ['Content-Type' => 'application/json', 'Authorization' => getenv('OpGenieKey')],
            'json'    => $json
            ]);
    }
}
