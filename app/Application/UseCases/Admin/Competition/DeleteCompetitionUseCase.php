<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\Competition;

use App\Infrastructure\Persistence\Models\CompetitionModel;
use Illuminate\Support\Facades\Storage;

class DeleteCompetitionUseCase
{
    public function execute(string $id): void
    {
        $competition = CompetitionModel::with('editions.leagues', 'editions.stages', 'editions.participants')->findOrFail($id);

        if ($competition->cover_image) {
            Storage::disk('s3')->delete($competition->cover_image);
        }

        if ($competition->logo_image) {
            Storage::disk('s3')->delete($competition->logo_image);
        }

        $competition->editions->each(function ($edition): void {
            $edition->participants()->delete();
            $edition->stages()->delete();
            $edition->leagues()->delete();
            $edition->delete();
        });

        $competition->delete();
    }
}
