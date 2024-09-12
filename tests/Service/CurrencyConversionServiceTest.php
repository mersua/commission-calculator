<?php

declare(strict_types=1);

namespace Tests\Service;

use PHPUnit\Framework\TestCase;
use App\Service\CurrencyConversionService;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;

class CurrencyConversionServiceTest extends TestCase
{
    private string $currencyApiUrl;
    private string $currencyApiKey;

    protected function setUp(): void
    {
        $this->currencyApiUrl = $_ENV['CURRENCY_API_URL'];
        $this->currencyApiKey = $_ENV['CURRENCY_API_KEY'];
    }

    public function testConvertToEURSuccess()
    {
        $clientMock = $this->createMock(Client::class);
        $clientMock->method('get')->willReturn(new Response(200, [], '{"rates": {"USD": 1.2}}'));

        $service = new CurrencyConversionService($this->currencyApiUrl, $this->currencyApiKey);

        $this->assertEquals(83.33, $service->convertToEUR(100, 'USD'));
    }

    public function testConvertToEURNoConversionNeeded()
    {
        $service = new CurrencyConversionService($this->currencyApiUrl, $this->currencyApiKey);

        $this->assertEquals(100, $service->convertToEUR(100, 'EUR'));
    }

    public function testConvertToEURInvalidCurrency()
    {
        $clientMock = $this->createMock(Client::class);
        $clientMock->method('get')->willReturn(new Response(200, [], '{"rates": {}}'));

        $service = new CurrencyConversionService($this->currencyApiUrl, $this->currencyApiKey);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Currency rate for "USD" not found');
        $service->convertToEUR(100, 'USD');
    }

    public function testConvertToEURApiFailure()
    {
        $clientMock = $this->createMock(Client::class);
        $clientMock->method('get')->willThrowException(new RequestException('Error Communicating with Server', new \GuzzleHttp\Psr7\Request('GET', 'test')));

        $service = new CurrencyConversionService($this->currencyApiUrl, $this->currencyApiKey);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Error fetching currency rates: Error Communicating with Server');
        $service->convertToEUR(100, 'USD');
    }
}
