<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\Stage;

use App\Domain\ValueObjects\StageStatus;
use App\Infrastructure\Persistence\Models\StageModel;

class MarkStageFinishedUseCase
{
    public function execute(string $editionId, string $id): void
    {
        $stage = StageModel::where('edition_id', $editionId)->findOrFail($id);

        if ($stage->status === StageStatus::Finished) {
            return;
        }

        $stage->update(['status' => StageStatus::Finished->value]);
    }
}
