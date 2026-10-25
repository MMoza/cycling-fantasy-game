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
        return Inertia::render('Stages/Index', $this->listStagesUseCase->execute($request->user(), $league)->toArray());
    }

    public function show(Request $request, string $league, string $stage)
    {
        return Inertia::render('Stages/Show', $this->showStageUseCase->execute($request->user(), $league, $stage)->toArray());
    }
}
