<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use Illuminate\Contracts\Support\Arrayable;

/**
 * All props of the Predictions/PreRace page.
 */
readonly class PreRaceFormDTO implements Arrayable
{
    public function __construct(
        public string $leagueId,
        public string $leagueName,
        public string $competitionName,
        public int $competitionYear,
        public bool $isLocked,
        public mixed $predictions,
        public mixed $availableRiders,
        public mixed $availableTeams,
        public mixed $scoringSystem,
    ) {}

    public function toArray(): array
    {
        return [
            'league_id' => $this->leagueId,
            'league_name' => $this->leagueName,
            'competition' => [
                'name' => $this->competitionName,
                'year' => $this->competitionYear,
            ],
            'is_locked' => $this->isLocked,
            'predictions' => $this->predictions,
            'availableRiders' => $this->availableRiders,
            'availableTeams' => $this->availableTeams,
            'scoring_system' => $this->scoringSystem,
        ];
    }
}
