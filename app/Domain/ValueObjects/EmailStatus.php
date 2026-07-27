<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

enum EmailStatus: string
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Sent = 'sent';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Borrador',
            self::Scheduled => 'Programado',
            self::Sent => 'Enviado',
            self::Failed => 'Error',
        };
    }

    public function isEditable(): bool
    {
        return $this === self::Draft || $this === self::Scheduled;
    }

    public function isDeletable(): bool
    {
        return $this !== self::Sent;
    }
}
