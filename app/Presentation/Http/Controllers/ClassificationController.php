<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Application\UseCases\Classification\ShowClassificationUseCase;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClassificationController extends Controller
{
    public function __construct(
        private readonly ShowClassificationUseCase $showClassificationUseCase,
    ) {}

    public function index(Request $request, string $league)
    {
        $data = $this->showClassificationUseCase->execute($request->user(), $league);

        return Inertia::render('Classification/Index', [
            'league_id' => $data['leagueId'],
            'league_name' => $data['leagueName'],
            'stages' => $data['stages'],
            'general_leaderboard' => $data['general_leaderboard'],
            'stage_leaderboards' => $data['stage_leaderboards'],
            'last_scored_stage_id' => $data['last_scored_stage_id'],
            'user_position' => $data['user_position'],
            'general_details' => $data['general_details'],
        ]);
    }
}
