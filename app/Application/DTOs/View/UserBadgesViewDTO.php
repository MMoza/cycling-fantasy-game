<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Estado de badges de un usuario para "Mis badges": {earned, pending}.
 */
readonly class UserBadgesViewDTO implements Arrayable
{
    /**
     * @param  array<int, array<string, mixed>>  $earned
     * @param  array<int, array<string, mixed>>  $pending
     */
    public function __construct(
        public array $earned,
        public array $pending,
    ) {}

    public function toArray(): array
    {
        return [
            'earned' => $this->earned,
            'pending' => $this->pending,
        ];
    }
}
