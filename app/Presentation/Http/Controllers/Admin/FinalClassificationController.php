<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Admin;

use App\Application\UseCases\Admin\FinalClassification\GetFinalClassificationsUseCase;
use App\Application\UseCases\Admin\FinalClassification\UpdateFinalClassificationsUseCase;
use App\Presentation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FinalClassificationController extends Controller
{
    public function __construct(
        private readonly GetFinalClassificationsUseCase $getFinalClassificationsUseCase,
        private readonly UpdateFinalClassificationsUseCase $updateFinalClassificationsUseCase,
    ) {}

    public function edit(string $editionId): Response
    {
        $data = $this->getFinalClassificationsUseCase->execute($editionId);

        return Inertia::render('Admin/Editions/FinalClassifications', [
            'edition' => $data['edition'],
            'riders' => $data['riders'],
            'teams' => $data['teams'],
            'classifications' => $data['classifications'],
        ]);
    }

    public function update(Request $request, string $editionId): RedirectResponse
    {
        $validated = $request->validate([
            'classifications' => 'required|array',
            'classifications.gc_top_5' => 'nullable|array|size:5',
            'classifications.gc_top_5.*' => 'required|string|exists:riders,id',
            'classifications.points_winner' => 'nullable|array|size:3',
            'classifications.points_winner.*' => 'required|string|exists:riders,id',
            'classifications.mountains_winner' => 'nullable|array|size:3',
            'classifications.mountains_winner.*' => 'required|string|exists:riders,id',
            'classifications.youth_winner' => 'nullable|array|size:3',
            'classifications.youth_winner.*' => 'required|string|exists:riders,id',
            'classifications.teams_winner' => 'nullable|string|exists:teams,id',
            'classifications.super_combativo' => 'nullable|string|exists:riders,id',
        ]);

        $this->updateFinalClassificationsUseCase->execute(
            $editionId,
            $validated['classifications'],
        );

        return redirect()->back()->with('success', 'Clasificaciones guardadas correctamente');
    }
}
