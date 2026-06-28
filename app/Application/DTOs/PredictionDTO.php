<?php

declare(strict_types=1);

namespace App\Application\DTOs;

readonly class PredictionDTO
{
    public function __construct(
        public string $category,
        public array $value,
        public ?string $lockedAt,
    ) {}
}
