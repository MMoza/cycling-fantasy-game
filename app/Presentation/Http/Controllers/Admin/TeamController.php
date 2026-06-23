<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Admin;

use App\Infrastructure\Persistence\Models\RiderModel;
use App\Infrastructure\Persistence\Models\TeamModel;
use App\Presentation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Str;

class TeamController extends Controller
{
    public function index(): Response
    {
        $teams = TeamModel::withCount('rosters')
            ->orderBy('name')
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'country' => $t->country,
                'logo_url' => $t->logo_url,
                'riders_count' => $t->rosters_count,
            ]);

        return Inertia::render('Admin/Teams/Index', [
            'teams' => $teams,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Teams/Form', [
            'team' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'country' => 'nullable|string|max:255',
            'logo_url' => 'nullable|string|max:255',
        ]);

        TeamModel::create([
            'id' => Str::uuid()->toString(),
            ...$validated,
        ]);

        return redirect()->route('admin.teams.index');
    }

    public function show(string $id): Response
    {
        $team = TeamModel::with('rosters.rider')->findOrFail($id);

        $rostersByYear = $team->rosters
            ->groupBy('year')
            ->map(fn ($rosters, $year) => [
                'year' => (int) $year,
                'riders' => $rosters->map(fn ($r) => [
                    'id' => $r->rider->id,
                    'name' => $r->rider->name,
                    'nationality' => $r->rider->nationality,
                ]),
            ])
            ->values();

        $allRiders = RiderModel::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Teams/Show', [
            'team' => [
                'id' => $team->id,
                'name' => $team->name,
                'country' => $team->country,
                'logo_url' => $team->logo_url,
            ],
            'rosters' => $rostersByYear,
            'allRiders' => $allRiders,
        ]);
    }

    public function edit(string $id): Response
    {
        $team = TeamModel::findOrFail($id);

        return Inertia::render('Admin/Teams/Form', [
            'team' => [
                'id' => $team->id,
                'name' => $team->name,
                'country' => $team->country,
                'logo_url' => $team->logo_url,
            ],
        ]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $team = TeamModel::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'country' => 'nullable|string|max:255',
            'logo_url' => 'nullable|string|max:255',
        ]);

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
