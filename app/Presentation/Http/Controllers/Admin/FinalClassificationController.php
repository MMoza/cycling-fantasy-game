<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Admin;

use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\FinalClassificationModel;
use App\Infrastructure\Persistence\Models\RiderModel;
use App\Infrastructure\Persistence\Models\TeamModel;
use App\Presentation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class FinalClassificationController extends Controller
{
    public function edit(string $editionId): Response
    {
        $edition = EditionModel::with('competition')->findOrFail($editionId);

        $riders = RiderModel::orderBy('last_name')->orderBy('first_name')
            ->get()
            ->map(fn ($r) => ['value' => $r->id, 'label' => trim("{$r->last_name} {$r->first_name}")]);

        $teams = TeamModel::orderBy('name')
            ->get()
            ->map(fn ($t) => ['value' => $t->id, 'label' => $t->name]);

        $classifications = FinalClassificationModel::where('edition_id', $editionId)
            ->get()
            ->groupBy('category')
            ->map(fn ($items) => $items->keyBy('position'));

        return Inertia::render('Admin/Editions/FinalClassifications', [
            'edition' => [
                'id' => $edition->id,
                'year' => $edition->year,
                'competition' => $edition->competition->name,
            ],
            'riders' => $riders,
            'teams' => $teams,
            'classifications' => $classifications,
        ]);
    }

    public function update(Request $request, string $editionId): RedirectResponse
    {
        $edition = EditionModel::findOrFail($editionId);

        $validated = $request->validate([
            'classifications' => 'required|array',
            'classifications.gc_top_5' => 'nullable|array|size:5',
            'classifications.gc_top_5.*' => 'required|string|exists:riders,id',
            'classifications.points_winner' => 'nullable|array|size:3',
            'classifications.points_winner.*' => 'required|string|exists:riders,id',
            'classifications.mountains_winner' => 'nullable|array|size:3',
            'classifications.mountains_winner.*' => 'required|string|exists:riders,id',
            'classifications.youth_winner' => 'nullable|array|size:3',
            'classifications.youth_winner.*' => 'required|string|exists:riders,id',
            'classifications.teams_winner' => 'nullable|string|exists:teams,id',
            'classifications.super_combativo' => 'nullable|string|exists:riders,id',
        ]);

        DB::transaction(function () use ($editionId, $validated): void {
            FinalClassificationModel::where('edition_id', $editionId)->delete();

            $categories = [
                'gc_top_5',
                'points_winner',
                'mountains_winner',
                'youth_winner',
            ];

            foreach ($categories as $category) {
                $items = $validated['classifications'][$category] ?? [];

                foreach ($items as $position => $riderId) {
                    FinalClassificationModel::create([
                        'id' => Str::uuid()->toString(),
                        'edition_id' => $editionId,
                        'category' => $category,
                        'rider_id' => $riderId,
                        'position' => $position + 1,
                    ]);
                }
            }

            if (! empty($validated['classifications']['teams_winner'])) {
                FinalClassificationModel::create([
                    'id' => Str::uuid()->toString(),
                    'edition_id' => $editionId,
                    'category' => 'teams_winner',
                    'team_id' => $validated['classifications']['teams_winner'],
                ]);
            }

            if (! empty($validated['classifications']['super_combativo'])) {
                FinalClassificationModel::create([
                    'id' => Str::uuid()->toString(),
                    'edition_id' => $editionId,
                    'category' => 'super_combativo',
                    'rider_id' => $validated['classifications']['super_combativo'],
                ]);
            }
        });

        return redirect()->back()->with('success', 'Clasificaciones guardadas correctamente');
    }
}
