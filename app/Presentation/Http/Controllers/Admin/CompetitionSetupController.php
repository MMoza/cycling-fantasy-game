<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Admin;

use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Infrastructure\Persistence\Models\CompetitionParticipantModel;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\TeamModel;
use App\Presentation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Str;

class CompetitionSetupController extends Controller
{
    public function show(string $competitionId, string $editionId): Response
    {
        $competition = CompetitionModel::findOrFail($competitionId);
        $edition = EditionModel::with('stages')->findOrFail($editionId);
        $year = $edition->year;

        $teamIds = CompetitionParticipantModel::where('competition_id', $competitionId)
            ->where('edition_id', $editionId)
            ->distinct()
            ->pluck('team_id');

        $activeRiderIds = CompetitionParticipantModel::where('competition_id', $competitionId)
            ->where('edition_id', $editionId)
            ->pluck('rider_id')
            ->toArray();

        $participants = TeamModel::whereIn('id', $teamIds)
            ->with(['rosters' => fn ($q) => $q->where('year', $year)->with('rider.country')])
            ->get()
            ->map(fn ($team) => [
                'team_id' => $team->id,
                'team_name' => $team->name,
                'riders' => $team->rosters->map(fn ($r) => [
                    'id' => $r->rider->id,
                    'full_name' => $r->rider->full_name,
                    'country' => $r->rider->country?->name,
                    'active' => in_array($r->rider_id, $activeRiderIds),
                ]),
            ]);

        $allTeams = TeamModel::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Competitions/Setup', [
            'competition' => ['id' => $competition->id, 'name' => $competition->name],
            'edition' => [
                'id' => $edition->id,
                'year' => $edition->year,
                'stages_count' => $edition->stages->count(),
            ],
            'participants' => $participants,
            'teams' => $allTeams,
        ]);
    }

    public function addTeam(Request $request, string $competitionId, string $editionId): RedirectResponse
    {
        $competition = CompetitionModel::findOrFail($competitionId);
        EditionModel::where('id', $editionId)->where('competition_id', $competitionId)->firstOrFail();

        $validated = $request->validate([
            'team_id' => 'required|string|exists:teams,id',
        ]);

        $team = TeamModel::findOrFail($validated['team_id']);

        $rosteredRiders = $team->rosters()
            ->where('year', $competition->editions()->where('id', $editionId)->first()->year)
            ->with('rider')
            ->get();

        if ($rosteredRiders->isEmpty()) {
            return redirect()->back()->withErrors(['team_id' => 'Este equipo no tiene corredores en su plantilla para esta temporada.']);
        }

        foreach ($rosteredRiders as $roster) {
            CompetitionParticipantModel::firstOrCreate([
                'competition_id' => $competitionId,
                'edition_id' => $editionId,
                'team_id' => $validated['team_id'],
                'rider_id' => $roster->rider_id,
            ]);
        }

        return redirect()->route('admin.competitions.setup', [$competitionId, $editionId]);
    }

    public function removeTeam(string $competitionId, string $editionId, string $teamId): RedirectResponse
    {
        CompetitionParticipantModel::where('competition_id', $competitionId)
            ->where('edition_id', $editionId)
            ->where('team_id', $teamId)
            ->delete();

        return redirect()->route('admin.competitions.setup', [$competitionId, $editionId]);
    }

    public function toggleRider(Request $request, string $competitionId, string $editionId): RedirectResponse
    {
        $validated = $request->validate([
            'team_id' => 'required|string|exists:teams,id',
            'rider_id' => 'required|string|exists:riders,id',
            'active' => 'required|boolean',
        ]);

        if ($validated['active']) {
            CompetitionParticipantModel::create([
                'id' => Str::uuid()->toString(),
                'competition_id' => $competitionId,
                'edition_id' => $editionId,
                'team_id' => $validated['team_id'],
                'rider_id' => $validated['rider_id'],
            ]);
        } else {
            CompetitionParticipantModel::where('competition_id', $competitionId)
                ->where('edition_id', $editionId)
                ->where('team_id', $validated['team_id'])
                ->where('rider_id', $validated['rider_id'])
                ->delete();
        }

        return redirect()->route('admin.competitions.setup', [$competitionId, $editionId]);
    }
}
