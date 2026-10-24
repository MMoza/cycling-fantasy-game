<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use App\Infrastructure\Persistence\Models\LeagueModel;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Public league card from the league search: {id, name, edition, scoring_system,
 * member_count, owner_name, is_joined, is_official}.
 */
readonly class PublicLeagueCardDTO implements Arrayable
{
    /**
     * @param  array<string, int>  $joinedLeagueIds
     */
    public function __construct(
        public string $id,
        public string $name,
        public EditionLabelViewDTO $edition,
        public ScoringSystemLabelViewDTO $scoringSystem,
        public int $memberCount,
        public string $ownerName,
        public bool $isJoined,
        public bool $isOfficial,
    ) {}

    /**
     * @param  array<string, int>  $joinedLeagueIds
     */
    public static function fromModel(LeagueModel $league, array $joinedLeagueIds): self
    {
        return new self(
            id: $league->id,
            name: $league->name,
            edition: new EditionLabelViewDTO(
                name: $league->edition->competition->name,
                year: (int) $league->edition->year,
            ),
            scoringSystem: new ScoringSystemLabelViewDTO(name: $league->scoringSystem->name),
            memberCount: (int) $league->users_count,
            ownerName: $league->owner->name,
            isJoined: isset($joinedLeagueIds[$league->id]),
            isOfficial: (bool) $league->is_official,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'edition' => $this->edition,
            'scoring_system' => $this->scoringSystem,
            'member_count' => $this->memberCount,
            'owner_name' => $this->ownerName,
            'is_joined' => $this->isJoined,
            'is_official' => $this->isOfficial,
        ];
    }
}
