<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\FinalClassification;

use App\Application\Exceptions\ApplicationException;
use App\Application\Services\PreRaceScoringService;
use App\Domain\ValueObjects\EditionStatus;
use App\Domain\ValueObjects\StageStatus;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\FinalClassificationModel;
use App\Infrastructure\Persistence\Models\StageModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UpdateFinalClassificationsUseCase
{
    public function __construct(
        private readonly PreRaceScoringService $preRaceScoring,
    ) {}

    public function execute(string $editionId, array $classifications): array
    {
        $edition = EditionModel::findOrFail($editionId);

        $pendingStages = StageModel::where('edition_id', $editionId)
            ->where('type', '!=', 'rest')
            ->where('status', '!=', StageStatus::Finished)
            ->count();

        if ($pendingStages > 0) {
            throw new ApplicationException(
                "No se pueden guardar las clasificaciones finales: quedan {$pendingStages} etapas sin finalizar"
            );
        }

        DB::transaction(function () use ($editionId, $classifications, $edition): void {
            FinalClassificationModel::where('edition_id', $editionId)->delete();

            $categories = [
                'gc_top_5',
                'points_winner',
                'mountains_winner',
                'youth_winner',
            ];

            foreach ($categories as $category) {
                $items = $classifications[$category] ?? [];

                foreach ($items as $position => $riderId) {
                    FinalClassificationModel::create([
                        'id' => Str::uuid()->toString(),
                        'edition_id' => $editionId,
                        'category' => $category,
                        'rider_id' => $riderId,
                        'position' => $position + 1,
                    ]);
                }
            }

            if (! empty($classifications['teams_winner'])) {
                FinalClassificationModel::create([
                    'id' => Str::uuid()->toString(),
                    'edition_id' => $editionId,
                    'category' => 'teams_winner',
                    'team_id' => $classifications['teams_winner'],
                ]);
            }

            if (! empty($classifications['super_combativo'])) {
                FinalClassificationModel::create([
                    'id' => Str::uuid()->toString(),
                    'edition_id' => $editionId,
                    'category' => 'super_combativo',
                    'rider_id' => $classifications['super_combativo'],
                ]);
            }

            if ($edition->status !== EditionStatus::Finished) {
                $edition->update(['status' => EditionStatus::Finished]);
            }
        });

        return $this->preRaceScoring->scoreEdition($editionId, force: true);
    }
}
