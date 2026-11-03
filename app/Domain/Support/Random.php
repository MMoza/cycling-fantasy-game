<?php

declare(strict_types=1);

namespace App\Domain\Support;

/**
 * Generación de identificadores y códigos aleatorios con PHP puro: mantiene
 * la capa de Domain libre de helpers de Laravel.
 */
final class Random
{
    public static function uuid(): string
    {
        $bytes = random_bytes(16);

        $bytes[6] = chr((ord($bytes[6]) & 0x0F) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3F) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }

    public static function alnum(int $length): string
    {
        $charset = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $result = '';

        for ($i = 0; $i < $length; $i++) {
            $result .= $charset[random_int(0, strlen($charset) - 1)];
        }

        return $result;
    }
}
