<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\Competition;

use App\Infrastructure\Persistence\Models\CompetitionModel;

class UpdateCompetitionUseCase
{
    public function execute(string $id, array $data): void
    {
        $competition = CompetitionModel::findOrFail($id);
        $competition->update($data);
    }
}
