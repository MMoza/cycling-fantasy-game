<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\Edition;

use App\Infrastructure\Persistence\Models\EditionModel;

class UpdateEditionUseCase
{
    public function execute(string $competitionId, string $id, array $data): void
    {
        $edition = EditionModel::where('competition_id', $competitionId)->findOrFail($id);
        $edition->update($data);
    }
}
