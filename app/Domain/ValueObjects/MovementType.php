<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

/**
 * Tipo de movimiento financiero. Los importes se almacenan siempre como
 * valores positivos o cero: el signo contable lo determina el tipo
 * (balance = income_total - expense_total). Un reembolso se registra
 * como un ingreso.
 */
enum MovementType: string
{
    case Income = 'income';
    case Expense = 'expense';

    public function label(): string
    {
        return match ($this) {
            self::Income => 'Ingreso',
            self::Expense => 'Gasto',
        };
    }

    /**
     * Signo contable asociado al tipo de movimiento.
     */
    public function sign(): int
    {
        return match ($this) {
            self::Income => 1,
            self::Expense => -1,
        };
    }
}
