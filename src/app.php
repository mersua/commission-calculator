<?php

declare(strict_types=1);

require 'vendor/autoload.php';

use App\Service\CommissionCalculator;
use App\Service\BinLookupService;
use App\Service\CurrencyConversionService;
use Dotenv\Dotenv;
use GuzzleHttp\Client;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

try {
    printf('Commission calculation started:%s', PHP_EOL);

    $transactions = file($argv[1]);

    if (false !== $transactions) {
        $client = new Client();
        $calculator = new CommissionCalculator(
            new BinLookupService($client, $_ENV['BIN_API_URL']),
            new CurrencyConversionService($client, $_ENV['CURRENCY_API_URL'], $_ENV['CURRENCY_API_KEY'])
        );

        foreach ($transactions as $transaction) {
            try {
                $result = $calculator->calculateCommission($transaction);
                printf('%s%s', $result, PHP_EOL);
            } catch (Exception $e) {
                printf('Error processing transaction: %s%s', $e->getMessage(), PHP_EOL);
            }
        }
    } else {
        printf('There is a problem with reading an input file: "%s"%s', $argv[1], PHP_EOL);
    }

    printf('Commission calculation ended!%s', PHP_EOL);
} catch (Exception $e) {
    printf('Fatal error: %s%s', $e->getMessage(), PHP_EOL);
    exit(1);
}
