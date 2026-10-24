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
        return Inertia::render('Classification/Index', $this->showClassificationUseCase->execute($request->user(), $league)->toArray());
    }
}
