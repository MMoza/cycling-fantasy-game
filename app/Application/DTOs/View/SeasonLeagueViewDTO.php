<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use App\Domain\Interfaces\MediaUrlResolverInterface;
use App\Infrastructure\Persistence\Models\LeagueModel;
use Illuminate\Contracts\Support\Arrayable;

/**
 * League of the current season for the league switcher:
 * {id, name, year, is_official, member_count, edition_status, competition}.
 */
readonly class SeasonLeagueViewDTO implements Arrayable
{
    public function __construct(
        public string $id,
        public string $name,
        public int $year,
        public bool $isOfficial,
        public int $memberCount,
        public string $editionStatus,
        public SeasonLeagueCompetitionViewDTO $competition,
    ) {}

    public static function fromModel(LeagueModel $league, MediaUrlResolverInterface $mediaUrls): self
    {
        $competition = $league->edition->competition;

        return new self(
            id: $league->id,
            name: $league->name,
            year: (int) $league->edition->year,
            isOfficial: (bool) $league->is_official,
            memberCount: (int) $league->users_count,
            editionStatus: $league->edition->status->value,
            competition: new SeasonLeagueCompetitionViewDTO(
                name: $competition->name,
                type: $competition->type->value,
                coverImageUrl: $mediaUrls->resolve($competition->cover_image),
                logoImageUrl: $mediaUrls->resolve($competition->logo_image),
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'year' => $this->year,
            'is_official' => $this->isOfficial,
            'member_count' => $this->memberCount,
            'edition_status' => $this->editionStatus,
            'competition' => $this->competition,
        ];
    }
}
