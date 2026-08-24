<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Validates European VAT numbers (IVA/VAT) per country format and, when the
 * VIES webservice is reachable, performs a live validity + name check.
 *
 * VIES REST endpoint: https://ec.europa.eu/taxation_customs/vies/rest-api/check-vat
 * Falls back to format-only validation when the network is unavailable, so the
 * application keeps working offline.
 */
class EuVatValidator
{
    private const EU_COUNTRIES = [
        'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'EL', 'ES', 'FI', 'FR',
        'HR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO',
        'SE', 'SI', 'SK',
    ];

    private const FORMATS = [
        'AT' => '/^U\d{8}$/',
        'BE' => '/^(0\d{9}|1\d{9})$/',
        'BG' => '/^\d{9,10}$/',
        'CY' => '/^\d{8}[A-Z]$/',
        'CZ' => '/^\d{8,10}$/',
        'DE' => '/^\d{9}$/',
        'DK' => '/^\d{8}$/',
        'EE' => '/^\d{9}$/',
        'EL' => '/^\d{9}$/',
        'ES' => '/^[A-Z0-9]\d{7}[0-9A-J]$/',
        'FI' => '/^\d{8}$/',
        'FR' => '/^[0-9A-Z]{2}\d{9}$/',
        'HR' => '/^\d{11}$/',
        'HU' => '/^\d{8}$/',
        'IE' => '/^(\d{7}[A-W]|\d{6}[A-W]{2})$/',
        'IT' => '/^\d{11}$/',
        'LT' => '/^\d{9}(\d{3})?$/',
        'LU' => '/^\d{8}$/',
        'LV' => '/^\d{11}$/',
        'MT' => '/^\d{8}$/',
        'NL' => '/^\d{9}B\d{2}$/',
        'PL' => '/^\d{10}$/',
        'PT' => '/^\d{9}$/',
        'RO' => '/^\d{8,10}$/',
        'SE' => '/^\d{12}$/',
        'SI' => '/^\d{8}$/',
        'SK' => '/^\d{10}$/',
    ];

    public function __construct(private readonly SpanishTaxIdValidator $spanishTaxId) {}

    public function isValidCountry(string $country): bool
    {
        return in_array(strtoupper($country), self::EU_COUNTRIES, true);
    }

    /**
     * Format-validates a VAT number in the given country. The number should be
     * passed without the country prefix (VIES style), but a prefixed number is
     * stripped automatically.
     */
    public function hasValidFormat(string $country, string $vatNumber): bool
    {
        $country = strtoupper($country);

        if (! $this->isValidCountry($country)) {
            return false;
        }

        $vatNumber = $this->stripCountryPrefix($vatNumber, $country);

        if ($country === 'ES') {
            return $this->spanishTaxId->isValid($vatNumber);
        }

        $pattern = self::FORMATS[$country];

        return preg_match($pattern, $vatNumber) === 1;
    }

    /**
     * Full validation: format first, then a live VIES lookup when reachable.
     *
     * @return array{valid: bool, checked_via_vies: bool, name?: string, address?: string}
     */
    public function validate(string $country, string $vatNumber, bool $vies = true): array
    {
        $country = strtoupper($country);
        $vatNumber = $this->stripCountryPrefix($vatNumber, $country);

        if (! $this->hasValidFormat($country, $vatNumber)) {
            return ['valid' => false, 'checked_via_vies' => false];
        }

        if (! $vies) {
            return ['valid' => true, 'checked_via_vies' => false];
        }

        return $this->checkVies($country, $vatNumber);
    }

    public function stripCountryPrefix(string $vatNumber, ?string $country = null): string
    {
        $vatNumber = strtoupper(preg_replace('/[\s\-._]/', '', trim($vatNumber)) ?? '');

        if ($country !== null && str_starts_with($vatNumber, $country)) {
            return substr($vatNumber, 2);
        }

        // Strip any two-letter EU prefix if present.
        $candidate = substr($vatNumber, 0, 2);
        if (in_array($candidate, self::EU_COUNTRIES, true) && strlen($vatNumber) > 2) {
            return substr($vatNumber, 2);
        }

        return $vatNumber;
    }

    /**
     * @return array{valid: bool, checked_via_vies: bool, name?: string, address?: string}
     */
    private function checkVies(string $country, string $vatNumber): array
    {
        try {
            $response = Http::connectTimeout(5)
                ->timeout(10)
                ->get('https://ec.europa.eu/taxation_customs/vies/rest-api/check-vat', [
                    'countryCode' => $country,
                    'vatNumber' => $vatNumber,
                ]);

            if ($response->failed()) {
                return $this->formatOnlyResult();
            }

            $data = $response->json();

            $valid = filter_var($data['valid'] ?? false, FILTER_VALIDATE_BOOL);

            return array_filter([
                'valid' => $valid,
                'checked_via_vies' => true,
                'name' => $data['name'] ?? null,
                'address' => $data['address'] ?? null,
            ]);
        } catch (\Throwable) {
            // VIES unreachable: fall back to format validation.
            return $this->formatOnlyResult();
        }
    }

    /**
     * @return array{valid: bool, checked_via_vies: bool}
     */
    private function formatOnlyResult(): array
    {
        return ['valid' => true, 'checked_via_vies' => false];
    }
}
