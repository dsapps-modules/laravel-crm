<?php

namespace DsApps\LaravelCrm\Support;

final class BrazilianDocument
{
    public static function digits(?string $value): ?string
    {
        if ($value === null) return null;
        $digits = preg_replace('/\D+/', '', $value);
        return $digits === '' ? null : $digits;
    }

    public static function valid(string $type, ?string $value): bool
    {
        $digits = self::digits($value);
        if ($type === 'cpf') return $digits !== null && strlen($digits) === 11 && self::validCpf($digits);
        if ($type === 'cnpj') return $digits !== null && strlen($digits) === 14 && self::validCnpj($digits);
        return false;
    }

    private static function validCpf(string $value): bool
    {
        if (preg_match('/^(\d)\1{10}$/', $value)) return false;
        for ($length = 9; $length <= 10; $length++) {
            $sum = 0;
            for ($index = 0; $index < $length; $index++) $sum += (int) $value[$index] * (($length + 1) - $index);
            $digit = ($sum * 10) % 11;
            if ($digit === 10) $digit = 0;
            if ($digit !== (int) $value[$length]) return false;
        }
        return true;
    }

    private static function validCnpj(string $value): bool
    {
        if (preg_match('/^(\d)\1{13}$/', $value)) return false;
        foreach ([12, 13] as $length) {
            $weights = $length === 12 ? [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2] : [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
            $sum = 0;
            foreach ($weights as $index => $weight) $sum += (int) $value[$index] * $weight;
            $digit = $sum % 11 < 2 ? 0 : 11 - ($sum % 11);
            if ($digit !== (int) $value[$length]) return false;
        }
        return true;
    }
}
