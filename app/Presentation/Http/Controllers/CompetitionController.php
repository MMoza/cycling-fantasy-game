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
        return Inertia::render('Competitions/Index', $this->listActiveCompetitionsUseCase->execute($year)->toArray());
    }

    public function show(string $edition): Response
    {
        $detail = $this->showCompetitionUseCase->execute($edition);

        return Inertia::render('Competitions/Show', [
            'competition' => $detail,
        ]);
    }
}
