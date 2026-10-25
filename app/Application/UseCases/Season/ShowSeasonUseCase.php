<?php

declare(strict_types=1);

namespace App\Application\UseCases\Season;

use App\Application\DTOs\View\SeasonIndexPageDTO;
use App\Domain\Interfaces\MediaUrlResolverInterface;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Models\User;

readonly class SeasonCompetition
{
    public function __construct(
        public string $editionId,
        public string $competitionId,
        public string $competitionName,
        public string $competitionType,
        public string $typeLabel,
        public ?string $countryId,
        public ?string $countryName,
        public ?string $coverImageUrl,
        public ?string $logoImageUrl,
        public string $editionStatus,
        public string $officialLeagueId,
        public int $memberCount,
        public bool $isUserMember,
        public bool $canJoin,
        public int $year,
    ) {}
}

class ShowSeasonUseCase
{
    public function __construct(private readonly MediaUrlResolverInterface $mediaUrls) {}

    public function execute(User $user, ?int $year = null): SeasonIndexPageDTO
    {
        $year ??= (int) date('Y');

        $years = EditionModel::select('year')
            ->distinct()
            ->whereHas('competition', fn ($q) => $q->where('active', true))
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->map(fn ($y) => (int) $y)
            ->all();

        $editions = EditionModel::with([
            'competition.country',
            'leagues' => fn ($q) => $q->where('is_official', true)->withCount('users'),
        ])
            ->where('year', $year)
            ->whereHas('competition', fn ($q) => $q->where('active', true))
            ->orderBy('start_date')
            ->get();

        $userLeagueIds = $user->leagues()->pluck('leagues.id')->toArray();

        $competitions = [];

        foreach ($editions as $edition) {
            $officialLeague = $edition->leagues->first();

            if ($officialLeague === null) {
                continue;
            }

            $competitions[] = new SeasonCompetition(
                editionId: $edition->id,
                competitionId: $edition->competition->id,
                competitionName: $edition->competition->name,
                competitionType: $edition->competition->type->value,
                typeLabel: $edition->competition->type->label(),
                countryId: $edition->competition->country_id,
                countryName: $edition->competition->country?->name,
                coverImageUrl: $this->mediaUrls->resolve($edition->competition->cover_image),
                logoImageUrl: $this->mediaUrls->resolve($edition->competition->logo_image),
                editionStatus: $edition->status->value,
                officialLeagueId: $officialLeague->id,
                memberCount: $officialLeague->users_count,
                isUserMember: in_array($officialLeague->id, $userLeagueIds, true),
                canJoin: $edition->status->value !== 'finished',
                year: $edition->year,
            );
        }

        $userJoinedCount = count(array_filter($competitions, fn ($c) => $c->isUserMember));

        return new SeasonIndexPageDTO(
            year: $year,
            years: $years,
            competitions: $competitions,
            userJoinedCount: $userJoinedCount,
            totalCompetitions: count($competitions),
        );
    }
}
