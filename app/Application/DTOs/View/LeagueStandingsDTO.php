<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

/**
 * Result of the league standings computation (not serialised directly).
 */
readonly class LeagueStandingsDTO
{
    /**
     * @param  LeaderboardEntryViewDTO[]  $entries
     */
    public function __construct(
        public array $entries,
        public UserPositionViewDTO $userPosition,
    ) {}
}
