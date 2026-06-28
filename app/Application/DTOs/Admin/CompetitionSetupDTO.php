<?php

declare(strict_types=1);

namespace App\Application\DTOs\Admin;

readonly class CompetitionSetupDTO
{
    public function __construct(
        public string $competitionId,
        public string $competitionName,
        public array $teams,
    ) {}
}
