<?php

namespace App\Services;

/**
 * Tax identification validation for Latin American countries.
 *
 * - Argentina: CUIT/CUIL (11 digits, check digit mod-11).
 * - Chile:     RUT (7-8 digits + check digit [0-9K], mod-11).
 * - México:    RFC (personas físicas y morales) — formato + homoclave.
 * - Colombia:  NIT / cédula — formato.
 * - Perú:      RUC (11 dígitos) / DNI (8 dígitos) — formato.
 * - Uruguay:   RUT (12 dígitos) / cédula (8 dígitos) — formato.
 *
 * Fuentes: AFIP (CUIT), SII (RUT), SAT (RFC), DIAN (NIT), SUNAT (RUC), DGI (RUT).
 */
class LatinAmericanTaxIdValidator
{
    public function isValid(string $country, string $taxId): bool
    {
        $taxId = $this->normalize($taxId);

        return match (strtoupper($country)) {
            'AR' => $this->validCuit($taxId),
            'CL' => $this->validRut($taxId),
            'MX' => $this->validRfc($taxId),
            'CO' => preg_match('/^\d{6,10}$/', $taxId) === 1,
            'PE' => $this->validPeru($taxId),
            'UY' => $this->validUruguay($taxId),
            default => false,
        };
    }

    public function normalize(string $taxId): string
    {
        return strtoupper(preg_replace('/[\s\-._]/', '', trim($taxId)) ?? '');
    }

    private function validCuit(string $taxId): bool
    {
        if (preg_match('/^(20|23|24|27|30|33|34)\d{8}\d$/', $taxId) !== 1) {
            return false;
        }

        $weights = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        foreach ($weights as $i => $weight) {
            $sum += (int) $taxId[$i] * $weight;
        }

        $mod = $sum % 11;
        $check = match ($mod) {
            0 => 0,
            1 => 9,
            default => 11 - $mod,
        };

        return (int) $taxId[10] === $check;
    }

    private function validRut(string $taxId): bool
    {
        if (preg_match('/^\d{7,8}[0-9K]$/', $taxId) !== 1) {
            return false;
        }

        $body = substr($taxId, 0, -1);
        $check = $taxId[-1];

        $sum = 0;
        $multiplier = 2;
        foreach (array_reverse(str_split($body)) as $digit) {
            $sum += (int) $digit * $multiplier;
            $multiplier = $multiplier === 7 ? 2 : $multiplier + 1;
        }

        $expected = 11 - ($sum % 11);
        $expected = match ($expected) {
            11 => '0',
            10 => 'K',
            default => (string) $expected,
        };

        return $check === $expected;
    }

    private function validRfc(string $taxId): bool
    {
        // Persona física: AAAA 000000 XXX ; persona moral: AAA000000XXX
        return preg_match('/^[A-ZÑ&]{3,4}\d{6}[A-Z0-9]{3}$/', $taxId) === 1;
    }

    private function validPeru(string $taxId): bool
    {
        return preg_match('/^(10|15|17|20)\d{9}$/', $taxId) === 1
            || preg_match('/^\d{8}$/', $taxId) === 1; // DNI
    }

    private function validUruguay(string $taxId): bool
    {
        return preg_match('/^\d{12}$/', $taxId) === 1     // RUT
            || preg_match('/^\d{8}$/', $taxId) === 1;    // cédula
    }
}
