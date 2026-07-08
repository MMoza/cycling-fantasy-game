<?php

declare(strict_types=1);

namespace App\Application\UseCases\Season;

use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\ScoreEventModel;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

readonly class AggregatedLeaderboardEntry
{
    public function __construct(
        public int $rank,
        public string $userId,
        public string $userName,
        public ?string $avatar,
        public int $totalPoints,
        public bool $isCurrentUser,
        public array $breakdown,
    ) {}
}

readonly class CompetitionLeaderboardEntry
{
    public function __construct(
        public int $rank,
        public string $userId,
        public string $userName,
        public ?string $avatar,
        public int $points,
        public bool $isCurrentUser,
    ) {}
}

readonly class CompetitionClassification
{
    public function __construct(
        public string $editionId,
        public string $competitionName,
        public string $competitionType,
        public string $typeLabel,
        public ?string $logoImageUrl,
        public string $editionStatus,
        public string $leagueId,
        public array $leaderboard,
    ) {}
}

class ShowSeasonClassificationUseCase
{
    public function execute(User $user, ?int $year = null): array
    {
        $year ??= (int) date('Y');

        $editions = EditionModel::with([
            'competition.country',
            'leagues' => fn ($q) => $q->where('is_official', true),
        ])
            ->where('year', $year)
            ->whereHas('competition', fn ($q) => $q->where('active', true))
            ->whereHas('leagues', fn ($q) => $q->where('is_official', true))
            ->orderBy('start_date')
            ->get();

        $officialLeagueIds = $editions->pluck('leagues')->flatten()->pluck('id')->toArray();

        if (empty($officialLeagueIds)) {
            return [
                'year' => $year,
                'aggregated_leaderboard' => [],
                'per_competition' => [],
            ];
        }

        $scoresByUserAndLeague = ScoreEventModel::whereIn('league_id', $officialLeagueIds)
            ->selectRaw('user_id, league_id, SUM(points) as total_points')
            ->groupBy('user_id', 'league_id')
            ->get();

        $leagueIdToEdition = [];
        foreach ($editions as $edition) {
            foreach ($edition->leagues as $league) {
                $leagueIdToEdition[$league->id] = $edition;
            }
        }

        $scoresByUser = $scoresByUserAndLeague->groupBy('user_id');

        $userIds = $scoresByUser->keys()->toArray();
        $users = User::whereIn('id', $userIds)->get()->keyBy('id');

        $aggregatedData = [];

        foreach ($scoresByUser as $userId => $leagueScores) {
            $totalPoints = $leagueScores->sum('total_points');
            $breakdown = [];

            foreach ($leagueScores as $leagueScore) {
                $edition = $leagueIdToEdition[$leagueScore->league_id] ?? null;
                if ($edition) {
                    $breakdown[] = [
                        'competition_name' => $edition->competition->name,
                        'edition_id' => $edition->id,
                        'league_id' => $leagueScore->league_id,
                        'points' => (int) $leagueScore->total_points,
                    ];
                }
            }

            $aggregatedData[] = [
                'user_id' => $userId,
                'total_points' => (int) $totalPoints,
                'breakdown' => $breakdown,
            ];
        }

        usort($aggregatedData, fn ($a, $b) => $b['total_points'] - $a['total_points']);

        $aggregatedLeaderboard = [];
        foreach ($aggregatedData as $index => $data) {
            $userModel = $users[$data['user_id']] ?? null;
            if ($userModel === null) {
                continue;
            }

            $aggregatedLeaderboard[] = new AggregatedLeaderboardEntry(
                rank: $index + 1,
                userId: $data['user_id'],
                userName: $userModel->name,
                avatar: $this->resolveAvatarUrl($userModel->avatar),
                totalPoints: $data['total_points'],
                isCurrentUser: $data['user_id'] === $user->id,
                breakdown: $data['breakdown'],
            );
        }

        $perCompetition = [];

        foreach ($editions as $edition) {
            $officialLeague = $edition->leagues->first();
            if ($officialLeague === null) {
                continue;
            }

            $leagueScores = $scoresByUserAndLeague->where('league_id', $officialLeague->id);

            $competitionEntries = [];
            foreach ($leagueScores as $leagueScore) {
                $userModel = $users[$leagueScore->user_id] ?? null;
                if ($userModel === null) {
                    continue;
                }

                $competitionEntries[] = [
                    'user_id' => $leagueScore->user_id,
                    'user_name' => $userModel->name,
                    'avatar' => $this->resolveAvatarUrl($userModel->avatar),
                    'points' => (int) $leagueScore->total_points,
                    'is_current_user' => $leagueScore->user_id === $user->id,
                ];
            }

            usort($competitionEntries, fn ($a, $b) => $b['points'] - $a['points']);

            $rankedEntries = [];
            foreach ($competitionEntries as $index => $entry) {
                $rankedEntries[] = new CompetitionLeaderboardEntry(
                    rank: $index + 1,
                    userId: $entry['user_id'],
                    userName: $entry['user_name'],
                    avatar: $entry['avatar'],
                    points: $entry['points'],
                    isCurrentUser: $entry['is_current_user'],
                );
            }

            $perCompetition[] = new CompetitionClassification(
                editionId: $edition->id,
                competitionName: $edition->competition->name,
                competitionType: $edition->competition->type->value,
                typeLabel: $edition->competition->type->label(),
                logoImageUrl: $this->resolveS3Url($edition->competition->logo_image),
                editionStatus: $edition->status->value,
                leagueId: $officialLeague->id,
                leaderboard: $rankedEntries,
            );
        }

        return [
            'year' => $year,
            'aggregated_leaderboard' => $aggregatedLeaderboard,
            'per_competition' => $perCompetition,
        ];
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

    private function resolveS3Url(?string $path): ?string
    {
        if ($path === null) {
            return null;
        }

        $disk = Storage::disk('s3');

        try {
            return $disk->temporaryUrl($path, now()->addHours(24));
        } catch (\Exception) {
            // fall through
        }

        try {
            return $disk->url($path);
        } catch (\Exception) {
            // fall through
        }

        $endpoint = rtrim(config('filesystems.disks.s3.endpoint', ''), '/');
        $bucket = config('filesystems.disks.s3.bucket', '');

        if ($endpoint && $bucket) {
            return "{$endpoint}/{$bucket}/".ltrim($path, '/');
        }

        return null;
    }
}
