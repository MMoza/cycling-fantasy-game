<?php

declare(strict_types=1);

namespace App\Application\DTOs\Admin;

readonly class FinalClassificationDTO
{
    public function __construct(
        public string $editionId,
        public string $category,
        public int $position,
        public ?string $riderId,
        public ?string $teamId,
    ) {}
}

readonly class UpdateFinalClassificationsDTO
{
    public function __construct(
        public array $classifications,
    ) {}
}
