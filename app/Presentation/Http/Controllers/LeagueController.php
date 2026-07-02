<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Application\DTOs\CreateLeagueDTO;
use App\Application\Exceptions\ApplicationException;
use App\Application\UseCases\League\CreateLeagueUseCase;
use App\Application\UseCases\League\GetCreateLeagueFormDataUseCase;
use App\Application\UseCases\League\JoinLeagueUseCase;
use App\Application\UseCases\League\ListLeaguesUseCase;
use App\Application\UseCases\League\ShowLeagueUseCase;
use App\Application\UseCases\League\UpdateLeagueUseCase;
use App\Infrastructure\Persistence\Models\ScoreEventModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class LeagueController extends Controller
{
    public function __construct(
        private readonly ListLeaguesUseCase $listLeaguesUseCase,
        private readonly GetCreateLeagueFormDataUseCase $getCreateLeagueFormDataUseCase,
        private readonly CreateLeagueUseCase $createLeagueUseCase,
        private readonly ShowLeagueUseCase $showLeagueUseCase,
        private readonly JoinLeagueUseCase $joinLeagueUseCase,
        private readonly UpdateLeagueUseCase $updateLeagueUseCase,
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

        $userId = $request->user()->id;

        $scoresPerUser = ScoreEventModel::where('league_id', $leagueModel->id)
            ->selectRaw('user_id, SUM(points) as total_points')
            ->groupBy('user_id')
            ->pluck('total_points', 'user_id');

        $members = DB::table('league_user')
            ->where('league_id', $leagueModel->id)
            ->join('users', 'users.id', '=', 'league_user.user_id')
            ->select('users.id', 'users.name')
            ->get();

        $leaderboard = $members
            ->map(fn ($member) => [
                'user_id' => $member->id,
                'user_name' => $member->name,
                'points' => (int) ($scoresPerUser[$member->id] ?? 0),
                'is_current_user' => $member->id === $userId,
            ])
            ->sortByDesc('points')
            ->values()
            ->map(fn ($entry, $index) => [
                'rank' => $index + 1,
                ...$entry,
            ]);

        $topPoints = $leaderboard->first()['points'] ?? 0;

        $leaderboard = $leaderboard->map(fn ($entry) => [
            ...$entry,
            'behind_leader' => $topPoints - $entry['points'],
        ]);

        $userEntry = $leaderboard->firstWhere('is_current_user', true);

        $activityLogs = $leagueModel->activityLogs->map(fn ($log) => [
            'id' => $log->id,
            'type' => $log->type->value,
            'title' => $log->title,
            'description' => $log->description,
            'data' => $log->data,
            'created_at' => $log->created_at->diffForHumans(),
        ]);

        return Inertia::render('Leagues/Show', [
            'league' => [
                'id' => $leagueModel->id,
                'name' => $leagueModel->name,
                'invite_code' => $leagueModel->invite_code,
                'competition' => [
                    'name' => $leagueModel->edition->competition->name,
                    'year' => $leagueModel->edition->year,
                ],
                'owner_id' => $leagueModel->owner_id,
                'scoring_system' => [
                    'name' => $leagueModel->scoringSystem->name,
                    'type' => $leagueModel->scoringSystem->type->value,
                    'description' => $leagueModel->scoringSystem->type->description(),
                    'rules' => $leagueModel->scoringSystem->rules->map(fn ($rule) => [
                        'type' => $rule->type->value,
                        'label' => $rule->type->label(),
                        'context' => $rule->type->context()->value,
                        'points' => $rule->points,
                        'difficulty' => $rule->difficulty,
                        'position' => $rule->position,
                    ])->values(),
                ],
                'is_public' => $leagueModel->is_public,
                'max_players' => $leagueModel->max_players,
                'invite_code' => $leagueModel->invite_code,
                'member_count' => $members->count(),
                'is_owner' => $leagueModel->owner_id === $userId,
                'progress' => [
                    'current_stage' => $completedStages + 1,
                    'total_stages' => $totalStages,
                ],
            ],
            'next_stage' => $nextStage ? [
                'id' => $nextStage->id,
                'number' => $nextStage->number,
                'name' => $nextStage->name,
                'date' => $nextStage->date->format('d M'),
                'type' => $nextStage->type->label(),
                'distance' => $nextStage->distance ? "{$nextStage->distance} km" : null,
                'origin' => $nextStage->origin,
                'destination' => $nextStage->destination,
            ] : null,
            'user_position' => $userEntry ? [
                'rank' => (string) $userEntry['rank'],
                'points' => (string) $userEntry['points'],
                'behind_leader' => $userEntry['behind_leader'].' pts',
            ] : [
                'rank' => '-',
                'points' => '0',
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
            'leaderboard' => $leaderboard->values(),
            'activity_logs' => $activityLogs,
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

        try {
            $league = $this->createLeagueUseCase->execute($request->user(), $dto);
        } catch (ApplicationException $e) {
            return redirect()->back()->withErrors(['plan' => $e->getMessage()])->withInput();
        }

        return redirect()->route('leagues.show', $league->id);
    }

    public function join(Request $request)
    {
        $validated = $request->validate([
            'invite_code' => ['required', 'string', 'exists:leagues,invite_code'],
        ]);

        try {
            $league = $this->joinLeagueUseCase->execute($request->user(), $validated['invite_code']);
        } catch (ApplicationException $e) {
            return redirect()->back()->withErrors(['invite_code' => $e->getMessage()]);
        }

        return redirect()->route('leagues.show', $league->id);
    }

    public function update(Request $request, string $league)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'max_players' => ['sometimes', 'integer', 'min:2', 'max:200'],
            'is_public' => ['sometimes', 'boolean'],
        ]);

        try {
            $this->updateLeagueUseCase->execute($request->user(), $league, $validated);
        } catch (ApplicationException $e) {
            return redirect()->back()->withErrors(['settings' => $e->getMessage()]);
        }

        return redirect()->back();
    }
}
