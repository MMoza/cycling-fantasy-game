<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Admin;

use App\Application\Exceptions\ApplicationException;
use App\Application\UseCases\Admin\FinalClassification\GetFinalClassificationsUseCase;
use App\Application\UseCases\Admin\FinalClassification\UpdateFinalClassificationsUseCase;
use App\Presentation\Http\Controllers\Controller;
use App\Presentation\Http\Requests\Admin\UpdateFinalClassificationsRequest;
use Illuminate\Http\RedirectResponse;
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
        return Inertia::render('Admin/Editions/FinalClassifications', $this->getFinalClassificationsUseCase->execute($editionId)->toArray());
    }

    public function update(UpdateFinalClassificationsRequest $request, string $editionId): RedirectResponse
    {
        try {
            $result = $this->updateFinalClassificationsUseCase->execute(
                $editionId,
                $request->classifications(),
            );

            $message = "Clasificaciones guardadas. Puntuación pre-carrera: {$result['scored']} eventos en {$result['leagues']} liga(s)";

            return redirect()->back()->with('success', $message);
        } catch (ApplicationException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
