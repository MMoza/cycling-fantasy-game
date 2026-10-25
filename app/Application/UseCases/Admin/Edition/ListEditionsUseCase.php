<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\Edition;

use App\Application\DTOs\Admin\EditionDTO;
use App\Application\DTOs\View\AdminEditionsListPageDTO;
use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Infrastructure\Persistence\Models\EditionModel;

class ListEditionsUseCase
{
    public function execute(string $competitionId): AdminEditionsListPageDTO
    {
        $competition = CompetitionModel::findOrFail($competitionId);

        $editions = EditionModel::where('competition_id', $competitionId)
            ->orderBy('year', 'desc')
            ->get()
            ->map(fn ($e) => new EditionDTO(
                id: $e->id,
                year: $e->year,
                startDate: $e->start_date->format('Y-m-d'),
                endDate: $e->end_date->format('Y-m-d'),
                status: $e->status->label(),
            ));

        return new AdminEditionsListPageDTO(
            competition: ['id' => $competition->id, 'name' => $competition->name],
            editions: $editions,
        );
    }
}
