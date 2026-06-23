<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Admin;

use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Presentation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Str;

class EditionController extends Controller
{
    public function index(string $competitionId): Response
    {
        $competition = CompetitionModel::findOrFail($competitionId);

        $editions = EditionModel::where('competition_id', $competitionId)
            ->orderBy('year', 'desc')
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'year' => $e->year,
                'start_date' => $e->start_date->format('Y-m-d'),
                'end_date' => $e->end_date->format('Y-m-d'),
                'status' => $e->status->label(),
            ]);

        return Inertia::render('Admin/Editions/Index', [
            'competition' => ['id' => $competition->id, 'name' => $competition->name],
            'editions' => $editions,
        ]);
    }

    public function create(string $competitionId): Response
    {
        $competition = CompetitionModel::findOrFail($competitionId);

        return Inertia::render('Admin/Editions/Form', [
            'competition' => ['id' => $competition->id, 'name' => $competition->name],
            'edition' => null,
        ]);
    }

    public function store(Request $request, string $competitionId): RedirectResponse
    {
        $competition = CompetitionModel::findOrFail($competitionId);

        $validated = $request->validate([
            'year' => 'required|integer|min:1900|max:2100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        EditionModel::create([
            'id' => Str::uuid()->toString(),
            'competition_id' => $competition->id,
            'year' => $validated['year'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'status' => 'upcoming',
        ]);

        return redirect()->route('admin.competitions.editions.index', $competition->id);
    }

    public function edit(string $competitionId, string $id): Response
    {
        $competition = CompetitionModel::findOrFail($competitionId);
        $edition = EditionModel::where('competition_id', $competitionId)->findOrFail($id);

        return Inertia::render('Admin/Editions/Form', [
            'competition' => ['id' => $competition->id, 'name' => $competition->name],
            'edition' => [
                'id' => $edition->id,
                'year' => $edition->year,
                'start_date' => $edition->start_date->format('Y-m-d'),
                'end_date' => $edition->end_date->format('Y-m-d'),
                'status' => $edition->status->value,
            ],
        ]);
    }

    public function update(Request $request, string $competitionId, string $id): RedirectResponse
    {
        $edition = EditionModel::where('competition_id', $competitionId)->findOrFail($id);

        $validated = $request->validate([
            'year' => 'required|integer|min:1900|max:2100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|string|in:upcoming,ongoing,finished',
        ]);

        $edition->update($validated);

        return redirect()->route('admin.competitions.editions.index', $competitionId);
    }
}
