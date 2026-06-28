<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Admin;

use App\Application\UseCases\Admin\Competition\GetCompetitionFormDataUseCase;
use App\Application\UseCases\Admin\Competition\ListCompetitionsUseCase;
use App\Application\UseCases\Admin\Competition\StoreCompetitionUseCase;
use App\Application\UseCases\Admin\Competition\UpdateCompetitionUseCase;
use App\Presentation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompetitionController extends Controller
{
    public function __construct(
        private readonly ListCompetitionsUseCase $listCompetitionsUseCase,
        private readonly GetCompetitionFormDataUseCase $getCompetitionFormDataUseCase,
        private readonly StoreCompetitionUseCase $storeCompetitionUseCase,
        private readonly UpdateCompetitionUseCase $updateCompetitionUseCase,
    ) {}

    public function index(): Response
    {
        $data = $this->listCompetitionsUseCase->execute();

        return Inertia::render('Admin/Competitions/Index', [
            'competitions' => $data['competitions'],
            'countries' => $data['countries'],
        ]);
    }

    public function create(): Response
    {
        $data = $this->getCompetitionFormDataUseCase->execute();

        return Inertia::render('Admin/Competitions/Form', [
            'competition' => $data['competition'],
            'countries' => $data['countries'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:grand_tour,one_week,classic',
            'country_id' => 'required|string|size:2|exists:countries,id',
        ]);

        $this->storeCompetitionUseCase->execute($validated);

        return redirect()->route('admin.competitions.index');
    }

    public function edit(string $id): Response
    {
        $data = $this->getCompetitionFormDataUseCase->execute($id);

        return Inertia::render('Admin/Competitions/Form', [
            'competition' => $data['competition'],
            'countries' => $data['countries'],
        ]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:grand_tour,one_week,classic',
            'country_id' => 'required|string|size:2|exists:countries,id',
            'active' => 'boolean',
        ]);

        $this->updateCompetitionUseCase->execute($id, $validated);

        return redirect()->route('admin.competitions.index');
    }
}
