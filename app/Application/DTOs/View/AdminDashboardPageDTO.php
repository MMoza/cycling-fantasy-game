<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use Illuminate\Contracts\Support\Arrayable;

/**
 * All props of the Admin/Dashboard page. The counts are exposed to the page
 * inside the `stats` object, so toArray() keeps that wrapper.
 */
readonly class AdminDashboardPageDTO implements Arrayable
{
    public function __construct(
        public int $competitionsCount,
        public int $editionsCount,
        public int $stagesCount,
        public int $usersCount,
        public int $activeCompetitions,
    ) {}

    public function toArray(): array
    {
        return [
            'stats' => [
                'competitions_count' => $this->competitionsCount,
                'editions_count' => $this->editionsCount,
                'stages_count' => $this->stagesCount,
                'users_count' => $this->usersCount,
                'active_competitions' => $this->activeCompetitions,
            ],
        ];
    }
}
