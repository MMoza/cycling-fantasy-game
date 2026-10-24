<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use App\Application\DTOs\StageDTO;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

/**
 * All props of the Stages/Index page:
 * {league_id, league_name, competition, year, stages, predictionsPerStage, pointsPerStage}.
 */
readonly class StagesIndexDTO implements Arrayable
{
    /**
     * @param  Collection<int, StageDTO>  $stages
     */
    public function __construct(
        public string $leagueId,
        public string $leagueName,
        public string $competitionName,
        public int $year,
        public Collection $stages,
    ) {}

    public function toArray(): array
    {
        return [
            'league_id' => $this->leagueId,
            'league_name' => $this->leagueName,
            'competition' => $this->competitionName,
            'year' => $this->year,
            'stages' => $this->stages,
            'predictionsPerStage' => $this->stages
                ->filter(fn (StageDTO $stage) => $stage->hasPredictions)
                ->mapWithKeys(fn (StageDTO $stage) => [$stage->id => true]),
            'pointsPerStage' => $this->stages
                ->mapWithKeys(fn (StageDTO $stage) => [$stage->id => $stage->points]),
        ];
    }
}
