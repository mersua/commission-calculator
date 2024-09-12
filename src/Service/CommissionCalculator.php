<?php

declare(strict_types=1);

namespace App\Service;

use App\Contracts\BinProviderInterface;
use App\Contracts\CurrencyProviderInterface;
use App\Utils\CountryUtils;

class CommissionCalculator
{
    private BinProviderInterface $binProvider;
    private CurrencyProviderInterface $currencyProvider;
    private float $euCommissionRate;
    private float $nonEuCommissionRate;

    public function __construct(BinProviderInterface $binProvider, CurrencyProviderInterface $currencyProvider)
    {
        $this->binProvider = $binProvider;
        $this->currencyProvider = $currencyProvider;

        $this->euCommissionRate = (float) $_ENV['EU_COMMISSION_RATE'];
        $this->nonEuCommissionRate = (float) $_ENV['NON_EU_COMMISSION_RATE'];
    }

    /**
     * @throws \Throwable
     */
    public function calculateCommission(string $transaction): float
    {
        try {
            $transactionData = json_decode($transaction);

            if (!isset($transactionData->bin, $transactionData->amount, $transactionData->currency)) {
                throw new \Exception('Invalid transaction data format');
            }

            $bin = $transactionData->bin;
            $amount = (float) $transactionData->amount;
            $currency = strtoupper($transactionData->currency);

            $amountInEur = $this->currencyProvider->convertToEUR($amount, $currency);

            $countryCode = $this->binProvider->getBinCountryCode($bin);
            $isEu = CountryUtils::isEuCountry($countryCode);

            $commissionRate = $isEu ? $this->euCommissionRate : $this->nonEuCommissionRate;
            $commission = $amountInEur * $commissionRate;

            return ceil($commission * 100) / 100;

        } catch (\Exception $e) {
            throw new \Exception(sprintf('Error calculating commission: %s', $e->getMessage()));
        }
    }
}
