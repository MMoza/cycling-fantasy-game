<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Application\Exceptions\ApplicationException;
use App\Application\UseCases\Prediction\GenerateAiStagePredictionUseCase;
use App\Application\UseCases\Prediction\ShowPreRaceFormUseCase;
use App\Application\UseCases\Prediction\StorePreRacePredictionUseCase;
use App\Application\UseCases\Prediction\StoreStagePredictionUseCase;
use App\Presentation\Http\Requests\Prediction\StorePreRacePredictionsRequest;
use App\Presentation\Http\Requests\Prediction\StoreStagePredictionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PredictionController extends Controller
{
    public function __construct(
        private readonly ShowPreRaceFormUseCase $showPreRaceFormUseCase,
        private readonly StorePreRacePredictionUseCase $storePreRacePredictionUseCase,
        private readonly StoreStagePredictionUseCase $storeStagePredictionUseCase,
        private readonly GenerateAiStagePredictionUseCase $generateAiStagePredictionUseCase,
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

    public function generateAiProposal(Request $request, string $league, string $stage): JsonResponse
    {
        try {
            $proposals = $this->generateAiStagePredictionUseCase->execute($request->user(), $league, $stage);
        } catch (ApplicationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['predictions' => $proposals]);
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
