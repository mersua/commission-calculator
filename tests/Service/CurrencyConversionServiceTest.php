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
    private static string $currencyApiUrl;
    private static string $currencyApiKey;

    public static function setUpBeforeClass(): void
    {
        self::$currencyApiUrl = $_ENV['CURRENCY_API_URL'];
        self::$currencyApiKey = $_ENV['CURRENCY_API_KEY'];
    }

    public function testConvertToEURSuccess()
    {
        $clientMock = $this->createMock(Client::class);
        $clientMock->method('get')->willReturn(new Response(200, [], '{"rates": {"USD": 1.2}}'));

        $service = new CurrencyConversionService($clientMock, self::$currencyApiUrl, self::$currencyApiKey);

        $this->assertEquals(83.33, round($service->convertToEUR(100, 'USD'), 2));
    }

    public function testConvertToEURNoConversionNeeded()
    {
        $clientMock = $this->createMock(Client::class);

        $service = new CurrencyConversionService($clientMock, self::$currencyApiUrl, self::$currencyApiKey);

        $this->assertEquals(100, $service->convertToEUR(100, 'EUR'));
    }

    public function testConvertToEURInvalidCurrency()
    {
        $clientMock = $this->createMock(Client::class);
        $clientMock->method('get')->willReturn(new Response(200, [], '{"rates": {}}'));

        $service = new CurrencyConversionService($clientMock, self::$currencyApiUrl, self::$currencyApiKey);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Currency rate for "USD" not found');
        $service->convertToEUR(100, 'USD');
    }

    public function testConvertToEURApiFailure()
    {
        $clientMock = $this->createMock(Client::class);
        $clientMock->method('get')->willThrowException(new RequestException('Error Communicating with Server', new \GuzzleHttp\Psr7\Request('GET', 'test')));

        $service = new CurrencyConversionService($clientMock, self::$currencyApiUrl, self::$currencyApiKey);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Error fetching or processing currency data: Error Communicating with Server');
        $service->convertToEUR(100, 'USD');
    }
}
