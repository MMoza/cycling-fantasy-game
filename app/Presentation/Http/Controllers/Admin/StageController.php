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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $data = $this->listAdminStagesUseCase->execute($editionId);

        return Inertia::render('Admin/Stages/Index', [
            'edition' => $data['edition'],
            'stages' => $data['stages'],
        ]);
    }

    public function create(string $editionId): Response
    {
        $data = $this->getStageFormDataUseCase->execute($editionId);

        return Inertia::render('Admin/Stages/Form', [
            'edition' => $data['edition'],
            'stage' => $data['stage'],
            'stageTypes' => $data['stageTypes'],
        ]);
    }

    public function store(Request $request, string $editionId): RedirectResponse
    {
        $validated = $request->validate([
            'number' => 'required|integer|min:1',
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'type' => 'required|string',
            'distance' => 'nullable|numeric|min:0',
            'elevation_gain' => 'nullable|integer|min:0',
            'difficulty' => 'nullable|integer|min:1|max:3',
            'origin' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'scheduled_start' => 'nullable|date',
            'profile_image' => 'nullable|image|max:2048',
        ]);

        $this->storeStageUseCase->execute(
            $editionId,
            $validated,
            $request->hasFile('profile_image') ? $request->file('profile_image') : null,
        );

        return redirect()->route('admin.editions.stages.index', $editionId);
    }

    public function edit(string $editionId, string $id): Response
    {
        $data = $this->getStageFormDataUseCase->execute($editionId, $id);

        return Inertia::render('Admin/Stages/Form', [
            'edition' => $data['edition'],
            'stage' => $data['stage'],
            'stageTypes' => $data['stageTypes'],
        ]);
    }

    public function update(Request $request, string $editionId, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'number' => 'required|integer|min:1',
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'type' => 'required|string',
            'distance' => 'nullable|numeric|min:0',
            'elevation_gain' => 'nullable|integer|min:0',
            'difficulty' => 'nullable|integer|min:1|max:3',
            'origin' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'scheduled_start' => 'nullable|date',
            'profile_image' => 'nullable|image|max:2048',
        ]);

        $this->updateStageUseCase->execute(
            $editionId,
            $id,
            $validated,
            $request->hasFile('profile_image') ? $request->file('profile_image') : null,
        );

        return redirect()->route('admin.editions.stages.index', $editionId);
    }

    public function show(string $editionId, string $id): Response
    {
        $data = $this->showAdminStageUseCase->execute($editionId, $id);

        return Inertia::render('Admin/Stages/Show', [
            'edition' => $data['edition'],
            'stage' => $data['stage'],
            'availableRiders' => $data['availableRiders'],
            'results' => $data['results'],
        ]);
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

    public function storeResult(Request $request, string $editionId, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'results' => 'required|array|min:1',
            'results.*.rider_id' => 'required|string',
            'results.*.position' => 'required|integer|min:1',
            'results.*.time' => 'nullable|string|max:50',
            'results.*.gap' => 'nullable|string|max:50',
            'results.*.is_gc_leader' => 'nullable|boolean',
            'results.*.is_combativo' => 'nullable|boolean',
        ]);

        try {
            $this->storeStageResultUseCase->execute($editionId, $id, $validated['results']);
        } catch (ApplicationException $e) {
            return redirect()->back()->withErrors(['results' => $e->getMessage()]);
        }

        return redirect()->route('admin.editions.stages.show', [$editionId, $id]);
    }
}
