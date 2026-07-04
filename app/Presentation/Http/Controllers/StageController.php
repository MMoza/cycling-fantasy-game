<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Application\UseCases\Stage\ListStagesUseCase;
use App\Application\UseCases\Stage\ShowStageUseCase;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StageController extends Controller
{
    public function __construct(
        private readonly ListStagesUseCase $listStagesUseCase,
        private readonly ShowStageUseCase $showStageUseCase,
    ) {}

    public function index(Request $request, string $league)
    {
        $data = $this->listStagesUseCase->execute($request->user(), $league);

        return Inertia::render('Stages/Index', [
            'league_id' => $data['leagueId'],
            'league_name' => $data['leagueName'],
            'competition' => $data['competitionName'],
            'year' => $data['year'],
            'stages' => $data['stages'],
            'predictionsPerStage' => $data['stages']
                ->filter(fn ($s) => $s->hasPredictions)
                ->mapWithKeys(fn ($s) => [$s->id => true]),
            'pointsPerStage' => $data['stages']
                ->mapWithKeys(fn ($s) => [$s->id => $s->points]),
        ]);
    }

    public function show(Request $request, string $league, string $stage)
    {
        $data = $this->showStageUseCase->execute($request->user(), $league, $stage);

        return Inertia::render('Stages/Show', [
            'league_id' => $data['leagueId'],
            'stage' => [
                'id' => $data['stage']->id,
                'number' => $data['stage']->number,
                'name' => $data['stage']->name,
                'date' => $data['stage']->date,
                'type' => $data['stage']->type,
                'type_value' => $data['stage']->typeValue,
                'distance' => $data['stage']->distance,
                'elevation_gain' => $data['stage']->elevationGain,
                'profile_image' => $data['stage']->profileImage,
                'origin' => $data['stage']->origin,
                'destination' => $data['stage']->destination,
                'difficulty' => $data['stage']->difficulty,
                'status' => $data['stage']->status,
                'scheduled_start' => $data['stage']->scheduledStart,
            ],
            'is_finished' => $data['isFinished'],
            'is_locked' => $data['isLocked'],
            'predictions' => $data['predictions'],
            'stage_results' => $data['stageResults'],
            'stage_classification' => $data['stageClassification'],
            'league_name' => $data['leagueName'],
            'navigation' => $data['navigation'],
            'all_stages' => $data['allStages'],
            'availableRiders' => $data['availableRiders'],
            'availableTeams' => $data['availableTeams'],
        ]);
    }
}
