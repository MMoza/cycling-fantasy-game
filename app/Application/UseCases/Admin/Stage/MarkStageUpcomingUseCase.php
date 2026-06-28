<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\Stage;

use App\Domain\ValueObjects\StageStatus;
use App\Infrastructure\Persistence\Models\StageModel;

class MarkStageUpcomingUseCase
{
    public function execute(string $editionId, string $id): void
    {
        $stage = StageModel::where('edition_id', $editionId)->findOrFail($id);
        $stage->update(['status' => StageStatus::Upcoming->value]);
    }
}
