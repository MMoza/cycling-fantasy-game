<?php

declare(strict_types=1);

namespace App\Application\UseCases\Season;

use App\Application\DTOs\View\SeasonClassificationPageDTO;
use App\Application\Services\LeaderboardService;
use App\Domain\Interfaces\MediaUrlResolverInterface;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\ScoreEventModel;
use App\Models\User;

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

readonly class TypeClassification
{
    public function __construct(
        public string $type,
        public string $label,
        public int $totalPoints,
        public array $leaderboard,
    ) {}
}

class ShowSeasonClassificationUseCase
{
    public function __construct(private readonly MediaUrlResolverInterface $mediaUrls) {}

    public function execute(User $user, ?int $year = null): SeasonClassificationPageDTO
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
            return new SeasonClassificationPageDTO(
                year: $year,
                aggregatedLeaderboard: [],
                perCompetition: [],
                byType: [],
            );
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

        $pointsByUser = [];
        $breakdownsByUser = [];

        foreach ($scoresByUser as $userId => $leagueScores) {
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

            $pointsByUser[$userId] = (int) $leagueScores->sum('total_points');
            $breakdownsByUser[$userId] = $breakdown;
        }

        $aggregatedLeaderboard = [];

        foreach (LeaderboardService::rankPoints($pointsByUser) as $row) {
            $userModel = $users[$row['user_id']] ?? null;
            if ($userModel === null) {
                continue;
            }

            $aggregatedLeaderboard[] = new AggregatedLeaderboardEntry(
                rank: $row['rank'],
                userId: $row['user_id'],
                userName: $userModel->name,
                avatar: $this->mediaUrls->resolve($userModel->avatar),
                totalPoints: $row['points'],
                isCurrentUser: $row['user_id'] === $user->id,
                breakdown: $breakdownsByUser[$row['user_id']],
            );
        }

        $perCompetition = [];

        foreach ($editions as $edition) {
            $officialLeague = $edition->leagues->first();
            if ($officialLeague === null) {
                continue;
            }

            $leagueScores = $scoresByUserAndLeague->where('league_id', $officialLeague->id);

            $competitionPoints = [];

            foreach ($leagueScores as $leagueScore) {
                if (! isset($users[$leagueScore->user_id])) {
                    continue;
                }

                $competitionPoints[$leagueScore->user_id] = (int) $leagueScore->total_points;
            }

            $rankedEntries = [];

            foreach (LeaderboardService::rankPoints($competitionPoints) as $row) {
                $userModel = $users[$row['user_id']];

                $rankedEntries[] = new CompetitionLeaderboardEntry(
                    rank: $row['rank'],
                    userId: $row['user_id'],
                    userName: $userModel->name,
                    avatar: $this->mediaUrls->resolve($userModel->avatar),
                    points: $row['points'],
                    isCurrentUser: $row['user_id'] === $user->id,
                );
            }

            $perCompetition[] = new CompetitionClassification(
                editionId: $edition->id,
                competitionName: $edition->competition->name,
                competitionType: $edition->competition->type->value,
                typeLabel: $edition->competition->type->label(),
                logoImageUrl: $this->mediaUrls->resolve($edition->competition->logo_image),
                editionStatus: $edition->status->value,
                leagueId: $officialLeague->id,
                leaderboard: $rankedEntries,
            );
        }

        $typeOrder = ['gc', 'championship', 'monument', 'major', 'classic'];
        $typeLabels = [
            'gc' => 'Grandes Vueltas',
            'championship' => 'Campeonatos',
            'monument' => 'Monumentos',
            'major' => 'Majors',
            'classic' => 'Clásicas',
        ];

        $scoresByTypeAndUser = [];
        foreach ($scoresByUserAndLeague as $scoreEntry) {
            $edition = $leagueIdToEdition[$scoreEntry->league_id] ?? null;
            if ($edition === null) {
                continue;
            }
            $type = $edition->competition->type->value;
            $userId = $scoreEntry->user_id;
            $scoresByTypeAndUser[$type][$userId] = ($scoresByTypeAndUser[$type][$userId] ?? 0) + (int) $scoreEntry->total_points;
        }

        $byType = [];
        foreach ($typeOrder as $type) {
            if (! isset($scoresByTypeAndUser[$type])) {
                continue;
            }

            $typePoints = [];

            foreach ($scoresByTypeAndUser[$type] as $userId => $totalPoints) {
                if (! isset($users[$userId])) {
                    continue;
                }

                $typePoints[$userId] = (int) $totalPoints;
            }

            $rankedTypeEntries = [];

            foreach (LeaderboardService::rankPoints($typePoints) as $row) {
                $userModel = $users[$row['user_id']];

                $rankedTypeEntries[] = new AggregatedLeaderboardEntry(
                    rank: $row['rank'],
                    userId: $row['user_id'],
                    userName: $userModel->name,
                    avatar: $this->mediaUrls->resolve($userModel->avatar),
                    totalPoints: $row['points'],
                    isCurrentUser: $row['user_id'] === $user->id,
                    breakdown: [],
                );
            }

            $totalTypePoints = array_sum($scoresByTypeAndUser[$type]);

            $byType[] = new TypeClassification(
                type: $type,
                label: $typeLabels[$type] ?? $type,
                totalPoints: $totalTypePoints,
                leaderboard: $rankedTypeEntries,
            );
        }

        return new SeasonClassificationPageDTO(
            year: $year,
            aggregatedLeaderboard: $aggregatedLeaderboard,
            perCompetition: $perCompetition,
            byType: $byType,
        );
    }
}
