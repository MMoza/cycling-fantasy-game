<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\ScoringSystemModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LeagueController extends Controller
{
    public function index(Request $request)
    {
        $leagues = $request->user()->leagues()->with(['edition', 'scoringSystem'])->get();

        return Inertia::render('Leagues/Index', [
            'leagues' => $leagues->map(fn ($league) => [
                'id' => $league->id,
                'name' => $league->name,
                'edition' => [
                    'name' => $league->edition->competition->name,
                    'year' => $league->edition->year,
                ],
                'scoring_system' => [
                    'name' => $league->scoringSystem->name,
                ],
                'member_count' => $league->users()->count(),
                'owner_id' => $league->owner_id,
                'invite_code' => $league->invite_code,
            ]),
        ]);
    }

    public function create()
    {
        $editions = EditionModel::with('competition')->get();
        $scoringSystems = ScoringSystemModel::all();

        return Inertia::render('Leagues/Create', [
            'editions' => $editions->map(fn ($e) => [
                'id' => $e->id,
                'name' => $e->competition->name,
                'year' => $e->year,
                'competition' => ['name' => $e->competition->name],
            ]),
            'scoringSystems' => $scoringSystems->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'description' => $s->description,
                'type' => $s->type->value,
            ]),
        ]);
    }

    public function show(Request $request, string $league)
    {
        $user = $request->user();

        $leagueModel = LeagueModel::with(['edition.competition', 'scoringSystem', 'stages', 'users'])
            ->find($league);

        if (! $leagueModel || ! $user->leagues()->where('leagues.id', $league)->exists()) {
            abort(404);
        }

        $user->update(['last_visited_league_id' => $league]);

        $nextStage = $leagueModel->stages()
            ->where('status', 'upcoming')
            ->orderBy('date')
            ->first();

        $totalStages = $leagueModel->stages()->where('type', '!=', 'rest')->count();
        $completedStages = $leagueModel->stages()->where('status', 'finished')->count();

        return Inertia::render('Leagues/Show', [
            'league' => [
                'id' => $leagueModel->id,
                'name' => $leagueModel->name,
                'invite_code' => $leagueModel->invite_code,
                'competition' => [
                    'name' => $leagueModel->edition->competition->name,
                    'year' => $leagueModel->edition->year,
                ],
                'scoring_system' => [
                    'name' => $leagueModel->scoringSystem->name,
                ],
                'progress' => [
                    'current_stage' => $completedStages + 1,
                    'total_stages' => $totalStages,
                ],
            ],
            'next_stage' => $nextStage ? [
                'number' => $nextStage->number,
                'name' => $nextStage->name,
                'date' => $nextStage->date->format('d M'),
                'type' => $nextStage->type->label(),
                'distance' => $nextStage->distance ? "{$nextStage->distance} km" : null,
                'origin' => $nextStage->origin,
                'destination' => $nextStage->destination,
            ] : null,
            'user_position' => [
                'rank' => '-',
                'points' => '-',
                'behind_leader' => '-',
            ],
            'stages' => $leagueModel->stages()
                ->orderBy('number')
                ->limit(5)
                ->get()
                ->map(fn ($s) => [
                    'number' => $s->number,
                    'name' => "{$s->origin} → {$s->destination}",
                    'date' => $s->date->format('d M'),
                    'type' => $s->type->label(),
                    'distance' => $s->distance ? "{$s->distance} km" : null,
                    'status' => $s->status->value,
                ]),
            'leaderboard' => [],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'edition_id' => ['required', 'uuid', 'exists:editions,id'],
            'scoring_system_id' => ['required', 'uuid', 'exists:scoring_systems,id'],
        ]);

        $league = LeagueModel::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'name' => $validated['name'],
            'edition_id' => $validated['edition_id'],
            'scoring_system_id' => $validated['scoring_system_id'],
            'owner_id' => Auth::id(),
            'invite_code' => \Illuminate\Support\Str::random(8),
        ]);

        $league->users()->attach(Auth::id(), ['role' => 'owner']);

        return redirect()->route('leagues.show', $league->id);
    }

    public function join(Request $request)
    {
        $validated = $request->validate([
            'invite_code' => ['required', 'string', 'exists:leagues,invite_code'],
        ]);

        $league = LeagueModel::where('invite_code', $validated['invite_code'])->first();

        if ($request->user()->leagues()->where('leagues.id', $league->id)->exists()) {
            return redirect()->route('leagues.show', $league->id);
        }

        $league->users()->attach($request->user()->id, ['role' => 'member']);

        return redirect()->route('leagues.show', $league->id);
    }
}
