<?php

declare(strict_types=1);

require 'vendor/autoload.php';

use App\Service\CommissionCalculator;
use App\Service\BinLookupService;
use App\Service\CurrencyConversionService;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

try {
    printf('%s%s', 'Commission calculation started:', PHP_EOL);

    $transactions = file($argv[1]);

    if (false !== $transactions) {
        $calculator = new CommissionCalculator(
            new BinLookupService($_ENV['BIN_API_URL']),
            new CurrencyConversionService($_ENV['CURRENCY_API_URL'], $_ENV['CURRENCY_API_KEY'])
        );

        foreach ($transactions as $transaction) {
            try {
                $result = $calculator->calculateCommission($transaction);
                printf('%s%s', $result, PHP_EOL);
            } catch (Exception $e) {
                printf('%s %s%s', 'Error processing transaction:', $e->getMessage(), PHP_EOL);
            }
        }
    } else {
        printf('%s "%s"%s', 'There is a problem with reading an input file:', $argv[1], PHP_EOL);
    }

    printf('%s%s', 'Commission calculation ended!', PHP_EOL);
} catch (Exception $e) {
    printf('%s %s%s', 'Fatal error:', $e->getMessage(), PHP_EOL);
    exit(1);
}
