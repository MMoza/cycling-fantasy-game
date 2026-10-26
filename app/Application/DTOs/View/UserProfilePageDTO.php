<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use Illuminate\Contracts\Support\Arrayable;

/**
 * All props of the Users/Show page (member profile inside a league).
 */
readonly class UserProfilePageDTO implements Arrayable
{
    public function __construct(
        public string $leagueId,
        public string $leagueName,
        public bool $competitionStarted,
        public bool $hasStagePredictions,
        public mixed $user,
        public mixed $globalStats,
        public mixed $pointsHistory,
        public mixed $preRacePredictions,
        public mixed $stageDetails,
        public mixed $badges = [],
    ) {}

    public function toArray(): array
    {
        return [
            'league_id' => $this->leagueId,
            'league_name' => $this->leagueName,
            'competition_started' => $this->competitionStarted,
            'has_stage_predictions' => $this->hasStagePredictions,
            'user' => $this->user,
            'global_stats' => $this->globalStats,
            'points_history' => $this->pointsHistory,
            'pre_race_predictions' => $this->preRacePredictions,
            'stage_details' => $this->stageDetails,
            'badges' => $this->badges,
        ];
    }
}
