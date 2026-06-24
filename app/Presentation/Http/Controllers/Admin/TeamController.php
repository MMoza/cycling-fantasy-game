<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Admin;

use App\Infrastructure\Persistence\Models\CountryModel;
use App\Infrastructure\Persistence\Models\RiderModel;
use App\Infrastructure\Persistence\Models\TeamModel;
use App\Infrastructure\Persistence\Models\TeamRosterModel;
use App\Presentation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TeamController extends Controller
{
    public function index(): Response
    {
        $teams = TeamModel::with('country')
            ->withCount('rosters')
            ->orderBy('name')
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'abbreviation' => $t->abbreviation,
                'country_id' => $t->country_id,
                'logo_url' => $t->logo_url,
                'riders_count' => $t->rosters_count,
            ]);

        $countries = CountryModel::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($c) => ['value' => $c->id, 'label' => $c->name]);

        return Inertia::render('Admin/Teams/Index', [
            'teams' => $teams,
            'countries' => $countries,
        ]);
    }

    public function create(): Response
    {
        $countries = CountryModel::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($c) => ['value' => $c->id, 'label' => $c->name]);

        return Inertia::render('Admin/Teams/Form', [
            'team' => null,
            'countries' => $countries,
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

        $validated['abbreviation'] = $validated['abbreviation'] ? strtoupper($validated['abbreviation']) : null;

        TeamModel::create($validated);

        return redirect()->route('admin.teams.index');
    }

    public function show(string $id): Response
    {
        $team = TeamModel::with('rosters.rider', 'country')->findOrFail($id);

        $rostersByYear = $team->rosters
            ->groupBy('year')
            ->map(fn ($rosters, $year) => [
                'year' => (int) $year,
                'riders' => $rosters->map(fn ($r) => [
                    'id' => $r->rider->id,
                    'full_name' => $r->rider->full_name,
                    'country_id' => $r->rider->country_id,
                ]),
            ])
            ->values();

        $allRiders = RiderModel::orderBy('last_name')
            ->orderBy('first_name')
            ->get(['id', 'last_name', 'first_name'])
            ->map(fn ($r) => [
                'id' => $r->id,
                'full_name' => $r->full_name,
            ]);

        $countries = CountryModel::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($c) => ['value' => $c->id, 'label' => $c->name]);

        return Inertia::render('Admin/Teams/Show', [
            'team' => [
                'id' => $team->id,
                'name' => $team->name,
                'abbreviation' => $team->abbreviation,
                'country_id' => $team->country_id,
                'logo_url' => $team->logo_url,
            ],
            'rosters' => $rostersByYear,
            'allRiders' => $allRiders,
            'countries' => $countries,
        ]);
    }

    public function edit(string $id): Response
    {
        $team = TeamModel::findOrFail($id);

        $countries = CountryModel::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($c) => ['value' => $c->id, 'label' => $c->name]);

        return Inertia::render('Admin/Teams/Form', [
            'team' => [
                'id' => $team->id,
                'name' => $team->name,
                'abbreviation' => $team->abbreviation,
                'country_id' => $team->country_id,
                'logo_url' => $team->logo_url,
            ],
            'countries' => $countries,
        ]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $team = TeamModel::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'abbreviation' => 'nullable|string|size:3|unique:teams,abbreviation,' . $id,
            'country_id' => 'nullable|string|size:2|exists:countries,id',
            'logo_url' => 'nullable|string|max:255',
        ]);

        $validated['abbreviation'] = $validated['abbreviation'] ? strtoupper($validated['abbreviation']) : null;

        $team->update($validated);

        return redirect()->route('admin.teams.index');
    }

    public function addRider(Request $request, string $id): RedirectResponse
    {
        $team = TeamModel::findOrFail($id);

        $validated = $request->validate([
            'rider_id' => 'required|string|exists:riders,id',
            'year' => 'required|integer|min:2020|max:2100',
        ]);

        $alreadyRostered = TeamRosterModel::where('rider_id', $validated['rider_id'])
            ->where('year', $validated['year'])
            ->where('team_id', '!=', $id)
            ->exists();

        if ($alreadyRostered) {
            return redirect()->back()->withErrors(['rider_id' => 'Este corredor ya pertenece a otro equipo en esta temporada.']);
        }

        $team->rosters()->firstOrCreate([
            'rider_id' => $validated['rider_id'],
            'year' => $validated['year'],
        ]);

        return redirect()->route('admin.teams.show', $id);
    }

    public function removeRider(string $id, string $riderId, int $year): RedirectResponse
    {
        $team = TeamModel::findOrFail($id);

        $team->rosters()
            ->where('rider_id', $riderId)
            ->where('year', $year)
            ->delete();

        return redirect()->route('admin.teams.show', $id);
    }
}
