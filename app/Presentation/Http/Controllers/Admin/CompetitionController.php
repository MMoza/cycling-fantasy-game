<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Admin;

use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Presentation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Str;

class CompetitionController extends Controller
{
    public function index(): Response
    {
        $competitions = CompetitionModel::withCount('editions')
            ->orderBy('name')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'type' => $c->type->label(),
                'country' => $c->country,
                'active' => $c->active,
                'editions_count' => $c->editions_count,
            ]);

        return Inertia::render('Admin/Competitions/Index', [
            'competitions' => $competitions,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Competitions/Form', [
            'competition' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:grand_tour,one_week,classic',
            'country' => 'required|string|max:255',
        ]);

        CompetitionModel::create([
            'id' => Str::uuid()->toString(),
            'name' => $validated['name'],
            'type' => $validated['type'],
            'country' => $validated['country'],
            'active' => true,
        ]);

        return redirect()->route('admin.competitions.index');
    }

    public function edit(string $id): Response
    {
        $competition = CompetitionModel::findOrFail($id);

        return Inertia::render('Admin/Competitions/Form', [
            'competition' => [
                'id' => $competition->id,
                'name' => $competition->name,
                'type' => $competition->type->value,
                'country' => $competition->country,
                'active' => $competition->active,
            ],
        ]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $competition = CompetitionModel::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:grand_tour,one_week,classic',
            'country' => 'required|string|max:255',
            'active' => 'boolean',
        ]);

        $competition->update($validated);

        return redirect()->route('admin.competitions.index');
    }
}
