<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Admin;

use App\Application\Exceptions\ApplicationException;
use App\Application\UseCases\Admin\Team\AddRiderToTeamUseCase;
use App\Application\UseCases\Admin\Team\GetTeamFormDataUseCase;
use App\Application\UseCases\Admin\Team\ListTeamsUseCase;
use App\Application\UseCases\Admin\Team\RemoveRiderFromTeamUseCase;
use App\Application\UseCases\Admin\Team\ShowTeamUseCase;
use App\Application\UseCases\Admin\Team\StoreTeamUseCase;
use App\Application\UseCases\Admin\Team\UpdateTeamUseCase;
use App\Presentation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TeamController extends Controller
{
    public function __construct(
        private readonly ListTeamsUseCase $listTeamsUseCase,
        private readonly GetTeamFormDataUseCase $getTeamFormDataUseCase,
        private readonly StoreTeamUseCase $storeTeamUseCase,
        private readonly UpdateTeamUseCase $updateTeamUseCase,
        private readonly ShowTeamUseCase $showTeamUseCase,
        private readonly AddRiderToTeamUseCase $addRiderToTeamUseCase,
        private readonly RemoveRiderFromTeamUseCase $removeRiderFromTeamUseCase,
    ) {}

    public function index(): Response
    {
        $data = $this->listTeamsUseCase->execute();

        return Inertia::render('Admin/Teams/Index', [
            'teams' => $data['teams'],
            'countries' => $data['countries'],
        ]);
    }

    public function create(): Response
    {
        $data = $this->getTeamFormDataUseCase->execute();

        return Inertia::render('Admin/Teams/Form', [
            'team' => $data['team'],
            'countries' => $data['countries'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'abbreviation' => 'nullable|string|size:3|unique:teams,abbreviation',
            'country_id' => 'nullable|string|size:2|exists:countries,id',
            'logo_url' => 'nullable|string|max:255',
        ]);

        $this->storeTeamUseCase->execute($validated);

        return redirect()->route('admin.teams.index');
    }

    public function show(string $id): Response
    {
        $data = $this->showTeamUseCase->execute($id);

        return Inertia::render('Admin/Teams/Show', [
            'team' => $data['team'],
            'rosters' => $data['rosters'],
            'allRiders' => $data['allRiders'],
            'countries' => $data['countries'],
        ]);
    }

    public function edit(string $id): Response
    {
        $data = $this->getTeamFormDataUseCase->execute($id);

        return Inertia::render('Admin/Teams/Form', [
            'team' => $data['team'],
            'countries' => $data['countries'],
        ]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'abbreviation' => 'nullable|string|size:3|unique:teams,abbreviation,'.$id,
            'country_id' => 'nullable|string|size:2|exists:countries,id',
            'logo_url' => 'nullable|string|max:255',
        ]);

        $this->updateTeamUseCase->execute($id, $validated);

        return redirect()->route('admin.teams.index');
    }

    public function addRider(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'rider_id' => 'required|string|exists:riders,id',
            'year' => 'required|integer|min:2020|max:2100',
        ]);

        try {
            $this->addRiderToTeamUseCase->execute($id, $validated['rider_id'], $validated['year']);
        } catch (ApplicationException $e) {
            return redirect()->back()->withErrors(['rider_id' => $e->getMessage()]);
        }

        return redirect()->route('admin.teams.show', $id);
    }

    public function removeRider(string $id, string $riderId, int $year): RedirectResponse
    {
        $this->removeRiderFromTeamUseCase->execute($id, $riderId, $year);

        return redirect()->route('admin.teams.show', $id);
    }
}
