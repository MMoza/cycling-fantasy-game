<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use Illuminate\Contracts\Support\Arrayable;

/**
 * All props of the Season/Classification page:
 * {year, aggregated_leaderboard, per_competition, by_type}.
 */
readonly class SeasonClassificationPageDTO implements Arrayable
{
    /**
     * @param  array<int, mixed>  $aggregatedLeaderboard
     * @param  array<int, mixed>  $perCompetition
     * @param  array<int, mixed>  $byType
     */
    public function __construct(
        public int $year,
        public array $aggregatedLeaderboard,
        public array $perCompetition,
        public array $byType,
    ) {}

    public function toArray(): array
    {
        return [
            'year' => $this->year,
            'aggregated_leaderboard' => $this->aggregatedLeaderboard,
            'per_competition' => $this->perCompetition,
            'by_type' => $this->byType,
        ];
    }
}
