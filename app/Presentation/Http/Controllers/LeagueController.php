<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Application\DTOs\CreateLeagueDTO;
use App\Application\UseCases\League\CreateLeagueUseCase;
use App\Application\UseCases\League\GetCreateLeagueFormDataUseCase;
use App\Application\UseCases\League\JoinLeagueUseCase;
use App\Application\UseCases\League\ListLeaguesUseCase;
use App\Application\UseCases\League\ShowLeagueUseCase;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LeagueController extends Controller
{
    public function __construct(
        private readonly ListLeaguesUseCase $listLeaguesUseCase,
        private readonly GetCreateLeagueFormDataUseCase $getCreateLeagueFormDataUseCase,
        private readonly CreateLeagueUseCase $createLeagueUseCase,
        private readonly ShowLeagueUseCase $showLeagueUseCase,
        private readonly JoinLeagueUseCase $joinLeagueUseCase,
    ) {}

    public function index(Request $request)
    {
        $leagues = $this->listLeaguesUseCase->execute($request->user());

        return Inertia::render('Leagues/Index', [
            'leagues' => $leagues->map(fn ($dto) => [
                'id' => $dto->id,
                'name' => $dto->name,
                'edition' => [
                    'name' => $dto->editionName,
                    'year' => $dto->editionYear,
                ],
                'scoring_system' => [
                    'name' => $dto->scoringSystemName,
                ],
                'member_count' => $dto->memberCount,
                'owner_id' => $dto->ownerId,
                'invite_code' => $dto->inviteCode,
                'max_players' => $dto->maxPlayers,
                'is_public' => $dto->isPublic,
            ]),
        ]);
    }

    public function create()
    {
        $data = $this->getCreateLeagueFormDataUseCase->execute();

        return Inertia::render('Leagues/Create', [
            'editions' => $data['editions'],
            'scoringSystems' => $data['scoringSystems'],
        ]);
    }

    public function show(Request $request, string $league)
    {
        $leagueModel = $this->showLeagueUseCase->execute($request->user(), $league);

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
                    'id' => $s->id,
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
            'max_players' => ['required', 'integer', 'min:2', 'max:200'],
            'is_public' => ['required', 'boolean'],
        ]);

        $dto = new CreateLeagueDTO(
            name: $validated['name'],
            editionId: $validated['edition_id'],
            scoringSystemId: $validated['scoring_system_id'],
            maxPlayers: $validated['max_players'],
            isPublic: $validated['is_public'],
        );

        $league = $this->createLeagueUseCase->execute($request->user(), $dto);

        return redirect()->route('leagues.show', $league->id);
    }

    public function join(Request $request)
    {
        $validated = $request->validate([
            'invite_code' => ['required', 'string', 'exists:leagues,invite_code'],
        ]);

        $league = $this->joinLeagueUseCase->execute($request->user(), $validated['invite_code']);

        return redirect()->route('leagues.show', $league->id);
    }
}
