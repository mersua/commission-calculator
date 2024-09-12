<?php

declare(strict_types=1);

namespace Tests\Service;

use PHPUnit\Framework\TestCase;
use App\Service\BinLookupService;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;

class BinLookupServiceTest extends TestCase
{
    private string $binApiUrl;

    protected function setUp(): void
    {
        $this->binApiUrl = $_ENV['BIN_API_URL'];
    }

    public function testGetBinCountryCodeSuccess()
    {
        $clientMock = $this->createMock(Client::class);
        $clientMock->method('get')->willReturn(new Response(200, [], '{"country": {"alpha2": "DE"}}'));

        $service = new BinLookupService($this->binApiUrl);

        $this->assertEquals('DE', $service->getBinCountryCode('45717360'));
    }

    public function testGetBinCountryCodeInvalidResponse()
    {
        $clientMock = $this->createMock(Client::class);
        $clientMock->method('get')->willReturn(new Response(200, [], '{}'));

        $service = new BinLookupService($this->binApiUrl);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid response: BIN lookup did not return a valid country code');
        $service->getBinCountryCode('45717360');
    }

    public function testGetBinCountryCodeApiFailure()
    {
        $clientMock = $this->createMock(Client::class);
        $clientMock->method('get')->willThrowException(new RequestException('Error Communicating with Server', new \GuzzleHttp\Psr7\Request('GET', 'test')));

        $service = new BinLookupService($this->binApiUrl);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Error fetching BIN data: Error Communicating with Server');
        $service->getBinCountryCode('45717360');
    }
}
