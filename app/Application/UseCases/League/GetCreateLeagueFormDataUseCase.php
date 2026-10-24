<?php

declare(strict_types=1);

namespace App\Application\UseCases\League;

use App\Application\DTOs\View\EditionOptionViewDTO;
use App\Application\DTOs\View\LeagueFormDataDTO;
use App\Application\DTOs\View\ScoringSystemOptionViewDTO;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\ScoringSystemModel;

class GetCreateLeagueFormDataUseCase
{
    public function execute(): LeagueFormDataDTO
    {
        $editions = EditionModel::with('competition')->get();
        $scoringSystems = ScoringSystemModel::all();

        return new LeagueFormDataDTO(
            editions: $editions->filter->competition->values()
                ->map(fn (EditionModel $edition) => EditionOptionViewDTO::fromModel($edition))
                ->values()
                ->all(),
            scoringSystems: $scoringSystems
                ->map(fn (ScoringSystemModel $scoringSystem) => ScoringSystemOptionViewDTO::fromModel($scoringSystem))
                ->values()
                ->all(),
        );
    }
}
