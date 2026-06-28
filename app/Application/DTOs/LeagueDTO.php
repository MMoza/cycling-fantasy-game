<?php

declare(strict_types=1);

namespace App\Application\DTOs;

readonly class LeagueDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public string $editionId,
        public string $editionName,
        public int $editionYear,
        public string $scoringSystemId,
        public string $scoringSystemName,
        public string $ownerId,
        public string $inviteCode,
        public int $memberCount,
        public int $maxPlayers,
        public bool $isPublic,
    ) {}
}
