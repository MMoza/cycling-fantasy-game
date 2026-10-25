<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use App\Application\DTOs\StageDetailDTO;
use Illuminate\Contracts\Support\Arrayable;

/**
 * All props of the Stages/Show page.
 */
readonly class StageShowDTO implements Arrayable
{
    /**
     * @param  array<string, mixed>  $navigation
     * @param  array<int, mixed>  $stageRules
     */
    public function __construct(
        public string $leagueId,
        public string $leagueName,
        public StageDetailDTO $stage,
        public bool $isFinished,
        public bool $isLocked,
        public mixed $predictions,
        public mixed $stageResults,
        public mixed $stageClassification,
        public array $navigation,
        public mixed $allStages,
        public mixed $availableRiders,
        public mixed $availableTeams,
        public ?string $pcsSlug,
        public int $editionYear,
        public array $stageRules,
        public int $totalPossiblePoints,
    ) {}

    public function toArray(): array
    {
        return [
            'league_id' => $this->leagueId,
            'stage' => $this->stage,
            'is_finished' => $this->isFinished,
            'is_locked' => $this->isLocked,
            'predictions' => $this->predictions,
            'stage_results' => $this->stageResults,
            'stage_classification' => $this->stageClassification,
            'league_name' => $this->leagueName,
            'navigation' => $this->navigation,
            'all_stages' => $this->allStages,
            'availableRiders' => $this->availableRiders,
            'availableTeams' => $this->availableTeams,
            'pcs_slug' => $this->pcsSlug,
            'edition_year' => $this->editionYear,
            'stage_rules' => $this->stageRules,
            'total_possible_points' => $this->totalPossiblePoints,
        ];
    }
}
