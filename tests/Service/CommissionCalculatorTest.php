<?php

declare(strict_types=1);

namespace Tests\Service;

use PHPUnit\Framework\TestCase;
use App\Service\CommissionCalculator;
use App\Contracts\BinProviderInterface;
use App\Contracts\CurrencyProviderInterface;

class CommissionCalculatorTest extends TestCase
{
    public function testCalculateCommissionForEuSuccess()
    {
        $binProviderMock = $this->createMock(BinProviderInterface::class);
        $binProviderMock->method('getBinCountryCode')->willReturn('DE');

        $currencyProviderMock = $this->createMock(CurrencyProviderInterface::class);
        $currencyProviderMock->method('convertToEUR')->willReturn(100.00);

        $calculator = new CommissionCalculator($binProviderMock, $currencyProviderMock);
        $transaction = '{"bin":"45717360","amount":"100.00","currency":"EUR"}';

        $this->assertEquals(1.00, $calculator->calculateCommission($transaction));
    }

    public function testCalculateCommissionForNonEuSuccess()
    {
        $binProviderMock = $this->createMock(BinProviderInterface::class);
        $binProviderMock->method('getBinCountryCode')->willReturn('US');

        $currencyProviderMock = $this->createMock(CurrencyProviderInterface::class);
        $currencyProviderMock->method('convertToEUR')->willReturn(100.00);

        $calculator = new CommissionCalculator($binProviderMock, $currencyProviderMock);
        $transaction = '{"bin":"516793","amount":"100.00","currency":"USD"}';

        $this->assertEquals(2.00, $calculator->calculateCommission($transaction));
    }

    public function testInvalidTransactionData()
    {
        $binProviderMock = $this->createMock(BinProviderInterface::class);
        $currencyProviderMock = $this->createMock(CurrencyProviderInterface::class);

        $calculator = new CommissionCalculator($binProviderMock, $currencyProviderMock);
        $transaction = '{"amount":"100.00","currency":"USD"}';  // Missing BIN field

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid transaction data format');
        $calculator->calculateCommission($transaction);
    }

    public function testHandleBinProviderException()
    {
        $binProviderMock = $this->createMock(BinProviderInterface::class);
        $binProviderMock->method('getBinCountryCode')
            ->willThrowException(new \Exception('BIN provider failure'));

        $currencyProviderMock = $this->createMock(CurrencyProviderInterface::class);
        $currencyProviderMock->method('convertToEUR')
            ->willReturn(100.00);

        $calculator = new CommissionCalculator($binProviderMock, $currencyProviderMock);
        $transaction = '{"bin":"45717360","amount":"100.00","currency":"EUR"}';

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Error calculating commission: BIN provider failure');
        $calculator->calculateCommission($transaction);
    }

    public function testHandleCurrencyProviderException()
    {
        $binProviderMock = $this->createMock(BinProviderInterface::class);
        $binProviderMock->method('getBinCountryCode')
            ->willReturn('US');

        $currencyProviderMock = $this->createMock(CurrencyProviderInterface::class);
        $currencyProviderMock->method('convertToEUR')
            ->willThrowException(new \Exception('Currency provider failure'));

        $calculator = new CommissionCalculator($binProviderMock, $currencyProviderMock);
        $transaction = '{"bin":"45717360","amount":"100.00","currency":"USD"}';

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Error calculating commission: Currency provider failure');
        $calculator->calculateCommission($transaction);
    }
}
