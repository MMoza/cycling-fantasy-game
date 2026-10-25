<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Application\Exceptions\ApplicationException;
use App\Application\UseCases\Prediction\ShowPreRaceFormUseCase;
use App\Application\UseCases\Prediction\StorePreRacePredictionUseCase;
use App\Application\UseCases\Prediction\StoreStagePredictionUseCase;
use App\Presentation\Http\Requests\Prediction\StorePreRacePredictionsRequest;
use App\Presentation\Http\Requests\Prediction\StoreStagePredictionRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PredictionController extends Controller
{
    public function __construct(
        private readonly ShowPreRaceFormUseCase $showPreRaceFormUseCase,
        private readonly StorePreRacePredictionUseCase $storePreRacePredictionUseCase,
        private readonly StoreStagePredictionUseCase $storeStagePredictionUseCase,
    ) {}

    public function store(StoreStagePredictionRequest $request, string $league, string $stage)
    {
        try {
            $this->storeStagePredictionUseCase->execute($request->user(), $league, $stage, $request->predictions());
        } catch (ApplicationException $e) {
            return redirect()->back()->withErrors(['stage' => $e->getMessage()]);
        }

        return redirect()->back();
    }

    public function preRace(Request $request, string $league)
    {
        return Inertia::render('Predictions/PreRace', $this->showPreRaceFormUseCase->execute($request->user(), $league)->toArray());
    }

    public function storePreRace(StorePreRacePredictionsRequest $request, string $league)
    {
        try {
            $this->storePreRacePredictionUseCase->execute($request->user(), $league, $request->predictions());
        } catch (ApplicationException $e) {
            return redirect()->back()->withErrors(['race' => $e->getMessage()]);
        }

        return redirect()->back();
    }
}
