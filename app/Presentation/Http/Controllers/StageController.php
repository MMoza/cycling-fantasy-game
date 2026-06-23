<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\PredictionModel;
use App\Infrastructure\Persistence\Models\StageModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StageController extends Controller
{
    public function index(Request $request, string $league)
    {
        $leagueModel = $this->getLeague($request, $league);

        $stage = $leagueModel->stages()
            ->where('status', '!=', 'finished')
            ->orderBy('date')
            ->orderBy('number')
            ->first();

        if (! $stage) {
            $stage = $leagueModel->stages()
                ->orderBy('number')
                ->first();
        }

        if (! $stage) {
            return redirect()->route('leagues.show', $league);
        }

        return redirect()->route('stages.show', [$league, $stage->id]);
    }

    public function show(Request $request, string $league, string $stage)
    {
        $leagueModel = $this->getLeague($request, $league);

        $stageModel = StageModel::where('edition_id', $leagueModel->edition_id)
            ->findOrFail($stage);

        $user = $request->user();

        $predictions = PredictionModel::where('league_id', $leagueModel->id)
            ->where('user_id', $user->id)
            ->where('stage_id', $stageModel->id)
            ->where('type', 'pre_stage')
            ->get()
            ->keyBy(fn ($p) => $p->category->value)
            ->map(fn ($p) => [
                'category' => $p->category->value,
                'value' => $p->prediction_value,
                'locked_at' => $p->locked_at?->toIso8601String(),
            ]);

        $isLocked = $stageModel->scheduled_start && now()->greaterThanOrEqualTo($stageModel->scheduled_start);

        $prevStage = StageModel::where('edition_id', $leagueModel->edition_id)
            ->where('number', '<', $stageModel->number)
            ->orderBy('number', 'desc')
            ->first();

        $nextStage = StageModel::where('edition_id', $leagueModel->edition_id)
            ->where('number', '>', $stageModel->number)
            ->orderBy('number')
            ->first();

        $allStages = StageModel::where('edition_id', $leagueModel->edition_id)
            ->orderBy('number')
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'number' => $s->number,
                'name' => $s->name,
            ]);

        return Inertia::render('Stages/Show', [
            'league_id' => $leagueModel->id,
            'stage' => [
                'id' => $stageModel->id,
                'number' => $stageModel->number,
                'name' => $stageModel->name,
                'date' => $stageModel->date->format('d M'),
                'type' => $stageModel->type->label(),
                'distance' => $stageModel->distance ? "{$stageModel->distance} km" : null,
                'elevation_gain' => $stageModel->elevation_gain,
                'profile_image' => $stageModel->profile_image,
                'origin' => $stageModel->origin,
                'destination' => $stageModel->destination,
                'status' => $stageModel->status->value,
                'scheduled_start' => $stageModel->scheduled_start?->toIso8601String(),
            ],
            'is_locked' => $isLocked,
            'predictions' => $predictions,
            'navigation' => [
                'prev' => $prevStage ? ['id' => $prevStage->id, 'number' => $prevStage->number] : null,
                'next' => $nextStage ? ['id' => $nextStage->id, 'number' => $nextStage->number] : null,
            ],
            'all_stages' => $allStages,
        ]);
    }

    private function getLeague(Request $request, string $leagueId): LeagueModel
    {
        $user = $request->user();

        $league = LeagueModel::with('edition')->findOrFail($leagueId);

        if (! $user->leagues()->where('leagues.id', $league->id)->exists()) {
            abort(404);
        }

        $user->update(['last_visited_league_id' => $league->id]);

        return $league;
    }
}
