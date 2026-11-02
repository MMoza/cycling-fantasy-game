<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

/**
 * Categorías de movimiento financiero. No persisten en base de datos ni
 * tienen CRUD: son un enum de dominio y cada categoría pertenece a un tipo
 * de movimiento concreto (la categoría "other" existe para ambos tipos).
 */
enum MovementCategory: string
{
    case Hosting = 'hosting';
    case Domains = 'domains';
    case Software = 'software';
    case Marketing = 'marketing';
    case Sponsorships = 'sponsorships';
    case Donations = 'donations';
    case Advertising = 'advertising';
    case Other = 'other';

    public function label(MovementType $type): string
    {
        return match ($this) {
            self::Hosting => 'Infraestructura',
            self::Domains => 'Dominios',
            self::Software => 'APIs y software',
            self::Marketing => 'Marketing',
            self::Sponsorships => 'Patrocinios',
            self::Donations => 'Donaciones',
            self::Advertising => 'Publicidad',
            self::Other => match ($type) {
                MovementType::Expense => 'Otros gastos',
                MovementType::Income => 'Otros ingresos',
            },
        };
    }

    /**
     * Indica si la categoría puede usarse con el tipo de movimiento dado.
     */
    public function supports(MovementType $type): bool
    {
        return match ($this) {
            self::Hosting, self::Domains, self::Software, self::Marketing => $type === MovementType::Expense,
            self::Sponsorships, self::Donations, self::Advertising => $type === MovementType::Income,
            self::Other => true,
        };
    }

    /**
     * Categorías disponibles para un tipo de movimiento.
     *
     * @return list<self>
     */
    public static function forType(MovementType $type): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $category): bool => $category->supports($type),
        ));
    }
}
