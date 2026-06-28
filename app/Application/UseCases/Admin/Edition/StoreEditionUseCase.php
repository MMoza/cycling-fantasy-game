<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\Edition;

use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Infrastructure\Persistence\Models\EditionModel;
use Illuminate\Support\Str;

class StoreEditionUseCase
{
    public function execute(string $competitionId, array $data): EditionModel
    {
        CompetitionModel::findOrFail($competitionId);

        return EditionModel::create([
            'id' => Str::uuid()->toString(),
            'competition_id' => $competitionId,
            'year' => $data['year'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'status' => 'upcoming',
        ]);
    }
}
