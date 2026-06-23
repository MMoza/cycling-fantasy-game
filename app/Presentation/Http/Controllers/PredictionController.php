<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Domain\ValueObjects\PredictionCategory;
use App\Domain\ValueObjects\PredictionType;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\PredictionModel;
use App\Infrastructure\Persistence\Models\StageModel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PredictionController extends Controller
{
    public function store(Request $request, string $league, string $stage)
    {
        $user = $request->user();

        $leagueModel = LeagueModel::findOrFail($league);

        if (! $user->leagues()->where('leagues.id', $league)->exists()) {
            abort(404);
        }

        $stageModel = StageModel::where('edition_id', $leagueModel->edition_id)
            ->findOrFail($stage);

        if ($stageModel->scheduled_start && now()->greaterThanOrEqualTo($stageModel->scheduled_start)) {
            return redirect()->back()->withErrors(['stage' => 'La etapa ya ha comenzado']);
        }

        $validated = $request->validate([
            'predictions' => ['required', 'array'],
            'predictions.*.category' => ['required', 'string', 'in:' . implode(',', [
                PredictionCategory::StageWinner->value,
                PredictionCategory::StageSecond->value,
                PredictionCategory::StageThird->value,
                PredictionCategory::StageLeader->value,
                PredictionCategory::StageCombativo->value,
            ])],
            'predictions.*.value' => ['required'],
        ]);

        foreach ($validated['predictions'] as $prediction) {
            PredictionModel::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'league_id' => $leagueModel->id,
                    'stage_id' => $stageModel->id,
                    'type' => PredictionType::PreStage,
                    'category' => $prediction['category'],
                ],
                [
                    'id' => Str::uuid()->toString(),
                    'prediction_value' => $prediction['value'],
                ]
            );
        }

        return redirect()->back();
    }
}
