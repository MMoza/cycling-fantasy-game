<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use Illuminate\Contracts\Support\Arrayable;

/**
 * All props of the Season/Index page:
 * {year, competitions, user_joined_count, total_competitions}.
 */
readonly class SeasonIndexPageDTO implements Arrayable
{
    /**
     * @param  array<int, mixed>  $competitions
     */
    public function __construct(
        public int $year,
        public array $competitions,
        public int $userJoinedCount,
        public int $totalCompetitions,
    ) {}

    public function toArray(): array
    {
        return [
            'year' => $this->year,
            'competitions' => $this->competitions,
            'user_joined_count' => $this->userJoinedCount,
            'total_competitions' => $this->totalCompetitions,
        ];
    }
}
