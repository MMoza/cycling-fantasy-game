<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use Illuminate\Contracts\Support\Arrayable;

/**
 * All props of the Leagues/Show page. The keys of toArray() are the Inertia
 * prop names and must stay stable: partial reloads filter on them.
 */
readonly class LeagueDashboardDTO implements Arrayable
{
    /**
     * @param  SeasonLeagueViewDTO[]  $seasonLeagues
     * @param  StageSummaryViewDTO[]  $stages
     * @param  LeaderboardEntryViewDTO[]  $leaderboard
     * @param  ActivityLogViewDTO[]  $activityLogs
     */
    public function __construct(
        public string $leagueId,
        public array $seasonLeagues,
        public LeagueViewDTO $league,
        public ?NextStageViewDTO $nextStage,
        public UserPositionViewDTO $userPosition,
        public array $stages,
        public array $leaderboard,
        public array $activityLogs,
    ) {}

    public function toArray(): array
    {
        return [
            'league_id' => $this->leagueId,
            'season_leagues' => $this->seasonLeagues,
            'league' => $this->league,
            'next_stage' => $this->nextStage,
            'user_position' => $this->userPosition,
            'stages' => $this->stages,
            'leaderboard' => $this->leaderboard,
            'activity_logs' => $this->activityLogs,
        ];
    }
}
