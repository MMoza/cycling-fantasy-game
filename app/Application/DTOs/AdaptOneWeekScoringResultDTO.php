<?php

declare(strict_types=1);

namespace App\Application\DTOs;

/**
 * Result of adapting one-week race leagues to the OneWeek scoring system.
 */
readonly class AdaptOneWeekScoringResultDTO
{
    /**
     * @param  array<int, array{id: string, league: string, from: string, to: string}>  $updated
     * @param  array<int, array{id: string, league: string, system: string}>  $skipped
     * @param  array<int, array{id: string, league: string}>  $alreadyOneWeek
     */
    public function __construct(
        public int $year,
        public bool $dryRun,
        public array $updated,
        public array $skipped,
        public array $alreadyOneWeek,
    ) {}
}
