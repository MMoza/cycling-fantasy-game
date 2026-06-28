<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Admin;

use App\Application\Exceptions\ApplicationException;
use App\Application\UseCases\Admin\CompetitionSetup\AddTeamToCompetitionUseCase;
use App\Application\UseCases\Admin\CompetitionSetup\RemoveTeamFromCompetitionUseCase;
use App\Application\UseCases\Admin\CompetitionSetup\ShowSetupUseCase;
use App\Application\UseCases\Admin\CompetitionSetup\ToggleRiderUseCase;
use App\Presentation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function addTeam(Request $request, string $competitionId, string $editionId): RedirectResponse
    {
        $validated = $request->validate([
            'team_id' => 'required|string|exists:teams,id',
        ]);

        try {
            $this->addTeamToCompetitionUseCase->execute($competitionId, $editionId, $validated['team_id']);
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

    public function toggleRider(Request $request, string $competitionId, string $editionId): RedirectResponse
    {
        $validated = $request->validate([
            'team_id' => 'required|string|exists:teams,id',
            'rider_id' => 'required|string|exists:riders,id',
            'active' => 'required|boolean',
        ]);

        $this->toggleRiderUseCase->execute(
            $competitionId,
            $editionId,
            $validated['team_id'],
            $validated['rider_id'],
            $validated['active'],
        );

        return redirect()->route('admin.competitions.setup', [$competitionId, $editionId]);
    }
}
