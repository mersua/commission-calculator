<?php

declare(strict_types=1);

namespace App\Contracts;

interface CurrencyProviderInterface
{
    public function convertToEUR(float $amount, string $currency): float;
}
