<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

enum EmailRecipients: string
{
    case AllUsers = 'all_users';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::AllUsers => 'Todos los usuarios',
            self::Custom => 'Destinatarios personalizados',
        };
    }
}
