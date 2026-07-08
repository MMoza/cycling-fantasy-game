<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Application\UseCases\Team\ShowTeamsUseCase;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TeamController extends Controller
{
    public function __construct(
        private readonly ShowTeamsUseCase $showTeamsUseCase,
    ) {}

    public function index(Request $request, string $league): Response
    {
        $data = $this->showTeamsUseCase->execute($league);

        return Inertia::render('Teams/Index', [
            'league_id' => $data['league_id'],
            'league_name' => $data['league_name'],
            'competition_name' => $data['competition_name'],
            'year' => $data['year'],
            'teams' => $data['teams'],
            'total_teams' => $data['total_teams'],
            'total_riders' => $data['total_riders'],
        ]);
    }
}
