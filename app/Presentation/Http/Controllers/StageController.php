<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Domain\ValueObjects\PredictionType;
use App\Domain\ValueObjects\StageStatus;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\PredictionModel;
use App\Infrastructure\Persistence\Models\ScoreEventModel;
use App\Infrastructure\Persistence\Models\StageModel;
use App\Infrastructure\Persistence\Models\TeamModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StageController extends Controller
{
    public function index(Request $request, string $league)
    {
        $leagueModel = $this->getLeague($request, $league);
        $user = $request->user();

        $edition = $leagueModel->edition;

        $stages = StageModel::where('edition_id', $edition->id)
            ->orderBy('number')
            ->get()
            ->map(fn (StageModel $s) => [
                'id' => $s->id,
                'number' => $s->number,
                'name' => $s->name,
                'date' => $s->date->format('d M'),
                'type' => $s->type->label(),
                'type_value' => $s->type->value,
                'distance' => $s->distance,
                'origin' => $s->origin,
                'destination' => $s->destination,
                'status' => $s->status->value,
                'difficulty' => $s->difficulty,
                'profile_image' => $s->profile_image,
                'elevation_gain' => $s->elevation_gain,
                'scheduled_start' => $s->scheduled_start?->toIso8601String(),
            ]);

        $stageIds = collect($stages)->pluck('id');

        $predictionsPerStage = PredictionModel::where('league_id', $leagueModel->id)
            ->where('user_id', $user->id)
            ->whereIn('stage_id', $stageIds)
            ->where('type', PredictionType::PreStage)
            ->get()
            ->groupBy('stage_id')
            ->map(fn ($preds) => true);

        $pointsPerStage = ScoreEventModel::where('league_id', $leagueModel->id)
            ->where('user_id', $user->id)
            ->whereNotNull('stage_id')
            ->whereIn('stage_id', $stageIds)
            ->get()
            ->groupBy('stage_id')
            ->map(fn ($events) => $events->sum('points'));

        return Inertia::render('Stages/Index', [
            'league_id' => $leagueModel->id,
            'league_name' => $leagueModel->name,
            'competition' => $edition->competition->name,
            'year' => $edition->year,
            'stages' => $stages,
            'predictionsPerStage' => $predictionsPerStage,
            'pointsPerStage' => $pointsPerStage,
        ]);
    }

    public function show(Request $request, string $league, string $stage)
    {
        $leagueModel = $this->getLeague($request, $league);

        $stageModel = StageModel::where('edition_id', $leagueModel->edition_id)
            ->findOrFail($stage);

        $user = $request->user();
        $isFinished = $stageModel->status === StageStatus::Finished;

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

        $isLocked = $stageModel->status === StageStatus::Ongoing
            || ($stageModel->scheduled_start && now()->greaterThanOrEqualTo($stageModel->scheduled_start));

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

        $edition = $leagueModel->edition;

        $availableRiders = DB::table('competition_participants')
            ->join('riders', 'competition_participants.rider_id', '=', 'riders.id')
            ->where('competition_participants.competition_id', $edition->competition_id)
            ->where('competition_participants.edition_id', $edition->id)
            ->select('riders.id', 'riders.last_name', 'riders.first_name')
            ->distinct()
            ->orderBy('riders.last_name')
            ->orderBy('riders.first_name')
            ->get()
            ->map(fn ($r) => ['value' => $r->id, 'label' => trim("{$r->last_name} {$r->first_name}")]);

        $availableTeams = TeamModel::whereHas('rosters', fn ($q) => $q->where('year', $edition->year))
            ->orderBy('name')
            ->get()
            ->map(fn ($t) => ['value' => $t->id, 'label' => $t->name]);

        $stageResults = [];
        $stageClassification = [];

        if ($isFinished) {
            $stageResults = DB::table('stage_results')
                ->join('riders', 'stage_results.rider_id', '=', 'riders.id')
                ->where('stage_results.stage_id', $stageModel->id)
                ->orderBy('stage_results.position')
                ->select([
                    'stage_results.position',
                    'stage_results.rider_id',
                    'stage_results.time',
                    'stage_results.gap',
                    'stage_results.is_gc_leader',
                    'stage_results.is_combativo',
                    'riders.last_name',
                    'riders.first_name',
                    'riders.profile_image',
                ])
                ->get()
                ->map(fn ($r) => [
                    'position' => $r->position,
                    'rider_id' => $r->rider_id,
                    'rider_name' => trim("{$r->last_name} {$r->first_name}"),
                    'time' => $r->time,
                    'gap' => $r->gap,
                    'profile_image' => $r->profile_image,
                    'is_gc_leader' => (bool) $r->is_gc_leader,
                    'is_combativo' => (bool) $r->is_combativo,
                ]);

            $stageClassification = DB::table('score_events')
                ->join('users', 'score_events.user_id', '=', 'users.id')
                ->where('score_events.league_id', $leagueModel->id)
                ->where('score_events.stage_id', $stageModel->id)
                ->select([
                    'users.id as user_id',
                    'users.name as user_name',
                    DB::raw('SUM(score_events.points) as total_points'),
                ])
                ->groupBy('users.id', 'users.name')
                ->orderByDesc('total_points')
                ->get()
                ->map(fn ($r) => [
                    'user_id' => $r->user_id,
                    'user_name' => $r->user_name,
                    'total_points' => (int) $r->total_points,
                ]);
        }

        return Inertia::render('Stages/Show', [
            'league_id' => $leagueModel->id,
            'stage' => [
                'id' => $stageModel->id,
                'number' => $stageModel->number,
                'name' => $stageModel->name,
                'date' => $stageModel->date->format('d M'),
                'type' => $stageModel->type->label(),
                'type_value' => $stageModel->type->value,
                'distance' => $stageModel->distance ? "{$stageModel->distance} km" : null,
                'elevation_gain' => $stageModel->elevation_gain,
                'profile_image' => $stageModel->profile_image,
                'origin' => $stageModel->origin,
                'destination' => $stageModel->destination,
                'difficulty' => $stageModel->difficulty,
                'status' => $stageModel->status->value,
                'scheduled_start' => $stageModel->scheduled_start?->toIso8601String(),
            ],
            'is_finished' => $isFinished,
            'is_locked' => $isLocked,
            'predictions' => $predictions,
            'stage_results' => $stageResults,
            'stage_classification' => $stageClassification,
            'navigation' => [
                'prev' => $prevStage ? ['id' => $prevStage->id, 'number' => $prevStage->number] : null,
                'next' => $nextStage ? ['id' => $nextStage->id, 'number' => $nextStage->number] : null,
            ],
            'all_stages' => $allStages,
            'availableRiders' => $availableRiders,
            'availableTeams' => $availableTeams,
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
