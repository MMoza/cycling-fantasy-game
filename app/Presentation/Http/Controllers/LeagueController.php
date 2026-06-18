<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\ScoringSystemModel;
use Illuminate\Http\Request;
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
}
