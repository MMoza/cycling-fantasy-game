<?php

declare(strict_types=1);

namespace App\Application\UseCases\Competition;

use App\Application\DTOs\View\CompetitionsIndexDTO;
use App\Domain\Interfaces\MediaUrlResolverInterface;
use App\Infrastructure\Persistence\Models\EditionModel;

readonly class CompetitionCard
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
        public ?string $officialLeagueId,
        public ?string $officialLeagueName,
        public string $editionId,
        public int $year,
        public string $editionStatus,
    ) {}
}

readonly class CompetitionGroup
{
    /** @param CompetitionCard[] $competitions */
    public function __construct(
        public string $type,
        public string $typeLabel,
        public array $competitions,
    ) {}
}

readonly class YearGroup
{
    /** @param CompetitionGroup[] $groups */
    public function __construct(
        public int $year,
        public array $groups,
    ) {}
}

class ListActiveCompetitionsUseCase
{
    public function __construct(private readonly MediaUrlResolverInterface $mediaUrls) {}

    public function execute(?int $year = null): CompetitionsIndexDTO
    {
        $year ??= (int) date('Y');

        $editions = EditionModel::with([
            'competition.country',
            'leagues' => fn ($q) => $q->where('is_official', true),
        ])
            ->where('year', $year)
            ->whereHas('competition', fn ($q) => $q->where('active', true))
            ->orderBy('start_date')
            ->get();

        $cards = [];
        $typeOrder = ['gc', 'major', 'monument', 'classic', 'championship'];

        foreach ($editions as $edition) {
            $competition = $edition->competition;
            $officialLeague = $edition->leagues->first();

            $cards[] = new CompetitionCard(
                id: $competition->id,
                name: $competition->name,
                type: $competition->type->value,
                typeLabel: $competition->type->label(),
                countryId: $competition->country_id,
                countryName: $competition->country?->name,
                coverImageUrl: $this->mediaUrls->resolve($competition->cover_image),
                logoImageUrl: $this->mediaUrls->resolve($competition->logo_image),
                officialLeagueId: $officialLeague?->id,
                officialLeagueName: $officialLeague?->name,
                editionId: $edition->id,
                year: $edition->year,
                editionStatus: $edition->status->value,
            );
        }

        $grouped = collect($cards)->groupBy('type');

        $groups = collect($typeOrder)
            ->filter(fn (string $type) => $grouped->has($type))
            ->map(function (string $type) use ($grouped) {
                $first = $grouped[$type][0];

                return new CompetitionGroup(
                    type: $type,
                    typeLabel: $first->typeLabel,
                    competitions: $grouped[$type]->sortBy('name')->values()->all(),
                );
            })
            ->values()
            ->all();

        $years = EditionModel::select('year')
            ->distinct()
            ->whereHas('competition', fn ($q) => $q->where('active', true))
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        return new CompetitionsIndexDTO(
            yearGroups: [new YearGroup(year: $year, groups: $groups)],
            years: $years,
            currentYear: $year,
        );
    }
}
