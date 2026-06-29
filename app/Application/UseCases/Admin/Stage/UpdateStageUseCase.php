<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\Stage;

use App\Infrastructure\Persistence\Models\StageModel;

class UpdateStageUseCase
{
    public function execute(string $editionId, string $id, array $data): void
    {
        $stage = StageModel::where('edition_id', $editionId)->findOrFail($id);

        $stage->update($data);
    }
}
