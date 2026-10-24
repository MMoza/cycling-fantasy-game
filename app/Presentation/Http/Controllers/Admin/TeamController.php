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
use App\Presentation\Http\Requests\Admin\AddTeamRiderRequest;
use App\Presentation\Http\Requests\Admin\StoreTeamRequest;
use App\Presentation\Http\Requests\Admin\UpdateTeamRequest;
use Illuminate\Http\RedirectResponse;
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

    public function store(StoreTeamRequest $request): RedirectResponse
    {
        $validated = $request->validated();

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

    public function update(UpdateTeamRequest $request, string $id): RedirectResponse
    {
        $validated = $request->validated();

        $this->updateTeamUseCase->execute($id, $validated);

        return redirect()->route('admin.teams.index');
    }

    public function addRider(AddTeamRiderRequest $request, string $id): RedirectResponse
    {
        try {
            $this->addRiderToTeamUseCase->execute($id, $request->riderId(), $request->year());
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
