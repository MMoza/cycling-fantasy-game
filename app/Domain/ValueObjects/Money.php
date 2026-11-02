<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Importe monetario inmutable representado en céntimos enteros: los cálculos
 * financieros nunca se apoyan en aritmética de coma flotante.
 */
final readonly class Money
{
    private function __construct(public int $cents) {}

    public static function fromCents(int $cents): self
    {
        return new self($cents);
    }

    /**
     * Interpretación exacta de una representación decimal como "1234.56"
     * sin pasar por float.
     *
     * @throws InvalidArgumentException cuando el formato no es válido
     */
    public static function fromDecimalString(string $amount): self
    {
        $amount = trim($amount);

        if (preg_match('/^(-?)(\d+)(?:\.(\d{1,2}))?$/', $amount, $matches) !== 1) {
            throw new InvalidArgumentException("Importe monetario no válido: {$amount}");
        }

        $cents = ((int) $matches[2]) * 100 + (int) str_pad($matches[3] ?? '', 2, '0');

        return new self($matches[1] === '-' ? -$cents : $cents);
    }

    /**
     * Interpretación del resultado de un agregado SQL en céntimos, por ejemplo
     * SUM(ROUND(amount * 100)), compatible con MySQL y SQLite.
     *
     * @throws InvalidArgumentException cuando el valor no es un agregado válido
     */
    public static function fromCentsAggregate(mixed $raw): self
    {
        if ($raw === null) {
            return new self(0);
        }

        if (is_int($raw)) {
            return new self($raw);
        }

        if (is_float($raw)) {
            return new self((int) sprintf('%.0f', $raw));
        }

        $value = trim((string) $raw);

        if (preg_match('/^(-?)(\d+)(?:\.0+)?$/', $value, $matches) !== 1) {
            throw new InvalidArgumentException("Agregado monetario no válido: {$value}");
        }

        $cents = (int) $matches[2];

        return new self($matches[1] === '-' ? -$cents : $cents);
    }

    public function add(self $other): self
    {
        return new self($this->cents + $other->cents);
    }

    public function subtract(self $other): self
    {
        return new self($this->cents - $other->cents);
    }

    public function isNegative(): bool
    {
        return $this->cents < 0;
    }

    /**
     * Representación decimal con dos posiciones para serializar al frontend.
     */
    public function toDecimalString(): string
    {
        $sign = $this->cents < 0 ? '-' : '';
        $absolute = abs($this->cents);

        return sprintf('%s%d.%02d', $sign, intdiv($absolute, 100), $absolute % 100);
    }
}
