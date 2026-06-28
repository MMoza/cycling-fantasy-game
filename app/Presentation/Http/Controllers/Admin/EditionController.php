<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Admin;

use App\Application\UseCases\Admin\Edition\GetEditionFormDataUseCase;
use App\Application\UseCases\Admin\Edition\ListEditionsUseCase;
use App\Application\UseCases\Admin\Edition\StoreEditionUseCase;
use App\Application\UseCases\Admin\Edition\UpdateEditionUseCase;
use App\Presentation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EditionController extends Controller
{
    public function __construct(
        private readonly ListEditionsUseCase $listEditionsUseCase,
        private readonly GetEditionFormDataUseCase $getEditionFormDataUseCase,
        private readonly StoreEditionUseCase $storeEditionUseCase,
        private readonly UpdateEditionUseCase $updateEditionUseCase,
    ) {}

    public function index(string $competitionId): Response
    {
        $data = $this->listEditionsUseCase->execute($competitionId);

        return Inertia::render('Admin/Editions/Index', [
            'competition' => $data['competition'],
            'editions' => $data['editions'],
        ]);
    }

    public function create(string $competitionId): Response
    {
        $data = $this->getEditionFormDataUseCase->execute($competitionId);

        return Inertia::render('Admin/Editions/Form', [
            'competition' => $data['competition'],
            'edition' => $data['edition'],
        ]);
    }

    public function store(Request $request, string $competitionId): RedirectResponse
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:1900|max:2100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $this->storeEditionUseCase->execute($competitionId, $validated);

        return redirect()->route('admin.competitions.editions.index', $competitionId);
    }

    public function edit(string $competitionId, string $id): Response
    {
        $data = $this->getEditionFormDataUseCase->execute($competitionId, $id);

        return Inertia::render('Admin/Editions/Form', [
            'competition' => $data['competition'],
            'edition' => $data['edition'],
        ]);
    }

    public function update(Request $request, string $competitionId, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:1900|max:2100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|string|in:upcoming,ongoing,finished',
        ]);

        $this->updateEditionUseCase->execute($competitionId, $id, $validated);

        return redirect()->route('admin.competitions.editions.index', $competitionId);
    }
}
