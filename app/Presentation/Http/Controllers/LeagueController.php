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
use App\Domain\ValueObjects\PredictionType;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\PredictionModel;
use App\Infrastructure\Persistence\Models\ScoreEventModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
        $user = $request->user();

        $myLeagues = $this->listLeaguesUseCase->execute($user);

        $search = $request->query('q');
        $publicLeagues = collect();

        if ($search || $request->has('page')) {
            $query = LeagueModel::withCount('users')
                ->with(['edition.competition', 'scoringSystem', 'owner'])
                ->where('is_public', true);

            if ($search) {
                $query->where('name', 'like', "%{$search}%");
            }

            $publicLeagues = $query->orderBy('name')
                ->paginate(20)
                ->through(fn ($league) => [
                    'id' => $league->id,
                    'name' => $league->name,
                    'edition' => [
                        'name' => $league->edition->competition->name,
                        'year' => $league->edition->year,
                    ],
                    'scoring_system' => [
                        'name' => $league->scoringSystem->name,
                    ],
                    'member_count' => $league->users_count,
                    'owner_name' => $league->owner->name,
                    'is_joined' => $user->leagues()->where('leagues.id', $league->id)->exists(),
                    'is_official' => $league->is_official,
                ]);
        }

        return Inertia::render('Leagues/Index', [
            'my_leagues' => $myLeagues->map(fn ($dto) => [
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
                'is_official' => $dto->isOfficial,
                'is_public' => $dto->isPublic,
            ]),
            'public_leagues' => $publicLeagues,
            'search_query' => $search ?? '',
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
            ->whereIn('status', ['upcoming', 'ongoing'])
            ->orderByRaw("CASE WHEN status = 'ongoing' THEN 0 ELSE 1 END")
            ->orderBy('date')
            ->orderBy('scheduled_start')
            ->first();

        $nextStageHasPredictions = $nextStage
            ? PredictionModel::where('league_id', $leagueModel->id)
                ->where('user_id', $userId)
                ->where('stage_id', $nextStage->id)
                ->where('type', PredictionType::PreStage)
                ->exists()
            : false;

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
            ->select('users.id', 'users.name', 'users.avatar')
            ->get();

        $leaderboard = $members
            ->map(fn ($member) => [
                'user_id' => $member->id,
                'user_name' => $member->name,
                'avatar' => $this->resolveAvatarUrl($member->avatar),
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
            'league_id' => $leagueModel->id,
            'league' => [
                'id' => $leagueModel->id,
                'name' => $leagueModel->name,
                'invite_code' => $leagueModel->invite_code,
                'competition' => [
                    'name' => $leagueModel->edition->competition->name,
                    'year' => $leagueModel->edition->year,
                    'coverImageUrl' => $this->resolveAvatarUrl($leagueModel->edition->competition->cover_image),
                    'logoImageUrl' => $this->resolveAvatarUrl($leagueModel->edition->competition->logo_image),
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
                'is_official' => $leagueModel->is_official,
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
                'type_value' => $nextStage->type->value,
                'distance' => $nextStage->distance ? "{$nextStage->distance} km" : null,
                'distance_value' => $nextStage->distance,
                'origin' => $nextStage->origin,
                'destination' => $nextStage->destination,
                'status' => $nextStage->status->value,
                'scheduled_start' => $nextStage->scheduled_start?->toIso8601String(),
                'difficulty' => $nextStage->difficulty,
                'has_predictions' => $nextStageHasPredictions,
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
            'is_public' => ['required', 'boolean'],
            'is_official' => ['sometimes', 'boolean'],
        ]);

        $dto = new CreateLeagueDTO(
            name: $validated['name'],
            editionId: $validated['edition_id'],
            scoringSystemId: $validated['scoring_system_id'],
            isPublic: $validated['is_public'],
            isOfficial: $validated['is_official'] ?? false,
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
            'is_public' => ['sometimes', 'boolean'],
        ]);

        try {
            $this->updateLeagueUseCase->execute($request->user(), $league, $validated);
        } catch (ApplicationException $e) {
            return redirect()->back()->withErrors(['settings' => $e->getMessage()]);
        }

        return redirect()->back();
    }

    private function resolveAvatarUrl(?string $path): ?string
    {
        if ($path === null) {
            return null;
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        $disk = Storage::disk('s3');

        try {
            return $disk->temporaryUrl($path, now()->addHours(24));
        } catch (\Exception) {
            try {
                return $disk->url($path);
            } catch (\Exception) {
                return null;
            }
        }
    }
}
