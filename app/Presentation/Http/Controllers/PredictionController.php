<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Domain\ValueObjects\PredictionCategory;
use App\Domain\ValueObjects\PredictionType;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\PredictionModel;
use App\Infrastructure\Persistence\Models\StageModel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PredictionController extends Controller
{
    private const PRE_RACE_CATEGORIES = [
        'gc_top_5',
        'points_winner',
        'youth_winner',
        'mountains_winner',
        'teams_winner',
        'super_combativo',
    ];

    private const PRE_STAGE_CATEGORIES = [
        'stage_winner',
        'stage_second',
        'stage_third',
        'stage_leader',
        'stage_combativo',
    ];

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
            'predictions.*.category' => ['required', 'string', 'in:' . implode(',', self::PRE_STAGE_CATEGORIES)],
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

    public function preRace(Request $request, string $league)
    {
        $user = $request->user();

        $leagueModel = LeagueModel::with('edition.competition')->findOrFail($league);

        if (! $user->leagues()->where('leagues.id', $league)->exists()) {
            abort(404);
        }

        $user->update(['last_visited_league_id' => $league]);

        $edition = $leagueModel->edition;

        $isLocked = $this->isPreRaceLocked($edition);

        $predictions = PredictionModel::where('league_id', $leagueModel->id)
            ->where('user_id', $user->id)
            ->whereNull('stage_id')
            ->where('type', PredictionType::PreRace)
            ->get()
            ->keyBy(fn ($p) => $p->category->value)
            ->map(fn ($p) => [
                'category' => $p->category->value,
                'value' => $p->prediction_value,
                'locked_at' => $p->locked_at?->toIso8601String(),
            ]);

        return Inertia::render('Predictions/PreRace', [
            'league_id' => $leagueModel->id,
            'league_name' => $leagueModel->name,
            'competition' => [
                'name' => $edition->competition->name,
                'year' => $edition->year,
            ],
            'is_locked' => $isLocked,
            'predictions' => $predictions,
        ]);
    }

    public function storePreRace(Request $request, string $league)
    {
        $user = $request->user();

        $leagueModel = LeagueModel::with('edition')->findOrFail($league);

        if (! $user->leagues()->where('leagues.id', $league)->exists()) {
            abort(404);
        }

        $edition = $leagueModel->edition;

        if ($this->isPreRaceLocked($edition)) {
            return redirect()->back()->withErrors(['race' => 'La competición ya ha comenzado']);
        }

        $validated = $request->validate([
            'predictions' => ['required', 'array'],
            'predictions.*.category' => ['required', 'string', 'in:' . implode(',', self::PRE_RACE_CATEGORIES)],
            'predictions.*.value' => ['required'],
        ]);

        foreach ($validated['predictions'] as $prediction) {
            PredictionModel::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'league_id' => $leagueModel->id,
                    'stage_id' => null,
                    'type' => PredictionType::PreRace,
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

    private function isPreRaceLocked(EditionModel $edition): bool
    {
        return $edition->status->value !== 'upcoming'
            || ($edition->start_date && now()->greaterThanOrEqualTo($edition->start_date));
    }
}
