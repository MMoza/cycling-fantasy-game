<?php

declare(strict_types=1);

namespace App\Application\DTOs;

readonly class ClassificationEntryDTO
{
    public function __construct(
        public int $rank,
        public string $userName,
        public string $userId,
        public int $points,
        public int $behindLeader,
        public bool $isCurrentUser,
    ) {}
}

readonly class ClassificationDTO
{
    public function __construct(
        public string $leagueId,
        public string $leagueName,
        public array $leaderboard,
        public array $userPosition,
    ) {}
}
