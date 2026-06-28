<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Admin;

use App\Application\UseCases\Admin\Rider\GetRiderFormDataUseCase;
use App\Application\UseCases\Admin\Rider\ListRidersUseCase;
use App\Application\UseCases\Admin\Rider\StoreRiderUseCase;
use App\Application\UseCases\Admin\Rider\UpdateRiderUseCase;
use App\Presentation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'country_id' => 'nullable|string|size:2|exists:countries,id',
            'birth_date' => 'nullable|date',
            'profile_image' => 'nullable|string|max:255',
        ]);

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

    public function update(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'country_id' => 'nullable|string|size:2|exists:countries,id',
            'birth_date' => 'nullable|date',
            'profile_image' => 'nullable|string|max:255',
        ]);

        $this->updateRiderUseCase->execute($id, $validated);

        return redirect()->route('admin.riders.index');
    }
}
