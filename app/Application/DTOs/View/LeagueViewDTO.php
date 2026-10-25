<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use Illuminate\Contracts\Support\Arrayable;

/**
 * League detail block of the dashboard.
 */
readonly class LeagueViewDTO implements Arrayable
{
    public function __construct(
        public string $id,
        public string $name,
        public string $inviteCode,
        public CompetitionViewDTO $competition,
        public string $ownerId,
        public ScoringSystemViewDTO $scoringSystem,
        public bool $isOfficial,
        public bool $isPublic,
        public int $maxPlayers,
        public int $memberCount,
        public bool $isOwner,
        public string $editionStatus,
        public StageProgressViewDTO $progress,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'invite_code' => $this->inviteCode,
            'competition' => $this->competition,
            'owner_id' => $this->ownerId,
            'scoring_system' => $this->scoringSystem,
            'is_official' => $this->isOfficial,
            'is_public' => $this->isPublic,
            'max_players' => $this->maxPlayers,
            'member_count' => $this->memberCount,
            'is_owner' => $this->isOwner,
            'edition_status' => $this->editionStatus,
            'progress' => $this->progress,
        ];
    }
}
