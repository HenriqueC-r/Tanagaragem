<?php

namespace App\Support;

class Document
{
    /** CPF: só os 11 dígitos. */
    public static function cpf(?string $value): string
    {
        return preg_replace('/\D/', '', (string) $value);
    }

    /** CNPJ (numérico ou alfanumérico): só letras A-Z maiúsculas e números. */
    public static function cnpj(?string $value): string
    {
        return preg_replace('/[^0-9A-Z]/', '', strtoupper((string) $value));
    }

    public static function isValidCpf(?string $value): bool
    {
        $cpf = self::cpf($value);

        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $sum = 0;
            for ($i = 0; $i < $t; $i++) {
                $sum += (int) $cpf[$i] * (($t + 1) - $i);
            }
            $digit = ((10 * $sum) % 11) % 10;

            if ((int) $cpf[$t] !== $digit) {
                return false;
            }
        }

        return true;
    }

    public static function isValidCnpj(?string $value): bool
    {
        $cnpj = self::cnpj($value);

        // 12 caracteres (números ou letras) + 2 dígitos verificadores numéricos
        if (! preg_match('/^[0-9A-Z]{12}[0-9]{2}$/', $cnpj)) {
            return false;
        }

        if (preg_match('/^(\d)\1{13}$/', $cnpj)) {
            return false;
        }

        $dv = function (string $base): int {
            $weights = strlen($base) === 12
                ? [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]
                : [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

            $sum = 0;
            foreach (str_split($base) as $i => $char) {
                $sum += (ord($char) - 48) * $weights[$i]; // valor = ASCII - 48
            }

            $rest = $sum % 11;

            return $rest < 2 ? 0 : 11 - $rest;
        };

        $first = $dv(substr($cnpj, 0, 12));
        $second = $dv(substr($cnpj, 0, 12) . $first);

        return (int) $cnpj[12] === $first && (int) $cnpj[13] === $second;
    }
}
