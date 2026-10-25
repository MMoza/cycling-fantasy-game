<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use Illuminate\Contracts\Support\Arrayable;

/**
 * All props of the Classification/Index page.
 */
readonly class ClassificationPageDTO implements Arrayable
{
    /**
     * @param  array<int, mixed>  $stages
     * @param  array<int, ClassificationLeaderboardEntryViewDTO>  $generalLeaderboard
     * @param  array<int, mixed>  $stageLeaderboards
     * @param  array<int, mixed>  $generalDetails
     * @param  array<string, mixed>  $userPosition
     */
    public function __construct(
        public string $leagueId,
        public string $leagueName,
        public array $stages,
        public array $generalLeaderboard,
        public array $stageLeaderboards,
        public ?string $lastScoredStageId,
        public array $generalDetails,
        public array $userPosition,
    ) {}

    public function toArray(): array
    {
        return [
            'league_id' => $this->leagueId,
            'league_name' => $this->leagueName,
            'stages' => $this->stages,
            'general_leaderboard' => $this->generalLeaderboard,
            'stage_leaderboards' => $this->stageLeaderboards,
            'last_scored_stage_id' => $this->lastScoredStageId,
            'user_position' => $this->userPosition,
            'general_details' => $this->generalDetails,
        ];
    }
}
