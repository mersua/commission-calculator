<?php

declare(strict_types=1);

namespace App\Service;

use App\Contracts\CurrencyProviderInterface;
use GuzzleHttp\Client;

class CurrencyConversionService implements CurrencyProviderInterface
{
    private string $currencyApiUrl;
    private string $currencyApiKey;
    private Client $client;

    public function __construct(Client $client, string $currencyApiUrl, string $currencyApiKey)
    {
        $this->currencyApiUrl = $currencyApiUrl;
        $this->currencyApiKey = $currencyApiKey;
        $this->client = $client;
    }

    /**
     * @throws \Exception
     */
    public function convertToEUR(float $amount, string $currency): float
    {
        if ($currency === 'EUR') {
            return $amount;
        }

        try {
            $response = $this->client->get(
                sprintf('%s?%s', $this->currencyApiUrl, http_build_query(['access_key' => $this->currencyApiKey]))

            );
            $data = json_decode($response->getBody()->getContents());

            if (!isset($data->rates->$currency)) {
                throw new \Exception(sprintf('Currency rate for "%s" not found', $currency));
            }

            return $amount / $data->rates->$currency;

        } catch (\Throwable $e) {
            throw new \Exception(sprintf('Error fetching or processing currency data: %s', $e->getMessage()));
        }
    }
}
