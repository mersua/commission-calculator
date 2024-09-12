<?php

declare(strict_types=1);

namespace App\Utils;

class CountryUtils
{
    private static array $euCountries = [
        'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GR', 'HR',
        'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PO', 'PT', 'RO', 'SE', 'SI', 'SK'
    ];

    public static function isEuCountry(string $countryCode): bool
    {
        return in_array($countryCode, self::$euCountries, true);
    }
}
