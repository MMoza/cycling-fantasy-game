<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Admin;

use App\Application\UseCases\Admin\Edition\GetEditionFormDataUseCase;
use App\Application\UseCases\Admin\Edition\ListEditionsUseCase;
use App\Application\UseCases\Admin\Edition\StoreEditionUseCase;
use App\Application\UseCases\Admin\Edition\UpdateEditionUseCase;
use App\Presentation\Http\Controllers\Controller;
use App\Presentation\Http\Requests\Admin\StoreEditionRequest;
use App\Presentation\Http\Requests\Admin\UpdateEditionRequest;
use Illuminate\Http\RedirectResponse;
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

    public function store(StoreEditionRequest $request, string $competitionId): RedirectResponse
    {
        $validated = $request->validated();

        $this->storeEditionUseCase->execute($competitionId, $validated, $request->user()->id);

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

    public function update(UpdateEditionRequest $request, string $competitionId, string $id): RedirectResponse
    {
        $validated = $request->validated();

        $this->updateEditionUseCase->execute($competitionId, $id, $validated);

        return redirect()->route('admin.competitions.editions.index', $competitionId);
    }
}
