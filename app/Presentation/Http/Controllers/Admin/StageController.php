<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Admin;

use App\Application\Exceptions\ApplicationException;
use App\Application\UseCases\Admin\Stage\GetStageFormDataUseCase;
use App\Application\UseCases\Admin\Stage\ListAdminStagesUseCase;
use App\Application\UseCases\Admin\Stage\MarkStageFinishedUseCase;
use App\Application\UseCases\Admin\Stage\MarkStageUpcomingUseCase;
use App\Application\UseCases\Admin\Stage\ShowAdminStageUseCase;
use App\Application\UseCases\Admin\Stage\StoreStageResultUseCase;
use App\Application\UseCases\Admin\Stage\StoreStageUseCase;
use App\Application\UseCases\Admin\Stage\UpdateStageUseCase;
use App\Presentation\Http\Controllers\Controller;
use App\Presentation\Http\Requests\Admin\StoreStageRequest;
use App\Presentation\Http\Requests\Admin\StoreStageResultRequest;
use App\Presentation\Http\Requests\Admin\UpdateStageRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class StageController extends Controller
{
    public function __construct(
        private readonly ListAdminStagesUseCase $listAdminStagesUseCase,
        private readonly GetStageFormDataUseCase $getStageFormDataUseCase,
        private readonly StoreStageUseCase $storeStageUseCase,
        private readonly UpdateStageUseCase $updateStageUseCase,
        private readonly ShowAdminStageUseCase $showAdminStageUseCase,
        private readonly MarkStageFinishedUseCase $markStageFinishedUseCase,
        private readonly MarkStageUpcomingUseCase $markStageUpcomingUseCase,
        private readonly StoreStageResultUseCase $storeStageResultUseCase,
    ) {}

    public function index(string $editionId): Response
    {
        return Inertia::render('Admin/Stages/Index', $this->listAdminStagesUseCase->execute($editionId)->toArray());
    }

    public function create(string $editionId): Response
    {
        return Inertia::render('Admin/Stages/Form', $this->getStageFormDataUseCase->execute($editionId)->toArray());
    }

    public function store(StoreStageRequest $request, string $editionId): RedirectResponse
    {
        $validated = $request->validated();

        $this->storeStageUseCase->execute(
            $editionId,
            $validated,
        );

        return redirect()->route('admin.editions.stages.index', $editionId);
    }

    public function edit(string $editionId, string $id): Response
    {
        return Inertia::render('Admin/Stages/Form', $this->getStageFormDataUseCase->execute($editionId, $id)->toArray());
    }

    public function update(UpdateStageRequest $request, string $editionId, string $id): RedirectResponse
    {
        $validated = $request->validated();

        $this->updateStageUseCase->execute(
            $editionId,
            $id,
            $validated,
        );

        return redirect()->route('admin.editions.stages.index', $editionId);
    }

    public function show(string $editionId, string $id): Response
    {
        return Inertia::render('Admin/Stages/Show', $this->showAdminStageUseCase->execute($editionId, $id)->toArray());
    }

    public function markFinished(string $editionId, string $id): RedirectResponse
    {
        $this->markStageFinishedUseCase->execute($editionId, $id);

        return redirect()->route('admin.editions.stages.show', [$editionId, $id]);
    }

    public function markUpcoming(string $editionId, string $id): RedirectResponse
    {
        $this->markStageUpcomingUseCase->execute($editionId, $id);

        return redirect()->route('admin.editions.stages.show', [$editionId, $id]);
    }

    public function storeResult(StoreStageResultRequest $request, string $editionId, string $id): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $this->storeStageResultUseCase->execute($editionId, $id, $validated['results']);
        } catch (ApplicationException $e) {
            return redirect()->back()->withErrors(['results' => $e->getMessage()]);
        }

        return redirect()->route('admin.editions.stages.show', [$editionId, $id]);
    }
}
