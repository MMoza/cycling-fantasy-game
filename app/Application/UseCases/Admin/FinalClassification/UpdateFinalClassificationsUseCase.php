<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\FinalClassification;

use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\FinalClassificationModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UpdateFinalClassificationsUseCase
{
    public function execute(string $editionId, array $classifications): void
    {
        EditionModel::findOrFail($editionId);

        DB::transaction(function () use ($editionId, $classifications): void {
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
        });
    }
}
