<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Application\UseCases\Competition\ListActiveCompetitionsUseCase;
use App\Application\UseCases\Competition\ShowCompetitionUseCase;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompetitionController extends Controller
{
    public function __construct(
        private readonly ListActiveCompetitionsUseCase $listActiveCompetitionsUseCase,
        private readonly ShowCompetitionUseCase $showCompetitionUseCase,
    ) {}

    public function index(Request $request, ?int $year = null): Response
    {
        $data = $this->listActiveCompetitionsUseCase->execute($year);

        return Inertia::render('Competitions/Index', [
            'yearGroups' => $data['yearGroups'],
            'years' => $data['years'],
            'currentYear' => $data['currentYear'],
        ]);
    }

    public function show(string $edition): Response
    {
        $detail = $this->showCompetitionUseCase->execute($edition);

        return Inertia::render('Competitions/Show', [
            'competition' => $detail,
        ]);
    }
}
