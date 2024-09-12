<?php

declare(strict_types=1);

namespace App\Service;

use App\Contracts\BinProviderInterface;
use GuzzleHttp\Client;

class BinLookupService implements BinProviderInterface
{
    private string $binApiUrl;
    private Client $client;

    public function __construct(Client $client, string $binApiUrl)
    {
        $this->binApiUrl = $binApiUrl;
        $this->client = $client;
    }

    /**
     * @throws \Throwable
     */
    public function getBinCountryCode(string $bin): string
    {
        try {
            $response = $this->client->get(sprintf('%s%s', $this->binApiUrl, $bin));
            $data = json_decode($response->getBody()->getContents());

            if (!isset($data->country->alpha2) || strlen($data->country->alpha2) !== 2) {
                throw new \Exception('Invalid response: BIN lookup did not return a valid country code');
            }

            return $data->country->alpha2;

        } catch (\Throwable $e) {
            throw new \Exception(sprintf('Error fetching or processing BIN "%s" data: %s', $bin, $e->getMessage()));
        }
    }
}
