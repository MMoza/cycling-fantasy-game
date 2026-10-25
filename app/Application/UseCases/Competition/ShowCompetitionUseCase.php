<?php

declare(strict_types=1);

namespace App\Application\UseCases\Competition;

use App\Domain\Interfaces\MediaUrlResolverInterface;
use App\Infrastructure\Persistence\Models\EditionModel;

readonly class CompetitionDetail
{
    public function __construct(
        public string $id,
        public string $name,
        public string $type,
        public string $typeLabel,
        public ?string $countryId,
        public ?string $countryName,
        public ?string $coverImageUrl,
        public ?string $logoImageUrl,
        public string $editionId,
        public int $year,
        public string $editionStatus,
        public string $editionStartDate,
        public string $editionEndDate,
        public ?string $officialLeagueId,
        public ?string $officialLeagueName,
        public int $stagesCount,
        public int $teamsCount,
        public int $ridersCount,
    ) {}
}

class ShowCompetitionUseCase
{
    public function __construct(private readonly MediaUrlResolverInterface $mediaUrls) {}

    public function execute(string $editionId): CompetitionDetail
    {
        $edition = EditionModel::with([
            'competition.country',
            'leagues' => fn ($q) => $q->where('is_official', true),
            'stages',
            'participants.team.rosters' => fn ($q) => $q->where('year', $editionId),
        ])->findOrFail($editionId);

        $competition = $edition->competition;
        $officialLeague = $edition->leagues->first();

        $teamsCount = $edition->participants->count();
        $ridersCount = $edition->participants->sum(fn ($p) => $p->team->rosters->count());

        return new CompetitionDetail(
            id: $competition->id,
            name: $competition->name,
            type: $competition->type->value,
            typeLabel: $competition->type->label(),
            countryId: $competition->country_id,
            countryName: $competition->country?->name,
            coverImageUrl: $this->mediaUrls->resolve($competition->cover_image),
            logoImageUrl: $this->mediaUrls->resolve($competition->logo_image),
            editionId: $edition->id,
            year: $edition->year,
            editionStatus: $edition->status->value,
            editionStartDate: $edition->start_date->format('d M Y'),
            editionEndDate: $edition->end_date->format('d M Y'),
            officialLeagueId: $officialLeague?->id,
            officialLeagueName: $officialLeague?->name,
            stagesCount: $edition->stages->count(),
            teamsCount: $teamsCount,
            ridersCount: $ridersCount,
        );
    }
}
