<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Admin;

use App\Domain\ValueObjects\CompetitionType;
use App\Domain\ValueObjects\StageStatus;
use App\Domain\ValueObjects\StageType;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\StageModel;
use App\Presentation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class StageController extends Controller
{
    public function index(string $editionId): Response
    {
        $edition = EditionModel::with('competition')->findOrFail($editionId);

        $stages = StageModel::where('edition_id', $editionId)
            ->orderBy('number')
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'number' => $s->number,
                'name' => $s->name,
                'date' => $s->date->format('Y-m-d'),
                'type' => $s->type->label(),
                'type_value' => $s->type->value,
                'distance' => $s->distance,
                'elevation_gain' => $s->elevation_gain,
                'difficulty' => $s->difficulty,
                'origin' => $s->origin,
                'destination' => $s->destination,
                'status' => $s->status->label(),
            ]);

        return Inertia::render('Admin/Stages/Index', [
            'edition' => [
                'id' => $edition->id,
                'year' => $edition->year,
                'competition_id' => $edition->competition->id,
                'competition' => $edition->competition->name,
            ],
            'stages' => $stages,
        ]);
    }

    public function create(string $editionId): Response
    {
        $edition = EditionModel::with('competition')->findOrFail($editionId);

        return Inertia::render('Admin/Stages/Form', [
            'edition' => [
                'id' => $edition->id,
                'year' => $edition->year,
                'competition' => $edition->competition->name,
                'competition_id' => $edition->competition->id,
                'competition_type' => $edition->competition->type->value,
            ],
            'stage' => null,
            'stageTypes' => collect(StageType::cases())->map(fn ($t) => [
                'value' => $t->value,
                'label' => $t->label(),
            ]),
        ]);
    }

    public function store(Request $request, string $editionId): RedirectResponse
    {
        $edition = EditionModel::with('competition')->findOrFail($editionId);

        if ($edition->competition->type === CompetitionType::Classic) {
            $existing = StageModel::where('edition_id', $editionId)->count();
            if ($existing >= 1) {
                return redirect()->back()->withErrors(['error' => 'Las clásicas solo pueden tener una etapa.']);
            }
        }

        $validated = $request->validate([
            'number' => 'required|integer|min:1',
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'type' => 'required|string',
            'distance' => 'nullable|numeric|min:0',
            'elevation_gain' => 'nullable|integer|min:0',
            'difficulty' => 'nullable|integer|min:1|max:3',
            'origin' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'profile_image' => 'nullable|image|max:2048',
        ]);

        $profileImage = null;

        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store('stages/profiles', 'public');
            $profileImage = Storage::url($path);
        }

        StageModel::create([
            'id' => Str::uuid()->toString(),
            'edition_id' => $edition->id,
            'number' => $validated['number'],
            'name' => $validated['name'],
            'date' => $validated['date'],
            'type' => $validated['type'],
            'distance' => $validated['distance'],
            'elevation_gain' => $validated['elevation_gain'],
            'difficulty' => $validated['difficulty'],
            'origin' => $validated['origin'],
            'destination' => $validated['destination'],
            'profile_image' => $profileImage,
            'status' => 'upcoming',
        ]);

        return redirect()->route('admin.editions.stages.index', $edition->id);
    }

    public function edit(string $editionId, string $id): Response
    {
        $edition = EditionModel::with('competition')->findOrFail($editionId);
        $stage = StageModel::where('edition_id', $editionId)->findOrFail($id);

        return Inertia::render('Admin/Stages/Form', [
            'edition' => [
                'id' => $edition->id,
                'year' => $edition->year,
                'competition' => $edition->competition->name,
                'competition_id' => $edition->competition->id,
                'competition_type' => $edition->competition->type->value,
            ],
            'stage' => [
                'id' => $stage->id,
                'number' => $stage->number,
                'name' => $stage->name,
                'date' => $stage->date->format('Y-m-d'),
                'type' => $stage->type->value,
                'distance' => $stage->distance,
                'elevation_gain' => $stage->elevation_gain,
                'difficulty' => $stage->difficulty,
                'origin' => $stage->origin,
                'destination' => $stage->destination,
                'profile_image' => $stage->profile_image,
                'status' => $stage->status->value,
            ],
            'stageTypes' => collect(StageType::cases())->map(fn ($t) => [
                'value' => $t->value,
                'label' => $t->label(),
            ]),
        ]);
    }

    public function update(Request $request, string $editionId, string $id): RedirectResponse
    {
        $stage = StageModel::where('edition_id', $editionId)->findOrFail($id);

        $validated = $request->validate([
            'number' => 'required|integer|min:1',
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'type' => 'required|string',
            'distance' => 'nullable|numeric|min:0',
            'elevation_gain' => 'nullable|integer|min:0',
            'difficulty' => 'nullable|integer|min:1|max:3',
            'origin' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'profile_image' => 'nullable|image|max:2048',
        ]);

        $data = collect($validated)->except('profile_image')->toArray();

        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store('stages/profiles', 'public');
            $data['profile_image'] = Storage::url($path);
        }

        $stage->update($data);

        return redirect()->route('admin.editions.stages.index', $editionId);
    }

    public function show(string $editionId, string $id): Response
    {
        $edition = EditionModel::with('competition')->findOrFail($editionId);
        $stage = StageModel::where('edition_id', $editionId)->findOrFail($id);

        $participantRiders = DB::table('competition_participants')
            ->join('riders', 'competition_participants.rider_id', '=', 'riders.id')
            ->where('competition_participants.competition_id', $edition->competition_id)
            ->where('competition_participants.edition_id', $editionId)
            ->where('competition_participants.team_id', '!=', '') // all
            ->select('riders.id', 'riders.first_name', 'riders.last_name', 'riders.country_id')
            ->distinct()
            ->orderBy('riders.last_name')
            ->orderBy('riders.first_name')
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'name' => trim("{$r->last_name} {$r->first_name}"),
                'country_id' => $r->country_id,
            ]);

        $results = DB::table('stage_results')
            ->where('stage_id', $stage->id)
            ->orderBy('position')
            ->get();

        return Inertia::render('Admin/Stages/Show', [
            'edition' => [
                'id' => $edition->id,
                'year' => $edition->year,
                'competition' => $edition->competition->name,
            ],
            'stage' => [
                'id' => $stage->id,
                'number' => $stage->number,
                'name' => $stage->name,
                'type' => $stage->type->label(),
                'type_value' => $stage->type->value,
                'date' => $stage->date->format('Y-m-d'),
                'distance' => $stage->distance,
                'elevation_gain' => $stage->elevation_gain,
                'difficulty' => $stage->difficulty,
                'origin' => $stage->origin,
                'destination' => $stage->destination,
                'status' => $stage->status->value,
                'status_label' => $stage->status->label(),
            ],
            'availableRiders' => $participantRiders,
            'results' => $results->map(fn ($r) => [
                'id' => $r->id,
                'rider_id' => $r->rider_id,
                'position' => $r->position,
                'time' => $r->time,
                'gap' => $r->gap,
                'is_gc_leader' => (bool) $r->is_gc_leader,
                'is_combativo' => (bool) $r->is_combativo,
            ]),
        ]);
    }

    public function markFinished(string $editionId, string $id): RedirectResponse
    {
        $stage = StageModel::where('edition_id', $editionId)->findOrFail($id);

        if ($stage->status === StageStatus::Finished) {
            return redirect()->back();
        }

        $stage->update(['status' => StageStatus::Finished->value]);

        return redirect()->route('admin.editions.stages.show', [$editionId, $id]);
    }

    public function storeResult(Request $request, string $editionId, string $id): RedirectResponse
    {
        $stage = StageModel::where('edition_id', $editionId)->findOrFail($id);

        $validated = $request->validate([
            'results' => 'required|array|min:1',
            'results.*.rider_id' => 'required|string',
            'results.*.position' => 'required|integer|min:1',
            'results.*.time' => 'nullable|string|max:50',
            'results.*.gap' => 'nullable|string|max:50',
            'results.*.is_gc_leader' => 'nullable|boolean',
            'results.*.is_combativo' => 'nullable|boolean',
        ]);

        DB::table('stage_results')->where('stage_id', $stage->id)->delete();

        foreach ($validated['results'] as $result) {
            DB::table('stage_results')->insert([
                'id' => Str::uuid()->toString(),
                'stage_id' => $stage->id,
                'rider_id' => $result['rider_id'],
                'position' => $result['position'],
                'time' => $result['time'] ?? null,
                'gap' => $result['gap'] ?? null,
                'is_gc_leader' => $result['is_gc_leader'] ?? false,
                'is_combativo' => $result['is_combativo'] ?? false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $stage->update(['status' => StageStatus::Finished->value]);

        return redirect()->route('admin.editions.stages.show', [$editionId, $id]);
    }

    public function markUpcoming(string $editionId, string $id): RedirectResponse
    {
        $stage = StageModel::where('edition_id', $editionId)->findOrFail($id);
        $stage->update(['status' => StageStatus::Upcoming->value]);

        return redirect()->route('admin.editions.stages.show', [$editionId, $id]);
    }
}
