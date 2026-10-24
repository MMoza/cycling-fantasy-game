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
        return Inertia::render('Season/Index', $this->showSeasonUseCase->execute($request->user())->toArray());
    }

    public function classification(Request $request): Response
    {
        return Inertia::render('Season/Classification', $this->showSeasonClassificationUseCase->execute($request->user())->toArray());
    }
}
