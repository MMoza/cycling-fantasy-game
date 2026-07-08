<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Application\UseCases\Season\ShowSeasonClassificationUseCase;
use App\Application\UseCases\Season\ShowSeasonUseCase;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SeasonController extends Controller
{
    public function __construct(
        private readonly ShowSeasonUseCase $showSeasonUseCase,
        private readonly ShowSeasonClassificationUseCase $showSeasonClassificationUseCase,
    ) {}

    public function index(Request $request): Response
    {
        $data = $this->showSeasonUseCase->execute($request->user());

        return Inertia::render('Season/Index', [
            'year' => $data['year'],
            'competitions' => $data['competitions'],
            'user_joined_count' => $data['user_joined_count'],
            'total_competitions' => $data['total_competitions'],
        ]);
    }

    public function classification(Request $request): Response
    {
        $data = $this->showSeasonClassificationUseCase->execute($request->user());

        return Inertia::render('Season/Classification', [
            'year' => $data['year'],
            'aggregated_leaderboard' => $data['aggregated_leaderboard'],
            'per_competition' => $data['per_competition'],
        ]);
    }
}
