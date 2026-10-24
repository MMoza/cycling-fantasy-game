<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Admin;

use App\Application\UseCases\Admin\Rider\GetRiderFormDataUseCase;
use App\Application\UseCases\Admin\Rider\ListRidersUseCase;
use App\Application\UseCases\Admin\Rider\StoreRiderUseCase;
use App\Application\UseCases\Admin\Rider\UpdateRiderUseCase;
use App\Presentation\Http\Controllers\Controller;
use App\Presentation\Http\Requests\Admin\StoreRiderRequest;
use App\Presentation\Http\Requests\Admin\UpdateRiderRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class RiderController extends Controller
{
    public function __construct(
        private readonly ListRidersUseCase $listRidersUseCase,
        private readonly GetRiderFormDataUseCase $getRiderFormDataUseCase,
        private readonly StoreRiderUseCase $storeRiderUseCase,
        private readonly UpdateRiderUseCase $updateRiderUseCase,
    ) {}

    public function index(): Response
    {
        $data = $this->listRidersUseCase->execute();

        return Inertia::render('Admin/Riders/Index', [
            'riders' => $data['riders'],
            'countries' => $data['countries'],
        ]);
    }

    public function create(): Response
    {
        $data = $this->getRiderFormDataUseCase->execute();

        return Inertia::render('Admin/Riders/Form', [
            'rider' => $data['rider'],
            'countries' => $data['countries'],
        ]);
    }

    public function store(StoreRiderRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $this->storeRiderUseCase->execute($validated);

        return redirect()->route('admin.riders.index');
    }

    public function edit(string $id): Response
    {
        $data = $this->getRiderFormDataUseCase->execute($id);

        return Inertia::render('Admin/Riders/Form', [
            'rider' => $data['rider'],
            'countries' => $data['countries'],
        ]);
    }

    public function update(UpdateRiderRequest $request, string $id): RedirectResponse
    {
        $validated = $request->validated();

        $this->updateRiderUseCase->execute($id, $validated);

        return redirect()->route('admin.riders.index');
    }
}
