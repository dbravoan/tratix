<?php

namespace App\Services;

/**
 * Validates Spanish tax identification numbers:
 * - NIF (DNI + control letter) for individuals
 * - NIE (foreigner identity number) for individuals
 * - CIF (company tax code) for legal persons / societies
 *
 * Source of rules: Anexo II y IV del Real Decreto 1065/2007 (Reglamento
 * General de las actuaciones y los procedimientos de gestión e inspección
 * tributaria), art. 10-13.
 */
class SpanishTaxIdValidator
{
    private const DNI_LETTERS = 'TRWAGMYFPDXBNJZSQVHLCKE';

    private const CIF_NUMERIC_LETTERS = 'JABCDEFGHI';

    /** Legal-person prefixes allowed for CIF. */
    private const CIF_PREFIXES = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'N', 'P', 'Q', 'R', 'S', 'U', 'V', 'W'];

    /** NIE leading letters and their numeric mapping. */
    private const NIE_MAP = ['X' => 0, 'Y' => 1, 'Z' => 2];

    public function isValid(string $taxId): bool
    {
        $taxId = $this->normalize($taxId);

        if (strlen($taxId) !== 9) {
            return false;
        }

        return match (true) {
            $this->isNif($taxId) => $this->validNif($taxId),
            $this->isNie($taxId) => $this->validNie($taxId),
            $this->isCif($taxId) => $this->validCif($taxId),
            default => false,
        };
    }

    public function isValidNif(string $taxId): bool
    {
        return $this->isNif($this->normalize($taxId)) && $this->validNif($this->normalize($taxId));
    }

    public function isValidNie(string $taxId): bool
    {
        return $this->isNie($this->normalize($taxId)) && $this->validNie($this->normalize($taxId));
    }

    public function isValidCif(string $taxId): bool
    {
        return $this->isCif($this->normalize($taxId)) && $this->validCif($this->normalize($taxId));
    }

    public function normalize(string $taxId): string
    {
        return strtoupper(preg_replace('/[\s\-._]/', '', trim($taxId)) ?? '');
    }

    private function isNif(string $taxId): bool
    {
        return preg_match('/^\d{8}[A-Z]$/', $taxId) === 1;
    }

    private function isNie(string $taxId): bool
    {
        return preg_match('/^[XYZ]\d{7}[A-Z]$/', $taxId) === 1;
    }

    private function isCif(string $taxId): bool
    {
        return in_array($taxId[0], self::CIF_PREFIXES, true)
            && preg_match('/^[A-Z]\d{7}[0-9A-J]$/', $taxId) === 1;
    }

    private function validNif(string $taxId): bool
    {
        $expected = self::DNI_LETTERS[(int) substr($taxId, 0, 8) % 23];

        return $taxId[8] === $expected;
    }

    private function validNie(string $taxId): bool
    {
        $numeric = self::NIE_MAP[$taxId[0]].substr($taxId, 1, 7);
        $expected = self::DNI_LETTERS[(int) $numeric % 23];

        return $taxId[8] === $expected;
    }

    private function validCif(string $taxId): bool
    {
        $sum = $this->cifSum(substr($taxId, 1, 7));
        $control = (10 - ($sum % 10)) % 10;

        $last = $taxId[8];
        $isNumeric = ctype_digit($last);

        if ($isNumeric) {
            return (int) $last === $control;
        }

        return $last === self::CIF_NUMERIC_LETTERS[$control];
    }

    private function cifSum(string $digits): int
    {
        $sum = 0;
        foreach (str_split($digits) as $index => $digit) {
            if (($index + 1) % 2 === 1) {
                $sum += (int) $digit;

                continue;
            }
            $sum += array_sum(str_split((string) ((int) $digit * 2)));
        }

        return $sum;
    }
}
