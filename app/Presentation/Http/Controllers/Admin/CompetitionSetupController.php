<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Admin;

use App\Application\Exceptions\ApplicationException;
use App\Application\UseCases\Admin\CompetitionSetup\AddTeamToCompetitionUseCase;
use App\Application\UseCases\Admin\CompetitionSetup\RemoveTeamFromCompetitionUseCase;
use App\Application\UseCases\Admin\CompetitionSetup\ShowSetupUseCase;
use App\Application\UseCases\Admin\CompetitionSetup\ToggleRiderUseCase;
use App\Presentation\Http\Controllers\Controller;
use App\Presentation\Http\Requests\Admin\AddCompetitionTeamRequest;
use App\Presentation\Http\Requests\Admin\ToggleCompetitionRiderRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CompetitionSetupController extends Controller
{
    public function __construct(
        private readonly ShowSetupUseCase $showSetupUseCase,
        private readonly AddTeamToCompetitionUseCase $addTeamToCompetitionUseCase,
        private readonly RemoveTeamFromCompetitionUseCase $removeTeamFromCompetitionUseCase,
        private readonly ToggleRiderUseCase $toggleRiderUseCase,
    ) {}

    public function show(string $competitionId, string $editionId): Response
    {
        $data = $this->showSetupUseCase->execute($competitionId, $editionId);

        return Inertia::render('Admin/Competitions/Setup', [
            'competition' => $data['competition'],
            'edition' => $data['edition'],
            'participants' => $data['participants'],
            'teams' => $data['teams'],
        ]);
    }

    public function addTeam(AddCompetitionTeamRequest $request, string $competitionId, string $editionId): RedirectResponse
    {
        try {
            $this->addTeamToCompetitionUseCase->execute($competitionId, $editionId, $request->teamId());
        } catch (ApplicationException $e) {
            return redirect()->back()->withErrors(['team_id' => $e->getMessage()]);
        }

        return redirect()->route('admin.competitions.setup', [$competitionId, $editionId]);
    }

    public function removeTeam(string $competitionId, string $editionId, string $teamId): RedirectResponse
    {
        $this->removeTeamFromCompetitionUseCase->execute($competitionId, $editionId, $teamId);

        return redirect()->route('admin.competitions.setup', [$competitionId, $editionId]);
    }

    public function toggleRider(ToggleCompetitionRiderRequest $request, string $competitionId, string $editionId): RedirectResponse
    {
        $this->toggleRiderUseCase->execute(
            $competitionId,
            $editionId,
            $request->teamId(),
            $request->riderId(),
            $request->active(),
        );

        return redirect()->route('admin.competitions.setup', [$competitionId, $editionId]);
    }
}
