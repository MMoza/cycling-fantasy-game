<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\ValueObjects\BadgeCode;
use App\Domain\ValueObjects\BadgeMode;

/**
 * Definición de un badge: cómo se llama, cómo se consigue (modo) y sus niveles.
 */
readonly class BadgeDefinition
{
    /**
     * @param  array<int, int>  $levels  tiers alcanzables; en modo `once` el
     *                                   objetivo es el único nivel (y además
     *                                   se guarda progreso parcial)
     */
    public function __construct(
        public BadgeCode $code,
        public string $name,
        public string $description,
        public BadgeMode $mode,
        public array $levels,
        public string $category,
    ) {}

    public function svg(): string
    {
        return '/badges/'.$this->code->value.'.svg';
    }

    public function target(): int
    {
        return $this->levels === [] ? 0 : (int) max($this->levels);
    }

    public function levelFor(int $value): int
    {
        $level = 0;

        foreach ($this->levels as $tier) {
            if ($value >= $tier) {
                $level = $tier;
            }
        }

        return $level;
    }

    public function nextLevel(int $value): ?int
    {
        foreach ($this->levels as $tier) {
            if ($value < $tier) {
                return $tier;
            }
        }

        return null;
    }
}
