<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Application\Exceptions\ApplicationException;
use App\Application\UseCases\Prediction\ShowPreRaceFormUseCase;
use App\Application\UseCases\Prediction\StorePreRacePredictionUseCase;
use App\Application\UseCases\Prediction\StoreStagePredictionUseCase;
use Illuminate\Http\Request;
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

    public function __construct(
        private readonly ShowPreRaceFormUseCase $showPreRaceFormUseCase,
        private readonly StorePreRacePredictionUseCase $storePreRacePredictionUseCase,
        private readonly StoreStagePredictionUseCase $storeStagePredictionUseCase,
    ) {}

    public function store(Request $request, string $league, string $stage)
    {
        $validated = $request->validate([
            'predictions' => ['required', 'array'],
            'predictions.*.category' => ['required', 'string', 'in:'.implode(',', self::PRE_STAGE_CATEGORIES)],
            'predictions.*.value' => ['required'],
        ]);

        try {
            $this->storeStagePredictionUseCase->execute(
                $request->user(),
                $league,
                $stage,
                $validated['predictions'],
            );
        } catch (ApplicationException $e) {
            return redirect()->back()->withErrors(['stage' => $e->getMessage()]);
        }

        return redirect()->back();
    }

    public function preRace(Request $request, string $league)
    {
        $data = $this->showPreRaceFormUseCase->execute($request->user(), $league);

        return Inertia::render('Predictions/PreRace', [
            'league_id' => $data['leagueId'],
            'league_name' => $data['leagueName'],
            'competition' => [
                'name' => $data['competitionName'],
                'year' => $data['competitionYear'],
            ],
            'is_locked' => $data['isLocked'],
            'predictions' => $data['predictions'],
            'availableRiders' => $data['availableRiders'],
            'availableTeams' => $data['availableTeams'],
            'scoring_system' => $data['scoringSystem'],
        ]);
    }

    public function storePreRace(Request $request, string $league)
    {
        $validated = $request->validate([
            'predictions' => ['required', 'array'],
            'predictions.*.category' => ['required', 'string', 'in:'.implode(',', self::PRE_RACE_CATEGORIES)],
            'predictions.*.value' => ['required'],
        ]);

        try {
            $this->storePreRacePredictionUseCase->execute(
                $request->user(),
                $league,
                $validated['predictions'],
            );
        } catch (ApplicationException $e) {
            return redirect()->back()->withErrors(['race' => $e->getMessage()]);
        }

        return redirect()->back();
    }
}
