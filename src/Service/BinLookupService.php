<?php

declare(strict_types=1);

namespace App\Service;

use App\Contracts\BinProviderInterface;
use GuzzleHttp\Client;

class BinLookupService implements BinProviderInterface
{
    private Client $client;

    public function __construct(string $binApiUrl)
    {
        $this->client = new Client(['base_uri' => $binApiUrl]);
    }

    /**
     * @throws \Throwable
     */
    public function getBinCountryCode(string $bin): string
    {
        try {
            $response = $this->client->get($bin);
            $data = json_decode($response->getBody()->getContents());

            if (!isset($data->country->alpha2) || strlen($data->country->alpha2) !== 2) {
                throw new \Exception('Invalid response: BIN lookup did not return a valid country code');
            }

            return $data->country->alpha2;

        } catch (\Throwable $e) {
            throw new \Exception(sprintf('%s "%s"', 'Error fetching or processing BIN data:', $e->getMessage()));
        }
    }
}
