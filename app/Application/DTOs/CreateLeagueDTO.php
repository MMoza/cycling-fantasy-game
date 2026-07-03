<?php

declare(strict_types=1);

namespace App\Application\DTOs;

readonly class CreateLeagueDTO
{
    public function __construct(
        public string $name,
        public string $editionId,
        public string $scoringSystemId,
        public bool $isPublic,
        public bool $isOfficial = false,
    ) {}
}
