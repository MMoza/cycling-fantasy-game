<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Admin;

use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Infrastructure\Persistence\Models\CountryModel;
use App\Presentation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompetitionController extends Controller
{
    public function index(): Response
    {
        $competitions = CompetitionModel::with('country')
            ->withCount('editions')
            ->orderBy('name')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'type' => $c->type->label(),
                'country_id' => $c->country_id,
                'active' => $c->active,
                'editions_count' => $c->editions_count,
            ]);

        $countries = CountryModel::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($c) => ['value' => $c->id, 'label' => $c->name]);

        return Inertia::render('Admin/Competitions/Index', [
            'competitions' => $competitions,
            'countries' => $countries,
        ]);
    }

    public function create(): Response
    {
        $countries = CountryModel::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($c) => ['value' => $c->id, 'label' => $c->name]);

        return Inertia::render('Admin/Competitions/Form', [
            'competition' => null,
            'countries' => $countries,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:grand_tour,one_week,classic',
            'country_id' => 'required|string|size:2|exists:countries,id',
        ]);

        CompetitionModel::create($validated);

        return redirect()->route('admin.competitions.index');
    }

    public function edit(string $id): Response
    {
        $competition = CompetitionModel::findOrFail($id);

        $countries = CountryModel::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($c) => ['value' => $c->id, 'label' => $c->name]);

        return Inertia::render('Admin/Competitions/Form', [
            'competition' => [
                'id' => $competition->id,
                'name' => $competition->name,
                'type' => $competition->type->value,
                'country_id' => $competition->country_id,
                'active' => $competition->active,
            ],
            'countries' => $countries,
        ]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $competition = CompetitionModel::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:grand_tour,one_week,classic',
            'country_id' => 'required|string|size:2|exists:countries,id',
            'active' => 'boolean',
        ]);

        $competition->update($validated);

        return redirect()->route('admin.competitions.index');
    }
}
