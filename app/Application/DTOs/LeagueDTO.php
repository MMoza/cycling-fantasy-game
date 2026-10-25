<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Application\DTOs\View\EditionLabelViewDTO;
use App\Application\DTOs\View\ScoringSystemLabelViewDTO;
use Illuminate\Contracts\Support\Arrayable;

readonly class LeagueDTO implements Arrayable
{
    public function __construct(
        public string $id,
        public string $name,
        public string $editionId,
        public string $editionName,
        public int $editionYear,
        public string $scoringSystemId,
        public string $scoringSystemName,
        public string $ownerId,
        public string $inviteCode,
        public int $memberCount,
        public bool $isOfficial,
        public bool $isPublic,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'edition' => new EditionLabelViewDTO(name: $this->editionName, year: $this->editionYear),
            'scoring_system' => new ScoringSystemLabelViewDTO(name: $this->scoringSystemName),
            'member_count' => $this->memberCount,
            'owner_id' => $this->ownerId,
            'invite_code' => $this->inviteCode,
            'is_official' => $this->isOfficial,
            'is_public' => $this->isPublic,
        ];
    }
}
